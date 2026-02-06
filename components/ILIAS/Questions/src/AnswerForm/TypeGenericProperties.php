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

use ILIAS\Questions\Persistence\CoreTables;
use ILIAS\Questions\Persistence\Delete;
use ILIAS\Questions\Persistence\Factory as PersistenceFactory;
use ILIAS\Questions\Persistence\Insert;
use ILIAS\Questions\Persistence\Manipulate;
use ILIAS\Questions\Persistence\ManipulationType;
use ILIAS\Questions\Persistence\Storable;
use ILIAS\Questions\Persistence\Update;
use ILIAS\Questions\Persistence\Where;
use ILIAS\Data\UUID\Uuid;

class TypeGenericProperties implements Storable
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

    #[\Override]
    public function toStorage(
        Manipulate $manipulate
    ): Manipulate {
        if ($this->definition === null) {
            throw new \UnexpectedValueException(
                'You cannot save a Answer Form without a Type!'
            );
        }
        return $manipulate->withAdditionalStatement(
            $manipulate->getManipulationType() === ManipulationType::Create
                ? $this->buildInsertStatement($manipulate->getPersistenceFactory())
                : $this->buildUpdateStatement($manipulate->getPersistenceFactory())
        );
    }

    #[\Override]
    public function toDelete(
        Manipulate $manipulate
    ): Manipulate {
        $answer_form_table_definition = CoreTables::AnswerForms;

        return $manipulate->withAdditionalStatement(
            $manipulate->getPersistenceFactory()->delete(
                $answer_form_table_definition->getTable(
                    $manipulate->getPersistenceFactory()
                ),
                [
                    $manipulate->getPersistenceFactory()->where(
                        $answer_form_table_definition->getIdColumn(
                            $manipulate->getPersistenceFactory()
                        ),
                        $manipulate->getPersistenceFactory()->value(
                            \ilDBConstants::T_TEXT,
                            $this->answer_form_id->toString()
                        )
                    )
                ]
            )
        );
    }

    private function buildInsertStatement(
        PersistenceFactory $persistence_factory
    ): Insert {
        return $persistence_factory->insert(
            CoreTables::AnswerForms->getColumns(
                $persistence_factory
            ),
            [
                $persistence_factory->value(
                    \ilDBConstants::T_TEXT,
                    $this->answer_form_id->toString()
                ),
                $persistence_factory->value(
                    \ilDBConstants::T_TEXT,
                    $this->definition::class
                ),
                $persistence_factory->value(
                    \ilDBConstants::T_TEXT,
                    $this->question_id->toString()
                ),
                $persistence_factory->value(
                    \ilDBConstants::T_FLOAT,
                    $this->available_points
                ),
                $persistence_factory->value(
                    \ilDBConstants::T_INTEGER,
                    $this->image_size
                ),
                $persistence_factory->value(
                    \ilDBConstants::T_INTEGER,
                    $this->getShuffleAnswerOptionsForStorage()
                ),
                $persistence_factory->value(
                    \ilDBConstants::T_TEXT,
                    $this->additional_text
                ),
                $persistence_factory->value(
                    \ilDBConstants::T_TEXT,
                    $this->additional_text_legacy
                )
            ]
        );
    }

    private function buildUpdateStatement(
        PersistenceFactory $persistence_factory
    ): Update {
        $answer_form_table_definition = CoreTables::AnswerForms;
        return $persistence_factory->update(
            $answer_form_table_definition->getColumns(
                $persistence_factory,
                [
                    'id',
                    'type',
                    'question_id',
                    'additional_text_legacy'
                ]
            ),
            [
                $persistence_factory->value(
                    \ilDBConstants::T_FLOAT,
                    $this->available_points
                ),
                $persistence_factory->value(
                    \ilDBConstants::T_INTEGER,
                    $this->image_size
                ),
                $persistence_factory->value(
                    \ilDBConstants::T_INTEGER,
                    $this->getShuffleAnswerOptionsForStorage()
                ),
                $persistence_factory->value(
                    \ilDBConstants::T_TEXT,
                    $this->additional_text
                )

            ],
            [
                $persistence_factory->where(
                    $answer_form_table_definition->getIdColumn(
                        $persistence_factory
                    ),
                    $persistence_factory->value(
                        \ilDBConstants::T_TEXT,
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
