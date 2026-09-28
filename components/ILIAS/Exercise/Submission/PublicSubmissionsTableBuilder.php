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

class PublicSubmissionsTableBuilder extends CommonTableBuilder
{
    public function __construct(
        protected InternalDomainService $domain,
        protected InternalGUIService $gui,
        protected \ilExAssignment $assignment,
        object $parent_gui,
        string $parent_cmd
    ) {
        parent::__construct($parent_gui, $parent_cmd);
    }

    protected function getId(): string
    {
        return 'public_submissions';
    }

    protected function getTitle(): string
    {
        $lng = $this->domain->lng();

        return $lng->txt('exc_assignment') . ': ' . $this->assignment->getTitle();
    }

    protected function getRetrieval(): RetrievalInterface
    {
        return $this->domain->publicSubmissionsRetrieval($this->assignment);
    }

    protected function transformRow(array $data_row): array
    {
        $lng = $this->domain->lng();

        return [
            'id' => $data_row['id'],
            'name' => $data_row['name'] . ' [' . $data_row['login'] . ']',
            'submission_count' => $data_row['submission_count'] . ' ' . $lng->txt('exc_files_returned')
        ];
    }

    protected function activeAction(string $action, array $data_row): bool
    {
        if ($action === 'downloadReturned') {
            return $data_row['submission_count'] > 0;
        }

        return true;
    }

    protected function build(TableAdapterGUI $table): TableAdapterGUI
    {
        $lng = $this->domain->lng();

        return $table
            ->textColumn('name', $lng->txt('name'), true)
            ->textColumn('submission_count', $lng->txt('exc_submission'))
            ->singleRedirectAction(
                'downloadReturned',
                $lng->txt('exc_download_files'),
                [\ilExSubmissionGUI::class, \ilExSubmissionFileGUI::class],
                'downloadReturned',
                'member_id'
            );
    }
}
