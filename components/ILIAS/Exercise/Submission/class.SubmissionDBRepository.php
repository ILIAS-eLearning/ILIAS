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

namespace ILIAS\Exercise\Submission;

/**
 * Submission repository
 *
 * @author Jesús López <lopez@leifos.com>
 * @author Alexander Killing <killing@leifos.de>
 */
class SubmissionDBRepository implements SubmissionRepositoryInterface
{
    protected const TABLE_NAME = "exc_returned";
    protected const TEAM_TABLE_NAME = "il_exc_team";

    protected \ilDBInterface $db;

    public function __construct(\ilDBInterface $db = null)
    {
        global $DIC;

        $this->db = (is_null($db))
            ? $DIC->database()
            : $db;
    }

    public function getUserId(int $submission_id): int
    {
        $q = "SELECT user_id FROM " . self::TABLE_NAME .
            " WHERE returned_id = " . $this->db->quote($submission_id, "integer");
        $usr_set = $this->db->query($q);

        $rec = $this->db->fetchAssoc($usr_set);
        return (int) ($rec["user_id"] ?? 0);
    }

    public function hasSubmissions(int $assignment_id): int
    {
        $query = "SELECT * FROM " . self::TABLE_NAME .
            " WHERE ass_id = " . $this->db->quote($assignment_id, "integer") .
            " AND (filename IS NOT NULL OR atext IS NOT NULL)" .
            " AND ts IS NOT NULL";
        $res = $this->db->query($query);
        return $res->numRows();
    }

    // Update web_dir_access_time. It defines last HTML opening data.
    public function updateWebDirAccessTime(int $assignment_id, int $member_id): void
    {
        $this->db->manipulate("UPDATE " . self::TABLE_NAME .
            " SET web_dir_access_time = " . $this->db->quote(\ilUtil::now(), "timestamp") .
            " WHERE ass_id = " . $this->db->quote($assignment_id, "integer") .
            " AND user_id = " . $this->db->quote($member_id, "integer"));
    }

    /**
     * Checks if a user has submitted anything for a number of assignments.
     * This function should be performant, because it is being used for task
     * determination. It assumes, that team db entries only exist for team
     * assignment types and thus does not read the assignment types at all.
     */
    public function getUserSubmissionState(int $user_id, array $assignment_ids): array
    {
        $db = $this->db;

        $submitted = [];
        foreach ($assignment_ids as $id) {
            $submitted[(int) $id] = false;
        }

        $set = $db->queryF(
            "SELECT ass_id FROM  " . self::TABLE_NAME .
            " WHERE " . $db->in("ass_id", $assignment_ids, false, "integer") .
            " AND user_id = %s " .
            " AND (filename IS NOT NULL OR atext IS NOT NULL)" .
            " AND ts IS NOT NULL",
            ["integer"],
            [$user_id]
        );
        while ($rec = $db->fetchAssoc($set)) {
            $submitted[(int) $rec["ass_id"]] = true;
        }

        $set = $db->queryF(
            "SELECT ret.ass_id FROM  " . self::TABLE_NAME . " ret JOIN " .
            self::TEAM_TABLE_NAME . " team ON (ret.team_id = team.id AND ret.ass_id = team.ass_id) " .
            " WHERE " . $db->in("ret.ass_id", $assignment_ids, false, "integer") .
            " AND team.user_id = %s " .
            " AND (ret.filename IS NOT NULL OR ret.atext IS NOT NULL)" .
            " AND ret.ts IS NOT NULL",
            ["integer"],
            [$user_id]
        );
        while ($rec = $db->fetchAssoc($set)) {
            $submitted[(int) $rec["ass_id"]] = true;
        }

        return $submitted;
    }
}
