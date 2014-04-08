<?php
// get the interface
require("commands.php");

if (!isset($ebm_category)) {
     header("Location: index.php");
     exit;
}

$myname="edit.php";

$loguser=currentUser(true);

require("setter.php");
require("header.php");

echo "<h1>Edit $ebm_category</h1>\n";
echo "<hr>\n";

//    echo "cmd=$ebm_cmd / file=$ebm_file / link=$ebm_link / line=$ebm_line / cat=$ebm_category / user=$ebm_user<br>\n";

if(empty($ebm_cmd)){
    $ebm_cmd="show";
}
if($ebm_cmd=="remove"){
    db_removeEntry($ebm_file, $ebm_link, $ebm_line, $ebm_user );
}
if($ebm_cmd=="update"){
    update($ebm_category, $ebm_oldlink, $ebm_oldline, $ebm_newlink, $ebm_newline, $ebm_user );
}

if($ebm_cmd=="rencat"){
    $ebm_category=renCat($ebm_category, $ebm_newcat, $ebm_user);
}

if($ebm_cmd=="move"){
    movebyname($ebm_category, $ebm_line, $ebm_file, $ebm_user);
}

$encat=urlencode($ebm_category);
echo "<a href=\"index.php?category=$encat&amp;user=$ebm_user\"><b>Back to $ebm_category</b></a>\n";
echo "<hr>\n";

// get all categories
$categories=getCategories( $ebm_user );
// sort the categories
sort($categories);

/*****************************************************************************/
// Open the master Table

$actual=chop($ebm_category);
$entries = getEntries( $actual, $ebm_user );

echo "<center>\n";

echo "<table class=\"links\" cellpadding=\"1\" border=\"0\">\n";
echo "  <tr>\n";
echo "    <td class=\"catlist\" width=\"95%\" colspan=\"3\">\n";
echo "      <form action=\"$myname\" method=\"post\">\n";
echo "        <input type=\"hidden\" name=\"user\" value=\"$ebm_user\">\n";
echo "        <input type=\"hidden\" name=\"cmd\" value=\"rencat\">\n";
echo "        <input type=\"hidden\" name=\"category\" value=\"$actual\">\n";
echo "        <input type=\"text\" size=\"16\" name=\"newcat\" value=\"$actual\">\n";
echo "        <input type=\"submit\" value=\"rename\">\n";
echo "      </form>\n";
echo "    </td>\n";

echo "    <td class=\"catlist\">\n";
echo "      <form action=\"index.php\" method=\"post\">\n";
echo "        <input type=\"hidden\" name=\"user\" value=\"$ebm_user\">\n";
echo "        <input type=\"hidden\" name=\"cmd\" value=\"delete\">\n";
echo "        <input type=\"hidden\" name=\"line\" value=\"$actual\">\n";
echo "        <input type=\"image\" src=\"kill.gif\">\n";
echo "      </form>\n";
echo "    </td>\n";
echo "  </tr>\n";

$flag = true;
$left = true;

// Move to different category
// if($flag) $rowcol="oddrow";
// else $rowcol="everow";

// echo "    <td colspan=\"4\">&nbsp;</td>\n";
echo "    <td colspan=\"4\" class=\"catlist\">\n";
echo "      <form action=\"$myname\" method=\"post\">\n";
echo "      Move\n";
echo "        <input type=\"hidden\" name=\"category\" value=\"$actual\">\n";
echo "        <input type=\"hidden\" name=\"user\" value=\"$ebm_user\">\n";
echo "        <select name=\"line\">\n";
foreach($entries as $entry){
    echo "          <option>".$entry['desc']."</option>\n";
}
echo "        </select>\n";
echo "        to\n";
echo "        <input type=\"hidden\" name=\"cmd\" value=\"move\">\n";
echo "        <select name=\"file\">\n";
foreach($categories as $target){
    if(chop($target) != chop($actual))
        echo "          <option>$target</option>\n";
}
echo "        </select>\n";
echo "        <input type=\"submit\" value=\"OK\">\n";
echo "      </form>\n";
echo "    </td>\n";
// echo "    <td>&nbsp;</td>\n";
echo "  </tr>\n";

foreach($entries as $entry){
    $name=$entry['desc'];
    $ebm_link=$entry['link'];
    if($flag) $rowcol="oddrow";
    else $rowcol="everow";

    if( $left ) echo "  <tr>\n";

// Radiobutton
//    echo "    <td class=\"$rowcol\" align=\"center\">\n";
//    echo "      <input type=\"radio\" name=\"entry\" value=\"$entry\">\n";
//    echo "    </td>\n";

// Name and link
    echo "    <td class=\"$rowcol\" align=\"left\">\n";
    echo "      <form action=\"$myname\" method=\"post\">\n";
    echo "        <input type=\"hidden\" name=\"user\" value=\"$ebm_user\">\n";
    echo "        <input type=\"hidden\" name=\"cmd\" value=\"update\">\n";
    echo "        <input type=\"hidden\" name=\"oldlink\" value=\"$ebm_link\">\n";
    echo "        <input type=\"hidden\" name=\"oldline\" value=\"$name\">\n";
    echo "        <input type=\"hidden\" name=\"category\" value=\"$actual\">\n";
    echo "        <input type=\"text\" size=\"55\" name=\"newline\" value=\"$name\"><br>\n";
    echo "        <input type=\"text\" size=\"55\" name=\"newlink\" value=\"$ebm_link\">\n";
    echo "        <input type=\"submit\" value=\"update\">\n";
    echo "      </form>\n";
    echo "    </td>\n";

// Killbutton
    echo "    <td class=\"$rowcol\" align=\"center\">\n";
    echo "      <form action=\"$myname\" method=\"post\">\n";
    echo "        <input type=\"hidden\" name=\"user\" value=\"$ebm_user\">\n";
    echo "        <input type=\"hidden\" name=\"cmd\" value=\"remove\">\n";
    echo "        <input type=\"hidden\" name=\"file\" value=\"$actual\">\n";
    echo "        <input type=\"hidden\" name=\"link\" value=\"$ebm_link\">\n";
    echo "        <input type=\"hidden\" name=\"line\" value=\"$name\">\n";
    echo "        <input type=\"hidden\" name=\"category\" value=\"$actual\">\n";
    echo "        <input type=\"image\" src=\"kill.gif\">\n";
    echo "      </form>\n";
    echo "    </td>\n";
    if(!$left){
         echo "  </tr>\n";
         $flag=!$flag;
    }
    $left=!$left;
}

if(!$left){
    echo "    <td>&nbsp;</td><td>&nbsp;</td></tr>\n";
}


echo "  <tr>\n";


// Close the master Table
echo"</table>\n";

echo "<a href=\"checkcat.php?category=$ebm_category&user=$ebm_user\">";
echo "Check Links</a>\n";
$maxtime=count($entries)*10;
echo "(&lt; $maxtime sec!)\n";

echo "</center>\n";

/*****************************************************************************/

echo "<hr>\n";
echo "<a href=\"index.php?category=$encat&user=$ebm_user\"><b>Back to $ebm_category</b></a>\n";

require("footer.php");
?>
