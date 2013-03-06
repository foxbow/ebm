<?php
// get the interface
require("commands.php");

if (!isset($ebm_category)) {
     header("Location: index.php");
     exit;
}

$myname="oedit.php";

$loguser=currentUser(true);

require("setter.php");
require("header.php");

if(empty($ebm_public)){
    $ebm_public="on";
}
if($ebm_public=="off"){
    $ebm_user = $loguser;
}else{
    $ebm_user = "PUBLIC";
}

echo "<h1>Edit $ebm_category</h1>\n";
echo "<hr>\n";

if(empty($ebm_cmd)){
    $ebm_cmd="show";
}
if($ebm_cmd=="remove"){
    remove($ebm_file, $ebm_link, $ebm_line );
}
if($ebm_cmd=="update"){
    update($ebm_category, $ebm_oldlink, $ebm_oldline, $ebm_newlink, $ebm_newline );
}

if($ebm_cmd=="copy"){
    if($ebm_file==""){
	echo "<a href=\"index.php?public=on\"><b>Forward to public</b></a>\n";
	echo "<hr>\n";
	echo "<h2>No public categories available!</h2>\n";
	echo "<hr>\n";
	echo "<a href=\"index.php?public=off\"><b>Back to private</b></a>\n";
	require("footer.php");
	return;
    }else{
	append($ebm_file, $ebm_link, $ebm_line);
	// Make sure to end up in the old view so we can go on
	if($ebm_public=="on"){
	    $ebm_public="off";
	    $ebm_user=$loguser;
	}else{
	    $ebm_public="on";
	    $ebm_user="PUBLIC";
	}
    }
}
$user2=$ebm_user;

// echo "public=$ebm_public<br>user=$ebm_user<br>\n";

if($ebm_cmd=="move"){
     move($ebm_category, $ebm_link, $ebm_line ,$ebm_file);
}

if($ebm_cmd=="rencat"){
    $ebm_category=renCat($ebm_category, $ebm_newcat);
}
if($ebm_cmd=="append"){
    append($ebm_category, $ebm_link, $ebm_line );
}

$encat=urlencode($ebm_category);
echo "<a href=\"index.php?category=$encat&amp;public=$ebm_public\"><b>Back to $ebm_category</b></a>\n";
echo "<hr>\n";

// Get public categories when looking at private ones
if($ebm_public=="off"){
     $ebm_user="PUBLIC";
     $pcategories=db_getCategories();
     sort($pcategories);
     $ebm_user=$user2;
// Get private categories if available, so public entries 
// can be copied too.
}else if($loguser!=""){
    $ebm_user=$loguser;
    $pcategories=db_getCategories();
    sort($pcategories);
    $ebm_user="PUBLIC";
}

// get all categories
$categories=db_getCategories();
// sort the categories
sort($categories);

/*****************************************************************************/
// Open the master Table

$actual=chop($ebm_category);
$entries = db_getEntries($actual);

echo "<center>\n";

echo "<table class=\"links\" cellpadding=\"1\" border=\"0\" width=\"95%\">\n";
echo "  <tr>\n";
if (( $ebm_public=="on" ) && ($loguser=="")){
    echo "    <td class=\"catlist\" width=\"95%\" colspan=\"2\">\n";
}else{
    echo "    <td class=\"catlist\" width=\"95%\" colspan=\"3\">\n";
}
echo "      <form action=\"$myname\" method=\"post\">\n";
echo "        <input type=\"hidden\" name=\"public\" value=\"$ebm_public\">\n";
echo "        <input type=\"hidden\" name=\"cmd\" value=\"rencat\">\n";
echo "        <input type=\"hidden\" name=\"category\" value=\"$actual\">\n";
echo "        <input type=\"text\" size=\"16\" name=\"newcat\" value=\"$actual\">\n";
echo "        <input type=\"submit\" value=\"rename\">\n";
echo "      </form>\n";
echo "    </td>\n";

echo "    <td class=\"catlist\">\n";
echo "      <form action=\"index.php\" method=\"post\">\n";
echo "        <input type=\"hidden\" name=\"public\" value=\"$ebm_public\">\n";
echo "        <input type=\"hidden\" name=\"cmd\" value=\"delete\">\n";
echo "        <input type=\"hidden\" name=\"line\" value=\"$actual\">\n";
echo "        <input type=\"image\" src=\"kill.gif\">\n";
echo "      </form>\n";
echo "    </td>\n";
echo "  </tr>\n";

