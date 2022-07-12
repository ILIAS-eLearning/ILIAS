<?php declare(strict_types=1);
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
    protected function fillRow(array $a_set) : void
    {
        $this->tpl->setVariable('VAL_ID', $a_set['id']);
        $this->tpl->setVariable('VAL_SIZE', sprintf("%.1f KB", $a_set['file_size'] / 1024));
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
    public function parseFiles(array $a_file_data) : void
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
