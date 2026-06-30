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

namespace ILIAS\Questions\AnswerForm;

use ILIAS\Questions\AnswerForm\Persistence\AnswerFormGenericTableDefinitions;
use ILIAS\Questions\AnswerForm\Persistence\AnswerFormGenericTableTypes;
use ILIAS\Questions\Persistence\Factory as PersistenceFactory;
use ILIAS\Questions\Persistence\Insert;
use ILIAS\Questions\Persistence\Manipulate;
use ILIAS\Questions\Persistence\ManipulationType;
use ILIAS\Questions\Persistence\TableNameBuilder;
use ILIAS\Questions\Persistence\Update;
use ILIAS\Data\UUID\Uuid;
use ILIAS\Database\FieldDefinition;

class TypeGenericProperties
{
    public function __construct(
        private readonly Uuid $answer_form_id,
        private readonly Uuid $question_id,
        private readonly ?Definition $definition = null,
        private readonly ?float $available_points = null,
        private readonly ?int $image_size = null,
        private readonly ?bool $shuffle_answer_options = null,
        private readonly string $additional_text = '',
        private readonly string $additional_text_legacy = ''
    ) {
    }

    public function getAnswerFormId(): Uuid
    {
        return $this->answer_form_id;
    }

    public function getQuestionId(): Uuid
    {
        return $this->question_id;
    }

    public function getDefinition(): ?Definition
    {
        return $this->definition;
    }

    public function getAvailablePoints(): ?float
    {
        return $this->available_points;
    }

    public function getImageSize(): ?int
    {
        return $this->image_size;
    }

    public function getShuffleAnswerOptions(): ?bool
    {
        return $this->shuffle_answer_options;
    }

    public function getAdditionalText(): string
    {
        return $this->additional_text;
    }

    public function getAdditionalTextLegacy(): string
    {
        return $this->additional_text_legacy;
    }

    public function toStorage(
        PersistenceFactory $persistence_factory,
        AnswerFormGenericTableDefinitions $answer_form_generic_definitions,
        ManipulationType $manipulation_type,
        Manipulate $manipulate
    ): Manipulate {
        if ($this->definition === null) {
            throw new \UnexpectedValueException(
                'You cannot save a Answer Form without a Type!'
            );
        }

        $table_names_builder = $manipulate->getTableNameBuilder(null);

        return $manipulate->withAdditionalStatement(
            $manipulation_type === ManipulationType::Create
                ? $this->buildInsertStatement(
                    $persistence_factory,
                    $answer_form_generic_definitions,
                    $table_names_builder
                ) : $this->buildUpdateStatement(
                    $persistence_factory,
                    $answer_form_generic_definitions,
                    $table_names_builder
                )
        );
    }

    public function toDelete(
        PersistenceFactory $persistence_factory,
        AnswerFormGenericTableDefinitions $answer_form_generic_table_definitions,
        Manipulate $manipulate
    ): Manipulate {
        $table_names_builder = $manipulate->getTableNameBuilder(
            $answer_form_generic_table_definitions->getTableSubNameSpace()
        );
        $table_type = AnswerFormGenericTableTypes::AnswerForms;

        return $manipulate->withAdditionalStatement(
            $persistence_factory->delete(
                $persistence_factory->table(
                    $table_names_builder,
                    $table_type
                ),
                [
                    $persistence_factory->where(
                        $answer_form_generic_table_definitions->getIdColumn(
                            $table_names_builder,
                            $table_type
                        ),
                        $persistence_factory->value(
                            FieldDefinition::T_TEXT,
                            $this->answer_form_id->toString()
                        )
                    )
                ]
            )
        );
    }

    private function buildInsertStatement(
        PersistenceFactory $persistence_factory,
        AnswerFormGenericTableDefinitions $answer_form_generic_table_definitions,
        TableNameBuilder $table_names_builder
    ): Insert {
        return $persistence_factory->insert(
            $answer_form_generic_table_definitions->getColumns(
                $table_names_builder,
                AnswerFormGenericTableTypes::AnswerForms
            ),
            [
                $persistence_factory->value(
                    FieldDefinition::T_TEXT,
                    $this->answer_form_id->toString()
                ),
                $persistence_factory->value(
                    FieldDefinition::T_TEXT,
                    $this->definition::class
                ),
                $persistence_factory->value(
                    FieldDefinition::T_TEXT,
                    $this->question_id->toString()
                ),
                $persistence_factory->value(
                    FieldDefinition::T_FLOAT,
                    $this->available_points
                ),
                $persistence_factory->value(
                    FieldDefinition::T_INTEGER,
                    $this->image_size
                ),
                $persistence_factory->value(
                    FieldDefinition::T_INTEGER,
                    $this->getShuffleAnswerOptionsForStorage()
                ),
                $persistence_factory->value(
                    FieldDefinition::T_TEXT,
                    $this->additional_text
                ),
                $persistence_factory->value(
                    FieldDefinition::T_TEXT,
                    $this->additional_text_legacy
                )
            ]
        );
    }

    private function buildUpdateStatement(
        PersistenceFactory $persistence_factory,
        AnswerFormGenericTableDefinitions $answer_form_generic_table_definitions,
        TableNameBuilder $table_names_builder
    ): Update {
        $table_type = AnswerFormGenericTableTypes::AnswerForms;

        return $persistence_factory->update(
            $answer_form_generic_table_definitions->getColumns(
                $table_names_builder,
                $table_type,
                [
                    'id',
                    'type',
                    'question_id',
                    'additional_text_legacy'
                ]
            ),
            [
                $persistence_factory->value(
                    FieldDefinition::T_FLOAT,
                    $this->available_points
                ),
                $persistence_factory->value(
                    FieldDefinition::T_INTEGER,
                    $this->image_size
                ),
                $persistence_factory->value(
                    FieldDefinition::T_INTEGER,
                    $this->getShuffleAnswerOptionsForStorage()
                ),
                $persistence_factory->value(
                    FieldDefinition::T_TEXT,
                    $this->additional_text
                )

            ],
            [
                $persistence_factory->where(
                    $answer_form_generic_table_definitions->getIdColumn(
                        $table_names_builder,
                        $table_type
                    ),
                    $persistence_factory->value(
                        FieldDefinition::T_TEXT,
                        $this->answer_form_id->toString()
                    )
                )
            ]
        );
    }

    private function getShuffleAnswerOptionsForStorage(): ?int
    {
        if ($this->shuffle_answer_options === null) {
            return null;
        }

        return $this->shuffle_answer_options ? 1 : 0;
    }
}
