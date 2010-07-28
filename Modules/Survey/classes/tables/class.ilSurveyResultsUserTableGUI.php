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
* @version $Id: class.ilSurveyResultsCumulatedTableGUI.php 23310 2010-03-21 23:41:39Z hschottm $
*
* @ingroup ModulesSurvey
*/

class ilSurveyResultsUserTableGUI extends ilTable2GUI
{
	private $is_anonymized;
	
	/**
	 * Constructor
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function __construct($a_parent_obj, $a_parent_cmd, $is_anonymized)
	{
		$this->setId("svy_usr");
		parent::__construct($a_parent_obj, $a_parent_cmd);

		global $lng, $ilCtrl;

		$this->is_anonymized = $is_anonymized;
		$this->lng = $lng;
		$this->ctrl = $ilCtrl;
		$this->counter = 1;
		
		$this->setFormName('invitegroups');
		$this->setStyle('table', 'fullwidth');

		$this->addColumn($this->lng->txt("username"),'username', '');
		if (!$is_anonymized)
		{
			$this->addColumn($this->lng->txt("gender"),'gender', '');
		}
		$this->addColumn($this->lng->txt("question"),'question', '');
		$this->addColumn($this->lng->txt("results"),'results', '');
	
		$this->setRowTemplate("tpl.il_svy_svy_results_user_row.html", "Modules/Survey");

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
		if (!$this->is_anonymized)
		{
			$this->tpl->setCurrentBlock('gender');
			$this->tpl->setVariable("GENDER", $data['gender']);
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setVariable("USERNAME", $data['username']);
		$this->tpl->setVariable("QUESTION", print_r($data['question'], true));
		$this->tpl->setVariable("RESULTS", print_r($data['results'], true));
	}
}
?>