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

    public function getGapById(Uuid $gap_id): ?Gap
    {
        return $this->gaps[$gap_id->toString()] ?? null;
    }

    public function getGapByTagName(string $tag_name): ?Gap
    {
        return $this->gaps[$this->extractIdFromTagName($tag_name)] ?? null;
    }

    public function hasAtLeastOneGap(): bool
    {
        return $this->gaps !== [];
    }

    public function withGap(Gap $gap): self
    {
        $clone = clone $this;
        $clone->gaps[$gap->getAnswerInputId()->toString()] = $gap;
        return $clone;
    }

    public function withNewGap(int $position): self
    {
        $new_gap = $this->factory->getNewGap($position);
        $clone = clone $this;
        $clone->gaps[$new_gap->getAnswerInputId()->toString()] = $new_gap;
        return $clone;
    }

    public function withAdditionalGapFromTagName(string $tag_name, $position): self
    {
        $id = $this->extractIdFromTagName($tag_name);
        $clone = clone $this;
        $clone->gaps[$id] = $this->factory->getNewGap($position, $id);
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

    private function extractIdFromTagName(string $tag_name): string
    {
        return mb_substr($tag_name, mb_strlen(Gap::GAP_PLACEHOLDER_NAME) + 1);
    }
}
