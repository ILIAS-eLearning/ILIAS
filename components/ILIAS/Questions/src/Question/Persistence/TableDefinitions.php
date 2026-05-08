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

namespace ILIAS\Questions\Question\Persistence;

use ILIAS\Questions\Persistence\Column;
use ILIAS\Questions\Persistence\Factory as PersistenceFactory;
use ILIAS\Questions\Persistence\JoinType;
use ILIAS\Questions\Persistence\Query;
use ILIAS\Questions\Persistence\TableSubNameSpace;
use ILIAS\Questions\Persistence\TableNameBuilder;
use ILIAS\Questions\Persistence\TableTypes as TableTypesInterface;

class TableDefinitions
{
    private const string QUESTION_TABLE_ID_COLUMN = 'id';
    private const array QUESTION_TABLE_COLUMNS = [
        'id',
        'page_id',
        'title',
        'author',
        'lifecycle',
        'remarks',
        'original_id',
        'last_update',
        'created'
    ];

    public const string LINKING_TABLE_ID_COLUMN = 'question_id';
    private const string LINKING_TABLE_FOREIGN_KEY_COLUMN = 'obj_id';
    private const array LINKING_TABLE_COLUMNS = [
        'question_id',
        'obj_id',
        'position'
    ];

    public const string MIGRATIONS_TABLE_ID_COLUMN = 'new_question_id';
    private const string MIGRATIONS_TABLE_FOREIGN_KEY_COLUMN = 'old_question_id';
    private const array MIGRATIONS_TABLE_COLUMNS = [
        'old_question_id',
        'new_question_id',
        'success'
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
        TableTypesInterface $table_type,
        array $columns_to_skip = []
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
            array_values(
                array_filter(
                    match($table_type) {
                        TableTypes::Questions => self::QUESTION_TABLE_COLUMNS,
                        TableTypes::Linking => self::LINKING_TABLE_COLUMNS,
                        TableTypes::MigrationsTable => self::MIGRATIONS_TABLE_COLUMNS
                    },
                    fn(string $v) => !in_array($v, $columns_to_skip)
                )
            )
        );
    }

    public function getIdColumn(
        TableNameBuilder $table_name_builder,
        TableTypesInterface $table_type
    ): Column {
        $table = $this->persistence_factory->table(
            $table_name_builder,
            $table_type
        );

        return match($table_type) {
            TableTypes::Questions => $this->persistence_factory->column(
                $table,
                self::QUESTION_TABLE_ID_COLUMN
            ),
            TableTypes::Linking => $this->persistence_factory->column(
                $table,
                self::LINKING_TABLE_ID_COLUMN
            ),
            TableTypes::MigrationsTable => $this->persistence_factory->column(
                $table,
                self::MIGRATIONS_TABLE_ID_COLUMN
            )
        };
    }

    public function completeLoadQuestionQuery(
        Query $query
    ): Query {
        $table_name_builder = $query->getTableNameBuilder(null);
        $questions_id_column = $this->getIdColumn(
            $table_name_builder,
            TableTypes::Questions
        );

        return $query->withAdditionalSelect(
            $this->persistence_factory->select(
                $this->getColumns(
                    $table_name_builder,
                    TableTypes::Linking
                )
            )
        )->withAdditionalSelect(
            $this->persistence_factory->select(
                $this->getColumns(
                    $table_name_builder,
                    TableTypes::Questions
                )
            )
        )->withAdditionalJoin(
            $this->persistence_factory->join(
                $this->getIdColumn(
                    $table_name_builder,
                    TableTypes::Linking
                ),
                $questions_id_column,
                JoinType::Inner
            )
        )->withAdditionalOrder(
            $this->persistence_factory->order(
                $questions_id_column
            )
        )->withAdditionalOrder(
            $this->persistence_factory->order(
                $this->getIdColumn(
                    $table_name_builder,
                    TableTypes::Linking
                )
            )
        );
    }
}
