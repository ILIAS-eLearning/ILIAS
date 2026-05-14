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

use ILIAS\Questions\AnswerForm\Persistence\AnswerFormGenericTableDefinitions;
use ILIAS\Questions\AnswerForm\Properties as AnswerFormProperties;
use ILIAS\Questions\Question\Definitions\Lifecycle;
use ILIAS\Questions\Persistence\Factory as PersistenceFactory;
use ILIAS\Questions\Persistence\Insert;
use ILIAS\Questions\Persistence\Manipulate;
use ILIAS\Questions\Persistence\TableNameBuilder;
use ILIAS\Questions\Persistence\Update;
use ILIAS\Database\FieldDefinition;
use ILIAS\Data\UUID\Uuid;

class DatabaseStatementBuilder
{
    public function __construct(
        private readonly PersistenceFactory $persistence_factory,
        private readonly TableDefinitions $question_tables_definitions,
        private readonly AnswerFormGenericTableDefinitions $answer_form_generic_table_definitions
    ) {
    }

    public function addInsertStatementsToManipulation(
        Manipulate $manipulate,
        Uuid $question_id,
        ?int $page_id,
        string $title,
        string $author,
        Lifecycle $lifecycle,
        string $remarks,
        ?Uuid $original_id,
        int $parent_obj_id,
        ?int $position
    ): Manipulate {
        if ($this->created === null) {
            $manipulate = $manipulate
                ->withAdditionalStatement(
                    $this->buildInsertLinkingStatement(
                        $manipulate->getTableNameBuilder(null),
                        $question_id,
                        $parent_obj_id,
                        $position
                    )
                )->withAdditionalStatement(
                    $this->buildInsertQuestionStatement(
                        $manipulate->getTableNameBuilder(null),
                        $question_id,
                        $page_id,
                        $title,
                        $author,
                        $lifecycle,
                        $remarks,
                        $original_id
                    )
                );
        }

        if ($this->updated_answer_forms !== []) {
            return $this->addAnswerFormStatementsToManipulate(
                $manipulate,
                $this->updated_answer_forms
            );
        }

        if ($this->answer_forms !== []) {
            return $this->addAnswerFormStatementsToManipulate(
                $manipulate,
                $this->answer_forms
            );
        }

        return $manipulate;
    }

    public function addUpdateStatementsToManipulation(
        Manipulate $manipulate,
        bool $self_updated,
        bool $linking_information_updated,
        bool $page_id_updated,
        Uuid $question_id,
        ?int $page_id,
        string $title,
        string $author,
        Lifecycle $lifecycle,
        string $remarks,
        ?Uuid $original_id,
        int $parent_obj_id,
        ?int $position,
        array $updated_answer_forms,
        array $deleted_answer_forms
    ): Manipulate {
        $table_names_builder = $manipulate->getTableNameBuilder(null);

        if ($linking_information_updated) {
            $manipulate = $manipulate
                ->withAdditionalStatement(
                    $this->buildUpdateLinkingStatement(
                        $table_names_builder,
                        $question_id,
                        $parent_obj_id,
                        $position
                    )
                );
        }

        if ($self_updated) {
            $manipulate = $manipulate->withAdditionalStatement(
                $this->buildUpdateQuestionStatement(
                    $table_names_builder,
                    $question_id,
                    $title,
                    $author,
                    $lifecycle,
                    $remarks,
                    $original_id
                )
            );
        }

        if ($page_id_updated) {
            $manipulate = $manipulate->withAdditionalStatement(
                $this->buildUpdatePageIdStatement(),
                $question_id,
                $page_id
            );
        }

        if ($deleted_answer_forms !== []) {
            $manipulate = $this->addDeleteAnswerFormsStatementsToManipulate(
                $manipulate,
                $deleted_answer_forms
            );
        }

        return $this->addAnswerFormStatementsToManipulate(
            $manipulate,
            $updated_answer_forms
        );
    }

    public function addDeleteAnswerFormsStatementsToManipulate(
        Manipulate $manipulate,
        array $answer_forms_to_delete
    ): Manipulate {
        return array_reduce(
            $answer_forms_to_delete,
            fn(Manipulate $c, AnswerFormProperties $v): Manipulate => $v->toDelete(
                $this->persistence_factory,
                $v->getTypeGenericProperties()->toDelete(
                    $this->persistence_factory,
                    $this->answer_form_generic_table_definitions,
                    $c,
                )
            ),
            $manipulate
        );
    }

