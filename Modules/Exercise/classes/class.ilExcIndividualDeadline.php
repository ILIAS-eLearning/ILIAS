<?php

/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Individual deadlines
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @ingroup ModulesExercise
 */
class ilExcIndividualDeadline
{
    /**
     * @var int
     */
    protected $participant_id;

    /**
     * @var bool
     */
    protected $is_team;

    /**
     * @var int
     */
    protected $ass_id;

    /**
     * @var ilDB
     */
    protected $db;

    /**
     * @var int
     */
    protected $starting_timestamp = 0;

    /**
     * @var int
     */
    protected $individual_deadline;

    /**
     * ilExcIndividualDeadline constructor.
     *
     * @param int $a_ass_id
     * @param int $a_participant_id
     * @param bool $a_is_team
     */
    protected function __construct($a_ass_id, $a_participant_id, $a_is_team)
    {
        global $DIC;
        $this->participant_id = $a_participant_id;
        $this->is_team = $a_is_team;
        $this->ass_id = $a_ass_id;
        $this->db = $DIC->database();
        $this->read();
    }

    /**
     * Get instance
     *
     * @param int $a_ass_id
     * @param int $a_participant_id
     * @param bool $a_is_team
     * @return ilExcIndividualDeadline
     */
    public static function getInstance($a_ass_id, $a_participant_id, $a_is_team = false)
    {
        return new self($a_ass_id, $a_participant_id, $a_is_team);
    }

    /**
     * Set starting timestamp
     *
     * @param int $a_val starting timestamp
     */
    public function setStartingTimestamp($a_val)
    {
        $this->starting_timestamp = $a_val;
    }

    /**
     * Get starting timestamp
     *
     * @return int starting timestamp
     */
    public function getStartingTimestamp()
    {
        return $this->starting_timestamp;
    }

    /**
     * Set Individual Deadline
     *
     * @param int $a_val
     */
    public function setIndividualDeadline($a_val)
    {
        $this->individual_deadline = $a_val;
    }

    /**
     * Get Individual Deadline
     *
     * @return int
     */
    public function getIndividualDeadline()
    {
        return $this->individual_deadline;
    }

    /**
     * Read
     */
    public function read()
    {
        $ilDB = $this->db;

        $set = $ilDB->query(
            "SELECT * FROM exc_idl " .
            " WHERE ass_id = " . $this->db->quote($this->ass_id, "integer") .
            " AND member_id = " . $this->db->quote($this->participant_id, "integer") .
            " AND is_team = " . $this->db->quote($this->is_team, "integer")
        );
        $rec = $this->db->fetchAssoc($set);

        $this->setIndividualDeadline((int) $rec["tstamp"]);
        $this->setStartingTimestamp((int) $rec["starting_ts"]);
    }

    /**
     * Save
     */
    public function save()
    {
        $ilDB = $this->db;

        $ilDB->replace(
            "exc_idl",
            array(
                "ass_id" => array("integer", $this->ass_id),
                "member_id" => array("integer", $this->participant_id),
                "is_team" => array("integer", $this->is_team)
            ),
            array(
                "tstamp" => array("integer", $this->getIndividualDeadline()),
                "starting_ts" => array("integer", $this->getStartingTimestamp())
            )
        );
    }

    /**
     * Delete
     */
    public function delete()
    {
        $ilDB = $this->db;

        $ilDB->manipulate(
            "DELETE FROM exc_idl " .
            " WHERE ass_id = " . $this->db->quote($this->ass_id, "integer") .
            " AND member_id = " . $this->db->quote($this->participant_id, "integer") .
            " AND is_team = " . $this->db->quote($this->is_team, "integer")
        );
    }


    /**
     * Get starting timestamp data for an assignment.
     *
     * This is mainly used by ilExAssignment to determine the calculated deadlines
     *
     * @param $a_ass_id
     * @return array
     */
    public static function getStartingTimestamps($a_ass_id)
    {
        global $DIC;

        $ilDB = $DIC->database();
        $res = array();

        $set = $ilDB->query("SELECT * FROM exc_idl" .
            " WHERE ass_id = " . $ilDB->quote($a_ass_id, "integer"));
        while ($row = $ilDB->fetchAssoc($set)) {
            $res[] = array("member_id" => $row["member_id"],
                "is_team" => $row["is_team"],
                "starting_ts" => $row["starting_ts"]);
        }

        return $res;
    }
}
