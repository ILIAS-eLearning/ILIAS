<?php declare(strict_types=1);


    
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
 
use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Refinery\Factory;

/**
 * @author  Stefan Meyer <meyer@leifos.com>
 * @ingroup ModulesCourse
 */
class ilMemberExportGUI
{
    private int $ref_id;
    private int $obj_id;
    private string $type;

    protected GlobalHttpState $http;
    protected Factory $refinery;

    protected ilCtrl $ctrl;
    protected ilGlobalTemplateInterface $tpl;
    protected ilLanguage $lng;
    protected ilToolbarGUI $toolbar;

    protected ?ilMemberExport $export = null;
    protected ?ilExportFieldsInfo $fields_info = null;
    protected ?ilFileSystemAbstractionStorage $fss_export = null;
    protected ilUserFormSettings $exportSettings;

    /**
     * Constructor
     * @access public
     * @param
     */
    public function __construct(int $a_ref_id)
    {
        global $DIC;

        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();

        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->toolbar = $DIC->toolbar();
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule('ps');
        $this->ref_id = $a_ref_id;
        $this->obj_id = ilObject::_lookupObjId($this->ref_id);
        $this->type = ilObject::_lookupType($this->obj_id);

        $this->fields_info = ilExportFieldsInfo::_getInstanceByType(ilObject::_lookupType($this->obj_id));
        $this->initFileSystemStorage();
    }

