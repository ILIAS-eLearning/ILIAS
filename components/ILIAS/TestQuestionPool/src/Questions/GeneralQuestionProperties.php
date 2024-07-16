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

namespace ILIAS\TestQuestionPool\Questions;

class GeneralQuestionProperties
{
    public function __construct(
        private readonly \ilComponentFactory $component_factory,
        private readonly int $question_id,
        private ?int $original_id = null,
        private ?string $external_id = null,
        private int $parent_id = 0,
        private readonly int $type_id = 0,
        private readonly string $class_name = '',
        private int $owner = 0,
        private string $title = '',
        private string $description = '',
        private string $question_text = '',
        private float $reachable_points = 0.0,
        private int $number_of_tries = 0,
        private string $lifecycle = 'draft',
        private ?string $author = null,
        private int $updated_timestamp = 0,
        private int $created_timestamp = 0,
        private bool $complete = true,
        private readonly ?string $additional_content_editing_mode = null
    ) {
    }

    public function getQuestionId(): int
    {
        return $this->question_id;
    }

    public function getOriginalId(): ?int
    {
        return $this->original_id;
    }

    public function withOriginalId(int $original_id): self
    {
        $clone = clone $this;
        $clone->original_id = $original_id;
        return $clone;
    }

    public function getExternalId(): ?string
    {
        return $this->external_id;
    }

    public function withExternalId(string $external_id): self
    {
        $clone = clone $this;
        $clone->external_id = $external_id;
        return $clone;
    }

    public function getParentId(): int
    {
        return $this->parent_id;
    }

    public function withParentId(int $parent_id): self
    {
        $clone = clone $this;
        $clone->parent_id = $parent_id;
        return $clone;
    }

    public function getTypeId(): int
    {
        return $this->type_id;
    }

    public function getClassName(): string
    {
        return $this->class_name;
    }

    public function getGuiClassName(): string
    {
        return $this->class_name . 'GUI';
    }

    public function getTypeName(\ilLanguage $lng): string
    {
        if ($this->class_name === '') {
            return '';
        }

        if (file_exists("./components/ILIAS/TestQuestionPool/classes/class." . $this->class_name . ".php")) {
            return $lng->txt($this->class_name);
        }

        foreach ($this->component_factory->getActivePluginsInSlot('qst') as $pl) {
            if ($pl->getQuestionType() === $this->class_name) {
                return $pl->getQuestionTypeTranslation();
            }
        }
        return "";
    }

    public function getOwner(): int
    {
        return $this->owner;
    }

    public function withOwner(int $owner): self
    {
        $clone = clone $this;
        $clone->owner = $owner;
        return $clone;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function withTitle(string $title): self
    {
        $clone = clone $this;
        $clone->title = $title;
        return $clone;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function withDescription(string $description): self
    {
        $clone = clone $this;
        $clone->description = $description;
        return $clone;
    }

    public function getQuestionText(): string
    {
        return $this->question_text;
    }

    public function withQuestionText(string $question_text): self
    {
        $clone = clone $this;
        $clone->question_text = $question_text;
        return $clone;
    }

    public function getMaximumPoints(): float
    {
        return $this->reachable_points;
    }

    public function withMaximumPoints(float $reachable_points): self
    {
        $clone = clone $this;
        $clone->reachable_points = $reachable_points;
        return $clone;
    }

    public function getNumberOfTries(): int
    {
        return $this->number_of_tries;
    }

    public function withNumberOfTries(int $number_of_tries): self
    {
        $clone = clone $this;
        $clone->number_of_tries = $number_of_tries;
        return $clone;
    }

    public function getLifecycle(): string
    {
        return $this->lifecycle;
    }

    public function withLifecycle(string $lifecycle): self
    {
        $clone = clone $this;
        $clone->lifecycle = $lifecycle;
        return $clone;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function withAuthor(string $author): self
    {
        $clone = clone $this;
        $clone->author = $author;
        return $clone;
    }

    public function getUpdatedTimestamp(): int
    {
        return $this->updated_timestamp;
    }

    public function withUpdatedTimestamp(int $updated_timestamp): self
    {
        $clone = clone $this;
        $clone->updated_timestamp = $updated_timestamp;
        return $clone;
    }

    public function getCreatedTimestamp(): int
    {
        return $this->created_timestamp;
    }

    public function withCreatedTimestamp(int $created_timestamp): self
    {
        $clone = clone $this;
        $clone->created_timestamp = $created_timestamp;
        return $clone;
    }

    public function getCompletionStatus(): bool
    {
        return $this->complete;
    }

    public function withCompletionStatus(bool $complete): self
    {
        $clone = clone $this;
        $clone->complete = $complete;
        return $clone;
    }

    public function getAdditionalContentEditingMode(): ?string
    {
        return $this->additional_content_editing_mode;
    }

    /**
     * Checks whether the question is a clone of another question or not
     */
    public function isClone(): bool
    {
        return $this->original_id !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function toStorage(): array
    {
        return [
            'question_id' => [\ilDBConstants::T_INTEGER, $this->getQuestionId()],
            'question_type_fi' => [\ilDBConstants::T_INTEGER, $this->getTypeId()],
            'obj_fi' => [\ilDBConstants::T_INTEGER, $this->getParentId()],
            'title' => [\ilDBConstants::T_TEXT, $this->getTitle()],
            'description' => [\ilDBConstants::T_TEXT, $this->getDescription()],
            'author' => [\ilDBConstants::T_TEXT, $this->getAuthor()],
            'owner' => [\ilDBConstants::T_INTEGER, $this->getOwner()],
            'points' => [\ilDBConstants::T_FLOAT, $this->getMaximumPoints()],
            'complete' => [\ilDBConstants::T_TEXT, $this->getCompletionStatus()],
            'original_id' => [\ilDBConstants::T_INTEGER, $this->getOriginalId()],
            'tstamp' => [\ilDBConstants::T_INTEGER, $this->getUpdatedTimestamp()],
            'created' => [\ilDBConstants::T_INTEGER, $this->getCreatedTimestamp()],
            'nr_of_tries' => [\ilDBConstants::T_INTEGER, $this->getNumberOfTries()],
            'question_text' => [\ilDBConstants::T_TEXT, $this->getQuestionText()],
            'add_content_edit_mode' => [\ilDBConstants::T_TEXT, $this->getAdditionalContentEditingMode()],
            'external_id' => [\ilDBConstants::T_TEXT, $this->getExternalId()],
            'lifecycle' => [\ilDBConstants::T_TEXT, $this->getLifecycle()]
        ];
    }

}
