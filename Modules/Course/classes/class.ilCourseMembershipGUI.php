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
				case $this->getParentObject()->getDefaultAdminRole():
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
	 * => save button in member table
	 */
	protected function updateParticipantsStatus()
	{
		global $ilAccess,$ilErr,$ilUser,$rbacadmin;
		
		$visible_members = (array) $_POST['visible_member_ids'];
		$passed = (array) $_POST['passed'];
		$blocked = (array) $_POST['blocked'];
		$contact = (array) $_POST['contact'];
		$notification = (array) $_POST['notification'];
		
		foreach($visible_members as $member_id)
		{
			$this->getMembersObject()->updatePassed($member_id,in_array($member_id,$passed),true);
			$this->updateLPFromStatus($member_id, in_array($member_id, $passed));
			
			if($this->getMembersObject()->isAdmin($member_id) or $this->getMembersObject()->isTutor($member_id))
			{
				// remove blocked
				$this->getMembersObject()->updateBlocked($member_id, 0);
				$this->getMembersObject()->updateNotification($member_id, in_array($member_id, $notification));
				$this->getMembersObject()->updateContact($member_id, in_array($member_id, $contact));
			}
			else
			{
				// send notifications => unblocked
				if($this->getMembersObject()->isBlocked($member_id) && !in_array($member_id,$blocked))
				{
					$this->getMembersObject()->sendNotification($this->getMembersObject()->NOTIFY_UNBLOCK_MEMBER,$member_id);
				}
				// => blocked
				if(!$this->getMembersObject()->isBlocked($member_id) && in_array($member_id, $blocked))
				{
					$this->getMembersObject()->sendNotification($this->getMembersObject()->NOTIFY_BLOCK_MEMBER,$member_id);
				}

				// normal member => remove notification, contact
				$this->getMembersObject()->updateNotification($member_id, false);
				$this->getMembersObject()->updateContact($member_id, false);
				$this->getMembersObject()->updateBlocked($member_id, in_array($member_id, $blocked));
			}
		}
			
		
		ilUtil::sendSuccess($this->lng->txt('settings_saved'),true);
		$this->ctrl->redirect($this, 'participants');
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
	 * init edit participants table gui
	 * @param array $participants
	 * @return \ilCourseEditParticipantsTableGUI
	 */
	protected function initEditParticipantTableGUI(array $participants)
	{
		include_once './Modules/Course/classes/class.ilCourseEditParticipantsTableGUI.php';
		$table = new ilCourseEditParticipantsTableGUI($this, $this->getParentObject());
		$table->setTitle($this->lng->txt($this->getParentObject()->getType().'_header_edit_members'));
		$table->setData($this->getParentGUI()->readMemberData($participants));
		
		return $table;
	}

		/**
	 * Init participant view template
	 */
	protected function initParticipantTemplate()
	{
		$this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.crs_edit_members.html','Modules/Course');
	}
	
	/**
	 * @todo refactor delete
	 */
	protected function getLocalTypeRole($a_translation = false)
	{
		return $this->getParentObject()->getLocalCourseRoles($a_translation);
	}
	
	/**
	 * Update lp from status
	 */
	protected function updateLPFromStatus($a_member_id, $a_passed)
	{
		return $this->getParentGUI()->updateLPFromStatus($a_member_id, $a_passed);
	}
	
	/**
	 * init waiting list
	 * @return ilCourseWaitingList
	 */
	protected function initWaitingList()
	{
		include_once './Modules/Course/classes/class.ilCourseWaitingList.php';
		$wait = new ilCourseWaitingList($this->getParentObject()->getId());
		return $wait;
	}
}
?>