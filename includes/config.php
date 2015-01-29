<?php


$host   =   "localhost";
$user   =   "f2rank";
$pass   =   "f2rank";
$db     =   "f2rank";

$lensave = 236616;
$lenrank = 65560;
$ucssizelimit = 256 * 1024; //  256KB

$tempdirname = "/tmp";
$logdirname = "/tmp";

$backupdir = "/var/www/f2rank/pfbackups";
$fbstuff    =   "/var/www/f2rank/fbstuff_t";

$siteurl    =   "http://localhost/f2rank/";
$tpldir     =   "/var/www/f2rank/tpl/";

define("TPLDIR", $tpldir);

$version = "V2.0c (BETA)";
$updated = "04/06/2014";


$fbappid    =   "";
$fbappsec   =   "";
$fbadminid  =   "";
$fbpostpage =   '';

$blacklist = array(
    "SKYFLOW",
    //"TROLLOLO",
    //"ELENIC",
    //"SKYOYUMI"
    );

function exit_with_error_msg($prefix, $error, $suffix) {
    exit(
     $prefix .
     $error .
     $suffix
     );
}

function GetTPL($name)  {
    return file_get_contents(TPLDIR.$name.".html");
}
