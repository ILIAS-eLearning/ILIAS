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

namespace ILIAS\AdvancedMetaData\Data;

use PHPUnit\Framework\TestCase;

class PersistenceTrackingDataTest extends TestCase
{
    protected function getData(
        bool $persisted,
        PersistenceTrackingData ...$sub_data
    ): PersistenceTrackingDataImplementation {
        return new class ($persisted, $sub_data) extends PersistenceTrackingDataImplementation {
            public function __construct(
                protected bool $persisted,
                protected array $sub_data
            ) {
            }

            public function isPersisted(): bool
            {
                return $this->persisted;
            }

            protected function getSubData(): \Generator
            {
                yield from $this->sub_data;
            }

            public function exposeMarkAsChanged(): void
            {
                $this->markAsChanged();
            }
        };
    }

    public function testContainsChangesTrueIfNotPersisted(): void
    {
        $data = $this->getData(false);
        $this->assertTrue($data->containsChanges());
    }

    public function testContainsChangesFalse(): void
    {
        $data = $this->getData(true);
        $this->assertFalse($data->containsChanges());
    }

    public function testContainsChangesTrue(): void
    {
        $data = $this->getData(true);
        $data->exposeMarkAsChanged();

        $this->assertTrue($data->containsChanges());
    }

    public function testContainsChangesTrueWithUnchangedSubData(): void
    {
        $data = $this->getData(
            true,
            $this->getData(true),
            $this->getData(true)
        );
        $data->exposeMarkAsChanged();

        $this->assertTrue($data->containsChanges());
    }

    public function testContainsChangesFalseWithUnchangedSubData(): void
    {
        $data = $this->getData(
            true,
            $this->getData(true),
            $this->getData(true)
        );
        $data->exposeMarkAsChanged();

        $this->assertTrue($data->containsChanges());
    }

    public function testContainsChangesTrueFromSubData(): void
    {
        $sub_data_1 = $this->getData(true);
        $sub_data_2 = $this->getData(true);
        $sub_data_1->exposeMarkAsChanged();
        $data = $this->getData(true, $sub_data_1, $sub_data_2);

        $this->assertTrue($data->containsChanges());
    }
}
