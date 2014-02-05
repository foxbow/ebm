<?php
require "commands.php";
require "setter.php";
require "header.php";
echo "  <center><b>";

if(!isset($ebm_category)) $ebm_category=$defcat;
if(!isset($ebm_next)) $ebm_next="";

if ($ebm_next=="") echo "Done.";
else{
    $ebm_user=urlencode($ebm_user);
    $next=urlencode($ebm_next);
    $ebm_category=urlencode($ebm_category);
    echo "<a href=\"ebmbrowse.php?current=$next&category=$ebm_category&user=$ebm_user\" target=\"_top\">$ebm_next</a>";
/*
    if($killbutton=="on"){
	echo "      <form action=\"ebmbrowse.php\" method=\"post\">\n";
	echo "        <input type=\"hidden\" name=\"user\" value=\"$ebm_user\">\n";
	echo "        <input type=\"hidden\" name=\"cmd\" value=\"remove\">\n";
	echo "        <input type=\"hidden\" name=\"file\" value=\"$ebm_category\">\n";
	echo "        <input type=\"hidden\" name=\"line\" value=\"$name\">\n";
	echo "        <input type=\"hidden\" name=\"link\" value=\"$link\">\n";
	echo "        <input type=\"image\" src=\"kill.gif\">\n";
	echo "      </form>\n";
    }
*/
}
echo "    </center></b>\n";
echo "  </body></html>\n";
?>
