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
* @ingroup ModulesQuestionPool
*/

class ilQuestionPoolPrintViewTableGUI extends ilTable2GUI
{	
	protected $outputmode;
	
	/**
	 * Constructor
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function __construct($a_parent_obj, $a_parent_cmd, $outputmode = '')
	{
		parent::__construct($a_parent_obj, $a_parent_cmd);

		global $lng, $ilCtrl;

		$this->lng = $lng;
		$this->ctrl = $ilCtrl;
		$this->outputmode = $outputmode;
	
		$this->setFormName('printviewform');
		$this->setStyle('table', 'fullwidth');
		$this->addColumn($this->lng->txt("title"),'title', '');
		$this->addColumn($this->lng->txt("description"),'description', '');
		$this->addColumn($this->lng->txt("author"),'author', '');
		$this->addColumn($this->lng->txt("question_type"),'ttype', '');
		$this->addColumn($this->lng->txt("create_date"),'created', '');
		$this->addColumn($this->lng->txt("last_update"),'updated', '');

		$this->addCommandButton('print', $this->lng->txt('print'), "javascript:window.print();return false;");

		$this->setRowTemplate("tpl.il_as_qpl_printview_row.html", "Modules/TestQuestionPool");

		$this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));
		$this->setDefaultOrderField("title");
		$this->setDefaultOrderDirection("asc");
		$this->setLimit(999);
		
		$this->enable('sort');
		$this->enable('header');
//		$this->disable('numinfo');
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
		$this->tpl->setVariable("TITLE", ilUtil::prepareFormOutput($data['title']));
		$this->tpl->setVariable("DESCRIPTION", ilUtil::prepareFormOutput($data['description']));
		$this->tpl->setVariable("AUTHOR", ilUtil::prepareFormOutput($data['author']));
		$this->tpl->setVariable("TYPE", ilUtil::prepareFormOutput($data['ttype']));
		$this->tpl->setVariable("CREATED", ilDatePresentation::formatDate(new ilDate($data['created'],IL_CAL_UNIX)));
		$this->tpl->setVariable("UPDATED", ilDatePresentation::formatDate(new ilDate($data['tstamp'],IL_CAL_UNIX)));
		if ((strcmp($this->outputmode, "detailed") == 0) || (strcmp($this->outputmode, "detailed_printview") == 0))
		{
			$this->tpl->setCurrentBlock("overview_row_detail");
			include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
			$question_gui = assQuestion::_instanciateQuestionGUI($data["question_id"]);
			if (strcmp($this->outputmode, "detailed") == 0)
			{
				$solutionoutput = $question_gui->getSolutionOutput($active_id = "", $pass = NULL, $graphicalOutput = FALSE, $result_output = FALSE, $show_question_only = FALSE, $show_feedback = FALSE, $show_correct_solution = true, $show_manual_scoring = false);
				if (strlen($solutionoutput) == 0) $solutionoutput = $question_gui->getPreview();
				$this->tpl->setVariable("DETAILS", $solutionoutput);
			}
			else
			{
				$this->tpl->setVariable("DETAILS", $question_gui->getPreview());
			}
			$this->tpl->parseCurrentBlock();
		}
	}
}
?>