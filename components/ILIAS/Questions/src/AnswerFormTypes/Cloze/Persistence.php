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

namespace ILIAS\Questions\AnswerFormTypes\Cloze;

use ILIAS\Questions\AnswerForm\Persistence as PersistenceInterface;
use ILIAS\Questions\Persistence\TableTypes;
use ILIAS\Questions\Persistence\Query;
use ILIAS\Questions\Persistence\Column;
use ILIAS\Questions\Persistence\Factory as PersistenceFactory;
use ILIAS\Questions\Persistence\JoinType;
use ILIAS\Questions\Persistence\TableNameBuilder;
use ILIAS\Questions\Persistence\TableNameSpace;
use ILIAS\Questions\Persistence\TableNameSpaceCore;

class Persistence implements PersistenceInterface
{
    private const string ID_COLUMN = 'id';

    private const string ANSWER_FORM_TABLE_ID_COLUMN = 'answer_form_id';
    private const string ANSWER_FORM_TABLE_FOREIGN_KEY_COLUMN = 'answer_form_id';
    private const array ANSWER_FORM_TABLE_COLUMNS = [
        'answer_form_id',
        'scoring_identical_responses',
        'combinations_enabled'
    ];

    private const string ANSWER_INPUTS_TABLE_FOREIGN_KEY_COLUMN = 'answer_form_id';
    private const array ANSWER_INPUTS_TABLE_COLUMNS = [
        'id',
        'answer_form_id',
        'position',
        'gap_type',
        'max_chars',
        'step_size',
        'text_matching_method',
        'min_autocomplete',
        'shuffle_answer_options'
    ];

    private const string ANSWER_OPTIONS_TABLE_FOREIGN_KEY_COLUMN = 'answer_input_id';
    private const array ANSWER_OPTIONS_TABLE_COLUMNS = [
        'id',
        'answer_input_id',
        'position',
        'text_value',
        'points',
        'lower_limit',
        'upper_limit'
    ];

    public const string COMBINATION_TABLE_IDENTIFIER = 'combinations';
    private const string COMBINATION_TABLE_FOREIGN_KEY_COLUMN = 'answer_form_id';
    private const array COMBINATION_TABLE_COLUMNS = [
        'id',
        'answer_form_id',
        'points'
    ];

    public const string COMBINATION_TO_ANSWER_OPTIONS_TABLE_IDENTIFIER = 'combinations_to_answer_options';
    private const string COMBINATION_TO_ANSWER_OPTIONS_TABLE_ID_COLUMN = 'combination_id';
    private const string COMBINATION_TO_ANSWER_OPTIONS_TABLE_FOREIGN_KEY_COLUMN = 'combination_id';
    private const array COMBINATION_TO_ANSWER_OPTIONS_TABLE_COLUMNS = [
        'combination_id',
        'gap_id',
        'answer_option_id',
        'in_range'
    ];

    public function __construct(
        private readonly TableNameSpaceCore $table_namespace
    ) {
    }

    #[\Override]
    public function getTableNameSpace(): TableNameSpace
    {
        return $this->table_namespace;
    }

    #[\Override]
    public function getColumns(
        PersistenceFactory $persistence_factory,
        TableNameBuilder $table_name_builder,
        TableTypes $table_type,
        string $table_identifier = '',
        array $columns_to_skip = []
    ): array {
        $table = $table_type->getTable(
            $persistence_factory,
            $table_name_builder,
            $table_identifier
        );
        $column_identifiers = match($table_type) {
            TableTypes::TypeSpecificAnswerForms => self::ANSWER_FORM_TABLE_COLUMNS,
            TableTypes::AnswerInputs => self::ANSWER_INPUTS_TABLE_COLUMNS,
            TableTypes::AnswerOptions => self::ANSWER_OPTIONS_TABLE_COLUMNS,
            TableTypes::Additional => match($table_identifier) {
                self::COMBINATION_TABLE_IDENTIFIER => self::COMBINATION_TABLE_COLUMNS,
                self::COMBINATION_TO_ANSWER_OPTIONS_TABLE_IDENTIFIER => self::COMBINATION_TO_ANSWER_OPTIONS_TABLE_COLUMNS
            }
        };
        return array_map(
            fn(string $v): Column => $persistence_factory->column($table, $v),
            array_values(
                array_filter(
                    $column_identifiers,
                    fn(string $v) => !in_array($v, $columns_to_skip)
                )
            )
        );
    }

    #[\Override]
    public function getIdColumn(
        PersistenceFactory $persistence_factory,
        TableNameBuilder $table_name_builder,
        TableTypes $table_type,
        string $table_identifier = ''
    ): Column {
        if ($table_type === TableTypes::TypeSpecificAnswerForms) {
            return $persistence_factory->column(
                $table_type->getTable(
                    $persistence_factory,
                    $table_name_builder
                ),
                self::ANSWER_FORM_TABLE_FOREIGN_KEY_COLUMN
            );
        }

        if ($table_identifier === self::COMBINATION_TO_ANSWER_OPTIONS_TABLE_IDENTIFIER) {
            return $persistence_factory->column(
                $table_type->getTable(
                    $persistence_factory,
                    $table_name_builder,
                    self::COMBINATION_TO_ANSWER_OPTIONS_TABLE_IDENTIFIER
                ),
                self::COMBINATION_TO_ANSWER_OPTIONS_TABLE_ID_COLUMN
            );
        }

        return $persistence_factory->column(
            $table_type->getTable(
                $persistence_factory,
                $table_name_builder,
                $table_identifier
            ),
            self::ID_COLUMN
        );
    }

