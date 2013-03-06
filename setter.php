<?php

if(!isset($loguser)) $loguser="";

// Make sure that default values exist
if (!isset( $settings_loaded ) || ( $settings_loaded == "off" ) ) {
    $defcat        = getSetting("defcat", "Daily", $loguser);
    $allowshortcut = getSetting("allowshortcut", "off", $loguser);
    $killbutton    = getSetting("killbutton", "off", $loguser);
    $newwin        = getSetting("newwin", "off", $loguser);
    $quicksearch   = getSetting("quicksearch", "off", $loguser);
    $quickdict     = getSetting("quickdict", "off", $loguser);
    $quickgoogle   = getSetting("quickgoogle", "off", $loguser);
    $usecss        = getSetting("usecss", "on", $loguser);
    $cssfile       = getSetting("cssfile", "ebm.css", $loguser);
    $jumptopriv    = getSetting("jumptopriv", "off", $loguser);
    $toedit        = getSetting("toedit", "off", $loguser);
    $showval       = getSetting("showval", "off", $loguser);
    $showbrowse    = getSetting("showbrowse", "off", $loguser);
    $showrss       = getSetting("showrss", "off", $loguser);
    $settings_loaded = "on";
}

if ($usecss=="on"){
    $bgclass="class";
    $newlink="newlink";
    $header="header";
    $catlist="catlist";
    $links="links";
    $oddrow="oddrow";
    $everow="everow";
}else{
    $bgclass="bgcolor";
    $newlink="#88cc88";
    $header="#cccccc";
    $catlist="#bbbbbb";
    $links="#aaaaaa";
    $oddrow="#ddddff";
    $everow="#ddffdd";
}

?>
