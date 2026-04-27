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

use ILIAS\Questions\AnswerForm\Capabilities\Feedback\Types;
use ILIAS\Questions\AnswerForm\Persistence\AnswerFormSpecificTableTypes;
use ILIAS\Questions\AnswerForm\Migration\Migration;
use ILIAS\Questions\AnswerForm\Migration\MigrationInsert;
use ILIAS\Questions\AnswerFormTypes\Cloze\Definition;
use ILIAS\Questions\AnswerFormTypes\Cloze\TableDefinitions;
use ILIAS\Questions\Definitions\Range;
use ILIAS\Questions\Persistence\Factory as PersistenceFactory;
use ILIAS\Questions\Persistence\Insert;
use ILIAS\Questions\Persistence\TableNameBuilder;
use ILIAS\Questions\Persistence\TableNameSpace;
use ILIAS\Data\UUID\Uuid;
use ILIAS\Database\FieldDefinition;
use ILIAS\Setup\Environment;

class MigrationCloze implements Migration
{
    use BasicMigrationFunctions;

    private array $answer_options_mapping_for_feedback = [];

    public function __construct(
        private readonly TableDefinitions $table_definitions,
        private readonly \EvalMath $math
    ) {
    }

    #[\Override]
    public function getOldQuestionTypeIdentifier(): string
    {
        return 'assClozeTest';
    }

    #[\Override]
    public function getDefinitionClass(): string
    {
        return Definition::class;
    }

    #[\Override]
    public function getTableNameSpace(): TableNameSpace
    {
        return $this->table_definitions->getTableNameSpace();
    }

    #[\Override]
    public function completeMigrationInsert(
        Environment $environment,
        MigrationInsert $migration_insert
    ): ?MigrationInsert {
        $answer_input_mapping = [];
        $answer_options_mapping = [];
        $gaps_insert = null;
        $answer_options_insert = null;

        foreach ($this->fetchDBValues(
            $migration_insert->getDb(),
            $migration_insert->getOldQuestionId()
        ) as $db_row) {
            $answer_form_id = $migration_insert->getAnswerFormId();
            if (!isset($answer_input_mapping[$db_row->gap_id])) {
                $answer_input_mapping[$db_row->gap_id] = $migration_insert->getUuid();
                $answer_options_mapping[$db_row->gap_id] = [];
                $this->answer_options_mapping_for_feedback[$db_row->gap_id] = [
                    'is_numeric' => $db_row->cloze_type == \assClozeGap::TYPE_NUMERIC,
                    'is_feedback_per_gap' => $db_row->feedback_mode === \ilAssClozeTestFeedback::FB_MODE_GAP_QUESTION,
                    'answer_options' => []
                ];
                $gaps_insert = $this->buildGapInsertStatement(
                    $this->table_definitions,
                    $migration_insert->getPersistenceFactory(),
                    $migration_insert->getTableNameBuilder(),
                    $gaps_insert,
                    $answer_input_mapping[$db_row->gap_id],
                    $answer_form_id,
                    $db_row->gap_id,
                    $this->buildNewGapTypeIdentifierFromOld((int) $db_row->cloze_type),
                    null,
                    null,
                    $this->buildNewTextRatingFromOld($db_row->textgap_rating),
                    null,
                    $db_row->shuffle === '1' ? 1 : 0
                );
            }

            $answer_option_id = $migration_insert->getUuid();
            $answer_options_mapping[$db_row->gap_id][$db_row->answertext] = [
                'is_numeric' => $db_row->cloze_type == \assClozeGap::TYPE_NUMERIC,
                'answer_option_id' => $answer_option_id
            ];
            $this->answer_options_mapping_for_feedback[$db_row->gap_id]['answer_options'][$db_row->aorder] = $answer_option_id;

            $answer_options_insert = $this->buildAnswerOptionInsertStatement(
                $this->table_definitions,
                $migration_insert->getPersistenceFactory(),
                $migration_insert->getTableNameBuilder(),
                $answer_options_insert,
                $answer_option_id,
                $answer_input_mapping[$db_row->gap_id],
                $db_row->aorder,
                $db_row->answertext,
                $db_row->points,
                $this->limitToFloat($this->math, $db_row->lowerlimit),
                $this->limitToFloat($this->math, $db_row->upperlimit)
            );
        }

        if (!isset($db_row)) {
            return null;
        }

        if ($db_row->combinations_enabled) {
            $migration_insert = $this->addCombinationInsertStatements(
                $migration_insert->getPersistenceFactory(),
                $migration_insert,
                $answer_input_mapping,
                $answer_options_mapping
            );
        }

        return $migration_insert
            ->withAdditionalInsert(
                $this->buildAnswerFormInsertStatement(
                    $this->table_definitions,
                    $migration_insert->getPersistenceFactory(),
                    $migration_insert->getTableNameBuilder(),
                    $answer_form_id,
                    $this->buildScoringIdenticalFromOld((int) $db_row->identical_scoring),
                    $db_row->combinations_enabled
                )
            )->withAdditionalInsert(
                $gaps_insert
            )->withAdditionalInsert(
                $answer_options_insert
            )->withAdditionalTextLegacy(
                $this->replaceGapsAndSantizeLegacyClozeText(
                    $migration_insert->getDb(),
                    '\[gap\].+?\[\/gap\]',
                    $db_row->cloze_text,
                    $answer_input_mapping,
                    $migration_insert->wasIliasPageEditorUsedForAdditionalTexts()
                )
            );
    }

