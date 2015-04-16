<?php
require "commands.php";
require "setter.php";
require "header.php";
echo "  <center><b>";

if(!isset($ebm_category)) $ebm_category=$defcat;
if(!isset($ebm_next)) $ebm_next="";
if(!isset($ebm_curlink)) $ebm_curlink="";

if ($ebm_next=="") echo "Done.";
else{
    $ebm_user=urlencode($ebm_user);
    $next=urlencode($ebm_next);
    $ebm_category=urlencode($ebm_category);
    echo "<a href=\"ebmbrowse.php?current=$next&category=$ebm_category&user=$ebm_user\" target=\"_top\">$ebm_next</a>";
    echo "<a href='$ebm_curlink' target='new'>new tab</a>";
}
echo "    </center></b>\n";
echo "  </body></html>\n";
?>
