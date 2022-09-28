<?php

declare(strict_types=1);

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

class ilStudyProgrammeSettingsDBRepository implements ilStudyProgrammeSettingsRepository
{
    private const TABLE = 'prg_settings';

    private const FIELD_OBJ_ID = 'obj_id';
    private const FIELD_SUBTYPE_ID = 'subtype_id';
    private const FIELD_STATUS = 'status';
    private const FIELD_LP_MODE = 'lp_mode';
    private const FIELD_POINTS = 'points';
    private const FIELD_LAST_CHANGED = 'last_change';
    private const FIELD_DEADLINE_PERIOD = 'deadline_period';
    private const FIELD_DEADLINE_DATE = 'deadline_date';
    private const FIELD_VALIDITY_QUALIFICATION_DATE = 'vq_date';
    private const FIELD_VALIDITY_QUALIFICATION_PERIOD = 'vq_period';
    private const FIELD_VQ_RESTART_PERIOD = 'vq_restart_period';
    private const FIELD_RM_NOT_RESTARTED_BY_USER_DAY = 'rm_nr_by_usr_days';
    private const FIELD_PROC_ENDS_NOT_SUCCESSFUL = 'proc_end_no_success';
    private const FIELD_SEND_RE_ASSIGNED_MAIL = "send_re_assigned_mail";
    private const FIELD_SEND_INFO_TO_RE_ASSIGN_MAIL = "send_info_to_re_assign_mail";
    private const FIELD_SEND_RISKY_TO_FAIL_MAIL = "send_risky_to_fail_mail";

    protected static array $cache = [];
    protected ilDBInterface $db;

    public function __construct(ilDBInterface $db)
    {
        $this->db = $db;
    }

    /**
     * @inheritdoc
     * @throws ilException
     */
    public function createFor(int $obj_id): ilStudyProgrammeSettings
    {
        $type_settings = new ilStudyProgrammeTypeSettings(
            ilStudyProgrammeSettings::DEFAULT_SUBTYPE
        );
        $assessment_settings = new ilStudyProgrammeAssessmentSettings(
            ilStudyProgrammeSettings::DEFAULT_POINTS,
            ilStudyProgrammeSettings::STATUS_DRAFT
        );
        $deadline_settings = new ilStudyProgrammeDeadlineSettings(null, null);
        $validity_of_achieved_qualification_settings =
            new ilStudyProgrammeValidityOfAchievedQualificationSettings(null, null, null)
        ;
        $automail = new ilStudyProgrammeAutoMailSettings(false, null, null);

        $prg = new ilStudyProgrammeSettings(
            $obj_id,
            $type_settings,
            $assessment_settings,
            $deadline_settings,
            $validity_of_achieved_qualification_settings,
            $automail
        );

        $this->insertDB(
            $obj_id,
            ilStudyProgrammeSettings::DEFAULT_SUBTYPE,
            ilStudyProgrammeSettings::STATUS_DRAFT,
            ilStudyProgrammeSettings::MODE_UNDEFINED,
            ilStudyProgrammeSettings::DEFAULT_POINTS,
            (new DateTime())->format(ilStudyProgrammeSettings::DATE_TIME_FORMAT),
            0,
            ilStudyProgrammeSettings::NO_VALIDITY_OF_QUALIFICATION_PERIOD,
            ilStudyProgrammeSettings::NO_RESTART
        );

        $prg = $prg->setLPMode(ilStudyProgrammeSettings::MODE_UNDEFINED);
        self::$cache[$obj_id] = $prg;
        return $prg;
    }

    /**
     * @inheritdoc
     * @throws ilException
     */
    public function get(int $obj_id): ilStudyProgrammeSettings
    {
        if (!array_key_exists($obj_id, self::$cache)) {
            self::$cache[$obj_id] = $this->loadDB($obj_id);
        }
        return self::$cache[$obj_id];
    }

