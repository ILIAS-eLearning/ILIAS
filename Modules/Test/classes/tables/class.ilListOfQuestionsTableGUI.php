<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */


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
	protected $show_marker;
	
	protected $obligationsFilter;
	
	/**
	 * Constructor
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function __construct($a_parent_obj, $a_parent_cmd, $show_points, $show_marker, $obligationsNotAnswered = false, $obligationsFilter = false)
	{
		parent::__construct($a_parent_obj, $a_parent_cmd);

		global $lng, $ilCtrl;

		$this->lng = $lng;
		$this->ctrl = $ilCtrl;
		$this->show_points = $show_points;
		$this->show_marker = $show_marker;
		$this->obligationsNotAnswered = $obligationsNotAnswered;
		$this->obligationsFilter = $obligationsFilter;
		
		$this->setFormName('listofquestions');
		$this->setStyle('table', 'fullwidth');

		$this->addColumn($this->lng->txt("tst_qst_order"),'order', '');
		$this->addColumn($this->lng->txt("tst_question_title"),'title', '');
		$this->addColumn($this->lng->txt("obligatory"),'obligatory', '');
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
	
		if( $obligationsFilter )
		{
			$this->setTitle($this->lng->txt('obligations_summary'));
		}
		else
		{
			$this->setTitle($this->lng->txt('question_summary'));
		}
	
		$this->setRowTemplate("tpl.il_as_tst_list_of_questions_row.html", "Modules/Test");

		$this->addCommandButton('backFromSummary', $this->lng->txt('back'));
		
		if( !$obligationsNotAnswered )
		{
			$this->addCommandButton('finishTest', $this->lng->txt('save_finish'));
		}
		
		$this->setLimit(999);

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

		// obligatory icon
		if( $data["obligatory"] )
		{
			$OBLIGATORY = "<img src=\"".ilUtil::getImagePath("obligatory.gif", "Modules/Test").
					"\" alt=\"".$this->lng->txt("question_obligatory").
					"\" title=\"".$this->lng->txt("question_obligatory")."\" />";
		}
		else $OBLIGATORY = '';

		
		$this->tpl->setVariable("QUESTION_OBLIGATORY", $OBLIGATORY);
		
		$this->tpl->setVariable("ORDER", $data['order']);
		$this->tpl->setVariable("TITLE", ilUtil::prepareFormOutput($data['title']));
		$this->tpl->setVariable("HREF", $data['href']);
		$this->tpl->setVariable("POSTPONED", $data['postponed']);
		if ($data["worked_through"])
		{
			$this->tpl->setVariable("WORKED_THROUGH", $this->lng->txt("yes"));
		}
		else
		{
			$this->tpl->setVariable("WORKED_THROUGH", '&nbsp;');
		}
	}
}