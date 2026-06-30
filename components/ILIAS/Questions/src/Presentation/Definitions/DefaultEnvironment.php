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

namespace ILIAS\Questions\Presentation\Definitions;

use ILIAS\Questions\AnswerForm\Capabilities\Definitions\AdditionalFormStepAction;
use ILIAS\Questions\AnswerForm\Capabilities\RequiredCapabilities;
use ILIAS\Questions\AnswerForm\Properties;
use ILIAS\Questions\Presentation\Layout\Factory;
use ILIAS\Questions\Presentation\Views\Edit;
use ILIAS\Data\URI;
use ILIAS\Data\UUID\Factory as UuidFactory;
use ILIAS\Data\UUID\Uuid;
use ILIAS\HTTP\Services as HTTPServices;
use ILIAS\Language\Language;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Transformation;
use ILIAS\UI\Component\Table\Action\Action as TableAction;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;

class DefaultEnvironment implements Environment
{
    private const array QUERY_PARAMETER_NAME_SPACE = ['q'];
    private const string TOKEN_STRING_ACTION = 'a';
    private const string TOKEN_STRING_SUB_ACTION = 'sa';
    private const string TOKEN_STRING_QUESTION_ID = 'q';
    private const string TOKEN_STRING_TABLE_ROW_ID = 'r';
    private const string TOKEN_STRING_TYPE_HASH = 't';
    private const string TOKEN_STRING_ANSWER_FORM_ID = 'af';
    private const string TOKEN_STRING_CREATE_MODE = 'cm';
    private const string TOKEN_STRING_FORM_START_SUB_ACTION = 'fssa';
    private const string TOKEN_STRING_CREATE_AND_NEW = 'can';

    private const string PARAMETER_STRING_HIER_ID = 'hier_id';

    private const string INTERRUPTIVE_ITEMS_KEY = 'interruptive_items';

    private const string TAB_ID_ANSWER_FORM = 'answer_form';

    private ?Properties $answer_form_properties = null;

    private bool $default_sub_action = false;
    private bool $is_in_creation_context = false;

    private URLBuilder $url_builder;
    private readonly URLBuilderToken $table_row_token;
    private readonly URLBuilderToken $question_id_token;
    private ?URLBuilderToken $sub_action_token = null;
    private ?URLBuilderToken $action_token = null;
    private ?URLBuilderToken $type_hash_token = null;
    private ?URLBuilderToken $answer_form_id_token = null;
    private ?URLBuilderToken $create_mode_token = null;
    private ?URLBuilderToken $form_start_sub_action_token = null;

    private ?array $table_row_ids = null;

    public function __construct(
        private readonly \ilCtrl $ctrl,
        private readonly HTTPServices $http,
        private readonly Refinery $refinery,
        private readonly Language $lng,
        private readonly \ilTabsGUI $tabs_gui,
        private readonly UuidFactory $uuid_factory,
        private readonly Factory $presentation_factory,
        private readonly Editability $editability,
        private readonly RequiredCapabilities $required_capabilities,
        private readonly int $parent_obj_id,
        URI $base_uri
    ) {
        $this->acquireURLBuilderAndParameters($base_uri);
    }

    #[\Override]
    public function getHttpServices(): HTTPServices
    {
        return $this->http;
    }

    #[\Override]
    public function getLanguage(): Language
    {
        return $this->lng;
    }

    #[\Override]
    public function getRefinery(): Refinery
    {
        return $this->refinery;
    }

    #[\Override]
    public function getUIFactory(): UIFactory
    {
        return $this->presentation_factory->getUIFactory();
    }

    #[\Override]
    public function setEditAnswerFormBackTarget(): void
    {
        $this->tabs_gui->clearTargets();
        $this->tabs_gui->setBackTarget(
            $this->lng->txt('cancel'),
            $this->buildEditAnswerFormBackUrl()->buildURI()->__toString()
        );
    }

