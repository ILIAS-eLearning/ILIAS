<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilAnswerFrequencyStatisticTableGUI
 *
 * @author    Björn Heyser <info@bjoernheyser.de>
 * @version    $Id$
 *
 * @package    Modules/TestQuestionPool
 */
class ilAnswerFrequencyStatisticTableGUI extends ilTable2GUI
{
	/**
	 * @var \ILIAS\DI\Container
	 */
	protected $DIC;
	
	/**
	 * @var assQuestion
	 */
	protected $question;
	
	/**
	 * @var bool
	 */
	protected $actionsColumnEnabled = false;
	
	/**
	 * ilAnswerFrequencyStatisticTableGUI constructor.
	 * @param object $a_parent_obj
	 * @param string $a_parent_cmd
	 * @param string $question
	 */
	public function __construct($a_parent_obj, $a_parent_cmd = "", $question)
	{
		global $DIC; /* @var ILIAS\DI\Container $this->DIC */
		
		$this->DIC = $DIC;
		
		$this->question = $question;
		
		$this->setId('tstAnswerStatistic');
		$this->setPrefix('tstAnswerStatistic');
		$this->setTitle($this->DIC->language()->txt('tst_corrections_answers_tbl'));
		
		$this->setRowTemplate('tpl.tst_corrections_answer_row.html', 'Modules/Test');
		
		parent::__construct($a_parent_obj, $a_parent_cmd, $a_template_context = '');
		
		$this->setDefaultOrderDirection('asc');
		$this->setDefaultOrderField('answer');
	}
	
	/**
	 * @return bool
	 */
	public function isActionsColumnEnabled(): bool
	{
		return $this->actionsColumnEnabled;
	}
	
	/**
	 * @param bool $actionsColumnEnabled
	 */
	public function setActionsColumnEnabled(bool $actionsColumnEnabled)
	{
		$this->actionsColumnEnabled = $actionsColumnEnabled;
	}
	
	public function initColumns()
	{
		$this->addColumn($this->DIC->language()->txt('tst_corr_answ_stat_tbl_header_answer'), '');
		$this->addColumn($this->DIC->language()->txt('tst_corr_answ_stat_tbl_header_frequency'), '');
		
		foreach($this->getData() as $row)
		{
			if( isset($row['addable']) )
			{
				$this->setActionsColumnEnabled(true);
				$this->addColumn('', '', '1%');
				break;
			}
		}
	}
	
	public function fillRow($data)
	{
		$this->tpl->setCurrentBlock('answer');
		$this->tpl->setVariable('ANSWER', $data['answer']);
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock('frequency');
		$this->tpl->setVariable('FREQUENCY', $data['frequency']);
		$this->tpl->parseCurrentBlock();
		
		if( $this->isActionsColumnEnabled() )
		{
			if( isset($data['addable']) )
			{
				$this->tpl->setCurrentBlock('actions');
				$this->tpl->setVariable('ACTIONS', $this->buildAddAnswerAction($data));
				$this->tpl->parseCurrentBlock();
			}
			else
			{
				$this->tpl->setCurrentBlock('actions');
				$this->tpl->touchBlock('actions');
				$this->tpl->parseCurrentBlock();
			}
		}
	}
	
	protected function buildAddAnswerAction($data)
	{
		$l = $this->DIC->language();
		$c = $this->DIC->ctrl();
		$f = $this->DIC->ui()->factory();
		$r = $this->DIC->ui()->renderer();
		
		$inputs = array(
			$f->input()->field()->numeric(
				$l->txt('tst_corr_points_field'), $l->txt('tst_corr_points_field_desc') 
			)
		);
		
		$form = $f->input()->container()->form()->standard(
			'', $inputs
		);
		
		$modal = $f->modal()->roundtrip(
			$l->txt('tst_corr_add_as_answer_btn'), $form
		);
		
		$button = $f->button()->standard(
			$l->txt('tst_corr_add_as_answer_btn'), $modal->getShowSignal()
		);
		
		return $r->render($modal) . $r->render($button);
	}
}