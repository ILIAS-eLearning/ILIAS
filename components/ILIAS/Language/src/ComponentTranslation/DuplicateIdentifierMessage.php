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

namespace ILIAS\Language\ComponentTranslation;

use ILIAS\Language\Language;
use ILIAS\Refinery\Factory as Refinery;

/**
 * Texts naming entries left out or rejected because their identifier exists in another module
 * (see IdentifierUniqueness): "lng_identifier_not_unique" for the screen, a bounded line for the log.
 * Keys come from uploaded files, so every entry is shortened and the list is escaped.
 */
final class DuplicateIdentifierMessage
{
    private const int MAX_ENTRY_WIDTH = 200;
    private const int MAX_LOGGED_ENTRIES = 200;

    /** @param list<string> $existing "<module>: <identifier>" the entry would duplicate */
    public static function entry(string $key, array $existing): string
    {
        return sprintf('%s (%s)', $key, implode(', ', $existing));
    }

    /** @param list<string> $entries see entry() */
    public static function of(Language $lng, Refinery $refinery, array $entries): string
    {
        $list = $refinery->encode()->htmlSpecialCharsAsEntities()->transform(
            PlainLogText::of(PlainLogText::keyList(self::shortened($entries), '; '))
        );

        // str_replace(), not sprintf(): a locally changed text with another "%" must not fail
        $text = $lng->txt('lng_identifier_not_unique');

        return str_contains($text, '%s') ? str_replace('%s', $list, $text) : $text . ' ' . $list;
    }

    /** @param list<string> $entries see entry() */
    public static function forLog(array $entries): string
    {
        $logged = implode('; ', self::shortened(array_slice($entries, 0, self::MAX_LOGGED_ENTRIES)));
        $left_out = count($entries) - self::MAX_LOGGED_ENTRIES;

        return PlainLogText::of(sprintf(
            'Language entries rejected or not applied, their identifier exists in another module or in another spelling: %s%s',
            $logged,
            $left_out > 0 ? sprintf(' … (+%d)', $left_out) : ''
        ));
    }

    /**
     * @param list<string> $entries
     * @return list<string>
     */
    private static function shortened(array $entries): array
    {
        return array_map(static fn(string $entry): string => mb_strimwidth($entry, 0, self::MAX_ENTRY_WIDTH, '…'), $entries);
    }
}
