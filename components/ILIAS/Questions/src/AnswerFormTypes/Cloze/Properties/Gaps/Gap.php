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

use ILIAS\Questions\AnswerForm\Persistence\AnswerFormSpecificTableTypes;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Gaps\AnswerOptions\AnswerOptions;
use ILIAS\Questions\AnswerFormTypes\Cloze\TableDefinitions;
use ILIAS\Questions\Definitions\TextMatchingOptions;
use ILIAS\Questions\Persistence\Factory as PersistenceFactory;
use ILIAS\Questions\Persistence\Replace;
use ILIAS\Questions\Persistence\TableNameBuilder;
use ILIAS\Data\UUID\Uuid;
use ILIAS\Language\Language;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Component\Input\Field\Section;
use ILIAS\UI\Component\Table\DataRow;
use ILIAS\UI\Component\Table\DataRowBuilder;

class Gap
{
    public const string GAP_PLACEHOLDER_NAME = 'GAP';

    private const string KEY_TYPE = 'type';
    private const string KEY_POSITION = 'position';
    private const string KEY_MAX_CHARS = 'max_chars';
    private const string KEY_STEP_SIZE = 'step_size';
    private const string KEY_TEXT_MATCHING_METHOD = 'matching_method';
    private const string KEY_MIN_AUTOCOMPLETE = 'min_autocomplete';
    private const string KEY_SHUFFLE_ANSWER_OPTIONS = 'shuffle';
    private const string KEY_ANSWER_OPTIONS = 'answer_options';

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

    public function toCarry(): array
    {
        $inputs = [
            self::KEY_TYPE => $this->type?->getIdentifier() ?? '',
            self::KEY_POSITION => $this->position,
            self::KEY_ANSWER_OPTIONS => $this->answer_options->toCarry()
        ];

        if ($this->max_chars !== null) {
            $inputs[self::KEY_MAX_CHARS] = (string) $this->getMaxChars();
        }

        if ($this->step_size !== null) {
            $inputs[self::KEY_STEP_SIZE] = (string) $this->getStepSize();
        }

        if ($this->text_matching_method !== null) {
            $inputs[self::KEY_TEXT_MATCHING_METHOD] = $this->getTextMatchingMethod()->value;
        }

        if ($this->min_autocomplete !== null) {
            $inputs[self::KEY_MIN_AUTOCOMPLETE] = (string) $this->getMinAutocomplete();
        }

        if ($this->shuffle_answer_options !== null) {
            $inputs[self::KEY_SHUFFLE_ANSWER_OPTIONS] = $this->getShuffleAnswerOptions() ? '1' : '0';
        }

        return $inputs;
    }

    public function withValuesFromCarry(
        Refinery $refinery,
        Factory $gaps_factory,
        array $carry
    ): self {
        if ($carry === null) {
            return $this;
        }

        $clone = clone $this;
        $clone->type = $carry[self::KEY_TYPE] === ''
            ? $this->getType()
            : $gaps_factory->getGapTypeByIdentifier($carry[self::KEY_TYPE]);
        $clone->position = $carry[self::KEY_POSITION];

        $clone->max_chars = $refinery->byTrying([
            $refinery->kindlyTo()->int(),
            $refinery->always($this->getMaxChars())
        ])->transform($carry[self::KEY_MAX_CHARS] ?? null);

        $clone->step_size = $refinery->byTrying([
            $refinery->kindlyTo()->float(),
            $refinery->always($this->getStepSize())
        ])->transform($carry[self::KEY_STEP_SIZE] ?? null);

        $clone->text_matching_method = is_string($carry[self::KEY_TEXT_MATCHING_METHOD] ?? null)
            ? TextMatchingOptions::tryFrom($carry[self::KEY_TEXT_MATCHING_METHOD])
            : $this->getTextMatchingMethod();

        $clone->min_autocomplete = $refinery->byTrying([
            $refinery->kindlyTo()->int(),
            $refinery->always($this->getMinAutocomplete())
        ])->transform($carry[self::KEY_MIN_AUTOCOMPLETE] ?? null);

        $clone->shuffle_answer_options = $refinery->byTrying([
            $refinery->kindlyTo()->bool(),
            $refinery->always($this->getShuffleAnswerOptions())
        ])->transform($carry[self::KEY_SHUFFLE_ANSWER_OPTIONS] ?? null);

        $clone->answer_options = $this->answer_options
            ->withValuesFromCarry($carry[self::KEY_ANSWER_OPTIONS]);

        return $clone;
    }

    public function buildReplace(
        ?Replace $replace,
        TableDefinitions $table_definitions,
        PersistenceFactory $persistence_factory,
        TableNameBuilder $table_name_builder
    ): Replace {
        if ($this->type === null) {
            throw new \UnexpectedValueException(
                'A Gap without Type cannot be stored.'
            );
        }

        $table_type = AnswerFormSpecificTableTypes::AnswerInputs;

        if ($replace === null) {
            return $persistence_factory->replace(
                $table_definitions->getColumns(
                    $table_name_builder,
                    $table_type
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

    private function getShortenedAnswerInputId(): string
    {
        return mb_substr($this->answer_input_id->toString(), 0, 4);
    }
}
