<?php
extract($_REQUEST, EXTR_PREFIX_ALL|EXTR_REFS, 'ebm');
error_reporting( E_ALL );

if( !isset($ebm_return) || $ebm_return=="" ) $ebm_return="index.php";

setcookie( "user", "", time()-3600 );
setcookie( "pass", "", time()-3600 );
//header("HTTP/1.0 301");
header("Location: $ebm_return");
exit;
?>
