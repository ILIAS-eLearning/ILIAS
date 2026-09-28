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

namespace ILIAS\Exercise\PeerReview;

use ILIAS\Exercise\InternalDomainService;
use ILIAS\Exercise\InternalGUIService;
use ILIAS\Repository\RetrievalInterface;
use ILIAS\Repository\Table\CommonTableBuilder;
use ILIAS\Repository\Table\TableAdapterGUI;

class PeerReviewOverviewTableBuilder extends CommonTableBuilder
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
        return 'peer_review_overview';
    }

    protected function getTitle(): string
    {
        return $this->assignment->getTitle() . ': ' .
            $this->domain->lng()->txt('exc_peer_review_overview');
    }

    protected function getRetrieval(): RetrievalInterface
    {
        return $this->domain->peerReviewOverviewRetrieval($this->assignment);
    }

    protected function transformRow(array $data_row): array
    {
        return [
            'id' => $data_row['id'],
            'recipient' => $data_row['recipient'],
            'giver' => $data_row['giver'],
            'status' => $data_row['status']
        ];
    }

    protected function build(TableAdapterGUI $table): TableAdapterGUI
    {
        $lng = $this->domain->lng();

        return $table
            ->textColumn('recipient', $lng->txt('exc_peer_review_recipient'), true)
            ->textColumn('giver', $lng->txt('exc_peer_review_giver'), true)
            ->textColumn('status', $lng->txt('status'));
    }
}
