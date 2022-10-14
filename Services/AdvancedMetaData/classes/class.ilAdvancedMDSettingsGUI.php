<?php

declare(strict_types=1);

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

use ILIAS\UI\Factory as UIFactory;
use ILIAS\Refinery\Factory as RefineryFactory;
use ILIAS\UI\Renderer;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\HTTP\GlobalHttpState;

/**
 * @author       Stefan Meyer <meyer@leifos.com>
 * @ilCtrl_Calls ilAdvancedMDSettingsGUI: ilPropertyFormGUI
 * @ingroup      ServicesAdvancedMetaData
 */
class ilAdvancedMDSettingsGUI
{
    public const CONTEXT_ADMINISTRATION = 1;
    public const CONTEXT_OBJECT = 2;

    protected const TAB_RECORD_SETTINGS = 'editRecord';
    protected const TAB_TRANSLATION = 'translations';

    /**
     * Active settings mode
     * @var null|int
     */
    private $context = null;
    protected ?ilPropertyFormGUI $import_form = null;
    protected ?ilPropertyFormGUI $form = null;

    protected ilLanguage $lng;
    protected ilGlobalTemplateInterface $tpl;
    protected ilCtrl $ctrl;
    protected RequestInterface $request;
    protected GlobalHttpState $http;
    protected RefineryFactory $refinery;

    protected ilTabsGUI $tabs_gui;
    protected UIFactory $ui_factory;
    protected Renderer $ui_renderer;
    protected ilToolbarGUI $toolbar;
    protected ilLogger $logger;

    protected ilAdvancedMDPermissionHelper $permissions;
    protected ?ilAdvancedMDRecord $record = null;

    private string $active_language = '';
    protected int $ref_id;
    protected ?int $obj_id;
    protected ?string $obj_type = null;
    /**
     * @var string|string[]|null
     */
    protected $sub_type = null;

    /**
     * Constructor
     * @access public
     */
    public function __construct(int $a_context, int $a_ref_id, ?string $a_obj_type = null, $a_sub_type = null)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule('meta');
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->tabs_gui = $DIC->tabs();

        $this->refinery = $DIC->refinery();
        $this->toolbar = $DIC->toolbar();
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();
        $this->request = $DIC->http()->request();
        $this->http = $DIC->http();

        /** @noinspection PhpUndefinedMethodInspection */
        $this->logger = $DIC->logger()->amet();

        $this->context = $a_context;
        $this->initContextParameters(
            $this->context,
            $a_ref_id,
            $a_obj_type,
            $a_sub_type
        );

