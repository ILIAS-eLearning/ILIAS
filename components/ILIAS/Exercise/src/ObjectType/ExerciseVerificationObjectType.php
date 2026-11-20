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

namespace ILIAS\Exercise\ObjectType;

use ILIAS\ILIASObject\Definition\AbstractObjectType;

class ExerciseVerificationObjectType extends AbstractObjectType
{
    public function getId(): string
    {
        return 'excv';
    }

    public function getClassName(): string
    {
        return 'ExerciseVerification';
    }

    public function getFlags(): array
    {
        return [];
    }

    public function getDefaultPosition(): int
    {
        return 100;
    }

    public function getDefaultPresentationPosition(): int
    {
        return 160;
    }

    public function getParentTypes(): array
    {
        return [
            'wsrt' => 0,
            'wfld' => 0,
        ];
    }
}
