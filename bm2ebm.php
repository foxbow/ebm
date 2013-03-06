<?PHP
require("commands.php");
$loguser=currentUser(true);
require("setter.php");
if($loguser=="ebm") $ebm_user="PUBLIC";
else $ebm_user=$loguser;
require("header.php");

echo "<h1>$ebm_user</h1>\n";

function parseline( $line, $key, $open, $close ){
  $pos=strpos( $line, $key )+strlen( $key );
  $start=strpos( $line, $open, $pos )+strlen( $open );
  $len=strpos( $line, $close, $start )-$start;
  return substr( $line, $start, $len );
}

$category="IMPORT";
newCat( $category );
$filename=$_FILES['bookmarks']['tmp_name'];
$data=file($filename);
$stack=array();

foreach( $data as $dataline ){
  // new category (<H3>)
  if(strpos($dataline, "<H3") !== false){
    array_push( $stack, $category );
    $category=parseline( $dataline, "<H3", ">", "</H3>" );
    $category=strtr( $category, "&", "+" );
    newCat( $category );
    echo "Import <b>$category</b><br>\n";      
  }

  // category closed (</DL>)
  if(strpos($dataline, "</DL>") !== false) 
    $category=array_pop( $stack );

  // new link (<a href)
  if(strpos($dataline, "<A HREF=") !== false){
    $link=parseline( $dataline, "<A HREF=", '"', '"' );
    if((strpos($link,"http")!==false)&&(strpos($link, "http" )==0)){
      $line=parseline( $dataline, "<A HREF=", ">", "</A>" );
      // echo "<b>$category</b> <i>$line</i><br>\n$link<br>\n";
      append( $category, $link, $line );
    }
  }
}

echo "Done.<br>&nbsp;<br>\n";

unlink($filename);

echo "\n<a href=\"$ebmurl/index.php?public=off\">Main</a><br>\n";

require("footer.php");
?>	
