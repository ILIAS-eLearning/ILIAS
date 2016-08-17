<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Membership/classes/class.ilMembershipGUI.php';

/**
 * Member-tab content
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de> 
 * 
 * 
 * @ilCtrl_Calls ilCourseMembershipGUI: ilMailMemberSearchGUI, ilUsersGalleryGUI, ilRepositorySearchGUI
 * @ilCtrl_Calls ilCourseMembershipGUI: ilCourseParticipantsGroupsGUI
 * @ilCtrl_Calls ilCourseMembershipGUI: ilSessionOverviewGUI
 * @ilCtrl_Calls ilCourseMembershipGUI: ilMemberExportGUI
 */
class ilCourseMembershipGUI extends ilMembershipGUI
{
	
	/**
	 * callback from repository search gui
	 * @global ilRbacSystem $rbacsystem
	 * @param array $a_usr_ids
	 * @param int $a_type role_id
	 * @return bool
	 */
	public function assignMembers(array $a_usr_ids,$a_type)
	{
		global $rbacsystem, $ilErr;

		
		if(!$GLOBALS['ilAccess']->checkAccess('write','', $this->getParentObject()->getRefId()))
		{
			$ilErr->raiseError($this->lng->txt("msg_no_perm_read"), $ilErr->FATAL);
		}

		if(!count($a_usr_ids))
		{
			ilUtil::sendFailure($this->lng->txt("crs_no_users_selected"),true);
			return false;
		}

		$added_users = 0;
		foreach($a_usr_ids as $user_id)
		{
			if(!$tmp_obj = ilObjectFactory::getInstanceByObjId($user_id,false))
			{
				continue;
			}
			if($this->getMembersObject()->isAssigned($user_id))
			{
				continue;
			}
			switch($a_type)
			{
				case $this->getParentObject()->getDefaultMemberRole():
					$this->getMembersObject()->add($user_id,IL_CRS_MEMBER);
					break;
				case $this->getParentObject()->getDefaultTutorRole():
					$this->getMembersObject()->add($user_id,IL_CRS_TUTOR);
					break;
				case $this->getMembersObject()->getDefaultAdminRole():
					$this->getMembersObject()->add($user_id,IL_CRS_ADMIN);
					break;
				default:
					if(in_array($a_type,$this->getParentObject()->getLocalCourseRoles(true)))
					{
						$this->getMembersObject()->add($user_id,IL_CRS_MEMBER);
						$this->getMembersObject()->updateRoleAssignments($user_id,(array)$a_type);
					}
					else
					{
						$this->log->notice('Can\'t find role with id .' . $a_type. ' to assign users.');
						ilUtil::sendFailure($this->lng->txt("crs_cannot_find_role"),true);
						return false;
					}
					break;
			}
			$this->getMembersObject()->sendNotification($this->getMembersObject()->NOTIFY_ACCEPT_USER,$user_id);

			$this->getParentObject()->checkLPStatusSync($user_id);

			++$added_users;
		}
		if($added_users)
		{
			ilUtil::sendSuccess($this->lng->txt("crs_users_added"),true);
			$this->ctrl->redirect($this,'participants');
		}
		ilUtil::sendFailure($this->lng->txt("crs_users_already_assigned"),true);
		return false;
	}
	
	/**
	 * @return \ilParticpantTableGUI
	 */
	protected function initParticipantTableGUI()
	{
		include_once './Services/Tracking/classes/class.ilObjUserTracking.php';
		$show_tracking = 
			(ilObjUserTracking::_enabledLearningProgress() && ilObjUserTracking::_enabledUserRelatedData())
		;
		if($show_tracking)
		{			
			include_once('./Services/Object/classes/class.ilObjectLP.php');
			$olp = ilObjectLP::getInstance($this->getParentObject()->getId());
			$show_tracking = $olp->isActive();
		}

		include_once('./Services/Object/classes/class.ilObjectActivation.php');
		$timings_enabled = 
			(ilObjectActivation::hasTimings($this->getParentObject()->getRefId()) && ($this->getParentObject()->getViewMode() == IL_CRS_VIEW_TIMING))
		;
		
		
		include_once './Modules/Course/classes/class.ilCourseParticipantsTableGUI.php';
		return new ilCourseParticipantsTableGUI(
			$this,
			$this->getParentObject(),
			$show_tracking,
			$timings_enabled,
			$this->getParentObject()->getStatusDetermination() == ilObjCourse::STATUS_DETERMINATION_LP
		);
	}

	/**
	 * Init participant view template
	 */
	protected function initParticipantTemplate()
	{
		$this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.crs_edit_members.html','Modules/Course');
	}
}
?>