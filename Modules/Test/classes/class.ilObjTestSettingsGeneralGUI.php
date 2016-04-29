<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * GUI class that manages the editing of general test settings/properties
 * shown on "general" subtab
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/Test
 * 
 * @ilCtrl_Calls ilObjTestSettingsGeneralGUI: ilPropertyFormGUI, ilTestSettingsChangeConfirmationGUI
 */
class ilObjTestSettingsGeneralGUI
{
	/**
	 * command constants
	 */
	const CMD_SHOW_FORM					= 'showForm';
	const CMD_SAVE_FORM					= 'saveForm';
	const CMD_CONFIRMED_SAVE_FORM		= 'confirmedSaveForm';
	const CMD_SHOW_RESET_TPL_CONFIRM	= 'showResetTemplateConfirmation';
	const CMD_CONFIRMED_RESET_TPL		= 'confirmedResetTemplate';
	
	/** @var ilCtrl $ctrl */
	protected $ctrl = null;
	
	/** @var ilAccess $access */
	protected $access = null;
	
	/** @var ilLanguage $lng */
	protected $lng = null;
	
	/** @var ilTemplate $tpl */
	protected $tpl = null;
	
	/** @var ilTree $tree */
	protected $tree = null;
	
	/** @var ilDB $db */
	protected $db = null;

	/** @var ilPluginAdmin $pluginAdmin */
	protected $pluginAdmin = null;

	/** @var ilObjUser $activeUser */
	protected $activeUser = null;

	/** @var ilObjTest $testOBJ */
	protected $testOBJ = null;

	/** @var ilObjTestGUI $testGUI */
	protected $testGUI = null;
	
	/** @var ilTestQuestionSetConfigFactory $testQuestionSetConfigFactory Factory for question set config. */
	private $testQuestionSetConfigFactory = null;

	/**
	 * object instance for currently active settings template
	 *
	 * @var $settingsTemplate ilSettingsTemplate 
	 */
	protected $settingsTemplate = null;
	
	/**
	 * the fact wether participant data exists or not, initialised by lazy loading
	 * 
	 * DO NOT ACCESS THIS VARIABLE DIRECTLY
	 * ALWAYS USE -> ilObjTestSettingsGeneralGUI::participantDataExist()
	 * 
	 * @var boolean
	 */
	private $participantDataExist = null;

	/**
	 * Constructor 
	 * 
	 * @param ilCtrl          $ctrl
	 * @param ilAccessHandler $access
	 * @param ilLanguage      $lng
	 * @param ilTemplate      $tpl
	 * @param ilDB            $db
	 * @param ilObjTestGUI    $testGUI
	 * 
	 * @return \ilObjTestSettingsGeneralGUI
	 */
	public function __construct(
		ilCtrl $ctrl, 
		ilAccessHandler $access, 
		ilLanguage $lng, 
		ilTemplate $tpl, 
		ilTree $tree,
		ilDB $db,
		ilPluginAdmin $pluginAdmin,
		ilObjUser $activeUser,
		ilObjTestGUI $testGUI
	)
	{
		$this->ctrl = $ctrl;
		$this->access = $access;
		$this->lng = $lng;
		$this->tpl = $tpl;
		$this->tree = $tree;
		$this->db = $db;
		$this->pluginAdmin = $pluginAdmin;
		$this->activeUser = $activeUser;

		$this->testGUI = $testGUI;
		$this->testOBJ = $testGUI->object;

		require_once 'Modules/Test/classes/class.ilTestQuestionSetConfigFactory.php';
		$this->testQuestionSetConfigFactory = new ilTestQuestionSetConfigFactory($this->tree, $this->db, $this->pluginAdmin, $this->testOBJ);
		
		$templateId = $this->testOBJ->getTemplate();

		if( $templateId )
		{
			include_once "Services/Administration/classes/class.ilSettingsTemplate.php";
			$this->settingsTemplate = new ilSettingsTemplate($templateId, ilObjAssessmentFolderGUI::getSettingsTemplateConfig());
		}
	}

	/**
	 * Command Execution
	 */
	public function executeCommand()
	{
		// allow only write access
		
		if (!$this->access->checkAccess("write", "", $this->testGUI->ref_id)) 
		{
			ilUtil::sendInfo($this->lng->txt("cannot_edit_test"), true);
			$this->ctrl->redirect($this->testGUI, "infoScreen");
		}
		
		// process command
		
		$nextClass = $this->ctrl->getNextClass();
		
		switch($nextClass)
		{
			default:
				$cmd = $this->ctrl->getCmd(self::CMD_SHOW_FORM).'Cmd';
				$this->$cmd();
		}
	}

	private function showFormCmd(ilPropertyFormGUI $form = null)
	{
		$this->tpl->addJavascript("./Services/JavaScript/js/Basic.js");
		
		if( $form === null )
		{
			$form = $this->buildForm();
		}
		
		$formHTML = $this->ctrl->getHTML($form);
		$msgHTML = $this->getSettingsTemplateMessageHTML();

		$this->tpl->setContent($formHTML.$msgHTML);
	}

	private function confirmedSaveFormCmd()
	{
		return $this->saveFormCmd(true);
	}

	private function fixPostValuesForInconsistentFormObjectTree(ilPropertyFormGUI $form)
	{
		$fields = array('act_starting_time', 'act_ending_time', 'starting_time', 'ending_time');
		
		foreach($fields as $field)
		{
			if( !($form->getItemByPostVar($field) instanceof ilFormPropertyGUI) )
			{
				continue;
			}
			
			if( !$form->getItemByPostVar($field)->getDisabled() )
			{
				continue;
			}
			
			unset($_POST[$field]);
		}
	}
	
