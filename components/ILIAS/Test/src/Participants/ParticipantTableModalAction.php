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

use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Test\RequestDataCollector;
use ILIAS\Test\ResponseHandler;
use ILIAS\UI\Component\Input\Container\Form\Standard;
use ILIAS\UI\Component\Modal\Modal;
use ILIAS\UI\Component\Table\DataRow;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;

abstract class ParticipantTableModalAction implements TableAction
{
    protected ?\ilObjTest $test_object = null;

    public function __construct(
        protected readonly \ilCtrlInterface $ctrl,
        protected readonly \ilLanguage $lng,
        protected readonly \ilGlobalTemplateInterface $tpl,
        protected readonly UIFactory $ui_factory,
        protected readonly UIRenderer $ui_renderer,
        protected readonly Refinery $refinery,
        protected readonly RequestDataCollector $test_request,
        protected readonly ResponseHandler $test_response,
        protected readonly ParticipantRepository $repository,
    ) {
    }

    public function withTestObject(?\ilObjTest $test_object): self
    {
        $clone = clone $this;
        $clone->test_object = $test_object;
        return $clone;
    }

    public function getActions(URLBuilder $url_builder): array
    {
        [$url_builder, $id_token, $action_token] = $this->acquireParameters($url_builder);

        return [
            $this->getActionId() => $this->ui_factory->table()->action()->standard(
                $this->lng->txt($this->getActionId()),
                $url_builder->withParameter($action_token, "showModal"),
                $id_token
            )->withAsync()
        ];
    }

    public function execute(URLBuilder $url_builder): void
    {
        [$url_builder, $id_token, $action_token] = $this->acquireParameters($url_builder);
        $table_actions = $this->test_request->strVal($action_token->getName());

        match($table_actions) {
            'submitTableAction' => $this->submitModal($url_builder, $id_token, $action_token),
            default => $this->showModal($url_builder, $id_token, $action_token),
        };
    }

    public function onDataRow(DataRow $row, mixed $record): DataRow
    {
        return $this->allowActionForRecord($record)
            ? $row
            : $row->withDisabledAction($this->getActionId());
    }

    public function isEnabled(): bool
    {
        return true;
    }

    protected function showModal(URLBuilder $url_builder, URLBuilderToken $id_token, URLBuilderToken $action_token, bool $is_async = true): void
    {
        $selected_participants = $this->test_request->getMultiSelectionIds($id_token->getName());

        $renderer = $is_async ?
            $this->ui_renderer->renderAsync(...) :
            $this->ui_renderer->render(...);

        $output = $renderer([
            $this->getModal(
                $url_builder
                    ->withParameter($id_token, $selected_participants)
                    ->withParameter($action_token, "submitTableAction"),
                $selected_participants
            )
        ]);

        $this->test_response->sendAsync($output);
    }

    protected function submitModal(URLBuilder $url_builder, URLBuilderToken $id_token, URLBuilderToken $action_token): void
    {
        $selected_participants = $this->test_request->getMultiSelectionIds($id_token->getName());

        $modal = $this->getModal($url_builder, $selected_participants);

        if ($modal instanceof Standard) {
            $modal = $modal->withRequest($this->test_request->getRequest());
            $modal->getData(); // call to validate data

            if ($modal->getError()) {
                $this->showModal($url_builder, $id_token, $action_token, false);
                return;
            }
        }

        $participants = $this->resolveSelectedParticipants($selected_participants);

        if (count($participants) === 0) {
            $this->tpl->setOnScreenMessage(
                \ilGlobalTemplateInterface::MESSAGE_TYPE_FAILURE,
                $this->lng->txt("no_valid_participant_selection"),
                true
            );

            $this->ctrl->redirectByClass(
                \ilTestParticipantsGUI::class,
                \ilTestParticipantsGUI::CMD_SHOW
            );
        }

        $this->onSubmit(
            $modal,
            $participants
        );

        $this->ctrl->redirectByClass(
            \ilTestParticipantsGUI::class,
            \ilTestParticipantsGUI::CMD_SHOW
        );
    }

    /**
     * @return array{URLBuilder, ...URLBuilderToken}
     */
    protected function acquireParameters(URLBuilder $url_builder): array
    {
        return $url_builder->acquireParameters(
            [$this->getActionId()],
            "p_id",
            "action"
        );
    }

    protected function resolveSelectedParticipants(array|string $selected_participants): array
    {
        $participant_list = $this->test_object->getActiveParticipantList();

        if ($selected_participants === 'ALL_OBJECTS') {
            $selected_participants = $participant_list->getAllActiveIds();
        }

        return array_filter(
            array_map(
                fn(int $user_id) => $this->repository->getParticipantByUserId($this->test_object->getTestId(), $user_id),
                $selected_participants
            ),
            fn(Participant $participant) => $this->allowActionForRecord($participant)
        );
    }

    abstract public function getActionId(): string;

    abstract protected function getModal(URLBuilder $url_builder, array|string $selected_participants): Modal|Standard;

    /**
     * @param Modal|Standard     $modal
     * @param array<Participant> $participants
     *
     * @return void
     */
    abstract protected function onSubmit(Modal|Standard $modal, array $participants): void;

    abstract protected function allowActionForRecord(Participant $record): bool;
}
