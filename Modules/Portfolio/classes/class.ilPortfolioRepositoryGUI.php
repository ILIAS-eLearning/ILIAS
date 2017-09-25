<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once('./Modules/Portfolio/classes/class.ilObjPortfolio.php');		

/**
 * Portfolio repository gui class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ilCtrl_Calls ilPortfolioRepositoryGUI: ilObjPortfolioGUI, ilObjExerciseGUI
 *
 * @ingroup ModulesPortfolio
 */
class ilPortfolioRepositoryGUI 
{	
	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * @var ilObjUser
	 */
	protected $user;

	/**
	 * @var ilCtrl
	 */
	protected $ctrl;

	/**
	 * @var ilTemplate
	 */
	protected $tpl;

	/**
	 * @var ilTabsGUI
	 */
	protected $tabs;

	/**
	 * @var ilHelpGUI
	 */
	protected $help;

	/**
	 * @var ilLocatorGUI
	 */
	protected $locator;

	/**
	 * @var ilToolbarGUI
	 */
	protected $toolbar;

	/**
	 * @var ilSetting
	 */
	protected $settings;

	protected $user_id; // [int]
	protected $access_handler; // [ilPortfolioAccessHandler]
	
	public function __construct()
	{
		global $DIC;

		$this->lng = $DIC->language();
		$this->user = $DIC->user();
		$this->ctrl = $DIC->ctrl();
		$this->tpl = $DIC["tpl"];
		$this->tabs = $DIC->tabs();
		$this->help = $DIC["ilHelp"];
		$this->locator = $DIC["ilLocator"];
		$this->toolbar = $DIC->toolbar();
		$this->settings = $DIC->settings();
		$lng = $DIC->language();
		$ilUser = $DIC->user();

		$lng->loadLanguageModule("prtf");
		$lng->loadLanguageModule("user");

		include_once('./Modules/Portfolio/classes/class.ilPortfolioAccessHandler.php');
		$this->access_handler = new ilPortfolioAccessHandler();	
				
		$this->user_id = $ilUser->getId();		
	}
	
	public function executeCommand()
	{
		$ilCtrl = $this->ctrl;
		$lng = $this->lng;
		$tpl = $this->tpl;
		$ilTabs = $this->tabs;

		$next_class = $ilCtrl->getNextClass($this);
		$cmd = $ilCtrl->getCmd("show");
						
		$tpl->setTitle($lng->txt("portfolio"));
		$tpl->setTitleIcon(ilUtil::getImagePath("icon_prtf.svg"),
			$lng->txt("portfolio"));

		switch($next_class)
		{			
			case "ilobjportfoliogui":

				include_once('./Modules/Portfolio/classes/class.ilObjPortfolioGUI.php');
				$gui = new ilObjPortfolioGUI((int) $_REQUEST["prt_id"]);

				if($cmd != "preview")
				{
					$this->setLocator();

					if ((int) $_GET["exc_back_ref_id"] > 0)
					{
						include_once("./Services/Link/classes/class.ilLink.php");
						$ilTabs->setBack2Target($lng->txt("obj_exc"), ilLink::_getLink((int) $_GET["exc_back_ref_id"]));
					}
					else
					{
						$ilTabs->setBack2Target($lng->txt("prtf_tab_portfolios"), $ilCtrl->getLinkTarget($this, "show"));
					}
				}

				$ilCtrl->forwardCommand($gui);
				break;

			default:						
				$this->setLocator();
				$this->setTabs();				
				$this->$cmd();
				break;
		}

		return true;
	}
	
	public function setTabs()
	{
		$ilTabs = $this->tabs;
		$lng = $this->lng;
		$ilCtrl = $this->ctrl;
		$ilHelp = $this->help;
		
		$ilHelp->setScreenIdComponent("prtf");
		
		$ilTabs->addTab("mypf", $lng->txt("prtf_tab_portfolios"),
			$ilCtrl->getLinkTarget($this));
		
		$ilTabs->addTab("otpf", $lng->txt("prtf_tab_other_users"),
			$ilCtrl->getLinkTarget($this, "showotherFilter"));
		
		$ilTabs->activateTab("mypf");
	}

	protected function setLocator()
	{
		$ilLocator = $this->locator;
		$lng = $this->lng;
		$ilCtrl = $this->ctrl;
		$tpl = $this->tpl;
		
		$ilLocator->addItem($lng->txt("portfolio"),
			$ilCtrl->getLinkTarget($this, "show"));
		
		$tpl->setLocator();		
	}
	