    #[\Override]
    public function addEditAnswerFormSubTab(
        string $sub_action,
        string $language_variable
    ): void {
        $this->tabs_gui->addSubTab(
            $sub_action,
            $this->lng->txt($language_variable),
            $this->withSubActionParameter($sub_action)
                ->withActionParameter(Edit::ACTION_OTHER_ANSWER_FORM)
                ->getUrlBuilder()
                ->buildURI()
                ->__toString()
        );
    }

    #[\Override]
    public function activateEditAnswerFormSubTab(
        string $sub_action
    ): void {
        $this->tabs_gui->activateSubTab($sub_action);
    }

    #[\Override]
    public function getPresentationFactory(): Factory
    {
        return $this->presentation_factory;
    }

    #[\Override]
    public function getUrlBuilder(): URLBuilder
    {
        return $this->url_builder;
    }

    #[\Override]
    public function withSubActionParameter(
        string $sub_action
    ): self {
        $clone = clone $this;
        if ($clone->sub_action_token === null) {
            [
                $clone->url_builder,
                $clone->sub_action_token
            ] = $this->url_builder->acquireParameter(
                self::QUERY_PARAMETER_NAME_SPACE,
                self::TOKEN_STRING_SUB_ACTION
            );
        }
        $clone->url_builder = $clone->url_builder
            ->withParameter($clone->sub_action_token, $sub_action);
        return $clone;
    }

    #[\Override]
    public function withDefaultSubAction(): self
    {
        $clone = clone $this;
        $clone->default_sub_action = true;
        return $clone;
    }

    #[\Override]
    public function getSubAction(): string
    {
        if ($this->default_sub_action) {
            return '';
        }

        $sub_action_token = $this->sub_action_token;
        if ($sub_action_token === null) {
            [,$sub_action_token] = $this->url_builder->acquireParameter(
                self::QUERY_PARAMETER_NAME_SPACE,
                self::TOKEN_STRING_SUB_ACTION
            );
        }

        return $this->retrieveStringValueForToken(
            $sub_action_token,
            self::TOKEN_STRING_SUB_ACTION
        );
    }

    #[\Override]
    public function getEditability(): Editability
    {
        return $this->editability;
    }

    #[\Override]
    public function isMarkingRequired(): bool
    {
        return $this->required_capabilities->isMarkingRequired();
    }

    #[\Override]
    public function getAnswerFormTableActionsForRequiredCapabilities(): array
    {
        return array_map(
            fn(AdditionalFormStepAction $v): TableAction => $v->getAsTableAction(
                $this->withActionParameter($v->getIdentifier())
            ),
            $this->required_capabilities->getRequiredFormStepActions()
        );
    }

    #[\Override]
    public function isInCreationContext(): bool
    {
        return $this->is_in_creation_context;
    }

    #[\Override]
    public function getAnswerFormId(): ?Uuid
    {
        if ($this->answer_form_properties !== null) {
            return $this->answer_form_properties->getAnswerFormId();
        }

        $answer_form_id_token = $this->answer_form_id_token;
        if ($answer_form_id_token === null) {
            [,$answer_form_id_token] = $this->url_builder->acquireParameter(
                self::QUERY_PARAMETER_NAME_SPACE,
                self::TOKEN_STRING_ANSWER_FORM_ID
            );
        }
        return $this->http->wrapper()->query()->retrieve(
            $answer_form_id_token->getName(),
            $this->refinery->byTrying([
                $this->refinery->custom()->transformation(
                    $this->buildRetrieveUuidClosure()
                ),
                $this->refinery->always(null)
            ])
        );
    }

    #[\Override]
    public function getAnswerFormProperties(): ?Properties
    {
        return $this->answer_form_properties;
    }

    #[\Override]
    public function withAnswerFormProperties(
        Properties $properties
    ): self {
        $clone = clone $this;
        $clone->answer_form_properties = $properties;
        return $clone;
    }

    #[\Override]
    public function getTableRowIdToken(): URLBuilderToken
    {
        return $this->table_row_token;
    }

