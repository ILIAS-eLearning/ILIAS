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

namespace ILIAS\Questions\Presentation\Views;

use ILIAS\Questions\Administration\ConfigurationRepository;
use ILIAS\Questions\AnswerForm\Capabilities\Factory as CapabilitiesFactory;
use ILIAS\Questions\AnswerForm\Capabilities\RequiredCapabilities;
use ILIAS\Questions\AnswerForm\Definition;
use ILIAS\Questions\AnswerForm\Factory as AnswerFormFactory;
use ILIAS\Questions\AnswerForm\Properties as AnswerFormProperties;
use ILIAS\Questions\AnswerForm\Views\Edit as AnswerFormEditView;
use ILIAS\Questions\Attempt\Repository as AttemptRepository;
use ILIAS\Questions\Presentation\Definitions\DefaultEnvironment;
use ILIAS\Questions\Presentation\Definitions\Editability;
use ILIAS\Questions\Presentation\Definitions\Environment;
use ILIAS\Questions\Presentation\Definitions\ForImmediateStorage;
use ILIAS\Questions\Presentation\Definitions\ViewConfiguration;
use ILIAS\Questions\Presentation\Layout\Async;
use ILIAS\Questions\Presentation\Layout\EditForm;
use ILIAS\Questions\Presentation\Layout\Factory as LayoutFactory;
use ILIAS\Questions\Presentation\Layout\GlobalScreen\LayoutProvider;
use ILIAS\Questions\Presentation\Layout\QuestionsTable;
use ILIAS\Questions\Presentation\Layout\Viewable;
use ILIAS\Questions\Question\Persistence\Repository;
use ILIAS\Questions\Question\Question;
use ILIAS\Questions\UserSettings\CreateModes;
use ILIAS\Data\URI;
use ILIAS\Data\UUID\Factory as UuidFactory;
use ILIAS\Data\UUID\Uuid;
use ILIAS\HTTP\Services as HTTP;
use ILIAS\Language\Language;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UICore\GlobalTemplate;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Item\Group as ItemGroup;
use ILIAS\UI\Component\MainControls\Slate\Legacy as LegacySlate;
use ILIAS\Style\Content\Service as ContentStyle;
use ILIAS\GlobalScreen\Services as GlobalScreen;

class Edit
{
    private const string ACTION_CREATE_QUESTION = 'create';
    public const string ACTION_EDIT_QUESTION = 'edit';
    public const string ACTION_CLONE_QUESTION = 'clone';
    public const string ACTION_DELETE_QUESTIONS = 'delete';
    private const string ACTION_CREATE_ANSWER_FORM = 'create_af';
    public const string ACTION_OTHER_ANSWER_FORM = 'other_af';

    private Editability $editability = Editability::Full;
    private bool $ordering_enabled = false;

    private readonly RequiredCapabilities $required_capabilities;

    /**
     * @param list<class-string<Capability>> $capability_identifiers
     */
    public function __construct(
        private readonly Language $lng,
        private readonly \ilObjUser $current_user,
        private readonly Refinery $refinery,
        private readonly UIFactory $ui_factory,
        private readonly UIRenderer $ui_renderer,
        private readonly GlobalScreen $global_screen,
        private readonly GlobalTemplate $global_tpl,
        private readonly ContentStyle $content_style,
        private readonly \ilCtrl $ctrl,
        private readonly HTTP $http,
        private readonly \ilTabsGUI $tabs_gui,
        private readonly \ilUIService $ui_services,
        private readonly UuidFactory $uuid_factory,
        private readonly ConfigurationRepository $configuration_repository,
        private readonly AnswerFormFactory $answer_form_factory,
        private readonly Repository $questions_repository,
        private readonly AttemptRepository $attempt_repository,
        private readonly LayoutFactory $layout_factory,
        private readonly CapabilitiesFactory $capabilities_factory,
        array $capability_identifiers,
        private readonly int $owner_object_id
    ) {
        $this->required_capabilities = $this->capabilities_factory->get(
            $capability_identifiers
        );
    }

