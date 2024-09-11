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

namespace ILIAS\Test\Logging;

use ILIAS\TestQuestionPool\Questions\GeneralQuestionPropertiesRepository;

use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Component\Listing\Descriptive as DescriptiveListing;
use ILIAS\Refinery\Factory as Refinery;

class AdditionalInformationGenerator
{
    public const DATE_STORAGE_FORMAT = \DateTimeInterface::ISO8601;
    public const KEY_USER = 'user';
    public const KEY_USERS = 'users';
    public const KEY_QUESTION_ID = 'id';
    public const KEY_QUESTION_ORDER = 'order';
    public const KEY_QUESTIONS = 'questions';
    public const KEY_ANON_IDS = 'anonymous';

    public const KEY_EVAL_FINALIZED = 'evaluation_finalized';
    public const KEY_FEEDBACK = 'tst_feedback';
    public const KEY_QUESTION_TITLE = 'question_title';
    public const KEY_QUESTION_TEXT = 'tst_question';
    public const KEY_QUESTION_TYPE = 'tst_question_type';
    public const KEY_HOMOGENEOUS_SCORING = 'tst_inp_all_quest_points_equal_per_pool_desc';
    public const KEY_QUESTION_AMOUNT_TYPE = 'tst_inp_quest_amount_cfg_mode';
    public const KEY_QUESTION_AMOUNT_PER_TEST = 'tst_inp_quest_amount_per_test';
    public const KEY_QUESTION_AMOUNT_PER_POOL = 'tst_inp_quest_amount_per_source_pool';
    public const KEY_SOURCE_POOL = 'obj_qpl';
    public const KEY_SOURCE_TAXONOMY_FILTER = 'tst_inp_source_pool_filter_tax';
    public const KEY_SOURCE_TYPE_FILTER = 'tst_filter_question_type';
    public const KEY_SOURCE_LIFECYCLE_FILTER = 'qst_lifecycle';
    public const KEY_REACHED_POINTS = 'tst_reached_points';
    public const KEY_MARK_SHORT_NAME = 'tst_mark_short_form';
    public const KEY_MARK_OFFICIAL_NAME = 'tst_mark_official_form';
    public const KEY_MARK_MINIMUM_LEVEL = 'tst_mark_minimum_level';
    public const KEY_MARK_IS_PASSING = 'tst_mark_passed';

