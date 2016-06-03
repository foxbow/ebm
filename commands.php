<?PHP
$version="3.1a";
error_reporting( E_ALL );

if(!file_exists("settings.php")){
     require("header.php");
     echo "<h1>Configuration missing!</h2>\n";
     echo "<p>Copy <b>settings.ini</b> to <b>settings.php</b>, set the desired database\n";
     echo "and the needed parameters and try again.</p>\n";
     echo "<p>This has only to be done once. Even after an update the settings\n";
     echo "will be preserved.</p>\n";
     require("footer.php");
     exit;
}
require_once( "settings.php" );

// Get the values that can't be overridden by user
// uservalues are in setter.php
if (!isset( $settings_loaded ) || ( $settings_loaded == "off" ) ) {
    $days          = getSetting("days", "60", "ebm" );
    $forcelogin    = getSetting("forcelogin", "off", "ebm" );
    $title         = getSetting("title",  "Easybookmarks", "ebm" );
    $publicadd     = getSetting("publicadd", "off", "ebm" );
    $contact       = getSetting("contact", "", "ebm" );
    $motd          = getSetting("motd", "", "ebm" );
	$https		   = getSetting("https", "off", "ebm");
}

// extract($_REQUEST, EXTR_PREFIX_ALL|EXTR_REFS, 'ebm');
extract($_GET, EXTR_PREFIX_ALL|EXTR_REFS, 'ebm');
extract($_POST, EXTR_PREFIX_ALL|EXTR_REFS, 'ebm');
if( !isset( $ebm_user ) ) $ebm_user="PUBLIC";

// Find out where we are located
$uripath=$_SERVER['PHP_SELF'];

$uripath=substr($uripath, 0, strrpos($uripath, '/'));
if( $https == "off" ) {
	$ebmurl="http://".$_SERVER['SERVER_NAME'].$uripath;
} else {
	$ebmurl="https://".$_SERVER['SERVER_NAME'].$uripath;
}

/**
 * SQL wrapper to do error checking and transactions automatically
 * Will always return the result set.
 **/
