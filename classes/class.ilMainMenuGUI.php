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
* Handles display of the main menu
*
* @author Alex Killing
* @version $Id$
*
* @package ilias-core
*/
class ilMainMenuGUI
{
	/**
	* ilias object
	* @var		object ilias
	* @access	private
	*/
	var $ilias;
	var $tpl

	function ilMainMenuGUI()
	{
		global $ilias:
		$this->ilias =& $ilias;
	}

	function setTemplate(&$tpl)
	{
		$this->tpl =& $tpl;
	}

	function getTemplate()
	{
		return $this->tpl;
	}

	function createTemplate()
	{
	}

	function addMenuBlock($a_var = "CONTENT", $a_block = "navigation")
	{
		$this->tpl->addBlockFile($a_var, $a_block, "tpl.main_buttons.html");
	}

	function setTemplateVars()
	{
		$this->tpl->setVariable("IMG_DESK", ilUtil::getImagePath("navbar/desk.gif", false));
		$this->tpl->setVariable("IMG_SPACE", ilUtil::getImagePath("spacer.gif", false));
		$this->tpl->setVariable("IMG_COURSE", ilUtil::getImagePath("navbar/course.gif", false));
		$this->tpl->setVariable("IMG_MAIL", ilUtil::getImagePath("navbar/mail.gif", false));
		$this->tpl->setVariable("IMG_FORUMS", ilUtil::getImagePath("navbar/newsgr.gif", false));
		$this->tpl->setVariable("IMG_SEARCH", ilUtil::getImagePath("navbar/search.gif", false));
		$this->tpl->setVariable("IMG_LITERAT", ilUtil::getImagePath("navbar/literat.gif", false));
		$this->tpl->setVariable("IMG_GROUPS", ilUtil::getImagePath("navbar/groups.gif", false));
		$this->tpl->setVariable("IMG_ADMIN", ilUtil::getImagePath("navbar/admin.gif", false));
		$this->tpl->setVariable("IMG_HELP", ilUtil::getImagePath("navbar/help.gif", false));
		$this->tpl->setVariable("IMG_FEEDB", ilUtil::getImagePath("navbar/feedb.gif", false));
		$this->tpl->setVariable("IMG_LOGOUT", ilUtil::getImagePath("navbar/logout.gif", false));
		$this->tpl->setVariable("IMG_ILIAS", ilUtil::getImagePath("navbar/ilias.gif", false));
		$this->tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());
		$this->tpl->setVariable("JS_BUTTONS", ilUtil::getJSPath("buttons.js"));
	}
}
?>
