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

use ILIAS\Questions\Persistence\Delete;
use ILIAS\Questions\Persistence\Junctor;
use ILIAS\Questions\Persistence\Manipulate;
use ILIAS\Questions\Persistence\Operator;
use ILIAS\Questions\Persistence\TableNameBuilder;
use ILIAS\Questions\Persistence\TableTypes;
use ILIAS\Questions\Persistence\Value;
use ILIAS\Questions\Persistence\Where;
use ILIAS\Questions\AnswerFormTypes\Cloze\Persistence;
use ILIAS\Questions\Presentation\Layout\Definitions\CarryWrapper;
use ILIAS\Data\UUID\Uuid;
use ILIAS\Language\Language;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Transformation;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Component\Input\Field\Section;
use ILIAS\UI\Component\Input\Field\Group;

class Gaps
{
    public function __construct(
        private readonly Factory $factory,
        private array $gaps
    ) {
    }

    public function getGapById(
        Uuid $gap_id
    ): ?Gap {
        return $this->gaps[$gap_id->toString()] ?? null;
    }

    public function getGapByTagName(
        string $tag_name
    ): ?Gap {
        return $this->gaps[$this->extractIdFromTagName($tag_name)] ?? null;
    }

    public function hasAtLeastOneGap(): bool
    {
        return $this->gaps !== [];
    }

    public function withGap(
        Gap $gap
    ): self {
        $clone = clone $this;
        $clone->gaps[$gap->getAnswerInputId()->toString()] = $gap;
        return $clone;
    }

    public function withNewGap(
        Uuid $answer_form_id,
        int $position
    ): self {
        $new_gap = $this->factory->getNewGap($answer_form_id, $position);
        $clone = clone $this;
        $clone->gaps[$new_gap->getAnswerInputId()->toString()] = $new_gap;
        return $clone;
    }

    public function withAdditionalGapFromTagName(
        Uuid $answer_form_id,
        string $tag_name,
        int $position
    ): self {
        $answer_input_id = $this->extractIdFromTagName($tag_name);
        $clone = clone $this;
        $clone->gaps[$answer_input_id] = $this->factory->getNewGap(
            $answer_form_id,
            $position,
            $answer_input_id
        );
        return $clone;
    }

    public function withResetGaps(): self
    {
        if ($this->gaps === []) {
            return $this;
        }

        $clone = clone $this;
        $clone->gaps = [];
        return $clone;
    }

    public function getUndefinedGaps(): array
    {
        return array_filter(
            $this->gaps,
            fn(Gap $v): bool => $v->isUndefined()
        );
    }

    public function getPlaceholderArrayForPreview(): array
    {
        return array_reduce(
            $this->gaps,
            function (array $c, Gap $v): array {
                $c[$v->buildGapPlaceholderNameWithId($v)] = $v->buildShortenedGapRepresentation($v);
                return $c;
            },
            []
        );
    }

    public function buildGapsTypeInputs(
        Language $lng,
        FieldFactory $ff,
        Refinery $refinery,
        array $available_gap_types
    ): Section {
        return $ff->section(
            array_reduce(
                $this->getUndefinedGaps(),
                function (array $c, Gap $v) use ($ff, $available_gap_types): array {
                    $c[$v->getAnswerInputId()->toString()] = $ff->select(
                        $v->buildShortenedGapName(),
                        $available_gap_types
                    )->withRequired(true);
                    return $c;
                },
                []
            ),
            $lng->txt('select_gap_types')
        )->withAdditionalTransformation(
            $refinery->custom()->transformation(
                fn(array $vs): self => array_reduce(
                    array_keys($vs),
                    fn(self $c, string $v): self => $c->withGap(
                        $c->gaps[$v]->withType(
                            $this->factory->getGapTypeByIdentifier($vs[$v])
                        )
                    ),
                    $this
                )
            )
        );
    }

