<?PHP

$database="Pear:DB";
include("DB.php");

// Initialize the basics and create the 'root' user called 'ebm'
// with password 'ebm'
function db_initDB(){
    $cid = db_openDB();
    $res = $cid->query( "CREATE TABLE users ( name VARCHAR(16) UNIQUE, password CHAR(32) );" );
    $res = $cid->query( "INSERT INTO users VALUES ( 'ebm', '3dc70661cd5ea000608c27661b5c240b' );" );
    $res = $cid->query( "CREATE TABLE cats ( name VARCHAR(16), cat VARCHAR(32), cid INTEGER );" );
    $res = $cid->query( "CREATE TABLE links ( cid INTEGER, link VARCHAR(256), text VARCHAR(128) );" );
    $res = $cid->query( "CREATE TABLE settings ( name VARCHAR(16), value VARCHAR(32), uname VARCHAR(16), PRIMARY KEY (name, uname) );" );
    $cid->disconnect( $cid );
//  nodb();
}

/**
 * open the database.
 * If it does not exist, try to create it or at least prompt the
 * user/admin to do that.
 **/
function db_openDB(){
    global $connectstring;
    $cid = DB::connect($connectstring);
    if(DB::isError($cid)) {
//        echo"<h2>$connectstring</h2>";
//        echo"<h3>".$cid->toString()."</h3>";
        nodb();
    }
    return $cid;
}

/**
 * return a value to a given setting.
 **/
function db_getSetting( $name, $def, $uname ){
    $cid = db_openDB();
    $res = $cid->query( "SELECT value FROM settings WHERE name='$name' AND uname='ebm';" );
    if(!$res){
        $cid->disconnect( $cid );
        db_initDB();
        return db_getSetting( $name, $def, $uname );
    }
    if( $ret = $res->fetchRow() ){
        $def = db_decode($ret[0]);
    }
    $res = $cid->query( "SELECT value FROM settings WHERE name='$name' AND uname='$uname';" );
    if( $ret = $res->fetchRow() ){
        $def = db_decode($ret[0]);
    }
    $cid->disconnect();
    return $def;
}

/**
 * set a certain value in the settings.
 * If it does not exist yet, create a new one.
 **/
function db_setSetting( $name, $value, $uname ){
     $cid = db_openDB();
     $value = db_encode( $value );
     $res = $cid->query( "DELETE FROM settings WHERE name='$name' AND uname='$uname';" );
     $res = $cid->query( "INSERT INTO settings VALUES ('$name','$value','$uname');" );
     $cid->disconnect();
     return db_decode($value);
}

/**
 * get the password of the given user.
 **/
function db_getPassword( $name ){
     $cid = db_openDB();
     $res = $cid->query( "SELECT password FROM users WHERE name='$name';" );
     $val="";
     if( $ret = $res->fetchRow() ){
         $val=$ret[0];
     }
     $cid->disconnect();
     return $val;
}

/**
 * adds a new user
 **/
function db_addUser( $name, $pass ){
     $cid = db_openDB();
     $res = $cid->query( "SELECT password FROM users WHERE name='$name';" );
     if( $res->num_rows() < 1 )
        $res = $cid->query( "INSERT INTO users ( name, password ) VALUES ( '$name', '$pass' );" );
     $cid->disconnect();
}

/**
 * sets a new password
 */
function db_updateUser( $name, $pass ){
     $cid = db_openDB();
     $res = $cid->query( "UPDATE users SET password='$pass' WHERE name='$name';" );
     $cid->disconnect();
}

/**
 * deletes an user together with all his categories
 * and links.
 **/
function db_deleteUser( $user ){
    $cid = db_openDB();
    $cid->autoCommit(false);

    $res = $cid->query( "SELECT cid FROM cats WHERE name='$user';" );

    $cid->prepare( "DELETE FROM links WHERE ( cid=? );" );
    while( $row = $res->fetchRow() ){
        $res = $cid->execute( $row[0] );
    }

    $res = $cid->query( "DELETE FROM cats WHERE ( name='$user' );" );
    $res = $cid->query( "DELETE FROM users WHERE name='$user';" );

    $cid->commit();
    $cid->disconnect();
}

/**
 * returns all users
 **/
function db_getUsers(){
    $cid = db_openDB();
    $res = $cid->query( "SELECT name FROM users;" );
    $users = array();
    $i=0;
    while( $row = $res->fetchRow() ){
        $users[$i] = $row[0];
        $i++;
    }
    $cid->disconnect();
    return $users;
}

/*
 * returns an array containing all categories
 */
function db_getCategories(){
    global $user;
    $cid = db_openDB();
    $res = $cid->query( "SELECT cat FROM cats WHERE name='$user';" );
    $category = array();
    $i=0;
    while( $row = $res->fetchRow() ){
        $category[ $i ] = db_decode( $row[0] );
        $i++;
    }
    $cid->disconnect();
    return $category;
}

