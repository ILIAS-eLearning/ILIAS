<?php

use ILIAS\Modules\OrgUnit\ARHelper\BaseCommands;

/**
 * Class ilOrgUnitDefaultPermissionFormGUI
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilOrgUnitDefaultPermissionFormGUI extends ilPropertyFormGUI
{
    public const F_OPERATIONS = 'operations';
    protected BaseCommands $parent_gui;
    /** @var ilOrgUnitPermission[] */
    protected array $ilOrgUnitPermissions = [];

    /**
     * ilOrgUnitDefaultPermissionFormGUI constructor.
     * @param ilOrgUnitPermission[] $ilOrgUnitPermissionsFilter
     */
    public function __construct(BaseCommands $parent_gui, array $ilOrgUnitPermissionsFilter)
    {
        $this->parent_gui = $parent_gui;
        $this->ilOrgUnitPermissions = $ilOrgUnitPermissionsFilter;
        $this->dic()->ctrl()->saveParameter($parent_gui, 'arid');
        $this->setFormAction($this->dic()->ctrl()->getFormAction($this->parent_gui));
        $this->initFormElements();
        $this->initButtons();
        $this->setTarget('_top');
        parent::__construct();
    }

    final public function saveObject() : bool
    {
        if ($this->fillObject() === false) {
            return false;
        }
        foreach ($this->ilOrgUnitPermissions as $ilOrgUnitPermission) {
            $ilOrgUnitPermission->update();
        }

        return true;
    }

    private function initButtons() : void
    {
        $this->setTitle($this->txt("form_title_org_default_permissions_"
            . BaseCommands::CMD_UPDATE));
        $this->addCommandButton(BaseCommands::CMD_UPDATE, $this->txt('save', true));
        $this->addCommandButton(BaseCommands::CMD_CANCEL, $this->txt(BaseCommands::CMD_CANCEL));
    }

    private function initFormElements() : void
    {
        foreach ($this->ilOrgUnitPermissions as $ilOrgUnitPermission) {
            $header = new ilFormSectionHeaderGUI();
            $context = $ilOrgUnitPermission->getContext()->getContext();
            $header->setTitle($this->txt("obj_{$context}", false));
            $this->addItem($header);

            // Checkboxes
            foreach ($ilOrgUnitPermission->getPossibleOperations() as $operation) {
                $title = $this->txt("org_op_{$operation->getOperationString()}", false);
                $id = $operation->getOperationId();
                $cb = new ilCheckboxInputGUI($title, "operations[{$context}][{$id}]");
                $this->addItem($cb);
            }
        }
    }

    final public function fillForm() : void
    {
        $operations = array();
        foreach ($this->ilOrgUnitPermissions as $ilOrgUnitPermission) {
            $context = $ilOrgUnitPermission->getContext()->getContext();
            foreach ($ilOrgUnitPermission->getPossibleOperations() as $operation) {
                $id = $operation->getOperationId();
                $operations["operations[{$context}][{$id}]"] = $ilOrgUnitPermission->isOperationIdSelected($operation->getOperationId());
            }
        }
        $this->setValuesByArray($operations);
    }

    private function fillObject() : bool
    {
        if (!$this->checkInput()) {
            return false;
        }
        $sent_operation_ids = $this->getInput(self::F_OPERATIONS);
        foreach ($this->ilOrgUnitPermissions as $ilOrgUnitPermission) {
            $operations = [];
            $context = $ilOrgUnitPermission->getContext()->getContext();
            foreach ($ilOrgUnitPermission->getPossibleOperations() as $operation) {
                $id = $operation->getOperationId();
                if ($sent_operation_ids[$context][$id]) {
                    $operations[] = ilOrgUnitOperation::find($id);
                }
            }
            $ilOrgUnitPermission->setOperations($operations);
        }

        return true;
    }

    /**
     * @return ilOrgUnitPermission[]
     */
    public function getIlOrgUnitPermissions() : array
    {
        return $this->ilOrgUnitPermissions;
    }

    /**
     * @param ilOrgUnitPermission[] $ilOrgUnitPermissions
     */
    public function setIlOrgUnitPermissions(array $ilOrgUnitPermissions) : void
    {
        $this->ilOrgUnitPermissions = $ilOrgUnitPermissions;
    }

    private function dic() : \ILIAS\DI\Container
    {
        return $GLOBALS["DIC"];
    }

    private function txt(string $key) : string
    {
        return $this->parent_gui->txt($key);
    }
}
