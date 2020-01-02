<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailAddressTypeHelper
 * @author Michael Jansen <mjansen@databay.de>
 */
interface ilMailAddressTypeHelper
{
    /**
     * @param string $name
     * @return bool
     */
    public function doesGroupNameExists(string $name) : bool;

    /**
     * @param string $title
     * @return int
     */
    public function getGroupObjIdByTitle(string $title) : int;

    /**
     * @param int $refId
     * @return \ilObject
     */
    public function getInstanceByRefId(int $refId) : \ilObject;

    /**
     * @param int $objId
     * @return int[]
     */
    public function getAllRefIdsForObjId(int $objId) : array;

    /**
     * @param string $login
     * @return int
     */
    public function getUserIdByLogin(string $login) : int;

    /**
     * @return string
     */
    public function getInstallationHost() : string;

    /**
     * @return int
     */
    public function getGlobalMailSystemId() : int;

    /**
     * @param int $usrId
     * @return bool
     */
    public function receivesInternalMailsOnly(int $usrId) : bool;
}
