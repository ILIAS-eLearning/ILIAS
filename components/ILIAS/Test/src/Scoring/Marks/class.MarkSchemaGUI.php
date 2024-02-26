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
use ILIAS\Test\Logging\TestAdministrationInteraction;
use ILIAS\Test\Logging\TestAdministrationInteractionTypes;

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
        private \ilCtrl $ctrl,
        private \ilGlobalTemplateInterface $tpl,
        private \ilToolbarGUI $toolbar,
        private \ilTabsGUI $tabs,
        private TestLogger $logger,
        private RequestWrapper $post_wrapper,
        private RequestWrapper $request_wrapper,
        private Request $request,
        private Refinery $refinery,
        private UIFactory $ui_factory,
        private UIRenderer $ui_renderer
    ) {
        $this->mark_schema = $test->getMarkSchema();
        $this->editable = $test->marksEditable();

        $url_builder = new URLBuilder(
            (new DataFactory())->uri(ILIAS_HTTP_PATH . '/' . $this->ctrl->getLinkTargetByClass([\ilObjTestGUI::class, self::class], self::DEFAULT_CMD))
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
        $this->tabs->activateSubTab(\ilTestTabsManager::SETTINGS_SUBTAB_ID_MARK_SCHEMA);
        $cmd = $this->ctrl->getCmd(self::DEFAULT_CMD);
        $this->$cmd();
    }

    protected function showMarkSchema(?RoundTripModal $add_mark_modal = null): void
    {
        if (!$this->editable) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('cannot_edit_marks'));
        }

        if ($this->runTableCommand()) {
            return;
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

        $confirmation_modal = $this->buildConfirmationModal();

        if ($add_mark_modal === null) {
            $add_mark_modal = $this->buildAddMarkModal();
        }

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

        $modal = $this->buildAddMarkModal()->withRequest($this->request);
        $data = $modal->getData();
        if ($data === null) {
            $this->showMarkSchema($modal->withOnLoad($modal->getShowSignal()));
            return;
        }

        $mark_steps = $this->mark_schema->getMarkSteps();
        $mark_steps[$data['index']] = $data['mark'];
        $this->mark_schema = $this->mark_schema->withMarkSteps($mark_steps);

        $this->test->storeMarkSchema(
            $this->mark_schema
        );
        $this->test->onMarkSchemaSaved();
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('saved_successfully'), true);

        if ($this->logger->isLoggingEnabled()) {
            $this->logger->logTestAdministrationInteraction(
                new TestAdministrationInteraction(
                    $this->lng,
                    $this->test->getRefId(),
                    $this->active_user,
                    TestAdministrationInteractionTypes::MARK_SCHEMA_MODIFIED,
                    time(),
                    $this->mark_schema->toLog($this->lng)
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
                new TestAdministrationInteraction(
                    $this->lng,
                    $this->test->getRefId(),
                    $this->active_user,
                    TestAdministrationInteractionTypes::MARK_SCHEMA_RESET,
                    time(),
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

        $new_schema = $this->buildAndCheckNewSchema($marks_to_be_deleted);

        if ($new_schema === null) {
            $this->showMarkSchema();
            return;
        }

        $this->mark_schema = $new_schema;
        $this->test->storeMarkSchema($new_schema);

        if ($this->logger->isLoggingEnabled()) {
            $this->logger->logTestAdministrationInteraction(
                new TestAdministrationInteraction(
                    $this->lng,
                    $this->test->getRefId(),
                    $this->active_user,
                    TestAdministrationInteractionTypes::MARK_SCHEMA_MODIFIED,
                    time(),
                    $this->mark_schema->toLog($this->lng)
                )
            );
        }

        $this->showMarkSchema();
    }

    private function buildConfirmationModal(): InterruptiveModal
    {
        return $this->ui_factory->modal()->interruptive(
            $this->lng->txt('tst_mark_reset_to_simple_mark_schema'),
            $this->lng->txt('tst_mark_reset_to_simple_mark_schema_confirmation'),
            $this->ctrl->getFormActionByClass(MarkSchemaGUI::class, 'resetToSimpleMarkSchema')
        )->withActionButtonLabel($this->lng->txt('tst_mark_reset_to_simple_mark_schema'));
    }

    private function buildAddMarkModal(Mark $mark = null, int $mark_index = -1): RoundTripModal
    {
        if ($mark === null) {
            $mark = new Mark();
        }
        return $this->ui_factory->modal()->roundtrip(
            $this->lng->txt('edit'),
            null,
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
        $edit_modal = $this->buildAddMarkModal($mark_steps[$affected_mark], $affected_mark);
        echo $this->ui_renderer->renderAsync($edit_modal);
        exit;
    }

    private function populateToolbar(InterruptiveModal $confirmation_modal, RoundTripModal $add_mark_modal): void
    {
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
        if ($action === null) {
            return;
        }

        $affected_marks = $this->getTableAffectedItemsFromQuery();

        if ($affected_marks === null) {
            echo $this->ui_renderer->render(
                $this->ui_factory->modal()->roundtrip(
                    $this->lng->txt('error'),
                    $this->ui_factory->messageBox()->failure($this->lng->txt('tst_delete_missing_mark'))
                )
            );
            exit;
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

    private function confirmMarkDeletion(array $affected_marks): void
    {
        $this->exitOnMarkSchemaNotEditable();
        $this->exitOnSchemaError($affected_marks);

        $confirm_delete_modal = $this->ui_factory->modal()->interruptive(
            $this->lng->txt('confirm'),
            $this->lng->txt('tst_mark_reset_to_simple_mark_schema_confirmation'),
            $this->ctrl->getFormActionByClass(MarkSchemaGUI::class, 'deleteMarkSteps')
        )->withActionButtonLabel($this->lng->txt('delete'))
        ->withAffectedItems($this->buildInteruptiveItems($affected_marks));

        echo $this->ui_renderer->renderAsync($confirm_delete_modal);
        exit;
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

        echo $this->ui_renderer->renderAsync(
            $this->ui_factory->modal()->roundtrip(
                $this->lng->txt('error'),
                $this->ui_factory->messageBox()->failure($this->lng->txt('permission_denied'))
            )
        );
        exit;
    }

    private function buildAndCheckNewSchema(array $affected_marks): ?MarkSchema
    {
        $message = $this->checkSchemaForErrors($affected_marks);

        if (!is_string($message)) {
            return $message;
        }

        $this->tpl->setOnScreenMessage('failure', $message);
        return null;
    }

    private function exitOnSchemaError(array $affected_marks): void
    {
        $message = $this->checkSchemaForErrors($affected_marks);

        if (!is_string($message)) {
            return;
        }

        echo $this->ui_renderer->render(
            $this->ui_factory->modal()->roundtrip(
                $this->lng->txt('error'),
                $this->ui_factory->messageBox()->failure($message)
            )
        );
        exit;
    }

    private function checkSchemaForErrors(array $affected_marks): MarkSchema|string
    {
        $new_marks = $this->mark_schema->getMarkSteps();
        foreach($affected_marks as $mark) {
            unset($new_marks[$mark]);
        }
        $local_schema = $this->mark_schema->withMarkSteps(array_values($new_marks));
        $messages = [];
        if ($local_schema->checkForMissingPassed()) {
            $messages[] = $this->lng->txt('no_passed_mark');
        }
        if ($local_schema->checkForMissingZeroPercentage()) {
            $messages[] = $this->lng->txt('min_percentage_ne_0');
        }

        if (isset($messages[1])) {
            $messages[0] .= '<br>' . $messages[1];
        }

        if ($messages === []) {
            return $local_schema;
        }

        return $messages[0];
    }
}
