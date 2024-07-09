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

namespace ILIAS\Glossary\Flashcard;

use PHPUnit\Framework\TestCase;

class FlashcardShuffleManagerTest extends TestCase
{
    protected const BOX_ENTRIES = [
        [
            "last_access" => "2024-01-01 01:00:00"
        ],
        [
            "last_access" => "2024-01-02 01:00:00"
        ],
        [
            "last_access" => "2024-01-02 02:00:00"
        ],
        [
            "last_access" => "2024-01-02 03:00:00"
        ],
        [
            "last_access" => "2024-01-02 04:00:00"
        ],
        [
            "last_access" => "2024-01-03 01:00:00"
        ],
        [
            "last_access" => "2024-01-04 02:00:00"
        ],
        [
            "last_access" => "2024-01-05 01:00:00"
        ],
        [
            "last_access" => "2024-02-01 01:00:00"
        ],
        [
            "last_access" => "2024-02-02 01:00:00"
        ],
        [
            "last_access" => "2024-02-03 01:00:00"
        ],
        [
            "last_access" => "2024-02-03 01:01:00"
        ],
        [
            "last_access" => "2024-02-03 02:00:00"
        ],
        [
            "last_access" => "2024-03-01 01:00:00"
        ],
    ];

    protected const BOX_ENTRIES_SHUFFLED = [
        [
            "last_access" => "2024-03-01 01:00:00"
        ],
        [
            "last_access" => "2024-02-03 02:00:00"
        ],
        [
            "last_access" => "2024-02-03 01:01:00"
        ],
        [
            "last_access" => "2024-02-03 01:00:00"
        ],
        [
            "last_access" => "2024-02-02 01:00:00"
        ],
        [
            "last_access" => "2024-02-01 01:00:00"
        ],
        [
            "last_access" => "2024-01-05 01:00:00"
        ],
        [
            "last_access" => "2024-01-04 02:00:00"
        ],
        [
            "last_access" => "2024-01-03 01:00:00"
        ],
        [
            "last_access" => "2024-01-02 04:00:00"
        ],
        [
            "last_access" => "2024-01-02 03:00:00"
        ],
        [
            "last_access" => "2024-01-02 02:00:00"
        ],
        [
            "last_access" => "2024-01-02 01:00:00"
        ],
        [
            "last_access" => "2024-01-01 01:00:00"
        ]
    ];

    protected const BOX_ENTRIES_SHUFFLED_EQUAL_DAY = [
        [
            "last_access" => "2024-01-01 01:00:00"
        ],
        [
            "last_access" => "2024-01-02 04:00:00"
        ],
        [
            "last_access" => "2024-01-02 03:00:00"
        ],
        [
            "last_access" => "2024-01-02 02:00:00"
        ],
        [
            "last_access" => "2024-01-02 01:00:00"
        ],
        [
            "last_access" => "2024-01-03 01:00:00"
        ],
        [
            "last_access" => "2024-01-04 02:00:00"
        ],
        [
            "last_access" => "2024-01-05 01:00:00"
        ],
        [
            "last_access" => "2024-02-01 01:00:00"
        ],
        [
            "last_access" => "2024-02-02 01:00:00"
        ],
        [
            "last_access" => "2024-02-03 02:00:00"
        ],
        [
            "last_access" => "2024-02-03 01:01:00"
        ],
        [
            "last_access" => "2024-02-03 01:00:00"
        ],
        [
            "last_access" => "2024-03-01 01:00:00"
        ],
    ];

    protected function getShuffleManagerMock(): FlashcardShuffleManager
    {
        return new class () extends FlashcardShuffleManager {
            public function __construct()
            {

            }

            protected function shuffle(array $entries): array
            {
                array_multisort( array_column($entries, "last_access"), SORT_DESC, $entries);

                return $entries;
            }
        };
    }

    public function testShuffleEntries(): void
    {
        $manager = $this->getShuffleManagerMock();

        $entries = $manager->shuffleEntries(self::BOX_ENTRIES);

        $this->assertSame(self::BOX_ENTRIES_SHUFFLED, $entries);
    }

    public function testShuffleEntriesWithEqualDay(): void
    {
        $manager = $this->getShuffleManagerMock();

        $entries = $manager->shuffleEntriesWithEqualDay(self::BOX_ENTRIES);

        $this->assertSame(self::BOX_ENTRIES_SHUFFLED_EQUAL_DAY, $entries);
    }
}