/**
* creates a new category
**/
function db_newCat( $cat ){
    global $user;
    $cat=db_encode($cat);
    $cid = db_openDB();
    $res = $cid->query( "SELECT count(*) FROM cats WHERE ( name='$user' AND cat='$cat' );" );
    $val = $res->fetchArray( $res );
    if( $val[0] == 0 ){
        $res = $cid->query( "SELECT MAX(cid) FROM cats;" );
        $ret = $res->fetchArray( $res );
        $val = $ret[0];
        if("$val" == "") $val = 0;
        else $val++;
        $res = $cid->query( "INSERT INTO cats ( name, cat, cid ) VALUES ( '$user', '$cat', $val );" );
    }
    $cid->disconnect();
}

function db_removeCat( $cat ){
     global $user;
     $cat = db_encode( $cat );
     $cid = db_openDB();
     $catid = db_getCatID( $cat, $cid );
     $res = $cid->query( "DELETE FROM links WHERE ( cid=$catid );" );
     $res = $cid->query( "DELETE FROM cats WHERE ( name='$user' AND cat='$cat' );" );
     $cid->disconnect();
}

function db_renCat( $cat, $ncat ){
    global $user;
    $cat = db_encode( $cat );
    $ncat = db_encode( $ncat );
    $cid = db_openDB();
    $res = $cid->query( "UPDATE cats SET cat='$ncat' WHERE ( name='$user' and cat='$cat' );" );
    $cid->disconnect();
}

function db_getCatID( $cat, $cid ){
     global $user;
     $res = $cid->query( "SELECT cid FROM cats WHERE ( name='$user' AND cat='$cat' );" );
     $catid = $res->fetchRow();
     return $catid[0];
}
/*
 * returns all entries of a category in the form
 * description<>link
 */
function db_getEntries( $cat ){
    global $user;
    global $prefix;
    $cid = db_openDB();
    $catid = db_getCatID( $cat, $cid );
    $res = $cid->query( "SELECT text, link FROM links WHERE cid=$catid ORDER BY text;" );
    $entries=array();
    $rowid=0;
    while( $row = $res->fetchRow() ){
        $desc=db_decode( $row[0] );
        $entries[ $rowid ]="$desc<>$row[1]";
        $rowid++;
    }
    $cid->disconnect();
    return $entries;
}


function db_searchEntries( $keyword, $name ){
    global $user;
    global $prefix;
    
    $cid = db_openDB();
    $res = $cid->query( "SELECT text, link FROM links WHERE lower(text) LIKE '%$keyword%' AND cid IN (SELECT cid FROM cats WHERE name='$name') ORDER BY text;" );
    $entries=array();
    $rowid=0;
    while( $row=$res->fetchRow() ){
        $desc=db_decode( $row[0] );
        $entries[ $rowid ]="$desc<>$row[1]";
        $rowid++;
    }
    $cid->disconnect();
    
    return $entries;
}

/**
 * takes an addslashed text (like we get from a a'post'
 * and turns it into something appropriate for the
 * database
 * Hopefully Pear takes care of DB encoding....
 **/
function db_encode( $text ){
     return $text;
}

/**
 * takes a database encoded string and returns is into
 * standard text format.
 * Hopefully Pear takes care of DB encoding....
 **/
function db_decode( $text ){
    return $text;
}

/**
 * append an entry
 **/
function db_appendEntry($cat, $link, $desc){
     global $user;
     $desc = db_encode( $desc );
     $cid = db_openDB();
     $catid = db_getCatID( $cat, $cid );
     $res = $cid->query( "SELECT cid FROM links WHERE ( cid=$catid AND link='$link' AND text='$desc' );" );
     if( $res->numRows() < 1 ){
          $res = $cid->query( "INSERT INTO links ( cid, link, text ) VALUES ( $catid, '$link', '$desc' );" );
     }
     $cid->disconnect();
}

/**
 * delete an entry
 **/
function db_removeEntry($cat, $link, $desc){
     global $user;
     $desc = db_encode( $desc );
     $cid = db_openDB();
     $catid = db_getCatID( $cat, $cid );
     $res = $cid->query( "DELETE FROM links WHERE ( cid=$catid AND link='$link' AND text='$desc' );" );
     $cid->disconnect();
}

/**
 * change an existing entry into a new one.
 **/
function db_updateEntry($cat, $olink, $odesc, $nlink, $ndesc){
    global $user;
    $cid = db_openDB();
    $catid = db_getCatID( $cat, $cid );
    $res = $cid->query( "UPDATE links SET link='$nlink',text='$ndesc' WHERE ( cid=$catid AND link='$olink' AND text='$odesc' );" );
    $cid->disconnect();
}

/**
 * Move an entry from one category to another
 **/
function db_moveEntry($source, $link, $desc, $target){
    global $user;
    $cid = db_openDB();
    $scatid = db_getCatID( $source, $cid );
    $dcatid = db_getCatID( $target, $cid );
    $res = $cid->query( "UPDATE links SET cid=$dcatid WHERE ( cid=$scatid AND link='$link' AND text='$desc' );" );
    $cid->disconnect();
}

?>
