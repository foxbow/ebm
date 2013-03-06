<?PHP

require( "commands.php" );
require( "header.php" );

echo "<h1>Linktester</h1>\n";

if( isset( $ebm_link ) ){
	 echo "<h2>Testing $ebm_link</h2>\n";
	 $status = validate( $ebm_link );
	 if ($status == "") $status = "OK!";
	 echo "Status = $status<br>\n";
	 $title = getTitle( $ebm_link );
	 echo "Title  = $title<br>\n";
}else{
	 echo "<h2>Set link to test!</h2>\n";
}

require( "footer.php" );
?>
