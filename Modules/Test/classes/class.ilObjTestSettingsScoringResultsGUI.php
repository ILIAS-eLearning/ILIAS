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
 * @ilCtrl_Calls ilObjTestSettingsScoringResultsGUI: ilPropertyFormGUI, ilConfirmationGUI
 */
class ilObjTestSettingsScoringResultsGUI
{
	/**
	 * command constants
	 */
	const CMD_SHOW_FORM					= 'showForm';
	const CMD_SAVE_FORM					= 'saveForm';
	const CMD_CONFIRMED_SAVE_FORM		= 'confirmedSaveForm';

	/**
	 * form field value constants
	 */
	const FORM_FIELD_VALUE_RESULTS_GRADING_SHOW_STATUS = 'status';
	const FORM_FIELD_VALUE_RESULTS_GRADING_SHOW_MARK = 'mark';
	const FORM_FIELD_VALUE_RESULTS_GRADING_SHOW_NONE = 'none';
	const FORM_FIELD_VALUE_RESULTS_GRADING_SHOW_BOTH = 'both';

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
		
		if (!$this->access->checkAccess('write', '', $this->testGUI->ref_id))
		{
			ilUtil::sendInfo($this->lng->txt('cannot_edit_test'), true);
			$this->ctrl->redirect($this->testGUI, 'infoScreen');
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
		//$this->tpl->addJavascript("./Services/JavaScript/js/Basic.js");
		
		if( $form === null )
		{
			$form = $this->buildForm();
		}

		$this->tpl->setContent( $this->ctrl->getHTML($form) );
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

		// return to form when any form validation errors exist

		if($errors)
		{
			ilUtil::sendFailure($this->lng->txt('form_input_not_valid'));
			return $this->showFormCmd($form);
		}

		// check for required confirmation and redirect if neccessary

		if( !$isConfirmedSave && $this->isScoreRecalculationRequired($form) )
		{
			return $this->showConfirmation($form);
		}

		// perform save

		$this->performSaveForm($form);

		if( $this->isScoreRecalculationRequired($form) )
		{
			$this->testOBJ->recalculateScores();
		}

		// redirect to form output

		ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
		$this->ctrl->redirect($this, self::CMD_SHOW_FORM);
	}

