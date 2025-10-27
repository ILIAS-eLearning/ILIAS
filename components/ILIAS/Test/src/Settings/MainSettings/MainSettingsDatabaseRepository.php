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
use ILIAS\Test\Settings\SettingsNotFoundException;

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
        return isset($this->settings_by_obj_fi[$obj_fi])
            ? $this->settings_instances[$this->settings_by_obj_fi[$obj_fi]]
            : $this->doSelect("WHERE obj_fi = {$this->db->quote($obj_fi, \ilDBConstants::T_INTEGER)}");
    }

    public function getFor(int $test_id): MainSettings
    {
        return isset($this->settings_by_test_fi[$test_id])
            ? $this->settings_instances[$this->settings_by_test_fi[$test_id]]
            : $this->doSelect("WHERE test_id = {$this->db->quote($test_id, \ilDBConstants::T_INTEGER)}");
    }

    public function getById(int $settings_id): MainSettings
    {
        if (isset($this->settings_instances[$settings_id])) {
            return $this->settings_instances[$settings_id];
        }

        $res = $this->db->queryF(
            "SELECT * FROM tst_test_settings WHERE id = %s",
            [\ilDBConstants::T_INTEGER],
            [$settings_id]
        );

        if ($this->db->numRows($res) === 0) {
            throw new SettingsNotFoundException("No main settings with id: {$settings_id}");
        }

        $settings = $this->factory->createMainSettingsFromDBRow($this->db->fetchAssoc($res));
        $this->settings_instances[$settings->getId()] = $settings;

        return $settings;
    }

    protected function doSelect(string $where_part): MainSettings
    {
        $query = 'SELECT ' . PHP_EOL
            . 'tst_set.id,' . PHP_EOL
            . 'tst_set.question_set_type,' . PHP_EOL
            . 'tst_set.anonymity,' . PHP_EOL
            . 'tst_set.intro_enabled,' . PHP_EOL
            . 'tst_set.hide_info_tab,' . PHP_EOL
            . 'tst_set.conditions_checkbox_enabled,' . PHP_EOL
            . 'tst_set.introduction,' . PHP_EOL
            . 'tst_set.introduction_page_id,' . PHP_EOL
            . 'tst_set.starting_time_enabled,' . PHP_EOL
            . 'tst_set.starting_time,' . PHP_EOL
            . 'tst_set.ending_time_enabled,' . PHP_EOL
            . 'tst_set.ending_time,' . PHP_EOL
            . 'tst_set.password_enabled,' . PHP_EOL
            . 'tst_set.password,' . PHP_EOL
            . 'tst_set.ip_range_from,' . PHP_EOL
            . 'tst_set.ip_range_to,' . PHP_EOL
            . 'tst_set.fixed_participants,' . PHP_EOL
            . 'tst_set.nr_of_tries,' . PHP_EOL
            . 'tst_set.block_after_passed,' . PHP_EOL
            . 'tst_set.pass_waiting,' . PHP_EOL
            . 'tst_set.enable_processing_time,' . PHP_EOL
            . 'tst_set.processing_time,' . PHP_EOL
            . 'tst_set.reset_processing_time,' . PHP_EOL
            . 'tst_set.kiosk,' . PHP_EOL
            . 'tst_set.examid_in_test_pass,' . PHP_EOL
            . 'tst_set.title_output,' . PHP_EOL
            . 'tst_set.autosave,' . PHP_EOL
            . 'tst_set.autosave_ival,' . PHP_EOL
            . 'tst_set.shuffle_questions,' . PHP_EOL
            . 'tst_set.answer_feedback_points,' . PHP_EOL
            . 'tst_set.answer_feedback,' . PHP_EOL
            . 'tst_set.specific_feedback,' . PHP_EOL
            . 'tst_set.instant_verification,' . PHP_EOL
            . 'tst_set.force_inst_fb,' . PHP_EOL
            . 'tst_set.inst_fb_answer_fixation,' . PHP_EOL
            . 'tst_set.follow_qst_answer_fixation,' . PHP_EOL
            . 'tst_set.use_previous_answers,' . PHP_EOL
            . 'tst_set.suspend_test_allowed,' . PHP_EOL
            . 'tst_set.sequence_settings,' . PHP_EOL
            . 'tst_set.usr_pass_overview_mode,' . PHP_EOL
            . 'tst_set.show_marker,' . PHP_EOL
            . 'tst_set.show_questionlist,' . PHP_EOL
            . 'tst_set.enable_examview,' . PHP_EOL
            . 'tst_set.showfinalstatement,' . PHP_EOL
            . 'tst_set.finalstatement,' . PHP_EOL
            . 'tst_set.concluding_remarks_page_id,' . PHP_EOL
            . 'tst_set.redirection_mode,' . PHP_EOL
            . 'tst_set.redirection_url,' . PHP_EOL
            . 'tst_set.skill_service,' . PHP_EOL
            . 'tst.test_id AS test_id,' . PHP_EOL
            . 'tst.obj_fi AS obj_fi' . PHP_EOL
            . 'FROM tst_test_settings AS tst_set' . PHP_EOL
            . 'INNER JOIN tst_tests AS tst ON tst.settings_id = tst_set.id' . PHP_EOL
            . $where_part;

        $res = $this->db->query($query);

        if ($this->db->numRows($res) === 0) {
            throw new SettingsNotFoundException("No main settings for: {$where_part}");
        }

        $row = $this->db->fetchAssoc($res);
        $settings = $this->factory->createMainSettingsFromDBRow($row);

        $this->settings_by_obj_fi[$row['obj_fi']] = $settings->getId();
        $this->settings_by_test_fi[$row['test_id']] = $settings->getId();
        $this->settings_instances[$settings->getId()] = $settings;

        return $settings;
    }

    public function store(MainSettings $settings, ?int $test_id = null): MainSettings
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

        if ($settings->getId() === 0) {
            $settings = $settings->withId($this->db->nextId('tst_test_settings'));
            $values['id'] = [\ilDBConstants::T_INTEGER, $settings->getId()];

            $this->db->insert('tst_test_settings', $values);
            $this->db->update(
                'tst_tests',
                ['settings_id' => [\ilDBConstants::T_INTEGER, $settings->getId()]],
                ['test_id' => [\ilDBConstants::T_INTEGER, $test_id]]
            );
        } else {
            $this->db->update(
                'tst_test_settings',
                $values,
                ['id' => [\ilDBConstants::T_INTEGER, $settings->getId()]]
            );
        }

        unset($this->settings_instances[$settings->getId()]);
        $this->settings_by_obj_fi = array_filter(
            $this->settings_by_obj_fi,
            static fn(int $value): bool => $value !== $settings->getId(),
        );
        $this->settings_by_test_fi = array_filter(
            $this->settings_by_test_fi,
            static fn(int $value): bool => $value !== $settings->getId(),
        );

        return $settings;
    }
}
