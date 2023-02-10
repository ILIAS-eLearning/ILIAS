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

/**
 * Assignments are relations of users to a PRG;
 * They hold progress-information for (sub-)nodes of the PRG-tree.
 */
class ilPRGAssignmentDBRepository implements PRGAssignmentRepository
{
    public const ASSIGNMENT_TABLE = 'prg_usr_assignments';
    public const ASSIGNMENT_FIELD_ID = 'id';
    public const ASSIGNMENT_FIELD_USR_ID = 'usr_id';
    public const ASSIGNMENT_FIELD_ROOT_PRG_ID = 'root_prg_id';
    public const ASSIGNMENT_FIELD_LAST_CHANGE = 'last_change';
    public const ASSIGNMENT_FIELD_LAST_CHANGE_BY = 'last_change_by';
    public const ASSIGNMENT_FIELD_RESTART_DATE = 'restart_date';
    public const ASSIGNMENT_FIELD_RESTARTED_ASSIGNMENT_ID = 'restarted_assignment_id';
    public const ASSIGNMENT_FIELD_RESTART_MAIL = 'restart_mail_send';
    public const ASSIGNMENT_FIELD_MANUALLY_ASSIGNED = 'assigned_manually';

    public const PROGRESS_TABLE = 'prg_usr_progress';
    public const PROGRESS_FIELD_ASSIGNMENT_ID = 'assignment_id';
    public const PROGRESS_FIELD_USR_ID = 'usr_id';
    public const PROGRESS_FIELD_PRG_ID = 'prg_id';
    public const PROGRESS_FIELD_POINTS = 'points';
    public const PROGRESS_FIELD_POINTS_CUR = 'points_cur';
    public const PROGRESS_FIELD_STATUS = 'status';
    public const PROGRESS_FIELD_COMPLETION_BY = 'completion_by';
    public const PROGRESS_FIELD_ASSIGNMENT_DATE = 'assignment_date';
    public const PROGRESS_FIELD_LAST_CHANGE = 'last_change'; //'p_' .
    public const PROGRESS_FIELD_LAST_CHANGE_BY = 'last_change_by'; //'p_' .
    public const PROGRESS_FIELD_COMPLETION_DATE = 'completion_date';
    public const PROGRESS_FIELD_DEADLINE = 'deadline';
    public const PROGRESS_FIELD_VQ_DATE = 'vq_date';
    public const PROGRESS_FIELD_INVALIDATED = 'invalidated';
    public const PROGRESS_FIELD_MAIL_SENT_RISKYTOFAIL = 'sent_mail_risky_to_fail';
    public const PROGRESS_FIELD_MAIL_SENT_WILLEXPIRE = 'sent_mail_expires';
    public const PROGRESS_FIELD_IS_INDIVIDUAL = 'individual';

    public const DATE_FORMAT_ENDOFDAY = 'Y-m-d 23:59:59';

    protected ilDBInterface $db;
    protected ilTree $tree;
    protected ilStudyProgrammeSettingsRepository $settings_repo;
    /**
     * <id => ilPRGProgress>
     */
    protected array $progresses = [];
    protected StudyProgrammeEvents $events;

    public function __construct(
        ilDBInterface $db,
        ilTree $tree,
        ilStudyProgrammeSettingsRepository $settings_repo,
        PRGEventsDelayed $events
    ) {
        $this->db = $db;
        $this->tree = $tree;
        $this->settings_repo = $settings_repo;
        $this->events = $events;
    }

    public function getDashboardInstancesforUser(int $usr_id): array
    {
        $assignments = $this->getForUser($usr_id);
        //TODO: decide, which ones are relevant for the dashboard
        return $assignments;
    }

    public function createFor(
        int $prg_obj_id,
        int $usr_id,
        int $assigning_usr_id
    ): ilPRGAssignment {
        $manually = false;
        if (ilObject::_lookupType($assigning_usr_id) === "usr") {
            $manually = true;
        }
        $row = [
            self::ASSIGNMENT_FIELD_ID => $this->nextId(),
            self::ASSIGNMENT_FIELD_USR_ID => $usr_id,
            self::ASSIGNMENT_FIELD_ROOT_PRG_ID => $prg_obj_id,
            self::ASSIGNMENT_FIELD_LAST_CHANGE_BY => $assigning_usr_id,
            self::ASSIGNMENT_FIELD_LAST_CHANGE => ilUtil::now(),
            self::ASSIGNMENT_FIELD_RESTART_DATE => null,
            self::ASSIGNMENT_FIELD_RESTARTED_ASSIGNMENT_ID => ilPRGAssignment::NO_RESTARTED_ASSIGNMENT,
            self::ASSIGNMENT_FIELD_MANUALLY_ASSIGNED => $manually
        ];
        $this->insertAssignmentRowDB($row);
        $this->progresses = [];

        //add user_colums : ilPRGUserInformation::COLNAMES
        $query = 'SELECT ' . implode(' ,', ilPRGUserInformation::COLNAMES) . PHP_EOL
            . 'FROM usr_data WHERE usr_id = ' . $this->db->quote($usr_id, 'integer');
        $res = $this->db->query($query);
        $row = array_merge($row, $this->db->fetchAssoc($res));


        $ass = $this->assignmentByRow($row);
        return $ass;
    }

