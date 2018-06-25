<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/Test/classes/class.ilTestServiceGUI.php';

/**
 * Class ilTestSubmissionReviewGUI
 * 
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 * 
 * @ctrl_calls 	  ilTestSubmissionReviewGUI: ilAssQuestionPageGUI
 */
class ilTestSubmissionReviewGUI extends ilTestServiceGUI
{
	/** @var ilTestOutputGUI */
	protected $testOutputGUI = null;

	/** @var \ilTestSession */
	protected $testSession;

	public function __construct(ilTestOutputGUI $testOutputGUI, ilObjTest $testOBJ, ilTestSession $testSession)
	{
		$this->testOutputGUI = $testOutputGUI;
		$this->testSession = $testSession;
		
		parent::__construct($testOBJ);
	}
	
	function executeCommand()
	{
		if( !$this->object->getEnableExamview() )
		{
			return '';
		}
		
		switch( $this->ctrl->getNextClass($this) )
		{
			default:
				$this->dispatchCommand();
				break;
		}
		
		return '';
	}
	
	protected function dispatchCommand()
	{
		switch( $this->ctrl->getCmd() )
		{
			case 'pdfDownload':
				
				if( $this->object->getShowExamviewPdf() )
				{
					$this->pdfDownload();
				}
				
				break;
				
			case 'show':
			default:
				
				$this->show();
		}
	}
	
	/**
	 * Returns the name of the current content block (depends on the kiosk mode setting)
	 *
	 * @return string The name of the content block
	 * @access public
	 */
	private function getContentBlockName()
	{
		if ($this->object->getKioskMode())
		{
			$this->tpl->setBodyClass("kiosk");
			$this->tpl->setAddFooter(FALSE);
			return "CONTENT";
		}
		else
		{
			return "ADM_CONTENT";
		}
	}
	
	/**
	 * @return ilToolbarGUI
	 */
	protected function buildToolbar($toolbarId)
	{
		require_once 'Modules/Test/classes/class.ilTestPlayerCommands.php';
		require_once 'Services/UIComponent/Toolbar/classes/class.ilToolbarGUI.php';
		require_once 'Services/UIComponent/Button/classes/class.ilLinkButton.php';
		require_once 'Services/UIComponent/Button/classes/class.ilButton.php';
		
		$toolbar = new ilToolbarGUI();
		$toolbar->setId($toolbarId);
		
		$backUrl = $this->ctrl->getLinkTarget($this->testOutputGUI, $this->object->getListOfQuestionsEnd() ?
			ilTestPlayerCommands::QUESTION_SUMMARY : ilTestPlayerCommands::BACK_FROM_FINISHING
		);
		
		$button = ilLinkButton::getInstance();
		$button->setCaption('btn_previous');
		$button->setUrl($backUrl);
		$toolbar->addButtonInstance($button);
		
		if( $this->object->getShowExamviewPdf() )
		{
			$pdfUrl = $this->ctrl->getLinkTarget($this, 'pdfDownload');
			
			$button = ilLinkButton::getInstance();
			$button->setCaption('pdf_export');
			$button->setUrl($pdfUrl);
			$button->setTarget(ilButton::FORM_TARGET_BLANK);
			$toolbar->addButtonInstance($button);
		}
		
		$this->ctrl->setParameter($this->testOutputGUI, 'reviewed', 1);
		$nextUrl = $this->ctrl->getLinkTarget($this->testOutputGUI, ilTestPlayerCommands::FINISH_TEST);
		$this->ctrl->setParameter($this->testOutputGUI, 'reviewed', 0);
		
		$button = ilLinkButton::getInstance();
		$button->setPrimary(true);
		$button->setCaption('btn_next');
		$button->setUrl($nextUrl);
		$toolbar->addButtonInstance($button);
		
		return $toolbar;
	}
	
