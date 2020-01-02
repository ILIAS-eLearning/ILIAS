<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Table/classes/class.ilTable2GUI.php';

/**
 * Table presentation of membership export files
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @Id: $Id$
 */
class ilMemberExportFileTableGUI extends ilTable2GUI
{
    public function __construct($a_parent_obj, $a_parent_cmd, $a_fss_export)
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        
        $this->setId('memexp');
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        
        $this->setTitle($this->lng->txt('ps_export_files'));
        
        $this->addColumn('', '', 1);
        $this->addColumn($this->lng->txt('type'), 'type');
        $this->addColumn($this->lng->txt('ps_size'), 'size');
        $this->addColumn($this->lng->txt('date'), 'date');
        $this->addColumn($this->lng->txt('action'), '');
        
        $this->setDefaultOrderField('date');
        $this->setDefaultOrderDirection('desc');
                
        $this->setRowTemplate('tpl.mem_export_file_row.html', 'Services/Membership');
        $this->setFormAction($ilCtrl->getFormAction($this->getParentObject(), $this->getParentCmd()));
        $this->addMultiCommand('confirmDeleteExportFile', $this->lng->txt('delete'));
        
        $this->setSelectAllCheckbox('id[]');

        $this->getFiles($a_fss_export);
    }
    
    public function numericOrdering($a_field)
    {
        return in_array($a_field, array('size', 'date'));
    }
    
    protected function getFiles($a_fss_export)
    {
        $data = array();
        
        foreach ($a_fss_export->getMemberExportFiles() as $exp_file) {
            $data[] = array(
                'id' => md5($exp_file['name']),
                'type' => strtoupper($exp_file["type"]),
                'date' => $exp_file['timest'],
                'size' => $exp_file['size']
            );
        }
        
        $this->setData($data);
    }
        
    public function fillRow($a_set)
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        
        $this->tpl->setVariable('VAL_ID', $a_set['id']);
        $this->tpl->setVariable('VAL_TYPE', $a_set['type']);
        $this->tpl->setVariable('VAL_SIZE', ilUtil::formatSize($a_set['size']));
        $this->tpl->setVariable('VAL_DATE', ilDatePresentation::formatDate(new ilDateTime($a_set['date'], IL_CAL_UNIX)));
        
        $ilCtrl->setParameter($this->getParentObject(), 'fl', $a_set['id']);
        $url = $ilCtrl->getLinkTarget($this->getParentObject(), 'downloadExportFile');
        $ilCtrl->setParameter($this->getParentObject(), 'fl', '');
        
        $this->tpl->setVariable('URL_DOWNLOAD', $url);
        $this->tpl->setVariable('TXT_DOWNLOAD', $this->lng->txt('download'));
    }
}
