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
    const FIELD_MAIL_SENT_RISKYTOFAIL = 'sent_mail_risky_to_fail';
    const FIELD_MAIL_SENT_WILLEXPIRE = 'sent_mail_expires';
    const FIELD_IS_INDIVIDUAL = 'individual';

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
        ilStudyProgrammeAssignment $ass,
        int $acting_user = null
    ) : ilStudyProgrammeProgress {
        $id = $this->nextId();
        $row = [
            self::FIELD_ID => $id,
            self::FIELD_ASSIGNMENT_ID => $ass->getId(),
            self::FIELD_PRG_ID => $prg->getObjId(),
            self::FIELD_USR_ID => $ass->getUserId(),
            self::FIELD_POINTS => $prg->getAssessmentSettings()->getPoints(),
            self::FIELD_POINTS_CUR => 0,
            self::FIELD_STATUS => ilStudyProgrammeProgress::STATUS_IN_PROGRESS,
            self::FIELD_COMPLETION_BY => null,
            self::FIELD_LAST_CHANGE => ilUtil::now(),
            self::FIELD_ASSIGNMENT_DATE => ilUtil::now(),
            self::FIELD_LAST_CHANGE_BY => $acting_user,
            self::FIELD_COMPLETION_DATE => null,
            self::FIELD_DEADLINE => null,
            self::FIELD_VQ_DATE => null,
            self::FIELD_INVALIDATED => 0,
            self::FIELD_IS_INDIVIDUAL => 0
        ];
        $this->insertRowDB($row);
        return $this->buildByRow($row);
    }

    /**
     * @inheritdoc
     *
     * @throws ilException
     */
    public function get(int $id) : ilStudyProgrammeProgress
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
    public function getByIds(
        int $prg_id,
        int $assignment_id
    ) : ilStudyProgrammeProgress {
        return $this->getByPrgIdAndAssignmentId($prg_id, $assignment_id);
    }

    /**
     * @inheritdoc
     *
     * @throws ilException
     *
     * @return ilStudyProgrammeProgress | void
     */
    public function getByPrgIdAndAssignmentId(int $prg_id, int $assignment_id)
    {
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

    public function getRootProgressOf(ilStudyProgrammeAssignment $assignment) : ilStudyProgrammeProgress
    {
        $rows = $this->loadByFilter(
            [
                self::FIELD_PRG_ID => $assignment->getRootId(),
                self::FIELD_ASSIGNMENT_ID => $assignment->getId(),
                self::FIELD_USR_ID => $assignment->getUserId()
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
    public function getByPrgIdAndUserId(int $prg_id, int $usr_id) : array
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
    public function getByPrgId(int $prg_id) : array
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
    public function getFirstByPrgId(int $prg_id)
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
    public function getByAssignmentId(int $assignment_id) : array
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
    public function getExpiredSuccessfull() : array
    {
        $return = [];
        foreach ($this->loadExpiredSuccessful() as $row) {
            $return[] = $this->buildByRow($row);
        }
        return $return;
    }

    /**
     * @inheritDoc
     *
     * @throws ilException
     */
    public function getPassedDeadline() : array
    {
        $return = [];
        foreach ($this->loadPassedDeadline() as $row) {
            $return[] = $this->buildByRow($row);
        }
        return $return;
    }

    /**
     * @inheritdoc
     *
     * @throws ilException
     */
    public function getRiskyToFailInstances() : array
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
                self::FIELD_INVALIDATED => $progress->isInvalidated() ? 1 : 0,
                self::FIELD_IS_INDIVIDUAL => $progress->hasIndividualModifications() ? 1 : 0
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
                , self::FIELD_IS_INDIVIDUAL => ['integer', $row[self::FIELD_IS_INDIVIDUAL]]
            ]
        );
    }

    /**
     * @return int[] node_ids the user had progresses on
     */
    public function deleteForAssignmentId(int $assignment_id) : array
    {
        $progresses = $this->getByAssignmentId($assignment_id);

        $query = 'DELETE FROM ' . self::TABLE . PHP_EOL
            . ' WHERE ' . self::FIELD_ASSIGNMENT_ID . ' = '
            . $this->db->quote($assignment_id, 'integer');

        $this->db->manipulate($query);

        return array_map(
            function ($progress) {
                return $progress->getNodeId();
            },
            $progresses
        );
    }

    public function sentRiskyToFailFor(int $progress_id) : void
    {
        $where = [
            self::FIELD_ID => [
                'integer',
                $progress_id
            ]
        ];

        $values = [
            self::FIELD_MAIL_SENT_RISKYTOFAIL => [
                'timestamp',
                date('Y-m-d H:i:s')
            ]
        ];

        $this->db->update(self::TABLE, $values, $where);
    }

    public function sentExpiryInfoFor(int $progress_id) : void
    {
        $where = [
            self::FIELD_ID => [
                'integer',
                $progress_id
            ]
        ];

        $values = [
            self::FIELD_MAIL_SENT_WILLEXPIRE => [
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
            self::FIELD_IS_INDIVIDUAL => [
                'integer',
                $data[self::FIELD_IS_INDIVIDUAL]
            ]
        ];

        $this->db->update(self::TABLE, $values, $where);
    }

    /**
     * @throws ilException
     */
    protected function buildByRow(array $row) : ilStudyProgrammeProgress
    {
        $prgrs = (new ilStudyProgrammeProgress((int) $row[self::FIELD_ID]))
            ->withAssignmentId((int) $row[self::FIELD_ASSIGNMENT_ID])
            ->withNodeId((int) $row[self::FIELD_PRG_ID])
            ->withUserId((int) $row[self::FIELD_USR_ID])
            ->withStatus((int) $row[self::FIELD_STATUS])
            ->withAmountOfPoints((int) $row[self::FIELD_POINTS])
            ->withCurrentAmountOfPoints((int) $row[self::FIELD_POINTS_CUR])
            ->withDeadline(
                $row[self::FIELD_DEADLINE] ?
                    DateTimeImmutable::createFromFormat(ilStudyProgrammeProgress::DATE_FORMAT, $row[self::FIELD_DEADLINE]) :
                    null
            )
            ->withAssignmentDate(
                DateTimeImmutable::createFromFormat(ilStudyProgrammeProgress::DATE_TIME_FORMAT, $row[self::FIELD_ASSIGNMENT_DATE])
            )
            ->withCompletion(
                (int) $row[self::FIELD_COMPLETION_BY],
                $row[self::FIELD_COMPLETION_DATE] ?
                    DateTimeImmutable::createFromFormat(ilStudyProgrammeProgress::DATE_TIME_FORMAT, $row[self::FIELD_COMPLETION_DATE]) :
                    null
            )
            ->withLastChange(
                (int) $row[self::FIELD_LAST_CHANGE_BY],
                $row[self::FIELD_LAST_CHANGE] ?
                    DateTimeImmutable::createFromFormat(ilStudyProgrammeProgress::DATE_TIME_FORMAT, $row[self::FIELD_LAST_CHANGE]) :
                    null
            )
            ->withValidityOfQualification(
                $row[self::FIELD_VQ_DATE] ?
                    DateTimeImmutable::createFromFormat(ilStudyProgrammeProgress::DATE_TIME_FORMAT, $row[self::FIELD_VQ_DATE]) :
                    null
            )
            ->withIndividualModifications((bool) $row[self::FIELD_IS_INDIVIDUAL]);



        if ((int) $row[self::FIELD_INVALIDATED] === 1) {
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
                (new DateTimeImmutable())->format(ilStudyProgrammeProgress::DATE_FORMAT),
                'text'
            )
            . '    AND ' . self::FIELD_INVALIDATED . ' != 1 OR ' . self::FIELD_INVALIDATED . ' IS NULL';

        $res = $this->db->query($q);
        while ($rec = $this->db->fetchAssoc($res)) {
            yield $rec;
        }
    }

    protected function loadPassedDeadline()
    {
        $q =
             $this->getSQLHeader() . PHP_EOL
            . 'WHERE ' . $this->db->in(
                self::FIELD_STATUS,
                [
                    ilStudyProgrammeProgress::STATUS_IN_PROGRESS,
                    ilStudyProgrammeProgress::STATUS_ACCREDITED
                ],
                false,
                'integer'
            ) . PHP_EOL
            . 'AND ' . self::FIELD_DEADLINE . ' IS NOT NULL' . PHP_EOL
            . 'AND DATE(' . self::FIELD_DEADLINE . ') < ' . $this->db->quote(
                (new DateTimeImmutable())->format(ilStudyProgrammeProgress::DATE_FORMAT),
                'text'
            ) . PHP_EOL
        ;
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
                (new DateTimeImmutable())->format(ilStudyProgrammeProgress::DATE_FORMAT),
                'text'
            )
            . '    AND ' . self::FIELD_MAIL_SENT_RISKYTOFAIL . ' IS NULL'
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
            . ', ' . self::FIELD_IS_INDIVIDUAL
            . ' FROM ' . self::TABLE;
    }

    /**
     * @param array <int, DateTimeImmutable>    $programmes_and_due
     * @return ilStudyProgrammeProgress[]
     */
    public function getRiskyToFail(array $programmes_and_due) : array
    {
        $ret = [];
        if (count($programmes_and_due) == 0) {
            return $ret;
        }

        $where = [];
        foreach ($programmes_and_due as $programme_obj_id => $due) {
            $due = $due->format(ilStudyProgrammeProgress::DATE_FORMAT);
            $where[] = '('
                . self::FIELD_PRG_ID . '=' . $programme_obj_id
                . ' AND ' . self::FIELD_DEADLINE . '<=' . $this->db->quote($due, 'text')
                . ' AND ' . self::FIELD_MAIL_SENT_RISKYTOFAIL . ' IS NULL'
                . ')';
        }
        $query = $this->getSQLHeader() . ' WHERE ' . implode(' OR ', $where);
        
        $res = $this->db->query($query);
        while ($rec = $this->db->fetchAssoc($res)) {
            $ret[] = $this->buildByRow($rec);
        }
        return $ret;
    }

    /**
     * @param array <int, DateTimeImmutable>    $programmes_and_due
     * @return ilStudyProgrammeProgress[]
     */
    public function getAboutToExpire(
        array $programmes_and_due,
        bool $discard_formerly_notified = true
    ) : array {
        $ret = [];
        if (count($programmes_and_due) == 0) {
            return $ret;
        }

        $where = [];
        foreach ($programmes_and_due as $programme_obj_id => $due) {
            $due = $due->format(ilStudyProgrammeProgress::DATE_FORMAT_ENDOFDAY);
            $where_clause = '('
                . self::FIELD_PRG_ID . '=' . $programme_obj_id
                . ' AND ' . self::FIELD_VQ_DATE . '<=' . $this->db->quote($due, 'text');

            if ($discard_formerly_notified) {
                $where_clause .= ' AND ' . self::FIELD_MAIL_SENT_WILLEXPIRE . ' IS NULL';
            }
            
            $where_clause .= ')';
            $where[] = $where_clause;
        }

        $query = $this->getSQLHeader() . ' WHERE ' . implode(' OR ', $where);
        $res = $this->db->query($query);
        while ($rec = $this->db->fetchAssoc($res)) {
            $ret[] = $this->buildByRow($rec);
        }
        return $ret;
    }
        
    protected function nextId() : int
    {
        return (int) $this->db->nextId(self::TABLE);
    }

    public function deleteAllOrphanedProgresses(
        string $assignment_table,
        string $assignment_id_field
    ) : void {
        $query = 'DELETE FROM ' . self::TABLE . PHP_EOL
            . 'WHERE ' . self::FIELD_ASSIGNMENT_ID . PHP_EOL
            . 'NOT IN (' . PHP_EOL
            . 'SELECT ' . $this->db->quoteIdentifier($assignment_id_field)
            . ' FROM ' . $this->db->quoteIdentifier($assignment_table) . PHP_EOL
            . ');' . PHP_EOL;
        $this->db->manipulate($query);
    }

    public function deleteProgressesFor(int $prg_obj_id) : void
    {
        $query = 'DELETE FROM ' . self::TABLE . PHP_EOL
            . ' WHERE ' . self::FIELD_PRG_ID . ' = '
            . $this->db->quote($prg_obj_id, 'integer');

        $this->db->manipulate($query);
    }
}