	private function saveFormCmd($isConfirmedSave = false)
	{
		$form = $this->buildForm();
		
		// form validation and initialisation
		
		$errors = !$form->checkInput(); // ALWAYS CALL BEFORE setValuesByPost()
		$this->fixPostValuesForInconsistentFormObjectTree($form);
		$form->setValuesByPost(); // NEVER CALL THIS BEFORE checkInput()
									// Sarcasm? No. Because checkInput checks the form graph against the POST without
									// actually setting the values into the form. Sounds ridiculous? Indeed, and it is.

		// return to form when any form validation errors exist

		if($errors)
		{
			ilUtil::sendFailure($this->lng->txt('form_input_not_valid'));
			return $this->showFormCmd($form);
		}
		
		// return to form when online is to be set, but no questions are configured
		
		$currentQuestionSetConfig = $this->testQuestionSetConfigFactory->getQuestionSetConfig();
		if( $form->getItemByPostVar('online')->getChecked() && !$this->testOBJ->isComplete($currentQuestionSetConfig) )
		{
			$form->getItemByPostVar('online')->setAlert(
					$this->lng->txt("cannot_switch_to_online_no_questions_andor_no_mark_steps")
			);
			
			ilUtil::sendFailure($this->lng->txt('form_input_not_valid'));
			return $this->showFormCmd($form);
		}

		// solve conflicts with question set type setting with confirmation screen if required
		// determine wether question set type relating data is to be removed (questions/pools)
		
		$questionSetTypeRelatingDataCleanupRequired = false;
		
		if( $form->getItemByPostVar('question_set_type') instanceof ilFormPropertyGUI )
		{
			$oldQuestionSetType = $this->testOBJ->getQuestionSetType();
			$newQuestionSetType = $form->getItemByPostVar('question_set_type')->getValue();

			if( !$this->testOBJ->participantDataExist() && $newQuestionSetType != $oldQuestionSetType )
			{
				$oldQuestionSetConfig = $this->testQuestionSetConfigFactory->getQuestionSetConfigByType(
						$oldQuestionSetType
				);
				
				if( $oldQuestionSetConfig->doesQuestionSetRelatedDataExist() )
				{
					if( !$isConfirmedSave )
					{
						if( $oldQuestionSetType == ilObjTest::QUESTION_SET_TYPE_FIXED )
						{
							return $this->showConfirmation(
									$form, $oldQuestionSetType, $newQuestionSetType,
									$this->testOBJ->hasQuestionsWithoutQuestionpool()
							);
						}
						
						return $this->showConfirmation(
								$form, $oldQuestionSetType, $newQuestionSetType, false
						);
					}

					$questionSetTypeRelatingDataCleanupRequired = true;
				}

				if( $form->getItemByPostVar('online')->getChecked() )
				{
					$form->getItemByPostVar('online')->setChecked(false);

					if( $this->testOBJ->isOnline() )
					{
						$infoMsg = $this->lng->txt("tst_set_offline_due_to_switched_question_set_type_setting");
					}
					else
					{
						$infoMsg = $this->lng->txt("tst_cannot_online_due_to_switched_quest_set_type_setting");
					}

					ilUtil::sendInfo($infoMsg, true);
				}
			}
		}
		
		// adjust use previous answers setting due to desired question set type
		
		if( $newQuestionSetType != ilObjTest::QUESTION_SET_TYPE_FIXED )
		{
			$form->getItemByPostVar('chb_use_previous_answers')->setValue(0);
		}
				
		// perform saving the form data
		
		$this->performSaveForm($form);
		
		// clean up test mode relating configuration data (questions/questionpools)
		
		if( $questionSetTypeRelatingDataCleanupRequired )
		{
			$oldQuestionSetConfig->removeQuestionSetRelatedData();
		}
		
		// disinvite all invited users if required
			
		if( !$this->testOBJ->participantDataExist() && !$this->testOBJ->getFixedParticipants() )
		{
			foreach ($this->testOBJ->getInvitedUsers() as $usrId => $usrData)
			{
				$this->testOBJ->disinviteUser($usrId);
			}
		}		
		
		// redirect to form output
		
		ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
		$this->ctrl->redirect($this, self::CMD_SHOW_FORM);
	}

