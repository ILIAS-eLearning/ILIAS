<?php declare(strict_types=1);

/* Copyright (c) 2021 - Denis Klöpfer <denis.kloepfer@concepts-and-training.de> - Extended GPL, see LICENSE */
/* Copyright (c) 2021 - Stefan Hecken <stefan.hecken@concepts-and-training.de> - Extended GPL, see LICENSE */

/**
 * Store member infos to DB
 */
class ilIndividualAssessmentMembersStorageDB implements ilIndividualAssessmentMembersStorage
{
    const MEMBERS_TABLE = "iass_members";

    protected ilDBInterface $db;

    public function __construct(ilDBInterface $ilDB)
    {
        $this->db = $ilDB;
    }

    /**
     * @inheritdoc
     */
    public function loadMembers(ilObjIndividualAssessment $obj) : ilIndividualAssessmentMembers
    {
        $members = new ilIndividualAssessmentMembers($obj);
        $obj_id = $obj->getId();
        $sql = $this->loadMembersQuery($obj_id);
        $res = $this->db->query($sql);
        while ($rec = $this->db->fetchAssoc($res)) {
            $members = $members->withAdditionalRecord($rec);
        }
        return $members;
    }

    /**
     * @inheritdoc
     */
    public function loadMembersAsSingleObjects(
        ilObjIndividualAssessment $obj,
        string $filter = null,
        string $sort = null
    ) : array {
        $members = [];
        $sql = $this->loadMemberQuery();
        $sql .= "	WHERE obj_id = " . $this->db->quote($obj->getId(), 'integer');

        if (!is_null($filter)) {
            $sql .= $this->getWhereFromFilter($filter);
        }

        if (!is_null($sort)) {
            $sql .= $this->getOrderByFromSort($sort);
        }
        $res = $this->db->query($sql);
        while ($rec = $this->db->fetchAssoc($res)) {
            $usr = new ilObjUser((int)$rec["usr_id"]);
            $members[] = $this->createAssessmentMember($obj, $usr, $rec);
        }
        return $members;
    }

    /**
     * @inheritdoc
     */
    public function loadMember(ilObjIndividualAssessment $obj, ilObjUser $usr) : ilIndividualAssessmentMember
    {
        $obj_id = $obj->getId();
        $usr_id = $usr->getId();
        $sql = $this->loadMemberQuery();
        $sql .= "	WHERE obj_id = " . $this->db->quote($obj_id, 'integer') . "\n"
            . "		AND iassme.usr_id = " . $this->db->quote($usr_id, 'integer');

        $rec = $this->db->fetchAssoc($this->db->query($sql));
        if ($rec) {
            return $this->createAssessmentMember($obj, $usr, $rec);
        } else {
            throw new ilIndividualAssessmentException("invalid usr-obj combination");
        }
    }

    protected function createAssessmentMember(
        ilObjIndividualAssessment $obj,
        ilObjUser $usr,
        array $record
    ) : ilIndividualAssessmentMember {
        $changer_id = $record[ilIndividualAssessmentMembers::FIELD_CHANGER_ID];
        if (!is_null($changer_id)) {
            $changer_id = (int) $changer_id;
        }
        $change_time = null;
        $change_time_db = $record[ilIndividualAssessmentMembers::FIELD_CHANGE_TIME];
        if (!is_null($change_time_db)) {
            $change_time = new DateTime($change_time_db);
        }
        $examiner_id = $record[ilIndividualAssessmentMembers::FIELD_EXAMINER_ID];
        if (!is_null($examiner_id)) {
            $examiner_id = (int) $examiner_id;
        }
        return new ilIndividualAssessmentMember(
            $obj,
            $usr,
            $this->createGrading($record, $usr->getFullname()),
            (int) $record[ilIndividualAssessmentMembers::FIELD_NOTIFICATION_TS],
            $examiner_id,
            $changer_id,
            $change_time
        );
    }

