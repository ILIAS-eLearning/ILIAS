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
use ILIAS\Data\Privacy\Purpose\Purpose;
use PHPUnit\Framework\Assert;

/**
 * Reusable assertions for testing privacy data types.
 */
trait PrivacyDataTypeAssertions
{
    protected function assertToStringDoesNotExposeValue(
        PrivacyDataType $type,
        string $raw_value
    ): void {
        Assert::assertStringNotContainsString($raw_value, (string) $type);
    }

    protected function assertResolvesTo(
        PrivacyDataType $type,
        mixed $expected_value,
        Purpose $purpose
    ): void {
        Assert::assertSame($expected_value, $type->resolve($purpose));
    }

    protected function assertSourceDescribes(
        PrivacyDataType $type,
        string $expected
    ): void {
        Assert::assertSame($expected, $type->getSource()->describe());
    }

    protected function assertResolveTriggersLog(
        PrivacyDataType $type,
        Purpose $purpose,
        InMemoryPrivacyLogger $logger
    ): void {
        $count_before = count($logger->getEntries());
        $type->resolve($purpose);
        Assert::assertCount($count_before + 1, $logger->getEntries());
        $logger->assertLastPurposeIs($purpose->describe());
    }
}