    public function withEditable(
        Editability $editability
    ): self {
        $clone = clone $this;
        $clone->editability = $editability;
        return $clone;
    }

    public function withOrderingEnabled(
        bool $enable
    ): self {
        $clone = clone $this;
        $clone->ordering_enabled = $enable;
        return $clone;
    }

    public function getUI(
        URI $base_uri,
        int $ref_id
    ): array|Component {
        $this->content_style->gui()->addCss(
            $this->global_tpl,
            $ref_id
        );

        $environment = $this->buildEnvironment($base_uri);

        $view = match($environment->getAction()) {
            self::ACTION_CREATE_QUESTION => $this->createQuestion(
                $environment->withIsInCreationContext(true)
            ),
            self::ACTION_EDIT_QUESTION => $this->editQuestion($environment),
            self::ACTION_CLONE_QUESTION => $this->cloneQuestion($environment),
            self::ACTION_DELETE_QUESTIONS => $this->deleteQuestions($environment),
            default => $this->showTable($environment)
        };

        if ($view instanceof Async) {
            $view->render($this->ui_renderer);
        }

        return $view->getUI();
    }

    public function forwardPageCmds(
        URI $base_uri,
        int $ref_id
    ): void {
        $environment = $this->buildEnvironment($base_uri);

        if ($this->ctrl->getCmd() === 'insert'
            && $environment->getAction() === self::ACTION_DELETE_QUESTIONS) {
            $this->deleteQuestions($environment);
            return;
        }

        $this->initializeEditMode($environment);
        $environment->preserveParametersForPageEditorCmds();

        $this->content_style->gui()->addCss(
            $this->global_tpl,
            $ref_id
        );

        $this->global_tpl->setContent(
            $this->ctrl->forwardCommand(
                new \QstsQuestionPageGUI(
                    $this->questions_repository->getForQuestionId(
                        $environment->getQuestionId()
                    ),
                    $this->owner_object_id,
                    $this->required_capabilities,
                    new ViewConfiguration(
                        true,
                        false,
                        false,
                        false
                    )
                )->withEditView(
                    $this
                )->withReturnURI(
                    $environment
                        ->withActionParameter(self::ACTION_EDIT_QUESTION)
                        ->withQuestionIdParameter($environment->getQuestionId())
                        ->getUrlBuilder()
                        ->buildURI()
                )
            )
        );
    }

    public function getCreateAnswerForm(
        URI $base_uri,
        Question $question,
        \ilPCAnswerForm $content_object
    ): array|Component {
        $environment = $this->buildEnvironment($base_uri)
            ->withIsInCreationContext(true)
            ->withQuestionIdParameter($question->getId());

        $environment->setEditAnswerFormBackTarget();

        if ($this->configuration_repository->isCreateModeSimple($environment)) {
            $environment = $environment->withCreateModeParameter();
        }

        $answer_form_type_class_hash = $environment->getTypeClassHash();

        if ($answer_form_type_class_hash !== '') {
            $type_definition = $this->answer_form_factory
                ->getTypeDefinitionFromSelectValue($answer_form_type_class_hash);

            return $this->forwardCreateAnswerFormCmd(
                $environment->withAnswerFormProperties(
                    $type_definition->buildProperties(
                        $this->answer_form_factory->getDefaultTypeGenericProperties(
                            $question->getId(),
                            $type_definition,
                            $environment->getAnswerFormId(),
                        ),
                        null
                    )
                )->withAnswerFormTypeHashParameter($answer_form_type_class_hash),
                $question,
                $content_object,
                $type_definition->getEditView()
            )->getUI();
        }

        return match($environment->getAction()) {
            self::ACTION_CREATE_ANSWER_FORM => $this->processCreateAnswerForm(
                $environment,
                $question,
                $content_object
            )->getUI(),
            default => $this->buildCreateAnswerForm($environment)->getUI()
        };
    }

