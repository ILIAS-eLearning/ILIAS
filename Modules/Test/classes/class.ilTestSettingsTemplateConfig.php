<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Administration/classes/class.ilSettingsTemplateConfig.php';

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 */
class ilTestSettingsTemplateConfig extends ilSettingsTemplateConfig
{
	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * @param ilLanguage $lng
	 */
	public function __construct(ilLanguage $lng)
	{
		$this->lng = $lng;
		parent::__construct('tst');
	}

	public function init()
	{
		$this->initLanguage();
		$this->initHidableTabs();
		$this->initSettings();
	}

	private function initLanguage()
	{
		$this->lng->loadLanguageModule("tst");
		$this->lng->loadLanguageModule("assessment");
	}

	private function initHidableTabs()
	{
		$this->addHidableTab("questions", $this->lng->txt('assQuestions').' - '.$this->lng->txt('edit_test_questions'));
		$this->addHidableTab("mark_schema", $this->lng->txt('settings').' - '.$this->lng->txt("mark_schema"));
		$this->addHidableTab("certificate", $this->lng->txt('settings').' - '.$this->lng->txt("certificate"));
		$this->addHidableTab("defaults", $this->lng->txt('settings').' - '.$this->lng->txt("tst_default_settings"));

		$this->addHidableTab("learning_progress", $this->lng->txt("learning_progress"));
		$this->addHidableTab("manscoring", $this->lng->txt("manscoring"));
		$this->addHidableTab("history", $this->lng->txt("history"));
		$this->addHidableTab("meta_data", $this->lng->txt("meta_data"));
		$this->addHidableTab("export", $this->lng->txt("export"));
		$this->addHidableTab("permissions", $this->lng->txt("permission"));
	}

