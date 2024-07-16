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
    protected static array $box_entries = [];
    protected static array $box_entries_shuffled = [];
    protected static array $box_entries_shuffled_equal_day = [];

    public static function setUpBeforeClass(): void
    {
        if (empty(self::$box_entries)) {
            self::initializeBoxEntries();
        }
        if (empty(self::$box_entries_shuffled)) {
            self::initializeBoxEntriesShuffled();
        }
        if (empty(self::$box_entries_shuffled_equal_day)) {
            self::initializeBoxEntriesShuffledEqualDay();
        }
    }

    protected static function initializeBoxEntries(): void
    {
        self::$box_entries = [
            new Term(1, 11, 111, 1, "2024-01-01 01:00:00"),
            new Term(2, 11, 111, 1, "2024-01-02 01:00:00"),
            new Term(3, 11, 111, 1, "2024-01-02 02:00:00"),
            new Term(4, 11, 111, 1, "2024-01-02 03:00:00"),
            new Term(5, 11, 111, 1, "2024-01-02 04:00:00"),
            new Term(6, 11, 111, 1, "2024-01-03 01:00:00"),
            new Term(7, 11, 111, 1, "2024-01-04 02:00:00"),
            new Term(8, 11, 111, 1, "2024-01-05 01:00:00"),
            new Term(9, 11, 111, 1, "2024-02-01 01:00:00"),
            new Term(10, 11, 111, 1, "2024-02-02 01:00:00"),
            new Term(11, 11, 111, 1, "2024-02-03 01:00:00"),
            new Term(12, 11, 111, 1, "2024-02-03 01:01:00"),
            new Term(13, 11, 111, 1, "2024-02-03 02:00:00"),
            new Term(14, 11, 111, 1, "2024-03-01 01:00:00")
        ];
    }

    protected static function initializeBoxEntriesShuffled(): void
    {
        self::$box_entries_shuffled = [
            new Term(14, 11, 111, 1, "2024-03-01 01:00:00"),
            new Term(13, 11, 111, 1, "2024-02-03 02:00:00"),
            new Term(12, 11, 111, 1, "2024-02-03 01:01:00"),
            new Term(11, 11, 111, 1, "2024-02-03 01:00:00"),
            new Term(10, 11, 111, 1, "2024-02-02 01:00:00"),
            new Term(9, 11, 111, 1, "2024-02-01 01:00:00"),
            new Term(8, 11, 111, 1, "2024-01-05 01:00:00"),
            new Term(7, 11, 111, 1, "2024-01-04 02:00:00"),
            new Term(6, 11, 111, 1, "2024-01-03 01:00:00"),
            new Term(5, 11, 111, 1, "2024-01-02 04:00:00"),
            new Term(4, 11, 111, 1, "2024-01-02 03:00:00"),
            new Term(3, 11, 111, 1, "2024-01-02 02:00:00"),
            new Term(2, 11, 111, 1, "2024-01-02 01:00:00"),
            new Term(1, 11, 111, 1, "2024-01-01 01:00:00"),
        ];
    }

    protected static function initializeBoxEntriesShuffledEqualDay(): void
    {
        self::$box_entries_shuffled_equal_day = [
            new Term(1, 11, 111, 1, "2024-01-01 01:00:00"),
            new Term(5, 11, 111, 1, "2024-01-02 04:00:00"),
            new Term(4, 11, 111, 1, "2024-01-02 03:00:00"),
            new Term(3, 11, 111, 1, "2024-01-02 02:00:00"),
            new Term(2, 11, 111, 1, "2024-01-02 01:00:00"),
            new Term(6, 11, 111, 1, "2024-01-03 01:00:00"),
            new Term(7, 11, 111, 1, "2024-01-04 02:00:00"),
            new Term(8, 11, 111, 1, "2024-01-05 01:00:00"),
            new Term(9, 11, 111, 1, "2024-02-01 01:00:00"),
            new Term(10, 11, 111, 1, "2024-02-02 01:00:00"),
            new Term(13, 11, 111, 1, "2024-02-03 02:00:00"),
            new Term(12, 11, 111, 1, "2024-02-03 01:01:00"),
            new Term(11, 11, 111, 1, "2024-02-03 01:00:00"),
            new Term(14, 11, 111, 1, "2024-03-01 01:00:00")
        ];
    }

    protected function getShuffleManagerMock(): FlashcardShuffleManager
    {
        return new class () extends FlashcardShuffleManager {
            public function __construct()
            {
            }

            protected function shuffle(array $entries): array
            {
                usort($entries, fn($a, $b) => strcmp($b->getLastAccess(), $a->getLastAccess()));

                return $entries;
            }
        };
    }

    public function testShuffleEntries(): void
    {
        $manager = $this->getShuffleManagerMock();

        $entries = $manager->shuffleEntries(self::$box_entries);

        $this->assertEquals(self::$box_entries_shuffled, $entries);
    }

    public function testShuffleEntriesWithEqualDay(): void
    {
        $manager = $this->getShuffleManagerMock();

        $entries = $manager->shuffleEntriesWithEqualDay(self::$box_entries);

        $this->assertEquals(self::$box_entries_shuffled_equal_day, $entries);
    }
}
