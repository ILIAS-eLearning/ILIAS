<?php
/**
 * Class ilBiblAdminBibtexFieldGUI
 *
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 */

class ilBiblAdminBibtexFieldGUI extends ilBiblAdminFieldGUI {


	protected function initType() {
		$this->type = ilBiblField::DATA_TYPE_BIBTEX;
	}
}