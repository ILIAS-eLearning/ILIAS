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
 
/**
 * Table presentation of membership export files
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilMemberExportFileTableGUI extends ilTable2GUI
{
    public function __construct(object $a_parent_obj, string $a_parent_cmd, ilFileSystemAbstractionStorage $a_fss_export)
    {
        $this->setId('memexp');
        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setTitle($this->lng->txt('ps_export_files'));
        $this->addColumn('', '', '1');
        $this->addColumn($this->lng->txt('type'), 'type');
        $this->addColumn($this->lng->txt('ps_size'), 'size');
        $this->addColumn($this->lng->txt('date'), 'date');
        $this->addColumn($this->lng->txt('action'), '');

        $this->setDefaultOrderField('date');
        $this->setDefaultOrderDirection('desc');

        $this->setRowTemplate('tpl.mem_export_file_row.html', 'Services/Membership');
        $this->setFormAction($this->ctrl->getFormAction($this->getParentObject(), $this->getParentCmd()));
        $this->addMultiCommand('confirmDeleteExportFile', $this->lng->txt('delete'));
        $this->setSelectAllCheckbox('id[]');
        $this->getFiles($a_fss_export);
    }

    public function numericOrdering(string $a_field) : bool
    {
        return in_array($a_field, array('size', 'date'));
    }

    protected function getFiles(ilFileSystemAbstractionStorage $a_fss_export) : void
    {
        $data = [];
        foreach ($a_fss_export->getMemberExportFiles() as $exp_file) {
            $data[] = [
                'id' => md5($exp_file['name']),
                'type' => strtoupper($exp_file["type"]),
                'date' => $exp_file['timest'],
                'size' => $exp_file['size']
            ];
        }

        $this->setData($data);
    }

    protected function fillRow(array $a_set) : void
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];

        $this->tpl->setVariable('VAL_ID', $a_set['id']);
        $this->tpl->setVariable('VAL_TYPE', $a_set['type']);
        $this->tpl->setVariable('VAL_SIZE', ilUtil::formatSize($a_set['size']));
        $this->tpl->setVariable(
            'VAL_DATE',
            ilDatePresentation::formatDate(new ilDateTime($a_set['date'], IL_CAL_UNIX))
        );

        $ilCtrl->setParameter($this->getParentObject(), 'fl', $a_set['id']);
        $url = $ilCtrl->getLinkTarget($this->getParentObject(), 'downloadExportFile');
        $ilCtrl->setParameter($this->getParentObject(), 'fl', '');

        $this->tpl->setVariable('URL_DOWNLOAD', $url);
        $this->tpl->setVariable('TXT_DOWNLOAD', $this->lng->txt('download'));
    }
}
