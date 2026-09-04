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

namespace ILIAS\Questions\AnswerFormTypes\Cloze\Properties;

use ILIAS\Questions\AnswerForm\Persistence\AnswerFormSpecificTableTypes;
use ILIAS\Questions\AnswerForm\Properties as PropertiesInterface;
use ILIAS\Questions\AnswerForm\TypeGenericProperties;
use ILIAS\Questions\AnswerFormTypes\Cloze\Definition;
use ILIAS\Questions\AnswerFormTypes\Cloze\Layout\OverviewTable;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\ClozeText\Text;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\ClozeText\Factory as ClozeTextFactory;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Combinations\Combinations;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Definitions\ScoringIdentical;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Gaps\Gap;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Gaps\Gaps;
use ILIAS\Questions\AnswerFormTypes\Cloze\TableDefinitions;
use ILIAS\Questions\Persistence\Delete;
use ILIAS\Questions\Persistence\Factory as PersistenceFactory;
use ILIAS\Questions\Persistence\Insert;
use ILIAS\Questions\Persistence\Update;
use ILIAS\Questions\Persistence\Manipulate;
use ILIAS\Questions\Persistence\ManipulationType;
use ILIAS\Questions\Persistence\TableNameBuilder;
use ILIAS\Questions\Presentation\Definitions\Environment;
use ILIAS\Questions\Presentation\Layout\EditOverview;
use ILIAS\Data\UUID\Factory as UuidFactory;
use ILIAS\Data\UUID\Uuid;
use ILIAS\Database\FieldDefinition;
use ILIAS\UI\Component\Input\Field\Section;

class Properties implements PropertiesInterface
{
    private const string KEY_CLOZE_TEXT = 'cloze_text';
    private const string KEY_SCORING_IDENTICAL = 'identical_scoring';
    private const string KEY_ENABLE_COMBINATIONS = 'enable_combinations';
    private const string KEY_GAPS = 'gaps';

    private bool $updated_gaps = false;
    private bool $updated_combinations = false;

    /**
     * @param array<string, \ILIAS\Questions\AnswerFormTypes\Cloze\Gap> $gaps
     */
    public function __construct(
        private Uuid $answer_form_id,
        private Uuid $question_id,
        private readonly Definition $definition,
        private readonly ?float $available_points,
        private Text $cloze_text,
        private string $legacy_cloze_text,
        private ScoringIdentical $scoring_identical,
        private Gaps $gaps,
        private Combinations $combinations
    ) {
    }

    #[\Override]
    public function getAnswerFormId(): Uuid
    {
        return $this->answer_form_id;
    }

    #[\Override]
    public function getQuestionId(): Uuid
    {
        return $this->question_id;
    }

    #[\Override]
    public function getDefinition(): ?Definition
    {
        return $this->definition;
    }

    #[\Override]
    public function getTypeGenericProperties(): TypeGenericProperties
    {
        return new TypeGenericProperties(
            $this->answer_form_id,
            $this->question_id,
            $this->definition,
            $this->buildAvailablePointsForGenericProperties(),
            null,
            null,
            $this->cloze_text->getRawRepresentation(),
            $this->legacy_cloze_text
        );
    }

    public function getClozeText(): Text
    {
        return $this->cloze_text;
    }

    public function withClozeText(
        Text $cloze_text
    ): self {
        $clone = clone $this;
        $clone->cloze_text = $cloze_text;
        return $clone;
    }

    public function getLegacyClozeText(): string
    {
        return $this->legacy_cloze_text;
    }

    public function getClozeTextForPresentation(): string
    {
        return $this->cloze_text->getRawRepresentation() === ''
            ? \ilRTE::_replaceMediaObjectImageSrc($this->legacy_cloze_text, 0)
            : $this->cloze_text->getRenderedMarkdownForParticipantPresentation();
    }

    public function getScoringOfIdenticalResponses(): ScoringIdentical
    {
        return $this->scoring_identical;
    }

    public function withScoringOfIdenticalResponses(
        ScoringIdentical $scoring_identical
    ): self {
        $clone = clone $this;
        $clone->scoring_identical = $scoring_identical;
        return $clone;
    }

    public function getCombinations(): Combinations
    {
        return $this->combinations;
    }

    public function withCombinations(
        Combinations $combinations
    ): self {
        $clone = clone $this;
        $clone->combinations = $combinations;
        $clone->updated_combinations = true;
        return $clone;
    }

    public function getGaps(): Gaps
    {
        return $this->gaps;
    }

    public function withGaps(
        Gaps $gaps
    ): self {
        $clone = clone $this;
        $clone->gaps = $gaps;
        $clone->updated_gaps = true;
        return $clone;
    }

    #[\Override]
    public function getBasicPropertiesForListing(
        Environment $environment
    ): array {
        $lng = $environment->getLanguage();

        $listing = [
            $lng->txt('cloze_text') => $this->cloze_text
                ->getRenderedMarkdownForEditingPresentation(
                    $this->gaps,
                    $this->getLegacyClozeText()
                )
        ];

        if ($environment->isMarkingRequired()) {
            $listing[$lng->txt('scoring_of_identical_responses')] = $this
                ->scoring_identical
                ->getTranslatedOptionName($lng);
        }

        return [
            ...$listing,
            $lng->txt('gap_combinations') => $this->combinations->areCombinationsEnabled()
                ? $lng->txt('enabled')
                : $lng->txt('disabled')
        ];
    }

