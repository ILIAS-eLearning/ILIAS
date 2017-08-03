<?php

/**
 * Class ilOrgUnitPositionGUI
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilOrgUnitPositionGUI extends \ILIAS\Modules\OrgUnit\CtrlHelper\BaseCommands {

	use \ILIAS\Modules\OrgUnit\CtrlHelper\Executer;


	protected function index() {
//		$b = new arBuilder(new ilOrguAuthority());
//		$b->generateDBUpdateForInstallation();

//		$n = new ilOrgUnitPosition();
//		$n->setTitle("Testerei Eins");
//		$n->setDescription("Testerei Eins Desc");
//		$n->create();


		$b = ilLinkButton::getInstance();
		$b->setUrl($this->getCtrl()->getLinkTarget($this, self::CMD_ADD));
		$b->setCaption(self::CMD_ADD);
		$this->getToolbar()->addButtonInstance($b);

		$ilOrgUnitPositionTableGUI = new ilOrgUnitPositionTableGUI($this, self::CMD_INDEX);
		$this->setContent($ilOrgUnitPositionTableGUI->getHTML());
	}


	protected function add() {
	}
}