    #[\Override]
    public function getForeignKeyColumn(
        PersistenceFactory $persistence_factory,
        TableNameBuilder $table_name_builder,
        TableTypes $table_type,
        string $table_identifier = ''
    ): Column {
        return match($table_type) {
            TableTypes::TypeSpecificAnswerForms => $persistence_factory->column(
                $table_type->getTable(
                    $persistence_factory,
                    $table_name_builder
                ),
                self::ANSWER_FORM_TABLE_ID_COLUMN
            ),
            TableTypes::AnswerInputs => $persistence_factory->column(
                $table_type->getTable(
                    $persistence_factory,
                    $table_name_builder
                ),
                self::ANSWER_INPUTS_TABLE_FOREIGN_KEY_COLUMN
            ),
            TableTypes::AnswerOptions => $persistence_factory->column(
                $table_type->getTable(
                    $persistence_factory,
                    $table_name_builder
                ),
                self::ANSWER_OPTIONS_TABLE_FOREIGN_KEY_COLUMN
            ),
            TableTypes::Additional => $persistence_factory->column(
                $table_type->getTable(
                    $persistence_factory,
                    $table_name_builder,
                    $table_identifier
                ),
                $table_identifier === self::COMBINATION_TABLE_IDENTIFIER
                    ? self::COMBINATION_TABLE_FOREIGN_KEY_COLUMN
                    : self::COMBINATION_TO_ANSWER_OPTIONS_TABLE_FOREIGN_KEY_COLUMN
            )
        };
    }

    #[\Override]
    public function completeQuestionsQuery(
        Query $query,
        Column $answer_form_id_column
    ): Query {
        $persistence_factory = $query->getPersistenceFactory();
        $table_name_builder = $query->getTableNameBuilder(Definition::class);

        $answer_form_specific_table_definition = TableTypes::TypeSpecificAnswerForms;
        $answer_input_table_definition = TableTypes::AnswerInputs;
        $answer_options_table_definition = TableTypes::AnswerOptions;
        $additional_table_definition = TableTypes::Additional;

        $combinations_id_column = $this->getIdColumn(
            $persistence_factory,
            $table_name_builder,
            $additional_table_definition,
            self::COMBINATION_TABLE_IDENTIFIER
        );

        return $query->withAdditionalJoin(
            $persistence_factory->join(
                $answer_form_id_column,
                $this->getForeignKeyColumn(
                    $persistence_factory,
                    $table_name_builder,
                    $answer_form_specific_table_definition
                ),
                JoinType::Left
            )
        )->withAdditionalSelect(
            $persistence_factory->select(
                $this->getColumns(
                    $persistence_factory,
                    $table_name_builder,
                    $answer_form_specific_table_definition
                )
            )
        )->withAdditionalJoin(
            $persistence_factory->join(
                $answer_form_id_column,
                $this->getForeignKeyColumn(
                    $persistence_factory,
                    $table_name_builder,
                    $answer_input_table_definition
                ),
                JoinType::Left
            )
        )->withAdditionalSelect(
            $persistence_factory->select(
                $this->getColumns(
                    $persistence_factory,
                    $table_name_builder,
                    $answer_input_table_definition
                )
            )
        )->withAdditionalJoin(
            $persistence_factory->join(
                $this->getIdColumn(
                    $persistence_factory,
                    $table_name_builder,
                    $answer_input_table_definition
                ),
                $this->getForeignKeyColumn(
                    $persistence_factory,
                    $table_name_builder,
                    $answer_options_table_definition
                ),
                JoinType::Left
            )
        )->withAdditionalOrder(
            $persistence_factory->order(
                $this->getIdColumn(
                    $persistence_factory,
                    $table_name_builder,
                    $answer_input_table_definition
                )
            )
        )->withAdditionalSelect(
            $persistence_factory->select(
                $this->getColumns(
                    $persistence_factory,
                    $table_name_builder,
                    $answer_options_table_definition
                )
            )
        )->withAdditionalOrder(
            $persistence_factory->order(
                $persistence_factory->column(
                    $answer_options_table_definition->getTable(
                        $persistence_factory,
                        $table_name_builder
                    ),
                    'position'
                )
            )
        )->withAdditionalJoin(
            $persistence_factory->join(
                $answer_form_id_column,
                $this->getForeignKeyColumn(
                    $persistence_factory,
                    $table_name_builder,
                    $additional_table_definition,
                    self::COMBINATION_TABLE_IDENTIFIER
                ),
                JoinType::Left
            )
        )->withAdditionalSelect(
            $persistence_factory->select(
                $this->getColumns(
                    $persistence_factory,
                    $table_name_builder,
                    $additional_table_definition,
                    self::COMBINATION_TABLE_IDENTIFIER
                )
            )
        )->withAdditionalJoin(
            $persistence_factory->join(
                $combinations_id_column,
                $this->getForeignKeyColumn(
                    $persistence_factory,
                    $table_name_builder,
                    $additional_table_definition,
                    self::COMBINATION_TO_ANSWER_OPTIONS_TABLE_IDENTIFIER
                ),
                JoinType::Left
            )
        )->withAdditionalSelect(
            $persistence_factory->select(
                $this->getColumns(
                    $persistence_factory,
                    $table_name_builder,
                    $additional_table_definition,
                    self::COMBINATION_TO_ANSWER_OPTIONS_TABLE_IDENTIFIER
                )
            )
        )->withAdditionalOrder(
            $persistence_factory->order(
                $combinations_id_column
            )
        );
    }

    public function getCombinationsTableIdentifier(): string
    {
        return self::COMBINATION_TABLE_IDENTIFIER;
    }

    public function getCombinationToAnswerOptionsTableIdentifier(): string
    {
        return self::COMBINATION_TO_ANSWER_OPTIONS_TABLE_IDENTIFIER;
    }
}
