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
 ********************************************************************
 */

/**
 * Class ilDclBaseFieldModel
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Marcel Raimann <mr@studer-raimann.ch>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @author  Oskar Truffer <ot@studer-raimann.ch>
 * @version $Id:
 * @ingroup ModulesDataCollection
 */
class ilDclTableEditGUI
{
    private ?int $table_id;
    private ilDclTable $table;
    protected ilLanguage $lng;
    protected ilCtrl $ctrl;
    protected ilGlobalTemplateInterface $tpl;
    protected ilToolbarGUI $toolbar;
    protected ilPropertyFormGUI $form;
    protected ILIAS\HTTP\Services $http;
    protected ILIAS\Refinery\Factory $refinery;
    protected ilDclTableListGUI $parent_object;
    protected int $obj_id;

    /**
     * Constructor
     */
    public function __construct(ilDclTableListGUI $a_parent_obj)
    {
        global $DIC;

        $locator = $DIC['ilLocator'];

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->toolbar = $DIC->toolbar();
        $this->parent_object = $a_parent_obj;
        $this->obj_id = $a_parent_obj->getObjId();
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();

        $table_id = null;
        if ($this->http->wrapper()->query()->has("table_id")) {
            $table_id = $this->http->wrapper()->query()->retrieve('table_id', $this->refinery->kindlyTo()->int());
        }

        $this->table_id = $table_id;
        $this->table = ilDclCache::getTableCache($this->table_id);

        $this->ctrl->saveParameter($this, 'table_id');
        if ($this->table->getTitle()) {
            $locator->addItem($this->table->getTitle(), $this->ctrl->getLinkTarget($this, 'edit'));
        }
        $this->tpl->setLocator();

        if (!$this->checkAccess()) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('permission_denied'), true);
            $this->ctrl->redirectByClass('ildclrecordlistgui', 'listRecords');
        }
    }

    public function executeCommand(): void
    {
        $cmd = $this->ctrl->getCmd();

        switch ($cmd) {
            case 'update':
                $this->save("update");
                break;
            default:
                $this->$cmd();
                break;
        }
    }

    public function create(): void
    {
        $this->initForm();
        $this->getStandardValues();
        $this->tpl->setContent($this->form->getHTML());
    }

    public function edit(): void
    {
        if (!$this->table_id) {
            $this->ctrl->redirectByClass("ildclfieldeditgui", "listFields");

            return;
        } else {
            $this->table = ilDclCache::getTableCache($this->table_id);
        }
        $this->initForm("edit");
        $this->getValues();
        $this->tpl->setContent($this->form->getHTML());
    }

    public function getValues(): void
    {
        $values = array(
            'title' => $this->table->getTitle(),
            'add_perm' => (int) $this->table->getAddPerm(),
            'edit_perm' => (int) $this->table->getEditPerm(),
            'edit_perm_mode' => $this->table->getEditByOwner() ? 'own' : 'all',
            'delete_perm' => (int) $this->table->getDeletePerm(),
            'delete_perm_mode' => $this->table->getDeleteByOwner() ? 'own' : 'all',
            'export_enabled' => $this->table->getExportEnabled(),
            'import_enabled' => $this->table->getImportEnabled(),
            'limited' => $this->table->getLimited(),
            'limit_start' => substr($this->table->getLimitStart(), 0, 10) . " " . substr(
                $this->table->getLimitStart(),
                -8
            ),
            'limit_end' => substr($this->table->getLimitEnd(), 0, 10) . " " . substr($this->table->getLimitEnd(), -8),
            'default_sort_field' => $this->table->getDefaultSortField(),
            'default_sort_field_order' => $this->table->getDefaultSortFieldOrder(),
            'description' => $this->table->getDescription(),
            'view_own_records_perm' => $this->table->getViewOwnRecordsPerm(),
            'save_confirmation' => $this->table->getSaveConfirmation(),
        );
        if (!$this->table->getLimitStart()) {
            $values['limit_start'] = null;
        }
        if (!$this->table->getLimitEnd()) {
            $values['limit_end'] = null;
        }
        $this->form->setValuesByArray($values);
    }

    public function getStandardValues(): void
    {
        $values = array(
            'title' => "",
            'add_perm' => 1,
            'edit_perm' => 1,
            'edit_perm_mode' => 'own',
            'delete_perm_mode' => 'own',
            'delete_perm' => 1,
            'edit_by_owner' => 1,
            'export_enabled' => 0,
            'import_enabled' => 0,
            'limited' => 0,
            'limit_start' => null,
            'limit_end' => null,
        );
        $this->form->setValuesByArray($values);
    }

    public function cancel(): void
    {
        $this->ctrl->redirectByClass("ilDclTableListGUI", "listTables");
    }

    /**
     * initEditCustomForm
     */
    public function initForm(string $a_mode = "create"): void
    {
        $this->form = new ilPropertyFormGUI();

        $item = new ilTextInputGUI($this->lng->txt('title'), 'title');
        $item->setRequired(true);
        $this->form->addItem($item);

        // Show default order field, direction and tableswitcher only in edit mode, because table id is not yet given and there are no fields to select
        if ($a_mode != 'create') {
            $this->createTableSwitcher();

            $item = new ilSelectInputGUI($this->lng->txt('dcl_default_sort_field'), 'default_sort_field');
            $item->setInfo($this->lng->txt('dcl_default_sort_field_desc'));
            $fields = array_filter($this->table->getFields(), function (ilDclBaseFieldModel $field) {
                return !is_null($field->getRecordQuerySortObject());
            });
            $options = array(0 => $this->lng->txt('dcl_please_select'));
            foreach ($fields as $field) {
                if ($field->getId() == 'comments') {
                    continue;
                }
                $options[$field->getId()] = $field->getTitle();
            }
            $item->setOptions($options);
            $this->form->addItem($item);

            $item = new ilSelectInputGUI($this->lng->txt('dcl_default_sort_field_order'), 'default_sort_field_order');
            $options = array('asc' => $this->lng->txt('dcl_asc'), 'desc' => $this->lng->txt('dcl_desc'));
            $item->setOptions($options);
            $this->form->addItem($item);
        }

        $item = new ilTextAreaInputGUI($this->lng->txt('additional_info'), 'description');
        $item->setUseRte(true);
        $item->setInfo($this->lng->txt('dcl_additional_info_desc'));
        $item->setRteTagSet('mini');
        $this->form->addItem($item);

        $section = new ilFormSectionHeaderGUI();
        $section->setTitle($this->lng->txt('dcl_permissions_form'));
        $this->form->addItem($section);

        $item = new ilCustomInputGUI();
        $item->setHtml($this->lng->txt('dcl_table_info'));
        $item->setTitle($this->lng->txt('dcl_table_info_title'));
        $this->form->addItem($item);

        $item = new ilCheckboxInputGUI($this->lng->txt('dcl_add_perm'), 'add_perm');
        $item->setInfo($this->lng->txt("dcl_add_perm_desc"));
        $this->form->addItem($item);

        $item = new ilCheckboxInputGUI($this->lng->txt('dcl_save_confirmation'), 'save_confirmation');
        $item->setInfo($this->lng->txt('dcl_save_confirmation_desc'));
        $this->form->addItem($item);

        $item = new ilCheckboxInputGUI($this->lng->txt('dcl_edit_perm'), 'edit_perm');
        $this->form->addItem($item);

        $radios = new ilRadioGroupInputGUI('', 'edit_perm_mode');
        $radios->addOption(new ilRadioOption($this->lng->txt('dcl_all_entries'), 'all'));
        $radios->addOption(new ilRadioOption($this->lng->txt('dcl_own_entries'), 'own'));
        $item->addSubItem($radios);

        $item = new ilCheckboxInputGUI($this->lng->txt('dcl_delete_perm'), 'delete_perm');
        $this->form->addItem($item);

        $radios = new ilRadioGroupInputGUI('', 'delete_perm_mode');
        $radios->addOption(new ilRadioOption($this->lng->txt('dcl_all_entries'), 'all'));
        $radios->addOption(new ilRadioOption($this->lng->txt('dcl_own_entries'), 'own'));
        $item->addSubItem($radios);

        $item = new ilCheckboxInputGUI($this->lng->txt('dcl_view_own_records_perm'), 'view_own_records_perm');
        $this->form->addItem($item);

        $item = new ilCheckboxInputGUI($this->lng->txt('dcl_export_enabled'), 'export_enabled');
        $item->setInfo($this->lng->txt('dcl_export_enabled_desc'));
        $this->form->addItem($item);

        $item = new ilCheckboxInputGUI($this->lng->txt('dcl_import_enabled'), 'import_enabled');
        $item->setInfo($this->lng->txt('dcl_import_enabled_desc'));
        $this->form->addItem($item);

        $item = new ilCheckboxInputGUI($this->lng->txt('dcl_limited'), 'limited');
        $sitem1 = new ilDateTimeInputGUI($this->lng->txt('dcl_limit_start'), 'limit_start');
        $sitem1->setShowTime(true);
        $sitem2 = new ilDateTimeInputGUI($this->lng->txt('dcl_limit_end'), 'limit_end');
        $sitem2->setShowTime(true);
        $item->setInfo($this->lng->txt("dcl_limited_desc"));
        $item->addSubItem($sitem1);
        $item->addSubItem($sitem2);
        $this->form->addItem($item);

        if ($a_mode == "edit") {
            $this->form->addCommandButton('update', $this->lng->txt('dcl_table_' . $a_mode));
        } else {
            $this->form->addCommandButton('save', $this->lng->txt('dcl_table_' . $a_mode));
        }

        $this->form->addCommandButton('cancel', $this->lng->txt('cancel'));
        $this->form->setFormAction($this->ctrl->getFormAction($this, $a_mode));
        if ($a_mode == "edit") {
            $this->form->setTitle($this->lng->txt('dcl_edit_table'));
        } else {
            $this->form->setTitle($this->lng->txt('dcl_new_table'));
        }
    }

    public function doTableSwitch(): void
    {
        $table_id = $this->http->wrapper()->post()->retrieve('table_id', $this->refinery->kindlyTo()->int());
        $this->ctrl->setParameter($this, "table_id", $table_id);
        $this->ctrl->redirect($this, "edit");
    }

    public function save(string $a_mode = "create"): void
    {
        global $DIC;
        $ilTabs = $DIC['ilTabs'];

        if (!ilObjDataCollectionAccess::checkActionForObjId('write', $this->obj_id)) {
            $this->accessDenied();

            return;
        }

        $ilTabs->activateTab("id_fields");
        $this->initForm($a_mode);

        if ($this->checkInput($a_mode)) {
            if ($a_mode != "update") {
                $this->table = ilDclCache::getTableCache();
            } elseif ($this->table_id) {
                $this->table = ilDclCache::getTableCache($this->table_id);
            } else {
                $this->ctrl->redirectByClass("ildclfieldeditgui", "listFields");
            }

            $this->table->setTitle($this->form->getInput("title"));
            $this->table->setObjId($this->obj_id);
            $this->table->setSaveConfirmation((bool) $this->form->getInput('save_confirmation'));
            $this->table->setAddPerm((bool) $this->form->getInput("add_perm"));
            $this->table->setEditPerm((bool) $this->form->getInput("edit_perm"));
            if ($this->table->getEditPerm()) {
                $edit_by_owner = ($this->form->getInput('edit_perm_mode') == 'own');
                $this->table->setEditByOwner($edit_by_owner);
            }
            $this->table->setDeletePerm((bool) $this->form->getInput("delete_perm"));
            if ($this->table->getDeletePerm()) {
                $delete_by_owner = ($this->form->getInput('delete_perm_mode') == 'own');
                $this->table->setDeleteByOwner($delete_by_owner);
            }
            $this->table->setViewOwnRecordsPerm($this->form->getInput('view_own_records_perm'));
            $this->table->setExportEnabled($this->form->getInput("export_enabled"));
            $this->table->setImportEnabled($this->form->getInput("import_enabled"));
            $this->table->setDefaultSortField($this->form->getInput("default_sort_field"));
            $this->table->setDefaultSortFieldOrder($this->form->getInput("default_sort_field_order"));
            $this->table->setLimited($this->form->getInput("limited"));
            $this->table->setDescription($this->form->getInput('description'));
            $limit_start = $this->form->getInput("limit_start");
            $limit_end = $this->form->getInput("limit_end");
            $this->table->setLimitStart($limit_start);
            $this->table->setLimitEnd($limit_end);
            if ($a_mode == "update") {
                $this->table->doUpdate();
                $this->tpl->setOnScreenMessage('success', $this->lng->txt("dcl_msg_table_edited"), true);
                $this->ctrl->redirectByClass("ildcltableeditgui", "edit");
            } else {
                $this->table->doCreate();
                $this->tpl->setOnScreenMessage('success', $this->lng->txt("dcl_msg_table_created"), true);
                $this->ctrl->setParameterByClass("ildclfieldlistgui", "table_id", $this->table->getId());
                $this->ctrl->redirectByClass("ildclfieldlistgui", "listFields");
            }
        } else {
            $this->form->setValuesByPost();
            $this->tpl->setContent($this->form->getHTML());
        }
    }

    /**
     * Custom checks for the form input
     * @param $a_mode 'create' | 'update'
     */
    protected function checkInput(string $a_mode): bool
    {
        $return = $this->form->checkInput();

        // Title of table must be unique in one DC
        if ($a_mode == 'create') {
            if ($title = $this->form->getInput('title')) {
                if (ilObjDataCollection::_hasTableByTitle($title, $this->obj_id)) {
                    $inputObj = $this->form->getItemByPostVar('title');
                    $inputObj->setAlert($this->lng->txt("dcl_table_title_unique"));
                    $return = false;
                }
            }
        }

        if (!$return) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("form_input_not_valid"));
        }

        return $return;
    }

    public function accessDenied(): void
    {
        $this->tpl->setContent("Access denied.");
    }

    public function confirmDelete(): void
    {
        $conf = new ilConfirmationGUI();
        $conf->setFormAction($this->ctrl->getFormAction($this));
        $conf->setHeaderText($this->lng->txt('dcl_confirm_delete_table'));

        $conf->addItem('table', $this->table->getId(), $this->table->getTitle());

        $conf->setConfirm($this->lng->txt('delete'), 'delete');
        $conf->setCancel($this->lng->txt('cancel'), 'cancelDelete');

        $this->tpl->setContent($conf->getHTML());
    }

    public function cancelDelete(): void
    {
        $this->ctrl->redirectByClass("ilDclTableListGUI", "listTables");
    }

    public function delete(): void
    {
        if (count($this->table->getCollectionObject()->getTables()) < 2) {
            $this->tpl->setOnScreenMessage(
                'failure',
                $this->lng->txt("dcl_cant_delete_last_table"),
                true
            ); //TODO change lng var
            $this->table->doDelete(true);
        } else {
            $this->table->doDelete(false);
        }

        $this->ctrl->redirectByClass("ildcltablelistgui", "listtables");
    }

    protected function checkAccess(): bool
    {
        $ref_id = $this->parent_object->getDataCollectionObject()->getRefId();

        return $this->table_id ? ilObjDataCollectionAccess::hasAccessToEditTable(
            $ref_id,
            $this->table_id
        ) : ilObjDataCollectionAccess::hasWriteAccess($ref_id);
    }

    /**
     * @return string[]
     */
    protected function createTableSwitcher(): array
    {
        // Show tables
        $tables = $this->parent_object->getDataCollectionObject()->getTables();

        foreach ($tables as $table) {
            $options[$table->getId()] = $table->getTitle();
        }
        $table_selection = new ilSelectInputGUI($this->lng->txt("dcl_select"), 'table_id');
        $table_selection->setOptions($options);
        $table_selection->setValue($this->table->getId());

        $this->toolbar->setFormAction($this->ctrl->getFormActionByClass("ilDclTableEditGUI", "doTableSwitch"));
        $this->toolbar->addText($this->lng->txt("dcl_select"));
        $this->toolbar->addInputItem($table_selection);
        $button = ilSubmitButton::getInstance();
        $button->setCommand("doTableSwitch");
        $button->setCaption('change');
        $this->toolbar->addButtonInstance($button);

        return $options;
    }
}
