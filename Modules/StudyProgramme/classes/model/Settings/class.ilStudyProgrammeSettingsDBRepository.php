<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */
/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */

declare(strict_types = 1);

class ilStudyProgrammeSettingsDBRepository implements ilStudyProgrammeSettingsRepository
{
    /**
     * @var array
     */
    protected static $cache = [];

    /**
     * @var ilDBInterface
     */
    protected $db;

    /**
     * @var ilOrgUnitObjectTypePositionSetting
     */
    protected $tps;

    const TABLE = 'prg_settings';

    const FIELD_OBJ_ID = 'obj_id';
    const FIELD_SUBTYPE_ID = 'subtype_id';
    const FIELD_STATUS = 'status';
    const FIELD_LP_MODE = 'lp_mode';
    const FIELD_POINTS = 'points';
    const FIELD_LAST_CHANGED = 'last_change';
    const FIELD_DEADLINE_PERIOD = 'deadline_period';
    const FIELD_DEADLINE_DATE = 'deadline_date';
    const FIELD_VALIDITY_QUALIFICATION_DATE = 'vq_date';
    const FIELD_VALIDITY_QUALIFICATION_PERIOD = 'vq_period';
    const FIELD_VQ_RESTART_PERIOD = 'vq_restart_period';
    const FIELD_ACCESS_CONTROL_ORGU_POSITIONS = 'access_ctrl_org_pos';
    const FIELD_RM_NOT_RESTARTED_BY_USER_DAY = 'rm_nr_by_usr_days';
    const FIELD_PROC_ENDS_NOT_SUCCESSFUL = 'proc_end_no_success';
	const FIELD_SEND_RE_ASSIGNED_MAIL = "send_re_assigned_mail";
	const FIELD_SEND_INFO_TO_RE_ASSIGN_MAIL = "send_info_to_re_assign_mail";
	const FIELD_SEND_RISKY_TO_FAIL_MAIL = "send_risky_to_fail_mail";

    public function __construct(
        ilDBInterface $db,
        ilOrgUnitObjectTypePositionSetting $tps
    ) {
        $this->db = $db;
        $this->tps = $tps;
    }

    /**
     * @inheritdoc
     * @throws ilException
     */
    public function createFor(int $obj_id) : ilStudyProgrammeSettings
    {
        $prg = new ilStudyProgrammeSettings($obj_id);
        $this->insertDB(
            $obj_id,
            ilStudyProgrammeSettings::DEFAULT_SUBTYPE,
            ilStudyProgrammeSettings::STATUS_DRAFT,
            ilStudyProgrammeSettings::MODE_UNDEFINED,
            ilStudyProgrammeSettings::DEFAULT_POINTS,
            (new DateTime())->format(ilStudyProgrammeSettings::DATE_TIME_FORMAT),
            0,
            ilStudyProgrammeSettings::NO_VALIDITY_OF_QUALIFICATION_PERIOD,
            ilStudyProgrammeSettings::NO_RESTART,
            $this->tps->isActive(),
            null,
            null,
            null,
            null
        );
        $prg->setSubtypeId(ilStudyProgrammeSettings::DEFAULT_SUBTYPE)
            ->setStatus(ilStudyProgrammeSettings::STATUS_DRAFT)
            ->setLPMode(ilStudyProgrammeSettings::MODE_UNDEFINED)
            ->setPoints(ilStudyProgrammeSettings::DEFAULT_POINTS)
            ->setAccessControlByOrguPositions($this->tps->isActive());
        self::$cache[$obj_id] = $prg;
        return $prg;
    }

    /**
     * @inheritdoc
     * @throws ilException
     */
    public function read(int $obj_id) : ilStudyProgrammeSettings
    {
        if (!array_key_exists($obj_id, self::$cache)) {
            self::$cache[$obj_id] = $this->loadDB($obj_id);
        }
        return self::$cache[$obj_id];
    }

    /**
     * @inheritdoc
     */
    public function update(ilStudyProgrammeSettings $settings) : void
    {
        $deadine_date = $settings->getDeadlineDate();
        if(!is_null($deadine_date)) {
            $deadine_date = $deadine_date->format(ilStudyProgrammeSettings::DATE_TIME_FORMAT);
        }
        $vq_date = $settings->getValidityOfQualificationDate();
        if(!is_null($vq_date)) {
            $vq_date = $vq_date->format(ilStudyProgrammeSettings::DATE_TIME_FORMAT);
        }

        $this->updateDB(
            $settings->getObjId(),
            $settings->getSubtypeId(),
            $settings->getStatus(),
            $settings->getLPMode(),
            $settings->getPoints(),
            $settings->getLastChange()->format(ilStudyProgrammeSettings::DATE_TIME_FORMAT),
            $settings->getDeadlinePeriod(),
            $settings->getValidityOfQualificationPeriod(),
            $settings->getRestartPeriod(),
            $settings->getAccessControlByOrguPositions(),
            $deadine_date,
            $vq_date,
            $settings->getReminderNotRestartedByUserDays(),
            $settings->getProcessingEndsNotSuccessfulDays(),
			$settings->sendReAssignedMail(),
			$settings->sendInfoToReAssignMail(),
			$settings->sendRiskyToFailMail()
        );
        self::$cache[$settings->getObjId()] = $settings;
    }

