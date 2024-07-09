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
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\UI\Component\Button\Standard as StandardButton;
use ILIAS\UI\Component\Modal\Interruptive as InterruptiveModal;

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @package components\ILIASTest
 */
class MarkSchemaGUI
{
    public function __construct(
        private MarkSchemaAware $object,
        private \ilObjUser $active_user,
        private \ilLanguage $lng,
        private \ilCtrl $ctrl,
        private \ilGlobalTemplateInterface $tpl,
        private \ilToolbarGUI $toolbar,
        private \ilTabsGUI $tabs,
        private TestLogger $logger,
        private RequestWrapper $post_wrapper,
        private Request $request,
        private Refinery $refinery,
        private UIFactory $ui_factory,
        private UIRenderer $ui_renderer
    ) {
    }

    public function executeCommand(): void
    {
        $this->tabs->activateTab(\ilTestTabsManager::TAB_ID_SETTINGS);
        $cmd = $this->ctrl->getCmd('showMarkSchema');
        $this->$cmd();
    }

    protected function ensureMarkSchemaCanBeEdited(): void
    {
        if (!$this->object->canEditMarks()) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('permission_denied'), true);
            $this->ctrl->redirect($this, 'showMarkSchema');
        }
    }

    protected function addMarkStep(): void
    {
        $this->ensureMarkSchemaCanBeEdited();

        $this->populateMarkSchemaFormData();
        $this->object->getMarkSchema()->addMarkStep();
        $this->showMarkSchema();
    }

    protected function populateMarkSchemaFormData(): bool
    {
        $no_save_error = true;
        $this->object->getMarkSchema()->flush();
        $postdata = $this->request->getParsedBody();
        foreach (array_keys($postdata) as $key) {
            if (preg_match('/mark_short_(\d+)/', $key, $matches)) {
                $passed = "0";
                if (isset($postdata["passed_$matches[1]"])) {
                    $passed = "1";
                }

                $percentage = str_replace(',', '.', \ilUtil::stripSlashes($postdata["mark_percentage_$matches[1]"]));
                if (!is_numeric($percentage)
                    || (float) $percentage < 0.0
                    || (float) $percentage > 100.0) {
                    $percentage = 0;
                    $no_save_error = false;
                }

                $this->object->getMarkSchema()->addMarkStep(
                    \ilUtil::stripSlashes($postdata["mark_short_$matches[1]"]),
                    \ilUtil::stripSlashes($postdata["mark_official_$matches[1]"]),
                    (float) $percentage,
                    (int) \ilUtil::stripSlashes($passed)
                );
            }
        }

        return $no_save_error;
    }

    protected function resetToSimpleMarkSchema(): void
    {
        $this->ensureMarkSchemaCanBeEdited();

        $mark_schema = $this->object->getMarkSchema();
        $mark_schema->createSimpleSchema(
            $this->lng->txt('failed_short'),
            $this->lng->txt('failed_official'),
            0,
            0,
            $this->lng->txt('passed_short'),
            $this->lng->txt('passed_official'),
            50,
            1
        );
        $this->object->storeMarkSchema($mark_schema);
        if ($this->logger->getLoggingEnabled()) {
            $this->logger->logTestAdministrationInteraction(
                new TestAdministrationInteraction(
                    $this->lng,
                    $this->object->getRefId(),
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
        $marks_trafo = $this->refinery->custom()->transformation(
            function ($vs): ?array {
                if ($vs === null || !is_array($vs)) {
                    return null;
                }
                return $vs;
            }
        );
        $deleted_mark_steps = null;
        if ($this->post_wrapper->has('marks')) {
            $deleted_mark_steps = $this->post_wrapper->retrieve(
                'marks',
                $marks_trafo
            );
        }

        $this->ensureMarkSchemaCanBeEdited();
        if (!isset($deleted_mark_steps) || !is_array($deleted_mark_steps)) {
            $this->showMarkSchema();
            return;
        }

        // test delete
        $mark_schema = clone $this->object->getMarkSchema();
        $mark_schema->deleteMarkSteps($deleted_mark_steps);
        $check_result = $mark_schema->checkMarks();
        if (is_string($check_result)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt($check_result), true);
            $this->showMarkSchema();
            return;
        }

        //  actual delete
        if (empty($deleted_mark_steps)) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('tst_delete_missing_mark'));
        }
        $this->object->storeMarkSchema($mark_schema);

        if ($this->logger->getLoggingEnabled()) {
            $this->logger->logTestAdministrationInteraction(
                new TestAdministrationInteraction(
                    $this->lng,
                    $this->object->getRefId(),
                    $this->active_user,
                    TestAdministrationInteractionTypes::MARK_SCHEMA_MODIFIED,
                    time(),
                    $this->object->getMarkSchema()->toLog()
                )
            );
        }

        $this->showMarkSchema();
    }

    protected function saveMarks(): void
    {
        $this->ensureMarkSchemaCanBeEdited();

        $result = 'mark_schema_invalid';
        if ($this->populateMarkSchemaFormData()) {
            $result = $this->object->checkMarks();
        }

        if (is_string($result)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt($result), true);
            $this->showMarkSchema();
            return;
        }

        $this->object->storeMarkSchema(
            $this->object->getMarkSchema()
        );
        $this->object->onMarkSchemaSaved();
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('saved_successfully'), true);

        if ($this->logger->getLoggingEnabled()) {
            $this->logger->logTestAdministrationInteraction(
                new TestAdministrationInteraction(
                    $this->lng,
                    $this->object->getRefId(),
                    $this->active_user,
                    TestAdministrationInteractionTypes::MARK_SCHEMA_MODIFIED,
                    time(),
                    $this->object->getMarkSchema()->toLog($this->lng)
                )
            );
        }

        $this->showMarkSchema();
    }

    protected function showMarkSchema(): void
    {
        if (!$this->object->canEditMarks()) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('cannot_edit_marks'));
        }

        $this->toolbar->setFormAction($this->ctrl->getFormAction($this, 'showMarkSchema'));

        $mark_schema_table = new MarkSchemaTableGUI($this, 'showMarkSchema', $this->object);
        $mark_schema_table->setShowRowsSelector(false);

        $rendered_modal = '';
        if ($this->object->canEditMarks()) {
            $confirmation_modal = $this->ui_factory->modal()->interruptive(
                $this->lng->txt('tst_mark_reset_to_simple_mark_schema'),
                $this->lng->txt('tst_mark_reset_to_simple_mark_schema_confirmation'),
                $this->ctrl->getFormAction($this, 'resetToSimpleMarkSchema')
            )->withActionButtonLabel($this->lng->txt('tst_mark_reset_to_simple_mark_schema'));
            $this->populateToolbar($confirmation_modal, $mark_schema_table->getId());
            $rendered_modal = $this->ui_renderer->render($confirmation_modal);
        }

        $this->tpl->setContent(
            $mark_schema_table->getHTML() . $rendered_modal
        );
    }

    private function populateToolbar(InterruptiveModal $confirmation_modal, string $mark_schema_id): void
    {
        $create_simple_schema_button = $this->ui_factory->button()->standard(
            $this->lng->txt('tst_mark_reset_to_simple_mark_schema'),
            $confirmation_modal->getShowSignal()
        );
        $this->toolbar->addComponent($create_simple_schema_button);

        $create_step_button = $this->buildCreateStepButton($mark_schema_id);
        $this->toolbar->addComponent($create_step_button);
    }

    private function buildCreateStepButton(string $mark_schema_id): StandardButton
    {
        return $this->ui_factory->button()->standard(
            $this->lng->txt('tst_mark_create_new_mark_step'),
            ''
        )->withAdditionalOnLoadCode(
            fn(string $id): string =>
            "{$id}.addEventListener('click', "
            . ' (e) => {'
            . '     e.preventDefault();'
            . '     e.target.name = "cmd[addMarkStep]";'
            . "     let form = document.getElementById('form_{$mark_schema_id}');"
            . '     let submitter = e.target.cloneNode();'
            . '     submitter.style.visibility = "hidden";'
            . '     form.appendChild(submitter);'
            . '     form.requestSubmit(submitter);'
            . ' }'
            . ');'
        );
    }
}
