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

namespace ILIAS\User\ObjectType;

use ILIAS\ILIASObject\Definition\AbstractObjectType;
use ILIAS\ILIASObject\Definition\ObjectTypeFlag;

class UserFolderObjectType extends AbstractObjectType
{
    public function getId(): string
    {
        return 'usrf';
    }

    public function getClassName(): string
    {
        return 'UserFolder';
    }

    public function getFlags(): array
    {
        return [
            ObjectTypeFlag::INHERIT,
            ObjectTypeFlag::RBAC,
            ObjectTypeFlag::SYSTEM,
            ObjectTypeFlag::REPOSITORY,
            ObjectTypeFlag::ADMINISTRATION,
            ObjectTypeFlag::ORGUNIT_PERMISSIONS,
        ];
    }

    public function getTranslationType(): string
    {
        return 'sys';
    }

    public function getParentTypes(): array
    {
        return [
            'adm' => 1,
        ];
    }

    public function getSubObjectTypes(): array
    {
        return [
            'usr' => 0,
        ];
    }
}
