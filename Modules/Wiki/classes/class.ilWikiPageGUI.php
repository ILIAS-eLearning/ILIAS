<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2005 ILIAS open source, University of Cologne            |
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

include_once("./Services/COPage/classes/class.ilPageObjectGUI.php");
include_once("./Modules/Wiki/classes/class.ilWikiPage.php");

/**
* Class ilWikiPage GUI class
* 
* @author Alex Killing <alex.killing@gmx.de> 
* @version $Id$
*
* @ilCtrl_Calls ilWikiPageGUI: ilPageEditorGUI, ilEditClipboardGUI, ilMediaPoolTargetSelector
* @ilCtrl_Calls ilWikiPageGUI: ilRatingGUI, ilPublicUserProfileGUI, ilPageObjectGUI
*
* @ingroup ModulesWiki
*/
class ilWikiPageGUI extends ilPageObjectGUI
{
	/**
	* Constructor
	*/
	function __construct($a_id = 0, $a_old_nr = 0)
	{
		global $tpl;
		
		parent::__construct("wpg", $a_id, $a_old_nr);
		
		// content style
		include_once("./Services/Style/classes/class.ilObjStyleSheet.php");
		$tpl->setCurrentBlock("ContentStyle");
		$tpl->setVariable("LOCATION_CONTENT_STYLESHEET",
			ilObjStyleSheet::getContentStylePath(0));
		$tpl->parseCurrentBlock();
		
		$tpl->setCurrentBlock("SyntaxStyle");
		$tpl->setVariable("LOCATION_SYNTAX_STYLESHEET",
			ilObjStyleSheet::getSyntaxStylePath());
		$tpl->parseCurrentBlock();
		
		$this->setEnabledMaps(true);
		$this->setPreventHTMLUnmasking(true);
		$this->setEnabledInternalLinks(false);

	}
	
	function initPageObject($a_parent_type, $a_id, $a_old_nr)
	{
		$page = new ilWikiPage($a_id, $a_old_nr);
		$this->setPageObject($page);
	}

	/**
	* execute command
	*/
	function &executeCommand()
	{
		global $ilCtrl;
		
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		switch($next_class)
		{
			case "ilratinggui":
				include_once("./Services/Rating/classes/class.ilRatingGUI.php");
				$rating_gui = new ilRatingGUI();
				$rating_gui->setObject($this->getPageObject()->getParentId(), "wiki",
					$this->getPageObject()->getId(), "wpg");
				$this->ctrl->forwardCommand($rating_gui);
				$ilCtrl->redirect($this, "preview");
				break;
				
			case "ilpageobjectgui":
				$page_gui = new ilPageObjectGUI("wpg",
					$this->getPageObject()->getId(), $this->getPageObject()->old_nr);
				return $ilCtrl->forwardCommand($page_gui);
				
			default:
				return parent::executeCommand();
		}
	}

	/**
	* Set Wiki Page Object.
	*
	* @param	object	$a_wikipage	Wiki Page Object
	*/
	function setWikiPage($a_wikipage)
	{
		$this->setPageObject($a_wikipage);
	}

	/**
	* Get Wiki Page Object.
	*
	* @return	object	Wiki Page Object
	*/
	function getWikiPage()
	{
		return $this->getPageObject();
	}

	/**
	* Get wiki page gui for id and title
	*/
	static function getGUIForTitle($a_wiki_id, $a_title, $a_old_nr = 0)
	{
		global $ilDB;
		
		include_once("./Modules/Wiki/classes/class.ilWikiPage.php");
		$id = ilWikiPage::getPageIdForTitle($a_wiki_id, $a_title);
		$page_gui = new ilWikiPageGUI($id, $a_old_nr);
		
		return $page_gui;
	}
	
	function setSideBlock()
	{
		global $tpl;
		
		// side block
		include_once("./Modules/Wiki/classes/class.ilWikiSideBlockGUI.php");
		$wiki_side_block = new ilWikiSideBlockGUI();
		$wiki_side_block->setPageObject($this->getWikiPage());
		$tpl->setRightContent($wiki_side_block->getHTML());
	}

	function preview()
	{
		global $ilCtrl;
		
		$this->getWikiPage()->increaseViewCnt(); // todo: move to page object
		$this->setSideBlock();
		$wtpl = new ilTemplate("tpl.wiki_page_view_main_column.html",
			true, true, "Modules/Wiki");
		
		// rating
		if (ilObjWiki::_lookupRating($this->getPageObject()->getParentId()))
		{
			include_once("./Services/Rating/classes/class.ilRatingGUI.php");
			$rating_gui = new ilRatingGUI();
			$rating_gui->setObject($this->getPageObject()->getParentId(), "wiki",
				$this->getPageObject()->getId(), "wpg");
			$wtpl->setVariable("RATING", $ilCtrl->getHtml($rating_gui));
		}
		
		$wtpl->setVariable("PAGE", parent::preview());
		return $wtpl->get();
	}
	
	function showPage()
	{
		global $tpl, $ilCtrl;
		
		// content style
/*		include_once("./classes/class.ilObjStyleSheet.php");
		$tpl->setCurrentBlock("ContentStyle");
		$tpl->setVariable("LOCATION_CONTENT_STYLESHEET",
			ilObjStyleSheet::getContentStylePath(0));
		$tpl->parseCurrentBlock();
*/
		$this->setTemplateOutput(false);
		$this->setPresentationTitle($this->getWikiPage()->getTitle());
		$this->getWikiPage()->increaseViewCnt();
		$output = parent::showPage();
		
		return $output;
	}

	/**
	* Finalizing output processing.
	*/
	function postOutputProcessing($a_output)
	{
//echo htmlentities($a_output);
		include_once("./Modules/Wiki/classes/class.ilWikiUtil.php");
		$output = ilWikiUtil::replaceInternalLinks($a_output,
			$this->getWikiPage()->getWikiId());
		return $output;
	}
	
	/**
	* All links to a specific page
	*/
	function whatLinksHere()
	{
		global $tpl;
		
		include_once("./Modules/Wiki/classes/class.ilWikiPagesTableGUI.php");
		
		$this->setSideBlock($_GET["wpg_id"]);
		$table_gui = new ilWikiPagesTableGUI($this, "",
			$this->getWikiPage()->getWikiId(), IL_WIKI_WHAT_LINKS_HERE, $_GET["wpg_id"]);
			
		$tpl->setContent($table_gui->getHTML());
	}

} // END class.ilWikiPageGUI
?>
