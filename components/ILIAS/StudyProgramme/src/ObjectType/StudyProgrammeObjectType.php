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

namespace ILIAS\StudyProgramme\ObjectType;

use ILIAS\ILIASObject\Definition\AbstractObjectType;
use ILIAS\ILIASObject\Definition\ObjectTypeFlag;

class StudyProgrammeObjectType extends AbstractObjectType
{
    public function getId(): string
    {
        return 'prg';
    }

    public function getClassName(): string
    {
        return 'StudyProgramme';
    }

    public function getFlags(): array
    {
        return [
            ObjectTypeFlag::CHECKBOX,
            ObjectTypeFlag::INHERIT,
            ObjectTypeFlag::ALLOW_COPY,
            ObjectTypeFlag::RBAC,
            ObjectTypeFlag::REPOSITORY,
            ObjectTypeFlag::ORGUNIT_PERMISSIONS,
        ];
    }

    public function getTranslationType(): string
    {
        return 'db';
    }

    public function getDefaultPosition(): int
    {
        return 30;
    }

    public function getDefaultPresentationPosition(): int
    {
        return 30;
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

    public function getSubObjectTypes(): array
    {
        return [
            'rolf' => 1,
            'crsr' => 1,
        ];
    }

    public function getSubTypes(): array
    {
        return [
            'prg_type' => true,
        ];
    }
}