    protected function createGrading(array $record, string $user_fullname) : ilIndividualAssessmentUserGrading
    {
        $event_time = null;
        $event_time_db = $record[ilIndividualAssessmentMembers::FIELD_EVENTTIME];
        if (!is_null($event_time_db)) {
            $event_time = new DateTimeImmutable();
            $event_time = $event_time->setTimestamp($event_time_db);
        }
        return new ilIndividualAssessmentUserGrading(
            $user_fullname,
            (string) $record[ilIndividualAssessmentMembers::FIELD_RECORD],
            (string) $record[ilIndividualAssessmentMembers::FIELD_INTERNAL_NOTE],
            (string) $record[ilIndividualAssessmentMembers::FIELD_FILE_NAME],
            (bool) $record[ilIndividualAssessmentMembers::FIELD_USER_VIEW_FILE],
            (int) $record[ilIndividualAssessmentMembers::FIELD_LEARNING_PROGRESS],
            (string) $record[ilIndividualAssessmentMembers::FIELD_PLACE],
            $event_time,
            (bool) $record[ilIndividualAssessmentMembers::FIELD_NOTIFY],
            (bool) $record[ilIndividualAssessmentMembers::FIELD_FINALIZED]
        );
    }

    /**
     * @inheritdoc
     */
    public function updateMember(ilIndividualAssessmentMember $member) : void
    {
        $where = [
            "obj_id" => ["integer", $member->assessmentId()],
            "usr_id" => ["integer", $member->id()]
        ];

        $event_time = $member->eventTime();
        if (!is_null($event_time)) {
            $event_time = $event_time->getTimestamp();
        }

        $values = [
            ilIndividualAssessmentMembers::FIELD_LEARNING_PROGRESS => ["text", $member->LPStatus()],
            ilIndividualAssessmentMembers::FIELD_EXAMINER_ID => ["integer", $member->examinerId()],
            ilIndividualAssessmentMembers::FIELD_RECORD => ["text", $member->record()],
            ilIndividualAssessmentMembers::FIELD_INTERNAL_NOTE => ["text", $member->internalNote()],
            ilIndividualAssessmentMembers::FIELD_PLACE => ["text", $member->place()],
            ilIndividualAssessmentMembers::FIELD_EVENTTIME => ["integer", $event_time],
            ilIndividualAssessmentMembers::FIELD_NOTIFY => ["integer", $member->notify()],
            ilIndividualAssessmentMembers::FIELD_FINALIZED => ["integer", $member->finalized()],
            ilIndividualAssessmentMembers::FIELD_NOTIFICATION_TS => ["integer", $member->notificationTS()],
            ilIndividualAssessmentMembers::FIELD_FILE_NAME => ["text", $member->fileName()],
            ilIndividualAssessmentMembers::FIELD_USER_VIEW_FILE => ["integer", $member->viewFile()],
            ilIndividualAssessmentMembers::FIELD_CHANGER_ID => ["integer", $member->changerId()],
            ilIndividualAssessmentMembers::FIELD_CHANGE_TIME => ["string", $this->getActualDateTime()]
        ];

        $this->db->update(self::MEMBERS_TABLE, $values, $where);
    }

    protected function getActualDateTime() : string
    {
        return date("Y-m-d H:i:s");
    }

    /**
     * @inheritdoc
     */
    public function deleteMembers(ilObjIndividualAssessment $obj) : void
    {
        $sql = "DELETE FROM " . self::MEMBERS_TABLE . " WHERE obj_id = " . $this->db->quote($obj->getId(), 'integer');
        $this->db->manipulate($sql);
    }

