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
	var $tpl;
	var $target;
	var $start_template;


	/**
	* @param	string		$a_target				target frame
	* @param	boolean		$a_use_start_template	true means: target scripts should
	*												be called through start template
	*/
	function ilMainMenuGUI($a_target = "_top", $a_use_start_template = false)
	{
		global $ilias;

		$this->ilias =& $ilias;
		$this->target = $a_target;
		$this->start_template = $a_use_start_template;

	}

	/**
	* set output template
	*/
	function setTemplate(&$tpl)
	{
		$this->tpl =& $tpl;
	}

	/**
	* get output template
	*/
	function getTemplate()
	{
		return $this->tpl;
	}

	/**
	* add menu template as block
	*/
	function addMenuBlock($a_var = "CONTENT", $a_block = "navigation")
	{
		$this->tpl->addBlockFile($a_var, $a_block, "tpl.main_buttons.html");
	}

	/**
	* set all template variables (images, scripts, target frames, ...)
	*/
	function setTemplateVars()
	{
		global $rbacsystem, $lng, $ilias;

		// administration button
		if ($rbacsystem->checkAccess("visible", ROOT_FOLDER_ID))
		{
			$this->tpl->setCurrentBlock("userisadmin");
			$this->tpl->setVariable("IMG_ADMIN2", ilUtil::getImagePath("navbar/admin.gif", false));
			$this->tpl->setVariable("IMG_SPACE2", ilUtil::getImagePath("spacer.gif", false));
			$this->tpl->setVariable("TXT_ADMINISTRATION", $lng->txt("administration"));
			$this->tpl->setVariable("SCRIPT_ADMIN", $this->getScriptTarget("adm_index.php"));
			$this->tpl->setVariable("TARGET2", $this->target);
			$this->tpl->parseCurrentBlock();
		}

		$var2image = array( "IMG_DESK" => "navbar/desk.gif",
							"IMG_DESK_O" => "navbar/desk_o.gif",
							"IMG_SPACE" => "spacer.gif",
							"IMG_COURSE" => "navbar/course.gif",
							"IMG_COURSE_O" => "navbar/course_o.gif",
							"IMG_MAIL" => "navbar/mail.gif",
							"IMG_MAIL_O" => "navbar/mail_o.gif",
							"IMG_FORUMS" => "navbar/newsgr.gif",
							"IMG_FORUMS_O" => "navbar/newsgr_o.gif",
							"IMG_SEARCH" => "navbar/search.gif",
							"IMG_SEARCH_O" => "navbar/search_o.gif",
							"IMG_LITERAT" => "navbar/literat.gif",
							"IMG_LITERAT_O" => "navbar/literat_o.gif",
							"IMG_GROUPS" => "navbar/groups.gif",
							"IMG_GROUPS_O" => "navbar/groups_o.gif",
							"IMG_HELP" => "navbar/help.gif",
							"IMG_HELP_O" => "navbar/help_o.gif",
							"IMG_FEEDB" => "navbar/feedb.gif",
							"IMG_FEEDB_O" => "navbar/feedb_o.gif",
							"IMG_ADMIN" => "navbar/admin.gif",
							"IMG_ADMIN_O" => "navbar/admin_o.gif",
							"IMG_LOGOUT" => "navbar/logout.gif",
							"IMG_LOGOUT_O" => "navbar/logout_o.gif",
							"IMG_ILIAS" => "navbar/ilias.gif");
		foreach ($var2image as $var => $im)
		{
			$this->tpl->setVariable($var, ilUtil::getImagePath($im, false));
		}
		$this->tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());
		$this->tpl->setVariable("JS_BUTTONS", ilUtil::getJSPath("buttons.js"));

		// set target scripts
		$scripts = array (	"SCRIPT_DESK" => "usr_personaldesktop.php",
							"SCRIPT_COURSE" => "lo_list.php",
							"SCRIPT_SEARCH" => "search.php",
							"SCRIPT_LITERAT" => "literature.php",
							"SCRIPT_MAIL" => "mail_frameset.php",
							"SCRIPT_FORUMS" => "forums.php",
							"SCRIPT_FEEDB" => "feedback.php",
							"SCRIPT_LOGOUT" => "logout.php" );
		foreach($scripts as $var => $script)
		{
			$this->tpl->setVariable($var, $this->getScriptTarget($script));
		}

		// set tooltip texts
		$this->tpl->setVariable("TXT_PERSONAL_DESKTOP", $lng->txt("personal_desktop"));
		$this->tpl->setVariable("TXT_LO_OVERVIEW", $lng->txt("lo_overview"));
		$this->tpl->setVariable("TXT_BOOKMARKS", $lng->txt("bookmarks"));
		$this->tpl->setVariable("TXT_SEARCH", $lng->txt("search"));
		$this->tpl->setVariable("TXT_LITERATURE", $lng->txt("literature"));
		$this->tpl->setVariable("TXT_MAIL", $lng->txt("mail"));
		$this->tpl->setVariable("TXT_FORUMS", $lng->txt("forums"));
		$this->tpl->setVariable("TXT_GROUPS", $lng->txt("groups"));
		$this->tpl->setVariable("TXT_HELP", $lng->txt("help"));
		$this->tpl->setVariable("TXT_FEEDBACK", $lng->txt("feedback"));
		$this->tpl->setVariable("TXT_LOGOUT", $lng->txt("logout"));

		// set target frame
		$this->tpl->setVariable("TARGET", $this->target);

		$this->tpl->parseCurrentBlock();
	}

	/**
	* generates complete script target (private)
	*/
	function getScriptTarget($a_script)
	{
		global $ilias;

		$script = "./".$a_script;

		if ($this->start_template == true)
		{
			if(is_file("./templates/".$ilias->account->skin."/tpl.start.html"))
			{
				$script = "./start.php?script=".rawurlencode($script);
			}
		}
		if (defined("ILIAS_MODULE"))
		{
			$script = ".".$script;
		}
		return $script;
	}
}
?>
