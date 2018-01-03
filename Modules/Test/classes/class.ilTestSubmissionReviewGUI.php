<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestSubmissionReviewGUI
 * 
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 * 
 * @ilCtrl_calls 	  ilTestSubmissionReviewGUI: ilAssQuestionPageGUI
 */
class ilTestSubmissionReviewGUI 
{
	/** @var ilTestOutputGUI */
	protected $testOutputGUI = null;

	/** @var ilObjTest */
	protected $test = null;
	
	/** @var $lng \ilLanguage */
	protected $lng;
	
	/** @var $ilCtrl ilCtrl */
	protected $ilCtrl;

	/** @var $tpl \ilTemplate */
	protected $tpl;

	/** @var \ilTestSession */
	protected $testSession;

	public function __construct(ilTestOutputGUI $testOutputGUI, ilObjTest $test, ilTestSession $testSession)
	{
		global $lng, $ilCtrl, $tpl;
		$this->lng = $lng;
		$this->ilCtrl = $ilCtrl;
		$this->tpl = $tpl;
		
		$this->testOutputGUI = $testOutputGUI;
		$this->test = $test;
		$this->testSession = $testSession;
	}
	
	function executeCommand()
	{
		if( !$this->test->getEnableExamview() )
		{
			return '';
		}
		
		switch( $this->ilCtrl->getNextClass($this) )
		{
			default:
				$this->dispatchCommand();
				break;
		}
		
		return '';
	}
	
	protected function dispatchCommand()
	{
		switch( $this->ilCtrl->getCmd() )
		{
			case 'pdfDownload':
				
				if( $this->test->getShowExamviewPdf() )
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
		if ($this->test->getKioskMode())
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
		require_once 'Services/UIComponent/Toolbar/classes/class.ilToolbarGUI.php';
		require_once 'Services/UIComponent/Button/classes/class.ilButton.php';
		require_once 'Services/UIComponent/Button/classes/class.ilLinkButton.php';
		
		$toolbar = new ilToolbarGUI();
		$toolbar->setId($toolbarId);
		
		$backUrl = $this->ilCtrl->getLinkTarget($this->testOutputGUI, $this->test->getListOfQuestionsEnd() ?
			'outQuestionSummary' : 'backFromSummary'
		);
		
		$button = ilLinkButton::getInstance();
		$button->setCaption('btn_previous');
		$button->setUrl($backUrl);
		$toolbar->addButtonInstance($button);
		
		if( $this->test->getShowExamviewPdf() )
		{
			$pdfUrl = $this->ilCtrl->getLinkTarget($this, 'pdfDownload');
			
			$button = ilLinkButton::getInstance();
			$button->setCaption('pdf_export');
			$button->setUrl($pdfUrl);
			$button->setTarget('_blank');
			$toolbar->addButtonInstance($button);
		}
		
		$this->ilCtrl->setParameter($this->testOutputGUI, 'reviewed', 1);
		$nextUrl = $this->ilCtrl->getLinkTarget($this->testOutputGUI, 'finishTest');
		$this->ilCtrl->setParameter($this->testOutputGUI, 'reviewed', 0);
		
		$button = ilLinkButton::getInstance();
		$button->setCaption('btn_next');
		$button->setUrl($nextUrl);
		$toolbar->addButtonInstance($button);
		
		return $toolbar;
	}
	
	protected function buildUserReviewOutput()
	{
		$results = $this->test->getTestResult(
			$this->testSession->getActiveId(), $this->testSession->getPass(), false
		);
		
		require_once 'class.ilTestEvaluationGUI.php';
		$testevaluationgui = new ilTestEvaluationGUI($this->test);
		$testevaluationgui->setContextWithinTestPass(true);
		
		$results_output = $testevaluationgui->getPassListOfAnswers( $results,
			$this->testSession->getActiveId(), $this->testSession->getPass(),
			false, false, false, false,
			false
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
		
		$path =  ilUtil::getWebspaceDir() . '/assessment/'. $this->test->getId() . '/exam_pdf';
		
		if (!is_dir($path))
		{
			ilUtil::makeDirParents($path);
		}
		
		$filename = ilUtil::removeTrailingPathSeparators(ILIAS_ABSOLUTE_PATH) . '/' . $path . '/exam_N';
		$filename .= $inst_id . '-' . $this->test->getId();
		$filename .= '-' . $this->testSession->getActiveId() . '-';
		$filename .= $this->testSession->getPass() . '.pdf';
		
		return $filename;
	}
}