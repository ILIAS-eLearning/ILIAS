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
 * Recommended content manager
 * (business logic)
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilRecommendedContentManager
{
    protected ilRecommendedContentDBRepository $repo;
    protected ilRbacReview $rbacreview;
    protected ilFavouritesManager $fav_manager;

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

    public function addRoleRecommendation(int $role_id, int $ref_id) : void
    {
        $this->repo->addRoleRecommendation($role_id, $ref_id);
    }

    public function removeRoleRecommendation(int $role_id, int $ref_id) : void
    {
        $this->repo->removeRoleRecommendation($role_id, $ref_id);
    }

    /**
     * @return int[] ref ids of recommendations
     */
    public function getRecommendationsOfRole(int $role_id) : array
    {
        return $this->repo->getRecommendationsOfRoles([$role_id]);
    }


    public function addObjectRecommendation(int $user_id, int $ref_id) : void
    {
        $this->repo->addObjectRecommendation($user_id, $ref_id);
    }

    public function removeObjectRecommendation(int $user_id, int $ref_id) : void
    {
        $this->repo->removeObjectRecommendation($user_id, $ref_id);
    }

    //  Remove all recommendations of a ref id (role and user/object related)
    public function removeRecommendationsOfRefId(int $ref_id) : void
    {
        $this->repo->removeRecommendationsOfRefId($ref_id);
    }

    public function removeRecommendationsOfUser(int $user_id) : void
    {
        $this->repo->removeRecommendationsOfUser($user_id);
    }

    public function removeRecommendationsOfRole(int $role_id) : void
    {
        $this->repo->removeRecommendationsOfRole($role_id);
    }

    /**
     * @return int[] ref ids
     */
    public function getOpenRecommendationsOfUser(int $user_id) : array
    {
        $review = $this->rbacreview;
        $repo = $this->repo;

        $role_ids = $review->assignedRoles($user_id);

        $recommendations = $repo->getOpenRecommendationsOfUser($user_id, $role_ids);

        // filter out favourites
        $favourites = $this->fav_manager->getFavouritesOfUser($user_id);
        $favourites_ref_ids = array_column($favourites, "ref_id");
        
        return array_filter($recommendations, static function (int $i) use ($favourites_ref_ids) : bool {
            return !in_array($i, $favourites_ref_ids, true);
        });
    }

    public function declineObjectRecommendation(int $user_id, int $ref_id) : void
    {
        $this->repo->declineObjectRecommendation($user_id, $ref_id);
    }
}