	private function performSaveForm(ilPropertyFormGUI $form)
	{
		if( $this->areScoringSettingsWritable() )
		{
			if( !$this->isHiddenFormItem('count_system') )
			{
				$this->testOBJ->setCountSystem($form->getItemByPostVar('count_system')->getValue());
			}
			if( !$this->isHiddenFormItem('mc_scoring') )
			{
				$this->testOBJ->setMCScoring($form->getItemByPostVar('mc_scoring')->getValue());
			}
			if( !$this->isHiddenFormItem('score_cutting') )
			{
				$this->testOBJ->setScoreCutting($form->getItemByPostVar('score_cutting')->getValue());
			}
			if( !$this->isHiddenFormItem('pass_scoring') )
			{
				$this->testOBJ->setPassScoring($form->getItemByPostVar('pass_scoring')->getValue());
			}
		}

		if( !$this->testOBJ->participantDataExist() )
		{
			if( !$this->isHiddenFormItem('obligations_enabled') )
			{
				$this->testOBJ->setObligationsEnabled($form->getItemByPostVar('obligations_enabled')->getChecked());
			}
			if( !$this->isHiddenFormItem('offer_hints') )
			{
				$this->testOBJ->setOfferingQuestionHintsEnabled($form->getItemByPostVar('offer_hints')->getChecked());
			}
		}

		if( !$this->isHiddenFormItem('instant_feedback') )
		{
			$this->testOBJ->setScoringFeedbackOptionsByArray($form->getItemByPostVar('instant_feedback')->getValue());
		}

		if( !$this->isHiddenFormItem('results_access') )
		{
			$this->testOBJ->setScoreReporting($form->getItemByPostVar('results_access')->getValue());
			
			if( $this->testOBJ->getScoreReporting() == REPORT_AFTER_DATE )
			{
				$this->testOBJ->setReportingDate(
					$form->getItemByPostVar('reporting_date')->getDate()->get(IL_CAL_FKT_DATE, 'YmdHis')
				);
			}
			else
			{
				$this->testOBJ->setReportingDate('');
			}
		}

		if( !$this->isHiddenFormItem('show_result_grading') )
		{
			switch( $form->getItemByPostVar('show_result_grading')->getValue() )
			{
				case self::FORM_FIELD_VALUE_RESULTS_GRADING_SHOW_BOTH:
					$this->testOBJ->setShowGradingStatusEnabled(true);
					$this->testOBJ->setShowGradingMarkEnabled(true);
					break;
				case self::FORM_FIELD_VALUE_RESULTS_GRADING_SHOW_NONE:
					$this->testOBJ->setShowGradingStatusEnabled(false);
					$this->testOBJ->setShowGradingMarkEnabled(false);
					break;
				case self::FORM_FIELD_VALUE_RESULTS_GRADING_SHOW_STATUS:
					$this->testOBJ->setShowGradingStatusEnabled(true);
					$this->testOBJ->setShowGradingMarkEnabled(false);
					break;
				case self::FORM_FIELD_VALUE_RESULTS_GRADING_SHOW_MARK:
					$this->testOBJ->setShowGradingStatusEnabled(false);
					$this->testOBJ->setShowGradingMarkEnabled(true);
					break;
			}
		}

		if( !$this->isHiddenFormItem('print_bs_with_res') )
		{
			$this->testOBJ->setPrintBestSolutionWithResult( (int)$form->getItemByPostVar('print_bs_with_res')->getChecked() );
		}

		if( !$this->isHiddenFormItem('results_presentation') )
		{
			$resultsPresentationSettings = (array)$form->getItemByPostVar('results_presentation')->getValue();
			$this->testOBJ->setShowPassDetails( (int)in_array('pass_details', $resultsPresentationSettings) );
			$this->testOBJ->setShowSolutionDetails( (int)in_array('solution_details', $resultsPresentationSettings) );
			$this->testOBJ->setShowSolutionPrintview( (int)in_array('solution_printview', $resultsPresentationSettings) );
			$this->testOBJ->setShowSolutionFeedback( (int)in_array('solution_feedback', $resultsPresentationSettings) );
			$this->testOBJ->setShowSolutionAnswersOnly( (int)in_array('solution_answers_only', $resultsPresentationSettings) );
			$this->testOBJ->setShowSolutionSignature( (int)in_array('solution_signature', $resultsPresentationSettings) );
			$this->testOBJ->setShowSolutionSuggested( (int)in_array('solution_suggested', $resultsPresentationSettings) );
			$this->testOBJ->setShowSolutionListComparison( (int)in_array('solution_compare', $resultsPresentationSettings) );
		}

		if( !$this->isHiddenFormItem('export_settings') )
		{
			$exportSettings = (array)$form->getItemByPostVar('export_settings');
			$this->testOBJ->setExportSettingsSingleChoiceShort( (int)in_array('exp_sc_short', $exportSettings) );
		}

		if( !$this->isHiddenFormItem('pass_deletion_allowed') )
		{
			$this->testOBJ->setPassDeletionAllowed( (bool)$form->getItemByPostVar('pass_deletion_allowed')->getValue() );
		}

		// result filter taxonomies
		if( $this->testQuestionSetConfigFactory->getQuestionSetConfig()->isResultTaxonomyFilterSupported() )
		{
			if( !$this->isHiddenFormItem'results_tax_filters' && count($this->getAvailableTaxonomyIds()) )
			{
				$this->testOBJ->setResultFilterTaxIds( array_intersect(
					$this->getAvailableTaxonomyIds(), $form->getItemByPostVar('results_tax_filters')->getValue()
				));
			}
		}

		// store settings to db
		$this->testOBJ->saveToDb(true);
	}
	
