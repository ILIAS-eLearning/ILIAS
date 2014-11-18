<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Table/classes/class.ilTable2GUI.php';
require_once 'Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php';

/**
 * @author	Björn Heyser <bheyser@databay.de>
 * @version	$Id$
 *
 * @package	Modules/Test
 *
 * @ilCtrl_Calls ilTestPassDetailsOverviewTableGUI: ilFormPropertyDispatchGUI
 */
class ilTestPassDetailsOverviewTableGUI extends ilTable2GUI
{
	private $singleAnswerScreenCmd = null;

	private $answerListAnchorEnabled = false;

	private $showHintCount = false;

	private $showSuggestedSolution = false;

	private $activeId = null;

	private $pass = null;
	
	private $is_pdf_generation_request = false;

	public function __construct(ilCtrl $ctrl, $parent, $cmd)
	{
		$this->ctrl = $ctrl;

		$this->setId('tst_pass_details_overview');
		$this->setPrefix('tst_pass_details_overview');

		$this->setDefaultOrderField('nr');
		$this->setDefaultOrderDirection('ASC');

		parent::__construct($parent, $cmd);

		$this->setFormName('tst_pass_details_overview');
		$this->setFormAction($this->ctrl->getFormAction($parent, $cmd));

		// Don't set any limit because of print/pdf views.
		$this->setLimit(PHP_INT_MAX);
		$this->setExternalSegmentation(true);

		$this->disable('linkbar');
		$this->disable('hits');
		$this->disable('sort');

		//$this->disable('numinfo');
		//$this->disable('numinfo_header');
		// KEEP THIS ENABLED, SINCE NO TABLE FILTER ARE PROVIDED OTHERWISE


		$this->setTitle($this->lng->txt('tst_pass_details_overview_table_title'));

		$this->setRowTemplate('tpl.il_as_tst_pass_details_overview_qst_row.html', 'Modules/Test');
	}

	/**
	 * @return ilTestPassDetailsOverviewTableGUI $this
	 */
	public function initColumns()
	{
		$this->setTitle(sprintf(
			$this->lng->txt('tst_pass_details_overview_table_title'), $this->getPass() + 1
		));

		$this->addColumn($this->lng->txt("tst_question_no"), '', '');
		$this->addColumn($this->lng->txt("question_id"), '', '');
		$this->addColumn($this->lng->txt("tst_question_title"), '', '');
		$this->addColumn($this->lng->txt("tst_maximum_points"), '', '');
		$this->addColumn($this->lng->txt("tst_reached_points"), '', '');

		if( $this->getShowHintCount() )
		{
			$this->addColumn($this->lng->txt("tst_question_hints_requested_hint_count_header"), '', '');
		}

		$this->addColumn($this->lng->txt("tst_percent_solved"), '', '');

		if( $this->getShowSuggestedSolution() )
		{
			$this->addColumn($this->lng->txt("solution_hint"), '', '');
		}

		if( $this->areActionListsRequired() )
		{
			$this->addColumn('', '', '1');
		}

		return $this;
	}

	/**
	 * @return ilTestPassDetailsOverviewTableGUI $this
	 */
	public function initFilter()
	{
		if( count($this->parent_obj->object->getResultFilterTaxIds()) )
		{
			require_once 'Services/Taxonomy/classes/class.ilTaxSelectInputGUI.php';

			foreach($this->parent_obj->object->getResultFilterTaxIds() as $taxId)
			{
				$postvar = "tax_$taxId";

				$inp = new ilTaxSelectInputGUI($taxId, $postvar, true);
				$this->addFilterItem($inp);
				$inp->readFromSession();
				$this->filter[$postvar] = $inp->getValue();
			}
		}

		return $this;
	}

	/**
	 * @return boolean
	 */
	public function isPdfGenerationRequest()
	{
		return $this->is_pdf_generation_request;
	}

	/**
	 * @param boolean $is_print_request
	 */
	public function setIsPdfGenerationRequest($is_print_request)
	{
		$this->is_pdf_generation_request = $is_print_request;
	}

