<?php
/*
 * This is the default settings file. Copy it as settings.php
 * into your data directory as defined in commands.php
 */
/**********
* PEAR:DB *
**********/
// require("db/peardb.php");
// $connectstring="mysql://ebm:ebm@localhost/ebm";

/********************
* Native DB support *
********************/
/** sqlite **/
// require("db/sqlite.php");
// $ebm_prefix="db/";

/** sqlite3 **/
require("db/PDO.php");
$ebm_prefix="db/";

/** PostgreSQL **/
// require("db/postgres.php");
// $connectstring="dbname=ebm user=ebm password=ebm";

/** MySQL **/
//require("db/mysql.php");
//$my_host="localhost";
//$my_user="ebm";
//$my_pass="ebm";
//$my_db="ebm";
?>