	private function showConfirmation(ilPropertyFormGUI $form)
	{
		require_once 'Services/Utilities/classes/class.ilConfirmationGUI.php';
		$confirmation = new ilConfirmationGUI();
		
		$confirmation->setHeaderText($this->lng->txt('tst_trigger_result_refreshing'));
		
		$confirmation->setFormAction( $this->ctrl->getFormAction($this) );
		$confirmation->setCancel($this->lng->txt('cancel'), self::CMD_SHOW_FORM);
		$confirmation->setConfirm($this->lng->txt('confirm'), self::CMD_CONFIRMED_SAVE_FORM);

		foreach ($form->getInputItemsRecursive() as $key => $item)
		{
			//vd("$key // {$item->getType()} // ".json_encode($_POST[$item->getPostVar()]));

			switch( $item->getType() )
			{
				case 'section_header':
					
					continue;
					
				case 'datetime':
					
					list($date, $time) = explode(' ', $item->getDate()->get(IL_CAL_DATETIME));

					if( $item->getMode() == ilDateTimeInputGUI::MODE_SELECT )
					{
						list($y, $m, $d) = explode('-', $date);

						$confirmation->addHiddenItem("{$item->getPostVar()}[date][y]", $y);
						$confirmation->addHiddenItem("{$item->getPostVar()}[date][m]", $m);
						$confirmation->addHiddenItem("{$item->getPostVar()}[date][d]", $d);

						if( $item->getShowTime() )
						{
							list($h, $m, $s) = explode('-', $time);

							$confirmation->addHiddenItem("{$item->getPostVar()}[time][h]", $h);
							$confirmation->addHiddenItem("{$item->getPostVar()}[time][m]", $m);
							$confirmation->addHiddenItem("{$item->getPostVar()}[time][s]", $s);
						}
					}
					else
					{
						$confirmation->addHiddenItem("{$item->getPostVar()}[date]", $date);
						$confirmation->addHiddenItem("{$item->getPostVar()}[time]", $time);
					}

					break;
					
				case 'duration':
					
					$confirmation->addHiddenItem("{$item->getPostVar()}[MM]", (int)$item->getMonths());
					$confirmation->addHiddenItem("{$item->getPostVar()}[dd]", (int)$item->getDays());
					$confirmation->addHiddenItem("{$item->getPostVar()}[hh]", (int)$item->getHours());
					$confirmation->addHiddenItem("{$item->getPostVar()}[mm]", (int)$item->getMinutes());
					$confirmation->addHiddenItem("{$item->getPostVar()}[ss]", (int)$item->getSeconds());
					
					break;

				case 'checkboxgroup':
					
					if( is_array($item->getValue()) )
					{
						foreach( $item->getValue() as $option )
						{
							$confirmation->addHiddenItem("{$item->getPostVar()}[]", $option);
						}
					}
					
					break;
					
				case 'checkbox':
					
					if( $item->getChecked() )
					{
						$confirmation->addHiddenItem($item->getPostVar(), 1);
					}
					
					break;
				
				default:
					
					$confirmation->addHiddenItem($item->getPostVar(), $item->getValue());
			}
		}
		
		$this->tpl->setContent( $this->ctrl->getHTML($confirmation) );
	}
	
	private function buildForm()
	{
		include_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTableWidth('100%');
		$form->setId('test_scoring_results');

		$this->addScoringSettingsFormSection($form);
		$this->addPresentationSettingsFormSection($form);
		$this->addResultSettingsFormSection($form);
		$this->addMiscSettingsFormSection($form);

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

		$form->addCommandButton(self::CMD_SAVE_FORM, $this->lng->txt('save'));

		return $form;
	}

