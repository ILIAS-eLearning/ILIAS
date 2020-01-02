<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilUsersGalleryUser
 */
interface ilUsersGalleryUser
{
    /**
     * @return boolean
     */
    public function hasPublicProfile();

    /**
     * @return string
     */
    public function getPublicName();

    /**
     * @return string
     */
    public function getSortablePublicName();

    /**
     * @return ilObjUser
     */
    public function getAggregatedUser();
}
