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

use ILIAS\Test\Settings\SettingsFactory;

class MainSettingsDatabaseRepository implements MainSettingsRepository
{
    /** @var array<int, int> Object ID -> Settings ID */
    private array $settings_by_obj_fi = [];

    /** @var array<int, int> Test ID -> Settings ID */
    private array $settings_by_test_fi = [];

    /** @var array<int, MainSettings> Settings ID -> Settings DTO */
    private array $settings_instances = [];
    public function __construct(
        protected \ilDBInterface $db,
        protected SettingsFactory $factory
    ) {
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
            . 'st.id,' . PHP_EOL
            . 'st.question_set_type,' . PHP_EOL
            . 'st.anonymity,' . PHP_EOL
            . 'st.intro_enabled,' . PHP_EOL
            . 'st.hide_info_tab,' . PHP_EOL
            . 'st.conditions_checkbox_enabled,' . PHP_EOL
            . 'st.introduction,' . PHP_EOL
            . 'st.introduction_page_id,' . PHP_EOL
            . 'st.starting_time_enabled,' . PHP_EOL
            . 'st.starting_time,' . PHP_EOL
            . 'st.ending_time_enabled,' . PHP_EOL
            . 'st.ending_time,' . PHP_EOL
            . 'st.password_enabled,' . PHP_EOL
            . 'st.password,' . PHP_EOL
            . 'st.ip_range_from,' . PHP_EOL
            . 'st.ip_range_to,' . PHP_EOL
            . 'st.fixed_participants,' . PHP_EOL
            . 'st.nr_of_tries,' . PHP_EOL
            . 'st.block_after_passed,' . PHP_EOL
            . 'st.pass_waiting,' . PHP_EOL
            . 'st.enable_processing_time,' . PHP_EOL
            . 'st.processing_time,' . PHP_EOL
            . 'st.reset_processing_time,' . PHP_EOL
            . 'st.kiosk,' . PHP_EOL
            . 'st.examid_in_test_pass,' . PHP_EOL
            . 'st.title_output,' . PHP_EOL
            . 'st.autosave,' . PHP_EOL
            . 'st.autosave_ival,' . PHP_EOL
            . 'st.shuffle_questions,' . PHP_EOL
            . 'st.answer_feedback_points,' . PHP_EOL
            . 'st.answer_feedback,' . PHP_EOL
            . 'st.specific_feedback,' . PHP_EOL
            . 'st.instant_verification,' . PHP_EOL
            . 'st.force_inst_fb,' . PHP_EOL
            . 'st.inst_fb_answer_fixation,' . PHP_EOL
            . 'st.follow_qst_answer_fixation,' . PHP_EOL
            . 'st.use_previous_answers,' . PHP_EOL
            . 'st.suspend_test_allowed,' . PHP_EOL
            . 'st.sequence_settings,' . PHP_EOL
            . 'st.usr_pass_overview_mode,' . PHP_EOL
            . 'st.show_marker,' . PHP_EOL
            . 'st.show_questionlist,' . PHP_EOL
            . 'st.enable_examview,' . PHP_EOL
            . 'st.showfinalstatement,' . PHP_EOL
            . 'st.finalstatement,' . PHP_EOL
            . 'st.concluding_remarks_page_id,' . PHP_EOL
            . 'st.redirection_mode,' . PHP_EOL
            . 'st.redirection_url,' . PHP_EOL
            . 'st.skill_service,' . PHP_EOL
            . 'tst.test_id AS test_id,' . PHP_EOL
            . 'tst.obj_fi AS obj_fi' . PHP_EOL
            . 'FROM tst_test_settings AS st' . PHP_EOL
            . 'INNER JOIN tst_tests AS tst ON tst.settings_id = st.id' . PHP_EOL
            . $where_part;

        $res = $this->db->query($query);

        if ($this->db->numRows($res) == 0) {
            throw new \Exception('Mo main settings for: ' . $where_part);
        }

        $row = $this->db->fetchAssoc($res);
        $settings = $this->factory->createMainSettings($row);

        $this->settings_by_obj_fi[$row['obj_fi']] = $settings->getId();
        $this->settings_by_test_fi[$row['test_id']] = $settings->getId();
        $this->settings_instances[$settings->getId()] = $settings;

        return $settings;
    }

    public function createFor(int $test_id): int
    {
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

        return $settings_id;
    }

    public function store(MainSettings $settings): void
    {
        if ($settings->getId() === 0) {
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
}
