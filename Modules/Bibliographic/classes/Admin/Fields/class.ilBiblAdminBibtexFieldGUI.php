<?php
/**
 * Class ilBiblAdminBibtexFieldGUI
 *
 * @author       Benjamin Seglias   <bs@studer-raimann.ch>
 * @author       Fabian Schmid <fs@studer-raimann.ch>
 *
 * @ilCtrl_Calls ilBiblAdminBibtexFieldGUI: ilBiblTranslationGUI
 */

class ilBiblAdminBibtexFieldGUI extends ilBiblAdminFieldGUI {

	protected function initType() {
		$this->type = $this->type_factory->getInstanceForType(ilBiblTypeFactoryInterface::DATA_TYPE_BIBTEX);
	}
}