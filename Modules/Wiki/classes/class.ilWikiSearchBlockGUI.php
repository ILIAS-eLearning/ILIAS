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

include_once("Services/Block/classes/class.ilBlockGUI.php");

/**
* BlockGUI class for wiki searchblock
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_Is+++CalledBy ilWikiSearchBlockGUI: ilColumnGUI
*
* @ingroup ModulesWiki
*/
class ilWikiSearchBlockGUI extends ilBlockGUI
{
	static $block_type = "wikisearch";
	static $st_data;
	
	/**
	* Constructor
	*/
	function __construct()
	{
		global $ilCtrl, $lng;
		
		parent::ilBlockGUI();
		
		$lng->loadLanguageModule("wiki");
		$this->setEnableNumInfo(false);
		
		$this->setTitle($lng->txt("wiki_wiki_search"));
		$this->allow_moving = false;
	}

	/**
	* Get block type
	*
	* @return	string	Block type.
	*/
	static function getBlockType()
	{
		return self::$block_type;
	}

	/**
	* Is this a repository object
	*
	* @return	string	Block type.
	*/
	static function isRepositoryObject()
	{
		return false;
	}
	
	/**
	* Get Screen Mode for current command.
	*/
	static function getScreenMode()
	{
		return IL_SCREEN_SIDE;
	}

	/**
	* execute command
	*/
	function &executeCommand()
	{
		global $ilCtrl;

		$next_class = $ilCtrl->getNextClass();
		$cmd = $ilCtrl->getCmd("getHTML");

		switch ($next_class)
		{
			default:
				return $this->$cmd();
		}
	}

	/**
	* Get bloch HTML code.
	*/
	function getHTML()
	{
		global $ilCtrl, $lng, $ilUser;
		
		return parent::getHTML();
	}

	/**
	* Fill data section
	*/
	function fillDataSection()
	{
		global $ilCtrl, $lng, $ilAccess;
		
		$tpl = new ilTemplate("tpl.wiki_search_block.html", true, true, "Modules/Wiki");
		
		// go
		$tpl->setVariable("TXT_PERFORM", $lng->txt("wiki_search"));
		$tpl->setVariable("FORMACTION",
			$ilCtrl->getFormActionByClass("ilobjwikigui", "performSearch"));
		$tpl->setVariable("SEARCH_TERM",
			ilUtil::prepareFormOutput(ilUtil::stripSlashes($_POST["search_term"])));

		$this->setDataSection($tpl->get());
	}
}

?>
