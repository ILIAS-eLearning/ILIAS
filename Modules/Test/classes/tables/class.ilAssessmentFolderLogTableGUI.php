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

class ilAssessmentFolderLogTableGUI extends ilTable2GUI
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
		parent::__construct($a_parent_obj, $a_parent_cmd);

		global $lng, $ilCtrl;

		$this->lng = $lng;
		$this->ctrl = $ilCtrl;
		$this->counter = 1;
		
		$this->setFormName('showlog');
		$this->setStyle('table', 'fullwidth');

		$this->addColumn($this->lng->txt("assessment_log_datetime"), 'date', '');
		$this->addColumn($this->lng->txt("user"), 'user', '');
		$this->addColumn($this->lng->txt("assessment_log_text"), 'message', '');
	
		$this->setRowTemplate("tpl.il_as_tst_assessment_log_row.html", "Modules/Test");

		$this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));

		$this->setDefaultOrderField("date");
		$this->setDefaultOrderDirection("asc");
		
		$this->enable('header');
		$this->enable('sort');
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
		$this->tpl->setVariable("DATE", ilDatePresentation::formatDate(new ilDate($data['tstamp'], IL_CAL_UNIX)));
		$user = ilObjUser::_lookupName($data["user_fi"]);
		$this->tpl->setVariable("USER", ilUtil::prepareFormOutput(trim($user["title"] . " " . $user["firstname"] . " " . $user["lastname"])));
		$title = "";
		if ($data["question_fi"] || $data["original_fi"])
		{
			include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
			$title = assQuestion::_getQuestionTitle($data["question_fi"]);
			if (strlen($title) == 0)
			{
				$title = assQuestion::_getQuestionTitle($data["original_fi"]);
			}
			$title = $this->lng->txt("assessment_log_question") . ": " . $title;
		}
		$this->tpl->setVariable("MESSAGE", ilUtil::prepareFormOutput($data['logtext']) . ((strlen($title)) ?  " (" . $title . ")" : ''));
	}
}
?>