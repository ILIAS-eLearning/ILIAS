<?php

declare(strict_types=1);

/**
 * GUI class for learning sequence membership features.
 *
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 *
 * @ilCtrl_Calls ilLearningSequenceMembershipGUI: ilMailMemberSearchGUI, ilUsersGalleryGUI, ilRepositorySearchGUI
 * @ilCtrl_Calls ilLearningSequenceMembershipGUI: ilCourseParticipantsGroupsGUI, ilObjectCustomuserFieldsGUI
 * @ilCtrl_Calls ilLearningSequenceMembershipGUI: ilSessionOverviewGUI
 * @ilCtrl_Calls ilLearningSequenceMembershipGUI: ilMemberExportGUI
 *
 */
class ilLearningSequenceMembershipGUI extends ilMembershipGUI
{
	public function __construct(
		ilObjectGUI $repository_gui,
		ilObject $repository_obj,
		ilPrivacySettings $privacy_settings,
		ilLanguage $lng,
		ilCtrl $ctrl,
		ilAccess $access,
		ilRbacReview $rbac_review,
		ilSetting $settings
	) {
		parent::__construct($repository_gui, $repository_obj);

		$this->privacy_settings = $privacy_settings;
		$this->lng = $lng;
		$this->ctrl = $ctrl;
		$this->access = $access;
		$this->rbac_review = $rbac_review;
		$this->settings = $settings;
	}

	/**
	 * Filter user ids by access
	 * @param int[] $a_user_ids
	 * @return int[]
	 */
	public function filterUserIdsByRbacOrPositionOfCurrentUser($user_ids)
	{
		return $this->access->filterUserIdsByRbacOrPositionOfCurrentUser(
			'manage_members',
			'manage_members',
			$this->getParentObject()->getRefId(),
			$user_ids
		);
	}

	public function assignMembers(array $user_ids, string $type)
	{
		$object = $this->getParentObject();
		$members = $this->getParentObject()->getLSParticipants();

		if (count($user_ids) == 0) {
			$this->lng->loadLanguageModule('search');
			ilUtil::sendFailure($this->lng->txt('search_err_user_not_exist'),true);
			return false;
		}

		$assigned = false;
		foreach ($user_ids as $new_member) {
			if ($members->isAssigned($new_member)) {
				continue;
			}

			switch ($type) {
				case $object->getDefaultAdminRole():
					$members->add($new_member, IL_LSO_ADMIN);
					$members->sendNotification(
						ilLearningSequenceMembershipMailNotification::TYPE_ADMISSION_MEMBER, 
						$new_member
					);
					$assigned = true;
					break;
				case $object->getDefaultMemberRole();
					$members->add($new_member, IL_LSO_MEMBER);
					$members->sendNotification(
						ilLearningSequenceMembershipMailNotification::TYPE_ADMISSION_MEMBER, 
						$new_member
					);
					$assigned = true;
					break;
				default:
					if (in_array($type,$object->getLocalLearningSequenceRoles(true))) {
						$members->add($new_member,IL_LSO_MEMBER);
						$members->updateRoleAssignments($new_member,array($type));
					} else {
						ilLoggerFactory::getLogger('lso')->notice('Can not find role with id .' . $type. ' to assign users.');
						ilUtil::sendFailure($this->lng->txt("lso_cannot_find_role"),true);
						return false;
					}

					$members->sendNotification(
						ilLearningSequenceMembershipMailNotification::TYPE_ADMISSION_MEMBER, 
						$new_member
					);
					$assigned = true;
					break;
			}
		}
		
		if ($assigned) {
			ilUtil::sendSuccess($this->lng->txt("lso_msg_member_assigned"),true);
		} else {
			ilUtil::sendSuccess($this->lng->txt('lso_users_already_assigned'),true);
		}

		$this->ctrl->redirect($this, 'participants');
	}

	/**
	 * save in participants table
	 */
	protected function updateParticipantsStatus()
	{
		$members = $this->getParentObject()->getLSParticipants();

		$participants = (array) $_POST['visible_member_ids'];
		$notification = (array) $_POST['notification'];

		foreach($participants as $participant) {
			if($members->isAdmin($participant)) {
				$members->updateNotification($participant, in_array($participant, $notification));
				continue;
			}
			$members->updateNotification($participant, false);
		}

		ilUtil::sendSuccess($this->lng->txt('settings_saved'),true);
		$this->ctrl->redirect($this, 'participants');
	}

