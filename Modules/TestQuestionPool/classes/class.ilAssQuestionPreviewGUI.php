<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/TestQuestionPool
 *
 * @ilCtrl_Calls ilAssQuestionPreviewGUI: ilAssQuestionPreviewToolbarGUI
 * @ilCtrl_Calls ilAssQuestionPreviewGUI: ilAssQuestionRelatedNavigationBarGUI
 * @ilCtrl_Calls ilAssQuestionPreviewGUI: ilAssQuestionHintRequestGUI

 */
class ilAssQuestionPreviewGUI
{
	const CMD_SHOW = 'show';
	const CMD_RESET = 'reset';
	const CMD_INSTANT_RESPONSE = 'instantResponse';
	const CMD_HANDLE_QUESTION_ACTION = 'handleQuestionAction';
	const CMD_GATEWAY_CONFIRM_HINT_REQUEST = 'gatewayConfirmHintRequest';
	const CMD_GATEWAY_SHOW_HINT_LIST = 'gatewayShowHintList';

	const TAB_ID_QUESTION_PREVIEW = 'preview';
	
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;
	
	/**
	 * @var ilTabsGUI
	 */
	protected $tabs;

	/**
	 * @var ilTemplate
	 */
	protected $tpl;

	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * @var ilDB
	 */
	protected $db;

	/**
	 * @var assQuestionGUI
	 */
	protected $questionGUI;

	/**
	 * @var assQuestion
	 */
	protected $questionOBJ;

	/**
	 * @var ilAssQuestionPreviewSettings
	 */
	protected $previewSettings;

	/**
	 * @var ilAssQuestionPreviewSession
	 */
	protected $previewSession;

	/**
	 * @var ilAssQuestionPreviewHintTracking
	 */
	protected $hintTracking;
	
	public function __construct(ilCtrl $ctrl, ilTabsGUI $tabs, ilTemplate $tpl, ilLanguage $lng, ilDB $db)
	{
		$this->ctrl = $ctrl;
		$this->tabs = $tabs;
		$this->tpl = $tpl;
		$this->lng = $lng;
		$this->db = $db;
	}

	public function initQuestion($questionId, $parentObjId)
	{
		require_once 'Modules/TestQuestionPool/classes/class.assQuestion.php';
		
		$this->questionGUI = assQuestion::instantiateQuestionGUI($questionId);
		$this->questionOBJ = $this->questionGUI->object;

		$this->questionOBJ->setObjId($parentObjId);

		$this->questionGUI->setQuestionTabs();
		$this->questionGUI->outAdditionalOutput();

		$this->questionOBJ->setOutputType(OUTPUT_JAVASCRIPT);
			
		$this->questionGUI->setTargetGui($this);
		$this->questionGUI->setQuestionActionCmd(self::CMD_HANDLE_QUESTION_ACTION);
	}

	public function initPreviewSettings($parentRefId)
	{
		require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionPreviewSettings.php';
		$this->previewSettings = new ilAssQuestionPreviewSettings($parentRefId);
		
		$this->previewSettings->init();
	}

	public function initPreviewSession($userId, $questionId)
	{
		require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionPreviewSession.php';
		$this->previewSession = new ilAssQuestionPreviewSession($userId, $questionId);

		$this->previewSession->init();
	}
	
	public function initHintTracking()
	{
		require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionPreviewHintTracking.php';
		$this->hintTracking = new ilAssQuestionPreviewHintTracking($this->db, $this->previewSession);
	}
	
	public function initStyleSheets()
	{
		include_once("./Services/Style/classes/class.ilObjStyleSheet.php");
		
		$this->tpl->setCurrentBlock("ContentStyle");
		$this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET", ilObjStyleSheet::getContentStylePath(0));
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("SyntaxStyle");
		$this->tpl->setVariable("LOCATION_SYNTAX_STYLESHEET", ilObjStyleSheet::getSyntaxStylePath());
		$this->tpl->parseCurrentBlock();
	}
	
	public function executeCommand()
	{
		$this->tabs->setTabActive(self::TAB_ID_QUESTION_PREVIEW);
		
		$this->lng->loadLanguageModule('content');
		
		$nextClass = $this->ctrl->getNextClass($this);
		
		switch($nextClass)
		{
			case 'ilassquestionhintrequestgui':
				
				require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionHintRequestGUI.php';
				$gui = new ilAssQuestionHintRequestGUI($this, self::CMD_SHOW, $this->questionGUI, $this->hintTracking);

				$this->ctrl->forwardCommand($gui);

				break;
			
			default:

				$cmd = $this->ctrl->getCmd(self::CMD_SHOW).'Cmd';
				
				$this->$cmd();
		}
	}
	
