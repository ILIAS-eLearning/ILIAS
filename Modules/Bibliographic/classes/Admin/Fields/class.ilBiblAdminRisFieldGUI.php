<?php
/**
 * Class ilBiblAdminRisFieldGUI
 *
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 */

class ilBiblAdminRisFieldGUI extends ilBiblAdminFieldGUI {

	public function initType() {
		$this->type = ilBiblField::DATA_TYPE_RIS;
	}
}