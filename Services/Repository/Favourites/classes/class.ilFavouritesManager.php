<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Manages favourites, currently the interface for other components, needs discussion
 *
 * @author killing@leifos.de
 */
class ilFavouritesManager
{
    /**
     * @var ilFavouritesDBRepository
     */
    protected $repo;

    /**
     * Constructor
     */
    public function __construct(ilFavouritesDBRepository $repo = null)
    {
        $this->repo = is_null($repo)
            ? new ilFavouritesDBRepository()
            : $repo;
    }

    /**
     * Add favourite
     * @param int $user_id
     * @param int $ref_id
     */
    public function add(int $user_id, int $ref_id)
    {
        $this->repo->add($user_id, $ref_id);
        ilCalendarCategories::deletePDItemsCache($user_id);
    }

    /**
     * Remove favourite
     * @param int $user_id
     * @param int $ref_id
     */
    public function remove(int $user_id, int $ref_id)
    {
        $this->repo->remove($user_id, $ref_id);
        ilCalendarCategories::deletePDItemsCache($user_id);
    }

    /**
     * Is item favourite?
     * @param int $user_id
     * @param int $ref_id
     * @return bool
     */
    public function ifIsFavourite(int $user_id, int $ref_id): bool
    {
        return $this->repo->ifIsFavourite($user_id, $ref_id);
    }

    /**
     * Preloads data into cache
     *
     * @param int $user_id
     * @param array $ref_ids
     */
    public function loadData(int $user_id, array $ref_ids)
    {
        $this->repo->loadData($user_id, $ref_ids);
    }

    /**
     * Get favourits of user
     *
     * @param int $user_id
     * @param array|null $a_types
     * @return array
     */
    public function getFavouritesOfUser(int $user_id, array $a_types = null): array
    {
        return $this->repo->getFavouritesOfUser($user_id, $a_types);
    }

    /**
     * Remove favourite entries of a repository item
     *
     * @param int $ref_id
     */
    public function removeFavouritesOfRefId(int $ref_id)
    {
        $this->repo->removeFavouritesOfRefId($ref_id);
    }

    /**
     * Remove favourite entries of a user
     *
     * @param int $user_id
     */
    public function removeFavouritesOfUser(int $user_id)
    {
        $this->repo->removeFavouritesOfRefId($user_id);
    }

}