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

namespace ILIAS\SystemCheck\Table\Task;

use ilCtrl;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\DI\UIServices;
use ILIAS\HTTP\Services as HTTPServices;
use ILIAS\SystemCheck\I\Table\Task\DataRetrievalInterface;
use ILIAS\SystemCheck\I\Table\Task\HandlerInterface;
use ILIAS\UI\Component\Table\Data as DataTable;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken as ilURLBuilderToken;
use ilLanguage;
use ILIAS\Refinery\Factory as RefineryFactory;
use ilSCComponentTaskFactory;

class Handler implements HandlerInterface
{
    protected const string TABLE_ID = "sc_groups";
    protected const string TABLE_ACTION_ID = "table_action";
    protected const string ROW_ID = "row_ids";
    public const string TABLE_COL_TITLE = 'title';
    public const string TABLE_COL_DESCRIPTION = 'description';
    public const string TABLE_COL_LAST_UPDATE = 'last_update';
    public const string TABLE_COL_STATUS = 'status';
    protected const string LNG_TABLE_TITLE = 'sysc_task_overview';
    protected const string LNG_TABLE_COL_TITLE = 'title';
    protected const string LNG_TABLE_COL_DESCRIPTION = 'description';
    protected const string LNG_TABLE_COL_LAST_UPDATE = 'last_update';
    protected const string LNG_TABLE_COL_STATUS = 'status';

    protected DataTable $table;
    protected URLBuilder $url_builder;
    protected ilURLBuilderToken $action_parameter_token;
    protected ilURLBuilderToken $row_id_token;

    public function __construct(
        protected readonly DataFactory $data_factory,
        protected readonly UIServices $ui,
        protected readonly ilLanguage $lng,
        protected readonly HTTPServices $http,
        protected readonly DataRetrievalInterface $data_retrieval,
        protected readonly RefineryFactory $refinery,
        protected readonly ilCtrl $ctrl,
        protected readonly bool $actions_permitted = false
    ) {
        $this->initTable();
    }

    final protected function getColumns(): array
    {
        return [
            self::TABLE_COL_TITLE => $this->ui->factory()->table()->column()->text(
                $this->lng->txt(self::LNG_TABLE_COL_TITLE)
            )->withHighlight(true)->withIsSortable(true),
            self::TABLE_COL_DESCRIPTION => $this->ui->factory()->table()->column()->text(
                $this->lng->txt(self::LNG_TABLE_COL_DESCRIPTION)
            )->withHighlight(true)->withIsSortable(true),
            self::TABLE_COL_LAST_UPDATE => $this->ui->factory()->table()->column()->text(
                $this->lng->txt(self::LNG_TABLE_COL_LAST_UPDATE)
            )->withHighlight(true)->withIsSortable(true),
            self::TABLE_COL_STATUS => $this->ui->factory()->table()->column()->text(
                $this->lng->txt(self::LNG_TABLE_COL_STATUS)
            )->withHighlight(true)->withIsSortable(true)
        ];
    }

    final protected function getActions(): array
    {
        if (!$this->actions_permitted) {
            return [];
        }
        $this->url_builder = new URLBuilder($this->data_factory->uri($this->http->request()->getUri()->__toString()));
        list($this->url_builder, $this->action_parameter_token, $this->row_id_token) =
            $this->url_builder->acquireParameters(
                ['datatable', self::TABLE_ID],
                self::TABLE_ACTION_ID,
                self::ROW_ID
            );
        $actions = [];
        foreach ($this->data_retrieval->getAllActions() as $command => $txt) {
            $actions[$command] = $this->ui->factory()->table()->action()->single(
                $txt,
                $this->url_builder->withParameter($this->action_parameter_token, $command),
                $this->row_id_token
            );
        }
        return $actions;
    }

    final protected function initTable(): void
    {
        if (!isset($this->table)) {
            $this->table = $this->ui->factory()->table()->data(
                $this->data_retrieval,
                $this->lng->txt(self::LNG_TABLE_TITLE),
                $this->getColumns(),
            )
                ->withActions($this->getActions())
                ->withId(self::TABLE_ID)->withRequest($this->http->request());
        }
    }

    final protected function getTable(): DataTable
    {
        return $this->table;
    }

    final public function getHTML(): string
    {
        return $this->ui->renderer()->render([$this->getTable()]);
    }

    final public function handleTableActions(): void
    {
        if (!$this->http->wrapper()->query()->has($this->action_parameter_token->getName())) {
            return;
        }
        if (!$this->actions_permitted) {
            return;
        }
        $action = $this->http->wrapper()->query()->retrieve(
            $this->action_parameter_token->getName(),
            $this->refinery->to()->string()
        );
        $tokens = $this->http->wrapper()->query()->retrieve(
            $this->row_id_token->getName(),
            $this->refinery->kindlyTo()->listOf($this->refinery->to()->int())
        );
        $task_id = (int) $tokens[0];
        $task_handler = ilSCComponentTaskFactory::getComponentTask($task_id);
        $this->ctrl->setParameterByClass(get_class($task_handler), 'task_id', $task_id);
        $this->ctrl->redirectByClass(get_class($task_handler), $action);
        $this->ctrl->clearParameterByClass(get_class($task_handler), 'task_id');
    }
}
