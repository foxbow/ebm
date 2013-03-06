<?php
require("commands.php");

if( !isset($ebm_user) ) $ebm_user="";
$ebm_user = chop($ebm_user);

if( !isset($ebm_persist) ) $ebm_persist="off";

if( isset($ebm_pass) ){
    $ebm_pass = md5(chop($ebm_pass));
    $found = checkpass( $ebm_user, $ebm_pass );

    if( $found == 1 ){
	if( $ebm_persist=="on" ){
	    setcookie( "user", "$ebm_user", time()+$days*60*24*30 );
	    setcookie( "pass", "$ebm_pass", time()+$days*60*24*30 );
	}else{
	    setcookie( "user", "$ebm_user" );
	    setcookie( "pass", "$ebm_pass" );
	}      // header("HTTP/1.0 301");
	$header="Location: index.php";
	
	if( getSetting("jumptopriv", "off", $ebm_user)  == "on" ){
	    $header=$header."?public=off";
	    if( isset($ebm_category) ) $header=$header."&category=$ebm_category";
	}else if( isset($ebm_category) ) $header=$header."?category=$ebm_category";
	header( $header );
	exit;
    }
}

require("setter.php");

require("header.php");

echo "<h1>Log into Easybookmarks</h1>\n";
echo "<form action=\"login.php\" method=\"post\">\n";
echo "  <table border=0>\n";
echo "    <tr><td>Login:</td>\n";
echo "        <td><input type=\"text\" name=\"user\" value=\"$ebm_user\"></td>\n";
echo "    </tr>\n";
echo "    <tr><td>Password:</td>\n";
echo "        <td><input type=\"password\" name=\"pass\" value=\"\">\n";
if(isset($ebm_category)){
  echo"             <input type=\"hidden\" name=\"category\" value=\"$ebm_category\">\n";
}
echo "        </td>\n";
echo "    </tr>\n";
echo "    <tr><td>Remember Login:</td>\n";
echo "      <td><input type=\"checkbox\" name=\"persist\" value=\"on\"></td>\n";
echo "    </tr>\n";
echo "    <tr><td>&nbsp;</td>\n";
echo "        <td><input type=\"submit\" value=\"login\">\n";
echo "        </td>\n";
echo "    </tr>\n";
echo "  </table>\n";
echo "</form>\n";

require("footer.php");
?>