    /**
     * @inheritdoc
     */
    public function update(ilStudyProgrammeSettings $settings): void
    {
        $deadline_period = $settings->getDeadlineSettings()->getDeadlinePeriod();
        if (is_null($deadline_period)) {
            $deadline_period = 0;
        }

        $deadline_date = $settings->getDeadlineSettings()->getDeadlineDate();
        if (!is_null($deadline_date)) {
            $deadline_date = $deadline_date->format(ilStudyProgrammeSettings::DATE_TIME_FORMAT);
        }

        $vq_date = $settings->getValidityOfQualificationSettings()->getQualificationDate();
        if (!is_null($vq_date)) {
            $vq_date = $vq_date->format(ilStudyProgrammeSettings::DATE_TIME_FORMAT);
        }

        $qp = $settings->getValidityOfQualificationSettings()->getQualificationPeriod();
        if (is_null($qp)) {
            $qp = 0;
        }

        $rp = $settings->getValidityOfQualificationSettings()->getRestartPeriod();
        if (is_null($rp)) {
            $rp = 0;
        }

        $this->updateDB(
            $settings->getObjId(),
            $settings->getTypeSettings()->getTypeId(),
            $settings->getAssessmentSettings()->getStatus(),
            $settings->getLPMode(),
            $settings->getAssessmentSettings()->getPoints(),
            $settings->getLastChange()->format(ilStudyProgrammeSettings::DATE_TIME_FORMAT),
            $deadline_period,
            $qp,
            $rp,
            $deadline_date,
            $vq_date,
            $settings->getAutoMailSettings()->getReminderNotRestartedByUserDays(),
            $settings->getAutoMailSettings()->getProcessingEndsNotSuccessfulDays(),
            $settings->getAutoMailSettings()->getSendReAssignedMail(),
        );
        self::$cache[$settings->getObjId()] = $settings;
    }

    /**
     * @inheritdoc
     */
    public function delete(ilStudyProgrammeSettings $settings): void
    {
        unset(self::$cache[$settings->getObjId()]);
        $this->deleteDB($settings->getObjId());
    }

    /**
     * @inheritdoc
     * @throws ilException
     */
    public function loadByType(int $type_id): array
    {
        $q = 'SELECT ' . self::FIELD_SUBTYPE_ID
            . '	,' . self::FIELD_STATUS
            . '	,' . self::FIELD_POINTS
            . '	,' . self::FIELD_LP_MODE
            . '	,' . self::FIELD_LAST_CHANGED
            . '	,' . self::FIELD_OBJ_ID
            . '	,' . self::FIELD_DEADLINE_PERIOD
            . '	,' . self::FIELD_DEADLINE_DATE
            . '	,' . self::FIELD_VALIDITY_QUALIFICATION_PERIOD
            . '	,' . self::FIELD_VALIDITY_QUALIFICATION_DATE
            . '	,' . self::FIELD_VQ_RESTART_PERIOD
            . ', ' . self::FIELD_RM_NOT_RESTARTED_BY_USER_DAY
            . ', ' . self::FIELD_PROC_ENDS_NOT_SUCCESSFUL
            . ', ' . self::FIELD_SEND_RE_ASSIGNED_MAIL
            . ', ' . self::FIELD_SEND_INFO_TO_RE_ASSIGN_MAIL
            . ', ' . self::FIELD_SEND_RISKY_TO_FAIL_MAIL
            . '	FROM ' . self::TABLE
            . '	WHERE ' . self::FIELD_SUBTYPE_ID . ' = ' . $this->db->quote($type_id, 'integer');
        $res = $this->db->query($q);
        $return = [];
        while ($rec = $this->db->fetchAssoc($res)) {
            $return[] = $this->createByRow($rec);
        }
        return $return;
    }


    public function loadIdsByType(int $type_id): array
    {
        return [];
    }

    protected function insertDB(
        int $obj_id,
        int $subtype_id,
        int $status,
        int $lp_mode,
        int $points,
        string $last_change,
        int $deadline_period,
        int $vq_period,
        int $vq_restart_period,
        string $deadline_date = null,
        string $vq_date = null,
        int $rm_nr_by_usr_days = null,
        int $proc_end_no_success = null,
        bool $send_re_assigned_mail = false,
        bool $send_info_to_re_assign_mail = false,
        bool $send_risky_to_fail_mail = false
    ): void {
        $this->db->insert(
            self::TABLE,
            [
                self::FIELD_OBJ_ID => ['integer', $obj_id],
                self::FIELD_SUBTYPE_ID => ['integer', $subtype_id],
                self::FIELD_STATUS => ['integer', $status],
                self::FIELD_POINTS => ['integer', $points],
                self::FIELD_LP_MODE => ['integer', $lp_mode],
                self::FIELD_LAST_CHANGED => ['timestamp', $last_change],
                self::FIELD_DEADLINE_PERIOD => ['integer', $deadline_period],
                self::FIELD_DEADLINE_DATE => ['timestamp', $deadline_date],
                self::FIELD_VALIDITY_QUALIFICATION_DATE => ['timestamp', $vq_date],
                self::FIELD_VALIDITY_QUALIFICATION_PERIOD => ['integer', $vq_period],
                self::FIELD_VQ_RESTART_PERIOD => ['integer', $vq_restart_period],
                self::FIELD_RM_NOT_RESTARTED_BY_USER_DAY => ['integer', $rm_nr_by_usr_days],
                self::FIELD_PROC_ENDS_NOT_SUCCESSFUL => ['integer', $proc_end_no_success],
                self::FIELD_SEND_RE_ASSIGNED_MAIL => ['integer', $send_re_assigned_mail],
                self::FIELD_SEND_INFO_TO_RE_ASSIGN_MAIL => ['integer', $send_info_to_re_assign_mail],
                self::FIELD_SEND_RISKY_TO_FAIL_MAIL => ['integer', $send_risky_to_fail_mail]
            ]
        );
    }

