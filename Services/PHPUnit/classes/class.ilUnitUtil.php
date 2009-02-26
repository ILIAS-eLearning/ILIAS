<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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

require_once 'PHPUnit/Framework.php';

/**
* Utilities for Unit Testing
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id: class.ilSetting.php 15697 2008-01-08 20:04:33Z hschottm $
*/
class ilUnitUtil
{
	function performInitialisation()
	{
		define("IL_PHPUNIT_TEST", true);
		session_id("phpunittest");
		$_SESSION = array();
		include_once("./Services/PHPUnit/config/cfg.phpunit.php");
		include_once("./include/inc.header.php");
		$ilUnitUtil = new ilUnitUtil;
		PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, array($ilUnitUtil, "errorHandler"));
	}
	
	function errorHandler($a_error_obj)
	{
		var_dump($a_error_obj);
	}
}
?>
