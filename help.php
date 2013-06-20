<?PHP
require("header.php");
?>
<h1>EBM Quickhelp</h1>
<table cellpadding=5>
<tr><th><i>Login</i></b></th><td>Gives the chance to log in and get access to the personal links.</td></tr>
<tr><th><i>Logout</i></th><td>Logs you out of the curent session.</td></tr>
<tr><th>[*]</th><td>Creates a new category.</td></tr>
<tr><th>?</th><td>D'Oh!</td></tr>
<tr><th><img src="park.gif"></th><td>Parks the entry in your personal startcategory.</td></tr>
<tr><th><img src="kill.gif"></th><td>Deletes the current entry.</td></tr>
<tr><th>[create]</th><td>Creates a new entry.</td></tr>
<tr><th>EBM</th><td>Shows the full docs</td></tr>
<tr><th>version x.x.x</th><td>Shows the version history</td></tr>
</table>
If any of these features is not available, it's either not configured
or you need log in to access them.
<?PHP
require("commands.php");
require("footer.php");
?>
