<?php
/**
 * Class ilBiblAdminBibtexFieldGUI
 *
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 */

class ilBiblAdminBibtexFieldGUI extends ilBiblAdminFieldGUI {

	const CMD_SHOW_BIBTEX = 'showBibTex';

	protected function performCommand() {
		$cmd = $this->ctrl->getCmd(self::CMD_SHOW_BIBTEX);
		switch ($cmd) {
			case self::CMD_SHOW_BIBTEX:
				if ($this->dic->access()->checkAccess('write', "", $this->object->getRefId())) {
					$this->{$cmd}();
					break;
				} else {
					ilUtil::sendFailure($this->dic->language()->txt("no_permission"), true);
					break;
				}
		}
	}

	public function showBibTex() {
		$this->setSubTabs(ilBiblField::DATA_TYPE_BIBTEX);
		$this->ctrl->saveParameterByClass(ilBiblAdminFieldTableGUI::class, ilBiblAdminFieldGUI::FIELD_IDENTIFIER);
		$ilBiblAdminFieldTableGUI = new ilBiblAdminFieldTableGUI($this, self::CMD_SHOW_BIBTEX, $this->object, ilBiblField::DATA_TYPE_BIBTEX);
		$this->tpl->setContent($ilBiblAdminFieldTableGUI->getHTML());
	}
}