    public function getEditAnswerForm(
        URI $base_uri,
        Question $question,
        AnswerFormProperties $answer_form_properties,
        Definition $type_definition
    ): array|Component {
        $environment = $this->buildEnvironment($base_uri)
            ->withAnswerFormProperties($answer_form_properties)
            ->withQuestionIdParameter($question->getId());

        $action = $environment->getAction();
        $edit_view = $type_definition->getEditView();

        $from_capabilites = $this->required_capabilities->edit(
            $this->tabs_gui,
            $environment,
            $edit_view,
            $action
        );

        if ($from_capabilites instanceof AnswerFormProperties) {
            $this->updateAnswerFormAndRedirect(
                $environment,
                $question,
                $from_capabilites
            );
        }

        if ($from_capabilites instanceof Async) {
            $from_capabilites->render($this->ui_renderer);
        }

        if ($from_capabilites instanceof Viewable) {
            return $from_capabilites->getUI();
        }

        if ($action === self::ACTION_OTHER_ANSWER_FORM) {
            return $this->processOtherAnswerFormAction(
                $environment->withActionParameter(self::ACTION_OTHER_ANSWER_FORM),
                $question,
                $edit_view
            )->getUI();
        }

        $from_edit_view = $edit_view->edit($environment);
        if ($from_edit_view instanceof EditForm
            && $this->required_capabilities->additionalAnswerFormStepsRequired()) {
            return $from_edit_view->withIsFinalStep(false)->getUI();
        }

        if ($from_edit_view instanceof Async) {
            $from_edit_view->render($this->ui_renderer);
        }

        if ($from_edit_view instanceof Viewable) {
            return $from_edit_view->getUI();
        }

        if ($from_edit_view instanceof ForImmediateStorage) {
            $this->updateAnswerFormAndRedirect(
                $environment,
                $question,
                $from_edit_view->unpack()
            );
        }

        $return_form_step_actions = $this->required_capabilities
            ->doFirstFormStepAction(
                $environment->withAnswerFormProperties($from_edit_view),
                $edit_view
            );

        if ($return_form_step_actions instanceof Async) {
            $return_form_step_actions->render($this->ui_renderer);
        }

        if ($return_form_step_actions instanceof Viewable) {
            return $return_form_step_actions->getUI();
        }

        $this->updateAnswerFormAndRedirect(
            $environment,
            $question,
            $from_edit_view
        );
    }

    private function createQuestion(
        DefaultEnvironment $environment
    ): EditForm {
        $this->initializeEditMode($environment);
        $this->tabs_gui->setBackTarget(
            $this->lng->txt('cancel'),
            $environment->withDefaultSubAction()->getUrlBuilder()
                ->buildURI()
                ->__toString()
        );

        $create = $this->questions_repository->getNew(
            $environment->getParentObjId()
        )->getEditView(
            $this->current_user,
            $this->ctrl,
            $this->http->wrapper()->post(),
            $this->ui_renderer,
            $this->uuid_factory,
            $this->configuration_repository,
            $this->attempt_repository,
            $this->required_capabilities
        )->create(
            $environment->withActionParameter(self::ACTION_CREATE_QUESTION)
        );

        if ($create instanceof EditForm) {
            return $create;
        }

        $this->questions_repository->create([$create]);
        $this->ctrl->redirectToURL(
            $this->buildAfterQuestionCreationRedirectUri(
                $environment,
                $create->getCreateMode(),
                $create->getId()
            )
        );

    }

