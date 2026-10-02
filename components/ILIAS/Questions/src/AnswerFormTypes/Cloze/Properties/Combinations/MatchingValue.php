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

namespace ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Combinations;

use ILIAS\Questions\AnswerForm\Persistence\AnswerFormSpecificTableTypes;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Gaps\AnswerOptions\AnswerOption;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Gaps\Gap;
use ILIAS\Questions\AnswerFormTypes\Cloze\TableDefinitions;
use ILIAS\Questions\Definitions\Clonable;
use ILIAS\Questions\Definitions\Range;
use ILIAS\Questions\Persistence\Factory as PersistenceFactory;
use ILIAS\Questions\Persistence\Replace;
use ILIAS\Questions\Persistence\TableNameBuilder;
use ILIAS\Language\Language;
use ILIAS\Data\UUID\Factory as UuidFactory;
use ILIAS\Data\UUID\Uuid;
use ILIAS\Database\FieldDefinition;

class MatchingValue implements Clonable
{
    public const string SEPARATOR_IDS = '/';
    public const string SEPARATOR_IN_RANGE = '|';

    public function __construct(
        private readonly Uuid $combination_id,
        private readonly Gap $gap,
        private readonly ?AnswerOption $answer_option = null,
        private readonly ?Range $in_range = null
    ) {
    }

    public function getGap(): Gap
    {
        return $this->gap;
    }

    public function getAnswerOption(): ?AnswerOption
    {
        return $this->answer_option;
    }

    public function getInRange(): ?Range
    {
        return $this->in_range;
    }

    public function buildPresentationString(
        Language $lng
    ): string {
        if ($this->answer_option === null) {
            return '';
        }

        if ($this->in_range !== null) {
            return $this->in_range->getLabel($lng);
        }

        $value = $this->answer_option->getTextValue();
        if (strlen($value) < 11) {
            return $value;
        }

        return mb_substr($value, 0, 10) . '...';
    }

    public function clone(
        UuidFactory $uuid_factory,
        array $environment = []
    ): static {
        $clone = clone $this;
        $clone->combination_id = $environment['combination_id'];
        $clone->gap = $environment['gaps']->getGapByPosition(
            $this->gap->getPosition()
        );
        return $clone;
    }

    public function toStorage(
        TableDefinitions $table_definitions,
        PersistenceFactory $persistence_factory,
        TableNameBuilder $table_names_builder
    ): Replace {
        if ($this->answer_option === null) {
            throw new \UnexpectedValueException(
                'A MatchingValue without AnswerOption cannot be stored.'
            );
        }

        $table_type = AnswerFormSpecificTableTypes::Additional;
        return $persistence_factory->replace(
            $table_definitions->getColumns(
                $table_names_builder,
                $table_type,
                $table_definitions->getCombinationToAnswerOptionsTableIdentifier()
            ),
            [
                $persistence_factory->value(
                    FieldDefinition::T_TEXT,
                    $this->combination_id->toString()
                ),
                $persistence_factory->value(
                    FieldDefinition::T_TEXT,
                    $this->gap->getAnswerInputId()->toString()
                ),
                $persistence_factory->value(
                    FieldDefinition::T_TEXT,
                    $this->answer_option?->getAnswerOptionId()->toString()
                ),
                $persistence_factory->value(
                    FieldDefinition::T_TEXT,
                    $this->in_range?->value
                )
            ]
        );
    }
}
