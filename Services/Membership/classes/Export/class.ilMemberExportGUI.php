<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

include_once('Services/PrivacySecurity/classes/class.ilExportFieldsInfo.php');
include_once('./Services/Membership/classes/Export/class.ilMemberExport.php');
include_once('Modules/Course/classes/class.ilFSStorageCourse.php');
include_once('Modules/Group/classes/class.ilFSStorageGroup.php');
include_once('Modules/Course/classes/Export/class.ilCourseDefinedFieldDefinition.php');
include_once('Services/User/classes/class.ilUserDefinedFields.php');
include_once('Services/User/classes/class.ilUserFormSettings.php');

/**
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*
* @ilCtrl_Calls ilMemberExportGUI:
* @ingroup ModulesCourse
*/
class ilMemberExportGUI
{
    private $ref_id;
    private $obj_id;
    private $type;
    private $ctrl;
    private $tpl;
    private $lng;
    
    private $fields_info;
    private $fss_export = null;
    /**
     * @var ilUserFormSettings
     */
    private $exportSettings;

    /**
     * Constructor
     *
     * @access public
     * @param
     *
     */
    public function __construct($a_ref_id)
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        $tpl = $DIC['tpl'];
        $lng = $DIC['lng'];
        $ilUser = $DIC['ilUser'];
        $ilObjDataCache = $DIC['ilObjDataCache'];
        
        $this->ctrl = $ilCtrl;
        $this->tpl = $tpl;
        $this->lng = $lng;
        $this->lng->loadLanguageModule('ps');
        $this->ref_id = $a_ref_id;
        $this->obj_id = $ilObjDataCache->lookupObjId($this->ref_id);
        $this->type = ilObject::_lookupType($this->obj_id);
        
