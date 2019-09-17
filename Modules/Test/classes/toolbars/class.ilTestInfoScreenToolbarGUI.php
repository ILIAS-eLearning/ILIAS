<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/UIComponent/Toolbar/classes/class.ilToolbarGUI.php';
require_once 'Services/UIComponent/Button/classes/class.ilLinkButton.php';
require_once 'Services/UIComponent/Button/classes/class.ilSubmitButton.php';
require_once 'Services/Form/classes/class.ilFormPropertyGUI.php';
require_once 'Services/Form/classes/class.ilHiddenInputGUI.php';

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package     Modules/Test
 */
class ilTestInfoScreenToolbarGUI extends ilToolbarGUI
{
	private static $TARGET_CLASS_PATH_BASE = array('ilRepositoryGUI', 'ilObjTestGUI');
	
	/**
	 * @var \ILIAS\DI\Container
	 */
	protected $DIC;
	
	/**
	 * @var parent
	 */
	private $globalToolbar;

	/**
	 * @var ilDB
	 */
	protected $db;

	/**
	 * @var ilAccessHandler
	 */
	protected $access;

	/**
	 * @var ilCtrl
	 */
	protected $ctrl;

	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * @var ilPluginAdmin
	 */
	protected $pluginAdmin;
	
	/**
	 * @var ilObjTest
	 */
	protected $testOBJ;

	/**
	 * @var ilTestQuestionSetConfig
	 */
	protected $testQuestionSetConfig;

	/**
	 * @var ilTestPlayerAbstractGUI
	 */
	protected $testPlayerGUI;

	/**
	 * @var ilTestSession
	 */
	protected $testSession;

	/**
	 * @var ilTestSequence|ilTestSequenceDynamicQuestionSet
	 */
	protected $testSequence;

	/**
	 * @var string
	 */
	private $sessionLockString;

	/**
	 * @var array
	 */
	private $infoMessages = array();
	
	/**
	 * @var array
	 */
	private $failureMessages = array();

	public function __construct(ilDBInterface $db, ilAccessHandler $access, ilCtrl $ctrl, ilLanguage $lng, ilPluginAdmin $pluginAdmin)
	{
		global $DIC; /* @var ILIAS\DI\Container $DIC */
		$this->DIC = $DIC;
		$this->db = $db;
		$this->access = $access;
		$this->ctrl = $ctrl;
		$this->lng = $lng;
		$this->pluginAdmin = $pluginAdmin;
	}

	/**
	 * @return parent
	 */
	public function getGlobalToolbar()
	{
		return $this->globalToolbar;
	}

	/**
	 * @param parent $globalToolbar
	 */
	public function setGlobalToolbar($globalToolbar)
	{
		$this->globalToolbar = $globalToolbar;
	}
	
	/**
	 * @return ilObjTest
	 */
	public function getTestOBJ()
	{
		return $this->testOBJ;
	}

	/**
	 * @param ilObjTest $testOBJ
	 */
	public function setTestOBJ($testOBJ)
	{
		$this->testOBJ = $testOBJ;
	}

	/**
	 * @return ilTestQuestionSetConfig
	 */
	public function getTestQuestionSetConfig()
	{
		return $this->testQuestionSetConfig;
	}

	/**
	 * @param ilTestQuestionSetConfig $testQuestionSetConfig
	 */
	public function setTestQuestionSetConfig($testQuestionSetConfig)
	{
		$this->testQuestionSetConfig = $testQuestionSetConfig;
	}

	/**
	 * @return ilTestPlayerAbstractGUI
	 */
	public function getTestPlayerGUI()
	{
		return $this->testPlayerGUI;
	}

	/**
	 * @param ilTestPlayerAbstractGUI $testPlayerGUI
	 */
	public function setTestPlayerGUI($testPlayerGUI)
	{
		$this->testPlayerGUI = $testPlayerGUI;
	}

	/**
	 * @return ilTestSession
	 */
	public function getTestSession()
	{
		return $this->testSession;
	}

	/**
	 * @param ilTestSession $testSession
	 */
	public function setTestSession($testSession)
	{
		$this->testSession = $testSession;
	}

