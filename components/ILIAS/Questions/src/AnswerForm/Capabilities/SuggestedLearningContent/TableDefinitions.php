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

namespace ILIAS\Questions\AnswerForm\Capabilities\SuggestedLearningContent;

use ILIAS\Questions\Persistence\Column;
use ILIAS\Questions\Persistence\Factory as PersistenceFactory;
use ILIAS\Questions\Persistence\TableDefinitions as TableDefinitionsInterface;
use ILIAS\Questions\Persistence\Query;
use ILIAS\Questions\Persistence\TableSubNameSpace;
use ILIAS\Questions\Persistence\TableNameBuilder;
use ILIAS\Questions\Persistence\TableTypes as TableTypesInterface;

class TableDefinitions implements TableDefinitionsInterface
{
    private const string SUGGESTED_LEARNING_CONTENT_TABLE_ID_COLUMN = 'answer_form_id';
    private const string SUGGESTED_LEARNING_CONTENT_TABLE_FOREIGN_KEY_COLUMN = 'answer_form_id';
    private const array SUGGESTED_LEARNING_CONTENT_TABLE_COLUMNS = [
        'answer_form_id',
        'type',
        'content'
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
        return array_map(
            fn(string $v): Column => $this->persistence_factory->column(
                $table,
                $v
            ),
            array_values(
                array_filter(
                    self::SUGGESTED_LEARNING_CONTENT_TABLE_COLUMNS,
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

        return $this->persistence_factory->column(
            $table,
            self::SUGGESTED_LEARNING_CONTENT_TABLE_ID_COLUMN
        );
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

        return $this->persistence_factory->column(
            $table,
            self::SUGGESTED_LEARNING_CONTENT_TABLE_FOREIGN_KEY_COLUMN
        );
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
                    TableTypes::SuggestedLearningContent
                )
            )
        )->withAdditionalOrder(
            $this->persistence_factory->order(
                $base_table_id_column
            )
        );
    }
}
