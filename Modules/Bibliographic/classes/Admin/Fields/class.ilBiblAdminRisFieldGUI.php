<?php
/**
 * Class ilBiblAdminRisFieldGUI
 *
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 */

class ilBiblAdminRisFieldGUI extends ilBiblAdminFieldGUI {

	const CMD_SHOW_RIS = 'showRis';

	protected function performCommand() {
		$cmd = $this->ctrl->getCmd(self::CMD_SHOW_RIS);
		switch ($cmd) {
			case self::CMD_SHOW_RIS:
				if ($this->dic->access()->checkAccess('write', "", $this->object->getRefId())) {
					$this->{$cmd}();
					break;
				} else {
					ilUtil::sendFailure($this->dic->language()->txt("no_permission"), true);
					break;
				}
		}
	}

	public function showRis() {
		$this->setSubTabs(ilBiblField::DATA_TYPE_RIS);
		$this->ctrl->saveParameterByClass(ilBiblAdminFieldTableGUI::class, ilBiblAdminFieldGUI::FIELD_IDENTIFIER);
		$ilBiblAdminFieldTableGUI = new ilBiblAdminFieldTableGUI($this, self::CMD_SHOW_RIS, $this->object, ilBiblField::DATA_TYPE_RIS);
		$this->tpl->setContent($ilBiblAdminFieldTableGUI->getHTML());
	}
}