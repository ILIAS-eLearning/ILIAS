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
 * Class ilOrgUnitPermissionTableGUI
 * @author            Fabian Schmid <fs@studer-raimann.ch>
 */
class ilOrgUnitDefaultPermissionTableGUI extends ilTable2GUI
{
    protected string $context_string;
    private ?ilOrgUnitPermission $ilOrgUnitPermission = null;

    /**
     * ilOrgUnitDefaultPermissionTableGUI constructor.
     */
    public function __construct(object $a_parent_obj, string $a_parent_cmd, ilOrgUnitPermission $ilOrgUnitPermission)
    {
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->ilOrgUnitPermission = $ilOrgUnitPermission;
        if ($this->ilOrgUnitPermission->getId() !== 0) {
            $this->context_string = $this->ilOrgUnitPermission->getContext()->getContext();
        }
        $this->dic()->language()->loadLanguageModule('orgu');

        $this->setId('objpositionperm_' . $this->type);

        $this->dic()
             ->ui()
             ->mainTemplate()
             ->addJavaScript('./Services/AccessControl/js/ilPermSelect.js');

        $this->setTitle(
            $this->dic()->language()->txt('orgu_permission_settings_'
                . $this->context_string)
        );
        $this->setEnableHeader(true);
        $this->disable('sort');
        $this->setFormAction($this->dic()->ctrl()->getFormAction($a_parent_obj, $a_parent_cmd));
        $this->disable('numinfo');
        $this->setRowTemplate("tpl.obj_role_template_perm_row.html", "Modules/OrgUnit");
        $this->setShowRowsSelector(false);
        $this->setDisableFilterHiding(true);
        $this->setData($this->ilOrgUnitPermission->getPossibleOperations());
        $this->setOpenFormTag(false);
        $this->setCloseFormTag(false);
    }

    public function start(): void
    {
        $this->setEnableHeader(false);
        $this->setOpenFormTag(true);
        $this->setNoEntriesText('');
        $this->setData(array());
        $this->addMultiCommand(BaseCommands::CMD_UPDATE, $this->lng->txt('save'));
    }

    public function end(): void
    {
        $this->setCloseFormTag(true);
        $this->addCommandButton(BaseCommands::CMD_UPDATE, $this->lng->txt('save'));
    }

    public function fillRow(array $a_set): void
    {
        $this->tpl->setVariable('OBJ_TYPE', $this->context_string);
        $this->tpl->setVariable('PERM_PERM_ID', $a_set['operation_id']);
        if ($this->ilOrgUnitPermission->isOperationIdSelected($a_set['operation_id'])) {
            $this->tpl->setVariable('PERM_CHECKED', "checked=checked");
        }
        // $this->tpl->setVariable('PERM_DISABLED', "disabled=disabled");
        $this->tpl->setVariable('DESC_TYPE', $this->context_string);
        $this->tpl->setVariable('DESC_PERM_ID', $a_set['operation_id']);
        $this->tpl->setVariable('TXT_PERMISSION', $this->dic()->language()->txt('orgu_op_'
            . $a_set['operation_string']));
    }

    public function collectData(): void
    {
    }

    private function dic(): \ILIAS\DI\Container
    {
        return $GLOBALS['DIC'];
    }
}