        $this->fields_info = ilExportFieldsInfo::_getInstanceByType(ilObject::_lookupType($this->obj_id));
        $this->initFileSystemStorage();
    }
    
    /**
     * Execute Command
     *
     * @access public
     * @param
     *
     */
    public function executeCommand()
    {
        global $DIC;

        $ilAccess = $DIC['ilAccess'];
        $rbacsystem = $DIC['rbacsystem'];

        
        include_once('Services/PrivacySecurity/classes/class.ilPrivacySettings.php');
        if (!ilPrivacySettings::_getInstance()->checkExportAccess($this->ref_id)) {
            ilUtil::sendFailure($this->lng->txt('permission_denied'), true);
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
    
    
    //
    // export incl. settings
    //
    
    protected function initSettingsForm($a_is_excel = false)
    {
        // Check user selection
        $this->exportSettings = new ilUserFormSettings('memexp');
        
        include_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt('ps_export_settings'));
                
        if ((bool) $a_is_excel) {
            $form->addCommandButton('exportExcel', $this->lng->txt('ps_export_excel'));
        } else {
            $form->addCommandButton('export', $this->lng->txt('ps_perform_export'));
        }
        $form->addCommandButton('show', $this->lng->txt('cancel'));
        
        // roles
        $roles = new ilCheckboxGroupInputGUI($this->lng->txt('ps_user_selection'), 'export_members');
        $roles->addOption(new ilCheckboxOption($this->lng->txt('ps_export_admin'), 'admin'));
        if ($this->type == 'crs') {
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
        include_once './Services/Booking/classes/class.ilBookingEntry.php';
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
    
    public function initCSV(ilPropertyFormGUI $a_form = null)
    {
        if (!$a_form) {
            $a_form = $this->initSettingsForm();
        }
        $this->tpl->setContent($a_form->getHTML());
    }
    
    public function initExcel(ilPropertyFormGUI $a_form = null)
    {
        if (!$a_form) {
            $a_form = $this->initSettingsForm(true);
        }
        $this->tpl->setContent($a_form->getHTML());
    }
    
    /**
     * Show list of export files
     *
     * @access public
     *
     */
    public function show()
    {
        global $DIC;

        $ilToolbar = $DIC['ilToolbar'];
        
        $ilToolbar->addButton(
            $this->lng->txt('ps_perform_export'),
            $this->ctrl->getLinkTarget($this, "initCSV")
        );
        $ilToolbar->addButton(
            $this->lng->txt('ps_export_excel'),
            $this->ctrl->getLinkTarget($this, "initExcel")
        );
        
        $this->showFileList();
    }
    
    protected function handleIncoming()
    {
        $settings = array();
        $incoming = $_POST['export_members'];
        if (is_array($incoming)) {
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
     * Export Create member export file and store it in data directory.
     *
     * @access public
     *
     */
    public function export()
    {
        $this->handleIncoming();
        
        $this->export = new ilMemberExport($this->ref_id);
        $this->export->create();
        
        $filename = time() . '_participant_export_csv_' . $this->obj_id . '.csv';
        $this->fss_export->addMemberExportFile($this->export->getCSVString(), $filename);

        $this->ctrl->redirect($this, 'show');
    }
    
    public function exportExcel()
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
    
    /**
     * Deliver Data
     *
     * @access public
     * @param
     *
     */
    public function deliverData()
    {
        foreach ($this->fss_export->getMemberExportFiles() as $file) {
            if ($file['name'] == $_SESSION['member_export_filename']) {
                $content = $this->fss_export->getMemberExportFile($_SESSION['member_export_filename']);
                ilUtil::deliverData($content, date('Y_m_d_H-i', $file['timest']) .
                '_member_export_' .
                $this->obj_id .
                '.csv', 'text/csv');
            }
        }
    }
    
    /**
     * Show file list of available export files
     *
     * @access public
     *
     */
    public function showFileList()
    {
        include_once 'Services/Membership/classes/Export/class.ilMemberExportFileTableGUI.php';
        $tbl = new ilMemberExportFileTableGUI($this, 'show', $this->fss_export);
        $this->tpl->setContent($tbl->getHTML());
    }
    
    /**
     * Download export file
     *
     * @access public
     * @param
     *
     */
    public function downloadExportFile()
    {
        $hash = trim($_GET['fl']);
        if (!$hash) {
            $this->ctrl->redirect($this, 'show');
        }
        
        foreach ($this->fss_export->getMemberExportFiles() as $file) {
            if (md5($file['name']) == $hash) {
                $contents = $this->fss_export->getMemberExportFile($file['timest'] . '_participant_export_' .
                    $file['type'] . '_' . $this->obj_id . '.' . $file['type']);
                
                // newer export files could be .xlsx
                if ($file['type'] == 'xls' && !$contents) {
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
                        ilUtil::deliverData($contents, date('Y_m_d_H-i' . $file['timest']) .
                            '_member_export_' .
                            $this->obj_id .
                            '.csv', 'text/csv');
                        break;
                }
                return true;
            }
        }
    }
    
    /**
     * Confirm deletion of export files
     *
     * @access public
     * @param
     *
     */
    public function confirmDeleteExportFile()
    {
        if (!array_key_exists('id', $_POST) || !is_array($_POST['id']) || !count($_POST['id'])) {
            ilUtil::sendFailure($this->lng->txt('ps_select_one'), true);
            $this->ctrl->redirect($this, 'show');
        }
        
        include_once('./Services/Utilities/classes/class.ilConfirmationGUI.php');
        $confirmation_gui = new ilConfirmationGUI();
        $confirmation_gui->setFormAction($this->ctrl->getFormAction($this));
        $confirmation_gui->setHeaderText($this->lng->txt('info_delete_sure') /* .' '.$this->lng->txt('ps_delete_export_files') */);
        $confirmation_gui->setCancel($this->lng->txt('cancel'), 'show');
        $confirmation_gui->setConfirm($this->lng->txt('delete'), 'deleteExportFile');
                
        $counter = 0;
        foreach ($this->fss_export->getMemberExportFiles() as $file) {
            if (!in_array(md5($file['name']), $_POST['id'])) {
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
     *
     * @access public
     * @param
     *
     */
    public function deleteExportFile()
    {
        if (!count($_POST['id'])) {
            $this->ctrl->redirect($this, 'show');
        }
        
        $counter = 0;
        foreach ($this->fss_export->getMemberExportFiles() as $file) {
            if (!in_array(md5($file['name']), $_POST['id'])) {
                continue;
            }

            $ret = $this->fss_export->deleteMemberExportFile($file['timest'] . '_participant_export_' .
                $file['type'] . '_' . $this->obj_id . '.' . $file['type']);

            //try xlsx if return is false and type is xls
            if ($file['type'] == "xls" && !$ret) {
                $this->fss_export->deleteMemberExportFile($file['timest'] . '_participant_export_' .
                    $file['type'] . '_' . $this->obj_id . '.' . "xlsx");
            }
        }
        
        ilUtil::sendSuccess($this->lng->txt('ps_files_deleted'), true);
        $this->ctrl->redirect($this, 'show');
    }
    
    
    /**
     * Init file object
     * @return
     */
    protected function initFileSystemStorage()
    {
        if ($this->type == 'crs') {
            $this->fss_export = new ilFSStorageCourse($this->obj_id);
        }
        if ($this->type == 'grp') {
            $this->fss_export = new ilFSStorageGroup($this->obj_id);
        }
    }
}