    public function executeCommand() : void
    {
        if (!ilPrivacySettings::getInstance()->checkExportAccess($this->ref_id)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('permission_denied'), true);
            $this->ctrl->returnToParent($this);
        }

        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        switch ($next_class) {
            default:
                if (!$cmd) {
                    $cmd = 'show';
                }
                $this->$cmd();
                break;
        }
    }

    protected function initSettingsForm(bool $a_is_excel = false) : ilPropertyFormGUI
    {
        // Check user selection
        $this->exportSettings = new ilUserFormSettings('memexp');

        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt('ps_export_settings'));

        if ($a_is_excel) {
            $form->addCommandButton('exportExcel', $this->lng->txt('ps_export_excel'));
        } else {
            $form->addCommandButton('export', $this->lng->txt('ps_perform_export'));
        }
        $form->addCommandButton('show', $this->lng->txt('cancel'));

        // roles
        $roles = new ilCheckboxGroupInputGUI($this->lng->txt('ps_user_selection'), 'export_members');
        $roles->addOption(new ilCheckboxOption($this->lng->txt('ps_export_admin'), 'admin'));
        if ($this->type === 'crs') {
            $roles->addOption(new ilCheckboxOption($this->lng->txt('ps_export_tutor'), 'tutor'));
        }
        $roles->addOption(new ilCheckboxOption($this->lng->txt('ps_export_member'), 'member'));
        $roles->addOption(new ilCheckboxOption($this->lng->txt('ps_export_sub'), 'subscribers'));
        $roles->addOption(new ilCheckboxOption($this->lng->txt('ps_export_wait'), 'waiting_list'));
        $form->addItem($roles);
        $current_roles = array();
        foreach (array('admin', 'tutor', 'member', 'subscribers', 'waiting_list') as $role) {
            if ($this->exportSettings->enabled($role)) {
                $current_roles[] = $role;
            }
        }
        $roles->setValue($current_roles);

        // user data
        $current_udata = array();
        $udata = new ilCheckboxGroupInputGUI($this->lng->txt('ps_export_user_data'), 'export_members');
        $form->addItem($udata);

        // standard fields
        $this->fields_info->sortExportFields();
        foreach ($this->fields_info->getFieldsInfo() as $field => $exportable) {
            if (!$exportable) {
                continue;
            }
            $udata->addOption(new ilCheckboxOption($this->lng->txt($field), $field));
            if ($this->exportSettings->enabled($field)) {
                $current_udata[] = $field;
            }
        }

        // udf
        foreach (ilUserDefinedFields::_getInstance()->getExportableFields($this->obj_id) as $field_id => $udf_data) {
            $field = 'udf_' . $field_id;
            $udata->addOption(new ilCheckboxOption($udf_data['field_name'], $field));
            if ($this->exportSettings->enabled($field)) {
                $current_udata[] = $field;
            }
        }

        $udata->setValue($current_udata);

        // course custom data
        $cdf_fields = ilCourseDefinedFieldDefinition::_getFields($this->obj_id);
        if (count($cdf_fields)) {
            $cdf = new ilCheckboxGroupInputGUI($this->lng->txt('ps_' . $this->type . '_user_fields'), 'export_members');
            $form->addItem($cdf);

            $current_cdf = array();
            foreach ($cdf_fields as $field_obj) {
                $field = 'cdf_' . $field_obj->getId();
                $cdf->addOption(new ilCheckboxOption($field_obj->getName(), $field));
                if ($this->exportSettings->enabled($field)) {
                    $current_cdf[] = $field;
                }
            }

            $cdf->setValue($current_cdf);
        }

        // consultation hours
        if (ilBookingEntry::hasObjectBookingEntries($this->obj_id, $GLOBALS['DIC']['ilUser']->getId())) {
            $this->lng->loadLanguageModule('dateplaner');
            $chours = new ilCheckboxInputGUI($this->lng->txt('cal_ch_field_ch'), 'export_members[]');
            $chours->setValue('consultation_hour');
            $chours->setChecked($this->exportSettings->enabled('consultation_hour'));
            $form->addItem($chours);
        }

        $grp_membr = new ilCheckboxInputGUI($this->lng->txt('crs_members_groups'), 'export_members[]');
        $grp_membr->setValue('group_memberships');
        $grp_membr->setChecked($this->exportSettings->enabled('group_memberships'));
        $form->addItem($grp_membr);
        return $form;
    }

    public function initCSV(ilPropertyFormGUI $a_form = null) : void
    {
        if (!$a_form) {
            $a_form = $this->initSettingsForm();
        }
        $this->tpl->setContent($a_form->getHTML());
    }

    public function initExcel(ilPropertyFormGUI $a_form = null) : void
    {
        if (!$a_form) {
            $a_form = $this->initSettingsForm(true);
        }
        $this->tpl->setContent($a_form->getHTML());
    }

    public function show() : void
    {
        $this->toolbar->addButton(
            $this->lng->txt('ps_perform_export'),
            $this->ctrl->getLinkTarget($this, "initCSV")
        );
        $this->toolbar->addButton(
            $this->lng->txt('ps_export_excel'),
            $this->ctrl->getLinkTarget($this, "initExcel")
        );

        $this->showFileList();
    }

    protected function handleIncoming() : void
    {
        $settings = [];
        $incoming = [];
        if ($this->http->wrapper()->post()->has('export_members')) {
            $incoming = $this->http->wrapper()->post()->retrieve(
                'export_members',
                $this->refinery->kindlyTo()->dictOf(
                    $this->refinery->kindlyTo()->string()
                )
            );
        }
        if (count($incoming)) {
            foreach ($incoming as $id) {
                $settings[$id] = true;
            }
        }

        // Save (form) settings
        $this->exportSettings = new ilUserFormSettings('memexp');
        $this->exportSettings->set($settings);
        $this->exportSettings->store();
    }

    /**
     * Export, create member export file and store it in data directory.
     */
    public function export() : void
    {
        $this->handleIncoming();

        $this->export = new ilMemberExport($this->ref_id);
        $this->export->create();

        $filename = time() . '_participant_export_csv_' . $this->obj_id . '.csv';
        $this->fss_export->addMemberExportFile($this->export->getCSVString(), $filename);
        $this->ctrl->redirect($this, 'show');
    }

    public function exportExcel() : void
    {
        $this->handleIncoming();

        $filename = time() . '_participant_export_xls_' . $this->obj_id;
        $this->fss_export->initMemberExportDirectory();
        $filepath = $this->fss_export->getMemberExportDirectory() . DIRECTORY_SEPARATOR . $filename;

        $this->export = new ilMemberExport($this->ref_id, ilMemberExport::EXPORT_EXCEL);
        $this->export->setFilename($filepath);
        $this->export->create();

        $this->ctrl->redirect($this, 'show');
    }

    public function deliverData() : void
    {
        foreach ($this->fss_export->getMemberExportFiles() as $file) {
            $member_export_filename = (string) ilSession::get('member_export_filename');
            if ($file['name'] === $member_export_filename) {
                $content = $this->fss_export->getMemberExportFile($member_export_filename);
                ilUtil::deliverData(
                    $content,
                    date('Y_m_d_H-i', $file['timest']) .
                    '_member_export_' .
                    $this->obj_id .
                    '.csv',
                    'text/csv'
                );
            }
        }
    }

    /**
     * Show file list of available export files
     */
    public function showFileList() : void
    {
        $tbl = new ilMemberExportFileTableGUI($this, 'show', $this->fss_export);
        $this->tpl->setContent($tbl->getHTML());
    }

    /**
     * Download export file
     */
    public function downloadExportFile() : void
    {
        $fl = '';
        if ($this->http->wrapper()->query()->has('fl')) {
            $fl = $this->http->wrapper()->query()->retrieve(
                'fl',
                $this->refinery->kindlyTo()->string()
            );
        }

        $hash = trim($fl);
        if (!$hash) {
            $this->ctrl->redirect($this, 'show');
        }

        foreach ($this->fss_export->getMemberExportFiles() as $file) {
            if (md5($file['name']) === $hash) {
                $contents = $this->fss_export->getMemberExportFile($file['timest'] . '_participant_export_' .
                    $file['type'] . '_' . $this->obj_id . '.' . $file['type']);

                // newer export files could be .xlsx
                if ($file['type'] === 'xls' && !$contents) {
                    $contents = $this->fss_export->getMemberExportFile($file['timest'] . '_participant_export_' .
                        $file['type'] . '_' . $this->obj_id . '.xlsx');
                    $file['type'] = 'xlsx';
                }

                switch ($file['type']) {
                    case 'xlsx':
                        ilUtil::deliverData(
                            $contents,
                            date('Y_m_d_H-i' . $file['timest']) . '_member_export_' . $this->obj_id . '.xlsx',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                        );

                    // no break
                    case 'xls':
                        ilUtil::deliverData(
                            $contents,
                            date('Y_m_d_H-i' . $file['timest']) . '_member_export_' . $this->obj_id . '.xls',
                            'application/vnd.ms-excel'
                        );

                    // no break
                    default:
                    case 'csv':
                        ilUtil::deliverData(
                            $contents,
                            date('Y_m_d_H-i' . $file['timest']) .
                            '_member_export_' .
                            $this->obj_id .
                            '.csv',
                            'text/csv'
                        );
                        break;
                }
            }
        }
    }

    /**
     * @return string[]
     */
    protected function initFileIdsFromPost() : array
    {
        $ids = [];
        if ($this->http->wrapper()->post()->has('id')) {
            $ids = $this->http->wrapper()->post()->retrieve(
                'id',
                $this->refinery->kindlyTo()->string()
            );
        }
        return $ids;
    }

    /**
     * Confirm deletion of export files
     */
    public function confirmDeleteExportFile() : void
    {
        $file_ids = $this->initFileIdsFromPost();
        if (!count($file_ids)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('ps_select_one'), true);
            $this->ctrl->redirect($this, 'show');
        }
        $confirmation_gui = new ilConfirmationGUI();
        $confirmation_gui->setFormAction($this->ctrl->getFormAction($this));
        $confirmation_gui->setHeaderText($this->lng->txt('info_delete_sure') /* .' '.$this->lng->txt('ps_delete_export_files') */);
        $confirmation_gui->setCancel($this->lng->txt('cancel'), 'show');
        $confirmation_gui->setConfirm($this->lng->txt('delete'), 'deleteExportFile');
        foreach ($this->fss_export->getMemberExportFiles() as $file) {
            if (!in_array(md5($file['name']), $file_ids)) {
                continue;
            }
            $confirmation_gui->addItem(
                "id[]",
                md5($file['name']),
                strtoupper($file['type']) . ' - ' .
                ilDatePresentation::formatDate(new ilDateTime($file['timest'], IL_CAL_UNIX))
            );
        }
        $this->tpl->setContent($confirmation_gui->getHTML());
    }

    /**
     * Delete member export files
     */
    public function deleteExportFile() : void
    {
        $file_ids = $this->initFileIdsFromPost();
        if (!count($file_ids)) {
            $this->ctrl->redirect($this, 'show');
        }
        foreach ($this->fss_export->getMemberExportFiles() as $file) {
            if (!in_array(md5($file['name']), $file_ids)) {
                continue;
            }

            $ret = $this->fss_export->deleteMemberExportFile($file['timest'] . '_participant_export_' .
                $file['type'] . '_' . $this->obj_id . '.' . $file['type']);

            //try xlsx if return is false and type is xls
            if ($file['type'] === "xls" && !$ret) {
                $this->fss_export->deleteMemberExportFile($file['timest'] . '_participant_export_' .
                    $file['type'] . '_' . $this->obj_id . '.' . "xlsx");
            }
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('ps_files_deleted'), true);
        $this->ctrl->redirect($this, 'show');
    }

    protected function initFileSystemStorage() : void
    {
        if ($this->type === 'crs') {
            $this->fss_export = new ilFSStorageCourse($this->obj_id);
        }
        if ($this->type === 'grp') {
            $this->fss_export = new ilFSStorageGroup($this->obj_id);
        }
    }
}
