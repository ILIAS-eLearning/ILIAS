<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/COPage/classes/class.ilPageObjectGUI.php");
include_once("./Modules/MediaPool/classes/class.ilMediaPoolPage.php");

/**
* Class ilMediaPoolPage GUI class
* 
* @author Alex Killing <alex.killing@gmx.de> 
* @version $Id$
*
* @ilCtrl_Calls ilMediaPoolPageGUI: ilPageEditorGUI, ilEditClipboardGUI, ilMediaPoolTargetSelector
* @ilCtrl_Calls ilMediaPoolPageGUI: ilPageObjectGUI
*
* @ingroup ModulesMediaPool
*/
class ilMediaPoolPageGUI extends ilPageObjectGUI
{
	/**
	* Constructor
	*/
	function __construct($a_id = 0, $a_old_nr = 0)
	{
		global $tpl;
		
		parent::__construct("mep", $a_id, $a_old_nr);
		
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
		
		$this->setEnabledMaps(false);
		$this->setPreventHTMLUnmasking(false);
		$this->setEnabledInternalLinks(false);
		$this->setEnabledWikiLinks(false);

	}
	
	function initPageObject($a_parent_type, $a_id, $a_old_nr)
	{
		$page = new ilMediaPoolPage($a_id, $a_old_nr);
		$this->setPageObject($page);
	}

	/**
	* execute command
	*/
	function &executeCommand()
	{
		global $ilCtrl, $ilTabs;
		
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		switch($next_class)
		{				
			case "ilpageobjectgui":
				$page_gui = new ilPageObjectGUI("wpg",
					$this->getPageObject()->getId(), $this->getPageObject()->old_nr);
				return $ilCtrl->forwardCommand($page_gui);
				
			default:
				return parent::executeCommand();
		}
	}

	/**
	* Set Media Pool Page Object.
	*
	* @param	object	$a_media_pool_page	Media Pool Page Object
	*/
	function setMediaPoolPage($a_media_pool_page)
	{
		$this->setPageObject($a_media_pool_page);
	}

	/**
	* Get Media Pool Page Object.
	*
	* @return	object	Media Pool Page Object
	*/
	function getMediaPoolPage()
	{
		return $this->getPageObject();
	}

	/**
	* Get media pool page gui for id and title
	*/
	static function getGUIForTitle($a_media_pool_id, $a_title, $a_old_nr = 0)
	{
		global $ilDB;
		
		include_once("./Modules/MediaPool/classes/class.ilMediaPoolPage.php");
		$id = ilMediaPoolPage::getPageIdForTitle($a_media_pool_id, $a_title);
		$page_gui = new ilMediaPoolPageGUI($id, $a_old_nr);
		
		return $page_gui;
	}
	
	/**
	* View media pool page.
	*/
	function preview()
	{
		global $ilCtrl, $ilAccess, $lng;
		
		return parent::preview();
	}
	
	function showPage()
	{
		global $tpl, $ilCtrl;
		
		$this->setTemplateOutput(false);
		$this->setPresentationTitle($this->getMediaPoolPage()->getTitle());
		$output = parent::showPage();
		
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
		$table_gui = new ilWikiPagesTableGUI($this, "whatLinksHere",
			$this->getWikiPage()->getWikiId(), IL_WIKI_WHAT_LINKS_HERE, $_GET["wpg_id"]);
			
		$tpl->setContent($table_gui->getHTML());
	}

	function getTabs($a_activate = "")
	{
		global $ilTabs, $ilCtrl;

		parent::getTabs($a_activate);		
	}

}
?>
