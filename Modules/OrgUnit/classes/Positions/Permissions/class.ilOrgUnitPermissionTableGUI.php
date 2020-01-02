<?php

/**
 * Class ilOrgUnitPermissionTableGUI
 *
 * @author            Fabian Schmid <fs@studer-raimann.ch>
 */
class ilOrgUnitPermissionTableGUI extends ilTable2GUI
{

    /**
     * @var null|int
     */
    private $ref_id = null;


    /**
     * ilOrgUnitPermissionTableGUI constructor.
     *
     * @param \ILIAS\Modules\OrgUnit\ARHelper\BaseCommands $a_parent_obj
     * @param string                                       $a_parent_cmd
     * @param string                                       $a_ref_id
     */
    public function __construct($a_parent_obj, $a_parent_cmd, $a_ref_id)
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

        $this->addCommandButton(ilPermissionGUI::CMD_SAVE_POSITIONS_PERMISSIONS, $this->lng->txt('save'));
    }


    /**
     * @return int
     */
    public function getRefId()
    {
        return (int) $this->ref_id;
    }


    /**
     * @return int Object-ID of current object
     */
    public function getObjId()
    {
        return (int) ilObject::_lookupObjId($this->getRefId());
    }


    /**
     * @return string
     */
    public function getObjType()
    {
        return (string) ilObject::_lookupType($this->getObjId());
    }


    /**
     * @param array $row
     *
     * @return bool
     */
    public function fillRow($row)
    {
        // Select all
        if (isset($row['show_select_all'])) {
            $this->fillSelectAll($row);

            return true;
        }
        if (isset($row['header_command'])) {
            $this->fillHeaderCommand($row);

            return true;
        }

        foreach ($row as $permission) {
            /**
             * @var $operation \ilOrgUnitOperation
             * @var $position  \ilOrgUnitPosition
             */
            $position = $permission["position"];
            $op_id = $permission["op_id"];
            $operation = $permission["operation"];
            $this->tpl->setCurrentBlock('position_td');
            $this->tpl->setVariable('POSITION_ID', $position->getId());
            $this->tpl->setVariable('PERM_ID', $op_id);

            $this->tpl->setVariable('TXT_PERM', $this->dic()->language()->txt('org_op_' . $operation->getOperationString()));
            //$this->tpl->setVariable('PERM_LONG', $op_id);

            if ($permission['permission_set']) {
                $this->tpl->setVariable('PERM_CHECKED', 'checked="checked"');
            }
            if ($permission['from_template']) {
                $this->tpl->setVariable('PERM_DISABLED', 'disabled="disabled"');
            }

            $this->tpl->parseCurrentBlock();
        }
    }


    public function collectData()
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
                $ilOrgUnitPermission = ilOrgUnitPermissionQueries::getSetForRefId($this->getRefId(), $position->getId());

                $is_template = $ilOrgUnitPermission->isTemplate();
                $from_templates[$position->getId()] = $is_template;

                $ops[] = [
                    "op_id"          => $op->getOperationId(),
                    "operation"      => $op,
                    "position"       => $position,
                    "permission"     => $ilOrgUnitPermission,
                    "permission_set" => $ilOrgUnitPermission->isOperationIdSelected($op->getOperationId()),
                    "from_template"  => $is_template,
                ];
            }
            $perms[] = $ops;
        }

        $perms[] = [
            "show_select_all" => true,
            "positions"       => $positions,
            "ops"             => $ops_ids,
            "template"        => $from_templates,
        ];
        if (ilOrgUnitGlobalSettings::getInstance()
                                   ->isPositionAccessActiveForObject($this->getObjId())) {
            $perms[] = [
                "header_command" => true,
                "positions"      => $positions,
                "template"       => $from_templates,
            ];
        }

        $this->setData($perms);

        return;
    }


    /**
     * @param array $positions
     *
     * @return bool
     */
    protected function initColumns(array $positions)
    {
        foreach ($positions as $position) {
            $this->addColumn($position->getTitle(), '', '', '', false, $position->getDescription());
        }

        return true;
    }


    /**
     * @return \ILIAS\DI\Container
     */
    private function dic()
    {
        return $GLOBALS['DIC'];
    }


    /**
     * @param $row
     */
    protected function fillSelectAll($row)
    {
        /**
         * @var $position \ilOrgUnitPosition
         */
        foreach ($row["positions"] as $position) {
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
     * @param $row
     */
    protected function fillHeaderCommand($row)
    {
        /**
         * @var $position \ilOrgUnitPosition
         */
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
