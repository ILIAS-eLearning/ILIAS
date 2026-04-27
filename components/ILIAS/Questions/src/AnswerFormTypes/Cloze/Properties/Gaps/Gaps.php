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
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Properties;
use ILIAS\Questions\AnswerFormTypes\Cloze\TableDefinitions;
use ILIAS\Questions\Attempt\Attempt;
use ILIAS\Questions\Persistence\Delete;
use ILIAS\Questions\Persistence\Factory as PersistenceFactory;
use ILIAS\Questions\Persistence\Junctor;
use ILIAS\Questions\Persistence\Manipulate;
use ILIAS\Questions\Persistence\Operator;
use ILIAS\Questions\Persistence\TableNameBuilder;
use ILIAS\Questions\Presentation\Definitions\Environment;
use ILIAS\Data\UUID\Uuid;
use ILIAS\Database\FieldDefinition;
use ILIAS\FileUpload\FileUpload;
use ILIAS\Language\Language;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Random\Seed\RandomSeed;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Component\Input\Field\MultiSelect;
use ILIAS\UI\Component\Input\Field\Section;
use ILIAS\UI\Component\Input\Field\Select;
use ILIAS\UI\Component\Table\DataRowBuilder;

class Gaps
{
    /**
     * @var array<string, \ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Gaps\Gap>
     */
    private array $gaps;

    public function __construct(
        private readonly Refinery $refinery,
        private readonly Factory $factory,
        private Uuid $answer_form_id,
        array $gaps
    ) {
        $this->gaps = array_reduce(
            $gaps,
            function (array $c, Gap $v): array {
                $c[$v->getAnswerInputId()->toString()] = $v;
                return $c;
            },
            []
        );
    }

