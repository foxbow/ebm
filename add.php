<?php
require("commands.php");
$loguser=currentUser(true);
require("setter.php");

require("header.php");

if(isset($_SERVER["HTTP_REFERER"])) $referer=$_SERVER["HTTP_REFERER"];
else $referer="No referer set!";
   
if (!isset($ebm_gotlink)) $ebm_gotlink=$referer;
if (!isset($ebm_gotline)) $ebm_gotline="";

$encoding = mb_detect_encoding($ebm_gotline, "UTF-8,ISO-8859-1,WINDOWS-1252");
if ($encoding != 'UTF-8') {
    $ebm_gotline=iconv($encoding, 'UTF-8//TRANSLIT', $ebm_gotline);
}

// Headers
$rows=5;
$percent=100/$rows;

if($ebm_user == "PUBLIC" ){
    echo "<h1>Add link to $title</h1>\n";
}else{
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

echo "<table class='header' cellpadding='1' border='0' width='98%'>\n";
/*
 * Add the view and the logout switch
 */
echo "  <tr><td class='header' width='20%'>\n";
if($ebm_user != "PUBLIC" ){
	 echo "      <a href='add.php?user=PUBLIC&gotlink=$ebm_gotlink&gotline=$ebm_gotline'>Public links</a>\n";
}else{
	 if( $loguser == "PUBLIC" ){
		  if($ebm_category==""){
				echo "      <a href='login.php'>Login</a>\n";
		  }else{
				echo "      <a href='login.php?category=$ebm_category'><b><i>Login</i></b></a>\n";
		  }
	 }else if( $loguser=="ebm" ){
		  echo "      <a href='admin.php'>Admin</a>\n";
	 }else{
		  echo "      <a href='add.php?user=$loguser&gotlink=$ebm_gotlink&gotline=$ebm_gotline'>$loguser's links</a>\n";
	 }
}
echo "  </td>\n";

echo "  <td class='header' width='20%'>\n";
echo "    <a href='logout.php'>Logout</a>\n";
echo "  </td>\n";

if($quicksearch=="on"){
     echo "  <td class='header' style='white-space:nowrap;'>\n";
     echo "    <form action='search.php' method='post'\n";
     echo "      <input name='search' type='text'>\n";
     echo "      <input type='submit' value='search'>\n";
     echo "    </form>\n";
     echo "  </td>\n";
}
echo "  <td class='header' width='20'>\n";
echo "    <a href='admin.php'>?</a>\n";
echo "  </td>\n";
echo "</tr></table>\n";

echo "\n<p style='margin:20px;'></p>\n";

$categories=getCategories( $ebm_user );
sort($categories);

if( ($publicadd=="on") || ($loguser!="PUBLIC") ){
     // New entry
     if(count($categories > 0)){
          echo "<table class='newlink' cellpadding='1' border='0'>\n";
          echo "<tr>\n";
          echo "<form action='index.php' method='post'>\n";
          echo "  <input type='hidden' name='user' value='$ebm_user'>\n";
          echo "  <input type='hidden' name='cmd' value='append'>\n";
          echo "    <td>New Link</td><td><input type='text' name='link' value='$ebm_gotlink'></td>\n";
          echo "    <td>description:</td><td><input type='text' name='line' value='$ebm_gotline'></td>\n";
          echo "    <td>in</td><td><select name='category'>\n";
          foreach($categories as $actual){
                $actual=chop($actual);
                if($actual!=$ebm_category){
                     echo "      <option>$actual\n";
                }else{
                     echo "      <option selected>$actual\n";
                }
          }
          echo "  </select></td>\n";
          echo "  <td><input type='submit' value='create'></td>\n";
          echo "</form>\n\n";
          echo"</tr>\n";
          echo "</table>\n\n";
     }else{
  echo "<table cellpadding='1' border='0' bgcolor='#ddffdd'>\n";
  echo "<tr>\n";
  echo "<th>Log in first to add links.</th>\n";
  echo"</tr>\n";
  echo "</table>\n";
     }
}

echo "</center>\n";

echo "\n<p style='margin:20px;'></p>\n";
echo "<a href='index.php'>Back</a>\n";
echo "\n<p style='margin:20px;'></p>\n";

require("footer.php");
?>