	private function addScoringSettingsFormSection(ilPropertyFormGUI $form)
	{
		// scoring settings
		$header = new ilFormSectionHeaderGUI();
		$header->setTitle($this->lng->txt('test_scoring'));
		$form->addItem($header);

		// scoring system
		$count_system = new ilRadioGroupInputGUI($this->lng->txt('tst_text_count_system'), 'count_system');
		$count_system->addOption(new ilRadioOption($this->lng->txt('tst_count_partial_solutions'), 0, ''));
		$count_system->addOption(new ilRadioOption($this->lng->txt('tst_count_correct_solutions'), 1, ''));
		$count_system->setValue($this->testOBJ->getCountSystem());
		$count_system->setInfo($this->lng->txt('tst_count_system_description'));
		$form->addItem($count_system);

		// mc questions
		$mc_scoring = new ilRadioGroupInputGUI($this->lng->txt('tst_score_mcmr_questions'), 'mc_scoring');
		$mc_scoring->addOption(new ilRadioOption($this->lng->txt('tst_score_mcmr_zero_points_when_unanswered'), 0, ''));
		$mc_scoring->addOption(new ilRadioOption($this->lng->txt('tst_score_mcmr_use_scoring_system'), 1, ''));
		$mc_scoring->setValue($this->testOBJ->getMCScoring());
		$mc_scoring->setInfo($this->lng->txt('tst_score_mcmr_questions_description'));
		$form->addItem($mc_scoring);

		// score cutting
		$score_cutting = new ilRadioGroupInputGUI($this->lng->txt('tst_score_cutting'), 'score_cutting');
		$score_cutting->addOption(new ilRadioOption($this->lng->txt('tst_score_cut_question'), 0, ''));
		$score_cutting->addOption(new ilRadioOption($this->lng->txt('tst_score_cut_test'), 1, ''));
		$score_cutting->setValue($this->testOBJ->getScoreCutting());
		$score_cutting->setInfo($this->lng->txt('tst_score_cutting_description'));
		$form->addItem($score_cutting);

		// pass scoring
		$pass_scoring = new ilRadioGroupInputGUI($this->lng->txt('tst_pass_scoring'), 'pass_scoring');
		$pass_scoring->addOption(new ilRadioOption($this->lng->txt('tst_pass_last_pass'), 0, ''));
		$pass_scoring->addOption(new ilRadioOption($this->lng->txt('tst_pass_best_pass'), 1, ''));
		$pass_scoring->setValue($this->testOBJ->getPassScoring());
		$pass_scoring->setInfo($this->lng->txt('tst_pass_scoring_description'));
		$form->addItem($pass_scoring);

		// disable scoring settings
		if( !$this->areScoringSettingsWritable() )
		{
			$count_system->setDisabled(true);
			$mc_scoring->setDisabled(true);
			$score_cutting->setDisabled(true);
			$pass_scoring->setDisabled(true);
		}
	}

