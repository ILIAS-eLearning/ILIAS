<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestCorrectionsGUI
 *
 * @author    Björn Heyser <info@bjoernheyser.de>
 * @version    $Id$
 *
 * @package    Modules/Test
 */
class ilTestCorrectionsGUI
{
	/**
	 * @var \ILIAS\DI\Container
	 */
	protected $DIC;
	
	/**
	 * @var ilObjTest
	 */
	protected $testOBJ;
	
	/**
	 * ilTestCorrectionsGUI constructor.
	 * @param \ILIAS\DI\Container $DIC
	 * @param ilObjTest $testOBJ
	 */
	public function __construct(\ILIAS\DI\Container $DIC, ilObjTest $testOBJ)
	{
		$this->DIC = $DIC;
		$this->testOBJ = $testOBJ;
		$this->object = $testOBJ;
	}
	
	public function executeCommand()
	{
		if ($_GET["eqid"] && $_GET["eqpl"])
		{
			$this->DIC->ctrl()->setParameter($this, 'qid', $_GET["eqid"]);
			$this->DIC->ctrl()->redirect($this, 'showQuestion');
		}
		
		$this->DIC->ctrl()->saveParameter($this, 'qid');
		
		switch($this->DIC->ctrl()->getNextClass($this))
		{
			default:
				
				$command = $this->DIC->ctrl()->getCmd('showQuestionList');
				$this->{$command}();
		}
	}
	
	protected function showQuestionList()
	{
		$this->DIC->tabs()->activateTab(ilTestTabsManager::TAB_ID_CORRECTION."2");
		
		$table_gui = new ilTestQuestionsTableGUI(
			$this,'showQuestionList', true, false, 0
		);
		
		$table_gui->setData(
			$this->testOBJ->getTestQuestions()
		);
		
		$table_gui->clearActionButtons();
		$table_gui->clearCommandButtons();
		$table_gui->setRowTemplate('tpl.il_as_tst_adjust_questions_row.html', 'Modules/Test');
		
		$this->DIC->ui()->mainTemplate()->setContent($table_gui->getHTML());
	}
	
	protected function showQuestion()
	{
		$this->setCorrectionTabsContext('question');
	}
	
	protected function showSolution()
	{
		$this->setCorrectionTabsContext('solution');
		
		$questionGUI = $this->getQuestion();
		
		$pageGUI = new ilAssQuestionPageGUI($questionGUI->object->getId());
		$pageGUI->setRenderPageContainer(false);
		$pageGUI->setEditPreview(true);
		$pageGUI->setEnabledTabs(false);
		
		$solutionHTML = $questionGUI->getSolutionOutput(
			0, null, false, false, false,
			false,true, false, true
		);
		
		$pageGUI->setQuestionHTML(array($questionGUI->object->getId() => $solutionHTML));
		$pageGUI->setPresentationTitle($questionGUI->object->getTitle());
		
		$this->DIC->ui()->mainTemplate()->addCss('Modules/Test/templates/default/ta.css');
		$this->DIC->ui()->mainTemplate()->setContent($pageGUI->preview());
	}
	
	protected function showAnswerStatistic()
	{
		$this->setCorrectionTabsContext('answers');
	}
	
	protected function setCorrectionTabsContext($activeTabId)
	{
		$this->DIC->tabs()->clearTargets();
		$this->DIC->tabs()->clearSubTabs();
		
		$this->DIC->tabs()->setBackTarget('Back',
			$this->DIC->ctrl()->getLinkTarget($this, 'showQuestionList'));
		
		$this->DIC->tabs()->addTab('question', 'Question',
			$this->DIC->ctrl()->getLinkTarget($this, 'showQuestion')
		);
		
		$this->DIC->tabs()->addTab('solution', 'Solution',
			$this->DIC->ctrl()->getLinkTarget($this, 'showSolution')
		);
		
		$this->DIC->tabs()->addTab('answers', 'Answer Statistic',
			$this->DIC->ctrl()->getLinkTarget($this, 'showAnswerStatistic')
		);
		
		$this->DIC->tabs()->activateTab($activeTabId);
	}
	
	/**
	 * @return assQuestionGUI
	 */
	protected function getQuestion()
	{
		$question = assQuestion::instantiateQuestionGUI((int)$_GET["qid"]);
		$question->object->setObjId($this->testOBJ->getId());
		
		return $question;
	}
}