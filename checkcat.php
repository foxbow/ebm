<?php
// get the interface
require("commands.php");

if (!isset($ebm_category)) {
     header("Location: index.php");
     exit;
}

$myname="checkcat.php";

$loguser=currentUser(true);

require("setter.php");
require("header.php");

if(empty($ebm_public)){
    $ebm_public="on";
}
if($ebm_public=="off"){
    $ebm_user = $loguser;
}else{
    $ebm_user = "PUBLIC";
}

echo "<h1>Check $ebm_category</h1>\n";
echo "<hr>\n";

if(empty($ebm_cmd)){
    $ebm_cmd="show";
}
if($ebm_cmd=="delete"){
    if( count( $ebm_name ) == 0 ){
	echo "<li>Nothing to delete!</li>\n";
    }else{
	echo "Deleting:<ul>\n";
	foreach( $ebm_name as $name ){
	    removeByName( $ebm_category, $name );
	    echo "<li>$name</li>\n";
	}
	echo "</ul>\n";
    }
//    remove($ebm_file, $ebm_link, $ebm_line );
}

$encat=urlencode($ebm_category);
echo "<a href=\"index.php?category=$encat&amp;public=$ebm_public\"><b>Back to $ebm_category</b></a>\n";
echo "<hr>\n";

/*****************************************************************************/
// Open the master Table

$actual=chop($ebm_category);
$entries = db_getEntries($actual);

echo "<center>\n";

echo "<form action=\"$myname\" method=\"post\">";
echo "<input type=\"hidden\" name=\"cmd\" value=\"delete\">\n";
echo "<input type=\"hidden\" name=\"category\" value=\"$ebm_category\">\n";
echo "<input type=\"hidden\" name=\"public\" value=\"$ebm_public\">\n";

echo "<table class=\"links\" cellpadding=\"1\" border=\"0\">\n";
echo "  <tr>\n";
echo "    <td class=\"catlist\" width=\"95%\" colspan=\"3\">$actual</td>\n";
echo "  </tr>\n";

$flag = true;
$left = true;
foreach($entries as $entry){
    $entry=chop($entry);
    $name=$entry['desc'];
    $ebm_link=$entry['link'];
    if($flag) $rowcol="oddrow";
    else $rowcol="everow";

    echo "  <tr>\n";

// Radiobutton
//    echo "    <td class=\"$rowcol\" align=\"center\">\n";
//    echo "      <input type=\"radio\" name=\"entry\" value=\"$entry\">\n";
//    echo "    </td>\n";

// Name and check
    echo "    <td class=\"$rowcol\" align=\"left\">";
    echo "<a href=\"$ebm_link\"";
    if( $newwin == "on" ) echo " target=\"_blank\"";
    echo ">$name</a></td>\n";
    $result = validate( $ebm_link );

    echo "    <td class=\"$rowcol\" align=\"left\">";
    echo "$result</td>\n";

// Killbutton
    echo "    <td><input type=\"checkbox\" name=\"name[]\" value=\"$name\"></td>\n";
    echo "  </tr>\n";

    $flag=!$flag;
}

echo "  <tr>\n";
echo "  <tr><td>&nbsp;</td>\n";
echo "    <td><input type=\"submit\" value=\"Delete!\"></td>\n";
echo "  <td>&nbsp;</td></tr>\n";
// Close the master Table
echo "</table>\n";
echo "</form>\n";

echo "</center>\n";

/*****************************************************************************/

echo "<hr>\n";
echo "<a href=\"index.php?category=$encat&public=$ebm_public\"><b>Back to $ebm_category</b></a>\n";

require("footer.php");
?>