    public function buildAnswerOptionsInputs(
        Language $lng,
        FieldFactory $ff,
        Refinery $refinery
    ): Section {
        return $ff->section(
            array_reduce(
                $this->gaps,
                function (array $c, Gap $v) use ($lng, $ff): array {
                    $c[$v->getAnswerInputId()->toString()] = $v->getEditAnswerOptionsSection(
                        $lng,
                        $ff
                    );
                    return $c;
                },
                []
            ),
            $lng->txt('add_answer_options')
        )->withAdditionalTransformation(
            $refinery->custom()->transformation(
                fn(array $vs): self => array_reduce(
                    array_keys($vs),
                    fn(self $c, string $v): self => $c->withGap($vs[$v]),
                    $this
                )
            )
        );
    }

    public function buildPointInputs(
        Language $lng,
        FieldFactory $ff,
        Refinery $refinery
    ): Section {
        return $ff->section(
            array_reduce(
                $this->gaps,
                function (array $c, Gap $v) use ($lng, $ff): array {
                    $c[$v->getAnswerInputId()->toString()] = $v->getEditPointsSection(
                        $lng,
                        $ff
                    );
                    return $c;
                },
                []
            ),
            $lng->txt('add_points')
        )->withAdditionalTransformation(
            $refinery->custom()->transformation(
                fn(array $vs): self => array_reduce(
                    array_keys($vs),
                    fn(self $c, string $v): self => $c->withGap($vs[$v]),
                    $this
                )
            )
        );
    }

    public function getCarryInputs(FieldFactory $ff): Group
    {
        return $ff->group(
            array_reduce(
                $this->gaps,
                function (array $c, Gap $v) use ($ff): array {
                    $c[$v->getAnswerInputId()->toString()] = $v->getCarryInputs($ff)
                        ->withDedicatedName($v->getAnswerInputId()->toString());
                    return $c;
                },
                []
            )
        );
    }

    public function getFromCarryTransformation(
        Refinery $refinery,
        Factory $gaps_factory
    ): Transformation {
        return $refinery->custom()->transformation(
            function (?CarryWrapper $v) use ($refinery, $gaps_factory): self {
                if ($v === null) {
                    return $this;
                }
                $clone = clone $this;
                $clone->gaps = array_map(
                    fn(Gap $gap): Gap => $v->retrieve(
                        $gap->getAnswerInputId()->toString(),
                        $gap->getFromCarryTransformation(
                            $refinery,
                            $gaps_factory
                        )
                    ),
                    $this->gaps
                );
                return $clone;
            }
        );
    }

    public function toStorage(
        Manipulate $manipulate,
        Persistence $persistence,
        TableNameBuilder $table_name_builder
    ): Manipulate {
        $table_definition = TableTypes::AnswerInputs;
        $manipulate->withAdditionalStatement(
            new Delete(
                $table_definition->getTable($table_name_builder),
                [
                    new Where(
                        $persistence->getIdColumn($table_name_builder, $table_definition),
                        new Value(
                            \ilDBConstants::T_TEXT,
                            array_map(
                                fn(Gap $v): string => $v->getAnswerInputId()->toString(),
                                $this->gaps
                            )
                        ),
                        Operator::In,
                        Junctor::Conjunction,
                        true
                    )
                ]
            )
        );

        [
            'gaps' => $replace_for_gaps,
            'answer_options' => $replace_for_answer_options
        ] = array_reduce(
            $this->gaps,
            fn(array $c, Gap $v): array => [
                'gaps' => $v->buildReplace(
                    $c['gaps'],
                    $persistence,
                    $table_name_builder
                ),
                'answer_options' => $v->getAnswerOptions()->buildReplace(
                    $c['answer_options'],
                    $persistence,
                    $table_name_builder
                )
            ],
            [
                'gaps' => null,
                'answer_options' => null
            ]
        );

        return $manipulate->withAdditionalStatement($replace_for_gaps)
            ->withAdditionalStatement($replace_for_answer_options);
    }

    private function extractIdFromTagName(string $tag_name): string
    {
        return mb_substr($tag_name, mb_strlen(Gap::GAP_PLACEHOLDER_NAME) + 1);
    }
}
