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

namespace ILIAS\Questions\Question;

use ILIAS\Questions\AnswerForm\Properties as AnswerFormProperties;
use ILIAS\Questions\Persistence\CoreTables;
use ILIAS\Questions\Persistence\Column;
use ILIAS\Questions\Persistence\Delete;
use ILIAS\Questions\Persistence\Insert;
use ILIAS\Questions\Persistence\Update;
use ILIAS\Questions\Persistence\Manipulate;
use ILIAS\Questions\Persistence\ManipulationType;
use ILIAS\Questions\Persistence\Value;
use ILIAS\Questions\Persistence\Where;
use ILIAS\Questions\Presentation\Definitions\EnvironmentImplementation;
use ILIAS\Questions\Question\Definitions\Lifecycle;
use ILIAS\Data\UUID\Uuid;
use ILIAS\Language\Language;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Component\Link\Factory as LinkFactory;
use ILIAS\UI\Component\Link\Standard as StandardLink;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\UI\Component\Table\DataRow;
use ILIAS\Refinery\Factory as Refinery;
use Psr\Http\Message\RequestInterface;

class QuestionImplementation implements Question
{
    private bool $linking_information_updated = false;
    private bool $self_updated = false;
    private bool $page_id_updated = false;
    private array $updated_answer_forms = [];
    private array $deleted_answer_forms = [];

    private array $answer_forms;

