<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\Test\Settings\GlobalSettings;

class Repository
{
    private const SETTINGS_KEY_DISABLED_QUESTION_TYPES_LEGACY = 'forbidden_questiontypes';
    private const SETTINGS_KEY_MANUAL_SCORING_LEGACY = 'assessment_manual_scoring';

    private const SETTINGS_KEY_PROCESS_LOCK_MODE = 'ass_process_lock_mode';
    private const SETTINGS_KEY_IMAGE_MAP_LINE_COLOR = 'imap_line_color';
    private const SETTINGS_KEY_UNIQUE_USER_IDENTIFIER = 'user_criteria';
    private const SETTINGS_KEY_SKILL_TRIGGERING_NUMBER_OF_ANSWERS = 'ass_skl_trig_num_answ_barrier';
    private const SETTINGS_KEY_EXPORT_ESSAY_QUESTIONS_AS_HTML = 'export_essay_qst_with_html';
    private const SETTINGS_KEY_DISABLED_QUESTION_TYPES = 'disabled_question_types';
    private const SETTINGS_KEY_MANUAL_SCORING_ENABLED = 'manual_scoring';
    private const SETTINGS_KEY_ADJUSTING_QUESTIONS_WITH_RESULTS_ALLOWED = 'assessment_adjustments_enabled';
    private const SETTINGS_KEY_PAGE_EDITOR_ENABLED = 'enable_tst_page_edit';

    private const SETTINGS_KEY_LOGGING_ENABLED = 'assessment_logging';
    private const SETTINGS_KEY_IP_LOGGING_ENABLED = 'assessment_logging_ip';

    private ?GlobalTestSettings $global_test_settings = null;
    private ?TestLoggingSettings $test_logging_settings = null;

    public function __construct(
        private \ilSetting $settings
    ) {
    }

    public function getGlobalSettings(): GlobalTestSettings
    {
        if ($this->global_test_settings === null) {
            $this->global_test_settings = $this->buildGlobalTestSettings();
        }
        return $this->global_test_settings;
    }

    private function buildGlobalTestSettings(): GlobalTestSettings
    {
        $global_settings = new GlobalTestSettings();

        if (($process_lock_mode = ProcessLockModes::tryFrom(
            $this->settings->get(self::SETTINGS_KEY_PROCESS_LOCK_MODE) ?? ''
        )) !== null
        ) {
            $global_settings = $global_settings->withProcessLockMode($process_lock_mode);
        }

        if (($image_map_line_color = $this->settings->get(self::SETTINGS_KEY_IMAGE_MAP_LINE_COLOR)) !== null) {
            $global_settings = $global_settings->withImageMapLineColor($image_map_line_color);
        }

        if (($user_identifier = UserIdentifiers::tryFrom(
            $this->settings->get(self::SETTINGS_KEY_UNIQUE_USER_IDENTIFIER) ?? ''
        )) !== null
        ) {
            $global_settings = $global_settings->withUserIdentifier($user_identifier);
        }

        if (($skill_triggering_number_of_answers = $this->settings->get(self::SETTINGS_KEY_SKILL_TRIGGERING_NUMBER_OF_ANSWERS)) !== null) {
            $global_settings = $global_settings->withSkillTriggeringNumberOfAnswers((int) $skill_triggering_number_of_answers);
        }

        if (($export_essay_questions_as_html = $this->settings->get(self::SETTINGS_KEY_EXPORT_ESSAY_QUESTIONS_AS_HTML)) !== null) {
            $global_settings = $global_settings->withExportEssayQuestionsAsHtml($export_essay_questions_as_html === '1');
        }

        if (($disabled_question_types_legacy = $this->settings->get(self::SETTINGS_KEY_DISABLED_QUESTION_TYPES_LEGACY)) !== null) {
            $this->migrateLegacyQuestionTypes($disabled_question_types_legacy);
        }

        if (($disabled_question_types = $this->settings->get(self::SETTINGS_KEY_DISABLED_QUESTION_TYPES)) !== null) {
            $global_settings = $global_settings->withDisabledQuestionTypes(explode(',', $disabled_question_types));
        }

        if (($manual_scoring_legacy = $this->settings->get(self::SETTINGS_KEY_MANUAL_SCORING_LEGACY)) !== null) {
            $this->migrateLegacyManualScoring($manual_scoring_legacy);
        }

        if (($manual_scoring_enabled = $this->settings->get(self::SETTINGS_KEY_MANUAL_SCORING_ENABLED)) !== null) {
            $global_settings = $global_settings->withManualScoringEnabled($manual_scoring_enabled === '1');
        }

        if (($adjusting_questions_with_results_allowed = $this->settings->get(self::SETTINGS_KEY_ADJUSTING_QUESTIONS_WITH_RESULTS_ALLOWED)) !== null) {
            $global_settings = $global_settings->withAdjustingQuestionsWithResultsAllowed($adjusting_questions_with_results_allowed === '1');
        }

        if (($page_editor_enabled = $this->settings->get(self::SETTINGS_KEY_PAGE_EDITOR_ENABLED)) !== null) {
            $global_settings = $global_settings->withPageEditorEnabled($page_editor_enabled === '1');
        }

        return $global_settings;
    }

