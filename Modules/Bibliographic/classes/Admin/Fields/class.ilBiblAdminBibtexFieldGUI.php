<?php
/**
 * Class ilBiblAdminBibtexFieldGUI
 *
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 */

class ilBiblAdminBibtexFieldGUI extends ilBiblAdminFieldGUI {

	public function __construct() {
		$this->initType();
		parent::__construct();
	}

	protected function initType() {
		$this->type = ilBiblField::DATA_TYPE_BIBTEX;
	}
}