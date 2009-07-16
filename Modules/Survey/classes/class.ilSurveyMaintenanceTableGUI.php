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
* @ingroup ModulesSurvey
*/

class ilSurveyMaintenanceTableGUI extends ilTable2GUI
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
		
		$this->setFormName('maintenanceform');
		$this->setStyle('table', 'fullwidth');

		if (!$confirmdelete)
		{
			$this->addColumn('','f','1%');
		}
		$this->addColumn($this->lng->txt("name"),'name', '');
		$this->addColumn($this->lng->txt("login"),'login', '');
		$this->addColumn($this->lng->txt("last_access"),'lastaccess', '');
	
		$this->setRowTemplate("tpl.il_svy_svy_maintenance_row.html", "Modules/Survey");

		if ($confirmdelete)
		{
			$this->addCommandButton('confirmDeleteSelectedUserData', $this->lng->txt('confirm'));
			$this->addCommandButton('cancelDeleteSelectedUserData', $this->lng->txt('cancel'));
		}
		else
		{
			$this->addMultiCommand('deleteSingleUserResults', $this->lng->txt('delete_user_data'));
			$this->addCommandButton('deleteAllUserData', $this->lng->txt('svy_delete_all_user_data'));
		}

		$this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));

		$this->setDefaultOrderField("name");
		$this->setDefaultOrderDirection("asc");
		$this->setPrefix('chbUser');
		$this->setSelectAllCheckbox('chbUser');
		
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
			$this->tpl->setVariable("CB_USER_ID", $data['id']);
			$this->tpl->parseCurrentBlock();
		}
		else
		{
			$this->tpl->setCurrentBlock('hidden');
			$this->tpl->setVariable('HIDDEN_USER_ID', $data["id"]);
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setVariable("USER_ID", $data["id"]);
		$this->tpl->setVariable("VALUE_USER_NAME", $data['name']);
		$this->tpl->setVariable("VALUE_USER_LOGIN", $data['login']);
		$this->tpl->setVariable("LAST_ACCESS", $data['last_access']);
	}
}
?>