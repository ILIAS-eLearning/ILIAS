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

namespace ILIAS\Questions\AnswerForm\Capabilities\TextFeedback;

use ILIAS\Questions\Persistence\Factory as PersistenceFactory;
use ILIAS\Data\Text\Markdown;
use ILIAS\Data\UUID\Uuid;
use ILIAS\Database\FieldDefinition;
use ILIAS\Refinery\Factory as Refinery;

class SpecificTextFeedback
{
    public function __construct(
        private readonly Uuid $id,
        private readonly Uuid $answer_form_id,
        private readonly Uuid $parent_id,
        private string $condition,
        private ?Markdown $feedback_text = null,
        private string $feedback_legacy = ''
    ) {
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getParentId(): Uuid
    {
        return $this->parent_id;
    }

    public function getCondition(): string
    {
        return $this->condition;
    }

    public function withCondition(
        string $condition
    ): self {
        $clone = clone $this;
        $clone->condition = $condition;
        return $clone;
    }

    public function getFeedbackText(): Markdown
    {
        return $this->feedback_text;
    }

    public function withFeedbackText(
        Markdown $text
    ): self {
        $clone = clone $this;
        $clone->feedback_text = $text;
        return $clone;
    }

    public function getFeedbackTextForPresentation(
        Refinery $refinery
    ): string {
        if ($this->feedback_text !== null) {
            return $refinery->string()->markdown()->toHTML()->transform(
                $this->feedback_text->getRawRepresentation()
            );
        }

        return $this->feedback_legacy;
    }

    public function toStorage(
        PersistenceFactory $persistence_factory
    ): array {
        if ($this->feedback_text === null) {
            throw new \UnexpectedValueException(
                'You cannot save a SpecificTextFeedback without a feedback text!'
            );
        }

        return [
            $persistence_factory->value(
                FieldDefinition::T_TEXT,
                $this->id->toString()
            ),
            $persistence_factory->value(
                FieldDefinition::T_TEXT,
                $this->answer_form_id->toString()
            ),
            $persistence_factory->value(
                FieldDefinition::T_TEXT,
                $this->parent_id->toString()
            ),
            $persistence_factory->value(
                FieldDefinition::T_TEXT,
                $this->condition
            ),
            $persistence_factory->value(
                FieldDefinition::T_TEXT,
                $this->feedback_text->getRawRepresentation()
            ),
            $persistence_factory->value(
                FieldDefinition::T_TEXT,
                $this->feedback_legacy
            )
        ];
    }
}
