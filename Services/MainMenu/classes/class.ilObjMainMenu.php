<?php

/**
 * Class ilObjMainMenuGUI
 *
 * @author            Fabian Schmid <fs@studer-raimann.ch>
 */
class ilObjMainMenu extends ilObject {

	/**
	 * ilObjMainMenu constructor.
	 *
	 * @param int  $id
	 * @param bool $call_by_reference
	 */
	public function __construct($id = 0, bool $call_by_reference = true) {
		$this->type = "mme";
		parent::__construct($id, $call_by_reference);
	}


	/**
	 * @inheritDoc
	 */
	public function getPresentationTitle() {
		return "Main Menu";
	}


	/**
	 * @inheritDoc
	 */
	function getLongDescription() {
		return "Add, remove or edit entries of the main menu";
	}
}