    private function editQuestion(
        DefaultEnvironment $environment
    ): EditForm {
        $this->initializeEditMode($environment);
        $this->tabs_gui->setBackTarget(
            $this->lng->txt('back'),
            $environment->withDefaultSubAction()->getUrlBuilder()->buildURI()->__toString()
        );

        $question_id = $environment->getQuestionId();
        $question = $this->questions_repository->getForQuestionId($question_id);
        $environment_with_question_parameter = $environment
            ->withQuestionIdParameter($question_id);

        $edit = $question->getEditView(
            $this->current_user,
            $this->ctrl,
            $this->http->wrapper()->post(),
            $this->ui_renderer,
            $this->uuid_factory,
            $this->configuration_repository,
            $this->attempt_repository,
            $this->required_capabilities
        )->edit(
            $environment_with_question_parameter
                ->withActionParameter(self::ACTION_EDIT_QUESTION)
        );

        if ($edit instanceof EditForm) {
            return $edit;
        }

        $this->questions_repository->update([$edit]);
        return $this->buildEditStartView(
            $environment_with_question_parameter
                ->withDefaultSubAction()
                ->withActionParameter(self::ACTION_EDIT_QUESTION),
            $edit
        );
    }

    private function cloneQuestion(
        DefaultEnvironment $environment
    ): QuestionsTable {
        $question_to_clone = $environment->getQuestionIds();

        if (!isset($question_to_clone[0])) {
            return $this->showTable($environment);
        }

        $question = $this->questions_repository->getForQuestionId(
            $question_to_clone[0]
        );
        $this->questions_repository->create(
            [
                $question->clone(
                    $this->uuid_factory,
                    [
                        'parent_obj_id' => $environment->getParentObjId(),
                        'new_question_page_id' => $this->questions_repository
                            ->getNextAvailableQuestionPageId(),
                        'required_capabilities' => $this->required_capabilities
                    ]
                )->withParentObjId($environment->getParentObjId())
            ]
        );

        $this->ctrl->redirectToURL(
            $environment->getUrlBuilder()->buildURI()->__toString()
        );
    }

    private function deleteQuestions(
        DefaultEnvironment $environment
    ): Async {
        $question_ids = $environment->getQuestionIds();

        if ($question_ids === null) {
            return $environment->getPresentationFactory()->getAsync(
                $this->ui_factory->messageBox()->failure(
                    $this->lng->txt('msg_no_questions_selected')
                )
            );
        }

        if ($environment->getSubAction() === self::ACTION_DELETE_QUESTIONS) {
            $this->deleteSelectedQuestions($question_ids);
            $this->ctrl->redirectToURL(
                $environment->getUrlBuilder()->buildURI()->__toString()
            );
        }

        return $environment->getPresentationFactory()->getAsync(
            $this->ui_factory->modal()->interruptive(
                $this->lng->txt('confirm'),
                $this->lng->txt('confirm_delete_questions'),
                $environment->withActionParameter(
                    self::ACTION_DELETE_QUESTIONS
                )->withSubActionParameter(
                    self::ACTION_DELETE_QUESTIONS
                )->getUrlBuilder()->buildURI()->__toString()
            )->withAffectedItems(
                $this->buildAffectedItems($question_ids)
            )
        );
    }

    private function showTable(
        DefaultEnvironment $environment
    ): QuestionsTable {
        $this->questions_repository->migrateQuestionPages();

        return new QuestionsTable(
            $this->ui_services,
            $this->answer_form_factory,
            $this->questions_repository,
            $environment,
            $this->required_capabilities
        )->withCreateQuestionButton(
            $this->ui_factory->button()->primary(
                $this->lng->txt('create'),
                $environment->withActionParameter(self::ACTION_CREATE_QUESTION)
                    ->getUrlBuilder()
                    ->buildURI()
                    ->__toString()
            )
        );
    }

