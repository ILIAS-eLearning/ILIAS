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

namespace ILIAS\Questions\Presentation;

use ILIAS\Questions\AnswerForm\Definition;
use ILIAS\Questions\AnswerForm\Factory as AnswerFormFactory;
use ILIAS\Questions\AnswerForm\TypeGenericProperties;
use ILIAS\Questions\Question\Persistence\Repository;
use ILIAS\Questions\Question\QuestionImplementation;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\URI;
use ILIAS\Data\UUID\Factory as UuidFactory;
use ILIAS\Data\UUID\Uuid;
use ILIAS\Language\Language;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Transformation;
use ILIAS\HTTP\Services as HTTP;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\UI\Component\Input\Container\Form\Standard as StandardForm;
use ILIAS\UI\Component\Item\Standard as StandardItem;
use ILIAS\UI\Component\Item\Group as ItemGroup;
use ILIAS\UI\Component\MainControls\Slate\Legacy as LegacySlate;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ILIAS\GlobalScreen\Services as GlobalScreen;

class Edit
{
    private const array QUERY_PARAMETER_NAME_SPACE = ['q'];
    private const string TOKEN_STRING_ACTION = 'a';
    private const string TOKEN_STRING_STEP = 's';
    private const string TOKEN_STRING_QUESTION_ID = 'q';
    private const string TOKEN_STRING_PAGE_ID = 'p';
    private const string TOKEN_TYPE_HASH = 't';
    private const string CMD_CREATE_QUESTION = 'create';
    private const string CMD_EDIT_QUESTION = 'edit';
    private const string CMD_CREATE_ANSWER_FORM = 'create_af';
    private const string CMD_EDIT_ANSWER_FORM = 'edit_af';

    private array $required_capabilities = [];
    private Editability $editability = Editability::Full;
    private bool $ordering_enabled = false;

    public function __construct(
        private readonly Language $lng,
        private readonly \ilObjUser $current_user,
        private readonly Refinery $refinery,
        private readonly UIFactory $ui_factory,
        private readonly UIRenderer $ui_renderer,
        private readonly GlobalScreen $global_screen,
        private readonly \ilCtrl $ctrl,
        private readonly HTTP $http,
        private readonly \ilUIService $ui_services,
        private readonly DataFactory $data_factory,
        private readonly UuidFactory $uuid_factory,
        private readonly AnswerFormFactory $answer_form_factory,
        private readonly Repository $questions_repository
    ) {

    }

    public function withRequiredCapabilities(array $capability_class_names): self
    {
        $this->checkCapabilities($capability_class_names);
        $clone = clone $this;
        $clone->required_capabilities = $capability_class_names;
        return $clone;
    }

    public function withEditable(Editability $editability): self
    {
        $clone = clone $this;
        $clone->editability = $editability;
        return $clone;
    }

    public function withOrderingEnabled(bool $enable): self
    {
        $clone = clone $this;
        $clone->ordering_enabled = $enable;
        return $clone;
    }

    public function view(
        \ilToolbarGUI $toolbar,
        URI $base_uri
    ): array {
        [
            $url_builder,
            $action_token,
            $step_token,
            $question_id_token,
            $page_id_token
        ] = $this->acquireURLBuilderAndParameters($base_uri);
        return match($this->retrieveStringValueForToken($action_token)) {
            self::CMD_CREATE_QUESTION => $this->createQuestion(
                $url_builder,
                $action_token,
                $step_token,
                $question_id_token,
                $page_id_token
            ),
            self::CMD_EDIT_QUESTION => $this->editQuestion(
                $url_builder,
                $action_token,
                $step_token,
                $question_id_token,
                $page_id_token
            ),
            default => $this->showTable(
                $toolbar,
                $url_builder,
                $action_token,
                $question_id_token
            )
        };
    }

