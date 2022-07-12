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
 * This class determines assignment member state information
 * directly on the persistence layer. Thus its procedures are fast
 * but may not include/respect all higher application logic of the assignment state of members
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilExcAssMemberStateRepository
{
    protected ilDBInterface $db;

    public function __construct(ilDBInterface $db = null)
    {
        global $DIC;

        $this->db = (is_null($db))
            ? $DIC->database()
            : $db;
    }
    
    /**
     * Get all assignments for a user where the user may hand in submissions
     *
     * @param int[] $exc_ids	exercises the user is "member" in
     * @return int[]
     */
    public function getSubmitableAssignmentIdsOfUser(
        array $exc_ids,
        int $user_id
    ) : array {
        $db = $this->db;
        $set = $db->queryF(
            'SELECT ass.id FROM exc_assignment ass LEFT JOIN exc_idl idl
			ON (ass.id = idl.ass_id AND idl.member_id = %s)
			WHERE ' . $db->in("ass.exc_id", $exc_ids, false, "integer") . ' 
				AND ((	ass.deadline_mode = %s
						AND (ass.start_time IS NULL OR ass.start_time < %s )
						AND (ass.time_stamp IS NULL OR ass.time_stamp > %s OR ass.deadline2 > %s OR idl.tstamp > %s))
					) OR (
						ass.deadline_mode = %s
						AND (idl.starting_ts > 0)
						AND (idl.starting_ts + (ass.relative_deadline * 24 * 60 * 60) > %s)
					)',
            array("integer", "integer", "integer", "integer", "integer", "integer", "integer", "integer"),
            array($user_id, 0, time(), time(), time(), time(), 1, time())
        );
        $ids = [];
        while ($rec = $db->fetchAssoc($set)) {
            $ids[] = $rec["id"];
        }
        return $ids;
    }

    /**
     * Get assignments with open gradings
     *
     * @param int[] $exc_ids exercises the user is "tutor" of
     * @return int[]
     */
    public function getAssignmentIdsWithGradingNeeded(array $exc_ids) : array
    {
        $db = $this->db;

        $set = $db->queryF(
            'SELECT ass.id, COUNT(*) open_grading FROM exc_mem_ass_status st LEFT JOIN exc_assignment ass 
			ON (st.ass_id = ass.id)
			WHERE ' . $db->in("ass.exc_id", $exc_ids, false, "integer") . '
			AND st.status = %s
			AND st.returned = %s
			GROUP BY (ass.id)',
            array("text","integer"),
            array("notgraded", 1)
        );
        $open_gradings = [];
        while ($rec = $db->fetchAssoc($set)) {
            $open_gradings[$rec["id"]] = (int) $rec["open_grading"];
        }
        return $open_gradings;
    }

    /**
     * Get all assignments for a user where the user may hand in submissions
     *
     * @param int[] $exc_ids	exercises the user is "member" in
     * @return int[]
     */
    public function getAssignmentIdsWithPeerFeedbackNeeded(
        array $exc_ids,
        int $user_id
    ) : array {
        $db = $this->db;

        // peer groups exist
        $set = $db->queryF(
            'SELECT ass.id, count(*) nr_given, ass.peer_dl, ass.peer_min, max(idl.tstamp) maxidl, max(peer.tstamp) maxpeer 
			FROM exc_assignment ass
			LEFT JOIN exc_assignment_peer peer ON (ass.id = peer.ass_id)
			LEFT JOIN exc_idl idl ON (ass.id = idl.ass_id)
			WHERE ' . $db->in("ass.exc_id", $exc_ids, false, "integer") . ' 
				AND ass.deadline_mode = %s
				AND ass.time_stamp < %s
				AND (ass.deadline2 < %s OR ass.deadline2 IS NULL)
				AND ass.peer = %s
				AND (peer.giver_id = %s)
				AND (ass.peer_dl > %s OR ass.peer_dl IS NULL)
				AND (peer.is_valid = %s)
			GROUP BY (ass.id)
			HAVING (ass.peer_min > nr_given) AND (maxidl < %s OR maxidl IS NULL)
					',
            array("integer", "integer", "integer", "integer", "integer", "integer", "integer", "integer"),
            array(0, time(), time(), 1, $user_id, time(), 1, time())
        );
        $ids = [];
        while ($rec = $db->fetchAssoc($set)) {
            $ids[] = $rec["id"];
        }

        // peer groups do not exist
        $set = $db->queryF(
            'SELECT ass.id, count(*) nr_given, ass.peer_dl, ass.peer_min, max(idl.tstamp) maxidl, max(peer.tstamp) maxpeer 
			FROM exc_assignment ass
			LEFT JOIN exc_assignment_peer peer ON (ass.id = peer.ass_id)
			LEFT JOIN exc_idl idl ON (ass.id = idl.ass_id)
			WHERE ' . $db->in("ass.exc_id", $exc_ids, false, "integer") . ' 
				AND ass.deadline_mode = %s
				AND ass.time_stamp < %s
				AND (ass.deadline2 < %s OR ass.deadline2 IS NULL)
				AND ass.peer = %s
				AND (peer.giver_id IS NULL)
				AND (ass.peer_dl > %s OR ass.peer_dl IS NULL)
				AND (peer.tstamp IS NULL)
			GROUP BY (ass.id)
			HAVING (maxpeer IS NULL) AND (maxidl < %s OR maxidl IS NULL)
					',
            array("integer", "integer", "integer", "integer", "integer", "integer"),
            array(0, time(), time(), 1, time(), time())
        );
        while ($rec = $db->fetchAssoc($set)) {
            $ids[] = $rec["id"];
        }

        return $ids;
    }
}