    /**
     * @param array{string, \ILIAS\Questions\AnswerForm\Properties} $answer_forms
     */
    public function __construct(
        private readonly Uuid $id,
        private int $parent_obj_id,
        private ?int $position = null,
        private ?int $page_id = null,
        private string $title = '',
        private string $author = '',
        private Lifecycle $lifecycle = Lifecycle::Draft,
        private string $remarks = '',
        private ?Uuid $original_id = null,
        private ?\DateTimeImmutable $last_update = null,
        private readonly ?\DateTimeImmutable $created = null,
        array $answer_forms = [],
        private ?Taxonomies $taxonomies = null,
        private ?ContentForRecapitulation $content_for_recapitulation = null
    ) {
        $this->answer_forms = array_reduce(
            $answer_forms,
            function (array $c, AnswerFormProperties $v): array {
                $c[$v->getAnswerFormId()->toString()] = $v;
                return $c;
            },
            []
        );
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getParentObjId(): int
    {
        return $this->parent_obj_id;
    }

    public function withParentObjId(
        int $parent_obj_id
    ): self {
        $clone = clone $this;
        $clone->parent_obj_id = $parent_obj_id;
        $clone->linking_information_updated = true;
        return $clone;
    }

    public function withPosition(
        int $position
    ): self {
        $clone = clone $this;
        $clone->position = $position;
        $clone->linking_information_updated = true;
        return $clone;
    }

    public function getPageId(): ?int
    {
        return $this->page_id;
    }

    public function withPageId(
        int $page_id
    ): self {
        $clone = clone $this;
        $clone->page_id = $page_id;
        $clone->page_id_updated = true;
        return $clone;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function withTitle(
        string $title
    ): self {
        $clone = clone $this;
        $clone->title = $title;
        $clone->self_updated = true;
        return $clone;
    }

    public function getAuthor(): string
    {
        return $this->author;
    }

    public function withAuthor(
        string $author
    ): self {
        $clone = clone $this;
        $clone->author = $author;
        $clone->self_updated = true;
        return $clone;
    }

    public function getLifecycle(): Lifecycle
    {
        return $this->lifecycle;
    }

    public function withLifecycle(
        Lifecycle $lifecycle
    ): self {
        $clone = clone $this;
        $clone->lifecycle = $lifecycle;
        $clone->self_updated = true;
        return $clone;
    }

    public function getRemarks(): string
    {
        return $this->remarks;
    }

    public function withRemarks(
        string $remarks
    ): self {
        $clone = clone $this;
        $clone->remarks = $remarks;
        $clone->self_updated = true;
        return $clone;
    }

    public function getOriginalId(): ?Uuid
    {
        return $this->original_id;
    }

    public function withOriginalId(
        Uuid $original_id
    ): self {
        $clone = clone $this;
        $clone->original_id = $original_id;
        $clone->self_updated = true;
        return $clone;
    }

    public function getLastUpdate(): ?\DateTimeImmutable
    {
        return $this->last_update;
    }

    public function getCreated(): ?\DateTimeImmutable
    {
        return $this->created;
    }

    public function getAnswerFormProperties(): array
    {
        return $this->answer_forms;
    }

    public function getAnswerFormPropertiesByIdString(
        string $form_id
    ): ?AnswerFormProperties {
        return $this->answer_forms[$form_id] ?? null;
    }

    public function withAnswerFormProperties(
        AnswerFormProperties $answer_form
    ): self {
        $clone = clone $this;
        $clone->answer_forms[$answer_form->getAnswerFormId()->toString()] = $answer_form;
        $clone->updated_answer_forms[] = $answer_form;
        return $clone;
    }

    public function withoutDeletedAnswerForms(
        array $found_answer_form_ids
    ): self {
        $clone = clone $this;
        foreach (array_keys($this->answer_forms) as $answer_form_id) {
            if (!in_array($answer_form_id, $found_answer_form_ids)) {
                $clone->deleted_answer_forms[] = $clone->answer_forms[$answer_form_id];
                unset($clone->answer_forms[$answer_form_id]);
            }
        }

        return $clone;
    }

    /**
     * Checks whether the question is a clone of another question or not
     */
    public function isClone(): bool
    {
        return $this->original_id !== null;
    }

    public function getEditView(
        Language $lng,
        \ilObjUser $current_user,
        UIFactory $ui_factory,
        Refinery $refinery,
        RequestInterface $request,
        \ilCtrl $ctrl
    ): Views\Edit {
        return new Views\Edit(
            $lng,
            $current_user,
            $ui_factory,
            $refinery,
            $request,
            $ctrl,
            $this
        );
    }

    #[\Override]
    public function getParticipantView(): Views\Participant
    {
        return new Views\Participant(
            $this
        );
    }

    public function toEditLink(
        LinkFactory $link_factory,
        EnvironmentImplementation $environment
    ): StandardLink {
        return $link_factory->standard(
            $this->title,
            $environment->withQuestionIdParameter($this->id)
                ->getUrlBuilder()
                ->buildURI()
                ->__toString()
        );
    }

    public function toTableRow(
        DataRowBuilder $row_builder,
        UIFactory $ui_factory,
        EnvironmentImplementation $environment
    ): DataRow {
        return $row_builder->buildDataRow(
            $this->id->toString(),
            [
                'title' => $ui_factory->link()->standard(
                    $this->title,
                    $environment->withQuestionIdParameter(
                        $this->id
                    )->getUrlBuilder()
                    ->buildURI()
                    ->__toString()
                )
            ]
        );
    }

    public function toStorage(
        Manipulate $manipulate
    ): Manipulate {
        return $manipulate->getManipulationType() === ManipulationType::Create
            ? $this->addInsertStatementsToManipulation($manipulate)
            : $this->addUpdateStatementsToManipulation($manipulate);
    }

    public function toDelete(
        Manipulate $manipulate
    ): Manipulate {
        return $this->addDeleteAnswerFormsStatementsToManipulate(
            $manipulate->withAdditionalStatement(
                $this->buildDeleteQuestionStatement()
            )->withAdditionalStatement(
                $this->buildDeleteLinkingStatement()
            )->withAdditionalStatement(
                $this->buildDeleteMigrationStatement()
            ),
            $this->answer_forms
        );
    }

    private function addInsertStatementsToManipulation(
        Manipulate $manipulate
    ): Manipulate {
        if ($this->created === null) {
            $manipulate = $manipulate
                ->withAdditionalStatement(
                    $this->buildInsertLinkingStatement()
                )->withAdditionalStatement(
                    $this->buildInsertQuestionStatement()
                );
        }

        if ($this->updated_answer_forms !== []) {
            return $this->addAnswerFormStatementsToManipulate(
                $manipulate,
                $this->updated_answer_forms
            );
        }

        if ($this->answer_forms !== []) {
            return $this->addAnswerFormStatementsToManipulate(
                $manipulate,
                $this->answer_forms
            );
        }

        return $manipulate;
    }

    private function addUpdateStatementsToManipulation(
        Manipulate $manipulate
    ): Manipulate {
        if ($this->linking_information_updated) {
            $manipulate = $manipulate
                ->withAdditionalStatement(
                    $this->buildUpdateLinkingStatement()
                );
        }

        if ($this->self_updated) {
            $manipulate = $manipulate->withAdditionalStatement(
                $this->buildUpdateQuestionStatement()
            );
        }

        if ($this->page_id) {
            $manipulate = $manipulate->withAdditionalStatement(
                $this->buildUpdatePageIdStatement()
            );
        }

        if ($this->deleted_answer_forms !== []) {
            $manipulate = $this->addDeleteAnswerFormsStatementsToManipulate(
                $manipulate,
                $this->deleted_answer_forms
            );
        }

        return $this->addAnswerFormStatementsToManipulate(
            $manipulate,
            $this->updated_answer_forms
        );
    }

    private function addAnswerFormStatementsToManipulate(
        Manipulate $manipulate,
        array $answer_forms
    ): Manipulate {
        return array_reduce(
            $answer_forms,
            fn(Manipulate $c, AnswerFormProperties $v): Manipulate => $v->toStorage(
                $v->getTypeGenericProperties()->toStorage($c)
            ),
            $manipulate
        );
    }

    private function addDeleteAnswerFormsStatementsToManipulate(
        Manipulate $manipulate,
        array $answer_forms_to_delete
    ): Manipulate {
        return array_reduce(
            $answer_forms_to_delete,
            fn(Manipulate $c, AnswerFormProperties $v): Manipulate => $v->toDelete(
                $v->getTypeGenericProperties()->toDelete($c)
            ),
            $manipulate
        );
    }



    private function buildInsertLinkingStatement(): Insert
    {
        return new Insert(
            CoreTables::Linking->getColumns(),
            [
                new Value(\ilDBConstants::T_TEXT, $this->id->toString()),
                new Value(\ilDBConstants::T_INTEGER, $this->parent_obj_id),
                new Value(\ilDBConstants::T_INTEGER, $this->position)
            ]
        );
    }

    private function buildInsertQuestionStatement(): Insert
    {
        return new Insert(
            CoreTables::Questions->getColumns(),
            [
                new Value(\ilDBConstants::T_TEXT, $this->id->toString()),
                new Value(\ilDBConstants::T_INTEGER, $this->page_id),
                new Value(\ilDBConstants::T_TEXT, $this->title),
                new Value(\ilDBConstants::T_TEXT, $this->author),
                new Value(\ilDBConstants::T_TEXT, $this->lifecycle->value),
                new Value(\ilDBConstants::T_TEXT, $this->remarks),
                new Value(\ilDBConstants::T_TEXT, $this->original_id?->toString()),
                new Value(\ilDBConstants::T_INTEGER, time()),
                new Value(\ilDBConstants::T_INTEGER, time())
            ]
        );
    }

    private function buildUpdateLinkingStatement(): Update
    {
        $linking_table_definition = CoreTables::Linking;
        return new Update(
            $linking_table_definition->getColumns(
                [CoreTables::LINKING_TABLE_ID_COLUMN]
            ),
            [
                new Value(\ilDBConstants::T_INTEGER, $this->parent_obj_id),
                new Value(\ilDBConstants::T_INTEGER, $this->position)
            ],
            [
                new Where(
                    $linking_table_definition->getIdColumn(),
                    new Value(\ilDBConstants::T_TEXT, $this->id->toString())
                )
            ]
        );
    }

    private function buildUpdateQuestionStatement(): Update
    {
        $questions_table_definition = CoreTables::Questions;
        return new Update(
            $questions_table_definition->getColumns([
                CoreTables::ANSWER_FORM_TABLE_ID_COLUMN,
                'page_id',
                'created'
            ]),
            [
                new Value(\ilDBConstants::T_TEXT, $this->title),
                new Value(\ilDBConstants::T_TEXT, $this->author),
                new Value(\ilDBConstants::T_TEXT, $this->lifecycle->value),
                new Value(\ilDBConstants::T_TEXT, $this->remarks),
                new Value(\ilDBConstants::T_TEXT, $this->original_id?->toString()),
                new Value(\ilDBConstants::T_INTEGER, time())
            ],
            [
                new Where(
                    $questions_table_definition->getIdColumn(),
                    new Value(\ilDBConstants::T_TEXT, $this->id->toString())
                )
            ]
        );
    }

    private function buildDeleteQuestionStatement(): Delete
    {
        $table_definition = CoreTables::Questions;
        return new Delete(
            $table_definition->getTable(),
            [
                new Where(
                    $table_definition->getIdColumn(),
                    new Value(
                        \ilDBConstants::T_TEXT,
                        $this->id->toString()
                    )
                )
            ]
        );
    }

    private function buildDeleteLinkingStatement(): Delete
    {
        $table_definition = CoreTables::Linking;
        return new Delete(
            $table_definition->getTable(),
            [
                new Where(
                    $table_definition->getIdColumn(),
                    new Value(
                        \ilDBConstants::T_TEXT,
                        $this->id->toString()
                    )
                )
            ]
        );
    }

    /**
     * skergomard, 2026-01-86: This we only need while the migrations exist, after
     * this MUST go!
     */
    private function buildDeleteMigrationStatement(): Delete
    {
        $table_definition = CoreTables::MigrationsTable;
        return new Delete(
            $table_definition->getTable(),
            [
                new Where(
                    $table_definition->getIdColumn(),
                    new Value(
                        \ilDBConstants::T_TEXT,
                        $this->id->toString()
                    )
                )
            ]
        );
    }

    /**
     * skergomard, 2026-01-26: This we only need while the migrations exist, after
     * this a question MUST never change the page assigned to it after its creation!
     */
    private function buildUpdatePageIdStatement(): Update
    {
        $questions_table_definition = CoreTables::Questions;
        return new Update(
            [
                new Column(
                    $questions_table_definition->getTable(),
                    'page_id'
                ),
                new Column(
                    $questions_table_definition->getTable(),
                    'last_update'
                )
            ],
            [
                new Value(\ilDBConstants::T_TEXT, $this->page_id),
                new Value(\ilDBConstants::T_INTEGER, time())
            ],
            [
                new Where(
                    $questions_table_definition->getIdColumn(),
                    new Value(\ilDBConstants::T_TEXT, $this->id->toString())
                )
            ]
        );
    }
}
