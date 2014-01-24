<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Workspace deep link handler GUI
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 * 
 * @ilCtrl_Calls ilSharedResourceGUI: ilObjBlogGUI, ilObjFileGUI, ilObjTestVerificationGUI
 * @ilCtrl_Calls ilSharedResourceGUI: ilObjExerciseVerificationGUI, ilObjLinkResourceGUI
 * @ilCtrl_Calls ilSharedResourceGUI: ilObjPortfolioGUI
 *
 * @ingroup ServicesPersonalWorkspace
 */
class ilSharedResourceGUI
{
	protected $node_id;
	protected $portfolio_id;
	protected $access_handler;	

	function __construct()
	{
		global $ilCtrl;
		
		$ilCtrl->saveParameter($this, "wsp_id");
		$ilCtrl->saveParameter($this, "prt_id");
		$this->node_id = $_GET["wsp_id"];			
		$this->portfolio_id = $_GET["prt_id"];			
	}
	
	function executeCommand()
	{
		global $ilCtrl, $tpl, $ilMainMenu, $ilLocator, $ilUser, $lng;
		
		$next_class = $ilCtrl->getNextClass($this);
		$cmd = $ilCtrl->getCmd();
		
		$tpl->getStandardTemplate();
		
		// #8509
		$ilMainMenu->setActive("desktop");
		
		// #12096
		if($ilUser->getId() != ANONYMOUS_USER_ID &&
			$next_class &&
			!in_array($next_class, array("ilobjbloggui", "ilobjportfoliogui")))
		{														
			include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceAccessHandler.php";
			include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceTree.php";			
			$tree = new ilWorkspaceTree($ilUser->getId());
			$access_handler = new ilWorkspaceAccessHandler($tree);	
			$owner_id = $tree->lookupOwner($this->node_id);						
			$obj_id = $tree->lookupObjectId($this->node_id);
			
			$lng->loadLanguageModule("wsp");

			// see ilPersonalWorkspaceGUI				
			if($owner_id != $ilUser->getId())
			{					
				$ilCtrl->setParameterByClass("ilpersonaldesktopgui", "dsh", $owner_id);
				$link = $ilCtrl->getLinkTargetByClass("ilpersonaldesktopgui", "jumptoworkspace");
				$ilLocator->addItem($lng->txt("wsp_tab_shared"), $link);		

				include_once "Services/User/classes/class.ilUserUtil.php";
				$ilLocator->addItem(ilUserUtil::getNamePresentation($owner_id), $link);					
			}
			else
			{					
				$link = $ilCtrl->getLinkTargetByClass("ilpersonaldesktopgui", "jumptoworkspace");
				$ilLocator->addItem($lng->txt("wsp_tab_personal"), $link);	
			}
			
			$link = $access_handler->getGotoLink($this->node_id, $obj_id);														
			$ilLocator->addItem(ilObject::_lookupTitle($obj_id), $link);				
			$tpl->setLocator($ilLocator);		
		}
		
		switch($next_class)
		{
			case "ilobjbloggui":
				include_once "Modules/Blog/classes/class.ilObjBlogGUI.php";
				$bgui = new ilObjBlogGUI($this->node_id, ilObject2GUI::WORKSPACE_NODE_ID);				
				$ilCtrl->forwardCommand($bgui);			
				break;
			
			case "ilobjfilegui":
				include_once "Modules/File/classes/class.ilObjFileGUI.php";
				$fgui = new ilObjFileGUI($this->node_id, ilObject2GUI::WORKSPACE_NODE_ID);
				$ilCtrl->forwardCommand($fgui);
				break;		
			
			case "ilobjtestverificationgui":
				include_once "Modules/Test/classes/class.ilObjTestVerificationGUI.php";
				$tgui = new ilObjTestVerificationGUI($this->node_id, ilObject2GUI::WORKSPACE_NODE_ID);
				$ilCtrl->forwardCommand($tgui);
				break;		
			
			case "ilobjexerciseverificationgui":
				include_once "Modules/Exercise/classes/class.ilObjExerciseVerificationGUI.php";
				$egui = new ilObjExerciseVerificationGUI($this->node_id, ilObject2GUI::WORKSPACE_NODE_ID);
				$ilCtrl->forwardCommand($egui);
				break;		
			
			case "ilobjlinkresourcegui":
				include_once "Modules/WebResource/classes/class.ilObjLinkResourceGUI.php";
				$lgui = new ilObjLinkResourceGUI($this->node_id, ilObject2GUI::WORKSPACE_NODE_ID);
				$ilCtrl->forwardCommand($lgui);
				break;		
			
			case "ilobjportfoliogui":
				include_once "Modules/Portfolio/classes/class.ilObjPortfolioGUI.php";
				$pgui = new ilObjPortfolioGUI($this->portfolio_id, ilObject2GUI::PORTFOLIO_OBJECT_ID);
				$ilCtrl->forwardCommand($pgui);
				break;	
			
			default:
				if(!$cmd)
				{
					$cmd = "process";
				}
				$this->$cmd();
		}
		
		$tpl->show();
	}
	
