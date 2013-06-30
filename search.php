<?PHP
require("commands.php");

$loguser=currentUser(true);
if(($loguser=="") || ($loguser=="ebm")) $ebm_user="PUBLIC";
else $ebm_user=$loguser;

if(!isset($ebm_public)) $ebm_public="on";

require("setter.php");

$left=1;
$rows=5;
$percent=20;

require("header.php");

$ebm_search=strtolower($ebm_search);

echo "<h1>Searchresults for $ebm_search</h1>\n";

echo "<center>\n";

echo "<table class='header' cellpadding='1' border='0' width='98%'>\n";
/*
 * Add the view and the logout switch
 */
echo "  <tr><td class='header' width='20%'>\n";
if($ebm_public=="off"){
    echo "      <a href='$ebmurl/index.php?public=on'>Public links</a>\n";
}else{
    if($loguser==""){
        echo "      <a href='$ebmurl/login.php'>Login</a>\n";
    }else if( $loguser=="ebm" ){
        echo "      <a href='$ebmurl/admin.php'>Admin</a>\n";
    }else{
        echo "      <a href='$ebmurl/index.php?public=off'>$loguser's links</a>\n";
    }
}
echo "  </td>\n";

echo "  <td class='header' width='20%'>\n";
echo "    <a href='$ebmurl/logout.php'>Logout</a>\n";
echo "  </td>\n";
if($quicksearch=="on"){
     echo "  <td class='header' style='white-space:nowrap;'>\n";
     echo "    <form action='search.php' method='post'>\n";
     echo "      <input name='search' type='text'>\n";
     echo "      <input type='hidden' name='public' value='$ebm_public'>\n";
     echo "      <input type='submit' value='search'>\n";
     echo "    </form>\n";
     echo "  </td>\n";
}

echo "  <td class='header' width='20'>\n";
echo "    <a href='admin.php'>?</a>\n";
echo "  </td>\n";
echo "</tr></table>\n";

echo "\n<p style='margin:20px;'></p>\n";

// get all categories
$categories=getCategories();
sort($categories);

$flag = true;

$entries = searchEntries($ebm_search, "PUBLIC");
$entrycount = count( $entries );

if( $entrycount <= 10 )
  $lrows = 1;
else if ( $entrycount <= 20 )
  $lrows = 2;
else if ( $entrycount <= 30 )
  $lrows = 3;
else
  $lrows = 4;

// Links
$lwidth=100-(50/$lrows);
$factor=1;

$lpercent=(105-(5*$factor))/$lrows;
$colspan = $factor*$lrows;

echo "<table class='links' cellpadding='1' border='0' width='$lwidth%'>\n";
echo "  <tr>\n";
echo "    <td class='catlist' width='95%' colspan='$colspan'>\n";
echo "      $ebm_search in public links\n";
echo "    </td>\n";
echo "  </tr>\n";

$left=1;
foreach($entries as $entry){
    $name=$entry['desc'];
    $link=$entry['link'];

    if($left==1){
        if($flag){
            $rowcol="oddrow";
        }else{
            $rowcol="everow";
        }
        $flag = !$flag;

        echo "  <tr>\n";
    }

    if($newwin=="on")
	echo "    <td class='$rowcol' width='$lpercent%'><a href='$link' target='_blank'>$name</a></td>\n";
    else
	echo "    <td class='$rowcol' width='$lpercent%'><a href='$link'>$name</a></td>\n";

    if($left==$lrows){
        echo "  </tr>\n";
        $left=0;
    }

    $left+=1;

}

if($left!=1){
    for($i=$left; $i<=$lrows; $i++){
        echo "    <td width='$lpercent%'>&nbsp;</td>\n";
        for($j=1;$j<$factor;$j++) echo "    <td>&nbsp;</td>\n";
    }
    echo "  </tr>\n";
}

echo"</table>\n";

/**
 * do we need to check private links too?
 **/

if($ebm_user != "PUBLIC"){
    echo "\n<p style='margin:20px;'></p>\n";

    $flag = true;
    $entries = searchEntries($ebm_search, $loguser);

    $entrycount = count( $entries );

    if( $entrycount <= 10 )
      $lrows = 1;
    else if ( $entrycount <= 20 )
      $lrows = 2;
    else if ( $entrycount <= 30 )
      $lrows = 3;
    else
      $lrows = 4;

    // Links
    $lwidth=100-(50/$lrows);
    $factor=1;

    $lpercent=(105-(5*$factor))/$lrows;
    $colspan = $factor*$lrows;

    echo "<table class='links' cellpadding='1' border='0' width='$lwidth%'>\n";
    echo "  <tr>\n";
    echo "    <td class='catlist' width='95%' colspan='$colspan'>\n";
    echo "      $ebm_search in $loguser's links\n";
    echo "    </td>\n";
    echo "  </tr>\n";

    $left=1;
    foreach($entries as $entry){
        $name=$entry['desc'];
        $link=$entry['link'];

        if($left==1){
            if($flag){
                $rowcol="oddrow";
            }else{
                $rowcol="everow";
            }
            $flag = !$flag;

            echo "  <tr>\n";
        }

        if($newwin=="on")
	    echo "    <td class='$rowcol' width='$lpercent%'><a href='$link' target='_blank'>$name</a></td>\n";
	else
            echo "    <td class='$rowcol' width='$lpercent%'><a href='$link'>$name</a></td>\n";

        if($left==$lrows){
            echo "  </tr>\n";
            $left=0;
        }

        $left+=1;

    }

    if($left!=1){
        for($i=$left; $i<=$lrows; $i++){
            echo "    <td width='$lpercent%'>&nbsp;</td>\n";
            for($j=1;$j<$factor;$j++) echo "    <td>&nbsp;</td>\n";
        }
        echo "  </tr>\n";
    }

    echo"</table>\n";
}

echo "\n<p style='margin:20px;'></p>\n";

echo "<table class='newlink' cellpadding='1' border='0'>\n";
echo "<tr>\n";
echo "  <form action='search.php' method='post'\n";
echo "    <input type='hidden' name='public' value='$ebm_public'>\n";
echo "    <td>\n";
echo "      New Search:\n";
echo "    </td><td>\n";
echo "      <input name='search' type='text'>\n";
echo "    </td><td>\n";
echo "      <input type='submit' value='search'>\n";
echo "    </td>\n";
echo "  </form>\n";
echo "</tr>\n";
echo "</table>\n\n";

echo "</center>\n";

echo "\n<p style='margin:20px;'></p>\n";
echo "<a class='flow' href='$ebmurl/index.php'>Back</a>\n";
echo "\n<p style='margin:20px;'></p>\n";

require("footer.php");
?>
