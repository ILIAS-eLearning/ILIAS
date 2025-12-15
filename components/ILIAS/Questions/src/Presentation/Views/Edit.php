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

use ILIAS\Questions\Presentation\Layout\Definitions\EditForm;
use ILIAS\Questions\Presentation\Layout\Definitions\Factory as DefinitionsFactory;
use ILIAS\Questions\Presentation\Definitions\Editability;
use ILIAS\Questions\Presentation\Layout\Definitions\EnvironmentImplementation;
use ILIAS\Questions\Presentation\Layout\Definitions\QuestionsTable;
use ILIAS\Questions\Presentation\Layout\GlobalScreen\LayoutProvider;
use ILIAS\Questions\AnswerForm\Definition;
use ILIAS\Questions\AnswerForm\Factory as AnswerFormFactory;
use ILIAS\Questions\AnswerForm\TypeGenericProperties;
use ILIAS\Questions\Question\Persistence\Repository;
use ILIAS\Questions\Question\QuestionImplementation;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\URI;
use ILIAS\Data\UUID\Factory as UuidFactory;
use ILIAS\Language\Language;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\HTTP\Services as HTTP;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\UI\Component\Item\Standard as StandardItem;
use ILIAS\UI\Component\Item\Group as ItemGroup;
use ILIAS\UI\Component\MainControls\Slate\Legacy as LegacySlate;
use ILIAS\GlobalScreen\Services as GlobalScreen;