    public function store(ilPRGAssignment $assignment): void
    {
        $row = [
            self::ASSIGNMENT_FIELD_ID => $assignment->getId(),
            self::ASSIGNMENT_FIELD_USR_ID => $assignment->getUserId(),
            self::ASSIGNMENT_FIELD_ROOT_PRG_ID => $assignment->getRootId(),
            self::ASSIGNMENT_FIELD_LAST_CHANGE_BY => $assignment->getLastChangeBy(),
            self::ASSIGNMENT_FIELD_LAST_CHANGE => $assignment->getLastChange()->format(ilPRGAssignment::DATE_TIME_FORMAT),
            self::ASSIGNMENT_FIELD_RESTART_DATE => $assignment->getRestartDate() ? $assignment->getRestartDate()->format(ilPRGAssignment::DATE_TIME_FORMAT) : null,
            self::ASSIGNMENT_FIELD_RESTARTED_ASSIGNMENT_ID => $assignment->getRestartedAssignmentId(),
            self::ASSIGNMENT_FIELD_MANUALLY_ASSIGNED => $assignment->isManuallyAssigned()
        ];
        $this->updateAssignmentRowDB($row);
        foreach ($assignment->getProgresses() as $pgs) {
            $this->storeProgressRow(
                $assignment->getId(),
                $assignment->getUserId(),
                $pgs
            );
        }

        $this->events->raiseCollected();
    }

    public function delete(ilPRGAssignment $assignment): void
    {
        $ass_id = $assignment->getId();
        $query = 'DELETE FROM ' . self::ASSIGNMENT_TABLE . PHP_EOL
         . 'WHERE ' . self::ASSIGNMENT_FIELD_ID . ' = ' . $ass_id;
        $this->db->manipulate($query);

        $query = 'DELETE FROM ' . self::PROGRESS_TABLE . PHP_EOL
         . 'WHERE ' . self::PROGRESS_FIELD_ASSIGNMENT_ID . ' = ' . $ass_id;
        $this->db->manipulate($query);
    }

    public function deleteAllAssignmentsForProgrammeId(int $prg_obj_id): void
    {
        $query = 'DELETE FROM ' . self::ASSIGNMENT_TABLE . PHP_EOL
            . 'WHERE ' . self::ASSIGNMENT_FIELD_ROOT_PRG_ID . '=' . $this->db->quote($prg_obj_id, 'integer');
        $this->db->manipulate($query);
        $this->deleteAllOrphanedProgresses();
    }
    protected function deleteAllOrphanedProgresses(): void
    {
        $query = 'DELETE FROM ' . self::PROGRESS_TABLE . PHP_EOL
            . 'WHERE ' . self::PROGRESS_FIELD_ASSIGNMENT_ID . PHP_EOL
            . 'NOT IN (' . PHP_EOL
            . 'SELECT ' . $this->db->quoteIdentifier(self::ASSIGNMENT_FIELD_ID)
            . ' FROM ' . $this->db->quoteIdentifier(self::ASSIGNMENT_TABLE) . PHP_EOL
            . ');' . PHP_EOL;
        $this->db->manipulate($query);
    }


    public function get(int $id): ilPRGAssignment
    {
        $ass = $this->read([
            'ass.' . self::ASSIGNMENT_FIELD_ID . ' = ' . $this->db->quote($id, 'integer')
        ]);

        return $ass->current();
    }

    public function getForUser(int $usr_id): array
    {
        $assignments = array_filter(iterator_to_array(
            $this->read([
                'ass.' . self::ASSIGNMENT_FIELD_USR_ID . ' = ' . $this->db->quote($usr_id, 'integer')
            ])
        ));
        return $assignments;
    }

