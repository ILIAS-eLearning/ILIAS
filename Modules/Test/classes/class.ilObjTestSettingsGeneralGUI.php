<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/Test/classes/class.ilTestSettingsGUI.php';
require_once 'Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php';

/**
 * GUI class that manages the editing of general test settings/properties
 * shown on "general" subtab
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/Test
 * 
 * @ilCtrl_Calls ilObjTestSettingsGeneralGUI: ilPropertyFormGUI
 * @ilCtrl_Calls ilObjTestSettingsGeneralGUI: ilConfirmationGUI
 * @ilCtrl_Calls ilObjTestSettingsGeneralGUI: ilTestSettingsChangeConfirmationGUI
 */
class ilObjTestSettingsGeneralGUI extends ilTestSettingsGUI
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

	/** @var ilObjTestGUI $testGUI */
	protected $testGUI = null;

	/** @var ilTestQuestionSetConfigFactory $testQuestionSetConfigFactory Factory for question set config. */
	private $testQuestionSetConfigFactory = null;

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

		require_once 'Modules/Test/classes/class.ilTestQuestionSetConfigFactory.php';
		$this->testQuestionSetConfigFactory = new ilTestQuestionSetConfigFactory($this->tree, $this->db, $this->pluginAdmin, $testGUI->object);

		parent::__construct($testGUI->object);
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

	private function confirmedSaveFormCmd()
	{
		return $this->saveFormCmd(true);
	}

	private function saveFormCmd($isConfirmedSave = false)
	{
		$form = $this->buildForm();

		// form validation and initialisation

		$errors = !$form->checkInput(); // ALWAYS CALL BEFORE setValuesByPost()
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

		$infoMsg = array();

		// solve conflicts with question set type setting with confirmation screen if required
		// determine wether question set type relating data is to be removed (questions/pools)

		$questionSetTypeRelatingDataCleanupRequired = false;

		$oldQuestionSetType = $this->testOBJ->getQuestionSetType();
		if( $form->getItemByPostVar('question_set_type') instanceof ilFormPropertyGUI )
		{
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
						$infoMsg[] = $this->lng->txt("tst_set_offline_due_to_switched_question_set_type_setting");
					}
					else
					{
						$infoMsg[] = $this->lng->txt("tst_cannot_online_due_to_switched_quest_set_type_setting");
					}
				}
			}
		}
		else
		{
			$newQuestionSetType = $oldQuestionSetType;
		}

		// adjust settiue to desired question set type

		if( $newQuestionSetType != ilObjTest::QUESTION_SET_TYPE_FIXED )
		{
			$form->getItemByPostVar('chb_use_previous_answers')->setChecked(false);

			if( $this->isSkillServiceSettingToBeAdjusted($form) )
			{
				$form->getItemByPostVar('skill_service')->setChecked(false);

				if( $this->testOBJ->isSkillServiceEnabled() )
				{
					$infoMsg[] = $this->lng->txt("tst_disabled_skl_due_to_non_fixed_quest_set_type");
				}
				else
				{
					$infoMsg[] = $this->lng->txt("tst_cannot_enable_skl_due_to_non_fixed_quest_set_type");
				}
			}
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

		if( count($infoMsg) )
		{
			ilUtil::sendInfo(implode('<br />', $infoMsg), true);
		}

		ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
		$this->ctrl->redirect($this, self::CMD_SHOW_FORM);
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

	private function isSkillServiceSettingToBeAdjusted(ilPropertyFormGUI $form)
	{
		if( !($form->getItemByPostVar('skill_service') instanceof ilFormPropertyGUI) )
		{
			return false;
		}

		if( !ilObjTest::isSkillManagementGloballyActivated() )
		{
			return false;
		}

		if( !$form->getItemByPostVar('skill_service')->getChecked() )
		{
			return false;
		}

		return true;
	}

	private function isCharSelectorPropertyRequired()
	{
		global $ilSetting;

		return $ilSetting->get('char_selector_availability') > 0;
	}


	private function buildForm()
	{
		require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->addCommandButton(self::CMD_SAVE_FORM, $this->lng->txt("save"));
		$form->setTableWidth("100%");
		$form->setId("test_properties");

		$this->addGeneralProperties($form);
		$this->addAvailabilityProperties($form);
		$this->addTestIntroProperties($form);
		$this->addTestAccessProperties($form);
		$this->addTestRunProperties($form);
		$this->addQuestionBehaviourProperties($form);
		$this->addTestSequenceProperties($form);
		$this->addTestFinishProperties($form);

		// Edit ecs export settings
		include_once 'Modules/Test/classes/class.ilECSTestSettings.php';
		$ecs = new ilECSTestSettings($this->testOBJ);
		$ecs->addSettingsToForm($form, 'tst');

		// skill service activation for FIXED tests only
		if( $this->testOBJ->isFixedTest() && ilObjTest::isSkillManagementGloballyActivated() )
		{
			$otherHead = new ilFormSectionHeaderGUI();
			$otherHead->setTitle($this->lng->txt('other'));
			$form->addItem($otherHead);

			$skillService = new ilCheckboxInputGUI($this->lng->txt('tst_activate_skill_service'), 'skill_service');
			$skillService->setInfo($this->lng->txt('tst_activate_skill_service_desc'));
			$skillService->setChecked($this->testOBJ->isSkillServiceEnabled());
			if($this->testOBJ->participantDataExist()) $skillService->setDisabled(true);
			$form->addItem($skillService);
		}

		// remove items when using template
		$this->removeHiddenItems($form);

		return $form;
	}

	private function performSaveForm(ilPropertyFormGUI $form)
	{
		$this->saveGeneralProperties($form);
		$this->saveAvailabilityProperties($form);
		$this->saveTestIntroProperties($form);
		$this->saveTestAccessProperties($form);
		$this->saveTestRunProperties($form);
		$this->saveQuestionBehaviourProperties($form);
		$this->saveTestSequenceSettings($form);
		$this->saveTestFinishProperties($form);

		if( !$this->testOBJ->participantDataExist() )
		{
			// skill service
			if( ilObjTest::isSkillManagementGloballyActivated() && $form->getItemByPostVar('skill_service') instanceof ilFormPropertyGUI )
			{
				$this->testOBJ->setSkillServiceEnabled($form->getItemByPostVar('skill_service')->getChecked());
			}
		}

		// store settings to db
		$this->testOBJ->saveToDb(true);

		// Update ecs export settings
		include_once 'Modules/Test/classes/class.ilECSTestSettings.php';
		$ecs = new ilECSTestSettings($this->testOBJ);
		$ecs->handleSettingsUpdate();
	}

	/**
	 * @param ilPropertyFormGUI $form
	 */
	private function addGeneralProperties(ilPropertyFormGUI $form)
	{
		$header = new ilFormSectionHeaderGUI();
		$header->setTitle($this->lng->txt("tst_general_properties"));
		$form->addItem($header);

		// title & description (meta data)

		include_once 'Services/MetaData/classes/class.ilMD.php';
		$md_obj = new ilMD($this->testOBJ->getId(), 0, "tst");
		$md_section = $md_obj->getGeneral();

		$title = new ilTextInputGUI($this->lng->txt("title"), "title");
		$title->setRequired(true);
		$title->setValue($md_section->getTitle());
		$form->addItem($title);

		$ids = $md_section->getDescriptionIds();
		if ($ids) {
			$desc_obj = $md_section->getDescription(array_pop($ids));

			$desc = new ilTextAreaInputGUI($this->lng->txt("description"), "description");
			$desc->setCols(50);
			$desc->setRows(4);
			$desc->setValue($desc_obj->getDescription());
			$form->addItem($desc);
		}

		// pool usage
		$pool_usage = new ilRadioGroupInputGUI($this->lng->txt('test_question_pool_usage'), 'use_pool');

		$optional_qpl = new ilRadioOption($this->lng->txt('test_question_pool_usage_optional'), 1);
		$optional_qpl->setInfo($this->lng->txt('test_question_pool_usage_optional_info'));
		$pool_usage->addOption($optional_qpl);

		$tst_directly = new ilRadioOption($this->lng->txt('test_question_pool_usage_tst_directly'), 0);
		$tst_directly->setInfo($this->lng->txt('test_question_pool_usage_tst_directly_info'));
		$pool_usage->addOption($tst_directly);

		$pool_usage->setValue($this->testOBJ->getPoolUsage() ? 1 : 0);
		$form->addItem($pool_usage);

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
		if ($this->testOBJ->participantDataExist()) {
			$questSetType->setDisabled(true);
		}
		$form->addItem($questSetType);
	}

	/**
	 * @param ilPropertyFormGUI $form
	 */
	private function saveGeneralProperties(ilPropertyFormGUI $form)
	{
		include_once 'Services/MetaData/classes/class.ilMD.php';
		$md_obj =& new ilMD($this->testOBJ->getId(), 0, "tst");
		$md_section = $md_obj->getGeneral();

		// title
		$md_section->setTitle(ilUtil::stripSlashes($form->getItemByPostVar('title')->getValue()));
		$md_section->update();

		// Description
		$md_desc_ids = $md_section->getDescriptionIds();
		if ($md_desc_ids)
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
		if ($form->getItemByPostVar('use_pool') instanceof ilFormPropertyGUI)
		{
			$this->testOBJ->setPoolUsage((int)$form->getItemByPostVar('use_pool')->getValue());
		}

		if (!$this->testOBJ->participantDataExist())
		{
			// question set type
			if ($form->getItemByPostVar('question_set_type') instanceof ilFormPropertyGUI)
			{
				$this->testOBJ->setQuestionSetType($form->getItemByPostVar('question_set_type')->getValue());
			}
		}
	}

	/**
	 * @param ilPropertyFormGUI $form
	 */
	private function addAvailabilityProperties(ilPropertyFormGUI $form)
	{
		include_once "Services/Object/classes/class.ilObjectActivation.php";
		$this->lng->loadLanguageModule('rep');

		$section = new ilFormSectionHeaderGUI();
		$section->setTitle($this->lng->txt('rep_activation_availability'));
		$form->addItem($section);

		// additional info only with multiple references
		$act_obj_info = $act_ref_info = "";
		if (sizeof(ilObject::_getAllReferences($this->testOBJ->getId())) > 1) {
			$act_obj_info = ' ' . $this->lng->txt('rep_activation_online_object_info');
			$act_ref_info = $this->lng->txt('rep_activation_access_ref_info');
		}

		$online = new ilCheckboxInputGUI($this->lng->txt('rep_activation_online'), 'online');
		$online->setChecked($this->testOBJ->isOnline());
		$online->setInfo($this->lng->txt('tst_activation_online_info') . $act_obj_info);
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
	}

	/**
	 * @param ilPropertyFormGUI $form
	 */
	private function saveAvailabilityProperties(ilPropertyFormGUI $form)
	{
		// online status
		$this->testOBJ->setOnline($form->getItemByPostVar('online')->getChecked());

		// activation
		if ($form->getItemByPostVar('activation_type')->getChecked())
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
	}

	/**
	 * @param ilPropertyFormGUI $form
	 */
	private function addTestIntroProperties(ilPropertyFormGUI $form)
	{
		$section = new ilFormSectionHeaderGUI();
		$section->setTitle($this->lng->txt('tst_settings_header_intro'));
		$form->addItem($section);

		// introduction
		$introEnabled = new ilCheckboxInputGUI($this->lng->txt("tst_introduction"), 'intro_enabled');
		$introEnabled->setChecked($this->testOBJ->isIntroductionEnabled());
		$introEnabled->setInfo($this->lng->txt('tst_introduction_desc'));
		$form->addItem($introEnabled);
		$intro = new ilTextAreaInputGUI($this->lng->txt("tst_introduction_text"), "introduction");
		$intro->setRequired(true);
		$intro->setValue($this->testOBJ->prepareTextareaOutput($this->testOBJ->getIntroduction(), false, true));
		$intro->setRows(10);
		$intro->setCols(80);
		$intro->setUseRte(TRUE);
		$intro->addPlugin("latex");
		$intro->addButton("latex");
		$intro->setRTESupport($this->testOBJ->getId(), "tst", "assessment");
		$intro->setRteTagSet('full');
		$introEnabled->addSubItem($intro);

		// showinfo
		$showinfo = new ilCheckboxInputGUI($this->lng->txt("showinfo"), "showinfo");
		$showinfo->setValue(1);
		$showinfo->setChecked($this->testOBJ->getShowInfo());
		$showinfo->setInfo($this->lng->txt("showinfo_desc"));
		$form->addItem($showinfo);
	}

	/**
	 * @param ilPropertyFormGUI $form
	 */
	private function saveTestIntroProperties(ilPropertyFormGUI $form)
	{
		if ($form->getItemByPostVar('intro_enabled') instanceof ilFormPropertyGUI)
		{
			$this->testOBJ->setIntroductionEnabled($form->getItemByPostVar('intro_enabled')->getChecked());

			if ($form->getItemByPostVar('intro_enabled')->getChecked())
			{
				$this->testOBJ->setIntroduction($form->getItemByPostVar('introduction')->getValue(), false, ilObjAdvancedEditing::_getUsedHTMLTagsAsString("assessment"));
			}
			else
			{
				$this->testOBJ->setIntroduction('');
			}
		}

		if ($form->getItemByPostVar('showinfo') instanceof ilFormPropertyGUI)
		{
			$this->testOBJ->setShowInfo($form->getItemByPostVar('showinfo')->getChecked());
		}
	}

	/**
	 * @param ilPropertyFormGUI $form
	 */
	private function addTestAccessProperties(ilPropertyFormGUI $form)
	{
		$header = new ilFormSectionHeaderGUI();
		$header->setTitle($this->lng->txt("tst_settings_header_execution"));
		$form->addItem($header);

		// enable starting time
		$enablestartingtime = new ilCheckboxInputGUI($this->lng->txt("tst_starting_time"), "chb_starting_time");
		$enablestartingtime->setInfo($this->lng->txt("tst_starting_time_desc"));
		$enablestartingtime->setChecked($this->testOBJ->isStartingTimeEnabled());

		// starting time
		$startingtime = new ilDateTimeInputGUI('', 'starting_time');
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
		if ($this->testOBJ->participantDataExist())
		{
			$enablestartingtime->setDisabled(true);
			$startingtime->setDisabled(true);
		}

		// enable ending time
		$enableendingtime = new ilCheckboxInputGUI($this->lng->txt("tst_ending_time"), "chb_ending_time");
		$enableendingtime->setInfo($this->lng->txt("tst_ending_time_desc"));
		$enableendingtime->setChecked($this->testOBJ->isEndingTimeEnabled());

		// ending time
		$endingtime = new ilDateTimeInputGUI('', 'ending_time');
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
		$pwEnabled = new ilCheckboxInputGUI($this->lng->txt('tst_password'), 'password_enabled');
		$pwEnabled->setChecked($this->testOBJ->isPasswordEnabled());
		$pwEnabled->setInfo($this->lng->txt("tst_password_details"));
		$password = new ilTextInputGUI($this->lng->txt("tst_password_enter"), "password");
		$password->setRequired(true);
		$password->setSize(20);
		$password->setValue($this->testOBJ->getPassword());
		$pwEnabled->addSubItem($password);
		$form->addItem($pwEnabled);

		// fixed participants
		$fixedparticipants = new ilCheckboxInputGUI($this->lng->txt('participants_invitation'), "fixedparticipants");
		$fixedparticipants->setValue(1);
		$fixedparticipants->setChecked($this->testOBJ->getFixedParticipants());
		$fixedparticipants->setInfo($this->lng->txt("participants_invitation_description"));
		$invited_users = $this->testOBJ->getInvitedUsers();
		if ($this->testOBJ->participantDataExist() && (count($invited_users) == 0))
		{
			$fixedparticipants->setDisabled(true);
		}
		$form->addItem($fixedparticipants);

		// simultaneous users
		$simulLimited = new ilCheckboxInputGUI($this->lng->txt("tst_allowed_users"), 'limitUsers');
		$simulLimited->setInfo($this->lng->txt("tst_allowed_users_desc"));
		$simulLimited->setChecked($this->testOBJ->isLimitUsersEnabled());

		// allowed simultaneous users
		$simul = new ilNumberInputGUI($this->lng->txt("tst_allowed_users_max"), "allowedUsers");
		$simul->setRequired(true);
		$simul->allowDecimals(false);
		$simul->setMinValue(1);
		$simul->setMinvalueShouldBeGreater(false);
		$simul->setSize(4);
		$simul->setValue(($this->testOBJ->getAllowedUsers()) ? $this->testOBJ->getAllowedUsers() : '');
		$simulLimited->addSubItem($simul);

		// idle time
		$idle = new ilNumberInputGUI($this->lng->txt("tst_allowed_users_time_gap"), "allowedUsersTimeGap");
		$idle->setInfo($this->lng->txt("tst_allowed_users_time_gap_desc"));
		$idle->setSize(4);
		$idle->setSuffix($this->lng->txt("seconds"));
		$idle->setValue(($this->testOBJ->getAllowedUsersTimeGap()) ? $this->testOBJ->getAllowedUsersTimeGap() : 300);
		$simulLimited->addSubItem($idle);

		$form->addItem($simulLimited);
		return $header;
	}

	/**
	 * @param ilPropertyFormGUI $form
	 */
	private function saveTestAccessProperties(ilPropertyFormGUI $form)
	{
		// starting time
		if( $this->formPropertyExists($form, 'chb_starting_time') && !$this->testOBJ->participantDataExist() )
		{
			if( $form->getItemByPostVar('chb_starting_time')->getChecked() )
			{
				$this->testOBJ->setStartingTime(ilFormat::dateDB2timestamp(
					$form->getItemByPostVar('starting_time')->getDate()->get(IL_CAL_DATETIME)
				));

				$this->testOBJ->setStartingTimeEnabled(true);
			}
			else
			{
				$this->testOBJ->setStartingTimeEnabled(false);
			}
		}

		// ending time
		if( $this->formPropertyExists($form, 'chb_ending_time') )
		{
			if( $form->getItemByPostVar('chb_ending_time')->getChecked() )
			{
				$this->testOBJ->setEndingTime(ilFormat::dateDB2timestamp(
					$form->getItemByPostVar('ending_time')->getDate()->get(IL_CAL_DATETIME)
				));

				$this->testOBJ->setEndingTimeEnabled(true);
			}
			else
			{
				$this->testOBJ->setEndingTimeEnabled(false);
			}
		}

		if( $this->formPropertyExists($form, 'password_enabled') )
		{
			$this->testOBJ->setPasswordEnabled($form->getItemByPostVar('password_enabled')->getChecked());

			if( $form->getItemByPostVar('password_enabled')->getChecked() )
			{
				$this->testOBJ->setPassword($form->getItemByPostVar('password')->getValue());
			}
			else
			{
				$this->testOBJ->setPassword(''); // otherwise test will still respect value
			}
		}

		if( $this->formPropertyExists($form, 'fixedparticipants') && !$this->testOBJ->participantDataExist() )
		{
			$this->testOBJ->setFixedParticipants($form->getItemByPostVar('fixedparticipants')->getChecked());
		}

		if( $this->formPropertyExists($form, 'limitUsers') )
		{
			$this->testOBJ->setLimitUsersEnabled($form->getItemByPostVar('limitUsers')->getChecked());

			if( $form->getItemByPostVar('limitUsers')->getChecked() )
			{
				$this->testOBJ->setAllowedUsers($form->getItemByPostVar('allowedUsers')->getValue());
				$this->testOBJ->setAllowedUsersTimeGap($form->getItemByPostVar('allowedUsersTimeGap')->getValue());
			}
			else
			{
				$this->testOBJ->setAllowedUsers(''); // otherwise test will still respect value
			}
		}
	}

	/**
	 * @param ilPropertyFormGUI $form
	 */
	private function addTestRunProperties(ilPropertyFormGUI $form)
	{
		// section header test run
		$header = new ilFormSectionHeaderGUI();
		$header->setTitle($this->lng->txt("tst_settings_header_test_run"));
		$form->addItem($header);

		// max. number of passes
		$limitPasses = new ilCheckboxInputGUI($this->lng->txt("tst_limit_nr_of_tries"), 'limitPasses');
		$limitPasses->setInfo($this->lng->txt("tst_nr_of_tries_desc"));
		$limitPasses->setChecked($this->testOBJ->getNrOfTries() > 0);
		$nr_of_tries = new ilNumberInputGUI($this->lng->txt("tst_nr_of_tries"), "nr_of_tries");
		$nr_of_tries->setSize(3);
		$nr_of_tries->allowDecimals(false);
		$nr_of_tries->setMinValue(1);
		$nr_of_tries->setMinvalueShouldBeGreater(false);
		$nr_of_tries->setValue($this->testOBJ->getNrOfTries() ? $this->testOBJ->getNrOfTries() : 1);
		$nr_of_tries->setRequired(true);
		if( $this->testOBJ->participantDataExist() )
		{
			$limitPasses->setDisabled(true);
			$nr_of_tries->setDisabled(true);
		}
		$limitPasses->addSubItem($nr_of_tries);
		$form->addItem($limitPasses);

		// enable max. processing time
		$processing = new ilCheckboxInputGUI($this->lng->txt("tst_processing_time"), "chb_processing_time");
		$processing->setInfo($this->lng->txt("tst_processing_time_desc"));
		$processing->setValue(1);

		if ($this->settingsTemplate && $this->getTemplateSettingValue('chb_processing_time')) {
			$processing->setChecked(true);
		} else {
			$processing->setChecked($this->testOBJ->getEnableProcessingTime());
		}

		// max. processing time
		$processingtime = new ilNumberInputGUI($this->lng->txt("tst_processing_time_duration"), 'processing_time');
		$processingtime->allowDecimals(false);
		$processingtime->setMinValue(1);
		$processingtime->setMinvalueShouldBeGreater(false);
		$processingtime->setValue($this->testOBJ->getProcessingTimeAsMinutes());
		$processingtime->setSize(5);
		$processingtime->setSuffix($this->lng->txt('minutes'));
		$processingtime->setInfo($this->lng->txt("tst_processing_time_duration_desc"));
		$processing->addSubItem($processingtime);

		// reset max. processing time
		$resetprocessing = new ilCheckboxInputGUI('', "chb_reset_processing_time");
		$resetprocessing->setValue(1);
		$resetprocessing->setOptionTitle($this->lng->txt("tst_reset_processing_time"));
		$resetprocessing->setChecked($this->testOBJ->getResetProcessingTime());
		$resetprocessing->setInfo($this->lng->txt("tst_reset_processing_time_desc"));
		$processing->addSubItem($resetprocessing);
		$form->addItem($processing);

		if( $this->testOBJ->participantDataExist() )
		{
			$processing->setDisabled(true);
			$processingtime->setDisabled(true);
			$resetprocessing->setDisabled(true);
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
	}

	/**
	 * @param ilPropertyFormGUI $form
	 */
	private function saveTestRunProperties(ilPropertyFormGUI $form)
	{
		if (!$this->testOBJ->participantDataExist())
		{
			// nr of tries (max passes)
			if ($form->getItemByPostVar('limitPasses') instanceof ilFormPropertyGUI)
			{
				if ($form->getItemByPostVar('limitPasses')->getChecked())
				{
					$this->testOBJ->setNrOfTries($form->getItemByPostVar('nr_of_tries')->getValue());
				}
				else
				{
					$this->testOBJ->setNrOfTries(0);
				}
			}

			$this->testOBJ->setEnableProcessingTime($form->getItemByPostVar('chb_processing_time')->getChecked());
			if ($this->testOBJ->getEnableProcessingTime())
			{
				$this->testOBJ->setProcessingTimeByMinutes($form->getItemByPostVar('processing_time')->getValue());
				$this->testOBJ->setResetProcessingTime($form->getItemByPostVar('chb_reset_processing_time')->getChecked());
			}
			else
			{
				$this->testOBJ->setProcessingTime('');
				$this->testOBJ->setResetProcessingTime(false);
			}
		}

		if ($form->getItemByPostVar('kiosk') instanceof ilFormPropertyGUI)
		{
			$this->testOBJ->setKioskMode($form->getInput('kiosk'));
			$kioskOptions = $form->getItemByPostVar('kiosk_options')->getValue();
			if (is_array($kioskOptions))
			{
				$this->testOBJ->setShowKioskModeTitle(in_array('kiosk_title', $kioskOptions));
				$this->testOBJ->setShowKioskModeParticipant(in_array('kiosk_participant', $kioskOptions));
			}
			else
			{
				$this->testOBJ->setShowKioskModeTitle(false);
				$this->testOBJ->setShowKioskModeParticipant(false);
			}
		}

		if ($form->getItemByPostVar('examid_in_test_pass') instanceof ilFormPropertyGUI)
		{
			$value = $form->getItemByPostVar('examid_in_test_pass')->getChecked();
			$this->testOBJ->setShowExamIdInTestPassEnabled($value);
		}
	}

	/**
	 * @param ilPropertyFormGUI $form
	 */
	private function addQuestionBehaviourProperties(ilPropertyFormGUI $form)
	{
		$fields = array(
			'title_output', 'autosave', 'chb_shuffle_questions', 'chb_shuffle_questions',
			'offer_hints', 'instant_feedback', 'obligations_enabled',
		);

		if( $this->isSectionHeaderRequired($fields) || $this->isCharSelectorPropertyRequired() )
		{
			// sequence properties
			$seqheader = new ilFormSectionHeaderGUI();
			$seqheader->setTitle($this->lng->txt("tst_presentation_properties"));
			$form->addItem($seqheader);
		}

		// question title output
		$title_output = new ilRadioGroupInputGUI($this->lng->txt("tst_title_output"), "title_output");
		$title_output->addOption(new ilRadioOption($this->lng->txt("tst_title_output_full"), 0, ''));
		$title_output->addOption(new ilRadioOption($this->lng->txt("tst_title_output_hide_points"), 1, ''));
		$title_output->addOption(new ilRadioOption($this->lng->txt("tst_title_output_no_title"), 2, ''));
		$title_output->setValue($this->testOBJ->getTitleOutput());
		$form->addItem($title_output);

		// Autosave
		$autosave_output = new ilCheckboxInputGUI($this->lng->txt('autosave'), 'autosave');
		$autosave_output->setValue(1);
		$autosave_output->setChecked($this->testOBJ->getAutosave());
		$autosave_output->setInfo($this->lng->txt('autosave_info'));
		$autosave_interval = new ilTextInputGUI($this->lng->txt('autosave_ival'), 'autosave_ival');
		$autosave_interval->setSize(10);
		$autosave_interval->setValue($this->testOBJ->getAutosaveIval()/1000);
		$autosave_interval->setSuffix($this->lng->txt('seconds'));
		$autosave_output->addSubItem($autosave_interval);
		$form->addItem($autosave_output);

		// shuffle questions
		$shuffle = new ilCheckboxInputGUI($this->lng->txt("tst_shuffle_questions"), "chb_shuffle_questions");
		$shuffle->setValue(1);
		$shuffle->setChecked($this->testOBJ->getShuffleQuestions());
		$shuffle->setInfo($this->lng->txt("tst_shuffle_questions_description"));
		$form->addItem($shuffle);

		// offer hints
		$checkBoxOfferHints = new ilCheckboxInputGUI($this->lng->txt('tst_setting_offer_hints_label'), 'offer_hints');
		$checkBoxOfferHints->setChecked($this->testOBJ->isOfferingQuestionHintsEnabled());
		$checkBoxOfferHints->setInfo($this->lng->txt('tst_setting_offer_hints_info'));
		if( $this->testOBJ->participantDataExist() )
		{
			$checkBoxOfferHints->setDisabled(true);
		}
		$form->addItem($checkBoxOfferHints);

		// instant feedback
		$instant_feedback = new ilCheckboxGroupInputGUI($this->lng->txt('tst_instant_feedback'), 'instant_feedback');
		$instant_feedback->addOption(new ilCheckboxOption(
			$this->lng->txt('tst_instant_feedback_results'), 'instant_feedback_points',
			$this->lng->txt('tst_instant_feedback_results_desc')
		));
		$instant_feedback->addOption(new ilCheckboxOption(
			$this->lng->txt('tst_instant_feedback_answer_generic'), 'instant_feedback_generic',
			$this->lng->txt('tst_instant_feedback_answer_generic_desc')
		));
		$instant_feedback->addOption(new ilCheckboxOption(
			$this->lng->txt('tst_instant_feedback_answer_specific'), 'instant_feedback_specific',
			$this->lng->txt('tst_instant_feedback_answer_specific_desc')
		));
		$instant_feedback->addOption(new ilCheckboxOption(
			$this->lng->txt('tst_instant_feedback_solution'), 'instant_feedback_solution',
			$this->lng->txt('tst_instant_feedback_solution_desc')
		));
		$instant_feedback->addOption(new ilCheckboxOption(
			$this->lng->txt('tst_instant_feedback_fix_usr_answer'), 'instant_feedback_answer_fixation',
			$this->lng->txt('tst_instant_feedback_fix_usr_answer_desc')
		));
		$values = array();
		if ($this->testOBJ->getSpecificAnswerFeedback()) array_push($values, 'instant_feedback_specific');
		if ($this->testOBJ->getGenericAnswerFeedback()) array_push($values, 'instant_feedback_generic');
		if ($this->testOBJ->getAnswerFeedbackPoints()) array_push($values, 'instant_feedback_points');
		if ($this->testOBJ->getInstantFeedbackSolution()) array_push($values, 'instant_feedback_solution');
		if( $this->testOBJ->isInstantFeedbackAnswerFixationEnabled() ) array_push($values, 'instant_feedback_answer_fixation');
		$instant_feedback->setValue($values);
		$form->addItem($instant_feedback);

		// enable obligations
		$checkBoxEnableObligations = new ilCheckboxInputGUI($this->lng->txt('tst_setting_enable_obligations_label'), 'obligations_enabled');
		$checkBoxEnableObligations->setChecked($this->testOBJ->areObligationsEnabled());
		$checkBoxEnableObligations->setInfo($this->lng->txt('tst_setting_enable_obligations_info'));
		if( $this->testOBJ->participantDataExist() )
		{
			$checkBoxEnableObligations->setDisabled(true);
		}
		$form->addItem($checkBoxEnableObligations);

		// selector for unicode characters
		if( $this->isCharSelectorPropertyRequired() )
		{
			require_once 'Services/UIComponent/CharSelector/classes/class.ilCharSelectorGUI.php';
			$char_selector = new ilCharSelectorGUI(ilCharSelectorConfig::CONTEXT_TEST);
			$char_selector->getConfig()->setAvailability($this->testOBJ->getCharSelectorAvailability());
			$char_selector->getConfig()->setDefinition($this->testOBJ->getCharSelectorDefinition());
			$char_selector->addFormProperties($form);
			$char_selector->setFormValues($form);
		}
	}

	/**
	 * @param ilPropertyFormGUI $form
	 */
	private function saveQuestionBehaviourProperties(ilPropertyFormGUI $form)
	{
		if ($form->getItemByPostVar('title_output') instanceof ilFormPropertyGUI)
		{
			$this->testOBJ->setTitleOutput($form->getItemByPostVar('title_output')->getValue());
		}

		$this->testOBJ->setAutosave($form->getItemByPostVar('autosave')->getChecked());
		$this->testOBJ->setAutosaveIval($form->getItemByPostVar('autosave_ival')->getValue() * 1000);

		$this->testOBJ->setShuffleQuestions($form->getItemByPostVar('chb_shuffle_questions')->getChecked());

		if (!$this->testOBJ->participantDataExist() && $this->formPropertyExists($form, 'offer_hints'))
		{
			$this->testOBJ->setOfferingQuestionHintsEnabled($form->getItemByPostVar('offer_hints')->getChecked());
		}

		if ($this->formPropertyExists($form, 'instant_feedback'))
		{
			$this->testOBJ->setScoringFeedbackOptionsByArray($form->getItemByPostVar('instant_feedback')->getValue());
		}

		if (!$this->testOBJ->participantDataExist() && $this->formPropertyExists($form, 'obligations_enabled'))
		{
			$this->testOBJ->setObligationsEnabled($form->getItemByPostVar('obligations_enabled')->getChecked());
		}

		if( $this->isCharSelectorPropertyRequired() )
		{
			require_once 'Services/UIComponent/CharSelector/classes/class.ilCharSelectorGUI.php';
			$char_selector = new ilCharSelectorGUI(ilCharSelectorConfig::CONTEXT_TEST);
			$char_selector->addFormProperties($form);
			$char_selector->getFormValues($form);
			$this->testOBJ->setCharSelectorAvailability($char_selector->getConfig()->getAvailability());
			$this->testOBJ->setCharSelectorDefinition($char_selector->getConfig()->getDefinition());
		}
	}

	/**
	 * @param ilPropertyFormGUI $form
	 */
	private function addTestSequenceProperties(ilPropertyFormGUI $form)
	{
		// sequence properties
		$seqheader = new ilFormSectionHeaderGUI();
		$seqheader->setTitle($this->lng->txt("tst_sequence_properties"));
		$form->addItem($seqheader);

		// use previous answers
		$prevanswers = new ilCheckboxInputGUI($this->lng->txt("tst_use_previous_answers"), "chb_use_previous_answers");
		$prevanswers->setValue(1);
		$prevanswers->setChecked($this->testOBJ->getUsePreviousAnswers());
		$prevanswers->setInfo($this->lng->txt("tst_use_previous_answers_description"));
		$form->addItem($prevanswers);

		// show suspend test
		$cancel = new ilCheckboxInputGUI($this->lng->txt("tst_show_cancel"), "chb_show_cancel");
		$cancel->setValue(1);
		$cancel->setChecked($this->testOBJ->getShowCancel());
		$cancel->setInfo($this->lng->txt("tst_show_cancel_description"));
		$form->addItem($cancel);

		// postpone questions
		$postpone = new ilCheckboxInputGUI($this->lng->txt("tst_postpone"), "chb_postpone");
		$postpone->setValue(1);
		$postpone->setChecked($this->testOBJ->getSequenceSettings());
		$postpone->setInfo($this->lng->txt("tst_postpone_description"));
		$form->addItem($postpone);

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
	}

	/**
	 * @param ilPropertyFormGUI $form
	 */
	private function saveTestSequenceSettings(ilPropertyFormGUI $form)
	{
		if( $this->formPropertyExists($form, 'chb_use_previous_answers') )
		{
			$this->testOBJ->setUsePreviousAnswers($form->getInput('chb_use_previous_answers'));
		}

		if( $this->formPropertyExists($form, 'chb_show_cancel') )
		{
			$this->testOBJ->setShowCancel($form->getItemByPostVar('chb_show_cancel')->getChecked());
		}

		if( $this->formPropertyExists($form, 'chb_postpone') )
		{
			$this->testOBJ->setPostponingEnabled($form->getItemByPostVar('chb_postpone')->getChecked());
		}

		$this->testOBJ->setListOfQuestions($form->getItemByPostVar('list_of_questions')->getChecked());
		$listOfQuestionsOptions = $form->getItemByPostVar('list_of_questions_options')->getValue();
		if(is_array($listOfQuestionsOptions))
		{
			$this->testOBJ->setListOfQuestionsStart(in_array('chb_list_of_questions_start', $listOfQuestionsOptions));
			$this->testOBJ->setListOfQuestionsEnd(in_array('chb_list_of_questions_end', $listOfQuestionsOptions));
			$this->testOBJ->setListOfQuestionsDescription(in_array('chb_list_of_questions_with_description', $listOfQuestionsOptions));
		}
		else
		{
			$this->testOBJ->setListOfQuestionsStart(0);
			$this->testOBJ->setListOfQuestionsEnd(0);
			$this->testOBJ->setListOfQuestionsDescription(0);
		}

		if( $this->formPropertyExists($form, 'chb_show_marker') )
		{
			$this->testOBJ->setShowMarker($form->getItemByPostVar('chb_show_marker')->getChecked());
		}
	}

	/**
	 * @param ilPropertyFormGUI $form
	 */
	private function addTestFinishProperties(ilPropertyFormGUI $form)
	{
		$testFinishHeader = new ilFormSectionHeaderGUI();
		$testFinishHeader->setTitle($this->lng->txt("tst_final_information"));
		$form->addItem($testFinishHeader);

		// examview
		$enable_examview = new ilCheckboxInputGUI($this->lng->txt("enable_examview"), 'enable_examview');
		$enable_examview->setValue(1);
		$enable_examview->setChecked($this->testOBJ->getEnableExamview());
		$enable_examview->setInfo($this->lng->txt("enable_examview_desc"));
		$show_examview_html = new ilCheckboxInputGUI('', 'show_examview_html');
		$show_examview_html->setValue(1);
		$show_examview_html->setChecked($this->testOBJ->getShowExamviewHtml());
		$show_examview_html->setOptionTitle($this->lng->txt("show_examview_html"));
		$enable_examview->addSubItem($show_examview_html);
		$show_examview_pdf = new ilCheckboxInputGUI('', 'show_examview_pdf');
		$show_examview_pdf->setValue(1);
		$show_examview_pdf->setChecked($this->testOBJ->getShowExamviewPdf());
		$show_examview_pdf->setOptionTitle($this->lng->txt("show_examview_pdf"));
		$enable_examview->addSubItem($show_examview_pdf);
		$form->addItem($enable_examview);

		// show final statement
		$showfinal = new ilCheckboxInputGUI($this->lng->txt("final_statement"), "showfinalstatement");
		$showfinal->setChecked($this->testOBJ->getShowFinalStatement());
		$showfinal->setInfo($this->lng->txt("final_statement_show_desc"));
		$form->addItem($showfinal);
		// final statement
		$finalstatement = new ilTextAreaInputGUI($this->lng->txt("final_statement"), "finalstatement");
		$finalstatement->setRequired(true);
		$finalstatement->setValue($this->testOBJ->prepareTextareaOutput($this->testOBJ->getFinalStatement(), false, true));
		$finalstatement->setRows(10);
		$finalstatement->setCols(80);
		$finalstatement->setUseRte(TRUE);
		$finalstatement->addPlugin("latex");
		$finalstatement->addButton("latex");
		$finalstatement->setRTESupport($this->testOBJ->getId(), "tst", "assessment");
		$finalstatement->setRteTagSet('full');
		$showfinal->addSubItem($finalstatement);

		$redirection_mode = $this->testOBJ->getRedirectionMode();
		$rm_enabled = new ilCheckboxInputGUI($this->lng->txt('redirect_after_finishing_tst'), 'redirection_enabled');
		$rm_enabled->setInfo($this->lng->txt('redirect_after_finishing_tst_desc'));
		$rm_enabled->setChecked($redirection_mode == '0' ? false : true);
		$radio_rm = new ilRadioGroupInputGUI($this->lng->txt('redirect_after_finishing_rule'), 'redirection_mode');
		$always = new ilRadioOption($this->lng->txt('redirect_always'), REDIRECT_ALWAYS);
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

		// mail notification
		$mailnotification = new ilCheckboxInputGUI($this->lng->txt("tst_finish_notification"), "mailnotification");
		$mailnotification->setInfo($this->lng->txt("tst_finish_notification_desc"));
		$mailnotification->setChecked($this->testOBJ->getMailNotification() > 0);
		$form->addItem($mailnotification);

		$mailnotificationContent = new ilRadioGroupInputGUI($this->lng->txt("tst_finish_notification_content"), "mailnotification_content");
		$mailnotificationContent->addOption(new ilRadioOption($this->lng->txt("tst_finish_notification_simple"), 1, ''));
		$mailnotificationContent->addOption(new ilRadioOption($this->lng->txt("tst_finish_notification_advanced"), 2, ''));
		$mailnotificationContent->setValue($this->testOBJ->getMailNotification() ? $this->testOBJ->getMailNotification() : 1);
		$mailnotificationContent->setRequired(true);
		$mailnotification->addSubItem($mailnotificationContent);

		$mailnottype = new ilCheckboxInputGUI('', "mailnottype");
		$mailnottype->setValue(1);
		$mailnottype->setOptionTitle($this->lng->txt("mailnottype"));
		$mailnottype->setChecked($this->testOBJ->getMailNotificationType());
		$mailnotification->addSubItem($mailnottype);
	}

	/**
	 * @param ilPropertyFormGUI $form
	 */
	private function saveTestFinishProperties(ilPropertyFormGUI $form)
	{
		if( $this->formPropertyExists($form, 'enable_examview') )
		{
			$this->testOBJ->setEnableExamview($form->getItemByPostVar('enable_examview')->getChecked());
			$this->testOBJ->setShowExamviewHtml($form->getItemByPostVar('show_examview_html')->getChecked());
			$this->testOBJ->setShowExamviewPdf($form->getItemByPostVar('show_examview_pdf')->getChecked());
		}

		$this->testOBJ->setShowFinalStatement($form->getItemByPostVar('showfinalstatement')->getChecked());
		$this->testOBJ->setFinalStatement($form->getItemByPostVar('finalstatement')->getValue(), false, ilObjAdvancedEditing::_getUsedHTMLTagsAsString("assessment"));

		if( $form->getItemByPostVar('redirection_enabled')->getChecked() )
		{
			$this->testOBJ->setRedirectionMode($form->getItemByPostVar('redirection_mode')->getValue());
		}
		else
		{
			$this->testOBJ->setRedirectionMode(REDIRECT_NONE);
		}
		if( strlen($form->getItemByPostVar('redirection_url')->getValue()) )
		{
			$this->testOBJ->setRedirectionUrl($form->getItemByPostVar('redirection_url')->getValue());
		}
		else
		{
			$this->testOBJ->setRedirectionUrl(null);
		}

		if( $this->formPropertyExists($form, 'sign_submission') )
		{
			$this->testOBJ->setSignSubmission($form->getItemByPostVar('sign_submission')->getChecked());
		}

		if( $this->formPropertyExists($form, 'mailnotification') && $form->getItemByPostVar('mailnotification')->getChecked() )
		{
			$this->testOBJ->setMailNotification($form->getItemByPostVar('mailnotification_content')->getValue());
			$this->testOBJ->setMailNotificationType($form->getItemByPostVar('mailnottype')->getChecked());
		}
		else
		{
			$this->testOBJ->setMailNotification(0);
			$this->testOBJ->setMailNotificationType(false);
		}
	}
}
