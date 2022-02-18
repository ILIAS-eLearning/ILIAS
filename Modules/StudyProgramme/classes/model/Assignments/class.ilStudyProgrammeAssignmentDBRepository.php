<?php declare(strict_types=1);

class ilStudyProgrammeAssignmentDBRepository implements ilStudyProgrammeAssignmentRepository
{
    /**
     * @var ilDBInterface
     */
    protected $db;

    const TABLE = 'prg_usr_assignments';

    const FIELD_ID = 'id';
    const FIELD_USR_ID = 'usr_id';
    const FIELD_ROOT_PRG_ID = 'root_prg_id';
    const FIELD_LAST_CHANGE = 'last_change';
    const FIELD_LAST_CHANGE_BY = 'last_change_by';
    const FIELD_RESTART_DATE = 'restart_date';
    const FIELD_RESTARTED_ASSIGNMENT_ID = 'restarted_assignment_id';
    const FIELD_RESTART_MAIL = 'restart_mail_send';

    public function __construct(ilDBInterface $db)
    {
        $this->db = $db;
    }

    /**
     * @inheritdoc
     *
     * @throws ilException
     */
    public function createFor(int $root_prg_id, int $usr_id, int $assigning_usr_id) : ilStudyProgrammeAssignment
    {
        if (ilObject::_lookupType($usr_id) != "usr") {
            throw new ilException("ilStudyProgrammeAssignment::createFor: '$usr_id' "
                . "is no id of a user.");
        }
        if (ilObject::_lookupType($root_prg_id) != "prg") {
            throw new ilException("ilStudyProgrammeAssignment::createFor: '$root_prg_id' "
                . "is no id of a prg.");
        }

        $row = [
            self::FIELD_ID => $this->nextId(),
            self::FIELD_USR_ID => $usr_id,
            self::FIELD_ROOT_PRG_ID => $root_prg_id,
            self::FIELD_LAST_CHANGE_BY => $assigning_usr_id,
            self::FIELD_LAST_CHANGE => ilUtil::now(),
            self::FIELD_RESTART_DATE => null,
            self::FIELD_RESTARTED_ASSIGNMENT_ID => ilStudyProgrammeAssignment::NO_RESTARTED_ASSIGNMENT
        ];
        $this->insertRowDB($row);
        return $this->assignmentByRow($row);
    }

    /**
     * @inheritdoc
     * @throws ilException
     */
    public function get(int $id)
    {
        foreach ($this->loadByFilterDB([self::FIELD_ID => $id]) as $row) {
            return $this->assignmentByRow($row);
        }
        return null;
    }

    /**
     * @inheritdoc
     * @throws ilException
     */
    public function getByUsrId(int $usr_id) : array
    {
        $return = [];
        foreach ($this->loadByFilterDB([self::FIELD_USR_ID => $usr_id]) as $row) {
            $return[] = $this->assignmentByRow($row);
        }
        return $return;
    }

    /**
     * @inheritdoc
     * @throws ilException
     */
    public function getByPrgId(int $prg_id) : array
    {
        $return = [];
        foreach ($this->loadByFilterDB([self::FIELD_ROOT_PRG_ID => $prg_id]) as $row) {
            $return[] = $this->assignmentByRow($row);
        }
        return $return;
    }

    /**
     * @inheritdoc
     * @throws ilException
     */
    public function getByUsrIdAndPrgId(int $usr_id, int $prg_id)
    {
        $return = [];
        foreach ($this->loadByFilterDB(
            [self::FIELD_USR_ID => $usr_id
                , self::FIELD_ROOT_PRG_ID => $prg_id]
        ) as $row) {
            $return[] = $this->assignmentByRow($row);
        }
        return $return;
    }

    /**
     * @inheritdoc
     * @throws ilException
     */
    public function getDueToRestart() : array
    {
        $return = [];
        foreach ($this->loadDueToRestart() as $row) {
            $return[] = $this->assignmentByRow($row);
        }
        return $return;
    }

