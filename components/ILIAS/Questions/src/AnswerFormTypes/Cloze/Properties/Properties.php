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

use ILIAS\Questions\AnswerForm\Persistence;
use ILIAS\Questions\AnswerForm\Properties as PropertiesInterface;
use ILIAS\Questions\AnswerForm\TypeGenericProperties;
use ILIAS\Questions\AnswerFormTypes\Cloze\Definition;
use ILIAS\Questions\AnswerFormTypes\Cloze\Layout\OverviewTable;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\ClozeText\Text;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\ClozeText\Factory as ClozeTextFactory;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Combinations\Combinations;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Definitions\ScoringIdentical;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Gaps\Gaps;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Gaps\Factory as GapsFactory;
use ILIAS\Questions\Persistence\Delete;
use ILIAS\Questions\Persistence\Insert;
use ILIAS\Questions\Persistence\Update;
use ILIAS\Questions\Persistence\Manipulate;
use ILIAS\Questions\Persistence\ManipulationType;
use ILIAS\Questions\Persistence\TableNameBuilder;
use ILIAS\Questions\Persistence\TableTypes;
use ILIAS\Questions\Persistence\Value;
use ILIAS\Questions\Persistence\Where;
use ILIAS\Questions\Presentation\Definitions\Environment;
use ILIAS\Questions\Presentation\Definitions\CarryWrapper;
use ILIAS\Data\UUID\Uuid;
use ILIAS\Language\Language;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Component\Input\Field\Section;
use ILIAS\UI\Component\Input\Field\Group;
use ILIAS\UI\Component\Table\Factory as TableFactory;
use ILIAS\UI\Component\Table\Data as DataTable;
use Psr\Http\Message\ServerRequestInterface;

class Properties implements PropertiesInterface
{
    private const string FORM_KEY_ID = 'form_id';
    private const string FORM_KEY_CLOZE_TEXT = 'cloze_text';
    private const string FORM_KEY_IDENTICAL_SCORING = 'identical_scoring';
    private const string FORM_KEY_ENABLE_COMBINATIONS = 'enable_combinations';
    private const string FORM_KEY_GAPS_TO_EDIT = 'gaps';

    private bool $updated_combinations = false;

    /**
     * @param array<string, \ILIAS\Questions\AnswerFormTypes\Cloze\Gap> $gaps
     */
    public function __construct(
        private readonly Uuid $answer_form_id,
        private readonly Uuid $question_id,
        private readonly Definition $definition,
        private Text $cloze_text,
        private readonly string $legacy_cloze_text,
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
            null,
            null,
            null,
            $this->cloze_text->getRawRepresentationForPersistence(),
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
        return $this->cloze_text->getRawRepresentationForPersistence() === ''
            ? $this->legacy_cloze_text
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
        return $clone;
    }

    #[\Override]
    public function getBasicPropertiesForListing(
        Language $lng
    ): array {
        return [
            $lng->txt('cloze_text') => $this->cloze_text
                ->getRenderedMarkdownForEditingPresentation($this->gaps),
            $lng->txt('score_identical') => $this->scoring_identical
                ->getTranslatedOptionName($lng),
            $lng->txt('gap_combinations') => $this->combinations->areCombinationsEnabled()
                ? $lng->txt('enabled')
                : $lng->txt('disabled')
        ];
    }

    #[\Override]
    public function getOverviewTable(
        TableFactory $table_factory,
        Language $lng,
        ServerRequestInterface $request,
        Environment $environment
    ): DataTable {
        return new OverviewTable(
            $table_factory,
            $lng,
            $request,
            $environment
        )->getTable();
    }

