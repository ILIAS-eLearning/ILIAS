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

namespace ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Definitions;

use ILIAS\Language\Language;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Component\Input\Field\Select;

enum ScoringIdentical: string
{
    case ScoreAll = 'score_all';
    case OnlyScoreDistinct = 'score_distinct';

    public static function buildInput(
        Language $lng,
        FieldFactory $ff,
        Refinery $refinery,
        self $default_value
    ): Select {
        return $ff->select(
            $lng->txt('scoring_of_identical_responses'),
            self::buildOptionsList($lng)
        )->withRequired(true)
        ->withAdditionalTransformation(
            $refinery->custom()->transformation(
                fn(string $v): self => self::tryFrom($v) ?? $default_value
            )
        );
    }

    private static function buildOptionsList(Language $lng): array
    {
        return array_reduce(
            self::cases(),
            function (array $c, self $v) use ($lng): array {
                $c[$v->value] = $lng->txt($v->value);
                return $c;
            },
            []
        );
    }
}
