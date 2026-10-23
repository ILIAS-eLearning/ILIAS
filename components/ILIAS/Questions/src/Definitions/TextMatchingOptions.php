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

namespace ILIAS\Questions\Definitions;

use ILIAS\Language\Language;
use ILIAS\Refinery\Factory as Refinery;

enum TextMatchingOptions: string
{
    case CaseInsensitive = 'case_insensitive';
    case CaseSensitive = 'case_sensitive';
    case Levenshtein1 = '1';
    case Levenshtein2 = '2';
    case Levenshtein3 = '3';
    case Levenshtein4 = '4';
    case Levenshtein5 = '5';

    public function getLabel(
        Language $lng
    ): string {
        return match ($this) {
            self::CaseInsensitive, self::CaseSensitive => $lng->txt("cloze_textgap_{$this->value}"),
            default => sprintf($lng->txt('cloze_textgap_levenshtein_of'), $this->value)
        };
    }

    public static function buildOptionsList(
        Language $lng
    ): array {
        return array_reduce(
            self::cases(),
            function (array $c, self $v) use ($lng): array {
                $c[$v->value] = $v->getLabel($lng);
                return $c;
            },
            []
        );
    }

    public function determineEquivalence(
        Refinery $refinery,
        string $predefined_text,
        string $response_text
    ): bool {
        return match ($this) {
            self::CaseInsensitive => strtolower($predefined_text) === strtolower($response_text),
            self::CaseSensitive => $predefined_text === $response_text,
            self::Levenshtein1 => $refinery->string()->levenshtein()->standard(
                $predefined_text,
                1
            )->transform($response_text) >= 0,
            self::Levenshtein2 => $refinery->string()->levenshtein()->standard(
                $predefined_text,
                2
            )->transform($response_text) >= 0,
            self::Levenshtein3 => $refinery->string()->levenshtein()->standard(
                $predefined_text,
                3
            )->transform($response_text) >= 0,
            self::Levenshtein4 => $refinery->string()->levenshtein()->standard(
                $predefined_text,
                4
            )->transform($response_text) >= 0,
            self::Levenshtein5 => $refinery->string()->levenshtein()->standard(
                $predefined_text,
                5
            )->transform($response_text) >= 0
        };
    }
}
