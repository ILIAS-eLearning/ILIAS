<?php

/**
 * Class ilOrgUnitPermissionTableGUI
 *
 * @author            Fabian Schmid <fs@studer-raimann.ch>
 */
class ilOrgUnitDefaultPermissionTableGUI extends ilTable2GUI
{

    /**
     * @var string
     */
    protected $context_string;
    /**
     * @var ilOrgUnitPermission
     */
    private $ilOrgUnitPermission = null;


    /**
     * ilOrgUnitDefaultPermissionTableGUI constructor.
     *
     * @param object               $a_parent_obj
     * @param string               $a_parent_cmd
     * @param \ilOrgUnitPermission $ilOrgUnitPermission
     */
    public function __construct($a_parent_obj, $a_parent_cmd, ilOrgUnitPermission $ilOrgUnitPermission)
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

        $this->setTitle($this->dic()->language()->txt('orgu_permission_settings_'
                                                      . $this->context_string));
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


    public function start()
    {
        $this->setEnableHeader(false);
        $this->setOpenFormTag(true);
        $this->setNoEntriesText('');
        $this->setData(array());
        $this->addMultiCommand(ilOrgUnitDefaultPermissionGUI::CMD_UPDATE, $this->lng->txt('save'));
    }


    public function end()
    {
        $this->setCloseFormTag(true);
        $this->addCommandButton(ilOrgUnitDefaultPermissionGUI::CMD_UPDATE, $this->lng->txt('save'));
    }


    /**
     * @param \ilOrgUnitOperation $row
     *
     * @return bool
     */
    public function fillRow($row)
    {
        $this->tpl->setVariable('OBJ_TYPE', $this->context_string);
        $this->tpl->setVariable('PERM_PERM_ID', $row->getOperationId());
        if ($this->ilOrgUnitPermission->isOperationIdSelected($row->getOperationId())) {
            $this->tpl->setVariable('PERM_CHECKED', "checked=checked");
        }
        // $this->tpl->setVariable('PERM_DISABLED', "disabled=disabled");
        $this->tpl->setVariable('DESC_TYPE', $this->context_string);
        $this->tpl->setVariable('DESC_PERM_ID', $row->getOperationId());
        $this->tpl->setVariable('TXT_PERMISSION', $this->dic()->language()->txt('orgu_op_'
                                                                                . $row->getOperationString()));
    }


    public function collectData()
    {
    }


    /**
     * @return \ILIAS\DI\Container
     */
    private function dic()
    {
        return $GLOBALS['DIC'];
    }
}
