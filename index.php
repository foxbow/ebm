<?php
require("commands.php");
// echo "user = $ebm_user<br>\n";

$loguser=currentUser(true);

require("setter.php");

// Headers
$rows=5;
$percent=100/$rows;

$myname = "index.php";

if( "PUBLIC" == $ebm_user ) {
    echo "<h1>$title</h1>\n";
}else{
    echo "<h1>Easybookmarks for $ebm_user</h1>\n";
}

require("header.php");

// --- start ---
echo "<center>\n";

if(empty($ebm_file)){
    $ebm_file=$defcat;
}
if(empty($ebm_category)){
    $ebm_category=$ebm_file;
}
if(empty($ebm_cmd)){
    $ebm_cmd="show";
}
// check for commands
if($ebm_cmd=="append"){
    append( $ebm_category, $ebm_link, $ebm_line, $ebm_user );
}

if($ebm_cmd=="newcat"){
    newcat($ebm_category);
}
if($ebm_cmd=="remove"){
    db_removeEntry($ebm_file, $ebm_link, $ebm_line, $ebm_user );
}
if($ebm_cmd=="delete"){
    removeCat($ebm_line);
}

if(($ebm_category=="categories") || ($ebm_category=="")){
    $ebm_category=$defcat;
}

/*
* @todo: WTF?
*/
if( ( $loguser=="PUBLIC" ) && ( $publicadd=="off" ) ){
    $killbutton="off";
}
if( ( $loguser != "PUBLIC" ) && ( $ebm_category == $defcat ) ){
    $killbutton="on";  
}

// get all categories
$categories=getCategories( $ebm_user );

$incat = FALSE;
foreach( $categories as $actual ){
     if( chop( $actual ) == $ebm_category ) $incat = TRUE;
}

if(!$incat) $ebm_category="";

// sort the categories
sort($categories);

echo "<table class='header' cellpadding='1' border='0' width='98%'>\n";
/*
 * Add the view and the logout switch
 */
echo "  <tr><td class='header' width='20%'>\n";

