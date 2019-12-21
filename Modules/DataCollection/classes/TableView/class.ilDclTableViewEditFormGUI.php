<?php

/**
 * Class ilDclTableViewEditFormGUI
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ilDclTableViewEditFormGUI extends ilPropertyFormGUI
{

    /**
     * @var ilDclTableView
     */
    protected $tableview;
    /**
     * @var ilDclTable
     */
    protected $table;
    /**
     * @var ilLanguage
     */
    protected $lng;
    /**
     * @var ilCtrl
     */
    protected $ctrl;
    /**
     * @var ilDclTableViewEditGUI
     */
    protected $parent_gui;


    public function __construct(ilDclTableViewEditGUI $parent_gui, ilDclTableView $tableview, ilDclTable $table = null)
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


    protected function initForm()
    {
        global $DIC;
        $rbacreview = $DIC['rbacreview'];

        $this->setTitle($this->tableview->getId() ? $this->lng->txt('settings') : $this->lng->txt('dcl_tableview_add'));

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

        foreach ($rbacreview->getParentRoleIds($_GET['ref_id']) as $role_array) {
            $option = new ilCheckboxOption(ilObjRole::_getTranslation($role_array['title'], $role_array['obj_id']));
            $option->setValue($role_array['obj_id']);
            $checkbox_group_input_gui->addOption($option);
        }
        foreach ($rbacreview->getLocalRoles($_GET['ref_id']) as $role_id) {
            $option = new ilCheckboxOption(ilObjRole::_getTranslation($role->getTitle(), $role_id));
            $option->setValue($role_id);
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


    public function updateTableView()
    {
        $this->tableview->setTitle($this->getInput('title'));
        $this->tableview->setDescription($this->getInput('description'));
        $this->tableview->setRoles((array) $this->getInput('roles'));
        $this->tableview->update();

        ilUtil::sendSuccess($this->lng->txt('dcl_msg_tableview_updated'), true);
    }


    public function createTableView()
    {
        $this->tableview->setTitle($this->getInput('title'));
        $this->tableview->setDescription($this->getInput('description'));
        $this->tableview->setRoles((array) $this->getInput('roles'));
        $this->tableview->setTableId($this->table->getId());
        $this->tableview->setOrder($this->table->getNewTableviewOrder());
        $this->tableview->create();

        $this->ctrl->setParameterByClass('ilDclTableViewGUI', 'tableview_id', $this->tableview->getId());

        ilUtil::sendSuccess($this->lng->txt('dcl_msg_tableview_created'), true);
    }
}