    /**
     * @inheritdoc
     */
    public function delete(ilStudyProgrammeSettings $settings) : void
    {
        unset(self::$cache[$settings->getObjId()]);
        $this->deleteDB($settings->getObjId());
    }

    /**
     * @inheritdoc
     * @throws ilException
     */
    public function loadByType(int $type_id) : array
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
            . '	,' . self::FIELD_ACCESS_CONTROL_ORGU_POSITIONS
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


    public function loadIdsByType(int $type_id) : array
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
        bool $access_ctrl_org_pos,
        string $deadline_date = null,
        string $vq_date = null,
        int $rm_nr_by_usr_days = null,
        int $proc_end_no_success = null,
		bool $send_re_assigned_mail = false,
		bool $send_info_to_re_assign_mail = false,
		bool $send_risky_to_fail_mail = false
    ) {
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
                self::FIELD_ACCESS_CONTROL_ORGU_POSITIONS => ['integer', $access_ctrl_org_pos],
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
    protected function loadDB(int $obj_id) : ilStudyProgrammeSettings
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
                . ', ' . self::FIELD_ACCESS_CONTROL_ORGU_POSITIONS
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
            throw new LogicException('invaid obj_id to load: ' . $obj_id);
        }
        return $this->createByRow($rec);
    }

    /**
     * @throws ilException
     */
    protected function createByRow(array $row) : ilStudyProgrammeSettings
    {
        $return = (new ilStudyProgrammeSettings((int)$row[self::FIELD_OBJ_ID]))
            ->setSubtypeId((int)$row[self::FIELD_SUBTYPE_ID])
            ->setStatus((int)$row[self::FIELD_STATUS])
            ->setLPMode((int)$row[self::FIELD_LP_MODE])
            ->setPoints((int)$row[self::FIELD_POINTS])
            ->setLastChange(DateTime::createFromFormat(ilStudyProgrammeSettings::DATE_TIME_FORMAT, $row[self::FIELD_LAST_CHANGED]));
        if ($row[self::FIELD_DEADLINE_DATE] !== null) {
            $return->setDeadlineDate(DateTime::createFromFormat(ilStudyProgrammeSettings::DATE_TIME_FORMAT, $row[self::FIELD_DEADLINE_DATE]));
        } else {
            $return->setDeadlinePeriod((int)$row[self::FIELD_DEADLINE_PERIOD]);
        }
        if ($row[self::FIELD_VALIDITY_QUALIFICATION_DATE] !== null) {
            $return->setValidityOfQualificationDate(DateTime::createFromFormat(ilStudyProgrammeSettings::DATE_TIME_FORMAT, $row[self::FIELD_VALIDITY_QUALIFICATION_DATE]));
        } else {
            $return->setValidityOfQualificationPeriod((int)$row[self::FIELD_VALIDITY_QUALIFICATION_PERIOD]);
        }

        $return->setRestartPeriod((int)$row[self::FIELD_VQ_RESTART_PERIOD]);
        $return->setAccessControlByOrguPositions((bool)$row[self::FIELD_ACCESS_CONTROL_ORGU_POSITIONS]);

        $rm_nr_by_usr_days = $row[self::FIELD_RM_NOT_RESTARTED_BY_USER_DAY];
        if(! is_null($rm_nr_by_usr_days)) {
            $rm_nr_by_usr_days = (int)$rm_nr_by_usr_days;
        }
        $proc_end_no_success = $row[self::FIELD_PROC_ENDS_NOT_SUCCESSFUL];
        if(! is_null($proc_end_no_success)) {
            $proc_end_no_success = (int)$proc_end_no_success;
        }

        $return->setReminderNotRestartedByUserDays($rm_nr_by_usr_days);
        $return->setProcessingEndsNotSuccessfulDays($proc_end_no_success);
        return $return->withSendReAssignedMail((bool)$row[self::FIELD_SEND_RE_ASSIGNED_MAIL])
			->withSendInfoToReAssignMail((bool)$row[self::FIELD_SEND_INFO_TO_RE_ASSIGN_MAIL])
			->withSendRiskyToFailMail((bool)$row[self::FIELD_SEND_RISKY_TO_FAIL_MAIL])
		;
    }

    /**
     * @throws LogicException
     */
    protected function deleteDB(int $obj_id)
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
        bool $access_ctrl_org_pos,
        string $deadline_date = null,
        string $vq_date = null,
        int $rm_nr_by_usr_days = null,
        int $proc_end_no_success = null,
		bool $send_re_assigned_mail = false,
		bool $send_info_to_re_assign_mail = false,
		bool $send_risky_to_fail_mail = false
    ) {
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
            self::FIELD_ACCESS_CONTROL_ORGU_POSITIONS => [
                'integer',
                $access_ctrl_org_pos
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

    protected function checkExists(int $obj_id)
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

    public static function clearCache()
    {
        self::$cache = [];
    }
}
