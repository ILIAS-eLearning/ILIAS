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

}