        /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
        $this->permissions = ilAdvancedMDPermissionHelper::getInstance($DIC->user()->getId(), $this->ref_id);
    }

    protected function getRecordIdFromQuery(): ?int
    {
        if ($this->http->wrapper()->query()->has('record_id')) {
            return $this->http->wrapper()->query()->retrieve(
                'record_id',
                $this->refinery->kindlyTo()->int()
            );
        }
        return null;
    }

    protected function getRecordIdsFromPost(): SplFixedArray
    {
        if ($this->http->wrapper()->post()->has('record_id')) {
            return SplFixedArray::fromArray(
                $this->http->wrapper()->post()->retrieve(
                    'record_id',
                    $this->refinery->kindlyTo()->listOf(
                        $this->refinery->kindlyTo()->int()
                    )
                )
            );
        }
        return new SplFixedArray(0);
    }

    protected function getFieldIdFromQuery(): ?int
    {
        if ($this->http->wrapper()->query()->has('field_id')) {
            return $this->http->wrapper()->query()->retrieve(
                'field_id',
                $this->refinery->kindlyTo()->int()
            );
        }
        return null;
    }

    protected function getFieldIdsFromPost(): SplFixedArray
    {
        if ($this->http->wrapper()->post()->has('field_id')) {
            return SplFixedArray::fromArray(
                $this->http->wrapper()->post()->retrieve(
                    'field_id',
                    $this->refinery->kindlyTo()->listOf(
                        $this->refinery->kindlyTo()->int()
                    )
                )
            );
        }
        return new SplFixedArray(0);
    }

    protected function getFileIdsFromPost(): SplFixedArray
    {
        if ($this->http->wrapper()->post()->has('file_id')) {
            return SplFixedArray::fromArray(
                $this->http->wrapper()->post()->retrieve(
                    'file_id',
                    $this->refinery->kindlyTo()->dictOf(
                        $this->refinery->kindlyTo()->int()
                    )
                )
            );
        }
        return new SplFixedArray(0);
    }

    protected function getFieldTypeFromQuery(): ?int
    {
        if ($this->http->wrapper()->query()->has('ftype')) {
            return $this->http->wrapper()->query()->retrieve(
                'ftype',
                $this->refinery->kindlyTo()->int()
            );
        }
        return null;
    }

    protected function getFieldTypeFromPost(): ?int
    {
        if ($this->http->wrapper()->post()->has('ftype')) {
            return $this->http->wrapper()->post()->retrieve(
                'ftype',
                $this->refinery->kindlyTo()->int()
            );
        }
        return null;
    }

    protected function getOidFromQuery(): ?int
    {
        if ($this->http->wrapper()->query()->has('oid')) {
            return $this->http->wrapper()->query()->retrieve(
                'oid',
                $this->refinery->kindlyTo()->int()
            );
        }
        return null;
    }

    /**
     * @return array<string, float>
     */
    protected function getPositionsFromPost(): array
    {
        if ($this->http->wrapper()->post()->has('position')) {
            return $this->http->wrapper()->post()->retrieve(
                'position',
                $this->refinery->kindlyTo()->dictOf(
                    $this->refinery->kindlyTo()->float()
                )
            );
        }
        return [];
    }

    /**
     * @return ilAdvancedMDPermissionHelper
     */
    protected function getPermissions(): ilAdvancedMDPermissionHelper
    {
        return $this->permissions;
    }

    /**
     * @param string|string[]|null $sub_type
     */
    protected function initContextParameters(
        int $context,
        int $ref_id,
        ?string $obj_type,
        $sub_type
    ): void {
        if ($context === self::CONTEXT_ADMINISTRATION) {
            $this->ref_id = $ref_id;
            $this->obj_id = null;
            $this->obj_type = null;
            $this->sub_type = null;
        } else {
            $this->ref_id = $ref_id;
            $this->obj_id = ilObject::_lookupObjId($ref_id);
            $this->obj_type = $obj_type;
            $this->sub_type = $sub_type;
        }
    }

    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();
        switch ($next_class) {

            case strtolower(ilAdvancedMDRecordTranslationGUI::class):
                $record = $this->initRecordObject();
                $this->setRecordSubTabs(1, true);
                $int_gui = new \ilAdvancedMDRecordTranslationGUI($record);
                $this->ctrl->forwardCommand($int_gui);
                break;

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

    public function showRecords(): void
    {
        global $DIC;

        $ilToolbar = $DIC['ilToolbar'];
        $ilAccess = $DIC['ilAccess'];

        $this->setSubTabs($this->context);

        $perm = $this->getPermissions()->hasPermissions(
            ilAdvancedMDPermissionHelper::CONTEXT_MD,
            (string) $this->ref_id,
            array(
                ilAdvancedMDPermissionHelper::ACTION_MD_CREATE_RECORD,
                ilAdvancedMDPermissionHelper::ACTION_MD_IMPORT_RECORDS
            )
        );

        if ($perm[ilAdvancedMDPermissionHelper::ACTION_MD_CREATE_RECORD]) {
            $button = ilLinkButton::getInstance();
            $button->setCaption("add");
            $button->setUrl($this->ctrl->getLinkTarget($this, "createRecord"));
            $ilToolbar->addButtonInstance($button);

            if ($perm[ilAdvancedMDPermissionHelper::ACTION_MD_IMPORT_RECORDS]) {
                $ilToolbar->addSeparator();
            }
        }

        if ($perm[ilAdvancedMDPermissionHelper::ACTION_MD_IMPORT_RECORDS]) {
            $button = ilLinkButton::getInstance();
            $button->setCaption("import");
            $button->setUrl($this->ctrl->getLinkTarget($this, "importRecords"));
            $ilToolbar->addButtonInstance($button);
        }

        $obj_type_context = ($this->obj_id > 0)
            ? ilObject::_lookupType($this->obj_id)
            : "";
        $table_gui = new ilAdvancedMDRecordTableGUI(
            $this,
            "showRecords",
            $this->getPermissions(),
            $obj_type_context
        );
        $table_gui->setTitle($this->lng->txt("md_record_list_table"));
        $table_gui->setData($this->getParsedRecordObjects());

        // permissions?
        //$table_gui->addCommandButton('createRecord',$this->lng->txt('add'));
        $table_gui->addMultiCommand("exportRecords", $this->lng->txt('export'));
        $table_gui->setSelectAllCheckbox("record_id");

        if ($ilAccess->checkAccess('write', '', $this->ref_id)) {
            $table_gui->addMultiCommand("confirmDeleteRecords", $this->lng->txt("delete"));
            $table_gui->addCommandButton("updateRecords", $this->lng->txt("save"));
        }

        $DIC->ui()->mainTemplate()->setContent($table_gui->getHTML());
    }

    protected function showPresentation(): void
    {
        $this->setSubTabs($this->context);
        $form = $this->initFormSubstitutions();
        if ($form instanceof ilPropertyFormGUI) {
            $this->tabs_gui->setSubTabActive('md_adv_presentation');
            $this->tpl->setContent($this->form->getHTML());
            return;
        }
        $this->showRecords();
    }

    /**
     * Update substitution
     * @access public
     */
    public function updateSubstitutions(): void
    {
        global $DIC;

        $ilAccess = $DIC['ilAccess'];

        if (!$ilAccess->checkAccess('write', '', $this->ref_id)) {
            $this->ctrl->redirect($this, "showPresentation");
        }

        $form = $this->initFormSubstitutions();
        if (!$form instanceof ilPropertyFormGUI) {
            $this->ctrl->redirect($this, 'showPresentation');
            return;
        }
        if (!$form->checkInput()) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('err_check_input'), true);
            $this->ctrl->redirect($this, "showPresentation");
        }

        foreach (ilAdvancedMDRecord::_getActivatedObjTypes() as $obj_type) {
            $perm = null;

            if (in_array($obj_type, $this->permissions->getAllowedObjectTypes())) {
                $perm = $this->getPermissions()->hasPermissions(
                    ilAdvancedMDPermissionHelper::CONTEXT_SUBSTITUTION,
                    "0",
                    array(
                        ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_SHOW_DESCRIPTION
                        ,
                        ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_SHOW_FIELDNAMES
                        ,
                        ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_FIELD_POSITIONS
                    )
                );
            }
            $sub = ilAdvancedMDSubstitution::_getInstanceByObjectType($obj_type);
            if ($perm && $perm[ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_SHOW_DESCRIPTION]) {
                $sub->enableDescription((bool) $form->getInput('enabled_desc_' . $obj_type));
            }

            if ($perm && $perm[ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_SHOW_FIELDNAMES]) {
                $sub->enableFieldNames((bool) $form->getInput('enabled_field_names_' . $obj_type));
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

            $sub->resetSubstitutions();

            $new_sub = [];
            foreach ($definitions as $def) {
                $field_id = $def->getFieldId();
                $old = $old_sub[$field_id];

                $perm_def = $this->getSubstitutionFieldPermissions($obj_type, $field_id);
                if ($perm_def["show"] ?? false) {
                    $active = (bool) $form->getInput('show_' . $obj_type . '_' . $field_id);
                } else {
                    $active = $old["active"] ?? false;
                }
                if ($active) {
                    $new_sub[$field_id] = $old;
                    if ($perm[ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_FIELD_POSITIONS]) {
                        $new_sub[$field_id]['pos'] = (int) $form->getInput('position_' . $obj_type . '_' . $field_id);
                    }
                    if ($perm_def["bold"] ?? false) {
                        $new_sub[$field_id]['bold'] = (bool) $form->getInput('bold_' . $obj_type . '_' . $field_id);
                    }
                    if ($perm_def["newline"] ?? false) {
                        $new_sub[$field_id]['newline'] = (bool) $form->getInput('newline_' . $obj_type . '_' . $field_id);
                    }
                }
            }

            if (sizeof($new_sub)) {
                $new_sub = ilArrayUtil::sortArray($new_sub, "pos", "asc", true, true);
                foreach ($new_sub as $field_id => $field) {
                    $sub->appendSubstitution($field_id, (bool) $field["bold"], (bool) $field["newline"]);
                }
            }
            $sub->update();
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
        $this->ctrl->redirect($this, "showPresentation");
    }

    /**
     * Export records
     * @access public
     */
    public function exportRecords(): void
    {
        $record_ids = $this->getRecordIdsFromPost();
        if (!count($record_ids)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'));
            $this->showRecords();
            return;
        }

        // all records have to be exportable
        $fail = array();
        foreach ($record_ids as $record_id) {
            if (!$this->getPermissions()->hasPermission(
                ilAdvancedMDPermissionHelper::CONTEXT_RECORD,
                (string) $record_id,
                ilAdvancedMDPermissionHelper::ACTION_RECORD_EXPORT
            )) {
                $record = ilAdvancedMDRecord::_getInstanceByRecordId($record_id);
                $fail[] = $record->getTitle();
            }
        }
        if ($fail) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('msg_no_perm_copy') . " " . implode(", ", $fail), true);
            $this->ctrl->redirect($this, "showRecords");
        }

        $xml_writer = new ilAdvancedMDRecordXMLWriter((array) $record_ids);
        $xml_writer->write();

        $export_files = new ilAdvancedMDRecordExportFiles(
            $this->context === self::CONTEXT_ADMINISTRATION ? null : $this->obj_id
        );
        $export_files->create($xml_writer->xmlDumpMem());

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('md_adv_records_exported'));
        $this->showFiles();
    }

    /**
     * Show export files
     */
    protected function showFiles(): void
    {
        $this->setSubTabs($this->context);
        $this->tabs_gui->setSubTabActive('md_adv_file_list');

        $files = new ilAdvancedMDRecordExportFiles(
            $this->context === self::CONTEXT_ADMINISTRATION ? null : $this->obj_id
        );
        $file_data = $files->readFilesInfo();

        $table_gui = new ilAdvancedMDRecordExportFilesTableGUI($this, "showFiles");
        $table_gui->setTitle($this->lng->txt("md_record_export_table"));
        $table_gui->parseFiles($file_data);
        $table_gui->addMultiCommand("downloadFile", $this->lng->txt('download'));

        if ($GLOBALS['DIC']->access()->checkAccess('write', '', $this->ref_id)) {
            $table_gui->addMultiCommand("confirmDeleteFiles", $this->lng->txt("delete"));
        }
        $table_gui->setSelectAllCheckbox("file_id");

        $this->tpl->setContent($table_gui->getHTML());
    }

    /**
     * Download XML file
     * @access public
     * @param
     */
    public function downloadFile(): void
    {
        $file_ids = $this->getFileIdsFromPost();
        if (count($file_ids) !== 1) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('md_adv_select_one_file'));
            $this->showFiles();
            return;
        }
        $files = new ilAdvancedMDRecordExportFiles(
            $this->context === self::CONTEXT_ADMINISTRATION ? null : $this->obj_id
        );
        $abs_path = $files->getAbsolutePathByFileId($file_ids[0]);
        ilFileDelivery::deliverFileLegacy($abs_path, 'ilias_meta_data_record.xml', 'application/xml');
    }

    /**
     * confirm delete files
     * @access public
     */
    public function confirmDeleteFiles(): void
    {
        $file_ids = $this->getFileIdsFromPost();
        if (count($file_ids) !== 1) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'));
            $this->showFiles();
            return;
        }

        $c_gui = new ilConfirmationGUI();

        // set confirm/cancel commands
        $c_gui->setFormAction($this->ctrl->getFormAction($this, "deleteFiles"));
        $c_gui->setHeaderText($this->lng->txt("md_adv_delete_files_sure"));
        $c_gui->setCancel($this->lng->txt("cancel"), "showFiles");
        $c_gui->setConfirm($this->lng->txt("confirm"), "deleteFiles");

        $files = new ilAdvancedMDRecordExportFiles(
            $this->context === self::CONTEXT_ADMINISTRATION ? null : $this->obj_id
        );
        $file_data = $files->readFilesInfo();

        // add items to delete
        foreach ($file_ids as $file_id) {
            $info = $file_data[$file_id];
            $c_gui->addItem(
                "file_id[]",
                $file_id,
                is_array($info['name']) ? implode(',', $info['name']) : 'No Records'
            );
        }
        $this->tpl->setContent($c_gui->getHTML());
    }

    /**
     * Delete files
     * @access public
     * @param
     */
    public function deleteFiles(): void
    {
        $file_ids = $this->getFileIdsFromPost();
        if (count($file_ids) === 0) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'));
            $this->showFiles();
            return;
        }

        if (!$GLOBALS['DIC']->access()->checkAccess('write', '', $this->ref_id)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('permission_denied'), true);
            $GLOBALS['DIC']->ctrl()->redirect($this, 'showFiles');
        }

        $files = new ilAdvancedMDRecordExportFiles(
            $this->context === self::CONTEXT_ADMINISTRATION ? null : $this->obj_id
        );
        foreach ($file_ids as $file_id) {
            $files->deleteByFileId((int) $file_id);
        }
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('md_adv_deleted_files'));
        $this->showFiles();
    }

    /**
     * Confirm delete
     * @access public
     */
    public function confirmDeleteRecords(): void
    {
        $this->initRecordObject();
        $this->setRecordSubTabs();

        $record_ids = $this->getRecordIdsFromPost();
        if (!count($record_ids)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'));
            $this->showRecords();
            return;
        }

        $c_gui = new ilConfirmationGUI();

        // set confirm/cancel commands
        $c_gui->setFormAction($this->ctrl->getFormAction($this, "deleteRecords"));
        $c_gui->setHeaderText($this->lng->txt("md_adv_delete_record_sure"));
        $c_gui->setCancel($this->lng->txt("cancel"), "showRecords");
        $c_gui->setConfirm($this->lng->txt("confirm"), "deleteRecords");

        // add items to delete
        foreach ($record_ids as $record_id) {
            $record = ilAdvancedMDRecord::_getInstanceByRecordId($record_id);
            $c_gui->addItem("record_id[]", (string) $record_id, $record->getTitle() ?: 'No Title');
        }
        $this->tpl->setContent($c_gui->getHTML());
    }

    /**
     * Permanently delete records
     * @access public
     */
    public function deleteRecords(): void
    {
        $record_ids = $this->getRecordIdsFromPost();
        if (!count($record_ids)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'));
            $this->showRecords();
            return;
        }

        // all records have to be deletable
        $fail = array();
        foreach ($record_ids as $record_id) {
            // must not delete global records in local context
            if ($this->context == self::CONTEXT_OBJECT) {
                $record = ilAdvancedMDRecord::_getInstanceByRecordId($record_id);
                if (!$record->getParentObject()) {
                    $fail[] = $record->getTitle();
                }
            }

            if (!$this->getPermissions()->hasPermission(
                ilAdvancedMDPermissionHelper::CONTEXT_RECORD,
                (string) $record_id,
                ilAdvancedMDPermissionHelper::ACTION_RECORD_DELETE
            )) {
                $record = ilAdvancedMDRecord::_getInstanceByRecordId($record_id);
                $fail[] = $record->getTitle();
            }
        }
        if ($fail) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('msg_no_perm_delete') . " " . implode(", ", $fail), true);
            $this->ctrl->redirect($this, "showRecords");
        }

        foreach ($record_ids as $record_id) {
            $record = ilAdvancedMDRecord::_getInstanceByRecordId($record_id);
            $record->delete();
        }
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('md_adv_deleted_records'), true);
        $this->ctrl->redirect($this, "showRecords");
    }

    /**
     * Save records (assigned object typed)
     * @access public
     * @param
     */
    public function updateRecords(): void
    {
        // sort positions and renumber
        $positions = $this->getPositionsFromPost();
        asort($positions, SORT_NUMERIC);

        $sorted_positions = [];
        $i = 1;
        foreach ($positions as $record_id => $pos) {
            $sorted_positions[(int) $record_id] = $i++;
        }
        $selected_global = array();

        $post_active = (array) ($this->http->request()->getParsedBody()['active'] ?? []);
        if ($this->obj_id > 0) {
            ilAdvancedMDRecord::deleteObjRecSelection($this->obj_id);
        }
        foreach ($this->getParsedRecordObjects() as $item) {
            $perm = $this->getPermissions()->hasPermissions(
                ilAdvancedMDPermissionHelper::CONTEXT_RECORD,
                (string) $item['id'],
                array(
                    ilAdvancedMDPermissionHelper::ACTION_RECORD_TOGGLE_ACTIVATION
                    ,
                    array(ilAdvancedMDPermissionHelper::ACTION_RECORD_EDIT_PROPERTY,
                          ilAdvancedMDPermissionHelper::SUBACTION_RECORD_OBJECT_TYPES
                    )
                )
            );


            $record_obj = ilAdvancedMDRecord::_getInstanceByRecordId($item['id']);

            if ($perm[ilAdvancedMDPermissionHelper::ACTION_RECORD_EDIT_PROPERTY][ilAdvancedMDPermissionHelper::SUBACTION_RECORD_OBJECT_TYPES]) {
                $obj_types = array();
                $post_object_types = (array) ($this->http->request()->getParsedBody()['obj_types'] ?? []);
                if (is_array($post_object_types[$record_obj->getRecordId()])) {
                    foreach ($post_object_types[$record_obj->getRecordId()] as $type => $status) {
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

                // global records in global administration and local records in local administration
                if (!$item['readonly']) {
                    // table adv_md_record_objs
                    $record_obj->setAssignedObjectTypes($obj_types);
                } else {    // global records in local administration
                    foreach ($obj_types as $t) {
                        // table adv_md_obj_rec_select
                        ilAdvancedMDRecord::saveObjRecSelection($this->obj_id, $t["sub_type"], [$record_obj->getRecordId()], false);
                    }
                }
            }

            if ($this->context == self::CONTEXT_ADMINISTRATION) {
                if ($perm[ilAdvancedMDPermissionHelper::ACTION_RECORD_TOGGLE_ACTIVATION]) {
                    $record_obj->setActive(isset($post_active[$record_obj->getRecordId()]));
                }

                $record_obj->setGlobalPosition((int) $sorted_positions[$record_obj->getRecordId()]);
                $record_obj->update();
            } elseif ($perm[ilAdvancedMDPermissionHelper::ACTION_RECORD_TOGGLE_ACTIVATION]) {
                // global, optional record
                if ($item['readonly'] &&
                    $item['optional'] &&
                    ($post_active[$item['id']] ?? false)) {
                    $selected_global[] = $item['id'];
                } elseif ($item['local']) {
                    $record_obj = ilAdvancedMDRecord::_getInstanceByRecordId($item['id']);
                    $record_obj->setActive((bool) ($post_active[$item['id']] ?? false));
                    $record_obj->update();
                }
            }

            // save local sorting
            if ($this->context == self::CONTEXT_OBJECT) {
                global $DIC;

                $local_position = new \ilAdvancedMDRecordObjectOrdering($item['id'], $this->obj_id, $DIC->database());
                $local_position->setPosition((int) $sorted_positions[$item['id']]);
                $local_position->save();
            }
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
        $this->ctrl->redirect($this, "showRecords");
    }

    public function confirmDeleteFields(): void
    {
        $field_ids = $this->getFieldIdsFromPost();
        if (!count($field_ids)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'));
            $this->editFields();
            return;
        }

        $this->initRecordObject();
        $this->setRecordSubTabs(2);

        $c_gui = new ilConfirmationGUI();

        // set confirm/cancel commands
        $c_gui->setFormAction($this->ctrl->getFormAction($this, "deleteFields"));
        $c_gui->setHeaderText($this->lng->txt("md_adv_delete_fields_sure"));
        $c_gui->setCancel($this->lng->txt("cancel"), "editFields");
        $c_gui->setConfirm($this->lng->txt("confirm"), "deleteFields");

        // add items to delete
        foreach ($field_ids as $field_id) {
            $field = ilAdvancedMDFieldDefinition::getInstance($field_id);
            $c_gui->addItem("field_id[]", (string) $field_id, $field->getTitle() ?: 'No Title');
        }
        $this->tpl->setContent($c_gui->getHTML());
    }

    public function deleteFields(): void
    {
        $this->ctrl->saveParameter($this, 'record_id');

        $field_ids = $this->getFieldIdsFromPost();
        if (!count($field_ids)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'));
            $this->editFields();
            return;
        }

        // all fields have to be deletable
        $fail = array();
        foreach ($field_ids as $field_id) {
            if (!$this->getPermissions()->hasPermission(
                ilAdvancedMDPermissionHelper::CONTEXT_FIELD,
                (string) $field_id,
                ilAdvancedMDPermissionHelper::ACTION_FIELD_DELETE
            )) {
                $field = ilAdvancedMDFieldDefinition::getInstance($field_id);
                $fail[] = $field->getTitle();
            }
        }
        if ($fail) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('msg_no_perm_delete') . " " . implode(", ", $fail), true);
            $this->ctrl->redirect($this, "editFields");
        }

        foreach ($field_ids as $field_id) {
            $field = ilAdvancedMDFieldDefinition::getInstance($field_id);
            $field->delete();
        }
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('md_adv_deleted_fields'), true);
        $this->ctrl->redirect($this, "editFields");
    }

    public function editRecord(ilPropertyFormGUI $form = null): void
    {
        $record_id = $this->getRecordIdFromQuery();
        if (!$record_id) {
            $this->ctrl->redirect($this, 'showRecords');
        }
        $this->initRecordObject();
        $this->setRecordSubTabs(1, true);
        $this->tabs_gui->activateTab(self::TAB_RECORD_SETTINGS);

        if (!$form instanceof ilPropertyFormGUI) {
            $this->initLanguage($record_id);
            $this->showLanguageSwitch($record_id, 'editRecord');
            $this->initForm('edit');
        }
        $this->tpl->setContent($this->form->getHTML());
    }

    protected function editFields(): void
    {
        global $DIC;

        $record_id = $this->getRecordIdFromQuery();
        if (!$record_id) {
            $this->ctrl->redirect($this, 'showRecords');
        }
        $this->ctrl->saveParameter($this, 'record_id');
        $this->initRecordObject();
        $this->setRecordSubTabs();
        $this->initLanguage($record_id);
        $this->showLanguageSwitch($record_id, 'editFields');

        $perm = $this->getPermissions()->hasPermissions(
            ilAdvancedMDPermissionHelper::CONTEXT_RECORD,
            (string) $this->record->getRecordId(),
            array(
                ilAdvancedMDPermissionHelper::ACTION_RECORD_CREATE_FIELD
                ,
                ilAdvancedMDPermissionHelper::ACTION_RECORD_FIELD_POSITIONS
            )
        );

        $filter_warn = array();
        if ($perm[ilAdvancedMDPermissionHelper::ACTION_RECORD_CREATE_FIELD]) {
            // type selection
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

            if (count($this->toolbar->getItems())) {
                $this->toolbar->addSeparator();
            }
            $this->toolbar->addInputItem($types);

            $this->toolbar->setFormAction($this->ctrl->getFormAction($this, "createField"));

            $button = ilSubmitButton::getInstance();
            $button->setCaption("add");
            $button->setCommand("createField");
            $this->toolbar->addButtonInstance($button);
        }

        // #17092
        if (sizeof($filter_warn)) {
            $this->tpl->setOnScreenMessage('info', sprintf($this->lng->txt("md_adv_field_filter_warning"), implode(", ", $filter_warn)));
        }

        // show field table
        $fields = ilAdvancedMDFieldDefinition::getInstancesByRecordId(
            $this->record->getRecordId(),
            false,
            $this->active_language
        );

        $table_gui = new ilAdvancedMDFieldTableGUI(
            $this,
            'editFields',
            $this->getPermissions(),
            $perm[ilAdvancedMDPermissionHelper::ACTION_RECORD_FIELD_POSITIONS],
            $this->record->getDefaultLanguage()
        );
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
     * @access public
     */
    public function updateFields(): void
    {
        $this->ctrl->saveParameter($this, 'record_id');
        $positions = $this->getPositionsFromPost();
        asort($positions, SORT_NUMERIC);
        $record_id = $this->getRecordIdFromQuery();
        if (!$record_id) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'));
            $this->editFields();
            return;
        }

        $fields = ilAdvancedMDFieldDefinition::getInstancesByRecordId($record_id);

        if ($this->getPermissions()->hasPermission(
            ilAdvancedMDPermissionHelper::CONTEXT_RECORD,
            (string) $record_id,
            ilAdvancedMDPermissionHelper::ACTION_RECORD_FIELD_POSITIONS
        )) {
            $positions_flipped = array_flip(array_keys($positions));
            foreach ($fields as $field) {
                $field->setPosition((int) $positions_flipped[$field->getFieldId()]);
                $field->update();
            }
        }

        foreach ($fields as $field) {
            if ($this->getPermissions()->hasPermission(
                ilAdvancedMDPermissionHelper::CONTEXT_FIELD,
                (string) $field->getFieldId(),
                ilAdvancedMDPermissionHelper::ACTION_FIELD_EDIT_PROPERTY,
                ilAdvancedMDPermissionHelper::SUBACTION_FIELD_SEARCHABLE
            )) {
                $post_searchable = (array) ($this->http->request()->getParsedBody()['searchable'] ?? []);
                $field->setSearchable((bool) ($post_searchable[$field->getFieldId()] ?? false));
                $field->update();
            }
        }

        $language = $this->request->getQueryParams()['mdlang'] ?? false;
        if ($language) {
            $this->ctrl->setParameter($this, 'mdlang', $this->request->getQueryParams()['mdlang']);
        }
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
        $this->ctrl->redirect($this, "editFields");
    }

    /**
     * Update record
     * @access public
     * @param
     */
    public function updateRecord(): void
    {
        $record_id = $this->getRecordIdFromQuery();
        if (!$record_id) {
            $this->ctrl->redirect($this, 'showRecords');
        }
        $this->initRecordObject();
        $this->initLanguage($record_id);
        $this->showLanguageSwitch($record_id, 'editRecord');

        $form = $this->initForm('edit');
        if (!$this->form->checkInput()) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('err_check_input'));
            $this->form->setValuesByPost();
            $this->editRecord($this->form);
            return;
        }

        $this->loadRecordFormData($form);
        $this->record->update();

        $translations = ilAdvancedMDRecordTranslations::getInstanceByRecordId($this->record->getRecordId());
        $translations->updateTranslations(
            $this->active_language,
            $this->form->getInput('title'),
            $this->form->getInput('desc')
        );

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
        $this->ctrl->redirect($this, 'editRecord');
    }

    /**
     * Show
     * @access public
     * @param
     */
    public function createRecord(ilPropertyFormGUI $form = null): void
    {
        $this->initRecordObject();
        $this->setRecordSubTabs();
        if (!$form instanceof ilPropertyFormGUI) {
            $this->initForm('create');
        }
        $this->tpl->setContent($this->form->getHTML());
    }

    protected function importRecords(): void
    {
        $this->initRecordObject();
        $this->setRecordSubTabs();

        // Import Table
        $this->initImportForm();
        $this->tpl->setContent($this->import_form->getHTML());
    }

    /**
     * Set subtabs for record editing/creation
     */
    protected function setRecordSubTabs(int $level = 1, bool $show_settings = false): void
    {
        $this->tabs_gui->clearTargets();
        $this->tabs_gui->clearSubTabs();

        if ($level == 1) {
            $this->tabs_gui->setBackTarget(
                $this->lng->txt('md_adv_record_list'),
                $this->ctrl->getLinkTarget($this, 'showRecords')
            );

            if ($show_settings) {
                $this->tabs_gui->addTab(
                    self::TAB_RECORD_SETTINGS,
                    $this->lng->txt('settings'),
                    $this->ctrl->getLinkTarget($this, self::TAB_RECORD_SETTINGS)
                );
                $this->ctrl->setParameterByClass(
                    strtolower(\ilAdvancedMDRecordTranslationGUI::class),
                    'record_id',
                    $this->record->getRecordId()
                );
                $this->lng->loadLanguageModule('obj');
                $this->tabs_gui->addTab(
                    self::TAB_TRANSLATION,
                    $this->lng->txt('obj_multilinguality'),
                    $this->ctrl->getLinkTargetByClass(
                        strtolower(\ilAdvancedMDRecordTranslationGUI::class),
                        ''
                    )
                );
            }
        }
        if ($level == 2) {
            $this->tabs_gui->setBack2Target(
                $this->lng->txt('md_adv_record_list'),
                $this->ctrl->getLinkTarget($this, 'showRecords')
            );
            $this->tabs_gui->setBackTarget(
                $this->lng->txt('md_adv_field_list'),
                $this->ctrl->getLinkTarget($this, 'editFields')
            );
        }
    }

    protected function initImportForm(): void
    {
        if (is_object($this->import_form)) {
            return;
        }

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

    public function importRecord(): void
    {
        $this->initImportForm();
        if (!$this->import_form->checkInput()) {
            $this->import_form->setValuesByPost();
            $this->importRecords();
            return;
        }

        $import_files = new ilAdvancedMDRecordImportFiles();
        if (!$create_time = $import_files->moveUploadedFile($_FILES['file']['tmp_name'])) {
            $this->createRecord();
            return;
        }

        try {
            $parser = new ilAdvancedMDRecordParser($import_files->getImportFileByCreationDate($create_time));

            // local import?
            if ($this->context === self::CONTEXT_OBJECT) {
                $parser->setContext($this->obj_id, $this->obj_type, $this->sub_type);
            }

            // Validate
            $parser->setMode(ilAdvancedMDRecordParser::MODE_INSERT_VALIDATION);
            $parser->startParsing();

            // Insert
            $parser->setMode(ilAdvancedMDRecordParser::MODE_INSERT);
            $parser->startParsing();
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('md_adv_added_new_record'), true);
            $this->ctrl->redirect($this, "showRecords");
        } catch (ilSaxParserException $exc) {
            $this->tpl->setOnScreenMessage('failure', $exc->getMessage(), true);
            $this->ctrl->redirect($this, "importRecords");
        }

        // Finally delete import file
        $import_files->deleteFileByCreationDate($create_time);
    }

    /**
     * Save record
     * @access public
     * @param
     */
    public function saveRecord(): void
    {
        $this->initRecordObject();
        $form = $this->initForm('create');
        if (!$this->form->checkInput()) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('err_check_input'));
            $this->createRecord($this->form);
            return;
        }

        $record = $this->loadRecordFormData($form);
        if ($this->obj_type) {
            $sub_types = (!is_array($this->sub_type))
                ? [$this->sub_type]
                : $this->sub_type;
            $assigned_object_types = array_map(function ($sub_type) {
                return [
                    "obj_type" => $this->obj_type,
                    "sub_type" => $sub_type,
                    "optional" => false
                ];
            }, $sub_types);
            $this->record->setAssignedObjectTypes($assigned_object_types);
        }

        $record->setDefaultLanguage($this->lng->getDefaultLanguage());
        $record->save();

        $translations = ilAdvancedMDRecordTranslations::getInstanceByRecordId($record->getRecordId());
        $translations->addTranslationEntry($record->getDefaultLanguage(), true);
        $translations->updateTranslations(
            $record->getDefaultLanguage(),
            $this->form->getInput('title'),
            $this->form->getInput('desc')
        );
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('md_adv_added_new_record'), true);
        $this->ctrl->redirect($this, 'showRecords');
    }

    /**
     * Edit field
     * @access public
     */
    public function editField(ilPropertyFormGUI $a_form = null): void
    {
        $record_id = $this->getRecordIdFromQuery();
        $field_id = $this->getFieldIdFromQuery();
        if (!$record_id || !$field_id) {
            $this->editFields();
            return;
        }
        $this->ctrl->saveParameter($this, 'field_id');
        $this->ctrl->saveParameter($this, 'record_id');
        $this->initRecordObject();
        $this->setRecordSubTabs(2);

        $field_definition = ilAdvancedMDFieldDefinition::getInstance($field_id);

        if (!$a_form instanceof ilPropertyFormGUI) {
            $this->initLanguage($this->record->getRecordId());
            $this->showLanguageSwitch($this->record->getRecordId(), 'editField');
            $a_form = $this->initFieldForm($field_definition);
        }
        $table = null;
        if ($field_definition->hasComplexOptions()) {
            $table = $field_definition->getComplexOptionsOverview($this, "editField");
        }
        $this->tpl->setContent($a_form->getHTML() . $table);
    }

    /**
     * Update field
     * @access public
     */
    public function updateField(): void
    {
        $record_id = $this->getRecordIdFromQuery();
        $field_id = $this->getFieldIdFromQuery();
        $this->ctrl->saveParameter($this, 'record_id');
        $this->ctrl->saveParameter($this, 'field_id');

        if (!$record_id || !$field_id) {
            $this->editFields();
            return;
        }

        $this->initRecordObject();
        $this->initLanguage($record_id);
        $this->showLanguageSwitch($record_id, 'editField');

        $confirm = false;
        $field_definition = ilAdvancedMDFieldDefinition::getInstance($field_id);
        $form = $this->initFieldForm($field_definition);
        if ($form->checkInput()) {
            $field_definition->importDefinitionFormPostValues($form, $this->getPermissions(), $this->active_language);
            if (!$field_definition->importDefinitionFormPostValuesNeedsConfirmation()) {
                $field_definition->update();
                $translations = ilAdvancedMDFieldTranslations::getInstanceByRecordId($this->record->getRecordId());
                $translations->updateFromForm($field_id, $this->active_language, $form);

                $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
                $this->ctrl->redirect($this, 'editField');
            } else {
                $confirm = true;
            }
        }

        $form->setValuesByPost();

        // fields needs confirmation of updated settings
        if ($confirm) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("md_adv_confirm_definition"));
            $field_definition->prepareDefinitionFormConfirmation($form);
        }

        $this->editField($form);
    }

    /**
     * Show field type selection
     * @access public
     */
    public function createField(ilPropertyFormGUI $a_form = null): void
    {
        $record_id = $this->getRecordIdFromQuery();
        $field_type = $this->getFieldTypeFromPost();
        if (!$field_type) {
            $field_type = $this->getFieldTypeFromQuery();
        }

        $this->initRecordObject();
        $this->ctrl->setParameter($this, 'ftype', $field_type);
        $this->setRecordSubTabs(2);
        if (!$record_id || !$field_type) {
            $this->editFields();
            return;
        }

        if (!$a_form) {
            $field_definition = ilAdvancedMDFieldDefinition::getInstance(null, $field_type);
            $field_definition->setRecordId($record_id);
            $a_form = $this->initFieldForm($field_definition);
        }
        $this->tpl->setContent($a_form->getHTML());
    }

    public function saveField(): void
    {
        $record_id = $this->getRecordIdFromQuery();
        $ftype = $this->getFieldTypeFromQuery();

        if (!$record_id || !$ftype) {
            $this->editFields();
            return;
        }

        $this->initRecordObject();
        $this->initLanguage($record_id);
        $this->ctrl->saveParameter($this, 'ftype');

        $field_definition = ilAdvancedMDFieldDefinition::getInstance(
            null,
            $ftype
        );
        $field_definition->setRecordId($record_id);
        $form = $this->initFieldForm($field_definition);

        if ($form->checkInput()) {
            $field_definition->importDefinitionFormPostValues($form, $this->getPermissions(), $this->active_language);
            $field_definition->save();

            $translations = ilAdvancedMDFieldTranslations::getInstanceByRecordId($record_id);
            $translations->read();
            $translations->updateFromForm($field_definition->getFieldId(), $this->active_language, $form);

            $this->tpl->setOnScreenMessage('success', $this->lng->txt('save_settings'), true);
            $this->ctrl->redirect($this, "editFields");
        }

        $form->setValuesByPost();
        $this->createField($form);
    }

    protected function initFieldForm(ilAdvancedMDFieldDefinition $a_definition): ilPropertyFormGUI
    {
        $is_creation_mode = $a_definition->getFieldId() ? false : true;

        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));

        $translations = ilAdvancedMDFieldTranslations::getInstanceByRecordId($this->record->getRecordId());
        if ($is_creation_mode) {
            $form->setDescription($a_definition->getDescription());
        } else {
            $form->setDescription($translations->getFormTranslationInfo(
                $a_definition->getFieldId(),
                $this->active_language
            ));
        }
        $type = new ilNonEditableValueGUI($this->lng->txt("type"));
        $type->setValue($this->lng->txt($a_definition->getTypeTitle()));
        $form->addItem($type);
        $a_definition->addToFieldDefinitionForm($form, $this->getPermissions(), $this->active_language);

        if ($is_creation_mode) {
            $form->setTitle($this->lng->txt('md_adv_create_field'));
            $form->addCommandButton('saveField', $this->lng->txt('create'));
        } else {
            $form->setTitle($this->lng->txt('md_adv_edit_field'));
            $form->addCommandButton('updateField', $this->lng->txt('save'));
        }

        $form->addCommandButton('editFields', $this->lng->txt('cancel'));

        return $form;
    }

    protected function initForm($a_mode): ilPropertyFormGUI
    {
        if ($this->form instanceof ilPropertyFormGUI) {
            return $this->form;
        }
        $perm = $this->getPermissions()->hasPermissions(
            ilAdvancedMDPermissionHelper::CONTEXT_RECORD,
            (string) $this->record->getRecordId(),
            array(
                array(ilAdvancedMDPermissionHelper::ACTION_RECORD_EDIT_PROPERTY,
                      ilAdvancedMDPermissionHelper::SUBACTION_RECORD_TITLE
                )
                ,
                array(ilAdvancedMDPermissionHelper::ACTION_RECORD_EDIT_PROPERTY,
                      ilAdvancedMDPermissionHelper::SUBACTION_RECORD_DESCRIPTION
                )
                ,
                array(ilAdvancedMDPermissionHelper::ACTION_RECORD_EDIT_PROPERTY,
                      ilAdvancedMDPermissionHelper::SUBACTION_RECORD_OBJECT_TYPES
                )
                ,
                ilAdvancedMDPermissionHelper::ACTION_RECORD_TOGGLE_ACTIVATION
            )
        );

        $this->form = new ilPropertyFormGUI();

        $translations = ilAdvancedMDRecordTranslations::getInstanceByRecordId($this->record->getRecordId());
        $this->form->setDescription($translations->getFormTranslationInfo($this->active_language));
        $this->form->setFormAction($this->ctrl->getFormAction($this));

        // title
        $title = new ilTextInputGUI($this->lng->txt('title'), 'title');
        $title->setValue($this->record->getTitle());
        $title->setSize(20);
        $title->setMaxLength(70);
        $title->setRequired(true);

        if (!$perm[ilAdvancedMDPermissionHelper::ACTION_RECORD_EDIT_PROPERTY][ilAdvancedMDPermissionHelper::SUBACTION_RECORD_TITLE]) {
            $title->setDisabled(true);
        }
        $this->form->addItem($title);
        $translations->modifyTranslationInfoForTitle($this->form, $title, $this->active_language);

        // desc
        $desc = new ilTextAreaInputGUI($this->lng->txt('description'), 'desc');
        $desc->setValue($this->record->getDescription());
        $desc->setRows(3);

        if (!$perm[ilAdvancedMDPermissionHelper::ACTION_RECORD_EDIT_PROPERTY][ilAdvancedMDPermissionHelper::SUBACTION_RECORD_DESCRIPTION]) {
            $desc->setDisabled(true);
        }
        $this->form->addItem($desc);
        $translations->modifyTranslationInfoForDescription($this->form, $desc, $this->active_language);

        // active
        $check = new ilCheckboxInputGUI($this->lng->txt('md_adv_active'), 'active');
        $check->setChecked($this->record->isActive());
        $check->setValue("1");
        $this->form->addItem($check);

        if (!$perm[ilAdvancedMDPermissionHelper::ACTION_RECORD_TOGGLE_ACTIVATION]) {
            $check->setDisabled(true);
        }

        if (!$this->obj_type) {
            // scope
            $scope = new ilCheckboxInputGUI($this->lng->txt('md_adv_scope'), 'scope');
            $scope->setChecked($this->record->enabledScope());
            $scope->setValue("1");
            $this->form->addItem($scope);
            $subitems = new ilRepositorySelector2InputGUI(
                $this->lng->txt("objects"),
                "scope_containers",
                true,
                $this->form
            );
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

                $sel_name = 'obj_types__' . $t;
                $check = new ilSelectInputGUI($type['text'], $sel_name);
                //$check = new ilSelectInputGUI($type["text"], 'obj_types[' . $t . ']');
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
                break;

            case 'edit':
                $this->form->setTitle($this->lng->txt('md_adv_edit_record'));
                $this->form->addCommandButton('updateRecord', $this->lng->txt('save'));
                $this->form->addCommandButton('showRecords', $this->lng->txt('cancel'));
        }
        return $this->form;
    }

    protected function getSubstitutionFieldPermissions(string $a_obj_type, int $a_field_id): array
    {
        if ($a_obj_type == "crs") {
            $perm = $this->getPermissions()->hasPermissions(
                ilAdvancedMDPermissionHelper::CONTEXT_SUBSTITUTION_COURSE,
                (string) $a_field_id,
                array(
                    ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_COURSE_SHOW_FIELD
                    ,
                    array(ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_COURSE_EDIT_FIELD_PROPERTY,
                          ilAdvancedMDPermissionHelper::SUBACTION_SUBSTITUTION_BOLD
                    )
                    ,
                    array(ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_COURSE_EDIT_FIELD_PROPERTY,
                          ilAdvancedMDPermissionHelper::SUBACTION_SUBSTITUTION_NEWLINE
                    )
                )
            );
            return array(
                "show" => $perm[ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_COURSE_SHOW_FIELD]
                ,
                "bold" => $perm[ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_COURSE_EDIT_FIELD_PROPERTY][ilAdvancedMDPermissionHelper::SUBACTION_SUBSTITUTION_BOLD]
                ,
                "newline" => $perm[ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_COURSE_EDIT_FIELD_PROPERTY][ilAdvancedMDPermissionHelper::SUBACTION_SUBSTITUTION_NEWLINE]
            );
        } elseif ($a_obj_type == "cat") {
            $perm = $this->getPermissions()->hasPermissions(
                ilAdvancedMDPermissionHelper::CONTEXT_SUBSTITUTION_CATEGORY,
                (string) $a_field_id,
                array(
                    ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_CATEGORY_SHOW_FIELD
                    ,
                    array(ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_CATEGORY_EDIT_FIELD_PROPERTY,
                          ilAdvancedMDPermissionHelper::SUBACTION_SUBSTITUTION_BOLD
                    )
                    ,
                    array(ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_CATEGORY_EDIT_FIELD_PROPERTY,
                          ilAdvancedMDPermissionHelper::SUBACTION_SUBSTITUTION_NEWLINE
                    )
                )
            );
            return array(
                "show" => $perm[ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_CATEGORY_SHOW_FIELD]
                ,
                "bold" => $perm[ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_CATEGORY_EDIT_FIELD_PROPERTY][ilAdvancedMDPermissionHelper::SUBACTION_SUBSTITUTION_BOLD]
                ,
                "newline" => $perm[ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_CATEGORY_EDIT_FIELD_PROPERTY][ilAdvancedMDPermissionHelper::SUBACTION_SUBSTITUTION_NEWLINE]
            );
        } elseif ($a_obj_type == "sess") {
            $perm = $this->getPermissions()->hasPermissions(
                ilAdvancedMDPermissionHelper::CONTEXT_SUBSTITUTION_SESSION,
                (string) $a_field_id,
                array(
                    ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_SESSION_SHOW_FIELD
                    ,
                    array(ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_SESSION_EDIT_FIELD_PROPERTY,
                          ilAdvancedMDPermissionHelper::SUBACTION_SUBSTITUTION_BOLD
                    )
                    ,
                    array(ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_SESSION_EDIT_FIELD_PROPERTY,
                          ilAdvancedMDPermissionHelper::SUBACTION_SUBSTITUTION_NEWLINE
                    )
                )
            );
            return array(
                "show" => $perm[ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_SESSION_SHOW_FIELD]
                ,
                "bold" => $perm[ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_SESSION_EDIT_FIELD_PROPERTY][ilAdvancedMDPermissionHelper::SUBACTION_SUBSTITUTION_BOLD]
                ,
                "newline" => $perm[ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_SESSION_EDIT_FIELD_PROPERTY][ilAdvancedMDPermissionHelper::SUBACTION_SUBSTITUTION_NEWLINE]
            );
        } elseif ($a_obj_type == "grp") {
            $perm = $this->getPermissions()->hasPermissions(
                ilAdvancedMDPermissionHelper::CONTEXT_SUBSTITUTION_GROUP,
                (string) $a_field_id,
                array(
                    ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_GROUP_SHOW_FIELD
                    ,
                    array(ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_GROUP_EDIT_FIELD_PROPERTY,
                          ilAdvancedMDPermissionHelper::SUBACTION_SUBSTITUTION_BOLD
                    )
                    ,
                    array(ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_GROUP_EDIT_FIELD_PROPERTY,
                          ilAdvancedMDPermissionHelper::SUBACTION_SUBSTITUTION_NEWLINE
                    )
                )
            );
            return array(
                "show" => $perm[ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_GROUP_SHOW_FIELD]
                ,
                "bold" => $perm[ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_GROUP_EDIT_FIELD_PROPERTY][ilAdvancedMDPermissionHelper::SUBACTION_SUBSTITUTION_BOLD]
                ,
                "newline" => $perm[ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_GROUP_EDIT_FIELD_PROPERTY][ilAdvancedMDPermissionHelper::SUBACTION_SUBSTITUTION_NEWLINE]
            );
        } elseif ($a_obj_type == "iass") {
            $perm = $this->getPermissions()->hasPermissions(
                ilAdvancedMDPermissionHelper::CONTEXT_SUBSTITUTION_IASS,
                (string) $a_field_id,
                array(
                    ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_IASS_SHOW_FIELD
                    ,
                    array(ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_IASS_EDIT_FIELD_PROPERTY,
                          ilAdvancedMDPermissionHelper::SUBACTION_SUBSTITUTION_BOLD
                    )
                    ,
                    array(ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_IASS_EDIT_FIELD_PROPERTY,
                          ilAdvancedMDPermissionHelper::SUBACTION_SUBSTITUTION_NEWLINE
                    )
                )
            );
            return array(
                "show" => $perm[ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_IASS_SHOW_FIELD]
                ,
                "bold" => $perm[ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_IASS_EDIT_FIELD_PROPERTY][ilAdvancedMDPermissionHelper::SUBACTION_SUBSTITUTION_BOLD]
                ,
                "newline" => $perm[ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_IASS_EDIT_FIELD_PROPERTY][ilAdvancedMDPermissionHelper::SUBACTION_SUBSTITUTION_NEWLINE]
            );
        } elseif ($a_obj_type == "exc") {
            $perm = $this->getPermissions()->hasPermissions(
                ilAdvancedMDPermissionHelper::CONTEXT_SUBSTITUTION_EXERCISE,
                (string) $a_field_id,
                array(
                    ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_EXERCISE_SHOW_FIELD
                    ,
                    array(ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_EXERCISE_EDIT_FIELD_PROPERTY,
                          ilAdvancedMDPermissionHelper::SUBACTION_SUBSTITUTION_BOLD
                    )
                    ,
                    array(ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_EXERCISE_EDIT_FIELD_PROPERTY,
                          ilAdvancedMDPermissionHelper::SUBACTION_SUBSTITUTION_NEWLINE
                    )
                )
            );
            return array(
                "show" => $perm[ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_EXERCISE_SHOW_FIELD]
                ,
                "bold" => $perm[ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_EXERCISE_EDIT_FIELD_PROPERTY][ilAdvancedMDPermissionHelper::SUBACTION_SUBSTITUTION_BOLD]
                ,
                "newline" => $perm[ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_EXERCISE_EDIT_FIELD_PROPERTY][ilAdvancedMDPermissionHelper::SUBACTION_SUBSTITUTION_NEWLINE]
            );
        }
        return [];
    }

    /**
     * init form table 'substitutions'
     * @access protected
     */
    protected function initFormSubstitutions(): ?ilPropertyFormGUI
    {
        global $DIC;

        $ilAccess = $DIC['ilAccess'];

        if (!$visible_records = ilAdvancedMDRecord::_getAllRecordsByObjectType()) {
            return null;
        }

        $this->form = new ilPropertyFormGUI();
        $this->form->setFormAction($this->ctrl->getFormAction($this));
        #$this->form->setTableWidth('100%');

        // substitution
        foreach ($visible_records as $obj_type => $records) {
            $perm = null;

            if (in_array($obj_type, $this->permissions->getAllowedObjectTypes())) {
                $perm = $this->getPermissions()->hasPermissions(
                    ilAdvancedMDPermissionHelper::CONTEXT_SUBSTITUTION,
                    (string) $obj_type,
                    array(
                        ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_SHOW_DESCRIPTION
                        ,
                        ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_SHOW_FIELDNAMES
                        ,
                        ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_FIELD_POSITIONS
                    )
                );
            }

            $sub = ilAdvancedMDSubstitution::_getInstanceByObjectType($obj_type);

            // Show section
            $section = new ilFormSectionHeaderGUI();
            $section->setTitle($this->lng->txt('objs_' . $obj_type));
            $this->form->addItem($section);

            $check = new ilCheckboxInputGUI($this->lng->txt('description'), 'enabled_desc_' . $obj_type);
            $check->setValue("1");
            $check->setOptionTitle($this->lng->txt('md_adv_desc_show'));
            $check->setChecked($sub->isDescriptionEnabled());
            $this->form->addItem($check);

            if ($perm && !$perm[ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_SHOW_DESCRIPTION]) {
                $check->setDisabled(true);
            }

            $check = new ilCheckboxInputGUI($this->lng->txt('md_adv_field_names'), 'enabled_field_names_' . $obj_type);
            $check->setValue("1");
            $check->setOptionTitle($this->lng->txt('md_adv_fields_show'));
            $check->setChecked($sub->enabledFieldNames());
            $this->form->addItem($check);

            if ($perm && !$perm[ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_SHOW_FIELDNAMES]) {
                $check->setDisabled(true);
            }

            $perm_pos = null;
            if ($perm) {
                $perm_pos = $perm[ilAdvancedMDPermissionHelper::ACTION_SUBSTITUTION_FIELD_POSITIONS];
            }

            $definitions = ilAdvancedMDFieldDefinition::getInstancesByObjType($obj_type);
            $definitions = $sub->sortDefinitions($definitions);

            $counter = 1;
            foreach ($definitions as $def) {
                $definition_id = $def->getFieldId();

                $perm = $this->getSubstitutionFieldPermissions($obj_type, $definition_id);

                $title = ilAdvancedMDRecord::_lookupTitle((int) $def->getRecordId());
                $title = $def->getTitle() . ' (' . $title . ')';

                $check = new ilCheckboxInputGUI($title, 'show_' . $obj_type . '_' . $definition_id);
                $check->setValue("1");
                $check->setOptionTitle($this->lng->txt('md_adv_show'));
                $check->setChecked($sub->isSubstituted($definition_id));

                if ($perm && !$perm["show"]) {
                    $check->setDisabled(true);
                }

                $pos = new ilNumberInputGUI(
                    $this->lng->txt('position'),
                    'position_' . $obj_type . '_' . $definition_id
                );
                $pos->setSize(3);
                $pos->setMaxLength(4);
                $pos->allowDecimals(true);
                $pos->setValue(sprintf('%.1f', $counter++));
                $check->addSubItem($pos);

                if ($perm && !$perm_pos) {
                    $pos->setDisabled(true);
                }

                $bold = new ilCheckboxInputGUI(
                    $this->lng->txt('bold'),
                    'bold_' . $obj_type . '_' . $definition_id
                );
                $bold->setValue("1");
                $bold->setChecked($sub->isBold($definition_id));
                $check->addSubItem($bold);

                if ($perm && !$perm["bold"]) {
                    $bold->setDisabled(true);
                }

                $bold = new ilCheckboxInputGUI(
                    $this->lng->txt('newline'),
                    'newline_' . $obj_type . '_' . $definition_id
                );
                $bold->setValue("1");
                $bold->setChecked($sub->hasNewline($definition_id));
                $check->addSubItem($bold);

                if ($perm && !$perm["newline"]) {
                    $bold->setDisabled(true);
                }

                $this->form->addItem($check);
            }
        }
        $this->form->setTitle($this->lng->txt('md_adv_substitution_table'));

        if ($ilAccess->checkAccess('write', '', $this->ref_id)) {
            $this->form->addCommandButton('updateSubstitutions', $this->lng->txt('save'));
        }
        return $this->form;
    }

    protected function loadRecordFormData(ilPropertyFormGUI $form): ilAdvancedMDRecord
    {
        $translations = ilAdvancedMDRecordTranslations::getInstanceByRecordId($this->record->getRecordId());

        $perm = $this->getPermissions()->hasPermissions(
            ilAdvancedMDPermissionHelper::CONTEXT_RECORD,
            (string) $this->record->getRecordId(),
            array(
                array(ilAdvancedMDPermissionHelper::ACTION_RECORD_EDIT_PROPERTY,
                      ilAdvancedMDPermissionHelper::SUBACTION_RECORD_TITLE
                )
                ,
                array(ilAdvancedMDPermissionHelper::ACTION_RECORD_EDIT_PROPERTY,
                      ilAdvancedMDPermissionHelper::SUBACTION_RECORD_DESCRIPTION
                )
                ,
                array(ilAdvancedMDPermissionHelper::ACTION_RECORD_EDIT_PROPERTY,
                      ilAdvancedMDPermissionHelper::SUBACTION_RECORD_OBJECT_TYPES
                )
                ,
                ilAdvancedMDPermissionHelper::ACTION_RECORD_TOGGLE_ACTIVATION
            )
        );

        if ($perm[ilAdvancedMDPermissionHelper::ACTION_RECORD_TOGGLE_ACTIVATION]) {
            $this->record->setActive((bool) $form->getInput('active'));
        }
        if ($perm[ilAdvancedMDPermissionHelper::ACTION_RECORD_EDIT_PROPERTY][ilAdvancedMDPermissionHelper::SUBACTION_RECORD_TITLE]) {
            if (
                $translations->getDefaultTranslation() == null ||
                $translations->getDefaultTranslation()->getLangKey() == $this->active_language
            ) {
                $this->record->setTitle((string) $form->getInput('title'));
            }
        }
        if ($perm[ilAdvancedMDPermissionHelper::ACTION_RECORD_EDIT_PROPERTY][ilAdvancedMDPermissionHelper::SUBACTION_RECORD_DESCRIPTION]) {
            if (
                $translations->getDefaultTranslation() == null ||
                $translations->getDefaultTranslation()->getLangKey() == $this->active_language) {
                $this->record->setDescription($form->getInput('desc'));
            }
        }

        if (!$this->obj_type) {
            if ($perm[ilAdvancedMDPermissionHelper::ACTION_RECORD_EDIT_PROPERTY][ilAdvancedMDPermissionHelper::SUBACTION_RECORD_OBJECT_TYPES]) {
                $obj_types = [];
                foreach (ilAdvancedMDRecord::_getAssignableObjectTypes(true) as $type) {
                    $t = $type["obj_type"] . ":" . $type["sub_type"];
                    $value = $form->getInput('obj_types__' . $t);
                    if (!$value) {
                        continue;
                    }
                    $obj_types[] = [
                        'obj_type' => $type['obj_type'],
                        'sub_type' => $type['sub_type'],
                        'optional' => ($value > 1)
                    ];
                }
                $this->record->setAssignedObjectTypes($obj_types);
            }
        }

        $scopes = $form->getInput('scope');
        if (is_array($scopes)) {
            $this->record->enableScope(true);
            $this->record->setScopes(
                array_map(
                    function ($scope_ref_id) {
                        $scope = new ilAdvancedMDRecordScope();
                        $scope->setRefId($scope_ref_id);
                        return $scope;
                    },
                    $scopes
                )
            );
        } else {
            $this->record->enableScope(false);
        }
        return $this->record;
    }

    /**
     * @todo get rid of $this->obj_id switch
     */
    protected function initRecordObject(): ilAdvancedMDRecord
    {
        if (!$this->record instanceof ilAdvancedMDRecord) {
            $record_id = $this->getRecordIdFromQuery();
            $this->record = ilAdvancedMDRecord::_getInstanceByRecordId((int) $record_id);
            $this->ctrl->saveParameter($this, 'record_id');

            // bind to parent object (aka local adv md)
            if (!$record_id && $this->obj_id) {
                $this->record->setParentObject($this->obj_id);
            }
        }
        return $this->record;
    }

    /**
     * Set sub tabs
     * @access protected
     */
    protected function setSubTabs(int $context): void
    {
        if ($context == self::CONTEXT_OBJECT) {
            return;
        }

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
     * @todo get rid of this (used in ilAdvancedMDRecordTableGUI) and the parsing there. Also get rid of the usage in
     *       ilAvancedMDSettingsGUI::updateRecords
     */
    protected function getParsedRecordObjects(): array
    {
        $res = [];

        $sub_type = (!is_array($this->sub_type))
            ? [$this->sub_type]
            : $this->sub_type;

        if ($this->context === self::CONTEXT_OBJECT) {
            // get all records selected for subtype
            foreach ($sub_type as $st) {
                $selected[$st] = ilAdvancedMDRecord::getObjRecSelection($this->obj_id, $st);
            }
        }

        $records = ilAdvancedMDRecord::_getRecords();
        $orderings = new ilAdvancedMDRecordObjectOrderings();
        $records = $orderings->sortRecords($records, $this->obj_id);

        $position = 0;

        // get all records usuable in current context
        foreach ($records as $record) {
            $parent_id = $record->getParentObject();

            if ($this->context == self::CONTEXT_ADMINISTRATION) {
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

            $tmp_arr = [];
            $tmp_arr['readonly'] = null;
            $tmp_arr['local'] = null;
            $tmp_arr['optional'] = null;
            $tmp_arr['id'] = $record->getRecordId();
            $tmp_arr['active'] = $record->isActive();
            $tmp_arr['title'] = $record->getTitle();
            $tmp_arr['description'] = $record->getDescription();
            $tmp_arr['fields'] = [];
            $tmp_arr['obj_types'] = $record->getAssignedObjectTypes();
            foreach ($record->getAssignedObjectTypes() as $idx => $item) {
                $tmp_arr['obj_types'][$idx]['context'] = null;
            }
            $position += 10;
            $tmp_arr['position'] = $position;

            $tmp_arr['perm'] = $this->permissions->hasPermissions(
                ilAdvancedMDPermissionHelper::CONTEXT_RECORD,
                (string) $record->getRecordId(),
                array(
                    ilAdvancedMDPermissionHelper::ACTION_RECORD_EDIT
                    ,
                    ilAdvancedMDPermissionHelper::ACTION_RECORD_EDIT_FIELDS
                    ,
                    ilAdvancedMDPermissionHelper::ACTION_RECORD_TOGGLE_ACTIVATION
                    ,
                    array(ilAdvancedMDPermissionHelper::ACTION_RECORD_EDIT_PROPERTY,
                          ilAdvancedMDPermissionHelper::SUBACTION_RECORD_OBJECT_TYPES
                    )
                )
            );

            if ($this->obj_type) {
                $tmp_arr["readonly"] = !(bool) $parent_id;
                $tmp_arr["local"] = $parent_id;

                // local records are never optional
                $assigned = $optional = false;
                foreach ($tmp_arr['obj_types'] as $idx => $item) {
                    if ($item["obj_type"] == $this->obj_type &&
                        in_array($item["sub_type"], $sub_type)) {
                        $assigned = true;
                        $optional = $item["optional"];
                        $tmp_arr['obj_types'][$idx]['context'] = true;
                    }
                }
                $tmp_arr['optional'] = $optional;
                if ($optional) {
                    // in object context "active" means selected record
                    // $tmp_arr['active'] = (is_array($selected[$item["sub_type"]]) && in_array($record->getRecordId(), $selected[$item["sub_type"]]));
                    $tmp_arr['local_selected'] = [];
                    foreach ($selected as $key => $records) {
                        if (in_array($record->getRecordId(), $records)) {
                            $tmp_arr['local_selected'][$this->obj_type][] = $key;
                        }
                    }
                }
            }

            $res[] = $tmp_arr;
        }
        return $res;
    }


    //
    // complex options
    //

    public function editComplexOption(ilPropertyFormGUI $a_form = null): void
    {
        $field_id = $this->getFieldIdFromQuery();
        if (!$field_id) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('err_select_one'));
            $this->ctrl->redirect($this, 'showRecords');
        }

        $field_definition = ilAdvancedMDFieldDefinition::getInstance($field_id);
        if (!$field_definition->hasComplexOptions()) {
            $this->ctrl->redirect($this, "editField");
        }

        if (!$a_form) {
            $a_form = $this->initComplexOptionForm($field_definition);
        }

        $this->tpl->setContent($a_form->getHTML());
    }

    protected function initComplexOptionForm(ilAdvancedMDFieldDefinition $a_def): ilPropertyFormGUI
    {
        $this->ctrl->saveParameter($this, "record_id");
        $this->ctrl->saveParameter($this, "field_id");
        $this->ctrl->saveParameter($this, "oid");

        $form = new ilPropertyFormGUI();
        $form->setTitle($this->lng->txt("md_adv_edit_complex_option"));
        $form->setFormAction($this->ctrl->getFormAction($this, "updateComplexOption"));

        $oid = $this->getOidFromQuery();
        $a_def->initOptionForm($form, $oid);

        $form->addCommandButton("updateComplexOption", $this->lng->txt("save"));
        $form->addCommandButton("editField", $this->lng->txt("cancel"));

        return $form;
    }

    public function updateComplexOption(): void
    {
        $field_id = $this->getFieldIdFromQuery();
        $field_definition = ilAdvancedMDFieldDefinition::getInstance($field_id);
        $oid = $this->getOidFromQuery();

        if ($field_definition->hasComplexOptions()) {
            $form = $this->initComplexOptionForm($field_definition);
            if ($form->checkInput() &&
                $field_definition->updateComplexOption($form, $oid)) {
                $field_definition->update();
                $this->tpl->setOnScreenMessage('success', $this->lng->txt("settings_saved"), true);
            }
        }

        $this->ctrl->redirect($this, "editField");
    }

    protected function initLanguage(int $record_id): void
    {
        $translations = ilAdvancedMDRecordTranslations::getInstanceByRecordId($record_id);
        // read active language
        $default = '';
        foreach ($translations->getTranslations() as $translation) {
            if ($translation->getLangKey() == $translations->getDefaultLanguage()) {
                $default = $translation->getLangKey();
            }
        }
        $active = $this->request->getQueryParams()['mdlang'] ?? $default;
        $this->active_language = $active;
    }

    protected function showLanguageSwitch(int $record_id, string $target): void
    {
        $translations = ilAdvancedMDRecordTranslations::getInstanceByRecordId($record_id);

        if (count($translations->getTranslations()) <= 1) {
            return;
        }
        $actions = [];
        foreach ($translations->getTranslations() as $translation) {
            $this->ctrl->setParameter($this, 'mdlang', $translation->getLangKey());
            $actions[$translation->getLangKey()] = $this->ctrl->getLinkTarget(
                $this,
                $target
            );
        }
        $this->ctrl->setParameter($this, 'mdlang', $this->active_language);
        $view_control = $this->ui_factory->viewControl()->mode(
            $actions,
            $this->lng->txt('meta_aria_language_selection')
        )->withActive($this->active_language);
        $this->toolbar->addComponent($view_control);
    }
}