    #[\Override]
    public function getConditionsForFeedbackFromOldValues(
        int $answer,
        int $question
    ): ?array {
        $gap = $this->answer_options_mapping_for_feedback[$question];

        if ($gap['is_feedback_per_gap'] && $answer !== -10
            || !$gap['is_feedback_per_gap'] && $answer === -10) {
            return null;
        }

        if ($answer === -10) {
            return array_map(
                fn(Uuid $v): string => $v->toString(),
                $gap['answer_options']
            );
        }

        if ($answer === -1) {
            return [Types::NothingSelected->value];
        }

        if ($gap['is_numeric']) {
            return [$this->buildRangeValue(true, $answer)];
        }

        $answer_option_id = array_filter(
            $gap['answer_options'],
            fn(string $v): bool => $v == $answer,
            ARRAY_FILTER_USE_KEY
        );

        if ($answer_option_id !== []) {
            return [$answer_option_id->toString()];
        }

        return [''];
    }

    private function fetchDBValues(
        \ilDBInterface $db,
        int $old_question_id
    ): \Generator {
        $query = $db->query(
            'SELECT *, EXISTS (' . PHP_EOL
                . 'SELECT gap_fi FROM qpl_a_cloze_combi_res' . PHP_EOL
                . 'WHERE question_fi = a.question_fi' . PHP_EOL
            . ') combinations_enabled' . PHP_EOL
            . 'FROM qpl_qst_cloze q' . PHP_EOL
            . 'JOIN qpl_a_cloze a ON q.question_fi = a.question_fi' . PHP_EOL
            . "WHERE q.question_fi = {$db->quote($old_question_id)}" . PHP_EOL
            . 'ORDER BY a.gap_id, a.aorder'
        );

        while (($row = $db->fetchObject($query)) !== null) {
            yield $row;
        }
    }

    private function fetchCombinationsDBValues(
        \ilDBInterface $db,
        int $old_question_id
    ): \Generator {
        $query = $db->query(
            'SELECT combination_id, gap_fi, answer, points, row_id FROM qpl_a_cloze_combi_res' . PHP_EOL
                . "WHERE question_fi = {$db->quote($old_question_id)}" . PHP_EOL
                . 'ORDER BY combination_id, row_id'
        );

        while (($row = $db->fetchObject($query)) !== null) {
            yield $row;
        }
    }

