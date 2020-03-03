<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDRecord.php');
include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php');
include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDPermissionHelper.php');
include_once './Services/AdvancedMetaData/classes/class.ilAdvancedMDRecordScope.php';
/**
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 *
 * @ilCtrl_Calls ilAdvancedMDSettingsGUI: ilPropertyFormGUI
 * @ingroup ServicesAdvancedMetaData
 */
class ilAdvancedMDSettingsGUI
{
    const MODE_ADMINISTRATION = 1;
    const MODE_OBJECT = 2;

    /**
     * Active settings mode
     * @var null
     */
    private $mode = null;


    protected $lng;
    protected $tpl;
    protected $ctrl;
    protected $tabs;
    protected $permissions; // [ilAdvancedMDPermissionHelper]
    protected $ref_id = null;
    protected $obj_id = null; // [int]
    protected $obj_type = null; // [string]
    protected $sub_type = null; // [string]
    
    /**
     * @var ilLogger
     */
    private $logger = null;
    
    /**
     * Constructor
     *
     * @access public
     *
     */
    public function __construct($a_ref_id = null, $a_obj_type = null, $a_sub_type = null)
    {
        global $DIC;

        $tpl = $DIC['tpl'];
        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];
        $ilTabs = $DIC['ilTabs'];
        
        $this->ctrl = $ilCtrl;
        $this->lng = $lng;
        $this->lng->loadLanguageModule('meta');
        $this->tpl = $tpl;
        $this->tabs_gui = $ilTabs;
        
        $this->logger = $GLOBALS['DIC']->logger()->amet();
        
        $this->ref_id = $a_ref_id;
        if ($this->ref_id) {
            $this->obj_id = ilObject::_lookupObjId($a_ref_id);
        }

        if (!$this->ref_id) {
            $this->mode = self::MODE_ADMINISTRATION;
        } else {
            $this->mode = self::MODE_OBJECT;
        }


        $this->obj_type = $a_obj_type;
        $this->sub_type = $a_sub_type
            ? $a_sub_type
            : "-";
        
        if (
            $this->obj_id &&
            !$this->obj_type) {
            $this->obj_type = ilObject::_lookupType($this->obj_id);
        }
        
