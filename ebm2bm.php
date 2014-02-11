<?PHP
header('Content-Description: Bookmark download');
header('Content-Type: application/force-download');
header('Content-Disposition: attachment; filename=bookmarks.html'); 

require("commands.php");

$ebm_user="PUBLIC";

echo "<!DOCTYPE NETSCAPE-Bookmark-file-1>\n";
echo "<!-- This is an automatically generated file.\n";
echo "     Created with ebm2bm -->\n";
echo "<META HTTP-EQUIV=\"Content-Type\" CONTENT=\"text/html; charset=UTF-8\">\n";
echo "<TITLE>$title</TITLE>\n";
echo "<H1>$title</H1>\n";
echo "<DL><p>\n";

/** Public bookmarks are suppressed for now **
echo "    <DT><H3>Public Bookmarks</H3>\n";
echo "    <DL><p>\n";
$categories=getCategories();
sort($categories);
foreach($categories as $actual){
  $actual=chop($actual);
  echo "        <DT><H3>$actual</H3>\n";
  echo "        <DL><p>\n";
  $entries=getEntries($actual);
  sort($entries);
  foreach($entries as $entry){
    echo "            <DT><A HREF=\"".$entry['link']."\">".$entry['desc']."</A>\n";
  }
  echo "        </DL><p>\n";
}
echo "    </DL><p>\n";
**/

$ebm_user=currentUser(false);
if(($ebm_user!="") && ($ebm_user!="ebm")){
  echo "    <HR>\n";
  echo "    <DT><H3>$ebm_user's Bookmarks</H3>\n";
  echo "    <DL><p>\n";
  $categories=getCategories($ebm_user);
  sort($categories);
  foreach($categories as $actual){
    $actual=chop($actual);
    echo "        <DT><H3>$actual</H3>\n";
    echo "        <DL><p>\n";
    $entries=getEntries($actual, $ebm_user);
    sort($entries);
    foreach($entries as $entry){
      echo "            <DT><A HREF=\"".$entry['link']."\">".$entry['desc']."</A>\n";
    }
    echo "        </DL><p>\n";
  }
  echo "    </DL><p>\n";
}

echo "</DL><p>\n";

?>	
