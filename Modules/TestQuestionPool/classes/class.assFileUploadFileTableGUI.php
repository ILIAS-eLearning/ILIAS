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
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup ModulesGroup
*/

class assFileUploadFileTableGUI extends ilTable2GUI
{
	/**
	 * Constructor
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function __construct($a_parent_obj, $a_parent_cmd)
	{
	 	global $lng,$ilCtrl;
	 	
	 	$this->lng = $lng;
	 	$this->ctrl = $ilCtrl;
	 	
		parent::__construct($a_parent_obj, $a_parent_cmd);

		$this->setFormName('test_output');
		$this->setStyle('table', 'std');
		$this->addColumn('','f','1');
		$this->addColumn($this->lng->txt('filename'),'filename', 300);
		$this->addColumn($this->lng->txt('date'),'date', 250);
	 	
		$this->setPrefix('file');
		$this->setSelectAllCheckbox('file');
		
		$this->addCommandButton('gotoquestion', $this->lng->txt('delete'));
		$this->setRowTemplate("tpl.il_as_qpl_fileupload_file_row.html", "Modules/TestQuestionPool");
		
		$this->disable('sort');
		$this->enable('header');
		$this->enable('select_all');
	}
	
	/**
	 * fill row 
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function fillRow($a_set)
	{
		global $ilUser,$ilAccess;
		
		$this->tpl->setVariable('VAL_ID', $a_set['solution_id']);
		$this->tpl->setVariable('VAL_FILE', ilUtil::prepareFormOutput($a_set['value2']));
		$this->tpl->setVariable('VAL_DATE', ilDatePresentation::formatDate(new ilDateTime($a_set["timestamp14"],IL_CAL_TIMESTAMP)));
	}
	
}
?>