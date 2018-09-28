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
		$questionGUI = $this->getQuestion();
		
		$this->setCorrectionTabsContext($questionGUI, 'question');
		
		$this->DIC->ui()->mainTemplate()->setTitle($questionGUI->object->getTitle());
		$this->DIC->ui()->mainTemplate()->setContent('blubb');
	}
	
	protected function showSolution()
	{
		$questionGUI = $this->getQuestion();
		
		$this->setCorrectionTabsContext($questionGUI,'solution');
		
		$pageGUI = new ilAssQuestionPageGUI($questionGUI->object->getId());
		$pageGUI->setRenderPageContainer(false);
		$pageGUI->setEditPreview(true);
		$pageGUI->setEnabledTabs(false);
		
		$solutionHTML = $questionGUI->getSolutionOutput(
			0, null, false, false, true,
			false,true, false, true
		);
		
		$pageGUI->setQuestionHTML(array($questionGUI->object->getId() => $solutionHTML));
		$pageGUI->setPresentationTitle($questionGUI->object->getTitle());
		
		$tpl = new ilTemplate('tpl.tst_corrections_solution_presentation.html', true, true, 'Modules/Test');
		$tpl->setVariable('SOLUTION_PRESENTATION', $pageGUI->preview());
		
		$this->DIC->ui()->mainTemplate()->setTitle($questionGUI->object->getTitle());
		$this->DIC->ui()->mainTemplate()->setContent($tpl->get());
		$this->DIC->ui()->mainTemplate()->addCss('Modules/Test/templates/default/ta.css');
		
		$this->DIC->ui()->mainTemplate()->setCurrentBlock("ContentStyle");
		$stylesheet = ilObjStyleSheet::getContentStylePath(0);
		$this->DIC->ui()->mainTemplate()->setVariable("LOCATION_CONTENT_STYLESHEET", $stylesheet);
		$this->DIC->ui()->mainTemplate()->parseCurrentBlock();
		
		$this->DIC->ui()->mainTemplate()->setCurrentBlock("SyntaxStyle");
		$stylesheet = ilObjStyleSheet::getSyntaxStylePath();
		$this->DIC->ui()->mainTemplate()->setVariable("LOCATION_SYNTAX_STYLESHEET", $stylesheet);
		$this->DIC->ui()->mainTemplate()->parseCurrentBlock();
	}
	
	protected function showAnswerStatistic()
	{
		$questionGUI = $this->getQuestion();
		$solutions = $this->getSolutions($questionGUI->object);
		
		$this->setCorrectionTabsContext($questionGUI, 'answers');
		
		$table = $questionGUI->getAnswerFrequencyTableGUI(
			$this, 'showAnswerStatistic', $solutions, $this->getQuestionIndexParameter()
		);
		
		$this->DIC->ui()->mainTemplate()->setContent($table->getHTML());
		$this->DIC->ui()->mainTemplate()->setTitle($questionGUI->object->getTitle());
		$this->DIC->ui()->mainTemplate()->addCss('Modules/Test/templates/default/ta.css');
		
	}
	
	protected function setCorrectionTabsContext(assQuestionGUI $questionGUI, $activeTabId)
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
		
		if($questionGUI->isAnswerFreuqencyStatisticSupported())
		{
			$this->DIC->ctrl()->setParameter($this, 'qindex', 0);
			$this->DIC->tabs()->addTab('answers', 'Answer Statistic',
				$this->DIC->ctrl()->getLinkTarget($this, 'showAnswerStatistic')
			);
		}
		
		$this->DIC->tabs()->activateTab($activeTabId);
		
		if($questionGUI->isAnswerFreuqencyStatisticSupported() && $activeTabId == 'answers')
		{
			foreach($questionGUI->getSubQuestionsIndex() as $subIndex => $subQuestion)
			{
				$this->DIC->ctrl()->setParameter($this, 'qindex', $subIndex);
				
				$this->DIC->tabs()->addSubTab('subqst'.$subIndex, $subQuestion, 
					$this->DIC->ctrl()->getLinkTarget($this, 'showAnswerStatistic')
				);
				
				if($subIndex == $this->getQuestionIndexParameter())
				{
					$this->DIC->tabs()->activateSubTab('subqst'.$subIndex);
				}
			}
		}
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
	
	protected function getQuestionIndexParameter()
	{
		return (int)$_GET["qindex"];
	}
	
	protected function getSolutions(assQuestion $question)
	{
		$solutionRows = array();
		
		foreach($this->testOBJ->getParticipants() as $activeId => $participantData)
		{
			$passesSelector = new ilTestPassesSelector($this->DIC->database(), $this->testOBJ);
			$passesSelector->setActiveId($activeId);
			$passesSelector->loadLastFinishedPass();
			
			foreach($passesSelector->getClosedPasses() as $pass)
			{
				foreach($question->getSolutionValues($activeId, $pass) as $row)
				{
					$solutionRows[] = $row;
				}
			}
		}
		
		return $solutionRows;
	}
}