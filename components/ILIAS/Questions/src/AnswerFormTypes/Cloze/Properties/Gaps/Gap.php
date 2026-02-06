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

use ILIAS\Questions\AnswerForm\Persistence;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Gaps\AnswerOptions\AnswerOptions;
use ILIAS\Questions\Definitions\TextMatchingOptions;
use ILIAS\Questions\Persistence\Factory as PersistenceFactory;
use ILIAS\Questions\Persistence\Replace;
use ILIAS\Questions\Persistence\TableNameBuilder;
use ILIAS\Questions\Persistence\TableTypes;
use ILIAS\Questions\Presentation\Definitions\CarryWrapper;
use ILIAS\Data\UUID\Uuid;
use ILIAS\Language\Language;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Component\Input\Field\Section;
use ILIAS\UI\Component\Input\Field\Group;
use ILIAS\UI\Component\Table\DataRow;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\Refinery\Transformation;

class Gap
{
    public const string GAP_PLACEHOLDER_NAME = 'GAP';

    private const string FORM_KEY_TYPE = 'type';
    private const string FORM_KEY_MAX_CHARS = 'max_chars';
    private const string FORM_KEY_STEP_SIZE = 'step_size';
    private const string FORM_KEY_TEXT_MATCHING_METHOD = 'matching_method';
    private const string FORM_KEY_MIN_AUTOCOMPLETE = 'min_autocomplete';
    private const string FORM_KEY_SHUFFLE_ANSWER_OPTIONS = 'shuffle';
    private const string FORM_KEY_ANSWER_OPTIONS = 'answer_options';

    /**
     * @param array<ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Gaps\AnswerOptions\AnswerOption> $answer_options
     */
    public function __construct(
        private readonly Uuid $answer_input_id,
        private readonly Uuid $answer_form_id,
        private int $position,
        private AnswerOptions $answer_options,
        private ?Type $type = null,
        private ?int $max_chars = null,
        private ?float $step_size = null,
        private ?TextMatchingOptions $text_matching_method = null,
        private ?int $min_autocomplete = null,
        private ?bool $shuffle_answer_options = null
    ) {
    }