    #[\Override]
    public function completeEditOverview(
        Environment $environment,
        EditOverview $edit_overview
    ): EditOverview {
        return $edit_overview->withTable(
            new OverviewTable(
                $environment
            )->getTable()
        );
    }

    public function buildBasicEditingInputs(
        Environment $environment,
        Factory $properties_factory,
        ClozeTextFactory $cloze_text_factory,
        bool $add_legacy_cloze_text_to_input
    ): Section {
        $lng = $environment->getLanguage();
        $ff = $environment->getUIFactory()->input()->field();
        $refinery = $environment->getRefinery();

        $cloze_text_input = $this->cloze_text->getInput(
            $lng,
            $ff,
            $cloze_text_factory,
            $this->legacy_cloze_text === ''
                || $this->legacy_cloze_text !== ''
                    && $this->cloze_text->getRawRepresentation() !== ''
        );

        if ($add_legacy_cloze_text_to_input) {
            $cloze_text_input = $cloze_text_input->withValue(
                strip_tags($this->legacy_cloze_text)
            );
        }

        $inputs = [
            self::KEY_CLOZE_TEXT => $cloze_text_input,
            self::KEY_SCORING_IDENTICAL => ScoringIdentical::buildInput(
                $lng,
                $ff,
                $refinery,
                $this->scoring_identical
            )->withValue($this->getScoringOfIdenticalResponses()->value)
        ];

        if ($environment->isMarkingRequired()) {
            $inputs[self::KEY_ENABLE_COMBINATIONS] = $ff->checkbox(
                $lng->txt('cloze_enable_gap_combinations')
            )->withValue($this->combinations->areCombinationsEnabled());
        }

        return $ff->section(
            $inputs,
            $lng->txt('edit_basic_answer_form_properties')
        )->withAdditionalTransformation(
            $refinery->custom()->transformation(
                fn(array $vs): self => $properties_factory->fromBasicEditingForm(
                    $this,
                    $vs[self::KEY_CLOZE_TEXT],
                    $vs[self::KEY_SCORING_IDENTICAL],
                    $vs[self::KEY_ENABLE_COMBINATIONS] ?? false
                )
            )
        );
    }

    public function toCarry(): string
    {
        return json_encode([
            self::KEY_CLOZE_TEXT => $this->cloze_text->getRawRepresentation(),
            self::KEY_GAPS => $this->gaps->toCarry(),
            self::KEY_SCORING_IDENTICAL => $this->scoring_identical->value,
            self::KEY_ENABLE_COMBINATIONS => $this->combinations
                ->areCombinationsEnabled() ? 1 : 0
        ]);
    }

    public function withValuesFromCarry(
        ClozeTextFactory $cloze_text_factory,
        array $carry
    ): self {
        $clone = clone $this;

        $clone->cloze_text = $cloze_text_factory->buildFromTextString(
            $carry[self::KEY_CLOZE_TEXT]
        );
        $clone->gaps = $this->getGaps()->withValuesFromCarry(
            $carry[self::KEY_GAPS]
        );
        $clone->scoring_identical = ScoringIdentical::tryFrom(
            $carry[self::KEY_SCORING_IDENTICAL]
        );
        $clone->combinations = $this->combinations->withCombinationsEnabled(
            $carry[self::KEY_ENABLE_COMBINATIONS] === 1
        );
        return $clone;
    }

    #[\Override]
    public function clone(
        UuidFactory $uuid_factory,
        array $environment = []
    ): static {
        $clone = clone $this;
        $clone->answer_form_id = $environment['answer_form_id'];
        $clone->question_id = $environment['new_question_id'];
        $environment['gaps'] = $clone->gaps->clone($uuid_factory, $environment);

        $update_text_closure = $this->buildReplaceClonedGapsInTextsClosure(
            $environment['gaps']
        );
        $clone->cloze_text = $clone->cloze_text->withUpdateMarkdownAfterCloning(
            $update_text_closure
        );
        $clone->legacy_cloze_text = $update_text_closure($this->legacy_cloze_text);

        $clone->gaps = $environment['gaps'];
        $clone->combinations->clone($uuid_factory, $environment);
        return $clone;
    }

    #[\Override]
    public function toStorage(
        PersistenceFactory $persistence_factory,
        ManipulationType $manipulation_type,
        Manipulate $manipulate
    ): Manipulate {
        $table_definitions = $this->definition->getTableDefinitions();

        $table_names_builder = $manipulate->getTableNameBuilder(
            $table_definitions->getTableSubNameSpace()
        );

        $answer_form_statement = $manipulation_type === ManipulationType::Create
            ? $this->buildInsertAnswerFormStatement(
                $table_definitions,
                $persistence_factory,
                $table_names_builder
            ) : $this->buildUpdateAnswerFormStatement(
                $table_definitions,
                $persistence_factory,
                $table_names_builder
            );

        return $this->gaps->toStorage(
            $this->addReplaceCombinationsStatements(
                $manipulate,
                $table_definitions,
                $table_names_builder
            )->withAdditionalStatement(
                $answer_form_statement
            ),
            $persistence_factory,
            $table_definitions,
            $table_names_builder
        );
    }

