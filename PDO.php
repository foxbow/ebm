<?PHP
require_once( "settings.php" );

$ebm_database="$ebm_prefix";

/**
 * SQL wrapper to do error checking and transactions automatically
 * Will always return the result set.
 **/
function db_exec( $SQL, $param=array() ){
	$cid=db_openDB();
	$cid->beginTransaction();
    $res = $cid->prepare( $SQL );
	if( (false === $res ) || ( false === $res->execute( $param ) ) ) {
    	echo( $SQL."<br>\n");
      	print_r( $cid->errorInfo() );
		$cid->rollback();
	 } else {
    	$cid->commit();
    }
	return $res->fetchAll();
}

/**
 * Initialize the basics and create the 'root' user called 'ebm'
 * with password 'ebm'
 **/
function db_initDB(){
    echo "<h1>Could not open db?!</h1>";
return;
    $cid = db_openDB();
	$cid->beginTransaction();
    $res = $cid->exec( "CREATE TABLE users ( name VARCHAR(16) UNIQUE, password CHAR(32) );" );
    $res = $cid->exec( "INSERT INTO users VALUES ( 'ebm', '3dc70661cd5ea000608c27661b5c240b' );" );
    $res = $cid->exec( "CREATE TABLE cats ( name VARCHAR(16), cat VARCHAR(32), cid INTEGER );" );
    $res = $cid->exec( "CREATE TABLE links ( cid INTEGER, link VARCHAR(256), text VARCHAR(128) );" );
    $res = $cid->exec( "CREATE TABLE settings ( name VARCHAR(16), value VARCHAR(32), uname VARCHAR(16), PRIMARY KEY (name, uname) );" );
    $cid->commit();
}

/**
 * open the database.
 * If it does not exist, try to create it or at least prompt the
 * user/admin to do that.
 **/
function db_openDB(){
    global $ebm_prefix, $cid;
	if( ! isset( $cid ) )
	    $cid = new PDO( $ebm_prefix );
    if($cid === false) nodb();
    return $cid;
}

/**
 * run the database internal cleanup
 * does this really work on any DB?
 **/
function db_cleanup(){
    db_exec( "VACUUM;" );
}

/**
 * return a value to a given setting.
 * 1. try the global settings
 * 2. try local settings
 * 3. return result ( $def if $name is not set at all )
 *
 * Not using db_exec as this one recycles the prepared statement
 **/
function db_getSetting( $name, $def, $uname ){
    $cid = db_openDB();
    $res = $cid->prepare( "SELECT value FROM settings WHERE name=? AND uname=?;" );
	// db_getSetting is the very first access to the database. If this fails make sure 
	// the databese is available and retry.
    if( $res === false){
        db_initDB();
        return db_getSetting( $name, $def, $uname );
    }

	// read the administrator set default
	$res->execute( array( $name, 'ebm' ) );
    $ret = $res->fetchAll();
	if( !empty( $ret ) )
	    $def = $ret[0]['value'];

	// read the user setting
    $res->execute( array( $name, $uname ) );
    $ret = $res->fetchAll();
    if( !empty( $ret ) )
        $def = $ret[0]['value'];

    return $def;
}

/**
 * set a certain value in the settings.
 * If it does not exist yet, create a new one.
 **/
function db_setSetting( $name, $value, $uname ){
	db_exec( "INSERT OR REPLACE INTO settings VALUES (?,?,?);", array( $name, $value, $uname ) );
    return $value;
}

/**
 * get the password of the given user.
 **/
function db_getPassword( $name ){
	$ret = db_exec( "SELECT password FROM users WHERE name=?;", array( $name ) );
	if( empty( $ret ) ) return "";
	return $ret['0']['password'];
}

/**
 * Add a new user
 * 
 * If the user exists it will not be overwritten
 */
function db_addUser( $name, $pass ){
	db_exec( "INSERT OR IGNORE INTO users ( name, password ) VALUES ( ?, ? );", array( $name, $pass ) );
}

/**
 * Set new password
 **/
function db_updateUser( $name, $pass ){
	db_exec( "UPDATE users SET password=? WHERE name=?;", array( $pass, $name ) );
}

/**
 * deletes an user together will all his categories
 * and links.
 **/
function db_deleteUser( $user ){
	$res = db_exec( "SELECT cid FROM cats WHERE name=?;", array( $user ) );
	foreach( $res as $cat ) {
		db_exec( "DELETE FROM links WHERE ( cid=? );", array( $cat['cid'] ) );
		db_exec( "DELETE FROM cats WHERE ( cid=? );", array( $cat['cid'] ) );
	}
	db_exec( "DELETE FROM users WHERE name=?;", $user );
}

/**
 * returns all users
 **/
function db_getUsers(){
	$res = db_exec(  "SELECT name FROM users;" );
    $users = array();
    $i = 0;
    foreach( $res as $row ) {
		$users[ $i ] = $row['name'];
      	$i++;
	}
    // Return them
    return $users;
}

/*
 * returns an array containing all categories
 */
