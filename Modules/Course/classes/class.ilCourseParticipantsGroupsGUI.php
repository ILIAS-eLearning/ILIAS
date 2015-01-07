<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "./Modules/Course/classes/class.ilCourseParticipantsGroupsTableGUI.php";

/**
* Class ilCourseParticipantsGroupsGUI
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* $Id: class.ilObjCourseGUI.php 24234 2010-06-14 12:35:45Z smeyer $
*
* @ilCtrl_Calls ilCourseParticipantsGroupsGUI:
*
*/
class ilCourseParticipantsGroupsGUI
{
	function __construct($a_ref_id)
	{
	  $this->ref_id = $a_ref_id;
	}

	function executeCommand()
	{
		global $ilCtrl, $ilErr, $ilAccess, $lng;
		
		if(!$ilAccess->checkAccess('write','',$this->ref_id))
		{
			$ilErr->raiseError($lng->txt('permission_denied'),$ilErr->WARNING);
		}

		$cmd = $ilCtrl->getCmd();
		if(!$cmd)
		{
			$cmd = "show";
		}
		$this->$cmd();
	}
	
	function show()
	{
		global $tpl;
		
		$tbl_gui = new ilCourseParticipantsGroupsTableGUI($this, "show", $this->ref_id);
		$tpl->setContent($tbl_gui->getHTML());
	}

	function applyFilter()
    {
		$tbl_gui = new ilCourseParticipantsGroupsTableGUI($this, "show", $this->ref_id);
		$tbl_gui->resetOffset();
		$tbl_gui->writeFilterToSession();
		$this->show();
	}

	function resetFilter()
    {
		$tbl_gui = new ilCourseParticipantsGroupsTableGUI($this, "show", $this->ref_id);
		$tbl_gui->resetOffset();
		$tbl_gui->resetFilter();
		$this->show();
	}

	function confirmRemove()
	{
		global $ilAccess, $ilCtrl, $lng, $tpl;
		
		include_once('./Services/Utilities/classes/class.ilConfirmationGUI.php');
		$confirm = new ilConfirmationGUI();
		$confirm->setFormAction($ilCtrl->getFormAction($this,'remove'));
		$confirm->addHiddenItem("grp_id", $_GET["grp_id"]);
		$confirm->setHeaderText($lng->txt('grp_dismiss_member'));
		$confirm->setConfirm($lng->txt('confirm'),'remove');
		$confirm->setCancel($lng->txt('cancel'),'show');

		include_once './Services/User/classes/class.ilUserUtil.php';
	  
		$confirm->addItem('usr_id',
				$_GET["usr_id"],
				ilUserUtil::getNamePresentation($_GET["usr_id"], false, false, "", true),
				ilUtil::getImagePath('icon_usr.svg'));

		$tpl->setContent($confirm->getHTML());
	}

	function remove()
	{
		global $ilAccess, $ilObjDataCache, $lng;
		
		if (!$ilAccess->checkAccess("write", "", $_POST["grp_id"]))
		{
			ilUtil::sendFailure($lng->txt("permission_denied"), true);
			$this->show();
			return;
		}
	
		include_once './Modules/Group/classes/class.ilGroupParticipants.php';
		$members_obj = ilGroupParticipants::_getInstanceByObjId($ilObjDataCache->lookupObjId($_POST["grp_id"]));
		$members_obj->delete($_POST["usr_id"]);

		// Send notification
		include_once './Modules/Group/classes/class.ilGroupMembershipMailNotification.php';
		$members_obj->sendNotification(
			ilGroupMembershipMailNotification::TYPE_DISMISS_MEMBER,
			$_POST["usr_id"]
		);
		
		ilUtil::sendSuccess($lng->txt("grp_msg_membership_annulled"));
		$this->show();
	}

	function add()
	{
		global $ilErr, $ilObjDataCache, $lng, $ilAccess;


		if(sizeof($_POST["usrs"]))
		{
			if (!$ilAccess->checkAccess("write", "", $_POST["grp_id"]))
			{
				ilUtil::sendFailure($lng->txt("permission_denied"), true);
				$this->show();
				return;
			}

			include_once './Modules/Group/classes/class.ilGroupParticipants.php';
			$members_obj = ilGroupParticipants::_getInstanceByObjId($ilObjDataCache->lookupObjId($_POST["grp_id"]));
			foreach ($_POST["usrs"] as $new_member)
			{
				if (!$members_obj->add($new_member, IL_GRP_MEMBER))
				{
					$ilErr->raiseError("An Error occured while assigning user to group !", $ilErr->MESSAGE);
				}

				include_once './Modules/Group/classes/class.ilGroupMembershipMailNotification.php';
				$members_obj->sendNotification(
					ilGroupMembershipMailNotification::TYPE_ADMISSION_MEMBER,
					$new_member
				);

			}

			ilUtil::sendSuccess($lng->txt("grp_msg_member_assigned"));
		}

		$this->show();
	}
}

?>