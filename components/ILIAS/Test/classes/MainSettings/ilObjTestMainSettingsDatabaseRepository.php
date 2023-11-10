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

use ILIAS\Test\MainSettingsRepository;

class ilObjTestMainSettingsDatabaseRepository implements MainSettingsRepository
{
    public const TABLE_NAME = 'tst_tests';
    public const STORAGE_DATE_FORMAT = 'YmdHis';

    protected ilDBInterface $db;

    public function __construct(ilDBInterface $db)
    {
        $this->db = $db;
    }

    public function getForObjFi(int $obj_fi): ilObjTestMainSettings
    {
        $where_part = 'WHERE obj_fi = ' . $this->db->quote($obj_fi, 'integer');
        return $this->doSelect($where_part);
    }

    public function getFor(int $test_id): ilObjTestMainSettings
    {
        $where_part = 'WHERE test_id = ' . $this->db->quote($test_id, 'integer');
        return $this->doSelect($where_part);
    }

    protected function doSelect(string $where_part): ilObjTestMainSettings
    {
        $query = 'SELECT ' . PHP_EOL
            . 'question_set_type,' . PHP_EOL
            . 'anonymity,' . PHP_EOL
            . 'test_id,' . PHP_EOL
            . 'intro_enabled,' . PHP_EOL
            . 'hide_info_tab,' . PHP_EOL
            . 'conditions_checkbox_enabled,' . PHP_EOL
            . 'introduction,' . PHP_EOL
            . 'introduction_page_id,' . PHP_EOL
            . 'starting_time_enabled,' . PHP_EOL
            . 'starting_time,' . PHP_EOL
            . 'ending_time_enabled,' . PHP_EOL
            . 'ending_time,' . PHP_EOL
            . 'password_enabled,' . PHP_EOL
            . 'password,' . PHP_EOL
            . 'fixed_participants,' . PHP_EOL
            . 'nr_of_tries,' . PHP_EOL
            . 'block_after_passed,' . PHP_EOL
            . 'pass_waiting,' . PHP_EOL
            . 'enable_processing_time,' . PHP_EOL
            . 'processing_time,' . PHP_EOL
            . 'reset_processing_time,' . PHP_EOL
            . 'kiosk,' . PHP_EOL
            . 'examid_in_test_pass,' . PHP_EOL
            . 'title_output,' . PHP_EOL
            . 'autosave,' . PHP_EOL
            . 'autosave_ival,' . PHP_EOL
            . 'shuffle_questions,' . PHP_EOL
            . 'offer_question_hints,' . PHP_EOL
            . 'answer_feedback_points,' . PHP_EOL
            . 'answer_feedback,' . PHP_EOL
            . 'specific_feedback,' . PHP_EOL
            . 'instant_verification,' . PHP_EOL
            . 'force_inst_fb,' . PHP_EOL
            . 'inst_fb_answer_fixation,' . PHP_EOL
            . 'follow_qst_answer_fixation,' . PHP_EOL
            . 'obligations_enabled,' . PHP_EOL
            . 'use_previous_answers,' . PHP_EOL
            . 'show_cancel,' . PHP_EOL
            . 'sequence_settings,' . PHP_EOL
            . 'usr_pass_overview_mode,' . PHP_EOL
            . 'show_marker,' . PHP_EOL
            . 'show_questionlist,' . PHP_EOL
            . 'enable_examview,' . PHP_EOL
            . 'showfinalstatement,' . PHP_EOL
            . 'finalstatement,' . PHP_EOL
            . 'concluding_remarks_page_id,' . PHP_EOL
            . 'redirection_mode,' . PHP_EOL
            . 'redirection_url,' . PHP_EOL
            . 'mailnotification,' . PHP_EOL
            . 'mailnottype,' . PHP_EOL
            . 'skill_service' . PHP_EOL
            . 'FROM ' . self::TABLE_NAME . PHP_EOL
            . $where_part;

        $res = $this->db->query($query);

        if ($this->db->numRows($res) == 0) {
            throw new \Exception('Mo main settings for: ' . $where_part);
        }

        $row = $this->db->fetchAssoc($res);

        $test_id = (int) $row['test_id'];

        $settings = new ilObjTestMainSettings(
            $test_id,
            new ilObjTestSettingsGeneral(
                $test_id,
                $row['question_set_type'],
                (bool) $row['anonymity']
            ),
            new ilObjTestSettingsIntroduction(
                $test_id,
                (bool) $row['intro_enabled'],
                $row['introduction'],
                $row['introduction_page_id'],
                (bool) $row['conditions_checkbox_enabled'],
            ),
            new ilObjTestSettingsAccess(
                $test_id,
                (bool) $row['starting_time_enabled'],
                $row['starting_time'] !== 0
                    ? DateTimeImmutable::createFromFormat('U', (string) $row['starting_time'])
                    : null,
                (bool) $row['ending_time_enabled'],
                $row['ending_time'] !== 0
                    ? DateTimeImmutable::createFromFormat('U', (string) $row['ending_time'])
                    : null,
                (bool) $row['password_enabled'],
                $row['password'],
                (bool) $row['fixed_participants'],
            ),
            new ilObjTestSettingsTestBehaviour(
                $test_id,
                $row['nr_of_tries'],
                (bool) $row['block_after_passed'],
                $row['pass_waiting'],
                (bool) $row['enable_processing_time'],
                $row['processing_time'],
                (bool) $row['reset_processing_time'],
                $row['kiosk'],
                (bool) $row['examid_in_test_pass']
            ),
            new ilObjTestSettingsQuestionBehaviour(
                $test_id,
                (int) $row['title_output'],
                (bool) $row['autosave'],
                $row['autosave_ival'],
                (bool) $row['shuffle_questions'],
                (bool) $row['offer_question_hints'],
                (bool) $row['answer_feedback_points'],
                (bool) $row['answer_feedback'],
                (bool) $row['specific_feedback'],
                (bool) $row['instant_verification'],
                (bool) $row['force_inst_fb'],
                (bool) $row['inst_fb_answer_fixation'],
                (bool) $row['follow_qst_answer_fixation'],
                (bool) $row['obligations_enabled']
            ),
            new ilObjTestSettingsParticipantFunctionality(
                $test_id,
                (bool) $row['use_previous_answers'],
                (bool) $row['show_cancel'],
                (bool) $row['sequence_settings'],
                $row['usr_pass_overview_mode'],
                (bool) $row['show_marker'],
                (bool) $row['show_questionlist']
            ),
            new ilObjTestSettingsFinishing(
                $test_id,
                (bool) $row['enable_examview'],
                (bool) $row['showfinalstatement'],
                $row['finalstatement'],
                $row['concluding_remarks_page_id'],
                $row['redirection_mode'],
                $row['redirection_url'],
                $row['mailnotification'],
                (bool) $row['mailnottype'],
            ),
            new ilObjTestSettingsAdditional(
                $test_id,
                (bool) $row['skill_service'],
                (bool) $row['hide_info_tab']
            )
        );

        return $settings;
    }

    public function store(ilObjTestMainSettings $settings): void
    {
        $values = array_merge(
            $settings->getGeneralSettings()->toStorage(),
            $settings->getIntroductionSettings()->toStorage(),
            $settings->getAccessSettings()->toStorage(),
            $settings->getTestBehaviourSettings()->toStorage(),
            $settings->getQuestionBehaviourSettings()->toStorage(),
            $settings->getParticipantFunctionalitySettings()->toStorage(),
            $settings->getFinishingSettings()->toStorage(),
            $settings->getAdditionalSettings()->toStorage()
        );

        $this->db->update(
            self::TABLE_NAME,
            $values,
            ['test_id' => ['integer', $settings->getTestId()]]
        );
    }
}
