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

namespace ILIAS\Test\Scoring\Marks;

use ILIAS\Test\Logging\TestLogger;
use ILIAS\Test\Logging\TestAdministrationInteractionTypes;
use ILIAS\Test\ResponseHandler;
use ILIAS\HTTP\Wrapper\RequestWrapper;
use GuzzleHttp\Psr7\Request;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\UI\Component\Modal\Interruptive as InterruptiveModal;
use ILIAS\UI\Component\Modal\RoundTrip as RoundTripModal;

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @package components\ILIASTest
 */
class MarkSchemaGUI
{
    private const DEFAULT_CMD = 'showMarkSchema';
    private MarkSchema $mark_schema;
    private bool $editable;
    private URLBuilder $url_builder;
    private URLBuilderToken $action_parameter_token;
    private URLBuilderToken $row_id_token;

    public function __construct(
        private \ilObjTest $test,
        private \ilObjUser $active_user,
        private \ilLanguage $lng,
        private \ilCtrlInterface $ctrl,
        private \ilGlobalTemplateInterface $tpl,
        private \ilToolbarGUI $toolbar,
        private TestLogger $logger,
        private RequestWrapper $post_wrapper,
        private RequestWrapper $request_wrapper,
        private ResponseHandler $response_handler,
        private Request $request,
        private Refinery $refinery,
        private UIFactory $ui_factory,
        private UIRenderer $ui_renderer
    ) {
        $this->mark_schema = $test->getMarkSchema();
        $this->editable = $test->marksEditable();
        $uri = ILIAS_HTTP_PATH . '/' . $this->ctrl->getLinkTargetByClass([\ilRepositoryGUI::class, \ilObjTestGUI::class, self::class], self::DEFAULT_CMD);
        $url_builder = new URLBuilder(
            (new DataFactory())->uri($uri)
        );

        list(
            $this->url_builder,
            $this->action_parameter_token,
            $this->row_id_token
        ) = $url_builder->acquireParameters(
            ['marks', 'overview_table'],
            'action', //this is the actions's parameter name
            'step_id'   //this is the parameter name to be used for row-ids
        );
    }

    public function executeCommand(): void
    {
        $cmd = $this->ctrl->getCmd(self::DEFAULT_CMD);
        $this->$cmd();
    }

