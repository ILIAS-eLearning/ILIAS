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

namespace ILIAS\LearningModule\ObjectType;

use ILIAS\ILIASObject\Definition\AbstractObjectType;
use ILIAS\ILIASObject\Definition\ObjectTypeFlag;

class LearningModuleObjectType extends AbstractObjectType
{
    public function getId(): string
    {
        return 'lm';
    }

    public function getClassName(): string
    {
        return 'LearningModule';
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
            ObjectTypeFlag::LTI_PROVIDER,
            ObjectTypeFlag::OFFLINE_HANDLING,
        ];
    }

    public function getTranslationType(): string
    {
        return 'db';
    }

    public function getGroup(): ?string
    {
        return 'lres';
    }

    public function getDefaultPosition(): int
    {
        return 120;
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
}