    public function buildBasicEditingInputs(
        Language $lng,
        FieldFactory $ff,
        Refinery $refinery,
        Factory $propteries_factory,
        ClozeTextFactory $cloze_text_factory,
        bool $add_legacy_cloze_text_to_input
    ): Section {
        $cloze_text_input = $this->getClozeText()->getInput(
            $lng,
            $ff,
            $cloze_text_factory
        );

        if ($add_legacy_cloze_text_to_input) {
            $cloze_text_input = $cloze_text_input->withValue(
                strip_tags($this->legacy_cloze_text)
            );
        }

        return $ff->section(
            [
                self::FORM_KEY_CLOZE_TEXT => $cloze_text_input,
                self::FORM_KEY_IDENTICAL_SCORING => ScoringIdentical::buildInput(
                    $lng,
                    $ff,
                    $refinery,
                    $this->scoring_identical
                )->withValue($this->getScoringOfIdenticalResponses()->value),
                self::FORM_KEY_ENABLE_COMBINATIONS => $ff->checkbox($lng->txt('cloze_enable_combinations'))
                    ->withValue($this->combinations->areCombinationsEnabled())
            ],
            $lng->txt('create_answer_form')
        )->withAdditionalTransformation(
            $refinery->custom()->transformation(
                fn(array $vs): self => $propteries_factory->fromForm(
                    $this,
                    $vs[self::FORM_KEY_CLOZE_TEXT],
                    $vs[self::FORM_KEY_IDENTICAL_SCORING],
                    $vs[self::FORM_KEY_ENABLE_COMBINATIONS]
                )
            )
        );
    }

    public function buildCarryInputs(
        FieldFactory $ff
    ): Group {
        return $ff->group(
            [
                self::FORM_KEY_ID => $ff->hidden()->withValue($this->answer_form_id->toString())
                    ->withDedicatedName(self::FORM_KEY_ID),
                self::FORM_KEY_CLOZE_TEXT => $this->getClozeText()->getCarryInputs($ff)
                    ->withDedicatedName(self::FORM_KEY_CLOZE_TEXT),
                self::FORM_KEY_GAPS_TO_EDIT => $this->gaps->getCarryInputs($ff)
                    ->withDedicatedName(self::FORM_KEY_GAPS_TO_EDIT),
                self::FORM_KEY_IDENTICAL_SCORING => $ff->hidden()
                    ->withDedicatedName(self::FORM_KEY_IDENTICAL_SCORING)
                    ->withValue($this->getScoringOfIdenticalResponses()->value),
                self::FORM_KEY_ENABLE_COMBINATIONS => $ff->hidden()
                    ->withDedicatedName(self::FORM_KEY_ENABLE_COMBINATIONS)
                    ->withValue($this->combinations->areCombinationsEnabled() ? 1 : 0)
            ]
        );
    }

    public function withValuesFromCarry(
        Refinery $refinery,
        ClozeTextFactory $cloze_text_factory,
        GapsFactory $gaps_factory,
        CarryWrapper $carry
    ): Properties {
        $clone = clone $this;
        $clone->cloze_text = $carry->retrieve(
            self::FORM_KEY_CLOZE_TEXT,
            $refinery->byTrying([
                $refinery->custom()->transformation(
                    fn(?string $v): Text => $v === null
                        ? throw new \InvalidArgumentException()
                        : $cloze_text_factory->buildFromHiddenInputString($v)
                ),
                $refinery->always($clone->cloze_text)
            ])
        );

        $clone->scoring_identical = $carry->retrieve(
            self::FORM_KEY_IDENTICAL_SCORING,
            $refinery->byTrying([
                $refinery->custom()->transformation(
                    fn(?string $v): ScoringIdentical => $v === null
                        ? throw new \InvalidArgumentException()
                        : ScoringIdentical::tryFrom($v) ?? $clone->scoring_identical
                ),
                $refinery->always($clone->scoring_identical)
            ])
        );

        $clone->combinations = $carry->retrieve(
            self::FORM_KEY_ENABLE_COMBINATIONS,
            $refinery->byTrying([
                $refinery->custom()->transformation(
                    fn($v): Combinations => $clone->combinations->withCombinationsEnabled(
                        $refinery->kindlyTo()->bool()->transform($v)
                    )
                ),
                $refinery->always($clone->combinations)
            ])
        );

        $clone->gaps = $carry->retrieve(
            self::FORM_KEY_GAPS_TO_EDIT,
            $clone->cloze_text->updateGapsFromMarkdown(
                $this->getAnswerFormId(),
                $this->getGaps()
            )->getFromCarryTransformation(
                $refinery,
                $gaps_factory
            )
        );

        return $clone;
    }

