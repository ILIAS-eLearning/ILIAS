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

namespace ILIAS\Questions\AnswerFormTypes\Cloze\Migration;

use ILIAS\Questions\AnswerFormTypes\Cloze\Persistence;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Definitions\ScoringIdentical;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Gaps\Gap;
use ILIAS\Questions\Persistence\Insert;
use ILIAS\Questions\Persistence\TableNameBuilder;
use ILIAS\Questions\Persistence\TableTypes;
use ILIAS\Questions\Persistence\Value;
use ILIAS\Questions\Definitions\TextMatchingOptions;
use ILIAS\Data\UUID\Uuid;

trait BasicMigrationFunctions
{
    private function buildGapInsertStatement(
        Persistence $persistence,
        TableNameBuilder $table_name_builder,
        ?Insert $gaps_insert,
        Uuid $answer_input_id,
        Uuid $answer_form_id,
        int $position,
        string $gap_type,
        ?int $max_chars,
        ?float $step_size,
        ?TextMatchingOptions $matching_options,
        ?int $min_autocomplete,
        ?int $shuffle
    ): Insert {
        if ($gaps_insert === null) {
            return new Insert(
                $persistence->getColumns(
                    $table_name_builder,
                    TableTypes::AnswerInputs
                ),
                $this->buildGapValuesForInsert(
                    $answer_input_id,
                    $answer_form_id,
                    $position,
                    $gap_type,
                    $max_chars,
                    $step_size,
                    $matching_options,
                    $min_autocomplete,
                    $shuffle
                )
            );
        }

        return $gaps_insert->withAdditionalValues(
            $this->buildGapValuesForInsert(
                $answer_input_id,
                $answer_form_id,
                $position,
                $gap_type,
                $max_chars,
                $step_size,
                $matching_options,
                $min_autocomplete,
                $shuffle
            )
        );
    }

    private function buildGapValuesForInsert(
        Uuid $answer_input_id,
        Uuid $answer_form_id,
        int $position,
        string $gap_type,
        ?int $max_chars,
        ?float $step_size,
        ?TextMatchingOptions $matching_options,
        ?int $min_autocomplete,
        ?int $shuffle
    ): array {
        return [
            new Value(\ilDBConstants::T_TEXT, $answer_input_id->toString()),
            new Value(\ilDBConstants::T_TEXT, $answer_form_id->toString()),
            new Value(\ilDBConstants::T_INTEGER, $position),
            new Value(\ilDBConstants::T_TEXT, $gap_type),
            new Value(\ilDBConstants::T_INTEGER, $max_chars),
            new Value(\ilDBConstants::T_FLOAT, $step_size),
            new Value(\ilDBConstants::T_INTEGER, $matching_options?->value),
            new Value(\ilDBConstants::T_INTEGER, $min_autocomplete),
            new Value(\ilDBConstants::T_INTEGER, $shuffle)
        ];
    }

    private function buildAnswerOptionInsertStatement(
        Persistence $persistence,
        TableNameBuilder $table_name_builder,
        ?Insert $options_insert,
        Uuid $answer_option_id,
        Uuid $answer_input_id,
        int $position,
        string $text_value,
        float $points,
        ?float $lower_limit,
        ?float $upper_limit
    ): Insert {
        if ($options_insert === null) {
            return new Insert(
                $persistence->getColumns(
                    $table_name_builder,
                    TableTypes::AnswerOptions
                ),
                $this->buildOptionValuesForInsert(
                    $answer_option_id,
                    $answer_input_id,
                    $position,
                    $text_value,
                    $points,
                    $lower_limit,
                    $upper_limit
                )
            );
        }

        return $options_insert->withAdditionalValues(
            $this->buildOptionValuesForInsert(
                $answer_option_id,
                $answer_input_id,
                $position,
                $text_value,
                $points,
                $lower_limit,
                $upper_limit
            )
        );
    }

    private function buildOptionValuesForInsert(
        Uuid $answer_option_id,
        Uuid $answer_input_id,
        int $position,
        string $text_value,
        float $points,
        ?float $lower_limit,
        ?float $upper_limit
    ): array {
        return [
            new Value(\ilDBConstants::T_TEXT, $answer_option_id->toString()),
            new Value(\ilDBConstants::T_TEXT, $answer_input_id->toString()),
            new Value(\ilDBConstants::T_INTEGER, $position),
            new Value(\ilDBConstants::T_TEXT, $text_value),
            new Value(\ilDBConstants::T_FLOAT, $points),
            new Value(\ilDBConstants::T_FLOAT, $lower_limit),
            new Value(
                \ilDBConstants::T_FLOAT,
                $lower_limit !== $upper_limit
                    ? $upper_limit
                    : null
            )
        ];
    }

    private function buildAnswerFormInsertStatement(
        Persistence $persistence,
        TableNameBuilder $table_name_builder,
        Uuid $answer_form_id,
        ScoringIdentical $scoring_identical,
        int $combinations_enabled
    ): Insert {
        return new Insert(
            $persistence->getColumns(
                $table_name_builder,
                TableTypes::TypeSpecificAnswerForms
            ),
            [
                new Value(\ilDBConstants::T_TEXT, $answer_form_id->toString()),
                new Value(\ilDBConstants::T_TEXT, $scoring_identical->value),
                new Value(\ilDBConstants::T_INTEGER, $combinations_enabled)
            ]
        );
    }

    private function buildScoringIdenticalFromOld(
        int $scoring_identical
    ): ScoringIdentical {
        if ($scoring_identical === '1') {
            return ScoringIdentical::ScoreAll;
        }

        return ScoringIdentical::OnlyScoreDistinct;
    }

    private function buildNewTextRatingFromOld(
        string $old_text_rating
    ): TextMatchingOptions {
        return match($old_text_rating) {
            \assClozeGap::TEXTGAP_RATING_CASEINSENSITIVE => TextMatchingOptions::CaseInsensitive,
            \assClozeGap::TEXTGAP_RATING_CASESENSITIVE => TextMatchingOptions::CaseSensitive,
            \assClozeGap::TEXTGAP_RATING_LEVENSHTEIN1 => TextMatchingOptions::Levenstein1,
            \assClozeGap::TEXTGAP_RATING_LEVENSHTEIN2 => TextMatchingOptions::Levenstein2,
            \assClozeGap::TEXTGAP_RATING_LEVENSHTEIN3 => TextMatchingOptions::Levenstein3,
            \assClozeGap::TEXTGAP_RATING_LEVENSHTEIN4 => TextMatchingOptions::Levenstein4,
            \assClozeGap::TEXTGAP_RATING_LEVENSHTEIN5 => TextMatchingOptions::Levenstein5
        };
    }

    private function replaceGapsAndSantizeLegacyClozeText(
        string $gap_replace_regex,
        string $text,
        array $gaps_mapping
    ): string {
        ksort($gaps_mapping);

        return mb_ereg_replace_callback(
            $gap_replace_regex,
            function (array $matches) use (&$gaps_mapping): string {
                return '{{' . Gap::GAP_PLACEHOLDER_NAME . '_' . array_shift($gaps_mapping) . '}}';
            },
            $text
        );
    }

    private function limitToFloat(
        \EvalMath $math,
        string $limit
    ): float {
        return (float) $math->e($limit);
    }
}
