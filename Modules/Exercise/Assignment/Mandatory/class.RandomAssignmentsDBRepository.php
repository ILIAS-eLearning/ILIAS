<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\Exercise\Assignment\Mandatory;

use ILIAS\Exercise\InternalDataService;

/**
 * Stores info about random assignments for users in exercises
 * @author Alexander Killing <killing@leifos.de>
 */
class RandomAssignmentsDBRepository
{
    protected \ilDBInterface $db;
    protected InternalDataService $data;

    public function __construct(
        InternalDataService $data,
        \ilDBInterface $db
    ) {
        $this->db = $db;
        $this->data = $data;
    }

    /**
     * Get mandatory assignments of user
     *
     * @param int $user_id
     * @param int $exc_id
     * @return int[]
     */
    public function getAssignmentsOfUser(
        int $user_id,
        int $exc_id
    ) : array {
        $db = $this->db;

        $set = $db->queryF(
            "SELECT * FROM exc_mandatory_random " .
            " WHERE usr_id  = %s " .
            " AND exc_id  = %s ",
            array("integer", "integer"),
            array($user_id, $exc_id)
        );
        $ass_ids = [];
        while ($rec = $db->fetchAssoc($set)) {
            if (\ilExAssignment::isInExercise($rec["ass_id"], $exc_id)) {
                $ass_ids[] = $rec["ass_id"];
            }
        }
        return $ass_ids;
    }

    /**
     * Save assignments of user
     *
     * @param int $user_id
     * @param int $exc_id
     * @param int[] $ass_ids
     */
    public function saveAssignmentsOfUser(
        int $user_id,
        int $exc_id,
        array $ass_ids
    ) : void {
        $db = $this->db;

        $db->manipulateF(
            "DELETE FROM exc_mandatory_random  WHERE " .
            " exc_id = %s" .
            " AND usr_id = %s",
            array("integer", "integer"),
            array($exc_id, $user_id)
        );

        foreach ($ass_ids as $ass_id) {
            if (\ilExAssignment::isInExercise($ass_id, $exc_id)) {
                $db->replace("exc_mandatory_random", array(        // pk
                    "usr_id" => array("integer", $user_id),
                    "exc_id" => array("integer", $exc_id),
                    "ass_id" => array("integer", $ass_id)
                ), []);
            }
        }
    }
}
