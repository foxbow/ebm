<?PHP
require("commands.php");
require("setter.php");

$loguser=currentUser( true );

if(!isset($ebm_category)) $ebm_category=$defcat;
if(!isset($ebm_title)) $ebm_title="EasyBookMarks";

$category=chop($ebm_category);

header("Content-type: text/xml");
echo "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\n";
echo "<rss version=\"0.92\">\n";
echo "  <channel>\n";
echo "    <title>$category</title>\n";
echo "    <link>$ebmurl/index.php?category=$category&#38;user=$ebm_user</link>\n";
echo "	  <description>$ebm_title</description>\n";
echo "    <item>\n";
echo "      <title>- $category -</title>\n";
echo "      <link>$ebmurl/index.php?category=$category&#38;user=$ebm_user</link>\n";
echo "	    <description>to $ebm_title</description>\n";
echo "    </item>\n";

if(($ebm_user!="PUBLIC") && ($ebm_user!=$loguser)){
  echo "    <item>\n";
  echo "      <title>Log in as $ebm_user first!</title>\n";
  echo "      <link>$ebmurl/login.php?user=$ebm_user</link>\n";
  echo "      <description>Not allowed!</description>\n";
  echo "    </item>\n";
}else{
  if ( (($loguser!="PUBLIC") || ($publicadd=="on")) && ($toedit=="on")){
    echo "    <item>\n";
    echo "      <title>- Edit -</title>\n";
    echo "      <link>$ebmurl/edit.php?category=$category&#38;user=$ebm_user";
    echo "</link>\n";
    echo "      <description>Edit Bookmarks on server</description>\n";
    echo "    </item>\n";
  }

  $entries=getEntries( $category, $ebm_user );
  sort($entries);
  foreach($entries as $entry){
    echo "    <item>\n";
    echo "      <title>".str_replace("&", "&#38;", $entry['desc'])."</title>\n";
    echo "      <link>".str_replace("&", "&#38;", $entry['link'])."</link>\n";
    echo "      <description>EBM Bookmark</description>\n";
    echo "    </item>\n";
  }
}
echo "  </channel>\n";
echo "</rss>\n";
?>	
