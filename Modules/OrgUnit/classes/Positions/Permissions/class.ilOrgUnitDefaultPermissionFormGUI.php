<?php
/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 ********************************************************************
 */

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
    protected ilObjectDefinition $objectDefinition;
    protected \ilLanguage $language;

    /**
     * ilOrgUnitDefaultPermissionFormGUI constructor.
     * @param \ILIAS\Modules\OrgUnit\ARHelper\BaseCommands $parent_gui
     * @param ilOrgUnitPermission[]                        $ilOrgUnitPermissionsFilter
     * @param ilObjectDefinition                           $objectDefinition
     */
    public function __construct(
        BaseCommands $parent_gui,
        array $ilOrgUnitPermissionsFilter,
        ilObjectDefinition $objectDefinition
    ) {
        global $DIC;
        $this->parent_gui = $parent_gui;
        $this->language = $DIC->language();
        $this->ilOrgUnitPermissions = $ilOrgUnitPermissionsFilter;
        $this->dic()->ctrl()->saveParameter($parent_gui, 'arid');
        $this->setFormAction($this->dic()->ctrl()->getFormAction($this->parent_gui));
        $this->objectDefinition = $objectDefinition;
        $this->initFormElements();
        $this->initButtons();
        $this->setTarget('_top');
        parent::__construct();
    }

    public function saveObject(): bool
    {
        if ($this->fillObject() === false) {
            return false;
        }
        foreach ($this->ilOrgUnitPermissions as $ilOrgUnitPermission) {
            $ilOrgUnitPermission->update();
        }

        return true;
    }

    private function initButtons(): void
    {
        $this->setTitle($this->txt("form_title_org_default_permissions_"
            . BaseCommands::CMD_UPDATE));
        $this->addCommandButton(BaseCommands::CMD_UPDATE, $this->txt('save', true));
        $this->addCommandButton(BaseCommands::CMD_CANCEL, $this->txt(BaseCommands::CMD_CANCEL));
    }

    private function initFormElements(): void
    {
        foreach ($this->ilOrgUnitPermissions as $ilOrgUnitPermission) {
            if ($ilOrgUnitPermission->getContext() !== null) {
                $header = new ilFormSectionHeaderGUI();
                $context = $ilOrgUnitPermission->getContext()->getContext();
                $header->setTitle($this->getTitleForFormHeaderByContext($context));
                $this->addItem($header);
            }



            // Checkboxes
            foreach ($ilOrgUnitPermission->getPossibleOperations() as $operation) {
                $title = $this->txt("org_op_{$operation->getOperationString()}");
                $id = $operation->getOperationId();
                $cb = new ilCheckboxInputGUI($title, "operations[{$context}][{$id}]");
                $this->addItem($cb);
            }
        }
    }

    public function fillForm(): void
    {
        $operations = array();
        foreach ($this->ilOrgUnitPermissions as $ilOrgUnitPermission) {
            if ($ilOrgUnitPermission->getContext() !== null) {
                $context = $ilOrgUnitPermission->getContext()->getContext();
                foreach ($ilOrgUnitPermission->getPossibleOperations() as $operation) {
                    $id = $operation->getOperationId();
                    $operations["operations[{$context}][{$id}]"] = $ilOrgUnitPermission->isOperationIdSelected($operation->getOperationId());
                }
            }
        }
        $this->setValuesByArray($operations);
    }

    private function fillObject(): bool
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
    public function getIlOrgUnitPermissions(): array
    {
        return $this->ilOrgUnitPermissions;
    }

    /**
     * @param ilOrgUnitPermission[] $ilOrgUnitPermissions
     */
    public function setIlOrgUnitPermissions(array $ilOrgUnitPermissions): void
    {
        $this->ilOrgUnitPermissions = $ilOrgUnitPermissions;
    }

    private function dic(): \ILIAS\DI\Container
    {
        return $GLOBALS["DIC"];
    }

    private function txt(string $key): string
    {
        return $this->language->txt($key);
    }

    protected function getTitleForFormHeaderByContext(string $context)
    {
        $lang_code = "obj_{$context}";
        if ($this->objectDefinition->isPlugin($context)) {
            return ilObjectPlugin::lookupTxtById($context, $lang_code);
        }

        return $this->txt($lang_code);
    }
}
