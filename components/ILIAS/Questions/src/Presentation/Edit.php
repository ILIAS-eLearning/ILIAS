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

use ILIAS\Questions\AnswerForm\Type;
use ILIAS\Questions\Question\Persistence\Repository;
use ILIAS\Questions\Question\Views\Edit as QuestionEdit;
use ILIAS\Questions\Question\QuestionImplementation;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\URI;
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
    private const string ACTION_TOKEN_STRING = 'a';
    private const string STEP_TOKEN_STRING = 's';
    private const string ROW_ID_TOKEN_STRING = 'r';
    private const string CMD_CREATE_QUESTION = 'create';
    private const string CMD_EDIT_QUESTION = 'edit';
    private const string CMD_CREATE_ACTION_FORM = 'create_af';
    private const string CMD_EDIT_ACTION_FORM = 'edit_af';

    private Editability $editability = Editability::Full;

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
        private readonly Repository $questions_repository
    ) {

    }

    public function withEditable(Editability $editability): self
    {
        $clone = clone $this;
        $clone->editability = $editability;
        return $clone;
    }

    public function view(
        \ilToolbarGUI $toolbar,
        URI $base_uri
    ): array {
        [$url_builder, $action_token, $step_token, $row_id_token] = (new URLBuilder($base_uri))
            ->acquireParameters(
                self::QUERY_PARAMETER_NAME_SPACE,
                self::ACTION_TOKEN_STRING,
                self::STEP_TOKEN_STRING,
                self::ROW_ID_TOKEN_STRING
            );

        return match($this->retrieveAction($action_token)) {
            self::CMD_CREATE_QUESTION => $this->createQuestion($url_builder, $action_token, $step_token, $row_id_token),
            self::CMD_EDIT_QUESTION => $this->editQuestion($url_builder, $action_token, $step_token, $row_id_token),
            default => $this->showTable($toolbar, $url_builder, $action_token, $row_id_token)
        };
    }

    public function forwardPageCmds(
        \ilGlobalTemplateInterface $tpl,
        URI $base_uri,
    ): void {
        [$url_builder, $action_token, $row_id_token] = (new URLBuilder($base_uri))
            ->acquireParameters(
                self::QUERY_PARAMETER_NAME_SPACE,
                self::ACTION_TOKEN_STRING,
                self::ROW_ID_TOKEN_STRING
            );

        $this->initializeEditMode($url_builder, $action_token, $row_id_token);

        $question_id = $this->http->wrapper()->query()->retrieve(
            $row_id_token->getName(),
            $this->refinery->kindlyTo()->string()
        );

        $page_id = $this->http->wrapper()->query()->retrieve(
            QuestionEdit::PAGE_ID_PARAM_FOR_EDITOR,
            $this->refinery->kindlyTo()->int()
        );

        $this->setParametersForQuestionCmds($row_id_token, $question_id, $page_id);

        $tpl->setContent(
            $this->ctrl->forwardCommand(
                new \QstsQuestionPageGUI(
                    $url_builder->withParameter($action_token, self::CMD_EDIT_QUESTION)
                        ->withParameter($row_id_token, $question_id)
                        ->buildURI(),
                    $page_id
                )
            )
        );
    }

    public function createAnswerForm(
        URI $base_uri
    ): array {
        [$url_builder, $action_token] = (new URLBuilder($base_uri))
            ->acquireParameters(
                self::QUERY_PARAMETER_NAME_SPACE,
                self::ACTION_TOKEN_STRING
            );
        return match($this->retrieveAction($action_token)) {
            self::CMD_CREATE_ACTION_FORM => $this->processCreateAnswerForm($url_builder, $action_token),
            default => [$this->buildCreateAnswerForm($url_builder, $action_token)]
        };
    }

    public function editAnswerForm(
        URI $base_uri
    ): array {
        [$url_builder, $action_token] = (new URLBuilder($base_uri))
            ->acquireParameters(
                self::QUERY_PARAMETER_NAME_SPACE,
                self::ACTION_TOKEN_STRING
            );
        return match($this->retrieveAction($action_token)) {
            self::CMD_CREATE_ACTION_FORM => [$this->retrieve($url_builder, $action_token)],
            default => [$this->buildCreateAnswerForm($url_builder, $action_token)]
        };
    }

    private function createQuestion(
        URLBuilder $url_builder,
        URLBuilderToken $action_token,
        URLBuilderToken $step_token,
        URLBuilderToken $row_id_token
    ): array {
        $this->initializeEditMode($url_builder, $action_token, $row_id_token);

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
            $this->retrieveAction($action_token)
        );

        if (is_array($create)) {
            return $create;
        }

        $this->questions_repository->store($create);
        return $this->buildEditStartView(
            $url_builder->withParameter($row_id_token, $create->getId()),
            $step_token,
            $create
        );

    }

    private function editQuestion(
        URLBuilder $url_builder,
        URLBuilderToken $action_token,
        URLBuilderToken $step_token,
        URLBuilderToken $row_id_token
    ): array {
        $this->initializeEditMode($url_builder, $action_token, $row_id_token);

        $question_id = $this->http->wrapper()->query()->retrieve(
            $row_id_token->getName(),
            $this->refinery->kindlyTo()->string()
        );

        $url_builder_with_row_id = $url_builder->withParameter($row_id_token, $question_id);

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
            $this->retrieveAction($action_token)
        );

        if (is_array($edit)) {
            return $edit;
        }

        $this->questions_repository->store($edit);
        return $this->buildEditStartView(
            $url_builder_with_row_id,
            $step_token,
            $edit
        );
    }

    private function showTable(
        \ilToolbarGUI $toolbar,
        URLBuilder $url_builder,
        URLBuilderToken $action_token,
        URLBuilderToken $row_id_token
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
            $this->questions_repository,
            $url_builder->withParameter($action_token, self::CMD_EDIT_QUESTION),
            $action_token,
            $row_id_token
        );
        return [
            $table->getFilter($url_builder->buildURI()->__toString()),
            $table->getTable()->withRequest($this->http->request())

        ];
    }

    private function processCreateAnswerForm(
        URLBuilder $url_builder,
        URLBuilderToken $action_token
    ): array {
        $form = $this->buildCreateAnswerForm($url_builder, $action_token)
            ->withRequest($this->http->request());
        $data = $form->getData();
        if ($data === null || $data['form_type'] === null) {
            return [$form];
        }
        return $data['form_type']->getEditView()->create();
    }

    private function retrieveAction(
        URLBuilderToken $action_token
    ): string {
        return $this->http->wrapper()->query()->retrieve(
            $action_token->getName(),
            $this->buildActionTrafo()
        );
    }

    private function buildActionTrafo(): Transformation
    {
        return $this->refinery->byTrying([
            $this->refinery->kindlyTo()->string(),
            $this->refinery->always('')
        ]);
    }

    private function initializeEditMode(
        URLBuilder $url_builder,
        URLBuilderToken $action_token,
        URLBuilderToken $row_id_token
    ): void {
        $this->global_screen->tool()->context()->current()->addAdditionalData(
            LayoutProvider::MODE_ENABLED,
            true
        );
        $this->global_screen->tool()->context()->current()->addAdditionalData(
            LayoutProvider::QUESTIONLIST_ENTRY,
            $this->buildQuestionListSlate($url_builder, $action_token, $row_id_token)
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
        URLBuilderToken $row_id_token,
        string $question_id,
        int $page_id
    ): void {
        $this->ctrl->setParameterByClass(
            \QstsQuestionPageGUI::class,
            $row_id_token->getName(),
            $question_id
        );
        $this->ctrl->setParameterByClass(
            \QstsQuestionPageGUI::class,
            QuestionEdit::PAGE_ID_PARAM_FOR_EDITOR,
            $page_id
        );
        $this->ctrl->setParameterByClass(
            $this->ctrl->getCmdClass(),
            $row_id_token->getName(),
            $question_id
        );
        $this->ctrl->setParameterByClass(
            $this->ctrl->getCmdClass(),
            QuestionEdit::PAGE_ID_PARAM_FOR_EDITOR,
            $page_id
        );
    }

    private function buildQuestionListSlate(
        URLBuilder $url_builder,
        URLBuilderToken $action_token,
        URLBuilderToken $row_id_token
    ): LegacySlate {
        return $this->ui_factory->mainControls()->slate()->legacy(
            $this->lng->txt('mainbar_button_label_questionlist'),
            $this->ui_factory->symbol()->icon()->standard('', '')->withAbbreviation('QL'),
            $this->ui_factory->legacy()->content(
                $this->ui_renderer->render(
                    $this->ui_factory->panel()->secondary()->listing(
                        $this->lng->txt('mainbar_button_label_questionlist'),
                        [
                            $this->buildItemGroupForQuestionListSlate($url_builder, $action_token, $row_id_token)
                        ]
                    )
                )
            )
        );
    }

    private function buildItemGroupForQuestionListSlate(
        URLBuilder $url_builder,
        URLBuilderToken $action_token,
        URLBuilderToken $row_id_token
    ): ItemGroup {
        return $this->ui_factory->item()->group(
            '',
            array_map(
                fn(QuestionImplementation $v): StandardItem => $this->ui_factory->item()->standard(
                    $v->toEditLink(
                        $this->ui_factory->link(),
                        $url_builder->withParameter($action_token, self::CMD_EDIT_QUESTION),
                        $row_id_token
                    )
                ),
                iterator_to_array($this->questions_repository->getAllQuestions())
            )
        );
    }

    private function buildEditStartView(
        URLBuilder $url_builder,
        URLBuilderToken $step_token,
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
            ''
        );
    }

    private function buildCreateAnswerForm(
        URLBuilder $url_builder,
        URLBuilderToken $action_token
    ): StandardForm {
        $if = $this->ui_factory->input();
        return $if->container()->form()->standard(
            $url_builder->withParameter($action_token, self::CMD_CREATE_ACTION_FORM)->buildURI()->__toString(),
            [
                'form_type' => $if->field()->section(
                    [
                        $if->field()->select(
                            $this->lng->txt('select_answer_form'),
                            array_reduce(
                                $this->questions_repository->getAvailableAnswerTypes(),
                                function (array $c, Type $v): array {
                                    $c[$v::class] = $v->getLabel($this->lng);
                                    return $c;
                                },
                                []
                            )
                        )->withRequired(true)
                    ],
                    $this->lng->txt('create_answer_form')
                )->withAdditionalTransformation(
                    $this->refinery->custom()->transformation(
                        fn(array $vs): ?Type => $this->questions_repository->getAvailableAnswerTypeByClass($vs[0])
                    )
                )
            ]
        );
    }
}