    public function getAllForNodeIsContained(
        int $prg_obj_id,
        array $user_filter = null,
        ilPRGAssignmentFilter $custom_filters = null
    ): array {
        $conditions = [
            'pgs.' . self::PROGRESS_FIELD_PRG_ID . ' = ' . $this->db->quote($prg_obj_id, 'integer')
        ];
        if ($user_filter) {
            $conditions[] = $this->db->in('ass.' . self::ASSIGNMENT_FIELD_USR_ID, $user_filter, false, 'integer');
        }
        if ($custom_filters) {
            $conditions = array_merge($conditions, $custom_filters->toConditions());
        }

        $assignments = array_filter(iterator_to_array(
            $this->read($conditions)
        ));
        return $assignments;
    }

    public function countAllForNodeIsContained(
        int $prg_obj_id,
        array $user_filter = null,
        ilPRGAssignmentFilter $custom_filters = null
    ): int {
        $conditions = [
            'pgs.' . self::PROGRESS_FIELD_PRG_ID . ' = ' . $this->db->quote($prg_obj_id, 'integer')
        ];
        if ($user_filter) {
            $conditions[] = $this->db->in('ass.' . self::ASSIGNMENT_FIELD_USR_ID, $user_filter, false, 'integer');
        }
        if ($custom_filters) {
            $conditions = array_merge($conditions, $custom_filters->toConditions());
        }
        return $this->count($conditions);
    }

    public function getAllForSpecificNode(int $prg_obj_id, array $user_filter = null): array
    {
        $conditions = [
            self::ASSIGNMENT_FIELD_ROOT_PRG_ID . ' = ' . $this->db->quote($prg_obj_id, 'integer')
        ];
        if ($user_filter) {
            $conditions[] = $this->db->in('ass.' . self::ASSIGNMENT_FIELD_USR_ID, $user_filter, false, 'integer');
        }

        $assignments = array_filter(iterator_to_array(
            $this->read($conditions)
        ));
        return $assignments;
    }

    public function getPassedDeadline(\DateTimeImmutable $deadline): array
    {
        $deadline = $this->db->quote(
            $deadline->format(ilPRGProgress::DATE_FORMAT),
            'text'
        );

        $conditions = [
            $this->db->in(
                self::PROGRESS_FIELD_STATUS,
                [
                    ilPRGProgress::STATUS_IN_PROGRESS,
                    ilPRGProgress::STATUS_ACCREDITED
                ],
                false,
                'integer'
            ),
            self::PROGRESS_FIELD_DEADLINE . ' IS NOT NULL',
            self::PROGRESS_FIELD_DEADLINE . ' < ' . $deadline
        ];

        $assignments = array_filter(iterator_to_array(
            $this->read($conditions)
        ));
        return $assignments;
    }

    public function getAboutToExpire(
        array $programmes_and_due,
        bool $discard_formerly_notified = true
    ): array {
        $ret = [];
        if (count($programmes_and_due) == 0) {
            return $ret;
        }

        $where = [];
        foreach ($programmes_and_due as $prg_obj_id => $due) {
            $due = $due->format(self::DATE_FORMAT_ENDOFDAY);

            $where_clause = '('
                . self::PROGRESS_FIELD_VQ_DATE . '<=' . $this->db->quote($due, 'text')
                . ' AND (pgs.' . self::PROGRESS_FIELD_PRG_ID . '=' . $prg_obj_id
                . ' OR ' . self::ASSIGNMENT_FIELD_ROOT_PRG_ID . '=' . $prg_obj_id . ')';

            if ($discard_formerly_notified) {
                $where_clause .= ' AND ' . self::PROGRESS_FIELD_MAIL_SENT_WILLEXPIRE . ' IS NULL';
            }

            $where_clause .= ')';
            $where[] = $where_clause;
        }

        $conditions = [
            implode(' OR ', $where)
        ];
        $assignments = array_filter(iterator_to_array(
            $this->read($conditions)
        ));
        return $assignments;
    }

    public function getExpiredAndNotInvalidated(): array
    {
        $now = (new \DateTimeImmutable())->format(self::DATE_FORMAT_ENDOFDAY);
        $conditions = [
            $this->db->in(
                self::PROGRESS_FIELD_STATUS,
                [
                    ilPRGProgress::STATUS_COMPLETED,
                    ilPRGProgress::STATUS_ACCREDITED
                ],
                false,
                'integer'
            ),
            self::PROGRESS_FIELD_VQ_DATE . ' IS NOT NULL',
            self::PROGRESS_FIELD_VQ_DATE . ' < ' . $this->db->quote($now, 'text'),
            self::PROGRESS_FIELD_INVALIDATED . ' = 0 ',
        ];

        $assignments = array_filter(iterator_to_array(
            $this->read($conditions)
        ));
        return $assignments;
    }


