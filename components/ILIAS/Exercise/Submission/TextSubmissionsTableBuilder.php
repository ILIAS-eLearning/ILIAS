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

class TextSubmissionsTableBuilder extends CommonTableBuilder
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
        return 'text_submissions';
    }

    protected function getTitle(): string
    {
        return $this->domain->lng()->txt('exc_list_text_assignment') . ': "' . $this->assignment->getTitle() . '"';
    }

    protected function getRetrieval(): RetrievalInterface
    {
        return $this->domain->textSubmissionsRetrieval(
            $this->assignment,
            false
        );
    }

    protected function transformRow(array $data_row): array
    {
        $row = [
            'id' => $data_row['id'],
            'user' => $data_row['user'],
            'timestamp' => (new \DateTimeImmutable('@' . $data_row['timestamp']))
                ->setTimezone(new \DateTimeZone($this->domain->user()->getTimeZone())),
            'text' => nl2br($data_row['text'])
        ];

        return $row;
    }

    protected function build(TableAdapterGUI $table): TableAdapterGUI
    {
        $lng = $this->domain->lng();

        $table = $table
            ->textColumn('user', $lng->txt('user'), true)
            ->dateColumn('timestamp', $lng->txt('exc_last_submission'), true)
            ->textColumn('text', $lng->txt('exc_files_returned_text'));

        return $table;
    }
}
