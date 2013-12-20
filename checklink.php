<?php
if( isset( $_GET["link"] ) ) $link = $_GET["link"];
else echo "No link given!";

if( $link != "" ) echo validate( $link );
else echo "Empty link!";

function validate( $link ){
	$message="";
	$errno=0;
	$errstr="";
	$details=parse_url( $link );
	$ans="buff $link";

	if (!isset($details['port']) || ($details['port']=="")){
		$details['port']="80";
	}
    // Make PHP ignore errors as we handle them ourselves
	$fp=@fsockopen($details['host'],$details['port'],$errno,$errstr,10);
	if ($fp === false){
		$ans="ERROR -1 Host Not Found";
	}else{
		if ( (!isset($details['path'])) || ($details['path']=="") )
			$details['path']="/";
		fputs($fp,"GET ".$details['path']." HTTP/1.1\r\n");
		fputs($fp,"host: ".$details['host']."\r\n");
		fputs($fp,"Connection: Close\r\n\r\n");
		$ans=fgets($fp,128);
		fclose($fp);
	}
/*
     $answer=explode(" ",$ans);
          if (($answer[1] != "200") and ($answer[1] != "302")) {
                $i=$answer[1];
                $message = "<font color=RED>[$i] $resultcode[$i]</font>";
          }
 */
    return strchr($ans, " ");
}
?>
