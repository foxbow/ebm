<?PHP

$ebm_database="SQLite";

// Initialize the basics and create the 'root' user called 'ebm'
// with password 'ebm'
function db_initDB(){
    $cid = db_openDB();
    $res = sqlite_query( "CREATE TABLE users ( name VARCHAR(16) UNIQUE, password CHAR(32) );", $cid );
    $res = sqlite_query( "INSERT INTO users VALUES ( 'ebm', '3dc70661cd5ea000608c27661b5c240b' );", $cid );
    $res = sqlite_query( "CREATE TABLE cats ( name VARCHAR(16), cat VARCHAR(32), cid INTEGER );", $cid );
    $res = sqlite_query( "CREATE TABLE links ( cid INTEGER, link VARCHAR(256), text VARCHAR(128) );", $cid );
    $res = sqlite_query( "CREATE TABLE settings ( name VARCHAR(16), value VARCHAR(32), uname VARCHAR(16), PRIMARY KEY (name, uname) );", $cid );
    sqlite_close( $cid );
}

/**
 * open the database.
 * If it does not exist, try to create it or at least prompt the
 * user/admin to do that.
 **/
function db_openDB(){
    global $ebm_prefix;
    $cid = sqlite_open("$ebm_prefix/ebm.db", 0666, $e);
    if(!$cid) nodb();
    return $cid;
}

function db_cleanup(){
    $cid = db_openDB();
    $res = sqlite_query( "VACUUM;", $cid );
    sqlite_close( $cid );
}

/**
 * return a value to a given setting.
 **/
function db_getSetting( $name, $def, $uname ){
    $cid = db_openDB();
    $res = sqlite_query( "SELECT value FROM settings WHERE name='$name' AND uname='ebm';", $cid );
    if(!$res){
        sqlite_close( $cid );
        db_initDB();
        return db_getSetting( $name, $def, $uname );
    }
    if( sqlite_num_rows( $res ) > 0 ){
        $ret = sqlite_fetch_array( $res );
        $def = db_decode($ret[0]);
    }
    $res = sqlite_query( "SELECT value FROM settings WHERE name='$name' AND uname='$uname';", $cid );
    if( sqlite_num_rows( $res ) > 0 ){
        $ret = sqlite_fetch_array( $res );
        $def = db_decode($ret[0]);
    }
    sqlite_close( $cid );
    return $def;
}

/**
 * set a certain value in the settings.
 * If it does not exist yet, create a new one.
 **/
function db_setSetting( $name, $value, $uname ){
    $cid = db_openDB();
    $value = db_encode( $value );
    $res = sqlite_query( "DELETE FROM settings WHERE name='$name' AND uname='$uname';", $cid );
    $res = sqlite_query( "INSERT INTO settings VALUES ('$name','$value','$uname');", $cid );
    sqlite_close( $cid );
    return db_decode($value);
}

/**
 * get the password of the given user.
 **/
function db_getPassword( $name ){
    $cid = db_openDB();
    $res = sqlite_query( "SELECT password FROM users WHERE name='$name';", $cid );
    if( sqlite_num_rows( $res ) < 1 ) return "";
    $ret = sqlite_fetch_array( $res );
    sqlite_close( $cid );
    return $ret[0];
}

function db_addUser( $name, $pass ){
    $cid = db_openDB();
    $res = sqlite_query( "SELECT password FROM users WHERE name='$name';", $cid  );
    if( sqlite_num_rows( $res ) < 1 )
      $res = sqlite_query( "INSERT INTO users ( name, password ) VALUES ( '$name', '$pass' );", $cid );
    sqlite_close( $cid );
}

function db_updateUser( $name, $pass ){
    $cid = db_openDB();
    $res = sqlite_query( "UPDATE users SET password='$pass' WHERE name='$name';", $cid );
    sqlite_close( $cid );
}

/**
 * deletes an user together will all his categories
 * and links.
 **/
function db_deleteUser( $user ){
    $cid = db_openDB();
    sqlite_query( "BEGIN TRANSACTION;", $cid );

    $res = sqlite_query( "SELECT cat FROM cats WHERE name='$user';", $cid );
    $rows = sqlite_num_rows( $res );
    $cats = array();
    for( $i=0; $i < $rows; $i++ ){
	$val = sqlite_fetch_array( $res );
	$cats[ $i ] = $val[0];
    }
    foreach( $cats as $cat ){
	$catid = db_getCatID( $cat, $cid );
	$res = sqlite_query( "DELETE FROM links WHERE ( cid='$catid' );", $cid );
	$res = sqlite_query( "DELETE FROM cats WHERE ( name='$user' AND cat='$cat' );", $cid );
    }

    $res = sqlite_query( "DELETE FROM users WHERE name='$user';", $cid );

    sqlite_query( "COMMIT;", $cid );
    sqlite_close( $cid );
}

