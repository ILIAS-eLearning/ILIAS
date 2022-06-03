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
 * Class ilDclRecordEditGUI
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Marcel Raimann <mr@studer-raimann.ch>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @author  Oskar Truffer <ot@studer-raimann.ch>
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @version $Id:
 */
class ilDclRecordEditGUI
{

    /**
     * Possible redirects after saving/updating a record - use GET['redirect'] to set constants
     */
    const REDIRECT_RECORD_LIST = 1;
    const REDIRECT_DETAIL = 2;

    protected ?int $tableview_id = null;
    protected int $record_id = 0;
    protected int $table_id = 1;
    protected ilDclTable $table;
    protected ilObjDataCollectionGUI $parent_obj;
    protected ilDclBaseRecordModel $record;
    protected ilCtrl $ctrl;
    protected ilGlobalPageTemplate $tpl;
    protected ilLanguage $lng;
    protected ilObjUser $user;
    protected ilDclPropertyFormGUI $form;
    protected ilDclTableView $tableview;
    protected ILIAS\HTTP\Services $http;
    protected ILIAS\Refinery\Factory $refinery;

    /**
     * @param ilObjDataCollectionGUI $parent_obj
     */
    public function __construct(ilObjDataCollectionGUI $parent_obj)
    {
        global $DIC;

        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();

        $this->ctrl = $DIC['ilCtrl'];
        $this->tpl = $DIC['tpl'];
        $this->lng = $DIC['lng'];
        $this->user = $DIC['ilUser'];
        $this->parent_obj = $parent_obj;
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();

        if ($this->http->wrapper()->query()->has('record_id')) {
            $this->record_id = $this->http->wrapper()->query()->retrieve('record_id',
                $this->refinery->kindlyTo()->int());
        }
        if ($this->http->wrapper()->post()->has('record_id')) {
            $this->record_id = $this->http->wrapper()->post()->retrieve('record_id',
                $this->refinery->kindlyTo()->int());
        }
        if ($this->http->wrapper()->query()->has('table_id')) {
            $this->table_id = $this->http->wrapper()->query()->retrieve('table_id', $this->refinery->kindlyTo()->int());
        }
        if ($this->http->wrapper()->post()->has('table_id')) {
            $this->table_id = $this->http->wrapper()->post()->retrieve('table_id', $this->refinery->kindlyTo()->int());
        }
        if ($this->http->wrapper()->query()->has('tableview_id')) {
            $this->tableview_id = $this->http->wrapper()->query()->retrieve('tableview_id',
                $this->refinery->kindlyTo()->int());
        }
        if ($this->http->wrapper()->post()->has('tableview_id')) {
            $this->tableview_id = $this->http->wrapper()->post()->retrieve('tableview_id',
                $this->refinery->kindlyTo()->int());
        }

        if (!$this->tableview_id) {
            $this->tableview_id = ilDclCache::getTableCache($this->table_id)
                                            ->getFirstTableViewId($this->parent_obj->getRefId());
        }
        $this->tableview = ilDclTableView::findOrGetInstance($this->tableview_id);
    }

    public function executeCommand() : void
    {
        $this->getRecord();

        $cmd = $this->ctrl->getCmd();
        $this->$cmd();
    }

    public function getRecord() : void
    {
        $hasMode = $this->http->wrapper()->query()->has('mode');
        if ($hasMode) {
            $mode = $this->http->wrapper()->query()->retrieve('mode', $this->refinery->kindlyTo()->int());

            $this->ctrl->saveParameter($this, 'mode');
            $this->ctrl->setParameterByClass("ildclrecordlistgui", "mode", $mode);
        }
        $this->ctrl->setParameterByClass('ildclrecordlistgui', 'tableview_id', $this->tableview_id);
        $this->ctrl->saveParameter($this, 'redirect');
        if ($this->record_id) {
            $this->record = ilDclCache::getRecordCache($this->record_id);
            if (!($this->record->hasPermissionToEdit($this->parent_obj->getRefId()) and $this->record->hasPermissionToView($this->parent_obj->getRefId())) && !$this->record->hasPermissionToDelete($this->parent_obj->getRefId())) {
                $this->accessDenied();
            }
            $this->table = $this->record->getTable();
            $this->table_id = $this->table->getId();
        } else {
            $this->table = ilDclCache::getTableCache($this->table_id);
            $ref_id = $this->http->wrapper()->query()->retrieve('ref_id', $this->refinery->kindlyTo()->int());
            if (!ilObjDataCollectionAccess::hasAddRecordAccess($ref_id)) {
                $this->accessDenied();
            }
        }
    }

