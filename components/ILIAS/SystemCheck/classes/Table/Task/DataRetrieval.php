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

use Generator;
use ilDatePresentation;
use ilDateTime;
use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\SystemCheck\I\Table\Task\DataRetrievalInterface;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ilSCComponentTaskFactory;
use ilSCTask;
use ilSCTasks;
use ilSCTreeTasksGUI;
use ilSCUtils;

class DataRetrieval implements DataRetrievalInterface
{
    /** @var ilSCTask[] */
    protected array $sc_tasks;
    /** @var string[] */
    protected array $actions;

    public function __construct(
        protected readonly int $group_id
    ) {
    }

    /**
     * @return ilSCTask[]
     */
    final public function getSCTasks(): array
    {
        if (isset($this->sc_tasks)) {
            return $this->sc_tasks;
        }

        $this->sc_tasks = [];
        foreach (ilSCTasks::getInstanceByGroupId($this->group_id)->getTasks() as $task) {
            if (!$task->isActive()) {
                continue;
            }
            $this->sc_tasks[$task->getId()] = $task;
        }
        return $this->sc_tasks;
    }

    final public function getRows(
        DataRowBuilder $row_builder,
        array $visible_column_ids,
        Range $range,
        Order $order,
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): Generator {
        [$column_name, $direction] = $order->join([], fn($ret, $key, $value) => [$key, $value]);
        $titles = $descriptions = $last_updates = $status = [];
        foreach ($this->getSCTasks() as $task) {
            $task_handler = ilSCComponentTaskFactory::getComponentTask($task->getId());
            $titles[$task->getId()] = $task_handler->getTitle();
            $descriptions[$task->getId()] = $task_handler->getDescription();
            $last_updates[$task->getId()] = $task->getLastUpdate();
            $status[$task->getId()] = $task->getStatus();
        }
        $comparator = match ($column_name) {
            Handler::TABLE_COL_TITLE => function (ilSCTask $f1, ilSCTask $f2) use ($titles) {
                return strcasecmp($titles[$f1->getId()], $titles[$f2->getId()]);
            },
            Handler::TABLE_COL_DESCRIPTION => function (ilSCTask $f1, ilSCTask $f2) use ($descriptions) {
                return strcasecmp($descriptions[$f1->getId()], $descriptions[$f2->getId()]);
            },
            Handler::TABLE_COL_LAST_UPDATE => function (ilSCTask $f1, ilSCTask $f2) use ($last_updates) {
                if (ilDateTime::_equals($last_updates[$f1->getId()], $last_updates[$f2->getId()])) {
                    return 0;
                }
                return ilDateTime::_before($last_updates[$f1->getId()], $last_updates[$f2->getId()]) ? -1 : 1;
            },
            Handler::TABLE_COL_STATUS => function (ilSCTask $f1, ilSCTask $f2) use ($status) {
                return $status[$f1->getId()] - $status[$f2->getId()];
            },
            default => fn(ilSCTask $f1, ilSCTask $f2) => 0
        };
        $sc_tasks = $this->getSCTasks();
        uasort($sc_tasks, $comparator);
        if ($direction === "DESC") {
            $sc_tasks = array_reverse($sc_tasks, true);
        }
        $sc_tasks = array_slice($sc_tasks, $range->getStart(), $range->getLength(), true);
        foreach ($sc_tasks as $sc_task) {
            $data_row = $row_builder->buildDataRow(
                (string) $sc_task->getId(),
                [
                    Handler::TABLE_COL_TITLE => $titles[$sc_task->getId()],
                    Handler::TABLE_COL_DESCRIPTION => $descriptions[$sc_task->getId()],
                    Handler::TABLE_COL_LAST_UPDATE => ilDatePresentation::formatDate($last_updates[$sc_task->getId()]),
                    Handler::TABLE_COL_STATUS => ilSCUtils::taskStatus2Text($status[$sc_task->getId()])
                ]
            );
            foreach ($this->getInactiveActions($sc_task) as $action_name) {
                $data_row = $data_row->withDisabledAction($action_name);
            }
            yield $data_row;
        }
    }

    final public function getTotalRowCount(
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): ?int {
        return count($this->getSCTasks());
    }

    /** @return array<string, string> */
    final public function getAllActions(): array
    {
        if (isset($this->actions)) {
            return $this->actions;
        }
        $this->actions = [];
        foreach ($this->getSCTasks() as $task) {
            $task_gui = ilSCComponentTaskFactory::getComponentTask($task->getId());
            foreach ($task_gui->getActions() as $action) {
                $this->actions[$action['command']] = $action['txt'];
            }
        }
        return $this->actions;
    }

    final protected function getInactiveActions(
        ilSCTask $task
    ): array {
        $active_actions = [];
        $task_gui = ilSCComponentTaskFactory::getComponentTask($task->getId());
        foreach ($task_gui->getActions() as $action) {
            $active_actions[$action['command']] = $action['txt'];
        }
        return array_diff(array_keys($this->getAllActions()), array_keys($active_actions));
    }
}
