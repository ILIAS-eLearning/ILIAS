<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Web Access Checker
*
* Checks the access rights of a directly requested content file.
* Called from a web server redirection rule.
*
* - determines the related learning module and checks the permission
* - either delivers the accessed file (without redirect)
* - or prints an error message (if too less rights)
*
* @author Fred Neumann <fred.neumann@fim.uni-erlangen.de>
* @version $Id$
*/

// Change to ILIAS main directory
chdir("../..");

// Load the checker class, which also initializes ILIAS
require_once "./Services/WebAccessChecker/classes/class.ilWebAccessChecker.php";

$checker = new ilWebAccessChecker();

if ($checker->checkAccess())
{
	$checker->sendFile();
}
else
{
	$checker->sendError();
}
?>