	/**
	 * @return ilTestSequence|ilTestSequenceDynamicQuestionSet
	 */
	public function getTestSequence()
	{
		return $this->testSequence;
	}

	/**
	 * @param ilTestSequence|ilTestSequenceDynamicQuestionSet $testSequence
	 */
	public function setTestSequence($testSequence)
	{
		$this->testSequence = $testSequence;
	}

	/**
	 * @return string
	 */
	public function getSessionLockString()
	{
		return $this->sessionLockString;
	}

	/**
	 * @param string $sessionLockString
	 */
	public function setSessionLockString($sessionLockString)
	{
		$this->sessionLockString = $sessionLockString;
	}

	/**
	 * @return array
	 */
	public function getInfoMessages()
	{
		return $this->infoMessages;
	}

	/**
	 * @param string $infoMessage
	 */
	public function addInfoMessage($infoMessage)
	{
		$this->infoMessages[] = $infoMessage;
	}

	/**
	 * @return array
	 */
	public function getFailureMessages()
	{
		return $this->failureMessages;
	}

	/**
	 * @param string $failureMessage
	 */
	public function addFailureMessage($failureMessage)
	{
		$this->failureMessages[] = $failureMessage;
	}

	public function setFormAction($formAction, $isMultipart = false, $target = '')
	{
		if($this->globalToolbar instanceof parent)
		{
			$this->globalToolbar->setFormAction($formAction, $isMultipart, $target);
		}
		else
		{
			parent::setFormAction($formAction, $isMultipart, $target);
		}
	}
	
	public function addButtonInstance(ilButtonBase $btnInstance)
	{
		if($this->globalToolbar instanceof parent)
		{
			$this->globalToolbar->addButtonInstance($btnInstance);
		}
		else
		{
			parent::addButtonInstance($btnInstance);
		}
	}
	
	public function setCloseFormTag($enabled)
	{
		if($this->globalToolbar instanceof parent)
		{
			$this->globalToolbar->setCloseFormTag($enabled);
		}
		else
		{
			parent::setCloseFormTag($enabled);
		}
	}
	
	public function addInputItem(ilToolbarItem $inputItem, $outputLabel = false)
	{
		if($this->globalToolbar instanceof parent)
		{
			$this->globalToolbar->addInputItem($inputItem, $outputLabel);
		}
		else
		{
			parent::addInputItem($inputItem, $outputLabel);
		}
	}
	
	public function addFormInput($formInput)
	{
		if($this->globalToolbar instanceof parent)
		{
			$this->globalToolbar->addFormInput($formInput);
		}
		else
		{
			parent::addFormInput($formInput);
		}
	}
	
	public function clearItems()
	{
		if($this->globalToolbar instanceof parent)
		{
			$this->globalToolbar->setItems(array());
		}
		else
		{
			$this->setItems(array());
		}
	}
	
	private function getClassName($target)
	{
		if( is_object($target) )
		{
			$target = get_class($target);
		}
		
		return $target;
	}

	private function getClassNameArray($target)
	{
		if( is_array($target) )
		{
			return $target;
		}
		
		return array($this->getClassName($target));
	}

	private function getClassPath($target)
	{
		return array_merge(self::$TARGET_CLASS_PATH_BASE, $this->getClassNameArray($target));
	}
	
	private function setParameter($target, $parameter, $value)
	{
		$this->ctrl->setParameterByClass($this->getClassName($target), $parameter, $value);
	}
	
	private function buildLinkTarget($target, $cmd = null)
	{
		return $this->ctrl->getLinkTargetByClass($this->getClassPath($target), $cmd);
	}
	
	private function buildFormAction($target)
	{
		return $this->ctrl->getFormActionByClass($this->getClassPath($target));
	}

	private function ensureInitialisedSessionLockString()
	{
		if( !strlen($this->getSessionLockString()) )
		{
			$this->setSessionLockString($this->buildSessionLockString());
		}
	}

	private function buildSessionLockString()
	{
		return md5($_COOKIE[session_name()] . time());
	}

