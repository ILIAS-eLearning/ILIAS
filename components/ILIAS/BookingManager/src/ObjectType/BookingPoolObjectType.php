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

namespace ILIAS\BookingManager\ObjectType;

use ILIAS\ILIASObject\Definition\AbstractObjectType;
use ILIAS\ILIASObject\Definition\ObjectTypeFlag;

class BookingPoolObjectType extends AbstractObjectType
{
    public function getId(): string
    {
        return 'book';
    }

    public function getClassName(): string
    {
        return 'BookingPool';
    }

    public function getFlags(): array
    {
        return [
            ObjectTypeFlag::CHECKBOX,
            ObjectTypeFlag::INHERIT,
            ObjectTypeFlag::ALLOW_LINK,
            ObjectTypeFlag::ALLOW_COPY,
            ObjectTypeFlag::RBAC,
            ObjectTypeFlag::REPOSITORY,
            ObjectTypeFlag::OFFLINE_HANDLING,
        ];
    }

    public function getDefaultPosition(): int
    {
        return 250;
    }

    public function getDefaultPresentationPosition(): int
    {
        return 250;
    }

    public function getParentTypes(): array
    {
        return [
            'cat' => 0,
            'crs' => 0,
            'grp' => 0,
            'root' => 0,
            'fold' => 0,
        ];
    }

    public function getSubTypes(): array
    {
        return [
            'bobj' => true,
        ];
    }
}
