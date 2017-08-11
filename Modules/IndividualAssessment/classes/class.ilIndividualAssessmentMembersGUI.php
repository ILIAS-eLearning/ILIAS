<?php
require_once 'Modules/IndividualAssessment/classes/class.ilIndividualAssessmentMembersTableGUI.php';
require_once 'Modules/IndividualAssessment/classes/LearningProgress/class.ilIndividualAssessmentLPInterface.php';
/**
 * For the purpose of streamlining the grading and learning-process status definition
 * outside of tests, SCORM courses e.t.c. the IndividualAssessment is used.
 * It caries a LPStatus, which is set Individually.
 *
 * @author Denis Klöpfer <denis.kloepfer@concepts-and-training.de>
 *
 * @ilCtrl_Calls ilIndividualAssessmentMembersGUI: ilRepositorySearchGUI
 * @ilCtrl_Calls ilIndividualAssessmentMembersGUI: ilIndividualAssessmentMemberGUI
 */
class ilIndividualAssessmentMembersGUI {

	protected $ctrl;
	protected $parent_gui;
	protected $ref_id;
	protected $tpl;
	protected $lng;

	public function __construct($a_parent_gui, $a_ref_id) {
		global $DIC;
		$this->ctrl = $DIC['ilCtrl'];
		$this->parent_gui = $a_parent_gui;
		$this->object = $a_parent_gui->object;
		$this->ref_id = $a_ref_id;
		$this->tpl =  $DIC['tpl'];
		$this->lng = $DIC['lng'];
		$this->toolbar = $DIC['ilToolbar'];
		$this->iass_access = $this->object->accessHandler();
	}

	public function executeCommand() {
		if(!$this->iass_access->mayEditMembers()
			&& !$this->iass_access->mayGradeUser()
			&& !$this->iass_access->mayViewUser()
		) {
			$this->parent_gui->handleAccessViolation();
		}
		$cmd = $this->ctrl->getCmd();
		$next_class = $this->ctrl->getNextClass();
		switch($next_class) {
			case "ilrepositorysearchgui":
				require_once 'Services/Search/classes/class.ilRepositorySearchGUI.php';
				$rep_search = new ilRepositorySearchGUI();
				$rep_search->setCallback($this,"addUsersFromSearch");
				$this->ctrl->forwardCommand($rep_search);
				break;
			case "ilindividualassessmentmembergui":
				require_once 'Modules/IndividualAssessment/classes/class.ilIndividualAssessmentMemberGUI.php';
				$member = new ilIndividualAssessmentMemberGUI($this, $this->parent_gui, $this->ref_id);
				$this->ctrl->forwardCommand($member);
				break;
			default:
				if(!$cmd) {
					$cmd = 'view';
				}
				$this->$cmd();
				break;
		}
	}

	protected function addedUsers() {
		if(!$_GET['failure']) {
			ilUtil::sendSuccess($this->lng->txt('iass_add_user_success'));
		} else {
			ilUtil::sendFailure($this->lng->txt('iass_add_user_failure'));
		}
		$this->view();
	}

	protected function view() {
		if($this->iass_access->mayEditMembers()) {
			require_once './Services/Search/classes/class.ilRepositorySearchGUI.php';

			$search_params = ['crs', 'grp'];
			$container_id = $this->object->getParentContainerIdByType($this->ref_id, $search_params);
			if($container_id !== 0) {
				ilRepositorySearchGUI::fillAutoCompleteToolbar(
				$this,
				$this->toolbar,
				array(
					'auto_complete_name'	=> $this->lng->txt('user'),
					'submit_name'			=> $this->lng->txt('add'),
					'add_search'			=> true,
					'add_from_container'		=> $container_id
				)
				);
			} else {
				ilRepositorySearchGUI::fillAutoCompleteToolbar(
				$this,
				$this->toolbar,
				array(
					'auto_complete_name'	=> $this->lng->txt('user'),
					'submit_name'			=> $this->lng->txt('add'),
					'add_search'			=> true
				)
				);
			}
		}
		$table = new ilIndividualAssessmentMembersTableGUI($this);
		$this->tpl->setContent($table->getHTML());
	}

	public function addUsersFromSearch($user_ids) {
		if($user_ids && is_array($user_ids) && !empty($user_ids)) {
			$this->addUsers($user_ids);
		}

		ilUtil::sendInfo($this->lng->txt("search_no_selection"), true);
		$this->ctrl->redirectByClass(array(get_class($this->parent_gui),get_class($this)),'view');
	}

	/**
	 * Add users to corresponding iass-object. To be used by repository search.
	 *
	 * @param	int|string[]	$user_ids
	 */
	public function addUsers(array $user_ids) {

		if(!$this->iass_access->mayEditMembers()) {
			$this->parent_gui->handleAccessViolation();
		}
		$iass = $this->object;
		$members = $iass->loadMembers();
		$failure = null;
		if(count($user_ids) === 0) {
			$failure = 1;
		}
		foreach ($user_ids as $user_id) {
			$user = new ilObjUser($user_id);
			if(!$members->userAllreadyMember($user)) {
				$members = $members->withAdditionalUser($user);
			} else {
				$failure = 1;
			}
		}
		$members->updateStorageAndRBAC($iass->membersStorage(),$iass->accessHandler());
		ilIndividualAssessmentLPInterface::updateLPStatusByIds($iass->getId(),$user_ids);
		$this->ctrl->setParameter($this, 'failure', $failure);
		$this->ctrl->redirectByClass(array(get_class($this->parent_gui),get_class($this)),'addedUsers');
	}

	protected function removeUserConfirmation() {
		if(!$this->iass_access->mayEditMembers()) {
			$this->parent_gui->handleAccessViolation();
		}
		include_once './Services/Utilities/classes/class.ilConfirmationGUI.php';
		$confirm = new ilConfirmationGUI();
		$confirm->addItem('usr_id',$_GET['usr_id'], ilObjUser::_lookupFullname($_GET['usr_id']));
		$confirm->setHeaderText($this->lng->txt('iass_remove_user_qst'));
		$confirm->setFormAction($this->ctrl->getFormAction($this));
		$confirm->setConfirm($this->lng->txt('remove'), 'removeUser');
		$confirm->setCancel($this->lng->txt('cancel'), 'view');
		$this->tpl->setContent($confirm->getHTML());
	}

	/**
	 * Remove users from corresponding iass-object. To be used by repository search.
	 *
	 * @param	int|string[]	$user_ids
	 */
	public function removeUser() {
		if(!$this->iass_access->mayEditMembers()) {
			$this->parent_gui->handleAccessViolation();
		}
		$usr_id = $_POST['usr_id'];
		$iass = $this->object;
		$iass->loadMembers()
			->withoutPresentUser(new ilObjUser($usr_id))
			->updateStorageAndRBAC($iass->membersStorage(),$iass->accessHandler());
		ilIndividualAssessmentLPInterface::updateLPStatusByIds($iass->getId(),array($usr_id));
		$this->ctrl->redirectByClass(array(get_class($this->parent_gui),get_class($this)),'view');
	}
}