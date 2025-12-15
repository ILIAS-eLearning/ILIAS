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

namespace ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Gaps;

use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Gaps\Properties\Factory as PropertiesFactory;
use ILIAS\Language\Language;
use ILIAS\Data\UUID\Factory as UuidFactory;

class Factory
{
    private array $available_gap_types;

    public function __construct(
        private readonly UuidFactory $uuid_factory,
        private readonly PropertiesFactory $data_factory,
        array $available_gap_types
    ) {
        foreach ($available_gap_types as $type) {
            $this->available_gap_types[$type->getIdentifier()] = $type;
        }
    }

    public function getAvailableGapTypes(): array
    {
        return $this->available_gap_types;
    }

    public function getAvailableGapTypesOptionsArray(
        Language $lng
    ): array {
        return array_map(
            fn(Type $v) => $lng->txt("{$v->getIdentifier()}_gap"),
            $this->available_gap_types
        );
    }

    public function getNewGap(
        int $position,
        string $id = ''
    ): Gap {
        $answer_input_id = $id !== ''
            ? $this->uuid_factory->fromString($id)
            : $this->uuid_factory->uuid4();
        return new Gap(
            $answer_input_id,
            $position,
            $this->data_factory->getDefaultProperties($answer_input_id)
        );
    }

    public function getEmptyGapsObject(): Gaps
    {
        return new Gaps(
            $this,
            []
        );
    }

    public function getGapTypeByIdentifier(string $identifier): Type
    {
        if (!array_key_exists($identifier, $this->available_gap_types)) {
            throw new \InvalidArgumentException('Gap type does not exist.');
        }
        return $this->available_gap_types[$identifier];
    }

    public function fromDatabase(
        array $data
    ): Gaps {

    }
}
