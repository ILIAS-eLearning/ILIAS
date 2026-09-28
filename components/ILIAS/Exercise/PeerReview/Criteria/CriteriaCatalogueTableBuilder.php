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

namespace ILIAS\Exercise\PeerReview\Criteria;

use ILIAS\Exercise\InternalDomainService;
use ILIAS\Exercise\InternalGUIService;
use ILIAS\Repository\RetrievalInterface;
use ILIAS\Repository\Table\CommonTableBuilder;
use ILIAS\Repository\Table\TableAdapterGUI;

class CriteriaCatalogueTableBuilder extends CommonTableBuilder
{
    protected CriteriaCatalogueRetrieval $retrieval;

    public function __construct(
        protected InternalDomainService $domain,
        protected InternalGUIService $gui,
        int $exc_id,
        object $parent_gui,
        string $parent_cmd
    ) {
        $this->retrieval = $domain->peerReview()->criteriaCatalogueRetrieval($exc_id);
        parent::__construct($parent_gui, $parent_cmd);
    }

    public function hasProtectedAssignments(): bool
    {
        return $this->retrieval->hasProtectedAssignments();
    }

    protected function getId(): string
    {
        return 'exc_criteria_catalogues';
    }

    protected function getTitle(): string
    {
        return $this->domain->lng()->txt('exc_criteria_catalogues');
    }

    protected function getRetrieval(): RetrievalInterface
    {
        return $this->retrieval;
    }

    protected function getOrderingCommand(): string
    {
        return 'saveOrder';
    }

    protected function activeAction(string $action, array $data_row): bool
    {
        if ($action === 'editCriteria') {
            return !$data_row['protected'];
        }

        return true;
    }

    protected function transformRow(array $data_row): array
    {
        $ctrl = $this->gui->ctrl();
        $ctrl->setParameter($this->parent_gui, 'cat_id', $data_row['id']);
        $title = $this->gui->ui()->factory()->link()->standard(
            $data_row['title'],
            $ctrl->getLinkTarget($this->parent_gui, 'edit')
        );
        $ctrl->setParameter($this->parent_gui, 'cat_id', '');

        return [
            'id' => $data_row['id'],
            'title' => $title,
            'criterias' => $data_row['criterias'],
            'assignments' => $data_row['assignments']
        ];
    }

    protected function build(TableAdapterGUI $table): TableAdapterGUI
    {
        $lng = $this->domain->lng();

        return $table
            ->linkColumn('title', $lng->txt('title'))
            ->textColumn('criterias', $lng->txt('exc_criterias'))
            ->textColumn('assignments', $lng->txt('exc_assignments'))
            ->singleRedirectAction(
                'edit',
                $lng->txt('edit'),
                [\ilExcCriteriaCatalogueGUI::class],
                'edit',
                'cat_id'
            )
            ->singleRedirectAction(
                'editCriteria',
                $lng->txt('exc_edit_criterias'),
                [\ilExcCriteriaCatalogueGUI::class, \ilExcCriteriaGUI::class],
                '',
                'cat_id'
            )
            ->multiAction('confirmDeletion', $lng->txt('delete'));
    }
}