    /**
     * @throws ilException
     * @thorws LogicException
     */
    protected function loadDB(int $obj_id): ilStudyProgrammeSettings
    {
        $rec = $this->db->fetchAssoc(
            $this->db->query(
                'SELECT ' . self::FIELD_SUBTYPE_ID
                . ', ' . self::FIELD_STATUS
                . ', ' . self::FIELD_POINTS
                . ', ' . self::FIELD_LP_MODE
                . ', ' . self::FIELD_LAST_CHANGED
                . ', ' . self::FIELD_OBJ_ID
                . ', ' . self::FIELD_DEADLINE_PERIOD
                . ', ' . self::FIELD_DEADLINE_DATE
                . ', ' . self::FIELD_VALIDITY_QUALIFICATION_PERIOD
                . ', ' . self::FIELD_VALIDITY_QUALIFICATION_DATE
                . ', ' . self::FIELD_VQ_RESTART_PERIOD
                . ', ' . self::FIELD_RM_NOT_RESTARTED_BY_USER_DAY
                . ', ' . self::FIELD_PROC_ENDS_NOT_SUCCESSFUL
                . ', ' . self::FIELD_SEND_RE_ASSIGNED_MAIL
                . ', ' . self::FIELD_SEND_INFO_TO_RE_ASSIGN_MAIL
                . ', ' . self::FIELD_SEND_RISKY_TO_FAIL_MAIL
                . '	FROM ' . self::TABLE
                . '	WHERE ' . self::FIELD_OBJ_ID . ' = ' . $this->db->quote($obj_id, 'integer')
            )
        );
        if (!$rec) {
            throw new LogicException('invalid obj_id to load: ' . $obj_id);
        }
        return $this->createByRow($rec);
    }

