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

namespace ILIAS\Questions\AnswerForm\Persistence;

use ILIAS\Questions\Persistence\Column;
use ILIAS\Questions\Persistence\Factory as PersistenceFactory;
use ILIAS\Questions\Persistence\JoinType;
use ILIAS\Questions\AnswerForm\Persistence\TableDefinitions as TableDefinitionsInterface;
use ILIAS\Questions\Persistence\Query;
use ILIAS\Questions\Persistence\TableSubNameSpace;
use ILIAS\Questions\Persistence\TableNameBuilder;
use ILIAS\Questions\Persistence\TableTypes;

class AnswerFormGenericTableDefinitions implements TableDefinitionsInterface
{
    public const string ANSWER_FORM_TABLE_ID_COLUMN = 'id';
    private const string ANSWER_FORM_TABLE_FOREIGN_KEY_COLUMN = 'question_id';
    private const array ANSWER_FORM_TABLE_COLUMNS = [
        'id',
        'type',
        'question_id',
        'available_points',
        'image_size',
        'shuffle_answer_options',
        'additional_text',
        'additional_text_legacy'
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
        TableNameBuilder $table_name_builder,
        TableTypes $table_type,
        array $columns_to_skip = []
    ): array {
        return array_map(
            fn(string $v): Column => $this->persistence_factory->column(
                $this->persistence_factory->table(
                    $table_name_builder,
                    $table_type
                ),
                $v
            ),
            array_values(
                array_filter(
                    self::ANSWER_FORM_TABLE_COLUMNS,
                    fn(string $v) => !in_array($v, $columns_to_skip)
                )
            )
        );
    }

    public function getIdColumn(
        TableNameBuilder $table_name_builder,
        TableTypes $table_type
    ): Column {
        return $this->persistence_factory->column(
            $this->persistence_factory->table(
                $table_name_builder,
                $table_type
            ),
            self::ANSWER_FORM_TABLE_ID_COLUMN
        );
    }

    public function getForeignKeyColumn(
        TableNameBuilder $table_name_builder,
        TableTypes $table_type
    ): Column {
        return $this->persistence_factory->column(
            $this->persistence_factory->table(
                $table_name_builder,
                $table_type
            ),
            self::ANSWER_FORM_TABLE_FOREIGN_KEY_COLUMN
        );
    }

    #[\Override]
    public function completeQuestionQuery(
        Query $query,
        ?Column $base_table_id_column
    ): Query {
        $table_name_builder = $query->getTableNameBuilder(null);

        return $query->withAdditionalSelect(
            $this->persistence_factory->select(
                $this->getColumns(
                    $table_name_builder,
                    AnswerFormGenericTableTypes::AnswerForms
                )
            )
        )->withAdditionalJoin(
            $this->persistence_factory->join(
                $base_table_id_column,
                $this->getForeignKeyColumn(
                    $table_name_builder,
                    AnswerFormGenericTableTypes::AnswerForms
                ),
                JoinType::Left
            )
        );
    }
}
