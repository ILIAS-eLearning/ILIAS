<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "./Modules/Test/classes/inc.AssessmentConstants.php";
include_once "./Modules/Test/classes/class.ilTestServiceGUI.php";

/**
* Scoring class for tests
*
* @author	Helmut Schottmüller <helmut.schottmueller@mac.com>
* @author	Björn Heyser <bheyser@databay.de>
* @version	$Id$
*
* @ingroup ModulesTest
* @extends ilTestServiceGUI
*/
class ilTestScoringGUI extends ilTestServiceGUI
{
	const PART_FILTER_ACTIVE_ONLY			= 1;
	const PART_FILTER_INACTIVE_ONLY			= 2;
	const PART_FILTER_ALL_USERS				= 3; // default
	const PART_FILTER_MANSCORING_DONE		= 4;
	const PART_FILTER_MANSCORING_NONE		= 5;
	//const PART_FILTER_MANSCORING_PENDING	= 6;
	
	/**
	* ilTestScoringGUI constructor
	*
	* The constructor takes the test object reference as parameter 
	*
	* @param object $a_object Associated ilObjTest class
	* @access public
	*/
	function ilTestScoringGUI(ilObjTest $a_object)
	{
		parent::ilTestServiceGUI($a_object);
	}
	
	private function fetchActiveIdParameter()
	{
		global $ilCtrl;
		
		// fetch active_id
		
		if( !isset($_GET['active_id']) || !(int)$_GET['active_id'] )
		{
			// allow only write access
			ilUtil::sendFailure('no active id given!', true);
			$ilCtrl->redirectByClass("ilobjtestgui", "infoScreen");
		}
		else
		{
			$activeId = (int)$_GET['active_id'];
		}
		
		return $activeId;
	}
	
	private function fetchPassParameter($activeId)
	{
		// fetch pass nr
		
		$maxPass = $this->object->_getMaxPass($activeId);
		if( isset($_GET["pass"]) && 0 <= (int)$_GET["pass"] && $maxPass >= (int)$_GET["pass"] )
		{
			$pass = $_GET["pass"];
		}
		elseif( $this->object->getPassScoring() == SCORE_LAST_PASS )
		{
			$pass = $maxPass;
		}
		else
		{
			$pass = $this->object->_getResultPass($activeId);
		}
		
		return $pass;
	}
	
	/**
	* execute command
	*/
	function &executeCommand()
	{
		global $ilAccess;
		
		if( !$ilAccess->checkAccess("write", "", $this->ref_id) )
		{
			// allow only write access
			ilUtil::sendFailure($this->lng->txt("cannot_edit_test"), true);
			$this->ctrl->redirectByClass("ilobjtestgui", "infoScreen");
		}

		require_once 'Modules/Test/classes/class.ilObjAssessmentFolder.php';
		if( !ilObjAssessmentFolder::_mananuallyScoreableQuestionTypesExists() )
		{
			// allow only if at least one question type is marked for manual scoring
			ilUtil::sendFailure($this->lng->txt("manscoring_not_allowed"), true);
			$this->ctrl->redirectByClass("ilobjtestgui", "infoScreen");
		}
		
		$cmd = $this->ctrl->getCmd();
		$next_class = $this->ctrl->getNextClass($this);

		if (strlen($cmd) == 0)
		{
			$this->ctrl->redirect($this, "manscoring");
		}
		
		$cmd = $this->getCommand($cmd);
		
		switch($next_class)
		{
			default:
				$ret =& $this->$cmd();
				break;
		}
		
		return $ret;
	}
	
	private function showManScoringParticipantsTable()
	{
		global $tpl;
		
		require_once 'Modules/Test/classes/tables/class.ilTestManScoringParticipantsTableGUI.php';
		$table = new ilTestManScoringParticipantsTableGUI($this);

		$participantStatusFilterValue = $table->getFilterItemByPostVar('participant_status')->getValue();
		
		$table->setData( $this->object->getTestParticipantsForManualScoring($participantStatusFilterValue) );
		
		$tpl->setContent( $table->getHTML() );
	}
	
	private function applyManScoringParticipantsFilter()
	{
		require_once 'Modules/Test/classes/tables/class.ilTestManScoringParticipantsTableGUI.php';
		$table = new ilTestManScoringParticipantsTableGUI($this);
		
		$table->resetOffset();
		$table->writeFilterToSession();
		
		$this->showManScoringParticipantsTable();
	}
	
	private function resetManScoringParticipantsFilter()
	{
		require_once 'Modules/Test/classes/tables/class.ilTestManScoringParticipantsTableGUI.php';
		$table = new ilTestManScoringParticipantsTableGUI($this);
		
		$table->resetOffset();
		$table->resetFilter();
	}
	
