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

use ILIAS\Questions\AnswerForm\Capabilities\Feedback\Feedback;
use ILIAS\Questions\AnswerForm\Capabilities\SuggestedLearningContent\SuggestedLearningContent;
use ILIAS\Questions\AnswerForm\Capabilities\Marking\Marking;
use ILIAS\Questions\AnswerForm\Factory as AnswerFormFactory;
use ILIAS\Questions\Legacy\LocalDIC;
use ILIAS\Questions\Question\Persistence\Repository;
use ILIAS\Questions\Question\Views\Participant as QuestionParticipantView;
use ILIAS\Questions\PublicInterface;
use ILIAS\Questions\Presentation\Definitions\OverviewTableColumns;
use ILIAS\Questions\Presentation\Views\Participant;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\Data\UUID\Factory as UuidFactory;
use ILIAS\Data\UUID\Uuid;
use ILIAS\HTTP\Services as HTTPServices;
use ILIAS\Language\Language;
use ILIAS\Refinery\ConstraintViolationException;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UICore\GlobalTemplate;
use ILIAS\UI\Component\Table\DataRetrieval;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\UI\Component\Input\Container\Filter\Standard as Filter;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;

/**
 * @ilCtrl_isCalledBy ilObjQuestionPreviewGUI: ilObjQuestionsGUI
 */
class ilObjQuestionPreviewGUI implements DataRetrieval
{
    private const array PARAMETER_NAMENSPACE = ['q', 'p'];
    private const string ACTION_TOKEN_STRING = 'a';
    private const string ROW_ID_TOKEN_STRING = 'r';

    private const string CMD_DEFAULT = 'show';
    private const string CMD_SHOW_QUESTION = 'showQuestion';
    private const string CMD_SHOW_QUESTION_ASYNC = 'showQuestionAsync';
    private const string CMD_RESPOND = 'respond';

    private readonly Repository $repository;
    private readonly AnswerFormFactory $answer_form_factory;
    private readonly Participant $participant_view;

    private readonly ilCtrl $ctrl;
    private readonly Language $lng;
    private readonly HTTPServices $http;
    private readonly ilGlobalTemplateInterface $tpl;
    private readonly ilObjUser $current_user;
    private readonly ilTabsGUI $tabs_gui;
    private readonly UIFactory $ui_factory;
    private readonly UIRenderer $ui_renderer;
    private readonly \ilUIService $ui_service;
    private readonly Refinery $refinery;
    private readonly DataFactory $data_factory;
    private readonly UuidFactory $uuid_factory;

    private readonly URLBuilder $url_builder;
    private readonly URLBuilderToken $action_token;
    private readonly URLBuilderToken $row_id_token;

    private readonly QstsTempAttemptRepository $temp_attempt_repository;

    public function __construct(
        private readonly int $object_id
    ) {
        /**
         * sk, 2026.05.06: This is done this way as this is a completely
         * temporary class. It should be made as simple as possible to get rid
         * of it.
         */
        $local_dic = LocalDIC::dic();
        $this->repository = $local_dic[Repository::class];
        $this->answer_form_factory = $local_dic[AnswerFormFactory::class];
        $this->participant_view = $local_dic[PublicInterface::class]
            ->getParticipantView($this->object_id);

        /** @var ILIAS\DI\Container $DIC */
        global $DIC;
        $this->ctrl = $DIC['ilCtrl'];
        $this->lng = $DIC['lng'];
        $this->tpl = $DIC['tpl'];
        $this->current_user = $DIC['user']->getLoggedInUser();
        $this->tabs_gui = $DIC['ilTabs'];
        $this->http = $DIC['http'];
        $this->ui_factory = $DIC['ui.factory'];
        $this->ui_renderer = $DIC['ui.renderer'];
        $this->ui_service = $DIC->uiService();
        $this->refinery = $DIC['refinery'];
        $this->data_factory = new DataFactory();
        $this->uuid_factory = new UuidFactory();

        $this->temp_attempt_repository = new QstsTempAttemptRepository(
            $DIC['ilDB'],
            $this->uuid_factory
        );

        [
            $this->url_builder,
            $this->action_token,
            $this->row_id_token
        ] = $this->getUrlBuilder()->acquireParameters(
            self::PARAMETER_NAMENSPACE,
            self::ACTION_TOKEN_STRING,
            self::ROW_ID_TOKEN_STRING
        );
    }