    public const KEY_TEST_TITLE = 'title';
    public const KEY_TEST_DESCRIPTION = 'description';
    public const KEY_TEST_ONLINE = 'online';
    public const KEY_TEST_VISIBILITY_PERIOD = 'crs_visibility_until';
    public const KEY_TEST_VISIBILITY_PERIOD_FROM = 'from';
    public const KEY_TEST_VISIBILITY_PERIOD_UNTIL = 'to';
    public const KEY_TEST_VISIBLE_OUTSIDE_PERIOD = 'activation_visible_when_disabled';
    public const KEY_TEST_QUESTION_SET_TYPE = 'test_question_set_type';
    public const KEY_TEST_ANONYMITY = 'tst_anonymity';
    public const KEY_TEST_INTRODUCTION_ENABLED = 'tst_introduction';
    public const KEY_TEST_CONDITIONS_ENABLED = 'tst_conditions_checkbox_enabled';
    public const KEY_TEST_START_TIME = 'tst_starting_time';
    public const KEY_TEST_END_TIME = 'tst_ending_time';
    public const KEY_TEST_PASSWORD = 'tst_password';
    public const KEY_TEST_IP_RANGE = 'ip_range_label';
    public const KEY_TEST_FIXED_PARTICIPANTS = 'participants_invitation';
    public const KEY_TEST_LIMIT_NR_OF_TRIES = 'tst_limit_nr_of_tries';
    public const KEY_TEST_BLOCK_AFTER_PASSED = 'tst_block_passes_after_passed';
    public const KEY_TEST_PASSWAITING_ENABLED = 'tst_pass_waiting_enabled';
    public const KEY_TEST_PROCESSING_TIME_ENABLED = 'tst_processing_time_duration';
    public const KEY_TEST_RESET_PROCESSING_TIME = 'tst_reset_processing_time';
    public const KEY_TEST_KIOSK_ENABLED = 'kiosk';
    public const KEY_TEST_KIOSK_SHOW_TITLE = 'kiosk_show_title';
    public const KEY_TEST_KIOSK_SHOW_PARTICIPANT_NAME = 'kiosk_show_participant';
    public const KEY_TEST_SHOW_EXAM_ID = 'examid_in_test_pass';
    public const KEY_TEST_TITLE_PRESENTATION = 'tst_title_output';
    public const KEY_TEST_AUTOSAVE_ENABLED = 'autosave';
    public const KEY_TEST_SHUFFLE_QUESTIONS = 'tst_shuffle_questions';
    public const KEY_TEST_HINTS_ENABLED = 'tst_setting_offer_hints_label';
    public const KEY_TEST_FEEDBACK_ENABLED = 'tst_instant_feedback';
    public const KEY_TEST_FEEDBACK_SHOW_POINTS = 'tst_instant_feedback_results';
    public const KEY_TEST_FEEDBACK_SHOW_GENERIC = 'tst_instant_feedback_answer_generic';
    public const KEY_TEST_FEEDBACK_SHOW_SPECIFIC = 'tst_instant_feedback_answer_specific';
    public const KEY_TEST_FEEDBACK_SHOW_SOLUTION = 'tst_instant_feedback_solution';
    public const KEY_TEST_FEEDBACK_TRIGGER = 'tst_instant_feedback_trigger';
    public const KEY_TEST_LOCK_ANSWERS_MODE = 'tst_answer_fixation_handling';
    public const KEY_TEST_USE_PREVIOUS_ANSWERS_ENABELD = 'tst_use_previous_answers';
    public const KEY_TEST_SUSPEND_ALLOWED = 'tst_show_cancel';
    public const KEY_TEST_POSTPONED_MOVE_TO_END = 'tst_postpone';
    public const KEY_TEST_OVERVIEW_ENABLED = 'tst_show_summary';
    public const KEY_TEST_OVERVIEW_SHOW_START = 'tst_list_of_questions_start';
    public const KEY_TEST_OVERVIEW_SHOW_END = 'tst_list_of_questions_end';
    public const KEY_TEST_OVERVIEW_SHOW_DESCRIPTION = 'tst_list_of_questions_with_description';
    public const KEY_TEST_QUESTION_MARKING_ENABLED = 'question_marking';
    public const KEY_TEST_QUESTION_LIST_ENABLED = 'tst_enable_questionlist';
    public const KEY_TEST_ANSWER_OVERVIEW_ENABLED = 'enable_examview';
    public const KEY_TEST_CONCLUDING_REMARKS_ENABLED = 'final_statement';
    public const KEY_TEST_REDIRECT_MODE = 'redirect_after_finishing_tst';
    public const KEY_TEST_REDIRECT_URL = 'redirection_url';
    public const KEY_TEST_MAIL_NOTIFICATION_CONTENT_TYPE = 'tst_finish_notification';
    public const KEY_TEST_ALWAYS_SEND_NOTIFICATION = 'tst_finish_notification_content_type';
    public const KEY_TEST_TAXONOMIES_ENABLED = 'tst_activate_skill_service';
    public const KEY_TEST_HIDE_INFO_TAB = 'tst_hide_info_tab';

    public const KEY_SCORING_COUNT_SYSTEM = 'tst_text_count_system';
    public const KEY_SCORING_SCORE_CUTTING = 'tst_score_cutting';
    public const KEY_SCORING_PASS_SCORING = 'tst_pass_scoring';
    public const KEY_SCORING_REPORTING = 'tst_results_access_setting';
    public const KEY_SCORING_REPORTING_SHOW_STATUS = 'tst_results_grading_opt_show_status';
    public const KEY_SCORING_REPORTING_SHOW_MARK = 'tst_results_grading_opt_show_mark';
    public const KEY_SCORING_REPORTING_SHOW_DETAILS = 'tst_results_grading_opt_show_details';
    public const KEY_SCORING_DELETION_ALLOWED = 'tst_pass_deletion';
    public const KEY_SCORING_SOLUTION_SHOW_BEST_SOLUTION = 'tst_results_print_best_solution';
    public const KEY_SCORING_SOLUTION_SHOW_FEEDBACK = 'tst_show_solution_feedback';
    public const KEY_SCORING_SOLUTION_SHOW_SUGGESTED = 'tst_show_solution_suggested';
    public const KEY_SCORING_SOLUTION_SHOW_PRINTVIEW = 'tst_show_solution_printview';
    public const KEY_SCORING_SOLUTION_SHOW_ANSWERS_ONLY = 'tst_hide_pagecontents';
    public const KEY_SCORING_SOLUTION_SHOW_SIGNATRUE = 'tst_show_solution_signature';
    public const KEY_SCORING_SOLUTION_SHOW_EXAM_ID = 'examid_in_test_res';
    public const KEY_SCORING_HIGHSCORE_ENABLED = 'tst_highscore_enabled';
    public const KEY_SCORING_HIGHSCORE_MODE = 'tst_highscore_mode';
    public const KEY_SCORING_HIGHSCORE_SHOW_TOP_NUM = 'tst_highscore_top_num';
    public const KEY_SCORING_HIGHSCORE_SHOW_ANON = 'tst_highscore_anon';
    public const KEY_SCORING_HIGHSCORE_SHOW_ACHIEVED_TS = 'tst_highscore_achieved_ts';
    public const KEY_SCORING_HIGHSCORE_SHOW_SCORE = 'tst_highscore_score';
    public const KEY_SCORING_HIGHSCORE_SHOW_PERCENTAGE = 'tst_highscore_percentage';
    public const KEY_SCORING_HIGHSCORE_SHOW_HINTS = 'tst_highscore_hints';
    public const KEY_SCORING_HIGHSCORE_SHOW_WTIME = 'tst_highscore_wtime';