	private function showManScoringParticipantScreen(ilPropertyForm $form = null)
	{
		global $tpl, $lng;
		
		$activeId = $this->fetchActiveIdParameter();
		$pass = $this->fetchPassParameter($activeId);

		$contentHTML = '';
		
		// pass overview table
		
		if( $this->object->getNrOfTries() != 1 )
		{
			require_once 'Modules/Test/classes/tables/class.ilTestPassOverwiewTableGUI.php';
			$table = new ilTestPassOverwiewTableGUI($this, 'showManScoringParticipantScreen');

			$userId = $this->object->_getUserIdFromActiveId($activeId);
			$userFullname = $this->object->userLookupFullName($userId, false, true);
			$tableTitle = sprintf($lng->txt('tst_pass_overview_for_participant'), $userFullname);
			$table->setTitle($tableTitle);
			
			$passOverviewData = $this->service->getPassOverviewData($activeId);
			$table->setData($passOverviewData['passes']);
			
			$contentHTML .= $table->getHTML().'<br />';
		}
		
		// pass scoring form
		
		if($form === null)
		{
			$questionGuiList = $this->service->getManScoringQuestionGuiList($activeId, $pass);
			$form = $this->buildManScoringParticipantForm($questionGuiList, $activeId, $pass, true);
		}
		
		$contentHTML .= $form->getHTML();
		
		// set content
		
		$tpl->setContent($contentHTML);
	}
	
	private function saveManScoringParticipantScreen()
	{
		global $tpl, $ilCtrl;
			
		$activeId = $this->fetchActiveIdParameter();
		$pass = $this->fetchPassParameter($activeId);
		
		$questionGuiList = $this->service->getManScoringQuestionGuiList($activeId, $pass);
		$form = $this->buildManScoringParticipantForm($questionGuiList, $activeId, $pass, false);
		
		$form->setValuesByPost();
		
		if( $form->checkInput() )
		{
			foreach($questionGuiList as $questionId => $questionGui)
			{
				$points = $form->getItemByPostVar("question__{$questionId}__points")->getValue();
				include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
				$maxpoints = assQuestion::_getMaximumPoints($questionId);
				assQuestion::_setReachedPoints($activeId, $questionId, $points, $maxpoints, $pass, 1);

				$feedback = $form->getItemByPostVar("question__{$questionId}__feedback")->getValue();
				include_once "./Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php";
				$feedback = ilUtil::stripSlashes($feedback, false, ilObjAdvancedEditing::_getUsedHTMLTagsAsString("assessment"));
				$this->object->saveManualFeedback($activeId, $questionId, $pass, $feedback);
			}
			
			$manScoringDone = $form->getItemByPostVar("manscoring_done")->getValue();
			ilTestService::setManScoringDone($activeId, $manScoringDone);
			
			include_once "./Modules/Test/classes/class.ilObjTestAccess.php";
			include_once("./Services/Tracking/classes/class.ilLPStatusWrapper.php");
			ilLPStatusWrapper::_updateStatus(
					$this->object->getId(), ilObjTestAccess::_getParticipantId($activeId)
			);
			
			ilUtil::sendSuccess('tst_saved_manscoring_successfully', true);
			$ilCtrl->redirect($this, 'showManScoringParticipantScreen');
		}
		else
		{
			$this->showManScoringParticipantScreen($form);
		}
	}
	
	private function buildManScoringParticipantForm($questionGuiList, $activeId, $pass, $initValues = false)
	{
		global $ilCtrl, $lng;
		
		require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
		require_once 'Services/Form/classes/class.ilFormSectionHeaderGUI.php';
		require_once 'Services/Form/classes/class.ilCustomInputGUI.php';
		require_once 'Services/Form/classes/class.ilCheckboxInputGUI.php';
		require_once 'Services/Form/classes/class.ilTextInputGUI.php';
		require_once 'Services/Form/classes/class.ilTextAreaInputGUI.php';
		
		$ilCtrl->setParameter($this, 'active_id', $activeId);
		$ilCtrl->setParameter($this, 'pass', $pass);
		
		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormAction($this));
		
		$form->setTitle( sprintf($lng->txt('manscoring_results_pass'), $pass + 1) );
		$form->setTableWidth('100%');
		
		foreach($questionGuiList as $questionId => $questionGUI)
		{
			$questionHeader = sprintf($lng->txt('tst_manscoring_question_section_header'), $questionGUI->object->getTitle());
			$questionSolution = $questionGUI->getSolutionOutput($activeId, $pass, false, false, true, false, false, true);
			$bestSolution = $questionGUI->object->getSuggestedSolutionOutput();
		
				$sect = new ilFormSectionHeaderGUI();
				$sect->setTitle($questionHeader);
			$form->addItem($sect);

				$cust = new ilCustomInputGUI('Frage und Teilnehmer Lösung');
				$cust->setHtml($questionSolution);
			$form->addItem($cust);

				$text = new ilTextInputGUI($lng->txt('tst_change_points_for_question'), "question__{$questionId}__points");
				if( $initValues ) $text->setValue( assQuestion::_getReachedPoints($activeId, $questionId, $pass) );
			$form->addItem($text);
			
				$area = new ilTextAreaInputGUI($lng->txt('set_manual_feedback'), "question__{$questionId}__feedback");
				$area->setUseRTE(true);
				if( $initValues ) $area->setValue( $this->object->getManualFeedback($activeId, $questionId, $pass) );
			$form->addItem($area);

				$cust = new ilCustomInputGUI('Muster Lösung');
				$cust->setHtml($bestSolution);
			$form->addItem($cust);
		}
		
		$sect = new ilFormSectionHeaderGUI();
		$sect->setTitle($lng->txt('tst_participant'));
		$form->addItem($sect);
		
		$check = new ilCheckboxInputGUI($lng->txt('set_manscoring_done'), 'manscoring_done');
		if( $initValues && ilTestService::isManScoringDone($activeId) ) $check->setChecked(true);
		$form->addItem($check);
		
		$form->addCommandButton('saveManScoringParticipantScreen', $lng->txt('save'));
		
		return $form;
	}

}