    /**
     * @return ilStudyProgrammeAssignment[]
     */
    public function getDueToRestartAndMail() : array
    {
        $return = [];
        foreach ($this->loadDueToRestartAndMail() as $row) {
            $return[] = $this->assignmentByRow($row);
        }
        return $return;
    }

    protected function loadDueToRestart()
    {
        $q = $this->getDueToRestartBaseSQL();
        $res = $this->db->query($q);
        while ($rec = $this->db->fetchAssoc($res)) {
            yield $rec;
        }
    }

    protected function loadDueToRestartAndMail()
    {
        $q = $this->getDueToRestartBaseSQL();
        $q .= '    AND ' . self::FIELD_RESTART_MAIL . ' IS NULL';

        $res = $this->db->query($q);
        while ($rec = $this->db->fetchAssoc($res)) {
            yield $rec;
        }
    }

    protected function getSQLHeader() : string
    {
        return 'SELECT ' . self::FIELD_ID
            . ', ' . self::FIELD_USR_ID
            . ', ' . self::FIELD_ROOT_PRG_ID
            . ', ' . self::FIELD_LAST_CHANGE
            . ', ' . self::FIELD_LAST_CHANGE_BY
            . ', ' . self::FIELD_RESTART_DATE
            . ', ' . self::FIELD_RESTARTED_ASSIGNMENT_ID . PHP_EOL
            . ' FROM ' . self::TABLE . PHP_EOL;
    }

    protected function getDueToRestartBaseSQL() : string
    {
        return $this->getSQLHeader()
            . ' WHERE ' . self::FIELD_RESTARTED_ASSIGNMENT_ID
            . ' = ' . $this->db->quote(
                ilStudyProgrammeAssignment::NO_RESTARTED_ASSIGNMENT,
                'integer'
            ) . PHP_EOL
            . '    AND ' . self::FIELD_RESTART_DATE . ' IS NOT NULL' . PHP_EOL
            . '    AND DATE(' . self::FIELD_RESTART_DATE . ') <= '
            . $this->db->quote(
                (new DateTime())->format(
                    ilStudyProgrammeAssignment::DATE_FORMAT
                ),
                'text'
            );
    }

    /**
     * @inheritdoc
     * @throws Exception
     */
    public function getDueToManuelRestart(int $days_before_end) : array
    {
        $return = [];
        foreach ($this->loadDueToManuelRestart($days_before_end) as $row) {
            $return[] = $this->assignmentByRow($row);
        }
        return $return;
    }

    /**
     * @throws Exception
     */
    protected function loadDueToManuelRestart(int $days_before_end)
    {
        $date = new DateTime();
        $date->sub(new DateInterval('P' . $days_before_end . 'D'));
        $q = $this->getSQLHeader()
            . ' WHERE ' . self::FIELD_RESTARTED_ASSIGNMENT_ID
            . ' = ' . $this->db->quote(
                ilStudyProgrammeAssignment::NO_RESTARTED_ASSIGNMENT,
                'integer'
            ) . PHP_EOL
            . '    AND ' . self::FIELD_RESTART_DATE . ' IS NOT NULL' . PHP_EOL
            . '    AND DATE(' . self::FIELD_RESTART_DATE . ') <= '
            . $this->db->quote(
                $date->format(
                    ilStudyProgrammeAssignment::DATE_FORMAT
                ),
                'text'
            );
        $res = $this->db->query($q);
        while ($rec = $this->db->fetchAssoc($res)) {
            yield $rec;
        }
    }

    /**
     * @inheritdoc
     */
    public function update(ilStudyProgrammeAssignment $assignment)
    {
        $row = [
            self::FIELD_ID => $assignment->getId(),
            self::FIELD_USR_ID => $assignment->getUserId(),
            self::FIELD_ROOT_PRG_ID => $assignment->getRootId(),
            self::FIELD_LAST_CHANGE_BY => $assignment->getLastChangeBy(),
            self::FIELD_LAST_CHANGE => $assignment->getLastChange()->format(ilStudyProgrammeAssignment::DATE_TIME_FORMAT),
            self::FIELD_RESTART_DATE => $assignment->getRestartDate() ? $assignment->getRestartDate()->format(ilStudyProgrammeAssignment::DATE_TIME_FORMAT) : null,
            self::FIELD_RESTARTED_ASSIGNMENT_ID => $assignment->getRestartedAssignmentId()
        ];
        $this->updatedRowDB($row);
    }

