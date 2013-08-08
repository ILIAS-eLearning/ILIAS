<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once('./Modules/Portfolio/classes/class.ilObjPortfolioBaseGUI.php');

/**
 * Portfolio template view gui class 
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ilCtrl_Calls ilObjPortfolioTemplateGUI: ilPortfolioTemplatePageGUI, ilPageObjectGUI, ilNoteGUI
 * @ilCtrl_Calls ilObjPortfolioTemplateGUI: ilObjectCopyGUI, ilInfoScreenGUI, ilCommonActionDispatcherGUI
 * @ilCtrl_Calls ilObjPortfolioTemplateGUI: ilPermissionGUI, ilRepositorySearchGUI
 *
 * @ingroup ModulesPortfolio
 */
class ilObjPortfolioTemplateGUI extends ilObjPortfolioBaseGUI
{					
	public function getType()
	{
		return "prtt";
	}	
		
	public function executeCommand()
	{
		global $ilTabs, $ilNavigationHistory;
				
		$this->tpl->getStandardTemplate();

		// add entry to navigation history
		if(!$this->getCreationMode() &&
			$this->getAccessHandler()->checkAccess("read", "", $this->node_id))
		{
			$link = $this->ctrl->getLinkTargetByClass("ilrepositorygui", "frameset");				
			$ilNavigationHistory->addItem($this->node_id, $link, "prtt");
		}
		
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd("view");		

		switch($next_class)
		{			
			case 'ilportfoliotemplatepagegui':		
				$this->prepareOutput();
				$this->handlePageCall($cmd);
				break;
				
			case "ilnotegui";				
				$this->preview();				
				break;
			
			case "ilinfoscreengui":
				$this->prepareOutput();
				$this->infoScreenForward();	
				break;
			
			case "ilcommonactiondispatchergui":
				include_once("Services/Object/classes/class.ilCommonActionDispatcherGUI.php");
				$gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
				$this->ctrl->forwardCommand($gui);
				break;
			
			case "ilpermissiongui":
				$this->prepareOutput();
				$ilTabs->activateTab("id_permissions");
				include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
				$perm_gui = new ilPermissionGUI($this);
				$this->ctrl->forwardCommand($perm_gui);
				break;
			
			case "ilobjectcopygui":
				include_once "./Services/Object/classes/class.ilObjectCopyGUI.php";
				$cp = new ilObjectCopyGUI($this);
				$cp->setType("prtt");
				$this->ctrl->forwardCommand($cmd);
				break;
			
			case 'ilrepositorysearchgui':
				$this->prepareOutput();
				$ilTabs->activateTab("contributors");
				include_once('./Services/Search/classes/class.ilRepositorySearchGUI.php');
				$rep_search = new ilRepositorySearchGUI();
				$rep_search->setTitle($this->lng->txt("blog_add_contributor"));
				$rep_search->setCallback($this,'addContributor');
				$this->ctrl->setReturn($this,'contributors');				
				$this->ctrl->forwardCommand($rep_search);
				break;
			
			default:			
				$this->addHeaderAction($cmd);				
				return ilObject2GUI::executeCommand();		
		}
	}
	
	protected function addTabs()
	{		
		// will add permissions if needed
		ilObject2GUI::setTabs();			
	}
	
	//
	// PAGES
	// 
	
	protected function getPageInstance($a_page_id)
	{		
		include_once "Modules/Portfolio/classes/class.ilPortfolioTemplatePage.php";			
		$page = new ilPortfolioTemplatePage($a_page_id);
		$page->setPortfolioId($this->object->getId());
		return $page;
	}
	
	protected function getPageGUIInstance($a_page_id)
	{
		include_once("Modules/Portfolio/classes/class.ilPortfolioTemplatePageGUI.php");
		$page_gui = new ilPortfolioTemplatePageGUI(
			$this->object->getId(),
			$a_page_id, 
			0, 
			$this->object->hasPublicComments()
		);
		$page_gui->setAdditional($this->getAdditional());
		return $page_gui;
	}
	
	public function getPageGUIClassName()
	{
		return "ilportfoliotemplatepagegui";
	}
	
	
	public function _goto($a_target)
	{
		$id = explode("_", $a_target);
		
		$_GET["baseClass"] = "ilRepositoryGUI";	
		$_GET["ref_id"] = $id[0];		
		$_GET["cmd"] = "view";
	
		include("ilias.php");
		exit;
	}
}

?>