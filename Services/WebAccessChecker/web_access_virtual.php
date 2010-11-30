<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Web Access Checker script for delivery via virtual().
*
* Checks the access rights of a directly requested content file.
* Called from a web server redirection rule.
*
* - determines the related learning module and checks the permission
* - either delivers the accessed file (by apache)
* - or prints an error message (if too less rights)
*
* This method needs additional settings on the server:
*
* - a symbolic link in the ILIAS directory: virtual-data -> data
* - specific directory settings in Apache for data and virtual-data
*
* @author Fred Neumann <fred.neumann@fim.uni-erlangen.de>
* @version $Id: $
*/

// Change to ILIAS main directory
chdir("../..");

// Load the checker class, which also initializes ILIAS
require_once "./Services/WebAccessChecker/classes/class.ilWebAccessChecker.php";

$checker = new ilWebAccessChecker();
$checker->setDisposition("virtual");

if ($checker->checkAccess())
{
	$checker->sendFile();
}
else
{
	$checker->sendError();
}
?>