    /**
     * @inheritdoc
     */
    public function delete(ilStudyProgrammeAssignment $assignment)
    {
        $this->deleteDB($assignment->getId());
    }

    public function reminderSendFor(int $assignment_id) : void
    {
        $where = [
            self::FIELD_ID => [
                'integer',
                $assignment_id
            ]
        ];

        $values = [
            self::FIELD_RESTART_MAIL => [
                'timestamp',
                date('Y-m-d H:i:s')
            ]
        ];

        $this->db->update(self::TABLE, $values, $where);
    }

    /**
     * @throws ilException
     */
    public function getDashboardInstancesforUser(int $usr_id)
    {
        global $DIC;
        $db = $DIC['ilDB'];
        $q = 'SELECT ' . self::FIELD_ID
            . ', ' . self::FIELD_USR_ID
            . ', ' . self::FIELD_ROOT_PRG_ID
            . ', ' . self::FIELD_LAST_CHANGE
            . ', ' . self::FIELD_LAST_CHANGE_BY
            . ', ' . self::FIELD_RESTART_DATE
            . ', ' . self::FIELD_RESTARTED_ASSIGNMENT_ID
            . ' FROM ' . self::TABLE
            . ' WHERE ' . self::FIELD_USR_ID . ' = ' . $usr_id
            . ' ORDER BY ' . self::FIELD_ROOT_PRG_ID . ', ' . self::FIELD_ID
        ;
        $ret = [];
        $assignments = [];
        $res = $db->query($q);
        $prg = 0;
        while ($row = $db->fetchAssoc($res)) {
            if ($prg == 0) {
                $prg = $row['root_prg_id'];
            }
            if ($prg != $row['root_prg_id']) {
                $ret[$prg] = $assignments;
                $prg = $row['root_prg_id'];
                $assignments = [];
            }
            $assignments[(int) $row['id']] = $this->assignmentByRow($row);
        }
        if (count($assignments) > 0) {
            $ret[$prg] = $assignments;
        }

        return $ret;
    }

    /**
     * @throws ilException
     */
    protected function assignmentByRow(array $row) : ilStudyProgrammeAssignment
    {
        return (new ilStudyProgrammeAssignment((int) $row[self::FIELD_ID]))
            ->withRootId((int) $row[self::FIELD_ROOT_PRG_ID])
            ->withUserId((int) $row[self::FIELD_USR_ID])
            ->withLastChange(
                (int) $row[self::FIELD_LAST_CHANGE_BY],
                DateTimeImmutable::createFromFormat(
                    ilStudyProgrammeAssignment::DATE_TIME_FORMAT,
                    $row[self::FIELD_LAST_CHANGE]
                )
            )
            ->withRestarted(
                (int) $row[self::FIELD_RESTARTED_ASSIGNMENT_ID],
                $row[self::FIELD_RESTART_DATE] ?
                    DateTimeImmutable::createFromFormat(ilStudyProgrammeAssignment::DATE_TIME_FORMAT, $row[self::FIELD_RESTART_DATE]) :
                    null
            );
    }

    protected function loadByFilterDB(array $filter)
    {
        $q = 'SELECT ' . self::FIELD_ID
            . '	,' . self::FIELD_USR_ID
            . '	,' . self::FIELD_ROOT_PRG_ID
            . '	,' . self::FIELD_LAST_CHANGE
            . '	,' . self::FIELD_LAST_CHANGE_BY
            . '	,' . self::FIELD_RESTART_DATE
            . '	,' . self::FIELD_RESTARTED_ASSIGNMENT_ID
            . '	FROM ' . self::TABLE
            . '	WHERE TRUE';
        foreach ($filter as $field => $value) {
            $q .= '	AND ' . $field . ' = ' . $this->db->quote($value, 'text');
        }
        $res = $this->db->query($q);
        while ($rec = $this->db->fetchAssoc($res)) {
            yield $rec;
        }
    }

