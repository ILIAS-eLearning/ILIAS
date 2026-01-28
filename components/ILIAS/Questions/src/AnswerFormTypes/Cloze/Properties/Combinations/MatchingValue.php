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

use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Gaps\AnswerOptions\AnswerOption;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Gaps\Gap;
use ILIAS\Questions\AnswerFormTypes\Cloze\Persistence;
use ILIAS\Questions\Persistence\Replace;
use ILIAS\Questions\Persistence\TableNameBuilder;
use ILIAS\Questions\Persistence\TableTypes;
use ILIAS\Questions\Persistence\Value;
use ILIAS\Language\Language;
use ILIAS\Data\UUID\Uuid;

class MatchingValue
{
    public const string SEPARATOR_IDS = '/';
    public const string SEPARATOR_IN_RANGE = '|';

    public function __construct(
        private readonly Uuid $combination_id,
        private readonly Gap $gap,
        private readonly ?AnswerOption $answer_option = null,
        private readonly ?InRange $in_range = null
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

    public function toStorage(
        Persistence $persistence,
        TableNameBuilder $table_name_builder
    ): Replace {
        if ($this->answer_option === null) {
            throw new \UnexpectedValueException(
                'A MatchingValue without AnswerOption cannot be stored.'
            );
        }

        $table_definition = TableTypes::Additional;
        return new Replace(
            $persistence->getColumns(
                $table_name_builder,
                $table_definition,
                $persistence->getCombinationToAnswerOptionsTableIdentifier()
            ),
            [
                new Value(\ilDBConstants::T_TEXT, $this->combination_id->toString()),
                new Value(\ilDBConstants::T_TEXT, $this->gap->getAnswerInputId()->toString()),
                new Value(\ilDBConstants::T_TEXT, $this->answer_option->getAnswerOptionId()->toString()),
                new Value(\ilDBConstants::T_TEXT, $this->in_range?->value)
            ]
        );
    }
}
