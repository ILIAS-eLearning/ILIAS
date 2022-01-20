<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * User export file table
 * Should be replaced in the future by standard export file handling
 *
 * @author Stefan Meyer <meyer@leifos.com>

 * @ingroup ServicesUser
 *
 */
class ilUserExportFileTableGUI extends \ilTable2GUI
{
    private const USER_EXPORT_TABLE_ID = 'usr_export_table';
    private const USER_EXPORT_FORM_NAME = 'usr_export_form';

    /**
     * @var null | \ilLogger
     */
    private $logger = null;

    public function __construct($a_parent_obj, $a_parent_cmd = "", $a_template_context = "")
    {
        global $DIC;

        $this->setId(self::USER_EXPORT_TABLE_ID);
        parent::__construct($a_parent_obj, $a_parent_cmd, $a_template_context);

        $this->logger = $DIC->logger()->user();
    }

    /**
     * Init table
     */
    public function init()
    {
        $this->lng->loadLanguageModule('usr');
        $this->setFormName(self::USER_EXPORT_FORM_NAME);
        $this->setFormAction($this->ctrl->getFormAction($this->getParentObject(), $this->getParentCmd()));

        $this->addColumn('', '');
        $this->addColumn($this->lng->txt('userfolder_export_file'), 'file', '50%');
        $this->addColumn($this->lng->txt('userfolder_export_file_size'), 'size_sort', '25%');
        $this->addColumn($this->lng->txt('date'), 'dt_sort', '25%');


        $this->setDefaultOrderField('dt');
        $this->setDefaultOrderDirection('desc');

        $this->setRowTemplate('tpl.usr_export_file_row.html', 'Services/User');
        $this->determineOffsetAndOrder();

        $this->addMultiCommand(
            'downloadExportFile',
            $this->lng->txt('download')
        );

        $this->addMultiCommand(
            'confirmDeleteExportFile',
            $this->lng->txt('delete')
        );
        $this->setSelectAllCheckbox('file');
        $this->enable('num_info');
    }

    /**
     * @param string $a_field
     * @return bool
     */
    public function numericOrdering(string $a_field) : bool
    {
        switch ($a_field) {
            case 'size_sort':
            case 'dt_sort':
                return true;
        }
        return false;
    }

    /**
     * @param array $a_set
     */
    protected function fillRow(array $a_set) : void
    {
        $this->tpl->setVariable('CHECKBOX_ID', $a_set['file']);
        $this->tpl->setVariable('TXT_FILENAME', $a_set['file']);
        $this->tpl->setVariable('TXT_SIZE', $a_set['size']);
        $this->tpl->setVariable('TXT_DATE', $a_set['date']);
    }

    /**
     *
     */
    public function parse(array $export_files)
    {
        $files = [];
        $counter = 0;
        foreach ($export_files as $num => $file_info) {
            $this->logger->dump($file_info, \ilLogLevel::NOTICE);

            $file_info_parts = explode('_', $file_info['filename']);
            $dt = $file_info_parts[0];

            $dt_obj = new \ilDateTime($dt, IL_CAL_UNIX);

            $files[$counter]['file'] = $file_info['filename'];
            $files[$counter]['size'] = $file_info['filesize'];
            $files[$counter]['size_sort'] = $file_info['filesize'];
            $files[$counter]['date'] = \ilDatePresentation::formatDate($dt_obj);
            $files[$counter]['dt_sort'] = $dt;
            ++$counter;
        }

        $this->logger->dump($files, \ilLogLevel::NOTICE);
        $this->setData($files);
    }
}
