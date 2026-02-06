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

use ILIAS\Questions\Persistence\Delete;
use ILIAS\Questions\Persistence\Factory as PersistenceFactory;
use ILIAS\Questions\Persistence\Replace;
use ILIAS\Questions\Persistence\TableNameBuilder;
use ILIAS\Questions\Persistence\TableTypes;
use ILIAS\Questions\AnswerFormTypes\Cloze\Persistence;
use ILIAS\Data\UUID\Uuid;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Transformation;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;

class AnswerOptions
{
    private array $answer_options_awarding_points;

    public function __construct(
        private readonly Factory $factory,
        private readonly Uuid $answer_input_id,
        private array $answer_options
    ) {
        $this->answer_options_awarding_points = $this->buildAnswerOptionsAwardingPointsFromAnswerOptions($answer_options);
    }

    public function isIncomplete(): bool
    {
        return $this->answer_options === []
            || $this->answer_options_awarding_points === [];
    }

    public function getAnswerOptionById(
        Uuid $answer_option_id
    ): ?AnswerOption {
        return array_find(
            $this->answer_options,
            fn(AnswerOption $v): bool => $v->getAnswerOptionId()->toString() === $answer_option_id->toString()
        );
    }

    public function getAnswerOptionForPositionOrNew(
        int $position
    ): AnswerOption {
        return $this->answer_options[$position]
            ?? $this->factory->getDefaultAnswerOptionForPosition(
                $this->answer_input_id,
                $position
            );
    }

    public function getTagsArrayFromAnswerOptions(): array
    {
        return array_reduce(
            $this->answer_options,
            function (array $c, AnswerOption $v): array {
                if ($v->getTextValue() === '') {
                    return $c;
                }
                $c[] = $v->getTextValue();
                return $c;
            },
            []
        );
    }

    public function getAnswerOptionsAwardingPoints(): array
    {
        return $this->answer_options_awarding_points;
    }

    public function withAnswerOptionsAwardingPoints(
        array $options
    ): self {
        $clone = clone $this;
        $clone->answer_options_awarding_points = array_reduce(
            $options,
            function (array $c, string $v): array {
                $answer_option = $this->retrieveAnswerOptionByTextValue($v);
                if ($answer_option !== null) {
                    $c[] = $answer_option;
                }
                return $c;
            },
            []
        );
        return $clone;
    }

    public function withAnswerOptions(
        array $answer_options
    ): self {
        $clone = clone $this;
        $clone->answer_options = $answer_options;
        return $clone;
    }

    public function withValuesFromHiddenInputValue(
        ?string $value
    ): self {
        if ($value === null
            || !is_array(
                ($decoded_value = json_decode($value, true))
            )
        ) {
            return $this;
        }

        $clone = clone $this;
        $clone->answer_options = array_map(
            fn(array $vs): AnswerOption => $this->factory->buildAnswerOption(
                $vs[AnswerOption::FORM_KEY_ID],
                $this->answer_input_id,
                $vs[AnswerOption::FORM_KEY_POSITION],
                $vs[AnswerOption::FORM_KEY_TEXT_VALUE],
                $vs[AnswerOption::FORM_KEY_LOWER_LIMIT] ?? null,
                $vs[AnswerOption::FORM_KEY_UPPER_LIMIT] ?? null,
                $vs[AnswerOption::FORM_KEY_AVAILABLE_POINTS] ?? null
            ),
            $decoded_value['answer_options'] ?? []
        );

        $answer_inputs_awarding_points = $decoded_value['answer_options_awarding_points'] ?? [];
        $clone->answer_options_awarding_points = array_filter(
            $clone->answer_options,
            fn(AnswerOption $v): bool => in_array(
                $v->getAnswerOptionId()->toString(),
                $answer_inputs_awarding_points
            )
        );
        return $clone;
    }

    public function withAnswerOptionsFromTags(
        array $tags
    ): self {
        $clone = clone $this;
        $position = 0;
        $clone->answer_options = array_map(
            function (string $v) use (&$position): AnswerOption {
                return $this->buildAnswerOptionFromTag(
                    $position++,
                    $v
                );
            },
            $tags
        );
        return $clone;
    }