    protected function showMarkSchema(?RoundTripModal $add_mark_modal = null): void
    {
        if (!$this->editable) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('cannot_edit_marks'));
        }

        $this->runTableCommand();

        if ($add_mark_modal === null) {
            $add_mark_modal = $this->buildMarkModal();
        }

        $mark_schema_table = new MarkSchemaTable(
            $this->mark_schema,
            $this->editable,
            $this->lng,
            $this->url_builder,
            $this->action_parameter_token,
            $this->row_id_token,
            $this->ui_factory
        );

        $confirmation_modal = $this->buildResetConfirmationModal();

        $this->populateToolbar($confirmation_modal, $add_mark_modal);

        $this->tpl->setContent(
            $this->ui_renderer->render([
                $mark_schema_table->getTable()->withRequest($this->request),
                $confirmation_modal,
                $add_mark_modal
            ])
        );
    }

    protected function saveMark(): void
    {
        $this->redirectOnMarkSchemaNotEditable();

        $modal = $this->buildMarkModal()->withRequest($this->request);
        $data = $modal->getData();
        if ($data === null) {
            $this->showMarkSchema($modal->withOnLoad($modal->getShowSignal()));
            return;
        }

        $mark_steps = $this->mark_schema->getMarkSteps();
        $mark_steps[$data['index']] = $data['mark'];
        $new_schema = $this->checkSchemaForErrors($this->mark_schema->withMarkSteps($mark_steps));
        if (is_string($new_schema)) {
            $this->tpl->setOnScreenMessage('failure', $new_schema);
            $this->showMarkSchema();
            return;
        }
        $this->mark_schema = $new_schema;
        $this->test->storeMarkSchema(
            $this->mark_schema
        );
        $this->test->onMarkSchemaSaved();
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('saved_successfully'), true);

        if ($this->logger->isLoggingEnabled()) {
            $this->logger->logTestAdministrationInteraction(
                $this->logger->getInteractionFactory()->buildTestAdministrationInteraction(
                    $this->test->getRefId(),
                    $this->active_user->getId(),
                    TestAdministrationInteractionTypes::MARK_SCHEMA_MODIFIED,
                    $this->mark_schema->toLog($this->logger->getAdditionalInformationGenerator())
                )
            );
        }

        $this->showMarkSchema();
    }

    protected function resetToSimpleMarkSchema(): void
    {
        $this->redirectOnMarkSchemaNotEditable();

        $this->mark_schema = $this->mark_schema->createSimpleSchema(
            $this->lng->txt('failed_short'),
            $this->lng->txt('failed_official'),
            0,
            false,
            $this->lng->txt('passed_short'),
            $this->lng->txt('passed_official'),
            50,
            true
        );
        $this->test->storeMarkSchema($this->mark_schema);
        if ($this->logger->isLoggingEnabled()) {
            $this->logger->logTestAdministrationInteraction(
                $this->logger->getInteractionFactory()->buildTestAdministrationInteraction(
                    $this->test->getRefId(),
                    $this->active_user->getId(),
                    TestAdministrationInteractionTypes::MARK_SCHEMA_RESET,
                    []
                )
            );
        }
        $this->showMarkSchema();
    }

    protected function deleteMarkSteps(): void
    {
        $this->redirectOnMarkSchemaNotEditable();

        if (!$this->post_wrapper->has('interruptive_items')) {
            $this->showMarkSchema();
            return;
        }

        $marks_to_be_deleted = $this->post_wrapper->retrieve(
            'interruptive_items',
            $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int())
        );

        $new_schema = $this->removeMarksAndCheckNewSchema($marks_to_be_deleted);
        if (is_string($new_schema)) {
            $this->tpl->setOnScreenMessage('failure', $new_schema);
            $this->showMarkSchema();
            return;
        }

        $this->mark_schema = $new_schema;
        $this->test->storeMarkSchema($new_schema);

        if ($this->logger->isLoggingEnabled()) {
            $this->logger->logTestAdministrationInteraction(
                $this->logger->getInteractionFactory()->buildTestAdministrationInteraction(
                    $this->test->getRefId(),
                    $this->active_user->getId(),
                    TestAdministrationInteractionTypes::MARK_SCHEMA_MODIFIED,
                    $this->mark_schema->toLog($this->logger->getAdditionalInformationGenerator())
                )
            );
        }

        $this->showMarkSchema();
    }

    private function buildResetConfirmationModal(): InterruptiveModal
    {
        return $this->ui_factory->modal()->interruptive(
            $this->lng->txt('tst_mark_reset_to_simple_mark_schema'),
            $this->lng->txt('tst_mark_reset_to_simple_mark_schema_confirmation'),
            $this->ctrl->getFormActionByClass(MarkSchemaGUI::class, 'resetToSimpleMarkSchema')
        )->withActionButtonLabel($this->lng->txt('tst_mark_reset_to_simple_mark_schema'));
    }

    private function buildMarkModal(?Mark $mark = null, int $mark_index = -1): RoundTripModal
    {
        $title_lng_var = 'edit';
        if ($mark === null) {
            $title_lng_var = 'create';
            $mark = new Mark();
        }
        return $this->ui_factory->modal()->roundtrip(
            $this->lng->txt($title_lng_var),
            [],
            [
                'mark' => $mark->toForm(
                    $this->lng,
                    $this->ui_factory->input()->field(),
                    $this->refinery,
                    $this->mark_schema
                ),
                'index' => $this->ui_factory->input()->field()->hidden()
                    ->withValue($mark_index)
            ],
            $this->ctrl->getFormActionByClass(MarkSchemaGUI::class, 'saveMark')
        );
    }

    private function editMark(array $affected_marks): void
    {
        $this->exitOnMarkSchemaNotEditable();

        $affected_mark = current($affected_marks);
        $mark_steps = $this->mark_schema->getMarkSteps();
        $this->response_handler->sendAsync(
            $this->ui_renderer->renderAsync(
                $this->buildMarkModal($mark_steps[$affected_mark], $affected_mark)
            )
        );
    }

    private function populateToolbar(InterruptiveModal $confirmation_modal, RoundTripModal $add_mark_modal): void
    {
        if (!$this->editable) {
            return;
        }
        $create_simple_schema_button = $this->ui_factory->button()->standard(
            $this->lng->txt('tst_mark_reset_to_simple_mark_schema'),
            $confirmation_modal->getShowSignal()
        );
        $this->toolbar->addComponent($create_simple_schema_button);

        $add_mark_button = $this->ui_factory->button()->standard(
            $this->lng->txt('tst_mark_create_new_mark_step'),
            $add_mark_modal->getShowSignal()
        );
        $this->toolbar->addComponent($add_mark_button);
    }

    public function runTableCommand(): void
    {
        $action = $this->getTableActionQueryString();
        if (!$this->editable || $action === null) {
            return;
        }

        $affected_marks = $this->getTableAffectedItemsFromQuery();
        if ($affected_marks === null) {
            $this->response_handler->sendAsync(
                $this->ui_renderer->renderAsync(
                    $this->ui_factory->modal()->roundtrip(
                        $this->lng->txt('error'),
                        $this->ui_factory->messageBox()->failure($this->lng->txt('tst_delete_missing_mark'))
                    )
                )
            );
        }

        switch ($action) {
            case MarkSchemaTable::EDIT_ACTION_NAME:
                $this->editMark($affected_marks);
                break;

            case MarkSchemaTable::DELETE_ACTION_NAME:
                $this->confirmMarkDeletion($affected_marks);
                break;
        }
    }

    private function confirmMarkDeletion(array $marks_to_delete): void
    {
        $this->exitOnMarkSchemaNotEditable();
        $this->exitOnSchemaError($this->removeMarksAndCheckNewSchema($marks_to_delete));

        $confirm_delete_modal = $this->ui_factory->modal()->interruptive(
            $this->lng->txt('confirm'),
            $this->lng->txt('delete_mark_confirmation'),
            $this->ctrl->getFormActionByClass(MarkSchemaGUI::class, 'deleteMarkSteps')
        )->withActionButtonLabel($this->lng->txt('delete'))
        ->withAffectedItems($this->buildInteruptiveItems($marks_to_delete));

        $this->response_handler->sendAsync(
            $this->ui_renderer->renderAsync($confirm_delete_modal)
        );
    }

    private function buildInteruptiveItems(array $affected_marks): array
    {
        $mark_steps = $this->mark_schema->getMarkSteps();
        $marks_to_be_deleted = [];
        foreach ($affected_marks as $affected_mark) {
            $marks_to_be_deleted[] = $this->ui_factory->modal()->interruptiveItem()->standard(
                (string) $affected_mark,
                $mark_steps[$affected_mark]->getOfficialName()
            );
        }
        return $marks_to_be_deleted;
    }

    private function getTableActionQueryString(): ?string
    {
        $param = $this->action_parameter_token->getName();
        if (!$this->request_wrapper->has($param)) {
            return null;
        }
        $trafo = $this->refinery->byTrying([
            $this->refinery->kindlyTo()->null(),
            $this->refinery->kindlyTo()->string()
        ]);
        return $this->request_wrapper->retrieve($param, $trafo);
    }

    private function getTableAffectedItemsFromQuery(): ?array
    {
        $affected_marks = $this->request_wrapper->retrieve(
            $this->row_id_token->getName(),
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->int(),
                $this->refinery->container()->mapValues(
                    $this->refinery->kindlyTo()->int()
                ),
                $this->refinery->identity()
            ])
        );

        if (is_int($affected_marks)) {
            $affected_marks = [$affected_marks];
        }

        return $affected_marks;
    }

    protected function redirectOnMarkSchemaNotEditable(): void
    {
        if ($this->editable) {
            return;
        }

        $this->tpl->setOnScreenMessage('failure', $this->lng->txt('permission_denied'), true);
        $this->ctrl->redirect($this, 'showMarkSchema');
    }

    private function exitOnMarkSchemaNotEditable(): void
    {
        if ($this->editable) {
            return;
        }

        $this->response_handler->sendAsync(
            $this->ui_renderer->renderAsync(
                $this->ui_factory->modal()->roundtrip(
                    $this->lng->txt('error'),
                    $this->ui_factory->messageBox()->failure($this->lng->txt('permission_denied'))
                )
            )
        );
        exit;
    }

    private function removeMarksAndCheckNewSchema(array $marks_to_delete): MarkSchema|String
    {
        $new_marks = $this->mark_schema->getMarkSteps();
        foreach ($marks_to_delete as $mark) {
            unset($new_marks[$mark]);
        }

        return $this->checkSchemaForErrors(
            $this->mark_schema->withMarkSteps(array_values($new_marks))
        );
    }

    private function exitOnSchemaError(MarkSchema|string $checked_value): MarkSchema
    {
        if (!is_string($checked_value)) {
            return $checked_value;
        }

        $this->response_handler->sendAsync(
            $this->ui_renderer->render(
                $this->ui_factory->modal()->roundtrip(
                    $this->lng->txt('error'),
                    $this->ui_factory->messageBox()->failure($checked_value)
                )
            )
        );
    }

    private function checkSchemaForErrors(MarkSchema $new_schema): MarkSchema|string
    {
        $messages = [];
        if ($new_schema->checkForMissingPassed()) {
            $messages[] = $this->lng->txt('no_passed_mark');
        }
        if ($new_schema->checkForMissingZeroPercentage()) {
            $messages[] = $this->lng->txt('min_percentage_ne_0');
        }
        if ($new_schema->checkForFailedAfterPassed()) {
            $messages[] = $this->lng->txt('no_passed_after_failed');
        }

        if (isset($messages[1])) {
            $messages[0] .= '<br>' . $messages[1];
        }

        if ($messages === []) {
            return $new_schema;
        }

        return $messages[0];
    }
}
