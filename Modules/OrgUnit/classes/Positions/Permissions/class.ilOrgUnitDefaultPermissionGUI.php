<?php

use ILIAS\Modules\OrgUnit\ARHelper\BaseCommands;

/**
 * Class ilOrgUnitDefaultPermissionGUI
 *
 * @author            Fabian Schmid <fs@studer-raimann.ch>
 *
 * @ilCtrl_IsCalledBy ilOrgUnitDefaultPermissionGUI: ilOrgUnitPositionGUI
 */
class ilOrgUnitDefaultPermissionGUI extends BaseCommands {

	protected function reset() {
		ilOrgUnitOperation::resetDB();
		ilOrgUnitOperationContext::resetDB();
		ilOrgUnitPermission::resetDB();

		ilOrgUnitOperationContext::registerNewContext(ilOrgUnitOperationContext::CONTEXT_OBJECT);
		ilOrgUnitOperationContext::registerNewContext(ilOrgUnitOperationContext::CONTEXT_IASS, ilOrgUnitOperationContext::CONTEXT_OBJECT);
		ilOrgUnitOperationContext::registerNewContext(ilOrgUnitOperationContext::CONTEXT_CRS, ilOrgUnitOperationContext::CONTEXT_OBJECT);
		ilOrgUnitOperationContext::registerNewContext(ilOrgUnitOperationContext::CONTEXT_GRP, ilOrgUnitOperationContext::CONTEXT_OBJECT);
		ilOrgUnitOperationContext::registerNewContext(ilOrgUnitOperationContext::CONTEXT_TST, ilOrgUnitOperationContext::CONTEXT_OBJECT);

		ilOrgUnitOperation::registerNewOperationForMultipleContexts(ilOrgUnitOperation::OPERATION_VIEW_LEARNING_PROGRESS, '', array(
			ilOrgUnitOperationContext::CONTEXT_CRS,
			ilOrgUnitOperationContext::CONTEXT_GRP,
			ilOrgUnitOperationContext::CONTEXT_IASS,
		));

		ilOrgUnitOperation::registerNewOperation(ilOrgUnitOperation::OPERATION_VIEW_TEST_RESULTS, '', ilOrgUnitOperationContext::CONTEXT_TST);
	}


	/**
	 * @return int
	 */
	protected function getCurrentPositionId() {
		static $id;
		if (!$id) {
			$id = $this->dic()->http()->request()->getQueryParams()['arid'];
		}

		return (int)$id;
	}


	protected function index() {
		$this->getParentGui()->addSubTabs();
		$ilOrgUnitPermissions = ilOrgUnitPermission::getAllTemplateSetsForAllActivedContexts($this->getCurrentPositionId());
		$ilOrgUnitDefaultPermissionFormGUI = new ilOrgUnitDefaultPermissionFormGUI($this, $ilOrgUnitPermissions);
		$ilOrgUnitDefaultPermissionFormGUI->fillForm();

		$this->setContent($ilOrgUnitDefaultPermissionFormGUI->getHTML());
	}


	protected function update() {
		$this->getParentGui()->addSubTabs();
		$ilOrgUnitPermissions = ilOrgUnitPermission::getAllTemplateSetsForAllActivedContexts($this->getCurrentPositionId());
		$ilOrgUnitDefaultPermissionFormGUI = new ilOrgUnitDefaultPermissionFormGUI($this, $ilOrgUnitPermissions);
		if ($ilOrgUnitDefaultPermissionFormGUI->saveObject()) {
			ilUtil::sendSuccess($this->txt('msg_success_permission_saved'), true);
			$this->cancel();
		}

		$this->setContent($ilOrgUnitDefaultPermissionFormGUI->getHTML());
	}


	public function oldInde() {
		//			$ilOrgUnitDefaultPermissionTableGUI = new ilOrgUnitDefaultPermissionTableGUI($this, self::CMD_INDEX, );
		//		$start = new ilOrgUnitDefaultPermissionTableGUI($this, self::CMD_INDEX, new ilOrgUnitPermission());
		//		$start->start();
		//		$sets[] = $start->getHTML();
		//		$i = 0;
	}
}


