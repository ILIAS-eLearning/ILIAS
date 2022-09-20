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

/**
 * User export file table
 * Should be replaced in the future by standard export file handling
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilUserExportFileTableGUI extends \ilTable2GUI
{
    private const USER_EXPORT_TABLE_ID = 'usr_export_table';
    private const USER_EXPORT_FORM_NAME = 'usr_export_form';

    private ?ilLogger $logger = null;

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd = "",
        $a_template_context = ""
    ) {
        global $DIC;

        $this->setId(self::USER_EXPORT_TABLE_ID);
        parent::__construct($a_parent_obj, $a_parent_cmd, $a_template_context);

        $this->logger = $DIC->logger()->user();
    }

    public function init(): void
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

    public function numericOrdering(string $a_field): bool
    {
        switch ($a_field) {
            case 'size_sort':
            case 'dt_sort':
                return true;
        }
        return false;
    }

    /**
     * @param array<string,mixed> $a_set
     */
    protected function fillRow(array $a_set): void // Missing array type.
    {
        $this->tpl->setVariable('CHECKBOX_ID', $a_set['file']);
        $this->tpl->setVariable('TXT_FILENAME', $a_set['file']);
        $this->tpl->setVariable('TXT_SIZE', $a_set['size']);
        $this->tpl->setVariable('TXT_DATE', $a_set['date']);
    }

    public function parse(array $export_files): void // Missing array type.
    {
        $files = [];
        $counter = 0;
        foreach ($export_files as $file_info) {
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
