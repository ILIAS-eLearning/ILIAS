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
use ILIAS\Questions\Persistence\Insert;
use ILIAS\Questions\Persistence\Manipulate;
use ILIAS\Questions\Persistence\ManipulationType;
use ILIAS\Questions\Persistence\Storable;
use ILIAS\Questions\Persistence\Update;
use ILIAS\Questions\Persistence\Value;
use ILIAS\Questions\Persistence\Where;
use ILIAS\Data\UUID\Uuid;

class TypeGenericProperties implements Storable
{
    public function __construct(
        private readonly Uuid $answer_form_id,
        private readonly Uuid $question_id,
        private readonly ?string $definition_class = null,
        private ?float $available_points = null,
        private ?int $image_size = null,
        private ?bool $shuffle_answer_options = null,
        private string $additional_text = '',
        private string $additional_text_legacy = ''
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
        Manipulate $manipulate
    ): Manipulate {
        if ($this->definition_class === null) {
            throw new \UnexpectedValueException(
                'You cannot save a Answer Form without a Type!'
            );
        }
        return $manipulate->withAdditionalStatement(
            $manipulate->getManipulationType() === ManipulationType::Create
                ? $this->buildInsertStatement()
                : $this->buildUpdateStatement()
        );
    }

    public function toDelete(
        Manipulate $manipulate
    ): Manipulate {
        $answer_form_table_definition = CoreTables::AnswerForms;
        return $manipulate->withAdditionalStatement(
            new Delete(
                $answer_form_table_definition->getTable(),
                [
                    new Where(
                        $answer_form_table_definition->getIdColumn(),
                        $this->answer_form_id->toString()
                    )
                ]
            )
        );
    }

    private function buildInsertStatement(): Insert
    {
        return new Insert(
            CoreTables::AnswerForms->getColumns(),
            [
                new Value(\ilDBConstants::T_TEXT, $this->answer_form_id->toString()),
                new Value(\ilDBConstants::T_TEXT, $this->definition_class),
                new Value(\ilDBConstants::T_TEXT, $this->question_id->toString()),
                new Value(\ilDBConstants::T_FLOAT, $this->available_points),
                new Value(\ilDBConstants::T_INTEGER, $this->image_size),
                new Value(\ilDBConstants::T_INTEGER, $this->shuffle_answer_options ? 1 : 0),
                new Value(\ilDBConstants::T_TEXT, $this->additional_text),
                new Value(\ilDBConstants::T_TEXT, $this->additional_text_legacy)

            ]
        );
    }

    private function buildUpdateStatement(): Update
    {
        $answer_form_table_definition = CoreTables::AnswerForms;
        return new Update(
            $answer_form_table_definition->getColumns([
                $answer_form_table_definition->getIdColumn(),
                'type',
                'question_id',
                'additional_text_legacy'
            ]),
            [
                new Value(\ilDBConstants::T_FLOAT, $this->available_points),
                new Value(\ilDBConstants::T_INT, $this->image_size),
                new Value(\ilDBConstants::T_INT, $this->shuffle_answer_options ? 1 : 0),
                new Value(\ilDBConstants::T_TEXT, $this->additional_text)

            ],
            new Where(
                $answer_form_table_definition->getIdColumn(),
                $this->answer_form_id->toString()
            )
        );
    }
}
