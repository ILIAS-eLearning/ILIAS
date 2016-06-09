<?php
require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
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

    function __construct(ilDclTableViewEditGUI $parent_gui, ilDclTableView $tableview, ilDclTable $table = null)
    {
        global $lng, $ilCtrl;
        parent::__construct();
        $this->lng = $lng;
        $this->ctrl = $ilCtrl;
        $this->parent_gui = $parent_gui;
        $this->tableview = $tableview;
        $this->table = $table;
        $this->ctrl->saveParameterByClass('ilDclTableViewGUI', 'tableview_id');
        $this->initForm();
    }

    protected function initForm() {
        global $rbacreview;

        $this->setTitle($this->tableview->getId() ? $this->lng->txt('general_settings') : $this->lng->txt('dcl_new_tableview'));

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
        $item = new ilMultiSelectInputGUI($this->lng->txt('roles'), 'roles');
        $options = array();
        foreach ($rbacreview->getParentRoleIds($_GET['ref_id']) as $role_array)
        {
            $options[$role_array['obj_id']] = ilObjRole::_getTranslation($role_array['title']);
        }
        foreach ($rbacreview->getLocalRoles($_GET['ref_id']) as $role_id)
        {
            $role = new ilObjRole($role_id);
            $options[$role_id] = ilObjRole::_getTranslation($role->getTitle());
        }

        $item->setOptions($options);
        $item->setValue($this->tableview->getRoles());
        $this->addItem($item);

        $this->setFormAction($this->ctrl->getFormAction($this->parent_gui));
        if($this->tableview->getId()) {
            $this->addCommandButton('update', $this->lng->txt('save'));
        } else {
            $this->addCommandButton('create', $this->lng->txt('create'));
        }
        $this->addCommandButton('cancel', $this->lng->txt('cancel'));
    }

    public function updateTableView() {
        if ($this->checkInput())
        {
            $this->tableview->setTitle($this->getInput('title'));
            $this->tableview->setDescription($this->getInput('description'));
            $this->tableview->setRoles($this->getInput('roles'));
            $this->tableview->update();

            ilUtil::sendSuccess($this->lng->txt('dcl_msg_tableview_updated'), true);
            return true;
        }
        return false;
    }
    
    public function createTableView() {
        if ($this->checkInput())
        {
            $this->tableview->setTitle($this->getInput('title'));
            $this->tableview->setDescription($this->getInput('description'));
            $this->tableview->setRoles($this->getInput('roles'));
            $this->tableview->setTableId($this->table->getId());
            $this->tableview->setOrder($this->table->getNewTableviewOrder() * 10);
            $this->tableview->create();

            $this->ctrl->setParameterByClass('ilDclTableViewGUI', 'tableview_id', $this->tableview->getId());

            ilUtil::sendSuccess($this->lng->txt('dcl_msg_tableview_created'), true);
            return true;
        }
        return false;
    }
    
    
    
}