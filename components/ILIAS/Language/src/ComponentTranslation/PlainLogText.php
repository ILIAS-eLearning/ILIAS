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

/**
 * Text taken from language files or entries (keys, values, file names) for a terminal or log line:
 * control and format characters (e.g. an escape sequence or a bidirectional override) must not reach
 * the terminal or split the log line. Long key lists are shortened for messages.
 */
final class PlainLogText
{
    public const int MAX_LISTED_KEYS = 20;

    public static function of(string $text): string
    {
        return preg_replace('/[\p{Cc}\p{Cf}]/u', '?', $text)
            ?? (string) preg_replace('/[\x00-\x1F\x7F]/', '?', $text);
    }

    /**
     * The first MAX_LISTED_KEYS of $keys, then "… (+N)" for the rest (the full list belongs into the
     * log). Not escaped for HTML.
     *
     * @param list<string> $keys
     */
    public static function keyList(array $keys, string $separator = ', '): string
    {
        $listed = implode($separator, array_slice($keys, 0, self::MAX_LISTED_KEYS));
        $left_out = count($keys) - self::MAX_LISTED_KEYS;

        return $left_out > 0 ? sprintf('%s … (+%d)', $listed, $left_out) : $listed;
    }
}