    public function buildDeleteQuestionStatement(
        TableNamesBuilder $table_names_builder,
        Uuid $question_id
    ): Delete {
        $table_type = TableTypes::Questions;
        return $this->persistence_factory->delete(
            $this->persistence_factory->table(
                $table_names_builder,
                $table_type
            ),
            [
                $this->persistence_factory->where(
                    $this->question_tables_definitions->getIdColumn(
                        $table_names_builder,
                        $table_type
                    ),
                    $this->persistence_factory->value(
                        FieldDefinition::T_TEXT,
                        $question_id->toString()
                    )
                )
            ]
        );
    }

    public function buildDeleteLinkingStatement(
        TableNameBuilder $table_names_builder,
        Uuid $question_id
    ): Delete {
        $table_type = TableTypes::Linking;
        return $this->persistence_factory->delete(
            $this->persistence_factory->table(
                $table_names_builder,
                $table_type
            ),
            [
                $this->persistence_factory->where(
                    $this->question_tables_definitions->getIdColumn(
                        $table_names_builder,
                        $table_type
                    ),
                    $this->persistence_factory->value(
                        FieldDefinition::T_TEXT,
                        $question_id->toString()
                    )
                )
            ]
        );
    }

    /**
     * @todo skergomard, 2026-01-86: This we only need while the migrations exist, after
     * this MUST go!
     */
    public function buildDeleteMigrationStatement(
        TableNameBuilder $table_names_builder,
        Uuid $question_id
    ): Delete {
        $table_type = TableTypes::MigrationsTable;
        return $this->persistence_factory->delete(
            $this->persistence_factory->table(
                $table_names_builder,
                $table_type
            ),
            [
                $this->persistence_factory->where(
                    $this->question_tables_definitions->getIdColumn(
                        $table_names_builder,
                        $table_type
                    ),
                    $this->persistence_factory->value(
                        FieldDefinition::T_TEXT,
                        $question_id->toString()
                    )
                )
            ]
        );
    }

    private function addAnswerFormStatementsToManipulate(
        Manipulate $manipulate,
        array $answer_forms
    ): Manipulate {
        return array_reduce(
            $answer_forms,
            function (
                Manipulate $c,
                AnswerFormProperties $v
            ): Manipulate {
                $manipulate_with_generic_properties = $v->getTypeGenericProperties()
                    ->toStorage(
                        $this->persistence_factory,
                        $this->answer_form_generic_table_definitions,
                        $c
                    );

                return $v->toStorage(
                    $this->persistence_factory,
                    $manipulate_with_generic_properties
                );
            },
            $manipulate
        );
    }

    private function buildInsertLinkingStatement(
        TableNameBuilder $table_names_builder,
        Uuid $question_id,
        int $parent_obj_id,
        ?int $position
    ): Insert {
        return $this->persistence_factory->insert(
            $this->question_tables_definitions->getColumns(
                $table_names_builder,
                TableTypes::Linking
            ),
            [
                $this->persistence_factory->value(
                    FieldDefinition::T_TEXT,
                    $question_id->toString()
                ),
                $this->persistence_factory->value(
                    FieldDefinition::T_INTEGER,
                    $parent_obj_id
                ),
                $this->persistence_factory->value(
                    FieldDefinition::T_INTEGER,
                    $position
                )
            ]
        );
    }

    private function buildInsertQuestionStatement(
        TableNameBuilder $table_names_builder,
        Uuid $question_id,
        ?int $page_id,
        string $title,
        string $author,
        Lifecycle $lifecycle,
        string $remarks,
        ?Uuid $original_id
    ): Insert {
        return $this->persistence_factory->insert(
            $this->question_tables_definitions->getColumns(
                $table_names_builder,
                TableTypes::Questions
            ),
            [
                $this->persistence_factory->value(
                    FieldDefinition::T_TEXT,
                    $question_id->toString()
                ),
                $this->persistence_factory->value(
                    FieldDefinition::T_INTEGER,
                    $page_id
                ),
                $this->persistence_factory->value(
                    FieldDefinition::T_TEXT,
                    $title
                ),
                $this->persistence_factory->value(
                    FieldDefinition::T_TEXT,
                    $author
                ),
                $this->persistence_factory->value(
                    FieldDefinition::T_TEXT,
                    $lifecycle->value
                ),
                $this->persistence_factory->value(
                    FieldDefinition::T_TEXT,
                    $remarks
                ),
                $this->persistence_factory->value(
                    FieldDefinition::T_TEXT,
                    $original_id?->toString()
                ),
                $this->persistence_factory->value(
                    FieldDefinition::T_INTEGER,
                    time()
                ),
                $this->persistence_factory->value(
                    FieldDefinition::T_INTEGER,
                    time()
                )
            ]
        );
    }

