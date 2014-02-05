<?php

if(!isset($loguser)) $loguser="PUBLIC";

// Make sure that default values exist
if (!isset( $settings_loaded ) || ( $settings_loaded == "off" ) ) {
    $killbutton    = getSetting("killbutton", "off", $loguser);
    $newwin        = getSetting("newwin", "off", $loguser);
    $quicksearch   = getSetting("quicksearch", "off", $loguser);
    $cssfile       = getSetting("cssfile", "ebm.css", $loguser);
    $toedit        = getSetting("toedit", "off", $loguser);
    $showval       = getSetting("showval", "off", $loguser);
    $showbrowse    = getSetting("showbrowse", "off", $loguser);
    $showrss       = getSetting("showrss", "off", $loguser);
    $settings_loaded = "on";
}

// Make sure that the user is always set
if( !isset( $ebm_user ) ) $ebm_user=$loguser;

// This depends on the currently displayed content
$defcat = getSetting("defcat", "Daily", $ebm_user );
?>
