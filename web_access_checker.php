<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

/**
* Web Access Checker
*
* Checks the access rights of a directly requested content file.
* Called from a web server redirection rule.
*
* - determines the related learning module and checks the permission
* - either delivers the accessed file (without redirect)
* - or redirects to the login screen (if not logged in)
* - or prints an error message (if too less rights)
*
*
* @author Fred Neumann <fred.neumann@fim.uni-erlangen.de>
* @version $Id$
*
*/

//define("ILIAS_MODULE", "content");
//chdir("../..");

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