	private function performSaveForm(ilPropertyFormGUI $form)
	{
		include_once 'Services/MetaData/classes/class.ilMD.php';
		$md_obj =& new ilMD($this->testOBJ->getId(), 0, "tst");
		$md_section = $md_obj->getGeneral();

		// title
		$md_section->setTitle(ilUtil::stripSlashes($form->getItemByPostVar('title')->getValue()));
		$md_section->update();

		// Description
		$md_desc_ids = $md_section->getDescriptionIds();
		if($md_desc_ids)
		{
			$md_desc = $md_section->getDescription(array_pop($md_desc_ids));
			$md_desc->setDescription(ilUtil::stripSlashes($form->getItemByPostVar('description')->getValue()));
			$md_desc->update();
		}
		else
		{
			$md_desc = $md_section->addDescription();
			$md_desc->setDescription(ilUtil::stripSlashes($form->getItemByPostVar('description')->getValue()));
			$md_desc->save();
		}

		$this->testOBJ->setTitle(ilUtil::stripSlashes($form->getItemByPostVar('title')->getValue()));
		$this->testOBJ->setDescription(ilUtil::stripSlashes($form->getItemByPostVar('description')->getValue()));
		$this->testOBJ->update();
		
		// pool usage setting
		if( $form->getItemByPostVar('use_pool') instanceof ilFormPropertyGUI )
		{
			$this->testOBJ->setPoolUsage($form->getItemByPostVar('use_pool')->getChecked());
		}
		
		// Examview
		$this->testOBJ->setEnableExamview($form->getItemByPostVar('enable_examview')->getChecked());
		$this->testOBJ->setShowExamviewHtml($form->getItemByPostVar('show_examview_html')->getChecked());
		$this->testOBJ->setShowExamviewPdf($form->getItemByPostVar('show_examview_pdf')->getChecked());

		// online status
		$this->testOBJ->setOnline($form->getItemByPostVar('online')->getChecked());

		// activation
		if($form->getItemByPostVar('activation_type')->getChecked())
		{	
			$this->testOBJ->setActivationLimited(true);								    			
			$this->testOBJ->setActivationVisibility($form->getItemByPostVar('activation_visibility')->getChecked());

			$period = $form->getItemByPostVar("access_period");			
			$this->testOBJ->setActivationStartingTime($period->getStart()->get(IL_CAL_UNIX));
			$this->testOBJ->setActivationEndingTime($period->getEnd()->get(IL_CAL_UNIX));							
		}
		else
		{
			$this->testOBJ->setActivationLimited(false);
		}

		include_once "./Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php";
		$this->testOBJ->setIntroduction($form->getItemByPostVar('introduction')->getValue(), false, ilObjAdvancedEditing::_getUsedHTMLTagsAsString("assessment"));
		$this->testOBJ->setShowInfo($form->getItemByPostVar('showinfo')->getChecked());
		$this->testOBJ->setFinalStatement($form->getItemByPostVar('finalstatement')->getValue(), false, ilObjAdvancedEditing::_getUsedHTMLTagsAsString("assessment"));
		$this->testOBJ->setShowFinalStatement($form->getItemByPostVar('showfinalstatement')->getChecked());
		if( $form->getItemByPostVar('chb_postpone') instanceof ilFormPropertyGUI )
		{
			$this->testOBJ->setSequenceSettings($form->getItemByPostVar('chb_postpone')->getChecked());
		}
		$this->testOBJ->setShuffleQuestions($form->getItemByPostVar('chb_shuffle_questions')->getChecked());
		$this->testOBJ->setListOfQuestions($form->getItemByPostVar('list_of_questions')->getChecked());
		$listOfQuestionsOptions = $form->getItemByPostVar('list_of_questions_options')->getValue();
		if( is_array($listOfQuestionsOptions) )
		{
			$this->testOBJ->setListOfQuestionsStart( in_array('chb_list_of_questions_start', $listOfQuestionsOptions) );
			$this->testOBJ->setListOfQuestionsEnd( in_array('chb_list_of_questions_end', $listOfQuestionsOptions) );
			$this->testOBJ->setListOfQuestionsDescription( in_array('chb_list_of_questions_with_description', $listOfQuestionsOptions) );
		}
		else
		{
			$this->testOBJ->setListOfQuestionsStart(0);
			$this->testOBJ->setListOfQuestionsEnd(0);
			$this->testOBJ->setListOfQuestionsDescription(0);
		}
		
		if( $form->getItemByPostVar('mailnotification') instanceof ilFormPropertyGUI )
		{
			$this->testOBJ->setMailNotification($form->getItemByPostVar('mailnotification')->getValue());
		}
		if( $form->getItemByPostVar('mailnottype') instanceof ilFormPropertyGUI )
		{
			$this->testOBJ->setMailNotificationType($form->getItemByPostVar('mailnottype')->getChecked());
		}
		if( $form->getItemByPostVar('chb_show_marker') instanceof ilFormPropertyGUI )
		{
			$this->testOBJ->setShowMarker($form->getItemByPostVar('chb_show_marker')->getChecked());
		}
		if( $form->getItemByPostVar('chb_show_cancel') instanceof ilFormPropertyGUI )
		{
			$this->testOBJ->setShowCancel($form->getItemByPostVar('chb_show_cancel')->getChecked());
		}
		
		if($form->getItemByPostVar('kiosk') instanceof ilFormPropertyGUI)
		{
			$this->testOBJ->setKioskMode($form->getItemByPostVar('kiosk')->getChecked());
			$kioskOptions = $form->getItemByPostVar('kiosk_options')->getValue();
			if( is_array($kioskOptions) )
			{
				$this->testOBJ->setShowKioskModeTitle( in_array('kiosk_title', $kioskOptions) );
				$this->testOBJ->setShowKioskModeParticipant( in_array('kiosk_participant', $kioskOptions) );
			}
			else
			{
				$this->testOBJ->setShowKioskModeTitle( false );
				$this->testOBJ->setShowKioskModeParticipant( false );
			}
		}
		
		if( $form->getItemByPostVar('examid_in_test_pass') instanceof ilFormPropertyGUI)
		{
			$value = $form->getItemByPostVar('examid_in_test_pass')->getChecked();
			$this->testOBJ->setShowExamIdInTestPassEnabled( $value );
		}
	
		// redirect after test
		if( $form->getItemByPostVar('redirection_enabled')->getChecked() )
		{
			$this->testOBJ->setRedirectionMode( $form->getItemByPostVar('redirection_mode')->getValue() );
		}
		else
		{
			$this->testOBJ->setRedirectionMode(REDIRECT_NONE);
		}
		
		if( strlen($form->getItemByPostVar('redirection_url')->getValue()) )
		{
			$this->testOBJ->setRedirectionUrl( $form->getItemByPostVar('redirection_url')->getValue() );
		}
		else
		{
			$this->testOBJ->setRedirectionUrl(null);
		}

		if( $form->getItemByPostVar('sign_submission')->getChecked() )
		{
			$this->testOBJ->setSignSubmission( true );
		}
		else
		{
			$this->testOBJ->setSignSubmission( false );
		}

		$this->testOBJ->setEnableProcessingTime($form->getItemByPostVar('chb_processing_time')->getChecked());
		if ($this->testOBJ->getEnableProcessingTime())
		{
			$processingTime = $form->getItemByPostVar('processing_time');
			$this->testOBJ->setProcessingTime(sprintf("%02d:%02d:%02d",
				$processingTime->getHours(), $processingTime->getMinutes(), $processingTime->getSeconds()
			));
		}
		else
		{
			$this->testOBJ->setProcessingTime('');
		}
		$this->testOBJ->setResetProcessingTime($form->getItemByPostVar('chb_reset_processing_time')->getChecked());			

		if(!$this->testOBJ->participantDataExist() && $form->getItemByPostVar('chb_starting_time')->getChecked() )
		{
			$startingTimeSetting = $form->getItemByPostVar('starting_time');
			$this->testOBJ->setStartingTime(ilFormat::dateDB2timestamp(
					$startingTimeSetting->getDate()->get(IL_CAL_DATETIME)
			));
		}
		else if(!$this->testOBJ->participantDataExist())
		{
			$this->testOBJ->setStartingTime('');
		}
		
		if( $form->getItemByPostVar('chb_ending_time')->getChecked() )
		{
			$endingTimeSetting = $form->getItemByPostVar('ending_time');
			$this->testOBJ->setEndingTime(ilFormat::dateDB2timestamp(
					$endingTimeSetting->getDate()->get(IL_CAL_DATETIME)
			));
		}
		else
		{
			$this->testOBJ->setEndingTime('');
		}
		
		if($form->getItemByPostVar('forcejs') instanceof ilFormPropertyGUI)
		{
			$this->testOBJ->setForceJS($form->getItemByPostVar('forcejs')->getChecked());
		}
		
		if( $form->getItemByPostVar('title_output') instanceof ilFormPropertyGUI )
		{
			$this->testOBJ->setTitleOutput($form->getItemByPostVar('title_output')->getValue());
		}
		if( $form->getItemByPostVar('password') instanceof ilFormPropertyGUI )
		{
			$this->testOBJ->setPassword($form->getItemByPostVar('password')->getValue());
		}
		if( $form->getItemByPostVar('allowedUsers') instanceof ilFormPropertyGUI )
		{
			$this->testOBJ->setAllowedUsers($form->getItemByPostVar('allowedUsers')->getValue());
		}
		if( $form->getItemByPostVar('allowedUsersTimeGap') instanceof ilFormPropertyGUI )
		{
			$this->testOBJ->setAllowedUsersTimeGap($form->getItemByPostVar('allowedUsersTimeGap')->getValue());
		}

		// Selector for uicode characters
		global $ilSetting;
		if ($ilSetting->get('char_selector_availability') > 0)
		{
			require_once 'Services/UIComponent/CharSelector/classes/class.ilCharSelectorGUI.php';
			$char_selector = new ilCharSelectorGUI(ilCharSelectorConfig::CONTEXT_TEST);
			$char_selector->addFormProperties($form);
			$char_selector->getFormValues($form);
			$this->testOBJ->setCharSelectorAvailability($char_selector->getConfig()->getAvailability());
			$this->testOBJ->setCharSelectorDefinition($char_selector->getConfig()->getDefinition());
		}

		$this->testOBJ->setAutosave($form->getItemByPostVar('autosave')->getChecked());
		$this->testOBJ->setAutosaveIval($form->getItemByPostVar('autosave_ival')->getValue() * 1000);

		$this->testOBJ->setUsePreviousAnswers($form->getItemByPostVar('chb_use_previous_answers')->getChecked());		

		// highscore settings
		$this->testOBJ->setHighscoreEnabled((bool) $form->getItemByPostVar('highscore_enabled')->getChecked());
		$this->testOBJ->setHighscoreAnon((bool) $form->getItemByPostVar('highscore_anon')->getChecked());
		$this->testOBJ->setHighscoreAchievedTS((bool) $form->getItemByPostVar('highscore_achieved_ts')->getChecked());
		$this->testOBJ->setHighscoreScore((bool) $form->getItemByPostVar('highscore_score')->getChecked());
		$this->testOBJ->setHighscorePercentage((bool) $form->getItemByPostVar('highscore_percentage')->getChecked());
		$this->testOBJ->setHighscoreHints((bool) $form->getItemByPostVar('highscore_hints')->getChecked());
		$this->testOBJ->setHighscoreWTime((bool) $form->getItemByPostVar('highscore_wtime')->getChecked());
		$this->testOBJ->setHighscoreOwnTable((bool) $form->getItemByPostVar('highscore_own_table')->getChecked());
		$this->testOBJ->setHighscoreTopTable((bool) $form->getItemByPostVar('highscore_top_table')->getChecked());
		$this->testOBJ->setHighscoreTopNum((int) $form->getItemByPostVar('highscore_top_num')->getValue());
		
		if( !$this->testOBJ->participantDataExist() )
		{
			// question set type
			if( $form->getItemByPostVar('question_set_type') instanceof ilFormPropertyGUI )
			{
				$this->testOBJ->setQuestionSetType($form->getItemByPostVar('question_set_type')->getValue());
			}
			
			// anonymity setting
			$this->testOBJ->setAnonymity($form->getItemByPostVar('anonymity')->getValue());
			
			// nr of tries (max passes)
			$this->testOBJ->setNrOfTries($form->getItemByPostVar('nr_of_tries')->getValue());
			
			// fixed participants setting
			if( $form->getItemByPostVar('fixedparticipants') instanceof ilFormPropertyGUI )
			{
				$this->testOBJ->setFixedParticipants($form->getItemByPostVar('fixedparticipants')->getChecked());
			}
		}		

		// store settings to db
		$this->testOBJ->saveToDb(true);
			
		// Update ecs export settings
		include_once 'Modules/Test/classes/class.ilECSTestSettings.php';	
		$ecs = new ilECSTestSettings($this->testOBJ);			
		$ecs->handleSettingsUpdate();	
	}

