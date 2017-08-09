<?php

use ILIAS\Modules\OrgUnit\ARHelper\BaseCommands;

/**
 * Class ilOrgUnitPositionGUI
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilOrgUnitPositionGUI extends BaseCommands {

	protected function index() {
		$b = ilLinkButton::getInstance();
		$b->setUrl($this->ctrl()->getLinkTarget($this, self::CMD_ADD));
		$b->setCaption(self::CMD_ADD);
		$this->dic()->toolbar()->addButtonInstance($b);

		$table = new ilOrgUnitPositionTableGUI($this, self::CMD_INDEX);
		$this->setContent($table->getHTML());
	}


	protected function add() {
		$form = new ilOrgUnitPositionFormGUI($this, new ilOrgUnitPosition());
		$this->tpl()->setContent($form->getHTML());
	}


	protected function create() {
		$form = new ilOrgUnitPositionFormGUI($this, new ilOrgUnitPosition());
		if ($form->saveObject()) {
			ilUtil::sendSuccess($this->txt('msg_position_created'), true);
			$this->ctrl()->redirect($this, self::CMD_INDEX);
		}

		$this->tpl()->setContent($form->getHTML());
	}


	protected function edit() {
		$position = $this->getPositionFromRequest();
		$form = new ilOrgUnitPositionFormGUI($this, $position);
		$form->fillForm();
		$this->tpl()->setContent($form->getHTML());
	}


	protected function update() {
		$position = $this->getPositionFromRequest();
		$form = new ilOrgUnitPositionFormGUI($this, $position);
		$form->setValuesByPost();
		if ($form->saveObject()) {
			ilUtil::sendSuccess($this->txt('msg_position_udpated'), true);
			$this->ctrl()->redirect($this, self::CMD_INDEX);
		}

		$this->tpl()->setContent($form->getHTML());
	}


	protected function confirm() {
		$position = $this->getPositionFromRequest();
		$confirmation = new ilConfirmationGUI();
		$confirmation->setFormAction($this->ctrl()->getFormAction($this));
		$confirmation->setCancel($this->txt(self::CMD_CANCEL), self::CMD_CANCEL);
		$confirmation->setConfirm($this->txt(self::CMD_DELETE), self::CMD_DELETE);
		$confirmation->setHeaderText($this->txt('msg_confirm_deletion'));
		$confirmation->addItem(self::AR_ID, $position->getId(), $position->getTitle());
		$this->tpl()->setContent($confirmation->getHTML());
	}


	protected function delete() {
		$position = $this->getPositionFromRequest();
		$position->delete();
		ilUtil::sendSuccess($this->txt('msg_deleted'), true);
		$this->ctrl()->redirect($this, self::CMD_INDEX);
	}


	protected function cancel() {
		$this->ctrl()->redirect($this, self::CMD_INDEX);
	}


	public function renderAuthoritiesForm() {
		echo "LOREM";
		exit;
	}


	/**
	 * @return mixed
	 */
	protected function getARIdFromRequest() {
		$get = $this->dic()->http()->request()->getQueryParams()[self::AR_ID];
		$post = $this->dic()->http()->request()->getParsedBody()[self::AR_ID];

		return $post ? $post : $get;
	}


	/**
	 * @return \ilOrgUnitPosition
	 */
	protected function getPositionFromRequest() {
		return ilOrgUnitPosition::find($this->getARIdFromRequest());
	}
}
