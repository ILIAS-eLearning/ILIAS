<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Membership/classes/class.ilMembershipGUI.php';

/**
 * GUI class for membership features
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de> 
 *
 * @ilCtrl_Calls ilGroupMembershipGUI: ilMailMemberSearchGUI, ilUsersGalleryGUI, ilRepositorySearchGUI
 * @ilCtrl_Calls ilGroupMembershipGUI: ilCourseParticipantsGroupsGUI
 * @ilCtrl_Calls ilGroupMembershipGUI: ilSessionOverviewGUI
 * @ilCtrl_Calls ilGroupMembershipGUI: ilMemberExportGUI
 *
 */
class ilGroupMembershipGUI extends ilMembershipGUI
{
	/**
	 * @access public
	 */
	public function assignMembers($user_ids, $a_type)
	{
		if(empty($user_ids[0]))
		{
			$this->lng->loadLanguageModule('search');
			ilUtil::sendFailure($this->lng->txt('search_err_user_not_exist'),true);
			return false;
		}

		$assigned = FALSE;
		foreach((array) $user_ids as $new_member)
		{
			if($this->getMembersObject()->isAssigned($new_member))
			{
				continue;
			}
			switch($a_type)
			{
				case $this->getParentObject()->getDefaultAdminRole():
					$this->getMembersObject()->add($new_member, IL_GRP_ADMIN);
					include_once './Modules/Group/classes/class.ilGroupMembershipMailNotification.php';
					$this->getMembersObject()->sendNotification(
						ilGroupMembershipMailNotification::TYPE_ADMISSION_MEMBER, 
						$new_member
					);
					$assigned = TRUE;
					break;
				
				default:
					$this->getMembersObject()->add($new_member, IL_GRP_MEMBER);
					include_once './Modules/Group/classes/class.ilGroupMembershipMailNotification.php';
					$this->getMembersObject()->sendNotification(
						ilGroupMembershipMailNotification::TYPE_ADMISSION_MEMBER, 
						$new_member
					);
					$assigned = TRUE;
					break;
			}
		}
		
		if($assigned)
		{
			ilUtil::sendSuccess($this->lng->txt("grp_msg_member_assigned"),true);
		}
		else
		{
			ilUtil::sendSuccess($this->lng->txt('grp_users_already_assigned'),TRUE);
		}
		$this->ctrl->redirect($this,'participants');
	}

}
?>