    public function getAnswerInputId(): Uuid
    {
        return $this->answer_input_id;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function withPosition(
        int $position
    ): self {
        $clone = clone $this;
        $clone->position = $position;
        return $clone;
    }

    public function isUndefined(): bool
    {
        return $this->type === null;
    }

    public function getType(): ?Type
    {
        return $this->type;
    }

    public function withType(
        Type $type
    ): self {
        $clone = clone $this;
        $clone->type = $type;
        return $clone;
    }

    public function getMaxChars(): ?int
    {
        return $this->max_chars;
    }

    public function withMaxChars(
        ?int $max_chars
    ): self {
        $clone = clone $this;
        $clone->max_chars = $max_chars;
        return $clone;
    }

    public function getStepSize(): ?float
    {
        return $this->step_size;
    }

    public function withStepSize(
        float $step_size
    ): self {
        $clone = clone $this;
        $clone->step_size = $step_size;
        return $clone;
    }

    public function getTextMatchingMethod(): ?TextMatchingOptions
    {
        return $this->text_matching_method;
    }

    public function withTextMatchingMethod(
        TextMatchingOptions $matching_method
    ): self {
        $clone = clone $this;
        $clone->text_matching_method = $matching_method;
        return $clone;
    }

    public function getMinAutocomplete(): ?int
    {
        return $this->min_autocomplete;
    }

    public function withMinAutocomplete(
        int $min_autocomplete
    ): self {
        $clone = clone $this;
        $clone->min_autocomplete = $min_autocomplete;
        return $clone;
    }

    public function getShuffleAnswerOptions(): ?bool
    {
        return $this->shuffle_answer_options;
    }

    public function withShuffleAnswerOptions(
        bool $shuffle_answer_options
    ): self {
        $clone = clone $this;
        $clone->shuffle_answer_options = $shuffle_answer_options;
        return $clone;
    }

    public function getAnswerOptions(): AnswerOptions
    {
        return $this->answer_options;
    }

    public function withAnswerOptions(
        AnswerOptions $answer_options
    ): self {
        $clone = clone $this;
        $clone->answer_options = $answer_options;
        return $clone;
    }

    public function getCarryInputs(
        FieldFactory $ff
    ): Group {
        $inputs = [];
        if ($this->type !== null) {
            $inputs[self::FORM_KEY_TYPE] = $ff->hidden()->withValue($this->type?->getIdentifier() ?? '')
                ->withDedicatedName(self::FORM_KEY_TYPE . $this->getShortenedAnswerInputId());
        }

        if ($this->max_chars !== null) {
            $inputs[self::FORM_KEY_MAX_CHARS] = $ff->hidden()->withValue($this->getMaxChars())
                ->withDedicatedName(self::FORM_KEY_MAX_CHARS . $this->getShortenedAnswerInputId());
        }

        if ($this->step_size !== null) {
            $inputs[self::FORM_KEY_STEP_SIZE] = $ff->hidden()->withValue($this->getStepSize())
                ->withDedicatedName(self::FORM_KEY_STEP_SIZE . $this->getShortenedAnswerInputId());
        }

        if ($this->text_matching_method !== null) {
            $inputs[self::FORM_KEY_TEXT_MATCHING_METHOD] = $ff->hidden()->withValue($this->getTextMatchingMethod()->value)
                ->withDedicatedName(self::FORM_KEY_TEXT_MATCHING_METHOD . $this->getShortenedAnswerInputId());
        }

        if ($this->min_autocomplete !== null) {
            $inputs[self::FORM_KEY_MIN_AUTOCOMPLETE] = $ff->hidden()->withValue($this->getMinAutocomplete())
                ->withDedicatedName(self::FORM_KEY_MIN_AUTOCOMPLETE . $this->getShortenedAnswerInputId());
        }

        if ($this->shuffle_answer_options !== null) {
            $inputs[self::FORM_KEY_SHUFFLE_ANSWER_OPTIONS] = $ff->hidden()->withValue($this->getShuffleAnswerOptions() ? '1' : '0')
                ->withDedicatedName(self::FORM_KEY_SHUFFLE_ANSWER_OPTIONS . $this->getShortenedAnswerInputId());
        }

        $inputs[self::FORM_KEY_ANSWER_OPTIONS] = $ff->hidden()->withValue($this->answer_options->buildHiddenInputValue())
            ->withDedicatedName(self::FORM_KEY_ANSWER_OPTIONS . $this->getShortenedAnswerInputId());

        return $ff->group($inputs);
    }

    public function getFromCarryTransformation(
        Refinery $refinery,
        Factory $gaps_factory
    ): Transformation {
        return $refinery->custom()->transformation(
            function (CarryWrapper $v) use ($refinery, $gaps_factory): self {
                $clone = clone $this;
                $clone->type = $this->retrieveTypeFromCarry($refinery, $v, $gaps_factory->getAvailableGapTypes());
                $clone->max_chars = $this->retrieveMaxCharsFromCarry($refinery, $v);
                $clone->step_size = $this->retrieveStepSizeFromCarry($refinery, $v);
                $clone->text_matching_method = $this->retrieveTextMatchingMethodFromCarry($refinery, $v);
                $clone->min_autocomplete = $this->retrieveMinAutocompleteFromCarry($refinery, $v);
                $clone->shuffle_answer_options = $this->retrieveShuffleAnswerOptionsFromCarry($refinery, $v);
                $clone->answer_options = $this->retrieveAnswerOptionsFromCarry($refinery, $v);
                return $clone;
            }
        );
    }

    public function buildReplace(
        ?Replace $replace,
        Persistence $persistence,
        PersistenceFactory $persistence_factory,
        TableNameBuilder $table_name_builder
    ): Replace {
        if ($this->type === null) {
            throw new \UnexpectedValueException(
                'A Gap without Type cannot be stored.'
            );
        }

        $table_definition = TableTypes::AnswerInputs;

        if ($replace === null) {
            return $persistence_factory->replace(
                $persistence->getColumns(
                    $persistence_factory,
                    $table_name_builder,
                    $table_definition
                ),
                $this->buildValuesForGapReplace($persistence_factory)
            );
        }

        return $replace->withAdditionalValues(
            $this->buildValuesForGapReplace($persistence_factory)
        );
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
        return self::GAP_PLACEHOLDER_NAME . '_' . $this->getAnswerInputId()->toString();
    }

    public function buildParticipantViewLegacyInput(): string
    {
        return $this->type->getParticipantViewLegacyInput($this);
    }

    public function getEditAnswerOptionsSection(
        Language $lng,
        FieldFactory $ff
    ): Section {
        $section = $ff->section(
            $this->getType()->getEditAnswerOptionsInputs($this),
            "{$this->buildShortenedGapName()} ({$lng->txt("{$this->getType()->getIdentifier()}_gap")})"
        );

        $edit_section_constraint = $this->getType()->getEditAnswerOptionsSectionConstraint();
        if ($edit_section_constraint !== null) {
            $section = $section->withAdditionalTransformation($edit_section_constraint);
        }


        return $section->withAdditionalTransformation(
            $this->getType()->getBuildGapTransformation($this)
        );
    }

    public function getEditPointsSection(
        Language $lng,
        FieldFactory $ff
    ): Section {
        $type = $this->getType();
        $section = $ff->section(
            $type->getEditPointsInputs($this->getAnswerOptions()),
            "{$this->buildShortenedGapName()} ({$lng->txt("{$type->getIdentifier()}_gap")})"
        );

        $edit_section_constraint = $type->getEditPointsSectionConstraint();
        if ($edit_section_constraint !== null) {
            $section = $section->withAdditionalTransformation($edit_section_constraint);
        }


        return $section->withAdditionalTransformation(
            $type->getAddPointsTransformation($this)
        );
    }

    public function toTableRow(
        DataRowBuilder $row_builder,
        Language $lng
    ): DataRow {
        $total_points = 0;
        $answer_options_list = '';
        foreach ($this->answer_options->getAnswerOptionsAwardingPoints() as $option) {
            $total_points += $option->getAvailablePoints();

            $gap_text = $option->getTextValue();
            if ($gap_text === '') {
                $gap_text = $option->getLowerlimit();
            }

            $answer_options_list .= "{$gap_text} ({$option->getAvailablePoints()})<br>";
        }

        return $row_builder->buildDataRow(
            $this->answer_input_id->toString(),
            [
                'gap' => $this->buildShortenedGapName(),
                'type' => $lng->txt("{$this->type->getIdentifier()}_gap"),
                'answers_options_awarding_points' => $answer_options_list,
                'available_points' => $total_points
            ]
        );
    }

    private function buildValuesForGapReplace(
        PersistenceFactory $persistence_factory
    ): array {
        return [
            $persistence_factory->value(\ilDBConstants::T_TEXT, $this->answer_input_id->toString()),
            $persistence_factory->value(\ilDBConstants::T_TEXT, $this->answer_form_id->toString()),
            $persistence_factory->value(\ilDBConstants::T_INTEGER, $this->position),
            $persistence_factory->value(\ilDBConstants::T_TEXT, $this->type->getIdentifier()),
            $persistence_factory->value(\ilDBConstants::T_INTEGER, $this->max_chars),
            $persistence_factory->value(\ilDBConstants::T_FLOAT, $this->step_size),
            $persistence_factory->value(\ilDBConstants::T_INTEGER, $this->text_matching_method?->value),
            $persistence_factory->value(\ilDBConstants::T_INTEGER, $this->min_autocomplete),
            $persistence_factory->value(
                \ilDBConstants::T_INTEGER,
                $this->shuffle_answer_options === null
                    ? null
                    : ($this->shuffle_answer_options ? 1 : 0)
            )

        ];
    }

    private function retrieveTypeFromCarry(
        Refinery $refinery,
        CarryWrapper $carry,
        array $available_gap_types
    ): ?Type {
        return $carry->retrieve(
            self::FORM_KEY_TYPE . $this->getShortenedAnswerInputId(),
            $refinery->custom()->transformation(
                fn(?string $v): ?Type => $available_gap_types[$v] ?? $this->getType()
            )
        );
    }

    private function retrieveMaxCharsFromCarry(
        Refinery $refinery,
        CarryWrapper $carry
    ): ?int {
        return $carry->retrieve(
            self::FORM_KEY_MAX_CHARS . $this->getShortenedAnswerInputId(),
            $refinery->byTrying([
                $refinery->kindlyTo()->int(),
                $refinery->always($this->getMaxChars())
            ])
        );
    }

    private function retrieveStepSizeFromCarry(
        Refinery $refinery,
        CarryWrapper $carry
    ): ?float {
        return $carry->retrieve(
            self::FORM_KEY_STEP_SIZE . $this->getShortenedAnswerInputId(),
            $refinery->byTrying([
                $refinery->kindlyTo()->float(),
                $refinery->always($this->getStepSize())
            ])
        );
    }

    private function retrieveTextMatchingMethodFromCarry(
        Refinery $refinery,
        CarryWrapper $carry
    ): ?TextMatchingOptions {
        return $carry->retrieve(
            self::FORM_KEY_TEXT_MATCHING_METHOD . $this->getShortenedAnswerInputId(),
            $refinery->custom()->transformation(
                fn(?string $v): ?TextMatchingOptions => $v !== null
                    ? TextMatchingOptions::tryFrom($v)
                    : $this->getTextMatchingMethod()
            )
        );
    }

    private function retrieveMinAutocompleteFromCarry(
        Refinery $refinery,
        CarryWrapper $carry
    ): ?int {
        return $carry->retrieve(
            self::FORM_KEY_MIN_AUTOCOMPLETE . $this->getShortenedAnswerInputId(),
            $refinery->byTrying([
                $refinery->kindlyTo()->int(),
                $refinery->always($this->getMinAutocomplete())
            ])
        );
    }

    private function retrieveShuffleAnswerOptionsFromCarry(
        Refinery $refinery,
        CarryWrapper $carry
    ): ?bool {
        return $carry->retrieve(
            self::FORM_KEY_SHUFFLE_ANSWER_OPTIONS . $this->getShortenedAnswerInputId(),
            $refinery->byTrying([
                $refinery->kindlyTo()->bool(),
                $refinery->always($this->getShuffleAnswerOptions())
            ])
        );
    }

    private function retrieveAnswerOptionsFromCarry(
        Refinery $refinery,
        CarryWrapper $carry
    ): AnswerOptions {
        return $carry->retrieve(
            self::FORM_KEY_ANSWER_OPTIONS . $this->getShortenedAnswerInputId(),
            $refinery->custom()->transformation(
                fn(?string $v): AnswerOptions => $this->answer_options
                    ->withValuesFromHiddenInputValue($v)
            )
        );
    }

    private function getShortenedAnswerInputId(): string
    {
        return mb_substr($this->answer_input_id->toString(), 0, 4);
    }
}