    private function processCreateAnswerForm(
        DefaultEnvironment $environment,
        Question $question,
        \ilPCAnswerForm $content_object
    ): EditForm {
        $form = $this->buildCreateAnswerForm($environment)
            ->withRequest($this->http->request());

        $data = $form->getData();
        if ($data === null) {
            return $form;
        }

        if ($this->configuration_repository->isCreateModeSimple($environment)) {
            $question_page = new \QstsQuestionPage($question->getPageId());
            $question_page->setQuestion($question);
            $question_page->addQuestionText($data['question_text']);
        }

        $type_definition = $data['type'];
        return $this->forwardCreateAnswerFormCmd(
            $environment->withAnswerFormProperties(
                $type_definition->buildProperties(
                    $this->answer_form_factory->getDefaultTypeGenericProperties(
                        $question->getId(),
                        $type_definition
                    ),
                    null
                )
            )->withAnswerFormTypeHashParameter(
                $this->answer_form_factory->getHashedClass($type_definition::class)
            ),
            $question,
            $content_object,
            $type_definition->getEditView()
        );
    }

    private function forwardCreateAnswerFormCmd(
        DefaultEnvironment $environment,
        Question $question,
        \ilPCAnswerForm $content_object,
        AnswerFormEditView $answer_form_edit_view
    ): ?EditForm {
        $action = $environment->getAction();

        $from_capabilites = $this->required_capabilities->edit(
            $this->tabs_gui,
            $environment,
            $answer_form_edit_view,
            $action
        );

        if ($from_capabilites instanceof Async) {
            $from_capabilites->render($this->ui_renderer);
        }

        if ($from_capabilites instanceof EditForm) {
            return $this->addSaveAndNewToAnswerFormCreateIfNeeded(
                $environment,
                $from_capabilites
            );
        }

        if ($from_capabilites instanceof AnswerFormProperties) {
            $this->createAnswerFormAndRedirect(
                $environment,
                $question->withAnswerFormProperties($from_capabilites),
                $content_object
            );
        }

        $from_edit_view = $answer_form_edit_view->create(
            $environment->withAnswerFormIdParameter(
                $environment->getAnswerFormId()
            )
        );

        if ($from_edit_view instanceof Async) {
            return $from_edit_view->render($this->ui_renderer);
            ;
        }

        if ($from_edit_view instanceof EditForm) {
            return $this->addSaveAndNewToAnswerFormCreateIfNeeded(
                $environment,
                $this->required_capabilities->additionalAnswerFormStepsRequired()
                    ? $from_edit_view->withIsFinalStep(false)
                    : $from_edit_view
            );
        }

        $from_capabilities_first_step = $this->required_capabilities
            ->doFirstFormStepAction(
                $environment->withAnswerFormProperties($from_edit_view),
                $answer_form_edit_view
            );

        if ($from_capabilities_first_step instanceof EditForm) {
            return $this->addSaveAndNewToAnswerFormCreateIfNeeded(
                $environment,
                $from_capabilities_first_step
            );
        }

        $this->createAnswerFormAndRedirect(
            $environment,
            $question->withAnswerFormProperties($from_capabilities_first_step),
            $content_object
        );
    }

    private function processOtherAnswerFormAction(
        Environment $environment,
        Question $question,
        AnswerFormEditView $edit_view
    ): Viewable {
        $from_edit_view = $edit_view->other($environment);

        if ($from_edit_view instanceof Viewable) {
            return $from_edit_view;
        }

        if ($from_edit_view instanceof Async) {
            $from_edit_view->render($this->ui_renderer);
        }

        $this->updateAnswerFormAndRedirect(
            $environment,
            $question,
            $from_edit_view
        );
    }

    private function updateAnswerFormAndRedirect(
        Environment $environment,
        Question $question,
        AnswerFormProperties $properties
    ): never {
        $this->questions_repository->update(
            [$question->withAnswerFormProperties($properties)]
        );

        $this->required_capabilities->onAnswerFormUpdate($properties);

        $this->ctrl->redirectToURL(
            $environment->getUrlBuilder()->buildURI()->__toString()
        );
    }

