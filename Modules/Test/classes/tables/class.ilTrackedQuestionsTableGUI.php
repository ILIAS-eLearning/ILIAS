<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Table/classes/class.ilTable2GUI.php';

/**
 *
 * @author	Björn Heyser <bheyser@databay.de>
 * @version	$Id$
 *
 * @package	Modules/Test
 */
class ilTrackedQuestionsTableGUI extends ilTable2GUI
{
	protected $show_postponed;
	protected $show_marker;
	
	/**
	 * Constructor
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function __construct($a_parent_obj, $a_parent_cmd, $show_postponed, $show_marker)
	{
		parent::__construct($a_parent_obj, $a_parent_cmd);

		global $lng, $ilCtrl;

		$this->lng = $lng;
		$this->ctrl = $ilCtrl;
		$this->show_postponed = $show_postponed;
		$this->show_marker = $show_marker;
		
		$this->setFormName('trackedquestions');
		$this->setStyle('table', 'fullwidth');

		$this->addColumn($this->lng->txt("tst_question_title"),'title', '');

		if ($this->show_postponed)
		{
			$this->addColumn($this->lng->txt("postpone_status") ,'postponed', '');
		}

		if ($this->show_marker)
		{
			$this->addColumn($this->lng->txt("tst_question_marker"),'marked', '');
		}
	
		$this->setTitle($this->lng->txt('tst_tracked_question_list'));
	
		$this->setRowTemplate("tpl.il_as_tst_tracked_questions_row.html", "Modules/Test");

		$this->addCommandButton('showQuestion', $this->lng->txt('back'));
		
		$this->setLimit(PHP_INT_MAX);

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
		if (strlen($data['description']))
		{
			$this->tpl->setCurrentBlock('description');
			$this->tpl->setVariable("DESCRIPTION", ilUtil::prepareFormOutput($data['description']));
			$this->tpl->parseCurrentBlock();
		}
		
		if( $this->show_postponed )
		{
			if($data['postponed'])
			{
				$this->tpl->setCurrentBlock('postponed');
				$this->tpl->setVariable('POSTPONED', $this->lng->txt('postponed'));
				$this->tpl->parseCurrentBlock();
			}
			else
			{
				$this->tpl->setCurrentBlock('postponed');
				$this->tpl->touchBlock('postponed');
				$this->tpl->parseCurrentBlock();
			}
		}
		
		if ($this->show_marker)
		{
			if ($data['marked'])
			{
				$this->tpl->setCurrentBlock('marked_img');
				$this->tpl->setVariable("HREF_MARKED", ilUtil::img('./templates/default/images/marked.svg', $this->lng->txt("tst_question_marked"), '24px', '24px'));
				$this->tpl->parseCurrentBlock();
				
				$this->tpl->setCurrentBlock('marker');
				$this->tpl->parseCurrentBlock();
			}
			else
			{
				$this->tpl->setCurrentBlock('marker');
				$this->tpl->touchBlock('marker');
				$this->tpl->parseCurrentBlock();
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
	}
	
	private function getPostponedLabel($isPostponed)
	{
		if(!$isPostponed)
		{
			return '';
		}
		
		return ;
	}
}
?>