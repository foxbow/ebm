<?PHP

$database="postgres";

// Initialize the basics and create the 'root' user called 'ebm'
// with password 'ebm'
function db_initDB(){
	 $cid = db_openDB();
	 $res = pg_query( "CREATE TABLE users ( name VARCHAR(16) UNIQUE, password CHAR(32) );" );
	 $res = pg_query( "INSERT INTO users VALUES ( 'ebm', '3dc70661cd5ea000608c27661b5c240b' );" );
	 $res = pg_query( "CREATE SEQUENCE cid;" );
	 $res = pg_query( "CREATE TABLE cats ( name VARCHAR(16), cat VARCHAR(32), cid INTEGER DEFAULT NEXTVAL('cid') );");
	 $res = pg_query( "CREATE TABLE links ( cid INTEGER, link VARCHAR(256), text VARCHAR(128) );");
	 $res = pg_query( "CREATE TABLE settings ( name VARCHAR(16), value VARCHAR(32),uname VARCHAR(16), PRIMARY KEY ( name, uname ) );" ); 
	 pg_close( $cid );
}

function db_openDB(){
	 global $connectstring;
	 $cid = pg_connect($connectstring);
	 if(!$cid) nodb();
	 return $cid;
}

/**
 * return a value to a given setting.
 **/
function db_getSetting( $name, $def, $uname ){
    $cid = db_openDB();
    $res = pg_query( "SELECT value FROM settings WHERE name='$name' AND uname='ebm';" );
    if(!$res){
        pg_close( $cid );
        db_initDB();
        return db_getSetting( $name, $def, $uname );
    }
    if( pg_num_rows( $res ) > 0 ){
        $ret = pg_fetch_array( $res );
        $def = stripslashes($ret[0]);
    }
    $res = pg_query( "SELECT value FROM settings WHERE name='$name' AND uname='$uname';" );
    if( pg_num_rows( $res ) > 0 ){
        $ret = pg_fetch_array( $res );
        $def = stripslashes($ret[0]);
    }
	 pg_close( $cid );
    return $def;
}

/**
 * set a certain value in the settings.
 * If it does not exist yet, create a new one.
 **/
function db_setSetting( $name, $value, $uname ){
	 $value = addslashes( $value );
	 $cid = db_openDB();
	 $res = pg_query( "DELETE FROM settings WHERE name='$name' AND uname='$uname';" );
	 $res = pg_query( "INSERT INTO settings (name,value,uname) VALUES ('$name', '$value', '$uname');" );
	 pg_close( $cid );
	 return stripslashes($value);
}

function db_updateUser( $name, $pass ){
	 $cid = db_openDB();
	 $res = pg_query( "UPDATE users SET password='$pass' WHERE name='$name';" );
	 pg_close( $cid );
}

/**
 * deletes an user together will all his categories
 * and links.
 **/
function db_deleteUser( $user ){
	 $cid = db_openDB();
	 pg_query( "BEGIN TRANSACTION;" );

	 $res = pg_query( "SELECT cat FROM cats WHERE name='$user';" );
	 $rows = sqlite_num_rows( $res );
	 $cats = array();
	 for( $i=0; $i < $rows; $i++ ){
		  $val = pg_fetch_array( $res );
		  $cats[ $i ] = $val[0];
	 }
	 foreach( $cats as $cat ){
		  $catid = db_getCatID( $cat, $cid );
		  $res = pg_query( "DELETE FROM links WHERE ( cid=$catid );" );
		  $res = pg_query( "DELETE FROM cats WHERE ( name='$user' AND cat='$cat' );" );
	 }
	 
	 $res = pg_query( "DELETE FROM users WHERE name='$user';" );
	 
	 pg_query( "COMMIT;" );
	 pg_close( $cid );
}

/**
 * returns all users
 **/
function db_getUsers(){
	 $cid = db_openDB();
	 $res = pg_query( "SELECT name FROM users;" );
	 $rows = pg_num_rows( $res );
	 $users = array();
	 for( $i=0; $i < $rows; $i++ ){
		  $val = pg_fetch_array( $res );
		  $users[ $i ] = $val[0];
	 }
	 pg_close( $cid );
	 return $users;
}


function db_getPassword( $user ){
	 $cid = db_openDB();
	 $res = pg_query( "SELECT password FROM users WHERE name='$user';" );
	 if( pg_num_rows( $res ) < 1 ) return "";
	 $ret = pg_fetch_result( $res, 0, 0 );
	 pg_close( $cid );
	 return $ret;
}

function db_newUser( $nuser, $pass ){
	 $cid = db_openDB();
	 $res = pg_query( "SELECT password FROM users WHERE name='$nuser';" );
	 if( pg_num_rows( $res ) < 1 )
		$res = pg_query( "INSERT INTO users ( name, password ) VALUES ( '$nuser', '$pass' );" );
	 pg_close( $cid );
}