    public function forwardPageCmds(
        \ilGlobalTemplateInterface $tpl,
        URI $base_uri,
    ): void {
        [
            0 => $url_builder,
            1 => $action_token,
            3 => $question_id_token,
            4 => $page_id_token
        ] = $this->acquireURLBuilderAndParameters($base_uri);

        $this->initializeEditMode($url_builder, $action_token, $question_id_token);

        $question_id = $this->retrieveQuestionId($question_id_token);
        $page_id = $this->retrievePageId($page_id_token);
        $this->setParametersForQuestionCmds($question_id_token, $question_id->toString(), $page_id_token, $page_id);

        $tpl->setContent(
            $this->ctrl->forwardCommand(
                new \QstsQuestionPageGUI(
                    $url_builder->withParameter($action_token, self::CMD_EDIT_QUESTION)
                        ->withParameter($question_id_token, $question_id->toString())
                        ->buildURI(),
                    $page_id
                )
            )
        );
    }

    public function createAnswerForm(
        URI $base_uri
    ): array {
        [
            $url_builder,
            $action_token,
            $step_token,
            $question_id_token,
            $page_id_token,
            $type_hash_token
        ] = $this->acquireURLBuilderAndParameters($base_uri);
        $question_id = $this->retrieveQuestionId($question_id_token);
        $url_builder_with_params = $url_builder
                ->withParameter($question_id_token, $question_id->toString())
                ->withParameter($page_id_token, (string) $this->retrievePageId($page_id_token))
                ->withParameter($action_token, self::CMD_CREATE_ANSWER_FORM);

        $answer_form_type_class_hash = $this->retrieveStringValueForToken($type_hash_token);
        if ($answer_form_type_class_hash !== '') {
            return $this->forwardCreateAnswerFormCmd(
                $this->answer_form_types_factory->buildTypeDefinitionFromSelectValue($answer_form_type_class_hash),
                $this->answer_form_factory->getDefaultTypeGenericProperties($question_id),
                $url_builder_with_params->withParameter($type_hash_token, $answer_form_type_class_hash),
                $step_token
            );
        }

        return match($this->retrieveStringValueForToken($action_token)) {
            self::CMD_CREATE_ANSWER_FORM => $this->processCreateAnswerForm(
                $url_builder_with_params,
                $action_token,
                $step_token,
                $type_hash_token
            ),
            default => [$this->buildCreateAnswerForm(
                $url_builder_with_params,
                $action_token
            )]
        };
    }

    public function editAnswerForm(
        URI $base_uri
    ): array {
        [$url_builder, $action_token] = $this->acquireURLBuilderAndParameters($base_uri);
        return match($this->retrieveStringValueForToken($action_token)) {
            self::CMD_EDIT_ANSWER_FORM => [$this->processCreateAnswerForm($url_builder, $action_token)],
            default => [$this->buildCreateAnswerForm($url_builder, $action_token)]
        };
    }

    private function createQuestion(
        URLBuilder $url_builder,
        URLBuilderToken $action_token,
        URLBuilderToken $step_token,
        URLBuilderToken $question_id_token,
        URLBuilderToken $page_id_token
    ): array {
        $this->initializeEditMode($url_builder, $action_token, $question_id_token);

        $create = (new QuestionImplementation())->getEditView(
            $this->lng,
            $this->current_user,
            $this->ui_factory,
            $this->refinery,
            $this->http->request(),
            $this->ctrl,
            $this->data_factory
        )->create(
            $url_builder->withParameter($action_token, self::CMD_CREATE_QUESTION),
            $step_token,
            $this->retrieveStringValueForToken($step_token)
        );

        if (is_array($create)) {
            return $create;
        }

        $this->questions_repository->store($create);
        return $this->buildEditStartView(
            $url_builder->withParameter($question_id_token, $create->getId()),
            $step_token,
            $page_id_token,
            $create
        );

    }

    private function editQuestion(
        URLBuilder $url_builder,
        URLBuilderToken $action_token,
        URLBuilderToken $step_token,
        URLBuilderToken $question_id_token,
        URLBuilderToken $page_id_token
    ): array {
        $this->initializeEditMode($url_builder, $action_token, $question_id_token);

        $question_id = $this->retrieveQuestionId($question_id_token);

        $url_builder_with_row_id = $url_builder->withParameter($question_id_token, $question_id->toString());

        $edit = $this->questions_repository->getForQuestionId($question_id)->getEditView(
            $this->lng,
            $this->current_user,
            $this->ui_factory,
            $this->refinery,
            $this->http->request(),
            $this->ctrl,
            $this->data_factory
        )->edit(
            $url_builder_with_row_id->withParameter($action_token, self::CMD_EDIT_QUESTION),
            $step_token,
            $page_id_token,
            $this->retrieveStringValueForToken($step_token)
        );

        if (is_array($edit)) {
            return $edit;
        }

        $this->questions_repository->store($edit);
        return $this->buildEditStartView(
            $url_builder_with_row_id,
            $step_token,
            $page_id_token,
            $edit
        );
    }

