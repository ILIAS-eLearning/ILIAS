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

namespace ILIAS\Questions\Attempt;

use ILIAS\Questions\Persistence\Column;
use ILIAS\Questions\Persistence\Delete;
use ILIAS\Questions\Persistence\Factory as PersistenceFactory;
use ILIAS\Questions\Persistence\JoinType;
use ILIAS\Questions\Persistence\OrderDirection;
use ILIAS\Questions\Persistence\Query;
use ILIAS\Questions\Persistence\TableSubNameSpace;
use ILIAS\Questions\Persistence\TableNameBuilder;
use ILIAS\Questions\Persistence\TableTypes as TableTypesInterface;

class TableDefinitions
{
    private const string ID_COLUMN = 'id';

    private const array ATTEMPT_DATA_TABLE_COLUMNS = [
        'id',
        'shuffler_seed'
    ];

    private const string ADDITIONAL_ATTEMPT_DATA_TABLE_FOREIGN_KEY_COLUMN = 'attempt_id';
    private const array ADDITIONAL_ATTEMPT_DATA_TABLE_COLUMNS = [
        'attempt_id',
        'parent_id',
        'data'
    ];

    private const string RESPONSES_TABLE_PRIMARY_FOREIGN_KEY_COLUMN = 'attempt_id';
    private const string RESPONSES_TABLE_SECONDARY_FOREIGN_KEY_COLUMN = 'question_id';
    private const string RESPONSES_TABLE_ADDITIONAL_ORDERING_COLUMN = 'create_timestamp';
    private const array RESPONSES_TABLE_COLUMNS = [
        'id',
        'attempt_id',
        'question_id',
        'awarded_points',
        'create_timestamp'
    ];

    public function __construct(
        private readonly PersistenceFactory $persistence_factory
    ) {
    }

    public function getTableSubNameSpace(): ?TableSubNameSpace
    {
        return null;
    }

    public function getColumns(
        TableNameBuilder $table_names_builder,
        TableTypesInterface $table_type
    ): array {
        $table = $this->persistence_factory->table(
            $table_names_builder,
            $table_type
        );

        return array_map(
            fn(string $v): Column => $this->persistence_factory->column(
                $table,
                $v
            ),
            match($table_type) {
                TableTypes::AttemptData => self::ATTEMPT_DATA_TABLE_COLUMNS,
                TableTypes::AdditionalAttemptData => self::ADDITIONAL_ATTEMPT_DATA_TABLE_COLUMNS,
                TableTypes::Responses => self::RESPONSES_TABLE_COLUMNS
            }
        );
    }

    public function getIdColumn(
        TableNameBuilder $table_names_builder,
        TableTypesInterface $table_type
    ): ?Column {
        return match($table_type) {
            TableTypes::AttemptData,
            TableTypes::Responses => $this->persistence_factory->column(
                $this->persistence_factory->table(
                    $table_names_builder,
                    $table_type
                ),
                self::ID_COLUMN
            ),
            default => null
        };
    }

    public function getForeignKeyColumn(
        TableNameBuilder $table_names_builder,
        TableTypesInterface $table_type
    ): ?Column {
        $table = $this->persistence_factory->table(
            $table_names_builder,
            $table_type
        );

        return match($table_type) {
            TableTypes::AdditionalAttemptData => $this->persistence_factory->column(
                $table,
                self::ADDITIONAL_ATTEMPT_DATA_TABLE_FOREIGN_KEY_COLUMN
            ),
            TableTypes::Responses => $this->persistence_factory->column(
                $table,
                self::RESPONSES_TABLE_PRIMARY_FOREIGN_KEY_COLUMN
            ),
            default => null
        };
    }

    public function completeAttemptQuery(
        Query $query,
        ?Column $base_table_id_column
    ): Query {
        $table_names_builder = $query->getTableNameBuilder(null);

        return $query->withAdditionalSelect(
            $this->persistence_factory->select(
                $this->getColumns(
                    $table_names_builder,
                    TableTypes::AttemptData
                )
            )
        )->withAdditionalSelect(
            $this->persistence_factory->select(
                $this->getColumns(
                    $table_names_builder,
                    TableTypes::AdditionalAttemptData
                )
            )
        )->withAdditionalJoin(
            $this->persistence_factory->join(
                $base_table_id_column,
                $this->getForeignKeyColumn(
                    $table_names_builder,
                    TableTypes::AdditionalAttemptData
                ),
                JoinType::Left
            )
        )->withAdditionalSelect(
            $this->persistence_factory->select(
                $this->getColumns(
                    $table_names_builder,
                    TableTypes::Responses
                )
            )
        )->withAdditionalJoin(
            $this->persistence_factory->join(
                $base_table_id_column,
                $this->getForeignKeyColumn(
                    $table_names_builder,
                    TableTypes::Responses
                ),
                JoinType::Left
            )
        )->withAdditionalOrder(
            $this->persistence_factory->order(
                $base_table_id_column
            )
        )->withAdditionalOrder(
            $this->persistence_factory->order(
                $this->persistence_factory->column(
                    $this->persistence_factory->table(
                        $table_names_builder,
                        TableTypes::Responses
                    ),
                    self::RESPONSES_TABLE_SECONDARY_FOREIGN_KEY_COLUMN
                )
            )
        )->withAdditionalOrder(
            $this->persistence_factory->order(
                $this->persistence_factory->column(
                    $this->persistence_factory->table(
                        $table_names_builder,
                        TableTypes::Responses
                    ),
                    self::RESPONSES_TABLE_ADDITIONAL_ORDERING_COLUMN
                ),
                OrderDirection::Asc
            )
        );
    }
}