	protected function process()
	{
		if(!$this->node_id && !$this->portfolio_id)
		{
			exit("invalid call");
		}
			
		// if already logged in, we need to re-check for public password
		if($this->node_id)
		{
			if(!self::hasAccess($this->node_id))
			{
				exit("no permission");
			}
			$this->redirectToResource($this->node_id);	     
		}	
		else
		{
			if(!self::hasAccess($this->portfolio_id, true))
			{
				exit("no permission");
			}
			$this->redirectToResource($this->portfolio_id, true);	     
		}						
	}
	
	public static function hasAccess($a_node_id, $a_is_portfolio = false)
	{
		global $ilUser, $ilSetting;				
	
		// if we have current user - check with normal access handler
		if($ilUser->getId() != ANONYMOUS_USER_ID)
		{			
			if(!$a_is_portfolio)
			{
				include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceAccessHandler.php";
				include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceTree.php";			
				$tree = new ilWorkspaceTree($ilUser->getId());
				$access_handler = new ilWorkspaceAccessHandler($tree);
			}
			else
			{
				include_once "Modules/Portfolio/classes/class.ilPortfolioAccessHandler.php";
				$access_handler = new ilPortfolioAccessHandler();
			}			
			if($access_handler->checkAccess("read", "", $a_node_id))
			{
				return true;
			}
		}
		
		// not logged in yet or no read access
		include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceAccessGUI.php";
			
		if(!$a_is_portfolio)
		{
			include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceAccessHandler.php";	
			$shared = ilWorkspaceAccessHandler::getPermissions($a_node_id);
		}
		else
		{
			// #12059
			if (!$ilSetting->get('user_portfolios'))
			{
				return false;
			}
			
			// #12039
			include_once "Modules/Portfolio/classes/class.ilObjPortfolio.php";
			$prtf = new ilObjPortfolio($a_node_id, false);
			if(!$prtf->isOnline())
			{
				return false;
			}
						
			include_once "Modules/Portfolio/classes/class.ilPortfolioAccessHandler.php";
			$shared = ilPortfolioAccessHandler::getPermissions($a_node_id);						
		}
		
		// object is "public"
		if(in_array(ilWorkspaceAccessGUI::PERMISSION_ALL, $shared))
		{
			return true;
		}

		// password protected
		if(in_array(ilWorkspaceAccessGUI::PERMISSION_ALL_PASSWORD, $shared))
		{
			if(!$a_is_portfolio)
			{
				ilUtil::redirect("ilias.php?baseClass=ilSharedResourceGUI&cmd=passwordForm&wsp_id=".$a_node_id);
			}
			else
			{
				ilUtil::redirect("ilias.php?baseClass=ilSharedResourceGUI&cmd=passwordForm&prt_id=".$a_node_id);
			}
		}		
		
		return false;
	}
	
	protected function redirectToResource($a_node_id, $a_is_portfolio = false)
	{
		global $ilCtrl, $objDefinition;
				
		if(!$a_is_portfolio)
		{
			include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceAccessHandler.php";
			$object_data = ilWorkspaceAccessHandler::getObjectDataFromNode($a_node_id);
			if(!$object_data["obj_id"])
			{
				exit("invalid object");
			}
		}
		else
		{			
			if(!ilObject::_lookupType($a_node_id, false))
			{
				exit("invalid object");
			}
			$object_data["obj_id"] = $a_node_id;
			$object_data["type"] = "prtf";
		}
		
		$class = $objDefinition->getClassName($object_data["type"]);
		$gui = "ilobj".$class."gui";
		
		switch($object_data["type"])
		{
			case "blog":
				$ilCtrl->setParameterByClass($gui, "wsp_id", $a_node_id);
				$ilCtrl->setParameterByClass($gui, "gtp", (int)$_GET["gtp"]);
				$ilCtrl->redirectByClass($gui, "preview");
				
			case "tstv":
			case "excv":
			case "crsv":
			case "scov":
				$ilCtrl->setParameterByClass($gui, "wsp_id", $a_node_id);
				$ilCtrl->redirectByClass($gui, "deliver");
				
			case "file":
			case "webr":
				$ilCtrl->setParameterByClass($gui, "wsp_id", $a_node_id);
				$ilCtrl->redirectByClass($gui);
				
			case "prtf":
				$ilCtrl->setParameterByClass($gui, "prt_id", $a_node_id);
				$ilCtrl->setParameterByClass($gui, "gtp", (int)$_GET["gtp"]);
				if($_GET["back_url"])
				{
					$ilCtrl->setParameterByClass($gui, "back_url", rawurlencode($_GET["back_url"]));
				}
				$ilCtrl->redirectByClass($gui, "preview");
				
			default:
				exit("invalid object type");						
		}		
	}
	
