<?php

/**
 * Class ilDclTableViewEditFormGUI
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ilDclTableViewEditFormGUI extends ilPropertyFormGUI
{
    protected ilDclTableView $tableview;
    protected ?ilDclTable $table = null;
    protected ilDclTableViewEditGUI $parent_gui;

    public function __construct(ilDclTableViewEditGUI $parent_gui, ilDclTableView $tableview, ?ilDclTable $table = null)
    {
        global $DIC;
        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];
        parent::__construct();
        $this->lng = $lng;
        $this->ctrl = $ilCtrl;
        $this->parent_gui = $parent_gui;
        $this->tableview = $tableview;
        $this->table = $table;
        $this->ctrl->saveParameterByClass('ilDclTableViewGUI', 'tableview_id');
        $this->initForm();
    }

    protected function initForm(): void
    {
        global $DIC;
        $rbacreview = $DIC['rbacreview'];

        $this->setTitle($this->tableview->getId() ? $this->lng->txt('dcl_view_settings') : $this->lng->txt('dcl_tableview_add'));

        //title
        $item = new ilTextInputGUI($this->lng->txt('title'), 'title');
        $item->setValue($this->tableview->getTitle());
        $item->setRequired(true);
        $this->addItem($item);

        //description
        $item = new ilTextInputGUI($this->lng->txt('description'), 'description');
        $item->setValue($this->tableview->getDescription());
        $this->addItem($item);

        //roles
        $checkbox_group_input_gui = new ilCheckboxGroupInputGUI($this->lng->txt('roles'), 'roles');

        $ref_id = $this->http->wrapper()->query()->retrieve('ref_id', $this->refinery->kindlyTo()->int());
        foreach ($rbacreview->getParentRoleIds($ref_id) as $role_array) {
            $option = new ilCheckboxOption(ilObjRole::_getTranslation($role_array['title']));
            $option->setValue($role_array['obj_id']);
            $checkbox_group_input_gui->addOption($option);
        }

        $checkbox_group_input_gui->setValue($this->tableview->getRoles());
        $this->addItem($checkbox_group_input_gui);

        $this->setFormAction($this->ctrl->getFormAction($this->parent_gui));
        if ($this->tableview->getId()) {
            $this->addCommandButton('update', $this->lng->txt('save'));
        } else {
            $this->addCommandButton('create', $this->lng->txt('create'));
        }
        $this->addCommandButton('cancel', $this->lng->txt('cancel'));
    }

    public function updateTableView(): void
    {
        $this->tableview->setTitle($this->getInput('title'));
        $this->tableview->setDescription($this->getInput('description'));
        $this->tableview->setRoles((array) $this->getInput('roles'));
        $this->tableview->update();

        $this->global_tpl->setOnScreenMessage('success', $this->lng->txt('dcl_msg_tableview_updated'), true);
    }

    public function createTableView(): void
    {
        $this->tableview->setTitle($this->getInput('title'));
        $this->tableview->setDescription($this->getInput('description'));
        $this->tableview->setRoles((array) $this->getInput('roles'));
        $this->tableview->setTableId($this->table->getId());
        $this->tableview->setStepVs(true);
        $this->tableview->setStepE(false);
        $this->tableview->setStepC(false);
        $this->tableview->setStepO(false);
        $this->tableview->setStepS(false);
        $this->tableview->setOrder($this->table->getNewTableviewOrder());
        $this->tableview->create();

        $this->ctrl->setParameterByClass('ilDclTableViewGUI', 'tableview_id', $this->tableview->getId());

        $this->global_tpl->setOnScreenMessage('success', $this->lng->txt('dcl_msg_tableview_created'), true);
    }
}
