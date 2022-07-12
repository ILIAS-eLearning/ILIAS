<?php declare(strict_types = 1);

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

namespace ILIAS\Survey\Participants;

use ILIAS\Survey\InternalDataService;

/**
 * Survey invitations repository
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class InvitationsDBRepository
{
    protected \ilDBInterface $db;
    protected InternalDataService $data;

    public function __construct(
        InternalDataService $data,
        \ilDBInterface $db
    ) {
        $this->data = $data;
        $this->db = $db;
    }


    /**
     * Remove invitation
     *
     * @param int $survey_id Survey ID not object ID!
     * @param int $user_id
     */
    public function remove(int $survey_id, int $user_id) : void
    {
        $db = $this->db;

        $db->manipulateF(
            "DELETE FROM svy_invitation WHERE " .
            " survey_id = %s AND user_id = %s",
            ["integer", "integer"],
            [$survey_id, $user_id]
        );
    }
    
    
    /**
     * Add invitation
     *
     * @param int $survey_id Survey ID not object ID!
     * @param int $user_id
     */
    public function add(int $survey_id, int $user_id) : void
    {
        $db = $this->db;

        $db->replace(
            "svy_invitation",
            [		// pk
                "survey_id" => ["integer", $survey_id],
                "user_id" => ["integer", $user_id]
            ],
            []
        );
    }

    /**
     * Get invitations for survey
     *
     * @param int $survey_id Survey ID not object ID!
     * @return int[]
     */
    public function getAllForSurvey(int $survey_id) : array
    {
        $db = $this->db;

        $items = [];
        $set = $db->queryF(
            "SELECT user_id FROM svy_invitation " .
            " WHERE survey_id = %s ",
            ["integer"],
            [$survey_id]
        );

        while ($rec = $db->fetchAssoc($set)) {
            $items[] = (int) $rec["user_id"];
        }
        return $items;
    }

    /**
     * Get surveys where user is invited
     * @param int $user_id user id
     * @return int[] survey IDs
     */
    public function getAllForUser(int $user_id) : array
    {
        $db = $this->db;

        $items = [];
        $set = $db->queryF(
            "SELECT survey_id FROM svy_invitation " .
            " WHERE user_id = %s ",
            ["integer"],
            [$user_id]
        );

        while ($rec = $db->fetchAssoc($set)) {
            $items[] = (int) $rec["survey_id"];
        }
        return $items;
    }
}