	protected function checkAccess($a_permission, $a_portfolio_id = null)
	{		
		if($a_portfolio_id)
		{
			return $this->access_handler->checkAccess($a_permission, "", $a_portfolio_id);		
		}
		// currently only object-based permissions
		return true;
	}
	
	
	//
	// LIST INCL. ACTIONS
	// 	
	
	protected function show()
	{
		$tpl = $this->tpl;
		$lng = $this->lng;
		$ilToolbar = $this->toolbar;
		$ilCtrl = $this->ctrl;
		
		include_once "Services/UIComponent/Button/classes/class.ilLinkButton.php";
		$button = ilLinkButton::getInstance();
		$button->setCaption("prtf_add_portfolio");
		$button->setUrl($ilCtrl->getLinkTargetByClass("ilObjPortfolioGUI", "create"));
		$ilToolbar->addButtonInstance($button);
		
		include_once "Modules/Portfolio/classes/class.ilPortfolioTableGUI.php";
		$table = new ilPortfolioTableGUI($this, "show", $this->user_id);
		
		include_once "Services/DiskQuota/classes/class.ilDiskQuotaHandler.php";

		$tpl->setContent($table->getHTML().ilDiskQuotaHandler::getStatusLegend());
	}
		
	protected function saveTitles()
	{
		$ilCtrl = $this->ctrl;
		$lng = $this->lng;
		
		foreach($_POST["title"] as $id => $title)
		{
			if(trim($title))
			{
				if($this->checkAccess("write", $id))
				{
					$portfolio = new ilObjPortfolio($id, false);
					$portfolio->setTitle(ilUtil::stripSlashes($title));

					if(is_array($_POST["online"]) && in_array($id, $_POST["online"]))
					{
						$portfolio->setOnline(true);
					}
					else
					{
						$portfolio->setOnline(false);
					}

					$portfolio->update();
				}
			}
		}
		
		ilUtil::sendSuccess($lng->txt("saved_successfully"), true);
		$ilCtrl->redirect($this, "show");
	}
	
	protected function confirmPortfolioDeletion()
	{
		$ilCtrl = $this->ctrl;
		$tpl = $this->tpl;
		$lng = $this->lng;

		if (!is_array($_POST["prtfs"]) || count($_POST["prtfs"]) == 0)
		{
			ilUtil::sendInfo($lng->txt("no_checkbox"), true);
			$ilCtrl->redirect($this, "show");
		}
		else
		{
			include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
			$cgui = new ilConfirmationGUI();
			$cgui->setFormAction($ilCtrl->getFormAction($this));
			$cgui->setHeaderText($lng->txt("prtf_sure_delete_portfolios"));
			$cgui->setCancel($lng->txt("cancel"), "show");
			$cgui->setConfirm($lng->txt("delete"), "deletePortfolios");

			foreach ($_POST["prtfs"] as $id)
			{
				$cgui->addItem("prtfs[]", $id, ilObjPortfolio::_lookupTitle($id));
			}

			$tpl->setContent($cgui->getHTML());
		}
	}

	protected function deletePortfolios()
	{
		$lng = $this->lng;
		$ilCtrl = $this->ctrl;

		if (is_array($_POST["prtfs"]))
		{
			foreach ($_POST["prtfs"] as $id)
			{
				if($this->checkAccess("write", $id))
				{
					$portfolio = new ilObjPortfolio($id, false);
					if ($portfolio->getOwner() == $this->user_id)
					{
						$this->access_handler->removePermission($id);
						$portfolio->delete();
					}
				}
			}
		}
		ilUtil::sendSuccess($lng->txt("prtf_portfolio_deleted"), true);
		$ilCtrl->redirect($this, "show");
	}
	
	
	//
	// DEFAULT PORTFOLIO (aka profile)	
	//
	
	protected function unsetDefault()
	{
		$ilCtrl = $this->ctrl;
		$lng = $this->lng;
		$ilUser = $this->user;

		if($this->checkAccess("write"))
		{
			// #12845
			$ilUser->setPref("public_profile", "n");
			$ilUser->writePrefs();
			
			ilObjPortfolio::setUserDefault($this->user_id);
			ilUtil::sendSuccess($lng->txt("prtf_unset_default_share_info"), true);
		}
		$ilCtrl->redirect($this, "show");
	}	
	