	protected function passwordForm($form = null)
	{
		global $tpl, $lng;
		
		$lng->loadLanguageModule("wsp");
		
		$tpl->setTitle($lng->txt("wsp_password_protected_resource"));
		$tpl->setDescription($lng->txt("wsp_password_protected_resource_info"));
		
		if(!$form)
		{
			$form = $this->initPasswordForm();
		}
	
		$tpl->setContent($form->getHTML());		
	}
	
	protected function initPasswordForm()
	{
		global $ilCtrl, $lng, $ilUser, $ilTabs;
		
		if($this->node_id)
		{			
			include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceAccessHandler.php";
			$object_data = ilWorkspaceAccessHandler::getObjectDataFromNode($this->node_id);
		}
		else
		{
			$object_data["title"] = ilObject::_lookupTitle($this->portfolio_id);
		}
		
		include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormAction($this, "checkPassword"));
		$form->setTitle($lng->txt("wsp_password_for").": ".$object_data["title"]);
		
		$password = new ilPasswordInputGUI($lng->txt("password"), "password");
		$password->setRetype(false);
		$password->setRequired(true);
		$form->addItem($password);
		
		$form->addCommandButton("checkPassword", $lng->txt("submit"));
		
		if($ilUser->getId() && $ilUser->getId() != ANONYMOUS_USER_ID)
		{
			$ilTabs->setBackTarget($lng->txt("back"), $ilCtrl->getLinkTarget($this, "cancelPassword"));			
			$form->addCommandButton("cancelPassword", $lng->txt("cancel"));
		}
		
		return $form;
	}
	
	protected function cancelPassword()
	{
		global $ilUser;
		
		if($ilUser->getId() && $ilUser->getId() != ANONYMOUS_USER_ID)
		{		
			if($this->node_id)
			{
				include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceAccessHandler.php";
				include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceTree.php";			
				$tree = new ilWorkspaceTree($ilUser->getId());
				$owner = $tree->lookupOwner($this->node_id);
				ilUtil::redirect("ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToWorkspace&dsh=".$owner);
			}		
			else
			{
				include_once "Modules/Portfolio/classes/class.ilObjPortfolio.php";
				$prtf = new ilObjPortfolio($this->portfolio_id, false);
				$owner = $prtf->getOwner();				
				ilUtil::redirect("ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToPortfolio&dsh=".$owner);
			}
		}
	}
	
	protected function checkPassword()
	{
		global $ilDB, $lng;
		
		$lng->loadLanguageModule("wsp");
		 
		$form = $this->initPasswordForm();
		if($form->checkInput())
		{
			$input = md5($form->getInput("password"));			
			if($this->node_id)
			{
				include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceAccessHandler.php";
				$password = ilWorkspaceAccessHandler::getSharedNodePassword($this->node_id);
			}
			else
			{
				include_once "Modules/Portfolio/classes/class.ilPortfolioAccessHandler.php";
				$password = ilPortfolioAccessHandler::getSharedNodePassword($this->portfolio_id);
			}
			if($input == $password)
			{
				if($this->node_id)
				{
					ilWorkspaceAccessHandler::keepSharedSessionPassword($this->node_id, $input);		
					$this->redirectToResource($this->node_id);
				}
				else
				{
					ilPortfolioAccessHandler::keepSharedSessionPassword($this->portfolio_id, $input);		
					$this->redirectToResource($this->portfolio_id, true);
				}				
			}
			else
			{
				$item = $form->getItemByPostVar("password");
				$item->setAlert($lng->txt("wsp_invalid_password"));
				ilUtil::sendFailure($lng->txt("form_input_not_valid"));
			}						
		}		
		
		$form->setValuesByPost();
		$this->passwordForm($form);
	}
}

?>