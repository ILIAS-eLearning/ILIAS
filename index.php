<?php
/**
* start page of ilias 
*
* @author Peter Gabriel <pgabriel@databay.de>
* @version $Id$
*
* @package ilias
*/
require_once "include/inc.check_pear.php";
require_once "include/inc.header.php";

$start = $ilias->ini->readVariable("server", "start");
if ($start == "")
{
	$start = "login.php";
}

header("location: ".$start);
exit();
?>
