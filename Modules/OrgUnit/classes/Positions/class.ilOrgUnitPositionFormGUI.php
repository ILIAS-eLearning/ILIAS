<?php

use ILIAS\Modules\OrgUnit\ARHelper\BaseForm;

/**
 * Class ilOrgUnitPositionFormGUI
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilOrgUnitPositionFormGUI extends BaseForm {

	const F_AUTHORITIES = "authorities";
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

//		$ilOrgUnitAuthorityFormGUI = new ilOrgUnitAuthorityFormGUI($this->parent_gui, new ilOrgUnitAuthority());

//		$c = new ilCustomInputGUI($this->txt('authorities'), 'null');
//		$f = $this->parent_gui->dic()->ui()->factory();
//		$r = $this->parent_gui->dic()->ui()->renderer();
//		$modal = $f->modal()->roundtrip("Modal", $f->legacy($ilOrgUnitAuthorityFormGUI->getHTML()))->withCloseWithKeyboard(false);
//		$button = $f->button()
//		            ->shy($this->txt("open_authorities_modal"), '#')
//		            ->withOnClick($modal->getShowSignal());
//
//		$c->setHtml($r->render([ $button, $modal ]));

		$c = new ilOrgUnitAuthorityInputGUI($this->txt(self::F_AUTHORITIES), self::F_AUTHORITIES);
		$this->addItem($c);

//		$m = new ilOrgUnitMultiLineInputGUI($this->txt(self::F_AUTHORITIES), self::F_AUTHORITIES);
//
//		$s1 = new ilSelectInputGUI();
//		$s1->setOptions(array(
//			1,32,3,4,5
//		));
//		$m->addInput($s1);
//		$this->addItem($m);
	}


	public function fillForm() {
		$array = array(
			self::F_TITLE       => $this->object->getTitle(),
			self::F_DESCRIPTION => $this->object->getDescription(),
			self::F_AUTHORITIES => $this->object->getAuthorities(),
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