    /**
     * @return list<string>
     */
    #[\Override]
    public function getTableRowIds(): array
    {
        if ($this->table_row_ids === null) {
            $this->table_row_ids = $this->http->wrapper()->query()->retrieve(
                $this->table_row_token->getName(),
                $this->refinery->byTrying([
                    $this->refinery->kindlyTo()->listOf(
                        $this->refinery->custom()->transformation(
                            fn($v): string => $v !== ''
                                ? $this->refinery->kindlyTo()->string()->transform($v)
                                : throw new \UnexpectedValueException()
                        )
                    ),
                    $this->refinery->always([])
                ])
            );
        }

        return $this->table_row_ids;
    }

    #[\Override]
    public function withPreservedTableRowIdsParameter(): self
    {
        $clone = clone $this;
        $clone->table_row_ids = $clone->getTableRowIds();
        $clone->url_builder = $this->url_builder
            ->withParameter($this->table_row_token, $clone->table_row_ids);
        return $clone;
    }

    #[\Override]
    public function redirectTo(URLBuilder $target): void
    {
        $this->ctrl->redirectToURL(
            $target->buildURI()->__toString()
        );
    }

    public function withIsInCreationContext(
        bool $is_in_creation_context
    ): self {
        $clone = clone $this;
        $clone->is_in_creation_context = $is_in_creation_context;
        return $clone;
    }

    public function getParentObjId(): int
    {
        return $this->parent_obj_id;
    }

    public function getAction(): string
    {
        $action_token = $this->action_token;
        if ($action_token === null) {
            [,$action_token] = $this->url_builder->acquireParameter(
                self::QUERY_PARAMETER_NAME_SPACE,
                self::TOKEN_STRING_ACTION
            );
        }
        return $this->retrieveStringValueForToken($action_token);
    }

    public function withActionParameter(
        string $action
    ): self {
        $clone = clone $this;
        if ($clone->action_token === null) {
            [
                $clone->url_builder,
                $clone->action_token
            ] = $this->url_builder->acquireParameter(
                self::QUERY_PARAMETER_NAME_SPACE,
                self::TOKEN_STRING_ACTION
            );
        }
        $clone->url_builder = $clone->url_builder
            ->withParameter($clone->action_token, $action);
        return $clone;
    }

    public function withQuestionIdParameter(
        Uuid $question_id
    ): self {
        $clone = clone $this;
        $clone->url_builder = $this->url_builder
            ->withParameter($this->question_id_token, $question_id->toString());
        return $clone;
    }

    public function withAnswerFormTypeHashParameter(
        string $type_hash
    ): self {
        $clone = clone $this;
        if ($clone->type_hash_token === null) {
            [
                $clone->url_builder,
                $clone->type_hash_token
            ] = $this->url_builder->acquireParameter(
                self::QUERY_PARAMETER_NAME_SPACE,
                self::TOKEN_STRING_TYPE_HASH
            );
        }

        $clone->url_builder = $clone->url_builder
            ->withParameter($clone->type_hash_token, $type_hash);
        return $clone;
    }

    public function withAnswerFormIdParameter(
        Uuid $answer_form_id
    ): self {
        $clone = clone $this;
        if ($clone->answer_form_id_token === null) {
            [
                $clone->url_builder,
                $clone->answer_form_id_token
            ] = $this->url_builder->acquireParameter(
                self::QUERY_PARAMETER_NAME_SPACE,
                self::TOKEN_STRING_ANSWER_FORM_ID
            );
        }
        $clone->url_builder = $clone->url_builder->withParameter(
            $clone->answer_form_id_token,
            $answer_form_id->toString()
        );
        return $clone;
    }

    public function withCreateModeParameter(): self
    {
        $clone = clone $this;
        if ($clone->create_mode_token === null) {
            [
                $clone->url_builder,
                $clone->create_mode_token
            ] = $this->url_builder->acquireParameter(
                self::QUERY_PARAMETER_NAME_SPACE,
                self::TOKEN_STRING_CREATE_MODE
            );
        }

        $clone->url_builder = $clone->url_builder
            ->withParameter($clone->create_mode_token, '1');
        return $clone;
    }