    private function showTable(
        \ilToolbarGUI $toolbar,
        URLBuilder $url_builder,
        URLBuilderToken $action_token,
        URLBuilderToken $question_id_token
    ): array {
        $toolbar->addComponent(
            $this->ui_factory->button()->standard(
                $this->lng->txt('create'),
                $url_builder->withParameter($action_token, self::CMD_CREATE_QUESTION)->buildURI()->__toString()
            )
        );

        $table = new QuestionsTable(
            $this->ui_factory,
            $this->ui_services,
            $this->lng,
            $this->answer_form_types_factory,
            $this->questions_repository,
            $url_builder->withParameter($action_token, self::CMD_EDIT_QUESTION),
            $action_token,
            $question_id_token
        );
        return [
            $table->getFilter($url_builder->buildURI()->__toString()),
            $table->getTable()->withRequest($this->http->request())

        ];
    }

    private function processCreateAnswerForm(
        URLBuilder $url_builder,
        URLBuilderToken $action_token,
        URLBuilderToken $step_token,
        URLBuilderToken $type_hash_token
    ): array {
        $form = $this->buildCreateAnswerForm(
            $url_builder,
            $action_token
        )->withRequest($this->http->request());

        $data = $form->getData();
        if ($data === null || $data['form_type'] === null) {
            return [$form];
        }

        return $this->forwardCreateAnswerFormCmd(
            $data['form_type'],
            $url_builder->withParameter($type_hash_token, $this->answer_form_types_factory->getHashedClass($data['form_type']::class)),
            $step_token
        );
    }

    private function forwardCreateAnswerFormCmd(
        Definition $type,
        TypeGenericProperties $type_generic_properties,
        URLBuilder $url_builder,
        URLBuilderToken $step_token
    ): array {
        return $type->getEditView()->create(
            $type->buildProperties($type_generic_properties, []),
            $url_builder,
            $step_token,
            $this->retrieveStringValueForToken($step_token)
        );
    }

    private function acquireURLBuilderAndParameters(URI $base_uri): array
    {
        return (new URLBuilder($base_uri))
            ->acquireParameters(
                self::QUERY_PARAMETER_NAME_SPACE,
                self::TOKEN_STRING_ACTION,
                self::TOKEN_STRING_STEP,
                self::TOKEN_STRING_QUESTION_ID,
                self::TOKEN_STRING_PAGE_ID,
                self::TOKEN_TYPE_HASH
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

    private function retrieveQuestionId(
        URLBuilderToken $question_id_token
    ): ?Uuid {
        return $this->http->wrapper()->query()->retrieve(
            $question_id_token->getName(),
            $this->refinery->byTrying([
                $this->refinery->custom()->transformation(
                    fn($v): Uuid => $this->uuid_factory->fromString($v)
                ),
                $this->refinery->always(null)
            ])
        );
    }

    public function retrievePageId(
        URLBuilderToken $page_id_token
    ): ?int {
        return $this->http->wrapper()->query()->retrieve(
            $page_id_token->getName(),
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->int(),
                $this->refinery->always(null)
            ])
        );
    }

    private function buildStringTrafo(): Transformation
    {
        return $this->refinery->byTrying([
            $this->refinery->kindlyTo()->string(),
            $this->refinery->always('')
        ]);
    }

