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

namespace ILIAS\RemoteGroup\ObjectType;

use ILIAS\ILIASObject\Definition\AbstractObjectType;
use ILIAS\ILIASObject\Definition\ObjectTypeFlag;

class RemoteGroupObjectType extends AbstractObjectType
{
    public function getId(): string
    {
        return 'rgrp';
    }

    public function getClassName(): string
    {
        return 'RemoteGroup';
    }

    public function getFlags(): array
    {
        return [
            ObjectTypeFlag::CHECKBOX,
            ObjectTypeFlag::ALLOW_LINK,
            ObjectTypeFlag::ALLOW_COPY,
            ObjectTypeFlag::RBAC,
        ];
    }

    public function getGroup(): ?string
    {
        return 'grp';
    }

    public function getDefaultPosition(): int
    {
        return 30;
    }

    public function getDefaultPresentationPosition(): int
    {
        return 40;
    }

    public function getParentTypes(): array
    {
        return [
            'cat' => 0,
            'root' => 0,
            'crs' => 0,
            'grp' => 0,
        ];
    }
}
