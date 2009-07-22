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

class ilTestParticipantsTableGUI extends ilTable2GUI
{
	protected $anonymity;
	
	/**
	 * Constructor
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function __construct($a_parent_obj, $a_parent_cmd, $anonymity, $nrOfDatasets)
	{
		parent::__construct($a_parent_obj, $a_parent_cmd);

		global $lng, $ilCtrl;

		$this->lng = $lng;
		$this->ctrl = $ilCtrl;
		
		$this->anonymity = $anonymity;
		$this->setFormName('participantsForm');
		$this->setStyle('table', 'fullwidth');

		$this->addColumn('','f','1%');
		$this->addColumn($this->lng->txt("login"),'login', '');
		$this->addColumn($this->lng->txt("lastname"),'lastname', '');
		$this->addColumn($this->lng->txt("firstname"),'firstname', '');
		$this->addColumn($this->lng->txt("tst_started"),'started', '');
		$this->addColumn($this->lng->txt("tst_nr_of_tries_of_user"),'passes', '');
		$this->addColumn($this->lng->txt("tst_finished"),'finished', '');
		$this->addColumn($this->lng->txt("last_access"),'access', '');
		$this->addColumn('','results', '');
	
		$this->setTitle($this->lng->txt('tst_participating_users'));
		$this->setRowTemplate("tpl.il_as_tst_participants_row.html", "Modules/Test");

		if (!$this->anonymity)
		{
			$this->addMultiCommand('showPassOverview', $this->lng->txt('show_pass_overview'));
			$this->addMultiCommand('showUserAnswers', $this->lng->txt('show_user_answers'));
			$this->addMultiCommand('showDetailedResults', $this->lng->txt('show_detailed_results'));
		}
		$this->addMultiCommand('deleteSingleUserResults', $this->lng->txt('delete_user_data'));

		if ($nrOfDatasets)
		{
			$this->addCommandButton('deleteAllUserResults', $this->lng->txt('delete_all_user_data'));
		}

		$this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));

		if (!$this->anonymity)
		{
			$this->setDefaultOrderField("login");
		}
		else
		{
			$this->setDefaultOrderField("access");
		}
		$this->setDefaultOrderDirection("asc");
		$this->setPrefix('chbUser');
		$this->setSelectAllCheckbox('chbUser');
		
		$this->enable('header');
		$this->enable('sort');
		$this->enable('select_all');
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
		$finished = "<img border=\"0\" align=\"middle\" src=\"".ilUtil::getImagePath("icon_ok.gif") . "\" alt=\"".$this->lng->txt("ok")."\" />";
		$started  = "<img border=\"0\" align=\"middle\" src=\"".ilUtil::getImagePath("icon_ok.gif") . "\" alt=\"".$this->lng->txt("ok")."\" />" ;
		$passes = ($data['maxpass']) ? (($data['maxpass'] == 1) ? sprintf($this->lng->txt("pass_finished"), $data['maxpass']) : sprintf($this->lng->txt("passes_finished"), $data['maxpass'])) : '';
		$this->tpl->setVariable("USER_ID", $data['usr_id']);
		$this->tpl->setVariable("LOGIN", $data['login']);
		$this->tpl->setVariable("FIRSTNAME", $data['firstname']);
		$this->tpl->setVariable("LASTNAME", $data['lastname']);
		$this->tpl->setVariable("STARTED", ($data['started']) ? $started : '');
		$this->tpl->setVariable("PASSES", $passes);
		$this->tpl->setVariable("FINISHED", ($data['finished']) ? $finished : '');
		$this->tpl->setVariable("ACCESS", $data['access']);
		$this->tpl->setVariable("RESULT", $data['result']);
	}
}
?>