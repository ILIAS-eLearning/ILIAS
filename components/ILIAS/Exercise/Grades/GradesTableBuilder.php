<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of the said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\Exercise\Grades;

use ILIAS\Exercise\InternalDomainService;
use ILIAS\Exercise\InternalGUIService;
use ILIAS\Repository\RetrievalInterface;
use ILIAS\Repository\Table\CommonTableBuilder;
use ILIAS\Repository\Table\TableAdapterGUI;

class GradesTableBuilder extends CommonTableBuilder
{
    /**
     * @param \ilExAssignment[] $assignments
     */
    public function __construct(
        protected InternalDomainService $domain,
        protected InternalGUIService $gui,
        protected \ilObjExercise $exercise,
        protected \ilExerciseMembers $members,
        protected array $assignments,
        object $parent_gui,
        string $parent_cmd
    ) {
        parent::__construct($parent_gui, $parent_cmd);
    }

    protected function getId(): string
    {
        return 'exc_grades_' . $this->exercise->getId();
    }

    protected function getTitle(): string
    {
        return $this->domain->lng()->txt('exc_grades');
    }

    protected function getRetrieval(): RetrievalInterface
    {
        return $this->domain->gradesRetrieval(
            $this->exercise,
            $this->members,
            $this->assignments
        );
    }

    protected function transformRow(array $data_row): array
    {
        $lng = $this->domain->lng();
        $factory = $this->gui->ui()->factory();
        $ctrl = $this->gui->ctrl();

        $ctrl->setParameter($this->parent_gui, 'part_id', $data_row['id']);
        $target = $ctrl->getLinkTarget($this->parent_gui, 'showParticipant');
        $ctrl->setParameter($this->parent_gui, 'part_id', '');

        $row = [
            'id' => $data_row['id'],
            'name' => $factory->link()->standard(
                $data_row['name'] . ' [' . $data_row['login'] . ']',
                $target
            ),
            'total' => $this->getStatusIcon($data_row['total']),
            'mark' => $data_row['mark'],
            'remark' => $data_row['remark']
        ];

        foreach ($this->assignments as $assignment) {
            $key = 'assignment_' . $assignment->getId();
            $row[$key] = $this->getStatusIcon($data_row[$key]);
        }

        return $row;
    }

    protected function build(TableAdapterGUI $table): TableAdapterGUI
    {
        $lng = $this->domain->lng();
        $lng->loadLanguageModule('trac');

        $table = $table->linkColumn('name', $lng->txt('name'), true);
        foreach ($this->assignments as $assignment) {
            $table = $table->iconColumn(
                'assignment_' . $assignment->getId(),
                $assignment->getTitle()
            );
        }

        return $table
            ->iconColumn('total', $lng->txt('exc_total_exc'))
            ->textColumn('mark', $lng->txt('exc_mark'))
            ->textColumn('remark', $lng->txt('trac_comment'))
            ->singleAction('editMark', $lng->txt('exc_mark'), true)
            ->singleAction('editRemark', $lng->txt('trac_comment'), true);
    }

    protected function getStatusIcon(string $status): \ILIAS\UI\Component\Symbol\Icon\Icon
    {
        $icons = \ilLPStatusIcons::getInstance(\ilLPStatusIcons::ICON_VARIANT_LONG);
        $lng = $this->domain->lng();

        switch ($status) {
            case 'passed':
                $path = $icons->getImagePathCompleted();
                break;
            case 'failed':
                $path = $icons->getImagePathFailed();
                break;
            default:
                $path = $icons->getImagePathNotAttempted();
                break;
        }

        return $icons->getIconComponent($path, $lng->txt('exc_' . $status));
    }
}