	protected function initParticipantTableGUI(): ilLearningSequenceParticipantsTableGUI
	{
		$show_tracking = (
			ilObjUserTracking::_enabledLearningProgress() &&
			ilObjUserTracking::_enabledUserRelatedData()
		);

		if ($show_tracking) {
			$olp = ilObjectLP::getInstance($this->getParentObject()->getId());
			$show_tracking = $olp->isActive();
		}

		return new ilLearningSequenceParticipantsTableGUI(
			$this,
			$this->getParentObject(),
			$show_tracking,
			$this->privacy_settings,
			$this->lng,
			$this->access,
			$this->rbac_review,
			$this->settings
		);
	}

	protected function initEditParticipantTableGUI(array $participants): ilLearningSequenceEditParticipantsTableGUI
	{
		$table = new ilLearningSequenceEditParticipantsTableGUI(
			$this,
			$this->getParentObject(),
			$this->getParentObject()->getLSParticipants(),
			$this->privacy_settings,
			$this->lng,
			$this->ctrl
		);

		$table->setTitle($this->lng->txt($this->getParentObject()->getType().'_header_edit_members'));
		$table->setData($this->readMemberData($participants));

		return $table;
	}

	/**
	 * Init participant view template
	 */
	protected function initParticipantTemplate()
	{
		$this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.lso_edit_members.html','Modules/LearningSequence');
	}

	public function getLocalTypeRole($a_translation = false)
	{
		return $this->getParentObject()->getLocalLearningSequenceRoles($a_translation);
	}

	public function readMemberData(array $user_ids, array $columns = null)
	{
		return $this->getParentObject()->readMemberData($user_ids, $columns);
	}

	protected function updateLPFromStatus()
	{
		return null;
	}

	protected function initWaitingList(): ilLearningSequenceWaitingList
	{
		return new ilLearningSequenceWaitingList($this->getParentObject()->getId());
	}

	protected function getDefaultRole(): int
	{
		return $this->getParentObject()->getDefaultMemberRole();
	}

	public function getPrintMemberData(array $members): array
	{
		$member_data = $this->readMemberData($members, array());
		$member_data = $this->getParentGUI()->addCustomData($member_data);

		return $member_data;
	}

	public function getAttendanceListUserData(int $user_id): array
	{
		$data = array();

		if ($this->filterUserIdsByRbacOrPositionOfCurrentUser([$user_id])) {
			$data = $this->member_data[$user_id];
			$data['access'] = $data['access_time'];
			$data['progress'] = $this->lng->txt($data['progress']);
		}

		return $data;
	}

	public function getMembersObject()
	{
		if($this->participants instanceof ilParticipants)
		{
			return $this->participants;
		}
		return $this->participants = ilParticipants::getInstance($this->getParentObject()->getRefId());
	}

	/**
	 * @return \ilMailMemberLearningSequenceRoles
	 */
	protected function getMailMemberRoles()
	{
		return new ilMailMemberLearningSequenceRoles();
	}

	protected function setSubTabs(ilTabsGUI $tabs)
	{
		$access = $this->checkRbacOrPositionAccessBool(
			'manage_members',
			'manage_members',
			$this->getParentObject()->getRefId()
		);

		if ($access) {
			$tabs->addSubTabTarget(
				$this->getParentObject()->getType()."_member_administration",
				$this->ctrl->getLinkTarget($this,'participants'),
				"members",
				get_class($this)
			);

			$tabs->addSubTabTarget(
				$this->getParentObject()->getType().'_members_gallery',
				$this->ctrl->getLinkTargetByClass(array(get_class($this),'ilUsersGalleryGUI')),
				'view',
				'ilUsersGalleryGUI'
			);
		} else if ($this->getParentObject()->getShowMembers()) {
			$tabs->addSubTabTarget(
				$this->getParentObject()->getType().'_members_gallery',
				$this->ctrl->getLinkTargetByClass(array(get_class($this),'ilUsersGalleryGUI')),
				'view',
				'ilUsersGalleryGUI'
			);
		}
	}
}