	private function addPresentationSettingsFormSection(ilPropertyFormGUI $form)
	{
		// test presentation
		$header_tp = new ilFormSectionHeaderGUI();
		$header_tp->setTitle($this->lng->txt('test_presentation'));
		$form->addItem($header_tp);

		// enable obligations
		$checkBoxEnableObligations = new ilCheckboxInputGUI($this->lng->txt('tst_setting_enable_obligations_label'), 'obligations_enabled');
		$checkBoxEnableObligations->setChecked($this->testOBJ->areObligationsEnabled());
		$checkBoxEnableObligations->setInfo($this->lng->txt('tst_setting_enable_obligations_info'));
		$form->addItem($checkBoxEnableObligations);

		// offer hints
		$checkBoxOfferHints = new ilCheckboxInputGUI($this->lng->txt('tst_setting_offer_hints_label'), 'offer_hints');
		$checkBoxOfferHints->setChecked($this->testOBJ->isOfferingQuestionHintsEnabled());
		$checkBoxOfferHints->setInfo($this->lng->txt('tst_setting_offer_hints_info'));
		$form->addItem($checkBoxOfferHints);

		// disable settings influencing results indirectly
		if( $this->testOBJ->participantDataExist() )
		{
			$checkBoxEnableObligations->setDisabled(true);
			$checkBoxOfferHints->setDisabled(true);
		}

		// instant feedback
		$instant_feedback = new ilCheckboxGroupInputGUI($this->lng->txt('tst_instant_feedback'), 'instant_feedback');
		$instant_feedback->addOption(new ilCheckboxOption(
			$this->lng->txt('tst_instant_feedback_answer_specific'), 'instant_feedback_specific',
			$this->lng->txt('tst_instant_feedback_answer_specific_desc')
		));
		$instant_feedback->addOption(new ilCheckboxOption(
			$this->lng->txt('tst_instant_feedback_answer_generic'), 'instant_feedback_generic',
			$this->lng->txt('tst_instant_feedback_answer_generic_desc')
		));
		$instant_feedback->addOption(new ilCheckboxOption(
			$this->lng->txt('tst_instant_feedback_results'), 'instant_feedback_points',
			$this->lng->txt('tst_instant_feedback_results_desc')
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
		$instant_feedback->setInfo($this->lng->txt('tst_instant_feedback_description'));
		$form->addItem($instant_feedback);
	}

	private function addResultSettingsFormSection(ilPropertyFormGUI $form)
	{
		// result settings
		$header_tr = new ilFormSectionHeaderGUI();
		$header_tr->setTitle($this->lng->txt('test_results'));
		$form->addItem($header_tr);

		// access to test results
		$results_access = new ilRadioGroupInputGUI($this->lng->txt('tst_results_access'), 'results_access');
		$results_access->addOption(new ilRadioOption($this->lng->txt('tst_results_access_always'), 2, ''));
		$results_access->addOption(new ilRadioOption($this->lng->txt('tst_results_access_finished'), 1, ''));
		$results_access_date_limitation = new ilRadioOption($this->lng->txt('tst_results_access_date'), 3, '');
		$results_access->addOption($results_access_date_limitation);
		$results_access->addOption(new ilRadioOption($this->lng->txt('tst_results_access_never'), 4, ''));
		$results_access->setValue($this->testOBJ->getScoreReporting());
		$results_access->setInfo($this->lng->txt('tst_results_access_description'));

		// access date
		$reporting_date = new ilDateTimeInputGUI('', 'reporting_date');
		$reporting_date->setShowDate(true);
		$reporting_date->setShowTime(true);
		if (strlen($this->testOBJ->getReportingDate()))
		{
			$reporting_date->setDate(new ilDateTime($this->testOBJ->getReportingDate(), IL_CAL_TIMESTAMP));
		}
		else
		{
			$reporting_date->setDate(new ilDateTime(time(), IL_CAL_UNIX));
		}
		$results_access_date_limitation->addSubItem($reporting_date);
		$form->addItem($results_access);

		// grading in test results
		$rg = new ilRadioGroupInputGUI($this->lng->txt('tst_results_grading'), 'show_result_grading');
		$rg->addOption(new ilRadioOption(
			$this->lng->txt('tst_results_grading_opt_show_both'), self::FORM_FIELD_VALUE_RESULTS_GRADING_SHOW_BOTH
		));
		$rg->addOption(new ilRadioOption(
			$this->lng->txt('tst_results_grading_opt_show_none'), self::FORM_FIELD_VALUE_RESULTS_GRADING_SHOW_NONE
		));
		$rg->addOption(new ilRadioOption(
			$this->lng->txt('tst_results_grading_opt_show_status'), self::FORM_FIELD_VALUE_RESULTS_GRADING_SHOW_STATUS
		));
		$rg->addOption(new ilRadioOption(
			$this->lng->txt('tst_results_grading_opt_show_mark'), self::FORM_FIELD_VALUE_RESULTS_GRADING_SHOW_MARK
		));
		$rg->setValue($this->getShowGradingFormFieldValue(
			$this->testOBJ->isShowGradingStatusEnabled(), $this->testOBJ->isShowGradingMarkEnabled()
		));
		$form->addItem($rg);

		// best solution in test results
		$results_print_best_solution = new ilCheckboxInputGUI($this->lng->txt('tst_results_print_best_solution'), 'print_bs_with_res');
		$results_print_best_solution->setInfo($this->lng->txt('tst_results_print_best_solution_info'));
		$results_print_best_solution->setValue(1);
		$results_print_best_solution->setChecked((bool) $this->testOBJ->isBestSolutionPrintedWithResult());
		$form->addItem($results_print_best_solution);

		// results presentation
		$results_presentation = new ilCheckboxGroupInputGUI($this->lng->txt('tst_results_presentation'), 'results_presentation');
		$results_presentation->addOption(new ilCheckboxOption($this->lng->txt('tst_show_pass_details'), 'pass_details', ''));
		$results_presentation->addOption(new ilCheckboxOption($this->lng->txt('tst_show_solution_details'), 'solution_details', ''));
		$results_presentation->addOption(new ilCheckboxOption($this->lng->txt('tst_show_solution_printview'), 'solution_printview', ''));
		$results_presentation->addOption(new ilCheckboxOption($this->lng->txt('tst_show_solution_compare'), 'solution_compare', ''));
		$results_presentation->addOption(new ilCheckboxOption($this->lng->txt('tst_show_solution_feedback'), 'solution_feedback', ''));
		$results_presentation->addOption(new ilCheckboxOption($this->lng->txt('tst_show_solution_answers_only'), 'solution_answers_only', ''));
		$signatureOption = new ilCheckboxOption($this->lng->txt('tst_show_solution_signature'), 'solution_signature', '');
		$results_presentation->addOption($signatureOption);
		$results_presentation->addOption(new ilCheckboxOption($this->lng->txt('tst_show_solution_suggested'), 'solution_suggested', ''));
		$values = array();
		if ($this->testOBJ->getShowPassDetails()) array_push($values, 'pass_details');
		if ($this->testOBJ->getShowSolutionDetails()) array_push($values, 'solution_details');
		if ($this->testOBJ->getShowSolutionPrintview()) array_push($values, 'solution_printview');
		if ($this->testOBJ->getShowSolutionFeedback()) array_push($values, 'solution_feedback');
		if ($this->testOBJ->getShowSolutionAnswersOnly()) array_push($values, 'solution_answers_only');
		if ($this->testOBJ->getShowSolutionSignature()) array_push($values, 'solution_signature');
		if ($this->testOBJ->getShowSolutionSuggested()) array_push($values, 'solution_suggested');
		if ($this->testOBJ->getShowSolutionListComparison()) array_push($values, 'solution_compare');
		$results_presentation->setValue($values);
		$results_presentation->setInfo($this->lng->txt('tst_results_presentation_description'));
		if ($this->testOBJ->getAnonymity())
		{
			$signatureOption->setDisabled(true);
		}
		$form->addItem($results_presentation);
	}

	private function addMiscSettingsFormSection(ilPropertyFormGUI $form)
	{
		// misc settings
		$header_misc = new ilFormSectionHeaderGUI();
		$header_misc->setTitle($this->lng->txt('misc'));
		$form->addItem($header_misc);

		// deletion of test results
		$passDeletion = new ilRadioGroupInputGUI($this->lng->txt('tst_pass_deletion'), 'pass_deletion_allowed');
		$passDeletion->addOption(new ilRadioOption($this->lng->txt('tst_pass_deletion_not_allowed'), 0, ''));
		$passDeletion->addOption(new ilRadioOption($this->lng->txt('tst_pass_deletion_allowed'), 1, ''));
		$passDeletion->setValue($this->testOBJ->isPassDeletionAllowed());
		$form->addItem($passDeletion);

		// export settings
		$export_settings = new ilCheckboxGroupInputGUI($this->lng->txt('tst_export_settings'), 'export_settings');
		$export_settings->addOption(new ilCheckboxOption($this->lng->txt('tst_exp_sc_short'), 'exp_sc_short', ''));
		$values = array();
		if( $this->testOBJ->getExportSettingsSingleChoiceShort() )
		{
			$values[] = 'exp_sc_short';
		}
		$export_settings->setValue($values);
		$form->addItem($export_settings);

		// result filter taxonomies
		if( $this->testQuestionSetConfigFactory->getQuestionSetConfig()->isResultTaxonomyFilterSupported() )
		{
			$availableTaxonomyIds = $this->getAvailableTaxonomyIds();

			if( count($availableTaxonomyIds) )
			{
				require_once 'Modules/Test/classes/class.ilTestTaxonomyFilterLabelTranslater.php';
				$labelTranslater = new ilTestTaxonomyFilterLabelTranslater($this->db);
				$labelTranslater->loadLabelsFromTaxonomyIds($availableTaxonomyIds);

				$results_presentation = new ilCheckboxGroupInputGUI($this->lng->txt('tst_results_tax_filters'), 'results_tax_filters');

				foreach($availableTaxonomyIds as $taxonomyId)
				{
					$results_presentation->addOption(new ilCheckboxOption(
						$labelTranslater->getTaxonomyTreeLabel($taxonomyId), $taxonomyId, ''
					));
				}

				$results_presentation->setValue($this->testOBJ->getResultFilterTaxIds());

				$form->addItem($results_presentation);
			}
		}
	}

	private function areScoringSettingsWritable()
	{
		if ( !$this->testOBJ->participantDataExist() )
		{
			return true;
		}

		if( !$this->testOBJ->isScoreReportingAvailable() )
		{
			return true;
		}

		return false;
	}

	private function isScoreRecalculationRequired(ilPropertyFormGUI $form)
	{
		if ( !$this->testOBJ->participantDataExist() )
		{
			return false;
		}

		if( !$this->areScoringSettingsWritable() )
		{
			return false;
		}

		if( !$this->hasScoringSettingsChanged($form) )
		{
			return false;
		}

		return true;
	}

	private function hasScoringSettingsChanged(ilPropertyFormGUI $form)
	{
		$countSystem = $form->getItemByPostVar('count_system');
		if( is_object($countSystem) && $countSystem->getValue() != $this->testOBJ->getCountSystem() )
		{
			return true;
		}

		$mcScoring = $form->getItemByPostVar('mc_scoring');
		if( is_object($mcScoring) && $mcScoring != $this->testOBJ->getMCScoring() )
		{
			return true;
		}

		$scoreCutting = $form->getItemByPostVar('score_cutting');
		if( is_object($scoreCutting) && $scoreCutting->getValue() != $this->testOBJ->getScoreCutting() )
		{
			return true;
		}

		$passScoring = $form->getItemByPostVar('pass_scoring');
		if( is_object($passScoring) && $passScoring->getValue() != $this->testOBJ->getPassScoring() )
		{
			return true;
		}

		return false;
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

	private $availableTaxonomyIds = null;

	private function getAvailableTaxonomyIds()
	{
		if( $this->getAvailableTaxonomyIds === null )
		{
			require_once 'Services/Taxonomy/classes/class.ilObjTaxonomy.php';
			$this->availableTaxonomyIds = (array)ilObjTaxonomy::getUsageOfObject($this->testOBJ->getId());
		}

		return $this->availableTaxonomyIds;
	}

	/**
	 * @param $showGradingStatusEnabled
	 * @param $showGradingMarkEnabled
	 * @return string
	 */
	private function getShowGradingFormFieldValue($showGradingStatusEnabled, $showGradingMarkEnabled)
	{
		if( $showGradingStatusEnabled && $showGradingMarkEnabled )
		{
			$formFieldValue = self::FORM_FIELD_VALUE_RESULTS_GRADING_SHOW_BOTH;
		}
		elseif( $showGradingStatusEnabled )
		{
			$formFieldValue = self::FORM_FIELD_VALUE_RESULTS_GRADING_SHOW_STATUS;
		}
		elseif( $showGradingMarkEnabled )
		{
			$formFieldValue = self::FORM_FIELD_VALUE_RESULTS_GRADING_SHOW_MARK;
		}
		else
		{
			$formFieldValue = self::FORM_FIELD_VALUE_RESULTS_GRADING_SHOW_NONE;
		}

		return $formFieldValue;
	}
	
	private function isHiddenFormItem($formFieldId)
	{
		$settings = $this->settingsTemplate->getSettings();
		
		if( !isset($settings[$formFieldId]) )
		{
			return false;
		}
		
		if( !$settings[$formFieldId]['hide']] )
		{
			return false;
		}
		
		return true;
	}
}
