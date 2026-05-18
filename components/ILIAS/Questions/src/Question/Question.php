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

use ILIAS\Questions\Administration\ConfigurationRepository;
use ILIAS\Questions\AnswerForm\Capabilities\MarkingAllowingPartialPoints\Capability as MarkingAllowingPartialPoints;
use ILIAS\Questions\AnswerForm\Capabilities\RequiredCapabilities;
use ILIAS\Questions\AnswerForm\Definition as AnswerFormDefinition;
use ILIAS\Questions\AnswerForm\Properties as AnswerFormProperties;
use ILIAS\Questions\AnswerForm\Response as AnswerFormResponse;
use ILIAS\Questions\Attempt\Attempt;
use ILIAS\Questions\Attempt\Repository as AttemptRepository;
use ILIAS\Questions\Attempt\Response;
use ILIAS\Questions\Persistence\Column;
use ILIAS\Questions\Persistence\Manipulate;
use ILIAS\Questions\Persistence\ManipulationType;
use ILIAS\Questions\Persistence\Query;
use ILIAS\Questions\Presentation\Definitions\DefaultEnvironment;
use ILIAS\Questions\Presentation\Definitions\OverviewTableColumns;
use ILIAS\Questions\Question\Definitions\Lifecycle;
use ILIAS\Questions\Question\Persistence\DatabaseStatementBuilder;
use ILIAS\Questions\UserSettings\CreateModes;
use ILIAS\Data\UUID\Factory as UuidFactory;
use ILIAS\Data\UUID\Uuid;
use ILIAS\HTTP\Wrapper\RequestWrapper;
use ILIAS\Language\Language;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Component\Link\Factory as LinkFactory;
use ILIAS\UI\Component\Link\Standard as StandardLink;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\UI\Component\Table\DataRow;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;

class Question
{
    private ?CreateModes $create_mode = null;
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
        array $answer_forms = []
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

    public function getCreateMode(): ?CreateModes
    {
        return $this->create_mode;
    }

    public function withCreateMode(
        CreateModes $create_mode
    ): self {
        $clone = clone $this;
        $clone->create_mode = $create_mode;
        return $clone;
    }

    public function getListOfContainedAnswerFormTypeLabels(
        Language $lng
    ): array {
        return array_map(
            fn(AnswerFormDefinition $v): string => $v->getLabel($lng),
            $this->getListOfContainedAnswerFormTypes()
        );
    }

    public function getEditView(
        \ilObjUser $current_user,
        \ilCtrl $ctrl,
        RequestWrapper $post_wrapper,
        UIRenderer $ui_renderer,
        UuidFactory $uuid_factory,
        ConfigurationRepository $configuration_repository,
        AttemptRepository $attempt_repository,
        RequiredCapabilities $required_capabilities
    ): Views\Edit {
        if (!$required_capabilities->areAllCapabilitiesSupportedByAnswerForms($this->answer_forms)) {
            throw new \UnexpectedValueException(
                "The Question does not support all required Capabilities."
            );
        }

        return new Views\Edit(
            $current_user,
            $ctrl,
            $post_wrapper,
            $ui_renderer,
            $uuid_factory,
            $configuration_repository,
            $attempt_repository,
            $required_capabilities,
            $this
        );
    }

    public function getParticipantView(
        Language $lng,
        Refinery $refinery,
        UIFactory $ui_factory,
        RequiredCapabilities $required_capabilities,
        ?Attempt $attempt_data,
        bool $interactive = true,
        bool $show_marks = false,
        bool $show_best_response = false,
        bool $show_feedback = false
    ): Views\Participant {
        if (!$required_capabilities->areAllCapabilitiesSupportedByAnswerForms($this->answer_forms)) {
            throw new \UnexpectedValueException(
                "The Question does not support all required Capabilities."
            );
        }

        return new Views\Participant(
            $lng,
            $refinery,
            $ui_factory,
            $required_capabilities,
            $this,
            $attempt_data,
            $interactive,
            $show_marks,
            $show_best_response,
            $show_feedback
        );
    }

    public function initializeAttemptData(
        Attempt $attempt
    ): Attempt {
        return array_reduce(
            $this->answer_forms,
            fn(Attempt $c, AnswerFormProperties $v): Attempt
                => $v->getDefinition()->initializeAttemptData($c, $v),
            $attempt
        );
    }

    public function toEditLink(
        LinkFactory $link_factory,
        DefaultEnvironment $environment
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
        DefaultEnvironment $environment,
        RequiredCapabilities $required_capabilities
    ): ?DataRow {
        if (!$required_capabilities->areAllCapabilitiesSupportedByAnswerForms($this->answer_forms)) {
            return null;
        }

        return $row_builder->buildDataRow(
            $this->id->toString(),
            [
                OverviewTableColumns::Title->value => $environment
                    ->getUIFactory()->link()->standard(
                        $this->title,
                        $environment->withQuestionIdParameter(
                            $this->id
                        )->getUrlBuilder()
                        ->buildURI()
                        ->__toString()
                    ),
                    OverviewTableColumns::AnswerFormTypes->value => implode(
                        '<br>',
                        $this->getListOfContainedAnswerFormTypeLabels(
                            $environment->getLanguage()
                        )
                    )
            ]
        );
    }

