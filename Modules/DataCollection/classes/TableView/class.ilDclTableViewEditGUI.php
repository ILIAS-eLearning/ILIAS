<?php

/**
 * Class ilDclTableViewEditGUI
 *
 * @author       Theodor Truffer <tt@studer-raimann.ch>
 * @ingroup      ModulesDataCollection
 *
 * @ilCtrl_Calls ilDclTableViewEditGUI: ilDclDetailedViewDefinitionGUI
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
     * @param ilDclTable $table
     */
    public function __construct(ilDclTableViewGUI $parent_obj, ilDclTable $table, ilDclTableView $tableview)
    {
        global $DIC;
        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];
        $tpl = $DIC['tpl'];
        $ilTabs = $DIC['ilTabs'];
        $locator = $DIC['ilLocator'];
        $this->table = $table;
        $this->tpl = $tpl;
        $this->lng = $lng;
        $this->ctrl = $ilCtrl;
        $this->parent_obj = $parent_obj;
        $this->tableview = $tableview;
        $this->tabs_gui = $ilTabs;

        $this->ctrl->saveParameterByClass('ilDclTableEditGUI', 'table_id');
        $this->ctrl->saveParameter($this, 'tableview_id');
        $locator->addItem($this->tableview->getTitle(), $this->ctrl->getLinkTarget($this, 'show'));
        $this->tpl->setLocator();
    }


    /**
     * execute command
     */
    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd('show');
        $next_class = $this->ctrl->getNextClass($this);

        if (!$this->checkAccess($cmd)) {
            $this->permissionDenied();
        }

        $this->tabs_gui->clearTargets();
        $this->tabs_gui->clearSubTabs();
        $this->tabs_gui->setBackTarget($this->lng->txt('dcl_tableviews'), $this->ctrl->getLinkTarget($this->parent_obj));
        $this->tabs_gui->setBack2Target($this->lng->txt('dcl_tables'), $this->ctrl->getLinkTarget($this->parent_obj->parent_obj));



        switch ($next_class) {
            case 'ildcldetailedviewdefinitiongui':
                $this->setTabs('detailed_view');
                                $recordedit_gui = new ilDclDetailedViewDefinitionGUI($this->tableview->getId());
                $ret = $this->ctrl->forwardCommand($recordedit_gui);
                if ($ret != "") {
                    $this->tpl->setContent($ret);
                }
                global $DIC;
                $ilTabs = $DIC['ilTabs'];
                $ilTabs->removeTab('edit');
                $ilTabs->removeTab('history');
                $ilTabs->removeTab('clipboard'); // Fixme
                $ilTabs->removeTab('pg');
                break;
            default:
                switch ($cmd) {
                    case 'show':
                        if ($this->tableview->getId()) {
                            $this->ctrl->redirect($this, 'editGeneralSettings');
                        } else {
                            $this->ctrl->redirect($this, 'add');
                        }
                        break;
                    case 'add':
                        $ilDclTableViewEditFormGUI = new ilDclTableViewEditFormGUI($this, $this->tableview);
                        $this->tpl->setContent($ilDclTableViewEditFormGUI->getHTML());
                        break;
                    case 'editGeneralSettings':
                        $this->setTabs('general_settings');
                        $ilDclTableViewEditFormGUI = new ilDclTableViewEditFormGUI($this, $this->tableview);
                        $this->tpl->setContent($ilDclTableViewEditFormGUI->getHTML());
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
        $this->tabs_gui->addTab('general_settings', $this->lng->txt('settings'), $this->ctrl->getLinkTarget($this, 'editGeneralSettings'));
        $this->tabs_gui->addTab('field_settings', $this->lng->txt('dcl_list_visibility_and_filter'), $this->ctrl->getLinkTarget($this, 'editFieldSettings'));
        $this->tabs_gui->addTab('detailed_view', $this->lng->txt('dcl_detailed_view'), $this->ctrl->getLinkTargetByClass('ilDclDetailedViewDefinitionGUI', 'edit'));
        $this->tabs_gui->setTabActive($active);
    }

    /**
     *
     */
    public function update()
    {
        $ilDclTableViewEditFormGUI = new ilDclTableViewEditFormGUI($this, $this->tableview);
        $ilDclTableViewEditFormGUI->setValuesByPost();
        if ($ilDclTableViewEditFormGUI->checkInput()) {
            $ilDclTableViewEditFormGUI->updateTableView();
            $this->ctrl->redirect($this, 'editGeneralSettings');
        } else {
            $this->setTabs('general_settings');
            $this->tpl->setContent($ilDclTableViewEditFormGUI->getHTML());
        }
    }

    /**
     *
     */
    public function create()
    {
        $ilDclTableViewEditFormGUI = new ilDclTableViewEditFormGUI($this, $this->tableview, $this->table);
        $ilDclTableViewEditFormGUI->setValuesByPost();
        if ($ilDclTableViewEditFormGUI->checkInput()) {
            $ilDclTableViewEditFormGUI->createTableView();
            $this->ctrl->redirect($this, 'editGeneralSettings');
        } else {
            $this->tpl->setContent($ilDclTableViewEditFormGUI->getHTML());
        }
    }

    /**
     *
     */
    public function saveTable()
    {
        /**
         * @var ilDclTableViewFieldSetting $setting
         */
        foreach ($this->tableview->getFieldSettings() as $setting) {
            //Checkboxes
            foreach (array("Visible", "InFilter", "FilterChangeable") as $attribute) {
                $key = $attribute . '_' . $setting->getField();
                $setting->{'set' . $attribute}($_POST[$key] == 'on');
            }

            //Filter Value
            $key = 'filter_' . $setting->getField();
            if ($_POST[$key] != null) {
                $setting->setFilterValue($_POST[$key]);
            } elseif ($_POST[$key . '_from'] != null && $_POST[$key . '_to'] != null) {
                $setting->setFilterValue(array( "from" => $_POST[$key . '_from'], "to" => $_POST[$key . '_to'] ));
            } else {
                $setting->setFilterValue(null);
            }

            $setting->update();
        }
        ilUtil::sendSuccess($this->lng->txt('dcl_msg_tableview_updated'), true);
        $this->ctrl->saveParameter($this->parent_obj, 'tableview_id');
        $this->ctrl->redirect($this, 'editFieldSettings');
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

        $conf->addItem('tableview_id', (int) $this->tableview->getId(), $this->tableview->getTitle());

        $conf->setConfirm($this->lng->txt('delete'), 'delete');
        $conf->setCancel($this->lng->txt('cancel'), 'cancel');

        $this->tpl->setContent($conf->getHTML());
    }
    
    protected function delete()
    {
        $this->tableview->delete();
        $this->table->sortTableViews();
        ilUtil::sendSuccess($this->lng->txt('dcl_msg_tableview_deleted'), true);
        $this->cancel();
    }


    /**
     *
     */
    public function permissionDenied()
    {
        ilUtil::sendFailure($this->lng->txt('permission_denied'), true);
        $this->ctrl->redirectByClass([ilObjDataCollectionGUI::class, ilDclRecordListGUI::class], ilDclRecordListGUI::CMD_LIST_RECORDS);
    }


    /**
     * @param $cmd
     *
     * @return bool
     */
    protected function checkAccess($cmd)
    {
        if (in_array($cmd, ['add', 'create'])) {
            return ilObjDataCollectionAccess::hasAccessToEditTable(
                $this->parent_obj->parent_obj->getDataCollectionObject()->getRefId(),
                $this->table->getId()
            );
        } else {
            return ilObjDataCollectionAccess::hasAccessTo(
                $this->parent_obj->parent_obj->getDataCollectionObject()->getRefId(),
                $this->table->getId(),
                $this->tableview->getId()
            );
        }
    }
}
