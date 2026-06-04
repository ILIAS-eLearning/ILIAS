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

use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Gaps\Data\Data;
use ILIAS\Data\UUID\Uuid;
use ILIAS\Language\Language;
use ILIAS\HTTP\Wrapper\ArrayBasedRequestWrapper;
use ILIAS\Refinery\Factory as Refinery;
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
        private Data $data,
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

    public function getData(): Data
    {
        return $this->data;
    }

    public function withData(Data $data): self
    {
        $clone = clone $this;
        $clone->data = $data;
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
            $this->type->getEditAnswerOptionsInputs($this->data),
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
            $this->type->getEditPointsInputs($this->data->getAnswerOptions()),
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

    public function getHiddenInput(
        FieldFactory $ff
    ): Group {
        return $ff->group([
            self::FORM_KEY_TYPE => $ff->hidden()->withValue($this->type?->getIdentifier() ?? '')
                ->withDedicatedName(self::FORM_KEY_TYPE . $this->getShortenedAnswerInputId()),
            self::FORM_KEY_DATA => $this->data->getHiddenInput($ff)
            ->withDedicatedName(self::FORM_KEY_DATA . $this->getShortenedAnswerInputId())
        ]);
    }

    public function withValuesFromPost(
        Refinery $refinery,
        ArrayBasedRequestWrapper $post_wrapper,
        Factory $gaps_factory,
        string $form_input_path
    ): self {
        $available_gap_types = $gaps_factory->getAvailableGapTypes();
        return $post_wrapper->retrieve(
            $form_input_path . '/' . self::FORM_KEY_TYPE . $this->getShortenedAnswerInputId(),
            $refinery->byTrying([
                $refinery->custom()->transformation(
                    fn(?string $v): self => $available_gap_types[$v]
                        ? $this->withType($available_gap_types[$v])
                        : $this
                ),
                $refinery->always($this)
            ])
        )->withData(
            $this->data->withValuesFromPost(
                $refinery,
                $post_wrapper,
                $form_input_path . '/' . self::FORM_KEY_DATA . $this->getShortenedAnswerInputId()
            )
        );
    }

    private function getShortenedAnswerInputId(): string
    {
        return mb_substr($this->answer_input_id->toString(), 0, 4);
    }
}