        $this->permissions = ilAdvancedMDPermissionHelper::getInstance();
    }
    
    protected function getPermissions()
    {
        return $this->permissions;
    }
    
    /**
     * Execute command
     *
     * @access public
     * @param
     *
     */
    public function executeCommand()
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();
        
        if (!$this->obj_id) {
            $this->setSubTabs();
        }
        
        switch ($next_class) {
            case "ilpropertyformgui":
                $this->initRecordObject();
                $this->initForm(
                    $this->record->getRecordId() > 0 ? 'edit' : 'create'
                );
                $GLOBALS['DIC']->ctrl()->forwardCommand($this->form);
                break;
            
            default:
                if (!$cmd) {
                    $cmd = 'showRecords';
                }
                $this->$cmd();
        }
    }
    
    /**
     * show record list
     *
     * @access public
     * @param
     *
     */
    public function showRecords()
    {
        global $DIC;

        $ilToolbar = $DIC['ilToolbar'];
        $ilAccess = $DIC['ilAccess'];
        
        $perm = $this->getPermissions()->hasPermissions(
            ilAdvancedMDPermissionHelper::CONTEXT_MD,
            $_REQUEST["ref_id"],
            array(
                ilAdvancedMDPermissionHelper::ACTION_MD_CREATE_RECORD,
                ilAdvancedMDPermissionHelper::ACTION_MD_IMPORT_RECORDS
        )
        );
        
        if ($perm[ilAdvancedMDPermissionHelper::ACTION_MD_CREATE_RECORD]) {
            include_once "Services/UIComponent/Button/classes/class.ilLinkButton.php";
            $button = ilLinkButton::getInstance();
            $button->setCaption("add");
            $button->setUrl($this->ctrl->getLinkTarget($this, "createRecord"));
            $ilToolbar->addButtonInstance($button);
            
            if ($perm[ilAdvancedMDPermissionHelper::ACTION_MD_IMPORT_RECORDS]) {
                $ilToolbar->addSeparator();
            }
        }
        
        if ($perm[ilAdvancedMDPermissionHelper::ACTION_MD_IMPORT_RECORDS]) {
            include_once "Services/UIComponent/Button/classes/class.ilLinkButton.php";
            $button = ilLinkButton::getInstance();
            $button->setCaption("import");
            $button->setUrl($this->ctrl->getLinkTarget($this, "importRecords"));
            $ilToolbar->addButtonInstance($button);
        }
        
        include_once("./Services/AdvancedMetaData/classes/class.ilAdvancedMDRecordTableGUI.php");
        $table_gui = new ilAdvancedMDRecordTableGUI($this, "showRecords", $this->getPermissions(), (bool) $this->obj_id);
        $table_gui->setTitle($this->lng->txt("md_record_list_table"));
        $table_gui->setData($this->getParsedRecordObjects());
        
        // permissions?
        //$table_gui->addCommandButton('createRecord',$this->lng->txt('add'));
        $table_gui->addMultiCommand("exportRecords", $this->lng->txt('export'));
        $table_gui->setSelectAllCheckbox("record_id");
        
        if ($ilAccess->checkAccess('write', '', $_REQUEST["ref_id"])) {
            $table_gui->addMultiCommand("confirmDeleteRecords", $this->lng->txt("delete"));
            $table_gui->addCommandButton("updateRecords", $this->lng->txt("save"));
        }

        $DIC->ui()->mainTemplate()->setContent($table_gui->getHTML());
        return true;
    }
    
    public function showPresentation()
    {
        if ($this->initFormSubstitutions()) {
            if (is_object($this->form)) {
                $this->tabs_gui->setSubTabActive('md_adv_presentation');
                return $this->tpl->setContent($this->form->getHTML());
            }
        }
        return $this->showRecords();
    }
    
    /**
     * Update substitution
     *
     * @access public
     *
     */
    public function updateSubstitutions()
    {
        global $DIC;

        $ilAccess = $DIC['ilAccess'];
        
        if (!$ilAccess->checkAccess('write', '', $_REQUEST["ref_id"])) {
            $this->ctrl->redirect($this, "showPresentation");
        }
        
        foreach (ilAdvancedMDRecord::_getActivatedObjTypes() as $obj_type) {
            $perm = null;
            // :TODO: hardwired?
            if (in_array($obj_type, ['cat','crs','sess','grp','iass'])) {
                $perm =	$this->getPermissions()->hasPermissions(
                    ilAdvancedMDPermissionHelper::CONTEXT_SUBSTITUTION,
                    $obj_type,
                    array(
                        ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_SHOW_DESCRIPTION
                        ,ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_SHOW_FIELDNAMES
                        ,ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_FIELD_POSITIONS
                )
                );
            }
            
            include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDSubstitution.php');
            $sub = ilAdvancedMDSubstitution::_getInstanceByObjectType($obj_type);
            
            if ($perm && $perm[ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_SHOW_DESCRIPTION]) {
                $sub->enableDescription($_POST['enabled_desc_' . $obj_type]);
            }
            
            if ($perm && $perm[ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_SHOW_FIELDNAMES]) {
                $sub->enableFieldNames((int) $_POST['enabled_field_names_' . $obj_type]);
            }
            
            $definitions = ilAdvancedMDFieldDefinition::getInstancesByObjType($obj_type);
            $definitions = $sub->sortDefinitions($definitions);
        
            // gather existing data
            $counter = 1;
            $old_sub = array();
            foreach ($definitions as $def) {
                $field_id = $def->getFieldId();
                $old_sub[$field_id] = array(
                    "active" => $sub->isSubstituted($field_id),
                    "pos" => $counter++,
                    "bold" => $sub->isBold($field_id),
                    "newline" => $sub->hasNewline($field_id)
                );
            }
        
            $sub->resetSubstitutions(array());
            
            $new_sub = array();
            foreach ($definitions as $def) {
                $field_id = $def->getFieldId();
                $old = $old_sub[$field_id];
                
                $perm_def = $this->getSubstitutionFieldPermissions($obj_type, $field_id);
                if ($perm_def["show"]) {
                    $active = (isset($_POST['show'][$obj_type][$field_id]) && $_POST['show'][$obj_type][$field_id]);
                } else {
                    $active = $old["active"];
                }
                
                if ($active) {
                    $new_sub[$field_id] = $old;
                
                    if ($perm[ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_FIELD_POSITIONS]) {
                        $new_sub[$field_id]["pos"] = (int) $_POST['position'][$obj_type][$field_id];
                    }
                    if ($perm_def["bold"]) {
                        $new_sub[$field_id]["bold"] = (isset($_POST['bold'][$obj_type][$field_id]) && $_POST['bold'][$obj_type][$field_id]);
                    }
                    if ($perm_def["newline"]) {
                        $new_sub[$field_id]["newline"] = (isset($_POST['newline'][$obj_type][$field_id]) && $_POST['newline'][$obj_type][$field_id]);
                    }
                }
            }
        
            if (sizeof($new_sub)) {
                $new_sub = ilUtil::sortArray($new_sub, "pos", "asc", true, true);
                foreach ($new_sub as $field_id => $field) {
                    $sub->appendSubstitution($field_id, $field["bold"], $field["newline"]);
                }
            }
            
            $sub->update();
        }
        
        
        ilUtil::sendSuccess($this->lng->txt('settings_saved'), true);
        $this->ctrl->redirect($this, "showPresentation");
    }
    
    /**
     * Export records
     *
     * @access public
     */
    public function exportRecords()
    {
        if (!isset($_POST['record_id'])) {
            ilUtil::sendFailure($this->lng->txt('select_one'));
            $this->showRecords();
            return false;
        }
        
        // all records have to be exportable
        $fail = array();
        foreach ($_POST['record_id'] as $record_id) {
            if (!$this->getPermissions()->hasPermission(
                ilAdvancedMDPermissionHelper::CONTEXT_RECORD,
                $record_id,
                ilAdvancedMDPermissionHelper::ACTION_RECORD_EXPORT
            )) {
                $record = ilAdvancedMDRecord::_getInstanceByRecordId($record_id);
                $fail[] = $record->getTitle();
            }
        }
        if ($fail) {
            ilUtil::sendFailure($this->lng->txt('msg_no_perm_copy') . " " . implode(", ", $fail), true);
            $this->ctrl->redirect($this, "showRecords");
        }
        
        include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDRecordXMLWriter.php');
        $xml_writer = new ilAdvancedMDRecordXMLWriter($_POST['record_id']);
        $xml_writer->write();
        
        include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDRecordExportFiles.php');
        $export_files = new ilAdvancedMDRecordExportFiles($this->obj_id);
        $export_files->create($xml_writer->xmlDumpMem());
        
        ilUtil::sendSuccess($this->lng->txt('md_adv_records_exported'));
        $this->showFiles();
    }
    
    /**
     * show export files
     *
     * @access public
     * @param
     *
     */
    public function showFiles()
    {
        $this->tabs_gui->setSubTabActive('md_adv_file_list');
        
        include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDRecordExportFiles.php');
        $files = new ilAdvancedMDRecordExportFiles($this->obj_id);
        $file_data = $files->readFilesInfo();

        include_once("./Services/AdvancedMetaData/classes/class.ilAdvancedMDRecordExportFilesTableGUI.php");
        $table_gui = new ilAdvancedMDRecordExportFilesTableGUI($this, "showFiles");
        $table_gui->setTitle($this->lng->txt("md_record_export_table"));
        $table_gui->parseFiles($file_data);
        $table_gui->addMultiCommand("downloadFile", $this->lng->txt('download'));
        
        if ($GLOBALS['DIC']->access()->checkAccess('write', '', $this->ref_id)) {
            $table_gui->addMultiCommand("confirmDeleteFiles", $this->lng->txt("delete"));
        }
        $table_gui->addCommandButton('showFiles', $this->lng->txt('cancel'));
        $table_gui->setSelectAllCheckbox("file_id");
        
        $this->tpl->setContent($table_gui->getHTML());
    }
    
    /**
     * Download XML file
     *
     * @access public
     * @param
     *
     */
    public function downloadFile()
    {
        if (!isset($_POST['file_id']) or count($_POST['file_id']) != 1) {
            ilUtil::sendFailure($this->lng->txt('md_adv_select_one_file'));
            $this->showFiles();
            return false;
        }
        
        include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDRecordExportFiles.php');
        $files = new ilAdvancedMDRecordExportFiles($this->obj_id);
        $abs_path = $files->getAbsolutePathByFileId((int) $_POST['file_id'][0]);
        
        ilUtil::deliverFile($abs_path, 'ilias_meta_data_record.xml', 'application/xml');
    }
    
    /**
     * confirm delete files
     *
     * @access public
     *
     */
    public function confirmDeleteFiles()
    {
        if (!isset($_POST['file_id'])) {
            ilUtil::sendFailure($this->lng->txt('select_one'));
            $this->showFiles();
            return false;
        }
    
        include_once("Services/Utilities/classes/class.ilConfirmationGUI.php");
        $c_gui = new ilConfirmationGUI();
        
        // set confirm/cancel commands
        $c_gui->setFormAction($this->ctrl->getFormAction($this, "deleteFiles"));
        $c_gui->setHeaderText($this->lng->txt("md_adv_delete_files_sure"));
        $c_gui->setCancel($this->lng->txt("cancel"), "showFiles");
        $c_gui->setConfirm($this->lng->txt("confirm"), "deleteFiles");

        include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDRecordExportFiles.php');
        $files = new ilAdvancedMDRecordExportFiles($this->obj_id);
        $file_data = $files->readFilesInfo();


        // add items to delete
        foreach ($_POST["file_id"] as $file_id) {
            $info = $file_data[$file_id];
            $c_gui->addItem("file_id[]", $file_id, is_array($info['name']) ? implode(',', $info['name']) : 'No Records');
        }
        $this->tpl->setContent($c_gui->getHTML());
    }
    
    /**
     * Delete files
     *
     * @access public
     * @param
     *
     */
    public function deleteFiles()
    {
        if (!isset($_POST['file_id'])) {
            ilUtil::sendFailure($this->lng->txt('select_one'));
            $this->editFiles();
            return false;
        }
        
        if (!$GLOBALS['DIC']->access()->checkAccess('write', '', $this->ref_id)) {
            ilUtil::sendFailure($this->lng->txt('permission_denied'), true);
            $GLOBALS['DIC']->ctrl()->redirect($this, 'showFiles');
        }

        include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDRecordExportFiles.php');
        $files = new ilAdvancedMDRecordExportFiles($this->obj_id);
        
        foreach ($_POST['file_id'] as $file_id) {
            $files->deleteByFileId((int) $file_id);
        }
        ilUtil::sendSuccess($this->lng->txt('md_adv_deleted_files'));
        $this->showFiles();
    }
    
    /**
     * Confirm delete
     *
     * @access public
     *
     */
    public function confirmDeleteRecords()
    {
        if (!isset($_POST['record_id'])) {
            ilUtil::sendFailure($this->lng->txt('select_one'));
            $this->showRecords();
            return false;
        }

        include_once("Services/Utilities/classes/class.ilConfirmationGUI.php");
        $c_gui = new ilConfirmationGUI();
        
        // set confirm/cancel commands
        $c_gui->setFormAction($this->ctrl->getFormAction($this, "deleteRecords"));
        $c_gui->setHeaderText($this->lng->txt("md_adv_delete_record_sure"));
        $c_gui->setCancel($this->lng->txt("cancel"), "showRecords");
        $c_gui->setConfirm($this->lng->txt("confirm"), "deleteRecords");

        // add items to delete
        foreach ($_POST["record_id"] as $record_id) {
            $record = ilAdvancedMDRecord::_getInstanceByRecordId($record_id);
            $c_gui->addItem("record_id[]", $record_id, $record->getTitle() ? $record->getTitle() : 'No Title');
        }
        $this->tpl->setContent($c_gui->getHTML());
    }
    
    /**
     * Permanently delete records
     *
     * @access public
     *
     */
    public function deleteRecords()
    {
        if (!isset($_POST['record_id'])) {
            ilUtil::sendFailure($this->lng->txt('select_one'));
            $this->showRecords();
            return false;
        }
        
        // all records have to be deletable
        $fail = array();
        foreach ($_POST['record_id'] as $record_id) {
            // must not delete global records in local context
            if ($this->obj_id) {
                $record = ilAdvancedMDRecord::_getInstanceByRecordId($record_id);
                if (!$record->getParentObject()) {
                    $fail[] = $record->getTitle();
                }
            }
            
            if (!$this->getPermissions()->hasPermission(
                ilAdvancedMDPermissionHelper::CONTEXT_RECORD,
                $record_id,
                ilAdvancedMDPermissionHelper::ACTION_RECORD_DELETE
            )) {
                $record = ilAdvancedMDRecord::_getInstanceByRecordId($record_id);
                $fail[] = $record->getTitle();
            }
        }
        if ($fail) {
            ilUtil::sendFailure($this->lng->txt('msg_no_perm_delete') . " " . implode(", ", $fail), true);
            $this->ctrl->redirect($this, "showRecords");
        }
        
        foreach ($_POST['record_id'] as $record_id) {
            $record = ilAdvancedMDRecord::_getInstanceByRecordId($record_id);
            $record->delete();
        }
        ilUtil::sendSuccess($this->lng->txt('md_adv_deleted_records'), true);
        $this->ctrl->redirect($this, "showRecords");
    }
    
    /**
     * Save records (assigned object typed)
     *
     * @access public
     * @param
     *
     */
    public function updateRecords()
    {
        // sort positions and renumber
        $positions = $_POST['pos'];
        asort($positions);

        $sorted_positions = [];
        $i = 1;
        foreach ($positions as $record_id => $pos) {
            $sorted_positions[$record_id] = $i++;
        }

        $selected_global = array();
        foreach ($this->getParsedRecordObjects() as $item) {
            $perm = $this->getPermissions()->hasPermissions(
                ilAdvancedMDPermissionHelper::CONTEXT_RECORD,
                $item['id'],
                array(
                    ilAdvancedMDPermissionHelper::ACTION_RECORD_TOGGLE_ACTIVATION
                    ,array(ilAdvancedMDPermissionHelper::ACTION_RECORD_EDIT_PROPERTY,
                        ilAdvancedMDPermissionHelper::SUBACTION_RECORD_OBJECT_TYPES)
                )
            );
                        
            if ($this->mode == self::MODE_ADMINISTRATION) {
                $record_obj = ilAdvancedMDRecord::_getInstanceByRecordId($item['id']);
                
                if ($perm[ilAdvancedMDPermissionHelper::ACTION_RECORD_EDIT_PROPERTY][ilAdvancedMDPermissionHelper::SUBACTION_RECORD_OBJECT_TYPES]) {
                    $obj_types = array();
                    if (is_array($_POST['obj_types'][$record_obj->getRecordId()])) {
                        foreach ($_POST['obj_types'][$record_obj->getRecordId()] as $type => $status) {
                            if ($status) {
                                $type = explode(":", $type);
                                $obj_types[] = array(
                                    "obj_type" => ilUtil::stripSlashes($type[0]),
                                    "sub_type" => ilUtil::stripSlashes($type[1]),
                                    "optional" => ((int) $status == 2)
                                );
                            }
                        }
                    }
                    $record_obj->setAssignedObjectTypes($obj_types);
                }
                
                if ($perm[ilAdvancedMDPermissionHelper::ACTION_RECORD_TOGGLE_ACTIVATION]) {
                    $record_obj->setActive(isset($_POST['active'][$record_obj->getRecordId()]));
                }

                $record_obj->setGlobalPosition((int) $sorted_positions[$record_obj->getRecordId()]);
                $record_obj->update();
            } elseif ($perm[ilAdvancedMDPermissionHelper::ACTION_RECORD_TOGGLE_ACTIVATION]) {
                // global, optional record
                if ($item['readonly'] &&
                    $item['optional'] &&
                    $_POST['active'][$item['id']]) {
                    $selected_global[] = $item['id'];
                } elseif ($item['local']) {
                    $record_obj = ilAdvancedMDRecord::_getInstanceByRecordId($item['id']);
                    $record_obj->setActive(isset($_POST['active'][$item['id']]));
                    $record_obj->update();
                }
            }

            // save local sorting
            if ($this->mode == self::MODE_OBJECT) {
                global $DIC;

                $local_position = new \ilAdvancedMDRecordObjectOrdering($item['id'], $this->obj_id, $DIC->database());
                $local_position->setPosition((int) $sorted_positions[$item['id']]);
                $local_position->save();
            }
        }

        if ($this->obj_type) {
            ilAdvancedMDRecord::saveObjRecSelection($this->obj_id, $this->sub_type, $selected_global);
        }

        ilUtil::sendSuccess($this->lng->txt('settings_saved'), true);
        $this->ctrl->redirect($this, "showRecords");
    }
    
    /**
     * show delete fields confirmation screen
     *
     * @access public
     *
     */
    public function confirmDeleteFields()
    {
        if (!isset($_POST['field_id'])) {
            ilUtil::sendFailure($this->lng->txt('select_one'));
            $this->editFields();
            return false;
        }
        
        $this->ctrl->saveParameter($this, 'record_id');
        
        include_once("Services/Utilities/classes/class.ilConfirmationGUI.php");
        $c_gui = new ilConfirmationGUI();
        
        // set confirm/cancel commands
        $c_gui->setFormAction($this->ctrl->getFormAction($this, "deleteFields"));
        $c_gui->setHeaderText($this->lng->txt("md_adv_delete_fields_sure"));
        $c_gui->setCancel($this->lng->txt("cancel"), "editFields");
        $c_gui->setConfirm($this->lng->txt("confirm"), "deleteFields");

        // add items to delete
        foreach ($_POST["field_id"] as $field_id) {
            $field = ilAdvancedMDFieldDefinition::getInstance($field_id);
            $c_gui->addItem("field_id[]", $field_id, $field->getTitle() ? $field->getTitle() : 'No Title');
        }
        $this->tpl->setContent($c_gui->getHTML());
    }
    
    /**
     * delete fields
     *
     * @access public
     * @param
     *
     */
    public function deleteFields()
    {
        $this->ctrl->saveParameter($this, 'record_id');
        
        if (!isset($_POST['field_id'])) {
            ilUtil::sendFailure($this->lng->txt('select_one'));
            $this->editFields();
            return false;
        }
        
        // all fields have to be deletable
        $fail = array();
        foreach ($_POST['field_id'] as $field_id) {
            if (!$this->getPermissions()->hasPermission(
                ilAdvancedMDPermissionHelper::CONTEXT_FIELD,
                $field_id,
                ilAdvancedMDPermissionHelper::ACTION_FIELD_DELETE
            )) {
                $field = ilAdvancedMDFieldDefinition::getInstance($field_id);
                $fail[] = $field->getTitle();
            }
        }
        if ($fail) {
            ilUtil::sendFailure($this->lng->txt('msg_no_perm_delete') . " " . implode(", ", $fail), true);
            $this->ctrl->redirect($this, "editFields");
        }
                
        foreach ($_POST["field_id"] as $field_id) {
            $field = ilAdvancedMDFieldDefinition::getInstance($field_id);
            $field->delete();
        }
        ilUtil::sendSuccess($this->lng->txt('md_adv_deleted_fields'), true);
        $this->ctrl->redirect($this, "editFields");
    }
    
    /**
     * Edit one record
     *
     * @access public
     * @param
     *
     */
    public function editRecord()
    {
        $this->ctrl->saveParameter($this, 'record_id');
        $this->initRecordObject();
        $this->initForm('edit');
        $this->tpl->setContent($this->form->getHTML());
    }
    
    public function editFields()
    {
        global $DIC;

        $ilToolbar = $DIC['ilToolbar'];
        
        $this->ctrl->saveParameter($this, 'record_id');
        $this->initRecordObject();
        
        $perm = $this->getPermissions()->hasPermissions(
            ilAdvancedMDPermissionHelper::CONTEXT_RECORD,
            $this->record->getRecordId(),
            array(
                ilAdvancedMDPermissionHelper::ACTION_RECORD_CREATE_FIELD
                ,ilAdvancedMDPermissionHelper::ACTION_RECORD_FIELD_POSITIONS
        )
        );
        
        $filter_warn = array();
        if ($perm[ilAdvancedMDPermissionHelper::ACTION_RECORD_CREATE_FIELD]) {
            // type selection
            include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
            $types = new ilSelectInputGUI("", "ftype");
            $options = array();
            foreach (ilAdvancedMDFieldDefinition::getValidTypes() as $type) {
                $field = ilAdvancedMDFieldDefinition::getInstance(null, $type);
                $options[$type] = $this->lng->txt($field->getTypeTitle());
                
                if (!$field->isFilterSupported()) {
                    $filter_warn[] = $this->lng->txt($field->getTypeTitle());
                }
            }
            $types->setOptions($options);
            $ilToolbar->addInputItem($types);
            
            $ilToolbar->setFormAction($this->ctrl->getFormAction($this, "createField"));
            
            include_once "Services/UIComponent/Button/classes/class.ilSubmitButton.php";
            $button = ilSubmitButton::getInstance();
            $button->setCaption("add");
            $button->setCommand("createField");
            $ilToolbar->addButtonInstance($button);
        }
        
        // #17092
        if (sizeof($filter_warn)) {
            ilUtil::sendInfo(sprintf($this->lng->txt("md_adv_field_filter_warning"), implode(", ", $filter_warn)));
        }
    
        // show field table
        include_once('./Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php');
        $fields = ilAdvancedMDFieldDefinition::getInstancesByRecordId($this->record->getRecordId());
        
        include_once("./Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldTableGUI.php");
        $table_gui = new ilAdvancedMDFieldTableGUI($this, "editRecord", $this->getPermissions(), $perm[ilAdvancedMDPermissionHelper::ACTION_RECORD_FIELD_POSITIONS]);
        $table_gui->setTitle($this->lng->txt("md_adv_field_table"));
        $table_gui->parseDefinitions($fields);
        if (sizeof($fields)) {
            $table_gui->addCommandButton("updateFields", $this->lng->txt("save"));
        }
        $table_gui->addCommandButton("showRecords", $this->lng->txt('cancel'));
        $table_gui->addMultiCommand("confirmDeleteFields", $this->lng->txt("delete"));
        $table_gui->setSelectAllCheckbox("field_id");
        
        $this->tpl->setContent($table_gui->getHTML());
    }
    
    /**
     * Update fields
     *
     * @access public
     *
     */
    public function updateFields()
    {
        $this->ctrl->saveParameter($this, 'record_id');
        
        if (!isset($_GET['record_id']) or !$_GET['record_id']) {
            ilUtil::sendFailure($this->lng->txt('select_one'));
            $this->editFields();
            return false;
        }
        
        include_once('./Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php');
        $fields = ilAdvancedMDFieldDefinition::getInstancesByRecordId($_GET['record_id']);
        
        if ($this->getPermissions()->hasPermission(
            ilAdvancedMDPermissionHelper::CONTEXT_RECORD,
            $_GET['record_id'],
            ilAdvancedMDPermissionHelper::ACTION_RECORD_FIELD_POSITIONS
        )) {
            if (!isset($_POST['position']) or !is_array($_POST['position'])) {
                $this->editFields();
                return false;
            }
            // sort by position
            asort($_POST['position'], SORT_NUMERIC);
            $positions = array_flip(array_keys($_POST['position']));
            foreach ($fields as $field) {
                $field->setPosition($positions[$field->getFieldId()]);
                $field->update();
            }
        }
                
        foreach ($fields as $field) {
            if ($this->getPermissions()->hasPermission(
                ilAdvancedMDPermissionHelper::CONTEXT_FIELD,
                $field->getFieldId(),
                ilAdvancedMDPermissionHelper::ACTION_FIELD_EDIT_PROPERTY,
                ilAdvancedMDPermissionHelper::SUBACTION_FIELD_SEARCHABLE
            )) {
                $field->setSearchable(isset($_POST['searchable'][$field->getFieldId()]) ? true : false);
                $field->update();
            }
        }
        
        
        ilUtil::sendSuccess($this->lng->txt('settings_saved'), true);
        $this->ctrl->redirect($this, "editFields");
    }

    /**
     * Update record
     *
     * @access public
     * @param
     *
     */
    public function updateRecord()
    {
        if (!isset($_GET['record_id']) or !$_GET['record_id']) {
            ilUtil::sendFailure($this->lng->txt('select_one'));
            $this->showRecords();
            return false;
        }
        $this->initRecordObject();
        $this->loadRecordFormData();
        
        $this->logger->dump($_POST);
        
        if (!$this->record->validate()) {
            ilUtil::sendFailure($this->lng->txt('fill_out_all_required_fields'));
            $this->editRecord();
            return false;
        }
        $this->record->update();
        ilUtil::sendSuccess($this->lng->txt('settings_saved'));
        $this->showRecords();
        return true;
    }
    

    /**
     * Show
     *
     * @access public
     * @param
     *
     */
    public function createRecord()
    {
        $this->initRecordObject();
        $this->initForm('create');
        $this->tpl->setContent($this->form->getHTML());
        return true;
    }
    
    public function importRecords()
    {
        // Import Table
        $this->initImportForm();
        $this->tpl->setContent($this->import_form->getHTML());
    }
    
    /**
     * show import form
     *
     * @access protected
     */
    protected function initImportForm()
    {
        if (is_object($this->import_form)) {
            return true;
        }
        
        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
        $this->import_form = new ilPropertyFormGUI();
        $this->import_form->setMultipart(true);
        $this->import_form->setFormAction($this->ctrl->getFormAction($this));
        
        // add file property
        $file = new ilFileInputGUI($this->lng->txt('file'), 'file');
        $file->setSuffixes(array('xml'));
        $file->setRequired(true);
        $this->import_form->addItem($file);
        
        $this->import_form->setTitle($this->lng->txt('md_adv_import_record'));
        $this->import_form->addCommandButton('importRecord', $this->lng->txt('import'));
        $this->import_form->addCommandButton('showRecords', $this->lng->txt('cancel'));
    }
    
    /**
     * import xml file
     *
     * @access public
     * @param
     *
     */
    public function importRecord()
    {
        $this->initImportForm();
        if (!$this->import_form->checkInput()) {
            $this->import_form->setValuesByPost();
            $this->createRecord();
            return false;
        }
        
        include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDRecordImportFiles.php');
        $import_files = new ilAdvancedMDRecordImportFiles();
        if (!$create_time = $import_files->moveUploadedFile($_FILES['file']['tmp_name'])) {
            $this->createRecord();
            return false;
        }
        
        try {
            include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDRecordParser.php');
            $parser = new ilAdvancedMDRecordParser($import_files->getImportFileByCreationDate($create_time));
            
            // local import?
            if ($this->obj_id) {
                $parser->setContext($this->obj_id, $this->obj_type, $this->sub_type);
            }
            
            // Validate
            $parser->setMode(ilAdvancedMDRecordParser::MODE_INSERT_VALIDATION);
            $parser->startParsing();
            
            // Insert
            $parser->setMode(ilAdvancedMDRecordParser::MODE_INSERT);
            $parser->startParsing();
            ilUtil::sendSuccess($this->lng->txt('md_adv_added_new_record'), true);
            $this->ctrl->redirect($this, "showRecords");
        } catch (ilSAXParserException $exc) {
            ilUtil::sendFailure($exc->getMessage(), true);
            $this->ctrl->redirect($this, "importRecords");
        }

        // Finally delete import file
        $import_files->deleteFileByCreationDate($create_time);
        return true;
    }
    
    
    /**
     * Save record
     *
     * @access public
     * @param
     *
     */
    public function saveRecord()
    {
        $this->initRecordObject();
        $this->loadRecordFormData();
        
        if ($this->obj_type) {
            $this->record->setAssignedObjectTypes(array(
                array(
                    "obj_type"=>$this->obj_type,
                    "sub_type"=>$this->sub_type,
                    "optional"=>false
            )));
        }
        
        if (!$this->record->validate()) {
            ilUtil::sendFailure($this->lng->txt('fill_out_all_required_fields'));
            $this->createRecord();
            return false;
        }
        $this->record->save();
        
        ilUtil::sendSuccess($this->lng->txt('md_adv_added_new_record'));
        $this->showRecords();
    }
    
    /**
     * Edit field
     *
     * @access public
     *
     */
    public function editField(ilPropertyFormGUI $a_form = null)
    {
        if (!$_REQUEST["record_id"] || !$_REQUEST["field_id"]) {
            return $this->editFields();
        }
        
        $this->ctrl->saveParameter($this, 'record_id');
        $this->ctrl->saveParameter($this, 'field_id');
                 
        if (!$a_form) {
            $field_definition = ilAdvancedMDFieldDefinition::getInstance((int) $_REQUEST['field_id']);
            $a_form = $this->initFieldForm($field_definition);
        }
        $this->tpl->setContent($a_form->getHTML());
    }
    
    /**
     * Update field
     *
     * @access public
     *
     */
    public function updateField()
    {
        if (!$_REQUEST["record_id"] || !$_REQUEST["field_id"]) {
            return $this->editFields();
        }
        
        $this->ctrl->saveParameter($this, 'record_id');
        $this->ctrl->saveParameter($this, 'field_id');
         
        $confirm = false;
        $field_definition = ilAdvancedMDFieldDefinition::getInstance((int) $_REQUEST['field_id']);
        $form = $this->initFieldForm($field_definition);
        if ($form->checkInput()) {
            $field_definition->importDefinitionFormPostValues($form, $this->getPermissions());
            if (!$field_definition->importDefinitionFormPostValuesNeedsConfirmation()) {
                $field_definition->update();

                ilUtil::sendSuccess($this->lng->txt('settings_saved'), true);
                $this->ctrl->redirect($this, "editFields");
            } else {
                $confirm = true;
            }
        }
        
        $form->setValuesByPost();
        
        // fields needs confirmation of updated settings
        if ($confirm) {
            ilUtil::sendInfo($this->lng->txt("md_adv_confirm_definition"));
            $field_definition->prepareDefinitionFormConfirmation($form);
        }
        
        $this->editField($form);
    }
    
    /**
     * Show field type selection
     *
     * @access public
     *
     */
    public function createField(ilPropertyFormGUI $a_form = null)
    {
        if (!$_REQUEST["record_id"] || !$_REQUEST["ftype"]) {
            return $this->editFields();
        }
        
        $this->ctrl->saveParameter($this, 'record_id');
        $this->ctrl->saveParameter($this, 'ftype');

        if (!$a_form) {
            include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php');
            $field_definition = ilAdvancedMDFieldDefinition::getInstance(null, $_REQUEST["ftype"]);
            $field_definition->setRecordId($_REQUEST["record_id"]);
            $a_form = $this->initFieldForm($field_definition);
        }
        $this->tpl->setContent($a_form->getHTML());
    }
    
    /**
     * create field
     *
     * @access public
     */
    public function saveField()
    {
        if (!$_REQUEST["record_id"] || !$_REQUEST["ftype"]) {
            return $this->editFields();
        }
        
        $this->ctrl->saveParameter($this, 'record_id');
        $this->ctrl->saveParameter($this, 'ftype');
        
        include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php');
        $field_definition = ilAdvancedMDFieldDefinition::getInstance(null, $_REQUEST["ftype"]);
        $field_definition->setRecordId($_REQUEST["record_id"]);
        $form = $this->initFieldForm($field_definition);
        
        if ($form->checkInput()) {
            $field_definition->importDefinitionFormPostValues($form, $this->getPermissions());
            $field_definition->save();
            
            ilUtil::sendSuccess($this->lng->txt('save_settings'), true);
            $this->ctrl->redirect($this, "editFields");
        }
        
        $form->setValuesByPost();
        $this->createField($form);
    }
        
    /**
     * init field form
     *
     * @access protected
     */
    protected function initFieldForm(ilAdvancedMDFieldDefinition $a_definition)
    {
        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");

        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        
        $type = new ilNonEditableValueGUI($this->lng->txt("type"));
        $type->setValue($this->lng->txt($a_definition->getTypeTitle()));
        $form->addItem($type);
        
        $a_definition->addToFieldDefinitionForm($form, $this->getPermissions());
    
        if (!$a_definition->getFieldId()) {
            $form->setTitle($this->lng->txt('md_adv_create_field'));
            $form->addCommandButton('saveField', $this->lng->txt('create'));
        } else {
            $form->setTitle($this->lng->txt('md_adv_edit_field'));
            $form->addCommandButton('updateField', $this->lng->txt('save'));
        }
        
        $form->addCommandButton('editFields', $this->lng->txt('cancel'));
        
        return $form;
    }
    
    /**
     * Init Form
     *
     * @access protected
     */
    protected function initForm($a_mode)
    {
        if (is_object($this->form)) {
            return true;
        }
        
        $perm = $this->getPermissions()->hasPermissions(
            ilAdvancedMDPermissionHelper::CONTEXT_RECORD,
            $this->record->getRecordId(),
            array(
                array(ilAdvancedMDPermissionHelper::ACTION_RECORD_EDIT_PROPERTY,
                    ilAdvancedMDPermissionHelper::SUBACTION_RECORD_TITLE)
                ,array(ilAdvancedMDPermissionHelper::ACTION_RECORD_EDIT_PROPERTY,
                    ilAdvancedMDPermissionHelper::SUBACTION_RECORD_DESCRIPTION)
                ,array(ilAdvancedMDPermissionHelper::ACTION_RECORD_EDIT_PROPERTY,
                    ilAdvancedMDPermissionHelper::SUBACTION_RECORD_OBJECT_TYPES)
                ,ilAdvancedMDPermissionHelper::ACTION_RECORD_TOGGLE_ACTIVATION
        )
        );
        
        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");

        $this->form = new ilPropertyFormGUI();
        $this->form->setFormAction($this->ctrl->getFormAction($this));
        
        
        // title
        $title = new ilTextInputGUI($this->lng->txt('title'), 'title');
        $title->setValue($this->record->getTitle());
        $title->setSize(20);
        $title->setMaxLength(70);
        $title->setRequired(true);
        $this->form->addItem($title);
        
        if (!$perm[ilAdvancedMDPermissionHelper::ACTION_RECORD_EDIT_PROPERTY][ilAdvancedMDPermissionHelper::SUBACTION_RECORD_TITLE]) {
            $title->setDisabled(true);
        }
        
        // desc
        $desc = new ilTextAreaInputGUI($this->lng->txt('description'), 'desc');
        $desc->setValue($this->record->getDescription());
        $desc->setRows(3);
        $desc->setCols(50);
        $this->form->addItem($desc);
        
        if (!$perm[ilAdvancedMDPermissionHelper::ACTION_RECORD_EDIT_PROPERTY][ilAdvancedMDPermissionHelper::SUBACTION_RECORD_DESCRIPTION]) {
            $desc->setDisabled(true);
        }
        
        // active
        $check = new ilCheckboxInputGUI($this->lng->txt('md_adv_active'), 'active');
        $check->setChecked($this->record->isActive());
        $check->setValue(1);
        $this->form->addItem($check);
        
        if (!$perm[ilAdvancedMDPermissionHelper::ACTION_RECORD_TOGGLE_ACTIVATION]) {
            $check->setDisabled(true);
        }

        if (!$this->obj_type) {
            // scope
            $scope = new ilCheckboxInputGUI($this->lng->txt('md_adv_scope'), 'scope');
            $scope->setChecked($this->record->enabledScope());
            $scope->setValue(1);
            $this->form->addItem($scope);

            $subitems = new ilRepositorySelector2InputGUI($this->lng->txt("objects"), "scope_containers", true);
            $subitems->setValue($this->record->getScopeRefIds());
            $exp = $subitems->getExplorerGUI();
            
            $definition = $GLOBALS['DIC']['objDefinition'];
            $white_list = [];
            foreach ($definition->getAllRepositoryTypes() as $type) {
                if ($definition->isContainer($type)) {
                    $white_list[] = $type;
                }
            }
            
            
            $exp->setTypeWhiteList($white_list);
            $exp->setSkipRootNode(false);
            $exp->setRootId(ROOT_FOLDER_ID);
            $scope->addSubItem($subitems);
        }
        
        if (!$this->obj_type) {
            $section = new ilFormSectionHeaderGUI();
            $section->setTitle($this->lng->txt('md_obj_types'));
            $this->form->addItem($section);
        
            // see ilAdvancedMDRecordTableGUI::fillRow()
            $options = array(
                0 => $this->lng->txt("meta_obj_type_inactive"),
                1 => $this->lng->txt("meta_obj_type_mandatory"),
                2 => $this->lng->txt("meta_obj_type_optional")
            );
            

            foreach (ilAdvancedMDRecord::_getAssignableObjectTypes(true) as $type) {
                $t = $type["obj_type"] . ":" . $type["sub_type"];
                $this->lng->loadLanguageModule($type["obj_type"]);
                
                $type_options = $options;
                switch ($type["obj_type"]) {
                    case "orgu":
                        // currently only optional records for org unit (types)
                        unset($type_options[1]);
                        break;
                    case "prg":
                        // currently only optional records for study programme (types)
                        unset($type_options[1]);
                        break;
                    case "rcrs":
                        // optional makes no sense for ecs-courses
                        unset($type_options[2]);
                        break;
                }
                
                $value = 0;
                if ($a_mode == "edit") {
                    foreach ($this->record->getAssignedObjectTypes() as $item) {
                        if ($item["obj_type"] == $type["obj_type"] &&
                            $item["sub_type"] == $type["sub_type"]) {
                            $value = $item["optional"]
                                ? 2
                                : 1;
                        }
                    }
                }
                    
                $check = new ilSelectInputGUI($type["text"], 'obj_types[' . $t . ']');
                $check->setOptions($type_options);
                $check->setValue($value);
                $this->form->addItem($check);

                if (!$perm[ilAdvancedMDPermissionHelper::ACTION_RECORD_EDIT_PROPERTY][ilAdvancedMDPermissionHelper::SUBACTION_RECORD_OBJECT_TYPES]) {
                    $check->setDisabled(true);
                }
            }
        }
        
        switch ($a_mode) {
            case 'create':
                $this->form->setTitle($this->lng->txt('md_adv_create_record'));
                $this->form->addCommandButton('saveRecord', $this->lng->txt('add'));
                $this->form->addCommandButton('showRecords', $this->lng->txt('cancel'));
        
                return true;
            
            case 'edit':
                $this->form->setTitle($this->lng->txt('md_adv_edit_record'));
                $this->form->addCommandButton('updateRecord', $this->lng->txt('save'));
                $this->form->addCommandButton('showRecords', $this->lng->txt('cancel'));
                
                return true;
        }
    }
    
    protected function getSubstitutionFieldPermissions($a_obj_type, $a_field_id)
    {
        if ($a_obj_type == "crs") {
            $perm =	$this->getPermissions()->hasPermissions(
                ilAdvancedMDPermissionHelper::CONTEXT_SUBSTITUTION_COURSE,
                $a_field_id,
                array(
                    ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_COURSE_SHOW_FIELD
                    ,array(ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_COURSE_EDIT_FIELD_PROPERTY,
                        ilAdvancedMDPermissionHelper::SUBACTION_SUBSTITUTION_BOLD)
                    ,array(ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_COURSE_EDIT_FIELD_PROPERTY,
                        ilAdvancedMDPermissionHelper::SUBACTION_SUBSTITUTION_NEWLINE)
            )
            );
            return array(
                "show" => $perm[ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_COURSE_SHOW_FIELD]
                ,"bold" => $perm[ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_COURSE_EDIT_FIELD_PROPERTY][ilAdvancedMDPermissionHelper::SUBACTION_SUBSTITUTION_BOLD]
                ,"newline" => $perm[ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_COURSE_EDIT_FIELD_PROPERTY][ilAdvancedMDPermissionHelper::SUBACTION_SUBSTITUTION_NEWLINE]
            );
        } elseif ($a_obj_type == "cat") {
            $perm =	$this->getPermissions()->hasPermissions(
                ilAdvancedMDPermissionHelper::CONTEXT_SUBSTITUTION_CATEGORY,
                $a_field_id,
                array(
                    ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_CATEGORY_SHOW_FIELD
                    ,array(ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_CATEGORY_EDIT_FIELD_PROPERTY,
                        ilAdvancedMDPermissionHelper::SUBACTION_SUBSTITUTION_BOLD)
                    ,array(ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_CATEGORY_EDIT_FIELD_PROPERTY,
                        ilAdvancedMDPermissionHelper::SUBACTION_SUBSTITUTION_NEWLINE)
            )
            );
            return array(
                "show" => $perm[ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_CATEGORY_SHOW_FIELD]
                ,"bold" => $perm[ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_CATEGORY_EDIT_FIELD_PROPERTY][ilAdvancedMDPermissionHelper::SUBACTION_SUBSTITUTION_BOLD]
                ,"newline" => $perm[ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_CATEGORY_EDIT_FIELD_PROPERTY][ilAdvancedMDPermissionHelper::SUBACTION_SUBSTITUTION_NEWLINE]
            );
        } elseif ($a_obj_type == "sess") {
            $perm =	$this->getPermissions()->hasPermissions(
                ilAdvancedMDPermissionHelper::CONTEXT_SUBSTITUTION_SESSION,
                $a_field_id,
                array(
                    ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_SESSION_SHOW_FIELD
                ,array(ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_SESSION_EDIT_FIELD_PROPERTY,
                    ilAdvancedMDPermissionHelper::SUBACTION_SUBSTITUTION_BOLD)
                ,array(ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_SESSION_EDIT_FIELD_PROPERTY,
                    ilAdvancedMDPermissionHelper::SUBACTION_SUBSTITUTION_NEWLINE)
                )
            );
            return array(
                "show" => $perm[ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_SESSION_SHOW_FIELD]
            ,"bold" => $perm[ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_SESSION_EDIT_FIELD_PROPERTY][ilAdvancedMDPermissionHelper::SUBACTION_SUBSTITUTION_BOLD]
            ,"newline" => $perm[ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_SESSION_EDIT_FIELD_PROPERTY][ilAdvancedMDPermissionHelper::SUBACTION_SUBSTITUTION_NEWLINE]
            );
        } elseif ($a_obj_type == "grp") {
            $perm =	$this->getPermissions()->hasPermissions(
                ilAdvancedMDPermissionHelper::CONTEXT_SUBSTITUTION_GROUP,
                $a_field_id,
                array(
                    ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_GROUP_SHOW_FIELD
                ,array(ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_GROUP_EDIT_FIELD_PROPERTY,
                    ilAdvancedMDPermissionHelper::SUBACTION_SUBSTITUTION_BOLD)
                ,array(ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_GROUP_EDIT_FIELD_PROPERTY,
                    ilAdvancedMDPermissionHelper::SUBACTION_SUBSTITUTION_NEWLINE)
                )
            );
            return array(
                "show" => $perm[ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_GROUP_SHOW_FIELD]
            ,"bold" => $perm[ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_GROUP_EDIT_FIELD_PROPERTY][ilAdvancedMDPermissionHelper::SUBACTION_SUBSTITUTION_BOLD]
            ,"newline" => $perm[ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_GROUP_EDIT_FIELD_PROPERTY][ilAdvancedMDPermissionHelper::SUBACTION_SUBSTITUTION_NEWLINE]
            );
        } elseif ($a_obj_type == "iass") {
            $perm =	$this->getPermissions()->hasPermissions(
                ilAdvancedMDPermissionHelper::CONTEXT_SUBSTITUTION_IASS,
                $a_field_id,
                array(
                    ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_IASS_SHOW_FIELD
                ,array(ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_IASS_EDIT_FIELD_PROPERTY,
                    ilAdvancedMDPermissionHelper::SUBACTION_SUBSTITUTION_BOLD)
                ,array(ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_IASS_EDIT_FIELD_PROPERTY,
                    ilAdvancedMDPermissionHelper::SUBACTION_SUBSTITUTION_NEWLINE)
                )
            );
            return array(
                "show" => $perm[ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_IASS_SHOW_FIELD]
            ,"bold" => $perm[ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_IASS_EDIT_FIELD_PROPERTY][ilAdvancedMDPermissionHelper::SUBACTION_SUBSTITUTION_BOLD]
            ,"newline" => $perm[ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_IASS_EDIT_FIELD_PROPERTY][ilAdvancedMDPermissionHelper::SUBACTION_SUBSTITUTION_NEWLINE]
            );
        }
    }
    
    /**
     * init form table 'substitutions'
     *
     * @access protected
     */
    protected function initFormSubstitutions()
    {
        global $DIC;

        $ilAccess = $DIC['ilAccess'];
        
        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
        
        if (!$visible_records = ilAdvancedMDRecord::_getAllRecordsByObjectType()) {
            return;
        }

        $this->form = new ilPropertyFormGUI();
        $this->form->setFormAction($this->ctrl->getFormAction($this));
        #$this->form->setTableWidth('100%');

        // substitution
        foreach ($visible_records as $obj_type => $records) {
            $perm = null;
            // :TODO: hardwird ?
            if (in_array($obj_type, ['crs','cat','sess','grp','iass'])) {
                $perm =	$this->getPermissions()->hasPermissions(
                    ilAdvancedMDPermissionHelper::CONTEXT_SUBSTITUTION,
                    $obj_type,
                    array(
                        ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_SHOW_DESCRIPTION
                        ,ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_SHOW_FIELDNAMES
                        ,ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_FIELD_POSITIONS
                )
                );
            }
            
            include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDSubstitution.php');
            $sub = ilAdvancedMDSubstitution::_getInstanceByObjectType($obj_type);
            
            // Show section
            $section = new ilFormSectionHeaderGUI();
            $section->setTitle($this->lng->txt('objs_' . $obj_type));
            $this->form->addItem($section);
            
            $check = new ilCheckboxInputGUI($this->lng->txt('description'), 'enabled_desc_' . $obj_type);
            $check->setValue(1);
            $check->setOptionTitle($this->lng->txt('md_adv_desc_show'));
            $check->setChecked($sub->isDescriptionEnabled() ? true : false);
            $this->form->addItem($check);
            
            if ($perm && !$perm[ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_SHOW_DESCRIPTION]) {
                $check->setDisabled(true);
            }
            
            $check = new ilCheckboxInputGUI($this->lng->txt('md_adv_field_names'), 'enabled_field_names_' . $obj_type);
            $check->setValue(1);
            $check->setOptionTitle($this->lng->txt('md_adv_fields_show'));
            $check->setChecked($sub->enabledFieldNames() ? true : false);
            $this->form->addItem($check);
            
            if ($perm && !$perm[ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_SHOW_FIELDNAMES]) {
                $check->setDisabled(true);
            }
            
            #$area = new ilTextAreaInputGUI($this->lng->txt('md_adv_substitution'),'substitution_'.$obj_type);
            #$area->setUseRte(true);
            #$area->setRteTagSet('standard');
            #$area->setValue(ilUtil::prepareFormOutput($sub->getSubstitutionString()));
            #$area->setRows(5);
            #$area->setCols(80);
            #$this->form->addItem($area);
            
            if ($perm) {
                $perm_pos = $perm[ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_FIELD_POSITIONS];
            }

            $definitions = ilAdvancedMDFieldDefinition::getInstancesByObjType($obj_type);
            $definitions = $sub->sortDefinitions($definitions);
        
            $counter = 1;
            foreach ($definitions as $def) {
                $definition_id = $def->getFieldId();
                
                $perm = $this->getSubstitutionFieldPermissions($obj_type, $definition_id);
            
                $title = ilAdvancedMDRecord::_lookupTitle($def->getRecordId());
                $title = $def->getTitle() . ' (' . $title . ')';
                
                $check = new ilCheckboxInputGUI($title, 'show[' . $obj_type . '][' . $definition_id . ']');
                $check->setValue(1);
                $check->setOptionTitle($this->lng->txt('md_adv_show'));
                $check->setChecked($sub->isSubstituted($definition_id));
                
                if ($perm && !$perm["show"]) {
                    $check->setDisabled(true);
                }
                
                $pos = new ilNumberInputGUI($this->lng->txt('position'), 'position[' . $obj_type . '][' . $definition_id . ']');
                $pos->setSize(3);
                $pos->setMaxLength(4);
                $pos->allowDecimals(true);
                $pos->setValue(sprintf('%.1f', $counter++));
                $check->addSubItem($pos);
                
                if ($perm && !$perm_pos) {
                    $pos->setDisabled(true);
                }
                
                $bold = new ilCheckboxInputGUI($this->lng->txt('bold'), 'bold[' . $obj_type . '][' . $definition_id . ']');
                $bold->setValue(1);
                $bold->setChecked($sub->isBold($definition_id));
                $check->addSubItem($bold);
                
                if ($perm && !$perm["bold"]) {
                    $bold->setDisabled(true);
                }

                $bold = new ilCheckboxInputGUI($this->lng->txt('newline'), 'newline[' . $obj_type . '][' . $definition_id . ']');
                $bold->setValue(1);
                $bold->setChecked($sub->hasNewline($definition_id));
                $check->addSubItem($bold);
                
                if ($perm && !$perm["newline"]) {
                    $bold->setDisabled(true);
                }


                $this->form->addItem($check);
            }
            
            
            // placeholder
            /*
            $custom = new ilCustomInputGUI($this->lng->txt('md_adv_placeholders'));
            $tpl = new ilTemplate('tpl.placeholder_info.html',true,true,'Services/AdvancedMetaData');
            foreach($records as $record)
            {
                foreach(ilAdvancedMDFieldDefinition::_getDefinitionsByRecordId($record->getRecordId()) as $definition)
                {
                    $tpl->setCurrentBlock('field');
                    $tpl->setVariable('FIELD_NAME',$definition->getTitle());
                    $tpl->setVariable('MODULE_VARS','[IF_F_'.$definition->getFieldId().']...[F_'.$definition->getFieldId().']'.
                        '[/IF_F_'.$definition->getFieldId().']');
                    $tpl->parseCurrentBlock();
                }

                $tpl->setCurrentBlock('record');
                $tpl->setVariable('PLACEHOLDER_FOR',$this->lng->txt('md_adv_placeholder_for'));
                $tpl->setVariable('TITLE',$record->getTitle());
                $tpl->parseCurrentBlock();
            }
            $custom->setHTML($tpl->get());
            $this->form->addItem($custom);
            */
        }
        $this->form->setTitle($this->lng->txt('md_adv_substitution_table'));
        
        if ($ilAccess->checkAccess('write', '', $_REQUEST["ref_id"])) {
            $this->form->addCommandButton('updateSubstitutions', $this->lng->txt('save'));
        }
        
        return true;
    }
    
    /**
     * load record form data
     *
     * @access protected
     */
    protected function loadRecordFormData()
    {
        $perm = $this->getPermissions()->hasPermissions(
            ilAdvancedMDPermissionHelper::CONTEXT_RECORD,
            $this->record->getRecordId(),
            array(
                array(ilAdvancedMDPermissionHelper::ACTION_RECORD_EDIT_PROPERTY,
                    ilAdvancedMDPermissionHelper::SUBACTION_RECORD_TITLE)
                ,array(ilAdvancedMDPermissionHelper::ACTION_RECORD_EDIT_PROPERTY,
                    ilAdvancedMDPermissionHelper::SUBACTION_RECORD_DESCRIPTION)
                ,array(ilAdvancedMDPermissionHelper::ACTION_RECORD_EDIT_PROPERTY,
                    ilAdvancedMDPermissionHelper::SUBACTION_RECORD_OBJECT_TYPES)
                ,ilAdvancedMDPermissionHelper::ACTION_RECORD_TOGGLE_ACTIVATION
            )
        );
        
        if ($perm[ilAdvancedMDPermissionHelper::ACTION_RECORD_TOGGLE_ACTIVATION]) {
            $this->record->setActive(ilUtil::stripSlashes($_POST['active']));
        }
        if ($perm[ilAdvancedMDPermissionHelper::ACTION_RECORD_EDIT_PROPERTY][ilAdvancedMDPermissionHelper::SUBACTION_RECORD_TITLE]) {
            $this->record->setTitle(ilUtil::stripSlashes($_POST['title']));
        }
        if ($perm[ilAdvancedMDPermissionHelper::ACTION_RECORD_EDIT_PROPERTY][ilAdvancedMDPermissionHelper::SUBACTION_RECORD_DESCRIPTION]) {
            $this->record->setDescription(ilUtil::stripSlashes($_POST['desc']));
        }
        
        if (!$this->obj_type) {
            if ($perm[ilAdvancedMDPermissionHelper::ACTION_RECORD_EDIT_PROPERTY][ilAdvancedMDPermissionHelper::SUBACTION_RECORD_OBJECT_TYPES]) {
                $obj_types = array();
                if (is_array($_POST['obj_types'])) {
                    foreach ($_POST['obj_types'] as $t => $value) {
                        if ($value) {
                            $t = explode(":", $t);
                            $obj_types[] = array(
                                "obj_type" => ilUtil::stripSlashes($t[0]),
                                "sub_type" => ilUtil::stripSlashes($t[1]),
                                "optional" => ($value > 1)
                                );
                        }
                    }
                }
                $this->record->setAssignedObjectTypes($obj_types);
            }
        }
        
        $scopes = [];
        foreach ((array) $_POST['scope_containers_sel'] as $ref_id) {
            $scope = new ilAdvancedMDRecordScope();
            $scope->setRefId($ref_id);
            $scopes[] = $scope;
        }
        $this->record->setScopes($_POST['scope'] ? $scopes : []);
        $this->record->enableScope($_POST['scope'] ? true : false);
    }
    
    /**
     * Init record object
     *
     * @access protected
     */
    protected function initRecordObject()
    {
        if (!is_object($this->record)) {
            $record_id = isset($_GET['record_id'])
                ? $_GET['record_id']
                : 0;
            $this->record = ilAdvancedMDRecord::_getInstanceByRecordId($record_id);
            
            // bind to parent object (aka local adv md)
            if (!$record_id &&
                $this->obj_id) {
                $this->record->setParentObject($this->obj_id);
            }
        }
                        
        return $this->record;
    }

    /**
     * Set sub tabs
     *
     * @access protected
     */
    protected function setSubTabs()
    {
        $this->tabs_gui->clearSubTabs();

        $this->tabs_gui->addSubTabTarget(
            "md_adv_record_list",
            $this->ctrl->getLinkTarget($this, "showRecords"),
            '',
            '',
            '',
            true
        );
                        
        
        if (ilAdvancedMDRecord::_getAllRecordsByObjectType()) {
            $this->tabs_gui->addSubTabTarget(
                "md_adv_presentation",
                $this->ctrl->getLinkTarget($this, "showPresentation")
            );
        }
            
        $this->tabs_gui->addSubTabTarget(
            "md_adv_file_list",
            $this->ctrl->getLinkTarget($this, "showFiles"),
            "showFiles"
        );
    }
    
    /**
     * Get and cache record objects
     *
     * @access protected
     */
    protected function getParsedRecordObjects()
    {
        $res = array();
        
        if ($this->mode == self::MODE_OBJECT) {
            $selected = ilAdvancedMDRecord::getObjRecSelection($this->obj_id, $this->sub_type);
        }

        $records = ilAdvancedMDRecord::_getRecords();
        $orderings = new ilAdvancedMDRecordObjectOrderings();
        $records = $orderings->sortRecords($records, $this->obj_id);

        $position = 0;
        foreach ($records as $record) {
            $parent_id = $record->getParentObject();
            
            if ($this->mode == self::MODE_ADMINISTRATION) {
                if ($parent_id) {
                    continue;
                }
            } else {
                // does not match current object
                if ($parent_id && $parent_id != $this->obj_id) {
                    continue;
                }
                
                // inactive records only in administration
                if (!$parent_id && !$record->isActive()) {
                    continue;
                }
                // scope needs to match in object context
                if (
                    ilAdvancedMDRecord::isFilteredByScope(
                        $this->ref_id,
                        $record->getScopes()
                    )
                ) {
                    continue;
                }
            }
            
            $tmp_arr = array();
            $tmp_arr['id'] = $record->getRecordId();
            $tmp_arr['active'] = $record->isActive();
            $tmp_arr['title'] = $record->getTitle();
            $tmp_arr['description'] = $record->getDescription();
            $tmp_arr['fields'] = array();
            $tmp_arr['obj_types'] = $record->getAssignedObjectTypes();
            $position += 10;
            $tmp_arr['position'] = $position;

            $tmp_arr['perm'] = $this->permissions->hasPermissions(
                ilAdvancedMDPermissionHelper::CONTEXT_RECORD,
                $record->getRecordId(),
                array(
                    ilAdvancedMDPermissionHelper::ACTION_RECORD_EDIT
                    ,ilAdvancedMDPermissionHelper::ACTION_RECORD_EDIT_FIELDS
                    ,ilAdvancedMDPermissionHelper::ACTION_RECORD_TOGGLE_ACTIVATION
                    ,array(ilAdvancedMDPermissionHelper::ACTION_RECORD_EDIT_PROPERTY,
                        ilAdvancedMDPermissionHelper::SUBACTION_RECORD_OBJECT_TYPES)
                )
            );

            if ($this->obj_type) {
                $tmp_arr["readonly"] = !(bool) $parent_id;
                $tmp_arr["local"] = $parent_id;

                // local records are never optional
                $assigned = $optional = false;
                foreach ($tmp_arr['obj_types'] as $idx => $item) {
                    if ($item["obj_type"] == $this->obj_type &&
                        $item["sub_type"] == $this->sub_type) {
                        $assigned = true;
                        $optional = $item["optional"];
                        $tmp_arr['obj_types'][$idx]['context'] = true;
                        break;
                    }
                }
                if (!$assigned) {
                    continue;
                }
                $tmp_arr['optional'] = $optional;
                if ($optional) {
                    // in object context "active" means selected record
                    $tmp_arr['active'] = in_array($record->getRecordId(), $selected);
                }
            }

            $res[] = $tmp_arr;
        }
        
        return $res;
    }
}
