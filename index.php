<?php

/**
* start page of ilias
* @author Peter Gabriel <pgabriel@databay.de>
*/
include_once "include/ilias_header.inc";
$start = $ilias->ini->readVariable("server", "start");

if ($start == "")
{
	$start = "login.php";
}
header("location: ".$start);

?>