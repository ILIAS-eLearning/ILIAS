<?php

/**
 * Class ilDclTableViewEditGUI
 * @author       Theodor Truffer <tt@studer-raimann.ch>
 * @ingroup      ModulesDataCollection
 * @ilCtrl_Calls ilDclTableViewEditGUI: ilDclDetailedViewDefinitionGUI
 * @ilCtrl_Calls ilDclTableViewEditGUI: ilDclCreateViewDefinitionGUI
 * @ilCtrl_Calls ilDclTableViewEditGUI: ilDclEditViewDefinitionGUI
 */
class ilDclTableViewEditGUI
{
    protected ilDclTableViewGUI $parent_obj;
    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected ilGlobalPageTemplate $tpl;
    public ilDclTableView $tableview;
    protected ilPropertyFormGUI $form;
    protected ilDclTableViewEditFieldsTableGUI $table_gui;
    protected ilTabsGUI $tabs_gui;
    public ilDclTable $table;
    protected ILIAS\HTTP\Services $http;
    protected ILIAS\Refinery\Factory $refinery;

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
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();

        $this->ctrl->saveParameterByClass('ilDclTableEditGUI', 'table_id');
        $this->ctrl->saveParameter($this, 'tableview_id');
        if ($this->tableview->getTitle()) {
            $locator->addItem($this->tableview->getTitle(), $this->ctrl->getLinkTarget($this, 'show'));
        }
        $this->tpl->setLocator();
    }

    public function executeCommand() : void
    {
        $cmd = $this->ctrl->getCmd('show');
        $next_class = $this->ctrl->getNextClass($this);

        if (!$this->checkAccess($cmd)) {
            $this->permissionDenied();
        }

        $this->tabs_gui->clearTargets();
        $this->tabs_gui->clearSubTabs();
        $this->tabs_gui->setBackTarget($this->lng->txt('dcl_tableviews'),
            $this->ctrl->getLinkTarget($this->parent_obj));
        $this->tabs_gui->setBack2Target($this->lng->txt('dcl_tables'),
            $this->ctrl->getLinkTarget($this->parent_obj->getParentObj()));

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
            case 'ildclcreateviewdefinitiongui':
                $this->setTabs('create_view');
                $creation_gui = new ilDclCreateViewDefinitionGUI($this->tableview->getId());
                $this->ctrl->forwardCommand($creation_gui);
                global $DIC;
                $ilTabs = $DIC['ilTabs'];
                $ilTabs->removeTab('edit');
                $ilTabs->removeTab('history');
                $ilTabs->removeTab('clipboard'); // Fixme
                $ilTabs->removeTab('pg');
                break;
            case 'ildcleditviewdefinitiongui':
                $this->setTabs('edit_view');
                $edit_gui = new ilDclEditViewDefinitionGUI($this->tableview->getId());
                $this->ctrl->forwardCommand($edit_gui);
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
                        $settings_tpl = new ilTemplate("tpl.dcl_settings.html", true, true, 'Modules/DataCollection');

                        $this->setTabs('general_settings');
                        $ilDclTableViewEditFormGUI = new ilDclTableViewEditFormGUI($this, $this->tableview);

                        global $DIC;
                        $f = $DIC->ui()->factory()->listing()->workflow();
                        $renderer = $DIC->ui()->renderer();

                        // Set Workflow flag to true
                        $view = ilDclTableView::getCollection()->where(array("id" => filter_input(INPUT_GET,
                            "tableview_id")
                        ))->first();
                        if (!is_null($view)) {
                            //setup steps
                            $step = $f->step('', '');
                            $steps = [
                                $f->step($this->lng->txt('dcl_view_settings'))
                                  ->withAvailability($step::AVAILABLE)->withStatus(4), //$view->getStepVs() ? 3 : 4
                                $f->step($this->lng->txt('dcl_create_entry_rules'))
                                  ->withAvailability($step::AVAILABLE)->withStatus(4), //$view->getStepC() ? 3 : 4
                                $f->step($this->lng->txt('dcl_edit_entry_rules'))
                                  ->withAvailability($step::AVAILABLE)->withStatus(4), //$view->getStepE() ? 3 : 4
                                $f->step($this->lng->txt('dcl_list_visibility_and_filter'))
                                  ->withAvailability($step::AVAILABLE)->withStatus(4), //$view->getStepO() ? 3 : 4
                                $f->step($this->lng->txt('dcl_detailed_view'))
                                  ->withAvailability($step::AVAILABLE)->withStatus(1), //$view->getStepS() ? 3 : 1
                            ];

                            //setup linear workflow
                            $wf = $f->linear($this->lng->txt('dcl_view_configuration'), $steps);
                            $settings_tpl->setVariable("WORKFLOW", $renderer->render($wf));
                        }

                        $settings_tpl->setVariable("SETTINGS", $ilDclTableViewEditFormGUI->getHTML());

                        $this->tpl->setContent($settings_tpl->get());
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

    protected function setTabs(string $active) : void
    {
        $this->tabs_gui->addTab('general_settings', $this->lng->txt('dcl_view_settings'),
            $this->ctrl->getLinkTarget($this, 'editGeneralSettings'));
        $this->tabs_gui->addTab('create_view', $this->lng->txt('dcl_create_entry_rules'),
            $this->ctrl->getLinkTargetByClass('ilDclCreateViewDefinitionGUI', 'presentation'));
        $this->tabs_gui->addTab('edit_view', $this->lng->txt('dcl_edit_entry_rules'),
            $this->ctrl->getLinkTargetByClass('ilDclEditViewDefinitionGUI', 'presentation'));
        $this->tabs_gui->addTab('field_settings', $this->lng->txt('dcl_list_visibility_and_filter'),
            $this->ctrl->getLinkTarget($this, 'editFieldSettings'));
        $this->tabs_gui->addTab('detailed_view', $this->lng->txt('dcl_detailed_view'),
            $this->ctrl->getLinkTargetByClass('ilDclDetailedViewDefinitionGUI', 'edit'));
        $this->tabs_gui->setTabActive($active);
    }

    public function update() : void
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

    public function create() : void
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

    public function saveTable() : void
    {
        /**
         * @var ilDclTableViewFieldSetting $setting
         */
        foreach ($this->tableview->getFieldSettings() as $setting) {
            //Checkboxes
            foreach (array("Visible", "InFilter", "FilterChangeable") as $attribute) {
                $key = $attribute . '_' . $setting->getField();
                if ($this->http->wrapper()->post()->has($key)) {
                    $checkbox_value = $this->http->wrapper()->post()->retrieve($key,
                        $this->refinery->kindlyTo()->string());
                    $setting->{'set' . $attribute}($checkbox_value === 'on');
                } else {
                    $setting->{'set' . $attribute}(false);
                }
            }

            //Filter Value
            $key = 'filter_' . $setting->getField();
            if ($this->http->wrapper()->post()->has($key)) {
                $setting->setFilterValue($this->http->wrapper()->post()->retrieve($key,
                    $this->refinery->kindlyTo()->string()));
            } elseif ($this->http->wrapper()->post()->has($key . '_from') && $this->http->wrapper()->post()->has($key . '_to')) {
                $setting->setFilterValue(array("from" => $this->http->wrapper()->post()->retrieve($key . '_from',
                    $this->refinery->kindlyTo()->string()),
                                               "to" => $this->http->wrapper()->post()->retrieve($key . '_to',
                                                   $this->refinery->kindlyTo()->string())
                ));
            } else {
                $setting->setFilterValue(null);
            }

            $setting->update();
        }

        // Set Workflow flag to true
        $view = ilDclTableView::getCollection()->where(array("id" => filter_input(INPUT_GET, "tableview_id")))->first();
        if (!is_null($view)) {
            $view->setStepO(true);
            $view->save();
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('dcl_msg_tableview_updated'), true);
        $this->ctrl->saveParameter($this->parent_obj, 'tableview_id');
        $this->ctrl->redirect($this, 'editFieldSettings');
    }

    protected function initTableGUI() : void
    {
        $table = new ilDclTableViewEditFieldsTableGUI($this);
        $this->table_gui = $table;
    }

    protected function cancel() : void
    {
        $this->ctrl->setParameter($this->parent_obj, 'table_id', $this->table->getId());
        $this->ctrl->redirect($this->parent_obj);
    }

    public function confirmDelete() : void
    {
        //at least one view must exist
        $this->parent_obj->checkViewsLeft(1);

        $conf = new ilConfirmationGUI();
        $conf->setFormAction($this->ctrl->getFormAction($this));
        $conf->setHeaderText($this->lng->txt('dcl_tableview_confirm_delete'));

        $conf->addItem('tableview_id', (int) $this->tableview->getId(), $this->tableview->getTitle());

        $conf->setConfirm($this->lng->txt('delete'), 'delete');
        $conf->setCancel($this->lng->txt('cancel'), 'cancel');

        $this->tpl->setContent($conf->getHTML());
    }

    protected function delete() : void
    {
        $this->tableview->delete();
        $this->table->sortTableViews();
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('dcl_msg_tableview_deleted'), true);
        $this->cancel();
    }

    public function permissionDenied() : void
    {
        $this->tpl->setOnScreenMessage('failure', $this->lng->txt('permission_denied'), true);
        $this->ctrl->redirectByClass([ilObjDataCollectionGUI::class, ilDclRecordListGUI::class],
            ilDclRecordListGUI::CMD_LIST_RECORDS);
    }

    protected function checkAccess(string $cmd) : bool
    {
        if (in_array($cmd, ['add', 'create'])) {
            return ilObjDataCollectionAccess::hasAccessToEditTable(
                $this->parent_obj->getParentObj()->getDataCollectionObject()->getRefId(),
                $this->table->getId()
            );
        } else {
            return ilObjDataCollectionAccess::hasAccessTo(
                $this->parent_obj->getParentObj()->getDataCollectionObject()->getRefId(),
                $this->table->getId(),
                $this->tableview->getId()
            );
        }
    }

    public function copy() : void
    {
        $new_tableview = new ilDclTableView();
        $new_tableview->setTableId($this->table->getId());
        $new_tableview->cloneStructure($this->tableview, array());
        $this->table->sortTableViews();
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("dcl_tableview_copy"), true);
        $this->cancel();
    }
}