    /**
     * @throws ilException
     */
    protected function createByRow(array $row): ilStudyProgrammeSettings
    {
        $type_settings = new ilStudyProgrammeTypeSettings(
            ilStudyProgrammeSettings::DEFAULT_SUBTYPE
        );
        $assessment_settings = new ilStudyProgrammeAssessmentSettings(
            ilStudyProgrammeSettings::DEFAULT_POINTS,
            ilStudyProgrammeSettings::STATUS_DRAFT
        );
        $deadline_settings = new ilStudyProgrammeDeadlineSettings(null, null);
        $validity_of_achieved_qualification_settings =
            new ilStudyProgrammeValidityOfAchievedQualificationSettings(null, null, null)
        ;
        $automail = new ilStudyProgrammeAutoMailSettings(false, null, null);

        $prg = new ilStudyProgrammeSettings(
            (int) $row[self::FIELD_OBJ_ID],
            $type_settings,
            $assessment_settings,
            $deadline_settings,
            $validity_of_achieved_qualification_settings,
            $automail
        );

        $return = $prg
            ->setLPMode((int) $row[self::FIELD_LP_MODE])
            ->setLastChange(DateTime::createFromFormat(
                ilStudyProgrammeSettings::DATE_TIME_FORMAT,
                $row[self::FIELD_LAST_CHANGED]
            ))
        ;

        $type = $return->getTypeSettings();
        $type = $type->withTypeId((int) $row['subtype_id']);
        $return = $return->withTypeSettings($type);

        $points = $return->getAssessmentSettings();
        $points = $points->withPoints((int) $row['points'])->withStatus((int) $row['status']);
        $return = $return->withAssessmentSettings($points);

        $deadline = $return->getDeadlineSettings();
        if ($row[self::FIELD_DEADLINE_DATE] !== null) {
            $deadline = $deadline->withDeadlineDate(DateTimeImmutable::createFromFormat(
                ilStudyProgrammeSettings::DATE_TIME_FORMAT,
                $row[self::FIELD_DEADLINE_DATE]
            ))
            ;
        } else {
            $deadline_period = (int) $row[self::FIELD_DEADLINE_PERIOD];
            if ($deadline_period === ilStudyProgrammeSettings::NO_DEADLINE) {
                $deadline_period = null;
            }
            $deadline = $deadline->withDeadlinePeriod($deadline_period);
        }
        $return = $return->withDeadlineSettings($deadline);

        $vqs = $return->getValidityOfQualificationSettings();
        if ($row[self::FIELD_VALIDITY_QUALIFICATION_DATE] !== null) {
            $vqs = $vqs->withQualificationDate(
                DateTimeImmutable::createFromFormat(
                    ilStudyProgrammeSettings::DATE_TIME_FORMAT,
                    $row[self::FIELD_VALIDITY_QUALIFICATION_DATE]
                )
            );
        } else {
            $qualification_period = (int) $row[self::FIELD_VALIDITY_QUALIFICATION_PERIOD];
            if ($qualification_period === ilStudyProgrammeSettings::NO_VALIDITY_OF_QUALIFICATION_PERIOD) {
                $qualification_period = null;
            }
            $vqs = $vqs->withQualificationPeriod($qualification_period);
        }
        $restart_period = (int) $row[self::FIELD_VQ_RESTART_PERIOD];
        if ($restart_period === ilStudyProgrammeSettings::NO_RESTART) {
            $restart_period = null;
        }
        $vqs = $vqs->withRestartPeriod($restart_period);
        $return = $return->withValidityOfQualificationSettings($vqs);

        $rm_nr_by_usr_days = $row[self::FIELD_RM_NOT_RESTARTED_BY_USER_DAY];
        if (!is_null($rm_nr_by_usr_days)) {
            $rm_nr_by_usr_days = (int) $rm_nr_by_usr_days;
        }
        $proc_end_no_success = $row[self::FIELD_PROC_ENDS_NOT_SUCCESSFUL];
        if (!is_null($proc_end_no_success)) {
            $proc_end_no_success = (int) $proc_end_no_success;
        }

        return $return->withAutoMailSettings(
            new ilStudyProgrammeAutoMailSettings(
                (bool) $row[self::FIELD_SEND_RE_ASSIGNED_MAIL],
                $rm_nr_by_usr_days,
                $proc_end_no_success
            )
        );
    }

    /**
     * @throws LogicException
     */
    protected function deleteDB(int $obj_id): void
    {
        if (!$this->checkExists($obj_id)) {
            throw new LogicException('invaid obj_id to delete: ' . $obj_id);
        }
        $this->db->manipulate(
            'DELETE FROM ' . self::TABLE
            . ' WHERE ' . self::FIELD_OBJ_ID . ' = ' . $this->db->quote($obj_id, 'integer')
        );
    }

    /**
     * @pthrows LogicException
     */
    protected function updateDB(
        int $obj_id,
        int $subtype_id,
        int $status,
        int $lp_mode,
        int $points,
        string $last_change,
        int $deadline_period,
        int $vq_period,
        int $vq_restart_period,
        string $deadline_date = null,
        string $vq_date = null,
        int $rm_nr_by_usr_days = null,
        int $proc_end_no_success = null,
        bool $send_re_assigned_mail = false,
        bool $send_info_to_re_assign_mail = false,
        bool $send_risky_to_fail_mail = false
    ): void {
        if (!$this->checkExists($obj_id)) {
            throw new LogicException('invalid obj_id to update: ' . $obj_id);
        }
        $where = [
            self::FIELD_OBJ_ID => [
                'integer',
                $obj_id
            ]
        ];

        $values = [
            self::FIELD_SUBTYPE_ID => [
                'integer',
                $subtype_id
            ],
            self::FIELD_STATUS => [
                'integer',
                $status
            ],
            self::FIELD_LP_MODE => [
                'integer',
                $lp_mode
            ],
            self::FIELD_POINTS => [
                'integer',
                $points
            ],
            self::FIELD_LAST_CHANGED => [
                'timestamp',
                $last_change
            ],
            self::FIELD_DEADLINE_PERIOD => [
                'integer',
                $deadline_period
            ],
            self::FIELD_DEADLINE_DATE => [
                'timestamp',
                $deadline_date
            ],
            self::FIELD_VALIDITY_QUALIFICATION_PERIOD => [
                'integer',
                $vq_period
            ],
            self::FIELD_VALIDITY_QUALIFICATION_DATE => [
                'timestamp',
                $vq_date
            ],
            self::FIELD_VQ_RESTART_PERIOD => [
                'integer',
                $vq_restart_period
            ],
            self::FIELD_RM_NOT_RESTARTED_BY_USER_DAY => [
                'integer',
                $rm_nr_by_usr_days
            ],
            self::FIELD_PROC_ENDS_NOT_SUCCESSFUL => [
                'integer',
                $proc_end_no_success
            ],
            self::FIELD_SEND_RE_ASSIGNED_MAIL => [
                'integer',
                $send_re_assigned_mail
            ],
            self::FIELD_SEND_INFO_TO_RE_ASSIGN_MAIL => [
                'integer',
                $send_info_to_re_assign_mail
            ],
            self::FIELD_SEND_RISKY_TO_FAIL_MAIL => [
                'integer',
                $send_risky_to_fail_mail
            ]
        ];

        $this->db->update(self::TABLE, $values, $where);
    }