    private function addCombinationInsertStatements(
        PersistenceFactory $persistence_factory,
        MigrationInsert $migration_insert,
        array $answer_input_mapping,
        array $answer_options_mapping
    ): MigrationInsert {
        $combination_mapping = [];
        $combinations_insert = null;
        $combinations_to_answer_options_insert = null;
        foreach ($this->fetchCombinationsDBValues(
            $migration_insert->getDb(),
            $migration_insert->getOldQuestionId()
        ) as $db_row) {
            if ($db_row->answer !== 'out_of_bound'
                && !isset($answer_options_mapping[$db_row->gap_fi][$db_row->answer])) {
                continue;
            }

            $answer_option = $db_row->answer === 'out_of_bound'
                ? reset($answer_options_mapping[$db_row->gap_fi])
                : $answer_options_mapping[$db_row->gap_fi][$db_row->answer];

            if (!isset($combination_mapping[$db_row->combination_id . $db_row->row_id])) {
                $combination_mapping[$db_row->combination_id . $db_row->row_id] = $migration_insert->getUuid();
                $combinations_insert = $this->buildCombinationsInsert(
                    $persistence_factory,
                    $migration_insert->getTableNameBuilder(),
                    $combinations_insert,
                    $combination_mapping[$db_row->combination_id . $db_row->row_id],
                    $migration_insert->getAnswerFormId(),
                    $db_row->points
                );
            }

            $combinations_to_answer_options_insert = $this->buildCombinationsToAnswerOptionsInsert(
                $persistence_factory,
                $migration_insert->getTableNameBuilder(),
                $combinations_to_answer_options_insert,
                $combination_mapping[$db_row->combination_id . $db_row->row_id],
                $answer_input_mapping[$db_row->gap_fi],
                $answer_option['answer_option_id'],
                $this->buildRangeValue($answer_option['is_numeric'], $db_row->answer)
            );
        }

        return $migration_insert->withAdditionalInsert($combinations_insert)
            ->withAdditionalInsert($combinations_to_answer_options_insert);
    }

    private function buildCombinationsInsert(
        PersistenceFactory $persistence_factory,
        TableNameBuilder $table_names_builder,
        ?Insert $combinations_insert,
        Uuid $combination_id,
        Uuid $answer_form_id,
        float $points
    ): Insert {
        $values = [
            $persistence_factory->value(FieldDefinition::T_TEXT, $combination_id->toString()),
            $persistence_factory->value(FieldDefinition::T_TEXT, $answer_form_id->toString()),
            $persistence_factory->value(FieldDefinition::T_FLOAT, $points),
        ];

        if ($combinations_insert === null) {
            return $persistence_factory->insert(
                $this->table_definitions->getColumns(
                    $table_names_builder,
                    AnswerFormSpecificTableTypes::Additional,
                    $this->table_definitions->getCombinationsTableIdentifier()
                ),
                $values
            );
        }

        return $combinations_insert->withAdditionalValues($values);
    }

    private function buildCombinationsToAnswerOptionsInsert(
        PersistenceFactory $persistence_factory,
        TableNameBuilder $table_names_builder,
        ?Insert $combinations_to_answer_options_insert,
        Uuid $combination_id,
        Uuid $gap_id,
        Uuid $answer_option_id,
        ?Range $in_range
    ): Insert {
        $values = [
            $persistence_factory->value(FieldDefinition::T_TEXT, $combination_id->toString()),
            $persistence_factory->value(FieldDefinition::T_TEXT, $gap_id->toString()),
            $persistence_factory->value(FieldDefinition::T_TEXT, $answer_option_id->toString()),
            $persistence_factory->value(FieldDefinition::T_TEXT, $in_range?->value)
        ];

        if ($combinations_to_answer_options_insert === null) {
            return $persistence_factory->insert(
                $this->table_definitions->getColumns(
                    $table_names_builder,
                    AnswerFormSpecificTableTypes::Additional,
                    $this->table_definitions->getCombinationToAnswerOptionsTableIdentifier()
                ),
                $values
            );
        }

        return $combinations_to_answer_options_insert->withAdditionalValues($values);
    }

    private function buildNewGapTypeIdentifierFromOld(
        int $old_gap_type
    ): string {
        return match($old_gap_type) {
            \assClozeGap::TYPE_TEXT => 'text',
            \assClozeGap::TYPE_SELECT => 'select',
            \assClozeGap::TYPE_NUMERIC => 'numeric'
        };
    }

    private function buildRangeValue(
        bool $is_numeric,
        string|int $value
    ): ?Range {
        if ($is_numeric === false) {
            return null;
        }

        if ($value === 'out_of_bounds') {
            return Range::OutOfRange;
        }

        if ($value === \ilAssClozeTestFeedback::FB_NUMERIC_GAP_TOO_LOW_INDEX) {
            return Range::BelowRange;
        }

        if ($value === \ilAssClozeTestFeedback::FB_NUMERIC_GAP_TOO_HIGH_INDEX) {
            return Range::AboveRange;
        }

        return Range::InRange;
    }
}
