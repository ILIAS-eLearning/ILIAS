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

/**
 * Class ilOrgUnitPermissionTableGUI
 * @author            Fabian Schmid <fs@studer-raimann.ch>
 */
class ilOrgUnitPermissionTableGUI extends ilTable2GUI
{
    private int $ref_id = 0;

    public function __construct(object $a_parent_obj, string $a_parent_cmd, int $a_ref_id)
    {
        global $ilCtrl, $tpl;

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->lng->loadLanguageModule('rbac');
        $this->lng->loadLanguageModule("orgu");

        $this->ref_id = $a_ref_id;

        $this->setId('objpositionperm_' . $this->ref_id);

        $tpl->addJavaScript('./Services/AccessControl/js/ilPermSelect.js');

        $this->setTitle($this->lng->txt('org_permission_settings'));
        $this->setEnableHeader(true);
        $this->disable('sort');
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj, $a_parent_cmd));
        $this->disable('numinfo');
        $this->setRowTemplate("tpl.obj_position_perm_row.html", "Modules/OrgUnit");
        $this->setShowRowsSelector(false);
        $this->setDisableFilterHiding(true);
        $this->setNoEntriesText($this->lng->txt('msg_no_roles_of_type'));

        $this->addCommandButton(\ILIAS\Modules\OrgUnit\ARHelper\BaseCommands::CMD_UPDATE, $this->lng->txt('save'));
    }

    public function getRefId(): int
    {
        return $this->ref_id;
    }

    /**
     * @return int Object-ID of current object
     */
    public function getObjId(): int
    {
        return ilObject::_lookupObjId($this->getRefId());
    }

    public function getObjType(): string
    {
        return ilObject::_lookupType($this->getObjId());
    }

    /**
     * @throws ilTemplateException
     */
    public function fillRow(array $a_set) : void
    {
        // Select all
        if (isset($a_set['show_select_all'])) {
            $this->fillSelectAll($a_set);

            return;
        }
        if (isset($a_set['header_command'])) {
            $this->fillHeaderCommand($a_set);

            return;
        }

        $objdefinition = $this->dic()['objDefinition'];
        $is_plugin = $objdefinition->isPlugin($this->getObjType());

        foreach ($a_set as $permission) {
            $position = $permission["position"];
            $op_id = $permission["op_id"];
            $operation = $permission["operation"];
            $this->tpl->setCurrentBlock('position_td');
            $this->tpl->setVariable('POSITION_ID', $position->getId());
            $this->tpl->setVariable('PERM_ID', $op_id);

            if ($is_plugin) {
                $label = ilObjectPlugin::lookupTxtById($this->getObjType(), $operation->getOperationString());
            } else {
                $label = $this->dic()->language()->txt('org_op_' . $operation->getOperationString());
            }

            $this->tpl->setVariable('TXT_PERM', $label);
            $this->tpl->setVariable('PERM_LONG', $op_id);

            if ($permission['permission_set']) {
                $this->tpl->setVariable('PERM_CHECKED', 'checked="checked"');
            }
            if ($permission['from_template']) {
                $this->tpl->setVariable('PERM_DISABLED', 'disabled="disabled"');
            }

            $this->tpl->parseCurrentBlock();
        }
    }

    public function collectData(): void
    {
        $positions = ilOrgUnitPosition::get();

        $this->initColumns($positions);

        $perms = [];

        $operations = ilOrgUnitOperationQueries::getOperationsForContextName($this->getObjType());
        $ops_ids = [];
        $from_templates = [];
        foreach ($operations as $op) {
            $ops_ids[] = $op->getOperationId();

            $ops = [];
            foreach ($positions as $position) {
                $ilOrgUnitPermission = ilOrgUnitPermissionQueries::getSetForRefId($this->getRefId(),
                    $position->getId());

                $is_template = $ilOrgUnitPermission->isTemplate();
                $from_templates[$position->getId()] = $is_template;

                $ops[] = [
                    "op_id" => $op->getOperationId(),
                    "operation" => $op,
                    "position" => $position,
                    "permission" => $ilOrgUnitPermission,
                    "permission_set" => $ilOrgUnitPermission->isOperationIdSelected($op->getOperationId()),
                    "from_template" => $is_template,
                ];
            }
            $perms[] = $ops;
        }

        $perms[] = [
            "show_select_all" => true,
            "positions" => $positions,
            "ops" => $ops_ids,
            "template" => $from_templates,
        ];
        if (ilOrgUnitGlobalSettings::getInstance()
                                   ->isPositionAccessActiveForObject($this->getObjId())
        ) {
            $perms[] = [
                "header_command" => true,
                "positions" => $positions,
                "template" => $from_templates,
            ];
        }

        $this->setData($perms);
    }

    protected function initColumns(array $positions): bool
    {
        foreach ($positions as $position) {
            $this->addColumn($position->getTitle(), '', '', '', false, $position->getDescription());
        }

        return true;
    }

    private function dic(): \ILIAS\DI\Container
    {
        return $GLOBALS['DIC'];
    }

    /**
     * @throws ilTemplateException
     */
    protected function fillSelectAll(array $row): void
    {
        foreach ($row["positions"] as $position) {
            assert($position instanceof ilOrgUnitPosition);
            $this->tpl->setCurrentBlock('position_select_all');
            $id = $position->getId();
            $this->tpl->setVariable('JS_ROLE_ID', $id);
            $this->tpl->setVariable('JS_SUBID', 0);
            $this->tpl->setVariable('JS_ALL_PERMS', "['" . implode("','", $row['ops']) . "']");
            $this->tpl->setVariable('JS_FORM_NAME', $this->getFormName());
            $this->tpl->setVariable('TXT_SEL_ALL', $this->lng->txt('select_all'));
            if ($row["template"][$id]) {
                $this->tpl->setVariable('ALL_DISABLED', "disabled='disabled'");
            }
            $this->tpl->parseCurrentBlock();
        }
    }

    /**
     * @throws ilTemplateException
     */
    protected function fillHeaderCommand(array $row): void
    {
        foreach ($row["positions"] as $position) {
            $this->tpl->setCurrentBlock('header_command');
            $this->tpl->setVariable('POSITION_ID', $position->getId());
            $this->tpl->setVariable('HEADER_COMMAND_TXT', $this->dic()
                                                               ->language()
                                                               ->txt('positions_override_operations'));
            if (ilOrgUnitPermissionQueries::hasLocalSet($this->getRefId(), $position->getId())) {
                $this->tpl->setVariable('HEADER_CHECKED', "checked='checked'");
            }

            $this->tpl->parseCurrentBlock();
        }
    }
}
