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

namespace ILIAS\EmployeeTalk\ObjectType;

use ILIAS\ILIASObject\Definition\AbstractObjectType;
use ILIAS\ILIASObject\Definition\ObjectTypeFlag;

class EmployeeTalkObjectType extends AbstractObjectType
{
    public function getId(): string
    {
        return 'etal';
    }

    public function getClassName(): string
    {
        return 'EmployeeTalk';
    }

    public function getLocation(): string
    {
        return $this->getComponent() . '/classes/Talk';
    }

    public function getFlags(): array
    {
        return [
            ObjectTypeFlag::CHECKBOX,
            ObjectTypeFlag::INHERIT,
            ObjectTypeFlag::ADMINISTRATION,
            ObjectTypeFlag::ORGUNIT_PERMISSIONS,
        ];
    }

    public function getTranslationType(): string
    {
        return 'db';
    }

    public function getGroup(): ?string
    {
        return 'tala';
    }

    public function getDefaultPosition(): int
    {
        return 10;
    }

    public function getDefaultPresentationPosition(): int
    {
        return 10;
    }

    public function getParentTypes(): array
    {
        return [
            'tals' => 0,
        ];
    }
}
