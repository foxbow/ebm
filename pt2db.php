<?php
require( "commands.php" );

if (isset($_COOKIE["user"])) {
	 $loguser = $_COOKIE["user"];
	 $password = $_COOKIE["pass"];
	 if( ($loguser != "ebm") ||
		  (checkpass( $loguser, $password )!=1) ){
		  header("HTTP/1.0 301");
		  header("Location: login.php");
		  exit;
	 }
}else{
	 header("HTTP/1.0 301");
	 header("Location: login.php");
	 exit;
}

require( "header.php" );

echo "<a href=\"index.php\">Back</a>\n";

function moveUser( $user, $pass ){
	 global $prefix;
	 
	 echo "<h2>Moving $user</h2>\n";
	
	 if($pass != ""){
		  db_addUser( $user, $pass );
	 }
	 
	 if(!file_exists("$prefix/db_$user")){
		  mkdir("$prefix/db_$user",0700);
	 }
	 if(!file_exists("$prefix/db_$user/categories.txt")){
		  touch("$prefix/db_$user/categories.txt");
	 }
	 $category = file("$prefix/db_$user/categories.txt");
	 
	 foreach( $category as $cat ){
		  $cat = chop( $cat );
		  echo "<h3>Moving $cat</h3>\n";
		  newCat( addslashes($cat) );
		  
		  if(!file_exists("$prefix/db_$user/$cat.txt")){
				touch("$prefix/db_$user/$cat.txt");
		  }
		  $entries = file("$prefix/db_$user/$cat.txt");
		  
		  foreach( $entries as $entry ){
				$entry=chop($entry);
				$break=strpos($entry,"<>");
				$name=substr($entry,0,$break);
				$link=substr($entry,$break+2);

				$name = addslashes( chop($name) );
				$link = chop( $link );
				
				append( $cat, $link, $name );
		  }
	 }
}

echo "<h1>Transferring data from plaintext to $database</h1>\n";

$user="PUBLIC";
moveUser($user, "");
$passwd="$prefix/ebm_passwd.txt";
if(!file_exists($passwd)){
	 require("header.php");
	 echo "<h1>$passwd not found, check installation!</h1>\n";
	 require("footer.php");
	 exit;
}
$lines=file($passwd);
$step=0;
foreach( $lines as $line ){
	 if($step==0){
		  $user=chop($line);
		  $step=1;
	 }else{
		  moveUser( $user, chop($line) );
		  $step=0;
	 }
}

echo"<h1>Done</h1>\n";
	 
echo "<a href=\"index.php\">Back</a>\n";

require( "footer.php" );
?>
	