    public function getRiskyToFail(
        array $programmes_and_due,
        bool $discard_formerly_notified = true
    ): array {
        $ret = [];
        if (count($programmes_and_due) == 0) {
            return $ret;
        }

        $where = [];
        foreach ($programmes_and_due as $prg_obj_id => $due) {
            $due = $due->format(ilPRGProgress::DATE_FORMAT);

            $where_clause = '('
                . self::PROGRESS_FIELD_DEADLINE . '<=' . $this->db->quote($due, 'text')
                . 'AND (pgs.' . self::PROGRESS_FIELD_PRG_ID . '=' . $prg_obj_id
                . ' OR ' . self::ASSIGNMENT_FIELD_ROOT_PRG_ID . '=' . $prg_obj_id . ')'
                . ' AND ' . $this->db->in(
                    self::PROGRESS_FIELD_STATUS,
                    [
                        ilPRGProgress::STATUS_ACCREDITED,
                        ilPRGProgress::STATUS_COMPLETED,
                        ilPRGProgress::STATUS_NOT_RELEVANT
                    ],
                    true,
                    'integer'
                );

            if ($discard_formerly_notified) {
                $where_clause .= ' AND ' . self::PROGRESS_FIELD_MAIL_SENT_RISKYTOFAIL . ' IS NULL';
            }

            $where_clause .= ')';
            $where[] = $where_clause;
        }

        $conditions = [
            implode(' OR ', $where)
        ];
        $assignments = array_filter(iterator_to_array(
            $this->read($conditions)
        ));
        return $assignments;
    }



    protected function query($filter): ilDBStatement
    {
        $q = 'SELECT'
            . '  ass.' . self::ASSIGNMENT_FIELD_ID . ' AS ' . self::ASSIGNMENT_FIELD_ID
            . ', ass.' . self::ASSIGNMENT_FIELD_USR_ID . ' AS ' . self::ASSIGNMENT_FIELD_USR_ID
            . ',' . self::ASSIGNMENT_FIELD_ROOT_PRG_ID
            . ', ass.' . self::ASSIGNMENT_FIELD_LAST_CHANGE
            . ', ass.' . self::ASSIGNMENT_FIELD_LAST_CHANGE_BY
            . ',' . self::ASSIGNMENT_FIELD_RESTART_DATE
            . ',' . self::ASSIGNMENT_FIELD_RESTARTED_ASSIGNMENT_ID
            . ',' . self::ASSIGNMENT_FIELD_MANUALLY_ASSIGNED
            . ',' . self::PROGRESS_FIELD_PRG_ID
            . ',' . self::PROGRESS_FIELD_POINTS
            . ',' . self::PROGRESS_FIELD_POINTS_CUR
            . ',' . self::PROGRESS_FIELD_STATUS
            . ',' . self::PROGRESS_FIELD_COMPLETION_BY
            . ',' . self::PROGRESS_FIELD_ASSIGNMENT_DATE
            . ', pgs.' . self::PROGRESS_FIELD_LAST_CHANGE . ' AS p_' . self::PROGRESS_FIELD_LAST_CHANGE
            . ', pgs.' . self::PROGRESS_FIELD_LAST_CHANGE_BY . ' AS p_' . self::PROGRESS_FIELD_LAST_CHANGE_BY
            . ',' . self::PROGRESS_FIELD_COMPLETION_DATE
            . ',' . self::PROGRESS_FIELD_DEADLINE
            . ',' . self::PROGRESS_FIELD_VQ_DATE
            . ',' . self::PROGRESS_FIELD_INVALIDATED
            . ',' . self::PROGRESS_FIELD_MAIL_SENT_RISKYTOFAIL
            . ',' . self::PROGRESS_FIELD_MAIL_SENT_WILLEXPIRE
            . ',' . self::PROGRESS_FIELD_IS_INDIVIDUAL

            . ', ' . implode(', ', ilPRGUserInformation::COLNAMES)

            . ' FROM ' . self::ASSIGNMENT_TABLE . ' ass '
            . ' JOIN ' . self::PROGRESS_TABLE . ' pgs '
            . ' ON ass.' . self::ASSIGNMENT_FIELD_ID . ' = pgs.' . self::PROGRESS_FIELD_ASSIGNMENT_ID


            . ' JOIN usr_data memberdata ON ass.usr_id = memberdata.usr_id '

            . ' WHERE TRUE AND ';
        $q = $q . implode(' AND ', $filter);
        $q = $q . ' ORDER BY assignment_id, ass.usr_id';

        $res = $this->db->query($q);
        return $res;
    }

    protected function nextId()
    {
        return $this->db->nextId(self::ASSIGNMENT_TABLE);
    }

    protected function count(array $filter): int
    {
        $res = $this->query($filter);
        return $this->db->numRows($res);
    }