    private function createAnswerFormAndRedirect(
        Environment $environment,
        Question $question,
        \ilPCAnswerForm $content_object
    ): never {
        $this->questions_repository->update(
            [$question]
        );

        $content_object->create(
            $environment->getAnswerFormProperties()->getAnswerFormId()
        );
        $content_object->getPage()->update();

        $this->ctrl->redirectToURL(
            $this->buildAfterAnswerFormCreationRedirectUri($environment)
        );

    }

    private function initializeEditMode(
        DefaultEnvironment $environment
    ): void {
        $this->tabs_gui->clearTargets();

        $this->global_screen->tool()->context()->current()->addAdditionalData(
            LayoutProvider::MODE_ENABLED,
            true
        );
        $this->global_screen->tool()->context()->current()->addAdditionalData(
            LayoutProvider::QUESTIONLIST_ENTRY,
            $this->buildQuestionListSlate($environment)
        );
        $this->global_screen->tool()->context()->current()->addAdditionalData(
            LayoutProvider::URL_CLOSE_MODE_INFO,
            $environment->getUrlBuilder()->buildURI()
        );
        $this->global_screen->tool()->context()->current()->addAdditionalData(
            LayoutProvider::URL_CREATE_QUESTION,
            $environment
            ->withActionParameter(self::ACTION_CREATE_QUESTION)
            ->getUrlBuilder()
            ->buildURI()
        );
    }

    private function buildQuestionListSlate(
        DefaultEnvironment $environment
    ): LegacySlate {
        return $this->ui_factory->mainControls()->slate()->legacy(
            $this->lng->txt('questionlist'),
            $this->ui_factory->symbol()->icon()->standard('', '')->withAbbreviation('QL'),
            $this->ui_factory->legacy()->content(
                $this->ui_renderer->render(
                    $this->ui_factory->panel()->secondary()->listing(
                        $this->lng->txt('questionlist'),
                        [
                            $this->buildItemGroupForQuestionListSlate($environment)
                        ]
                    )
                )
            )
        );
    }

    private function buildItemGroupForQuestionListSlate(
        DefaultEnvironment $environment
    ): ItemGroup {
        return $this->ui_factory->item()->group(
            '',
            $this->builEditLinksForQuestionListSlate($environment)
        );
    }

    private function builEditLinksForQuestionListSlate(
        Environment $environment
    ): array {
        $links = [];
        foreach ($this->questions_repository->getQuestionDataOnlyForAllQuestions() as $question) {
            $links[] = $this->ui_factory->item()->standard(
                $question->toEditLink(
                    $this->ui_factory->link(),
                    $environment->withActionParameter(self::ACTION_EDIT_QUESTION)
                )
            );
        }
        return $links;
    }

    private function buildEditStartView(
        DefaultEnvironment $environment,
        Question $question
    ): EditForm {
        return $question->getEditView(
            $this->current_user,
            $this->ctrl,
            $this->http->wrapper()->post(),
            $this->ui_renderer,
            $this->uuid_factory,
            $this->configuration_repository,
            $this->attempt_repository,
            $this->required_capabilities
        )->edit(
            $environment
        );
    }

    private function buildCreateAnswerForm(
        DefaultEnvironment $environment
    ): EditForm {
        $if = $this->ui_factory->input();

        $inputs = [];
        if ($this->configuration_repository->isCreateModeSimple($environment)) {
            $inputs['question_text'] = $if->field()->textarea(
                $this->lng->txt('question_text')
            )->withRequired(true);
        }

        return $environment->getPresentationFactory()->getEditForm(
            $if->field()->section(
                $inputs + [
                    'type' => $if->field()->select(
                        $this->lng->txt('select_answer_form_type'),
                        $this->answer_form_factory
                            ->getAnswerFormTypesArrayForSelect($this->lng)
                    )->withRequired(true)
                    ->withAdditionalTransformation(
                        $this->refinery->custom()->transformation(
                            fn(string $v): ?Definition => $this->answer_form_factory
                                ->getTypeDefinitionFromSelectValue($v)
                        )
                    )
                ],
                $this->lng->txt('create_answer_form')
            ),
            $environment->withActionParameter(
                self::ACTION_CREATE_ANSWER_FORM
            )->getUrlBuilder(),
            null
        );
    }

