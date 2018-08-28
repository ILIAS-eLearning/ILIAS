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
 * @ilCtrl_Calls ilAssQuestionPreviewGUI: ilAssGenFeedbackPageGUI
 * @ilCtrl_Calls ilAssQuestionPreviewGUI: ilAssSpecFeedbackPageGUI

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
	
	const FEEDBACK_FOCUS_ANCHOR = 'focus';
	
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
	 * @var ilDBInterface
	 */
	protected $db;

	/**
	 * @var ilObjUser
	 */
	protected $user;

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
	
	public function __construct(ilCtrl $ctrl, ilTabsGUI $tabs, ilTemplate $tpl, ilLanguage $lng, ilDBInterface $db, ilObjUser $user)
	{
		$this->ctrl = $ctrl;
		$this->tabs = $tabs;
		$this->tpl = $tpl;
		$this->lng = $lng;
		$this->db = $db;
		$this->user = $user;
	}

	public function initQuestion($questionId, $parentObjId)
	{
		require_once 'Modules/TestQuestionPool/classes/class.assQuestion.php';
		
		$this->questionGUI = assQuestion::instantiateQuestionGUI($questionId);
		$this->questionOBJ = $this->questionGUI->object;

		$this->questionOBJ->setObjId($parentObjId);

		$this->questionGUI->setQuestionTabs();
		$this->questionGUI->outAdditionalOutput();
		
		$this->questionGUI->populateJavascriptFilesRequiredForWorkForm($this->tpl);
		$this->questionOBJ->setOutputType(OUTPUT_JAVASCRIPT); // TODO: remove including depending stuff
			
		$this->questionGUI->setTargetGui($this);
		$this->questionGUI->setQuestionActionCmd(self::CMD_HANDLE_QUESTION_ACTION);
		
		$this->questionGUI->setRenderPurpose(assQuestionGUI::RENDER_PURPOSE_DEMOPLAY);
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
		include_once("./Services/Style/Content/classes/class.ilObjStyleSheet.php");
		
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

			case 'ilassspecfeedbackpagegui':
			case 'ilassgenfeedbackpagegui':
				require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionFeedbackPageObjectCommandForwarder.php';
				$forwarder = new ilAssQuestionFeedbackPageObjectCommandForwarder($this->questionOBJ, $this->ctrl, $this->tabs, $this->lng);
				$forwarder->forward();
				break;
			
			default:

				$cmd = $this->ctrl->getCmd(self::CMD_SHOW).'Cmd';
				
				$this->$cmd();
		}
	}
	
	/**
	 * @return string
	 */
	protected function buildPreviewFormAction()
	{
		return $this->ctrl->getFormAction($this, self::CMD_SHOW) . '#' . self::FEEDBACK_FOCUS_ANCHOR;
	}
	
	private function showCmd()
	{
		$tpl = new ilTemplate('tpl.qpl_question_preview.html', true, true, 'Modules/TestQuestionPool');

		$tpl->setVariable('PREVIEW_FORMACTION', $this->buildPreviewFormAction());

		$this->populatePreviewToolbar($tpl);
		
		$this->populateQuestionOutput($tpl);
		
		$this->populateQuestionNavigation($tpl);

		$this->handleInstantResponseRendering($tpl);
		
		$this->tpl->setContent($tpl->get());
	}
	
	protected function handleInstantResponseRendering(ilTemplate $tpl)
	{
		$renderHeader = false;
		$renderAnchor = false;
		
		if( $this->isShowBestSolutionRequired() )
		{
			$this->populateSolutionOutput($tpl);
			$renderAnchor = true;
		}
		
		if( $this->isShowGenericQuestionFeedbackRequired() )
		{
			$this->populateGenericQuestionFeedback($tpl);
			$renderAnchor = true;
			$renderHeader = true;
		}
		
		if( $this->isShowSpecificQuestionFeedbackRequired() )
		{
			$renderHeader = true;
			
			if( $this->questionGUI->hasInlineFeedback() )
			{
				$renderAnchor = false;
			}
			else
			{
				$this->populateSpecificQuestionFeedback($tpl);
				$renderAnchor = true;
			}
		}
		
		if( $renderHeader )
		{
			$this->populateInstantResponseHeader($tpl, $renderAnchor);
		}
	}
	
	private function resetCmd()
	{
		$this->previewSession->setRandomizerSeed(null);
		$this->previewSession->setParticipantsSolution(null);
		$this->previewSession->resetRequestedHints();
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
		$this->questionGUI->object->setShuffler($this->getQuestionAnswerShuffler());
		
		$questionHtml = $this->questionGUI->getPreview(true, $this->isShowSpecificQuestionFeedbackRequired());
		$this->questionGUI->magicAfterTestOutput();
		
		if( $this->isShowSpecificQuestionFeedbackRequired() && $this->questionGUI->hasInlineFeedback() )
		{
			$questionHtml = $this->questionGUI->buildFocusAnchorHtml() . $questionHtml;
		}
		
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

		$pageGUI->setQuestionHTML(array($this->questionOBJ->getId() => $this->questionGUI->getSolutionOutput(0, null, false, false, true, false, true, false, false)));

		//$pageGUI->setHeader($this->questionOBJ->getTitle()); // NO ADDITIONAL HEADER
		//$pageGUI->setPresentationTitle($this->questionOBJ->getTitle());

		//$pageGUI->setTemplateTargetVar("ADM_CONTENT"); // NOT REQUIRED, OR IS?
		
		$output = $this->questionGUI->getSolutionOutput(0, null, false, false, true, false, true, false, false);
		//$output = $pageGUI->preview();
		//$output = str_replace('<h1 class="ilc_page_title_PageTitle"></h1>', '', $output);
		
		$tpl->setCurrentBlock('solution_output');
		$tpl->setVariable('TXT_CORRECT_SOLUTION', $this->lng->txt('tst_best_solution_is'));
		$tpl->setVariable('SOLUTION_OUTPUT', $output);
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
			$cssClass = ilAssQuestionFeedback::CSS_CLASS_FEEDBACK_CORRECT;
		}
		else
		{
			$feedback = $this->questionGUI->getGenericFeedbackOutputForIncorrectSolution();
			$cssClass = ilAssQuestionFeedback::CSS_CLASS_FEEDBACK_WRONG;
		}
		
		if( strlen($feedback) )
		{
			$tpl->setCurrentBlock('instant_feedback_generic');
			$tpl->setVariable('GENERIC_FEEDBACK', $feedback);
			$tpl->setVariable('ILC_FB_CSS_CLASS', $cssClass);
			$tpl->parseCurrentBlock();
		}
	}

	private function populateSpecificQuestionFeedback(ilTemplate $tpl)
	{
		$fb = $this->questionGUI->getSpecificFeedbackOutput(
			(array)$this->previewSession->getParticipantsSolution()
		);
		
		$tpl->setCurrentBlock('instant_feedback_specific');
		$tpl->setVariable('ANSWER_FEEDBACK', $fb);
		$tpl->parseCurrentBlock();
	}
	
	protected function populateInstantResponseHeader(ilTemplate $tpl, $withFocusAnchor)
	{
		if( $withFocusAnchor )
		{
			$tpl->setCurrentBlock('inst_resp_id');
			$tpl->setVariable('INSTANT_RESPONSE_FOCUS_ID', self::FEEDBACK_FOCUS_ANCHOR);
			$tpl->parseCurrentBlock();
		}
		
		$tpl->setCurrentBlock('instant_response_header');
		$tpl->setVariable('INSTANT_RESPONSE_HEADER', $this->lng->txt('tst_feedback'));
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

	/**
	 * @return ilArrayElementShuffler
	 */
	private function getQuestionAnswerShuffler()
	{
		require_once 'Services/Randomization/classes/class.ilArrayElementShuffler.php';
		$shuffler = new ilArrayElementShuffler();
		
		if( !$this->previewSession->randomizerSeedExists() )
		{
			$this->previewSession->setRandomizerSeed($shuffler->buildRandomSeed());
		}
		
		$shuffler->setSeed($this->previewSession->getRandomizerSeed());		
		
		return $shuffler;
	}
}