    protected function read(array $filter): Generator
    {
        $res = $this->query($filter);

        $current_ass = -1;
        $ass = null;

        while ($row = $this->db->fetchAssoc($res)) {
            if ($row[self::ASSIGNMENT_FIELD_ID] !== $current_ass) {
                $current_ass = $row[self::ASSIGNMENT_FIELD_ID];
                if (!is_null($ass)) {
                    yield $ass;
                }
                $this->progresses = $this->prebuildProgressesForAssingment((int) $row[self::ASSIGNMENT_FIELD_ID]);
                $ass = $this->assignmentByRow($row); //amend all progresses based on tree
            }
        }

        yield $ass;
    }


    protected function prebuildProgressesForAssingment(int $assignment_id): array
    {
        $q = 'SELECT * FROM ' . self::PROGRESS_TABLE
            . ' WHERE ' . self::PROGRESS_FIELD_ASSIGNMENT_ID . ' = ' . $assignment_id;
        $res = $this->db->query($q);
        while ($row = $this->db->fetchAssoc($res)) {
            $ret[$row[self::PROGRESS_FIELD_PRG_ID]] = $this->buildProgressByRow($row);
        }
        return $ret;
    }

    protected function assignmentByRow(array $row): ilPRGAssignment
    {
        $ass = new ilPRGAssignment(
            (int) $row[self::ASSIGNMENT_FIELD_ID],
            (int) $row[self::ASSIGNMENT_FIELD_USR_ID]
        );
        $ass = $ass
            ->withEvents($this->events)
            ->withLastChange(
                (int) $row[self::ASSIGNMENT_FIELD_LAST_CHANGE_BY],
                \DateTimeImmutable::createFromFormat(
                    ilPRGAssignment::DATE_TIME_FORMAT,
                    $row[self::ASSIGNMENT_FIELD_LAST_CHANGE]
                )
            )
            ->withRestarted(
                (int) $row[self::ASSIGNMENT_FIELD_RESTARTED_ASSIGNMENT_ID],
                $row[self::ASSIGNMENT_FIELD_RESTART_DATE] ?
                    \DateTimeImmutable::createFromFormat(ilPRGAssignment::DATE_TIME_FORMAT, $row[self::ASSIGNMENT_FIELD_RESTART_DATE]) :
                    null
            )
            ->withManuallyAssigned((bool) $row[self::ASSIGNMENT_FIELD_MANUALLY_ASSIGNED]);


        $root_pgs_id = (int) $row[self::ASSIGNMENT_FIELD_ROOT_PRG_ID];
        $pgs = $this->buildProgressTreeFor($root_pgs_id);

        $user_information = $this->buildUserInformation($row);

        $ass = $ass
            ->withProgressTree($pgs)
            ->withUserInformation($user_information);
        return $ass;
    }


    protected function buildProgressTreeFor(int $node_obj_id): ilPRGProgress
    {
        $children = array_filter(
            $this->tree->getChilds($this->getRefIdFor($node_obj_id)),
            fn ($c) => in_array($c['type'], ['prg', 'prgr']),
        );
        $children = array_map(
            fn ($c) => $c['type'] === 'prg' ? (int) $c['obj_id'] : ilContainerReference::_lookupTargetId((int) $c['obj_id']),
            $children
        );

        $pgss = [];
        foreach ($children as $child_obj_id) {
            $pgss[] = $this->buildProgressTreeFor($child_obj_id);
        }

        if (!array_key_exists($node_obj_id, $this->progresses)) {
            $pgs = new ilPRGProgress((int) $node_obj_id);
        } else {
            $pgs = $this->progresses[$node_obj_id];
        }
        $pgs->setSubnodes($pgss);
        return $pgs;
    }


    protected function getRefIdFor(int $obj_id): int
    {
        $refs = ilObject::_getAllReferences($obj_id);
        if (count($refs) < 1) {
            throw new ilException("Could not find ref_id for programme with obj_id $obj_id");
        }
        return (int) array_shift($refs);
    }
    protected function getObjIdFor(int $ref_id): int
    {
        return (int) ilObject::_lookupObjectId($ref_id);
    }