    private function buildUpdateLinkingStatement(
        TableNameBuilder $table_names_builder,
        Uuid $question_id,
        int $parent_obj_id,
        ?int $position
    ): Update {
        $table_type = TableTypes::Linking;
        return $this->persistence_factory->update(
            $this->question_tables_definitions->getColumns(
                $table_names_builder,
                $table_type,
                [TableTypes::LINKING_TABLE_ID_COLUMN]
            ),
            [
                $this->persistence_factory->value(
                    FieldDefinition::T_INTEGER,
                    $parent_obj_id
                ),
                $this->persistence_factory->value(
                    FieldDefinition::T_INTEGER,
                    $position
                )
            ],
            [
                $this->persistence_factory->where(
                    $this->question_tables_definitions->getIdColumn(
                        $table_names_builder,
                        $table_type
                    ),
                    $this->persistence_factory->value(
                        FieldDefinition::T_TEXT,
                        $question_id->toString()
                    )
                )
            ]
        );
    }

    private function buildUpdateQuestionStatement(
        TableNameBuilder $table_names_builder,
        Uuid $question_id,
        string $title,
        string $author,
        Lifecycle $lifecycle,
        string $remarks,
        ?Uuid $original_id
    ): Update {
        $table_type = TableTypes::Questions;
        return $this->persistence_factory->update(
            $this->question_tables_definitions->getColumns(
                $table_names_builder,
                $table_type,
                [
                    'id',
                    'page_id',
                    'created'
                ]
            ),
            [
                $this->persistence_factory->value(
                    FieldDefinition::T_TEXT,
                    $title
                ),
                $this->persistence_factory->value(
                    FieldDefinition::T_TEXT,
                    $author
                ),
                $this->persistence_factory->value(
                    FieldDefinition::T_TEXT,
                    $lifecycle->value
                ),
                $this->persistence_factory->value(
                    FieldDefinition::T_TEXT,
                    $remarks
                ),
                $this->persistence_factory->value(
                    FieldDefinition::T_TEXT,
                    $original_id?->toString()
                ),
                $this->persistence_factory->value(
                    FieldDefinition::T_INTEGER,
                    time()
                )
            ],
            [
                $this->persistence_factory->where(
                    $this->question_tables_definitions->getIdColumn(
                        $table_names_builder,
                        $table_type
                    ),
                    $this->persistence_factory->value(
                        FieldDefinition::T_TEXT,
                        $question_id->toString()
                    )
                )
            ]
        );
    }

    /**
     * @todo skergomard, 2026-01-26: This we only need while the migrations exist, after
     * this a question MUST never change the page assigned to it after its creation!
     */
    private function buildUpdatePageIdStatement(
        TableNameBuilder $table_names_builder,
        Uuid $question_id,
        ?int $page_id
    ): Update {
        $table_type = TableTypes::Questions;
        return $this->persistence_factory->update(
            [
                $this->persistence_factory->column(
                    $this->persistence_factory->table(
                        $table_names_builder,
                        $table_type
                    ),
                    'page_id'
                ),
                $this->persistence_factory->column(
                    $this->persistence_factory->table(
                        $table_names_builder,
                        $table_type
                    ),
                    'last_update'
                )
            ],
            [
                $this->persistence_factory->value(
                    FieldDefinition::T_TEXT,
                    $page_id
                ),
                $this->persistence_factory->value(
                    FieldDefinition::T_INTEGER,
                    time()
                )
            ],
            [
                $this->persistence_factory->where(
                    $this->question_tables_definitions->getIdColumn(
                        $table_names_builder,
                        $table_type
                    ),
                    $this->persistence_factory->value(
                        FieldDefinition::T_TEXT,
                        $question_id->toString()
                    )
                )
            ]
        );
    }
}
