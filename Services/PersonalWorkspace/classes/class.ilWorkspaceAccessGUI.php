<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * ACL access handler GUI
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 * 
 * @ilCtrl_Calls ilWorkspaceAccessGUI: ilMailSearchCoursesGUI, ilMailSearchGroupsGUI
 * @ilCtrl_Calls ilWorkspaceAccessGUI: ilMailSearchGUI, ilPublicUserProfileGUI
 *
 * @ingroup ServicesPersonalWorkspace
 */
class ilWorkspaceAccessGUI
{
	protected $ctrl;
	protected $lng;
	protected $node_id;
	protected $access_handler;	
	protected $footer;	
	
	const PERMISSION_REGISTERED = -1;
	const PERMISSION_ALL_PASSWORD = -3;
	const PERMISSION_ALL = -5;
	
	
	function __construct($a_node_id, $a_access_handler, $a_is_portfolio = false, $a_footer = null)
	{
		global $ilCtrl, $lng;
		
		$this->ctrl = $ilCtrl;
		$this->lng = $lng;
		$this->node_id = $a_node_id;
		$this->access_handler = $a_access_handler;		
		$this->is_portfolio = (bool)$a_is_portfolio;
		$this->footer = $a_footer;		
	}
	
	function executeCommand()
	{
		global $ilTabs, $tpl;

		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		switch($next_class)
		{
			case "ilmailsearchcoursesgui";							
				$ilTabs->setBackTarget($this->lng->txt("back"),
					$this->ctrl->getLinkTarget($this, "share"));
				include_once('Services/Contact/classes/class.ilMailSearchCoursesGUI.php');
				$csearch = new ilMailSearchCoursesGUI($this->access_handler, $this->node_id);
				$this->ctrl->setReturn($this, 'share');
				$this->ctrl->forwardCommand($csearch);				
				
				$this->setObjectTitle();
				break;
			
			case "ilmailsearchgroupsgui";			
				$ilTabs->setBackTarget($this->lng->txt("back"),
					$this->ctrl->getLinkTarget($this, "share"));
				include_once('Services/Contact/classes/class.ilMailSearchGroupsGUI.php');
				$gsearch = new ilMailSearchGroupsGUI($this->access_handler, $this->node_id);
				$this->ctrl->setReturn($this, 'share');
				$this->ctrl->forwardCommand($gsearch);
				
				$this->setObjectTitle();
				break;
			
			case "ilmailsearchgui";			
				$ilTabs->setBackTarget($this->lng->txt("back"),
					$this->ctrl->getLinkTarget($this, "share"));
				include_once('Services/Contact/classes/class.ilMailSearchGUI.php');
				$usearch = new ilMailSearchGUI($this->access_handler, $this->node_id);
				$this->ctrl->setReturn($this, 'share');
				$this->ctrl->forwardCommand($usearch);
				
				$this->setObjectTitle();
				break;
			
			case "ilpublicuserprofilegui";				
				$ilTabs->clearTargets();
				$ilTabs->setBackTarget($this->lng->txt("back"),
					$this->ctrl->getLinkTarget($this, "share"));	
				
				include_once('./Services/User/classes/class.ilPublicUserProfileGUI.php');
				$prof = new ilPublicUserProfileGUI($_REQUEST["user"]);
				$prof->setBackUrl($this->ctrl->getLinkTarget($this, "share"));
				$tpl->setContent($prof->getHTML());
				break;

			default:
				// $this->prepareOutput(); 
				if(!$cmd)
				{
					$cmd = "share";
				}
				return $this->$cmd();
		}

		return true;
	}
	
	/**
	 * restore object title
	 * 
	 * @return string
	 */
	protected function setObjectTitle()
	{
		global $tpl;
		
		if(!$this->is_portfolio)
		{
			$obj_id = $this->access_handler->getTree()->lookupObjectId($this->node_id);
		}
		else
		{
			$obj_id = $this->node_id;
		}
		$tpl->setTitle(ilObject::_lookupTitle($obj_id));
	}
	
	protected function getAccessHandler()
	{
		return $this->access_handler;
	}
	