    protected function buildProgressByRow(array $row): ilPRGProgress
    {
        $pgs = new ilPRGProgress(
            (int) $row[self::PROGRESS_FIELD_PRG_ID],
            (int) $row[self::PROGRESS_FIELD_STATUS]
        );

        $pgs = $pgs
            ->withAmountOfPoints((int) $row[self::PROGRESS_FIELD_POINTS])
            ->withCurrentAmountOfPoints((int) $row[self::PROGRESS_FIELD_POINTS_CUR])
            ->withAssignmentDate(
                $row[self::PROGRESS_FIELD_ASSIGNMENT_DATE] ?
                    \DateTimeImmutable::createFromFormat(ilPRGProgress::DATE_TIME_FORMAT, $row[self::PROGRESS_FIELD_ASSIGNMENT_DATE]) :
                    null
            )
            ->withDeadline(
                $row[self::PROGRESS_FIELD_DEADLINE] ?
                    \DateTimeImmutable::createFromFormat(ilPRGProgress::DATE_FORMAT, $row[self::PROGRESS_FIELD_DEADLINE]) :
                    null
            )
            ->withCompletion(
                (int) $row[self::PROGRESS_FIELD_COMPLETION_BY],
                $row[self::PROGRESS_FIELD_COMPLETION_DATE] ?
                    \DateTimeImmutable::createFromFormat(ilPRGProgress::DATE_TIME_FORMAT, $row[self::PROGRESS_FIELD_COMPLETION_DATE]) :
                    null
            )
            ->withLastChange(
                (int) $row[self::PROGRESS_FIELD_LAST_CHANGE_BY],
                $row[self::PROGRESS_FIELD_LAST_CHANGE] ?
                    \DateTimeImmutable::createFromFormat(ilPRGProgress::DATE_TIME_FORMAT, $row[self::PROGRESS_FIELD_LAST_CHANGE]) :
                    null
            )
            ->withValidityOfQualification(
                $row[self::PROGRESS_FIELD_VQ_DATE] ?
                    \DateTimeImmutable::createFromFormat(ilPRGProgress::DATE_TIME_FORMAT, $row[self::PROGRESS_FIELD_VQ_DATE]) :
                    null
            )
            ->withIndividualModifications((bool) $row[self::PROGRESS_FIELD_IS_INDIVIDUAL])
            ->withInvalidated((bool) $row[self::PROGRESS_FIELD_INVALIDATED]);

        return $pgs;
    }


    /**
     * @deprecated; fix ilObjUser::lookupOrgUnitsRepresentation
     */
    protected function interimOrguLookup(int $usr_id): string
    {
        $orgu_repo = OrgUnit\Positions\UserAssignment\ilOrgUnitUserAssignmentRepository::getInstance();
        $orgus = array_values($orgu_repo->findAllUserAssingmentsByUserIds([$usr_id]));
        if ($orgus) {
            $orgu_ref_ids =  array_map(
                fn ($orgu_assignment) => $orgu_assignment->getOrguId(),
                $orgus[0]
            );
            $orgus = array_map(
                fn ($orgu_ref_id) => ilObject::_lookupTitle(ilObject::_lookupObjId($orgu_ref_id)),
                $orgu_ref_ids
            );
        }
        return implode(', ', $orgus);
    }

    protected function buildUserInformation(array $row): ilPRGUserInformation
    {
        $udf_data = new ilUserDefinedData((int) $row[self::ASSIGNMENT_FIELD_USR_ID]);
        $orgu_repr = ilObjUser::lookupOrgUnitsRepresentation((int) $row[self::ASSIGNMENT_FIELD_USR_ID]);

        $orgu_repr = $this->interimOrguLookup((int) $row[self::ASSIGNMENT_FIELD_USR_ID]);

        return new ilPRGUserInformation(
            $udf_data,
            $orgu_repr,
            (string) $row['firstname'],
            (string) $row['lastname'],
            (string) $row['login'],
            (bool) $row['active'],
            (string) $row['email'],
            (string) $row['gender'],
            (string) $row['title']
        );
    }

    protected function insertAssignmentRowDB(array $row)
    {
        $this->db->insert(
            self::ASSIGNMENT_TABLE,
            [
                self::ASSIGNMENT_FIELD_ID => ['integer', $row[self::ASSIGNMENT_FIELD_ID]]
                , self::ASSIGNMENT_FIELD_USR_ID => ['integer', $row[self::ASSIGNMENT_FIELD_USR_ID]]
                , self::ASSIGNMENT_FIELD_ROOT_PRG_ID => ['integer', $row[self::ASSIGNMENT_FIELD_ROOT_PRG_ID]]
                , self::ASSIGNMENT_FIELD_LAST_CHANGE => ['text', $row[self::ASSIGNMENT_FIELD_LAST_CHANGE]]
                , self::ASSIGNMENT_FIELD_LAST_CHANGE_BY => ['integer', $row[self::ASSIGNMENT_FIELD_LAST_CHANGE_BY]]
                , self::ASSIGNMENT_FIELD_RESTART_DATE => ['timestamp', $row[self::ASSIGNMENT_FIELD_RESTART_DATE]]
                , self::ASSIGNMENT_FIELD_RESTARTED_ASSIGNMENT_ID => ['integer', $row[self::ASSIGNMENT_FIELD_RESTARTED_ASSIGNMENT_ID]]
                , self::ASSIGNMENT_FIELD_MANUALLY_ASSIGNED => ['integer', $row[self::ASSIGNMENT_FIELD_MANUALLY_ASSIGNED]]
            ]
        );
    }