    public function withAnswerOptionsWithAddedPointsFromForm(
        Refinery $refinery,
        array $values_from_form
    ): self {
        $clone = clone $this;
        $clone->answer_options = array_map(
            function (AnswerOption $v) use ($refinery, $values_from_form): AnswerOption {
                $answer_option_id = $v->getAnswerOptionId()->toString();
                if (array_key_exists($answer_option_id, $values_from_form)) {
                    return $v->withAvailablePoints(
                        $refinery->byTrying([
                            $refinery->kindlyTo()->float(),
                            $refinery->always(null)
                        ])->transform($values_from_form[$answer_option_id])
                    );
                }

                return $v;
            },
            $clone->answer_options
        );
        $clone->answer_options_awarding_points = $clone
            ->buildAnswerOptionsAwardingPointsFromAnswerOptions($clone->answer_options);
        return $clone;
    }

    public function buildArrayForSelectInput(
        Transformation $shuffle_transformation
    ): array {
        return array_reduce(
            $shuffle_transformation->transform($this->answer_options),
            function (array $c, AnswerOption $v): array {
                $c[$v->getAnswerOptionId()->toString()] = $v->getTextValue();
                return $c;
            },
            []
        );
    }

    public function buildHiddenInputValue(): string
    {
        return json_encode([
            'answer_options' => array_map(
                fn(AnswerOption $v): array => $v->buildArrayForHiddenInput(),
                $this->answer_options
            ),
            'answer_options_awarding_points' => array_map(
                fn(AnswerOption $v): string => $v->getAnswerOptionId()->toString(),
                $this->answer_options_awarding_points
            )
        ]);
    }

    public function getEditPointsInputs(
        FieldFactory $ff,
        \Closure $build_label,
        ?array $answer_options_awarding_points = null
    ): array {
        return array_reduce(
            $answer_options_awarding_points ?? $this->answer_options,
            function (array $c, AnswerOption $v) use ($ff, $build_label): array {
                $c[$v->getAnswerOptionId()->toString()] = $ff->numeric($build_label($v))
                    ->withStepSize(0.01)
                    ->withValue($v->getAvailablePoints());
                return $c;
            },
            []
        );
    }

    public function buildReplace(
        ?Replace $replace,
        Persistence $persistence,
        PersistenceFactory $persistence_factory,
        TableNameBuilder $table_name_builder
    ): Replace {
        return array_reduce(
            $this->answer_options,
            fn(?Replace $c, AnswerOption $v): Replace => $v->buildReplace(
                $persistence_factory,
                $c,
                $persistence->getColumns(
                    $persistence_factory,
                    $table_name_builder,
                    TableTypes::AnswerOptions
                )
            ),
            $replace
        );
    }

    public function buildDelete(
        Persistence $persistence,
        PersistenceFactory $persistence_factory,
        TableNameBuilder $table_name_builder
    ): Delete {
        $answer_options_table_definition = TableTypes::AnswerOptions;

        return $persistence_factory->delete(
            $answer_options_table_definition->getTable(
                $persistence_factory,
                $table_name_builder
            ),
            [
                $persistence_factory->where(
                    $persistence->getForeignKeyColumn(
                        $table_name_builder,
                        $answer_options_table_definition
                    ),
                    $persistence_factory->value(
                        \ilDBConstants::T_TEXT,
                        $this->answer_input_id->toString()
                    )
                )
            ]
        );
    }

    private function buildAnswerOptionsAwardingPointsFromAnswerOptions(
        array $answer_options
    ): array {
        return array_filter(
            $answer_options,
            fn(AnswerOption $v): bool => $v->getAvailablePoints() > 0.0
        );
    }

    private function buildAnswerOptionFromTag(
        int $position,
        string $text_value
    ): AnswerOption {
        $answer_option = $this->retrieveAnswerOptionByTextValue($text_value)
            ?? $this->factory->getDefaultAnswerOptionForPosition(
                $this->answer_input_id,
                $position
            );

        return $answer_option
            ->withPosition($position)
            ->withTextValue($text_value);
    }

    private function retrieveAnswerOptionByTextValue(
        string $value
    ): ?AnswerOption {
        $filtered_array = array_filter(
            $this->answer_options,
            fn(AnswerOption $v): bool => $v->getTextValue() === $value
        );
        return array_shift($filtered_array) ?? null;
    }
}