	protected function share()
	{
		global $ilToolbar, $tpl, $ilUser, $ilSetting;
		
		$options = array();
		$options["user"] = $this->lng->txt("wsp_set_permission_single_user");
		
		include_once 'Modules/Group/classes/class.ilGroupParticipants.php';
		$grp_ids = ilGroupParticipants::_getMembershipByType($ilUser->getId(), 'grp');
		if(sizeof($grp_ids))
		{			
			$options["group"] = $this->lng->txt("wsp_set_permission_group");
		}
		
		include_once 'Modules/Course/classes/class.ilCourseParticipants.php';
		$crs_ids = ilCourseParticipants::_getMembershipByType($ilUser->getId(), 'crs');
		if(sizeof($crs_ids))
		{
			$options["course"] = $this->lng->txt("wsp_set_permission_course");
		}
		
		if(!$this->getAccessHandler()->hasRegisteredPermission($this->node_id))
		{
			$options["registered"] = $this->lng->txt("wsp_set_permission_registered");
		}
		
		if($ilSetting->get("enable_global_profiles"))
		{			
			if(!$this->getAccessHandler()->hasGlobalPasswordPermission($this->node_id))
			{
				$options["password"] = $this->lng->txt("wsp_set_permission_all_password");
			}

			if(!$this->getAccessHandler()->hasGlobalPermission($this->node_id))
			{
				$options["all"] = $this->lng->txt("wsp_set_permission_all");		
			}
		}
		
		include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
		$actions = new ilSelectInputGUI("", "action");		
		$actions->setOptions($options);		
		$ilToolbar->addInputItem($actions);
		
		$ilToolbar->setFormAction($this->ctrl->getFormAction($this));		
		
		include_once "Services/UIComponent/Button/classes/class.ilSubmitButton.php";
		$button = ilSubmitButton::getInstance();
		$button->setCaption("add");
		$button->setCommand("addpermissionhandler");
		$ilToolbar->addButtonInstance($button);
	
		include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceAccessTableGUI.php";
		$table = new ilWorkspaceAccessTableGUI($this, "share", $this->node_id, $this->getAccessHandler());
		$tpl->setContent($table->getHTML().$this->footer);
	}
	
	public function addPermissionHandler()
	{
		switch($_REQUEST["action"])
		{
			case "user":
				$this->ctrl->setParameterByClass("ilmailsearchgui", "ref", "wsp");
				$this->ctrl->redirectByClass("ilmailsearchgui");
			
			case "group":
				$this->ctrl->setParameterByClass("ilmailsearchgroupsgui", "ref", "wsp");
				$this->ctrl->redirectByClass("ilmailsearchgroupsgui");
			
			case "course":
				$this->ctrl->setParameterByClass("ilmailsearchcoursesgui", "ref", "wsp");
				$this->ctrl->redirectByClass("ilmailsearchcoursesgui");
			
			case "registered":				
				$this->getAccessHandler()->addPermission($this->node_id, self::PERMISSION_REGISTERED);
				ilUtil::sendSuccess($this->lng->txt("wsp_permission_registered_info"), true);
				$this->ctrl->redirect($this, "share");
			
			case "password":
				$this->showPasswordForm();
				break;
			
			case "all":
				$this->getAccessHandler()->addPermission($this->node_id, self::PERMISSION_ALL);
				ilUtil::sendSuccess($this->lng->txt("wsp_permission_all_info"), true);
				$this->ctrl->redirect($this, "share");
		}
	}
	
	public function removePermission()
	{
		if($_REQUEST["obj_id"])
		{
			$this->getAccessHandler()->removePermission($this->node_id, (int)$_REQUEST["obj_id"]);
		    ilUtil::sendSuccess($this->lng->txt("wsp_permission_removed"), true);
		}

		$this->ctrl->redirect($this, "share");
	}
	
	protected function initPasswordForm()
	{
		include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTitle($this->lng->txt("wsp_set_permission_all_password"));
		
		$password = new ilPasswordInputGUI($this->lng->txt("password"), "password");
		$password->setRequired(true);
		$form->addItem($password);
		
		$form->addCommandButton('savepasswordform', $this->lng->txt("save"));
		$form->addCommandButton('share', $this->lng->txt("cancel"));
		
		return $form;
	}
	
	protected function showPasswordForm(ilPropertyFormGUI $a_form = null)
	{
		global $tpl;
		
		if(!$a_form)
		{
			$a_form = $this->initPasswordForm();
		}
		$tpl->setContent($a_form->getHTML());
	}
	
	protected function savePasswordForm()
	{
		$form = $this->initPasswordForm();
		if($form->checkInput())
		{
			$this->getAccessHandler()->addPermission($this->node_id, 
				self::PERMISSION_ALL_PASSWORD, md5($form->getInput("password")));	
			ilUtil::sendSuccess($this->lng->txt("wsp_permission_all_info"), true);
			$this->ctrl->redirect($this, "share");
		}
	
		$form->setValuesByPost();
		$this->showPasswordForm($form);
	}
}

?>