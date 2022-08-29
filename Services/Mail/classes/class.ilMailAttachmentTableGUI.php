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

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @ingroup ServicesMail
 */
class ilMailAttachmentTableGUI extends ilTable2GUI
{
    public function __construct(?object $a_parent_obj, string $a_parent_cmd)
    {
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
        $this->setShowRowsSelector(false);
        $this->setLimit(PHP_INT_MAX);
    }

    protected function fillRow(array $a_set): void
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

    public function numericOrdering(string $a_field): bool
    {
        return $a_field === 'filesize' || $a_field === 'filecreatedate';
    }

    protected function formatValue(string $column, string $value): ?string
    {
        return match ($column) {
            'filecreatedate' => ilDatePresentation::formatDate(new ilDateTime($value, IL_CAL_UNIX)),
            'filesize' => ilUtil::formatSize((int) $value),
            default => $value,
        };
    }
}
