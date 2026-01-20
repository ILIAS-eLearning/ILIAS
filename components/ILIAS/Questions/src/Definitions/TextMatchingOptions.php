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

enum TextMatchingOptions: string
{
    case CaseInsensitive = 'case_insensitive';
    case CaseSensitive = 'case_sensitive';
    case Levenstein1 = '1';
    case Levenstein2 = '2';
    case Levenstein3 = '3';
    case Levenstein4 = '4';
    case Levenstein5 = '5';

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
}
