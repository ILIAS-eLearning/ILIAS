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

use ILIAS\Questions\AnswerFormTypes\Cloze\Definition;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Gaps\AnswerOptions\Factory as AnswerOptionsFactory;
use ILIAS\Questions\Persistence\Query;
use ILIAS\Questions\Persistence\TableTypes;
use ILIAS\Questions\Definitions\TextMatchingOptions;
use ILIAS\Language\Language;
use ILIAS\Data\UUID\Uuid;
use ILIAS\Data\UUID\Factory as UuidFactory;

class Factory
{
    private array $available_gap_types;

    public function __construct(
        private readonly UuidFactory $uuid_factory,
        private readonly AnswerOptionsFactory $answer_options_factory,
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
        Uuid $answer_form_id,
        int $position,
        string $id = ''
    ): Gap {
        $answer_input_id = $id !== ''
            ? $this->uuid_factory->fromString($id)
            : $this->uuid_factory->uuid4();

        return new Gap(
            $answer_input_id,
            $answer_form_id,
            $position,
            $this->answer_options_factory->getDefaultAnswerOptions($answer_input_id)
        );
    }

    public function getEmptyGapsObject(
        Uuid $answer_form_id,
    ): Gaps {
        return new Gaps(
            $this,
            $answer_form_id,
            []
        );
    }

    public function getGapTypeByIdentifier(
        string $identifier
    ): Type {
        if (!array_key_exists($identifier, $this->available_gap_types)) {
            throw new \InvalidArgumentException('Gap type does not exist.');
        }
        return $this->available_gap_types[$identifier];
    }

    public function fromDatabase(
        Uuid $answer_form_id,
        Query $query
    ): Gaps {
        $answer_options = $this->answer_options_factory->fromDatabase($query);

        return $query->retrieveCurrentRecord(
            TableTypes::AnswerInputs->getTable($query->getTableNameBuilder(Definition::class)),
            $query->getRefinery()->custom()->transformation(
                function (array $vs) use ($answer_form_id, $answer_options): Gaps {
                    $previous_answer_input_id = null;
                    $gaps = [];
                    foreach ($vs as $v) {
                        if ($v['answer_form_id'] !== $answer_form_id->toString()
                            || $previous_answer_input_id === $v['id']) {
                            continue;
                        }
                        $previous_answer_input_id = $v['id'];
                        $gaps[] = $this->buildGapFromDBValues($v, $answer_options);
                    }
                    return new Gaps(
                        $this,
                        $answer_form_id,
                        $gaps
                    );
                }
            )
        );
    }

    private function buildGapFromDBValues(
        array $values,
        array $answer_options
    ): Gap {
        $answer_input_uuid = $this->uuid_factory->fromString($values['id']);
        return new Gap(
            $answer_input_uuid,
            $this->uuid_factory->fromString($values['answer_form_id']),
            $values['position'],
            $answer_options[$values['id']],
            $this->getGapTypeByIdentifier($values['gap_type']),
            $values['max_chars'],
            $values['step_size'],
            $values['text_matching_method'] === null
                ? null
                : TextMatchingOptions::tryFrom($values['text_matching_method']),
            $values['min_autocomplete'],
            $values['shuffle_answer_options'] === 1
        );
    }
}
