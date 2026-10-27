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

namespace ILIAS\Tests\KeyValueStorage;

use ILIAS\Data\Factory as DataFactory;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Transformation;

trait RefineryHelper
{
    private function refinery(): Refinery
    {
        $language = $this->getMockBuilder(\ilLanguage::class)
            ->disableOriginalConstructor()
            ->getMock();

        return new Refinery(new DataFactory(), $language);
    }

    private function asStored(): Transformation
    {
        return $this->refinery()->identity();
    }

    private function withDefault(mixed $default): Transformation
    {
        return $this->refinery()->custom()->transformation(
            static fn(mixed $value): mixed => $value ?? $default
        );
    }
}