    public const KEY_PASS = 'pass';
    public const KEY_PAX_ANSWER = 'answer';

    public const KEY_QUESTION_SHUFFLE_ANSWER_OPTIONS = 'shuffle_answers';
    public const KEY_QUESTION_FEEDBACK_ON_INCOMPLETE = 'feedback_incomplete_solution';
    public const KEY_QUESTION_FEEDBACK_ON_COMPLETE = 'feedback_complete_solution';
    public const KEY_QUESTION_ANSWER_OPTION = 'answer';
    public const KEY_QUESTION_ANSWER_OPTIONS = 'answers';
    public const KEY_QUESTION_CORRECT_ANSWER_OPTIONS = 'correct_answers';
    public const KEY_QUESTION_LONGMENU_TEXT = 'lmtext';
    public const KEY_QUESTION_KPRIM_OPTION_LABEL = 'option_label';
    public const KEY_QUESTION_REACHABLE_POINTS = 'points';
    public const KEY_QUESTION_TEXT_MATCHING_METHOD = 'matching_method';
    public const KEY_QUESTION_FORMULA_VARIABLES = 'variables';
    public const KEY_QUESTION_FORMULA_RESULTS = 'results';
    public const KEY_QUESTION_FORMULA_VARIABLE = 'variable';
    public const KEY_QUESTION_FORMULA_RESULT = 'result';
    public const KEY_QUESTION_FORMULA_PRECISION = 'precision';
    public const KEY_QUESTION_FORMULA_INTPRECISION = 'intprecision';
    public const KEY_QUESTION_FORMULA_TOLERANCE = 'tolerance';
    public const KEY_QUESTION_FORMULA_UNIT = 'unit';
    public const KEY_QUESTION_FORMULA_RESULT_TYPE = 'result_type_selection';
    public const KEY_QUESTION_FORMULA_FORMULA = 'formula';
    public const KEY_QUESTION_ORDERING_NESTING_TYPE = 'qst_use_nested_answers';
    public const KEY_QUESTION_ANSWER_OPTION_IMAGE = 'image';
    public const KEY_QUESTION_ANSWER_OPTION_ORDER = 'order';
    public const KEY_QUESTION_ANSWER_OPTION_CORRECTNESS = 'correctness';
    public const KEY_QUESTION_POINTS_CHECKED = 'points_checked';
    public const KEY_QUESTION_POINTS_UNCHECKED = 'points_unchecked';
    public const KEY_QUESTION_IMAGEMAP_IMAGE = 'image';
    public const KEY_QUESTION_IMAGEMAP_MODE = 'tst_imap_qst_mode';
    public const KEY_QUESTION_IMAGEMAP_ANSWER_OPTION_COORDS = 'coordinates';
    public const KEY_QUESTION_IMAGEMAP_ANSWER_OPTION_STATE = 'state';
    public const KEY_QUESTION_TEXTSIZE = 'textsize';
    public const KEY_QUESTION_UPLOAD_MAXSIZE = 'maxsize';
    public const KEY_QUESTION_UPLOAD_ALLOWED_EXTENSIONS = 'allowedextensions';
    public const KEY_QUESTION_UPLOAD_COMPLETION_BY_SUBMISSION = 'ass_completion_by_submission';
    public const KEY_QUESTION_ERRORTEXT_ERRORTEXT = 'assErrorText';
    public const KEY_QUESTION_MAXCHARS = 'maxchars';
    public const KEY_QUESTION_LOWER_LIMIT = 'range_lower_limit';
    public const KEY_QUESTION_UPPER_LIMIT = 'range_upper_limit';
    public const KEY_QUESTION_MATCHING_TERM = 'term';
    public const KEY_QUESTION_MATCHING_TERMS = 'terms';
    public const KEY_QUESTION_MATCHING_DEFINITION = 'definition';
    public const KEY_QUESTION_MATCHING_DEFINITIONS = 'definitions';
    public const KEY_QUESTION_CLOZE_CLOZETEXT = 'cloze_text';
    public const KEY_QUESTION_CLOZE_GAPS = 'gaps';
    public const KEY_QUESTION_CLOZE_GAP_TYPE = 'type';
    public const KEY_QUESTION_KPRIM_SCORE_PARTIAL_SOLUTION_ENABLED = 'score_partsol_enabled';
    public const KEY_QUESTION_TEXT_WORDCOUNT_ENABLED = 'qst_essay_wordcounter_enabled';
    public const KEY_QUESTION_TEXT_SCORING_MODE = 'essay_scoring_mode';

