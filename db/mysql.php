<?PHP

$database="mySQL";

// Initialize the basics and create the 'root' user called 'ebm'
// with password 'ebm'
function db_initDB(){
    $cid = db_openDB();
    $res = mysql_query( "CREATE TABLE users ( name VARCHAR(16) UNIQUE, password CHAR(32) );", $cid );
    $res = mysql_query( "INSERT INTO users VALUES ( 'ebm', '3dc70661cd5ea000608c27661b5c240b' );", $cid );
    $res = mysql_query( "CREATE TABLE cats ( name VARCHAR(16), cat VARCHAR(32), cid INTEGER );", $cid );
    $res = mysql_query( "CREATE TABLE links ( cid INTEGER, link VARCHAR(255), text VARCHAR(128) );", $cid );
    $res = mysql_query( "CREATE TABLE settings ( name VARCHAR(16), value VARCHAR(32), uname VARCHAR(16), PRIMARY KEY (name, uname) );", $cid );
    mysql_close( $cid );
}

/**
 * open the database.
 * If it does not exist, try to create it or at least prompt the
 * user/admin to do that.
 **/
function db_openDB(){
    global $my_user, $my_host, $my_pass, $my_db;
    $cid = mysql_connect($my_host, $my_user, $my_pass);
    if(!$cid) nodb();
	if(!mysql_query("use $my_db")) nodb();
//    mysql_select_db( $my_db ) or nodb();
    return $cid;
}

/**
 * return a value to a given setting.
 **/
function db_getSetting( $name, $def, $uname ){
    $cid = db_openDB();
    $res = mysql_query( "SELECT value FROM settings WHERE name='$name' AND uname='ebm';", $cid );
    if(!$res){
	mysql_close( $cid );
	db_initDB();
	return db_getSetting( $name, $def, $uname );
    }
    if( mysql_num_rows( $res ) > 0 ){
	$ret = mysql_fetch_array( $res );
	$def = db_decode($ret[0]);
    }
    $res = mysql_query( "SELECT value FROM settings WHERE name='$name' AND uname='$uname';", $cid );
    if( mysql_num_rows( $res ) > 0 ){
	$ret = mysql_fetch_array( $res );
	$def = db_decode($ret[0]);
    }
    mysql_close( $cid );
    return $def;
}

/**
 * set a certain value in the settings.
 * If it does not exist yet, create a new one.
 **/
function db_setSetting( $name, $value, $uname ){
    $cid = db_openDB();
    $value = db_encode( $value );
    $res = mysql_query( "DELETE FROM settings WHERE name='$name' AND uname='$uname';", $cid );
    $res = mysql_query( "INSERT INTO settings VALUES ('$name', '$value', '$uname');", $cid );
    mysql_close( $cid );
    return db_decode($value);
}

/**
 * get the password of the given user.
 **/
function db_getPassword( $name ){
    $cid = db_openDB();
    $res = mysql_query( "SELECT password FROM users WHERE name='$name';", $cid );
    if( mysql_num_rows( $res ) < 1 ) return "";
    $ret = mysql_fetch_array( $res );
    mysql_close( $cid );
    return $ret[0];
}

function db_addUser( $name, $pass ){
    $cid = db_openDB();
    $res = mysql_query( "SELECT password FROM users WHERE name='$name';", $cid  );
    if( mysql_num_rows( $res ) < 1 )
      $res = mysql_query( "INSERT INTO users ( name, password ) VALUES ( '$name', '$pass' );", $cid );
    mysql_close( $cid );
}

function db_updateUser( $name, $pass ){
    $cid = db_openDB();
    $res = mysql_query( "UPDATE users SET password='$pass' WHERE name='$name';", $cid );
    mysql_close( $cid );
}

/**
 * deletes an user together will all his categories
 * and links.
 **/
