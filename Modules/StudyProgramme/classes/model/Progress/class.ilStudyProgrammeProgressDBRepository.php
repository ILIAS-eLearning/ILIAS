<?php

declare(strict_types=1);

class ilStudyProgrammeProgressDBRepository implements ilStudyProgrammeProgressRepository
{
    protected static $cache = [];
    protected $db;

    const TABLE = 'prg_usr_progress';

    const FIELD_ID = 'id';
    const FIELD_ASSIGNMENT_ID = 'assignment_id';
    const FIELD_PRG_ID = 'prg_id';
    const FIELD_USR_ID = 'usr_id';
    const FIELD_POINTS = 'points';
    const FIELD_POINTS_CUR = 'points_cur';
    const FIELD_STATUS = 'status';
    const FIELD_COMPLETION_BY = 'completion_by';
    const FIELD_ASSIGNMENT_DATE = 'assignment_date';
    const FIELD_LAST_CHANGE = 'last_change';
    const FIELD_LAST_CHANGE_BY = 'last_change_by';
    const FIELD_COMPLETION_DATE = 'completion_date';
    const FIELD_DEADLINE = 'deadline';
    const FIELD_VQ_DATE = 'vq_date';
    const FIELD_INVALIDATED = 'invalidated';
    const FIELD_MAIL_SEND = 'risky_to_fail_mail_send';

    public function __construct(ilDBInterface $db)
    {
        $this->db = $db;
    }

    /**
     * @inheritdoc
     *
     * @throws ilException
     */
    public function createFor(
        ilStudyProgrammeSettings $prg,
        ilStudyProgrammeAssignment $ass
    ) : ilStudyProgrammeProgress {
        $id = $this->nextId();
        $row = [
            self::FIELD_ID => $id,
            self::FIELD_ASSIGNMENT_ID => $ass->getId(),
            self::FIELD_PRG_ID => $prg->getObjId(),
            self::FIELD_USR_ID => $ass->getUserId(),
            self::FIELD_POINTS => $prg->getPoints(),
            self::FIELD_POINTS_CUR => 0,
            self::FIELD_STATUS => ilStudyProgrammeProgress::STATUS_IN_PROGRESS,
            self::FIELD_COMPLETION_BY => null,
            self::FIELD_LAST_CHANGE => ilUtil::now(),
            self::FIELD_ASSIGNMENT_DATE => ilUtil::now(),
            self::FIELD_LAST_CHANGE_BY => null,
            self::FIELD_COMPLETION_DATE => null,
            self::FIELD_DEADLINE => null,
            self::FIELD_VQ_DATE => null,
            self::FIELD_INVALIDATED => 0
        ];
        $this->insertRowDB($row);
        return $this->buildByRow($row);
    }

    /**
     * @inheritdoc
     *
     * @throws ilException
     */
    public function read(int $id) : ilStudyProgrammeProgress
    {
        foreach ($this->loadByFilter([self::FIELD_ID => $id]) as $row) {
            return $this->buildByRow($row);
        }
        throw new ilException('invalid id ' . $id);
    }


    /**
     * @inheritdoc
     *
     * @throws ilException
     */
    public function readByIds(
        int $prg_id,
        int $assignment_id,
        int $usr_id
    ) : ilStudyProgrammeProgress {
        return $this->readByPrgIdAndAssignmentId($prg_id, $assignment_id);
    }

    /**
     * @inheritdoc
     *
     * @throws ilException
     *
     * @return ilStudyProgrammeProgress | void
     */
    public function readByPrgIdAndAssignmentId(int $prg_id, int $assignment_id) {
        $rows = $this->loadByFilter(
            [
                self::FIELD_PRG_ID => $prg_id,
                self::FIELD_ASSIGNMENT_ID => $assignment_id
            ]
        );

        foreach ($rows as $row) {
            return $this->buildByRow($row);
        }
    }

    /**
     * @inheritdoc
     *
     * @throws ilException
     */
    public function readByPrgIdAndUserId(int $prg_id, int $usr_id) : array
    {
        $return = [];
        foreach ($this->loadByFilter([self::FIELD_PRG_ID => $prg_id, self::FIELD_USR_ID => $usr_id]) as $row) {
            $return[] = $this->buildByRow($row);
        }
        return $return;
    }

