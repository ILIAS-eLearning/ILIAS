<?php

require_once 'Services/Form/classes/class.ilTextAreaInputGUI.php';
require_once 'Services/Form/classes/class.ilTextInputGUI.php';
require_once 'Services/Form/classes/class.ilCheckboxInputGUI.php';
require_once 'Services/Form/classes/class.ilNonEditableValueGUI.php';
require_once 'Services/Form/classes/class.ilSelectInputGUI.php';
require_once 'Modules/IndividualAssessment/classes/LearningProgress/class.ilIndividualAssessmentLPInterface.php';
require_once 'Modules/IndividualAssessment/classes/Notification/class.ilIndividualAssessmentPrimitiveInternalNotificator.php';
require_once 'Modules/IndividualAssessment/classes/class.ilIndividualAssessmentLP.php';

/**
 * For the purpose of streamlining the grading and learning-process status definition
 * outside of tests, SCORM courses e.t.c. the IndividualAssessment is used.
 * It caries a LPStatus, which is set Individually.
 *
 * @author Denis Klöpfer <denis.kloepfer@concepts-and-training.de>
 */
class ilIndividualAssessmentMemberGUI {
		protected $notificator;

		public function __construct($members_gui ,$a_parent_gui, $a_ref_id) {
			$this->notificator = new ilIndividualAssessmentPrimitiveInternalNotificator();
			global $DIC;
			$this->ctrl = $DIC['ilCtrl'];
			$this->members_gui = $members_gui;
			$this->parent_gui = $a_parent_gui;
			$this->object = $a_parent_gui->object;
			$this->ref_id = $a_ref_id;
			$this->tpl =  $DIC['tpl'];
			$this->lng = $DIC['lng'];
			$this->ctrl->saveParameter($this,'usr_id');
			$this->examinee = new ilObjUser($_GET['usr_id']);
			$this->examiner = $DIC['ilUser'];
			$this->setTabs($DIC['ilTabs']);
			$this->member = $this->object->membersStorage()
								->loadMember($this->object, $this->examinee);
	}

	public function executeCommand() {
		$cmd = $this->ctrl->getCmd();
		switch($cmd) {
			case 'view':
			case 'edit':
			case 'save':
			case 'finalizeConfirmation':
			case 'finalize':
			case 'cancelFinalize':
			case 'amend':
			case 'saveAmend':
				break;
			default:
				$a_parent_gui->handleAccessViolation();
		}
		$this->$cmd();
	}

	/**
	 * View grading informations for user
	 *
	 * @return null
	 */
	protected function view() {
		if (!$this->mayBeViewed()) {
			$this->parent_gui->handleAccessViolation();
			return;
		}

		$form = $this->fillForm($this->initGradingForm(false),$this->member);
		$this->renderForm($form);
	}

	/**
	 * Edit grading informations for user
	 *
	 * @param ilPropertyFormGUI | null 	$form
	 *
	 * @return null
	 */
	protected function edit(ilPropertyFormGUI $form = null) {
		if (!$this->mayBeEdited()) {
			$this->parent_gui->handleAccessViolation();
			return;
		}

		if ($form === null) {
			$form = $this->fillForm($this->initGradingForm(), $this->member);
		}

		$form->addCommandButton('save', $this->lng->txt('save'));
		$form->addCommandButton('finalizeConfirmation', $this->lng->txt('iass_finalize'));
		$this->renderForm($form);
	}

	/**
	 * Save grading informations for user
	 *
	 * @return null
	 */
	protected function save() {
		if (!$this->mayBeEdited()) {
			$this->parent_gui->handleAccessViolation();
			return;
		}

		$form = $this->initGradingForm();
		if (!$form->checkInput()) {
			$form->setValuesByPost();
			$this->edit($form);
			return;
		}

		$this->saveMember($_POST);
		if ($this->object->isActiveLP()) {
			ilIndividualAssessmentLPInterface::updateLPStatusOfMember($this->member);
		}
		ilUtil::sendSuccess($this->lng->txt('iass_membership_saved'), true);
		$this->redirect('edit');
	}

	/**
	 * Save grading informations and show confirmation form
	 *
	 * @return null
	 */
	protected function finalizeConfirmation() {
		if (!$this->mayBeEdited()) {
			$this->parent_gui->handleAccessViolation();
			return;
		}

		$form = $this->initGradingForm();
		if (!$form->checkInput()) {
			$form->setValuesByPost();
			$this->edit($form);
			return;
		}

		$this->saveMember($_POST);

		if (!$this->member->mayBeFinalized()) {
			ilUtil::sendFailure($this->lng->txt('iass_may_not_finalize'), true);
			$this->redirect('edit');
		}

		include_once './Services/Utilities/classes/class.ilConfirmationGUI.php';
		$confirm = new ilConfirmationGUI();
		$confirm->addHiddenItem('usr_id', $_GET['usr_id']);
		$confirm->setHeaderText($this->lng->txt('iass_finalize_user_qst'));
		$confirm->setFormAction($this->ctrl->getFormAction($this));
		$confirm->setConfirm($this->lng->txt('iass_finalize'), 'finalize');
		$confirm->setCancel($this->lng->txt('cancel'), 'cancelFinalize');

		$this->tpl->setContent($confirm->getHTML());
	}

