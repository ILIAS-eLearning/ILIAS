<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Services/COPage/classes/class.ilPageObjectGUI.php");
require_once("./Services/Imprint/classes/class.ilImprint.php");

/**
* Class ilImprintGUI
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* 
* @ilCtrl_Calls ilImprintGUI: ilPageEditorGUI, ilEditClipboardGUI, ilMediaPoolTargetSelector
* @ilCtrl_Calls ilImprintGUI: ilPublicUserProfileGUI, ilPageObjectGUI
* 
* @ingroup ModulesImprint
*/
class ilImprintGUI extends ilPageObjectGUI
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		global $tpl;
		
		if(!ilImprint::_exists("impr", 1))
		{
			$page = new ilImprint("impr");
			$page->setId(1);
			$page->create();
		}

		// there is only 1 imprint page
		parent::__construct("impr", 1);
		
		// content style (using system defaults)
		include_once("./Services/Style/classes/class.ilObjStyleSheet.php");
		
		$tpl->setCurrentBlock("SyntaxStyle");
		$tpl->setVariable("LOCATION_SYNTAX_STYLESHEET",
			ilObjStyleSheet::getSyntaxStylePath());
		$tpl->parseCurrentBlock();
		
		$tpl->setCurrentBlock("ContentStyle");
		$tpl->setVariable("LOCATION_CONTENT_STYLESHEET",
			ilObjStyleSheet::getContentStylePath(0));
		$tpl->parseCurrentBlock();
		
		// config
		$this->setPreventHTMLUnmasking(true);
		$this->setEnabledPCTabs(true);
		$this->setEnabledMaps(false);
		$this->setEnabledInternalLinks(false);
		$this->setEnabledWikiLinks(false);						
		$this->setEnabledActivation(true);
	}
	
	/**
	 * Init internal data object
	 *
	 * @param string $a_parent_type
	 * @param int $a_id
	 * @param int $a_old_nr
	 */
	function initPageObject($a_parent_type, $a_id, $a_old_nr)
	{
		$this->setPageObject(new ilImprint("impr", 1));
	}
	
	/**
	* execute command
	*/
	function executeCommand()
	{
		global $ilCtrl, $ilLocator, $lng;
		
		if($_REQUEST["baseClass"] == "ilImprintGUI")
		{
			$this->renderFullscreen();
		}
		
		$next_class = $ilCtrl->getNextClass($this);
			
		$title = $lng->txt("adm_imprint");
		
		switch($next_class)
		{			
			case "ilpageobjectgui":
				$page_gui = new ilPageObjectGUI("impr",
					$this->getPageObject()->getId(),
					$this->getPageObject()->old_nr);
				$this->setPresentationTitle($title);
				return $ilCtrl->forwardCommand($page_gui);
				
			default:			
				$this->setPresentationTitle($title);					

				$ilLocator->addItem($title,
					$ilCtrl->getLinkTarget($this, "preview"));							
			
				return parent::executeCommand();
		}
	}
	
	function postOutputProcessing($a_output) 
	{
		global $lng;
		
		if($this->getOutputMode() == IL_PAGE_PREVIEW)
		{
			if(!$this->getPageObject()->getActive())
			{
				ilUtil::sendInfo($lng->txt("adm_imprint_inactive"));
			}
		}
		
		return $a_output;
	}
	
	protected function renderFullscreen()
	{
		global $tpl, $lng, $ilMainMenu;
		
		if(!ilImprint::isActive())
		{
			ilUtil::redirect("ilias.php?baseClass=ilPersonalDesktopGUI");
		}
		
		$tpl->getStandardTemplate();
		
		$this->setRawPageContent(true);
		$html = $this->showPage();
		
		$itpl = new ilTemplate("tpl.imprint.html", true, true, "Services/Imprint");
		$itpl->setVariable("PAGE_TITLE", $lng->txt("imprint"));
		$itpl->setVariable("IMPRINT", $html);
		unset($html);
		
		$tpl->setContent($itpl->get());
		
		$ilMainMenu->showLogoOnly(true);
		$tpl->setFrameFixedWidth(true);

		echo $tpl->show("DEFAULT", true, false);
		exit();
	}
}

?>