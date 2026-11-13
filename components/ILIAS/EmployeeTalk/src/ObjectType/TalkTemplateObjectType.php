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

class TalkTemplateObjectType extends AbstractObjectType
{
    public function getId(): string
    {
        return 'talt';
    }

    public function getClassName(): string
    {
        return 'TalkTemplate';
    }

    public function getFlags(): array
    {
        return [
            ObjectTypeFlag::CHECKBOX,
            ObjectTypeFlag::INHERIT,
            ObjectTypeFlag::RBAC,
            ObjectTypeFlag::ADMINISTRATION,
            ObjectTypeFlag::OFFLINE_HANDLING,
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
            'tala' => 0,
        ];
    }

    public function getSubTypes(): array
    {
        return [
            'etal' => true,
        ];
    }
}
