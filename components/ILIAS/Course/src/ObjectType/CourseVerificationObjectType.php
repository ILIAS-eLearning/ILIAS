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

namespace ILIAS\Course\ObjectType;

use ILIAS\ILIASObject\Definition\AbstractObjectType;

class CourseVerificationObjectType extends AbstractObjectType
{
    public function getId(): string
    {
        return 'crsv';
    }

    public function getClassName(): string
    {
        return 'CourseVerification';
    }

    public function getLocation(): string
    {
        return $this->getComponent() . '/classes/Verification';
    }

    public function getFlags(): array
    {
        return [];
    }

    public function getDefaultPosition(): int
    {
        return 90;
    }

    public function getDefaultPresentationPosition(): int
    {
        return 150;
    }

    public function getParentTypes(): array
    {
        return [
            'wsrt' => 0,
            'wfld' => 0,
        ];
    }
}
