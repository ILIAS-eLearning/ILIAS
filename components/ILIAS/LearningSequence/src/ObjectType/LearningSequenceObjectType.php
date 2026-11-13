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

namespace ILIAS\LearningSequence\ObjectType;

use ILIAS\ILIASObject\Definition\AbstractObjectType;
use ILIAS\ILIASObject\Definition\ObjectTypeFlag;

class LearningSequenceObjectType extends AbstractObjectType
{
    public function getId(): string
    {
        return 'lso';
    }

    public function getClassName(): string
    {
        return 'LearningSequence';
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
            ObjectTypeFlag::OFFLINE_HANDLING,
        ];
    }

    public function getGroup(): ?string
    {
        return 'lso';
    }

    public function getDefaultPosition(): int
    {
        return 30;
    }

    public function getDefaultPresentationPosition(): int
    {
        return 300;
    }

    public function getParentTypes(): array
    {
        return [
            'root' => 0,
            'cat' => 0,
            'crs' => 0,
            'grp' => 0,
            'fold' => 0,
        ];
    }

    public function getSubObjectTypes(): array
    {
        return [
            'rolf' => 1,
            'htlm' => 0,
            'sahs' => 0,
            'tst' => 0,
            'svy' => 0,
            'copa' => 0,
            'exc' => 0,
            'lm' => 0,
            'iass' => 0,
            'file' => 0,
        ];
    }
}
