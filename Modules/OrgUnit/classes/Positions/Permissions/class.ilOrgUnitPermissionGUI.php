<?php

use ILIAS\Modules\OrgUnit\ARHelper\BaseCommands;

/**
 * Class ilOrgUnitPermissionGUI
 *
 * @author            Fabian Schmid <fs@studer-raimann.ch>
 *
 * @ilCtrl_IsCalledBy ilOrgUnitPermissionGUI: ilObjOrgUnitGUI±
 */
class ilOrgUnitPermissionGUI extends BaseCommands {

	const PERMISSION_VIEW_LEARNING_PROGRESS = 'viewlp';
	const PERMISSION_VIEW_TEST_RESULTS = 'viewtstr';


	protected function index() {
		$table = new ilOrgUnitPermissionTableGUI($this, self::CMD_INDEX, $this->getParentRefId());
	}
}