    #[\Override]
    public function toDelete(
        PersistenceFactory $persistence_factory,
        Manipulate $manipulate
    ): Manipulate {
        $table_definitions = $this->definition->getTableDefinitions();
        $table_names_builder = $manipulate->getTableNameBuilder(
            $table_definitions->getTableSubNameSpace()
        );

        return $this->gaps->toDelete(
            $manipulate->withAdditionalStatement(
                $this->buildDeleteAnswerFormStatement(
                    $table_definitions,
                    $persistence_factory,
                    $table_names_builder
                )
            ),
            $persistence_factory,
            $table_definitions,
            $table_names_builder
        );
    }

    private function buildInsertAnswerFormStatement(
        TableDefinitions $table_definitions,
        PersistenceFactory $persistence_factory,
        TableNameBuilder $table_names_builder
    ): Insert {
        return $persistence_factory->insert(
            $table_definitions->getColumns(
                $table_names_builder,
                AnswerFormSpecificTableTypes::TypeSpecificAnswerForms
            ),
            [
                $persistence_factory->value(
                    FieldDefinition::T_TEXT,
                    $this->answer_form_id->toString()
                ),
                $persistence_factory->value(
                    FieldDefinition::T_TEXT,
                    $this->scoring_identical->value
                ),
                $persistence_factory->value(
                    FieldDefinition::T_INTEGER,
                    $this->combinations->areCombinationsEnabled() ? 1 : 0
                )
            ]
        );
    }

    private function buildUpdateAnswerFormStatement(
        TableDefinitions $table_definitions,
        PersistenceFactory $persistence_factory,
        TableNameBuilder $table_names_builder
    ): Update {
        $table_type = AnswerFormSpecificTableTypes::TypeSpecificAnswerForms;
        return $persistence_factory->update(
            $table_definitions->getColumns(
                $table_names_builder,
                $table_type,
                '',
                ['answer_form_id']
            ),
            [
                $persistence_factory->value(
                    FieldDefinition::T_TEXT,
                    $this->scoring_identical->value
                ),
                $persistence_factory->value(
                    FieldDefinition::T_INTEGER,
                    $this->combinations->areCombinationsEnabled() ? 1 : 0
                )
            ],
            [
                $persistence_factory->where(
                    $table_definitions->getIdColumn(
                        $table_names_builder,
                        $table_type
                    ),
                    $persistence_factory->value(
                        FieldDefinition::T_TEXT,
                        $this->answer_form_id->toString()
                    )
                )
            ]
        );
    }

    private function addReplaceCombinationsStatements(
        Manipulate $manipulate,
        TableDefinitions $table_definitions,
        TableNameBuilder $table_names_builder
    ): Manipulate {
        if (!$this->combinations->areCombinationsEnabled()
            || !$this->updated_combinations) {
            return $manipulate;
        }

        return $this->combinations->toStorage(
            $manipulate,
            $table_definitions,
            $table_names_builder
        );
    }

    private function buildDeleteAnswerFormStatement(
        TableDefinitions $table_definitions,
        PersistenceFactory $persistence_factory,
        TableNameBuilder $table_names_builder
    ): Delete {
        $table_type = AnswerFormSpecificTableTypes::TypeSpecificAnswerForms;

        return $persistence_factory->delete([
            $persistence_factory->where(
                $table_definitions->getForeignKeyColumn(
                    $table_names_builder,
                    $table_type
                ),
                $persistence_factory->value(
                    FieldDefinition::T_TEXT,
                    $this->answer_form_id->toString()
                )
            )
        ]);
    }

    private function buildAvailablePointsForGenericProperties(): ?float
    {
        if (!$this->updated_gaps && !$this->updated_combinations) {
            return $this->available_points;
        }

        if (!$this->combinations->areCombinationsEnabled()) {
            return $this->gaps->getTotalAvailablePoints();
        }

        return 1.1;
    }

    private function buildReplaceClonedGapsInTextsClosure(
        Gaps $new_gaps
    ): \Closure {
        return function ($text) use ($new_gaps): string {
            $position = 0;
            return mb_ereg_replace_callback(
                '{{' . Gap::GAP_PLACEHOLDER_NAME
                . '_[0-9a-fA-F]{8}\b-[0-9a-fA-F]{4}\b-[0-9a-fA-F]{4}\b-[0-9a-fA-F]{4}\b-[0-9a-fA-F]{12}}}',
                function ($matches) use ($new_gaps, &$position): string {
                    return  '{{' . Gap::GAP_PLACEHOLDER_NAME
                        . "_{$new_gaps->getGapByPosition($position++)->getAnswerInputId()->toString()}}}";
                },
                $text
            );
        };
    }
}