    #[\Override]
    public function withFormStartSubActionParameter(
        string $sub_action
    ): self {
        $clone = clone $this;
        if ($clone->form_start_sub_action_token === null) {
            [
                $clone->url_builder,
                $clone->form_start_sub_action_token
            ] = $this->url_builder->acquireParameter(
                self::QUERY_PARAMETER_NAME_SPACE,
                self::TOKEN_STRING_FORM_START_SUB_ACTION
            );
        }

        $clone->url_builder = $clone->url_builder
            ->withParameter(
                $clone->form_start_sub_action_token,
                $sub_action
            );
        return $clone;
    }

    #[\Override]
    public function withPreservedFormStartSubActionParameter(): self
    {
        return $this->withFormStartSubActionParameter(
            $this->getFormStartSubAction()
        );
    }

    public function getQuestionId(): ?Uuid
    {
        return $this->http->wrapper()->query()->retrieve(
            $this->question_id_token->getName(),
            $this->refinery->byTrying([
                $this->refinery->custom()->transformation(
                    $this->buildRetrieveUuidClosure()
                ),
                $this->refinery->always(null)
            ])
        );
    }

    /**
     * This function will either return the QuestionIds from the $_GET parameter
     * for row ids OR from an InterruptiveItems $_POST value.
     * @return array<\ILIAS\Data\UUID\Uuid>|string|null
     */
    public function getQuestionIds(): array|string|null
    {
        return $this->http->wrapper()->query()->retrieve(
            $this->table_row_token->getName(),
            $this->refinery->byTrying([
                $this->refinery->custom()->transformation(
                    fn($v): string => $v === ['ALL_OBJECTS']
                        ? 'ALL_OBJECTS'
                        : throw new \UnexpectedValueException()
                ),
                $this->refinery->kindlyTo()->listOf(
                    $this->refinery->custom()->transformation(
                        $this->buildRetrieveUuidClosure()
                    )
                ),
                $this->refinery->always(null)
            ])
        ) ?? $this->http->wrapper()->post()->retrieve(
            self::INTERRUPTIVE_ITEMS_KEY,
            $this->refinery->kindlyTo()->listOf(
                $this->refinery->custom()->transformation(
                    $this->buildRetrieveUuidClosure()
                )
            ),
            $this->refinery->always(null)
        );
    }

    public function getTypeClassHash(): string
    {
        $type_hash_token = $this->type_hash_token;
        if ($type_hash_token === null) {
            [,$type_hash_token] = $this->url_builder->acquireParameter(
                self::QUERY_PARAMETER_NAME_SPACE,
                self::TOKEN_STRING_TYPE_HASH
            );
        }
        return $this->retrieveStringValueForToken($type_hash_token);
    }

    public function getFormStartSubAction(): string
    {
        $form_start_command_token = $this->form_start_sub_action_token;
        if ($form_start_command_token === null) {
            [,$form_start_command_token] = $this->url_builder->acquireParameter(
                self::QUERY_PARAMETER_NAME_SPACE,
                self::TOKEN_STRING_FORM_START_SUB_ACTION
            );
        }
        return $this->retrieveStringValueForToken($form_start_command_token);
    }

    public function isCreateModeSimple(): bool
    {
        $create_mode_token = $this->create_mode_token;
        if ($create_mode_token === null) {
            [, $create_mode_token] = $this->url_builder->acquireParameter(
                self::QUERY_PARAMETER_NAME_SPACE,
                self::TOKEN_STRING_CREATE_MODE
            );
        }

        return $this->http->wrapper()->query()->has(
            $create_mode_token->getName()
        );
    }

