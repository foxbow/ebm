<?php
if($motd != ""){
	 if($motd=="file")
		include("ebm.motd");
	 else
		echo "<p><b>$motd</b></p>\n";
}
echo "<HR>\n";
echo "<TABLE width=\"100%\">\n";
echo "  <TR><TD>\n";
echo "    <TABLE>\n";
echo "      <TR><TD>\n";
if($contact != ""){
	 echo "        <ADDRESS>Any further questions? Send mail to&nbsp;\n";
	 echo "          <a class='flow' href=\"mailto:$contact?subject=[easybookmarks]\">\n";
	 echo "            $contact</a>.\n";
	 echo "        </ADDRESS>\n";
}else{
	 echo "        <address>";
	 echo "EasyBookMarks (w) 2002-2009 by Bj&ouml;rn 'foxbow' Weber.";
	 echo "</address>\n";
}
echo "		</TD></TR>\n";
echo "      <TR><TD>\n";
echo "        <p style=\"font-size:x-small;\">\n";
echo "          <a class='flow' href=\"docs.html\">EBM</a>&nbsp;\n";
echo "          <a class='flow' href=\"history.php\">version $version</a>\n";
echo "		  </p>\n";
echo "      </TD></TR>\n";
echo "    </TABLE>\n";
echo "  </TD>\n";
echo "  <TD align=\"right\"><IMG src=\"foxicon.gif\" alt=\"fox\"></TD>\n";
echo "  </TR>\n";
echo "</TABLE>\n";
echo "</BODY>\n";
echo "</HTML>\n";
?>
