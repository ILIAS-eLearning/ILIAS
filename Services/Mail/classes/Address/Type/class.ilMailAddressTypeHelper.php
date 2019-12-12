<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailAddressTypeHelper
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailAddressTypeHelperImpl implements ilMailAddressTypeHelper
{
    /** @var string */
    protected $installationHost = '';

    /**
     * ilMailAddressTypeHelperImpl constructor.
     * @param string $installationHost
     */
    public function __construct(string $installationHost)
    {
        $this->installationHost = $installationHost;
    }

    /**
     * @inheritdoc
     */
    public function doesGroupNameExists(string $name) : bool
    {
        return \ilUtil::groupNameExists($name);
    }

    /**
     * @inheritdoc
     */
    public function getGroupObjIdByTitle(string $title) : int
    {
        return (int) \ilObjGroup::_lookupIdByTitle($title);
    }

    /**
     * @inheritdoc
     */
    public function getInstanceByRefId(int $refId) : \ilObject
    {
        return \ilObjectFactory::getInstanceByRefId($refId);
    }

    /**
     * @inheritdoc
     */
    public function getAllRefIdsForObjId(int $objId) : array
    {
        return \ilObject::_getAllReferences($objId);
    }

    /**
     * @inheritdoc
     */
    public function getUserIdByLogin(string $login) : int
    {
        return (int) \ilObjUser::getUserIdByLogin($login);
    }

    /**
     * @inheritdoc
     */
    public function getInstallationHost() : string
    {
        return $this->installationHost;
    }

    /**
     * @inheritdoc
     */
    public function getGlobalMailSystemId() : int
    {
        return (int) \ilMailGlobalServices::getMailObjectRefId();
    }

    /**
     * @param int $usrId
     * @return bool
     */
    public function receivesInternalMailsOnly(int $usrId) : bool
    {
        $options = new \ilMailOptions($usrId);

        return (int) $options->getIncomingType() === (int) \ilMailOptions::INCOMING_LOCAL;
    }
}
