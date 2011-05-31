<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * ACL access handler GUI
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 * 
 * @ilCtrl_Calls ilWorkspaceAccessGUI: ilMailSearchCoursesGUI, ilMailSearchGroupsGUI
 * @ilCtrl_Calls ilWorkspaceAccessGUI: ilMailSearchGUI
 *
 * @ingroup ServicesPersonalWorkspace
 */
class ilWorkspaceAccessGUI
{
	protected $ctrl;
	protected $lng;
	protected $node_id;
	protected $access_handler;	
	
	const PERMISSION_REGISTERED = -1;
	const PERMISSION_ALL_PASSWORD = -3;
	const PERMISSION_ALL = -5;
	
	
	function __construct($a_node_id, $a_access_handler)
	{
		global $ilCtrl, $lng;
		
		$this->ctrl = $ilCtrl;
		$this->lng = $lng;
		$this->node_id = $a_node_id;
		$this->access_handler = $a_access_handler;		
	}
	
	function executeCommand()
	{
		global $rbacsystem, $ilTabs;

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
				break;
			
			case "ilmailsearchgroupsgui";			
				$ilTabs->setBackTarget($this->lng->txt("back"),
					$this->ctrl->getLinkTarget($this, "share"));
				include_once('Services/Contact/classes/class.ilMailSearchGroupsGUI.php');
				$gsearch = new ilMailSearchGroupsGUI($this->access_handler, $this->node_id);
				$this->ctrl->setReturn($this, 'share');
				$this->ctrl->forwardCommand($gsearch);
				break;
			
			case "ilmailsearchgui";			
				$ilTabs->setBackTarget($this->lng->txt("back"),
					$this->ctrl->getLinkTarget($this, "share"));
				include_once('Services/Contact/classes/class.ilMailSearchGUI.php');
				$usearch = new ilMailSearchGUI($this->access_handler, $this->node_id);
				$this->ctrl->setReturn($this, 'share');
				$this->ctrl->forwardCommand($usearch);
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
	
	protected function getAccessHandler()
	{
		return $this->access_handler;
	}
	
	protected function share()
	{
		global $ilToolbar, $tpl, $ilUser;
		
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
		
		$options["registered"] = $this->lng->txt("wsp_set_permission_registered");
		$options["password"] = $this->lng->txt("wsp_set_permission_all_password");
		$options["all"] = $this->lng->txt("wsp_set_permission_all");						
		
		include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
		$actions = new ilSelectInputGUI("", "action");		
		$actions->setOptions($options);		
		$ilToolbar->addInputItem($actions);
		
		$ilToolbar->setFormAction($this->ctrl->getFormAction($this));		
		$ilToolbar->addFormButton($this->lng->txt("add"), "addpermissionhandler");

		include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceAccessTableGUI.php";
		$table = new ilWorkspaceAccessTableGUI($this, "share", $this->node_id, $this->getAccessHandler());
		$tpl->setContent($table->getHTML());
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
}

?>