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

use ILIAS\Test\Administration\TestGlobalSettingsRepository;
use ILIAS\Test\Logging\TestLogViewer;
use ILIAS\Test\TestDIC;

/**
 * Class ilObjTestFolder
 * @author    Helmut Schottmüller <hschottm@gmx.de>
 * @author    Björn Heyser <bheyser@databay.de>
 * @ingroup components\ILIASTest
 */
class ilObjTestFolder extends ilObject
{
    public const ADDITIONAL_QUESTION_CONTENT_EDITING_MODE_PAGE_OBJECT_DISABLED = 0;
    public const ADDITIONAL_QUESTION_CONTENT_EDITING_MODE_PAGE_OBJECT_ENABLED = 1;

    public const ASS_PROC_LOCK_MODE_NONE = 'none';
    public const ASS_PROC_LOCK_MODE_FILE = 'file';
    public const ASS_PROC_LOCK_MODE_DB = 'db';

    private const SETTINGS_KEY_SKL_TRIG_NUM_ANSWERS_BARRIER = 'ass_skl_trig_num_answ_barrier';
    public const DEFAULT_SKL_TRIG_NUM_ANSWERS_BARRIER = '1';

    private TestGlobalSettingsRepository $global_settings_repository;
    private ?TestLogViewer $test_log_viewer = null;

    public ilSetting $setting;

    public function __construct(int $a_id = 0, bool $a_call_by_reference = true)
    {
        $this->setting = new \ilSetting('assessment');
        $this->type = 'assf';
        $local_dic = TestDIC::dic();
        $this->global_settings_repository = $local_dic['global_settings_repository'];
        $this->test_log_viewer = $local_dic['test_log_viewer'];

        parent::__construct($a_id, $a_call_by_reference);
    }

    public function getGlobalSettingsRepository(): TestGlobalSettingsRepository
    {
        return $this->global_settings_repository;
    }

    public function getTestLogViewer(): TestLogViewer
    {
        return $this->test_log_viewer;
    }

    public static function getSkillTriggerAnswerNumberBarrier(): int
    {
        $assSettings = new \ilSetting('assessment');

        return (int) $assSettings->get(
            self::SETTINGS_KEY_SKL_TRIG_NUM_ANSWERS_BARRIER,
            self::DEFAULT_SKL_TRIG_NUM_ANSWERS_BARRIER
        );
    }

    public function _enableAssessmentLogging(bool $a_enable): void
    {
        $setting = new \ilSetting('assessment');

        $setting->set('assessment_logging', (string) ((int) $a_enable));
    }

    public function _setLogLanguage(string $a_language): void
    {
        $setting = new \ilSetting('assessment');

        $setting->set('assessment_log_language', $a_language);
    }

    /**
     * Returns the forbidden questiontypes for ILIAS
     * @return int[]
     */
    public static function _getForbiddenQuestionTypes(): array
    {
        $setting = new \ilSetting('assessment');
        $types = $setting->get('forbidden_questiontypes', '');
        $result = [];

        if ($types === '') {
            $result = [];
        } else {
            $result = unserialize($types, ['allowed_classes' => false]);
        }

        return array_filter(array_map('intval', $result));
    }

    /**
     * Sets the forbidden questiontypes for ILIAS
     * @param int[] $typeIds An array containing the database ID's of the forbidden question types
     */
    public function _setForbiddenQuestionTypes(array $typeIds): void
    {
        $setting = new \ilSetting('assessment');

        $types = '';
        if ($typeIds !== []) {
            $types = serialize(array_map('intval', $typeIds));
        }

        $setting->set('forbidden_questiontypes', $types);
    }

    /**
     * Returns the fact wether manually scoreable
     * question types exist or not
     */
    public static function _mananuallyScoreableQuestionTypesExists(): bool
    {
        return count(self::_getManualScoring()) > 0;
    }

    /**
     * Retrieve the manual scoring settings
     * @return int[]
     */
    public static function _getManualScoring(): array
    {
        $setting = new \ilSetting('assessment');

        $types = $setting->get('assessment_manual_scoring', '');
        return array_filter(array_map('intval', explode(',', $types)));
    }

    /**
     * Retrieve the manual scoring settings as type strings
     * @return string[]
     */
    public static function _getManualScoringTypes(): array
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $setting = new \ilSetting('assessment');
        $typeIds = array_filter(array_map('intval', explode(',', $setting->get('assessment_manual_scoring', ''))));
        $manualScoringTypes = [];

