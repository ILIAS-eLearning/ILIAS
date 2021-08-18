<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Submission repository
 *
 * @author Jesús López <lopez@leifos.com>
 */
class ilExcSubmissionRepository implements ilExcSubmissionRepositoryInterface
{
    protected const TABLE_NAME = "exc_returned";

    protected ilDBInterface $db;

    public function __construct(ilDBInterface $db = null)
    {
        global $DIC;

        $this->db = (is_null($db))
            ? $DIC->database()
            : $db;
    }

    public function getUserId(int $submission_id) : int
    {
        $q = "SELECT user_id FROM " . self::TABLE_NAME .
            " WHERE returned_id = " . $this->db->quote($submission_id, "integer");
        $usr_set = $this->db->query($q);
        return $this->db->fetchAssoc($usr_set);
    }

    public function hasSubmissions(int $assignment_id) : int
    {
        $query = "SELECT * FROM " . self::TABLE_NAME .
            " WHERE ass_id = " . $this->db->quote($assignment_id, "integer") .
            " AND (filename IS NOT NULL OR atext IS NOT NULL)" .
            " AND ts IS NOT NULL";
        $res = $this->db->query($query);
        return (int) $res->numRows();
    }

    // Update web_dir_access_time. It defines last HTML opening data.
    public function updateWebDirAccessTime(int $assignment_id, int $member_id) : void
    {
        $this->db->manipulate("UPDATE " . self::TABLE_NAME .
            " SET web_dir_access_time = " . $this->db->quote(ilUtil::now(), "timestamp") .
            " WHERE ass_id = " . $this->db->quote($assignment_id, "integer") .
            " AND user_id = " . $this->db->quote($member_id, "integer"));
    }
}