function db_getCategories(){
    global $ebm_user;
	$ret=db_exec( "SELECT cat FROM cats WHERE name=?;", array( $ebm_user ) );
    $category = array();
    $i = 0;
    foreach( $ret as $row ) {
		$category[ $i ] = $row['cat'];
      	$i++;
	 }
    // Return them
    return $category;
}

/**
 * Creates a new category and sets a new catid
 **/
function db_newCat( $cat ){
    global $ebm_user;
    $cid = db_openDB();
	$res = $cid->query( "SELECT MAX(cid) FROM cats;" );
	$val = 0;
	while ($row = $res->fetch(PDO::FETCH_NUM)) {
		$val = $row[0];
	}
	$val=$val+1;
	db_exec( "INSERT OR IGNORE INTO cats ( name, cat, cid ) VALUES ( ?, ?, ? );",
		array( $ebm_user, $cat, $val ) );
}

/**
 * deletes a category
 **/
function db_removeCat( $cat ){
    global $ebm_user;
    $catid = db_getCatID( $cat );
	db_exec( "DELETE FROM links WHERE ( cid=? );", array( $catid ) );
	db_exec( "DELETE FROM cats WHERE ( cid=? );", array( $catid ) );
}

/**
 * rename a category
 **/
function db_renCat( $cat, $ncat ){
    global $ebm_user;
	db_exec( "UPDATE cats SET cat=? WHERE ( name=? and cat=? );",
		array( $ncat, $ebm_user, $cat ) );
}

/**
 * returns the id for a given category name
 */
function db_getCatID( $cat ){
    global $ebm_user;	
	$catid=db_exec( "SELECT cid FROM cats WHERE ( name=? AND cat=? );", array( $ebm_user, $cat ) );
    return $catid[0]['cid'];
}

/**
 * returns the category name to a given ID
 */
function db_getCatName( $cat ){
    global $ebm_user;
	$res = db_exec( "SELECT cat FROM cats WHERE ( cid=? );", array( $cat ) );
    return $res[0]['cat'];
}

/**
 * returns all entries of a category
 **/
function db_getEntries( $cat ){
    $catid = db_getCatID( $cat );
    $entries=array();
    if(empty($catid)) return $entries;
    $res = db_exec( "SELECT text, link FROM links WHERE cid=? ORDER BY text;", array( $catid ) );
    $rowid=0;
    foreach( $res as $row ) {
        $entries[ $rowid ][ 'desc' ]=$row['text'];
		$entries[ $rowid ][ 'link' ]=$row['link'];
        $rowid++;
    }
    return $entries;
}

/**
 * search for a keyword
 **/
function db_searchEntries( $keyword, $name ){
    $entries=array();
	// Do not search for ALL entries.
	if( $keyword == "" ) return $entries;
	$keyword="%$keyword%";
    $res = db_exec( "SELECT cid, text, link FROM links WHERE text LIKE ? AND cid IN (SELECT cid FROM cats WHERE name=?) ORDER BY cid, text;", array( $keyword, $name ) );
    $rowid=0;
    foreach( $res as $row ) {
		$entries[ $rowid ][ 'cat' ]=db_getCatName( $row['cid'] );
        $entries[ $rowid ][ 'desc' ]=$row['text'];
		$entries[ $rowid ][ 'link' ]=$row['link'];
        $rowid++;
    }
    return $entries;
}

/**
 * append an entry
 **/
function db_appendEntry($cat, $link, $desc){
    $catid = db_getCatID( $cat );
	db_exec( "INSERT OR IGNORE INTO links ( cid, link, text ) VALUES ( ?, ?, ? );", 
		array( $catid, $link, $desc ) );
}

/**
 * get an entry by description
 **/
function db_getLink($cat, $desc){
    $catid = db_getCatID( $cat );
	$row=db_exec( "SELECT link FROM links WHERE ( cid=? AND text=? );", 
		array( $catid, $desc ) );
	if( empty( $row ) ) return "";
    return $row[0]['link'];
}

/**
 * delete an entry
 **/
function db_removeEntry($cat, $link, $desc){
    $catid = db_getCatID( $cat );
	db_exec( "DELETE FROM links WHERE ( cid=? AND link=? AND text=? );", 
		array( $catid, $link, $desc ) );
}

/**
 * change an existing entry into a new one.
 **/
function db_updateEntry($cat, $olink, $odesc, $nlink, $ndesc){
    $catid = db_getCatID( $cat );
	db_exec( "UPDATE OR IGNORE links SET link=?,text=? WHERE ( cid=? AND link=? AND text=? );", 
		array( $nlink, $ndesc, $catid, $olink, $odesc ) );
}

/**
 * Move an entry from one category to another
 **/
function db_moveEntry($source, $link, $desc, $target){
    $scatid = db_getCatID( $source );
    $tcatid = db_getCatID( $target );
    db_exec( "UPDATE OR IGNORE links SET cid=? WHERE ( cid=? AND link=? AND text=? );", 
		array( $tcatid, $scatid, $link, $desc ) );
}
?>
