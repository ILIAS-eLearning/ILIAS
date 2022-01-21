<?php declare(strict_types=1);
/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailAddressTypeHelper
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailAddressTypeHelperImpl implements ilMailAddressTypeHelper
{
    protected string $installationHost = '';

    public function __construct(string $installationHost)
    {
        $this->installationHost = $installationHost;
    }

    public function doesGroupNameExists(string $name) : bool
    {
        return ilUtil::groupNameExists($name);
    }

    public function getGroupObjIdByTitle(string $title) : int
    {
        return ilObjGroup::_lookupIdByTitle($title);
    }

    public function getInstanceByRefId(int $refId) : ilObject
    {
        return ilObjectFactory::getInstanceByRefId($refId);
    }

    public function getAllRefIdsForObjId(int $objId) : array
    {
        return array_map('intval', ilObject::_getAllReferences($objId));
    }

    public function getUserIdByLogin(string $login) : int
    {
        return ilObjUser::getUserIdByLogin($login);
    }

    public function getInstallationHost() : string
    {
        return $this->installationHost;
    }

    public function getGlobalMailSystemId() : int
    {
        return ilMailGlobalServices::getMailObjectRefId();
    }

    public function receivesInternalMailsOnly(int $usrId) : bool
    {
        $options = new ilMailOptions($usrId);

        return $options->getIncomingType() === ilMailOptions::INCOMING_LOCAL;
    }
}
