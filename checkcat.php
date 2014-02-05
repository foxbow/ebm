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

echo "<script>
function checkit( link, fieldid ) {
var xmlhttp;
if ( window.XMLHttpRequest ) {
  // code for IE7+, Firefox, Chrome, Opera, Safari
  xmlhttp=new XMLHttpRequest();
} else {
  // code for IE6, IE5
  xmlhttp=new ActiveXObject(\"Microsoft.XMLHTTP\");
}

xmlhttp.onreadystatechange=function() {
  if (xmlhttp.readyState==4 && xmlhttp.status==200) {
    document.getElementById(fieldid).innerHTML=xmlhttp.responseText;
  }
}

xmlhttp.open(\"GET\",\"checklink.php?link=\"+link,true);
xmlhttp.send();
}
</script>\n";

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
		    removeByName( $ebm_category, $name, $ebm_user );
		    echo "<li>$name</li>\n";
		}
		echo "</ul>\n";
    }
}

$encat=urlencode($ebm_category);
echo "<a href=\"index.php?category=$encat&amp;user=$ebm_user\"><b>Back to $ebm_category</b></a>\n";
echo "<hr>\n";

/*****************************************************************************/
// Open the master Table

$actual=chop($ebm_category);
$entries = getEntries($actual, $ebm_user);

echo "<center>\n";

echo "<form action=\"$myname\" method=\"post\">";
echo "<input type=\"hidden\" name=\"cmd\" value=\"delete\">\n";
echo "<input type=\"hidden\" name=\"category\" value=\"$ebm_category\">\n";
echo "<input type=\"hidden\" name=\"user\" value=\"$ebm_user\">\n";

echo "<table class=\"links\" cellpadding=\"1\" border=\"0\">\n";
echo "  <tr>\n";
echo "    <td class=\"catlist\" width=\"95%\" colspan=\"3\">$actual</td>\n";
echo "  </tr>\n";

$flag = true;
$left = true;
$fid=0;
foreach($entries as $entry){
    $name=$entry['desc'];
    $ebm_link=$entry['link'];
    if($flag) $rowcol="oddrow";
    else $rowcol="everow";

    echo "  <tr>\n";
// Name and check
    echo "    <td class='$rowcol' align='left'>";
    echo "<a href=\"$ebm_link\"";
    if( $newwin == "on" ) echo " target='_blank'";
    echo ">$name</a></td>\n";
    echo "    <td class='$rowcol' align='left' id=\"field$fid\">.. checking ..</td>\n";

// Killbutton
    echo "    <td><input type='checkbox' name='name[]' value=\"$name\"></td>\n";
    echo "  </tr>\n";
	echo "    <script>checkit(\"$ebm_link\", \"field$fid\");</script>\n";

    $flag=!$flag;
	$fid++;
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
echo "<a href=\"index.php?category=$encat&user=$ebm_user\"><b>Back to $ebm_category</b></a>\n";

require("footer.php");
?>
