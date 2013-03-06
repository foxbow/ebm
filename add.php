<?php
// Make sure we won't run into naming conflicts
if (isset($HTTP_HOST) && isset($SERVER_NAME) && ($HTTP_HOST != $SERVER_NAME)) {
//    header("HTTP/1.0 301");
    header("Location: http://".$SERVER_NAME.$PHP_SELF);
    exit;
}

require("commands.php");
$loguser=currentUser(true);
require("setter.php");
$ebm_user=$loguser;
if(!isset($ebm_public)) $ebm_public="on";

if( ($ebm_public=="off") && ($ebm_user=="") ) $ebm_public="on";

require("header.php");

if(isset($_SERVER["HTTP_REFERER"])) $referer=$_SERVER["HTTP_REFERER"];
else $referer="No referer set!";
   
if (!isset($ebm_gotlink)) $ebm_gotlink=$referer;
if (!isset($ebm_gotline)) $ebm_gotline="";

// Headers
$rows=5;
$percent=100/$rows;

if(empty($ebm_public)) $ebm_public="on";

if($ebm_public=="on"){
    $ebm_user = "PUBLIC";
    echo "<h1>Add link to $title</h1>\n";
}else{
    // $ebm_user = $HTTP_SERVER_VARS["PHP_AUTH_USER"];
    echo "<h1>Add Link to $title for $ebm_user</h1>\n";
}

// --- start ---
echo "<center>\n";

if(empty($ebm_file)){
    $ebm_file="";
}
if(empty($ebm_category)){
    $ebm_category=$ebm_file;
}

if(($ebm_category=="categories") ||
    ($ebm_category=="")){
    $ebm_category=$defcat;
}

echo "<table $bgclass=\"$header\" cellpadding=\"1\" border=\"0\" width=\"98%\">\n";
/*
 * Add the view and the logout switch
 */
echo "  <tr><td $bgclass=\"$header\" width=\"20%\">\n";
if($ebm_public=="off"){
	 echo "      <a href=\"add.php?public=on&gotlink=$ebm_gotlink&gotline=$ebm_gotline\">Public links</a>\n";
}else{
	 if($loguser==""){
		  if($ebm_category==""){
				echo "      <a href=\"login.php\">Login</a>\n";
		  }else{
				echo "      <a href=\"login.php?category=$ebm_category\"><b><i>Login</i></b></a>\n";
		  }
	 }else if( $loguser=="ebm" ){
		  echo "      <a href=\"admin.php\">Admin</a>\n";
	 }else{
		  echo "      <a href=\"add.php?public=off&gotlink=$ebm_gotlink&gotline=$ebm_gotline\">$loguser's links</a>\n";
	 }
}
echo "  </td>\n";

echo "  <td $bgclass=\"$header\" width=\"20%\">\n";
echo "    <a href=\"logout.php\">Logout</a>\n";
echo "  </td>\n";

if($quicksearch=="on"){
     echo "  <td $bgclass=\"$header\" style=\"white-space:nowrap;\">\n";
     echo "    <form action=\"search.php\" method=\"post\"\n";
     echo "      <input name=\"search\" type=\"text\">\n";
     echo "      <input type=\"hidden\" name=\"public\" value=\"$ebm_public\">\n";
     echo "      <input type=\"submit\" value=\"search\">\n";
     echo "    </form>\n";
     echo "  </td>\n";
}
if($quickdict=="on"){
     echo "  <td $bgclass=\"$header\" style=\"white-space:nowrap;\">\n";
     echo "    <form action=\"http://dict.leo.org/\" method=\"get\" target=\"_blank\">\n";
     echo "      <input name=\"search\" type=\"text\">\n";
     echo "      <input type=\"submit\" value=\"dict\">\n";
     echo "    </form>\n";
     echo "  </td>\n";
}
if($quickgoogle=="on"){
     echo "  <td $bgclass=\"$header\" style=\"white-space:nowrap;\">\n";
     echo "    <form action=\"http://www.google.de/search\" method=\"get\" target=\"_blank\">\n";
     echo "      <input name=\"q\" type=\"text\">\n";
     echo "      <input type=\"submit\" value=\"Google\">\n";
     echo "    </form>\n";
     echo "  </td>\n";
}
echo "  <td $bgclass=\"$header\" width=\"20\">\n";
echo "    <a href=\"admin.php\">?</a>\n";
echo "  </td>\n";
echo "</tr></table>\n";

echo "\n<p style=\"margin:20px;\"></p>\n";

$categories=getCategories();
sort($categories);

if( ($publicadd=="on") || ($loguser!="") ){
     // New entry
     if(count($categories > 0)){
          echo "<table $bgclass=\"$newlink\" cellpadding=\"1\" border=\"0\">\n";
          echo "<tr>\n";
          echo "<form action=\"index.php\" method=\"post\">\n";
          echo "  <input type=\"hidden\" name=\"public\" value=\"$ebm_public\">\n";
          echo "  <input type=\"hidden\" name=\"cmd\" value=\"append\">\n";
          echo "    <td>New Link</td><td><input type=\"text\" name=\"link\" value=\"$ebm_gotlink\"></td>\n";
          echo "    <td>description:</td><td><input type=\"text\" name=\"line\" value=\"$ebm_gotline\"></td>\n";
          echo "    <td>in</td><td><select name=\"category\">\n";
          foreach($categories as $actual){
                $actual=chop($actual);
                if($actual!=$ebm_category){
                     echo "      <option>$actual\n";
                }else{
                     echo "      <option selected>$actual\n";
                }
          }
          echo "  </select></td>\n";
          echo "  <td><input type=\"submit\" value=\"create\"></td>\n";
          echo "</form>\n\n";
          echo"</tr>\n";
          echo "</table>\n\n";
     }else{
  echo "<table cellpadding=\"1\" border=\"0\" bgcolor=\"#ddffdd\">\n";
  echo "<tr>\n";
  echo "<th>Log in first to add links.</th>\n";
  echo"</tr>\n";
  echo "</table>\n";
     }
}

echo "</center>\n";

echo "\n<p style=\"margin:20px;\"></p>\n";
echo "<a href=\"index.php\">Back</a>\n";
echo "\n<p style=\"margin:20px;\"></p>\n";

require("footer.php");
?>