if( $ebm_user != "PUBLIC" ){
	 echo "      <a href='$myname?user=PUBLIC'>Public links</a>\n";
}else{
	 if( $loguser=="PUBLIC" ){
		  if($ebm_category==""){
				echo "      <a href='login.php'>Login</a>\n";
		  }else{
				echo "      <a href='login.php?category=$ebm_category'><b><i>Login</i></b></a>\n";
		  }
	 }else if( $loguser=="ebm" ){
		  echo "      <a href='admin.php'>Admin</a>\n";
	 }else{
		  echo "      <a href='$myname?user=$loguser'>$loguser's links</a>\n";
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
     echo "      <input type='submit' value='search'>\n";
     echo "    </form>\n";
     echo "  </td>\n";
}
echo "  <td class='header' width='20'>\n";
echo "    <a href='admin.php'>?</a>\n";
echo "  </td>\n";
echo "</tr></table>\n";

// Open the master Table
echo "<table class='catlist' cellpadding='1' border='0' width='98%'>\n";
$left=1;

/*
 * put links to the categories into the table
 */
foreach($categories as $actual){
	if($left==1){
		echo "  <tr><td class='catlist' width='$percent%'>\n";
	}else{
		echo "  <td class='catlist' width='$percent%'>\n";
	}
	$actual=chop($actual);
	$href = $myname."?category=".urlencode($actual)."&user=".urlencode($ebm_user);
	echo "    <a href='$href'>$actual</a>\n";
     if($left!=$rows){
          echo "  </td>\n";
     }else{
          echo "  </td></tr>\n";
          $left=0;
     }
     $left+=1;
}

/*
 * Add the 'Add header' field
 */
if( ( $publicadd=="on" ) || ( $loguser != "PUBLIC" ) ) {
     if($left==1){
          echo "  <tr><td width='$percent%' valign='top'>\n";
     }else{
          echo "  <td class='catlist' style='white-space:nowrap;' width='$percent%' valign='top'>\n";
     }
     echo "    <form action='$myname' method='post'>\n";
     echo "      <input type='hidden' name='user' value='$ebm_user'>\n";
     echo "      <input type='hidden' name='cmd' value='newcat'>\n";
     echo "      <input type='text' name='category'>\n";
     echo "      <input type='submit' value='*'>\n";
     echo "    </form>\n";
     if($left!=$rows){
          echo "  </td>\n";
     }else{
          echo "  </td></tr>\n";
          $left=0;
     }

     $left+=1;
}

if($left>1){
     for($i=$left; $i<$rows+1; $i++){
          echo "  <td>&nbsp;</td>\n";
     }
     echo "</tr>\n";
}

// Close the master Table
echo"</table>\n";

echo "\n<p style='margin:20px;'></p>\n";

// The actual links
if( $ebm_category != "" ){
     $flag = true;
     $entries = getEntries( $ebm_category, $ebm_user );

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
     if($killbutton=="on"){
          $factor += 1;
     }
     $lpercent=(105-(5*$factor))/$lrows;
     $colspan = $factor*$lrows;

     echo "<table class='links' cellpadding='1' border='0' width='$lwidth%'>\n";
     echo "  <tr>\n";
     echo "    <td class='catlist' width='95%' colspan='$colspan'>\n";
     if ($showbrowse == "on")
       echo "      <a class='flow' href='ebmbrowse.php?user=$ebm_user&category=".urlencode($ebm_category)."'>Browse $ebm_category</a>\n";
     else
       echo "      $ebm_category\n";
     if ($showval == "on")
       echo "      (<a class='flow' href='checkcat.php?user=$ebm_user&category=".urlencode($ebm_category)."'>check</a>)\n";
      
     if( ( $publicadd=="on" ) || ( $loguser!="PUBLIC" ) ){
          $href = "edit.php?category=".urlencode($ebm_category)."&user=".urlencode($ebm_user);
          echo "      (<a class='flow' href='$href'>edit</a>)\n";
     }
     if($showrss == "on"){
		echo "      (<a class='flow' href='ebm2rss.php?user=$ebm_user&category=".urlencode($ebm_category)."'>rss</a>)\n";
     }
     echo "    </td>\n";
     echo "  </tr>\n";

     $left=1;
     foreach($entries as $entry){
          if($left==1){
                if($flag){
                     $rowcol="oddrow";
                }else{
                     $rowcol="everow";
                }
                $flag = !$flag;

                echo "  <tr>\n";
          }

          echo "    <td class='$rowcol' width='$lpercent%'><a href='".$entry['link']."'";
          if($newwin=="on"){
		      echo " target='_blank'";
          }
		  echo ">".$entry['desc']."</a></td>\n";
          if($killbutton=="on"){
                echo "    <td class='$rowcol' width='20'>\n";
                echo "      <form action='$myname' method='post'>\n";
                echo "        <input type='hidden' name='user' value='$ebm_user'>\n";
                echo "        <input type='hidden' name='cmd' value='remove'>\n";
                echo "        <input type='hidden' name='file' value='$ebm_category'>\n";
                echo "        <input type='hidden' name='link' value='".$entry['link']."'>\n";
                echo "        <input type='hidden' name='line' value=\"".$entry['desc']."\">\n";
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

if( ($publicadd=="on") || ($loguser!="PUBLIC") ){
     // New entry
     if(count($categories > 0)){
          echo "<table class='newlink' cellpadding='1' border='0'>\n";
          echo "<tr>\n";
          echo "<form action='$myname' method='post'>\n";
          echo "  <input type='hidden' name='user' value='$ebm_user'>\n";
          echo "  <input type='hidden' name='cmd' value='append'>\n";
	  echo "    <td><a href='javascript:link=document.URL;line=document.title;if(window.confirm('Add '+link+' as '+line+' to $ebm_category?')){window.location='$ebmurl/add.php?user=$ebm_user&category=$ebm_category&gotlink='+escape(document.URL)+'&gotline='+escape(document.title);}else{window.stop();}'";
	  echo "title='Pull this Bookmarklet to your toolbar for autoadding pages to $ebm_category'>";
          echo "Add URL</a></td><td><input type='text' name='link'></td>\n";
          echo "    <td>description:</td><td><input type='text' name='line'></td>\n";
//          echo "    <td>in</td>";
	  echo "    <td><a href='javascript:link=document.URL;line=document.title;window.location='$ebmurl/index.php?user=$ebm_user&category=$ebm_category&link='+escape(document.URL)+'&line='+escape(document.title)+'&cmd=append';'";
	  echo "title='Pull this Bookmarklet to your toolbar for quick autoadding pages to $ebm_category'>";
	  echo "in</a></td>\n";
	  echo "    <td><select name='category'>\n";
          foreach($categories as $actual){
                $actual=chop($actual);
                if($actual!=$ebm_category){
                     echo "      <option>$actual\n";
                }else{
                     echo "      <option selected>$actual\n";
                }
          }
          echo "  </select></td>\n";
          echo "  <td><input type='submit' value='OK'>";
	  echo "</td>\n";
          echo "</form>\n\n";
          echo"</tr>\n";
          echo "</table>\n\n";
     }else{
#  echo "<table cellpadding='1' border='0' bgcolor='#ddffdd'>\n";
#  echo "<tr>\n";
#  echo "<th>Log in first to add links.</th>\n";
#  echo"</tr>\n";
#  echo "</table>\n";
     }
}

echo "</center>\n";
// --- end ---

require("footer.php");
?>
