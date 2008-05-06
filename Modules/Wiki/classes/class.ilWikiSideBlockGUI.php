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
* BlockGUI class for wiki sideblock
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_IsCalledBy ilNewsForContextBlockGUI: ilColumnGUI
*
* @ingroup ModulesWiki
*/
class ilWikiSideBlockGUI extends ilBlockGUI
{
	static $block_type = "wikiside";
	static $st_data;
	
	/**
	* Constructor
	*/
	function __construct()
	{
		global $ilCtrl, $lng;
		
		parent::ilBlockGUI();
		
		//$this->setImage(ilUtil::getImagePath("icon_news_s.gif"));

		$lng->loadLanguageModule("wiki");
		//$this->setBlockId(...);
		/*$this->setLimit(5);
		$this->setAvailableDetailLevels(3);*/
		$this->setEnableNumInfo(false);
		
		$this->setTitle($lng->txt("wiki_navigation"));
		//$this->setRowTemplate("tpl.block_row_news_for_context.html", "Services/News");
		//$this->setData($data);
		$this->allow_moving = false;
		//$this->handleView();
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
	* Set Page Object
	*
	* @param	int	$a_pageob	Page Object
	*/
	function setPageObject($a_pageob)
	{
		$this->pageob = $a_pageob;
	}

	/**
	* Get Page Object
	*
	* @return	int	Page Object
	*/
	function getPageObject()
	{
		return $this->pageob;
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
		global $ilCtrl, $lng;
		
		$tpl = new ilTemplate("tpl.wiki_side_block_content.html", true, true, "Modules/Wiki");
		
		$wp = $this->getPageObject();
		
		if (is_object($wp))
		{
			// what links here
			$ilCtrl->setParameterByClass("ilobjwikigui", "wpg_id", $wp->getId());
			$tpl->setCurrentBlock("what_links_here");
			$tpl->setVariable("HREF_WHAT_LINKS_HERE",
				$ilCtrl->getLinkTargetByClass("ilobjwikigui", "whatLinksHere"));
			$tpl->setVariable("TXT_WHAT_LINKS_HERE", $lng->txt("wiki_what_links_here"));
			$tpl->parseCurrentBlock();

			// print view
			$tpl->setCurrentBlock("print_view");
			$tpl->setVariable("HREF_PRINT_VIEW",
				$ilCtrl->getLinkTargetByClass("ilobjwikigui", "printView"));
			$tpl->setVariable("TXT_PRINT_VIEW", $lng->txt("wiki_print_view"));
			$tpl->parseCurrentBlock();

		}
		
		// start page
		$tpl->setVariable("HREF_START_PAGE",
			$ilCtrl->getLinkTargetByClass("ilobjwikigui", "gotoStartPage"));
		$tpl->setVariable("TXT_START_PAGE", $lng->txt("wiki_start_page"));

		// recent changes
		$tpl->setVariable("HREF_RECENT_CHANGES",
			$ilCtrl->getLinkTargetByClass("ilobjwikigui", "recentChanges"));
		$tpl->setVariable("TXT_RECENT_CHANGES", $lng->txt("wiki_recent_changes"));

		// random page
		$tpl->setVariable("HREF_RANDOM_PAGE",
			$ilCtrl->getLinkTargetByClass("ilobjwikigui", "randomPage"));
		$tpl->setVariable("TXT_RANDOM_PAGE", $lng->txt("wiki_random_page"));

		// all pages
		$tpl->setVariable("HREF_ALL_PAGES",
			$ilCtrl->getLinkTargetByClass("ilobjwikigui", "allPages"));
		$tpl->setVariable("TXT_ALL_PAGES", $lng->txt("wiki_all_pages"));

		// new pages
		$tpl->setVariable("HREF_NEW_PAGES",
			$ilCtrl->getLinkTargetByClass("ilobjwikigui", "newPages"));
		$tpl->setVariable("TXT_NEW_PAGES", $lng->txt("wiki_new_pages"));

		// popular pages
		$tpl->setVariable("HREF_POPULAR_PAGES",
			$ilCtrl->getLinkTargetByClass("ilobjwikigui", "popularPages"));
		$tpl->setVariable("TXT_POPULAR_PAGES", $lng->txt("wiki_popular_pages"));

		// orphaned pages
		$tpl->setVariable("HREF_ORPHANED_PAGES",
			$ilCtrl->getLinkTargetByClass("ilobjwikigui", "orphanedPages"));
		$tpl->setVariable("TXT_ORPHANED_PAGES", $lng->txt("wiki_orphaned_pages"));

		$this->setDataSection($tpl->get());
	}
}

?>
