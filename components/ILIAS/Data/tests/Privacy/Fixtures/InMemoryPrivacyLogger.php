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

use ILIAS\Data\Privacy\Logger\PrivacyLogger;
use ILIAS\Data\Privacy\PrivacyDataType;
use ILIAS\Data\Privacy\Purpose\Purpose;
use PHPUnit\Framework\Assert;

/**
 * Test double recording all resolve() calls in memory, with assertion
 * helpers for use in unit tests.
 */
final class InMemoryPrivacyLogger implements PrivacyLogger
{
    /**
     * @var list<array{data_type: class-string, source: string, purpose: string, timestamp: int}>
     */
    private array $entries = [];

    public function log(PrivacyDataType $data, Purpose $purpose): void
    {
        $this->entries[] = [
            'data_type' => $data::class,
            'source' => $data->getSource()->describe(),
            'purpose' => $purpose->describe(),
            'timestamp' => time(),
        ];
    }

    /**
     * @return list<array{data_type: class-string, source: string, purpose: string, timestamp: int}>
     */
    public function getEntries(): array
    {
        return $this->entries;
    }

    public function reset(): void
    {
        $this->entries = [];
    }

    public function assertLoggedOnce(): void
    {
        Assert::assertCount(1, $this->entries);
    }

    public function assertLoggedTimes(int $times): void
    {
        Assert::assertCount($times, $this->entries);
    }

    public function assertNothingLogged(): void
    {
        Assert::assertSame([], $this->entries);
    }

    public function assertLastPurposeIs(string $expected): void
    {
        Assert::assertNotEmpty($this->entries);
        Assert::assertSame($expected, $this->entries[array_key_last($this->entries)]['purpose']);
    }

    public function assertLastSourceIs(string $expected): void
    {
        Assert::assertNotEmpty($this->entries);
        Assert::assertSame($expected, $this->entries[array_key_last($this->entries)]['source']);
    }

    public function assertLastDataTypeIs(string $expected): void
    {
        Assert::assertNotEmpty($this->entries);
        Assert::assertSame($expected, $this->entries[array_key_last($this->entries)]['data_type']);
    }

    public function assertContainsPurpose(string $purpose_describe): void
    {
        Assert::assertContains(
            $purpose_describe,
            array_column($this->entries, 'purpose')
        );
    }
}