	private function showConfirmation(ilPropertyFormGUI $form, $oldQuestionSetType, $newQuestionSetType, $hasQuestionsWithoutQuestionpool)
	{
		require_once 'Modules/Test/classes/confirmations/class.ilTestSettingsChangeConfirmationGUI.php';
		$confirmation = new ilTestSettingsChangeConfirmationGUI($this->lng, $this->testOBJ);

		$confirmation->setFormAction( $this->ctrl->getFormAction($this) );
		$confirmation->setCancel($this->lng->txt('cancel'), self::CMD_SHOW_FORM);
		$confirmation->setConfirm($this->lng->txt('confirm'), self::CMD_CONFIRMED_SAVE_FORM);

		$confirmation->setOldQuestionSetType($oldQuestionSetType);
		$confirmation->setNewQuestionSetType($newQuestionSetType);
		$confirmation->setQuestionLossInfoEnabled($hasQuestionsWithoutQuestionpool);
		$confirmation->build();

		$confirmation->populateParametersFromPropertyForm($form, $this->activeUser->getTimeZone());

		$this->tpl->setContent( $this->ctrl->getHTML($confirmation) );
	}
	
	private function buildForm()
	{
		require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->addCommandButton(self::CMD_SAVE_FORM, $this->lng->txt("save"));
		$form->setTableWidth("100%");
		$form->setId("test_properties");
		
		if( !$this->settingsTemplate || $this->formShowGeneralSection($this->settingsTemplate->getSettings()) )
		{
			// general properties
			$header = new ilFormSectionHeaderGUI();
			$header->setTitle($this->lng->txt("tst_general_properties"));
			$form->addItem($header);
		}
		
		// title & description (meta data)

		include_once 'Services/MetaData/classes/class.ilMD.php';
		$md_obj = new ilMD($this->testOBJ->getId(), 0, "tst");
		$md_section = $md_obj->getGeneral();

		$title = new ilTextInputGUI($this->lng->txt("title"), "title");
		$title->setRequired(true);
		$title->setValue($md_section->getTitle());
		$form->addItem($title);

		$ids = $md_section->getDescriptionIds();
		if($ids)
		{
			$desc_obj = $md_section->getDescription(array_pop($ids));

			$desc = new ilTextAreaInputGUI($this->lng->txt("description"), "description");
			$desc->setCols(50);
			$desc->setRows(4);
			$desc->setValue($desc_obj->getDescription());
			$form->addItem($desc);
		}

		// anonymity		
		$anonymity = new ilRadioGroupInputGUI($this->lng->txt('tst_anonymity'), 'anonymity');
		if ($this->testOBJ->participantDataExist()) $anonymity->setDisabled(true);
		$rb = new ilRadioOption($this->lng->txt('tst_anonymity_no_anonymization'), 0);
		$anonymity->addOption($rb);
		$rb = new ilRadioOption($this->lng->txt('tst_anonymity_anonymous_test'), 1);
		$anonymity->addOption($rb);
		$anonymity->setValue((int)$this->testOBJ->getAnonymity());
		$form->addItem($anonymity);
		
		// test mode (question set type)
		$questSetType = new ilRadioGroupInputGUI($this->lng->txt("tst_question_set_type"), 'question_set_type');
		$questSetTypeFixed = new ilRadioOption(
			$this->lng->txt("tst_question_set_type_fixed"), ilObjTest::QUESTION_SET_TYPE_FIXED,
			$this->lng->txt("tst_question_set_type_fixed_desc")
		);
		$questSetType->addOption($questSetTypeFixed);
		$questSetTypeRandom = new ilRadioOption(
			$this->lng->txt("tst_question_set_type_random"), ilObjTest::QUESTION_SET_TYPE_RANDOM,
			$this->lng->txt("tst_question_set_type_random_desc")
		);
		$questSetType->addOption($questSetTypeRandom);
		$questSetTypeContinues = new ilRadioOption(
			$this->lng->txt("tst_question_set_type_dynamic"), ilObjTest::QUESTION_SET_TYPE_DYNAMIC,
			$this->lng->txt("tst_question_set_type_dynamic_desc")
		);
		$questSetType->addOption($questSetTypeContinues);
		$questSetType->setValue($this->testOBJ->getQuestionSetType());
		if( $this->testOBJ->participantDataExist() )
		{
			$questSetType->setDisabled(true);
		}
		$form->addItem($questSetType);
		
		// pool usage
		$pool_usage = new ilCheckboxInputGUI($this->lng->txt("test_question_pool_usage"), "use_pool");
		$pool_usage->setValue(1);
		$pool_usage->setChecked($this->testOBJ->getPoolUsage());
		$form->addItem($pool_usage);
		
		// activation/availability  (no template support yet)
		
		include_once "Services/Object/classes/class.ilObjectActivation.php";
		$this->lng->loadLanguageModule('rep');
		
		$section = new ilFormSectionHeaderGUI();
		$section->setTitle($this->lng->txt('rep_activation_availability'));
		$form->addItem($section);
		
		// additional info only with multiple references
		$act_obj_info = $act_ref_info = "";
		if(sizeof(ilObject::_getAllReferences($this->testOBJ->getId())) > 1)
		{
			$act_obj_info = ' '.$this->lng->txt('rep_activation_online_object_info');
			$act_ref_info = $this->lng->txt('rep_activation_access_ref_info');
		}
		
		$online = new ilCheckboxInputGUI($this->lng->txt('rep_activation_online'),'online');
		$online->setChecked($this->testOBJ->isOnline());
		$online->setInfo($this->lng->txt('tst_activation_online_info').$act_obj_info);
		$form->addItem($online);				
							
		$act_type = new ilCheckboxInputGUI($this->lng->txt('rep_visibility_until'), 'activation_type');
		$act_type->setChecked($this->testOBJ->isActivationLimited());
		// $act_type->setInfo($this->lng->txt('tst_availability_until_info'));
		
			$this->tpl->addJavaScript('./Services/Form/js/date_duration.js');
			include_once "Services/Form/classes/class.ilDateDurationInputGUI.php";
			$dur = new ilDateDurationInputGUI($this->lng->txt("rep_time_period"), "access_period");
			$dur->setShowTime(true);						
			$date = $this->testOBJ->getActivationStartingTime();				
			$dur->setStart(new ilDateTime($date ? $date : time(), IL_CAL_UNIX));
			$dur->setStartText($this->lng->txt('rep_activation_limited_start'));				
			$date = $this->testOBJ->getActivationEndingTime();
			$dur->setEnd(new ilDateTime($date ? $date : time(), IL_CAL_UNIX));
			$dur->setEndText($this->lng->txt('rep_activation_limited_end'));				
			$act_type->addSubItem($dur);

			$visible = new ilCheckboxInputGUI($this->lng->txt('rep_activation_limited_visibility'), 'activation_visibility');
			$visible->setInfo($this->lng->txt('tst_activation_limited_visibility_info'));
			$visible->setChecked($this->testOBJ->getActivationVisibility());
			$act_type->addSubItem($visible);
			
		$form->addItem($act_type);
		
		if( !$this->settingsTemplate || $this->formShowBeginningEndingInformation($this->settingsTemplate->getSettings()) )
		{
			// general properties
			$header = new ilFormSectionHeaderGUI();
			$header->setTitle($this->lng->txt("tst_beginning_ending_information"));
			$form->addItem($header);
		}

		// introduction
		$intro = new ilTextAreaInputGUI($this->lng->txt("tst_introduction"), "introduction");
		$intro->setValue($this->testOBJ->prepareTextareaOutput($this->testOBJ->getIntroduction()));
		$intro->setRows(10);
		$intro->setCols(80);
		$intro->setUseRte(TRUE);
		$intro->addPlugin("latex");
		$intro->addButton("latex");
		$intro->setRTESupport($this->testOBJ->getId(), "tst", "assessment");
		$intro->setRteTagSet('full');
		$intro->setInfo($this->lng->txt('intro_desc'));
		// showinfo
		$showinfo = new ilCheckboxInputGUI('', "showinfo");
		$showinfo->setValue(1);
		$showinfo->setChecked($this->testOBJ->getShowInfo());
		$showinfo->setOptionTitle($this->lng->txt("showinfo"));
		$showinfo->setInfo($this->lng->txt("showinfo_desc"));
		$intro->addSubItem($showinfo);
		$form->addItem($intro);

		// final statement
		$finalstatement = new ilTextAreaInputGUI($this->lng->txt("final_statement"), "finalstatement");
		$finalstatement->setValue($this->testOBJ->prepareTextareaOutput($this->testOBJ->getFinalStatement()));
		$finalstatement->setRows(10);
		$finalstatement->setCols(80);
		$finalstatement->setUseRte(TRUE);
		$finalstatement->addPlugin("latex");
		$finalstatement->addButton("latex");
		$finalstatement->setRTESupport($this->testOBJ->getId(), "tst", "assessment");
		$finalstatement->setRteTagSet('full');
		// show final statement
		$showfinal = new ilCheckboxInputGUI('', "showfinalstatement");
		$showfinal->setValue(1);
		$showfinal->setChecked($this->testOBJ->getShowFinalStatement());
		$showfinal->setOptionTitle($this->lng->txt("final_statement_show"));
		$showfinal->setInfo($this->lng->txt("final_statement_show_desc"));
		$finalstatement->addSubItem($showfinal);
		$form->addItem($finalstatement);

		// examview
		$enable_examview = new ilCheckboxInputGUI($this->lng->txt("enable_examview"), 'enable_examview');
		$enable_examview->setValue(1);
		$enable_examview->setChecked($this->testOBJ->getEnableExamview());
		$enable_examview->setInfo($this->lng->txt("enable_examview_desc"));
			$show_examview_html = new ilCheckboxInputGUI('', 'show_examview_html');
			$show_examview_html->setValue(1);
			$show_examview_html->setChecked($this->testOBJ->getShowExamviewHtml());
			$show_examview_html->setOptionTitle($this->lng->txt("show_examview_html"));
			$show_examview_html->setInfo($this->lng->txt("show_examview_html_desc"));
			$enable_examview->addSubItem($show_examview_html);
			$show_examview_pdf = new ilCheckboxInputGUI('', 'show_examview_pdf');
			$show_examview_pdf->setValue(1);
			$show_examview_pdf->setChecked($this->testOBJ->getShowExamviewPdf());
			$show_examview_pdf->setOptionTitle($this->lng->txt("show_examview_pdf"));
			$show_examview_pdf->setInfo($this->lng->txt("show_examview_pdf_desc"));
			$enable_examview->addSubItem($show_examview_pdf);
		$form->addItem($enable_examview);
		
		if( !$this->settingsTemplate || $this->formShowSessionSection($this->settingsTemplate->getSettings()) )
		{
			// session properties
			$sessionheader = new ilFormSectionHeaderGUI();
			$sessionheader->setTitle($this->lng->txt("tst_session_settings"));
			$form->addItem($sessionheader);
		}

		// max. number of passes
		$nr_of_tries = new ilTextInputGUI($this->lng->txt("tst_nr_of_tries"), "nr_of_tries");
		$nr_of_tries->setSize(3);
		$nr_of_tries->setValue($this->testOBJ->getNrOfTries());
		$nr_of_tries->setRequired(true);
		$nr_of_tries->setSuffix($this->lng->txt("0_unlimited"));
		$total = $this->testOBJ->evalTotalPersons();
		if ($total) $nr_of_tries->setDisabled(true);
		$form->addItem($nr_of_tries);

		// enable max. processing time
		$processing = new ilCheckboxInputGUI($this->lng->txt("tst_processing_time"), "chb_processing_time");
		$processing->setValue(1);
		//$processing->setOptionTitle($this->lng->txt("enabled"));

		if( $this->settingsTemplate && $this->getTemplateSettingValue('chb_processing_time') )
		{
			$processing->setChecked(true);
		}
		else
		{
			$processing->setChecked($this->testOBJ->getEnableProcessingTime());
		}
		
		// max. processing time
		$processingtime = new ilDurationInputGUI('', 'processing_time');
		$ptime = $this->testOBJ->getProcessingTimeAsArray();
		$processingtime->setHours($ptime['hh']);
		$processingtime->setMinutes($ptime['mm']);
		$processingtime->setSeconds($ptime['ss']);
		$processingtime->setShowMonths(false);
		$processingtime->setShowDays(false);
		$processingtime->setShowHours(true);
		$processingtime->setShowMinutes(true);
		$processingtime->setShowSeconds(true);
		$processingtime->setInfo($this->lng->txt("tst_processing_time_desc"));
		$processing->addSubItem($processingtime);

		// reset max. processing time
		$resetprocessing = new ilCheckboxInputGUI('', "chb_reset_processing_time");
		$resetprocessing->setValue(1);
		$resetprocessing->setOptionTitle($this->lng->txt("tst_reset_processing_time"));
		$resetprocessing->setChecked($this->testOBJ->getResetProcessingTime());
		$resetprocessing->setInfo($this->lng->txt("tst_reset_processing_time_desc"));
		$processing->addSubItem($resetprocessing);
		$form->addItem($processing);

		// enable starting time
		$enablestartingtime = new ilCheckboxInputGUI($this->lng->txt("tst_starting_time"), "chb_starting_time");
		$enablestartingtime->setValue(1);
		//$enablestartingtime->setOptionTitle($this->lng->txt("enabled"));
		if( $this->settingsTemplate && $this->getTemplateSettingValue('chb_starting_time') )
		{
			$enablestartingtime->setChecked(true);
		}
		else
		{
			$enablestartingtime->setChecked(strlen($this->testOBJ->getStartingTime()));
		}
		// starting time
		$startingtime = new ilDateTimeInputGUI('', 'starting_time');
		$startingtime->setShowDate(true);
		$startingtime->setShowTime(true);
		if( strlen($this->testOBJ->getStartingTime()) )
		{
			$startingtime->setDate(new ilDateTime($this->testOBJ->getStartingTime(), IL_CAL_TIMESTAMP));		
		}
		else
		{
			$startingtime->setDate(new ilDateTime(time(), IL_CAL_UNIX));
		}
		$enablestartingtime->addSubItem($startingtime);
		$form->addItem($enablestartingtime);
		if( $this->testOBJ->participantDataExist() )
		{
			$enablestartingtime->setDisabled(true);
			$startingtime->setDisabled(true);
		}
		
		// enable ending time
		$enableendingtime = new ilCheckboxInputGUI($this->lng->txt("tst_ending_time"), "chb_ending_time");
		$enableendingtime->setValue(1);
		//$enableendingtime->setOptionTitle($this->lng->txt("enabled"));
		if ($this->settingsTemplate && $this->getTemplateSettingValue('chb_ending_time') )
		$enableendingtime->setChecked(true);
		else
		$enableendingtime->setChecked(strlen($this->testOBJ->getEndingTime()));
		// ending time
		$endingtime = new ilDateTimeInputGUI('', 'ending_time');
		$endingtime->setShowDate(true);
		$endingtime->setShowTime(true);
		if (strlen($this->testOBJ->getEndingTime()))
		{
			$endingtime->setDate(new ilDateTime($this->testOBJ->getEndingTime(), IL_CAL_TIMESTAMP));
		}
		else
		{
			$endingtime->setDate(new ilDateTime(time(), IL_CAL_UNIX));
		}
		$enableendingtime->addSubItem($endingtime);
		$form->addItem($enableendingtime);
		
		// test password
		$password = new ilTextInputGUI($this->lng->txt("tst_password"), "password");
		$password->setSize(20);
		$password->setValue($this->testOBJ->getPassword());
		$password->setInfo($this->lng->txt("tst_password_details"));
		$form->addItem($password);

		if( !$this->settingsTemplate || $this->formShowPresentationSection($this->settingsTemplate->getSettings()) )
		{
			// sequence properties
			$seqheader = new ilFormSectionHeaderGUI();
			$seqheader->setTitle($this->lng->txt("tst_presentation_properties"));
			$form->addItem($seqheader);
		}

		// use previous answers
		$prevanswers = new ilCheckboxInputGUI($this->lng->txt("tst_use_previous_answers"), "chb_use_previous_answers");
		$prevanswers->setValue(1);
		$prevanswers->setChecked($this->testOBJ->getUsePreviousAnswers());
		$prevanswers->setInfo($this->lng->txt("tst_use_previous_answers_description"));
		$form->addItem($prevanswers);

		// force js
		$forcejs = new ilCheckboxInputGUI($this->lng->txt("forcejs_short"), "forcejs");
		$forcejs->setValue(1);
		$forcejs->setChecked($this->testOBJ->getForceJS());
		$forcejs->setOptionTitle($this->lng->txt("forcejs"));
		$forcejs->setInfo($this->lng->txt("forcejs_desc"));
		$form->addItem($forcejs);

		// question title output
		$title_output = new ilRadioGroupInputGUI($this->lng->txt("tst_title_output"), "title_output");
		$title_output->addOption(new ilRadioOption($this->lng->txt("tst_title_output_full"), 0, ''));
		$title_output->addOption(new ilRadioOption($this->lng->txt("tst_title_output_hide_points"), 1, ''));
		$title_output->addOption(new ilRadioOption($this->lng->txt("tst_title_output_no_title"), 2, ''));
		$title_output->setValue($this->testOBJ->getTitleOutput());
		$title_output->setInfo($this->lng->txt("tst_title_output_description"));
		$form->addItem($title_output);

		// selector for unicode characters
		global $ilSetting;
		if ($ilSetting->get('char_selector_availability') > 0)
		{
			require_once 'Services/UIComponent/CharSelector/classes/class.ilCharSelectorGUI.php';
			$char_selector = new ilCharSelectorGUI(ilCharSelectorConfig::CONTEXT_TEST);
			$char_selector->getConfig()->setAvailability($this->testOBJ->getCharSelectorAvailability());
			$char_selector->getConfig()->setDefinition($this->testOBJ->getCharSelectorDefinition());
			$char_selector->addFormProperties($form);
			$char_selector->setFormValues($form);
		}
		
		// Autosave
		$autosave_output = new ilCheckboxInputGUI($this->lng->txt('autosave'), 'autosave');
		$autosave_output->setValue(1);
		$autosave_output->setChecked($this->testOBJ->getAutosave());
		$autosave_output->setInfo($this->lng->txt('autosave_info'));
		
		$autosave_interval = new ilTextInputGUI($this->lng->txt('autosave_ival'), 'autosave_ival');
		$autosave_interval->setSize(10);
		$autosave_interval->setValue($this->testOBJ->getAutosaveIval()/1000);
		$autosave_interval->setInfo($this->lng->txt('autosave_ival_info'));
		$autosave_output->addSubItem($autosave_interval);
		$form->addItem($autosave_output);
		
		
		if( !$this->settingsTemplate || $this->formShowSequenceSection($this->settingsTemplate->getSettings()) )
		{
			// sequence properties
			$seqheader = new ilFormSectionHeaderGUI();
			$seqheader->setTitle($this->lng->txt("tst_sequence_properties"));
			$form->addItem($seqheader);
		}
	
		// postpone questions
		$postpone = new ilCheckboxInputGUI($this->lng->txt("tst_postpone"), "chb_postpone");
		$postpone->setValue(1);
		$postpone->setChecked($this->testOBJ->getSequenceSettings());
		$postpone->setInfo($this->lng->txt("tst_postpone_description"));
		$form->addItem($postpone);
		
		// shuffle questions
		$shuffle = new ilCheckboxInputGUI($this->lng->txt("tst_shuffle_questions"), "chb_shuffle_questions");
		$shuffle->setValue(1);
		$shuffle->setChecked($this->testOBJ->getShuffleQuestions());
		$shuffle->setInfo($this->lng->txt("tst_shuffle_questions_description"));
		$form->addItem($shuffle);

		// show list of questions
		$list_of_questions = new ilCheckboxInputGUI($this->lng->txt("tst_show_summary"), "list_of_questions");
		//$list_of_questions->setOptionTitle($this->lng->txt("tst_show_summary"));
		$list_of_questions->setValue(1);
		$list_of_questions->setChecked($this->testOBJ->getListOfQuestions());
		$list_of_questions->setInfo($this->lng->txt("tst_show_summary_description"));

		$list_of_questions_options = new ilCheckboxGroupInputGUI('', "list_of_questions_options");
		$list_of_questions_options->addOption(new ilCheckboxOption($this->lng->txt("tst_list_of_questions_start"), 'chb_list_of_questions_start', ''));
		$list_of_questions_options->addOption(new ilCheckboxOption($this->lng->txt("tst_list_of_questions_end"), 'chb_list_of_questions_end', ''));
		$list_of_questions_options->addOption(new ilCheckboxOption($this->lng->txt("tst_list_of_questions_with_description"), 'chb_list_of_questions_with_description', ''));
		$values = array();
		if ($this->testOBJ->getListOfQuestionsStart()) array_push($values, 'chb_list_of_questions_start');
		if ($this->testOBJ->getListOfQuestionsEnd()) array_push($values, 'chb_list_of_questions_end');
		if ($this->testOBJ->getListOfQuestionsDescription()) array_push($values, 'chb_list_of_questions_with_description');
		$list_of_questions_options->setValue($values);

		$list_of_questions->addSubItem($list_of_questions_options);
		$form->addItem($list_of_questions);

		// show question marking
		$marking = new ilCheckboxInputGUI($this->lng->txt("question_marking"), "chb_show_marker");
		$marking->setValue(1);
		$marking->setChecked($this->testOBJ->getShowMarker());
		$marking->setInfo($this->lng->txt("question_marking_description"));
		$form->addItem($marking);

		// show suspend test
		$cancel = new ilCheckboxInputGUI($this->lng->txt("tst_show_cancel"), "chb_show_cancel");
		$cancel->setValue(1);
		$cancel->setChecked($this->testOBJ->getShowCancel());
		$cancel->setInfo($this->lng->txt("tst_show_cancel_description"));
		$form->addItem($cancel);

		if( !$this->settingsTemplate || $this->formShowNotificationSection($this->settingsTemplate->getSettings()) )
		{
			// notifications
			$notifications = new ilFormSectionHeaderGUI();
			$notifications->setTitle($this->lng->txt("tst_mail_notification"));
			$form->addItem($notifications);
		}

		// mail notification
		$mailnotification = new ilRadioGroupInputGUI($this->lng->txt("tst_finish_notification"), "mailnotification");
		$mailnotification->addOption(new ilRadioOption($this->lng->txt("tst_finish_notification_no"), 0, ''));
		$mailnotification->addOption(new ilRadioOption($this->lng->txt("tst_finish_notification_simple"), 1, ''));
		$mailnotification->addOption(new ilRadioOption($this->lng->txt("tst_finish_notification_advanced"), 2, ''));
		$mailnotification->setValue($this->testOBJ->getMailNotification());
		$form->addItem($mailnotification);

		$mailnottype = new ilCheckboxInputGUI('', "mailnottype");
		$mailnottype->setValue(1);
		$mailnottype->setOptionTitle($this->lng->txt("mailnottype"));
		$mailnottype->setChecked($this->testOBJ->getMailNotificationType());
		$form->addItem($mailnottype);
		
		/* This options always active (?) */
		$highscore_head = new ilFormSectionHeaderGUI();
		$highscore_head->setTitle($this->lng->txt("tst_highscore_options"));
		$form->addItem($highscore_head);		

		$highscore = new ilCheckboxInputGUI($this->lng->txt("tst_highscore_enabled"), "highscore_enabled");
		$highscore->setValue(1);
		$highscore->setChecked($this->testOBJ->getHighscoreEnabled());
		$highscore->setInfo($this->lng->txt("tst_highscore_description"));
		$form->addItem($highscore);
		
		$highscore_anon = new ilCheckboxInputGUI($this->lng->txt("tst_highscore_anon"), "highscore_anon");
		$highscore_anon->setValue(1);
		$highscore_anon->setChecked($this->testOBJ->getHighscoreAnon());
		$highscore_anon->setInfo($this->lng->txt("tst_highscore_anon_description"));
		$highscore->addSubItem($highscore_anon);

		$highscore_achieved_ts = new ilCheckboxInputGUI($this->lng->txt("tst_highscore_achieved_ts"), "highscore_achieved_ts");
		$highscore_achieved_ts->setValue(1);
		$highscore_achieved_ts->setChecked($this->testOBJ->getHighscoreAchievedTS());
		$highscore_achieved_ts->setInfo($this->lng->txt("tst_highscore_achieved_ts_description"));
		$highscore->addSubItem($highscore_achieved_ts);
		
		$highscore_score = new ilCheckboxInputGUI($this->lng->txt("tst_highscore_score"), "highscore_score");
		$highscore_score->setValue(1);
		$highscore_score->setChecked($this->testOBJ->getHighscoreScore());
		$highscore_score->setInfo($this->lng->txt("tst_highscore_score_description"));
		$highscore->addSubItem($highscore_score);

		$highscore_percentage = new ilCheckboxInputGUI($this->lng->txt("tst_highscore_percentage"), "highscore_percentage");
		$highscore_percentage->setValue(1);
		$highscore_percentage->setChecked($this->testOBJ->getHighscorePercentage());
		$highscore_percentage->setInfo($this->lng->txt("tst_highscore_percentage_description"));
		$highscore->addSubItem($highscore_percentage);

		$highscore_hints = new ilCheckboxInputGUI($this->lng->txt("tst_highscore_hints"), "highscore_hints");
		$highscore_hints->setValue(1);
		$highscore_hints->setChecked($this->testOBJ->getHighscoreHints()); 
		$highscore_hints->setInfo($this->lng->txt("tst_highscore_hints_description"));
		$highscore->addSubItem($highscore_hints);

		$highscore_wtime = new ilCheckboxInputGUI($this->lng->txt("tst_highscore_wtime"), "highscore_wtime");
		$highscore_wtime->setValue(1);
		$highscore_wtime->setChecked($this->testOBJ->getHighscoreWTime());
		$highscore_wtime->setInfo($this->lng->txt("tst_highscore_wtime_description"));
		$highscore->addSubItem($highscore_wtime);
		
		$highscore_own_table = new ilCheckboxInputGUI($this->lng->txt("tst_highscore_own_table"), "highscore_own_table");
		$highscore_own_table->setValue(1);
		$highscore_own_table->setChecked($this->testOBJ->getHighscoreOwnTable());
		$highscore_own_table->setInfo($this->lng->txt("tst_highscore_own_table_description"));
		$highscore->addSubItem($highscore_own_table);

		$highscore_top_table = new ilCheckboxInputGUI($this->lng->txt("tst_highscore_top_table"), "highscore_top_table");
		$highscore_top_table->setValue(1);
		$highscore_top_table->setChecked($this->testOBJ->getHighscoreTopTable());
		$highscore_top_table->setInfo($this->lng->txt("tst_highscore_top_table_description"));
		$highscore->addSubItem($highscore_top_table);
		
		$highscore_top_num = new ilTextInputGUI($this->lng->txt("tst_highscore_top_num"), "highscore_top_num");
		$highscore_top_num->setSize(4);
		$highscore_top_num->setSuffix($this->lng->txt("tst_highscore_top_num_unit"));
		$highscore_top_num->setValue($this->testOBJ->getHighscoreTopNum());
		$highscore_top_num->setInfo($this->lng->txt("tst_highscore_top_num_description"));
		$highscore->addSubItem($highscore_top_num);
		
		if( !$this->settingsTemplate || $this->formShowTestExecutionSection($this->settingsTemplate->getSettings()) )
		{
			$testExecution = new ilFormSectionHeaderGUI();
			$testExecution->setTitle($this->lng->txt("tst_test_execution"));
			$form->addItem($testExecution);
		}

		// kiosk mode
		$kiosk = new ilCheckboxInputGUI($this->lng->txt("kiosk"), "kiosk");
		$kiosk->setValue(1);
		$kiosk->setChecked($this->testOBJ->getKioskMode());
		$kiosk->setInfo($this->lng->txt("kiosk_description"));

		// kiosk mode options
		$kiosktitle = new ilCheckboxGroupInputGUI($this->lng->txt("kiosk_options"), "kiosk_options");
		$kiosktitle->addOption(new ilCheckboxOption($this->lng->txt("kiosk_show_title"), 'kiosk_title', ''));
		$kiosktitle->addOption(new ilCheckboxOption($this->lng->txt("kiosk_show_participant"), 'kiosk_participant', ''));
		$values = array();
		if ($this->testOBJ->getShowKioskModeTitle()) array_push($values, 'kiosk_title');
		if ($this->testOBJ->getShowKioskModeParticipant()) array_push($values, 'kiosk_participant');
		$kiosktitle->setValue($values);
		$kiosktitle->setInfo($this->lng->txt("kiosk_options_desc"));
		$kiosk->addSubItem($kiosktitle);

		$form->addItem($kiosk);

		$examIdInPass = new ilCheckboxInputGUI($this->lng->txt('examid_in_test_pass'), 'examid_in_test_pass');
		$examIdInPass->setInfo($this->lng->txt('examid_in_test_pass_desc'));
		$examIdInPass->setChecked($this->testOBJ->isShowExamIdInTestPassEnabled());
		$form->addItem($examIdInPass);

		$redirection_mode = $this->testOBJ->getRedirectionMode();
		$rm_enabled = new ilCheckboxInputGUI($this->lng->txt('redirect_after_finishing_tst'), 'redirection_enabled' );
		$rm_enabled->setInfo($this->lng->txt('redirect_after_finishing_tst_desc'));
		$rm_enabled->setChecked($redirection_mode == '0' ? false : true);
			$radio_rm = new ilRadioGroupInputGUI($this->lng->txt('redirect_after_finishing_tst'), 'redirection_mode');
			$always = new ilRadioOption($this->lng->txt('tst_results_access_always'), REDIRECT_ALWAYS);
			$radio_rm->addOption($always);
			$kiosk = new ilRadioOption($this->lng->txt('redirect_in_kiosk_mode'), REDIRECT_KIOSK);
			$radio_rm->addOption($kiosk);
			$radio_rm->setValue(in_array($redirection_mode, array(REDIRECT_ALWAYS, REDIRECT_KIOSK)) ? $redirection_mode : REDIRECT_ALWAYS);
		$rm_enabled->addSubItem($radio_rm);
			$redirection_url = new ilTextInputGUI($this->lng->txt('redirection_url'), 'redirection_url');
			$redirection_url->setValue((string)$this->testOBJ->getRedirectionUrl());
			$redirection_url->setRequired(true);
		$rm_enabled->addSubItem($redirection_url);

		$form->addItem($rm_enabled);

		// Sign submission
		$sign_submission = $this->testOBJ->getSignSubmission();
		$sign_submission_enabled = new ilCheckboxInputGUI($this->lng->txt('sign_submission'), 'sign_submission');
		$sign_submission_enabled->setChecked($sign_submission);
		$sign_submission_enabled->setInfo($this->lng->txt('sign_submission_info'));
		$form->addItem($sign_submission_enabled);

		if( !$this->settingsTemplate || $this->formShowParticipantSection($this->settingsTemplate->getSettings()) )
		{
			// participants properties
			$restrictions = new ilFormSectionHeaderGUI();
			$restrictions->setTitle($this->lng->txt("tst_max_allowed_users"));
			$form->addItem($restrictions);
		}
						
		$fixedparticipants = new ilCheckboxInputGUI($this->lng->txt('participants_invitation'), "fixedparticipants");
		$fixedparticipants->setValue(1);
		$fixedparticipants->setChecked($this->testOBJ->getFixedParticipants());
		$fixedparticipants->setOptionTitle($this->lng->txt("tst_allow_fixed_participants"));
		$fixedparticipants->setInfo($this->lng->txt("participants_invitation_description"));
		$invited_users = $this->testOBJ->getInvitedUsers();
		if ($total && (count($invited_users) == 0))
		{
			$fixedparticipants->setDisabled(true);
		}
		$form->addItem($fixedparticipants);

		// simultaneous users
		$simul = new ilTextInputGUI($this->lng->txt("tst_allowed_users"), "allowedUsers");
		$simul->setSize(3);
		$simul->setValue(($this->testOBJ->getAllowedUsers()) ? $this->testOBJ->getAllowedUsers() : '');
		$form->addItem($simul);

		// idle time
		$idle = new ilTextInputGUI($this->lng->txt("tst_allowed_users_time_gap"), "allowedUsersTimeGap");
		$idle->setSize(4);
		$idle->setSuffix($this->lng->txt("seconds"));
		$idle->setValue(($this->testOBJ->getAllowedUsersTimeGap()) ? $this->testOBJ->getAllowedUsersTimeGap() : '');
		$form->addItem($idle);
				
		// Edit ecs export settings
		include_once 'Modules/Test/classes/class.ilECSTestSettings.php';
		$ecs = new ilECSTestSettings($this->testOBJ);		
		$ecs->addSettingsToForm($form, 'tst');

		// remove items when using template
		if($this->settingsTemplate)
		{
			foreach($this->settingsTemplate->getSettings() as $id => $item)
			{
				if($item["hide"])
				{
					$form->removeItemByPostVar($id);
				}
			}
		}
		
		return $form;
	}

