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
 * Export table
 * @author Alexander Killing <killing@leifos.de>
 */
class ilExportTableGUI extends ilTable2GUI
{
    protected ilObject $obj;
    protected array $custom_columns = array();
    protected array $formats = array();

    /**
     * Constructor
     */
    public function __construct(object $a_parent_obj, string $a_parent_cmd, ilObject $a_exp_obj)
    {
        global $DIC;

        $this->obj = $a_exp_obj;

        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->setData($this->getExportFiles());
        $this->setTitle($this->lng->txt('exp_export_files'));

        $this->initColumns();

        $this->setDefaultOrderField('timestamp');
        $this->setDefaultOrderDirection('desc');

        $this->setEnableHeader(true);
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->setRowTemplate('tpl.export_table_row.html', 'Services/Export');
        $this->initMultiCommands();
    }

    protected function initColumns(): void
    {
        $this->addColumn($this->lng->txt(''), '', '1', true);
        $this->addColumn($this->lng->txt('type'), 'type');
        $this->addColumn($this->lng->txt('file'), 'file');
        $this->addColumn($this->lng->txt('size'), 'size');
        $this->addColumn($this->lng->txt('date'), 'timestamp');
    }

    protected function prepareOutput(): void
    {
        // append at last position (after custom columns)
        $this->addColumn($this->lng->txt('actions'));
    }

    /**
     *
     */
    protected function initMultiCommands(): void
    {
        $this->addMultiCommand('confirmDeletion', $this->lng->txt('delete'));
    }

    public function addCustomColumn(string $a_txt, object $a_obj, string $a_func): void
    {
        $this->addColumn($a_txt);
        $this->custom_columns[] = array('txt' => $a_txt,
                                        'obj' => $a_obj,
                                        'func' => $a_func
        );
    }

    public function addCustomMultiCommand(string $a_txt, string $a_cmd)
    {
        $this->addMultiCommand($a_cmd, $a_txt);
    }

    public function getCustomColumns(): array
    {
        return $this->custom_columns;
    }

    public function getExportFiles(): array
    {
        $types = array();
        foreach ($this->parent_obj->getFormats() as $f) {
            $types[] = $f['key'];
            $this->formats[$f['key']] = $f['txt'];
        }
        return ilExport::_getExportFiles(
            $this->obj->getId(),
            $types,
            $this->obj->getType()
        );
    }

    protected function fillRow(array $a_set): void
    {
        foreach ($this->getCustomColumns() as $c) {
            $this->tpl->setCurrentBlock('custom');
            $f = $c['func'];
            $this->tpl->setVariable('VAL_CUSTOM', $c['obj']->$f($a_set['type'], $a_set['file']) . ' ');
            $this->tpl->parseCurrentBlock();
        }

        $file_id = $this->getRowId($a_set);
        $this->tpl->setVariable('VAL_ID', $file_id);

        $type = (isset($this->formats[$a_set['type']]) && $this->formats[$a_set['type']] != "")
            ? $this->formats[$a_set['type']]
            : $a_set['type'];
        $this->tpl->setVariable('VAL_TYPE', $type);
        $this->tpl->setVariable('VAL_FILE', $a_set['file']);
        $this->tpl->setVariable('VAL_SIZE', ilUtil::formatSize($a_set['size']));
        $this->tpl->setVariable(
            'VAL_DATE',
            ilDatePresentation::formatDate(new ilDateTime($a_set['timestamp'], IL_CAL_UNIX))
        );

        $this->tpl->setVariable('TXT_DOWNLOAD', $this->lng->txt('download'));

        $this->ctrl->setParameter($this->getParentObject(), "file", $file_id);
        $url = $this->ctrl->getLinkTarget($this->getParentObject(), "download");
        $this->ctrl->setParameter($this->getParentObject(), "file", "");
        $this->tpl->setVariable('URL_DOWNLOAD', $url);
    }

    protected function getRowId(array $row): string
    {
        return $row['type'] . ':' . $row['file'];
    }
}
