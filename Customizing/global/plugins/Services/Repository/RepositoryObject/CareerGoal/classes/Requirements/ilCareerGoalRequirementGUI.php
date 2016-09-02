<?php

namespace CaT\Plugins\CareerGoal\Requirements;

class ilCareerGoalRequirementGUI {
	use ilFormHelper;

	const MODE_NEW = "modeNew";
	const MODE_EDIT = "modeEdit";

	public function __construct($parent_obj, $mode, $obj_id = null) {
		global $ilCtrl, $tpl;

		$this->gCtrl = $ilCtrl;
		$this->gTpl = $tpl;

		$this->parent_obj = $parent_obj;
		$this->possible_cmd = $parent_obj->getPossibleCMD();
		$this->txt = $parent_obj->getTXTClosure();
		$this->mode = $mode;
		$this->obj_id = $obj_id;
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

	public function render(\ilPropertyFormGUI $form = null) {
		switch($this->mode) {
			case self::MODE_NEW:
				$this->newRequirement($form);
				break;
			case self::MODE_EDIT:
				$this->editRequirement($form);
				break;
		}
	}

	public function save() {
		if($this->checkForm()) {
			$this->parent_obj->getActions()->createRequirement($_POST);
			$red = $this->gCtrl->getLinkTarget($this->parent_obj, $this->possible_cmd["CMD_SHOW"], "", false, false);
			\ilUtil::sendSuccess($this->txt("requirement_saved"), true);
			\ilUtil::redirect($red);
		}
	}

	public function update() {
		if($this->checkForm()) {
			$this->parent_obj->getActions()->updateRequirement($_POST);
			$red = $this->gCtrl->getLinkTarget($this->parent_obj, $this->possible_cmd["CMD_SHOW"], "", false, false);
			\ilUtil::sendSuccess($this->txt("requirement_updated"), true);
			\ilUtil::redirect($red);
		}
	}

	protected function checkForm() {
		$form = $this->initForm();

		if(!$form->checkInput()) {
			$form->setValuesByPost();
			$this->render($form);
			return;
		}

		return true;
	}

	protected function initForm() {
		$form = new \ilPropertyFormGUI();
		$form->setFormAction($this->gCtrl->getFormAction($this->parent_obj));
		$form->setTitle($this->txt("new_requirement"));

		$this->addRequirementFormItems($form);

		return $form;
	}

	protected function newRequirement(\ilPropertyFormGUI $form = null) {
		if($form === null) {
			$form = $this->initForm();
			$form->setValuesByArray($this->parent_obj->getActions()->readNewRequirement());
		}

		$form->addCommandButton($this->possible_cmd["CMD_SAVE"], $this->txt("save"));
		$form->addCommandButton($this->possible_cmd["CMD_SHOW"], $this->txt("cancel"));

		$this->gTpl->setContent($form->getHtml());
	}

	protected function editRequirement(\ilPropertyFormGUI $form = null) {
		if($form === null) {
			$form = $this->initForm();
			$form->setValuesByArray($this->parent_obj->getActions()->readRequirement($this->obj_id));
		}

		$form->addCommandButton($this->possible_cmd["CMD_UPDATE"], $this->txt("update"));
		$form->addCommandButton($this->possible_cmd["CMD_SHOW"], $this->txt("cancel"));

		$this->gTpl->setContent($form->getHtml());
	}
}