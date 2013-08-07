<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once('./Modules/Portfolio/classes/class.ilObjPortfolioGUI.php');

/**
 * Portfolio template view gui class 
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ilCtrl_Calls ilObjPortfolioTemplateGUI: ilPortfolioPageGUI, ilPageObjectGUI, ilNoteGUI
 * @ilCtrl_Calls ilObjPortfolioTemplateGUI: ilObjectCopyGUI, ilInfoScreenGUI, ilCommonActionDispatcherGUI
 * @ilCtrl_Calls ilObjPortfolioTemplateGUI: ilPermissionGUI, ilRepositorySearchGUI
 *
 * @ingroup ModulesPortfolio
 */
class ilObjPortfolioTemplateGUI extends ilObjPortfolioGUI
{	
	public function executeCommand()
	{
		global $ilCtrl, $ilTabs, $lng, $tpl, $ilNavigationHistory;

		$next_class = $ilCtrl->getNextClass($this);
		$cmd = $ilCtrl->getCmd("view");
		
		$lng->loadLanguageModule("user");

		$tpl->getStandardTemplate();

		// add entry to navigation history
		if(!$this->getCreationMode() &&
			$this->getAccessHandler()->checkAccess("read", "", $this->node_id))
		{
			$link = $ilCtrl->getLinkTargetByClass("ilrepositorygui", "frameset");				
			$ilNavigationHistory->addItem($this->node_id, $link, "prtt");
		}

		switch($next_class)
		{			
			case 'ilportfoliopagegui':														
				$ilTabs->clearTargets();
				$ilTabs->setBackTarget($lng->txt("back"),
					$ilCtrl->getLinkTarget($this, "view"));
				
				// edit
				if(isset($_REQUEST["ppage"]) && $this->checkPermissionBool("write"))
				{
					$this->addLocator($_REQUEST["ppage"]);
					
					$page_id = $_REQUEST["ppage"];
					$ilCtrl->setParameter($this, "ppage", $_REQUEST["ppage"]);
				}
				// preview
				else
				{
					$page_id = $_REQUEST["user_page"];
					$ilCtrl->setParameter($this, "user_page", $_REQUEST["user_page"]);
				}
				
				include_once("Modules/Portfolio/classes/class.ilPortfolioPageGUI.php");
				$page_gui = new ilPortfolioPageGUI($this->object->getId(),
					$page_id, 0, $this->object->hasPublicComments());
				$page_gui->setAdditional($this->getAdditional());
				
				$ret = $ilCtrl->forwardCommand($page_gui);
				
				if ($ret != "" && $ret !== true)
				{						
					// preview (fullscreen)
					if(isset($_REQUEST["user_page"]))
					{						
						// suppress (portfolio) notes for blog postings 
						$this->preview(false, $ret, ($cmd != "previewEmbedded"));
					}
					// edit
					else
					{
						$tpl->setContent($ret);
					}
				}
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
				$ret =& $this->ctrl->forwardCommand($rep_search);
				break;
			
			default:			
				$this->addHeaderAction($cmd);				
				return ilObject2GUI::executeCommand();		
		}
	}
	
	public function getType()
	{
		return "prtt";
	}	
}

?>