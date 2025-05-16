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

declare(strict_types=1);

class ilMailAddressTypeHelperImpl implements ilMailAddressTypeHelper
{
    public function __construct(protected string $installation_host)
    {
    }

    public function doesGroupNameExists(string $name): bool
    {
        return ilUtil::groupNameExists($name);
    }

    public function getGroupObjIdByTitle(string $title): int
    {
        return ilObjGroup::_lookupIdByTitle($title);
    }

    public function getInstanceByRefId(int $ref_id): ilObject
    {
        return ilObjectFactory::getInstanceByRefId($ref_id);
    }

    public function getAllRefIdsForObjId(int $obj_id): array
    {
        return array_map(intval(...), ilObject::_getAllReferences($obj_id));
    }

    public function getUserIdByLogin(string $login): int
    {
        return ilObjUser::getUserIdByLogin($login);
    }

    public function getInstallationHost(): string
    {
        return $this->installation_host;
    }

    public function getGlobalMailSystemId(): int
    {
        return ilMailGlobalServices::getMailObjectRefId();
    }

    public function receivesInternalMailsOnly(int $usr_id): bool
    {
        $options = new ilMailOptions($usr_id);

        return $options->getIncomingType() === ilMailOptions::INCOMING_LOCAL;
    }
}
