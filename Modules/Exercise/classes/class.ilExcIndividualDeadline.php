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
 
/**
 * Individual deadlines
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilExcIndividualDeadline
{
    protected int $participant_id;
    protected bool $is_team;
    protected int $ass_id;
    protected ilDBInterface $db;
    protected int $starting_timestamp = 0;
    protected int $individual_deadline = 0;

    protected function __construct(
        int $a_ass_id,
        int $a_participant_id,
        bool $a_is_team
    ) {
        global $DIC;
        $this->participant_id = $a_participant_id;
        $this->is_team = $a_is_team;
        $this->ass_id = $a_ass_id;
        $this->db = $DIC->database();
        $this->read();
    }

    public static function getInstance(
        int $a_ass_id,
        int $a_participant_id,
        bool $a_is_team = false
    ) : ilExcIndividualDeadline {
        return new self($a_ass_id, $a_participant_id, $a_is_team);
    }

    public function setStartingTimestamp(int $a_val) : void
    {
        $this->starting_timestamp = $a_val;
    }

    public function getStartingTimestamp() : int
    {
        return $this->starting_timestamp;
    }

    public function setIndividualDeadline(int $a_val) : void
    {
        $this->individual_deadline = $a_val;
    }

    public function getIndividualDeadline() : int
    {
        return $this->individual_deadline;
    }

    public function read() : void
    {
        $ilDB = $this->db;

        $set = $ilDB->query(
            "SELECT * FROM exc_idl " .
            " WHERE ass_id = " . $this->db->quote($this->ass_id, "integer") .
            " AND member_id = " . $this->db->quote($this->participant_id, "integer") .
            " AND is_team = " . $this->db->quote($this->is_team, "integer")
        );
        if ($rec = $this->db->fetchAssoc($set)) {
            $this->setIndividualDeadline((int) $rec["tstamp"]);
            $this->setStartingTimestamp((int) $rec["starting_ts"]);
        }
    }

    public function save() : void
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

    public function delete() : void
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
     * This is mainly used by ilExAssignment to determine the calculated deadlines
     * @param int $a_ass_id
     * @return array
     */
    public static function getStartingTimestamps(int $a_ass_id) : array
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
