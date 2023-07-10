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
 * @author  Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 * @ingroup ServicesAdvancedMetaData
 */
class ilAdvancedMDRecordExportFilesTableGUI extends ilTable2GUI
{
    /**
     * Constructor
     * @access public
     * @param object calling gui class
     * @param string parent command
     */
    public function __construct($a_parent_obj, $a_parent_cmd = '')
    {
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->addColumn('', 'f', '1');
        $this->addColumn($this->lng->txt('md_adv_records'), 'records', "33%");
        $this->addColumn($this->lng->txt('date'), 'date', "33%");
        $this->addColumn($this->lng->txt('filesize'), 'file_size', "33%");

        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.edit_files_row.html", "Services/AdvancedMetaData");
        $this->setDefaultOrderField("date");
        $this->setDefaultOrderDirection('desc');
    }

    /**
     * Fill row
     * @access public
     * @param
     */
    protected function fillRow(array $a_set): void
    {
        $this->tpl->setVariable('VAL_ID', $a_set['id']);
        $this->tpl->setVariable('VAL_SIZE', sprintf("%.1f KB", (string) ((int) $a_set['file_size'] / 1024)));
        $this->tpl->setVariable(
            'VAL_DATE',
            ilDatePresentation::formatDate(new ilDateTime($a_set['date'], IL_CAL_UNIX))
        );

        foreach ($a_set['record_arr'] as $title) {
            $this->tpl->setCurrentBlock('record_title');
            $this->tpl->setVariable('REC_TITLE', $title);
            $this->tpl->parseCurrentBlock();
        }
    }

    /**
     * parese files
     * @access public
     * @param
     */
    public function parseFiles(array $a_file_data): void
    {
        $defs_arr = [];
        foreach ($a_file_data as $id => $data) {
            $tmp_arr['id'] = $id;
            $tmp_arr['records'] = implode(', ', $data['name']);
            $tmp_arr['date'] = $data['date'];
            $tmp_arr['file_size'] = $data['size'] . ' ' . $this->lng->txt('bytes');
            $tmp_arr['record_arr'] = $data['name'];
            $defs_arr[] = $tmp_arr;
        }
        $this->setData($defs_arr);
    }
}