    /**
     * Create new record gui
     */
    public function create() : void
    {
        $this->initForm();
        if ($this->ctrl->isAsynch()) {
            echo $this->form->getHTML();
            exit();
        } else {
            $this->tpl->setContent(
                $this->getLanguageJsKeys()
                . $this->form->getHTML()
            );
        }
    }

    /**
     * Record edit gui
     */
    public function edit() : void
    {
        $this->initForm();
        $this->cleanupTempFiles();

        $this->setFormValues();
        if ($this->ctrl->isAsynch()) {
            echo $this->form->getHTML();
            exit();
        } else {
            $this->tpl->setContent(
                $this->getLanguageJsKeys()
                . $this->form->getHTML()
            );
        }
    }

    /**
     * Delete confirmation
     * @throws ilDclException
     */
    public function confirmDelete() : void
    {
        $conf = new ilConfirmationGUI();
        $conf->setFormAction($this->ctrl->getFormAction($this));
        $conf->setHeaderText($this->lng->txt('dcl_confirm_delete_record'));
        $record = ilDclCache::getRecordCache($this->record_id);

        $all_fields = $this->table->getRecordFields();
        $record_data = "";
        foreach ($all_fields as $field) {
            $field_record = ilDclCache::getRecordFieldCache($record, $field);

            $record_representation = ilDclCache::getRecordRepresentation($field_record);
            if ($record_representation->getConfirmationHTML() !== false) {
                $record_data .= $field->getTitle() . ": " . $record_representation->getConfirmationHTML() . "<br />";
            }
        }
        $conf->addItem('record_id', $record->getId(), $record_data);
        $conf->addHiddenItem('table_id', $this->table_id);
        $conf->addHiddenItem('tableview_id', $this->tableview_id);
        $conf->setConfirm($this->lng->txt('delete'), 'delete');
        $conf->setCancel($this->lng->txt('cancel'), 'cancelDelete');
        $this->tpl->setContent($conf->getHTML());
    }

    /**
     * Cancel deletion
     */
    public function cancelDelete() : void
    {
        $this->ctrl->redirectByClass("ildclrecordlistgui", "listRecords");
    }