    protected function insertRowDB(array $row)
    {
        $this->db->insert(
            self::TABLE,
            [
                self::FIELD_ID => ['integer', $row[self::FIELD_ID]]
                , self::FIELD_USR_ID => ['integer', $row[self::FIELD_USR_ID]]
                , self::FIELD_ROOT_PRG_ID => ['integer', $row[self::FIELD_ROOT_PRG_ID]]
                , self::FIELD_LAST_CHANGE => ['text', $row[self::FIELD_LAST_CHANGE]]
                , self::FIELD_LAST_CHANGE_BY => ['integer', $row[self::FIELD_LAST_CHANGE_BY]]
                , self::FIELD_RESTART_DATE => ['timestamp', $row[self::FIELD_RESTART_DATE]]
                , self::FIELD_RESTARTED_ASSIGNMENT_ID => ['integer', $row[self::FIELD_RESTARTED_ASSIGNMENT_ID]]
            ]
        );
    }

    protected function updatedRowDB(array $values)
    {
        $q = 'UPDATE ' . self::TABLE
            . '	SET'
            . '	' . self::FIELD_USR_ID . ' = ' . $this->db->quote($values[self::FIELD_USR_ID], 'integer')
            . '	,' . self::FIELD_ROOT_PRG_ID . ' = ' . $this->db->quote($values[self::FIELD_ROOT_PRG_ID], 'integer')
            . '	,' . self::FIELD_LAST_CHANGE . ' = ' . $this->db->quote($values[self::FIELD_LAST_CHANGE], 'text')
            . '	,' . self::FIELD_LAST_CHANGE_BY . ' = ' . $this->db->quote($values[self::FIELD_LAST_CHANGE_BY], 'integer')
            . '	,' . self::FIELD_RESTART_DATE . ' = ' . $this->db->quote($values[self::FIELD_RESTART_DATE], 'timestamp')
            . '	,' . self::FIELD_RESTARTED_ASSIGNMENT_ID . ' = ' . $this->db->quote($values[self::FIELD_RESTARTED_ASSIGNMENT_ID], 'integer')
            . '	WHERE ' . self::FIELD_ID . ' = ' . $this->db->quote($values[self::FIELD_ID], 'integer');
        $this->db->manipulate($q);
    }

    protected function deleteDB(int $id)
    {
        $this->db->manipulate('DELETE FROM ' . self::TABLE . ' WHERE ' . self::FIELD_ID . ' = ' . $this->db->quote($id, 'integer'));
    }

    protected function nextId()
    {
        return $this->db->nextId(self::TABLE);
    }


    public function deleteAllAssignmentsForProgrammeId(int $prg_obj_id) : void
    {
        $query = 'DELETE FROM ' . self::TABLE . PHP_EOL
            . 'WHERE ' . self::FIELD_ROOT_PRG_ID . '=' . $this->db->quote($prg_obj_id, 'integer');
        $this->db->manipulate($query);
    }

    public function getTableAndFieldOfAssignmentIds() : array
    {
        return  [self::TABLE, self::FIELD_ID];
    }


    /**
      * ------------------------------------------------------------------------
      * Backport ilStudyProgrammeUserAssignmentDB
      * ------------------------------------------------------------------------
      */

    public function getInstanceById(int $id)
    {
        return $this->get($id);
    }

    public function getInstanceByModel(\ilStudyProgrammeAssignment $assignment)
    {
        return $assignment;
    }

    public function getInstancesOfUser(int $user_id)
    {
        global $DIC;
        $tree = $DIC['tree'];

        $assignments = $this->getByUsrId($user_id);

        //if parent object is deleted or in trash
        //the assignment for the user should not be returned
        $ret = [];
        foreach ($assignments as $ass) {
            foreach (ilObject::_getAllReferences($ass->getRootId()) as $value) {
                if ($tree->isInTree($value)) {
                    $ret[] = $ass;
                    continue 2;
                }
            }
        }
        return $ret;
    }
}
