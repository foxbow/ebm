<?php
require("commands.php");

$loguser=currentUser(true);
if($loguser=="PUBLIC"){
    header ("Location: docs.html");
    exit;
}

require("setter.php");

if( !isset($ebm_cmd) ) $ebm_cmd="";

if( $ebm_cmd == "set" ){
    $settings_loaded="off";

    if($loguser=="ebm"){
	if( $days != $ebm_days )
	  $days = setSetting( "days", $ebm_days, $loguser );
	if( "$title" != "$ebm_title" )
	  $title = setSetting( "title", $ebm_title, $loguser );
	if( "$motd" != "$ebm_motd" )
	  $motd = setSetting( "motd", $ebm_motd, $loguser );
	if( $contact != $ebm_contact )
	  $contact = setSetting( "contact", $ebm_contact, $loguser );
	if( !isset( $ebm_https ) ) $ebm_https="off";
	if( $https != $ebm_https )
	  $https = setSetting( "https", $ebm_https, $loguser );
	if( !isset( $ebm_forcelogin ) ) $ebm_forcelogin="off";
	if( $forcelogin != $ebm_forcelogin )
	  $forcelogin = setSetting( "forcelogin", $ebm_forcelogin, $loguser );
	if( !isset( $ebm_publicadd ) ) $ebm_publicadd="off";
	if( $publicadd != $ebm_publicadd )
	  $publicadd = setSetting( "publicadd", $ebm_publicadd, $loguser );
    }

    if( !isset( $ebm_defcat ) ) $ebm_defcat="";
    if( $defcat != $ebm_defcat )
      $defcat = setSetting( "defcat", $ebm_defcat, $loguser );
    if( !isset( $ebm_killbutton ) ) $ebm_killbutton="off";
    if( $killbutton != $ebm_killbutton )
      $killbutton = setSetting( "killbutton", $ebm_killbutton, $loguser );
    if( !isset( $ebm_newwin ) ) $ebm_newwin="off";
    if( $newwin != $ebm_newwin )
      $newwin = setSetting( "newwin", $ebm_newwin, $loguser );
    if( !isset( $ebm_quicksearch ) ) $ebm_quicksearch="off";
    if( $quicksearch != $ebm_quicksearch )
      $quicksearch = setSetting( "quicksearch", $ebm_quicksearch, $loguser );
    if( !isset( $ebm_cssfile ) ) $ebm_cssfile = "ebm.css";
    if( $cssfile != $ebm_cssfile )
      $cssfile = setSetting( "cssfile", $ebm_cssfile, $loguser );
    if( !isset( $ebm_toedit ) ) $ebm_toedit = "off";
    if( $toedit != $ebm_toedit )
      $toedit = setSetting( "toedit", $ebm_toedit, $loguser );
    if( !isset( $ebm_showval ) ) $ebm_showval = "off";
    if( $showval != $ebm_showval )
      $showval = setSetting( "showval", $ebm_showval, $loguser );
    if( !isset( $ebm_showbrowse ) ) $ebm_showbrowse = "off";
    if( $showbrowse != $ebm_showbrowse )
      $showbrowse = setSetting( "showbrowse", $ebm_showbrowse, $loguser );
    if( !isset( $ebm_showrss ) ) $ebm_showrss = "off";
    if( $showrss != $ebm_showrss )
      $showrss = setSetting( "showrss", $ebm_showrss, $loguser );
}

require("header.php");
echo "<a href=\"index.php\">Back</a>\n";

if( $ebm_cmd == "vacuum" ){
    dbcleanup();
    echo "<h2>Database clean</h2>\n";
}

if( $ebm_cmd=="adduser" ){
    if(!isset($ebm_name) || ($ebm_name=="")){
	echo "<h2>No username given!</h2>\n";
    }else if($ebm_pass == $ebm_pass2){
        $ebm_pass = md5( $ebm_pass );
        addUser( $ebm_name, $ebm_pass );
    }else{
        echo "<h2>Password mismatch!</h2>\n";
    }
}

if( $ebm_cmd=="deluser" ){
    if( isset($ebm_name) ){
        deleteUser( $ebm_name );
    }else{
        echo "<h2>No user to delete!</h2>\n";
    }
}

if( $ebm_cmd=="passwd" ){
    if( isset($ebm_name) ){
        if($ebm_pass == $ebm_pass2){
            $ebm_pass = md5( $ebm_pass );
            updateUser( $ebm_name, $ebm_pass );
        }else{
            echo "<h2>Password mismatch!</h2>\n";
        }
    }else{
        echo "<h2>No user given!</h2>}n";
    }
}

if(!isset($name)) $name="";

function lazyText( $text, $name, $value ){
    echo "    <tr><td>$text</td>\n";
    echo "      <td><input type=\"text\" maxlength=\"32\" name=\"$name\" value=\"$value\"></td>\n";
    echo "    </tr>\n";
}

function lazyCheck( $text, $name, $value ){
    echo "    <tr><td>$text</td>\n";
    if($value == "on"){
        echo "      <td><input type=\"checkbox\" name=\"$name\" value=\"on\" checked></td>\n";
    }else{
        echo "      <td><input type=\"checkbox\" name=\"$name\" value=\"on\"></td>\n";
    }
    echo "    </tr>\n";
}

echo "<h3>Settings</h3>\n";

echo "<form action=\"admin.php\" method=\"post\">\n";

