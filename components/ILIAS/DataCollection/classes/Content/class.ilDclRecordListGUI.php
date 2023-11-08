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
 *********************************************************************/

declare(strict_types=1);

class ilDclRecordListGUI
{
    public const GET_TABLE_ID = 'table_id';
    public const GET_TABLEVIEW_ID = 'tableview_id';
    public const GET_MODE = "mode";
    public const MODE_VIEW = "view";
    public const MODE_MANAGE = "manage";
    public const CMD_LIST_RECORDS = 'listRecords';
    public const CMD_SHOW = 'show';
    public const CMD_CONFIRM_DELETE_RECORDS = 'confirmDeleteRecords';
    public const CMD_CANCEL_DELETE = 'cancelDelete';
    public const CMD_DELETE_RECORDS = 'deleteRecords';
    public const CMD_SHOW_IMPORT_EXCEL = 'showImportExcel';

    private ilAccessHandler $access;
    private ilGlobalTemplateInterface $tpl;
    protected \ILIAS\UI\Factory $ui_factory;
    protected \ILIAS\UI\Renderer $renderer;

    /**
     * Stores current mode active
     */
    protected string $mode = self::MODE_VIEW;
    protected ilDclTable $table_obj;
    protected ?int $table_id;
    protected int $obj_id;
    protected ilObjDataCollectionGUI $parent_obj;
    protected ?int $tableview_id;
    protected static array $available_modes = [self::MODE_VIEW, self::MODE_MANAGE];

    protected ilCtrl $ctrl;
    protected ilToolbarGUI $toolbar;
    protected ilLanguage $lng;
    protected ilTabsGUI $tabs;
    protected ILIAS\HTTP\Services $http;
    protected ILIAS\Refinery\Factory $refinery;
    protected \ILIAS\ResourceStorage\Services $irss;

    /**
     * @throws ilCtrlException
     */
    public function __construct(ilObjDataCollectionGUI $a_parent_obj, int $table_id, int $tableview_id)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->toolbar = $DIC->toolbar();
        $this->lng = $DIC->language();
        $this->tabs = $DIC->tabs();
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
        $this->irss = $DIC->resourceStorage();
        $this->access = $DIC->access();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->ui_factory = $DIC->ui()->factory();
        $this->renderer = $DIC->ui()->renderer();

        $this->table_id = $table_id;
        $this->tableview_id = $tableview_id;

        $this->obj_id = $a_parent_obj->getObject()->getId();
        $this->parent_obj = $a_parent_obj;
        $this->table_obj = ilDclCache::getTableCache($table_id);

        $this->ctrl->setParameterByClass(ilDclRecordEditGUI::class, self::GET_TABLE_ID, $this->table_id);
        $this->ctrl->setParameterByClass(ilDclRecordEditGUI::class, self::GET_TABLEVIEW_ID, $this->tableview_id);
        $this->ctrl->setParameterByClass(ilDclDetailedViewGUI::class, self::GET_TABLEVIEW_ID, $this->tableview_id);

        $this->mode = self::MODE_VIEW;

