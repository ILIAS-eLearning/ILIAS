<?php

declare(strict_types=1);

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

namespace ILIAS\Glossary\Flashcard;

use ILIAS\Data\Clock\ClockInterface;
use ILIAS\Data\Factory as DataFactory;

/**
 * @author Thomas Famula <famula@leifos.de>
 */
class FlashcardShuffleManager
{
    public function __construct()
    {
    }

    public function shuffleEntries(
        array $box_entries
    ): array {
        shuffle($box_entries);
        return $box_entries;
    }

    public function shuffleEntriesWithEqualDay(
        array $box_entries
    ): array {
        $tmp_entries = [];
        $tmp_day = "";
        $i = 0;
        // split entries per day
        foreach ($box_entries as $entry) {
            $entry_day = substr($entry["last_access"], 0, 10);
            if (empty($tmp_day)
                || $entry_day === $tmp_day
            ) {
                $tmp_entries[$i][] = $entry;
            } else {
                $tmp_entries[++$i][] = $entry;
            }
            $tmp_day = $entry_day;
        }

        $entries = [];
        // shuffle entries with same day
        foreach ($tmp_entries as $entries_per_day) {
            shuffle($entries_per_day);
            foreach ($entries_per_day as $entry) {
                $entries[] = $entry;
            }
        }

        return $entries;
    }
}
