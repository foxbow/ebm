<?PHP
require("commands.php");

$loguser=currentUser(false);
require("setter.php");

if(!isset($ebm_user)) $ebm_user="PUBLIC";
if(!isset($ebm_category)) $ebm_category=$defcat;
if(!isset($ebm_title)) $ebm_title="EasyBookMarks";
if(!isset($ebm_public)) $ebm_public="on";

$category=chop($ebm_category);

echo "<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'\n";
echo "    'http://www.w3.org/TR/html4/loose.dtd'>\n";
echo "<html><head>\n";
echo "  <meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>\n";
echo "  <meta http-equiv='expires' content='0'>\n";
echo "  <meta name='robots' content='noindex'>\n";
echo "  <title>$category</title>\n";
echo "  <link rel='stylesheet' type='text/css' href='css/".$cssfile."'>\n";
echo "  <link rel='SHORTCUT ICON' href='favicon.ico'>\n";
echo "</head><body>\n";

if(($ebm_user!="PUBLIC") && ($ebm_user!=$loguser)){
  echo "<a href='$ebmurl/login.php?user=$ebm_user'&return='mobile.php'>Log in as $ebm_user first!</a>\n";
}else{
  echo "<a class='sidebar' href='mobilecat.php'><b>$category</b></a><hr>\n";
  $entries=getEntries($category);
  sort($entries);
  foreach($entries as $entry){
    echo "  <a class='sidebar' href='".$entry['link']."'>".$entry['desc']."</a>\n";
  }
  if( $ebm_user == "PUBLIC" ) {
    echo "<hr><a class='sidebar' href='$ebmurl/login.php?return=mobile.php'><b>Log in</b></a>\n";
  } else {
    echo "<hr><a class='sidebar' href='$ebmurl/logout.php?return=mobile.php'><b>Log out</b></a>\n";
  }
}
echo "</body></html>\n";
?>	
