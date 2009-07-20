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

class ilListOfQuestionsTableGUI extends ilTable2GUI
{
	protected $show_points;
	
	/**
	 * Constructor
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function __construct($a_parent_obj, $a_parent_cmd, $show_points, $show_marker)
	{
		parent::__construct($a_parent_obj, $a_parent_cmd);

		global $lng, $ilCtrl;

		$this->lng = $lng;
		$this->ctrl = $ilCtrl;
		$this->show_points = $show_points;
		$this->show_marker = $show_marker;
		
		$this->setFormName('listofquestions');
		$this->setStyle('table', 'fullwidth');

		$this->addColumn($this->lng->txt("tst_qst_order"),'order', '');
		$this->addColumn($this->lng->txt("tst_question_title"),'title', '');
		$this->addColumn('' ,'postponed', '');
		if ($this->show_points)
		{
			$this->addColumn($this->lng->txt("tst_maximum_points"),'points', '');
		}
		$this->addColumn($this->lng->txt("worked_through"),'worked_through', '');
		if ($this->show_marker)
		{
			$this->addColumn($this->lng->txt("tst_question_marker"),'marked', '');
		}
	
		$this->setTitle($this->lng->txt('question_summary'));
	
		$this->setRowTemplate("tpl.il_as_tst_list_of_questions_row.html", "Modules/Test");

		$this->addCommandButton('backFromSummary', $this->lng->txt('back'));
		$this->addCommandButton('finishTest', $this->lng->txt('save_finish'));

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
		if ($this->show_points)
		{
			$this->tpl->setCurrentBlock('points');
			$this->tpl->setVariable("POINTS", $data['points']);
			$this->tpl->parseCurrentBlock();
		}
		if (strlen($data['description']))
		{
			$this->tpl->setCurrentBlock('description');
			$this->tpl->setVariable("DESCRIPTION", ilUtil::prepareFormOutput($data['description']));
			$this->tpl->parseCurrentBlock();
		}
		if ($this->show_marker)
		{
			if ($data['marked'])
			{
				$this->tpl->setCurrentBlock('marked_img');
				$this->tpl->setVariable("ALT_MARKED", $this->lng->txt("tst_question_marked"));
				$this->tpl->setVariable("HREF_MARKED", ilUtil::getImagePath("marked.png"));
				$this->tpl->parseCurrentBlock();
			}
			else
			{
				$this->tpl->touchBlock('marker');
			}
		}
		$this->tpl->setVariable("ORDER", $data['order']);
		$this->tpl->setVariable("TITLE", ilUtil::prepareFormOutput($data['title']));
		$this->tpl->setVariable("HREF", $data['href']);
		$this->tpl->setVariable("POSTPONED", $data['postponed']);
		if ($data["worked_through"])
		{
			$this->tpl->setVariable("HREF_WORKED_THROUGH", ilUtil::getImagePath("icon_ok.gif"));
			$this->tpl->setVariable("WORKED_THROUGH", $this->lng->txt("worked_through"));
		}
		else
		{
			$this->tpl->setVariable("HREF_WORKED_THROUGH", ilUtil::getImagePath("icon_not_ok.gif"));
			$this->tpl->setVariable("WORKED_THROUGH", $this->lng->txt("not_worked_through"));
		}
	}
}
?>