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
 * HTML message naming the entries with disallowed markup: "lng_markup_not_allowed" followed by the
 * entries, each with its language-neutral markup (`tos#:#tos_agree_date: <script>, </form>; …`, see
 * TranslationMarkupPolicy::markupOf()), at most PlainLogText::MAX_LISTED_KEYS. Keys come from the
 * client and the markup quotes the value, so the list is escaped; the values are not repeated.
 */
final class InvalidMarkupMessage
{
    /**
     * @param array<string|int, list<string>> $invalid_values key => violations (as
     *        TranslationMarkupPolicy::findViolations() reports them; empty: only the key is named)
     */
    public static function of(Language $lng, Refinery $refinery, array $invalid_values): string
    {
        $policy = new TranslationMarkupPolicy();
        $entries = [];
        foreach ($invalid_values as $key => $violations) {
            $entries[] = $violations === []
                ? (string) $key
                : sprintf('%s: %s', $key, implode(', ', $policy->markupOf(array_map('strval', $violations))));
        }
        $list = $refinery->encode()->htmlSpecialCharsAsEntities()->transform(
            PlainLogText::of(PlainLogText::keyList($entries, '; '))
        );

        // A translation without the placeholder (or the text of a missing key) still names the entries.
        // str_replace(), not sprintf(): a locally changed text with another "%" must not fail
        $text = $lng->txt('lng_markup_not_allowed');

        return str_contains($text, '%s') ? str_replace('%s', $list, $text) : $text . ' ' . $list;
    }
}
