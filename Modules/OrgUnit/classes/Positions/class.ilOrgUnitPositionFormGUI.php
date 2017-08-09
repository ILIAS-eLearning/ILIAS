<?php

use ILIAS\Modules\OrgUnit\ARHelper\BaseForm;

/**
 * Class ilOrgUnitPositionFormGUI
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilOrgUnitPositionFormGUI extends BaseForm {

	/**
	 * @var \ilOrgUnitPosition
	 */
	protected $object;
	const F_TITLE = 'title';
	const F_DESCRIPTION = 'description';


	protected function initFormElements() {
		$te = new ilTextInputGUI($this->txt(self::F_TITLE), self::F_TITLE);
		$te->setRequired(true);
		$this->addItem($te);

		$te = new ilTextAreaInputGUI($this->txt(self::F_DESCRIPTION), self::F_DESCRIPTION);
		$this->addItem($te);

		$ilOrgUnitAuthorityFormGUI = new ilOrgUnitAuthorityFormGUI($this->parent_gui, new ilOrgUnitAuthority());


		$c = new ilCustomInputGUI($this->txt('authorities'));
		$f = $this->parent_gui->dic()->ui()->factory();
		$r = $this->parent_gui->dic()->ui()->renderer();
		$modal = $f->modal()->roundtrip("Modal", $f->legacy($ilOrgUnitAuthorityFormGUI->getHTML()))->withCloseWithKeyboard(false);
		$button = $f->button()
		            ->shy($this->txt("open_authorities_modal"), '#')
		            ->withOnClick($modal->getShowSignal());

		$c->setHtml($r->render([ $button, $modal ]));
		$this->addItem($c);
	}


	public function fillForm() {
		$array = array(
			self::F_TITLE       => $this->object->getTitle(),
			self::F_DESCRIPTION => $this->object->getDescription(),
		);

		$this->setValuesByArray($array);
	}


	/**
	 * returns whether checkinput was successful or not.
	 *
	 * @return bool
	 */
	public function fillObject() {
		if (!$this->checkInput()) {
			return false;
		}

		$this->object->setTitle($this->getInput(self::F_TITLE));
		$this->object->setDescription($this->getInput(self::F_DESCRIPTION));

		return true;
	}
}
