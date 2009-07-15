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

include_once('./Services/Table/classes/class.ilTable2GUI.php');

/**
*
* @author Helmut Schottmüller <ilias@aurealis.de>
* @version $Id$
*
* @ingroup ModulesTest
*/

class ilTestExportTableGUI extends ilTable2GUI
{
	protected $counter;
	protected $confirmdelete;
	
	/**
	 * Constructor
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function __construct($a_parent_obj, $a_parent_cmd, $confirmdelete = false)
	{
		parent::__construct($a_parent_obj, $a_parent_cmd);

		global $lng, $ilCtrl;

		$this->lng = $lng;
		$this->ctrl = $ilCtrl;
		$this->counter = 1;
		$this->confirmdelete = $confirmdelete;
		
		$this->setFormName('exporttest');
		$this->setStyle('table', 'fullwidth');

		if (!$confirmdelete)
		{
			$this->addColumn('','f','1%');
		}
		$this->addColumn($this->lng->txt("ass_file"),'file', '');
		$this->addColumn($this->lng->txt("ass_size"),'size', '');
		$this->addColumn($this->lng->txt("date"),'date', '');
	
		$this->setRowTemplate("tpl.il_as_tst_export_row.html", "Modules/Test");

		if ($confirmdelete)
		{
			$this->addCommandButton('deleteExportFile', $this->lng->txt('confirm'));
			$this->addCommandButton('cancelDeleteExportFile', $this->lng->txt('cancel'));
		}
		else
		{
			$this->addMultiCommand('downloadExportFile', $this->lng->txt('download'));
			$this->addMultiCommand('confirmDeleteExportFile', $this->lng->txt('delete'));

			$this->addCommandButton('createTestExport', $this->lng->txt('ass_create_export_file'));
			$this->addCommandButton('createTestResultsExport', $this->lng->txt('ass_create_export_test_results'));
		}

		$this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));

		$this->setDefaultOrderField("file");
		$this->setDefaultOrderDirection("asc");
		$this->setPrefix('file');
		$this->setSelectAllCheckbox('file');
		
		if ($confirmdelete)
		{
			$this->disable('sort');
			$this->disable('select_all');
		}
		else
		{
			$this->enable('sort');
			$this->enable('select_all');
		}
		$this->enable('header');
	}

	/**
	 * fill row 
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function fillRow($data)
	{
		if (!$this->confirmdelete)
		{
			$this->tpl->setCurrentBlock('checkbox');
			$this->tpl->setVariable('CB_ID', $this->counter++);
			$this->tpl->setVariable("CB_FILE", $data['file']);
			$this->tpl->parseCurrentBlock();
		}
		else
		{
			$this->tpl->setCurrentBlock('hidden');
			$this->tpl->setVariable('HIDDEN_FILE', $data["file"]);
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setVariable("ID", $this->counter++);
		$this->tpl->setVariable("FILE", $data['file']);
		$this->tpl->setVariable("SIZE", $data['size']);
		$this->tpl->setVariable("DATE", $data['date']);
	}
}
?>