	/**
	 * @param $testSession
	 * @param $testSequence
	 * @return bool
	 */
	private function isDeleteDynamicTestResultsButtonRequired()
	{
		if( !$this->getTestSession()->getActiveId() )
		{
			return false;
		}

		if( !$this->getTestOBJ()->isDynamicTest() )
		{
			return false;
		}

		if( !$this->getTestOBJ()->isPassDeletionAllowed() )
		{
			return false;
		}

		if( !$this->getTestSequence()->hasStarted($this->getTestSession()) )
		{
			return false;
		}

		return true;
	}

	/**
	 * @param $testSession
	 * @param $big_button
	 */
	private function populateDeleteDynamicTestResultsButton()
	{
		require_once 'Modules/Test/classes/confirmations/class.ilTestPassDeletionConfirmationGUI.php';

		$this->ctrl->setParameterByClass(
			'iltestevaluationgui', 'context', ilTestPassDeletionConfirmationGUI::CONTEXT_INFO_SCREEN
		);

		$this->setParameter('iltestevaluationgui', 'active_id', $this->getTestSession()->getActiveId());
		$this->setParameter('iltestevaluationgui', 'pass', $this->getTestSession()->getPass());

		$btn = ilLinkButton::getInstance();
		$btn->setCaption('tst_delete_dyn_test_results_btn');
		$btn->setUrl($this->buildLinkTarget('iltestevaluationgui',  'confirmDeletePass'));
		$btn->setPrimary(false);
		
		$this->addButtonInstance($btn);
	}

	private function areSkillLevelThresholdsMissing()
	{
		if( !$this->getTestOBJ()->isSkillServiceEnabled() )
		{
			return false;
		}

		if( $this->getTestOBJ()->isDynamicTest() )
		{
			$questionSetConfig = $this->getTestQuestionSetConfig();
			$questionContainerId = $questionSetConfig->getSourceQuestionPoolId();
		}
		else
		{
			$questionContainerId = $this->getTestOBJ()->getId();
		}

		require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionSkillAssignmentList.php';
		require_once 'Modules/Test/classes/class.ilTestSkillLevelThreshold.php';

		$assignmentList = new ilAssQuestionSkillAssignmentList($this->db);
		$assignmentList->setParentObjId($questionContainerId);
		$assignmentList->loadFromDb();

		foreach($assignmentList->getUniqueAssignedSkills() as $data)
		{
			foreach($data['skill']->getLevelData() as $level)
			{
				$treshold = new ilTestSkillLevelThreshold($this->db);
				$treshold->setTestId($this->getTestOBJ()->getTestId());
				$treshold->setSkillBaseId($data['skill_base_id']);
				$treshold->setSkillTrefId($data['skill_tref_id']);
				$treshold->setSkillLevelId($level['id']);

				if( !$treshold->dbRecordExists() )
				{
					return true;
				}
			}
		}

		return false;
	}

	private function getSkillLevelThresholdsMissingInfo()
	{
		$message = $this->lng->txt('tst_skl_level_thresholds_missing');
		
		$linkTarget = $this->buildLinkTarget(
			array('ilTestSkillAdministrationGUI', 'ilTestSkillLevelThresholdsGUI'),
			ilTestSkillLevelThresholdsGUI::CMD_SHOW_SKILL_THRESHOLDS
		);
		
		$link = $this->DIC->ui()->factory()->link()->standard(
			$this->DIC->language()->txt('tst_skl_level_thresholds_link'), $linkTarget
		);
		
		$msgBox = $this->DIC->ui()->factory()->messageBox()->failure($message)->withLinks(array($link));
		
		return $this->DIC->ui()->renderer()->render($msgBox);
	}
	
	private function hasFixedQuestionSetSkillAssignsLowerThanBarrier()
	{
		if( !$this->testOBJ->isFixedTest() )
		{
			return false;
		}
		
		require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionSkillAssignmentList.php';
		$assignmentList = new ilAssQuestionSkillAssignmentList($this->db);
		$assignmentList->setParentObjId($this->testOBJ->getId());
		$assignmentList->loadFromDb();
		
		return $assignmentList->hasSkillsAssignedLowerThanBarrier();
	}
	