    protected function loadMemberQuery() : string
    {
        return "SELECT "
            . "iassme.obj_id,"
            . "iassme.usr_id,"
            . "iassme.examiner_id,"
            . "iassme.record,"
            . "iassme.internal_note,"
            . "iassme.notify,"
            . "iassme.notification_ts,"
            . "iassme.learning_progress,"
            . "iassme.finalized,"
            . "iassme.place,"
            . "iassme.event_time,"
            . "iassme.user_view_file,"
            . "iassme.file_name,"
            . "iassme.changer_id,"
            . "iassme.change_time,"
            . "usr.lastname AS user_lastname,"
            . "ex.login AS examiner_login"
            . " FROM " . self::MEMBERS_TABLE . " iassme\n"
            . "	JOIN usr_data usr ON iassme.usr_id = usr.usr_id\n"
            . "	LEFT JOIN usr_data ex ON iassme.examiner_id = ex.usr_id\n"
        ;
    }

    protected function loadMembersQuery(int $obj_id) : string
    {
        return "SELECT ex.firstname as " . ilIndividualAssessmentMembers::FIELD_EXAMINER_FIRSTNAME
                . "     , ex.lastname as " . ilIndividualAssessmentMembers::FIELD_EXAMINER_LASTNAME
                . "     , ud.firstname as " . ilIndividualAssessmentMembers::FIELD_CHANGER_FIRSTNAME
                . "     , ud.lastname as " . ilIndividualAssessmentMembers::FIELD_CHANGER_LASTNAME
                . "     ,usr.firstname as " . ilIndividualAssessmentMembers::FIELD_FIRSTNAME
                . "     ,usr.lastname as " . ilIndividualAssessmentMembers::FIELD_LASTNAME
                . "     ,usr.login as " . ilIndividualAssessmentMembers::FIELD_LOGIN
                . "	   ,iassme." . ilIndividualAssessmentMembers::FIELD_FILE_NAME
                . "     ,iassme.obj_id, iassme.usr_id, iassme.examiner_id, iassme.record, iassme.internal_note, iassme.notify"
                . "     ,iassme.notification_ts, iassme.learning_progress, iassme.finalized,iassme.place"
                . "     ,iassme.event_time, iassme.changer_id, iassme.change_time\n"
                . " FROM iass_members iassme"
                . " JOIN usr_data usr ON iassme.usr_id = usr.usr_id"
                . " LEFT JOIN usr_data ex ON iassme.examiner_id = ex.usr_id"
                . " LEFT JOIN usr_data ud ON iassme.changer_id = ud.usr_id"
                . " WHERE obj_id = " . $this->db->quote($obj_id, 'integer');
    }

