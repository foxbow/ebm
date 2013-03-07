<?PHP
$version="3.0beta2";

if(!file_exists("settings.php")){
     require("header.php");
     echo "<h1>Configuration missing!</h2>\n";
     echo "<p>Copy <b>config.ini</b> to <b>config.php</b>, set the desired database\n";
     echo "and the needed parameters and try again.</p>\n";
     echo "<p>If you chose to use <i>SQLite</i> or <i>Plain Text</i> as database you\n";
     echo "only need to set the <b>\$prefix</b> variable to the directory containing\n";
     echo "the date. For <i>SQLite</i> the default <b>\".\"</b> should bear no problems.\n";
     echo "Other Databases probably need initialization, see <b>db/init_postgres.sh</b>\n";
     echo "as an example.</p>\n";
     echo "<p>This has only to be done once. Even after an update the settings\n";
     echo "will be preserved.</p>\n";
     require("footer.php");
     exit;
}

/* 
 * Load the database connector 
*/
require_once( "PDO.php" );

// Get the values that can't be overridden by user
// uservalues are in setter.php
if (!isset( $settings_loaded ) || ( $settings_loaded == "off" ) ) {
    $days          = getSetting("days", "60", "ebm" );
    $forcelogin    = getSetting("forcelogin", "off", "ebm" );
    $title         = getSetting("title",  "Easybookmarks", "ebm" );
    $publicadd     = getSetting("publicadd", "off", "ebm" );
    $contact       = getSetting("contact", "", "ebm" );
    $motd          = getSetting("motd", "", "ebm" );
}

	extract($_REQUEST, EXTR_PREFIX_ALL|EXTR_REFS, 'ebm');
//import_request_variables("gp", "ebm_");
error_reporting( E_ALL );

// Find out where we are located
$uripath=$_SERVER['PHP_SELF'];
$uripath=substr($uripath, 0, strrpos($uripath, '/'));
$ebmurl="http://".$_SERVER['SERVER_NAME'].$uripath;

/**
 * checks the credentials of the user that's stored in the current
 * cookie. If user and password are valid, the username is returned.
 * If no user is valid and $login is set to true, the user will be
 * forced to login, otherwise an empty string is returned.
 */
function currentUser($login){
    global $forcelogin, $ebm_user, $ebm_pass, $rssforce;
    $loguser="";
    $password="";
    if (isset($_COOKIE["user"]) || ($rssforce != "") ) {
	if ($rssforce!=""){
	    $loguser=$ebm_user;
	    $password=md5(chop($ebm_pass));
	}else{
	    $loguser = $_COOKIE["user"];
	    $password = $_COOKIE["pass"];
	}
	if( checkpass( $loguser, $password )!=1 ){
	    $loguser="";
	    if($login === true){
		//      header("HTTP/1.0 301");
		header("Location: login.php");
		echo "<a href=\"login.php\">Cookie failure - Please log in again!</a>\n";
		exit;
	    }
	}
    }else{
	$loguser = "";
	if(($forcelogin=="on") && ($login === true)){
	    header("Location: login.php");
	    echo "<a href=\"login.php\">Please log in first!</a>\n";
	    exit;
	}
    }
    return $loguser;
}

function getSetting( $name, $value, $uname ){
     if ($uname=="")
        return db_getSetting( $name, $value, "ebm" );
     else
        return db_getSetting( $name, $value, $uname );
}

function setSetting( $name, $value, $uname ){
     if($uname=="")
        return db_setSetting( $name, $value, "ebm" );
     else
        return db_setSetting( $name, $value, $uname );
}

/**
 * Helperfunction to find out the set title of a given page.
 * Parses the source for a <title>...</title> pair
 **/
function getTitle( $link ){
     $fp = fopen( $link, 'r');
     $line = "";

     if( $fp ){
          while (! feof ($fp)){
                $line .= fgets ($fp, 1024);
                if (stristr($line, '</title>' )){
                     break;
                }
          }

          if(feof($fp)){
                $title = "No title tag!";
          }else if (preg_match("#<title>(.*)</title>#", $line, $out)) {
                // Get rid of newlines in the title as they will break
                // the entry in the pt database!
                $title = trim(strtr($out[1], "\n", " "));
          }else{
                $title = "No title set between title tags!";
          }

          fclose( $fp );
     }else{
          $title = "! $link unreachable !";
     }

     return $title;
}

function validate( $link ){
     $message="";
     $errno=0;
     $errstr="";
     $details=parse_url( $link );

     if (!isset($details['port']) || ($details['port']=="")){
          $details['port']="80";
     }
    // Make PHP ignore errors as we handle them ourselves
     $fp=@fsockopen($details['host'],$details['port'],$errno,$errstr,10);
     if ($fp === false){
	  $ans="ERROR -1 Host Not Found";
     }else{
          if ( (!isset($details['path'])) || ($details['path']=="") )
	       $details['path']="/";
          fputs($fp,"GET ".$details['path']." HTTP/1.0\n");
	  fputs($fp,"host: ".$details['host']."\n\n");
          $ans=fgets($fp,128);
          fclose($fp);
     }
/*
     $answer=explode(" ",$ans);
          if (($answer[1] != "200") and ($answer[1] != "302")) {
                $i=$answer[1];
                $message = "<font color=RED>[$i] $resultcode[$i]</font>";
          }
 */
    return strchr($ans, " ");
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

/**
 * returns all categories
 **/
function getCategories(){
  return db_getCategories();
}

/**
 * returns all entries of a category
 **/
function getEntries($category){
    return db_getEntries($category);
}

/*
 * returns all entries matching the given keyword
 */
function searchEntries($keyword, $name){
    return db_searchEntries($keyword, $name);
}

/*
 * The commands to handle the lists
 *
 * For standard Entries:
 * $file - target category
 * $line - description
 * $link - URL
 */
function append($cat, $link, $desc){
     if($link != ""){
          // Make sure that we have a protocol
          if( substr_count($link, "://") == 0){
                $link="http://$link";
          }

          // Set the right Title or scan it from the site
          if("$desc" != ""){
                $desc=makedesc("$desc");
          }else{
                $desc=makedesc(getTitle( $link ));
          }

          // newCat( $cat );
          // Save the new entry
          db_appendEntry($cat, $link, $desc);
     }

     return "$desc";
}

function renCat( $ocat, $ncat ){
    if(($ocat != "") && ($ncat != "")){
        $ncat=ucfirst("$ncat");
        db_renCat( $ocat, $ncat );
        return $ncat;
    }
}

function newCat( $cat ){
     if($cat != ""){
          $cat=ucfirst("$cat");
          db_newCat( $cat );
     }
}

function removeCat( $cat ){
     if( "$cat" != "" ){
          $cat = ucfirst("$cat");
          db_removeCat( $cat );
     }
}

// Removal of entries
function remove($cat, $link, $desc){
     // $desc=makedesc($desc);
     db_removeEntry( $cat, $link, $desc);
}

function removebyname( $cat, $desc ){
    $link=db_getLink( $cat, $desc );
    remove( $cat, $link, $desc );
}

function movebyname($source, $desc, $target){
    $link=db_getLink($source, $desc);
    if($link!="") db_moveEntry($source, $link, $desc, $target);
}

// Moving of entries
function move($source, $link, $desc, $target){
     db_moveEntry($source, $link, $desc, $target);
}

// Renaming an entry
function update($cat, $olink, $odesc, $nlink, $ndesc){
     $ndesc = makedesc( $ndesc );
     db_updateEntry($cat, $olink, $odesc, $nlink, $ndesc );
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
