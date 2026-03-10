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

namespace ILIAS\Questions\AnswerForm\Capabilities\Feedback;

use ILIAS\Questions\Persistence\Column;
use ILIAS\Questions\Persistence\Factory as PersistenceFactory;
use ILIAS\Questions\Persistence\JoinType;
use ILIAS\Questions\Persistence\TableDefinitions as TableDefinitionsInterface;
use ILIAS\Questions\Persistence\Query;
use ILIAS\Questions\Persistence\TableSubNameSpace;
use ILIAS\Questions\Persistence\TableNameBuilder;
use ILIAS\Questions\Persistence\TableTypes as TableTypesInterface;

class TableDefinitions implements TableDefinitionsInterface
{
    private const string FEEDBACK_GENERIC_TABLE_ID_COLUMN = 'answer_form_id';
    private const string FEEDBACK_GENERIC_TABLE_FOREIGN_KEY_COLUMN = 'answer_form_id';
    private const array FEEDBACK_GENERIC_TABLE_COLUMNS = [
        'answer_form_id',
        'feedback_best_response',
        'feedback_best_response_legacy',
        'feedback_other_response',
        'feedback_other_response_legacy'
    ];

    private const string FEEDBACK_SPECIFIC_TABLE_ID_COLUMN = 'id';
    private const string FEEDBACK_SPECIFIC_TABLE_FOREIGN_KEY_COLUMN = 'answer_form_id';
    private const array FEEDBACK_SPECIFIC_TABLE_COLUMNS = [
        'id',
        'answer_form_id',
        'parent_id',
        'condition',
        'feedback',
        'feedback_legacy'
    ];

    public function __construct(
        private readonly PersistenceFactory $persistence_factory
    ) {
    }

    #[\Override]
    public function getTableSubNameSpace(): ?TableSubNameSpace
    {
        return null;
    }

    #[\Override]
    public function getColumns(
        TableNameBuilder $table_name_builder,
        TableTypesInterface $table_type,
        string $sub_table_identifier = '',
        array $columns_to_skip = []
    ): array {
        $table = $this->persistence_factory->table(
            $table_name_builder,
            $table_type
        );
        $column_identifiers = match($table_type) {
            TableTypes::FeedbackGeneric => self::FEEDBACK_GENERIC_TABLE_COLUMNS,
            TableTypes::FeedbackSpecific => self::FEEDBACK_SPECIFIC_TABLE_COLUMNS
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
        TableTypesInterface $table_type,
        string $sub_table_identifier = ''
    ): Column {
        $table = $this->persistence_factory->table(
            $table_name_builder,
            $table_type
        );

        return match($table_type) {
            TableTypes::FeedbackGeneric => $this->persistence_factory->column(
                $table,
                self::FEEDBACK_GENERIC_TABLE_ID_COLUMN
            ),
            TableTypes::FeedbackSpecific => $this->persistence_factory->column(
                $table,
                self::FEEDBACK_SPECIFIC_TABLE_ID_COLUMN
            )
        };
    }

    #[\Override]
    public function getForeignKeyColumn(
        TableNameBuilder $table_name_builder,
        TableTypesInterface $table_type,
        string $sub_table_identifier = ''
    ): Column {
        $table = $this->persistence_factory->table(
            $table_name_builder,
            $table_type
        );

        return match($table_type) {
            TableTypes::FeedbackGeneric => $this->persistence_factory->column(
                $table,
                self::FEEDBACK_GENERIC_TABLE_FOREIGN_KEY_COLUMN
            ),
            TableTypes::FeedbackSpecific => $this->persistence_factory->column(
                $table,
                self::FEEDBACK_SPECIFIC_TABLE_FOREIGN_KEY_COLUMN
            )
        };
    }

    #[\Override]
    public function completeQuery(
        Query $query,
        ?Column $base_table_id_column
    ): Query {
        $table_name_builder = $query->getTableNameBuilder(null);

        return $query->withAdditionalSelect(
            $this->persistence_factory->select(
                $this->getColumns(
                    $table_name_builder,
                    TableTypes::FeedbackGeneric
                )
            )
        )->withAdditionalSelect(
            $this->persistence_factory->select(
                $this->getColumns(
                    $table_name_builder,
                    TableTypes::FeedbackSpecific
                )
            )
        )->withAdditionalJoin(
            $this->persistence_factory->join(
                $base_table_id_column,
                $this->getForeignKeyColumn(
                    $table_name_builder,
                    TableTypes::FeedbackSpecific
                ),
                JoinType::Left
            )
        )->withAdditionalOrder(
            $this->persistence_factory->order(
                $base_table_id_column
            )
        );
    }
}