    public function getTotalAvailablePoints(): ?float
    {
        return array_reduce(
            $this->gaps,
            function (?float $c, Gap $v): ?float {
                $points_from_options = $v->getAnswerOptions()->getMaxAvailablePoints();

                if ($points_from_options === null) {
                    return $c;
                }

                if ($c === null) {
                    return $points_from_options;
                }

                return $c + $points_from_options;
            }
        );
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

    public function getNumberOfGaps(
    ): int {
        return count($this->gaps);
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

    public function getIncompleteGaps(): array
    {
        return array_filter(
            $this->gaps,
            fn(Gap $v): bool => $v->getAnswerOptions()->isIncomplete()
        );
    }

    public function withMarkedIncompleteGaps(): self
    {
        $clone = clone $this;
        $clone->gaps = array_map(
            fn(Gap $v): Gap => $v->getAnswerOptions()->isIncomplete()
                ? $v->withAnswerOptions(
                    $v->getAnswerOptions()->withIsIncomplete(true)
                ) : $v,
            $clone->gaps
        );
        return $clone;
    }

    public function getRemovedGaps(
        self $old_gaps
    ): array {
        return array_diff_key($old_gaps->gaps, $this->gaps);
    }

    public function getAddedGaps(
        self $old_gaps
    ): array {
        return array_diff_key($this->gaps, $old_gaps->gaps);
    }

    public function getPlaceholderArrayForParticipantView(
        ?Attempt $attempt_data
    ): array {
        return array_reduce(
            $this->gaps,
            function (array $c, Gap $v) use ($attempt_data): array {
                $c[$v->buildGapPlaceholderNameWithId($v)] = $v->buildParticipantViewLegacyInput(
                    $attempt_data
                );
                return $c;
            },
            []
        );
    }

    public function getPlaceholderArrayForEditFormPanel(): array
    {
        return array_reduce(
            $this->gaps,
            function (array $c, Gap $v): array {
                $c[$v->buildGapPlaceholderNameWithId($v)] = $v->buildShortenedGapRepresentation();
                return $c;
            },
            []
        );
    }

    public function buildGapsTypeInputs(
        Language $lng,
        FieldFactory $ff,
        array $available_gap_types,
        Properties $properties,
        bool $is_in_creation_context,
        array $selected_gaps
    ): Section {
        return $ff->section(
            array_reduce(
                $this->retrieveGapsForInputs(
                    $is_in_creation_context,
                    $selected_gaps
                ),
                function (array $c, Gap $v) use ($ff, $available_gap_types): array {
                    $c[$v->getAnswerInputId()->toString()] = $ff->select(
                        $v->buildShortenedGapName(),
                        $available_gap_types
                    )->withRequired(true)
                    ->withValue($v->getType()?->getIdentifier());
                    return $c;
                },
                []
            ),
            $lng->txt('select_gap_types')
        )->withAdditionalTransformation(
            $this->refinery->custom()->transformation(
                fn(array $vs): Properties => $properties->withGaps(
                    array_reduce(
                        array_keys($vs),
                        fn(self $c, string $v): self => $c->withGap(
                            $c->gaps[$v]->withType(
                                $this->factory->getGapTypeByIdentifier($vs[$v])
                            )
                        ),
                        $this
                    )
                )
            )
        );
    }

    public function buildAnswerOptionsInputs(
        FileUpload $file_upload,
        Environment $environment,
        Properties $properties,
    ): Section {

        return $environment->getUIFactory()->input()->field()->section(
            array_reduce(
                $this->retrieveGapsForInputs(
                    $environment->isInCreationContext(),
                    $environment->getTableRowIds()
                ),
                function (array $c, Gap $v) use ($environment, $file_upload): array {
                    $c[$v->getAnswerInputId()->toString()] = $v->getEditAnswerOptionsSection(
                        $file_upload,
                        $environment
                    );
                    return $c;
                },
                []
            ),
            $environment->getLanguage()->txt('add_answer_options')
        )->withAdditionalTransformation(
            $this->refinery->custom()->transformation(
                fn(array $vs): Properties => $properties->withGaps(
                    array_reduce(
                        array_keys($vs),
                        fn(self $c, string $v): self => $c->withGap($vs[$v]),
                        $this
                    )
                )
            )
        );
    }

    public function buildPointInputs(
        Language $lng,
        FieldFactory $ff,
        Properties $properties,
        bool $is_in_creation_context,
        array $selected_gaps
    ): Section {
        return $ff->section(
            array_reduce(
                $this->retrieveGapsForInputs(
                    $is_in_creation_context,
                    $selected_gaps
                ),
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
            $this->refinery->custom()->transformation(
                fn(array $vs): Properties => $properties->withGaps(
                    array_reduce(
                        array_keys($vs),
                        fn(self $c, string $v): self => $c->withGap($vs[$v]),
                        $this
                    )
                )
            )
        );
    }

    public function buildGapsSelect(
        string $label,
        FieldFactory $ff
    ): Select {
        return $ff->select(
            $label,
            $this->buildOptionsArray()
        );
    }

    public function buildGapsMultiSelect(
        string $label,
        FieldFactory $ff
    ): MultiSelect {
        return $ff->multiSelect(
            $label,
            $this->buildOptionsArray()
        );
    }

    public function toCarry(): array
    {
        return array_reduce(
            $this->gaps,
            function (array $c, Gap $v): array {
                $c[$v->getAnswerInputId()->toString()] = $v->toCarry();
                return $c;
            },
            []
        );
    }

    public function withValuesFromCarry(
        array $carry
    ): self {
        $clone = clone $this;
        foreach ($carry as $answer_input_id => $gap_definition) {
            if (!isset($clone->gaps[$answer_input_id])) {
                $clone->gaps[$answer_input_id] = $this->factory->getNewGap(
                    $this->answer_form_id,
                    0,
                    $answer_input_id
                );
            }

            $clone->gaps[$answer_input_id] = $clone->gaps[$answer_input_id]
                ->withValuesFromCarry(
                    $this->refinery,
                    $this->factory,
                    $gap_definition
                );
        }

        return $clone;
    }

    public function initializeAttemptData(
        Attempt $attempt
    ): Attempt {
        return array_reduce(
            $this->gaps,
            fn(Attempt $c, Gap $v): Attempt => $v->getShuffleAnswerOptions()
                ? $c->withAdditionalData(
                    $v->getAnswerInputId(),
                    $this->refinery->kindlyTo()->string()->transform(
                        (new RandomSeed())->createSeed()
                    )
                ) : $c,
            $attempt
        );
    }

    public function toTableRows(
        DataRowBuilder $row_builder,
        Language $lng
    ): \Generator {
        foreach ($this->orderGapsByPosition($this->gaps) as $gap) {
            yield $gap->toTableRow(
                $row_builder,
                $lng
            );
        }
    }

    public function toStorage(
        Manipulate $manipulate,
        PersistenceFactory $persistence_factory,
        TableDefinitions $table_definitions,
        TableNameBuilder $table_names_builder
    ): Manipulate {
        [
            'gaps' => $replace_for_gaps,
            'answer_options' => $replace_for_answer_options
        ] = array_reduce(
            $this->gaps,
            fn(array $c, Gap $v): array => [
                'gaps' => $v->buildReplace(
                    $c['gaps'],
                    $table_definitions,
                    $persistence_factory,
                    $table_names_builder
                ),
                'answer_options' => $v->getAnswerOptions()->buildReplace(
                    $c['answer_options'],
                    $table_definitions,
                    $persistence_factory,
                    $table_names_builder
                )
            ],
            [
                'gaps' => null,
                'answer_options' => null
            ]
        );

        return $manipulate->withAdditionalStatement(
            $this->buildDeleteForRemovedGaps(
                $table_definitions,
                $persistence_factory,
                $table_names_builder
            )
        )->withAdditionalStatement($replace_for_gaps)
        ->withAdditionalStatement($replace_for_answer_options);
    }

    public function toDelete(
        Manipulate $manipulate,
        PersistenceFactory $persistence_factory,
        TableDefinitions $table_definitions,
        TableNameBuilder $table_names_builder
    ): Manipulate {
        return array_reduce(
            $this->gaps,
            fn(Manipulate $c, Gap $v): Manipulate => $c->withAdditionalStatement(
                $v->getAnswerOptions()->buildDelete(
                    $table_definitions,
                    $persistence_factory,
                    $table_names_builder
                )
            ),
            $manipulate->withAdditionalStatement(
                $this->buildDeleteForDeletionOfAnswerForm(
                    $table_definitions,
                    $persistence_factory,
                    $table_names_builder
                )
            )
        );
    }

    private function buildDeleteForRemovedGaps(
        TableDefinitions $table_definitions,
        PersistenceFactory $persistence_factory,
        TableNameBuilder $table_names_builder
    ): Delete {
        $table_type = AnswerFormSpecificTableTypes::AnswerInputs;
        return $persistence_factory->delete(
            $persistence_factory->table(
                $table_names_builder,
                $table_type
            ),
            [
                $persistence_factory->where(
                    $table_definitions->getForeignKeyColumn(
                        $table_names_builder,
                        $table_type
                    ),
                    $persistence_factory->value(
                        FieldDefinition::T_TEXT,
                        $this->answer_form_id->toString()
                    )
                ),
                $persistence_factory->where(
                    $table_definitions->getIdColumn(
                        $table_names_builder,
                        $table_type
                    ),
                    $persistence_factory->value(
                        FieldDefinition::T_TEXT,
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
        );
    }

    private function buildDeleteForDeletionOfAnswerForm(
        TableDefinitions $table_definitions,
        PersistenceFactory $persistence_factory,
        TableNameBuilder $table_names_builder
    ): Delete {
        $table_type = AnswerFormSpecificTableTypes::AnswerInputs;

        return $persistence_factory->delete(
            $persistence_factory->table(
                $table_names_builder,
                $table_type
            ),
            [
                $persistence_factory->where(
                    $table_definitions->getForeignKeyColumn(
                        $table_names_builder,
                        $table_type
                    ),
                    $persistence_factory->value(
                        FieldDefinition::T_TEXT,
                        $this->answer_form_id->toString()
                    ),
                )
            ]
        );
    }

    private function orderGapsByPosition(
        array $gaps
    ): array {
        usort(
            $gaps,
            fn(Gap $a, Gap $b) => $a->getPosition() <=> $b->getPosition()
        );

        return $gaps;
    }

    private function extractIdFromTagName(
        string $tag_name
    ): string {
        return mb_substr($tag_name, mb_strlen(Gap::GAP_PLACEHOLDER_NAME) + 1);
    }

    private function buildOptionsArray(): array
    {
        return array_reduce(
            $this->gaps,
            function (array $c, Gap $v): array {
                $c[$v->getAnswerInputId()->toString()] = $v->buildShortenedGapName();
                return $c;
            },
            []
        );
    }

    private function retrieveGapsForInputs(
        bool $is_in_creation_context,
        array $selected_gaps
    ): array {
        if ($is_in_creation_context) {
            return $this->gaps;
        }

        if ($selected_gaps === []) {
            return $this->getIncompleteGaps();
        }

        return $this->filterGapsBySelected($selected_gaps);
    }

    private function filterGapsBySelected(
        array $selected_gaps
    ): array {
        return array_filter(
            $this->gaps,
            fn(string $k): bool => in_array($k, $selected_gaps),
            ARRAY_FILTER_USE_KEY
        );
    }
}