/*
 * returns an array containing all categories
 */
function db_getCategories(){
	 global $user; 
	 
	 $cid = db_openDB();
	 // Get the categories
	 $res = pg_query( "SELECT cat FROM cats WHERE name='$user';" );

	 $rows = pg_num_rows( $res );
	 $category=array();
	 for( $i=0; $i < $rows; $i++ ){
		  $category[ $i ] = stripslashes( pg_fetch_result( $res, $i, 0 ) );
	 }
	 
	 pg_close( $cid );
		 
	 // Return them
	 return $category;
}

function db_newCat( $cat ){
	 global $user;
	 $cid = db_openDB();
	 $res = pg_query( "SELECT count(*) FROM cats WHERE ( name='$user' AND cat='$cat' );" );
	 if( pg_fetch_result( $res, 0, 0 ) == 0 ){
		  $res = pg_query( "INSERT INTO cats ( name, cat ) VALUES ( '$user', '$cat' );" );
	 }
	 pg_close( $cid );
}

function db_removeCat( $cat ){
	 global $user;
	 $cid = db_openDB();
	 $catid = db_getCatID( $cat );
	 $res = pg_query( "DELETE FROM links WHERE ( cid=$catid );" );
	 $res = pg_query( "DELETE FROM cats WHERE ( name='$user' AND cat='$cat' );" );
	 pg_close( $cid );
}

function db_getCatID( $cat ){
	 global $user;
	 $res = pg_query( "SELECT cid FROM cats WHERE ( name='$user' AND cat='$cat' );" );
	 $catid = pg_fetch_result( $res, 0, 0 );
	 return $catid;
}
/*
 * returns all entries of a category in the form
 * description<>link
 */
function db_getEntries( $cat ){
	 global $user;
	 global $prefix;
	 
	 global $user;
	 $cid = db_openDB();
	 $catid = db_getCatID( $cat );
	 $res = pg_query( "SELECT text, link FROM links WHERE cid=$catid ORDER BY text;" );
	 $entries=array();
	 for( $rowid=0; $rowid < pg_num_rows( $res ); $rowid++ ){
		  $row=pg_fetch_row( $res, $rowid );
		  $desc=stripslashes( $row[0] );
		  $entries[ $rowid ]="$desc<>$row[1]";
	 }
	 pg_close( $cid );

	 // Return them
	 return $entries;
}

function db_searchEntries( $keyword, $name ){
    global $user;
    global $prefix;
    
    $cid = db_openDB();
    $res = pg_query( "SELECT text, link FROM links WHERE LOWER(text) LIKE '%$keyword%' AND cid IN (SELECT cid FROM cats WHERE name='$name') ORDER BY text;" );
    $entries=array();
    for( $rowid=0; $rowid < sqlite_num_rows( $res ); $rowid++ ){
        $row=pg_fetch_row( $res, $rowid );
        $desc=stripslashes( $row[0] );
        $entries[ $rowid ]="$desc<>$row[1]";
    }
    pg_close( $cid );
    
    return $entries;
}

/**
 * append an entry
 **/
function db_appendEntry($cat, $link, $desc){
	 global $user;
	 
	 $cid = db_openDB();
	 $catid = db_getCatID( $cat );
	 $res = pg_query( "SELECT count(*) FROM links WHERE ( cid=$catid AND link='$link' AND text='$desc' );" );
	 if( pg_fetch_result( $res, 0, 0 ) == 0 ){
		  $res = pg_query( "INSERT INTO links ( cid, link, text ) VALUES ( $catid, '$link', '$desc' );" );
	 }
	 pg_close( $cid );
}

/**
 * delete an entry
 **/
function db_removeEntry($cat, $link, $desc){
	 global $user;
	 $cid = db_openDB();
	 $catid = db_getCatID( $cat );
	 $res = pg_query( "DELETE FROM links WHERE ( cid=$catid AND link='$link' AND text='$desc' );" );
	 pg_close( $cid );
}

/**
 * change an existing entry into a new one.
 **/
function db_updateEntry($cat, $olink, $odesc, $nlink, $ndesc){
	 global $user;
	 global $prefix;
	 
    // Update the entry.
	 // - delete the old, write the new
	 // - update the old
	 // Whatever is more easy.
	 db_removeEntry($cat, $olink, $odesc);
	 db_appendEntry($cat, $nlink, $ndesc);
}

/**
 * Move an entry from one category to another
 * Most simple solution follows - maybe for your
 * DB this is not the best way, then change it
 * accordingly.
 **/
function db_moveEntry($source, $link, $desc, $target){
	 db_removeEntry($source, $link, $desc);
	 db_appendEntry($target, $link, $desc);
}

?>
