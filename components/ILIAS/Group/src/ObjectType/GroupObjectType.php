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

namespace ILIAS\Group\ObjectType;

use ILIAS\ILIASObject\Definition\AbstractObjectType;
use ILIAS\ILIASObject\Definition\ObjectTypeFlag;

class GroupObjectType extends AbstractObjectType
{
    public function getId(): string
    {
        return 'grp';
    }

    public function getClassName(): string
    {
        return 'Group';
    }

    public function getFlags(): array
    {
        return [
            ObjectTypeFlag::CHECKBOX,
            ObjectTypeFlag::INHERIT,
            ObjectTypeFlag::ALLOW_COPY,
            ObjectTypeFlag::RBAC,
            ObjectTypeFlag::EXPORT,
            ObjectTypeFlag::REPOSITORY,
            ObjectTypeFlag::AMET,
            ObjectTypeFlag::ORGUNIT_PERMISSIONS,
            ObjectTypeFlag::LTI_PROVIDER,
            ObjectTypeFlag::OFFLINE_HANDLING,
        ];
    }

    public function getTranslationType(): string
    {
        return 'db';
    }

    public function getDefaultPosition(): int
    {
        return 50;
    }

    public function getDefaultPresentationPosition(): int
    {
        return 70;
    }

    public function getParentTypes(): array
    {
        return [
            'cat' => 0,
            'crs' => 0,
            'grp' => 0,
            'fold' => 0,
            'recf' => 0,
            'root' => 0,
        ];
    }
}
