<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Web Access Checker (delivery as attachment)
*
* Checks the access rights of a directly requested content file.
* Called from a web server redirection rule.
*
* - determines the related learning module and checks the permission
* - either delivers the accessed file (as HTTP attachment)
* - or prints an error message (if too less rights)
*
* @author Fred Neumann <fred.neumann@fim.uni-erlangen.de>
* @version $Id: web_access_checker.php 13944 2007-05-22 08:02:47Z akill $
*/

// Change to ILIAS main directory
chdir("../..");

// Load the checker class, which also initializes ILIAS
require_once "./Services/WebAccessChecker/classes/class.ilWebAccessChecker.php";

$checker = new ilWebAccessChecker();
$checker->setDisposition("attachment");

if ($checker->checkAccess())
{
	$checker->sendFile();
}
else
{
	$checker->sendError();
}
?>
