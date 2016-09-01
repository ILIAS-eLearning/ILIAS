<?php

use CaT\Plugins\TalentAssessment;

include_once("Services/Form/classes/class.ilPropertyFormGUI.php");

class ilTalentAssessmentSettingsGUI {
	use TalentAssessment\Settings\ilFormHelper;

	const CMD_SHOW = "showSettings";
	const CMD_SAVE = "saveSettings";

	/**
	 * @var Closure
	 */
	protected $txt;

	/**
	 * @var ilActions
	 */
	protected $actions;

	public function __construct(TalentAssessment\ilActions $actions, \Closure $txt, $obj_id, $potential) {
		global $ilCtrl, $tpl;

		$this->gCtrl = $ilCtrl;
		$this->gTpl = $tpl;

		$this->actions = $actions;
		$this->txt = $txt;
		$this->obj_id = $obj_id;
		$this->potential = $potential;
	}

	public function executeCommand() {
		$cmd = $this->gCtrl->getCmd();
		switch($cmd) {
			case self::CMD_SHOW:
			case self::CMD_SAVE:
				$this->$cmd();
				break;
			default:
				throw new \Exception("ilTalentAssessmentSettingsGUI:: Unknown command ".$cmd);
		}
	}

	protected function showSettings() {
		$form = $this->initSettingsForm();
		$this->fillSettingsForm($form);
		$this->gTpl->setContent($form->getHTML());
	}

	/**
	 * @param 	string	$code
	 * @return	string
	 */
	public function txt($code) {
		assert('is_string($code)');
		$txt = $this->txt;

		return $txt($code);
	}

	protected function initSettingsForm() {
		$form = new \ilPropertyFormGUI();
		$form->setTitle($this->txt('obj_edit_settings'));

		$ti = new \ilTextInputGUI($this->txt('obj_title'), TalentAssessment\ilActions::F_TITLE);
		$ti->setRequired(true);
		$form->addItem($ti);

		$ta = new \ilTextAreaInputGUI($this->txt('obj_description'), TalentAssessment\ilActions::F_DESCRIPTION);
		$form->addItem($ta);

		$career_goal_options = $this->actions->getCareerGoalsOptions();
		$venue_options = $this->actions->getVenueOptions();
		$org_unit_options = $this->actions->getOrgUnitOptions();
		$this->addSettingsFormItemsUpdate($form, $career_goal_options, $venue_options, $org_unit_options, $this->actions->ObservationStarted($this->obj_id));

		$form->addCommandButton(self::CMD_SAVE, $this->txt('obj_save'));
		$form->setFormAction($this->gCtrl->getFormAction($this));

		return $form;
	}

	protected function fillSettingsForm(\ilPropertyFormGUI $form) {
		$values = $this->actions->read();
		$values = $this->actions->setPotentialToValues($values, $this->txt($this->actions->potentialText()));

		if($values[TalentAssessment\ilActions::F_FIRSTNAME] === null) {
			\ilUtil::sendFailure($this->txt("no_valid_username"));
		}
		$form->setValuesByArray($values);
	}

	protected function saveSettings() {
		$form = $this->initSettingsForm();
		if($form->checkInput()) {
			$post = $_POST;
			$this->actions->update($post);
			\ilUtil::sendSuccess($this->txt("saved"), true);
			$this->gCtrl->redirect($this, self::CMD_SHOW);
		}
		else {
			$form->setValuesByPost();
			\ilUtil::sendFailure($this->txt("not_saved"), true);
			$this->gTpl->setContent($form->getHTML());
		}
	}
}