    private const TAG_NONE = '{{ none }}';
    private const TAG_TRUE = '{{ true }}';
    private const TAG_FALSE = '{{ false }}';
    private const TAG_ENABLED = '{{ enabled }}';
    private const TAG_DISABLED = '{{ disabled }}';
    private const TAG_CHECKED = '{{ checked }}';
    private const TAG_UNCHECKED = '{{ unchecked }}';

    private const VALID_TAGS = [
        'none',
        'enabled',
        'disabled',
        'true',
        'false',
        'checked',
        'unchecked',
        'seconds',
        'redirect_always',
        'gap',
        'points',
        'type',
        'answers_select',
        'answers_text_box',
        'tst_finish_notification_simple',
        'tst_finish_notification_advanced',
        'test_question_set_type_fixed',
        'tst_title_output_full',
        'tst_title_output_hide_points',
        'tst_title_output_no_title',
        'tst_title_output_only_points',
        'tst_instant_feedback_trigger_forced',
        'tst_instant_feedback_trigger_manual',
        'tst_answer_fixation_none',
        'tst_answer_fixation_on_instantfb_or_followupqst',
        'tst_answer_fixation_on_instant_feedback',
        'tst_answer_fixation_on_followup_question',
        'tst_highscore_own_table',
        'tst_highscore_top_table',
        'tst_highscore_all_tables',
        'tst_results_access_setting',
        'tst_results_access_finished',
        'tst_results_access_always',
        'tst_results_access_setting',
        'tst_results_access_passed',
        'tst_count_partial_solutions',
        'tst_count_correct_solutions',
        'tst_score_cut_question',
        'tst_score_cut_test',
        'tst_pass_last_pass',
        'tst_pass_best_pass',
        'tst_inp_quest_amount_cfg_mode_pool',
        'tst_imap_qst_mode_mc',
        'tst_imap_qst_mode_sc',
        'option_label_right_wrong',
        'option_label_plus_minus',
        'option_label_applicable_or_not',
        'option_label_adequate_or_not',
        'option_label_custom',
        'qpl_qst_inp_matching_mode_one_on_one',
        'qpl_qst_inp_matching_mode_all_on_all',
        'essay_scoring_mode_without_keywords',
        'essay_scoring_mode_keyword_relation_any',
        'essay_scoring_mode_keyword_relation_all',
        'essay_scoring_mode_keyword_relation_one',
        'qst_nested_nested_answers_off',
        'qst_nested_nested_answers_on',
        'oq_btn_use_order_pictures',
        'oq_btn_use_order_terms',
    ];

    /**
     * @var array<string, string> LEGACY_TAGS Associative array containing mappings
     * from the legacy language tag as key to the new language tag as value
     */
    private const LEGACY_TAGS = [];

    private array $tags;

    public function __construct(
        private readonly \Mustache_Engine $mustache,
        private readonly \ilLanguage $lng,
        private readonly UIFactory $ui_factory,
        private readonly Refinery $refinery,
        private readonly GeneralQuestionPropertiesRepository $questions_repo
    ) {
        $lng->loadLanguageModule('assessment');
        $lng->loadLanguageModule('crs');
        $this->tags = $this->buildTags();
    }

    public function parseForTable(
        array $additional_info,
        array $environment
    ): DescriptiveListing {
        /**
         * @kergomard 01.07.2024: The name of the mark step might be a numeric
         * string. But numeric strings are treated like integers when used as
         * array keys. The descriptive items listing does check its keys if they
         * are strings and things go sideways from there. Thus as a workaround
         * a space is added to the key here.
         */
        return $this->ui_factory->listing()->descriptive(
            array_combine(
                array_map(
                    fn(string $k): string => $this->getCorrectedTranslationForKey($k) . ' ',
                    array_keys($additional_info)
                ),
                array_map(
                    fn(string $k): string => $this->parseValue($k, $additional_info[$k], $environment),
                    array_keys($additional_info)
                )
            )
        );
    }