    /**
     *
     * @param array<\ILIAS\Questions\AnswerForm\Capabilities\Action> $additional_actions
     */
    public function setEditAnswerFormTabs(
        array $additional_actions
    ): void {
        $this->tabs_gui->addTab(
            self::TAB_ID_ANSWER_FORM,
            $this->lng->txt('answer_form'),
            $this->withDefaultSubAction()->getUrlBuilder()->buildURI()->__toString()
        );

        foreach ($additional_actions as $action) {
            $action->addTab(
                $this,
                $this->tabs_gui,
                $this->lng
            );
        }

        $this->tabs_gui->addSubTab(
            self::TAB_ID_ANSWER_FORM,
            $this->lng->txt('overview'),
            $this->withDefaultSubAction()->getUrlBuilder()->buildURI()->__toString()
        );

        $this->tabs_gui->activateTab(self::TAB_ID_ANSWER_FORM);
        $this->tabs_gui->activateSubTab(self::TAB_ID_ANSWER_FORM);
    }

    public function preserveParametersForPageEditorCmds(): void
    {
        $this->setQuestionIdParamterForPageEditorCmds($this->getQuestionId());
    }

    public function setParamtersForSimpleCreateCmd(
        Uuid $question_id
    ): void {
        $this->setQuestionIdParamterForPageEditorCmds($question_id);

        $this->ctrl->setParameterByClass(
            \QstsQuestionPageGUI::class,
            self::PARAMETER_STRING_HIER_ID,
            '1'
        );

        [, $create_mode_token] = $this->url_builder->acquireParameter(
            self::QUERY_PARAMETER_NAME_SPACE,
            self::TOKEN_STRING_CREATE_MODE
        );

        $this->ctrl->setParameterByClass(
            \QstsQuestionPageGUI::class,
            $create_mode_token->getName(),
            '1'
        );
    }

    public function isCreateAndNewAction(): bool
    {
        return $this->http->wrapper()->query()->has(
            $this->buildURLBuilderTokenForCreateAndNew()->getName()
        );
    }

    public function buildURLBuilderTokenForCreateAndNew(): URLBuilderToken
    {
        return new URLBuilderToken(
            self::QUERY_PARAMETER_NAME_SPACE,
            self::TOKEN_STRING_CREATE_AND_NEW
        );
    }

    private function setQuestionIdParamterForPageEditorCmds(
        Uuid $question_id
    ): void {
        $this->ctrl->setParameterByClass(
            \QstsQuestionPageGUI::class,
            $this->question_id_token->getName(),
            $question_id->toString()
        );
    }

    private function buildEditAnswerFormBackUrl(): URLBuilder
    {
        if (!$this->is_in_creation_context) {
            return $this->withDefaultSubAction()->getUrlBuilder();
        }

        if (!$this->isCreateModeSimple()) {
            return new URLBuilder(
                new URI(
                    ILIAS_HTTP_PATH . '/' . $this->ctrl->getLinkTargetByClass(
                        \QstsQuestionPageGUI::class,
                        'edit'
                    )
                )
            );
        }

        return $this->withActionParameter(
            Edit::ACTION_DELETE_QUESTIONS
        )->withSubActionParameter(
            Edit::ACTION_DELETE_QUESTIONS
        )->getUrlBuilder()->withParameter(
            $this->table_row_token,
            [$this->getQuestionId()->toString()]
        );
    }

    private function acquireURLBuilderAndParameters(
        URI $base_uri
    ): void {
        [
            $this->url_builder,
            $this->table_row_token,
            $this->question_id_token
        ] = (new URLBuilder($base_uri))
            ->acquireParameters(
                self::QUERY_PARAMETER_NAME_SPACE,
                self::TOKEN_STRING_TABLE_ROW_ID,
                self::TOKEN_STRING_QUESTION_ID
            );
    }

    private function retrieveStringValueForToken(
        URLBuilderToken $token
    ): string {
        return $this->http->wrapper()->query()->retrieve(
            $token->getName(),
            $this->buildStringTrafo()
        );
    }

    private function buildStringTrafo(): Transformation
    {
        return $this->refinery->byTrying([
            $this->refinery->kindlyTo()->string(),
            $this->refinery->always('')
        ]);
    }

    private function buildRetrieveUuidClosure(): \Closure
    {
        return fn($v): Uuid => is_string($v)
            ? $this->uuid_factory->fromString($v)
            : throw new \UnexpectedValueException();
    }
}