    /**
     * @inheritdoc
     *
     * @throws ilException
     */
    public function readByPrgId(int $prg_id) : array
    {
        $return = [];
        foreach ($this->loadByFilter([self::FIELD_PRG_ID => $prg_id]) as $row) {
            $return[] = $this->buildByRow($row);
        }
        return $return;
    }

    /**
     * @return ilStudyProgrammeProgress | void
     * @throws ilException
     */
    public function readFirstByPrgId(int $prg_id)
    {
        foreach ($this->loadByFilter([self::FIELD_PRG_ID => $prg_id]) as $row) {
            return $this->buildByRow($row);
        }
    }

    /**
     * @inheritdoc
     *
     * @throws ilException
     */
    public function readByAssignmentId(int $assignment_id) : array
    {
        $return = [];
        foreach ($this->loadByFilter([self::FIELD_ASSIGNMENT_ID => $assignment_id]) as $row) {
            $return[] = $this->buildByRow($row);
        }
        return $return;
    }

    /**
     * @inheritdoc
     *
     * @throws ilException
     */
    public function readExpiredSuccessfull() : array
    {
        $return = [];
        foreach ($this->loadExpiredSuccessful() as $row) {
            $return[] = $this->buildByRow($row);
        }
        return $return;
    }

    /**
     * @inheritdoc
     *
     * @throws ilException
     */
    public function readRiskyToFailInstances() : array
    {
        $return = [];
        foreach ($this->loadRiskyToFailInstance() as $row) {
            $return[] = $this->buildByRow($row);
        }
        return $return;
    }