    protected function updateAssignmentRowDB(array $values)
    {
        $q = 'UPDATE ' . self::ASSIGNMENT_TABLE
            . ' SET'
            . ' ' . self::ASSIGNMENT_FIELD_USR_ID . ' = ' . $this->db->quote($values[self::ASSIGNMENT_FIELD_USR_ID], 'integer')
            . ' ,' . self::ASSIGNMENT_FIELD_ROOT_PRG_ID . ' = ' . $this->db->quote($values[self::ASSIGNMENT_FIELD_ROOT_PRG_ID], 'integer')
            . ' ,' . self::ASSIGNMENT_FIELD_LAST_CHANGE . ' = ' . $this->db->quote($values[self::ASSIGNMENT_FIELD_LAST_CHANGE], 'text')
            . ' ,' . self::ASSIGNMENT_FIELD_LAST_CHANGE_BY . ' = ' . $this->db->quote($values[self::ASSIGNMENT_FIELD_LAST_CHANGE_BY], 'integer')
            . ' ,' . self::ASSIGNMENT_FIELD_RESTART_DATE . ' = ' . $this->db->quote($values[self::ASSIGNMENT_FIELD_RESTART_DATE], 'timestamp')
            . ' ,' . self::ASSIGNMENT_FIELD_RESTARTED_ASSIGNMENT_ID . ' = ' . $this->db->quote($values[self::ASSIGNMENT_FIELD_RESTARTED_ASSIGNMENT_ID], 'integer')
            . ' ,' . self::ASSIGNMENT_FIELD_MANUALLY_ASSIGNED . ' = ' . $this->db->quote($values[self::ASSIGNMENT_FIELD_MANUALLY_ASSIGNED], 'integer')
            . ' WHERE ' . self::ASSIGNMENT_FIELD_ID . ' = ' . $this->db->quote($values[self::ASSIGNMENT_FIELD_ID], 'integer');
        $this->db->manipulate($q);
    }

