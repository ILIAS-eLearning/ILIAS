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

namespace ILIAS\Data\Privacy\Fixtures;

use ILIAS\Data\Privacy\PrivacyDataType;
use ILIAS\Data\Privacy\TechnicalRequirement\TechnicalRequirement;
use PHPUnit\Framework\Assert;

/**
 * Reusable assertions for testing privacy data types.
 */
trait PrivacyDataTypeAssertions
{
    /**
     * @param PrivacyDataType<mixed> $type
     */
    protected function assertToStringDoesNotExposeValue(
        PrivacyDataType $type,
        string $raw_value
    ): void {
        Assert::assertStringNotContainsString($raw_value, (string) $type);
    }

    /**
     * @param PrivacyDataType<mixed> $type
     */
    protected function assertResolvesTo(
        PrivacyDataType $type,
        mixed $expected_value,
        TechnicalRequirement $requirement
    ): void {
        Assert::assertSame($expected_value, $type->resolve($requirement));
    }

    /**
     * @param PrivacyDataType<mixed> $type
     */
    protected function assertSourceDescribes(
        PrivacyDataType $type,
        string $expected
    ): void {
        Assert::assertSame($expected, $type->getSource()->describe());
    }

}