    public function storeGlobalSettings(GlobalTestSettings $global_settings): void
    {
        $this->settings->set(self::SETTINGS_KEY_PROCESS_LOCK_MODE, $global_settings->getProcessLockMode()->value);
        $this->settings->set(self::SETTINGS_KEY_IMAGE_MAP_LINE_COLOR, $global_settings->getImageMapLineColor());
        $this->settings->set(self::SETTINGS_KEY_UNIQUE_USER_IDENTIFIER, $global_settings->getUserIdentifier()->value);
        $this->settings->set(self::SETTINGS_KEY_SKILL_TRIGGERING_NUMBER_OF_ANSWERS, (string) $global_settings->getSkillTriggeringNumberOfAnswers());
        $this->settings->set(self::SETTINGS_KEY_EXPORT_ESSAY_QUESTIONS_AS_HTML, $global_settings->getExportEssayQuestionsAsHtml() ? '1' : '0');
        $this->settings->set(self::SETTINGS_KEY_DISABLED_QUESTION_TYPES, implode(',', $global_settings->getDisabledQuestionTypes()));
        $this->settings->set(self::SETTINGS_KEY_MANUAL_SCORING_ENABLED, $global_settings->isManualScoringEnabled() ? '1' : '0');
        $this->settings->set(self::SETTINGS_KEY_ADJUSTING_QUESTIONS_WITH_RESULTS_ALLOWED, $global_settings->isAdjustingQuestionsWithResultsAllowed() ? '1' : '0');

        $this->global_test_settings = $global_settings;
    }

    public function getLoggingSettings(): TestLoggingSettings
    {
        if ($this->test_logging_settings === null) {
            $this->test_logging_settings = new TestLoggingSettings(
                $this->settings->get(self::SETTINGS_KEY_LOGGING_ENABLED) === '1',
                $this->settings->get(self::SETTINGS_KEY_IP_LOGGING_ENABLED) !== '0'
            );
        }
        return $this->test_logging_settings;
    }

    public function storeLoggingSettings(TestLoggingSettings $logging_settings): void
    {
        $this->settings->set(self::SETTINGS_KEY_LOGGING_ENABLED, $logging_settings->isLoggingEnabled() ? '1' : '0');
        $this->settings->set(self::SETTINGS_KEY_IP_LOGGING_ENABLED, $logging_settings->isIPLoggingEnabled() ? '1' : '0');
    }

    private function migrateLegacyQuestionTypes(string $legacy_types): void
    {
        $this->settings->delete(self::SETTINGS_KEY_DISABLED_QUESTION_TYPES_LEGACY);
        $this->settings->set(self::SETTINGS_KEY_DISABLED_QUESTION_TYPES, implode(',', unserialize($legacy_types, ['allowed_classes' => false])));
    }

    private function migrateLegacyManualScoring(string $legacy_manual_scoring): void
    {
        $this->settings->delete(self::SETTINGS_KEY_MANUAL_SCORING_LEGACY);
        $this->settings->set(self::SETTINGS_KEY_MANUAL_SCORING_ENABLED, array_filter(array_map('intval', explode(',', $legacy_manual_scoring))) !== [] ? '1' : '0');
    }
}
