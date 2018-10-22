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
	 * @var assQuestion
	 */
	protected $question;
	
	public function __construct($a_parent_obj, $a_parent_cmd = "", $question)
	{
		global $DIC; /* @var ILIAS\DI\Container $DIC */
		
		$this->question = $question;
		
		$this->setId('tstAnswerStatistic');
		$this->setPrefix('tstAnswerStatistic');
		$this->setTitle($DIC->language()->txt('tst_corrections_answers_tbl'));
		
		$this->setRowTemplate('tpl.tst_corrections_answer_row.html', 'Modules/Test');
		
		parent::__construct($a_parent_obj, $a_parent_cmd, $a_template_context = '');
		
		$this->setDefaultOrderDirection('asc');
		$this->setDefaultOrderField('answer');
	}
	
	public function initColumns()
	{
		$this->addColumn('Answer', '');
		$this->addColumn('Frequency', '');
	}
	
	public function fillRow($data)
	{
		$this->tpl->setCurrentBlock('answer');
		$this->tpl->setVariable('ANSWER', $data['answer']);
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock('frequency');
		$this->tpl->setVariable('FREQUENCY', $data['frequency']);
		$this->tpl->parseCurrentBlock();
		
		if( strlen($data['actions']) )
		{
			$this->tpl->setCurrentBlock('actions');
			$this->tpl->setVariable('ACTIONS', $data['actions']);
			$this->tpl->parseCurrentBlock();
		}
	}
}