	private function showCmd()
	{
		$tpl = new ilTemplate('tpl.qpl_question_preview.html', true, true, 'Modules/TestQuestionPool');

		$tpl->setVariable('PREVIEW_FORMACTION', $this->ctrl->getFormAction($this, self::CMD_SHOW));

		$this->populatePreviewToolbar($tpl);
		
		$this->populateQuestionOutput($tpl);
		
		$this->populateQuestionNavigation($tpl);

		if( $this->isShowGenericQuestionFeedbackRequired() )
		{
			$this->populateGenericQuestionFeedback($tpl);
		}

		if( $this->isShowSpecificQuestionFeedbackRequired() )
		{
			$this->populateSpecificQuestionFeedback($tpl);
		}
		
		if( $this->isShowBestSolutionRequired() )
		{
			$this->populateSolutionOutput($tpl);
		}
		
		$this->tpl->setContent($tpl->get());
	}
	
	private function resetCmd()
	{
		$this->previewSession->resetRequestedHints();
		$this->previewSession->setParticipantsSolution(null);
		$this->previewSession->setInstantResponseActive(false);
		
		ilUtil::sendInfo($this->lng->txt('qst_preview_reset_msg'), true);
		
		$this->ctrl->redirect($this, self::CMD_SHOW);
	}
	
	private function instantResponseCmd()
	{
		$this->questionOBJ->persistPreviewState($this->previewSession);
		$this->previewSession->setInstantResponseActive(true);
		$this->ctrl->redirect($this, self::CMD_SHOW);
	}
	
	private function handleQuestionActionCmd()
	{
		$this->questionOBJ->persistPreviewState($this->previewSession);
		$this->ctrl->redirect($this, self::CMD_SHOW);
	}
	
	private function populatePreviewToolbar(ilTemplate $tpl)
	{
		require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionPreviewToolbarGUI.php';
		$toolbarGUI = new ilAssQuestionPreviewToolbarGUI($this->lng);

		$toolbarGUI->setFormAction($this->ctrl->getFormAction($this, self::CMD_SHOW));
		$toolbarGUI->setResetPreviewCmd(self::CMD_RESET);

		$toolbarGUI->build();
		
		$tpl->setVariable('PREVIEW_TOOLBAR', $this->ctrl->getHTML($toolbarGUI));
	}

	private function populateQuestionOutput(ilTemplate $tpl)
	{
		// FOR WHAT EXACTLY IS THIS USEFUL?
		$this->ctrl->setReturnByClass('ilAssQuestionPageGUI', 'view');
		$this->ctrl->setReturnByClass('ilObjQuestionPoolGUI', 'questions');

		include_once("./Modules/TestQuestionPool/classes/class.ilAssQuestionPageGUI.php");
		$pageGUI = new ilAssQuestionPageGUI($this->questionOBJ->getId());
		$pageGUI->setRenderPageContainer(false);
		$pageGUI->setEditPreview(true);
		$pageGUI->setEnabledTabs(false);

		// FOR WHICH SITUATION IS THIS WORKAROUND NECCESSARY? (sure .. imagemaps, but where this can be done?)
		if (strlen($this->ctrl->getCmd()) == 0 && !isset($_POST['editImagemapForward_x'])) // workaround for page edit imagemaps, keep in mind
		{
			$this->ctrl->setCmdClass(get_class($pageGUI));
			$this->ctrl->setCmd('preview');
		}

		$this->questionGUI->setPreviewSession($this->previewSession);
		
		$questionHtml = $this->questionGUI->getPreview(true, $this->isShowSpecificQuestionFeedbackRequired());
		
		$pageGUI->setQuestionHTML(array($this->questionOBJ->getId() => $questionHtml));

		//$pageGUI->setHeader($this->questionOBJ->getTitle()); // NO ADDITIONAL HEADER
		$pageGUI->setPresentationTitle($this->questionOBJ->getTitle());

		//$pageGUI->setTemplateTargetVar("ADM_CONTENT"); // NOT REQUIRED, OR IS?

		$tpl->setVariable('QUESTION_OUTPUT', $pageGUI->preview());
	}

