<?php

declare(strict_types=1);

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

    public function doesGroupNameExists(string $name): bool
    {
        return ilUtil::groupNameExists($name);
    }

    public function getGroupObjIdByTitle(string $title): int
    {
        return ilObjGroup::_lookupIdByTitle($title);
    }

    public function getInstanceByRefId(int $refId): ilObject
    {
        return ilObjectFactory::getInstanceByRefId($refId);
    }

    public function getAllRefIdsForObjId(int $objId): array
    {
        return array_map('intval', ilObject::_getAllReferences($objId));
    }

    public function getUserIdByLogin(string $login): int
    {
        return ilObjUser::getUserIdByLogin($login);
    }

    public function getInstallationHost(): string
    {
        return $this->installationHost;
    }

    public function getGlobalMailSystemId(): int
    {
        return ilMailGlobalServices::getMailObjectRefId();
    }

    public function receivesInternalMailsOnly(int $usrId): bool
    {
        $options = new ilMailOptions($usrId);

        return $options->getIncomingType() === ilMailOptions::INCOMING_LOCAL;
    }
}
