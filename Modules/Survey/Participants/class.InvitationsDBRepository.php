<?php
declare(strict_types = 1);

namespace ILIAS\Survey\Participants;

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\Survey\InternalDataService;

/**
 * Survey invitations repository
 *
 * @author killing@leifos.de
 */
class InvitationsDBRepository
{
    /**
     * @var \ilDBInterface
     */
    protected $db;

    /**
     * @var InternalDataService
     */
    protected $data;

    /**
     * Constructor
     */
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
    public function add(int $survey_id, int $user_id)
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
            $items[] = $rec["user_id"];
        }
        return $items;
    }

    /**
     * Get surveys where user is invited
     *
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