	private function initSettings()
	{
		//general properties
		$this->addSetting(
			"anonymity",
			ilSettingsTemplateConfig::SELECT,
			$this->lng->txt("tst_anonymity"),
			false,
			0,
			array(
				'0' => $this->lng->txt("tst_anonymity_no_anonymization"),
				'1' => $this->lng->txt("tst_anonymity_anonymous_test"),
			)
		);

		$this->addSetting(
			"title_output",
			ilSettingsTemplateConfig::SELECT,
			$this->lng->txt("tst_title_output"),
			true,
			0,
			array(
				'0' => $this->lng->txt("test_enable_view_table"),
				'1' => $this->lng->txt("test_enable_view_express"),
				'2' => $this->lng->txt("test_enable_view_both"),
			)
		);

		$this->addSetting(
			"question_set_type",
			ilSettingsTemplateConfig::SELECT,
			$this->lng->txt("tst_question_set_type"),
			true,
			0,
			array(
				ilObjTest::QUESTION_SET_TYPE_FIXED => $this->lng->txt("tst_question_set_type_fixed"),
				ilObjTest::QUESTION_SET_TYPE_RANDOM => $this->lng->txt("tst_question_set_type_random"),
				ilObjTest::QUESTION_SET_TYPE_DYNAMIC => $this->lng->txt("tst_question_set_type_dynamic"),
			)
		);

		$this->addSetting(
			"use_pool",
			ilSettingsTemplateConfig::BOOL,
			$this->lng->txt("test_question_pool_usage"),
			true
		);

		// Information at beginning and end of test
		$this->addSetting(
			"showinfo",
			ilSettingsTemplateConfig::BOOL,
			$this->lng->txt("showinfo"),
			true
		);

		$this->addSetting(
			"showfinalstatement",
			ilSettingsTemplateConfig::BOOL,
			$this->lng->txt("final_statement_show"),
			false
		);

		// Session Settings
		$this->addSetting(
			"nr_of_tries",
			ilSettingsTemplateConfig::TEXT,
			$this->lng->txt("tst_nr_of_tries"),
			false,
			3
		);


		$this->addSetting(
			"chb_processing_time",
			ilSettingsTemplateConfig::BOOL,
			$this->lng->txt("tst_processing_time"),
			false
		);

		$this->addSetting(
			"chb_starting_time",
			ilSettingsTemplateConfig::BOOL,
			$this->lng->txt("tst_starting_time"),
			false
		);

		$this->addSetting(
			"chb_ending_time",
			ilSettingsTemplateConfig::BOOL,
			$this->lng->txt("tst_ending_time"),
			false
		);

		$this->addSetting(
			"password",
			ilSettingsTemplateConfig::TEXT,
			$this->lng->txt("tst_password"),
			true,
			20
		);

		// Presentation Properties

		$this->addSetting(
			"chb_use_previous_answers",
			ilSettingsTemplateConfig::BOOL,
			$this->lng->txt("tst_use_previous_answers"),
			false
		);
		$this->addSetting(
			"forcejs",
			ilSettingsTemplateConfig::BOOL,
			$this->lng->txt("forcejs_short"),
			true
		);

		$this->addSetting(
			"title_output",
			ilSettingsTemplateConfig::SELECT,
			$this->lng->txt("tst_title_output"),
			true,
			0,
			array(
				'0' => $this->lng->txt("test_enable_view_table"),
				'1' => $this->lng->txt("test_enable_view_express"),
				'2' => $this->lng->txt("test_enable_view_both"),
			)
		);

		// Sequence Properties

		$this->addSetting(
			"chb_postpone",
			ilSettingsTemplateConfig::BOOL,
			$this->lng->txt("tst_postpone"),
			true
		);
		$this->addSetting(
			"chb_shuffle_questions",
			ilSettingsTemplateConfig::BOOL,
			$this->lng->txt("tst_shuffle_questions"),
			false
		);
		$this->addSetting(
			"list_of_questions",
			ilSettingsTemplateConfig::BOOL,
			$this->lng->txt("tst_show_summary"),
			false
		);

		$this->addSetting(
			"chb_show_marker",
			ilSettingsTemplateConfig::BOOL,
			$this->lng->txt("question_marking"),
			true
		);
		$this->addSetting(
			"chb_show_cancel",
			ilSettingsTemplateConfig::BOOL,
			$this->lng->txt("tst_show_cancel"),
			true
		);

		// Notifications

		$this->addSetting(
			"mailnotification",
			ilSettingsTemplateConfig::SELECT,
			$this->lng->txt("tst_finish_notification"),
			true,
			0,
			array(
				'0' => $this->lng->txt("tst_finish_notification_no"),
				'1' => $this->lng->txt("tst_finish_notification_simple"),
				'2' => $this->lng->txt("tst_finish_notification_advanced"),
			)
		);

		$this->addSetting(
			"mailnottype",
			ilSettingsTemplateConfig::BOOL,
			$this->lng->txt("mailnottype"),
			true
		);

		// Kiosk Mode

		$this->addSetting(
			"kiosk",
			ilSettingsTemplateConfig::BOOL,
			$this->lng->txt("kiosk"),
			true
		);


		// Participants Restriction

		$this->addSetting(
			"fixedparticipants",
			ilSettingsTemplateConfig::BOOL,
			$this->lng->txt("participants_invitation"),
			true
		);

		$this->addSetting(
			"allowedUsers",
			ilSettingsTemplateConfig::TEXT,
			$this->lng->txt("tst_allowed_users"),
			true,
			3
		);

		$this->addSetting(
			"allowedUsersTimeGap",
			ilSettingsTemplateConfig::TEXT,
			$this->lng->txt("tst_allowed_users_time_gap"),
			true,
			4
		);

		/////////////////////////////////////
		// Scoring and Results
		/////////////////////////////////////

		$this->addSetting(
			"count_system",
			ilSettingsTemplateConfig::SELECT,
			$this->lng->txt("tst_text_count_system"),
			true,
			0,
			array(
				'0' => $this->lng->txt("tst_count_partial_solutions"),
				'1' => $this->lng->txt("tst_count_correct_solutions"),
			)
		);

		$this->addSetting(
			"mc_scoring",
			ilSettingsTemplateConfig::SELECT,
			$this->lng->txt("tst_score_mcmr_questions"),
			true,
			0,
			array(
				'0' => $this->lng->txt("tst_score_mcmr_zero_points_when_unanswered"),
				'1' => $this->lng->txt("tst_score_mcmr_use_scoring_system"),
			)
		);

		$this->addSetting(
			"score_cutting",
			ilSettingsTemplateConfig::SELECT,
			$this->lng->txt("tst_score_cutting"),
			true,
			0,
			array(
				'0' => $this->lng->txt("tst_score_cut_question"),
				'1' => $this->lng->txt("tst_score_cut_test"),
			)
		);

		$this->addSetting(
			"pass_scoring",
			ilSettingsTemplateConfig::SELECT,
			$this->lng->txt("tst_pass_scoring"),
			false,
			0,
			array(
				'0' => $this->lng->txt("tst_pass_last_pass"),
				'1' => $this->lng->txt("tst_pass_best_pass"),
			)
		);

		$this->addSetting(
			"instant_feedback",
			ilSettingsTemplateConfig::CHECKBOX,
			$this->lng->txt("tst_instant_feedback"),
			false,
			0,
			array(
				'instant_feedback_answer' => $this->lng->txt("tst_instant_feedback_answer_specific"),
				'instant_feedback_points' => $this->lng->txt("tst_instant_feedback_results"),
				'instant_feedback_solution' => $this->lng->txt("tst_instant_feedback_solution"),
			)
		);

		$this->addSetting(
			"results_access",
			ilSettingsTemplateConfig::SELECT,
			$this->lng->txt("tst_results_access"),
			false,
			0,
			array(
				'1' => $this->lng->txt("tst_results_access_finished"),
				'2' => $this->lng->txt("tst_results_access_always"),
				'3' => $this->lng->txt("tst_results_access_never"),
				'4' => $this->lng->txt("tst_results_access_date"),
			)
		);

		$this->addSetting(
			"print_bs_with_res",
			ilSettingsTemplateConfig::BOOL,
			$this->lng->txt("tst_results_print_best_solution"),
			true
		);

		$this->addSetting(
			"results_presentation",
			ilSettingsTemplateConfig::CHECKBOX,
			$this->lng->txt("tst_results_presentation"),
			false,
			0,
			array(
				'pass_details' => $this->lng->txt("tst_show_pass_details"),
				'solution_details' => $this->lng->txt("tst_show_solution_details"),
				'solution_printview' => $this->lng->txt("tst_show_solution_printview"),
				'solution_feedback' => $this->lng->txt("tst_show_solution_feedback"),
				'solution_answers_only' => $this->lng->txt("tst_show_solution_answers_only"),
				'solution_signature' => $this->lng->txt("tst_show_solution_signature"),
				'solution_suggested' => $this->lng->txt("tst_show_solution_suggested"),
			)
		);

		$this->addSetting(
			"export_settings",
			ilSettingsTemplateConfig::BOOL,
			$this->lng->txt("tst_export_settings"),
			true
		);
	}
}