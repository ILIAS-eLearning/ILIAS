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
* assessment test script used to call the test objects
*
* @author Helmut Schottmüller <hschottm@tzi.de>
* @version $Id$
*
* @package assessment
*/
define("ILIAS_MODULE", "assessment");
chdir("..");
require_once "./include/inc.header.php";

// for security
unset($id);

global $tpl;
global $lng;

$lng->loadLanguageModule("assessment");
$tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
$tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");
$title = "Not yet implemented";
// catch feedback message
sendInfo();
setLocator();
$tpl->setVariable("HEADER", $title);
$tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_introduction.html", true);
$tpl->parseCurrentBlock();
$tpl->show();

	/**
	* set Locator
	*
	* @access	public
	*/
	function setLocator()
	{
		global $lng;
		
	  $ilias_locator = new ilLocatorGUI(false);
		$i = 1;
		$ilias_locator->navigate($i++, $lng->txt("personal_desktop"), ILIAS_HTTP_PATH . "/usr_personaldesktop.php", "bottom");
		$ilias_locator->navigate($i++, $lng->txt("tst_already_taken"), ILIAS_HTTP_PATH . "/assessment/tests_taken.php", "bottom");
    $ilias_locator->output();
	}

?>