function db_exec( $SQL, $param=array() ){
	$cid=db_openDB();
	$cid->beginTransaction();
    $res = $cid->prepare( $SQL );
	if( (false === $res ) || ( false === $res->execute( $param ) ) ) {
    	echo( "'$SQL' failed!<br>\n");
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
    global $db_name, $cid;
	if( ! isset( $cid ) )
	    $cid = new PDO( $db_name );
    if($cid === false) nodb();
    return $cid;
}

/**
 * run the database internal cleanup
 * does this really work on any DB?
 **/
function dbcleanup(){
	$cid=db_openDB();
    $res = $cid->prepare( "VACUUM;" );
	if( (false === $res ) || ( false === $res->execute() ) ) {
    	echo( "'VACUUM;' failed!<br>\n");
      	print_r( $cid->errorInfo() );
	 }
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
function addUser( $name, $pass ){
	db_exec( "INSERT OR IGNORE INTO users ( name, password ) VALUES ( ?, ? );", array( $name, $pass ) );
}

/**
 * Set new password
 **/
function updateUser( $name, $pass ){
	db_exec( "UPDATE users SET password=? WHERE name=?;", array( $pass, $name ) );
}

/**
 * deletes an user together will all his categories
 * and links.
 **/
function deleteUser( $user ){
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
function getUsers(){
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
function getCategories( $user ){
	$ret=db_exec( "SELECT cat FROM cats WHERE name=?;", array( $user ) );
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
function db_newCat( $cat, $user ){
	$val = 0;
	$res = db_exec( "SELECT MAX(cid) FROM cats;" );
	if( !empty( $res ) ) $val=$res[0][0];
	$val=$val+1;
	db_exec( "INSERT OR IGNORE INTO cats ( name, cat, cid ) VALUES ( ?, ?, ? );",
		array( $user, $cat, $val ) );
}

/**
 * deletes a category
 **/
function db_removeCat( $cat, $user ){
    $catid = db_getCatID( $cat, $user );
	db_exec( "DELETE FROM links WHERE ( cid=? );", array( $catid ) );
	db_exec( "DELETE FROM cats WHERE ( cid=? );", array( $catid ) );
}

/**
 * rename a category
 **/
function db_renCat( $cat, $ncat, $user ){
	db_exec( "UPDATE cats SET cat=? WHERE ( name=? and cat=? );",
		array( $ncat, $user, $cat ) );
}

/**
 * returns the id for a given category name
 * $user is mandatory in case public links are required
 */
function db_getCatID( $cat, $user ){
	$catid=db_exec( "SELECT cid FROM cats WHERE ( name=? AND cat=? );", array( $user, $cat ) );
	if( empty( $catid ) ) return -1;
    else return $catid[0]['cid'];
}

/**
 * returns the category name to a given ID
 * catids are global, so no user is needed!
 */
function db_getCatName( $cat ){
	$res = db_exec( "SELECT cat FROM cats WHERE ( cid=? );", array( $cat ) );
    if( empty( $res) ) return "UNKNOWN";
	else return $res[0]['cat'];
}

/**
 * returns all entries of a category
 **/
function getEntries( $cat, $user ){
    $catid = db_getCatID( $cat, $user );
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
function searchEntries( $keyword, $name ){
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
function db_appendEntry($cat, $link, $desc, $user ){
    $catid = db_getCatID( $cat, $user );
	db_exec( "INSERT OR IGNORE INTO links ( cid, link, text ) VALUES ( ?, ?, ? );", 
		array( $catid, $link, $desc ) );
}

/**
 * get an entry by description
 **/
function db_getLink($cat, $desc, $user ){
    $catid = db_getCatID( $cat, $user );
	$row=db_exec( "SELECT link FROM links WHERE ( cid=? AND text=? );", 
		array( $catid, $desc ) );
	if( empty( $row ) ) return "";
    return $row[0]['link'];
}

/**
 * delete an entry
 **/
function db_removeEntry($cat, $link, $desc, $user ){
    $catid = db_getCatID( $cat, $user );
	db_exec( "DELETE FROM links WHERE ( cid=? AND link=? AND text=? );", 
		array( $catid, $link, $desc ) );
}

/**
 * change an existing entry into a new one.
 **/
function db_updateEntry($cat, $olink, $odesc, $nlink, $ndesc, $user ){
    $catid = db_getCatID( $cat, $user );
	db_exec( "UPDATE OR IGNORE links SET link=?,text=? WHERE ( cid=? AND link=? AND text=? );", 
		array( $nlink, $ndesc, $catid, $olink, $odesc ) );
}

/**
 * Move an entry from one category to another
 **/
function move($source, $link, $desc, $target, $user ){
    $scatid = db_getCatID( $source, $user );
    $tcatid = db_getCatID( $target, $user );
    db_exec( "UPDATE OR IGNORE links SET cid=? WHERE ( cid=? AND link=? AND text=? );", 
		array( $tcatid, $scatid, $link, $desc ) );
}

/**
 * checks the credentials of the user that's stored in the current
 * cookie. If user and password are valid, the username is returned.
 * If no user is valid and $login is set to true, the user will be
 * forced to login, otherwise an empty string is returned.
 */
function currentUser( $login )
{
    global $forcelogin, $ebm_user;
    $user="PUBLIC";
    $password="";

    if( isset($_COOKIE["user"]) ) 
	{
	    $user = $_COOKIE["user"];
	    $password = $_COOKIE["pass"];
		if( checkpass( $user, $password )!=1 ){
		    if($login === true){
				header("Location: login.php?user=$user");
				echo "<a href=\"login.php?user=$user\">Cookie failure - Please log in again!</a>\n";
				exit;
		    } else {
				$user="PUBLIC";
			}
		}
    }else{
/*
		if( ( $ebm_user != "PUBLIC" ) && ( ($forcelogin=="on") || ($login === true) ) ){
		    header("Location: login.php");
		    echo "<a href=\"login.php\">Please log in first!</a>\n";
		    exit;
		}
*/
		if( ( $ebm_user != "PUBLIC" ) && ( $login === true ) ) {
		    header("Location: login.php?user=$ebm_user");
		    echo "<a href=\"login.php?user=$ebm_user\">Please log in as $ebm_user first!</a>\n";
		    exit;
		}
    }

    return $user;
}

/**
 * return a value to a given setting.
 * 1. try the global settings
 * 2. try local settings
 * 3. return result ( $def if $name is not set at all )
 *
 * Not using db_exec as this one recycles the prepared statement
 **/
function getSetting( $name, $def, $uname ){
    if ( $uname=="PUBLIC" ) $uname="ebm";
    $cid = db_openDB();
    $res = $cid->prepare( "SELECT value FROM settings WHERE name=? AND uname=?;" );
	// db_getSetting is the very first access to the database. If this fails make sure 
	// the databese is available and retry.
    if( $res === false){
        db_initDB();
        return getSetting( $name, $def, $uname );
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
function setSetting( $name, $value, $uname ){
    if ($uname=="") $uname="ebm";
	db_exec( "INSERT OR REPLACE INTO settings VALUES (?,?,?);", array( $name, $value, $uname ) );
    return $value;
}

/**
 * Helperfunction to find out the set title of a given page.
 * Parses the source for a <title>...</title> pair
 **/
function getTitle( $link ){
	$fp = fopen( $link, 'r');

	$line = "";
	$title = "";	

	// Is title set explicitly?
	if( $fp ){
		while (! feof ($fp)){
			$line .= fgets ($fp, 1024);
			if (stristr($line, '</title>' )){
				if( preg_match( "#<title>(.*)</title>#", $line, $out ) ) {
					// Get rid of newlines in the title as they will break
					// the entry in the pt database!
					$title = trim(strtr($out[1], "\n", " "));
				}
				break;
			}
		}
		fclose( $fp );
	}

	if( $title == "" ) {
		// Get rid of the protocol (if any)
		$pre = strpos( $link, "://" );
		if( $pre !== false ) {
			$title = substr( $title, $pre+3 );
		}

		// get rid of www. (if any)
		$pre = strpos( $link, "www." );
		if( $pre !== false ) {
			$title = substr( $link, $pre+4 );
		}

		// No trailing slashes either
		if( $title[ strlen( $title )-1 ] == "/" ) {
			$title = substr( $title, 0, strlen( $title)-1 );

		}
	}
	return $title;
}

/**
 * checks if the given user and password are a valid pair.
 * By now the passwords are kept in a file and not in the
 * database yet. This will change soon.
 **/
function checkpass($user, $pass){
     $password = chop(db_getPassword( $user ));
     $pass = chop( $pass );

     if( "$password"=="" )      return 0;
     if( "$password"=="$pass" ) return 1;

     return -1;
}

/*
 * The commands to handle the lists
 *
 * For standard Entries:
 * $file - target category
 * $line - description
 * $link - URL
 * $user - the owner of the category, PUBLIC for public entries
 */
function append($cat, $link, $desc, $user ){
	if($link != ""){
		// Make sure that we have a protocol
		if( substr_count($link, "://") == 0){
			$link="http://$link";
		}

		// Looks like '+'s get lost in Chrome which is bad for Google+ links
		$link = str_replace(" ", "+", $link);

		// Set the right Title or scan it from the site
		if("$desc" != ""){
			$desc=makedesc("$desc");
		}else{
			$desc=makedesc(getTitle( $link ));
		}
		// Save the new entry
		db_appendEntry($cat, $link, $desc, $user);
	}

	return "$desc";
}

function renCat( $ocat, $ncat, $user ){
    if(($ocat != "") && ($ncat != "")){
        $ncat=ucfirst("$ncat");
        db_renCat( $ocat, $ncat, $user );
        return $ncat;
    }
}

function newCat( $cat, $user ){
     if($cat != ""){
          $cat=ucfirst("$cat");
          db_newCat( $cat, $user );
     }
}

function removeCat( $cat, $user ){
     if( "$cat" != "" ){
          $cat = ucfirst("$cat");
          db_removeCat( $cat, $user );
     }
}


function removebyname( $cat, $desc, $user ){
    $link=db_getLink( $cat, $desc, $user );
    db_removeEntry( $cat, $link, $desc, $user );
}

function movebyname($source, $desc, $target, $user){
    $link=db_getLink($source, $desc, $user);
    if($link!="") move($source, $link, $desc, $target, $user);
}

// Renaming an entry
function update($cat, $olink, $odesc, $nlink, $ndesc, $user){
     $ndesc = makedesc( $ndesc );
     db_updateEntry($cat, $olink, $odesc, $nlink, $ndesc, $user );
}

function nodb(){
     global $database;
     global $contact;

     require("header.php");
     echo "<h1>The $database Database is not available/not running!</h1>\n";
     echo "<h2>Please contact the admin.</h2>\n";
     require("footer.php");
     exit();
}

// Prepare descriptions for the database
// especially we want to get rid of &...; constructs as
// they will fsck up the database
function makedesc( $desc ){
	// A space is a space is a space...
	$desc = str_replace("&nbsp;", " ", $desc);
	// No blanks on the ends please
	$desc=trim($desc);
	// Only single quotes desired
	$desc = str_replace("\"", "'", $desc);
	// Get the translation
	$trans = get_html_translation_table(HTML_ENTITIES);
	$trans = array_flip($trans);
	// And translate
	$desc = strtr($desc, $trans);
	// Always start with a capital letter
	$desc = ucfirst( "$desc" );
	return $desc;
}
/*
 * Returns an array with all found *.css files
 */
function getCSSlist(){
     $this_dir = dir('./css');
     $result_array=array();
     while ($file = $this_dir->read()) {
          if (preg_match("#.css$#", $file)) {
                $result_array[] = $file;
          }
     }
     return $result_array;
}
?>
