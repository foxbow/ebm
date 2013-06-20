<?PHP
require_once( "settings.php" );

$ebm_database="$ebm_prefix";

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
 * @todo: does this really work on any DB?
 **/
function db_cleanup(){
    $cid = db_openDB();
    $res = $cid->exec( "VACUUM;" );
}

/**
 * return a value to a given setting.
 * 1. try the global settings
 * 2. try local settings
 * 3. return result ( $def if $name is not set at all )
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
    $cid = db_openDB();
	$cid->beginTransaction();
    $res = $cid->prepare( "INSERT OR REPLACE INTO settings VALUES (?,?,?);" );
	if( (false === $res ) || ( false === $res->execute( array( $name, $value, $uname ) ) ) ) {
    	echo( "INSERT OR REPLACE INTO settings VALUES ('$name','$value','$uname');<br>");
      	print_r( $cid->errorInfo() );
		$cid->rollback();
	 } else {
    	$cid->commit();
    }
    return $value;
}

/**
 * get the password of the given user.
 **/
function db_getPassword( $name ){
    $cid = db_openDB();
    $res = $cid->prepare( "SELECT password FROM users WHERE name=?;" );
	if( (false === $res ) || ( false === $res->execute( array( $name ) ) ) ) {
    	echo( "SELECT password FROM users WHERE name='$name';<br>");
      	print_r( $cid->errorInfo() );
		return "";
	}

	$ret = $res->fetchAll();
	if( empty( $ret ) ) return "";
	return $ret['0']['password'];
}

/**
 * Add a new user
 * @todo: PDO!
 */
function db_addUser( $name, $pass ){
    $cid = db_openDB();
    $res = sqlite_query( "SELECT password FROM users WHERE name='$name';", $cid  );
    if( sqlite_num_rows( $res ) < 1 )
      $res = sqlite_query( "INSERT INTO users ( name, password ) VALUES ( '$name', '$pass' );", $cid );
    sqlite_close( $cid );
}

/**
 * Set new password
 * @todo: PDO!
 **/
function db_updateUser( $name, $pass ){
    $cid = db_openDB();
    $res = sqlite_query( "UPDATE users SET password='$pass' WHERE name='$name';", $cid );
    sqlite_close( $cid );
}

/**
 * deletes an user together will all his categories
 * and links.
 * @todo: PDO!
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
 * @todo: error handling.
 **/
function db_getUsers(){
    $cid = db_openDB();
    // Get the users
    $res = $cid->query( "SELECT name FROM users;" );
    $users = array();
    $i = 0;
    foreach( $res->fetchAll() as $row ) {
		$users[ $i ] = $row['name'];
      	$i++;
	}
    // Return them
    return $users;
}

/*
 * returns an array containing all categories
 * @todo: error handling
 */
