<?php declare(strict_types=1);
/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @ingroup ServicesMail
 */
class ilMailAttachmentTableGUI extends ilTable2GUI
{
    public function __construct($a_parent_obj, $a_parent_cmd)
    {
        global $DIC;

        $this->setId('mail_attachments');

        $this->setDefaultOrderDirection('ASC');
        $this->setDefaultOrderField('filename');

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setTitle($this->lng->txt('attachment'));
        $this->setNoEntriesText($this->lng->txt('marked_entries'));

        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj, 'applyFilter'));

        $this->setSelectAllCheckbox('filename[]');

        $this->setRowTemplate('tpl.mail_attachment_row.html', 'Services/Mail');

        $this->addMultiCommand('saveAttachments', $this->lng->txt('adopt'));
        $this->addMultiCommand('deleteAttachments', $this->lng->txt('delete'));

        $this->addCommandButton('cancelSaveAttachments', $this->lng->txt('cancel'));

        $this->addColumn($this->lng->txt(''), '', '1px', true);
        $this->addColumn($this->lng->txt('mail_file_name'), 'filename');
        $this->addColumn($this->lng->txt('mail_file_size'), 'filesize');
        $this->addColumn($this->lng->txt('create_date'), 'filecreatedate');
        // Show all attachments on one page
        $this->setLimit(PHP_INT_MAX);
    }

    protected function fillRow(array $a_set) : void
    {
        /**
         * We need to encode this because of filenames with the following format: "anystring".txt (with ")
         */
        $this->tpl->setVariable(
            'VAL_CHECKBOX',
            ilLegacyFormElementsUtil::formCheckbox($a_set['checked'], 'filename[]', urlencode($a_set['filename']))
        );
        $this->tpl->setVariable(
            'VAL_FILENAME',
            $this->formatValue('filename', $a_set['filename'])
        );
        $this->tpl->setVariable(
            'VAL_FILESIZE',
            $this->formatValue('filesize', (string) $a_set['filesize'])
        );
        $this->tpl->setVariable(
            'VAL_FILECREATEDATE',
            $this->formatValue('filecreatedate', (string) $a_set['filecreatedate'])
        );
    }

    public function numericOrdering(string $a_field) : bool
    {
        return $a_field === 'filesize' || $a_field === 'filecreatedate';
    }

    protected function formatValue(string $column, string $value) : ?string
    {
        switch ($column) {
            case 'filecreatedate':
                return ilDatePresentation::formatDate(new ilDateTime($value, IL_CAL_UNIX));

            case 'filesize':
                return ilUtil::formatSize($value);

            default:
                return $value;
        }
    }
}
