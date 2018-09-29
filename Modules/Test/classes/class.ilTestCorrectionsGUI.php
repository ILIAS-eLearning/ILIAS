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
		
		$table_gui->setData($this->getQuestions());
		
		$table_gui->clearActionButtons();
		$table_gui->clearCommandButtons();
		$table_gui->setRowTemplate('tpl.il_as_tst_adjust_questions_row.html', 'Modules/Test');
		
		$this->DIC->ui()->mainTemplate()->setContent($table_gui->getHTML());
	}
	
	protected function showQuestion()
	{
		$questionGUI = $this->getQuestion((int)$_GET['qid']);
		
		$this->setCorrectionTabsContext($questionGUI, 'question');
		
		$form = new ilPropertyFormGUI();
		$form->setFormAction( $this->DIC->ctrl()->getFormAction($this) );
		$form->setId('tst_question_correction');
		
		$form->setTitle(
			$questionGUI->object->getTitle().'<br /><small>('.$questionGUI->outQuestionType().')</small>'
		);
		
		$hiddenQid = new ilHiddenInputGUI('qid');
		$hiddenQid->setValue($questionGUI->object->getId());
		$form->addItem($hiddenQid);
		
		$questionGUI->populateCorrectionsFormProperties($form);
		
		$manscoring_section = new ilFormSectionHeaderGUI();
		$manscoring_section->setTitle($this->DIC->language()->txt('manscoring'));
		$form->addItem($manscoring_section);
		
		$manscoringPreservation = new ilCheckboxInputGUI($this->DIC->language()->txt('preserve_manscoring'), 'preserve_manscoring');
		$manscoringPreservation->setChecked(true);
		$manscoringPreservation->setInfo($this->DIC->language()->txt('preserve_manscoring_info'));
		$form->addItem($manscoringPreservation);
		
		$form->addCommandButton('saveQuestion', 'Save');
		
		$this->DIC->ui()->mainTemplate()->setContent($form->getHTML());
		$this->DIC->ui()->mainTemplate()->setTitle($questionGUI->object->getTitle());
	}
	
	protected function showSolution()
	{
		$questionGUI = $this->getQuestion((int)$_GET['qid']);
		
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
		$questionGUI = $this->getQuestion((int)$_GET['qid']);
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
	protected function getQuestion($qId)
	{
		$question = assQuestion::instantiateQuestionGUI($qId);
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
	
	protected function getQuestions()
	{
		$questions = array();
		
		foreach($this->testOBJ->getTestQuestions() as $questionData)
		{
			$questionGUI = $this->getQuestion($questionData['question_id']);
			
			if( !$this->supportsAdjustment($questionGUI) )
			{
				continue;
			}
			
			if( !$this->allowedInAdjustment($questionGUI) )
			{
				continue;
			}
			
			$questions[] = $questionData;
		}
		
		return $questions;
	}
	
	/**
	 * Returns if the given question object support scoring adjustment.
	 *
	 * @param $question_object assQuestionGUI
	 *
	 * @return bool True, if relevant interfaces are implemented to support scoring adjustment.
	 */
	protected function supportsAdjustment(\assQuestionGUI $question_object)
	{
		return ($question_object instanceof ilGuiQuestionScoringAdjustable
				|| $question_object instanceof ilGuiAnswerScoringAdjustable)
			&& ($question_object->object instanceof ilObjQuestionScoringAdjustable
				|| $question_object->object instanceof ilObjAnswerScoringAdjustable);
	}
	
	/**
	 * Returns if the question type is allowed for adjustments in the global test administration.
	 *
	 * @param assQuestionGUI $question_object
	 * @return bool
	 */
	protected function allowedInAdjustment(\assQuestionGUI $question_object)
	{
		$setting = new ilSetting('assessment');
		$types = explode(',',$setting->get('assessment_scoring_adjustment'));
		require_once './Modules/TestQuestionPool/classes/class.ilObjQuestionPool.php';
		$type_def = array();
		foreach ($types as $type)
		{
			$type_def[$type] = ilObjQuestionPool::getQuestionTypeByTypeId($type);
		}
		
		$type = $question_object->getQuestionType();
		if (in_array($type,$type_def))
		{
			return true;
		}
		return false;
	}
}