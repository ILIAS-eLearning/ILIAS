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

namespace ILIAS\MediaPool\ObjectType;

use ILIAS\ILIASObject\Definition\AbstractObjectType;
use ILIAS\ILIASObject\Definition\ObjectTypeFlag;

class MediaPoolObjectType extends AbstractObjectType
{
    public function getId(): string
    {
        return 'mep';
    }

    public function getClassName(): string
    {
        return 'MediaPool';
    }

    public function getFlags(): array
    {
        return [
            ObjectTypeFlag::CHECKBOX,
            ObjectTypeFlag::INHERIT,
            ObjectTypeFlag::ALLOW_LINK,
            ObjectTypeFlag::ALLOW_COPY,
            ObjectTypeFlag::RBAC,
            ObjectTypeFlag::EXPORT,
            ObjectTypeFlag::REPOSITORY,
            ObjectTypeFlag::OFFLINE_HANDLING,
        ];
    }

    public function getTranslationType(): string
    {
        return 'db';
    }

    public function getDefaultPosition(): int
    {
        return 200;
    }

    public function getDefaultPresentationPosition(): int
    {
        return 190;
    }

    public function getParentTypes(): array
    {
        return [
            'cat' => 0,
            'crs' => 0,
            'fold' => 0,
            'grp' => 0,
            'recf' => 0,
            'root' => 0,
        ];
    }

    public function getSubTypes(): array
    {
        return [
            'mob' => true,
            'mpg' => true,
        ];
    }
}
