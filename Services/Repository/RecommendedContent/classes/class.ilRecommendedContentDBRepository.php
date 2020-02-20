<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Recommended content db repository
 *
 * Table rep_rec_content_obj (A repo object is directly recommended for a user, users can decline recommendations)
 * - user_id
 * - ref_id
 * - declined
 *
 * Table rep_rec_content_role (A repo object is recommended for users of a role)
 * - role_id
 * - ref_id
 *
 * @author killing@leifos.de
 */
class ilRecommendedContentDBRepository
{
    /**
     * @var \ilDBInterface
     */
    protected $db;

    /**
     * Constructor
     */
    public function __construct(\ilDBInterface $db = null)
    {
        global $DIC;

        $this->db = (is_null($db))
            ? $DIC->database()
            : $db;
    }


    /**
     * Add role recommendation
     * @param int $role_id
     * @param int $ref_id
     */
    public function addRoleRecommendation(int $role_id, int $ref_id)
    {
        $db = $this->db;

        $db->replace(
            "rep_rec_content_role",
            [		// pk
            "role_id" => ["integer", $role_id],
            "ref_id" => ["integer", $ref_id]
        ],
            []
        );
    }

    /**
     * Remove role recommendation
     * @param int $role_id
     * @param int $ref_id
     */
    public function removeRoleRecommendation(int $role_id, int $ref_id)
    {
        $db = $this->db;

        $db->manipulateF(
            "DELETE FROM rep_rec_content_role WHERE " .
            " role_id = %s AND ref_id = %s",
            ["integer", "integer"],
            [$role_id, $ref_id]
        );
    }

    /**
     * Add object recommendation
     * @param int $role_id
     * @param int $ref_id
     */
    public function addObjectRecommendation(int $user_id, int $ref_id)
    {
        $db = $this->db;

        if (!$this->ifExistsObjectRecommendation($user_id, $ref_id)) {
            $db->insert("rep_rec_content_obj", [
                "user_id" => ["integer", $user_id],
                "ref_id" => ["integer", $ref_id],
                "declined" => ["integer", false]
            ]);
        }
    }

    /**
     * Remove object recommendation
     * @param int $user_id
     * @param int $ref_id
     */
    public function removeObjectRecommendation(int $user_id, int $ref_id)
    {
        $db = $this->db;

        $db->manipulateF(
            "DELETE FROM rep_rec_content_obj WHERE " .
            " user_id = %s AND ref_id = %s",
            ["integer", "integer"],
            [$user_id, $ref_id]
        );
    }

    /**
     * Remove all recommendations of a ref id (role and user/object related)
     *
     * @param int $ref_id
     */
    public function removeRecommendationsOfRefId(int $ref_id)
    {
        $db = $this->db;

        $db->manipulateF(
            "DELETE FROM rep_rec_content_obj WHERE " .
            " ref_id = %s",
            ["integer"],
            [$ref_id]
        );

        $db->manipulateF(
            "DELETE FROM rep_rec_content_role WHERE " .
            " ref_id = %s",
            ["integer"],
            [$ref_id]
        );
    }

    /**
     * Remove all recommendations of a user
     *
     * @param int $user_id
     */
    public function removeRecommendationsOfUser(int $user_id)
    {
        $db = $this->db;

        $db->manipulateF(
            "DELETE FROM rep_rec_content_obj WHERE " .
            " user_id = %s",
            ["integer"],
            [$user_id]
        );
    }

    /**
     * Remove all recommendations of a role
     *
     * @param int $role_id
     */
    public function removeRecommendationsOfRole(int $role_id)
    {
        $db = $this->db;

        $db->manipulateF(
            "DELETE FROM rep_rec_content_role WHERE " .
            " role_id = %s",
            ["integer"],
            [$role_id]
        );
    }

    /**
     * Does object recommendation exist?
     *
     * @param
     * @return bool
     */
    protected function ifExistsObjectRecommendation(int $user_id, int $ref_id)
    {
        $db = $this->db;

        $set = $db->queryF(
            "SELECT * FROM rep_rec_content_obj " .
            " WHERE user_id = %s AND ref_id = %s",
            ["integer","integer"],
            [$user_id, $ref_id]
        );
        if ($rec = $db->fetchAssoc($set)) {
            return true;
        }
        return false;
    }


    /**
     * Decline object recommendation
     *
     * @param int $user_id
     * @param int $ref_id
     */
    public function declineObjectRecommendation(int $user_id, int $ref_id)
    {
        $db = $this->db;

        if ($this->ifExistsObjectRecommendation($user_id, $ref_id)) {
            $db->update(
                "rep_rec_content_obj",
                [
                    "declined" => ["integer", true]
                ],
                [	// where
                    "user_id" => ["integer", $user_id],
                    "ref_id" => ["integer", $ref_id]
                ]
            );
        } else {
            $db->insert("rep_rec_content_obj", [
                "user_id" => ["integer", $user_id],
                "ref_id" => ["integer", $ref_id],
                "declined" => ["integer", true]
            ]);
        }
    }

    /**
     * Get recommendations of roles
     *
     * @param $role_ids int[] role ids
     * @return int[] ref ids of recommendations
     */
    public function getRecommendationsOfRoles(array $role_ids)
    {
        $db = $this->db;

        $set = $db->query(
            "SELECT DISTINCT ref_id FROM rep_rec_content_role " .
            " WHERE " . $db->in("role_id", $role_ids, false, "integer")
        );
        return array_column($db->fetchAll($set), "ref_id");
    }
    
    /**
     * Get user object recommendations
     *
     * @param int $user_id
     * @return int[] ref ids of recommendations
     */
    protected function getUserObjectRecommendations(int $user_id) : array
    {
        $db = $this->db;

        $set = $db->queryF(
            "SELECT ref_id FROM rep_rec_content_obj " .
            " WHERE user_id = %s AND declined = %s",
            ["integer", "integer"],
            [$user_id, false]
        );
        return array_column($db->fetchAll($set), "ref_id");
    }

    /**
     * Get declined user object recommendations
     *
     * @param int $user_id
     * @return int[] ref ids of declined recommendations
     */
    protected function getDeclinedUserObjectRecommendations(int $user_id) : array
    {
        $db = $this->db;

        $set = $db->queryF(
            "SELECT ref_id FROM rep_rec_content_obj " .
            " WHERE user_id = %s AND declined = %s",
            ["integer", "integer"],
            [$user_id, true]
        );
        return array_column($db->fetchAll($set), "ref_id");
    }


    /**
     * Open recommendations of user (by role or object, without declined ones)
     *
     * @param int $user_id
     * @param int[] $role_ids
     * @return int[] ref ids of open recommendations
     */
    public function getOpenRecommendationsOfUser(int $user_id, array $role_ids) : array
    {
        // recommendations of role
        $role_recommendations = $this->getRecommendationsOfRoles($role_ids);

        // recommendations of user
        $obj_recommendations = $this->getUserObjectRecommendations($user_id);

        $recommendations = array_unique($role_recommendations + $obj_recommendations);

        // filter declined recommendations
        $declined_recommendations = $this->getDeclinedUserObjectRecommendations($user_id);
        return array_filter($recommendations, function ($i) use ($declined_recommendations) {
            return !in_array($i, $declined_recommendations);
        });
    }
}