/**
 * returns all users
 **/
function db_getUsers(){
    $cid = db_openDB();
    $res = sqlite_query( "SELECT name FROM users;", $cid );
    $rows = sqlite_num_rows( $res );
    $users = array();
    for( $i=0; $i < $rows; $i++ ){
	$val = sqlite_fetch_array( $res );
	$users[ $i ] = $val[0];
    }
    sqlite_close( $cid );
    return $users;
}

/*
 * returns an array containing all categories
 */
function db_getCategories(){
    global $ebm_user;

    $cid = db_openDB();
    // Get the categories
    $res = sqlite_query( "SELECT cat FROM cats WHERE name='$ebm_user';", $cid );
    $rows = sqlite_num_rows( $res );
    $category = array();
    // $val = sqlite_fetch_array( $res );
    for( $i=0; $i < $rows; $i++ ){
	$val = sqlite_fetch_array( $res );
	$category[ $i ] = db_decode( $val[0] );
    }

    sqlite_close( $cid );

    // Return them
    return $category;
}

function db_newCat( $cat ){
    global $ebm_user;
    $cat=db_encode($cat);
    $cid = db_openDB();
    $res = sqlite_query( "SELECT count(*) FROM cats WHERE ( name='$ebm_user' AND cat='$cat' );", $cid );
    $val = sqlite_fetch_array( $res );
    if( $val[0] == 0 ){
	$res = sqlite_query( "SELECT MAX(cid) FROM cats;", $cid );
	$ret = sqlite_fetch_array( $res );
	$val = $ret[0];
	if("$val" == "") $val = 0;
	else $val++;
	$res = sqlite_query( "INSERT INTO cats ( name, cat, cid ) VALUES ( '$ebm_user', '$cat', $val );", $cid );
    }
    sqlite_close( $cid );
}

function db_removeCat( $cat ){
    global $ebm_user;
    $cat = db_encode( $cat );
    $cid = db_openDB();
    $catid = db_getCatID( $cat, $cid );
    $res = sqlite_query( "DELETE FROM links WHERE ( cid='$catid' );", $cid );
    $res = sqlite_query( "DELETE FROM cats WHERE ( name='$ebm_user' AND cat='$cat' );", $cid );
    sqlite_close( $cid );
}

function db_renCat( $cat, $ncat ){
    global $ebm_user;
    $cat = db_encode( $cat );
    $ncat = db_encode( $ncat );
    $cid = db_openDB();
    $res = sqlite_query( "UPDATE cats SET cat='$ncat' WHERE ( name='$ebm_user' and cat='$cat' );", $cid );
    sqlite_close( $cid );
}

// $cat MUST be encoded!
function db_getCatID( $cat, $cid ){
    global $ebm_user;
    $res = sqlite_query( "SELECT cid FROM cats WHERE ( name='$ebm_user' AND cat='$cat' );", $cid );
    if(!$res) {
	echo "SELECT cid FROM cats WHERE ( name='$ebm_user' AND cat='$cat' );<br>";
	return -1;
    }
    $catid = sqlite_fetch_array( $res );
    return $catid[0];
}

/*
 * returns all entries of a category in the form
 * description<>link
 */
function db_getEntries( $cat ){
    $cid = db_openDB();
    $cat = db_encode( $cat );
    $catid = db_getCatID( $cat, $cid );
    $entries=array();
    if(empty($catid)) return $entries;
    $res = sqlite_query( "SELECT text, link FROM links WHERE cid=$catid ORDER BY text;", $cid );
    for( $rowid=0; $rowid < sqlite_num_rows( $res ); $rowid++ ){
        $row=sqlite_fetch_array( $res );
        $desc=db_decode( $row[0] );
	$link=db_decode( $row[1] );
        $entries[ $rowid ]="$desc<>$link";
    }
    sqlite_close( $cid );

    // Return them
    return $entries;
}

function db_searchEntries( $keyword, $name ){
    $cid = db_openDB();
    $keyword = db_encode( $keyword );
    $res = sqlite_query( "SELECT text, link FROM links WHERE text LIKE '%$keyword%' AND cid IN (SELECT cid FROM cats WHERE name='$name') ORDER BY text;", $cid );
    $entries=array();
    for( $rowid=0; $rowid < sqlite_num_rows( $res ); $rowid++ ){
        $row=sqlite_fetch_array( $res );
        $desc=db_decode( $row[0] );
	$link=db_decode( $row[1] );
        $entries[ $rowid ]="$desc<>$link";
    }
    sqlite_close( $cid );

    return $entries;
}

