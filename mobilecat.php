<?PHP
require("commands.php");

$loguser=currentUser(false);
require("setter.php");

if(!isset($ebm_category)) $ebm_category=$defcat;
if(!isset($ebm_title)) $ebm_title="EasyBookMarks";

echo "<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'\n";
echo "    'http://www.w3.org/TR/html4/loose.dtd'>\n";
echo "<html><head>\n";
echo "  <meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>\n";
echo "  <meta http-equiv='expires' content='0'>\n";
echo "  <meta name='robots' content='noindex'>\n";
echo "  <title>Select category</title>\n";
echo "  <link rel='stylesheet' type='text/css' href='css/".$cssfile."'>\n";
echo "  <link rel='SHORTCUT ICON' href='favicon.ico'>\n";
echo "</head><body>\n";

if(($ebm_user!="PUBLIC") && ($ebm_user!=$loguser)){
  echo "<a href='login.php?user=$ebm_user'&return='mobilecat.php'>Log in as $ebm_user first!</a>\n";
}else{
  $entries=getCategories();
  sort($entries);
  foreach($entries as $entry){
    echo "  <a class='mobile' href='mobile.php?category=$entry'>".$entry."</a>\n";
  }
  if( $ebm_user == "PUBLIC" ) {
    echo "<hr><a class='mobile' href='login.php?return=mobilecat.php'><b>Log in</b></a>\n";
  } else {
    echo "<hr><a class='mobile' href='logout.php?return=mobilecat.php'><b>Log out</b></a>\n";
  }
}
echo "</body></html>\n";
?>
