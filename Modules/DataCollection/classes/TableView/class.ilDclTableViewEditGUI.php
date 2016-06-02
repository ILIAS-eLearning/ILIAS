<?php
require_once("./Modules/DataCollection/classes/TableView/class.ilDclTableViewFieldsTableGUI.php");
require_once("./Services/AccessControl/classes/class.ilObjRole.php");
/**
 * Class ilDclTableViewEditGUI
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 * @ingroup ModulesDataCollection
 *
 * @ilCtrl_Calls ilDclTableViewEditGUI: ilDclRecordViewViewdefinitionGUI
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
    public $tableview;

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
    public $table;


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
    }


    /**
     * execute command
     */
    public function executeCommand()
    {
        $this->tabs_gui->clearTargets();
        $this->tabs_gui->clearSubTabs();
        $this->tabs_gui->setBackTarget($this->lng->txt('dcl_tableviews'), $this->ctrl->getLinkTarget($this->parent_obj));

        $cmd = $this->ctrl->getCmd('show');
        $next_class = $this->ctrl->getNextClass($this);

        switch($next_class)
        {
            case 'ildclrecordviewviewdefinitiongui':
                $this->setTabs('detailed_view');
                require_once('./Modules/DataCollection/classes/class.ilDclRecordViewViewdefinitionGUI.php');
                $recordedit_gui = new ilDclRecordViewViewdefinitionGUI($this->tableview->getId());
                $ret = $this->ctrl->forwardCommand($recordedit_gui);
                if ($ret != "") {
                    $this->tpl->setContent($ret);
                }
                global $ilTabs;
                $ilTabs->removeTab('edit');
                $ilTabs->removeTab('history');
                $ilTabs->removeTab('clipboard'); // Fixme
                $ilTabs->removeTab('pg');
                break;
            default:
                switch($cmd) {
                    case 'show':
                        if ($this->tableview->getId()) {
                            $this->ctrl->redirect($this, 'editGeneralSettings');
                        } else {
                            $this->ctrl->redirect($this, 'create');
                        }
                        break;
                    case 'add':
                        $this->initFormGUI(true);
                        $this->tpl->setContent($this->form->getHTML());
                        break;
                    case 'editGeneralSettings':
                        $this->setTabs('general_settings');
                        $this->initFormGUI();
                        $this->tpl->setContent($this->form->getHTML());
                        break;
                    case 'editFieldSettings':
                        $this->setTabs('field_settings');
                        $this->initTableGUI();
                        $this->tpl->setContent($this->table_gui->getHTML());
                        break;
                    default:
                        $this->$cmd();
                        break;
                }
                break;
        }

    }

    protected function setTabs($active)
    {
        $this->tabs_gui->addTab('general_settings', $this->lng->txt('general_settings'), $this->ctrl->getLinkTarget($this, 'show'));
        $this->tabs_gui->addTab('field_settings', $this->lng->txt('fields'), $this->ctrl->getLinkTarget($this, 'editFieldSettings'));
        $this->tabs_gui->addTab('detailed_view', $this->lng->txt('detailed_view'), $this->ctrl->getLinkTargetByClass('ildclrecordviewviewdefinitiongui', 'edit'));
        $this->tabs_gui->setTabActive($active);
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

            $this->tableview->update();
            ilUtil::sendSuccess($this->lng->txt('dcl_msg_tableview_updated'), true);

            $this->ctrl->saveParameter($this, 'tableview_id');
            $this->ctrl->redirect($this, 'editGeneralSettings');
        }

        $this->ctrl->redirect($this, 'editGeneralSettings');
    }

    /**
     *
     */
    public function create()
    {
        $this->initFormGUI();
        $this->form->setValuesByPost();

        if ($this->form->checkInput())
        {
            $this->tableview->setTitle($this->form->getInput('title'));
            $this->tableview->setDescription($this->form->getInput('description'));
            $this->tableview->setRoles($this->form->getInput('roles'));

            $this->tableview->setTableId($this->table->getId());
            $this->tableview->setOrder($this->table->getNewTableviewOrder() * 10);
            $this->tableview->create();
            require_once './Modules/DataCollection/classes/TableView/class.ilDclTableViewFieldSetting.php';
            $this->ctrl->setParameter($this, 'tableview_id', $this->tableview->getId());
            ilUtil::sendSuccess($this->lng->txt('dcl_msg_tableview_created'), true);
            $this->ctrl->redirect($this, 'editGeneralSettings');
        }

        $this->ctrl->redirect($this, 'editGeneralSettings');
    }

    /**
     *
     */
    public function saveTable()
    {
        $field_settings = ilDclTableViewFieldSetting::getAllForTableViewId($this->tableview->getId());

        /**
         * @var ilDclTableViewFieldSetting $setting
         */
        foreach ($field_settings as $setting)
        {
            //Checkboxes
            foreach (array("Visible", "InFilter", "FilterChangeable") as $attribute)
            {
                $key = $attribute . '_' . $setting->getField();
                $setting->{'set'.$attribute}($_POST[$key] == 'on');

            }

            //Filter Value
            $key = 'filter_' . $setting->getField();
            if (isset($_POST[$key]))
            {
                $setting->setFilterValue(array($key => $_POST[$key]));
            }
            elseif (isset($_POST[$key . '_from']) && isset($_POST[$key . '_to']))
            {
                $setting->setFilterValue( array( $key . "_from" => $_POST[$key . '_from'], $key . "_to" => $_POST[$key . '_to'] ) );
            }

            $setting->update();
        }
        $this->ctrl->saveParameter($this, 'tableview_id');
        $this->ctrl->redirect($this, 'editFieldSettings');
    }

    /**
     * @return ilPropertyFormGUI
     */
    protected function initFormGUI($creation = false)
    {
        global $rbacreview;

        $form = new ilPropertyFormGUI();
        $form->setTitle($creation ? $this->lng->txt('dcl_new_tableview') : $this->lng->txt('general_settings'));

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
        foreach ($rbacreview->getLocalRoles($_GET['ref_id']) as $role_id)
        {
            $role = new ilObjRole($role_id);
            $options[$role_id] = $role->getTitle();
        }
        $item->setOptions($options);
        $item->setValue($this->tableview->getRoles());
        $form->addItem($item);

        $this->ctrl->saveParameter($this, 'tableview_id');
        $form->setFormAction($this->ctrl->getFormAction($this));
        if($creation) {
            $form->addCommandButton('create', $this->lng->txt('create'));
        } else {
            $form->addCommandButton('saveForm', $this->lng->txt('save'));
        }
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
        $conf->setHeaderText($this->lng->txt('dcl_tableview_confirm_delete'));

        $conf->addItem('tableview_id', (int)$this->tableview->getId(), $this->tableview->getTitle());

        $conf->setConfirm($this->lng->txt('delete'), 'delete');
        $conf->setCancel($this->lng->txt('cancel'), 'cancel');

        $this->tpl->setContent($conf->getHTML());
    }
    
    protected function delete() {
        $this->tableview->delete();
        $this->table->sortTableViews();
        ilUtil::sendSuccess($this->lng->txt('dcl_msg_tableview_deleted'), true);
        $this->cancel();
    }

}