    /**
     * @inheritdoc
     */
    public function update(ilStudyProgrammeProgress $progress)
    {
        $this->updateRowDB(
            [
                self::FIELD_ID => $progress->getId(),
                self::FIELD_ASSIGNMENT_ID => $progress->getAssignmentId(),
                self::FIELD_PRG_ID => $progress->getNodeId(),
                self::FIELD_USR_ID => $progress->getUserId(),
                self::FIELD_STATUS => $progress->getStatus(),
                self::FIELD_POINTS => $progress->getAmountOfPoints(),
                self::FIELD_POINTS_CUR => $progress->getCurrentAmountOfPoints(),
                self::FIELD_COMPLETION_BY => $progress->getCompletionBy(),
                self::FIELD_LAST_CHANGE_BY => $progress->getLastChangeBy(),
                self::FIELD_LAST_CHANGE => $progress->getLastChange()->format(ilStudyProgrammeProgress::DATE_TIME_FORMAT),
                self::FIELD_ASSIGNMENT_DATE => $progress->getAssignmentDate()->format(ilStudyProgrammeProgress::DATE_TIME_FORMAT),
                self::FIELD_COMPLETION_DATE =>
                    $progress->getCompletionDate() ?
                        $progress->getCompletionDate()->format(ilStudyProgrammeProgress::DATE_TIME_FORMAT) : null,
                self::FIELD_DEADLINE => $progress->getDeadline() ? $progress->getDeadline()->format(ilStudyProgrammeProgress::DATE_FORMAT) : null,
                self::FIELD_VQ_DATE => $progress->getValidityOfQualification() ? $progress->getValidityOfQualification()->format(ilStudyProgrammeProgress::DATE_TIME_FORMAT) : null,
                self::FIELD_INVALIDATED => $progress->isInvalidated() ? 1 : 0
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function delete(ilStudyProgrammeProgress $progress)
    {
        $this->deleteDB($progress->getId());
    }

    protected function insertRowDB(array $row)
    {
        $this->db->insert(
            self::TABLE,
            [
                self::FIELD_ID => ['integer', $row[self::FIELD_ID]]
                , self::FIELD_ASSIGNMENT_ID => ['integer', $row[self::FIELD_ASSIGNMENT_ID]]
                , self::FIELD_PRG_ID => ['integer', $row[self::FIELD_PRG_ID]]
                , self::FIELD_USR_ID => ['integer', $row[self::FIELD_USR_ID]]
                , self::FIELD_STATUS => ['integer', $row[self::FIELD_STATUS]]
                , self::FIELD_POINTS => ['integer', $row[self::FIELD_POINTS]]
                , self::FIELD_POINTS_CUR => ['integer', $row[self::FIELD_POINTS_CUR]]
                , self::FIELD_COMPLETION_BY => ['integer', $row[self::FIELD_COMPLETION_BY]]
                , self::FIELD_LAST_CHANGE_BY => ['integer', $row[self::FIELD_LAST_CHANGE_BY]]
                , self::FIELD_LAST_CHANGE => ['text', $row[self::FIELD_LAST_CHANGE]]
                , self::FIELD_ASSIGNMENT_DATE => ['timestamp', $row[self::FIELD_ASSIGNMENT_DATE]]
                , self::FIELD_COMPLETION_DATE => ['timestamp', $row[self::FIELD_COMPLETION_DATE]]
                , self::FIELD_DEADLINE => ['text', $row[self::FIELD_DEADLINE]]
                , self::FIELD_VQ_DATE => ['timestamp', $row[self::FIELD_VQ_DATE]]
                , self::FIELD_INVALIDATED => ['timestamp', $row[self::FIELD_INVALIDATED]]
            ]
        );
    }

    public function deleteDB(int $id)
    {
        $this->db->manipulate(
            'DELETE FROM ' . self::TABLE . ' WHERE ' . self::FIELD_ID . ' = ' . $this->db->quote($id, 'integer')
        );
    }

    public function reminderSendFor(int $progress_id) : void
    {
        $where = [
            self::FIELD_ID => [
                'integer',
                $progress_id
            ]
        ];

        $values = [
            self::FIELD_MAIL_SEND => [
                'timestamp',
                date('Y-m-d H:i:s')
            ]
        ];

        $this->db->update(self::TABLE, $values, $where);
    }

    protected function updateRowDB(array $data)
    {
        $where = [
            self::FIELD_ID => [
                'integer',
                $data[self::FIELD_ID]
            ]
        ];

        $values = [
            self::FIELD_ASSIGNMENT_ID => [
                'integer',
                $data[self::FIELD_ASSIGNMENT_ID]
            ],
            self::FIELD_PRG_ID => [
                'integer',
                $data[self::FIELD_PRG_ID]
            ],
            self::FIELD_USR_ID => [
                'integer',
                $data[self::FIELD_USR_ID]
            ],
            self::FIELD_STATUS => [
                'integer',
                $data[self::FIELD_STATUS]
            ],
            self::FIELD_POINTS => [
                'integer',
                $data[self::FIELD_POINTS]
            ],
            self::FIELD_POINTS_CUR => [
                'integer',
                $data[self::FIELD_POINTS_CUR]
            ],
            self::FIELD_COMPLETION_BY => [
                'integer',
                $data[self::FIELD_COMPLETION_BY]
            ],
            self::FIELD_LAST_CHANGE_BY => [
                'integer',
                $data[self::FIELD_LAST_CHANGE_BY]
            ],
            self::FIELD_LAST_CHANGE => [
                'text',
                $data[self::FIELD_LAST_CHANGE]
            ],
            self::FIELD_ASSIGNMENT_DATE => [
                'timestamp',
                $data[self::FIELD_ASSIGNMENT_DATE]
            ],
            self::FIELD_COMPLETION_DATE => [
                'timestamp',
                $data[self::FIELD_COMPLETION_DATE]
            ],
            self::FIELD_DEADLINE => [
                'text',
                $data[self::FIELD_DEADLINE]
            ],
            self::FIELD_VQ_DATE => [
                'timestamp',
                $data[self::FIELD_VQ_DATE]
            ],
            self::FIELD_INVALIDATED => [
                'integer',
                $data[self::FIELD_INVALIDATED]
            ],
        ];

        $this->db->update(self::TABLE, $values, $where);
    }

    /**
     * @throws ilException
     */
    protected function buildByRow(array $row) : ilStudyProgrammeProgress
    {
        $prgrs = (new ilStudyProgrammeProgress((int)$row[self::FIELD_ID]))
            ->setAssignmentId((int)$row[self::FIELD_ASSIGNMENT_ID])
            ->setNodeId((int)$row[self::FIELD_PRG_ID])
            ->setUserId((int)$row[self::FIELD_USR_ID])
            ->setStatus((int)$row[self::FIELD_STATUS])
            ->setAmountOfPoints((int)$row[self::FIELD_POINTS])
            ->setCurrentAmountOfPoints((int)$row[self::FIELD_POINTS_CUR])
            ->setCompletionBy($row[self::FIELD_COMPLETION_BY])
            ->setDeadline(
                $row[self::FIELD_DEADLINE] ?
                    DateTime::createFromFormat(ilStudyProgrammeProgress::DATE_FORMAT, $row[self::FIELD_DEADLINE]) :
                    null
            )
            ->setAssignmentDate(
                DateTime::createFromFormat(ilStudyProgrammeProgress::DATE_TIME_FORMAT, $row[self::FIELD_ASSIGNMENT_DATE])
            )
            ->setCompletionDate(
                $row[self::FIELD_COMPLETION_DATE] ?
                    DateTime::createFromFormat(ilStudyProgrammeProgress::DATE_TIME_FORMAT, $row[self::FIELD_COMPLETION_DATE]) :
                    null
            )
            ->setLastChange(
                $row[self::FIELD_LAST_CHANGE] ?
                    DateTime::createFromFormat(ilStudyProgrammeProgress::DATE_TIME_FORMAT, $row[self::FIELD_LAST_CHANGE]) :
                    null
            )
            ->setValidityOfQualification(
                $row[self::FIELD_VQ_DATE] ?
                    DateTime::createFromFormat(ilStudyProgrammeProgress::DATE_TIME_FORMAT, $row[self::FIELD_VQ_DATE]) :
                    null
            );
        if ((int)$row[self::FIELD_INVALIDATED] === 1) {
            $prgrs = $prgrs->invalidate();
        }
        return $prgrs;

    }

    protected function loadByFilter(array $filter)
    {
        $q = $this->getSQLHeader()
            . '	WHERE TRUE';
        foreach ($filter as $field => $value) {
            $q .= '	AND ' . $field . ' = ' . $this->db->quote($value, 'text');
        }
        $res = $this->db->query($q);
        while ($rec = $this->db->fetchAssoc($res)) {
            yield $rec;
        }
    }

    protected function loadExpiredSuccessful()
    {
        $q = $this->getSQLHeader()
            . ' WHERE ' . $this->db->in(
                self::FIELD_STATUS,
                [
                    ilStudyProgrammeProgress::STATUS_ACCREDITED,
                    ilStudyProgrammeProgress::STATUS_COMPLETED
                ],
                false,
                'integer'
            )
            . '     AND ' . self::FIELD_VQ_DATE . ' IS NOT NULL'
            . '     AND DATE(' . self::FIELD_VQ_DATE . ') < '
            . $this->db->quote(
                (new DateTime())->format(ilStudyProgrammeProgress::DATE_FORMAT)
                , 'text'
            )
            . '    AND ' . self::FIELD_INVALIDATED . ' != 1 OR ' . self::FIELD_INVALIDATED . ' IS NULL';

        $res = $this->db->query($q);
        while ($rec = $this->db->fetchAssoc($res)) {
            yield $rec;
        }
    }

    protected function loadRiskyToFailInstance()
    {
        $q = $this->getSQLHeader()
            . ' WHERE ' . $this->db->in(
                self::FIELD_STATUS,
                [
                    ilStudyProgrammeProgress::STATUS_ACCREDITED,
                    ilStudyProgrammeProgress::STATUS_COMPLETED
                ],
                true,
                'integer'
            )
            . '     AND ' . self::FIELD_DEADLINE . ' IS NOT NULL'
            . '     AND DATE(' . self::FIELD_DEADLINE . ') < '
            . $this->db->quote(
                (new DateTime())->format(ilStudyProgrammeProgress::DATE_FORMAT)
                , 'text'
            )
            . '    AND ' . self::FIELD_MAIL_SEND . ' IS NULL'
        ;
        $res = $this->db->query($q);
        while ($rec = $this->db->fetchAssoc($res)) {
            yield $rec;
        }
    }

    protected function getSQLHeader() : string
    {
        return 'SELECT ' . self::FIELD_ID
            . ', ' . self::FIELD_ASSIGNMENT_ID
            . ', ' . self::FIELD_PRG_ID
            . ', ' . self::FIELD_USR_ID
            . ', ' . self::FIELD_STATUS
            . ', ' . self::FIELD_POINTS
            . ', ' . self::FIELD_POINTS_CUR
            . ', ' . self::FIELD_COMPLETION_BY
            . ', ' . self::FIELD_LAST_CHANGE
            . ', ' . self::FIELD_LAST_CHANGE_BY
            . ', ' . self::FIELD_ASSIGNMENT_DATE
            . ', ' . self::FIELD_COMPLETION_DATE
            . ', ' . self::FIELD_DEADLINE
            . ', ' . self::FIELD_VQ_DATE
            . ', ' . self::FIELD_INVALIDATED
            . ' FROM ' . self::TABLE;
    }

    protected function nextId() : int
    {
        return (int)$this->db->nextId(self::TABLE);
    }
}
