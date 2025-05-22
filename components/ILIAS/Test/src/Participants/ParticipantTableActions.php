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

namespace ILIAS\Test\Participants;

use ILIAS\Test\RequestDataCollector;
use ILIAS\Test\ResponseHandler;
use ILIAS\Language\Language;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Component\Table\DataRow;
use ILIAS\UI\Component\Table\Action\Standard as StandardAction;
use ILIAS\UI\Component\Modal\Modal;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;

class ParticipantTableActions
{
    public const ROW_ID_PARAMETER = 'p_id';
    public const ACTION_PARAMETER = 'action';
    public const ACTION_TYPE_PARAMETER = 'action_type';
    public const SHOW_ACTION = 'showTableAction';
    public const SUBMIT_ACTION = 'submitTableAction';
    /**
     * @param array<string, TableAction> $actions
     */
    public function __construct(
        protected readonly \ilCtrlInterface $ctrl,
        protected readonly Language $lng,
        protected readonly \ilGlobalTemplateInterface $tpl,
        protected readonly UIFactory $ui_factory,
        protected readonly UIRenderer $ui_renderer,
        protected readonly Refinery $refinery,
        protected readonly RequestDataCollector $test_request,
        protected readonly ResponseHandler $test_response,
        protected readonly ParticipantRepository $repository,
        protected readonly \ilObjTest $test_obj,
        protected readonly array $actions
    ) {
    }

    public function getEnabledActions(
        URLBuilder $url_builder,
        URLBuilderToken $row_id_token,
        URLBuilderToken $action_token,
        URLBuilderToken $action_type_token
    ): array {
        return array_filter(
            array_map(
                function (TableAction $action) use (
                    $url_builder,
                    $row_id_token,
                    $action_token,
                    $action_type_token
                ): ?StandardAction {
                    if (!$action->isAvailable()) {
                        return null;
                    }

                    return $action->getTableAction(
                        $url_builder,
                        $row_id_token,
                        $action_token,
                        $action_type_token
                    );
                },
                $this->actions
            )
        );
    }

    public function getAction(string $action_id): ?TableAction
    {
        return $this->actions[$action_id] ?? null;
    }

    public function execute(
        URLBuilder $url_builder,
        URLBuilderToken $row_id_token,
        URLBuilderToken $action_token,
        URLBuilderToken $action_type_token
    ): ?Modal {
        return match($this->test_request->strVal($action_type_token->getName())) {
            self::SUBMIT_ACTION => $this->submit(
                $url_builder,
                $row_id_token,
                $action_token,
                $action_type_token
            ),
            default => $this->showModal(
                $url_builder,
                $row_id_token,
                $action_token,
                $action_type_token
            ),
        };
    }

    public function onDataRow(DataRow $row, mixed $record): DataRow
    {
        return array_reduce(
            array_keys($this->actions),
            fn(DataRow $c, string $v): DataRow => $this->actions[$v]->allowActionForRecord($record)
                ? $c
                : $c->withDisabledAction($v),
            $row
        );
    }

    protected function showModal(
        URLBuilder $url_builder,
        URLBuilderToken $row_id_token,
        URLBuilderToken $action_token,
        URLBuilderToken $action_type_token
    ): void {
        $action = $this->actions[$this->test_request->strVal($action_token->getName())];
        $selected_participants_from_request = $this->test_request
            ->getMultiSelectionIds($row_id_token->getName());
        $selected_participants = $this->resolveSelectedParticipants(
            $action,
            $selected_participants_from_request
        );

        if ($selected_participants === []) {
            $error_message = $action->getSelectionErrorMessage() ?? $this->lng->txt('no_valid_participant_selection');
            $this->test_response->sendAsync(
                $this->ui_renderer->renderAsync(
                    $this->ui_factory->messageBox()->failure(
                        $error_message
                    )
                )
            );
        }

        $this->test_response->sendAsync(
            $this->ui_renderer->renderAsync(
                $action->getModal(
                    $url_builder
                        ->withParameter($row_id_token, $selected_participants_from_request)
                        ->withParameter($action_token, $action->getActionId())
                        ->withParameter($action_type_token, self::SUBMIT_ACTION),
                    $selected_participants,
                    $selected_participants_from_request === 'ALL_OBJECTS'
                )
            )
        );
    }

    protected function submit(
        URLBuilder $url_builder,
        URLBuilderToken $row_id_token,
        URLBuilderToken $action_token,
        URLBuilderToken $action_type_token
    ): ?Modal {
        $action = $this->actions[$this->test_request->strVal($action_token->getName())];
        $selected_participants_from_request = $this->test_request
            ->getMultiSelectionIds($row_id_token->getName());
        $selected_participants = $this->resolveSelectedParticipants(
            $action,
            $selected_participants_from_request
        );

        if ($selected_participants === []) {
            $this->tpl->setOnScreenMessage(
                \ilGlobalTemplateInterface::MESSAGE_TYPE_FAILURE,
                $this->lng->txt('no_valid_participant_selection'),
                true
            );
        }

        return $action->onSubmit(
            $url_builder
                ->withParameter($row_id_token, $selected_participants_from_request)
                ->withParameter($action_token, $action->getActionId())
                ->withParameter($action_type_token, self::SUBMIT_ACTION),
            $this->test_request->getRequest(),
            $selected_participants,
            $selected_participants_from_request === 'ALL_OBJECTS'
        );
    }

    protected function resolveSelectedParticipants(TableAction $action, array|string $selected_participants): array
    {
        if ($selected_participants === 'ALL_OBJECTS') {
            return array_filter(
                iterator_to_array($this->repository->getParticipants($this->test_obj->getTestId())),
                fn(Participant $participant) => $action->allowActionForRecord($participant)
            );
        }

        return array_filter(
            array_map(
                fn(int $user_id) => $this->repository->getParticipantByUserId($this->test_obj->getTestId(), $user_id),
                $selected_participants
            ),
            fn(Participant $participant) => $action->allowActionForRecord($participant)
        );
    }
}
