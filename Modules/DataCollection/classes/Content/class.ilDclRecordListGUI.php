<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 *
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Marcel Raimann <mr@studer-raimann.ch>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @author  Oskar Truffer <ot@studer-raimann.ch>
 * @version $Id:
 *
 * @ingroup ModulesDataCollection
 */
class ilDclRecordListGUI
{
    const GET_TABLE_ID = 'table_id';
    const GET_TABLEVIEW_ID = 'tableview_id';
    const GET_MODE = 'mode';

    const MODE_VIEW = 1;
    const MODE_MANAGE = 2;

    const CMD_LIST_RECORDS = 'listRecords';
    const CMD_SHOW = 'show';
    const CMD_CONFIRM_DELETE_RECORDS = 'confirmDeleteRecords';
    const CMD_CANCEL_DELETE = 'cancelDelete';
    const CMD_DELETE_RECORDS = 'deleteRecords';
    const CMD_SHOW_IMPORT_EXCEL = 'showImportExcel';

    /**
     * Stores current mode active
     *
     * @var int
     */
    protected $mode = self::MODE_VIEW;
    /**
     * @var ilDclTable
     */
    protected $table_obj;
    /**
     * @var ilCtrl
     */
    protected $ctrl;
    /**
     * @var ilLanguage
     */
    protected $lng;
    /**
     * @var integer
     */
    protected $tableview_id;
    /**
     * @var array
     */
    protected static $available_modes = array(self::MODE_VIEW, self::MODE_MANAGE);


