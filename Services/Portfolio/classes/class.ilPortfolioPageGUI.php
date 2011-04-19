<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/COPage/classes/class.ilPageObjectGUI.php");
include_once("./Services/Portfolio/classes/class.ilPortfolioPage.php");

/**
 * Portfolio page gui class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ilCtrl_Calls ilPortfolioPageGUI: ilPageEditorGUI, ilEditClipboardGUI, ilMediaPoolTargetSelector
 * @ilCtrl_Calls ilPortfolioPageGUI: ilPageObjectGUI
 *
 * @ingroup ServicesPortfolio
 */
class ilPortfolioPageGUI extends ilPageObjectGUI
{
	/**
	 * Constructor
	 */
	function __construct($a_id = 0, $a_old_nr = 0)
	{
		global $tpl;
		
		parent::__construct("user", $a_id, $a_old_nr);
		
		// content style
		include_once("./Services/Style/classes/class.ilObjStyleSheet.php");
		
		$tpl->setCurrentBlock("SyntaxStyle");
		$tpl->setVariable("LOCATION_SYNTAX_STYLESHEET",
			ilObjStyleSheet::getSyntaxStylePath());
		$tpl->parseCurrentBlock();
		
		$this->setEnabledMaps(true);
		$this->setPreventHTMLUnmasking(true);
		$this->setEnabledInternalLinks(false);
		$this->setEnabledPCTabs(true);
//		$this->initPageObject($a_id, $a_old_nr);

	}

	/**
	 * Init page object
	 *
	 * @param	string	parent type
	 * @param	int		id
	 * @param	int		old nr
	 */
	function initPageObject($a_parent_type, $a_id, $a_old_nr)
	{
		$page = new ilExtPublicProfilePage($a_id, $a_old_nr);
		$this->setPageObject($page);
	}

	/**
	 * execute command
	 */
	function &executeCommand()
	{
		global $ilCtrl, $ilTabs, $ilUser;
		
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		switch($next_class)
		{				
			case "ilpageobjectgui":
				$page_gui = new ilPageObjectGUI("user",
					$this->getPageObject()->getId(), $this->getPageObject()->old_nr);
				$page_gui->setPresentationTitle($this->getPageObject()->getTitle());
				return $ilCtrl->forwardCommand($page_gui);
				
			default:				
				$this->setPresentationTitle($this->getPageObject()->getTitle());
				return parent::executeCommand();
		}
	}
	
	/**
	 * Show page
	 *
	 * @return	string	page output
	 */
	function showPage()
	{
		global $tpl, $ilCtrl;
		
		$this->setTemplateOutput(false);
		$this->setPresentationTitle($this->getPageObject()->getTitle());
		$output = parent::showPage();
		
		return $output;
	}

	/**
	 * Set all tabs
	 *
	 * @param
	 * @return
	 */
	function getTabs($a_activate = "")
	{
		global $ilTabs, $ilCtrl;

		parent::getTabs($a_activate);		
	}


} 
?>
