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

namespace ILIAS\Test\Settings\MainSettings;

class MainSettingsDatabaseRepository implements MainSettingsRepository
{
    /** @var array<int, int> Object ID -> Settings ID */
    private array $settings_by_obj_fi = [];

    /** @var array<int, int> Test ID -> Settings ID */
    private array $settings_by_test_fi = [];

    /** @var array<int, MainSettings> Settings ID -> Settings DTO */
    private array $settings_instances = [];

    public function __construct(protected \ilDBInterface $db)
    {
    }

    public function getForObjFi(int $obj_fi): MainSettings
    {
        if (isset($this->settings_by_obj_fi[$obj_fi])) {
            return $this->settings_instances[$this->settings_by_obj_fi[$obj_fi]];
        }

        $where_part = 'WHERE obj_fi = ' . $this->db->quote($obj_fi, \ilDBConstants::T_INTEGER);
        return $this->doSelect($where_part);
    }

    public function getFor(int $test_id): MainSettings
    {
        if (isset($this->settings_by_test_fi[$test_id])) {
            return $this->settings_instances[$this->settings_by_test_fi[$test_id]];
        }

        $where_part = 'WHERE test_id = ' . $this->db->quote($test_id, \ilDBConstants::T_INTEGER);
        return $this->doSelect($where_part);
    }

    protected function doSelect(string $where_part): MainSettings
    {
        $query = 'SELECT ' . PHP_EOL
            . 'id,' . PHP_EOL
            . 'question_set_type,' . PHP_EOL
            . 'anonymity,' . PHP_EOL
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
            . 'ip_range_from,' . PHP_EOL
            . 'ip_range_to,' . PHP_EOL
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
            . 'answer_feedback_points,' . PHP_EOL
            . 'answer_feedback,' . PHP_EOL
            . 'specific_feedback,' . PHP_EOL
            . 'instant_verification,' . PHP_EOL
            . 'force_inst_fb,' . PHP_EOL
            . 'inst_fb_answer_fixation,' . PHP_EOL
            . 'follow_qst_answer_fixation,' . PHP_EOL
            . 'use_previous_answers,' . PHP_EOL
            . 'suspend_test_allowed,' . PHP_EOL
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
            . 'skill_service,' . PHP_EOL
            . 'tst_tests.test_id AS test_id,' . PHP_EOL
            . 'tst_tests.obj_fi AS obj_fi' . PHP_EOL
            . 'FROM tst_test_settings' . PHP_EOL
            . 'INNER JOIN tst_tests ON tst_tests.settings_id = tst_test_settings.id' . PHP_EOL
            . $where_part;

        $res = $this->db->query($query);

        if ($this->db->numRows($res) == 0) {
            throw new \Exception('Mo main settings for: ' . $where_part);
        }

        $row = $this->db->fetchAssoc($res);

        $settings = new MainSettings(
            $row['id'],
            new SettingsGeneral(
                $row['question_set_type'],
                (bool) $row['anonymity']
            ),
            new SettingsIntroduction(
                (bool) $row['intro_enabled'],
                $row['introduction'],
                $row['introduction_page_id'],
                (bool) $row['conditions_checkbox_enabled'],
            ),
            new SettingsAccess(
                (bool) $row['starting_time_enabled'],
                $row['starting_time'] !== 0
                    ? \DateTimeImmutable::createFromFormat('U', (string) $row['starting_time'])
                    : null,
                (bool) $row['ending_time_enabled'],
                $row['ending_time'] !== 0
                    ? \DateTimeImmutable::createFromFormat('U', (string) $row['ending_time'])
                    : null,
                (bool) $row['password_enabled'],
                $row['password'],
                $row['ip_range_from'],
                $row['ip_range_to'],
                (bool) $row['fixed_participants'],
            ),
            new SettingsTestBehaviour(
                $row['nr_of_tries'],
                (bool) $row['block_after_passed'],
                $row['pass_waiting'],
                (bool) $row['enable_processing_time'],
                $row['processing_time'],
                (bool) $row['reset_processing_time'],
                $row['kiosk'],
                (bool) $row['examid_in_test_pass']
            ),
            new SettingsQuestionBehaviour(
                (int) $row['title_output'],
                (bool) $row['autosave'],
                $row['autosave_ival'],
                (bool) $row['shuffle_questions'],
                (bool) $row['answer_feedback_points'],
                (bool) $row['answer_feedback'],
                (bool) $row['specific_feedback'],
                (bool) $row['instant_verification'],
                (bool) $row['force_inst_fb'],
                (bool) $row['inst_fb_answer_fixation'],
                (bool) $row['follow_qst_answer_fixation']
            ),
            new SettingsParticipantFunctionality(
                (bool) $row['use_previous_answers'],
                (bool) $row['suspend_test_allowed'],
                (bool) $row['sequence_settings'],
                $row['usr_pass_overview_mode'],
                (bool) $row['show_marker'],
                (bool) $row['show_questionlist']
            ),
            new SettingsFinishing(
                (bool) $row['enable_examview'],
                (bool) $row['showfinalstatement'],
                $row['finalstatement'],
                $row['concluding_remarks_page_id'],
                RedirectionModes::tryFrom($row['redirection_mode']) ?? RedirectionModes::NONE,
                $row['redirection_url']
            ),
            new SettingsAdditional(
                (bool) $row['skill_service'],
                (bool) $row['hide_info_tab']
            )
        );

        $this->settings_by_obj_fi[$row['obj_fi']] = $settings->getId();
        $this->settings_by_test_fi[$row['test_id']] = $settings->getId();
        $this->settings_instances[$settings->getId()] = $settings;

        return $settings;
    }

    public function createFor(int $test_id): void {
        $settings_id = $this->db->nextId('tst_test_settings');
        $this->db->insert(
            'tst_test_settings',
            ['id' => [\ilDBConstants::T_INTEGER, $settings_id]],
        );

        $this->db->update(
            'tst_tests',
            ['settings_id' => [\ilDBConstants::T_INTEGER, $settings_id]],
            ['test_id' => [\ilDBConstants::T_INTEGER, $test_id]]
        );
    }

    public function store(MainSettings $settings): void
    {
        if($settings->getId() === 0) {
            throw new \Exception('Cannot store settings without ID');
        }

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
            'tst_test_settings',
            $values,
            ['id' => [\ilDBConstants::T_INTEGER, $settings->getId()]]
        );

        unset($this->settings_instances[$settings->getId()]);
        $this->settings_by_obj_fi = array_filter(
            $this->settings_by_obj_fi,
            fn($value) => $value !== $settings->getId()
        );
        $this->settings_by_test_fi = array_filter(
            $this->settings_by_test_fi,
            fn($value) => $value !== $settings->getId()
        );
    }

    public function cloneFor(int $test_id, MainSettings $settings): MainSettings {
        $settings_id = $this->db->nextId('tst_test_settings');
        $new_settings = $settings->withId($settings_id);

        $this->store($new_settings);
        $this->db->update(
            'tst_tests',
            ['settings_id' => [\ilDBConstants::T_INTEGER, $settings_id]],
            ['test_id' => [\ilDBConstants::T_INTEGER, $test_id]]
        );

        return $new_settings;
    }

}
