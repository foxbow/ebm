<?PHP
require("commands.php");

$loguser=currentUser(false);
require("setter.php");

if(!isset($ebm_title)) $ebm_title="EasyBookMarks";

header("Content-type: text/xml");
echo "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\n";
echo "<rss version=\"0.92\">\n";
echo "  <channel>\n";
echo "    <title>$ebm_title</title>\n";
echo "    <link>index.php</link>\n";
echo "	  <description>Categories for $ebm_user</description>\n";

if(($ebm_user!="PUBLIC") && ($ebm_user!=$loguser)){
  echo "    <item>\n";
  echo "      <title>Log in as $ebm_user first!</title>\n";
  echo "      <link>login.php?user=$ebm_user</link>\n";
  echo "      <description>Not allowed!</description>\n";
  echo "    </item>\n";
}else{
  $entries=getCategories();
  sort($entries);
  foreach($entries as $entry){
    $entry=chop($entry);
    $link="index.php?category=".$entry."&user=".$ebm_user;
    echo "    <item>\n";
    echo "      <title>".str_replace("&", "&#38;", $entry)."</title>\n";
    echo "      <link>".str_replace("&", "&#38;", $link)."</link>\n";
    echo "      <description>EBM Category</description>\n";
    echo "    </item>\n";
  }
}
echo "  </channel>\n";
echo "</rss>\n";
?>	