	/**
	 * Enable all settings - Confirmation
	 */
	private function showResetTemplateConfirmationCmd()
	{
		require_once 'Services/Utilities/classes/class.ilConfirmationGUI.php';
		$confirmationGUI = new ilConfirmationGUI();
		
		$confirmationGUI->setFormAction($this->ctrl->getFormAction($this));
		$confirmationGUI->setHeaderText($this->lng->txt("test_confirm_template_reset"));
		$confirmationGUI->setCancel($this->lng->txt('cancel'), self::CMD_SHOW_FORM);
		$confirmationGUI->setConfirm($this->lng->txt('confirm'), self::CMD_CONFIRMED_RESET_TPL);
		
		$this->tpl->setContent( $this->ctrl->getHTML($confirmationGUI) );
	}

	/**
	 * Enable all settings - remove template
	 */
	private function confirmedResetTemplateCmd()
	{
		$this->testOBJ->setTemplate(null);
		$this->testOBJ->saveToDB();

		ilUtil::sendSuccess($this->lng->txt("test_template_reset"), true);
		$this->ctrl->redirect($this, self::CMD_SHOW_FORM);
	}
	
	protected function getTemplateSettingValue($settingName)
	{
		if( !$this->settingsTemplate )
		{
			return null;
		}
		
		$templateSettings = $this->settingsTemplate->getSettings();
		
		if( !isset($templateSettings[$settingName]) )
		{
			return false;
		}
		
		return $templateSettings[$settingName]['value'];
	}