    /**
     * @inheritdoc
     */
    public function insertMembersRecord(ilObjIndividualAssessment $iass, array $record) : void
    {
        $values = [
            "obj_id" => [
                "integer",
                $iass->getId()
            ],
            "usr_id" => [
                "integer",
                $record[ilIndividualAssessmentMembers::FIELD_USR_ID]
            ],
            ilIndividualAssessmentMembers::FIELD_LEARNING_PROGRESS => [
                "text",
                $record[ilIndividualAssessmentMembers::FIELD_LEARNING_PROGRESS]
            ],
           ilIndividualAssessmentMembers::FIELD_NOTIFY => [
                "integer",
                $record[ilIndividualAssessmentMembers::FIELD_NOTIFY] ?? 0
            ],
            ilIndividualAssessmentMembers::FIELD_FINALIZED => [
                "integer",
                0
            ],
            ilIndividualAssessmentMembers::FIELD_NOTIFICATION_TS => [
                "integer",
                -1
            ]
        ];

        if (isset($record[ilIndividualAssessmentMembers::FIELD_EXAMINER_ID])) {
            $record[ilIndividualAssessmentMembers::FIELD_EXAMINER_ID] =
                [
                    "integer",
                    $record[ilIndividualAssessmentMembers::FIELD_EXAMINER_ID]
                ];
        }
        if (isset($record[ilIndividualAssessmentMembers::FIELD_RECORD])) {
            $record[ilIndividualAssessmentMembers::FIELD_RECORD] =
                [
                    "text",
                    $record[ilIndividualAssessmentMembers::FIELD_RECORD]
                ];
        }
        if (isset($record[ilIndividualAssessmentMembers::FIELD_INTERNAL_NOTE])) {
            $record[ilIndividualAssessmentMembers::FIELD_INTERNAL_NOTE] =
                [
                    "text",
                    $record[ilIndividualAssessmentMembers::FIELD_INTERNAL_NOTE]
                ];
        }
        if (isset($record[ilIndividualAssessmentMembers::FIELD_PLACE])) {
            $record[ilIndividualAssessmentMembers::FIELD_PLACE] =
                [
                    "text",
                    $record[ilIndividualAssessmentMembers::FIELD_PLACE]
                ];
        }
        if (isset($record[ilIndividualAssessmentMembers::FIELD_EVENTTIME])) {
            $record[ilIndividualAssessmentMembers::FIELD_EVENTTIME] =
                [
                    "integer",
                    $record[ilIndividualAssessmentMembers::FIELD_EVENTTIME]
                ];
        }
        if (isset($record[ilIndividualAssessmentMembers::FIELD_FILE_NAME])) {
            $record[ilIndividualAssessmentMembers::FIELD_FILE_NAME] =
                [
                    "text",
                    $record[ilIndividualAssessmentMembers::FIELD_FILE_NAME]
                ];
        }
        if (isset($record[ilIndividualAssessmentMembers::FIELD_USER_VIEW_FILE])) {
            $record[ilIndividualAssessmentMembers::FIELD_USER_VIEW_FILE] =
                [
                    "integer",
                    $record[ilIndividualAssessmentMembers::FIELD_USER_VIEW_FILE]
                ];
        }
        if (isset($record[ilIndividualAssessmentMembers::FIELD_CHANGER_ID])) {
            $record[ilIndividualAssessmentMembers::FIELD_CHANGER_ID] =
                [
                    "integer",
                    $record[ilIndividualAssessmentMembers::FIELD_CHANGER_ID]
                ];
        }
        if (isset($record[ilIndividualAssessmentMembers::FIELD_CHANGE_TIME])) {
            $record[ilIndividualAssessmentMembers::FIELD_CHANGE_TIME] =
                [
                    "text",
                    $record[ilIndividualAssessmentMembers::FIELD_CHANGE_TIME]
                ];
        }

        $this->db->insert(self::MEMBERS_TABLE, $values);
    }

    /**
     * @inheritdoc
     */
    public function removeMembersRecord(ilObjIndividualAssessment $iass, array $record) : void
    {
        $sql =
             "DELETE FROM " . self::MEMBERS_TABLE . PHP_EOL
            . "WHERE obj_id = " . $this->db->quote($iass->getId(), 'integer') . PHP_EOL
            . "AND usr_id = " . $this->db->quote($record[ilIndividualAssessmentMembers::FIELD_USR_ID], 'integer') . PHP_EOL
        ;

        $this->db->manipulate($sql);
    }

    /**
     * @param string|int $filter
     */
    protected function getWhereFromFilter($filter) : string
    {
        switch ($filter) {
            case ilIndividualAssessmentMembers::LP_ASSESSMENT_NOT_COMPLETED:
                return "      AND finalized = 0 AND examiner_id IS NULL\n";
            case ilIndividualAssessmentMembers::LP_IN_PROGRESS:
                return "      AND finalized = 0 AND examiner_id IS NOT NULL\n";
            case ilIndividualAssessmentMembers::LP_COMPLETED:
                return "      AND finalized = 1 AND learning_progress = 2\n";
            case ilIndividualAssessmentMembers::LP_FAILED:
                return "      AND finalized = 1 AND learning_progress = 3\n";
            default:
                return "";
        }
    }

    protected function getOrderByFromSort(string $sort) : string
    {
        $vals = explode(":", $sort);

        return " ORDER BY " . $vals[0] . " " . $vals[1];
    }
}
