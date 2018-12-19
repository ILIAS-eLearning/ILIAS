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
	 * @var ilTestAccess
	 */
	protected $testAccess;
	
	/**
	 * ilTestCorrectionsGUI constructor.
	 * @param \ILIAS\DI\Container $DIC
	 * @param ilObjTest $testOBJ
	 */
	public function __construct(\ILIAS\DI\Container $DIC, ilObjTest $testOBJ)
	{
		$this->DIC = $DIC;
		$this->testOBJ = $testOBJ;
		
		$this->testAccess = new ilTestAccess($testOBJ->getRefId(), $testOBJ->getTestId());
	}
	
	public function executeCommand()
	{
		if( !$this->testAccess->checkCorrectionsAccess() )
		{
			ilObjTestGUI::accessViolationRedirect();
		}
		
		if (isset($_GET['eqid']) && (int)$_GET["eqid"] && isset($_GET['eqpl']) && (int)$_GET["eqpl"])
		{
			$this->DIC->ctrl()->setParameter($this, 'qid', (int)$_GET["eqid"]);
			$this->DIC->ctrl()->redirect($this, 'showQuestion');
		}
		
		if (isset($_GET['removeQid']) && (int)$_GET['removeQid'])
		{
			$this->DIC->ctrl()->setParameter($this, 'qid', (int)$_GET['removeQid']);
			$this->DIC->ctrl()->redirect($this, 'confirmQuestionRemoval');
		}
		
		if( (int)$_GET['qid'] && !$this->checkQuestion((int)$_GET['qid']) )
		{
			ilObjTestGUI::accessViolationRedirect();
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
		$this->DIC->tabs()->activateTab(ilTestTabsManager::TAB_ID_CORRECTION);
		
		$table_gui = new ilTestQuestionsTableGUI(
			$this, 'showQuestionList', $this->testOBJ->getRefId()
		);
		
		$table_gui->setQuestionTitleLinksEnabled(true);
		$table_gui->setQuestionRemoveRowButtonEnabled(true);
		$table_gui->init();
		
		$table_gui->setData($this->getQuestions());
		
		$this->DIC->ui()->mainTemplate()->setContent($table_gui->getHTML());
	}
	
	protected function showQuestion(ilPropertyFormGUI $form = null)
	{
		$questionGUI = $this->getQuestion((int)$_GET['qid']);
		
		$this->setCorrectionTabsContext($questionGUI, 'question');
		
		if($form === null)
		{
			$form = $this->buildQuestionCorrectionForm($questionGUI);
		}
		
		$this->populatePageTitleAndDescription($questionGUI);
		$this->DIC->ui()->mainTemplate()->setContent($form->getHTML());
	}
	
	protected function saveQuestion()
	{
		$questionGUI = $this->getQuestion((int)$_GET['qid']);
		
		$form = $this->buildQuestionCorrectionForm($questionGUI);
		
		$form->setValuesByPost();
		
		if( !$form->checkInput() )
		{
			$questionGUI->prepareReprintableCorrectionsForm($form);
			
			$this->showQuestion($form);
			return;
		}
		
		$questionGUI->saveCorrectionsFormProperties($form);
		$questionGUI->object->setPoints($questionGUI->object->getMaximumPoints());
		$questionGUI->object->saveToDb();
		
		$preserveManualScoring = (bool)$form->getItemByPostVar('preserve_manscoring')->getChecked();
		
		$scoring = new ilTestScoring($this->testOBJ);
		$scoring->setPreserveManualScores($preserveManualScoring);
		$scoring->recalculateSolutions();
		
		$this->DIC->ctrl()->redirect($this, 'showQuestion');
	}
	
	protected function buildQuestionCorrectionForm(assQuestionGUI $questionGUI)
	{
		$form = new ilPropertyFormGUI();
		$form->setFormAction( $this->DIC->ctrl()->getFormAction($this) );
		$form->setId('tst_question_correction');
		
		$form->setTitle($this->DIC->language()->txt('tst_corrections_qst_form'));
		
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
		
		$form->addCommandButton('saveQuestion', $this->DIC->language()->txt('save'));
		
		return $form;
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
		
		$this->populatePageTitleAndDescription($questionGUI);

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
		
		$tablesHtml = '';
		
		foreach($questionGUI->getSubQuestionsIndex() as $subQuestionIndex)
		{
			$table = $questionGUI->getAnswerFrequencyTableGUI(
				$this, 'showAnswerStatistic', $solutions, $subQuestionIndex
			);
			
			$tablesHtml .= $table->getHTML() . $table->getAdditionalHtml();
		}
		
		$this->populatePageTitleAndDescription($questionGUI);
		$this->DIC->ui()->mainTemplate()->setContent($tablesHtml);
		$this->DIC->ui()->mainTemplate()->addCss('Modules/Test/templates/default/ta.css');
		
	}
	
	protected function addAnswerAsynch()
	{
		$response = new stdClass();
		
		$form = new ilAddAnswerModalFormGUI();
		$form->build();
		$form->setValuesByPost();
		
		if( !$form->checkInput() )
		{
			$uid = md5($form->getInput('answer'));
			
			$form->setId($uid);
			$form->setFormAction($this->DIC->ctrl()->getFormAction($this, 'addAnswerAsynch'));

			$alert = $this->DIC->ui()->factory()->messageBox()->failure(
				$this->DIC->language()->txt('form_input_not_valid')
			);
			
			$bodyTpl = new ilTemplate('tpl.tst_corr_addanswermodal.html', true, true, 'Modules/TestQuestionPool');
			$bodyTpl->setVariable('MESSAGE', $this->DIC->ui()->renderer()->render($alert));
			$bodyTpl->setVariable('FORM', $form->getHTML());
			$bodyTpl->setVariable('BODY_UID', $uid);
			
			$response->result = false;
			$response->html = $bodyTpl->get();
			
			echo json_encode($response);
			exit;
		}
		
		$qid = (int)$form->getInput('qid');
		
		if( !$this->checkQuestion($qid) )
		{
			$response->html = '';
			$response->result = false;

			echo json_encode($response);
			exit;
		}
		
		$questionGUI = $this->getQuestion($qid);
		
		$qIndex = (int)$form->getInput('qindex');
		$points = (float)$form->getInput('points');
		$answerOption = $form->getInput('answer');
		
		if( $questionGUI->object->isAddableAnswerOptionValue($qIndex, $answerOption) )
		{
			$questionGUI->object->addAnswerOptionValue($qIndex, $answerOption, $points);
			$questionGUI->object->saveToDb();
		}
		
		$response->result = true;
		
		echo json_encode($response);
		exit;
	}
	
	protected function confirmQuestionRemoval()
	{
		$this->DIC->tabs()->activateTab(ilTestTabsManager::TAB_ID_CORRECTION);
		
		$questionGUI = $this->getQuestion((int)$_GET['qid']);

		$confirmation = sprintf($this->DIC->language()->txt('tst_corrections_qst_remove_confirmation'),
			$questionGUI->object->getTitle(), $questionGUI->object->getId() 
		);
		
		$buttons = array(
			$this->DIC->ui()->factory()->button()->standard(
				$this->DIC->language()->txt('confirm'),
				$this->DIC->ctrl()->getLinkTarget($this, 'performQuestionRemoval')
			),
			$this->DIC->ui()->factory()->button()->standard(
				$this->DIC->language()->txt('cancel'),
				$this->DIC->ctrl()->getLinkTarget($this, 'showQuestionList')
			)
		);
		
		$this->DIC->ui()->mainTemplate()->setContent($this->DIC->ui()->renderer()->render(
			$this->DIC->ui()->factory()->messageBox()->confirmation($confirmation)->withButtons($buttons)
		));
	}
	
	protected function performQuestionRemoval()
	{
		global $DIC; /* @var ILIAS\DI\Container $DIC */
		
		$questionGUI = $this->getQuestion((int)$_GET['qid']);
		$scoring = new ilTestScoring($this->testOBJ);
		
		$participantData = new ilTestParticipantData($DIC->database(), $DIC->language());
		$participantData->load($this->testOBJ->getTestId());
		
		// remove question from test and reindex remaining questions
		$this->testOBJ->removeQuestion($questionGUI->object->getId());
		$this->testOBJ->reindexFixedQuestionOrdering();
		$this->testOBJ->loadQuestions();
		
		// remove question solutions
		$questionGUI->object->removeAllExistingSolutions();
		
		// remove test question results
		$scoring->removeAllQuestionResults($questionGUI->object->getId());
		
		// update pass and test results
		$scoring->updatePassAndTestResults($participantData->getActiveIds());
		
		// trigger learning progress
		ilLPStatusWrapper::_refreshStatus($this->testOBJ->getId(), $participantData->getUserIds());
		
		// remove questions from all sequences
		$this->testOBJ->removeQuestionFromSequences($questionGUI->object->getId(), $participantData->getActiveIds());
		
		// finally delete the question itself
		$questionGUI->object->delete($questionGUI->object->getId());
		
		// check for empty test and set test offline
		if( !count($this->testOBJ->getTestQuestions()) )
		{
			$this->testOBJ->setOnline(false);
			$this->testOBJ->saveToDb(true);
		}
		
		$this->DIC->ctrl()->setParameter($this, 'qid', '');
		$this->DIC->ctrl()->redirect($this, 'showQuestionList');
	}
	
	protected function setCorrectionTabsContext(assQuestionGUI $questionGUI, $activeTabId)
	{
		$this->DIC->tabs()->clearTargets();
		$this->DIC->tabs()->clearSubTabs();
		
		$this->DIC->tabs()->setBackTarget($this->DIC->language()->txt('back'),
			$this->DIC->ctrl()->getLinkTarget($this, 'showQuestionList'));
		
		$this->DIC->tabs()->addTab('question', $this->DIC->language()->txt('tst_corrections_tab_question'),
			$this->DIC->ctrl()->getLinkTarget($this, 'showQuestion')
		);
		
		$this->DIC->tabs()->addTab('solution', $this->DIC->language()->txt('tst_corrections_tab_solution'),
			$this->DIC->ctrl()->getLinkTarget($this, 'showSolution')
		);
		
		if($questionGUI->isAnswerFreuqencyStatisticSupported())
		{
			$this->DIC->tabs()->addTab('answers', $this->DIC->language()->txt('tst_corrections_tab_statistics'),
				$this->DIC->ctrl()->getLinkTarget($this, 'showAnswerStatistic')
			);
		}
		
		$this->DIC->tabs()->activateTab($activeTabId);
	}
	
	/**
	 * @param assQuestionGUI $questionGUI
	 */
	protected function populatePageTitleAndDescription(assQuestionGUI $questionGUI)
	{
		$this->DIC->ui()->mainTemplate()->setTitle($questionGUI->object->getTitle());
		$this->DIC->ui()->mainTemplate()->setDescription($questionGUI->outQuestionType());
	}
	
	/**
	 * @param int $qId
	 * @return bool
	 */
	protected function checkQuestion($qId)
	{
		if( !$this->testOBJ->isTestQuestion($qId) )
		{
			return false;
		}
		
		$questionGUI = $this->getQuestion($qId);
		
		if( !$this->supportsAdjustment($questionGUI) )
		{
			return false;
		}
		
		if( !$this->allowedInAdjustment($questionGUI) )
		{
			return false;
		}
		
		return true;
	}
	
	/**
	 * @param int $qId
	 * @return assQuestionGUI
	 */
	protected function getQuestion($qId)
	{
		$question = assQuestion::instantiateQuestionGUI($qId);
		$question->object->setObjId($this->testOBJ->getId());
		
		return $question;
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
	
	/**
	 * @return array
	 */
	protected function getQuestions(): array
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