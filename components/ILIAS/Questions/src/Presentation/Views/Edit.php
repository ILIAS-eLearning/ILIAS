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

use ILIAS\Questions\AnswerForm\Capabilities\Action;
use ILIAS\Questions\AnswerForm\Capabilities\Capability;
use ILIAS\Questions\AnswerForm\Capabilities\Factory as CapabilitesFactory;
use ILIAS\Questions\AnswerForm\Definition;
use ILIAS\Questions\AnswerForm\Factory as AnswerFormFactory;
use ILIAS\Questions\AnswerForm\Properties as AnswerFormProperties;
use ILIAS\Questions\AnswerForm\Views\Edit as AnswerFormEditView;
use ILIAS\Questions\Administration\ConfigurationRepository;
use ILIAS\Questions\Presentation\Layout\Async;
use ILIAS\Questions\Presentation\Layout\EditForm;
use ILIAS\Questions\Presentation\Layout\Factory as LayoutFactory;
use ILIAS\Questions\Presentation\Layout\Renderable;
use ILIAS\Questions\Presentation\Definitions\Editability;
use ILIAS\Questions\Presentation\Definitions\Environment;
use ILIAS\Questions\Presentation\Definitions\EnvironmentImplementation;
use ILIAS\Questions\Presentation\Layout\QuestionsTable;
use ILIAS\Questions\Presentation\Layout\GlobalScreen\LayoutProvider;
use ILIAS\Questions\Question\Persistence\Repository;
use ILIAS\Questions\Question\QuestionImplementation;
use ILIAS\Questions\UserSettings\CreateModes;
use ILIAS\Data\URI;
use ILIAS\Data\UUID\Factory as UuidFactory;
use ILIAS\Data\UUID\Uuid;
use ILIAS\UICore\GlobalTemplate;
use ILIAS\Language\Language;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\HTTP\Services as HTTP;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\UI\Component\Item\Group as ItemGroup;
use ILIAS\UI\Component\MainControls\Slate\Legacy as LegacySlate;
use ILIAS\Style\Content\Service as ContentStyle;
use ILIAS\GlobalScreen\Services as GlobalScreen;

class Edit
{
    private const string ACTION_CREATE_QUESTION = 'create';
    public const string ACTION_EDIT_QUESTION = 'edit';
    public const string ACTION_DELETE_QUESTIONS = 'delete';
    private const string ACTION_CREATE_ANSWER_FORM = 'create_af';
    public const string ACTION_OTHER_ANSWER_FORM = 'other_af';

    private array $required_capabilities = [];
    private Editability $editability = Editability::Full;
    private bool $ordering_enabled = false;

    public function __construct(
        private readonly Language $lng,
        private readonly ConfigurationRepository $configuration_repository,
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
        private readonly CapabilitesFactory $capabilities_factory,
        private readonly AnswerFormFactory $answer_form_factory,
        private readonly Repository $questions_repository,
        private readonly LayoutFactory $definitions_factory
    ) {
    }