	protected function buildUserReviewOutput()
	{
		$ilObjDataCache = isset($GLOBALS['DIC']) ? $GLOBALS['DIC']['ilObjDataCache'] : $GLOBALS['ilObjDataCache'];
		
		require_once 'Modules/Test/classes/class.ilTestResultHeaderLabelBuilder.php';
		$testResultHeaderLabelBuilder = new ilTestResultHeaderLabelBuilder($this->lng, $ilObjDataCache);
		
		$objectivesList = null;
		
		if( $this->getObjectiveOrientedContainer()->isObjectiveOrientedPresentationRequired() )
		{
			$testSequence = $this->testSequenceFactory->getSequenceByActiveIdAndPass($this->testSession->getActiveId(), $this->testSession->getPass());
			$testSequence->loadFromDb();
			$testSequence->loadQuestions();
			
			require_once 'Modules/Course/classes/Objectives/class.ilLOTestQuestionAdapter.php';
			$objectivesAdapter = ilLOTestQuestionAdapter::getInstance($this->testSession);
			
			$objectivesList = $this->buildQuestionRelatedObjectivesList($objectivesAdapter, $testSequence);
			$objectivesList->loadObjectivesTitles();
			
			$testResultHeaderLabelBuilder->setObjectiveOrientedContainerId($this->testSession->getObjectiveOrientedContainerId());
			$testResultHeaderLabelBuilder->setUserId($this->testSession->getUserId());
			$testResultHeaderLabelBuilder->setTestObjId($this->object->getId());
			$testResultHeaderLabelBuilder->setTestRefId($this->object->getRefId());
			$testResultHeaderLabelBuilder->initObjectiveOrientedMode();
		}
		
		$results = $this->object->getTestResult(
			$this->testSession->getActiveId(), $this->testSession->getPass(), false,
			!$this->getObjectiveOrientedContainer()->isObjectiveOrientedPresentationRequired()
		);
		
		require_once 'class.ilTestEvaluationGUI.php';
		$testevaluationgui = new ilTestEvaluationGUI($this->object);
		$testevaluationgui->setContextResultPresentation(false);
		
		$results_output = $testevaluationgui->getPassListOfAnswers( $results,
			$this->testSession->getActiveId(), $this->testSession->getPass(),
			false, false, false, false,
			false, $objectivesList, $testResultHeaderLabelBuilder
		);
		
		return $results_output;
	}
	
	protected function show()
	{
		$html = $this->buildToolbar('review_nav_top')->getHTML();
		$html .= $this->buildUserReviewOutput() . '<br />';
		$html .= $this->buildToolbar('review_nav_bottom')->getHTML();
		
		$this->tpl->setVariable($this->getContentBlockName(), $html);
	}
	
	protected function pdfDownload()
	{
		$reviewOutput = $this->buildUserReviewOutput();
		
		require_once './Services/PDFGeneration/classes/class.ilPDFGeneration.php';
		ilPDFGeneration::prepareGeneration();
		
		require_once 'class.ilTestPDFGenerator.php';
		ilTestPDFGenerator::generatePDF($reviewOutput, ilTestPDFGenerator::PDF_OUTPUT_DOWNLOAD);
		
		exit;
	}
	
	/**
	 * not in use, but we keep the code (no archive for every user at end of test !!)
	 * 
	 * @return string
	 */
	protected function buildPdfFilename()
	{
		global $ilSetting;
		
		$inst_id = $ilSetting->get('inst_id', null);
		
		require_once 'Services/Utilities/classes/class.ilUtil.php';
		
		$path =  ilUtil::getWebspaceDir() . '/assessment/'. $this->testOutputGUI->object->getId() . '/exam_pdf';
		
		if (!is_dir($path))
		{
			ilUtil::makeDirParents($path);
		}
		
		$filename = ilUtil::removeTrailingPathSeparators(ILIAS_ABSOLUTE_PATH) . '/' . $path . '/exam_N';
		$filename .= $inst_id . '-' . $this->testOutputGUI->object->getId();
		$filename .= '-' . $this->testSession->getActiveId() . '-';
		$filename .= $this->testSession->getPass() . '.pdf';
		
		return $filename;
	}
}