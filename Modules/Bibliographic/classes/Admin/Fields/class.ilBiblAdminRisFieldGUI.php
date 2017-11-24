<?php
/**
 * Class ilBiblAdminRisFieldGUI
 *
 * @author       Benjamin Seglias   <bs@studer-raimann.ch>
 * @author       Fabian Schmid <fs@studer-raimann.ch>
 *
 * @ilCtrl_Calls ilBiblAdminRisFieldGUI: ilBiblTranslationGUI
 */

class ilBiblAdminRisFieldGUI extends ilBiblAdminFieldGUI {

	protected function initType() {
		$this->type = ilBiblField::DATA_TYPE_RIS;
	}
}