	/**
	 * Confirm sharing when setting default
	 */
	protected function setDefaultConfirmation()
	{
		$ilCtrl = $this->ctrl;
		$lng = $this->lng;
		$tpl = $this->tpl;
		$ilTabs = $this->tabs;
		$ilSetting = $this->settings;
		
		$prtf_id = (int)$_REQUEST["prt_id"];
		
		if($prtf_id && $this->checkAccess("write"))
		{
			// if already shared, no need to ask again
			if($this->access_handler->hasRegisteredPermission($prtf_id) ||
				$this->access_handler->hasGlobalPermission($prtf_id))
			{
				return $this->setDefault($prtf_id);
			}	
			
			$ilTabs->clearTargets();
			$ilTabs->setBackTarget($lng->txt("cancel"), 
				$ilCtrl->getLinkTarget($this, "show"));

			$ilCtrl->setParameter($this, "prt_id", $prtf_id);

			// #20310
			if(!$ilSetting->get("enable_global_profiles"))
			{
				$ilCtrl->redirect($this, "setDefaultRegistered");
			}

			include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
			$cgui = new ilConfirmationGUI();
			$cgui->setFormAction($ilCtrl->getFormAction($this));
			$cgui->setHeaderText($lng->txt("prtf_set_default_publish_confirmation"));
			$cgui->setCancel($lng->txt("prtf_set_default_publish_global"), "setDefaultGlobal");
			$cgui->setConfirm($lng->txt("prtf_set_default_publish_registered"), "setDefaultRegistered");			
			$tpl->setContent($cgui->getHTML());	
			
			return;
		}
		
		$ilCtrl->redirect($this, "show");
	}
	
	protected function setDefaultGlobal()
	{
		$ilCtrl = $this->ctrl;
		
		$prtf_id = (int)$_REQUEST["prt_id"];		
		if($prtf_id && $this->checkAccess("write"))
		{
			$this->access_handler->addPermission($prtf_id, ilWorkspaceAccessGUI::PERMISSION_ALL);			
			$this->setDefault($prtf_id);
		}
		$ilCtrl->redirect($this, "show");
	}
	
	protected function setDefaultRegistered()
	{
		$ilCtrl = $this->ctrl;
		
		$prtf_id = (int)$_REQUEST["prt_id"];		
		if($prtf_id && $this->checkAccess("write"))
		{
			$this->access_handler->addPermission($prtf_id, ilWorkspaceAccessGUI::PERMISSION_REGISTERED);			
			$this->setDefault($prtf_id);
		}
		$ilCtrl->redirect($this, "show");
	}
	
	protected function setDefault($a_prtf_id)
	{
		$ilCtrl = $this->ctrl;
		$lng = $this->lng;
		$ilUser = $this->user;

		if($a_prtf_id && $this->checkAccess("write"))
		{
			// #12845
			if($this->access_handler->hasGlobalPermission($a_prtf_id))
			{
				$ilUser->setPref("public_profile", "g");
				$ilUser->writePrefs();
			}
			else if($this->access_handler->hasRegisteredPermission($a_prtf_id))
			{
				$ilUser->setPref("public_profile", "y");
				$ilUser->writePrefs();
			}
			else
			{
				return;
			}			
			ilObjPortfolio::setUserDefault($this->user_id, $a_prtf_id);
			ilUtil::sendSuccess($lng->txt("settings_saved"), true);
		}
		$ilCtrl->redirect($this, "show");
	}

	
	//
	// SHARE
	// 
		
	protected function showOtherFilter()
	{
		$this->showOther(false);
	}
	
	protected function showOther($a_load_data = true)
	{		
		$tpl = $this->tpl;
		$ilTabs = $this->tabs;
		
		$ilTabs->activateTab("otpf");
		
		include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceShareTableGUI.php";
		$tbl = new ilWorkspaceShareTableGUI($this, "showOther", $this->access_handler, null, $a_load_data);		
		$tpl->setContent($tbl->getHTML());
	}
	
	protected function applyShareFilter()
	{
		include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceShareTableGUI.php";
		$tbl = new ilWorkspaceShareTableGUI($this, "showOther", $this->access_handler);		
		$tbl->resetOffset();
		$tbl->writeFilterToSession();
		
		$this->showOther();
	}
	
	protected function resetShareFilter()
	{
		include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceShareTableGUI.php";
		$tbl = new ilWorkspaceShareTableGUI($this, "showOther", $this->access_handler);		
		$tbl->resetOffset();
		$tbl->resetFilter();
		
		$this->showOther();
	}	
}

?>