function db_getCategories(){
    global $ebm_user;

    $cid = db_openDB();
    // Get the categories
    $res = $cid->query( "SELECT cat FROM cats WHERE name='$ebm_user';" );
    $category = array();
    $i = 0;
    foreach( $res->fetchAll() as $row ) {
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
    $cid->beginTransaction();
	$res = $cid->query( "SELECT MAX(cid) FROM cats;" );
	$val = 0;
	while ($row = $res->fetch(PDO::FETCH_NUM)) {
		$val = $row[0];
	}
	$val=$val+1;
	$res = $cid->prepare( "INSERT OR IGNORE INTO cats ( name, cat, cid ) VALUES ( ?, ?, ? );" );
	if( (false === $res ) || ( false === $res->execute( array( $ebm_user, $cat, $val ) ) ) ) {
   		echo "db_appendEntry: INSERT OR IGNORE INTO cats ( name, cat, cid ) VALUES ( '$ebm_user', '$cat', $val );<br>\n";
   		print_r( $cid->errorInfo() );
   		$cid->rollback();
	} else
	$cid->commit();
}

/**
 * deletes a category
 * @todo: join
 **/
function db_removeCat( $cat ){
    global $ebm_user;
    $cid = db_openDB();
    $catid = db_getCatID( $cat, $cid );
    $cid->beginTransaction();
	$res = $cid->prepare( "DELETE FROM links WHERE ( cid=? );" );
	if( (false === $res ) || ( false ===  $res->execute( array( $catid ) ) ) ) {
   		echo "db_removeCat: DELETE FROM links WHERE ( cid='$catid' );<br>\n";
   		print_r( $cid->errorInfo() );
   		$cid->rollback();
	} else {
		$res = $cid->prepare( "DELETE FROM cats WHERE ( name=? AND cat=? );" );
		if( (false === $res ) || ( false === $res->execute( array( $ebm_user, $cat ) ) ) ) {
	   		echo "db_removeCat: DELETE FROM cats WHERE ( name='$ebm_user' AND cat='$cat' );<br>\n";
	   		print_r( $cid->errorInfo() );
	   		$cid->rollback();
		} else
		$cid->commit();
	}
}

/**
 * rename a category
 **/
function db_renCat( $cat, $ncat ){
    global $ebm_user;
    $cid = db_openDB();
    $cid->beginTransaction();
	$res = $cid->prepare( "UPDATE cats SET cat=? WHERE ( name=? and cat=? );" );
	if( (false === $res ) || ( false === $res->execute( array( $ncat, $ebm_user, $cat ) ) ) ) {
   		echo "db_renCat: UPDATE cats SET cat='$ncat' WHERE ( name='$ebm_user' and cat='$cat' );<br>\n";
   		print_r( $cid->errorInfo() );
   		$cid->rollback();
	} else
	$cid->commit();
}

/**
 * returnd the id for a given category name
 * @todo: should be done with a JOIN instead..
 */
function db_getCatID( $cat, $cid ){
    global $ebm_user;
	$res = $cid->prepare( "SELECT cid FROM cats WHERE ( name=? AND cat=? );" );
	if( (false === $res ) || ( false === $res->execute( array( $ebm_user, $cat ) ) ) ) {
      	print_r( $cid->errorInfo() );
		echo "db_getCatID: SELECT cid FROM cats WHERE ( name='$ebm_user' AND cat=$cat );<br>";
		return -1;
    }
    $catid = $res->fetchAll();
    return $catid[0]['cid'];
}

/**
 * returns all entries of a category
 **/
function db_getEntries( $cat ){
    $cid = db_openDB();
    $catid = db_getCatID( $cat, $cid );
    $entries=array();
    if(empty($catid)) return $entries;
    $res = $cid->prepare( "SELECT text, link FROM links WHERE cid=? ORDER BY text;" );
    $res->execute( array( $catid ) );
    $rowid=0;
    foreach( $res->fetchall() as $row ) {
        $desc=$row['text'];
		$link=$row['link'];
        $entries[ $rowid ][ 'desc' ]=$desc;
		$entries[ $rowid ][ 'link' ]=$link;
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
    $cid = db_openDB();
	$keyword="%$keyword%";
    $res = $cid->prepare( "SELECT text, link FROM links WHERE text LIKE ? AND cid IN (SELECT cid FROM cats WHERE name=?) ORDER BY text;" );
    $res->execute( array( $keyword, $name ) );
    $rowid=0;
    foreach( $res->fetchall() as $row ) {
        $desc=$row['text'];
		$link=$row['link'];
        $entries[ $rowid ][ 'desc' ]=$desc;
		$entries[ $rowid ][ 'link' ]=$link;
        $rowid++;
    }
    return $entries;
}

/**
 * append an entry
 **/
function db_appendEntry($cat, $link, $desc){
    $cid = db_openDB();
    $cid->beginTransaction();
    $catid = db_getCatID( $cat, $cid );
	$res = $cid->prepare( "INSERT OR IGNORE INTO links ( cid, link, text ) VALUES ( ?, ?, ? );" );
	if( (false === $res ) || ( false === $res->execute( array( $catid, $link, $desc ) ) ) ) {
   		echo "db_appendEntry: INSERT INTO links ( cid, link, text ) VALUES ( $catid, '$link', '$desc' );<br>\n";
   		print_r( $cid->errorInfo() );
   		$cid->rollback();
	} else
	$cid->commit();
}

/**
 * get an entry by description
 *
 * @todo: error handling!
 **/
function db_getLink($cat, $desc){
    $cid=db_openDB();
    $catid=db_getCatID( $cat, $cid );
    $res = $cid->prepare( "SELECT link FROM links WHERE ( cid=? AND text=? );" );
	$res->execute( array( $catid, $desc ) );
	$row = $res->fetchAll();
	if( empty( $row ) ) return "";
    return $row[0]['link'];
}

/**
 * delete an entry
 **/
function db_removeEntry($cat, $link, $desc){
    $cid = db_openDB();
	$cid->beginTransaction();
    $catid = db_getCatID( $cat, $cid );
    $res = $cid->prepare( "DELETE FROM links WHERE ( cid=? AND link=? AND text=? );" );
	if( (false === $res ) || ( false === $res->execute( array( $catid, $link, $desc ) ) ) ) {
    	echo( "DELETE FROM links WHERE ( cid=$catid AND link='$link' AND text='$desc' );<br>");
      	print_r( $cid->errorInfo() );
		$cid->rollback();
	 } else {
    	$cid->commit();
    }
}

/**
 * change an existing entry into a new one.
 **/
function db_updateEntry($cat, $olink, $odesc, $nlink, $ndesc){
    // Update the entry.
    $cid = db_openDB();
    $catid = db_getCatID( $cat, $cid );
	$cid->beginTransaction();
    $res = $cid->prepare( "UPDATE OR IGNORE links SET link=?,text=? WHERE ( cid=? AND link=? AND text=? );" );
	if( (false === $res ) || ( false === $res->execute( array( $nlink, $ndesc, $catid, $olink, $odesc ) ) ) ) {
	 	echo( "UPDATE links SET link='$nlink',text='$ndesc' WHERE ( cid=$catid AND link='$olink' AND text='$odesc' );<br>");
      	print_r( $cid->errorInfo() );
		$cid->rollback();
	} else {
    	$cid->commit();
    }
}

/**
 * Move an entry from one category to another
 **/
function db_moveEntry($source, $link, $desc, $target){
    $cid = db_openDB();
	$cid->beginTransaction();
    $scatid = db_getCatID( $source, $cid );
    $tcatid = db_getCatID( $target, $cid );
    $res = $cid->prepare( "UPDATE OR IGNORE links SET cid=? WHERE ( cid=? AND link=? AND text=? );" );
	if( (false === $res ) || ( false === $res->execute( array( $tcatid, $scatid, $link, $desc ) ) ) ) {
    	echo( "UPDATE OR IGNORE links SET cid=$tcatid WHERE ( cid=$scatid AND link='$link' AND text='$desc' );<br>");
      	print_r( $cid->errorInfo() );
		$cid->rollback();
	 } else {
    	$cid->commit();
    }
}
?>
