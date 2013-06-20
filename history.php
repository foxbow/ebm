<?PHP
/** history **/
// $current$
// Removed obsolete files
// moved to GIT
// Removed generic database code, should be handled by PDO instead
// Porting to PHP5.4+
// Introducing PDO database engine
// <li>2.9.10a
// changed the update functions into actual UPDATE SQL calls
// Removing HTML entities before going into the database
// Removed deprecated eregi calls
// Fixed Search field on main page
// Fixed Layout in edit view
// Added SQLite vacuuming for databases without autovac on
// Browsing worked without authentication.. erk!
// Fixed a missing var in mysql.php
// <li>2.9.10
// Fixed a typo in SQLite when deleting categories
// Just SQLite and mySQL are currently tested
// Fixed default settings.ini
// Added a lot of config stuff
// Added linkcheck in edit page
// Reworked search page again
// Pulled version information out of commands code
// Completely reworked edit view to improve performance
// Put all stylefiles into extra dir
// Added RSS feed for category list overview
// Lots of functions on the admin page were not working (is nobody using this?)
// Added 'import' of bookmarks.html files
// Made export stick to private bookmarks
// Fixed some path issues in manual RSS link
// Added 'browse' feature to step through entries in a category, handy for comics.
// Added switch to set link to EBM in the RSS feed to either edit or normal view
// Username in search was displayed as 'PUBLIC' (thx SvOlli)
// Added quicklink bookmarklet that won't ask for the category
// <li>2.9.2
// Added restrictive robots.txt to clean local error log
// Fixed some quirks on the search page
// Improved CSS handling
// Tested on XAMPP with SQLite and mySQL
// Bookmarklet can now handle URLs with ampersands (thx SvOlli)
// Validity of cookie is an admin, not a user setting.
// Time that cookie is valid can now be set.
// Fixed that anyone could edit the basic settings without logging in.
// <li>2.9.1
// Open in new window option now affecting entry and no longer adding new button
// Edit entry in Life-Bookmark prone to be malformed
// Path to rootdir not used in rss feed so it wouldn't work if ebm was installed in a subdir
// Edit view now allows to copy public entries to private categories too
// Copying on an entry will no longer change edit view to show the target
// Login from RSS feed sets username in the form
// <li>2.9
// Code cleanup to reduce warnings further
// Added prefix to import_request to get rid of warnings
// Centralized user check
// Added RSS feeds to categories to use them as live-bookmarks in firefox
// Possibility to export database in Netscape Bookmark format
// Fixed bug that kept categories being shown in searchpage
// Noticed problems with <b>magic_quotes=on</b> double quotes will no longer be allowed in descs
// Introduced Bookmarklet to add links more easily
// With sqlite /' in a description could not be removed unlikely that anyone noticed
// <li>2.8.1
// Added session login (thanks Minuette)
// <li>2.8
// Removed checklink function to keep compiler from whining
// Added ebm_ prefix to all variables that go through http parameters
// <li>2.7 (Internal release - never went public)
// <li>2.6.2
// Added flag to make ebm jump automagically into the private section after logging in
// <li>2.6.1
// Removed stray slashes in the edit view
// Corrected forwarder to reflect proper addresses
// Brushed up the search page a little
// sqlite DB did not sort entries
// <li>2.6
// Entries are now ordered by description
// Added quicksearch for entries
// Split quicksearch into quicksearch (entries) quickdict (dict.leo.org) quickgoogle (google.com)
// <li>2.5
// Removed superflous header() directives as they made Apache choke on certain conditions
// Updated the native mySQL interface to get ebm running on strato domains
// Fixed a quirk in the table creation that arose with mySQL
// <li>2.4.1
// Introduced motd
// <li>2.4
// Introduced PEAR:DB support and cleaned up database access again
// Descriptions with " could not be edited
// <li>2.3.1
// Made categorynames changeable.
// <li>2.3
// First release on freshmeat!
// Big bug in edit page with variable name clash, making it impossible to edit an existing entry fixed.
// <li>2.2
// Added some .css files
// Made css selectable from drop down list
// New css will already be set on settings page after pressing [set].
// <li>2.1.1
// Fixed a wrong primary index in the settings table
// added nowraps to all text+button fields
// <li>2.1
// Pulled usersettings handling from commands
// Streamlined DB access a little
// Found a forgotten 'close' in database code which could explain poor performance
// Made use of CSS configurable
// user configurable options
// <li>2.0
// Made the output HTML 4.0 Transitional <b>Yay!</b>
// The generated page is CSS compliant too, happy editing...
// <li>1.6.2a
// Argh... it's possible to have something like <i>&amp;auml;</i> in a desc which will break stuff, taken care of that.
// <li>1.6.2
// Fixed bug with Titles containing an apostrophe (Thx SvOlli)
// Added the <b>[?]</b> for quick help
// <li>1.6.1
// fixed bug in db_setSetting()
// <li>1.6
// Fixed a link/desc mixup in move code
// Added mySQL support
// Froze version for new site =)
// Added basic dokumentation
// Integrate Database check, no db == Errorpage!
// Cosmetic changes to history
// Intruduced correct escape for sqlite database
// Turned ' ' to '%%20' in links
// <li>1.5
// Dropped plaintext database but offer to import it still
// settings.php is just holding the database connector
// Added Admin view for user ebm
// Put settings into database
// Introduced new user ebm/ebm as default -> admin
// Rebuilt DB access -> PostgreSQL, SQLite
// Introduced clear divide between link, description and category to clean up the interface
// <li>1.4.3b
// Code like
// <b>&lt;title&gt;<br>The Title<br>&lt;/title&gt</b>
// made appending entries barf
// <li>1.4.3a
// Aiming to pass HTML4 check...
// Added version history
// Taking care of escaped characters esp <b>\'</b>
// <li>1.4.3
// Automatic config creation
// Automatic config completition
// Made cosmetic changes for IExplorer
// Added config switches for forced login and quicksearch
// Added Leo and Google forms
// <li>1.4.2
// set <b>E_ALL</b> as default warninglevel
// Customizable contact (<b>$contact</b>)
// Custom headline is now set as title in the header
// Made login page more pretty
// Added MetaTag to keep robots from indexing the linklist.
// Added Woof's validate code (not used by now)
// Links w/o description will try to take the title of the page as desc
// Minor code cleanups
// <li>1.4.1
// Made update more easy
// Made Kill and Parkbutton to forms to get rid of telltale URIs
// <li>1.4
// Made kill, park, newwin configurable
// Killbutton reactivated
// Shortcut to add links to personal daily cat (park)
// <li>1.3.1
// Users not logged in could not add categories with <b>$publicadd="on"</b>
// Eliminated empty row showing with user not logged in
// <li>1.3
// Eliminated a bug with unknown categories showing up
// Added configurable title
// <li>1.2
// Added better configuration, put all strings into commands.php
// Added switch to enable editing w/o authentification
// <li>1.1
// Added authentification with cookies for dillo support
// <li>1.0
// Initial release
/** history **/

require("commands.php");
require("setter.php");
require("header.php");

printf("<h1>Easybookmarks version $version</h1>\n");

$inhist=false;
$source = file("history.php");
foreach($source as $line){
	 if(chop($line) == "/** history **/") $inhist = !$inhist;
	 else if (stristr( $line, "\$current\$" )){
		  printf("<h3>History</h3>\n");
		  printf("<ul><li>$version <i>Current Version</i><br>\n");
	 }
	 else if ($inhist == true ) printf( substr($line, 3)."<br>" );
}
printf("</ul>\n");

printf( "<center><a href=\"index.php\"><b>Back</b></a></center>\n" );

require("footer.php");
?>