	/**
	 * Finalize the grading
	 *
	 * @return null
	 */
	protected function finalize() {
		if (!$this->mayBeEdited()) {
			$this->parent_gui->handleAccessViolation();
			return;
		}

		if (!$this->member->mayBeFinalized()) {
			ilUtil::sendFailure($this->lng->txt('iass_may_not_finalize'), true);
			$this->redirect('edit');
			return;
		}

		$this->member = $this->member->withFinalized();
		$this->object->membersStorage()->updateMember($this->member);
		if ($this->object->isActiveLP()) {
			ilIndividualAssessmentLPInterface::updateLPStatusOfMember($this->member);
		}

		ilUtil::sendSuccess($this->lng->txt('iass_membership_finalized'), true);
		$this->redirect('view');
	}

	/**
	 * Cancel finalizing and get back to edit form
	 *
	 * @return null
	 */
	protected function cancelFinalize()
	{
		$this->edit();
	}

	/**
	 * Show grading form to amend the result
	 *
	 * @param ilPropertyFormGUI | null 	$form
	 *
	 * @return null
	 */
	protected function amend($form = null)
	{
		if (!$this->mayBeAmended()) {
			$this->parent_gui->handleAccessViolation();
			return;
		}

		if ($form === null) {
			$form = $this->fillForm($this->initGradingForm(), $this->member);
		}

		$form->addCommandButton('saveAmend', $this->lng->txt('iass_save_amend'));
		$this->renderForm($form);
	}

	/**
	 * Save changes of grading result
	 *
	 * @return null
	 */
	protected function saveAmend()
	{
		if (!$this->mayBeAmended()) {
			$this->parent_gui->handleAccessViolation();
			return;
		}

		$form = $this->initGradingForm();
		if (!$form->checkInput()) {
			$form->setValuesByPost();
			$this->amend($form);
			return;
		}

		$this->saveMember($_POST, true);

		if ($this->object->isActiveLP()) {
			ilIndividualAssessmentLPInterface::updateLPStatusOfMember($this->member);
		}

		ilUtil::sendSuccess($this->lng->txt('iass_amend_saved'), true);
		$this->redirect("amend");
	}

	/**
	 * Inint form for gradings
	 *
	 * @param bool 	$may_be_edite
	 *
	 * @return ilPropertyFormGUI
	 */
	protected function initGradingForm($may_be_edited = true) {
		require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTitle($this->lng->txt('iass_edit_record'));

		$examinee_name = $this->examinee->getLastname().', '.$this->examinee->getFirstname();

		$usr_name = new ilNonEditableValueGUI($this->lng->txt('name'),'name');
		$form->addItem($usr_name);
		// record
		$ti = new ilTextAreaInputGUI($this->lng->txt('iass_record'), 'record');
		$ti->setInfo($this->lng->txt('iass_record_info'));
		$ti->setCols(40);
		$ti->setRows(5);
		$ti->setDisabled(!$may_be_edited);
		$form->addItem($ti);

		// description
		$ta = new ilTextAreaInputGUI($this->lng->txt('iass_internal_note'), 'internal_note');
		$ta->setInfo($this->lng->txt('iass_internal_note_info'));
		$ta->setCols(40);
		$ta->setRows(5);
		$ta->setDisabled(!$may_be_edited);
		$form->addItem($ta);

		$learning_progress = new ilSelectInputGUI($this->lng->txt('grading'),'learning_progress');
		$learning_progress->setOptions(
			array(ilIndividualAssessmentMembers::LP_IN_PROGRESS => $this->lng->txt('iass_status_pending')
				, ilIndividualAssessmentMembers::LP_COMPLETED => $this->lng->txt('iass_status_completed')
				, ilIndividualAssessmentMembers::LP_FAILED => $this->lng->txt('iass_status_failed')));
		$learning_progress->setDisabled(!$may_be_edited);
		$form->addItem($learning_progress);

		// notify examinee
		$notify = new ilCheckboxInputGUI($this->lng->txt('iass_notify'), 'notify');
		$notify->setInfo($this->lng->txt('iass_notify_explanation'));
		$notify->setDisabled(!$may_be_edited);
		$form->addItem($notify);

		return $form;
	}

