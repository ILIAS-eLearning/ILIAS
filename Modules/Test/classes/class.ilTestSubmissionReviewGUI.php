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
		$next_class = $this->ilCtrl->getNextClass($this);

		switch($next_class)
		{
			default:
				$ret = $this->dispatchCommand();
				break;
		}
		return $ret;
	}
	
	protected function dispatchCommand()
	{
		$cmd = $this->ilCtrl->getCmd();
		switch ($cmd)
		{
			default:
				$ret = $this->show();
		}
		
		return $ret;
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
	
	public function show()
	{
		require_once 'class.ilTestEvaluationGUI.php';
		require_once './Services/PDFGeneration/classes/class.ilPDFGeneration.php';
		
		global $ilUser;
		
		$template = new ilTemplate("tpl.il_as_tst_submission_review.html", TRUE, TRUE, "Modules/Test");

		$this->ilCtrl->setParameter($this, "skipfinalstatement", 1);
		$template->setVariable("FORMACTION", $this->ilCtrl->getFormAction($this->testOutputGUI, 'redirectBack').'&reviewed=1');
		
		$template->setVariable("BUTTON_CONTINUE", $this->lng->txt("btn_next"));
		$template->setVariable("BUTTON_BACK", $this->lng->txt("btn_previous"));

		if($this->test->getListOfQuestionsEnd())
		{
			$template->setVariable("CANCEL_CMD", 'outQuestionSummary');
		}
		else
		{
			$template->setVariable("CANCEL_CMD", 'backFromSummary');
		}

		$active = $this->test->getActiveIdOfUser($ilUser->getId());

		$testevaluationgui = new ilTestEvaluationGUI($this->test);
		$results = $this->test->getTestResult($active, $this->testSession->getPass());
		$results_output = $testevaluationgui->getPassListOfAnswers(
			$results, $active, $this->testSession->getPass(), false, false, false, false
		);
	
		if ($this->test->getShowExamviewPdf())
		{
			$template->setCurrentBlock("pdf_export");
			$template->setVariable("PDF_TEXT", $this->lng->txt("pdf_export"));
			global $ilSetting;
			$inst_id = $ilSetting->get('inst_id', null);
			$path =  ilUtil::getWebspaceDir() . '/assessment/'. $this->testOutputGUI->object->getId() . '/exam_pdf';
			if (!is_dir($path))
			{
				ilUtil::makeDirParents($path);
			}
			$filename = $path . '/exam_N' . $inst_id . '-' . $this->testOutputGUI->object->getId() . '-' . $active . '-' . $this->testSession->getPass() . '.pdf';
			require_once 'class.ilTestPDFGenerator.php';
			ilTestPDFGenerator::generatePDF($results_output, ilTestPDFGenerator::PDF_OUTPUT_FILE, $filename);
			$template->setVariable("PDF_FILE_LOCATION", $filename);
		}

		if($this->test->getShowExamviewHtml())
		{
			if($this->test->getListOfQuestionsEnd())
			{
				$template->setVariable("CANCEL_CMD_BOTTOM", 'outQuestionSummary');
			}
			else
			{
				$template->setVariable("CANCEL_CMD_BOTTOM", 'backFromSummary');
			}
			$template->setVariable("BUTTON_CONTINUE_BOTTOM", $this->lng->txt("btn_next"));
			$template->setVariable("BUTTON_BACK_BOTTOM", $this->lng->txt("btn_previous"));

			$template->setVariable('HTML_REVIEW', $results_output);
		}

		$this->tpl->setVariable($this->getContentBlockName(), $template->get() );
	}
}