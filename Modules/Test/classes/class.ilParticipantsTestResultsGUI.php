<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilParticipantsTestResultsGUI
 *
 * @author    Björn Heyser <info@bjoernheyser.de>
 * @version    $Id$
 *
 * @package    Modules/Test
 * 
 * @ilCtrl_Calls ilParticipantsTestResultsGUI: ilTestEvaluationGUI
 * @ilCtrl_Calls ilParticipantsTestResultsGUI: ilAssQuestionPageGUI
 */
class ilParticipantsTestResultsGUI
{
	const CMD_SHOW_PARTICIPANTS = 'showParticipants';
	const CMD_CONFIRM_DELETE_ALL_USER_RESULTS = 'deleteAllUserResults';
	const CMD_PERFORM_DELETE_ALL_USER_RESULTS = 'confirmDeleteAllUserResults';
	const CMD_CONFIRM_DELETE_SELECTED_USER_RESULTS = 'deleteSingleUserResults';
	const CMD_PERFORM_DELETE_SELECTED_USER_RESULTS = 'confirmDeleteSelectedUserData';
	
	/**
	 * @var ilObjTest
	 */
	protected $testObj;
	
	/**
	 * @var ilTestQuestionSetConfig
	 */
	protected $questionSetConfig;
	
	/**
	 * @var ilTestAccess
	 */
	protected $testAccess;
	
	/**
	 * @var ilTestObjectiveOrientedContainer
	 */
	protected $objectiveParent;
	
	/**
	 * @return ilObjTest
	 */
	public function getTestObj()
	{
		return $this->testObj;
	}
	
	/**
	 * @param ilObjTest $testObj
	 */
	public function setTestObj($testObj)
	{
		$this->testObj = $testObj;
	}
	
	/**
	 * @return ilTestQuestionSetConfig
	 */
	public function getQuestionSetConfig()
	{
		return $this->questionSetConfig;
	}
	
	/**
	 * @param ilTestQuestionSetConfig $questionSetConfig
	 */
	public function setQuestionSetConfig($questionSetConfig)
	{
		$this->questionSetConfig = $questionSetConfig;
	}
	
	/**
	 * @return ilTestAccess
	 */
	public function getTestAccess()
	{
		return $this->testAccess;
	}
	
	/**
	 * @param ilTestAccess $testAccess
	 */
	public function setTestAccess($testAccess)
	{
		$this->testAccess = $testAccess;
	}
	
	/**
	 * @return ilTestObjectiveOrientedContainer
	 */
	public function getObjectiveParent()
	{
		return $this->objectiveParent;
	}
	
	/**
	 * @param ilTestObjectiveOrientedContainer $objectiveParent
	 */
	public function setObjectiveParent($objectiveParent)
	{
		$this->objectiveParent = $objectiveParent;
	}
	
	/**
	 * Execute Command
	 */
	public function	executeCommand()
	{
		global $DIC; /* @var ILIAS\DI\Container $DIC */
		
		switch( $DIC->ctrl()->getNextClass($this) )
		{
			case "iltestevaluationgui":
				require_once 'Modules/Test/classes/class.ilTestEvaluationGUI.php';
				$gui = new ilTestEvaluationGUI($this->getTestObj());
				$gui->setObjectiveOrientedContainer($this->getObjectiveParent());
				$gui->setTestAccess($this->getTestAccess());
				$DIC->tabs()->clearTargets();
				$DIC->tabs()->clearSubTabs();
				$DIC->ctrl()->forwardCommand($gui);
				break;
				
			case 'ilassquestionpagegui':
				require_once 'Modules/Test/classes/class.ilAssQuestionPageCommandForwarder.php';
				$forwarder = new ilAssQuestionPageCommandForwarder();
				$forwarder->setTestObj($this->getTestObj());
				$forwarder->forward();
				break;
			
			default:
				
				$command = $DIC->ctrl()->getCmd(self::CMD_SHOW_PARTICIPANTS).'Cmd';
				$this->{$command}();
		}
	}
	
	/**
	 * @return ilTestParticipantsTableGUI
	 */
	protected function buildTableGUI()
	{
		global $DIC; /* @var ILIAS\DI\Container $DIC */
		require_once 'Modules/Test/classes/tables/class.ilTestParticipantsTableGUI.php';
		$tableGUI = new ilTestParticipantsTableGUI($this, self::CMD_SHOW_PARTICIPANTS);
		$tableGUI->setTitle($DIC->language()->txt('tst_tbl_results_grades'));
		return $tableGUI;
	}
	