    public function withRequiredCapabilities(
        array $capability_class_names
    ): self {
        $clone = clone $this;
        $clone->required_capabilities = $this->buildCapabilities(
            $capability_class_names
        );
        return $clone;
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

    public function show(
        URI $base_uri,
        int $obj_id,
        int $ref_id
    ): Async|QuestionsTable|EditForm {
        $this->content_style->gui()->addCss(
            $this->global_tpl,
            $ref_id
        );

        $environment = $this->buildEnvironment(
            $base_uri,
            $obj_id
        );

        return match($environment->getAction()) {
            self::ACTION_CREATE_QUESTION => $this->createQuestion(
                $environment->withIsInCreationContext(true)
            ),
            self::ACTION_EDIT_QUESTION => $this->editQuestion($environment),
            self::ACTION_DELETE_QUESTIONS => $this->deleteQuestions($environment),
            default => $this->showTable($environment)
        };
    }

    public function forwardPageCmds(
        \ilGlobalTemplateInterface $tpl,
        URI $base_uri,
        int $obj_id,
        int $ref_id
    ): void {
        $environment = $this->buildEnvironment(
            $base_uri,
            $obj_id
        );

        if ($this->ctrl->getCmd() === 'insert'
            && $environment->getAction() === self::ACTION_DELETE_QUESTIONS) {
            $this->deleteQuestions($environment);
            return;
        }

        $this->initializeEditMode($environment);
        $environment->preserveParametersForPageEditorCmds();

        $this->content_style->gui()->addCss(
            $tpl,
            $ref_id
        );

        $tpl->setContent(
            $this->ctrl->forwardCommand(
                new \QstsQuestionPageGUI(
                    $this->questions_repository->getForQuestionId(
                        $environment->getQuestionId()
                    ),
                    $obj_id,
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

    public function createAnswerForm(
        URI $base_uri,
        int $obj_id,
        QuestionImplementation $question,
        \ilPCAnswerForm $content_object
    ): EditForm {
        $environment = $this->buildEnvironment(
            $base_uri,
            $obj_id
        )->withIsInCreationContext(true)
        ->withActionParameter(self::ACTION_CREATE_ANSWER_FORM)
        ->withQuestionIdParameter($question->getId());


        $environment->setEditAnswerFormBackTarget();

        if ($this->configuration_repository->isCreateModeSimple($environment)) {
            $environment = $environment->withCreateModeParameter();
        }

        $answer_form_type_class_hash = $environment->getTypeClassHash();

        if ($answer_form_type_class_hash !== '') {
            $type_definition = $this->answer_form_factory
                ->buildTypeDefinitionFromSelectValue($answer_form_type_class_hash);

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
            );
        }

        return match($environment->getAction()) {
            self::ACTION_CREATE_ANSWER_FORM => $this->processCreateAnswerForm(
                $environment,
                $question,
                $content_object
            ),
            default => $this->buildCreateAnswerForm($environment)
        };
    }

    public function editAnswerForm(
        URI $base_uri,
        int $obj_id,
        QuestionImplementation $question,
        AnswerFormProperties $answer_form_properties,
        Definition $type_definition
    ): Async|Renderable {
        $environment = $this->buildEnvironment(
            $base_uri,
            $obj_id
        )->withAnswerFormProperties($answer_form_properties)
        ->withQuestionIdParameter($question->getId());

        $capability_actions = array_filter(
            array_map(
                fn(Capability $v): ?Action => $v->getEditAction(),
                $this->required_capabilities
            )
        );

        $environment->setEditAnswerFormTabs(
            $capability_actions
        );

        $action = $environment->getAction();

        $capability_action = array_filter(
            $capability_actions,
            fn(Action $v): bool => $v->isThis($action)
        );
        if ($capability_action !== []) {
            $capability_action[0]->activateTab($this->tabs_gui);
            return $capability_action[0]->getCapability()->edit(
                $environment->withActionParameter($action)
            );
        }

        $edit_view = $type_definition->getEditView();

        if ($action === self::ACTION_OTHER_ANSWER_FORM) {
            $environment = $environment->withActionParameter(self::ACTION_OTHER_ANSWER_FORM);
            $next = $edit_view->other($environment);
        } else {
            $next = $edit_view->edit($environment);
        }

        if (!($next instanceof AnswerFormProperties)) {
            return $next;
        }

        $this->questions_repository->update(
            [$question->withAnswerFormProperties($next)]
        );

        foreach ($this->required_capabilities as $capability) {
            $capability->onAnswerFormUpdate($next);
        }

        $this->ctrl->redirectToURL(
            $environment->getUrlBuilder()->buildURI()->__toString()
        );
    }

    private function createQuestion(
        EnvironmentImplementation $environment
    ): EditForm {
        $this->initializeEditMode($environment);
        $this->tabs_gui->setBackTarget(
            $this->lng->txt('cancel'),
            $environment->withDefaultStep()->getUrlBuilder()
                ->buildURI()
                ->__toString()
        );

        $create = $this->questions_repository->getNew(
            $environment->getObjId()
        )->getEditView(
            $this->configuration_repository,
            $this->current_user,
            $this->ctrl
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
        EnvironmentImplementation $environment
    ): EditForm {
        $this->initializeEditMode($environment);
        $this->tabs_gui->setBackTarget(
            $this->lng->txt('back'),
            $environment->withDefaultStep()->getUrlBuilder()->buildURI()->__toString()
        );

        $question_id = $environment->getQuestionId();
        $question = $this->questions_repository->getForQuestionId($question_id);
        $environment_with_question_parameter = $environment
            ->withQuestionIdParameter($question_id);

        $edit = $question->getEditView(
            $this->configuration_repository,
            $this->current_user,
            $this->ctrl
        )->edit(
            $environment_with_question_parameter
                ->withActionParameter(self::ACTION_EDIT_QUESTION),
            $question->getParticipantView()
        );

        if ($edit instanceof EditForm) {
            return $edit;
        }

        $this->questions_repository->update([$edit]);
        return $this->buildEditStartView(
            $environment_with_question_parameter
                ->withDefaultStep()
                ->withActionParameter(self::ACTION_EDIT_QUESTION),
            $edit
        );
    }

    private function deleteQuestions(
        EnvironmentImplementation $environment
    ): Async {
        $question_ids = $environment->getQuestionIds();

        if ($question_ids === null) {
            return $environment->getPresentationFactory()->getAsync(
                $this->ui_factory->messageBox()->failure(
                    $this->lng->txt('msg_no_questions_selected')
                )
            );
        }

        if ($environment->getStep() === self::ACTION_DELETE_QUESTIONS) {
            $this->deleteSelectedQuestions($question_ids);
            $this->ctrl->redirectToURL(
                $environment->getUrlBuilder()->buildURI()->__toString()
            );
        }

        return $environment->getPresentationFactory()->getAsync(
            $this->ui_factory->modal()->interruptive(
                $this->lng->txt('confirm'),
                $this->lng->txt('qpl_confirm_delete_questions'),
                $environment->withActionParameter(
                    self::ACTION_DELETE_QUESTIONS
                )->withStepParameter(
                    self::ACTION_DELETE_QUESTIONS
                )->getUrlBuilder()->buildURI()->__toString()
            )->withAffectedItems(
                $this->buildAffectedItems($question_ids)
            )
        );
    }

    private function showTable(
        EnvironmentImplementation $environment
    ): QuestionsTable {
        return new QuestionsTable(
            $this->ui_services,
            $this->answer_form_factory,
            $this->questions_repository,
            $environment
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
        EnvironmentImplementation $environment,
        QuestionImplementation $question,
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
        EnvironmentImplementation $environment,
        QuestionImplementation $question,
        \ilPCAnswerForm $content_object,
        AnswerFormEditView $answer_form_edit_view
    ): ?EditForm {
        $create = $answer_form_edit_view->create(
            $environment->withAnswerFormIdParameter(
                $environment->getAnswerFormId()
            )
        );

        if ($create instanceof EditForm) {
            return $create->isFinalStep()
                ? $create->withAdditionalAction(
                    $environment->buildURLBuilderTokenForCreateAndNew(),
                    '1',
                    $this->lng->txt('save_and_new')
                ) : $create;
        }

        $this->questions_repository->create(
            [$question->withAnswerFormProperties($create)]
        );

        $content_object->create($create->getAnswerFormId());
        $content_object->getPage()->update();

        $this->ctrl->redirectToURL(
            $this->buildAfterAnswerFormCreationRedirectUri($environment)
        );
    }

    private function initializeEditMode(
        EnvironmentImplementation $environment
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
        EnvironmentImplementation $environment
    ): LegacySlate {
        return $this->ui_factory->mainControls()->slate()->legacy(
            $this->lng->txt('mainbar_button_label_questionlist'),
            $this->ui_factory->symbol()->icon()->standard('', '')->withAbbreviation('QL'),
            $this->ui_factory->legacy()->content(
                $this->ui_renderer->render(
                    $this->ui_factory->panel()->secondary()->listing(
                        $this->lng->txt('mainbar_button_label_questionlist'),
                        [
                            $this->buildItemGroupForQuestionListSlate($environment)
                        ]
                    )
                )
            )
        );
    }

    private function buildItemGroupForQuestionListSlate(
        EnvironmentImplementation $environment
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
        EnvironmentImplementation $environment,
        QuestionImplementation $question
    ): EditForm {
        return $question->getEditView(
            $this->configuration_repository,
            $this->current_user,
            $this->ctrl
        )->edit(
            $environment,
            $question->getParticipantView()
        );
    }

    private function buildCreateAnswerForm(
        EnvironmentImplementation $environment
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
                                ->buildTypeDefinitionFromSelectValue($v)
                        )
                    )
                ],
                $this->lng->txt('create_answer_form')
            ),
            $environment->getUrlBuilder(),
            null,
            false
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

    /**
     * @param list<string> $capabilities
     * @return list<\ILIAS\Questions\AnswerForm\Capabilities\Capability>
     */
    private function buildCapabilities(
        array $capabilities
    ): array {
        return array_map(
            function (string $v): Capability {
                $capability = $this->capabilities_factory->get($v);
                if ($capability === null) {
                    throw new \InvalidArgumentException(
                        "The capability {$v} does not exist."
                    );
                }
                return $capability;
            },
            $capabilities
        );
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

    private function buildEnvironment(
        URI $base_uri,
        int $obj_id
    ): EnvironmentImplementation {
        return new EnvironmentImplementation(
            $this->ctrl,
            $this->http,
            $this->refinery,
            $this->lng,
            $this->tabs_gui,
            $this->uuid_factory,
            $this->definitions_factory,
            $this->editability,
            $this->required_capabilities,
            $base_uri,
            $obj_id
        );
    }

    private function buildAfterQuestionCreationRedirectUri(
        EnvironmentImplementation $environment,
        CreateModes $create_mode,
        Uuid $question_uuid
    ): string {
        if ($create_mode !== CreateModes::Simple) {
            return $environment
                ->withDefaultStep()
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
        EnvironmentImplementation $environment,
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
