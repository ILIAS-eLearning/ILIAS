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

namespace ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Gaps\Properties;

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

    public function getDefaultProperties(
        Uuid $answer_input_id
    ): Properties {
        return new Properties(
            $answer_input_id,
            new AnswerOptions(
                $this,
                []
            )
        );
    }

    public function getDefaultAnswerOptionForPosition(int $position): AnswerOption
    {
        return new AnswerOption(
            $this->uuid_factory->uuid4(),
            $position
        );
    }

    public function buildAnswerOption(
        string $answer_option_id,
        int $position,
        string $text_value,
        ?string $lower_limit,
        ?string $upper_limit,
        ?string $points
    ): AnswerOption {
        return new AnswerOption(
            $this->uuid_factory->fromString($answer_option_id),
            $position,
            $text_value,
            $this->convertToFloatOrNull($lower_limit),
            $this->convertToFloatOrNull($upper_limit),
            $this->convertToFloatOrNull($points)
        );
    }

    public function fromDatabase(
        array $data
    ): Gaps {

    }

    private function convertToFloatOrNull(?string $value): ?float
    {
        return $this->refinery->byTrying([
            $this->refinery->kindlyTo()->float(),
            $this->refinery->always(null)
        ])->transform($value);
    }
}
