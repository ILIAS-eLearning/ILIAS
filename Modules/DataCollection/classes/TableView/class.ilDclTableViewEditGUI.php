<?php
require_once("./Modules/DataCollection/classes/TableView/class.ilDclTableViewFieldsTableGUI.php");
require_once("./Services/AccessControl/classes/class.ilObjRole.php");
/**
 * Class ilDclTableViewEditGUI
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 * @ingroup ModulesDataCollection
 */
class ilDclTableViewEditGUI
{
    /**
     * @var ilDclTableViewGUI
     */
    protected $parent_obj;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var ilDclTableView
     */
    protected $tableview;

    /**
     * @var ilPropertyFormGUI
     */
    protected $form;

    /**
     * @var ilDclTableViewEditFieldsTableGUI
     */
    protected $table_gui;

    /**
     * @var ilTabsGUI
     */
    protected $tabs_gui;

    /**
     * @var ilDclTable
     */
    protected $table;


    /**
     * ilDclTableViewEditGUI constructor.
     * @param ilDclTableViewGUI $parent_obj
     * @param $table_id
     * @param ilDclTableView $tableview
     */
    public function __construct(ilDclTableViewGUI $parent_obj)
    {
        global $lng, $ilCtrl, $tpl, $ilTabs;
        $this->table = $parent_obj->getTable();
        $this->tpl = $tpl;
        $this->lng = $lng;
        $this->ctrl = $ilCtrl;
        $this->parent_obj = $parent_obj;
        $this->tableview = ilDclTableView::findOrGetInstance($_GET['tableview_id']);
        $this->tabs_gui = $ilTabs;
        $ilTabs->setBackTarget($this->lng->txt('back'), $this->ctrl->getLinkTargetByClass('ildcltableviewgui', 'show'));
    }


    /**
     * execute command
     */
    public function executeCommand()
    {
        $this->tabs_gui->clearSubTabs();
        $cmd = $this->ctrl->getCmd('init');
        switch($cmd) {
            case 'show':
                $this->init();
                break;
            default:
                $this->$cmd();
                break;
        }
    }

    /**
     * initialize form and table
     */
    public function init()
    {
        $this->initFormGUI();
        if ($this->tableview->getId())
        {
            $this->initTableGUI();
        }
        $this->show();
    }

    /**
     * set content to template
     */
    public function show()
    {
        $detail_view_html = $this->tableview->getId() ? '<a>Detailed View<a>' : '';
        $this->tpl->setContent($this->form->getHTML() . ($this->table_gui ? $this->table_gui->getHTML() : '') . $detail_view_html);
    }

    /**
     * 
     */
    public function saveForm()
    {
        $this->initFormGUI();
        $this->form->setValuesByPost();

        if ($this->form->checkInput())
        {
            $this->tableview->setTitle($this->form->getInput('title'));
            $this->tableview->setDescription($this->form->getInput('description'));
            $this->tableview->setRoles($this->form->getInput('roles'));
           
            if (!$this->tableview->getId()) 
            {
                $this->tableview->setTableId($this->table->getId());
                $this->tableview->setOrder($this->table->getNewTableviewOrder() * 10);
                $this->tableview->create();
                require_once './Modules/DataCollection/classes/TableView/class.ilDclTableViewFieldSetting.php';
                ilDclTableViewFieldSetting::createDefaults($this->tableview->getTableId(), $this->tableview->getId());
                $this->ctrl->setParameter($this, 'tableview_id', $this->tableview->getId());
                ilUtil::sendSuccess($this->lng->txt('dcl_msg_tableview_create_succeed'), true);
            }
            else
            {
                $this->tableview->update();
                ilUtil::sendSuccess($this->lng->txt('dcl_msg_tableview_update_succeed'), true);
            }
            $this->ctrl->saveParameter($this, 'tableview_id');
            $this->ctrl->redirect($this, 'init');
        }

        $this->show();
    }

    /**
     *
     */
    public function saveTable()
    {
        $field_settings = ilDclTableViewFieldSetting::where(array("tableview_id" => $this->tableview->getId()))->get();

        /**
         * @var ilDclTableViewFieldSetting $setting
         */
        foreach ($field_settings as $setting)
        {
            foreach (array("Visible", "InFilter", "FilterChangeable") as $attribute)
            {
                $key = $attribute . '_' . $setting->getId();
                $setting->{'set'.$attribute}($_POST[$key] == 'on');

            }

            $key = 'filter_' . $setting->getId();
            if (isset($_POST[$key]))
            {
                $setting->setFilterValue($_POST[$key]);
            }
            elseif (isset($_POST[$key . '_from']) && isset($_POST[$key . '_to']))
            {
                $setting->setFilterValue( array( "from" => $_POST[$key . '_from'], "to" => $_POST[$key . '_to'] ) );
            }

            $setting->update();
        }
        $this->ctrl->saveParameter($this, 'tableview_id');
        $this->ctrl->redirect($this, 'init');
    }

    /**
     * @return ilPropertyFormGUI
     */
    protected function initFormGUI()
    {
        global $rbacreview;

        $form = new ilPropertyFormGUI();
        $form->setTitle($this->lng->txt('dcl_general_settings'));

        //title
        $item = new ilTextInputGUI($this->lng->txt('title'), 'title');
        $item->setValue($this->tableview->getTitle());
        $item->setRequired(true);
        $form->addItem($item);

        //description
        $item = new ilTextInputGUI($this->lng->txt('description'), 'description');
        $item->setValue($this->tableview->getDescription());
        $form->addItem($item);

        //roles
        $item = new ilMultiSelectInputGUI($this->lng->txt('roles'), 'roles');
        $options = array();
        foreach ($rbacreview->getGlobalRoles() as $role_id)
        {
            $role = new ilObjRole($role_id);
            $options[$role_id] = $role->getTitle();
        }
        $item->setOptions($options);
        $item->setValue($this->tableview->getRoles());
        $form->addItem($item);

        $this->ctrl->saveParameter($this, 'tableview_id');
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->addCommandButton('saveForm', $this->lng->txt('save'));
        $form->addCommandButton('cancel', $this->lng->txt('cancel'));

        $this->form = $form;
    }

    /**
     * @return ilDclTableViewEditFieldsTableGUI
     */
    protected function initTableGUI()
    {
        $table = new ilDclTableViewEditFieldsTableGUI($this);
        $this->table_gui = $table;
    }

    /**
     * return to overview
     */
    protected function cancel()
    {
        $this->ctrl->setParameter($this->parent_obj, 'table_id', $this->table->getId());
        $this->ctrl->redirect($this->parent_obj);
    }

    /**
     *
     */
    public function confirmDelete()
    {
        //at least one view must exist
        $this->parent_obj->checkViewsLeft(1);

        include_once './Services/Utilities/classes/class.ilConfirmationGUI.php';
        $conf = new ilConfirmationGUI();
        $conf->setFormAction($this->ctrl->getFormAction($this));
        $conf->setHeaderText($this->lng->txt('dcl_confirm_delete_tableview'));

        $conf->addItem('tableview_id', (int)$this->tableview->getId(), $this->tableview->getTitle());

        $conf->setConfirm($this->lng->txt('delete'), 'delete');
        $conf->setCancel($this->lng->txt('cancel'), 'cancel');

        $this->tpl->setContent($conf->getHTML());
    }
    
    protected function delete() {
        $this->tableview->delete();
        $this->table->sortTableViews();
        ilUtil::sendSuccess($this->lng->txt('dcl_msg_delete_success'), true);
        $this->cancel();
    }

}