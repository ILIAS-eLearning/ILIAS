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

namespace ILIAS\Exercise\Submission;

use ILIAS\Exercise\InternalDomainService;
use ILIAS\Exercise\InternalGUIService;
use ILIAS\Repository\RetrievalInterface;
use ILIAS\Repository\Table\CommonTableBuilder;
use ILIAS\Repository\Table\TableAdapterGUI;

class DeliveredFilesTableBuilder extends CommonTableBuilder
{
    protected bool $show_owner;
    protected bool $show_late;

    public function __construct(
        protected InternalDomainService $domain,
        protected InternalGUIService $gui,
        protected \ilExSubmission $submission,
        object $parent_gui,
        string $parent_cmd
    ) {
        $type = $submission->getAssignment()->getAssignmentType();
        $this->show_owner = $type->usesTeams() && $type->usesFileUpload();
        $this->show_late = (bool) $submission->getAssignment()->getExtendedDeadline();

        parent::__construct($parent_gui, $parent_cmd);
    }

    protected function getId(): string
    {
        return 'exc_delivered_files';
    }

    protected function getTitle(): string
    {
        return $this->domain->lng()->txt('already_delivered_files') . ' - ' .
            $this->submission->getAssignment()->getTitle();
    }

    protected function getRetrieval(): RetrievalInterface
    {
        return $this->domain->deliveredFilesRetrieval($this->submission);
    }

    protected function transformRow(array $data_row): array
    {
        $ctrl = $this->gui->ctrl();
        $lng = $this->domain->lng();

        $ctrl->setParameter($this->parent_gui, 'delivered', $data_row['id']);
        $download = $this->gui->ui()->factory()->link()->standard(
            $lng->txt('download'),
            $ctrl->getLinkTarget($this->parent_gui, 'download')
        );
        $ctrl->setParameter($this->parent_gui, 'delivered', '');

        $row = [
            'id' => $data_row['id'],
            'title' => htmlentities($data_row['title']),
            'timestamp' => (new \DateTimeImmutable('@' . $data_row['timestamp']))
                ->setTimezone(new \DateTimeZone($this->domain->user()->getTimeZone())),
            'download' => $download
        ];

        if ($this->show_owner) {
            $row['owner'] = \ilUserUtil::getNamePresentation($data_row['user_id']);
        }

        if ($this->show_late) {
            $row['late'] = $data_row['late']
                ? '<span class="warning">' . $lng->txt('yes') . '</span>'
                : $lng->txt('no');
        }

        return $row;
    }

    protected function build(TableAdapterGUI $table): TableAdapterGUI
    {
        $lng = $this->domain->lng();

        $table = $table
            ->textColumn('title', $lng->txt('filename'), true);

        if ($this->show_owner) {
            $this->domain->lng()->loadLanguageModule('file');
            $table = $table->textColumn('owner', $lng->txt('file_uploaded_by'));
        }

        $table = $table
            ->dateColumn('timestamp', $lng->txt('date'), true);

        if ($this->show_late) {
            $table = $table->textColumn('late', $lng->txt('exc_late_submission'));
        }

        $table = $table->linkColumn('download', $lng->txt('action'));

        if ($this->submission->canSubmit()) {
            $table = $table->multiAction(
                'confirmDeleteDelivered',
                $lng->txt('delete'),
                true
            );
        }

        return $table;
    }
}