	protected function getSettingsTemplateMessageHTML()
	{
		if( $this->settingsTemplate )
		{
			global $tpl;

			$link = $this->ctrl->getLinkTarget($this, self::CMD_SHOW_RESET_TPL_CONFIRM);
			$link = "<a href=\"".$link."\">".$this->lng->txt("test_using_template_link")."</a>";
			
			$msgHTML = $tpl->getMessageHTML(
				sprintf($this->lng->txt("test_using_template"), $this->settingsTemplate->getTitle(), $link), "info"
			);
			
			$msgHTML = "<div style=\"margin-top:10px\">$msgHTML</div>";
		}
		else
		{
			$msgHTML = '';
		}
		
		return $msgHTML;
	}

	private function formShowGeneralSection($templateData)
	{
		// alway show because of title and description
		return true;
	}

	private function formShowBeginningEndingInformation($templateData)
	{
		// show always because of statement text areas
		return true;
	}

	private function formShowSessionSection($templateData)
	{
		// show always because of "nr_of_tries", "chb_processing_time", "chb_starting_time", "chb_ending_time"
		return true;
	}

	private function formShowPresentationSection($templateData)
	{
		// show always because of "previous answer" setting
		return true;
	}
	
	private function formShowSequenceSection($templateData)
	{
		// show always because of "list of question" and "shuffle"
		return true;
	}

	private function formShowNotificationSection($templateData)
	{
		$fields = array(
			'mailnotification',
			'mailnottype',
		);
		return $this->formsectionHasVisibleFields($templateData, $fields);
	}

	private function formShowTestExecutionSection($templateData)
	{
		return true; // remove this when 'eredirection_enabled' and 'sign_submission' become hideable
		
		$fields = array(
			'kiosk',
			'redirection_enabled', 'sign_submission' // not hideable up to now
		);
		return $this->formsectionHasVisibleFields($templateData, $fields);
	}
	
	private function formShowParticipantSection($templateData)
	{
		$fields = array(
			'fixedparticipants',
			'allowedUsers',
			'allowedUsersTimeGap',
		);
		return $this->formsectionHasVisibleFields($templateData, $fields);
	}

	private function formsectionHasVisibleFields($templateData, $fields)
	{
		foreach($fields as $fld)
		{
			if( !isset($templateData[$fld]) || !$templateData[$fld]['hide'] )
			{
				return true;
			}
		}
		
		return false;
	}
}