function db_deleteUser( $user ){
    $cid = db_openDB();
    mysql_query( "BEGIN TRANSACTION;", $cid );

    $res = mysql_query( "SELECT cat FROM cats WHERE name='$user';", $cid );
    $rows = mysql_num_rows( $res );
    $cats = array();
    for( $i=0; $i < $rows; $i++ ){
	$val = mysql_fetch_array( $res );
	$cats[ $i ] = $val[0];
    }
    foreach( $cats as $cat ){
		$catid = db_getCatID( $cat, $cid );
		$res = mysql_query( "DELETE FROM links WHERE ( cid=$catid );", $cid );
		$res = mysql_query( "DELETE FROM cats WHERE ( name='$user' AND cat='$cat' );", $cid );
    }

    $res = mysql_query( "DELETE FROM users WHERE name='$user';", $cid );

    mysql_query( "COMMIT;", $cid );
    mysql_close( $cid );
}

/**
 * returns all users
 **/
function db_getUsers(){
    $cid = db_openDB();
    $res = mysql_query( "SELECT name FROM users;", $cid );
    $rows = mysql_num_rows( $res );
    $users = array();
    for( $i=0; $i < $rows; $i++ ){
	$val = mysql_fetch_array( $res );
	$users[ $i ] = $val[0];
    }
    mysql_close( $cid );
    return $users;
}

/*
 * returns an array containing all categories
 */
function db_getCategories(){
    global $ebm_user;

    $cid = db_openDB();
    // Get the categories
    $res = mysql_query( "SELECT cat FROM cats WHERE name='$ebm_user';", $cid );
    $rows = mysql_num_rows( $res );
    $category = array();
    // $val = mysql_fetch_array( $res );
    for( $i=0; $i < $rows; $i++ ){
		$val = mysql_fetch_array( $res );
		$category[ $i ] = db_decode( $val[0] );
    }

    mysql_close( $cid );

    // Return them
    return $category;
}

function db_newCat( $cat ){
    global $ebm_user;
    $cat=db_encode($cat);
    $cid = db_openDB();
    $res = mysql_query( "SELECT count(*) FROM cats WHERE ( name='$ebm_user' AND cat='$cat' );", $cid );
    $val = mysql_fetch_array( $res );
    if( $val[0] == 0 ){
		$res = mysql_query( "SELECT MAX(cid) FROM cats;", $cid );
		$ret = mysql_fetch_array( $res );
		$val = $ret[0];
		if("$val" == "") $val = 0;
		else $val++;
		$res = mysql_query( "INSERT INTO cats ( name, cat, cid ) VALUES ( '$ebm_user', '$cat', $val );", $cid );
    }
    mysql_close( $cid );
}

function db_removeCat( $cat ){
    global $ebm_user;
    $cat = db_encode( $cat );
    $cid = db_openDB();
    $catid = db_getCatID( $cat, $cid );
    $res = mysql_query( "DELETE FROM links WHERE ( cid=$catid );", $cid );
    $res = mysql_query( "DELETE FROM cats WHERE ( name='$ebm_user' AND cat='$cat' );", $cid );
    mysql_close( $cid );
}

function db_renCat( $cat, $ncat ){
    global $ebm_user;
    $cat = db_encode( $cat );
    $ncat = db_encode( $ncat );
    $cid = db_openDB();
    $res = mysql_query( "UPDATE cats SET cat='$ncat' WHERE ( name='$ebm_user' and cat='$cat' );", $cid );
    mysql_close( $cid );
}

function db_getCatID( $cat, $cid ){
    global $ebm_user;
    $res = mysql_query( "SELECT cid FROM cats WHERE ( name='$ebm_user' AND cat='$cat' );", $cid );
    $catid = mysql_fetch_array( $res );
    return $catid[0];
}
/*
 * returns all entries of a category in the form
 * description<>link
 */
