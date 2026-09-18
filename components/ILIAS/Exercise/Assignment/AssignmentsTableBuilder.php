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

namespace ILIAS\Exercise\Assignment;

use ILIAS\Exercise\InternalDomainService;
use ILIAS\Exercise\InternalGUIService;
use ILIAS\Repository\RetrievalInterface;
use ILIAS\Repository\Table\CommonTableBuilder;
use ILIAS\Repository\Table\TableAdapterGUI;

class AssignmentsTableBuilder extends CommonTableBuilder
{
    public function __construct(
        protected InternalDomainService $domain,
        protected InternalGUIService $gui,
        protected int $exercise_id,
        object $parent_gui,
        string $parent_cmd
    ) {
        parent::__construct($parent_gui, $parent_cmd);
    }

    protected function getId(): string
    {
        return 'exc_assignments_' . $this->exercise_id;
    }

    protected function getTitle(): string
    {
        return $this->domain->lng()->txt('exc_assignments');
    }

    protected function getRetrieval(): RetrievalInterface
    {
        return $this->domain->assignment()->assignmentsRetrieval($this->exercise_id);
    }

    protected function getOrderingCommand(): string
    {
        return 'saveAssignmentOrder';
    }

    protected function transformRow(array $data_row): array
    {
        $lng = $this->domain->lng();
        $ctrl = $this->gui->ctrl();
        $assignment = new \ilExAssignment($data_row['id']);

        $peer = $data_row['peer']
            ? $lng->txt('yes') . ' (' . $data_row['peer_min'] . ')'
            : $lng->txt('no');

        if ($data_row['peer']) {
            if ($data_row['peer_invalid']) {
                $peer .= '<br><span class="warning">' . $lng->txt('exc_peer_reviews_invalid_warning') . '</span>';
            }

            if ($assignment->afterDeadlineStrict()) {
                $ctrl->setParameter($this->parent_gui, 'ass_id', $data_row['id']);
                $peer .= '<br>' . $this->gui->ui()->factory()->link()->standard(
                    $lng->txt('exc_peer_review_overview'),
                    $ctrl->getLinkTargetByClass('ilexpeerreviewgui', 'showPeerReviewOverview')
                )->render();
                $ctrl->setParameter($this->parent_gui, 'ass_id', '');
            }
        }

        return [
            'id' => $data_row['id'],
            'title' => $data_row['title'],
            'type' => $data_row['type'],
            'order_val' => $data_row['order_val'],
            'start_time' => $data_row['start_time'] > 0
                ? ilDatePresentation::formatDate(new \ilDateTime($data_row['start_time'], IL_CAL_UNIX))
                : '',
            'deadline' => $this->getDeadline($assignment, $data_row),
            'mandatory' => $data_row['random']
                ? $lng->txt('exc_random')
                : ($data_row['mandatory'] ? $lng->txt('yes') : $lng->txt('no')),
            'peer' => $peer,
            'instruction' => nl2br(trim(\ilStr::shortenTextExtended(strip_tags($data_row['instruction']), 200, true)))
        ];
    }

    protected function build(TableAdapterGUI $table): TableAdapterGUI
    {
        $lng = $this->domain->lng();

        return $table
            ->textColumn('title', $lng->txt('title'), true)
            ->textColumn('type', $lng->txt('exc_assignment_type'), true)
            ->textColumn('start_time', $lng->txt('exc_start_time'), true)
            ->textColumn('deadline', $lng->txt('exc_deadline'), true)
            ->textColumn('mandatory', $lng->txt('exc_mandatory'), true)
            ->textColumn('peer', $lng->txt('exc_peer_review'), true)
            ->textColumn('instruction', $lng->txt('exc_instruction'))
            ->singleRedirectAction(
                'editAssignment',
                $lng->txt('edit'),
                [\ilExAssignmentEditorGUI::class],
                'editAssignment',
                'ass_id'
            )
            ->singleAction('confirmAssignmentDeletion', $lng->txt('delete'), true);
    }

    protected function getDeadline(\ilExAssignment $assignment, array $data_row): string
    {
        $lng = $this->domain->lng();

        if ($assignment->getDeadlineMode() === \ilExAssignment::DEADLINE_ABSOLUTE) {
            if ($data_row['deadline'] > 0) {
                $deadline = \ilDatePresentation::formatDate(
                    new \ilDateTime($data_row['deadline'], IL_CAL_UNIX)
                );
                if ($data_row['deadline2'] > 0) {
                    $deadline .= '<br>(' . \ilDatePresentation::formatDate(
                        new \ilDateTime($data_row['deadline2'], IL_CAL_UNIX)
                    ) . ')';
                }
                return $deadline;
            }
            return '-';
        }

        if ($assignment->getDeadlineMode() === \ilExAssignment::DEADLINE_ABSOLUTE_INDIVIDUAL) {
            return $lng->txt('exc_fixed_date_individual');
        }

        $deadline = '';
        if ($assignment->getRelativeDeadline() > 0) {
            $deadline = $assignment->getRelativeDeadline() . ' ' . $lng->txt('days');
        }
        if ($assignment->getRelDeadlineLastSubmission() > 0) {
            if ($deadline !== '') {
                $deadline .= ' / ';
            }
            $deadline .= ilDatePresentation::formatDate(
                new \ilDateTime($assignment->getRelDeadlineLastSubmission(), IL_CAL_UNIX)
            );
        }

        return $deadline;
    }
}