    /**
     * @param ilObjDataCollectionGUI $a_parent_obj
     * @param                        $table_id
     */
    public function __construct(ilObjDataCollectionGUI $a_parent_obj, $table_id)
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];
        $this->ctrl = $ilCtrl;
        $this->lng = $lng;

        $this->table_id = $table_id;
        if ($this->table_id == null) {
            $this->table_id = filter_input(INPUT_GET, self::GET_TABLE_ID);
        }

        $this->obj_id = $a_parent_obj->obj_id;
        $this->parent_obj = $a_parent_obj;
        $this->table_obj = ilDclCache::getTableCache($table_id);

        if ($tableview_id = filter_input(INPUT_GET, self::GET_TABLEVIEW_ID)) {
            $this->tableview_id = $tableview_id;
        } else {
            //get first visible tableview
            $this->tableview_id = $this->table_obj->getFirstTableViewId($this->parent_obj->ref_id);
            //this is for ilDclTextRecordRepresentation with link to detail page
            $_GET[self::GET_TABLEVIEW_ID] = $this->tableview_id; //TODO: find better way
        }
        
        $this->ctrl->setParameterByClass(ilDclRecordEditGUI::class, self::GET_TABLE_ID, $this->table_id);
        $this->ctrl->setParameterByClass(ilDclRecordEditGUI::class, self::GET_TABLEVIEW_ID, $this->tableview_id);
        $this->ctrl->setParameterByClass(ilDclDetailedViewGUI::class, self::GET_TABLEVIEW_ID, $this->tableview_id);
        $this->mode = (isset($_GET[self::GET_MODE]) && in_array($_GET[self::GET_MODE], self::$available_modes)) ? (int) $_GET[self::GET_MODE] : self::MODE_VIEW;
    }


    /**
     * execute command
     */
    public function executeCommand()
    {
        global $DIC;
        $ilTabs = $DIC['ilTabs'];

        if (!$this->checkAccess()) {
            ilUtil::sendFailure($this->lng->txt('permission_denied'), true);

            return;
        }

        $this->ctrl->saveParameter($this, self::GET_MODE);
        $cmd = $this->ctrl->getCmd(self::CMD_SHOW);

        // 'show' fills all filters with the predefined values from the tableview,
        // whereas 'listRecords' handels the filters "normally", filling them from the POST-variable
        switch ($cmd) {
            case self::CMD_SHOW:
                $this->setSubTabs();
                $this->listRecords(true);
                break;
            case self::CMD_LIST_RECORDS:
                $this->setSubTabs();
                $this->listRecords();
                break;
            case self::CMD_CONFIRM_DELETE_RECORDS:
                $this->confirmDeleteRecords();
                break;
            case self::CMD_CANCEL_DELETE:
                $this->setSubTabs();
                $this->listRecords();
                break;
            case self::CMD_DELETE_RECORDS:
                $this->deleteRecords();
                break;
            case self::CMD_SHOW_IMPORT_EXCEL:
                $ilTabs->setBack2Target($this->lng->txt('back'), $this->ctrl->getLinkTarget($this));
                $this->$cmd();
                break;

            default:
                $this->$cmd();
                break;
        }
    }


    public function listRecords($use_tableview_filter = false)
    {
        global $DIC;
        $tpl = $DIC['tpl'];
        $ilToolbar = $DIC['ilToolbar'];
        /**
         * @var $ilToolbar ilToolbarGUI
         */
        // Show tables
        $tpl->addCss("./Modules/DataCollection/css/dcl_reference_hover.css");

        $list = $this->getRecordListTableGUI($use_tableview_filter);

        $this->createSwitchers();

        $permission_to_add_or_import = ilObjDataCollectionAccess::hasPermissionToAddRecord($this->parent_obj->ref_id, $this->table_id) and $this->table_obj->hasCustomFields();
        if ($permission_to_add_or_import) {
            $this->ctrl->setParameterByClass("ildclrecordeditgui", "record_id", null);

            $add_new = ilLinkButton::getInstance();
            $add_new->setPrimary(true);
            $add_new->setCaption("dcl_add_new_record");
            $add_new->setUrl($this->ctrl->getFormActionByClass("ildclrecordeditgui", "create"));
            $ilToolbar->addStickyItem($add_new);
        }

        if ($permission_to_add_or_import && $this->table_obj->getImportEnabled()) {
            $this->ctrl->setParameterByClass("ildclrecordeditgui", "record_id", null);

            $import = ilLinkButton::getInstance();
            $import->setCaption("dcl_import_records .xls");
            $import->setUrl($this->ctrl->getFormActionByClass("ildclrecordlistgui", self::CMD_SHOW_IMPORT_EXCEL));
            $ilToolbar->addButtonInstance($import);
        }

        if (count($this->table_obj->getRecordFields()) == 0) {
            ilUtil::sendInfo($this->lng->txt("dcl_no_fields_yet") . " "
                . (ilObjDataCollectionAccess::hasAccessToFields($this->parent_obj->ref_id, $this->table_id) ? $this->lng->txt("dcl_create_fields") : ""));
        }

        $tpl->setPermanentLink("dcl", $this->parent_obj->ref_id . "_" . $this->tableview_id);

        if ($desc = $this->table_obj->getDescription()) {
            $ilSetting = new ilSetting('advanced_editing');
            if ((bool) $ilSetting->get('advanced_editing_javascript_editor')) {
                $desc = "<div class='ilDclTableDescription'>" . $desc . "</div>";
            } else {
                $desc = "<div class='ilDclTableDescription'>" . nl2br(ilUtil::stripSlashes($desc)) . "</div>";
            }
        }
        $tpl->setContent($desc . $list->getHTML());
    }


    public function showImportExcel($form = null)
    {
        global $DIC;
        $tpl = $DIC['tpl'];
        if (!$form) {
            $form = $this->initImportForm();
        }
        $tpl->setContent($form->getHTML());
    }


    /**
     * Init form
     *
     * @return ilPropertyFormGUI
     */
    public function initImportForm()
    {
        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();

        $item = new ilCustomInputGUI();
        $item->setHtml($this->lng->txt('dcl_file_format_description'));
        $item->setTitle("Info");
        $form->addItem($item);

        $file = new ilFileInputGUI($this->lng->txt("import_file"), "import_file");
        $file->setRequired(true);
        $form->addItem($file);

        $cb = new ilCheckboxInputGUI($this->lng->txt("dcl_simulate_import"), "simulate");
        $cb->setInfo($this->lng->txt("dcl_simulate_info"));
        $form->addItem($cb);

        $form->addCommandButton("importExcel", $this->lng->txt("import"));

        return $form;
    }


    /**
     * Import Data from Excel sheet
     */
    public function importExcel()
    {
        if (!(ilObjDataCollectionAccess::hasPermissionToAddRecord($this->parent_obj->ref_id, $this->table_id)) || !$this->table_obj->getImportEnabled()) {
            throw new ilDclException($this->lng->txt("access_denied"));
        }
        $form = $this->initImportForm();
        if ($form->checkInput()) {
            $file = $form->getInput("import_file");
            $file_location = $file["tmp_name"];
            $simulate = $form->getInput("simulate");
            $this->importRecords($file_location, $simulate);
        } else {
            $this->showImportExcel($form);
        }
    }


    /**
     * Import records from Excel file
     *
     * @param      $file
     * @param bool $simulate
     */
    private function importRecords($file, $simulate = false)
    {
        $importer = new ilDclContentImporter($this->parent_obj->object->getRefId(), $this->table_id);
        $result = $importer->import($file, $simulate);

        $this->endImport($result['line'], $result['warnings']);
    }


    /**
     * End import
     *
     * @param $i
     * @param $warnings
     */
    public function endImport($i, $warnings)
    {
        global $DIC;
        $tpl = $DIC['tpl'];
        $output = new ilTemplate("tpl.dcl_import_terminated.html", true, true, "Modules/DataCollection");
        $output->setVariable("IMPORT_TERMINATED", $this->lng->txt("dcl_import_terminated") . ": " . $i);
        foreach ($warnings as $warning) {
            $output->setCurrentBlock("warnings");
            $output->setVariable("WARNING", $warning);
            $output->parseCurrentBlock();
        }
        if (!count($warnings)) {
            $output->setCurrentBlock("warnings");
            $output->setVariable("WARNING", $this->lng->txt("dcl_no_warnings"));
            $output->parseCurrentBlock();
        }
        $output->setVariable("BACK_LINK", $this->ctrl->getLinkTargetByClass("ilDclRecordListGUI", "listRecords"));
        $output->setVariable("BACK", $this->lng->txt("back"));
        $tpl->setContent($output->get());
    }


    /**
     * doTableSwitch
     */
    public function doTableSwitch()
    {
        $this->ctrl->clearParameters($this);
        $this->ctrl->setParameterByClass(ilObjDataCollectionGUI::class, "table_id", $_POST['table_id']);
        $this->ctrl->setParameter($this, "table_id", $_POST['table_id']);
        $this->ctrl->clearParametersByClass(ilObjDataCollectionGUI::class, 'tableview_id');
        $this->ctrl->redirect($this, self::CMD_SHOW);
    }


    /**
     * doTableViewSwitch
     */
    public function doTableViewSwitch()
    {
        $this->ctrl->setParameterByClass("ilObjDataCollectionGUI", "tableview_id", $_POST['tableview_id']);
        $this->ctrl->redirect($this, self::CMD_SHOW);
    }


    /**
     *
     */
    protected function applyFilter()
    {
        $table = new ilDclRecordListTableGUI($this, "listRecords", $this->table_obj, $this->tableview_id);
        $table->initFilter();
        $table->resetOffset();
        $table->writeFilterToSession();
        $this->ctrl->redirect($this, self::CMD_LIST_RECORDS);
    }


    /**
     *
     */
    protected function resetFilter()
    {
        $table = new ilDclRecordListTableGUI($this, "show", $this->table_obj, $this->tableview_id);
        $table->initFilter();
        $table->resetOffset();
        $table->resetFilter();
        $this->ctrl->redirect($this, self::CMD_SHOW);
    }


    /**
     * send File to User
     */
    public function sendFile()
    {
        global $DIC;
        $ilAccess = $DIC['ilAccess'];
        $ilUser = $DIC['ilUser'];
        //need read access to receive file
        if ($ilAccess->checkAccess("read", "", $this->parent_obj->ref_id)) {

            // deliver temp-files
            if (isset($_GET['ilfilehash'])) {
                $filehash = $_GET['ilfilehash'];
                $field_id = $_GET['field_id'];
                ilDclPropertyFormGUI::rebuildTempFileByHash($filehash);

                $filepath = $_FILES["field_" . $field_id]['tmp_name'];
                $filetitle = $_FILES["field_" . $field_id]['name'];
            } else {
                $rec_id = $_GET['record_id'];
                $record = ilDclCache::getRecordCache($rec_id);
                $field_id = $_GET['field_id'];
                $file_obj = new ilObjFile($record->getRecordFieldValue($field_id), false);
                if (!$this->recordBelongsToCollection($record, $this->parent_obj->ref_id)) {
                    return;
                }
                $filepath = $file_obj->getFile();
                $filetitle = $file_obj->getTitle();
            }

            ilUtil::deliverFile($filepath, $filetitle);
        }
    }


    /**
     * Confirm deletion of multiple records
     *
     */
    public function confirmDeleteRecords()
    {
        global $DIC;
        $tpl = $DIC['tpl'];
        $ilTabs = $DIC['ilTabs'];
        /** @var ilTabsGUI $ilTabs */
        $ilTabs->clearSubTabs();

        $conf = new ilConfirmationGUI();
        $conf->setFormAction($this->ctrl->getFormAction($this));
        $conf->setHeaderText($this->lng->txt('dcl_confirm_delete_records'));
        $record_ids = isset($_POST['record_ids']) ? $_POST['record_ids'] : array();
        $all_fields = $this->table_obj->getRecordFields();
        foreach ($record_ids as $record_id) {
            /** @var ilDclBaseRecordModel $record */
            $record = ilDclCache::getRecordCache($record_id);
            if ($record) {
                $record_data = "";
                foreach ($all_fields as $key => $field) {
                    $field_record = ilDclCache::getRecordFieldCache($record, $field);

                    $record_representation = ilDclCache::getRecordRepresentation($field_record);
                    if ($record_representation->getConfirmationHTML() !== false) {
                        $record_data .= $field->getTitle() . ": " . $record_representation->getConfirmationHTML() . "<br />";
                    }
                }
                $conf->addItem('record_ids[]', $record->getId(), $record_data);
            }
        }
        $conf->addHiddenItem('table_id', $this->table_id);
        $conf->setConfirm($this->lng->txt('dcl_delete_records'), self::CMD_DELETE_RECORDS);
        $conf->setCancel($this->lng->txt('cancel'), self::CMD_CANCEL_DELETE);
        $tpl->setContent($conf->getHTML());
    }


    /**
     * Delete multiple records
     *
     * @param array $record_ids
     */
    public function deleteRecords(array $record_ids = array())
    {
        $record_ids = count($record_ids) ? $record_ids : $_POST['record_ids'];
        $record_ids = (is_null($record_ids)) ? array() : $record_ids;

        // Invoke deletion
        $n_skipped = 0;
        foreach ($record_ids as $record_id) {
            /** @var ilDclBaseRecordModel $record */
            $record = ilDclCache::getRecordCache($record_id);
            if ($record) {
                if ($record->hasPermissionToDelete((int) $_GET['ref_id'])) {
                    $record->doDelete();
                } else {
                    $n_skipped++;
                }
            }
        }

        $n_deleted = (count($record_ids) - $n_skipped);
        if ($n_deleted) {
            ilUtil::sendSuccess(sprintf($this->lng->txt('dcl_deleted_records'), $n_deleted), true);
        }
        if ($n_skipped) {
            ilUtil::sendInfo(sprintf($this->lng->txt('dcl_skipped_delete_records'), $n_skipped), true);
        }
        $this->ctrl->redirect($this, self::CMD_LIST_RECORDS);
    }


    /**
     * @param ilDclBaseRecordModel $record
     *
     * @return bool
     */
    private function recordBelongsToCollection(ilDclBaseRecordModel $record)
    {
        $table = $record->getTable();
        $obj_id = $this->parent_obj->object->getId();
        $obj_id_rec = $table->getCollectionObject()->getId();

        return $obj_id == $obj_id_rec;
    }


    /**
     * Add subtabs
     *
     */
    protected function setSubTabs($active_id = self::GET_MODE)
    {
        global $DIC;
        $ilTabs = $DIC['ilTabs'];

        /** @var ilTabsGUI $ilTabs */
        $this->ctrl->setParameter($this, self::GET_MODE, self::MODE_VIEW);
        $ilTabs->addSubTab('mode_1', $this->lng->txt('view'), $this->ctrl->getLinkTarget($this, self::CMD_LIST_RECORDS));
        $this->ctrl->clearParameters($this);

        if ($this->table_obj->hasPermissionToDeleteRecords((int) $_GET['ref_id'])) {
            $this->ctrl->setParameter($this, self::GET_MODE, self::MODE_MANAGE);
            $ilTabs->addSubTab('mode_2', $this->lng->txt('dcl_manage'), $this->ctrl->getLinkTarget($this, self::CMD_LIST_RECORDS));
            $this->ctrl->clearParameters($this);
        }

        if ($active_id == self::GET_MODE) {
            $active_id = 'mode_' . $this->mode;
        }

        $ilTabs->setSubTabActive($active_id);
    }


    /**
     * @return array
     */
    protected function getAvailableTables()
    {
        if (ilObjDataCollectionAccess::hasWriteAccess($this->parent_obj->ref_id)) {
            $tables = $this->parent_obj->object->getTables();
        } else {
            $tables = $this->parent_obj->object->getVisibleTables();
        }
        $options = array();
        foreach ($tables as $table) {
            $options[$table->getId()] = $table->getTitle();
        }

        return $options;
    }


    /**
     * @param $use_tableview_filter
     *
     * @return array
     */
    protected function getRecordListTableGUI($use_tableview_filter)
    {
        $table_obj = $this->table_obj;

        $list = new ilDclRecordListTableGUI($this, "listRecords", $table_obj, $this->tableview_id, $this->mode);
        if ($use_tableview_filter) {
            $list->initFilter();
            $list->resetFilter();
            $list->initFilterFromTableView();
        } else {
            $list->initFilter();
        }

        $list->setExternalSegmentation(true);
        $list->setExternalSorting(true);
        $list->determineOffsetAndOrder();

        $limit = $list->getLimit();
        $offset = $list->getOffset();

        $data = $table_obj->getPartialRecords($list->getOrderField(), $list->getOrderDirection(), $limit, $offset, $list->getFilter());
        $records = $data['records'];
        $total = $data['total'];

        $list->setMaxCount($total);
        $list->setRecordData($records);

        $list->determineOffsetAndOrder();
        $list->determineLimit();

        return $list;
    }


    /**
     * @internal param $options
     * @internal param $ilToolbar
     */
    protected function createSwitchers()
    {
        global $DIC;
        $ilToolbar = $DIC['ilToolbar'];
        $ilToolbar->setFormAction($this->ctrl->getFormActionByClass("ilDclRecordListGUI", "doTableSwitch"));

        //table switcher
        $options = $this->getAvailableTables();
        if (count($options) > 1) {
            include_once './Services/Form/classes/class.ilSelectInputGUI.php';
            $table_selection = new ilSelectInputGUI('', 'table_id');
            $table_selection->setOptions($options);
            $table_selection->setValue($this->table_id);

            $ilToolbar->addText($this->lng->txt("dcl_table"));
            $ilToolbar->addInputItem($table_selection);
            $button = ilSubmitButton::getInstance();
            $button->setCaption('change');
            $button->setCommand('doTableSwitch');
            $ilToolbar->addButtonInstance($button);
            $ilToolbar->addSeparator();
        }

        //tableview switcher
        $options = array();
        foreach ($this->table_obj->getVisibleTableViews($this->parent_obj->ref_id) as $tableview) {
            $options[$tableview->getId()] = $tableview->getTitle();
        }

        if (count($options) > 1) {
            $tableview_selection = new ilSelectInputGUI('', 'tableview_id');
            $tableview_selection->setOptions($options);
            $tableview_selection->setValue($this->tableview_id);
            $ilToolbar->addText($this->lng->txt("dcl_tableview"));
            $ilToolbar->addInputItem($tableview_selection);

            $button = ilSubmitButton::getInstance();
            $button->setCaption('change');
            $button->setCommand('doTableViewSwitch');
            $ilToolbar->addButtonInstance($button);
            $ilToolbar->addSeparator();
        }
    }


    /**
     * @return bool
     */
    protected function checkAccess()
    {
        return ilObjDataCollectionAccess::hasAccessTo($this->parent_obj->ref_id, $this->table_id, $this->tableview_id);
    }
}
