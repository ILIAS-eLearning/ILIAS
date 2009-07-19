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

class ilSurveyResultsCumulatedTableGUI extends ilTable2GUI
{
	/**
	 * Constructor
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function __construct($a_parent_obj, $a_parent_cmd, $detail)
	{
		parent::__construct($a_parent_obj, $a_parent_cmd);

		global $lng, $ilCtrl;

		$this->lng = $lng;
		$this->ctrl = $ilCtrl;
		$this->counter = 1;
		
		$this->setFormName('invitegroups');
		$this->setStyle('table', 'fullwidth');

		$this->addColumn($this->lng->txt("title"),'title', '');
		$this->addColumn($this->lng->txt("question"),'question', '');
		$this->addColumn($this->lng->txt("question_type"),'question_type', '');
		$this->addColumn($this->lng->txt("users_answered"),'users_answered', '');
		$this->addColumn($this->lng->txt("users_skipped"),'users_skipped', '');
		$this->addColumn($this->lng->txt("mode"),'mode', '');
		$this->addColumn($this->lng->txt("mode_nr_of_selections"),'mode_nr_of_selections', '');
		$this->addColumn($this->lng->txt("median"),'median', '');
		$this->addColumn($this->lng->txt("arithmetic_mean"),'arithmetic_mean', '');
	
		$this->setRowTemplate("tpl.il_svy_svy_results_cumulated_row.html", "Modules/Survey");

		$data = array(
			"excel" => $lng->txt('exp_type_excel'),
			"csv" => $lng->txt('exp_type_csv')
		);
		if ($detail)
		{
			$this->addSelectionButton('export_format', $data, 'exportDetailData', $this->lng->txt("export"));
		}
		else
		{
			$this->addSelectionButton('export_format', $data, 'exportData', $this->lng->txt("export"));
		}
		$this->addCommandButton('printEvaluation', $this->lng->txt('print'));

		$this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));

		$this->enable('header');
		$this->disable('sort');
		$this->disable('select_all');
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
		if (strlen($data['counter']))
		{
			$this->tpl->setCurrentBlock('counter');
			$this->tpl->setVariable("COUNTER", ilUtil::prepareFormOutput($data['counter']));
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setVariable("TITLE", ilUtil::prepareFormOutput($data['title']));
		$this->tpl->setVariable("QUESTION", ilUtil::prepareFormOutput($data['question']));
		$this->tpl->setVariable("QUESTION_TYPE", ilUtil::prepareFormOutput($data['question_type']));
		$this->tpl->setVariable("USERS_ANSWERED", ilUtil::prepareFormOutput($data['users_answered']));
		$this->tpl->setVariable("USERS_SKIPPED", ilUtil::prepareFormOutput($data['users_skipped']));
		$this->tpl->setVariable("MODE", ilUtil::prepareFormOutput($data['mode']));
		$this->tpl->setVariable("MODE_NR_OF_SELECTIONS", ilUtil::prepareFormOutput($data['mode_nr_of_selections']));
		$this->tpl->setVariable("MEDIAN", ilUtil::prepareFormOutput($data['median']));
		$this->tpl->setVariable("ARITHMETIC_MEAN", ilUtil::prepareFormOutput($data['arithmetic_mean']));
	}
}
?>