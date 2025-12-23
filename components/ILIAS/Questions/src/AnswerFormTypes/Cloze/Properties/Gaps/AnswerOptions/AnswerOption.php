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

namespace ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Gaps\AnswerOptions;

use ILIAS\Questions\Persistence\Replace;
use ILIAS\Questions\Persistence\TableNameBuilder;
use ILIAS\Questions\Persistence\TableTypes;
use ILIAS\Questions\Persistence\Value;
use ILIAS\Questions\AnswerFormTypes\Cloze\Persistence;
use ILIAS\Data\UUID\Uuid;

class AnswerOption
{
    public const string FORM_KEY_ID = 'id';
    public const string FORM_KEY_POSITION = 'position';
    public const string FORM_KEY_TEXT_VALUE = 'text_value';
    public const string FORM_KEY_LOWER_LIMIT = 'lower_limit';
    public const string FORM_KEY_UPPER_LIMIT = 'upper_limit';
    public const string FORM_KEY_AVAILABLE_POINTS = 'points';

    public function __construct(
        private readonly Uuid $answer_option_id,
        private readonly Uuid $answer_input_id,
        private int $position,
        private string $text_value = '',
        private ?float $lower_limit = null,
        private ?float $upper_limit = null,
        private ?float $available_points = null
    ) {
    }

    public function getAnswerOptionId(): Uuid
    {
        return $this->answer_option_id;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function withPosition(int $position): self
    {
        $clone = clone $this;
        $clone->position = $position;
        return $clone;
    }

    public function getTextValue(): string
    {
        return $this->text_value;
    }

    public function withTextValue(string $text_value): self
    {
        $clone = clone $this;
        $clone->text_value = $text_value;
        return $clone;
    }

    public function getLowerLimit(): ?float
    {
        return $this->lower_limit;
    }

    public function withLowerLimit(float $lower_limit): self
    {
        $clone = clone $this;
        $clone->lower_limit = $lower_limit;
        return $clone;
    }

    public function getUpperLimit(): ?float
    {
        return $this->upper_limit;
    }

    public function withUpperLimit(?float $upper_limit): self
    {
        $clone = clone $this;
        $clone->upper_limit = $upper_limit;
        return $clone;
    }

    public function getAvailablePoints(): ?float
    {
        return $this->available_points;
    }

    public function withAvailablePoints(?float $available_points): self
    {
        $clone = clone $this;
        $clone->available_points = $available_points;
        return $clone;
    }

    public function buildArrayForHiddenInput(): array
    {
        $values = [
            self::FORM_KEY_ID => $this->getAnswerOptionId()->toString(),
            self::FORM_KEY_POSITION => $this->getPosition(),
            self::FORM_KEY_TEXT_VALUE => $this->getTextValue()
        ];

        if ($this->getUpperLimit() !== null) {
            $values[self::FORM_KEY_LOWER_LIMIT] = (string) $this->getLowerLimit();
        }

        if ($this->getUpperLimit() !== null) {
            $values[self::FORM_KEY_UPPER_LIMIT] = (string) $this->getUpperLimit();
        }

        if ($this->getUpperLimit() !== null) {
            $values[self::FORM_KEY_AVAILABLE_POINTS] = (string) $this->getAvailablePoints();
        }

        return $values;
    }

    public function buildReplace(
        ?Replace $replace,
        Persistence $persistence,
        TableNameBuilder $table_name_builder
    ): Replace {
        $table_definition = TableTypes::AnswerOptions;

        if ($replace === null) {
            return new Replace(
                $persistence->getColumns($table_name_builder, $table_definition),
                $this->buildValuesForGapReplace()
            );
        }

        return $replace->withAdditionalValues(
            $this->buildValuesForGapReplace()
        );
    }

    private function buildValuesForGapReplace(): array
    {
        return [
            new Value(\ilDBConstants::T_TEXT, $this->answer_option_id->toString()),
            new Value(\ilDBConstants::T_TEXT, $this->answer_input_id->toString()),
            new Value(\ilDBConstants::T_INTEGER, $this->position),
            new Value(\ilDBConstants::T_TEXT, $this->text_value),
            new Value(\ilDBConstants::T_FLOAT, $this->available_points),
            new Value(\ilDBConstants::T_FLOAT, $this->lower_limit),
            new Value(\ilDBConstants::T_FLOAT, $this->upper_limit)

        ];
    }
}
