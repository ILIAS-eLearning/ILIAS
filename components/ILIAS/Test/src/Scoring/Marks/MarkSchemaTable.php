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

use ILIAS\UI\Component\Table\Data as DataTable;
use ILIAS\UI\Component\Table\DataRetrieval;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ILIAS\HTTP\Wrapper\RequestWrapper;
use ILIAS\Data\Range;
use ILIAS\Data\Order;

class MarkSchemaTable implements DataRetrieval
{
    private const DELETE_ACTION_NAME = 'delete';
    private const EDIT_ACTION_NAME = 'edit';
    private URLBuilder $url_builder;
    private URLBuilderToken $action_parameter_token;
    private URLBuilderToken $row_id_token;

    public function __construct(
        private MarkSchema $mark_schema,
        private bool $can_edit_marks,
        private \ilLanguage $lng,
        private RequestWrapper $request_wrapper,
        URLBuilder $url_builder,
        private UIFactory $ui_factory,
        private UIRenderer $ui_renderer
    ) {
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

    public function getTable(): DataTable
    {
        $f = $this->ui_factory->table();

        $table = $f->data(
            'mark_schema',
            [
                'name' => $f->column()->text($this->lng->txt('tst_mark_short_form')),
                'official_name' => $f->column()->text($this->lng->txt('tst_mark_official_form')),
                'minimum_level' => $f->column()->text($this->lng->txt('tst_mark_minimum_level')),
                'passed' => $f->column()->text($this->lng->txt('tst_mark_passed'))
            ],
            $this
        )->withActions(
            [
                self::EDIT_ACTION_NAME => $f->action()->single(
                    $this->lng->txt('edit'),
                    $this->url_builder->withParameter($this->action_parameter_token, self::EDIT_ACTION_NAME),
                    $this->row_id_token
                )->withAsync(),
                self::DELETE_ACTION_NAME => $f->action()->standard(
                    $this->lng->txt('delete'),
                    $this->url_builder->withParameter($this->action_parameter_token, self::DELETE_ACTION_NAME),
                    $this->row_id_token
                )->withAsync()
            ]
        );

        return $table;
    }

    public function getRows(
        DataRowBuilder $row_builder,
        array $visible_column_ids,
        Range $range,
        Order $order,
        ?array $filter_data,
        ?array $additional_parameters
    ): \Generator {
        foreach ($this->mark_schema->getMarkSteps() as $mark) {
            yield $row_builder->buildDataRow(
                $mark->getShortName(),
                [
                    'name' => $mark->getShortName(),
                    'official_name' => $mark->getOfficialName(),
                    'minimum_level' => $mark->getMinimumLevel(),
                    'passed' => $mark->getPassed()
                ]
            )->withDisabledAction('edit', !$this->can_edit_marks)
            ->withDisabledAction('delete', !$this->can_edit_marks);
        }
    }

    public function getTotalRowCount(
        ?array $filter_data,
        ?array $additional_parameters
    ): ?int {
        return count($this->mark_schema->getMarkSteps());
    }

    public function runTableCommand(): bool
    {
        $action = $this->getTableActionQueryString();
        if ($action === null) {
            return false;
        }

        switch ($action) {
            case self::EDIT_ACTION_NAME:
                return true;

            case self::DELETE_ACTION_NAME:
                $this->confirmMarkDeletion();
                return true;
        }

        return false;
    }

    protected function confirmMarkDeletion(): void
    {
        if (!$this->object->canEditMarks()) {
            echo $this->ui_factory->messageBox()->failure(
                $this->lng->txt('permission_denied')
            );
        }
        $confirmation_modal = $this->ui_factory->modal()->interruptive(
            $this->lng->txt('tst_mark_reset_to_simple_mark_schema'),
            $this->lng->txt('tst_mark_reset_to_simple_mark_schema_confirmation'),
            $this->ctrl->getFormAction($this, 'resetToSimpleMarkSchema')
        )->withActionButtonLabel($this->lng->txt('tst_mark_reset_to_simple_mark_schema'));
        $this->populateToolbar($confirmation_modal, $this->object->getMarkSchemaForeignId());
        echo $this->ui_renderer->render($confirmation_modal);
    }

    protected function getTableActionQueryString(): ?string
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
}
