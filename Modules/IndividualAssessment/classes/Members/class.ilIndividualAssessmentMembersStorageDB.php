<?php
require_once 'Modules/IndividualAssessment/interfaces/Members/interface.ilIndividualAssessmentMembersStorage.php';
require_once 'Modules/IndividualAssessment/classes/Members/class.ilIndividualAssessmentMembers.php';
require_once 'Modules/IndividualAssessment/classes/Members/class.ilIndividualAssessmentMember.php';
require_once 'Modules/IndividualAssessment/classes/class.ilObjIndividualAssessment.php';
/**
 * Store member infos to DB
 *
 * @author	Denis Klöpfer <denis.kloepfer@concepts-and-training.de>
 * @author	Stefan Hecken <stefan.hecken@concepts-and-training.de>
 *
 * @inheritdoc
 */
class ilIndividualAssessmentMembersStorageDB implements ilIndividualAssessmentMembersStorage
{
    const MEMBERS_TABLE = "iass_members";

    protected $db;

    public function __construct($ilDB)
    {
        $this->db = $ilDB;
    }

    /**
     * @inheritdoc
     */
    public function loadMembers(ilObjIndividualAssessment $obj)
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
    public function loadMembersAsSingleObjects(ilObjIndividualAssessment $obj, string $filter = null, string $sort = null)
    {
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
            $usr = new ilObjUser($rec["usr_id"]);
            $members[] = new ilIndividualAssessmentMember($obj, $usr, $rec);
        }
        return $members;
    }

    /**
     * @inheritdoc
     */
    public function loadMember(ilObjIndividualAssessment $obj, ilObjUser $usr)
    {
        $obj_id = $obj->getId();
        $usr_id = $usr->getId();
        $sql = $this->loadMemberQuery();
        $sql .= "	WHERE obj_id = " . $this->db->quote($obj_id, 'integer') . "\n"
            . "		AND iassme.usr_id = " . $this->db->quote($usr_id, 'integer');

        $rec = $this->db->fetchAssoc($this->db->query($sql));
        if ($rec) {
            $member = new ilIndividualAssessmentMember($obj, $usr, $rec);
            return $member;
        } else {
            throw new ilIndividualAssessmentException("invalid usr-obj combination");
        }
    }

    /**
     * @inheritdoc
     */
    public function updateMember(ilIndividualAssessmentMember $member)
    {
        $where = array("obj_id" => array("integer", $member->assessmentId())
             , "usr_id" => array("integer", $member->id())
        );

        $values = array(ilIndividualAssessmentMembers::FIELD_LEARNING_PROGRESS => array("text", $member->LPStatus())
                      , ilIndividualAssessmentMembers::FIELD_EXAMINER_ID => array("integer", $member->examinerId())
                      , ilIndividualAssessmentMembers::FIELD_RECORD => array("text", $member->record())
                      , ilIndividualAssessmentMembers::FIELD_INTERNAL_NOTE => array("text", $member->internalNote())
                      , ilIndividualAssessmentMembers::FIELD_PLACE => array("text", $member->place())
                      , ilIndividualAssessmentMembers::FIELD_EVENTTIME => array("integer", $member->eventTime()->get(IL_CAL_UNIX))
                      , ilIndividualAssessmentMembers::FIELD_NOTIFY => array("integer", $member->notify() ? 1 : 0)
                      , ilIndividualAssessmentMembers::FIELD_FINALIZED => array("integer", $member->finalized() ? 1 : 0)
                      , ilIndividualAssessmentMembers::FIELD_NOTIFICATION_TS => array("integer", $member->notificationTS())
                      , ilIndividualAssessmentMembers::FIELD_FILE_NAME => array("text", $member->fileName())
                      , ilIndividualAssessmentMembers::FIELD_USER_VIEW_FILE => array("integer", $member->viewFile() ? 1 : 0)
                      , ilIndividualAssessmentMembers::FIELD_CHANGER_ID => array("integer", $member->changerId())
                      , ilIndividualAssessmentMembers::FIELD_CHANGE_TIME => array("string", (new ilDateTime(time(), IL_CAL_UNIX))->get(IL_CAL_DATETIME))
                );

        $this->db->update(self::MEMBERS_TABLE, $values, $where);
    }

    /**
     * @inheritdoc
     */
    public function deleteMembers(ilObjIndividualAssessment $obj)
    {
        $sql = "DELETE FROM " . self::MEMBERS_TABLE . " WHERE obj_id = " . $this->db->quote($obj->getId(), 'integer');
        $this->db->manipulate($sql);
    }

    protected function loadMemberQuery()
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

    /**
     * @inheritdoc
     */
    protected function loadMembersQuery($obj_id)
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
    public function insertMembersRecord(ilObjIndividualAssessment $iass, array $record)
    {
        $values = array("obj_id" => array("integer", $iass->getId())
            , "usr_id" => array("integer", $record[ilIndividualAssessmentMembers::FIELD_USR_ID])
            , ilIndividualAssessmentMembers::FIELD_LEARNING_PROGRESS => array("text", $record[ilIndividualAssessmentMembers::FIELD_LEARNING_PROGRESS])
            , ilIndividualAssessmentMembers::FIELD_EXAMINER_ID => array("integer", $record[ilIndividualAssessmentMembers::FIELD_EXAMINER_ID])
            , ilIndividualAssessmentMembers::FIELD_RECORD => array("text", $record[ilIndividualAssessmentMembers::FIELD_RECORD])
            , ilIndividualAssessmentMembers::FIELD_INTERNAL_NOTE => array("text", $record[ilIndividualAssessmentMembers::FIELD_INTERNAL_NOTE])
            , ilIndividualAssessmentMembers::FIELD_PLACE => array("text", $record[ilIndividualAssessmentMembers::FIELD_PLACE])
            , ilIndividualAssessmentMembers::FIELD_EVENTTIME => array("integer", $record[ilIndividualAssessmentMembers::FIELD_EVENTTIME])
            , ilIndividualAssessmentMembers::FIELD_NOTIFY => array("integer", $record[ilIndividualAssessmentMembers::FIELD_NOTIFY])
            , ilIndividualAssessmentMembers::FIELD_FINALIZED => array("integer", 0)
            , ilIndividualAssessmentMembers::FIELD_NOTIFICATION_TS => array("integer", -1)
            , ilIndividualAssessmentMembers::FIELD_FILE_NAME => array("text", $record[ilIndividualAssessmentMembers::FIELD_FILE_NAME])
            , ilIndividualAssessmentMembers::FIELD_USER_VIEW_FILE => array("integer", $record[ilIndividualAssessmentMembers::FIELD_USER_VIEW_FILE])
            , ilIndividualAssessmentMembers::FIELD_CHANGER_ID => array("integer", $record[ilIndividualAssessmentMembers::FIELD_CHANGER_ID])
            , ilIndividualAssessmentMembers::FIELD_CHANGE_TIME => array("text", $record[ilIndividualAssessmentMembers::FIELD_CHANGE_TIME])
        );

        $this->db->insert(self::MEMBERS_TABLE, $values);
    }

    /**
     * @inheritdoc
     */
    public function removeMembersRecord(ilObjIndividualAssessment $iass, array $record)
    {
        $sql = "DELETE FROM " . self::MEMBERS_TABLE . "\n"
                . " WHERE obj_id = " . $this->db->quote($iass->getId(), 'integer') . "\n"
                . "     AND usr_id = " . $this->db->quote($record[ilIndividualAssessmentMembers::FIELD_USR_ID], 'integer');

        $this->db->manipulate($sql);
    }

    /**
     * @param int|string
     */
    protected function getWhereFromFilter($filter) : string
    {
        switch ($filter) {
            case ilIndividualAssessmentMembers::LP_ASSESSMENT_NOT_COMPLETED:
                return "      AND finalized = 0 AND examiner_id IS NULL\n";
                break;
            case ilIndividualAssessmentMembers::LP_IN_PROGRESS:
                return "      AND finalized = 0 AND examiner_id IS NOT NULL\n";
                break;
            case ilIndividualAssessmentMembers::LP_COMPLETED:
                return "      AND finalized = 1 AND learning_progress = 2\n";
                break;
            case ilIndividualAssessmentMembers::LP_FAILED:
                return "      AND finalized = 1 AND learning_progress = 3\n";
                break;
        }
    }

    protected function getOrderByFromSort(string $sort) : string
    {
        $vals = explode(":", $sort);

        return " ORDER BY " . $vals[0] . " " . $vals[1];
    }
}
