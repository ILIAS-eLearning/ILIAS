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
 * Manages favourites, currently the interface for other components, needs discussion
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilFavouritesManager
{
    protected ilFavouritesDBRepository $repo;

    public function __construct(ilFavouritesDBRepository $repo = null)
    {
        $this->repo = is_null($repo)
            ? new ilFavouritesDBRepository()
            : $repo;
    }

    // Add favourite
    public function add(int $user_id, int $ref_id) : void
    {
        $this->repo->add($user_id, $ref_id);
        ilCalendarCategories::deletePDItemsCache($user_id);
    }

    // Remove favourite
    public function remove(int $user_id, int $ref_id) : void
    {
        $this->repo->remove($user_id, $ref_id);
        ilCalendarCategories::deletePDItemsCache($user_id);
    }

    // Is item favourite?
    public function ifIsFavourite(int $user_id, int $ref_id) : bool
    {
        return $this->repo->ifIsFavourite($user_id, $ref_id);
    }

    /**
     * Preloads data into cache
     * @param int[] $ref_ids
     */
    public function loadData(int $user_id, array $ref_ids) : void
    {
        $this->repo->loadData($user_id, $ref_ids);
    }

    /**
     * Get favourites of user
     * @param ?string[] $a_types
     */
    public function getFavouritesOfUser(int $user_id, ?array $a_types = null) : array
    {
        return $this->repo->getFavouritesOfUser($user_id, $a_types);
    }

    // Remove favourite entries of a repository item
    public function removeFavouritesOfRefId(int $ref_id) : void
    {
        $this->repo->removeFavouritesOfRefId($ref_id);
    }

    // Remove favourite entries of a user
    public function removeFavouritesOfUser(int $user_id) : void
    {
        $this->repo->removeFavouritesOfRefId($user_id);
    }
}