    public function toStorage(
        DatabaseStatementBuilder $database_statement_builder,
        Manipulate $manipulate
    ): Manipulate {
        return $manipulate->getManipulationType() === ManipulationType::Create
            ? $database_statement_builder->addInsertStatementsToManipulation(
                $manipulate,
                $this->id,
                $this->page_id,
                $this->title,
                $this->author,
                $this->lifecycle,
                $this->remarks,
                $this->original_id,
                $this->parent_obj_id,
                $this->position
            ) : $database_statement_builder->addUpdateStatementsToManipulation(
                $manipulate,
                $this->self_updated,
                $this->linking_information_updated,
                $this->page_id_updated,
                $this->id,
                $this->page_id,
                $this->title,
                $this->author,
                $this->lifecycle,
                $this->remarks,
                $this->original_id,
                $this->parent_obj_id,
                $this->position,
                $this->updated_answer_forms,
                $this->deleted_answer_forms
            );
    }

    public function toDelete(
        DatabaseStatementBuilder $database_statement_builder,
        Manipulate $manipulate
    ): Manipulate {
        $table_names_builder = $manipulate->getTableNameBuilder(null);

        return $database_statement_builder->addDeleteAnswerFormsStatementsToManipulate(
            $manipulate->withAdditionalStatement(
                $database_statement_builder->buildDeleteQuestionStatement(
                    $table_names_builder,
                    $this->id
                )
            )->withAdditionalStatement(
                $database_statement_builder->buildDeleteLinkingStatement(
                    $table_names_builder,
                    $this->id
                )
            )->withAdditionalStatement(
                $database_statement_builder->buildDeleteMigrationStatement(
                    $table_names_builder,
                    $this->id
                )
            ),
            $this->answer_forms
        );
    }

    public function completeResponseQuery(
        Query $query,
        Column $base_table_id_column
    ): Query {
        return array_reduce(
            $this->getListOfContainedAnswerFormTypes(),
            fn(Query $c, AnswerFormDefinition $v): Query => $v
                ->getTableDefinitions()
                ->completeResponseQuery(
                    $c,
                    $base_table_id_column
                ),
            $query
        );
    }

    public function retrieveAnswerFormResponsesFromQuery(
        Uuid $response_id,
        Query $query
    ): array {
        return array_map(
            fn(AnswerFormProperties $v): AnswerFormResponse => $v
                ->getDefinition()
                ->buildResponse(
                    $response_id,
                    $v,
                    $query
                ),
            $this->answer_forms
        );
    }

    public function retrieveAnswerFormResponsesFromPost(
        RequiredCapabilities $required_capabilities,
        RequestWrapper $post_wrapper,
        Uuid $response_id
    ): array {
        return array_map(
            fn(AnswerFormProperties $v): AnswerFormResponse => $required_capabilities
                ->getParticipantViewProvider()
                ->getParticipantView($v)
                ->retrieveResponse(
                    $response_id,
                    $v,
                    $post_wrapper
                ),
            $this->answer_forms
        );
    }

    public function retrieveAnswerFormResponsesFromPreviewData(
        Uuid $response_id,
        array $preview_data
    ): array {
        return array_map(
            fn(AnswerFormProperties $v): AnswerFormResponse => $v
                ->getDefinition()
                ->buildResponseFromPreviewData(
                    $response_id,
                    $v,
                    $preview_data[$v->getAnswerFormId()->toString()] ?? []
                ),
            $this->answer_forms
        );
    }

    public function addAwardedPointsToResponse(
        Response $response
    ): Response {
        return $response->withAwardedPoints(
            array_reduce(
                $this->answer_forms,
                function (?float $c, AnswerFormProperties $v) use ($response): ?float {
                    /** @var Marking $marking */
                    $marking = $v->getDefinition()->getCapability(
                        MarkingAllowingPartialPoints::getIdentifier()
                    );

                    if ($marking === null) {
                        return $c;
                    }

                    $points_from_form = $marking->calculateAwardedPoints(
                        $v,
                        $response->getAnswerFormResponse($v->getAnswerFormId())
                    );

                    if ($points_from_form === null) {
                        return $c;
                    }

                    if ($c === null) {
                        return $points_from_form;
                    }

                    return $c + $points_from_form;
                }
            )
        );
    }

    private function getListOfContainedAnswerFormTypes(): array
    {
        return array_reduce(
            $this->answer_forms,
            function (
                array $c,
                AnswerFormProperties $v
            ): array {
                $definition = $v->getDefinition();
                if (!array_key_exists($definition::class, $c)) {
                    $c[$definition::class] = $definition;
                }
                return $c;
            },
            []
        );
    }
}
