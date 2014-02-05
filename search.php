<?PHP
require("commands.php");

$loguser=currentUser(true);
if( $loguser=="ebm" ) $ebm_user="PUBLIC";

require("setter.php");

$left=1;
$rows=5;
$percent=20;

require("header.php");

if( !isset( $ebm_search ) ) $ebm_search="Search";
$ebm_search=strtolower($ebm_search);

if(isset($ebm_cmd) && $ebm_cmd=="remove"){
    db_removeEntry($ebm_file, $ebm_link, $ebm_line, $ebm_user );
}

echo "<h1>Searchresults for $ebm_search</h1>\n";

echo "<center>\n";

echo "<table class='header' cellpadding='1' border='0' width='98%'>\n";
/*
 * Add the view and the logout switch
 */
echo "  <tr><td class='header' width='20%'>\n";
if($ebm_user=="PUBLIC"){
    echo "      <a href='index.php?user=PUBLIC'>Public links</a>\n";
}else{
    if( $loguser == "PUBLIC" ){
        echo "      <a href='login.php'>Login</a>\n";
    }else if( $loguser == "ebm" ){
        echo "      <a href='admin.php'>Admin</a>\n";
    }else{
        echo "      <a href='index.php?name=$loguser'>$loguser's links</a>\n";
    }
}
echo "  </td>\n";

echo "  <td class='header' width='20%'>\n";
echo "    <a href='logout.php'>Logout</a>\n";
echo "  </td>\n";
if($quicksearch=="on"){
     echo "  <td class='header' style='white-space:nowrap;'>\n";
     echo "    <form action='search.php' method='post'>\n";
     echo "      <input name='search' type='text'>\n";
     echo "      <input type='hidden' name='user' value='$ebm_user'>\n";
     echo "      <input type='submit' value='search'>\n";
     echo "    </form>\n";
     echo "  </td>\n";
}

echo "  <td class='header' width='20'>\n";
echo "    <a href='admin.php'>?</a>\n";
echo "  </td>\n";
echo "</tr></table>\n";

echo "\n<p style='margin:20px;'></p>\n";

printSearch( $ebm_search, "PUBLIC" );
if($ebm_user != "PUBLIC"){
    echo "\n<p style='margin:20px;'></p>\n";
	printSearch($ebm_search, $ebm_user);
}

echo "\n<p style='margin:20px;'></p>\n";

echo "<table class='newlink' cellpadding='1' border='0'>\n";
echo "<tr>\n";
echo "  <form action='search.php' method='post'\n";
echo "    <input type='hidden' name='user' value='$ebm_user'>\n";
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
echo "<a class='flow' href='index.php'>Back</a>\n";
echo "\n<p style='margin:20px;'></p>\n";

require("footer.php");

function printSearch( $keyword, $username ) {
global $newwin, $killbutton;
	$lastcat="";
	$flag = true;

	$entries = searchEntries( $keyword, $username );
	$entrycount = count( $entries );

	if( $entrycount == 0 ) return;

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
	
	if($killbutton=="on"){
	    $factor += 1;
    }

	$lpercent=(105-(5*$factor))/$lrows;
	$colspan = $factor*$lrows;

	echo "<table class='links' cellpadding='1' border='0' width='$lwidth%'>\n";
	echo "  <tr>\n";
	echo "    <td class='catlist' width='95%' colspan='$colspan'>\n";
	if( $username == "PUBLIC" )
		echo "      $keyword in public links\n";
	else
		echo "      $keyword in $username's links\n";		
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

		if( $lastcat != $entry['cat'] ) {
			if( $lastcat != "" ) { 
				fillTable( $left, $lrows, $lpercent, $factor );
			}
			$lastcat = $entry['cat'];
			echo "  <tr>\n";
			echo "    <td class='catlist' width='95%' colspan='$colspan'>\n";
			echo "      <a href='index.php?category=$lastcat&user=$username'>$lastcat</a>\n";
			echo "    </td>\n";
			echo "  </tr>\n";

			$left=1;
		}

		echo "    <td class='$rowcol' width='$lpercent%'>";
		echo "<a href='$link'";
		if($newwin=="on")echo "target='_blank'";
		echo ">$name</a></td>\n";
		if($killbutton=="on"){
			echo "    <td class='$rowcol' width='20'>\n";
		    echo "      <form action='search.php' method='post'>\n";
		    echo "        <input type='hidden' name='user' value='$username'>\n";
		    echo "        <input type='hidden' name='cmd' value='remove'>\n";
		    echo "        <input type='hidden' name='file' value='$lastcat'>\n";
		    echo "        <input type='hidden' name='link' value='$link'>\n";
		    echo "        <input type='hidden' name='line' value=\"$name\">\n";
			echo "		  <input type='hidden' name='search' value='$keyword'>\n";
		    echo "        <input type='image' src='kill.gif'>\n";
		    echo "      </form>\n";
		    echo "    </td>\n";
		}

		if($left==$lrows){
		    echo "  </tr>\n";
		    $left=0;
		}

		$left+=1;
	}

	fillTable( $left, $lrows, $lpercent, $factor );

	echo"</table>\n";
} 

function fillTable( $left, $lrows, $lpercent, $factor ) {
	if($left!=1){
		for($i=$left; $i<=$lrows; $i++){
		    echo "    <td width='$lpercent%'>&nbsp;</td>\n";
		    for($j=1;$j<$factor;$j++) echo "    <td>&nbsp;</td>\n";
		}
		echo "  </tr>\n";
	}
}
?>