        if ($this->http->wrapper()->query()->has(self::GET_MODE)) {
            $mode = $this->http->wrapper()->query()->retrieve(self::GET_MODE, $this->refinery->kindlyTo()->string());
            if (in_array($mode, self::$available_modes, true)) {
                $this->mode = $mode;
            }
        }
    }

    public function getRefId(): int
    {
        return $this->parent_obj->getRefId();
    }

    public function getObjId(): int
    {
        return $this->parent_obj->getObject()->getId();
    }

    /**
     * execute command
     * @throws ilCtrlException
     */
    public function executeCommand(): void
    {
        if (!$this->checkAccess()) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('permission_denied'), true);
            return;
        }

        $this->ctrl->saveParameter($this, self::GET_MODE);
        $cmd = $this->ctrl->getCmd(self::CMD_SHOW);

        // 'show' fills all filters with the predefined values from the tableview,
        // whereas 'listRecords' handels the filters "normally", filling them from the POST-variable
        switch ($cmd) {
            case self::CMD_SHOW:
                $this->setSubTabs($this->mode);
                $this->listRecords(true);
                break;
            case self::CMD_CANCEL_DELETE:
            case self::CMD_LIST_RECORDS:
                $this->setSubTabs($this->mode);
                $this->listRecords();
                break;
            case self::CMD_CONFIRM_DELETE_RECORDS:
                $this->confirmDeleteRecords();
                break;
            case self::CMD_DELETE_RECORDS:
                $this->deleteRecords();
                break;
            case self::CMD_SHOW_IMPORT_EXCEL:
                $this->tabs->setBackTarget($this->lng->txt('back'), $this->ctrl->getLinkTarget($this));
                $this->$cmd();
                break;

            default:
                $this->$cmd();
                break;
        }
    }

    public function listRecords(bool $use_tableview_filter = false): void
    {
        $list = $this->getRecordListTableGUI($use_tableview_filter);

        $this->createSwitchers();

        $permission_to_add_or_import = ilObjDataCollectionAccess::hasPermissionToAddRecord(
            $this->parent_obj->getRefId(),
            $this->table_id
        ) && $this->table_obj->hasCustomFields();
        if ($permission_to_add_or_import) {
            $this->ctrl->setParameterByClass(ilDclRecordEditGUI::class, "record_id", null);

            $add_new = $this->ui_factory->button()->primary(
                $this->lng->txt("dcl_add_new_record"),
                $this->ctrl->getFormActionByClass(ilDclRecordEditGUI::class, "create")
            );
            $this->toolbar->addStickyItem($add_new);
        }

        if ($permission_to_add_or_import && $this->table_obj->getImportEnabled()) {
            $this->ctrl->setParameterByClass(ilDclRecordEditGUI::class, "record_id", null);

            $import_button = $this->ui_factory->button()->standard(
                $this->lng->txt("dcl_import_records .xls"),
                $this->ctrl->getFormActionByClass(ilDclRecordListGUI::class, self::CMD_SHOW_IMPORT_EXCEL)
            );
            $this->toolbar->addComponent($import_button);
        }

        if (count($this->table_obj->getRecordFields()) == 0) {
            $message = $this->lng->txt("dcl_no_fields_yet") . " "
                . (ilObjDataCollectionAccess::hasAccessToFields(
                    $this->parent_obj->getRefId(),
                    $this->table_id
                ) ? $this->lng->txt("dcl_create_fields") : "");
            $this->tpl->setOnScreenMessage('info', $message, true);
        }

        $this->tpl->setPermanentLink("dcl", $this->parent_obj->getRefId(), "_" . $this->tableview_id);

        if ($desc = $this->table_obj->getDescription()) {
            $ilSetting = new ilSetting('advanced_editing');
            if ($ilSetting->get('advanced_editing_javascript_editor')) {
                $desc = "<div class='ilDclTableDescription'>" . $desc . "</div>";
            } else {
                $desc = "<div class='ilDclTableDescription'>" . nl2br(ilUtil::stripSlashes($desc)) . "</div>";
            }
        }
        $this->tpl->setContent($desc . $list->getHTML());
    }

    public function showImportExcel(?ilPropertyFormGUI $form = null): void
    {
        if (!$form) {
            $form = $this->initImportForm();
        }
        $this->tpl->setContent($form->getHTML());
    }

    /**
     * Init form
     */
    public function initImportForm(): ilPropertyFormGUI
    {
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
    public function importExcel(): void
    {
        if (!(ilObjDataCollectionAccess::hasPermissionToAddRecord(
            $this->parent_obj->getRefId(),
            $this->table_id
        )) || !$this->table_obj->getImportEnabled()) {
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
     */
    private function importRecords(string $file, bool $simulate = false): void
    {
        $importer = new ilDclContentImporter($this->parent_obj->object->getRefId(), $this->table_id);
        $result = $importer->import($file, $simulate);

        $this->endImport($result['line'], $result['warnings']);
    }

    /**
     * End import
     * @throws ilTemplateException
     */
    public function endImport(int $i, array $warnings): void
    {
        $output = new ilTemplate("tpl.dcl_import_terminated.html", true, true, "components/ILIAS/DataCollection");
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
        $output->setVariable("BACK_LINK", $this->ctrl->getLinkTargetByClass(ilDclRecordListGUI::class, "listRecords"));
        $output->setVariable("BACK", $this->lng->txt("back"));
        $this->tpl->setContent($output->get());
    }

    /**
     * @throws ilCtrlException
     */
    protected function applyFilter(): void
    {
        $table = new ilDclRecordListTableGUI($this, "listRecords", $this->table_obj, $this->tableview_id);
        $table->initFilter();
        $table->resetOffset();
        $table->writeFilterToSession();
        $this->ctrl->redirect($this, self::CMD_LIST_RECORDS);
    }

    /**
     * @throws ilCtrlException
     */
    protected function resetFilter(): void
    {
        $table = new ilDclRecordListTableGUI($this, "show", $this->table_obj, $this->tableview_id);
        $table->initFilter();
        $table->resetOffset();
        $table->resetFilter();
        $this->ctrl->redirect($this, self::CMD_LIST_RECORDS);
    }

    /**
     * send File to User
     */
    public function sendFile(): void
    {
        $hasIlFileHash = $this->http->wrapper()->query()->has('ilfilehash');
        //need read access to receive file
        if ($this->access->checkAccess('read', "", $this->parent_obj->getRefId())) {
            // deliver temp-files
            if ($hasIlFileHash) {
                $filehash = $this->http->wrapper()->query()->retrieve(
                    'ilfilehash',
                    $this->refinery->kindlyTo()->string()
                );
                $field_id = $this->http->wrapper()->query()->retrieve('field_id', $this->refinery->kindlyTo()->int());
                ilDclPropertyFormGUI::rebuildTempFileByHash($filehash);

                $filepath = $_FILES["field_" . $field_id]['tmp_name'];
                $filetitle = $_FILES["field_" . $field_id]['name'];

                ilFileDelivery::deliverFileLegacy($filepath, $filetitle);
            } else {
                $rec_id = $this->http->wrapper()
                                     ->query()
                                     ->retrieve('record_id', $this->refinery->kindlyTo()->int());

                $record = ilDclCache::getRecordCache($rec_id);
                if (!$this->recordBelongsToCollection($record)) {
                    return;
                }

                $field_id = $this->http->wrapper()
                                       ->query()
                                       ->retrieve('field_id', $this->refinery->kindlyTo()->string());



                // Find the current revision
                $rid_string = $record->getRecordFieldValue($field_id);
                $identification = $this->irss->manage()->find($rid_string);
                if ($identification === null) {
                    return;
                }
                $current_revision = $this->irss->manage()->getCurrentRevision($identification);

                // Download the File
                $this->irss->consume()
                           ->download($identification)
                           ->overrideFileName($current_revision->getTitle())
                           ->run();
            }
        }
    }

    /**
     * Confirm deletion of multiple records
     */
    public function confirmDeleteRecords(): void
    {
        $this->tabs->clearTargets();

        $conf = new ilConfirmationGUI();
        $conf->setFormAction($this->ctrl->getFormAction($this));
        $conf->setHeaderText($this->lng->txt('dcl_confirm_delete_records'));

        $has_record_ids = $this->http->wrapper()->post()->has('record_ids');
        $record_ids = [];
        if ($has_record_ids) {
            $record_ids = $this->http->wrapper()->post()->retrieve(
                'record_ids',
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int())
            );
        }
        $all_fields = $this->table_obj->getRecordFields();
        foreach ($record_ids as $record_id) {
            /** @var ilDclBaseRecordModel $record */
            $record = ilDclCache::getRecordCache($record_id);
            $record_data = "";
            foreach ($all_fields as $field) {
                $field_record = ilDclCache::getRecordFieldCache($record, $field);

                $record_representation = ilDclCache::getRecordRepresentation($field_record);
                if ($record_representation->getConfirmationHTML() != false) {
                    $record_data .= $field->getTitle() . ": " . $record_representation->getConfirmationHTML() . "<br />";
                }
            }
            $conf->addItem('record_ids[]', (string)$record->getId(), $record_data);
        }
        $conf->addHiddenItem('table_id', (string)$this->table_id);
        $conf->setConfirm($this->lng->txt('dcl_delete_records'), self::CMD_DELETE_RECORDS);
        $conf->setCancel($this->lng->txt('cancel'), self::CMD_CANCEL_DELETE);
        $this->tpl->setContent($conf->getHTML());
    }

    /**
     * Delete multiple records
     */
    public function deleteRecords(): void
    {
        $has_record_ids = $this->http->wrapper()->post()->has('record_ids');
        $record_ids = [];
        if ($has_record_ids) {
            $record_ids = $this->http->wrapper()->post()->retrieve(
                'record_ids',
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int())
            );
        }

        // Invoke deletion
        $n_skipped = 0;
        foreach ($record_ids as $record_id) {
            /** @var ilDclBaseRecordModel $record */
            $record = ilDclCache::getRecordCache($record_id);
            $ref_id = $this->parent_obj->getRefId();

            if ($record->hasPermissionToDelete($ref_id)) {
                $record->doDelete();
            } else {
                $n_skipped++;
            }
        }

        $n_deleted = (count($record_ids) - $n_skipped);
        if ($n_deleted) {
            $message = sprintf(
                $this->lng->txt('dcl_deleted_records'),
                $n_deleted
            );
            $this->tpl->setOnScreenMessage('success', $message, true);
        }
        if ($n_skipped) {
            $message = sprintf(
                $this->lng->txt('dcl_skipped_delete_records'),
                $n_skipped
            );
            $this->tpl->setOnScreenMessage('info', $message, true);
        }
        $this->ctrl->redirect($this, self::CMD_LIST_RECORDS);
    }

    private function recordBelongsToCollection(ilDclBaseRecordModel $record): bool
    {
        $table = $record->getTable();
        $obj_id = $this->parent_obj->object->getId();
        $obj_id_rec = $table->getCollectionObject()->getId();

        return $obj_id == $obj_id_rec;
    }

    protected function setSubTabs(string $active_mode = self::GET_MODE): void
    {
        $this->ctrl->setParameter($this, self::GET_MODE, self::MODE_VIEW);
        $this->tabs->addSubTab(
            self::MODE_VIEW,
            $this->lng->txt('view'),
            $this->ctrl->getLinkTarget($this, self::CMD_LIST_RECORDS)
        );

        $ref_id = $this->http->wrapper()->query()->retrieve('ref_id', $this->refinery->kindlyTo()->int());
        if ($this->table_obj->hasPermissionToDeleteRecords($ref_id)) {
            $this->ctrl->setParameter($this, self::GET_MODE, self::MODE_MANAGE);
            $this->tabs->addSubTab(
                self::MODE_MANAGE,
                $this->lng->txt('dcl_manage'),
                $this->ctrl->getLinkTarget($this, self::CMD_LIST_RECORDS)
            );
        }
        $this->tabs->activateSubTab($active_mode);
        $this->ctrl->clearParameters($this);
    }

    protected function getRecordListTableGUI(bool $use_tableview_filter): ilDclRecordListTableGUI
    {
        $table_obj = $this->table_obj;

        $list = new ilDclRecordListTableGUI($this, "listRecords", $table_obj, $this->tableview_id, $this->mode);
        $list->initFilter();
        if ($use_tableview_filter) {
            $list->initFilter();
            $list->resetOffset();
            $list->resetFilter();
            $list->initFilterFromTableView();
        }

        $list->setExternalSegmentation(true);
        $list->setExternalSorting(true);
        $list->determineOffsetAndOrder();

        $limit = $list->getLimit();
        $offset = $list->getOffset();

        $num_records = count($table_obj->getPartialRecords(
            (string)$this->getRefId(),
            $list->getOrderField(),
            $list->getOrderDirection(),
            $limit,
            $offset,
            $list->getFilter()
        ));

        // Fix no data found on new filter application when on a site other than the first
        if ($num_records === 0) {
            $list->resetOffset();
            $offset = 0;
        }

        $data = $table_obj->getPartialRecords(
            (string)$this->getRefId(),
            $list->getOrderField(),
            $list->getOrderDirection(),
            $limit,
            $offset,
            $list->getFilter()
        );
        $records = $data['records'];
        $total = $data['total'];

        $list->setMaxCount($total);
        $list->setRecordData($records);

        $list->determineOffsetAndOrder();
        $list->determineLimit();

        return $list;
    }

    protected function createSwitchers(): void
    {
        if (ilObjDataCollectionAccess::hasWriteAccess($this->parent_obj->getRefId())) {
            $tables = $this->parent_obj->object->getTables();
        } else {
            $tables = $this->parent_obj->object->getVisibleTables();
        }

        $switcher = new ilDclSwitcher($this->toolbar, $this->ui_factory, $this->ctrl, $this->lng);
        $switcher->addTableSwitcherToToolbar(
            $tables,
            self::class,
            self::CMD_SHOW
        );

        $switcher->addViewSwitcherToToolbar(
            $this->table_obj->getVisibleTableViews($this->parent_obj->getRefId()),
            $this->getTableId(),
            self::class,
            self::CMD_SHOW
        );
    }

    protected function checkAccess(): bool
    {
        if (null === $this->table_id || null === $this->tableview_id) {
            return false;
        }

        return ilObjDataCollectionAccess::hasAccessTo(
            $this->parent_obj->getRefId(),
            $this->table_id,
            $this->tableview_id
        );
    }

    public function getTableId(): int
    {
        return $this->table_id;
    }

    public function getTableviewId(): int
    {
        return $this->tableview_id;
    }
}
