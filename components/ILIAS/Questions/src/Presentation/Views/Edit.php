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

use ILIAS\Questions\Presentation\Layout\Async;
use ILIAS\Questions\Presentation\Layout\EditForm;
use ILIAS\Questions\Presentation\Layout\Factory as LayoutFactory;
use ILIAS\Questions\Presentation\Layout\Renderable;
use ILIAS\Questions\Presentation\Definitions\Editability;
use ILIAS\Questions\Presentation\Definitions\Environment;
use ILIAS\Questions\Presentation\Definitions\EnvironmentImplementation;
use ILIAS\Questions\Presentation\Layout\QuestionsTable;
use ILIAS\Questions\Presentation\Layout\GlobalScreen\LayoutProvider;
use ILIAS\Questions\AnswerForm\Definition;
use ILIAS\Questions\AnswerForm\Factory as AnswerFormFactory;
use ILIAS\Questions\AnswerForm\Properties as AnswerFormProperties;
use ILIAS\Questions\AnswerForm\Views\Edit as AnswerFormEditView;
use ILIAS\Questions\Persistence\Repository;
use ILIAS\Questions\Question\QuestionImplementation;
use ILIAS\Data\URI;
use ILIAS\Data\UUID\Factory as UuidFactory;
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
    private const string CMD_CREATE_QUESTION = 'create';
    public const string CMD_EDIT_QUESTION = 'edit';
    public const string CMD_DELETE_QUESTIONS = 'delete';
    private const string CMD_CREATE_ANSWER_FORM = 'create_af';
    public const string CMD_OTHER_ANSWER_FORM = 'other_af';
    private const string CMD_EDIT_FEEDBACK = 'edit_f';
    private const string CMD_EDIT_CONTENT_FOR_REPETITION = 'edit_cfr';

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
        private readonly GlobalTemplate $global_tpl,
        private readonly ContentStyle $content_style,
        private readonly \ilCtrl $ctrl,
        private readonly HTTP $http,
        private readonly \ilTabsGUI $tabs_gui,
        private readonly \ilUIService $ui_services,
        private readonly UuidFactory $uuid_factory,
        private readonly AnswerFormFactory $answer_form_factory,
        private readonly Repository $questions_repository,
        private readonly LayoutFactory $definitions_factory
    ) {
    }

    public function withRequiredCapabilities(
        array $capability_class_names
    ): self {
        $this->checkCapabilities($capability_class_names);
        $clone = clone $this;
        $clone->required_capabilities = $capability_class_names;
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
        \ilToolbarGUI $toolbar,
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
            self::CMD_CREATE_QUESTION => $this->createQuestion($environment),
            self::CMD_EDIT_QUESTION => $this->editQuestion($environment),
            self::CMD_DELETE_QUESTIONS => $this->deleteQuestions($environment),
            default => $this->showTable($toolbar, $environment)
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
        $this->initializeEditMode($environment);
        $environment->setParametersForQuestionCmds();

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
                    $obj_id
                )->withReturnURI(
                    $environment
                            ->withActionParameter(self::CMD_EDIT_QUESTION)
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
        )->withActionParameter(self::CMD_CREATE_ANSWER_FORM)
        ->withQuestionIdParameter($question->getId());

        $answer_form_type_class_hash = $environment->getTypeClassHash();

        if ($answer_form_type_class_hash !== '') {
            $type = $this->answer_form_factory
                ->buildTypeDefinitionFromSelectValue($answer_form_type_class_hash);

            return $this->forwardCreateAnswerFormCmd(
                $environment->withAnswerFormProperties(
                    $type->buildProperties(
                        $this->answer_form_factory->getDefaultTypeGenericProperties(
                            $question->getId(),
                            $type
                        ),
                        null
                    )
                )->withAnswerFormTypeHashParameter($answer_form_type_class_hash),
                $question,
                $content_object,
                $type->getEditView()
            );
        }

        return match($environment->getAction()) {
            self::CMD_CREATE_ANSWER_FORM => $this->processCreateAnswerForm(
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
        Definition $type
    ): Async|Renderable {
        $environment = $this->buildEnvironment(
            $base_uri,
            $obj_id
        )->withAnswerFormProperties($answer_form_properties)
        ->withQuestionIdParameter($question->getId());

        $environment->setEditAnswerFormTabs(
            self::CMD_EDIT_FEEDBACK,
            self::CMD_EDIT_CONTENT_FOR_REPETITION
        );

        $edit_view = $type->getEditView();

        if ($environment->getAction() === self::CMD_OTHER_ANSWER_FORM) {
            $environment = $environment->withActionParameter(self::CMD_OTHER_ANSWER_FORM);
            $next = $edit_view->other($environment);
        } else {
            $next = $edit_view->edit($environment);
        }

        if (!($next instanceof AnswerFormProperties)) {
            return $next;
        }

        $this->questions_repository->update(
            [$question->withAnswerForm($next)]
        );

        $this->ctrl->redirectToURL(
            $edit_view->getFinishEditingUrl($environment)->buildURI()->__toString()
        );
    }

    private function createQuestion(
        EnvironmentImplementation $environment
    ): EditForm {
        $this->initializeEditMode($environment);

        $create = $this->questions_repository->getNew(
            $environment->getObjId()
        )->getEditView(
            $this->lng,
            $this->current_user,
            $this->ui_factory,
            $this->refinery,
            $this->http->request(),
            $this->ctrl
        )->create(
            $environment->withActionParameter(self::CMD_CREATE_QUESTION)
        );

        if ($create instanceof EditForm) {
            return $create;
        }

        $this->questions_repository->create([$create]);
        return $this->ctrl->redirectToURL(
            $environment
                ->withDefaultStep()
                ->withActionParameter(self::CMD_EDIT_QUESTION)
                ->withQuestionIdParameter($create->getId())
                ->getUrlBuilder()
                ->buildURI()
                ->__toString()
        );

    }

    private function editQuestion(
        EnvironmentImplementation $environment
    ): EditForm {
        $this->initializeEditMode($environment);

        $question_id = $environment->getQuestionId();
        $question = $this->questions_repository->getForQuestionId($question_id);
        $environment_with_question_parameter = $environment
            ->withQuestionIdParameter($question_id);

        $edit = $question->getEditView(
            $this->lng,
            $this->current_user,
            $this->ui_factory,
            $this->refinery,
            $this->http->request(),
            $this->ctrl
        )->edit(
            $environment_with_question_parameter
                ->withActionParameter(self::CMD_EDIT_QUESTION),
            $question->getParticipantView()
        );

        if ($edit instanceof EditForm) {
            return $edit;
        }

        $this->questions_repository->update([$edit]);
        return $this->buildEditStartView(
            $environment_with_question_parameter
                ->withDefaultStep()
                ->withActionParameter(self::CMD_EDIT_QUESTION),
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

        if ($environment->getStep() === self::CMD_DELETE_QUESTIONS) {
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
                    self::CMD_DELETE_QUESTIONS
                )->getUrlBuilderWithStepParameter(
                    self::CMD_DELETE_QUESTIONS
                )->buildURI()->__toString()
            )->withAffectedItems(
                $this->buildAffectedItems($question_ids)
            )
        );
    }

    private function showTable(
        \ilToolbarGUI $toolbar,
        EnvironmentImplementation $environment
    ): QuestionsTable {
        $toolbar->addComponent(
            $this->ui_factory->button()->standard(
                $this->lng->txt('create'),
                $environment->withActionParameter(self::CMD_CREATE_QUESTION)
                    ->getUrlBuilder()
                    ->buildURI()
                    ->__toString()
            )
        );

        return new QuestionsTable(
            $this->ui_factory,
            $this->ui_services,
            $this->lng,
            $this->http->request(),
            $this->answer_form_factory,
            $this->questions_repository,
            $environment
        );
    }

    private function processCreateAnswerForm(
        EnvironmentImplementation $environment,
        QuestionImplementation $question,
        \ilPCAnswerForm $content_object
    ): EditForm {
        $form = $this->buildCreateAnswerForm($environment)->withRequest($this->http->request());

        $data = $form->getData();
        return $data === null
            ? $form
            : $this->forwardCreateAnswerFormCmd(
                $environment->withAnswerFormProperties(
                    $data->buildProperties(
                        $this->answer_form_factory->getDefaultTypeGenericProperties(
                            $question->getId(),
                            $data
                        ),
                        null
                    )
                )->withAnswerFormTypeHashParameter(
                    $this->answer_form_factory->getHashedClass($data::class)
                ),
                $question,
                $content_object,
                $data->getEditView()
            );
    }

    private function forwardCreateAnswerFormCmd(
        EnvironmentImplementation $environment,
        QuestionImplementation $question,
        \ilPCAnswerForm $content_object,
        AnswerFormEditView $answer_form_edit_view
    ): ?EditForm {
        $create = $answer_form_edit_view->create($environment);

        if ($create instanceof EditForm) {
            return $create;
        }

        $this->questions_repository->create(
            [$question->withAnswerForm($create)]
        );

        $content_object->create($create->getAnswerFormId());
        $content_object->getPage()->update();

        $this->ctrl->redirectByClass(\QstsQuestionPageGUI::class, 'edit');
    }

    private function initializeEditMode(
        EnvironmentImplementation $environment
    ): void {
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
            ->withActionParameter(self::CMD_CREATE_QUESTION)
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
                    $environment->withActionParameter(self::CMD_EDIT_QUESTION)
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
            $this->lng,
            $this->current_user,
            $this->ui_factory,
            $this->refinery,
            $this->http->request(),
            $this->ctrl
        )->edit(
            $environment,
            $question->getParticipantView()
        );
    }

    private function buildCreateAnswerForm(
        EnvironmentImplementation $environemt
    ): EditForm {
        $if = $this->ui_factory->input();
        return $environemt->getPresentationFactory()->getEditForm(
            $environemt->getUrlBuilder(),
            $if->field()->section(
                [
                        $if->field()->select(
                            $this->lng->txt('select_answer_form_type'),
                            $this->answer_form_factory->getAnswerFormTypesArrayForSelect($this->lng)
                        )->withRequired(true)
                    ],
                $this->lng->txt('create_answer_form')
            )->withAdditionalTransformation(
                $this->refinery->custom()->transformation(
                    fn(array $vs): ?Definition => $this->answer_form_factory->buildTypeDefinitionFromSelectValue($vs[0])
                )
            ),
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

    private function checkCapabilities(
        array $capabilities
    ): void {
        foreach ($capabilities as $capability) {
            if (!$this->questions_repository->capabilityExists($capability)) {
                throw new \InvalidArgumentException(
                    'All provided capabilities must implement '
                    . 'ILIAS\Questions\AnswerForm\Capabilities\Capability.'
                );
            }
        }
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
            $base_uri,
            $obj_id
        );
    }
}