    protected function storeProgressRow(
        int $assignment_id,
        int $usr_id,
        ilPRGProgress $pgs
    ) {
        //TODO: move into type?
        $lastchange = is_null($pgs->getLastChange()) ? 'NULL' : $this->db->quote($pgs->getLastChange()->format(ilPRGProgress::DATE_TIME_FORMAT), 'text');
        $assign_date = is_null($pgs->getAssignmentDate()) ? 'NULL' : $this->db->quote($pgs->getAssignmentDate()->format(ilPRGProgress::DATE_TIME_FORMAT), 'text');
        $completion_date = is_null($pgs->getCompletionDate()) ? 'NULL' : $this->db->quote($pgs->getCompletionDate()->format(ilPRGProgress::DATE_TIME_FORMAT), 'text');
        $deadline = is_null($pgs->getDeadline()) ? 'NULL' : $this->db->quote($pgs->getDeadline()->format(ilPRGProgress::DATE_FORMAT), 'text');
        $validity = is_null($pgs->getValidityOfQualification()) ? 'NULL' : $this->db->quote($pgs->getValidityOfQualification()->format(ilPRGProgress::DATE_FORMAT), 'text');
        $invalidated = $pgs->isInvalidated() ? 1 : 0;
        $individual = $pgs->hasIndividualModifications() ? 1 : 0;
        $completion = $pgs->getCompletionBy() ?? 'NULL';

        $q = 'INSERT INTO ' . self::PROGRESS_TABLE
            . '('
            . self::PROGRESS_FIELD_ASSIGNMENT_ID . ','
            . self::PROGRESS_FIELD_USR_ID . ','
            . self::PROGRESS_FIELD_PRG_ID . ','

            . self::PROGRESS_FIELD_STATUS . ','
            . self::PROGRESS_FIELD_POINTS . ','
            . self::PROGRESS_FIELD_POINTS_CUR . ','
            . self::PROGRESS_FIELD_COMPLETION_BY . ','
            . self::PROGRESS_FIELD_LAST_CHANGE_BY . ','
            . self::PROGRESS_FIELD_LAST_CHANGE . ','
            . self::PROGRESS_FIELD_ASSIGNMENT_DATE . ','
            . self::PROGRESS_FIELD_COMPLETION_DATE . ','
            . self::PROGRESS_FIELD_DEADLINE . ','
            . self::PROGRESS_FIELD_VQ_DATE . ','
            . self::PROGRESS_FIELD_INVALIDATED . ','
            . self::PROGRESS_FIELD_IS_INDIVIDUAL

            . PHP_EOL . ') VALUES (' . PHP_EOL
            . $assignment_id
            . ' ,' . $usr_id
            . ' ,' . $pgs->getNodeId()

            . ' ,' . $pgs->getStatus()
            . ' ,' . $pgs->getAmountOfPoints()
            . ' ,' . $pgs->getCurrentAmountOfPoints()
            . ' ,' . $completion
            . ' ,' . $pgs->getLastChangeBy()
            . ' ,' . $lastchange
            . ' ,' . $assign_date
            . ' ,' . $completion_date
            . ' ,' . $deadline
            . ' ,' . $validity
            . ' ,' . $invalidated
            . ' ,' . $individual
            . ')' . PHP_EOL
            . 'ON DUPLICATE KEY UPDATE' . PHP_EOL
            . self::PROGRESS_FIELD_STATUS . '=' . $pgs->getStatus() . ','
            . self::PROGRESS_FIELD_POINTS . '=' . $pgs->getAmountOfPoints() . ','
            . self::PROGRESS_FIELD_POINTS_CUR . '=' . $pgs->getCurrentAmountOfPoints() . ','
            . self::PROGRESS_FIELD_COMPLETION_BY . '=' . $completion . ','
            . self::PROGRESS_FIELD_LAST_CHANGE_BY . '=' . $pgs->getLastChangeBy() . ','
            . self::PROGRESS_FIELD_LAST_CHANGE . '=' . $lastchange . ','
            . self::PROGRESS_FIELD_ASSIGNMENT_DATE . '=' . $assign_date . ','
            . self::PROGRESS_FIELD_COMPLETION_DATE . '=' . $completion_date . ','
            . self::PROGRESS_FIELD_DEADLINE . '=' . $deadline . ','
            . self::PROGRESS_FIELD_VQ_DATE . '=' . $validity . ','
            . self::PROGRESS_FIELD_INVALIDATED . '=' . $invalidated . ','
            . self::PROGRESS_FIELD_IS_INDIVIDUAL . '=' . $individual
            ;
        $this->db->manipulate($q);
    }


    public function storeExpiryInfoSentFor(ilPRGAssignment $ass): void
    {
        $where = [
            self::PROGRESS_FIELD_ASSIGNMENT_ID => ['integer', $ass->getId()],
            self::PROGRESS_FIELD_PRG_ID => ['integer', $ass->getRootId()]
        ];

        $values = [
            self::PROGRESS_FIELD_MAIL_SENT_WILLEXPIRE => [
                'timestamp',
                date('Y-m-d H:i:s')
            ]
        ];
        $this->db->update(self::PROGRESS_TABLE, $values, $where);
    }

    public function resetExpiryInfoSentFor(ilPRGAssignment $ass): void
    {
        $where = [
            self::PROGRESS_FIELD_ASSIGNMENT_ID => ['integer', $ass->getId()],
            self::PROGRESS_FIELD_PRG_ID => ['integer', $ass->getRootId()]
        ];

        $values = [
            self::PROGRESS_FIELD_MAIL_SENT_WILLEXPIRE => ['null', null]
        ];
        $this->db->update(self::PROGRESS_TABLE, $values, $where);
    }

    public function storeRiskyToFailSentFor(ilPRGAssignment $ass): void
    {
        $where = [
            self::PROGRESS_FIELD_ASSIGNMENT_ID => ['integer', $ass->getId()],
            self::PROGRESS_FIELD_PRG_ID => ['integer', $ass->getRootId()]
        ];

        $values = [
            self::PROGRESS_FIELD_MAIL_SENT_RISKYTOFAIL => [
                'timestamp',
                date('Y-m-d H:i:s')
            ]
        ];
        $this->db->update(self::PROGRESS_TABLE, $values, $where);
    }

    public function resetRiskyToFailSentFor(ilPRGAssignment $ass): void
    {
        $where = [
            self::PROGRESS_FIELD_ASSIGNMENT_ID => ['integer', $ass->getId()],
            self::PROGRESS_FIELD_PRG_ID => ['integer', $ass->getRootId()]
        ];

        $values = [
            self::PROGRESS_FIELD_MAIL_SENT_RISKYTOFAIL => ['null', null]
        ];
        $this->db->update(self::PROGRESS_TABLE, $values, $where);
    }
}
