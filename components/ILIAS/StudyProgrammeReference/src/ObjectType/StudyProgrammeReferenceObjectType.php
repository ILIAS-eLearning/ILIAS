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

namespace ILIAS\StudyProgrammeReference\ObjectType;

use ILIAS\ILIASObject\Definition\AbstractObjectType;
use ILIAS\ILIASObject\Definition\ObjectTypeFlag;

class StudyProgrammeReferenceObjectType extends AbstractObjectType
{
    public function getId(): string
    {
        return 'prgr';
    }

    public function getClassName(): string
    {
        return 'StudyProgrammeReference';
    }

    public function getFlags(): array
    {
        return [
            ObjectTypeFlag::CHECKBOX,
            ObjectTypeFlag::ALLOW_LINK,
            ObjectTypeFlag::ALLOW_COPY,
            ObjectTypeFlag::RBAC,
            ObjectTypeFlag::REPOSITORY,
        ];
    }

    public function getGroup(): ?string
    {
        return 'prg';
    }

    public function getDefaultPosition(): int
    {
        return 25;
    }

    public function getDefaultPresentationPosition(): int
    {
        return 20;
    }

    public function getParentTypes(): array
    {
        return [
            'prg' => 0,
            'cat' => 0,
            'recf' => 0,
            'root' => 0,
        ];
    }
}