	private function getSkillAssignBarrierInfo()
	{
		require_once 'Modules/Test/classes/class.ilObjAssessmentFolder.php';
		
		return sprintf( $this->lng->txt('tst_skill_triggerings_num_req_answers_not_reached_warn'),
			ilObjAssessmentFolder::getSkillTriggerAnswerNumberBarrier()
		);
	}

	public function build()
	{
		$this->ensureInitialisedSessionLockString();
		
		$this->setParameter($this->getTestPlayerGUI(), 'lock', $this->getSessionLockString());
		$this->setParameter($this->getTestPlayerGUI(), 'sequence', $this->getTestSession()->getLastSequence());
		$this->setParameter('ilObjTestGUI', 'ref_id', $this->getTestOBJ()->getRefId());
		
		$this->setFormAction($this->buildFormAction($this->testPlayerGUI));
		
		$online_access = false;
		if ($this->getTestOBJ()->getFixedParticipants())
		{
			include_once "./Modules/Test/classes/class.ilObjTestAccess.php";
			$online_access_result = ilObjTestAccess::_lookupOnlineTestAccess($this->getTestOBJ()->getId(), $this->getTestSession()->getUserId());
			if ($online_access_result === true)
			{
				$online_access = true;
			}
			else
			{
				$this->addInfoMessage($online_access_result);
			}
		}

		if( !$this->getTestOBJ()->getOfflineStatus() && $this->getTestOBJ()->isComplete( $this->getTestQuestionSetConfig() ) )
		{
			if ((!$this->getTestOBJ()->getFixedParticipants() || $online_access) && $this->access->checkAccess("read", "", $this->getTestOBJ()->getRefId()))
			{
				$executable = $this->getTestOBJ()->isExecutable(
					$this->getTestSession(), $this->getTestSession()->getUserId(), $allowPassIncrease = TRUE
				);
				
				if ($executable["executable"])
				{
					if( $this->getTestOBJ()->areObligationsEnabled() && $this->getTestOBJ()->hasObligations($this->getTestOBJ()->getTestId()) )
					{
						$this->addInfoMessage($this->lng->txt('tst_test_contains_obligatory_questions'));
					}

					if ($this->getTestSession()->getActiveId() > 0)
					{
						// resume test
						require_once 'Modules/Test/classes/class.ilTestPassesSelector.php';
						$testPassesSelector = new ilTestPassesSelector($this->db, $this->getTestOBJ());
						$testPassesSelector->setActiveId($this->getTestSession()->getActiveId());
						$testPassesSelector->setLastFinishedPass($this->getTestSession()->getLastFinishedPass());

						$closedPasses = $testPassesSelector->getClosedPasses();
						$existingPasses = $testPassesSelector->getExistingPasses();

						if ($existingPasses > $closedPasses)
						{
							$btn = ilSubmitButton::getInstance();
							$btn->setCaption('tst_resume_test');
							$btn->setCommand('resumePlayer');
							$btn->setPrimary(true);
							$this->addButtonInstance($btn);
						}
						else
						{
							$btn = ilSubmitButton::getInstance();
							$btn->setCaption($this->getTestOBJ()->getStartTestLabel($this->getTestSession()->getActiveId()), false);
							$btn->setCommand('startPlayer');
							$btn->setPrimary(true);
							$this->addButtonInstance($btn);
						}
					}
					else
					{
						// start new test
						$btn = ilSubmitButton::getInstance();
						$btn->setCaption($this->getTestOBJ()->getStartTestLabel($this->getTestSession()->getActiveId()), false);
						$btn->setCommand('startPlayer');
						$btn->setPrimary(true);
						$this->addButtonInstance($btn);
					}
				}
				else
				{
					$this->addInfoMessage($executable['errormessage']);
				}
			}

			if( $this->isDeleteDynamicTestResultsButtonRequired() )
			{
				$this->populateDeleteDynamicTestResultsButton();
			}

			if($this->DIC->user()->getId() == ANONYMOUS_USER_ID)
			{
				if( $this->getItems() )
				{
					$this->addSeparator();
				}
				
				require_once 'Services/Form/classes/class.ilTextInputGUI.php';
				$anonymous_id = new ilTextInputGUI($this->lng->txt('enter_anonymous_code'), 'anonymous_id');
				$anonymous_id->setSize(8);
				$this->addInputItem($anonymous_id, true);
				$button = ilSubmitButton::getInstance();
			    $button->setCaption('submit');
			    $button->setCommand('setAnonymousId');
			    $this->addButtonInstance($button);
			}
		}
		if( $this->getTestOBJ()->getOfflineStatus() && !$this->getTestQuestionSetConfig()->areDepenciesBroken() )
		{
			$message = $this->lng->txt("test_is_offline");
			
			$links = array();
			
			if($this->access->checkAccess("write", "", $this->getTestOBJ()->getRefId()))
			{
				$links[] = $this->DIC->ui()->factory()->link()->standard(
					$this->DIC->language()->txt('test_edit_settings'),
					$this->buildLinkTarget('ilobjtestsettingsgeneralgui')
				);
			}
			
			$msgBox = $this->DIC->ui()->factory()->messageBox()->info($message)->withLinks($links);
			
			$this->populateMessage($this->DIC->ui()->renderer()->render($msgBox));
		}
		
		if($this->access->checkAccess("write", "", $this->getTestOBJ()->getRefId()))
		{
			require_once 'Modules/TestQuestionPool/classes/questions/class.ilAssQuestionSkillAssignmentImportFails.php';
			$qsaImportFails = new ilAssQuestionSkillAssignmentImportFails($this->testOBJ->getId());
			require_once 'Modules/Test/classes/class.ilTestSkillLevelThresholdImportFails.php';
			$sltImportFails = new ilTestSkillLevelThresholdImportFails($this->testOBJ->getId());
			
			if( $qsaImportFails->failedImportsRegistered() || $sltImportFails->failedImportsRegistered() )
			{
				$importFailsMsg = array();
				
				if( $qsaImportFails->failedImportsRegistered() )
				{
					$importFailsMsg[] = $qsaImportFails->getFailedImportsMessage($this->lng);
				}
				
				if( $sltImportFails->failedImportsRegistered() )
				{
					$importFailsMsg[] = $sltImportFails->getFailedImportsMessage($this->lng);
				}
				
				$message = implode('<br />', $importFailsMsg);
				
				$button = $this->DIC->ui()->factory()->button()->standard(
					$this->DIC->language()->txt('ass_skl_import_fails_remove_btn'),
					$this->DIC->ctrl()->getLinkTargetByClass('ilObjTestGUI', 'removeImportFails')
				);
				
				$msgBox = $this->DIC->ui()->factory()->messageBox()->failure($message)->withButtons(array($button));
				
				$this->populateMessage($this->DIC->ui()->renderer()->render($msgBox));
			}
			elseif( $this->getTestOBJ()->isSkillServiceToBeConsidered() )
			{
				if( $this->areSkillLevelThresholdsMissing() )
				{
					$this->populateMessage($this->getSkillLevelThresholdsMissingInfo());
				}
				
				if( $this->hasFixedQuestionSetSkillAssignsLowerThanBarrier() )
				{
					$this->addInfoMessage($this->getSkillAssignBarrierInfo());
				}
			}

			if( $this->getTestQuestionSetConfig()->areDepenciesBroken() )
			{
				$this->addFailureMessage($this->getTestQuestionSetConfig()->getDepenciesBrokenMessage($this->lng));

				$this->clearItems();
			}
			elseif( $this->getTestQuestionSetConfig()->areDepenciesInVulnerableState() )
			{
				$this->addInfoMessage( $this->getTestQuestionSetConfig()->getDepenciesInVulnerableStateMessage($this->lng) );
			}
		}
	}
	
	/**
	 * @param $message
	 */
	protected function populateMessage($message)
	{
		$this->DIC->ui()->mainTemplate()->setCurrentBlock('mess');
		$this->DIC->ui()->mainTemplate()->setVariable('MESSAGE', $message);
		$this->DIC->ui()->mainTemplate()->parseCurrentBlock();
	}
	
	public function sendMessages()
	{
		ilUtil::sendInfo( array_pop($this->getInfoMessages()) );
		ilUtil::sendFailure( array_pop($this->getFailureMessages()) );
	}
}