<?php
/**
 * Class ilBiblAdminRisFieldGUI
 *
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 */

class ilBiblAdminRisFieldGUI extends ilBiblAdminFieldGUI {


	protected function initType() {
		$this->type = ilBiblField::DATA_TYPE_RIS;
	}
}