    protected function checkExists(int $obj_id): bool
    {
        $rec = $this->db->fetchAssoc(
            $this->db->query(
                'SELECT ' . self::FIELD_OBJ_ID
                . '	FROM ' . self::TABLE
                . '	WHERE ' . self::FIELD_OBJ_ID . ' = ' . $this->db->quote($obj_id, 'integer')
            )
        );
        if ($rec) {
            return true;
        }
        return false;
    }

    public static function clearCache(): void
    {
        self::$cache = [];
    }

    /**
     * Programme must be active
     * and have a setting to send mails if the user is at risk to fail
     * completing the progress due to a deadline.
     * @return array <int id, int days_offset>
     */
    public function getProgrammeIdsWithRiskyToFailSettings(): array
    {
        $query = 'SELECT '
            . self::FIELD_OBJ_ID . ', '
            . self::FIELD_PROC_ENDS_NOT_SUCCESSFUL
            . ' FROM ' . self::TABLE . PHP_EOL
            . ' WHERE ' . self::FIELD_STATUS . ' = ' . ilStudyProgrammeSettings::STATUS_ACTIVE
            . ' AND ' . self::FIELD_PROC_ENDS_NOT_SUCCESSFUL . ' IS NOT NULL';

        $return = [];
        $res = $this->db->query($query);
        while ($rec = $this->db->fetchAssoc($res)) {
            $return[$rec[self::FIELD_OBJ_ID]] = $rec[self::FIELD_PROC_ENDS_NOT_SUCCESSFUL];
        }
        return $return;
    }

    /**
     * Programme must be active
     * and have a setting to send mails for qualifications about to expire
     * @return array <int id, int days_offset>
     */
    public function getProgrammeIdsWithMailsForExpiringValidity(): array
    {
        $query = 'SELECT '
            . self::FIELD_OBJ_ID . ', '
            . self::FIELD_RM_NOT_RESTARTED_BY_USER_DAY
            . ' FROM ' . self::TABLE . PHP_EOL
            . ' WHERE ' . self::FIELD_STATUS . ' = ' . ilStudyProgrammeSettings::STATUS_ACTIVE
            . ' AND ' . self::FIELD_RM_NOT_RESTARTED_BY_USER_DAY . ' IS NOT NULL';

        $return = [];
        $res = $this->db->query($query);
        while ($rec = $this->db->fetchAssoc($res)) {
            $return[$rec[self::FIELD_OBJ_ID]] = $rec[self::FIELD_RM_NOT_RESTARTED_BY_USER_DAY];
        }
        return $return;
    }

    /**
     * Programme must be active
     * and have a setting to reassign users when validity expires
     * @return array <int id, int days_offset>
     */
    public function getProgrammeIdsWithReassignmentForExpiringValidity(): array
    {
        $query = 'SELECT '
            . self::FIELD_OBJ_ID . ', '
            . self::FIELD_VQ_RESTART_PERIOD
            . ' FROM ' . self::TABLE . PHP_EOL
            . ' WHERE ' . self::FIELD_STATUS . ' = ' . ilStudyProgrammeSettings::STATUS_ACTIVE
            . ' AND ' . self::FIELD_VQ_RESTART_PERIOD . ' > 0';

        $return = [];
        $res = $this->db->query($query);
        while ($rec = $this->db->fetchAssoc($res)) {
            $return[$rec[self::FIELD_OBJ_ID]] = $rec[self::FIELD_VQ_RESTART_PERIOD];
        }
        return $return;
    }
}