	private function populateSolutionOutput(ilTemplate $tpl)
	{
		// FOR WHAT EXACTLY IS THIS USEFUL?
		$this->ctrl->setReturnByClass('ilAssQuestionPageGUI', 'view');
		$this->ctrl->setReturnByClass('ilObjQuestionPoolGUI', 'questions');

		include_once("./Modules/TestQuestionPool/classes/class.ilAssQuestionPageGUI.php");
		$pageGUI = new ilAssQuestionPageGUI($this->questionOBJ->getId());

		$pageGUI->setEditPreview(true);
		$pageGUI->setEnabledTabs(false);

		// FOR WHICH SITUATION IS THIS WORKAROUND NECCESSARY? (sure .. imagemaps, but where this can be done?)
		if (strlen($this->ctrl->getCmd()) == 0 && !isset($_POST['editImagemapForward_x'])) // workaround for page edit imagemaps, keep in mind
		{
			$this->ctrl->setCmdClass(get_class($pageGUI));
			$this->ctrl->setCmd('preview');
		}

		$this->questionGUI->setPreviewSession($this->previewSession);

		$pageGUI->setQuestionHTML(array($this->questionOBJ->getId() => $this->questionGUI->getSolutionOutput(0)));

		//$pageGUI->setHeader($this->questionOBJ->getTitle()); // NO ADDITIONAL HEADER
		//$pageGUI->setPresentationTitle($this->questionOBJ->getTitle());

		//$pageGUI->setTemplateTargetVar("ADM_CONTENT"); // NOT REQUIRED, OR IS?

		$tpl->setCurrentBlock('solution_output');
		$tpl->setVariable('SOLUTION_OUTPUT', $pageGUI->preview());
		$tpl->parseCurrentBlock();
	}

	private function populateQuestionNavigation(ilTemplate $tpl)
	{
		require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionRelatedNavigationBarGUI.php';
		$navGUI = new ilAssQuestionRelatedNavigationBarGUI($this->ctrl, $this->lng);

		$navGUI->setInstantResponseCmd(self::CMD_INSTANT_RESPONSE);
		$navGUI->setHintRequestCmd(self::CMD_GATEWAY_CONFIRM_HINT_REQUEST);
		$navGUI->setHintListCmd(self::CMD_GATEWAY_SHOW_HINT_LIST);
		
		$navGUI->setInstantResponseEnabled($this->previewSettings->isInstantFeedbackNavigationRequired());
		$navGUI->setHintProvidingEnabled($this->previewSettings->isHintProvidingEnabled());

		$navGUI->setHintRequestsPossible($this->hintTracking->requestsPossible());
		$navGUI->setHintRequestsExist($this->hintTracking->requestsExist());
		
		$tpl->setVariable('QUESTION_NAVIGATION', $this->ctrl->getHTML($navGUI));
	}
	
	private function populateGenericQuestionFeedback(ilTemplate $tpl)
	{
		if( $this->questionOBJ->isPreviewSolutionCorrect($this->previewSession) )
		{
			$feedback = $this->questionGUI->getGenericFeedbackOutputForCorrectSolution();
		}
		else
		{
			$feedback = $this->questionGUI->getGenericFeedbackOutputForIncorrectSolution();
		}
		
		$tpl->setCurrentBlock('instant_feedback_generic');
		$tpl->setVariable('GENERIC_FEEDBACK', $feedback);
		$tpl->parseCurrentBlock();
	}

	private function populateSpecificQuestionFeedback(ilTemplate $tpl)
	{
		$tpl->setCurrentBlock('instant_feedback_specific');
		$tpl->setVariable('ANSWER_FEEDBACK', $this->questionGUI->getSpecificFeedbackOutput(0, -1));
		$tpl->parseCurrentBlock();
	}

	private function isShowBestSolutionRequired()
	{
		if( !$this->previewSettings->isBestSolutionEnabled() )
		{
			return false;
		}

		return $this->previewSession->isInstantResponseActive();
	}

	private function isShowGenericQuestionFeedbackRequired()
	{
		if( !$this->previewSettings->isGenericFeedbackEnabled() )
		{
			return false;
		}

		return $this->previewSession->isInstantResponseActive();
	}

	private function isShowSpecificQuestionFeedbackRequired()
	{
		if( !$this->previewSettings->isSpecificFeedbackEnabled() )
		{
			return false;
		}

		return $this->previewSession->isInstantResponseActive();
	}
	
	public function saveQuestionSolution()
	{
		$this->questionOBJ->persistPreviewState($this->previewSession);
	}

	public function gatewayConfirmHintRequestCmd()
	{
		$this->saveQuestionSolution();
		
		require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionHintRequestGUI.php';
		
		$this->ctrl->redirectByClass(
			'ilAssQuestionHintRequestGUI', ilAssQuestionHintRequestGUI::CMD_CONFIRM_REQUEST
		);
	}

	public function gatewayShowHintListCmd()
	{
		$this->saveQuestionSolution();

		require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionHintRequestGUI.php';
		
		$this->ctrl->redirectByClass(
			'ilAssQuestionHintRequestGUI', ilAssQuestionHintRequestGUI::CMD_SHOW_LIST
		);
	}
}