	/**
	 * show participants command
	 */
	protected function showParticipantsCmd()
	{
		global $DIC; /* @var ILIAS\DI\Container $DIC */
		
		if( $this->getQuestionSetConfig()->areDepenciesBroken() )
		{
			ilUtil::sendFailure(
				$this->getQuestionSetConfig()->getDepenciesBrokenMessage($DIC->language())
			);
		}
		elseif( $this->getQuestionSetConfig()->areDepenciesInVulnerableState() )
		{
			ilUtil::sendInfo(
				$this->questionSetConfig->getDepenciesInVulnerableStateMessage($DIC->language())
			);
		}
		
		$manageParticipantFilter = ilTestParticipantAccessFilter::getManageParticipantsUserFilter($this->getTestObj()->getRefId());
		$accessResultsFilter = ilTestParticipantAccessFilter::getAccessResultsUserFilter($this->getTestObj()->getRefId());
		
		$participantList = $this->getTestObj()->getActiveParticipantList();
		$participantList = $participantList->getAccessFilteredList($manageParticipantFilter);
		$participantList = $participantList->getAccessFilteredList($accessResultsFilter);
		
		require_once 'Modules/Test/classes/tables/class.ilTestParticipantsTableGUI.php';
		$tableGUI = $this->buildTableGUI();
		$tableGUI->setRowKeyDataField('active_id');

		if( !$this->getQuestionSetConfig()->areDepenciesBroken() )
		{
			$tableGUI->setManageResultsCommandsEnabled(
				$this->getTestAccess()->checkManageParticipantsAccess()
			);
			
			$tableGUI->setAccessResultsCommandsEnabled(
				$this->getTestAccess()->checkParticipantsResultsAccess()
			);

			if( $participantList->hasUnfinishedPasses() )
			{
				$this->addFinishAllPassesButton($DIC->toolbar());
			}
			
			if( $participantList->hasTestResults() )
			{
				$this->addDeleteAllTestResultsButton($DIC->toolbar());
			}
		}
		
		$tableGUI->setAnonymity($this->getTestObj()->getAnonymity());
		
		$tableGUI->initColumns();
		$tableGUI->initCommands();
		
		$tableGUI->setData($participantList->getTableRows());
		
		$DIC->ui()->mainTemplate()->setContent($tableGUI->getHTML());
	}
	
	/**
	 * @param ilToolbarGUI $toolbar
	 */
	protected function addDeleteAllTestResultsButton(ilToolbarGUI $toolbar)
	{
		global $DIC; /* @var ILIAS\DI\Container $DIC */
		
		require_once  'Services/UIComponent/Button/classes/class.ilLinkButton.php';
		$delete_all_results_btn = ilLinkButton::getInstance();
		$delete_all_results_btn->setCaption('delete_all_user_data');
		$delete_all_results_btn->setUrl($DIC->ctrl()->getLinkTarget($this, 'deleteAllUserResults'));
		$toolbar->addButtonInstance($delete_all_results_btn);
	}
	
	/**
	 * @param ilToolbarGUI $toolbar
	 */
	protected function addFinishAllPassesButton(ilToolbarGUI $toolbar)
	{
		global $DIC; /* @var ILIAS\DI\Container $DIC */
		
		$finish_all_user_passes_btn = ilLinkButton::getInstance();
		$finish_all_user_passes_btn->setCaption('finish_all_user_passes');
		$finish_all_user_passes_btn->setUrl($DIC->ctrl()->getLinkTargetByClass('iltestevaluationgui', 'finishAllUserPasses'));
		$toolbar->addButtonInstance($finish_all_user_passes_btn);
	}
	
	/**
	 * Asks for a confirmation to delete all user data of the test object
	 */
	protected function deleteAllUserResultsCmd()
	{
		global $DIC; /* @var ILIAS\DI\Container $DIC */
		
		// display confirmation message
		include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
		$cgui = new ilConfirmationGUI();
		$cgui->setFormAction($DIC->ctrl()->getFormAction($this));
		$cgui->setHeaderText($DIC->language()->txt("delete_all_user_data_confirmation"));
		$cgui->setCancel($DIC->language()->txt("cancel"), self::CMD_SHOW_PARTICIPANTS);
		$cgui->setConfirm($DIC->language()->txt("proceed"), self::CMD_PERFORM_DELETE_ALL_USER_RESULTS);
		
		$DIC->ui()->mainTemplate()->setContent($cgui->getHTML());
	}
	
