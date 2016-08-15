<?php
require_once("Modules/ManualAssessment/classes/class.ilManualAssessmentMembersTableGUI.php");
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
	}

	public function executeCommand() {
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

	protected function view() {
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
		$this->toolbar->addButton($this->lng->txt("grp_search_users"),
			$this->ctrl->getLinkTargetByClass('ilRepositorySearchGUI','start'));
		$table = new ilManualAssessmentMembersTableGUI($this);
		$this->tpl->setContent($table->getHTML());
	}

	public function addUsers(array $user_ids) {
		$mass = $this->object;
		$members = $mass->loadMembers();
		foreach ($user_ids as $user_id) {
			$user = new ilObjUser($user_id);
			if(!$members->userAllreadyMember($user)) {
				$members = $members->withAdditionalUser($user);
				ilUtil::sendSuccess("goody");
			} else {
				ilUtil::sendFailure("allready_member");
			}
		}
		$members->updateStorageAndRBAC($mass->membersStorage());
		$this->ctrl->redirectByClass(array(get_class($this->parent_gui),get_class($this)),'view');
	}

	public function removeUser() {
		$usr_id = $_GET['usr_id'];
		$mass = $this->object;
		$mass->loadMembers()->withoutPresentUser(new ilObjUser($usr_id))->updateStorageAndRBAC($mass->membersStorage());
		$this->ctrl->redirectByClass(array(get_class($this->parent_gui),get_class($this)),'view');
	}
}