    /**
     * Remove record
     */
    public function delete() : void
    {
        $record = ilDclCache::getRecordCache($this->record_id);

        if (!$this->table->hasPermissionToDeleteRecord($this->parent_obj->getRefId(), $record)) {
            $this->accessDenied();

            return;
        }

        $record->doDelete();
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("dcl_record_deleted"), true);
        $this->ctrl->redirectByClass("ildclrecordlistgui", "listRecords");
    }

    /**
     * Return All fields and values from a record ID. If this method is requested over AJAX,
     * data is returned in JSON format
     * @param int $record_id
     * @return array
     */
    public function getRecordData(int $record_id = 0) : array
    {
        $get_record_id = $this->http->wrapper()->query()->retrieve('record_id', $this->refinery->kindlyTo()->int());

        $record_id = ($record_id) ?: $get_record_id;
        $return = array();
        if ($record_id) {
            $record = ilDclCache::getRecordCache((int) $record_id);
            if (is_object($record)) {
                $return = $record->getRecordFieldValues();
            }
        }
        if ($this->ctrl->isAsynch()) {
            echo json_encode($return);
            exit();
        }

        return $return;
    }

    /**
     * init Form
     * @move move parts to RecordRepresentationGUI
     */
    public function initForm() : void
    {
        $this->form = new ilDclPropertyFormGUI();
        $prefix = ($this->ctrl->isAsynch()) ? 'dclajax' : 'dcl'; // Used by datacolleciton.js to select input elements
        $this->form->setId($prefix . $this->table_id . $this->record_id);

        $hidden_prop = new ilHiddenInputGUI("table_id");
        $hidden_prop->setValue($this->table_id);
        $this->form->addItem($hidden_prop);
        $hidden_prop = new ilHiddenInputGUI("tableview_id");
        $hidden_prop->setValue($this->tableview_id);
        $this->form->addItem($hidden_prop);
        if ($this->record_id) {
            $hidden_prop = new ilHiddenInputGUI("record_id");
            $hidden_prop->setValue($this->record_id);
            $this->form->addItem($hidden_prop);
        }

        $this->ctrl->setParameter($this, "record_id", $this->record_id);
        $this->form->setFormAction($this->ctrl->getFormAction($this));
        $allFields = $this->table->getRecordFields();
        $inline_css = '';
        foreach ($allFields as $field) {
            $field_setting = $field->getViewSetting($this->tableview_id);
            if ($field_setting->isVisibleInForm(!$this->record_id)) {
                $item = ilDclCache::getFieldRepresentation($field)->getInputField($this->form, $this->record_id);
                if ($item === null) {
                    continue; // Fields calculating values at runtime, e.g. ilDclFormulaFieldModel do not have input
                }

                if (!ilObjDataCollectionAccess::hasWriteAccess($this->parent_obj->getRefId()) && $field_setting->isLocked(!$this->record_id)) {
                    $item->setDisabled(true);
                }

                $item->setRequired($field_setting->isRequired(!$this->record_id));
                $default_value = null;

                // If creation mode
                if (!$this->record_id) {
                    $default_value = ilDclTableViewBaseDefaultValue::findSingle($field_setting->getFieldObject()->getDatatypeId(),
                        $field_setting->getId());

                    if ($default_value !== null) {
                        if ($item instanceof ilDclCheckboxInputGUI) {
                            $item->setChecked($default_value->getValue());
                        } else {
                            $item->setValue($default_value->getValue());
                        }
                    }
                }
                $this->form->addItem($item);
            }
        }

        $this->tpl->addInlineCss($inline_css);

        // Add possibility to change the owner in edit mode
        if ($this->record_id) {
            $field_setting = $this->tableview->getFieldSetting('owner');
            if ($field_setting->isVisibleEdit()) {
                $ownerField = $this->table->getField('owner');
                $inputfield = ilDclCache::getFieldRepresentation($ownerField)->getInputField($this->form);

                if (!ilObjDataCollectionAccess::hasWriteAccess($this->parent_obj->getRefId()) && $field_setting->isLockedEdit()) {
                    $inputfield->setDisabled(true);
                } else {
                    $inputfield->setRequired(true);
                }

                $this->form->addItem($inputfield);
            }
        }

        // save and cancel commands
        if ($this->record_id) {
            $this->form->setTitle($this->lng->txt("dcl_update_record"));
            $this->form->addCommandButton("save", $this->lng->txt("dcl_update_record"));
            if (!$this->ctrl->isAsynch()) {
                $this->form->addCommandButton("cancelUpdate", $this->lng->txt("cancel"));
            }
        } else {
            $this->form->setTitle($this->lng->txt("dcl_add_new_record"));
            $this->form->addCommandButton("save", $this->lng->txt("save"));
            if (!$this->ctrl->isAsynch()) {
                $this->form->addCommandButton("cancelSave", $this->lng->txt("cancel"));
            }
        }
        $this->ctrl->setParameter($this, "tableview_id", $this->tableview_id);
        $this->ctrl->setParameter($this, "table_id", $this->table_id);
        $this->ctrl->setParameter($this, "record_id", $this->record_id);
    }

    /**
     * Set values from object to form
     */
    public function setFormValues() : bool
    {
        //Get Record-Values
        $record_obj = ilDclCache::getRecordCache($this->record_id);
        if ($record_obj->getId()) {
            //Get Table Field Definitions
            $allFields = $this->table->getFields();
            foreach ($allFields as $field) {
                if ($field->getViewSetting($this->tableview_id)->isVisibleEdit()) {
                    $record_obj->fillRecordFieldFormInput($field->getId(), $this->form);
                }
            }
        } else {
            $this->form->setValuesByPost();
        }

        return true;
    }

    /**
     * Cancel Update
     */
    public function cancelUpdate() : void
    {
        $this->checkAndPerformRedirect(true);
    }

    /**
     * Cancel Save
     */
    public function cancelSave() : void
    {
        $this->cancelUpdate();
    }

    /**
     * @throws ilCtrlException
     * @throws ilDateTimeException
     * @throws ilDclException
     */
    public function saveConfirmation(ilDclBaseRecordModel $record_obj, string $filehash) : void
    {
        $permission = ilObjDataCollectionAccess::hasWriteAccess($this->parent_obj->getRefId());
        if ($permission) {
            $all_fields = $this->table->getRecordFields();
        } else {
            $all_fields = $this->table->getEditableFields(!$this->record_id);
        }

        $date_obj = new ilDateTime(time(), IL_CAL_UNIX);
        $record_obj->setTableId($this->table_id);
        $record_obj->setLastUpdate($date_obj);
        $record_obj->setLastEditBy($this->user->getId());

        $confirmation = new ilConfirmationGUI();
        $confirmation->setFormAction($this->ctrl->getFormAction($this));
        $header_text = $this->lng->txt('dcl_confirm_storing_records');
        if (!$permission && !ilObjDataCollectionAccess::hasEditAccess($this->parent_obj->getRefId())
            && !$this->table->getEditByOwner()
            && !$this->table->getEditPerm()
        ) {
            $header_text .= " " . $this->lng->txt('dcl_confirm_storing_records_no_permission');
        }
        $confirmation->setHeaderText($header_text);

        $confirmation->setCancel($this->lng->txt('dcl_edit_record'), 'edit');
        $confirmation->setConfirm($this->lng->txt('dcl_save_record'), 'save');

        $record_data = "";

        $empty_fileuploads = array();
        foreach ($all_fields as $field) {
            $record_field = $record_obj->getRecordField($field->getId());
            /** @var ilDclBaseRecordFieldModel $record_field */
            $record_field->addHiddenItemsToConfirmation($confirmation);

            if (($record_field instanceof ilDclFileuploadRecordFieldModel || $record_field instanceof ilDclMobRecordFieldModel)
                && $record_field->getValue() == null
            ) {
                $empty_fileuploads['field_' . $field->getId()] = [
                    "name" => "",
                    "type" => "",
                    "tmp_name" => "",
                    "error" => 4,
                    "size" => 0
                ];
            }
            $record_representation = ilDclFieldFactory::getRecordRepresentationInstance($record_field);

            if ($record_representation->getConfirmationHTML() !== false) {
                $record_data .= $field->getTitle() . ": " . $record_representation->getConfirmationHTML() . "<br />";
            }
        }

        $confirmation->addHiddenItem('ilfilehash', $filehash);
        $confirmation->addHiddenItem('empty_fileuploads', htmlspecialchars(json_encode($empty_fileuploads)));
        $confirmation->addHiddenItem('table_id', $this->table_id);
        $confirmation->addHiddenItem('tableview_id', $this->tableview_id);
        $confirmation->addItem('save_confirmed', 1, $record_data);

        if ($this->ctrl->isAsynch()) {
            echo $confirmation->getHTML();
            exit();
        } else {
            $this->tpl->setContent($confirmation->getHTML());
        }
    }

    /**
     * Save record
     */
    public function save() : void
    {
        global $DIC;
        $ilAppEventHandler = $DIC['ilAppEventHandler'];

        $this->initForm();

        // if save confirmation is enabled: Temporary file-uploads need to be handled
        $has_save_confirmed = $this->http->wrapper()->post()->has('format');
        $has_ilfilehash = $this->http->wrapper()->post()->has('ilfilehash');
        $has_record_id = $this->http->wrapper()->post()->has('ilfilehash');

        if ($this->table->getSaveConfirmation() && $has_save_confirmed && $has_ilfilehash && $has_record_id && !$this->ctrl->isAsynch()) {

            $ilfilehash = $this->http->wrapper()->post()->retrieve('ilfilehash', $this->refinery->kindlyTo()->string());

            ilDclPropertyFormGUI::rebuildTempFileByHash($ilfilehash);

            $has_empty_fileuploads = $this->http->wrapper()->post()->has('empty_fileuploads');

            //handle empty fileuploads, since $_FILES has to have an entry for each fileuploadGUI
            if ($has_empty_fileuploads) {
                $empty_fileuploads = $this->http->wrapper()->post()->retrieve('empty_fileuploads',
                    $this->refinery->kindlyTo()->string());
                if (json_decode($empty_fileuploads)) {
                    $_FILES = $_FILES + json_decode($empty_fileuploads, true);
                }
            }
        }

        $valid = $this->form->checkInput();

        $record_obj = ilDclCache::getRecordCache($this->record_id);
        $unchanged_obj = $record_obj;
        $date_obj = new ilDateTime(time(), IL_CAL_UNIX);
        $record_obj->setTableId($this->table_id);
        $record_obj->setLastUpdate($date_obj);
        $record_obj->setLastEditBy($this->user->getId());

        $create_mode = !isset($this->record_id);

        if (ilObjDataCollectionAccess::hasWriteAccess($this->parent_obj->getRefId()) || $create_mode) {
            $all_fields = $this->table->getRecordFields();
        } else {
            $all_fields = $this->table->getEditableFields(!$this->record_id);
        }

        //Check if we can create this record.
        foreach ($all_fields as $field) {
            try {
                $field->checkValidityFromForm($this->form, $this->record_id);
            } catch (ilDclInputException $e) {
                $valid = false;
                $item = $this->form->getItemByPostVar('field_' . $field->getId());
                $item->setAlert($e);
            }
        }

        if (!$valid) {
            $this->sendFailure($this->lng->txt('form_input_not_valid'));

            return;
        }

        if ($valid) {
            if ($create_mode) {
                if (!(ilObjDataCollectionAccess::hasPermissionToAddRecord($this->parent_obj->getRefId(),
                    $this->table_id))) {
                    $this->accessDenied();

                    return;
                }

                // when save_confirmation is enabled, not yet confirmed and we have not an async-request => prepare for displaying confirmation
                if ($this->table->getSaveConfirmation() && $this->form->getInput('save_confirmed') == null && !$this->ctrl->isAsynch()) {
                    // temporary store fileuploads (reuse code from ilPropertyFormGUI)
                    $hash = $this->http->wrapper()->post()->retrieve('ilfilehash',
                        $this->refinery->kindlyTo()->string());
                    foreach ($_FILES as $field => $data) {
                        if (is_array($data["tmp_name"])) {
                            foreach ($data["tmp_name"] as $idx => $upload) {
                                if (is_array($upload)) {
                                    foreach ($upload as $idx2 => $file) {
                                        if ($file && is_uploaded_file($file)) {
                                            $file_name = $data["name"][$idx][$idx2];
                                            $file_type = $data["type"][$idx][$idx2];
                                            $this->form->keepTempFileUpload($hash, $field, $file, $file_name,
                                                $file_type, $idx, $idx2);
                                        }
                                    }
                                } else {
                                    if ($upload && is_uploaded_file($upload)) {
                                        $file_name = $data["name"][$idx];
                                        $file_type = $data["type"][$idx];
                                        $this->form->keepTempFileUpload($hash, $field, $upload, $file_name, $file_type,
                                            $idx);
                                    }
                                }
                            }
                        } else {
                            $this->form->keepTempFileUpload($hash, $field, $data["tmp_name"], $data["name"],
                                $data["type"]);
                        }
                    }

                    //edit values, they are valid we already checked them above
                    foreach ($all_fields as $field) {
                        $record_obj->setRecordFieldValueFromForm($field->getId(), $this->form);
                    }

                    $this->saveConfirmation($record_obj, $hash);

                    return;
                }

                $record_obj->setOwner($this->user->getId());
                $record_obj->setCreateDate($date_obj);
                $record_obj->setTableId($this->table_id);
                $record_obj->doCreate();

                $this->record_id = $record_obj->getId();
                $create_mode = true;
            } else {
                if (!$record_obj->hasPermissionToEdit($this->parent_obj->getRefId())) {
                    $this->accessDenied();

                    return;
                }
            }

            //edit values, they are valid we already checked them above
            foreach ($all_fields as $field) {
                $field_setting = $field->getViewSetting($this->tableview_id);

                if ($field_setting->isVisibleInForm($create_mode) &&
                    (!$field_setting->isLocked($create_mode) || ilObjDataCollectionAccess::hasWriteAccess($this->parent_obj->getRefId()))) {
                    // set all visible fields
                    $record_obj->setRecordFieldValueFromForm($field->getId(), $this->form);
                } elseif ($create_mode) {
                    // set default values when creating
                    $default_value = ilDclTableViewBaseDefaultValue::findSingle(
                        $field_setting->getFieldObject()->getDatatypeId(),
                        $field_setting->getId()
                    );
                    if ($default_value !== null) {
                        $record_obj->setRecordFieldValue($field->getId(), $default_value->getValue());
                    }
                }
            }

            // Do we need to set a new owner for this record?
            if (!$create_mode && $this->tableview->getFieldSetting('owner')->isVisibleEdit()) {
                if ($this->http->wrapper()->post()->has('field_owner')) {
                    $field_owner = $this->http->wrapper()->post()->retrieve('field_owner',
                        $this->refinery->kindlyTo()->int());
                    $owner_id = ilObjUser::_lookupId($field_owner);
                    if (!$owner_id) {
                        $this->sendFailure($this->lng->txt('user_not_known'));

                        return;
                    }
                    $record_obj->setOwner($owner_id);
                }
            }

            $dispatchEvent = "update";

            $dispatchEventData = array(
                'dcl' => $this->parent_obj->getDataCollectionObject(),
                'table_id' => $this->table_id,
                'record_id' => $record_obj->getId(),
                'record' => $record_obj,
            );

            if ($create_mode) {
                $dispatchEvent = "create";
                ilObjDataCollection::sendNotification("new_record", $this->table_id, $record_obj->getId());
            } else {
                $dispatchEventData['prev_record'] = $unchanged_obj;
            }

            $record_obj->doUpdate($create_mode);

            $ilAppEventHandler->raise(
                'Modules/DataCollection',
                $dispatchEvent . 'Record',
                $dispatchEventData
            );

            $this->ctrl->setParameter($this, "table_id", $this->table_id);
            $this->ctrl->setParameter($this, "tableview_id", $this->tableview_id);
            $this->ctrl->setParameter($this, "record_id", $this->record_id);

            if (!$this->ctrl->isAsynch()) {
                $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
            }

            $this->checkAndPerformRedirect();
            if ($this->ctrl->isAsynch()) {
                // If ajax request, return the form in edit mode again
                $this->record_id = $record_obj->getId();
                $this->initForm();
                $this->setFormValues();
                echo ilUtil::getSystemMessageHTML($this->lng->txt('msg_obj_modified'),
                        'success') . $this->form->getHTML();
                exit();
            } else {
                $this->ctrl->redirectByClass("ildclrecordlistgui", "listRecords");
            }
        } else {
            // Form not valid...
            //TODO: URL title flushes on invalid form
            $this->form->setValuesByPost();
            if ($this->ctrl->isAsynch()) {
                echo $this->form->getHTML();
                exit();
            } else {
                $this->tpl->setContent($this->getLanguageJsKeys() . $this->form->getHTML());
            }
        }
    }

    /**
     * Checks to what view (table or detail) should be redirected and performs redirect
     */
    protected function checkAndPerformRedirect(bool $force_redirect = false) : void
    {
        $hasRedirect = $this->http->wrapper()->query()->has('redirect');

        if ($force_redirect || ($hasRedirect && !$this->ctrl->isAsynch())) {
            $redirect = $this->http->wrapper()->query()->retrieve('redirect', $this->refinery->kindlyTo()->int());

            switch ($redirect) {
                case self::REDIRECT_DETAIL:
                    $this->ctrl->setParameterByClass('ilDclDetailedViewGUI', 'record_id', $this->record_id);
                    $this->ctrl->setParameterByClass('ilDclDetailedViewGUI', 'table_id', $this->table_id);
                    $this->ctrl->setParameterByClass('ilDclDetailedViewGUI', 'tableview_id', $this->tableview_id);
                    $this->ctrl->redirectByClass("ilDclDetailedViewGUI", "renderRecord");
                    break;
                case self::REDIRECT_RECORD_LIST:
                    $this->ctrl->redirectByClass("ildclrecordlistgui", "listRecords");
                    break;
                default:
                    $this->ctrl->redirectByClass("ildclrecordlistgui", "listRecords");
            }
        }
    }

    protected function accessDenied() : void
    {
        if (!$this->ctrl->isAsynch()) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('dcl_msg_no_perm_edit'), true);
            $this->ctrl->redirectByClass('ildclrecordlistgui', 'listRecords');
        } else {
            echo $this->lng->txt('dcl_msg_no_perm_edit');
            exit();
        }
    }

    protected function sendFailure(string $message) : void
    {
        $keep = !$this->ctrl->isAsynch();
        $this->form->setValuesByPost();
        if ($this->ctrl->isAsynch()) {
            echo ilUtil::getSystemMessageHTML($message, 'failure') . $this->form->getHTML();
            exit();
        } else {
            $this->tpl->setOnScreenMessage('failure', $message, $keep);

            // Fill locked fields on edit mode - otherwise they are empty (workaround)
            if (isset($this->record_id)) {
                $record_obj = ilDclCache::getRecordCache($this->record_id);
                if ($record_obj->getId()) {
                    //Get Table Field Definitions
                    $allFields = $this->table->getFields();
                    foreach ($allFields as $field) {
                        $field_setting = $field->getViewSetting($this->tableview_id);
                        if (
                            $field_setting->isLockedEdit() &&
                            $field_setting->isVisibleEdit()
                        ) {
                            $record_obj->fillRecordFieldFormInput($field->getId(), $this->form);
                        }
                    }
                }
            }

            $this->tpl->setContent($this->getLanguageJsKeys() . $this->form->getHTML());
        }
    }

    /**
     * This function is only used by the ajax request if searching for ILIAS references. It builds the html for the search results.
     */
    public function searchObjects() : void
    {
        $search = $this->http->wrapper()->post()->retrieve('search_for', $this->refinery->kindlyTo()->string());
        $dest = $this->http->wrapper()->post()->retrieve('dest', $this->refinery->kindlyTo()->string());
        $html = "";
        $query_parser = new ilQueryParser($search);
        $query_parser->setMinWordLength(1);
        $query_parser->setCombination(ilQueryParser::QP_COMBINATION_AND);
        $query_parser->parse();
        if (!$query_parser->validate()) {
            $html .= $query_parser->getMessage() . "<br />";
        }

        // only like search since fulltext does not support search with less than 3 characters
        $object_search = new ilLikeObjectSearch($query_parser);
        $res = $object_search->performSearch();
        //$res->setRequiredPermission('copy');
        $res->filter(ROOT_FOLDER_ID, true);

        if (!count($results = $res->getResultsByObjId())) {
            $html .= $this->lng->txt('dcl_no_search_results_found_for') . ' ' . $search . "<br />";
        }
        $results = $this->parseSearchResults($results);

        foreach ($results as $entry) {
            $tpl = new ilTemplate("tpl.dcl_tree.html", true, true, "Modules/DataCollection");
            foreach ((array) $entry['refs'] as $reference) {
                $path = new ilPathGUI();
                $tpl->setCurrentBlock('result');
                $tpl->setVariable('RESULT_PATH',
                    $path->getPath(ROOT_FOLDER_ID, (int) $reference) . " » " . $entry['title']);
                $tpl->setVariable('RESULT_REF', $reference);
                $tpl->setVariable('FIELD_ID', $dest);
                $tpl->parseCurrentBlock();
            }
            $html .= $tpl->get();
        }

        echo $html;
        exit;
    }

    protected function getLanguageJsKeys() : string
    {
        return "<script>ilDataCollection.strings.add_value='" . $this->lng->txt('add_value') . "';</script>";
    }

    /**
     * Parse search results
     * @param ilObject[] $a_res
     * @return array
     */
    protected function parseSearchResults(array $a_res) : array
    {
        $rows = array();
        foreach ($a_res as $obj_id => $references) {
            $r = array();
            $r['title'] = ilObject::_lookupTitle($obj_id);
            $r['desc'] = ilObject::_lookupDescription($obj_id);
            $r['obj_id'] = $obj_id;
            $r['refs'] = $references;
            $rows[] = $r;
        }

        return $rows;
    }

    /**
     * Cleanup temp-files
     */
    protected function cleanupTempFiles() : void
    {
        $has_ilfilehash = $this->http->wrapper()->post()->has('ilfilehash');
        if ($has_ilfilehash) {
            $ilfilehash = $this->http->wrapper()->post()->retrieve('ilfilehash', $this->refinery->kindlyTo()->string());
            $this->form->cleanupTempFiles($ilfilehash);
        }
    }

    public function getForm() : ilDclPropertyFormGUI
    {
        return $this->form;
    }
}
