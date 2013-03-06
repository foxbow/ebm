<?php
setcookie( "user", "", time()-3600 );
setcookie( "pass", "", time()-3600 );
//header("HTTP/1.0 301");
header("Location: index.php");
exit;
?>
