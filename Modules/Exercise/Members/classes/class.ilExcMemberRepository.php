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
 * This should hold all accesses to exc_members table in the future
 * @author Alexander Killing <killing@leifos.de>
 */
class ilExcMemberRepository
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
     * Get all exercise IDs of a user
     *
     * @param int user id
     * @return int[] exercise ids
     */
    public function getExerciseIdsOfUser(
        int $user_id
    ): array {
        $db = $this->db;

        $set = $db->queryF(
            "SELECT DISTINCT obj_id FROM exc_members " .
            " WHERE usr_id = %s ",
            array("integer"),
            array($user_id)
        );
        $ids = [];
        while ($rec = $db->fetchAssoc($set)) {
            $ids[] = $rec["obj_id"];
        }

        return $ids;
    }
}