class Edit
{
    private const string CMD_CREATE_QUESTION = 'create';
    public const string CMD_EDIT_QUESTION = 'edit';
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
        private readonly Repository $questions_repository,
        private readonly DefinitionsFactory $definitions_factory
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
    ): QuestionsTable|EditForm {
        $environment = $this->buildEnvironment($base_uri);
        return match($environment->getAction()) {
            self::CMD_CREATE_QUESTION => $this->createQuestion($environment),
            self::CMD_EDIT_QUESTION => $this->editQuestion($environment),
            default => $this->showTable($toolbar, $environment)
        };
    }

    public function forwardPageCmds(
        \ilGlobalTemplateInterface $tpl,
        URI $base_uri,
    ): void {
        $environment = $this->buildEnvironment($base_uri);
        $this->initializeEditMode($environment);
        $environment->setParametersForQuestionCmds();

        $tpl->setContent(
            $this->ctrl->forwardCommand(
                new \QstsQuestionPageGUI(
                    $environment
                        ->withActionParameter(self::CMD_EDIT_QUESTION)
                        ->withQuestionIdParameter($environment->getQuestionId())
                        ->getUrlBuilder()
                        ->buildURI(),
                    $this,
                    $this->questions_repository->getForQuestionId($environment->getQuestionId())
                )
            )
        );
    }

    public function createAnswerForm(
        URI $base_uri,
        QuestionImplementation $question,
        \ilPCAnswerForm $content_object
    ): EditForm {
        $environment = $this->buildEnvironment($base_uri)
            ->withActionParameter(self::CMD_CREATE_ANSWER_FORM)
            ->withQuestionIdParameter($question->getId());

        $answer_form_type_class_hash = $environment->getTypeClassHast();
        if ($answer_form_type_class_hash !== '') {
            return $this->forwardCreateAnswerFormCmd(
                $environment->withAnswerFormTypeHashParameter($answer_form_type_class_hash),
                $content_object,
                $this->answer_form_factory->buildTypeDefinitionFromSelectValue($answer_form_type_class_hash),
                $this->answer_form_factory->getDefaultTypeGenericProperties($question->getId())
            );
        }

        return match($environment->getAction()) {
            self::CMD_CREATE_ANSWER_FORM => $this->processCreateAnswerForm(
                $environment,
                $content_object,
                $this->answer_form_factory->getDefaultTypeGenericProperties($question->getId())
            ),
            default => $this->buildCreateAnswerForm($environment)
        };
    }

    public function editAnswerForm(
        URI $base_uri,
        QuestionImplementation $question,
        \ilPCAnswerForm $content_obj
    ): EditForm|EditOverview {
        $environment = $this->buildEnvironment($base_uri)
            ->withActionParameter(self::CMD_EDIT_ANSWER_FORM)
            ->withQuestionIdParameter($question->getId());

        return match($environment->getAction()) {
            self::CMD_EDIT_ANSWER_FORM => $this->processCreateAnswerForm($url_builder),
            default => $this->forwardEditAnswerFormCmd($environment)
        };
    }

    private function createQuestion(
        EnvironmentImplementation $environment
    ): EditForm {
        $this->initializeEditMode($environment);

        $create = $this->questions_repository->getNew()->getEditView(
            $this->lng,
            $this->current_user,
            $this->ui_factory,
            $this->refinery,
            $this->http->request(),
            $this->ctrl,
            $this->data_factory
        )->create(
            $environment->withActionParameter(self::CMD_CREATE_QUESTION)
        );

        if ($create instanceof EditForm) {
            return $create;
        }

        $this->questions_repository->store($create);
        return $this->buildEditStartView(
            $environment
                ->withDefaultStep()
                ->withActionParameter(self::CMD_EDIT_QUESTION)
                ->withQuestionIdParameter($create->getId()),
            $create
        );

    }

    private function editQuestion(
        EnvironmentImplementation $environment
    ): EditForm {
        $this->initializeEditMode($environment);

        $question_id = $environment->getQuestionId();

        $edit = $this->questions_repository->getForQuestionId($question_id)->getEditView(
            $this->lng,
            $this->current_user,
            $this->ui_factory,
            $this->refinery,
            $this->http->request(),
            $this->ctrl,
            $this->data_factory
        )->edit(
            $environment
                ->withActionParameter(self::CMD_EDIT_QUESTION)
                ->withQuestionIdParameter($question_id)
        );

        if ($edit instanceof EditForm) {
            return $edit;
        }

        $this->questions_repository->store($edit);
        return $this->buildEditStartView(
            $environment->withQuestionIdParameter($question_id),
            $edit
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
        \ilPCAnswerForm $content_obj,
        TypeGenericProperties $generic_answer_form_properties
    ): EditForm {
        $form = $this->buildCreateAnswerForm($environment)->withRequest($this->http->request());

        $data = $form->getData();
        return $data === null
            ? $form
            : $this->forwardCreateAnswerFormCmd(
                $environment->withAnswerFormTypeHashParameterParameter(
                    $this->answer_form_factory->getHashedClass($data::class)
                ),
                $content_obj,
                $data,
                $generic_answer_form_properties
            );
    }

    private function forwardCreateAnswerFormCmd(
        EnvironmentImplementation $environment,
        \ilPCAnswerForm $content_obj,
        Definition $type,
        TypeGenericProperties $type_generic_properties,
    ): ?EditForm {
        $create = $type->getEditView()->create(
            $environment->withProperties(
                $type->buildProperties($type_generic_properties, [])
            )
        );

        if ($create instanceof EditForm) {
            return $create;
        }

        $this->questions_repository->store($create);
        $content_obj->create($create->getAnswerFormId());
        $content_obj->getPage()->update();

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
            array_map(
                fn(QuestionImplementation $v): StandardItem => $this->ui_factory->item()->standard(
                    $v->toEditLink(
                        $this->ui_factory->link(),
                        $environment->withActionParameter(self::CMD_EDIT_QUESTION)
                    )
                ),
                iterator_to_array($this->questions_repository->getAllQuestions())
            )
        );
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
            $this->ctrl,
            $this->data_factory
        )->edit($environment);
    }

    private function buildCreateAnswerForm(
        EnvironmentImplementation $environemt
    ): EditForm {
        $if = $this->ui_factory->input();
        return $this->edit_form_factory->getEditForm(
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

    private function checkCapabilities(array $capabilities): void
    {
        foreach ($capabilities as $capability) {
            if (!$this->questions_repository->capabilityExists($capability)) {
                throw new \InvalidArgumentException('All provided capabilities must implement ILIAS\Questions\AnswerForm\Capabilities\Capability.');
            }
        }
    }

    public function buildEnvironment(
        URI $base_uri
    ): EnvironmentImplementation {
        return new EnvironmentImplementation(
            $this->ctrl,
            $this->http,
            $this->refinery,
            $this->uuid_factory,
            $this->definitions_factory,
            $this->editability,
            $base_uri
        );
    }
}
