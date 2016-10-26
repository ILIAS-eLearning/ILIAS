<?php
require_once 'Modules/ManualAssessment/classes/class.ilManualAssessmentMembersTableGUI.php';
require_once 'Modules/ManualAssessment/classes/LearningProgress/class.ilManualAssessmentLPInterface.php';
/**
 * For the purpose of streamlining the grading and learning-process status definition
 * outside of tests, SCORM courses e.t.c. the ManualAssessment is used.
 * It caries a LPStatus, which is set manually.
 *
 * @author Denis Klöpfer <denis.kloepfer@concepts-and-training.de>
 *
 * @ilCtrl_Calls ilManualAssessmentMembersGUI: ilRepositorySearchGUI
 * @ilCtrl_Calls ilManualAssessmentMembersGUI: ilManualAssessmentMemberGUI
 */
class ilManualAssessmentMembersGUI {

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
		$this->access_handler = $this->object->accessHandler();
	}

	public function executeCommand() {
		if(!$this->access_handler->checkAccessToObj($this->object,'edit_members')
			&& !$this->access_handler->checkAccessToObj($this->object,'edit_learning_progress')
			&& !$this->access_handler->checkAccessToObj($this->object,'read_learning_progress') ) {
			$this->parent_gui->handleAccessViolation();
		}
		$cmd = $this->ctrl->getCmd();
		$next_class = $this->ctrl->getNextClass();
		switch($next_class) {
			case "ilrepositorysearchgui":
				require_once 'Services/Search/classes/class.ilRepositorySearchGUI.php';
				$rep_search = new ilRepositorySearchGUI();
				$rep_search->setCallback($this,"addUsers");
				$this->ctrl->forwardCommand($rep_search);
				break;
			case "ilmanualassessmentmembergui":
				require_once 'Modules/ManualAssessment/classes/class.ilManualAssessmentMemberGUI.php';
				$member = new ilManualAssessmentMemberGUI($this, $this->parent_gui, $this->ref_id);
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
			ilUtil::sendSuccess($this->lng->txt('mass_add_user_success'));
		} else {
			ilUtil::sendFailure($this->lng->txt('mass_add_user_failure'));
		}
		$this->view();
	}

	protected function view() {
		if($this->access_handler->checkAccessToObj($this->object,'edit_members')) {
			require_once './Services/Search/classes/class.ilRepositorySearchGUI.php';
			ilRepositorySearchGUI::fillAutoCompleteToolbar(
				$this,
				$this->toolbar,
				array(
					'auto_complete_name'	=> $this->lng->txt('user'),
					'submit_name'			=> $this->lng->txt('add')
				)
			);
			$this->toolbar->addSeparator();
			$this->toolbar->addButton($this->lng->txt('search_user'),
			$this->ctrl->getLinkTargetByClass('ilRepositorySearchGUI','start'));
		}
		$table = new ilManualAssessmentMembersTableGUI($this);
		$this->tpl->setContent($table->getHTML());
	}

	/**
	 * Add users to corresponding mass-object. To be used by repository search.
	 *
	 * @param	int|string[]	$user_ids
	 */
	public function addUsers(array $user_ids) {

		if(!$this->object->accessHandler()->checkAccessToObj($this->object,'edit_members')) {
			$a_parent_gui->handleAccessViolation();
		}
		$mass = $this->object;
		$members = $mass->loadMembers();
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
		$members->updateStorageAndRBAC($mass->membersStorage(),$mass->accessHandler());
		ilManualAssessmentLPInterface::updateLPStatusByIds($mass->getId(),$user_ids);
		$this->ctrl->setParameter($this, 'failure', $failure);
		$this->ctrl->redirectByClass(array(get_class($this->parent_gui),get_class($this)),'addedUsers');
	}

	protected function removeUserConfirmation() {
		if(!$this->object->accessHandler()->checkAccessToObj($this->object,'edit_members')) {
			$a_parent_gui->handleAccessViolation();
		}
		include_once './Services/Utilities/classes/class.ilConfirmationGUI.php';
		$confirm = new ilConfirmationGUI();
		$confirm->addItem('usr_id',$_GET['usr_id'], ilObjUser::_lookupFullname($_GET['usr_id']));
		$confirm->setHeaderText($this->lng->txt('mass_remove_user_qst'));
		$confirm->setFormAction($this->ctrl->getFormAction($this));
		$confirm->setConfirm($this->lng->txt('remove'), 'removeUser');
		$confirm->setCancel($this->lng->txt('cancel'), 'view');
		$this->tpl->setContent($confirm->getHTML());
	}

	/**
	 * Remove users from corresponding mass-object. To be used by repository search.
	 *
	 * @param	int|string[]	$user_ids
	 */
	public function removeUser() {
		if(!$this->object->accessHandler()->checkAccessToObj($this->object,'edit_members')) {
			$a_parent_gui->handleAccessViolation();
		}
		$usr_id = $_POST['usr_id'];
		$mass = $this->object;
		$mass->loadMembers()
			->withoutPresentUser(new ilObjUser($usr_id))
			->updateStorageAndRBAC($mass->membersStorage(),$mass->accessHandler());
		ilManualAssessmentLPInterface::updateLPStatusByIds($mass->getId(),array($usr_id));
		$this->ctrl->redirectByClass(array(get_class($this->parent_gui),get_class($this)),'view');
	}
}