    #[\Override]
    public function toStorage(
        Manipulate $manipulate
    ): Manipulate {
        $persistence = $manipulate->getPersistenceForDefinitionClass(
            $this->definition::class
        );

        $table_name_builder = $manipulate->getTableNameBuilder(
            $this->definition::class
        );

        $answer_form_statement = $manipulate->getManipulationType() === ManipulationType::Create
            ? $this->buildInsertAnswerFormStatement(
                $persistence,
                $table_name_builder
            ) : $this->buildUpdateAnswerFormStatement(
                $persistence,
                $table_name_builder
            );

        return $this->gaps->toStorage(
            $this->addReplaceCombinationsStatements(
                $manipulate,
                $persistence,
                $table_name_builder
            )->withAdditionalStatement(
                $answer_form_statement
            ),
            $persistence,
            $table_name_builder
        );
    }

    #[\Override]
    public function toDelete(
        Manipulate $manipulate
    ): Manipulate {
        $persistence = $manipulate->getPersistenceForDefinitionClass(
            $this->definition::class
        );

        $table_name_builder = $manipulate->getTableNameBuilder(
            $this->definition::class
        );

        return $this->gaps->toDelete(
            $manipulate->withAdditionalStatement(
                $this->buildDeleteAnswerFormStatement(
                    $persistence,
                    $table_name_builder
                )
            ),
            $persistence,
            $table_name_builder
        );
    }

    private function buildInsertAnswerFormStatement(
        Persistence $persistence,
        TableNameBuilder $table_name_builder
    ): Insert {
        $table_definition = TableTypes::TypeSpecificAnswerForms;
        return new Insert(
            $persistence->getColumns(
                $table_name_builder,
                $table_definition
            ),
            [
                new Value(\ilDBConstants::T_TEXT, $this->answer_form_id->toString()),
                new Value(\ilDBConstants::T_TEXT, $this->scoring_identical->value),
                new Value(
                    \ilDBConstants::T_INTEGER,
                    $this->combinations->areCombinationsEnabled() ? 1 : 0
                )
            ]
        );
    }

    private function buildUpdateAnswerFormStatement(
        Persistence $persistence,
        TableNameBuilder $table_name_builder
    ): Update {
        $table_definition = TableTypes::TypeSpecificAnswerForms;
        return new Update(
            $persistence->getColumns(
                $table_name_builder,
                $table_definition,
                '',
                ['answer_form_id']
            ),
            [
                new Value(\ilDBConstants::T_TEXT, $this->scoring_identical->value),
                new Value(
                    \ilDBConstants::T_INTEGER,
                    $this->combinations->areCombinationsEnabled() ? 1 : 0
                )
            ],
            [
                new Where(
                    $persistence->getIdColumn(
                        $table_name_builder,
                        $table_definition
                    ),
                    new Value(\ilDBConstants::T_TEXT, $this->answer_form_id->toString())
                )
            ]
        );
    }

    private function addReplaceCombinationsStatements(
        Manipulate $manipulate,
        Persistence $persistence,
        TableNameBuilder $table_name_builder
    ): Manipulate {
        if (!$this->combinations->areCombinationsEnabled()
            || !$this->updated_combinations) {
            return $manipulate;
        }

        return $this->combinations->toStorage(
            $manipulate,
            $persistence,
            $table_name_builder
        );
    }

    private function buildDeleteAnswerFormStatement(
        Persistence $persistence,
        TableNameBuilder $table_name_builder
    ): Delete {
        $table_definition = TableTypes::TypeSpecificAnswerForms;

        return new Delete(
            $table_definition->getTable($table_name_builder),
            [
                new Where(
                    $persistence->getForeignKeyColumn(
                        $table_name_builder,
                        $table_definition
                    ),
                    new Value(
                        \ilDBConstants::T_TEXT,
                        $this->answer_form_id->toString()
                    )
                )
            ]
        );
    }
}