    public function executeCommand(): void
    {
        $cmd = $this->ctrl->getCmd();

        $table_action = $this->http->wrapper()->query()->retrieve(
            $this->action_token->getName(),
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->string(),
                $this->refinery->always(null)
            ])
        );

        if ($table_action !== null) {
            $cmd = $table_action;
        }

        if ($cmd === null || $cmd === '') {
            $cmd = self::CMD_DEFAULT;
        }
        $cmd .= 'Cmd';
        $this->$cmd();
    }

    private function showCmd(): void
    {
        $filter = $this->buildFilter(
            $this->ctrl->getLinkTargetByClass(
                $this->getClassPath()
            )
        );

        $this->tpl->setContent(
            $this->ui_renderer->render([
                $filter,
                $this->ui_factory->table()->data(
                    $this,
                    $this->lng->txt('questions'),
                    [
                        OverviewTableColumns::Title->value
                            => $this->ui_factory->table()->column()->text(
                                $this->lng->txt('title')
                            ),
                        OverviewTableColumns::AnswerFormTypes->value
                            => $this->ui_factory->table()->column()->text(
                                $this->lng->txt('contained_types')
                            )->withIsOptional(true, true)
                        ->withIsSortable(false),
                    ],
                )->withActions(
                    $this->getActions()
                )->withRange(new Range(0, 20))
                ->withFilter(
                    $this->ui_service->filter()->getData($filter)
                )->withRequest($this->http->request())
            ])
        );
    }

    private function showQuestionCmd(): void
    {
        $question_id = $this->retrieveQuestionIdFromQuery();
        $attempt_id = $this->temp_attempt_repository->get(
            $this->current_user->getId()
        );

        if ($question_id === null) {
            $this->tpl->setOnScreenMessage(
                GlobalTemplate::MESSAGE_TYPE_FAILURE,
                $this->lng->txt('invalid')
            );
            $this->showCmd();
        }

        $this->tabs_gui->clearTargets();
        $this->tabs_gui->setBackTarget(
            $this->lng->txt('back'),
            $this->ctrl->getLinkTargetByClass(
                $this->getClassPath()
            )
        );

        $view = $this->participant_view
            ->withRequiredCapabilities([
                Feedback::class,
                SuggestedLearningContent::class,
                Marking::class
            ])->getQuestionView(
                $question_id,
                $attempt_id
            );

        if ($attempt_id === null) {
            $this->temp_attempt_repository->store(
                $this->current_user->getId(),
                $view->getAttemptId()
            );
        }

        $this->tpl->setContent(
            $this->ui_renderer->render(
                $this->ui_factory->panel()->standard(
                    $this->lng->txt('question'),
                    $this->ui_factory->legacy()->content(
                        $this->buildQuestionForm($view)
                    )
                )
            )
        );
    }

    private function showQuestionAsyncCmd(): void
    {
        $this->tpl->setContent('Async');
    }

    private function respondCmd(): void
    {
        $question_id = $this->retrieveQuestionIdFromQuery();
        $attempt_id = $this->temp_attempt_repository->get(
            $this->current_user->getId()
        );

        if ($question_id === null || $attempt_id === null) {
            $this->tpl->setOnScreenMessage(
                GlobalTemplate::MESSAGE_TYPE_FAILURE,
                $this->lng->txt('invalid')
            );
            $this->showCmd();
        }

        $this->tabs_gui->clearTargets();
        $this->tabs_gui->setBackTarget(
            $this->lng->txt('back'),
            $this->ctrl->getLinkTargetByClass(
                $this->getClassPath()
            )
        );

        $response_id = $this->participant_view
            ->withRequiredCapabilities([
                Feedback::class,
                SuggestedLearningContent::class,
                Marking::class
            ])->persistResponse(
                $question_id,
                $response_id
            );
    }

    #[\Override]
    public function getRows(
        DataRowBuilder $row_builder,
        array $visible_column_ids,
        Range $range,
        Order $order,
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): \Generator {
        foreach ($this->repository->getQuestionDataOnlyForAllQuestions(
            $range,
            $order,
            $filter_data
        ) as $question) {
            yield $row_builder->buildDataRow(
                $question->getid()->toString(),
                [
                    OverviewTableColumns::Title->value => $question->getTitle(),
                    OverviewTableColumns::AnswerFormTypes->value => implode(
                        '<br>',
                        $question->getListOfContainedAnswerFormTypeLabels($this->lng)
                    )
                ]
            );
        }
    }

    #[\Override]
    public function getTotalRowCount(
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): ?int {
        return $this->repository->getQuestionsCount();
    }

    private function buildFilter(
        string $action
    ): Filter {
        $filter_inputs = OverviewTableColumns::getFilterInputs(
            $this->lng,
            $this->ui_factory->input()->field(),
            $this->answer_form_factory->getAnswerFormTypesArrayForSelect(
                $this->lng
            )
        );

        $active = array_fill(0, count($filter_inputs), true);

        $filter = $this->ui_service->filter()->standard(
            'question_table_filter_id',
            $action,
            $filter_inputs,
            $active,
            true,
            true
        );

        $request = $this->http->request();
        return $request->getMethod() === 'POST'
            ? $filter->withRequest($request)
            : $filter;
    }

    private function getActions(): array
    {
        return [
            'show' => $this->ui_factory->table()->action()->single(
                $this->lng->txt('show'),
                $this->url_builder->withParameter($this->action_token, self::CMD_SHOW_QUESTION),
                $this->row_id_token
            ),
            'show_async' => $this->ui_factory->table()->action()->single(
                $this->lng->txt('show_async'),
                $this->url_builder->withParameter($this->action_token, self::CMD_SHOW_QUESTION_ASYNC),
                $this->row_id_token
            ),
        ];
    }

    private function retrieveQuestionIdFromQuery(): ?Uuid
    {
        return $this->http->wrapper()->query()->retrieve(
            $this->row_id_token->getName(),
            $this->refinery->byTrying([
                $this->refinery->custom()->transformation(
                    function (array $v): Uuid {
                        try {
                            return $this->uuid_factory->fromString($v[0]);
                        } catch (Throwable $e) {
                            throw new ConstraintViolationException(
                                sprintf('The value could not be transformed into a Uuid'),
                                'not_valid'
                            );
                        }
                    }
                ),
                $this->refinery->always(null)
            ])
        );
    }

    private function getUrlBuilder(): URLBuilder
    {
        return new URLBuilder(
            $this->data_factory->uri(
                ILIAS_HTTP_PATH . '/' . $this->ctrl->getLinkTargetByClass(
                    $this->getClassPath()
                )
            )
        );
    }

    private function buildQuestionForm(
        QuestionParticipantView $view
    ): string {
        $form = new ilPropertyFormGUI();
        $form->setCloseTag(false);
        $form->setFormAction(
            $this->ctrl->getFormActionByClass(
                $this->getClassPath()
            )
        );
        $form->addCommandButton(
            self::CMD_RESPOND,
            $this->lng->txt('send')
        );

        $form_opening = $form->getHTML();

        $form->setOpenTag(false);
        $form->setCloseTag(true);

        return $form_opening
        . $this->ui_renderer->render(
            $view->getUI()
        ) . $form->getHTML();
    }

    private function getClassPath(): array
    {
        return [ilObjQuestionsGUI::class, ilObjQuestionPreviewGUI::class];
    }
}
