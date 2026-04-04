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

use ILIAS\Questions\AnswerForm\Persistence\AnswerFormSpecificTableTypes;
use ILIAS\Questions\AnswerForm\Migration\SanitizeLegacyText;
use ILIAS\Questions\AnswerFormTypes\Cloze\TableDefinitions;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Definitions\ScoringIdentical;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Gaps\Gap;
use ILIAS\Questions\Persistence\Factory as PersistenceFactory;
use ILIAS\Questions\Persistence\Insert;
use ILIAS\Questions\Persistence\TableNameBuilder;
use ILIAS\Questions\Definitions\TextMatchingOptions;
use ILIAS\Data\UUID\Uuid;
use ILIAS\Database\FieldDefinition;

trait BasicMigrationFunctions
{
    use SanitizeLegacyText;

    private ?array $answer_inputs_mapping = null;

    #[\Override]
    public function getNewAnswerInputIdForOld(
        int $id
    ): ?Uuid {
        if ($this->answer_inputs_mapping === null) {
            return null;
        }

        return $this->answer_inputs_mapping[$id] ?? null;
    }

    private function buildGapInsertStatement(
        TableDefinitions $table_definitions,
        PersistenceFactory $persistence_factory,
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
            return $persistence_factory->insert(
                $table_definitions->getColumns(
                    $table_name_builder,
                    AnswerFormSpecificTableTypes::AnswerInputs
                ),
                $this->buildGapValuesForInsert(
                    $persistence_factory,
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
                $persistence_factory,
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
        PersistenceFactory $persistence_factory,
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
            $persistence_factory->value(FieldDefinition::T_TEXT, $answer_input_id->toString()),
            $persistence_factory->value(FieldDefinition::T_TEXT, $answer_form_id->toString()),
            $persistence_factory->value(FieldDefinition::T_INTEGER, $position),
            $persistence_factory->value(FieldDefinition::T_TEXT, $gap_type),
            $persistence_factory->value(FieldDefinition::T_INTEGER, $max_chars),
            $persistence_factory->value(FieldDefinition::T_FLOAT, $step_size),
            $persistence_factory->value(FieldDefinition::T_INTEGER, $matching_options?->value),
            $persistence_factory->value(FieldDefinition::T_INTEGER, $min_autocomplete),
            $persistence_factory->value(FieldDefinition::T_INTEGER, $shuffle)
        ];
    }

    private function buildAnswerOptionInsertStatement(
        TableDefinitions $table_definitions,
        PersistenceFactory $persistence_factory,
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
        $this->answer_options_mapping[$position] = $answer_option_id;

        if ($options_insert === null) {
            return $persistence_factory->insert(
                $table_definitions->getColumns(
                    $table_name_builder,
                    AnswerFormSpecificTableTypes::AnswerOptions
                ),
                $this->buildOptionValuesForInsert(
                    $persistence_factory,
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
                $persistence_factory,
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
        PersistenceFactory $persistence_factory,
        Uuid $answer_option_id,
        Uuid $answer_input_id,
        int $position,
        string $text_value,
        float $points,
        ?float $lower_limit,
        ?float $upper_limit
    ): array {
        return [
            $persistence_factory->value(FieldDefinition::T_TEXT, $answer_option_id->toString()),
            $persistence_factory->value(FieldDefinition::T_TEXT, $answer_input_id->toString()),
            $persistence_factory->value(FieldDefinition::T_INTEGER, $position),
            $persistence_factory->value(FieldDefinition::T_TEXT, $text_value),
            $persistence_factory->value(FieldDefinition::T_FLOAT, $points),
            $persistence_factory->value(FieldDefinition::T_FLOAT, $lower_limit),
            $persistence_factory->value(
                FieldDefinition::T_FLOAT,
                $lower_limit !== $upper_limit
                    ? $upper_limit
                    : null
            )
        ];
    }

    private function buildAnswerFormInsertStatement(
        TableDefinitions $table_definitions,
        PersistenceFactory $persistence_factory,
        TableNameBuilder $table_name_builder,
        Uuid $answer_form_id,
        ScoringIdentical $scoring_identical,
        int $combinations_enabled
    ): Insert {
        return $persistence_factory->insert(
            $table_definitions->getColumns(
                $table_name_builder,
                AnswerFormSpecificTableTypes::TypeSpecificAnswerForms
            ),
            [
                $persistence_factory->value(FieldDefinition::T_TEXT, $answer_form_id->toString()),
                $persistence_factory->value(FieldDefinition::T_TEXT, $scoring_identical->value),
                $persistence_factory->value(FieldDefinition::T_INTEGER, $combinations_enabled)
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
        \ilDBInterface $db,
        string $gap_replace_regex,
        string $text,
        array $gaps_mapping,
        bool $ilias_page_editor_text
    ): string {
        ksort($gaps_mapping);

        return mb_ereg_replace_callback(
            $gap_replace_regex,
            function (array $matches) use (&$gaps_mapping): string {
                return '{{' . Gap::GAP_PLACEHOLDER_NAME . '_' . array_shift($gaps_mapping) . '}}';
            },
            $this->sanitizeLegacyText(
                $db,
                $text,
                $ilias_page_editor_text
            )
        );
    }

    private function limitToFloat(
        \EvalMath $math,
        string $limit
    ): float {
        return (float) $math->e($limit);
    }

    private function getHtmlQuestionContentPurifier(): \ilHtmlPurifierInterface
    {
        return \ilHtmlPurifierFactory::getInstanceByType('qpl_usersolution');
    }
}
