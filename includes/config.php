<?php

/*
Fiesta 2 Unnoficial Ranking
Copyright (C) 2014  HUEBR's Team

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License along
with this program; if not, write to the Free Software Foundation, Inc.,
51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*/

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
