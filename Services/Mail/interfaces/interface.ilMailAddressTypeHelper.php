<?php declare(strict_types=1);
/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailAddressTypeHelper
 * @author Michael Jansen <mjansen@databay.de>
 */
interface ilMailAddressTypeHelper
{
    public function doesGroupNameExists(string $name) : bool;

    public function getGroupObjIdByTitle(string $title) : int;

    public function getInstanceByRefId(int $refId) : ilObject;

    /**
     * @return int[]
     */
    public function getAllRefIdsForObjId(int $objId) : array;

    public function getUserIdByLogin(string $login) : int;

    public function getInstallationHost() : string;

    public function getGlobalMailSystemId() : int;

    public function receivesInternalMailsOnly(int $usrId) : bool;
}
