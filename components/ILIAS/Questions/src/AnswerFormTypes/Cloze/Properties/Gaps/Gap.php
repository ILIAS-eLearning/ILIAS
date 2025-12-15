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

use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Gaps\Properties\Properties;
use ILIAS\Questions\Presentation\Layout\Definitions\CarryWrapper;
use ILIAS\Data\UUID\Uuid;
use ILIAS\Language\Language;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Transformation;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Component\Input\Field\Section;
use ILIAS\UI\Component\Input\Field\Group;

class Gap
{
    public const string GAP_PLACEHOLDER_NAME = 'GAP';

    private const string FORM_KEY_TYPE = 'type';
    private const string FORM_KEY_DATA = 'data';

    public function __construct(
        private Uuid $answer_input_id,
        private int $position,
        private Properties $properties,
        private ?Type $type = null
    ) {
    }

    public function getAnswerInputId(): ?Uuid
    {
        return $this->answer_input_id;
    }

    public function withAnswerInputId(Uuid $answer_input_id): self
    {
        $clone = clone $this;
        $clone->answer_input_id = $answer_input_id;
        return $clone;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function withPosition(int $position): self
    {
        $clone = clone $this;
        $clone->position = $position;
        return $clone;
    }

    public function withType(Type $type): self
    {
        $clone = clone $this;
        $clone->type = $type;
        return $clone;
    }

    public function getProperties(): Properties
    {
        return $this->properties;
    }

    public function withProperties(Properties $properties): self
    {
        $clone = clone $this;
        $clone->properties = $properties;
        return $clone;
    }

    public function isUndefined(): bool
    {
        return $this->type === null;
    }

    public function getGapPlaceholder(): string
    {
        return "{{{$this->buildGapPlaceholderNameWithId()}}}";
    }

    public function buildShortenedGapName(): string
    {
        return self::GAP_PLACEHOLDER_NAME . '_' . $this->getShortenedAnswerInputId();
    }

    public function buildShortenedGapRepresentation(): string
    {
        return "[{$this->buildShortenedGapName()}]";
    }

    public function buildGapPlaceholderNameWithId(): string
    {
        return self::GAP_PLACEHOLDER_NAME . '_' . $this->answer_input_id->toString();
    }

    public function getEditAnswerOptionsSection(
        Language $lng,
        FieldFactory $ff
    ): Section {
        $section = $ff->section(
            $this->type->getEditAnswerOptionsInputs($this->properties),
            "{$this->buildShortenedGapName()} ({$lng->txt("{$this->type->getIdentifier()}_gap")})"
        );

        $edit_section_constraint = $this->type->getEditAnswerOptionsSectionConstraint();
        if ($edit_section_constraint !== null) {
            $section = $section->withAdditionalTransformation($edit_section_constraint);
        }


        return $section->withAdditionalTransformation(
            $this->type->getBuildGapTransformation($this)
        );
    }

    public function getEditPointsSection(
        Language $lng,
        FieldFactory $ff
    ): Section {
        $section = $ff->section(
            $this->type->getEditPointsInputs($this->properties->getAnswerOptions()),
            "{$this->buildShortenedGapName()} ({$lng->txt("{$this->type->getIdentifier()}_gap")})"
        );

        $edit_section_constraint = $this->type->getEditPointsSectionConstraint();
        if ($edit_section_constraint !== null) {
            $section = $section->withAdditionalTransformation($edit_section_constraint);
        }


        return $section->withAdditionalTransformation(
            $this->type->getAddPointsTransformation($this)
        );
    }

    public function getCarryInputs(
        FieldFactory $ff
    ): Group {
        return $ff->group([
            self::FORM_KEY_TYPE => $ff->hidden()->withValue($this->type?->getIdentifier() ?? '')
                ->withDedicatedName(self::FORM_KEY_TYPE . $this->getShortenedAnswerInputId()),
            self::FORM_KEY_DATA => $this->properties->getCarryInputs($ff)
            ->withDedicatedName(self::FORM_KEY_DATA . $this->getShortenedAnswerInputId())
        ]);
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
                $available_gap_types = $gaps_factory->getAvailableGapTypes();
                return $v->retrieve(
                    self::FORM_KEY_TYPE . $this->getShortenedAnswerInputId(),
                    $refinery->byTrying([
                        $refinery->custom()->transformation(
                            fn(?string $v): self => $available_gap_types[$v]
                                ? $this->withType($available_gap_types[$v])
                                : $this
                        ),
                        $refinery->always($this)
                    ])
                )->withProperties(
                    $v->retrieve(
                        self::FORM_KEY_DATA . $this->getShortenedAnswerInputId(),
                        $this->properties->getFromCarryTransformation($refinery)
                    )
                );
            }
        );
    }

    private function getShortenedAnswerInputId(): string
    {
        return mb_substr($this->answer_input_id->toString(), 0, 4);
    }
}