	public function fillRow(array $row)
	{
		$this->ctrl->setParameter($this->parent_obj, 'evaluation', $row['qid']);

		if( $this->isQuestionTitleLinkPossible() )
		{
			$questionTitleLink = $this->getQuestionTitleLink($row['qid']);

			if( strlen($questionTitleLink) )
			{
				$this->tpl->setVariable('URL_QUESTION_TITLE', $questionTitleLink);

				$this->tpl->setCurrentBlock('title_link_end_tag');
				$this->tpl->touchBlock('title_link_end_tag');
				$this->tpl->parseCurrentBlock();
			}
		}

		if( $this->getShowHintCount() )
		{
			$this->tpl->setVariable('VALUE_HINT_COUNT', (int)$row['requested_hints']);
		}

		if( $this->getShowSuggestedSolution() )
		{
			$this->tpl->setVariable('SOLUTION_HINT', $row['solution']);
		}

		if( $this->areActionListsRequired() )
		{
			$this->tpl->setVariable('ACTIONS_MENU', $this->getActionList($row['qid']));
		}

		$this->tpl->setVariable('VALUE_QUESTION_TITLE', $row['title']);
		$this->tpl->setVariable('VALUE_QUESTION_ID', $row['qid']);
		$this->tpl->setVariable('VALUE_QUESTION_COUNTER', $row['nr']);
		$this->tpl->setVariable('VALUE_MAX_POINTS', $row['max']);
		$this->tpl->setVariable('VALUE_REACHED_POINTS', $row['reached']);
		$this->tpl->setVariable('VALUE_PERCENT_SOLVED', $row['percent']);

		$this->tpl->setVariable('ROW_ID', $this->getRowId($row['qid']));
	}

	private function getRowId($questionId)
	{
		return "pass_details_tbl_row_act_{$this->getActiveId()}_qst_{$questionId}";
	}

	private function getQuestionTitleLink($questionId)
	{
		if( $this->getAnswerListAnchorEnabled() )
		{
			return $this->getAnswerListAnchor($questionId);
		}

		if( strlen($this->getSingleAnswerScreenCmd()) )
		{
			return $this->ctrl->getLinkTarget($this->parent_obj, $this->getSingleAnswerScreenCmd());
		}

		return '';
	}

	private function isQuestionTitleLinkPossible()
	{
		if( $this->getAnswerListAnchorEnabled() )
		{
			return true;
		}

		if( strlen($this->getSingleAnswerScreenCmd()) )
		{
			return true;
		}

		return false;
	}

	private function areActionListsRequired()
	{
		if( $this->isPdfGenerationRequest() )
		{
			return false;
		}

		if( !$this->getAnswerListAnchorEnabled() )
		{
			return false;
		}

		if( !strlen($this->getSingleAnswerScreenCmd()) )
		{
			return false;
		}

		return true;
	}

	private function getActionList($questionId)
	{
		$aslGUI = new ilAdvancedSelectionListGUI();
		$aslGUI->setListTitle($this->lng->txt('tst_answer_details'));
		$aslGUI->setId("act{$this->getActiveId()}_qst{$questionId}");

		if( $this->getAnswerListAnchorEnabled() )
		{
			$aslGUI->addItem(
				$this->lng->txt('tst_list_answer_details'), 'tst_pass_details', $this->getAnswerListAnchor($questionId)
			);
		}

		if( strlen($this->getSingleAnswerScreenCmd()) )
		{
			$aslGUI->addItem(
				$this->lng->txt('tst_single_answer_details'), 'tst_pass_details',
				$this->ctrl->getLinkTarget($this->parent_obj, $this->getSingleAnswerScreenCmd())
			);
		}

		return $aslGUI->getHTML();
	}

	public function setSingleAnswerScreenCmd($singleAnswerScreenCmd)
	{
		$this->singleAnswerScreenCmd = $singleAnswerScreenCmd;
	}

	public function getSingleAnswerScreenCmd()
	{
		return $this->singleAnswerScreenCmd;
	}

	public function setAnswerListAnchorEnabled($answerListAnchorEnabled)
	{
		$this->answerListAnchorEnabled = $answerListAnchorEnabled;
	}

	public function getAnswerListAnchorEnabled()
	{
		return $this->answerListAnchorEnabled;
	}

	private function getAnswerListAnchor($questionId)
	{
		return "#detailed_answer_block_act_{$this->getActiveId()}_qst_{$questionId}";
	}

	public function setShowHintCount($showHintCount)
	{
		// Has to be called before column initialization
		$this->showHintCount = $showHintCount;
	}

	public function getShowHintCount()
	{
		return $this->showHintCount;
	}

	public function setShowSuggestedSolution($showSuggestedSolution)
	{
		$this->showSuggestedSolution = $showSuggestedSolution;
	}

	public function getShowSuggestedSolution()
	{
		return $this->showSuggestedSolution;
	}

	public function setActiveId($activeId)
	{
		$this->activeId = $activeId;
	}

	public function getActiveId()
	{
		return $this->activeId;
	}

	public function setPass($pass)
	{
		$this->pass = $pass;
	}

	public function getPass()
	{
		return $this->pass;
	}
}