	/**
	 * Deletes all user data for the test object
	 */
	protected function confirmDeleteAllUserResultsCmd()
	{
		global $DIC; /* @var ILIAS\DI\Container $DIC */
		
		require_once 'Modules/Test/classes/class.ilTestParticipantAccessFilter.php';
		$accessFilter = ilTestParticipantAccessFilter::getManageParticipantsUserFilter(
			$this->getTestObj()->getRefId()
		);
		
		require_once 'Modules/Test/classes/class.ilTestParticipantData.php';
		$participantData = new ilTestParticipantData($DIC->database(), $DIC->language());
		$participantData->setParticipantAccessFilter($accessFilter);
		$participantData->load($this->getTestObj()->getTestId());
		
		$this->getTestObj()->removeTestResults($participantData);
		
		ilUtil::sendSuccess($DIC->language()->txt("tst_all_user_data_deleted"), true);
		$DIC->ctrl()->redirect($this, self::CMD_SHOW_PARTICIPANTS);
	}
	
	/**
	 * Asks for a confirmation to delete selected user data of the test object
	 */
	protected function deleteSingleUserResultsCmd()
	{
		global $DIC; /* @var ILIAS\DI\Container $DIC */
		
		if (count($_POST["chbUser"]) == 0)
		{
			ilUtil::sendInfo($DIC->language()->txt("select_one_user"), TRUE);
			$DIC->ctrl()->redirect($this);
		}
		
		include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
		$cgui = new ilConfirmationGUI();
		$cgui->setHeaderText($DIC->language()->txt("confirm_delete_single_user_data"));
		
		$cgui->setFormAction($DIC->ctrl()->getFormAction($this));
		$cgui->setCancel($DIC->language()->txt("cancel"), self::CMD_SHOW_PARTICIPANTS);
		$cgui->setConfirm($DIC->language()->txt("confirm"), self::CMD_PERFORM_DELETE_SELECTED_USER_RESULTS);
		
		require_once 'Modules/Test/classes/class.ilTestParticipantAccessFilter.php';
		$accessFilter = ilTestParticipantAccessFilter::getManageParticipantsUserFilter($this->getTestObj()->getRefId());
		
		require_once 'Modules/Test/classes/class.ilTestParticipantData.php';
		$participantData = new ilTestParticipantData($DIC->database(), $DIC->language());
		$participantData->setParticipantAccessFilter($accessFilter);
		
		$participantData->setActiveIdsFilter((array)$_POST["chbUser"]);
		
		$participantData->load($this->getTestObj()->getTestId());
		
		foreach( $participantData->getActiveIds() as $activeId )
		{
			$cgui->addItem(
				"chbUser[]", $activeId, $participantData->getFormatedFullnameByActiveId($activeId),
				ilUtil::getImagePath("icon_usr.svg"), $DIC->language()->txt("usr")
			);
		}
		
		$DIC->ui()->mainTemplate()->setContent($cgui->getHTML());
	}
	
	/**
	 * Deletes the selected user data for the test object
	 */
	protected function confirmDeleteSelectedUserDataCmd()
	{
		global $DIC; /* @var ILIAS\DI\Container $DIC */
		
		if( isset($_POST["chbUser"]) && is_array($_POST["chbUser"]) && count($_POST["chbUser"]) )
		{
			require_once 'Modules/Test/classes/class.ilTestParticipantAccessFilter.php';
			$accessFilter = ilTestParticipantAccessFilter::getManageParticipantsUserFilter($this->getTestObj()->getRefId());
			
			require_once 'Modules/Test/classes/class.ilTestParticipantData.php';
			$participantData = new ilTestParticipantData($DIC->database(), $DIC->language());
			$participantData->setParticipantAccessFilter($accessFilter);
			$participantData->setActiveIdsFilter($_POST["chbUser"]);
			
			$participantData->load($this->getTestObj()->getTestId());
			
			$this->getTestObj()->removeTestResults($participantData);
			
			ilUtil::sendSuccess($DIC->language()->txt("tst_selected_user_data_deleted"), true);
		}
		
		$DIC->ctrl()->redirect($this, self::CMD_SHOW_PARTICIPANTS);
	}
	
