<?php
use CaT\Plugins\CareerGoal;

include_once("Services/Form/classes/class.ilPropertyFormGUI.php");

class ilCareerGoalSettingsGUI {
	use CareerGoal\Settings\ilFormHelper;

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

	public function __construct(CareerGoal\ilActions $actions, \Closure $txt) {
		global $ilCtrl, $tpl;

		$this->gCtrl = $ilCtrl;
		$this->gTpl = $tpl;

		$this->actions = $actions;
		$this->txt = $txt;
	}

	public function executeCommand() {
		$cmd = $this->gCtrl->getCmd();
		switch($cmd) {
			case self::CMD_SHOW:
			case self::CMD_SAVE:
				$this->$cmd();
				break;
			default:
				throw new \Exception("ilCareerGoalSettingsGUI:: Unknown command ".$cmd);
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

		$ti = new \ilTextInputGUI($this->txt('obj_title'), CareerGoal\ilActions::F_TITLE);
		$ti->setRequired(true);
		$form->addItem($ti);

		$ta = new \ilTextAreaInputGUI($this->txt('obj_description'), CareerGoal\ilActions::F_DESCRIPTION);
		$form->addItem($ta);

		$this->addSettingsFormItems($form);

		$form->addCommandButton(self::CMD_SAVE, $this->txt('obj_save'));
		$form->setFormAction($this->gCtrl->getFormAction($this));

		return $form;
	}

	protected function fillSettingsForm(\ilPropertyFormGUI $form) {
		$values = $this->actions->read();
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