        $result = $ilDB->query('SELECT question_type_id, type_tag FROM qpl_qst_type');
        while ($row = $ilDB->fetchAssoc($result)) {
            if (in_array((int) $row['question_type_id'], $typeIds, true)) {
                $manualScoringTypes[] = $row['type_tag'];
            }
        }
        return array_filter($manualScoringTypes);
    }

    /**
     * Set the manual scoring settings
     * @param int[] $type_ids An array containing the database ids of the question types which could be scored manually
     */
    public function _setManualScoring(array $type_ids): void
    {
        $setting = new \ilSetting('assessment');
        if ($type_ids === []) {
            $setting->delete('assessment_manual_scoring');
        } else {
            $setting->set('assessment_manual_scoring', implode(',', $type_ids));
        }
    }

    /**
     * @return int[]
     */
    public static function getScoringAdjustableQuestions(): array
    {
        $setting = new \ilSetting('assessment');

        $types = $setting->get('assessment_scoring_adjustment', '');
        return array_filter(array_map('intval', explode(',', $types)));
    }

    /**
     * @param int[] $type_ids
     * @return void
     */
    public static function setScoringAdjustableQuestions(array $type_ids): void
    {
        $setting = new \ilSetting('assessment');
        if ($type_ids === []) {
            $setting->delete('assessment_scoring_adjustment');
        } else {
            $setting->set('assessment_scoring_adjustment', implode(',', $type_ids));
        }
    }

    public static function getScoringAdjustmentEnabled(): bool
    {
        $setting = new \ilSetting('assessment');
        return (bool) $setting->get('assessment_adjustments_enabled', '0');
    }

    public static function setScoringAdjustmentEnabled(bool $active): void
    {
        $setting = new \ilSetting('assessment');
        $setting->set('assessment_adjustments_enabled', (string) ((int) $active));
    }

    /**
     * Returns the number of log entries for a given test id
     * @param int $test_obj_id Database id of the ILIAS test object
     * @return int The number of log entries for the test object
     */
    public function getNrOfLogEntries(int $test_obj_id): int
    {
        $result = $this->db->queryF(
            "SELECT COUNT(obj_fi) logcount FROM ass_log WHERE obj_fi = %s",
            ['integer'],
            [$test_obj_id]
        );
        if ($result->numRows()) {
            $row = $this->db->fetchAssoc($result);
            return (int) $row["logcount"];
        }

        return 0;
    }

    /**
     * Deletes the log entries for a given array of test object IDs
     * @param int[] $a_array An array containing the object IDs of the tests
     */
    public function deleteLogEntries(array $a_array): void
    {
        foreach ($a_array as $object_id) {
            $affectedRows = $this->db->manipulateF(
                "DELETE FROM ass_log WHERE obj_fi = %s",
                ['integer'],
                [$object_id]
            );
            self::_addLog($this->user->getId(), $object_id, $this->lng->txt("assessment_log_deleted"));
        }
    }

    /**
     * Returns the fact wether content editing with ilias page editor is enabled for questions or not
     */
    public static function isAdditionalQuestionContentEditingModePageObjectEnabled(): bool
    {
        global $DIC;
        $ilSetting = $DIC->settings();

        $isPageEditorEnabled = $ilSetting->get(
            'enable_tst_page_edit',
            (string) self::ADDITIONAL_QUESTION_CONTENT_EDITING_MODE_PAGE_OBJECT_DISABLED
        );

        return (bool) $isPageEditorEnabled;
    }

    public function getAssessmentProcessLockMode(): string
    {
        return $this->setting->get('ass_process_lock_mode', self::ASS_PROC_LOCK_MODE_NONE);
    }

    public function setAssessmentProcessLockMode(string $lockMode): void
    {
        $this->setting->set('ass_process_lock_mode', $lockMode);
    }

    /**
     * @return string[]
     */
    public static function getValidAssessmentProcessLockModes(): array
    {
        return [
            self::ASS_PROC_LOCK_MODE_NONE,
            self::ASS_PROC_LOCK_MODE_FILE,
            self::ASS_PROC_LOCK_MODE_DB
        ];
    }

    public function getSkillTriggeringNumAnswersBarrier(): string
    {
        return $this->setting->get(
            'ass_skl_trig_num_answ_barrier',
            self::DEFAULT_SKL_TRIG_NUM_ANSWERS_BARRIER
        );
    }

    public function setSkillTriggeringNumAnswersBarrier(int $skillTriggeringNumAnswersBarrier): void
    {
        $this->setting->set('ass_skl_trig_num_answ_barrier', (string) $skillTriggeringNumAnswersBarrier);
    }

    public function setExportEssayQuestionsWithHtml(bool $value): void
    {
        $this->setting->set('export_essay_qst_with_html', (string) ((int) $value));
    }

    public function getExportEssayQuestionsWithHtml(): bool
    {
        return (bool) $this->setting->get('export_essay_qst_with_html', '0');
    }

    /**
     * @param array<string, array{question_type_id: int, type_tag: string, plugin: int, plugin_name: string|null}> $allQuestionTypes
     * @return array<string, array{question_type_id: int, type_tag: string, plugin: int, plugin_name: string|null}>
     * @throws ilTestQuestionPoolInvalidArgumentException
     */
    public function fetchScoringAdjustableTypes(array $allQuestionTypes): array
    {
        $scoringAdjustableQuestionTypes = [];

        foreach ($allQuestionTypes as $type => $typeData) {
            $questionGui = assQuestionGUI::_getQuestionGUI($typeData['type_tag']);

            if ($this->questionSupportsScoringAdjustment($questionGui)) {
                $scoringAdjustableQuestionTypes[$type] = $typeData;
            }
        }

        return $scoringAdjustableQuestionTypes;
    }

    private function questionSupportsScoringAdjustment(assQuestionGUI $question_object): bool
    {
        return (
            $question_object instanceof ilGuiQuestionScoringAdjustable ||
            $question_object instanceof ilGuiAnswerScoringAdjustable
        ) && (
            $question_object->object instanceof ilObjQuestionScoringAdjustable ||
            $question_object->object instanceof ilObjAnswerScoringAdjustable
        );
    }
}