	/**
	 * Shows the pass overview and the answers of one ore more users for the scored pass
	 */
	protected function showDetailedResultsCmd()
	{
		if (count($_POST))
		{
			$_SESSION["show_user_results"] = $_POST["chbUser"];
		}
		$this->showUserResults($show_pass_details = TRUE, $show_answers = TRUE, $show_reached_points = TRUE);
	}
	
	/**
	 * Shows the answers of one ore more users for the scored pass
	 */
	protected function showUserAnswersCmd()
	{
		if (count($_POST))
		{
			$_SESSION["show_user_results"] = $_POST["chbUser"];
		}
		$this->showUserResults($show_pass_details = FALSE, $show_answers = TRUE);
	}
	
	/**
	 * Shows the pass overview of the scored pass for one ore more users
	 */
	protected function showPassOverviewCmd()
	{
		if (count($_POST))
		{
			$_SESSION["show_user_results"] = $_POST["chbUser"];
		}
		$this->showUserResults($show_pass_details = TRUE, $show_answers = FALSE);
	}
	
	/**
	 * Shows the pass overview of the scored pass for one ore more users
	 *
	 * @access	public
	 */
	protected function showUserResults($show_pass_details, $show_answers, $show_reached_points = FALSE)
	{
		global $DIC; /* @var ILIAS\DI\Container $DIC */
		
		$DIC->tabs()->clearTargets();
		$DIC->tabs()->clearSubTabs();
		
		$show_user_results = $_SESSION["show_user_results"];
		
		if (count($show_user_results) == 0)
		{
			ilUtil::sendInfo($DIC->language()->txt("select_one_user"), TRUE);
			$DIC->ctrl()->redirect($this, self::CMD_SHOW_PARTICIPANTS);
		}
		
		
		$template = $this->createUserResults( $show_pass_details, $show_answers, $show_reached_points, $show_user_results);
		
		if($template instanceof ilTemplate)
		{
			$DIC->ui()->mainTemplate()->setVariable("ADM_CONTENT", $template->get());
			$DIC->ui()->mainTemplate()->addCss(ilUtil::getStyleSheetLocation("output", "test_print.css", "Modules/Test"), "print");
			if ($this->getTestObj()->getShowSolutionAnswersOnly())
			{
				$DIC->ui()->mainTemplate()->addCss(ilUtil::getStyleSheetLocation("output", "test_print_hide_content.css", "Modules/Test"), "print");
			}
		}
	}
	
