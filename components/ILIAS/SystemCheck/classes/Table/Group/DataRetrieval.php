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

namespace ILIAS\SystemCheck\Table\Group;

use Generator;
use ilCtrl;
use ilDatePresentation;
use ilDateTime;
use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\DI\UIServices;
use ILIAS\SystemCheck\I\Table\Group\DataRetrievalInterface;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ilObjSystemCheckGUI;
use ilSCComponentTaskFactory;
use ilSCGroup;
use ilSCGroups;
use ilSCTasks;

class DataRetrieval implements DataRetrievalInterface
{
    /* @var ilSCGroup[] */
    protected array $groups;

    public function __construct(
        protected readonly UIServices $ui,
        protected readonly ilCtrl $ctrl
    ) {
    }

    /**
     * @return ilSCGroup[]
     */
    final protected function getSCGroups(): array
    {
        return $this->groups ??= ilSCGroups::getInstance()->getGroups();
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
        $titles = $descriptions = $last_updates = $complete = $failed = [];
        foreach ($this->getSCGroups() as $group) {
            $task_gui = ilSCComponentTaskFactory::getComponentTaskGUIForGroup($group->getId());
            $titles[$group->getId()] = $task_gui->getGroupTitle();
            $descriptions[$group->getId()] = $task_gui->getGroupDescription();
            $complete[$group->getId()] = ilSCTasks::lookupCompleted($group->getId());
            $failed[$group->getId()] = ilSCTasks::lookupFailed($group->getId());
            $last_updates[$group->getId()] = ilSCTasks::lookupLastUpdate($group->getId());
        }
        $comparator = match ($column_name) {
            Handler::TABLE_COL_TITLE => function (ilSCGroup $f1, ilSCGroup $f2) use ($titles) {
                return strcasecmp($titles[$f1->getId()], $titles[$f2->getId()]);
            },
            Handler::TABLE_COL_DESCRIPTION => function (ilSCGroup $f1, ilSCGroup $f2) use ($descriptions) {
                return strcasecmp($descriptions[$f1->getId()], $descriptions[$f2->getId()]);
            },
            Handler::TABLE_COL_LAST_UPDATE => function (ilSCGroup $f1, ilSCGroup $f2) use ($last_updates) {
                if (ilDateTime::_equals($last_updates[$f1->getId()], $last_updates[$f2->getId()])) {
                    return 0;
                }
                return ilDateTime::_before($last_updates[$f1->getId()], $last_updates[$f2->getId()]) ? -1 : 1;
            },
            Handler::TABLE_COL_SOLVED_TASKS => function (ilSCGroup $f1, ilSCGroup $f2) use ($complete) {
                return $complete[$f1->getId()] - $complete[$f2->getId()];
            },
            Handler::TABLE_COL_UNSOLVED_TASKS => function (ilSCGroup $f1, ilSCGroup $f2) use ($failed) {
                return $failed[$f1->getId()] - $failed[$f2->getId()];
            },
            default => fn(ilSCGroup $f1, ilSCGroup $f2) => 0
        };
        $sc_groups = $this->getSCGroups();
        uasort($sc_groups, $comparator);
        if ($direction === "DESC") {
            $sc_groups = array_reverse($sc_groups, true);
        }
        $sc_groups = array_slice($sc_groups, $range->getStart(), $range->getLength(), true);
        foreach ($sc_groups as $sc_group) {
            $this->ctrl->setParameterByClass(ilObjSystemCheckGUI::class, 'grp_id', $sc_group->getId());
            $link = $this->ctrl->getLinkTargetByClass(ilObjSystemCheckGUI::class, 'showGroup');
            $this->ctrl->clearParameterByClass(ilObjSystemCheckGUI::class, 'grp_id');
            yield $row_builder->buildDataRow(
                $sc_group->getId() . '',
                [
                    Handler::TABLE_COL_TITLE => $this->ui->factory()->link()->standard($titles[$sc_group->getId()], $link),
                    Handler::TABLE_COL_DESCRIPTION => $descriptions[$sc_group->getId()],
                    Handler::TABLE_COL_LAST_UPDATE => ilDatePresentation::formatDate($last_updates[$sc_group->getId()]),
                    Handler::TABLE_COL_SOLVED_TASKS => $complete[$sc_group->getId()],
                    Handler::TABLE_COL_UNSOLVED_TASKS => $failed[$sc_group->getId()],
                ]
            );
        }
    }

    final public function getTotalRowCount(
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): ?int {
        return count($this->getSCGroups());
    }
}