    public function parseForExport(
        array $additional_info,
        array $environment
    ): string {
        return implode(
            '; ',
            array_map(
                fn($k) => "{$k}: {$this->parseValue($k, $additional_info[$k] ?? '', $environment)}",
                array_keys($additional_info)
            )
        );
    }

    public function getTrueFalseTagForBool(bool $bool): string
    {
        return $bool ? self::TAG_TRUE : self::TAG_FALSE;
    }

    public function getEnabledDisabledTagForBool(bool $bool): string
    {
        return $bool ? self::TAG_ENABLED : self::TAG_DISABLED;
    }

    public function getCheckedUncheckedTagForBool(bool $bool): string
    {
        return $bool ? self::TAG_CHECKED : self::TAG_UNCHECKED;
    }

    public function getNoneTag(): string
    {
        return self::TAG_NONE;
    }

    public function getTagForLangVar(string $lang_var): string
    {
        return "{{ {$lang_var} }}";
    }

    private function getCorrectedTranslationForKey(string $key): string
    {
        $lang_var = $key;
        if (array_key_exists($key, self::LEGACY_TAGS)) {
            $lang_var = self::LEGACY_TAGS[$key];
        }

        return $this->lng->exists($lang_var) ? $this->lng->txt($lang_var) : $key;
    }

    private function parseValue(
        int|string $key,
        string|int|float|array $value,
        array $environment
    ): string {
        switch ($key) {
            case self::KEY_USER:
                return \ilUserUtil::getNamePresentation(
                    $value,
                    false,
                    false,
                    '',
                    true
                );
            case self::KEY_USERS:
                return $this->buildListOfUsers($value);
            case self::KEY_QUESTION_TYPE:
                return $this->lng->txt($value);
            case self::KEY_QUESTIONS:
                return implode(
                    ', ',
                    array_map(
                        fn(int $usr): string => $this->questions_repo
                            ->getForQuestionId($usr)?->getTitle() ?? $this->lng->txt('deleted'),
                        $value
                    )
                );
            case self::KEY_QUESTION_ID:
                if (is_int($value)) {
                    return $this->questions_repo->getForQuestionId($value)?->getTitle() ?? $this->lng->txt('deleted');
                }
                //no break
            default:
                return $this->buildDefaultValueString($value, $environment);
        }
    }

    private function buildListOfUsers(array $user_ids): string
    {
        return implode(
            ', ',
            array_map(
                static fn(int $usr): string => \ilUserUtil::getNamePresentation(
                    $usr,
                    false,
                    false,
                    '',
                    true
                ),
                $user_ids
            )
        );
    }

    private function buildDefaultValueString(
        string|int|float|array $value,
        array $environment
    ): string {
        if (is_int($value)
            || is_float($value)) {
            return (string) $value;
        }
        if (is_array($value)) {
            return $this->buildValueStringFromArray($value, $environment);
        }
        if ($value === '') {
            return $this->lng->txt('none');
        }
        if (strpos($value, '+0000') !== false
            && ($date = \DateTimeImmutable::createFromFormat(self::DATE_STORAGE_FORMAT, $value)) !== false) {
            return $date
                ->setTimezone($environment['timezone'])
                ->format($environment['date_format']);
        }
        return $this->mustache->render(
            $this->refinery->string()->stripTags()->transform($value),
            $this->tags
        );
    }

    private function buildValueStringFromArray(array $value, array $environment): string
    {
        return array_reduce(
            array_keys($value),
            function ($c, $k) use ($value, $environment): string {
                $label = $k;
                if (is_string($k)) {
                    $label = $this->getCorrectedTranslationForKey($k);
                }
                if ($c !== '') {
                    $c .= ', ';
                }
                return "{$c}{$label}: {$this->parseValue($k, $value[$k], $environment)}";
            },
            ''
        );
    }

    private function buildTags(): array
    {
        return array_combine(
            self::VALID_TAGS,
            array_map(
                fn(string $v): string => $this->lng->txt($v),
                self::VALID_TAGS
            )
        ) + array_reduce(
            array_keys(self::LEGACY_TAGS),
            function (array $c, string $k): array {
                $c[$k] = $this->lng->txt(self::LEGACY_TAGS[$k]);
                return $c;
            },
            []
        );
    }
}