$flag = true;
foreach($entries as $entry){
    $entry=chop($entry);
    $break=strpos($entry,"<>");
    $name=substr($entry,0,$break);
    $ebm_link=substr($entry,$break+2);
    if($flag){
          $rowcol="oddrow";
    }else{
          $rowcol="everow";
    }
    $flag = !$flag;
// Name and link
    echo "  <tr>\n";
    echo "    <td class=\"$rowcol\" align=\"left\">\n";
    echo "      <form action=\"$myname\" method=\"post\">\n";
    echo "        <input type=\"hidden\" name=\"public\" value=\"$ebm_public\">\n";
    echo "        <input type=\"hidden\" name=\"cmd\" value=\"update\">\n";
    echo "        <input type=\"hidden\" name=\"oldlink\" value=\"$ebm_link\">\n";
    echo "        <input type=\"hidden\" name=\"oldline\" value=\"$name\">\n";
    echo "        <input type=\"hidden\" name=\"category\" value=\"$actual\">\n";
    echo "        <input type=\"text\" size=\"60\" name=\"newline\" value=\"$name\"><br>\n";
    echo "        <input type=\"text\" size=\"60\" name=\"newlink\" value=\"$ebm_link\">\n";
    echo "        <input type=\"submit\" value=\"update\">\n";
    echo "      </form>\n";
    echo "    </td>\n";

// Move to different category
    echo "    <td class=\"$rowcol\" align=\"center\">\n";
    echo "      <form action=\"$myname\" method=\"post\">\n";
    echo "        <input type=\"hidden\" name=\"public\" value=\"$ebm_public\">\n";
    echo "        <input type=\"hidden\" name=\"cmd\" value=\"move\">\n";
    echo "        <input type=\"hidden\" name=\"link\" value=\"$ebm_link\">\n";
    echo "        <input type=\"hidden\" name=\"line\" value=\"$name\">\n";
    echo "        <input type=\"hidden\" name=\"category\" value=\"$actual\">\n";
    echo "        <select name=\"file\">\n";
    foreach($categories as $target){
          if(chop($target) != chop($actual))
             echo "          <option>$target\n";
    }
    echo "        </select>\n";
    echo "        <input type=\"submit\" value=\"move\">\n";
    echo "      </form>\n";
    echo "    </td>\n\n";

// Copy between public and private categories
    if($ebm_public=="off"){
          echo "    <td class=\"$rowcol\" align=\"center\">\n";
          echo "      <form action=\"$myname\" method=\"post\">\n";
          echo "        <input type=\"hidden\" name=\"public\" value=\"on\">\n";
          echo "        <input type=\"hidden\" name=\"cmd\" value=\"copy\">\n";
          echo "        <input type=\"hidden\" name=\"link\" value=\"$ebm_link\">\n";
          echo "        <input type=\"hidden\" name=\"line\" value=\"$name\">\n";
          echo "        <input type=\"hidden\" name=\"category\" value=\"$actual\">\n";
          echo "        <select name=\"file\">\n";
          foreach($pcategories as $target){
                echo "          <option>$target\n";
          }
          echo "        </select>\n";
          echo "        <input type=\"submit\" value=\"public\">\n";
          echo "      </form>\n";
          echo "    </td>\n\n";
    }else if( $loguser!="" ){
          echo "    <td class=\"$rowcol\" align=\"center\">\n";
          echo "      <form action=\"$myname\" method=\"post\">\n";
          echo "        <input type=\"hidden\" name=\"public\" value=\"off\">\n";
          echo "        <input type=\"hidden\" name=\"cmd\" value=\"copy\">\n";
          echo "        <input type=\"hidden\" name=\"link\" value=\"$ebm_link\">\n";
          echo "        <input type=\"hidden\" name=\"line\" value=\"$name\">\n";
          echo "        <input type=\"hidden\" name=\"category\" value=\"$actual\">\n";
          echo "        <select name=\"file\">\n";
          foreach($pcategories as $target){
                echo "          <option>$target\n";
          }
          echo "        </select>\n";
          echo "        <input type=\"submit\" value=\"private\">\n";
          echo "      </form>\n";
          echo "    </td>\n\n";
    }

    echo "    <td class=\"$rowcol\" align=\"center\">\n";
    echo "      <form action=\"$myname\" method=\"post\">\n";
    echo "        <input type=\"hidden\" name=\"public\" value=\"$ebm_public\">\n";
    echo "        <input type=\"hidden\" name=\"cmd\" value=\"remove\">\n";
    echo "        <input type=\"hidden\" name=\"file\" value=\"$actual\">\n";
    echo "        <input type=\"hidden\" name=\"link\" value=\"$ebm_link\">\n";
    echo "        <input type=\"hidden\" name=\"line\" value=\"$name\">\n";
    echo "        <input type=\"hidden\" name=\"category\" value=\"$actual\">\n";
    echo "        <input type=\"image\" src=\"kill.gif\">\n";
    echo "      </form>\n";
    echo "    </td>\n";
    echo "  </tr>\n";
}

// Close the master Table
echo"</table>\n";

echo "<br>\n";

if( ($publicadd=="on") || ($loguser!="") ){
     // New entry
     if(count($categories > 0)){
          echo "<table $bgclass=\"$newlink\" cellpadding=\"1\" border=\"0\">\n";
          echo "<tr>\n";
          echo "<form action=\"$myname\" method=\"post\">\n";
          echo "  <input type=\"hidden\" name=\"public\" value=\"$ebm_public\">\n";
          echo "  <input type=\"hidden\" name=\"cmd\" value=\"append\">\n";
	  echo "    <td><a href=\"javascript:link=document.URL;line=document.title;if(window.confirm('Add '+link+' as '+line+' to $ebm_category?')){window.location='$ebmurl/add.php?public=$ebm_public&category=$ebm_category&gotlink='+document.URL+'&gotline='+document.title;}else{window.stop();}\"";
	  echo "title=\"Pull this Bookmarklet to your toolbar for autoadding pages to $ebm_category\">";
          echo "Add URL</a></td><td><input type=\"text\" name=\"link\"></td>\n";
          echo "    <td>description:</td><td><input type=\"text\" name=\"line\"></td>\n";
          echo "    <td>in</td><td><select name=\"category\">\n";
          foreach($categories as $entry){
                $entry=chop($entry);
                if($entry!=$ebm_category){
                     echo "      <option>$entry\n";
                }else{
                     echo "      <option selected>$entry\n";
                }
          }
          echo "  </select></td>\n";
          echo "  <td><input type=\"submit\" value=\"OK\">";
	  echo "</td>\n";
          echo "</form>\n\n";
          echo"</tr>\n";
          echo "</table>\n\n";
     }
}

echo "</center>\n";

/*****************************************************************************/

echo "<hr>\n";
echo "<a href=\"index.php?category=$ebm_category&public=$ebm_public\"><b>Back to $ebm_category</b></a>\n";

require("footer.php");
?>
