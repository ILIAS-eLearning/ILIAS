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

use ILIAS\Questions\AnswerFormTypes\Cloze\Definition;
use ILIAS\Questions\Persistence\Query;
use ILIAS\Questions\Persistence\TableTypes;
use ILIAS\Data\UUID\Factory as UuidFactory;
use ILIAS\Data\UUID\Uuid;
use ILIAS\Refinery\Factory as Refinery;

class Factory
{
    public function __construct(
        private readonly UuidFactory $uuid_factory,
        private readonly Refinery $refinery
    ) {
    }

    public function getDefaultAnswerOptions(
        Uuid $answer_input_id
    ): AnswerOptions {
        return new AnswerOptions(
            $this,
            $answer_input_id,
            []
        );
    }

    public function getDefaultAnswerOptionForPosition(
        Uuid $answer_input_id,
        int $position
    ): AnswerOption {
        return new AnswerOption(
            $this->uuid_factory->uuid4(),
            $answer_input_id,
            $position
        );
    }

    public function buildAnswerOption(
        string $answer_option_id,
        Uuid $answer_input_id,
        int $position,
        string $text_value,
        ?string $lower_limit,
        ?string $upper_limit,
        ?string $points
    ): AnswerOption {
        return new AnswerOption(
            $this->uuid_factory->fromString($answer_option_id),
            $answer_input_id,
            $position,
            $text_value,
            $this->convertToFloatOrNull($lower_limit),
            $this->convertToFloatOrNull($upper_limit),
            $this->convertToFloatOrNull($points)
        );
    }

    public function fromDatabase(
        Query $query
    ): array {
        return $query->retrieveCurrentRecord(
            TableTypes::AnswerOptions->getTable($query->getTableNameBuilder(Definition::class)),
            $query->getRefinery()->custom()->transformation(
                function (array $vs): array {
                    $previous_answer_input_id = null;
                    $return_array = [];
                    $answer_options = [];
                    foreach ($vs as $v) {
                        if ($previous_answer_input_id !== null
                            && $v['answer_input_id'] !== $previous_answer_input_id) {
                            $return_array[$previous_answer_input_id] = new AnswerOptions(
                                $this,
                                $this->uuid_factory->fromString($previous_answer_input_id),
                                $answer_options
                            );
                            $answer_options = [];
                        }
                        $previous_answer_input_id = $v['answer_input_id'];
                        $answer_options[] = new AnswerOption(
                            $this->uuid_factory->fromString($v['id']),
                            $this->uuid_factory->fromString($v['answer_input_id']),
                            $v['position'],
                            $v['text_value'],
                            $v['lower_limit'],
                            $v['upper_limit'],
                            $v['points']
                        );
                    }

                    $return_array[$v['answer_input_id']] = new AnswerOptions(
                        $this,
                        $this->uuid_factory->fromString($v['answer_input_id']),
                        $answer_options
                    );

                    return $return_array;
                }
            )
        );
    }

    private function convertToFloatOrNull(
        ?string $value
    ): ?float {
        return $this->refinery->byTrying([
            $this->refinery->kindlyTo()->float(),
            $this->refinery->always(null)
        ])->transform($value);
    }
}