    /**
     *
     * @param string|array<\ILIAS\Data\UUID\Uuid> $question_ids
     * @return array<\ILIAS\UI\Component\Modal\InterruptiveItem\Standard>
     */
    private function buildAffectedItems(
        string|array $question_ids
    ): array {
        $questions = $question_ids === 'ALL_OBJECTS'
                ? $this->questions_repository->getQuestionDataOnlyForAllQuestions()
                : $this->questions_repository->getQuestionDataOnlyForQuestionIds($question_ids);
        $affected_items = [];
        foreach ($questions as $question) {
            $affected_items[] = $this->ui_factory->modal()->interruptiveItem()->standard(
                $question->getId()->toString(),
                $question->getTitle()
            );
        }
        return $affected_items;
    }

    private function deleteSelectedQuestions(
        array $question_ids
    ): void {
        $questions_to_delete = [];
        foreach ($this->questions_repository->getForQuestionIds($question_ids) as $question) {
            if (count($questions_to_delete) < 100) {
                $questions_to_delete[] = $question;
                continue;
            }

            $this->questions_repository->delete($questions_to_delete);
            $questions_to_delete = [];
        }

        $this->questions_repository->delete($questions_to_delete);
    }

    private function addSaveAndNewToAnswerFormCreateIfNeeded(
        Environment $environment,
        EditForm $edit_form
    ): EditForm {
        if ($edit_form->isFinalStep() && $environment->isCreateModeSimple()) {
            $edit_form = $edit_form->withAdditionalAction(
                $environment->buildURLBuilderTokenForCreateAndNew(),
                '1',
                $this->lng->txt('save_and_new')
            );
        }

        return $edit_form;
    }

    private function buildEnvironment(
        URI $base_uri,
    ): DefaultEnvironment {
        return new DefaultEnvironment(
            $this->ctrl,
            $this->http,
            $this->refinery,
            $this->lng,
            $this->tabs_gui,
            $this->uuid_factory,
            $this->layout_factory,
            $this->editability,
            $this->required_capabilities,
            $this->owner_object_id,
            $base_uri
        );
    }

    private function buildAfterQuestionCreationRedirectUri(
        DefaultEnvironment $environment,
        CreateModes $create_mode,
        Uuid $question_uuid
    ): string {
        if ($create_mode !== CreateModes::Simple) {
            return $environment
                ->withDefaultSubAction()
                ->withActionParameter(self::ACTION_EDIT_QUESTION)
                ->withQuestionIdParameter($question_uuid)
                ->getUrlBuilder()
                ->buildURI()
                ->__toString();
        }

        $environment->setParamtersForSimpleCreateCmd($question_uuid);

        return $this->ctrl->getLinkTargetByClass(
            [
                \QstsQuestionPageGUI::class,
                \ilPageEditorGUI::class,
                \ilPCAnswerFormGUI::class
            ],
            'insert'
        );
    }

    private function buildAfterAnswerFormCreationRedirectUri(
        DefaultEnvironment $environment,
    ): string {
        if (!$this->configuration_repository->isCreateModeSimple($environment)) {
            return $this->ctrl->getLinkTargetByClass(
                \QstsQuestionPageGUI::class,
                'edit'
            );
        }

        $additonal_data = $this->global_screen
            ->tool()
            ->context()
            ->current()
            ->getAdditionalData();

        if ($environment->isCreateAndNewAction()) {
            return $additonal_data
                ->get(LayoutProvider::URL_CREATE_QUESTION)
                ->__toString();
        }

        return $additonal_data
            ->get(LayoutProvider::URL_CLOSE_MODE_INFO)
            ->__toString();
    }
}