    private function initializeEditMode(
        URLBuilder $url_builder,
        URLBuilderToken $action_token,
        URLBuilderToken $question_id_token
    ): void {
        $this->global_screen->tool()->context()->current()->addAdditionalData(
            LayoutProvider::MODE_ENABLED,
            true
        );
        $this->global_screen->tool()->context()->current()->addAdditionalData(
            LayoutProvider::QUESTIONLIST_ENTRY,
            $this->buildQuestionListSlate($url_builder, $action_token, $question_id_token)
        );
        $this->global_screen->tool()->context()->current()->addAdditionalData(
            LayoutProvider::URL_CLOSE_MODE_INFO,
            $url_builder->buildURI()
        );
        $this->global_screen->tool()->context()->current()->addAdditionalData(
            LayoutProvider::URL_CREATE_QUESTION,
            $url_builder->withParameter($action_token, self::CMD_CREATE_QUESTION)->buildURI()
        );
    }

    private function setParametersForQuestionCmds(
        URLBuilderToken $question_id_token,
        string $question_id,
        URLBuilderToken $page_id_token,
        int $page_id
    ): void {
        $this->ctrl->setParameterByClass(
            \QstsQuestionPageGUI::class,
            $question_id_token->getName(),
            $question_id
        );
        $this->ctrl->setParameterByClass(
            \QstsQuestionPageGUI::class,
            $page_id_token->getName(),
            $page_id
        );
    }

    private function buildQuestionListSlate(
        URLBuilder $url_builder,
        URLBuilderToken $action_token,
        URLBuilderToken $question_id_token
    ): LegacySlate {
        return $this->ui_factory->mainControls()->slate()->legacy(
            $this->lng->txt('mainbar_button_label_questionlist'),
            $this->ui_factory->symbol()->icon()->standard('', '')->withAbbreviation('QL'),
            $this->ui_factory->legacy()->content(
                $this->ui_renderer->render(
                    $this->ui_factory->panel()->secondary()->listing(
                        $this->lng->txt('mainbar_button_label_questionlist'),
                        [
                            $this->buildItemGroupForQuestionListSlate($url_builder, $action_token, $question_id_token)
                        ]
                    )
                )
            )
        );
    }

    private function buildItemGroupForQuestionListSlate(
        URLBuilder $url_builder,
        URLBuilderToken $action_token,
        URLBuilderToken $question_id_token
    ): ItemGroup {
        return $this->ui_factory->item()->group(
            '',
            array_map(
                fn(QuestionImplementation $v): StandardItem => $this->ui_factory->item()->standard(
                    $v->toEditLink(
                        $this->ui_factory->link(),
                        $url_builder->withParameter($action_token, self::CMD_EDIT_QUESTION),
                        $question_id_token
                    )
                ),
                iterator_to_array($this->questions_repository->getAllQuestions())
            )
        );
    }

    private function buildEditStartView(
        URLBuilder $url_builder,
        URLBuilderToken $step_token,
        URLBuilderToken $page_id_token,
        QuestionImplementation $question
    ): array {
        return $question->getEditView(
            $this->lng,
            $this->current_user,
            $this->ui_factory,
            $this->refinery,
            $this->http->request(),
            $this->ctrl,
            $this->data_factory
        )->edit(
            $url_builder,
            $step_token,
            $page_id_token,
            ''
        );
    }

    private function buildCreateAnswerForm(
        URLBuilder $url_builder
    ): StandardForm {
        $if = $this->ui_factory->input();
        return $if->container()->form()->standard(
            $url_builder->buildURI()->__toString(),
            [
                'form_type' => $if->field()->section(
                    [
                        $if->field()->select(
                            $this->lng->txt('select_answer_form_type'),
                            $this->answer_form_factory->getAnswerFormTypesArrayForSelect()
                        )->withRequired(true)
                    ],
                    $this->lng->txt('create_answer_form')
                )->withAdditionalTransformation(
                    $this->refinery->custom()->transformation(
                        fn(array $vs): ?Form => $this->answer_form_factory->buildTypeDefinitionFromSelectValue($vs[0])
                    )
                )
            ]
        )->withSubmitLabel($this->lng->txt('next'));
    }

    private function checkCapabilities(array $capabilities): void
    {
        foreach ($capabilities as $capability) {
            if (!$this->questions_repository->capabilityExists($capability)) {
                throw new \InvalidArgumentException('All provided capabilities must implement ILIAS\Questions\AnswerForm\Capabilities\Capability.');
            }
        }
    }
}