	/**
	 * @param $show_pass_details
	 * @param $show_answers
	 * @param $show_reached_points
	 * @param $show_user_results
	 *
	 * @return ilTemplate
	 */
	public function createUserResults($show_pass_details, $show_answers, $show_reached_points, $show_user_results)
	{
		global $DIC; /* @var ILIAS\DI\Container $DIC */
		
		// prepare generation before contents are processed (needed for mathjax)
		if( $this->isPdfDeliveryRequest() )
		{
			require_once 'Services/PDFGeneration/classes/class.ilPDFGeneration.php';
			ilPDFGeneration::prepareGeneration();
		}
		
		$DIC->tabs()->setBackTarget(
			$DIC->language()->txt('back'), $DIC->ctrl()->getLinkTarget($this, self::CMD_SHOW_PARTICIPANTS)
		);
		
		if( $this->getObjectiveParent()->isObjectiveOrientedPresentationRequired() )
		{
			require_once 'Services/Link/classes/class.ilLink.php';
			$courseLink = ilLink::_getLink($this->getObjectiveParent()->getRefId());
			$DIC->tabs()->setBack2Target($DIC->language()->txt('back_to_objective_container'), $courseLink);
		}
		
		$template = new ilTemplate("tpl.il_as_tst_participants_result_output.html", TRUE, TRUE, "Modules/Test");
		
		require_once 'Modules/Test/classes/toolbars/class.ilTestResultsToolbarGUI.php';
		$toolbar = new ilTestResultsToolbarGUI($DIC->ctrl(), $DIC->ui()->mainTemplate(), $DIC->language());
		
		$DIC->ctrl()->setParameter($this, 'pdf', '1');
		$toolbar->setPdfExportLinkTarget( $DIC->ctrl()->getLinkTarget($this, $DIC->ctrl()->getCmd()) );
		$DIC->ctrl()->setParameter($this, 'pdf', '');
		
		if( $show_answers )
		{
			if( isset($_GET['show_best_solutions']) )
			{
				$_SESSION['tst_results_show_best_solutions'] = true;
			}
			elseif( isset($_GET['hide_best_solutions']) )
			{
				$_SESSION['tst_results_show_best_solutions'] = false;
			}
			elseif( !isset($_SESSION['tst_results_show_best_solutions']) )
			{
				$_SESSION['tst_results_show_best_solutions'] = false;
			}
			
			if( $_SESSION['tst_results_show_best_solutions'] )
			{
				$DIC->ctrl()->setParameter($this, 'hide_best_solutions', '1');
				$toolbar->setHideBestSolutionsLinkTarget($DIC->ctrl()->getLinkTarget($this, $DIC->ctrl()->getCmd()));
				$DIC->ctrl()->setParameter($this, 'hide_best_solutions', '');
			}
			else
			{
				$DIC->ctrl()->setParameter($this, 'show_best_solutions', '1');
				$toolbar->setShowBestSolutionsLinkTarget($DIC->ctrl()->getLinkTarget($this, $DIC->ctrl()->getCmd()));
				$DIC->ctrl()->setParameterByClass('', 'show_best_solutions', '');
			}
		}
		
		require_once 'Modules/Test/classes/class.ilTestParticipantData.php';
		require_once 'Modules/Test/classes/class.ilTestParticipantAccessFilter.php';
		
		$participantData = new ilTestParticipantData($DIC->database(), $DIC->language());
		
		$participantData->setParticipantAccessFilter(
			ilTestParticipantAccessFilter::getAccessResultsUserFilter($this->getTestObj()->getRefId())
		);
		
		$participantData->setActiveIdsFilter($show_user_results);
		
		$participantData->load($this->getTestObj()->getTestId());
		$toolbar->setParticipantSelectorOptions($participantData->getOptionArray());
		
		$toolbar->build();
		$template->setVariable('RESULTS_TOOLBAR', $toolbar->getHTML());
		
		include_once "./Modules/Test/classes/class.ilTestServiceGUI.php";
		$serviceGUI = new ilTestServiceGUI($this->getTestObj());
		$serviceGUI->setObjectiveOrientedContainer($this->getObjectiveParent());
		$serviceGUI->setParticipantData($participantData);
		
		require_once 'Modules/Test/classes/class.ilTestSessionFactory.php';
		$testSessionFactory = new ilTestSessionFactory($this->getTestObj());
		
		$count      = 0;
		foreach ($show_user_results as $key => $active_id)
		{
			if ($this->getTestObj()->getFixedParticipants())
			{
				$active_id = $this->getTestObj()->getActiveIdOfUser( $active_id );
			}
			
			if( !in_array($active_id, $participantData->getActiveIds()) )
			{
				continue;
			}
			
			$count++;
			$results = "";
			if ($active_id > 0)
			{
				$results = $serviceGUI->getResultsOfUserOutput(
					$testSessionFactory->getSession( $active_id ),
					$active_id,
					$this->getTestObj()->_getResultPass( $active_id ),
					$this,
					$show_pass_details,
					$show_answers,
					FALSE,
					$show_reached_points
				);
			}
			if ($count < count( $show_user_results ))
			{
				$template->touchBlock( "break" );
			}
			$template->setCurrentBlock( "user_result" );
			$template->setVariable( "USER_RESULT", $results );
			$template->parseCurrentBlock();
		}
		
		if( $this->isPdfDeliveryRequest() )
		{
			require_once 'class.ilTestPDFGenerator.php';
			
			ilTestPDFGenerator::generatePDF(
				$template->get(), ilTestPDFGenerator::PDF_OUTPUT_DOWNLOAD, $this->getTestObj()->getTitle(), PDF_USER_RESULT
			);
		}
		else
		{
			return $template;
		}
	}
	
	/**
	 * @return bool
	 */
	protected function isPdfDeliveryRequest()
	{
		if( !isset($_GET['pdf']) )
		{
			return false;
		}
		
		if( !(bool)$_GET['pdf'] )
		{
			return false;
		}
		
		return true;
	}
}