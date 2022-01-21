<?php declare(strict_types=1);
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

interface ilUsersGalleryUser
{
    public function hasPublicProfile() : bool;

    public function getPublicName() : string;

    public function getSortablePublicName() : string;

    public function getAggregatedUser() : ilObjUser;
}