function db_getEntries( $cat ){
    $cid = db_openDB();
    $catid = db_getCatID( $cat, $cid );
    $res = mysql_query( "SELECT text, link FROM links WHERE cid=$catid ORDER BY text;", $cid );
    $entries=array();
    if($res) for( $rowid=0; $rowid < mysql_num_rows( $res ); $rowid++ ){
		$row=mysql_fetch_array( $res );
		$desc=db_decode( $row[0] );
		$entries[ $rowid ]="$desc<>$row[1]";
    }
    mysql_close( $cid );

    // Return them
    return $entries;
}

function db_searchEntries( $keyword, $name ){
    $cid = db_openDB();
//    $res = mysql_query( "SELECT text, link FROM links WHERE text LIKE '%$keyword%' AND cid IN (SELECT cid FROM cats WHERE name='$name') ORDER BY text;", $cid );
    $res = mysql_query( "SELECT links.text, links.link FROM links, cats WHERE links.text LIKE '%$keyword%' AND links.cid=cats.cid AND cats.name='$name' ORDER BY text;", $cid );
	 
    $entries=array();
    if($res) for( $rowid=0; $rowid < mysql_num_rows( $res ); $rowid++ ){
        $row=mysql_fetch_array( $res );
        $desc=db_decode( $row[0] );
        $entries[ $rowid ]="$desc<>$row[1]";
    }
    mysql_close( $cid );
    
    return $entries;
}

/**
 * takes an addslashed text (like we get from a a'post'
 * and turns it into something appropriate for the
 * database
 **/
function db_encode( $text ){
    return mysql_escape_string( $text );
}

/**
 * takes a database encoded string and returns is into
 * standard text format.
 **/
function db_decode( $text ){
    return stripslashes( $text );
}

/**
 * append an entry
 **/
function db_appendEntry($cat, $link, $desc){
    $desc = db_encode( $desc );
    $cid = db_openDB();
    $catid = db_getCatID( $cat, $cid );
    $res = mysql_query( "SELECT cid FROM links WHERE ( cid=$catid AND link='$link' AND text='$desc' );", $cid );
    if( mysql_num_rows($res) < 1 ){
		$res = mysql_query( "INSERT INTO links ( cid, link, text ) VALUES ( $catid, '$link', '$desc' );", $cid );
		if(!$res){
	    	echo "INSERT INTO links ( cid, link, text ) VALUES ( $catid, '$link', '$desc' );<br>\n";
    	}
    }
    mysql_close( $cid );
}

/**
 * delete an entry
 **/
function db_removeEntry($cat, $link, $desc){
    $desc = db_encode( $desc );
    $cid = db_openDB();
    $catid = db_getCatID( $cat, $cid );
    $res = mysql_query( "DELETE FROM links WHERE ( cid=$catid AND link='$link' AND text='$desc' );", $cid );
    mysql_close( $cid );
}

/**
 * get an entry by description
 **/
function db_getLink($cat, $desc){
    $desc=db_encode( $desc );
    $cid=db_openDB();
    $catid=db_getCatID( $cat, $cid );
    $res = mysql_query( "SELECT link FROM links WHERE ( cid=$catid AND text='$desc' );", $cid);
    if( (!$res) || (mysql_num_rows( $res ) < 1 ) ) return "";
    $row=mysql_fetch_array( $res );
    return db_decode( $row[0] );
}

/**
 * change an existing entry into a new one.
 **/
function db_updateEntry($cat, $olink, $odesc, $nlink, $ndesc){
    $cid = db_openDB();
    $catid = db_getCatID( $cat, $cid );
    $res = mysql_query( "UPDATE links SET link='$nlink',text='$ndesc' WHERE ( cid=$catid AND link='$olink' AND text='$odesc' );", $cid );
    mysql_close($cid);
}

/**
 * Move an entry from one category to another
 **/
function db_moveEntry($source, $link, $desc, $target){
    $cid = db_openDB();
    $scatid = db_getCatID( $source, $cid );
    $dcatid = db_getCatID( $target, $cid );
    $res = mysql_query( "UPDATE links SET cid=$dcatid WHERE ( cid=$scatid AND link='$link' AND text='$desc' );", $cid );
    mysql_close($cid);
}

?>