echo "  <table>\n";
if($loguser=="ebm")
    lazyText ("Title", "title", $title);
lazyText ("Default category", "defcat", $defcat);
if($loguser=="ebm"){
    lazyCheck("Add/edit without login", "publicadd", $publicadd);
    lazyText ("Message of the day", "motd", $motd );
}
lazyCheck("Kill button", "killbutton", $killbutton);
lazyCheck("Open links in new window", "newwin", $newwin);
if($loguser=="ebm"){
    lazyText ("Contact address", "contact", $contact);
    lazyCheck("Force login", "forcelogin", $forcelogin);
    lazyText("Days for cookie to be valid", "days", $days );
	lazyCheck("Use HTTPS", "https", $https);
}
lazyCheck("Show search field", "quicksearch", $quicksearch);
lazyCheck("Jump to edit page in livebookmarks", "toedit", $toedit);
lazyCheck("Show Browse link in title", "showbrowse", $showbrowse);
lazyCheck("Show Checklink link in title", "showval", $showval);
lazyCheck("Show RSS link in title", "showrss", $showrss);

echo "  <tr><td>CSS to use</td>\n";
echo "    <td>\n";
echo "      <select name=\"cssfile\">\n";
$cssfiles=getCSSlist();
foreach($cssfiles as $actual){
    $actual=chop($actual);
    if($actual!=$cssfile){
        echo "        <option>$actual\n";
    }else{
        echo "        <option selected>$actual\n";
    }
}
echo "      </select>\n";
echo "  </td></tr>\n";
echo "  <tr><td>&nbsp;</td><td><input type=\"submit\" value=\"Set\"></td></tr>\n";
echo "  </table>\n";
echo "  <input type=\"hidden\" name=\"cmd\" value=\"set\">\n";
echo "</form>\n";

if($loguser=="ebm"){
echo "<h3>New User</h3>\n";
echo "<form action=\"admin.php\" method=\"post\">\n";
echo "  <table border=0>\n";
echo "    <tr><td>Login:</td>\n";
echo "        <td><input type=\"text\" name=\"name\" value=\"$name\"></td>\n";
echo "    </tr>\n";
echo "    <tr><td>Password:</td>\n";
echo "        <td><input type=\"password\" name=\"pass\" value=\"\">\n";
echo "        </td>\n";
echo "    </tr>\n";
echo "    <tr><td>Retype:</td>\n";
echo "        <td><input type=\"password\" name=\"pass2\" value=\"\">\n";
echo "        </td>\n";
echo "    </tr>\n";
echo "    <tr><td>&nbsp;</td>\n";
echo "        <td><input type=\"submit\" value=\"Create\">\n";
echo "        </td>\n";
echo "    </tr>\n";
echo "  </table>\n";
echo "  <input type=\"hidden\" name=\"cmd\" value=\"adduser\">\n";
echo "</form>\n";
}

if($loguser=="ebm"){
$users=getUsers();
echo "<h3>Delete User</h3>\n";
echo "<form action=\"admin.php\" method=\"post\">\n";
echo "  <input type=\"hidden\" name=\"cmd\" value=\"deluser\">\n";
echo "    <select name=\"name\">\n";
foreach($users as $actual){
    $actual=chop($actual);
    if($actual!="ebm"){
        echo "      <option>$actual\n";
    }
}
echo "  </select>\n";
echo "  <input type=\"submit\" value=\"Delete\">\n";
echo "</form>\n";
}

echo "<h3>Utilities</h3>\n";
echo "<ul>\n";
echo "  <li><a href=\"admin.php?cmd=vacuum\">Clean up database</a></li>\n";
echo "  <li><a href=\"ebm2bm.php\">Export Database in Netscape bookmarks.html format</a></li>\n";
echo "  <li><form enctype=\"multipart/form-data\" action=\"bm2ebm.php\" method=\"post\">\n";
echo "    Database in bookmarks.html format: <input name=\"bookmarks\" type=\"file\" />\n";
echo "    <input type=\"submit\" value=\"Import\" />\n";
echo "  </form></li>\n";
echo "</ul>\n";

echo "<h3>Change Password</h3>\n";
echo "<form action=\"admin.php\" method=\"post\">\n";
echo "  <input type=\"hidden\" name=\"cmd\" value=\"passwd\">\n";
echo "  <table border=0>\n";
echo "    <tr><td>Login:</td>\n";
if($loguser=="ebm"){
echo "      <td><select name=\"name\">\n";
foreach($users as $actual){
    $actual=chop($actual);
    echo "        <option>$actual\n";
}
echo "      </select></td>\n";
}else{
    echo "      <td><b>$loguser</b></td>\n";
}
echo "    </tr>\n";
echo "    <tr><td>Password:</td>\n";
echo "        <td><input type=\"password\" name=\"pass\" value=\"\">\n";
echo "        </td>\n";
echo "    </tr>\n";
echo "    <tr><td>Retype:</td>\n";
echo "        <td><input type=\"password\" name=\"pass2\" value=\"\">\n";
echo "        </td>\n";
echo "    </tr>\n";
echo "    <tr><td>&nbsp;</td>\n";
echo "        <td><input type=\"submit\" value=\"Change\">\n";
echo "        </td>\n";
echo "    </tr>\n";
echo "  </table>\n";
echo "</form>\n";

echo "<a href=\"index.php\">Back</a>\n";

require("footer.php");
?>
