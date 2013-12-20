<?PHP
require("commands.php");

$loguser=currentUser(false);
require("setter.php");

if(!isset($ebm_user)) $ebm_user="PUBLIC";
if(!isset($ebm_category)) $ebm_category=$defcat;
if(!isset($ebm_title)) $ebm_title="EasyBookMarks";
if(!isset($ebm_public)) $ebm_public="on";

$category=chop($ebm_category);

header("Content-type: text/xml");
echo "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\n";
echo "<rss version=\"0.92\">\n";
echo "  <channel>\n";
echo "    <title>$category</title>\n";
echo "    <link>index.php?category=$category&#38;public=";
    if ($ebm_user!="PUBLIC") echo "off";
    else echo "on";
echo "</link>\n";
echo "	  <description>$ebm_title</description>\n";

if(($ebm_user!="PUBLIC") && ($ebm_user!=$loguser)){
  echo "    <item>\n";
  echo "      <title>Log in as $ebm_user first!</title>\n";
  echo "      <link>login.php?user=$ebm_user</link>\n";
  echo "      <description>Not allowed!</description>\n";
  echo "    </item>\n";
}else{
  if ( (($loguser!="") || ($publicadd=="on")) && ($toedit=="on")){
    echo "    <item>\n";
    echo "      <title>- Edit -</title>\n";
    echo "      <link>edit.php?category=$category";
    if ($ebm_user!="PUBLIC") echo "&#38;public=off";
    else echo "&#38;public=on";
    echo "</link>\n";
    echo "      <description>Edit Bookmarks on server</description>\n";
    echo "    </item>\n";
  }
/** 
 * is in the channel link now
 *
  else{
    echo "    <item>\n";
    echo "      <title>- $category -</title>\n";
    echo "      <link>index.php?category=$category&#38;public=";
    if ($ebm_user!="PUBLIC") echo "off";
    else echo "on";
    echo "</link>\n";
    echo "      <description>Go to $title</description>\n";
    echo "    </item>\n";
  }
**/

  $entries=getEntries($category);
  sort($entries);
  foreach($entries as $entry){
    echo "    <item>\n";
    echo "      <title>".str_replace("&", "&#38;", $entry['desc'])."</title>\n";
    echo "      <link>".str_replace("&", "&#38;", $entry['link'])."</link>\n";
    echo "      <description>EBM Bookmark</description>\n";
    echo "    </item>\n";
  }
    
  /* Bookmarklets are not RSS conforming - pity ;) *
  echo "    <item>\n";
  echo "      <title>Add URL</title>\n";
  // echo "      <link>".str_replace("&", "&#38;", "javascript:link=document.URL;line=document.title;if(window.confirm('Add '+link+' as '+line+' to $ebm_category?')){window.location='$ebm_url/add.php?ebm_public=$ebm_public&ebm_category=$ebm_category&ebm_gotlink='+document.URL+'&ebm_gotline='+document.title;}else{window.stop();}")."</link>\n";
  echo "      <link>add.php?public=$ebm_public&#38;category=$ebm_category</link>\n";
  echo "      <description>This entry will add the current page to $ebm_category.</description>\n";
  echo "    </item>\n";
  */
}
echo "  </channel>\n";
echo "</rss>\n";
?>	
