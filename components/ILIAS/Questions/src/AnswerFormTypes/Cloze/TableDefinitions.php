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

use ILIAS\Questions\AnswerForm\Persistence\AnswerFormSpecificTableTypes;
use ILIAS\Questions\Persistence\Column;
use ILIAS\Questions\Persistence\Factory as PersistenceFactory;
use ILIAS\Questions\Persistence\JoinType;
use ILIAS\Questions\Persistence\TableDefinitions as TableDefinitionsInterface;
use ILIAS\Questions\Persistence\Query;
use ILIAS\Questions\Persistence\TableSubNameSpace;
use ILIAS\Questions\Persistence\TableNameBuilder;
use ILIAS\Questions\Persistence\TableTypes;

class TableDefinitions implements TableDefinitionsInterface
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

    private const string COMBINATION_TABLE_IDENTIFIER = 'combinations';
    private const string COMBINATION_TABLE_FOREIGN_KEY_COLUMN = 'answer_form_id';
    private const array COMBINATION_TABLE_COLUMNS = [
        'id',
        'answer_form_id',
        'points'
    ];

    private const string COMBINATION_TO_ANSWER_OPTIONS_TABLE_IDENTIFIER = 'combinations_to_answer_options';
    private const string COMBINATION_TO_ANSWER_OPTIONS_TABLE_ID_COLUMN = 'combination_id';
    private const string COMBINATION_TO_ANSWER_OPTIONS_TABLE_FOREIGN_KEY_COLUMN = 'combination_id';
    private const array COMBINATION_TO_ANSWER_OPTIONS_TABLE_COLUMNS = [
        'combination_id',
        'gap_id',
        'answer_option_id',
        'in_range'
    ];

    public function __construct(
        private readonly PersistenceFactory $persistence_factory,
        private readonly TableSubNameSpace $table_name_specifiers
    ) {
    }

    #[\Override]
    public function getTableSubNameSpace(): TableSubNameSpace
    {
        return $this->table_name_specifiers;
    }

    #[\Override]
    public function getColumns(
        TableNameBuilder $table_name_builder,
        TableTypes $table_type,
        string $sub_table_identifier = '',
        array $columns_to_skip = []
    ): array {
        $table = $this->persistence_factory->table(
            $table_name_builder,
            $table_type,
            $sub_table_identifier
        );
        $column_identifiers = match($table_type) {
            AnswerFormSpecificTableTypes::TypeSpecificAnswerForms => self::ANSWER_FORM_TABLE_COLUMNS,
            AnswerFormSpecificTableTypes::AnswerInputs => self::ANSWER_INPUTS_TABLE_COLUMNS,
            AnswerFormSpecificTableTypes::AnswerOptions => self::ANSWER_OPTIONS_TABLE_COLUMNS,
            AnswerFormSpecificTableTypes::Additional => match($sub_table_identifier) {
                self::COMBINATION_TABLE_IDENTIFIER => self::COMBINATION_TABLE_COLUMNS,
                self::COMBINATION_TO_ANSWER_OPTIONS_TABLE_IDENTIFIER => self::COMBINATION_TO_ANSWER_OPTIONS_TABLE_COLUMNS
            }
        };
        return array_map(
            fn(string $v): Column => $this->persistence_factory->column(
                $table,
                $v
            ),
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
        TableNameBuilder $table_name_builder,
        TableTypes $table_type,
        string $sub_table_identifier = ''
    ): Column {
        $table = $this->persistence_factory->table(
            $table_name_builder,
            $table_type,
            $sub_table_identifier
        );

        if ($table_type === AnswerFormSpecificTableTypes::TypeSpecificAnswerForms) {
            return $this->persistence_factory->column(
                $table,
                self::ANSWER_FORM_TABLE_FOREIGN_KEY_COLUMN
            );
        }

        if ($sub_table_identifier === self::COMBINATION_TO_ANSWER_OPTIONS_TABLE_IDENTIFIER) {
            return $this->persistence_factory->column(
                $table,
                self::COMBINATION_TO_ANSWER_OPTIONS_TABLE_ID_COLUMN
            );
        }

        return $this->persistence_factory->column(
            $table,
            self::ID_COLUMN
        );
    }

    #[\Override]
    public function getForeignKeyColumn(
        TableNameBuilder $table_name_builder,
        TableTypes $table_type,
        string $sub_table_identifier = ''
    ): Column {
        $table = $this->persistence_factory->table(
            $table_name_builder,
            $table_type,
            $sub_table_identifier
        );

        return match($table_type) {
            AnswerFormSpecificTableTypes::TypeSpecificAnswerForms => $this->persistence_factory->column(
                $table,
                self::ANSWER_FORM_TABLE_ID_COLUMN
            ),
            AnswerFormSpecificTableTypes::AnswerInputs => $this->persistence_factory->column(
                $table,
                self::ANSWER_INPUTS_TABLE_FOREIGN_KEY_COLUMN
            ),
            AnswerFormSpecificTableTypes::AnswerOptions => $this->persistence_factory->column(
                $table,
                self::ANSWER_OPTIONS_TABLE_FOREIGN_KEY_COLUMN
            ),
            AnswerFormSpecificTableTypes::Additional => $this->persistence_factory->column(
                $table,
                $sub_table_identifier === self::COMBINATION_TABLE_IDENTIFIER
                    ? self::COMBINATION_TABLE_FOREIGN_KEY_COLUMN
                    : self::COMBINATION_TO_ANSWER_OPTIONS_TABLE_FOREIGN_KEY_COLUMN
            )
        };
    }

    #[\Override]
    public function completeQuery(
        Query $query,
        ?Column $answer_form_id_column
    ): Query {
        $table_name_builder = $query->getTableNameBuilder(
            $this->getTableSubNameSpace()
        );

        $combinations_id_column = $this->getIdColumn(
            $table_name_builder,
            AnswerFormSpecificTableTypes::Additional,
            self::COMBINATION_TABLE_IDENTIFIER
        );

        return $query->withAdditionalJoin(
            $this->persistence_factory->join(
                $answer_form_id_column,
                $this->getForeignKeyColumn(
                    $table_name_builder,
                    AnswerFormSpecificTableTypes::TypeSpecificAnswerForms
                ),
                JoinType::Left
            )
        )->withAdditionalSelect(
            $this->persistence_factory->select(
                $this->getColumns(
                    $table_name_builder,
                    AnswerFormSpecificTableTypes::TypeSpecificAnswerForms
                )
            )
        )->withAdditionalJoin(
            $this->persistence_factory->join(
                $answer_form_id_column,
                $this->getForeignKeyColumn(
                    $table_name_builder,
                    AnswerFormSpecificTableTypes::AnswerInputs
                ),
                JoinType::Left
            )
        )->withAdditionalSelect(
            $this->persistence_factory->select(
                $this->getColumns(
                    $table_name_builder,
                    AnswerFormSpecificTableTypes::AnswerInputs
                )
            )
        )->withAdditionalJoin(
            $this->persistence_factory->join(
                $this->getIdColumn(
                    $table_name_builder,
                    AnswerFormSpecificTableTypes::AnswerInputs
                ),
                $this->getForeignKeyColumn(
                    $table_name_builder,
                    AnswerFormSpecificTableTypes::AnswerOptions
                ),
                JoinType::Left
            )
        )->withAdditionalOrder(
            $this->persistence_factory->order(
                $this->getIdColumn(
                    $table_name_builder,
                    AnswerFormSpecificTableTypes::AnswerInputs
                )
            )
        )->withAdditionalSelect(
            $this->persistence_factory->select(
                $this->getColumns(
                    $table_name_builder,
                    AnswerFormSpecificTableTypes::AnswerOptions
                )
            )
        )->withAdditionalOrder(
            $this->persistence_factory->order(
                $this->persistence_factory->column(
                    $this->persistence_factory->table(
                        $table_name_builder,
                        AnswerFormSpecificTableTypes::AnswerOptions
                    ),
                    'position'
                )
            )
        )->withAdditionalJoin(
            $this->persistence_factory->join(
                $answer_form_id_column,
                $this->getForeignKeyColumn(
                    $table_name_builder,
                    AnswerFormSpecificTableTypes::Additional,
                    self::COMBINATION_TABLE_IDENTIFIER
                ),
                JoinType::Left
            )
        )->withAdditionalSelect(
            $this->persistence_factory->select(
                $this->getColumns(
                    $table_name_builder,
                    AnswerFormSpecificTableTypes::Additional,
                    self::COMBINATION_TABLE_IDENTIFIER
                )
            )
        )->withAdditionalJoin(
            $this->persistence_factory->join(
                $combinations_id_column,
                $this->getForeignKeyColumn(
                    $table_name_builder,
                    AnswerFormSpecificTableTypes::Additional,
                    self::COMBINATION_TO_ANSWER_OPTIONS_TABLE_IDENTIFIER
                ),
                JoinType::Left
            )
        )->withAdditionalSelect(
            $this->persistence_factory->select(
                $this->getColumns(
                    $table_name_builder,
                    AnswerFormSpecificTableTypes::Additional,
                    self::COMBINATION_TO_ANSWER_OPTIONS_TABLE_IDENTIFIER
                )
            )
        )->withAdditionalOrder(
            $this->persistence_factory->order(
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