	/**
	 * Fill form with current grading informations
	 *
	 * @param ilPropertyFormGUI 	$a_form
	 * @param ilIndividualAssessmentMember 	$member
	 *
	 * @return ilPropertyFormGUI
	 */
	protected function fillForm(ilPropertyFormGUI $a_form, ilIndividualAssessmentMember $member) {
		$a_form->setValuesByArray(array(
			  'name' => $member->name()
			, 'record' => $member->record()
			, 'internal_note' => $member->internalNote()
			, 'notify' => $member->notify()
			, 'learning_progress' => (int)$member->LPStatus()
			));
		return $a_form;
	}

	/**
	 * Render grading form into template
	 *
	 * @param ilPropertyFormGUI 	$form
	 */
	protected function renderForm(ilPropertyFormGUI $a_form) {
		$this->tpl->setContent($a_form->getHTML());
	}

	/**
	 * Set tabs
	 *
	 * @return null
	 */
	protected function setTabs(ilTabsGUI $tabs) {
		$tabs->clearTargets();
		$tabs->setBackTarget($this->lng->txt('back'),
			$this->getBackLink());
	}

	/**
	 * Get link for backlink
	 *
	 * @return string
	 */
	protected function getBackLink() {
		return $this->ctrl->getLinkTargetByClass(
				array(get_class($this->parent_gui)
					,get_class($this->members_gui))
				,'view');
	}

	/**
	 * Redirect to this with command
	 *
	 * @param string 	$cmd
	 *
	 * @return null
	 */
	protected function redirect($cmd)
	{
		$this->ctrl->redirect($this, $cmd);
	}

	/**
	 * Grading may be edited by current user
	 *
	 * @return bool
	 */
	protected function mayBeEdited() {
		if (!$this->isFinalized()
				&& ($this->userCanGrade() || $this->superiorCanGrade() || $this->mayGradeSelf() || $this->adminCanGrade())
		) {
			return true;
		}

		return false;
	}

	/**
	 * Content of grading may be viewes by current user
	 *
	 * @return bool
	 */
	protected function mayBeViewed()
	{
		if (($this->isFinalized() &&
			($this->userCanGrade() || $this->superiorCanGrade() || $this->mayGradeSelf()))
			|| $this->userCanView()
			|| $this->superiorCanView()
			|| $this->mayViewSelf()
		) {
			return true;
		}

		return false;
	}

	/**
	 * Grading may be edited by current user after finalization
	 *
	 * @return bool
	 */
	protected function mayBeAmended()
	{
		if ($this->isFinalized()
				&& $this->userCanAmend()) {
			return true;
		}

		return false;
	}

	/**
	 * Current user has permission to edit learning progess
	 *
	 * @return bool
	 */
	protected function userCanGrade()
	{
		return !$this->targetWasEditedByOtherUser($this->member) && $this->object->accessHandler()->checkAccessToObj($this->object, 'edit_learning_progress');
	}

	/**
	 * Current user has permission to read learning progress
	 *
	 * @return bool
	 */
	protected function userCanView()
	{
		return $this->object->accessHandler()->checkAccessToObj($this->object, 'read_learning_progress');
	}

	/**
	 * Current user has permission to amend grading
	 *
	 * @return bool
	 */
	protected function userCanAmend()
	{
		return $this->object->accessHandler()->checkAccessToObj($this->object, 'amend_grading');
	}

	/**
	 * Grading was edited by an other user
	 *
	 * @return bool
	 */
	protected function targetWasEditedByOtherUser(ilIndividualAssessmentMember $member) {
		return (int)$member->examinerId() !== (int)$this->examiner->getId()
				&& 0 !== (int)$member->examinerId();
	}

	/**
	 * Is grading finalized
	 *
	 * @return bool
	 */
	protected function isFinalized()
	{
		return $this->member->finalized();
	}

	/**
	 * Save grading informations
	 *
	 * @param string[] 	$post
	 * @param bool 	$keep_examiner
	 *
	 * @return null
	 */
	protected function saveMember(array $post, $keep_examiner = false)
	{
		$this->member = $this->updateDataInMemberByArray($this->member, $post, $keep_examiner);
		$this->object->membersStorage()->updateMember($this->member);
	}

	/**
	 * Updates member object with new grading informations
	 *
	 * @param ilIndividualAssessmentMember 	$member
	 * @param string[] 	$data
	 * @param bool 	$keep_examiner
	 *
	 * @return ilIndividualAssessmentMember
	 */
	protected function updateDataInMemberByArray(ilIndividualAssessmentMember $member, $data, $keep_examiner = false)
	{
		$member = $member->withRecord($data['record'])
					->withInternalNote($data['internal_note'])
					->withLPStatus($data['learning_progress']);

		if (!$keep_examiner) {
			$member = $member->withExaminerId($this->examiner->getId());
		}

		if ($data['notify']  == 1) {
			$member = $member->withNotify(true);
		} else {
			$member = $member->withNotify(false);
		}

		return $member;
	}
}