/**
 * takes an addslashed text (like we get from a a'post'
 * and turns it into something appropriate for the
 * database. html entities (&*;) will be resolved too.
 **/
function db_encode( $text ){
//    $text=str_replace("'", "''", $text);
	$text=html_entity_decode($text);
    return sqlite_escape_string( $text );
}

/**
 * takes a database encoded string and returns is into
 * standard text format.
 **/
function db_decode( $text ){
    // return str_replace("''", "'", $text);
    return $text;
}

/**
 * append an entry
 **/
function db_appendEntry($cat, $link, $desc){
    $desc = db_encode( $desc );
    $link = db_encode( $link );
    $cat = db_encode( $cat );
    $cid = db_openDB();
    $catid = db_getCatID( $cat, $cid );
    $res = sqlite_query( "SELECT cid FROM links WHERE ( cid=$catid AND link='$link' AND text='$desc' );", $cid );
    if($res===false) echo( "SELECT cid FROM links WHERE ( cid=$catid AND link='$link' AND text='$desc' );<br>" );
    if( sqlite_num_rows( $res ) < 1 ){
	$res = sqlite_query( "INSERT INTO links ( cid, link, text ) VALUES ( $catid, '$link', '$desc' );", $cid );
	if(!$res){
	    echo "INSERT INTO links ( cid, link, text ) VALUES ( $catid, '$link', '$desc' );<br>\n";
	}
    }
    sqlite_close( $cid );
}

/**
 * get an entry by description
 **/
function db_getLink($cat, $desc){
    $desc=db_encode( $desc );
    $cat =db_encode( $cat );
    $cid=db_openDB();
    $catid=db_getCatID( $cat, $cid );
    $res = sqlite_query( "SELECT link FROM links WHERE ( cid=$catid AND text='$desc' );", $cid);
    if( (!$res) || (sqlite_num_rows( $res ) < 1 ) ) return "";
    $row=sqlite_fetch_array( $res );
    return db_decode( $row[0] );
}

/**
 * delete an entry
 **/
function db_removeEntry($cat, $link, $desc){
    $cat = db_encode( $cat );
    $link = db_encode( $link );
    $desc = db_encode( $desc );
    $cid = db_openDB();
    $catid = db_getCatID( $cat, $cid );
    $res = sqlite_query( "DELETE FROM links WHERE ( cid=$catid AND link='$link' AND text='$desc' );", $cid );
    if( !$res )
      echo( "DELETE FROM links WHERE ( cid=$catid AND link='$link' AND text='$desc' );<br>");
    sqlite_close( $cid );
}

/**
 * change an existing entry into a new one.
 **/
function db_updateEntry($cat, $olink, $odesc, $nlink, $ndesc){
    // Update the entry.
    $cat = db_encode( $cat );
    $nlink = db_encode( $nlink );
    $ndesc = db_encode( $ndesc );
    $olink = db_encode( $olink );
    $odesc = db_encode( $odesc );
    $cid = db_openDB();
    $catid = db_getCatID( $cat, $cid );
    $res = sqlite_query( "UPDATE OR IGNORE links SET link='$nlink',text='$ndesc' WHERE ( cid=$catid AND link='$olink' AND text='$odesc' );", $cid );
     if( !$res )
      echo( "UPDATE links SET link='$nlink',text='$ndesc' WHERE ( cid=$catid AND link='$olink' AND text='$odesc' );<br>");

    sqlite_close( $cid );
}

/**
 * Move an entry from one category to another
 * Most simple solution follows - maybe for your
 * DB this is not the best way, then change it
 * accordingly.
 **/
function db_moveEntry($source, $link, $desc, $target){
    $scat = db_encode( $source );
    $link = db_encode( $link );
    $desc = db_encode( $desc );
    $tcat = db_encode( $target );
    $cid = db_openDB();
    $scatid = db_getCatID( $scat, $cid );
    $tcatid = db_getCatID( $tcat, $cid );
    $res = sqlite_query( "UPDATE OR IGNORE links SET cid=$tcatid WHERE ( cid=$scatid AND link='$link' AND text='$desc' );", $cid );
    if( !$res )
      echo( "UPDATE OR IGNORE links SET cid=$tcatid WHERE ( cid=$scatid AND link='$link' AND text='$desc' );<br>");

    sqlite_close( $cid );
//    db_removeEntry($source, $link, $desc);
//    db_appendEntry($target, $link, $desc);
}

?>
