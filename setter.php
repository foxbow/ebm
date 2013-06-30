<?php

if(!isset($loguser)) $loguser="";

// Make sure that default values exist
if (!isset( $settings_loaded ) || ( $settings_loaded == "off" ) ) {
    $defcat        = getSetting("defcat", "Daily", $loguser);
    $allowshortcut = getSetting("allowshortcut", "off", $loguser);
    $killbutton    = getSetting("killbutton", "off", $loguser);
    $newwin        = getSetting("newwin", "off", $loguser);
    $quicksearch   = getSetting("quicksearch", "off", $loguser);
    $cssfile       = getSetting("cssfile", "ebm.css", $loguser);
    $jumptopriv    = getSetting("jumptopriv", "off", $loguser);
    $toedit        = getSetting("toedit", "off", $loguser);
    $showval       = getSetting("showval", "off", $loguser);
    $showbrowse    = getSetting("showbrowse", "off", $loguser);
    $showrss       = getSetting("showrss", "off", $loguser);
    $settings_loaded = "on";
}
?>
