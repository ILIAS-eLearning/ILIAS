<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Recommended content manager
 * (business logic)
 *
 * @author killing@leifos.de
 */
class ilRecommendedContentManager
{
    /**
     * @var ilRecommendedContentDBRepository
     */
    protected $repo;

    /**
     * @var ilRbacReview
     */
    protected $rbacreview;

    /**
     * @var ilFavouritesManager
     */
    protected $fav_manager;

    /**
     * Constructor
     */
    public function __construct(
        ilRecommendedContentDBRepository $repo = null,
        ilRbacReview $rbacreview = null,
        ilFavouritesManager $fav_manager = null
    ) {
        global $DIC;

        $this->repo = (is_null($repo))
            ? new ilRecommendedContentDBRepository()
            : $repo;

        $this->rbacreview = (is_null($rbacreview))
            ? $DIC->rbac()->review()
            : $rbacreview;

        $this->fav_manager = (is_null($fav_manager))
            ? new ilFavouritesManager()
            : $fav_manager;
    }

    /**
     * Add role recommendation
     * @param int $role_id
     * @param int $ref_id
     */
    public function addRoleRecommendation(int $role_id, int $ref_id)
    {
        $this->repo->addRoleRecommendation($role_id, $ref_id);
    }

    /**
     * Remove role recommendation
     * @param int $role_id
     * @param int $ref_id
     */
    public function removeRoleRecommendation(int $role_id, int $ref_id)
    {
        $this->repo->removeRoleRecommendation($role_id, $ref_id);
    }

    /**
     * Add role recommendation
     * @param int $role_id
     * @return int[]
     */
    public function getRecommendationsOfRole(int $role_id) : array
    {
        return $this->repo->getRecommendationsOfRoles([$role_id]);
    }


    /**
     * Add object recommendation
     * @param int $role_id
     * @param int $ref_id
     */
    public function addObjectRecommendation(int $user_id, int $ref_id)
    {
        $this->repo->addObjectRecommendation($user_id, $ref_id);
    }

    /**
     * Remove object recommendation
     * @param int $user_id
     * @param int $ref_id
     */
    public function removeObjectRecommendation(int $user_id, int $ref_id)
    {
        $this->repo->removeObjectRecommendation($user_id, $ref_id);
    }

    /**
     * Remove all recommendations of a ref id (role and user/object related)
     *
     * @param int $ref_id
     */
    public function removeRecommendationsOfRefId(int $ref_id)
    {
        $this->repo->removeRecommendationsOfRefId($ref_id);
    }

    /**
     * Remove all recommendations of a user
     *
     * @param int $user_id
     */
    public function removeRecommendationsOfUser(int $user_id)
    {
        $this->repo->removeRecommendationsOfUser($user_id);
    }

    /**
     * Remove all recommendations of a role
     *
     * @param int $role_id
     */
    public function removeRecommendationsOfRole(int $role_id)
    {
        $this->repo->removeRecommendationsOfRole($role_id);
    }

    /**
     * Get open recommendations for user
     *
     * @param int $user_sid
     * @return int[] ref ids
     */
    public function getOpenRecommendationsOfUser(int $user_id)
    {
        $review = $this->rbacreview;
        $repo = $this->repo;

        $role_ids = $review->assignedRoles($user_id);

        $recommendations = $repo->getOpenRecommendationsOfUser($user_id, $role_ids);

        // filter out favourites
        $favourites = $this->fav_manager->getFavouritesOfUser($user_id);
        $favourites_ref_ids = array_column($favourites, "ref_id");

        return array_filter($recommendations, function ($i) use ($favourites_ref_ids) {
            return !in_array($i, $favourites_ref_ids);
        });
    }

    /**
     * Decline object recommendation
     *
     * @param int $user_id
     * @param int $ref_id
     */
    public function declineObjectRecommendation(int $user_id, int $ref_id)
    {
        $this->repo->declineObjectRecommendation($user_id, $ref_id);
    }
}
