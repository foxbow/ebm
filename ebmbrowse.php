<?PHP
require("commands.php");

$loguser=currentUser(true);
require("setter.php");

if(!isset($ebm_user)) $ebm_user="PUBLIC";
if(!isset($ebm_category)) $ebm_category=$defcat;
if(!isset($ebm_title)) $ebm_title="EasyBookMarks";
if(!isset($ebm_public)) $ebm_public="on";

$category=chop($ebm_category);
if(!isset($ebm_current)) $ebm_current="";

if(($ebm_user!="PUBLIC") && ($ebm_user!=$loguser)){
  echo "<html>\n";
  echo "  <head>\n";
  echo "    <meta http-equiv=\"refresh\" content=\"0; URL=$ebmurl/login.php?user=$ebm_user\">\n";
  echo "    <title>EBM Forwarder</title>\n";
  echo "  </head><body>\n";
  echo "    Ooops, this browser seems incapable of forwardingi!<br>\n";
  echo "    Please log in as <a href='$ebmurl/login.php?user=$ebm_user'>$ebm_user</a>.\n";
  echo "  </body>\n";
  echo "</html>\n";
  exit;
}

$table=array();
$entries=db_getEntries($category);
sort($entries);
foreach($entries as $entry){
  $entry=chop($entry);
  $break=strpos($entry,"<>");
  $name=substr($entry,0,$break);
  $link=substr($entry,$break+2);
  $table[$name]=$link;
}

$names=array_keys($table);
$len=count($names);
if( $len==0 ){
    echo "<h1>Category $category is empty!</h1>\n";
    exit();
}

if($ebm_current=="") $ebm_current=pos($names);
$curlink=$table[$ebm_current];

$pos=array_search($ebm_current, $names);

if( ( $pos !== false ) && ( $pos+1 < count($names) ) ) 
    $next=$names[$pos+1];
else $next="";

echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\"\n";
echo "    \"http://www.w3.org/TR/html4/loose.dtd\">\n";
echo "<html><head>\n";
echo "  <meta http-equiv=\"Content-Type\" content=\"text/html; charset=iso-8859-1\">\n";
echo "  <meta http-equiv=\"expires\" content=\"0\">\n";
echo "  <meta name=\"robots\" content=\"noindex\">\n";
echo "  <title>$ebm_current</title>\n";
if( $usecss=="on" )
  echo "  <link rel=\"stylesheet\" type=\"text/css\" href=\"css/$cssfile\">\n";
echo "	<link rel=\"SHORTCUT ICON\" href=\"favicon.ico\">\n";
echo "</head>\n";

$ebm_user=urlencode($ebm_user);
$next=urlencode($next);
$ebm_category=urlencode($ebm_category);

echo "<frameset rows=\"30px,*\">\n";
echo "  <frame src=\"$ebmurl/ebmbrowsehead.php?next=$next&user=$ebm_user&category=$ebm_category\">\n";
echo "  <frame src=\"$curlink\">\n";
echo "  <noframes>\n";
echo "    <body><h1>Sorry, I need frames for this feature</h1></body>\n";
echo "  </noframes>\n";
echo "</frameset></html>\n";
?>	
