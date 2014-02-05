<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
  "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
  <meta http-equiv="expires" content="0">
  <meta name="robots" content="noindex">
<?PHP
  echo "  <title>$title</title>\n";
  echo "  <link rel=\"stylesheet\" type=\"text/css\" href=\"css/$cssfile\">\n";
  if(isset($ebm_category)) $cat=$ebm_category;
  else $cat=$defcat;
  echo "  <link rel=\"alternate\" type=\"application/rss+xml\" title=\"$cat\" href=\"$uripath/ebm2rss.php?user=$ebm_user&category=$cat\">\n";
  if( $ebm_user == "PUBLIC" )
    echo "  <link rel=\"alternate\" type=\"application/rss+xml\" title=\"Overview\" href=\"$uripath/ebmcatrss.php\">\n";
  else
    echo "  <link rel=\"alternate\" type=\"application/rss+xml\" title=\"Overview\" href=\"$uripath/ebmcatrss.php?user=$ebm_user\">\n";
?>
  <link rel="SHORTCUT ICON" href="favicon.ico">
</head>
<body>
