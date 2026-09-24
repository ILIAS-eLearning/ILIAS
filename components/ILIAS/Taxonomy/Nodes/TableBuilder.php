<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with
 * the source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning/ILIAS
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\Taxonomy\Nodes;

use ILIAS\Taxonomy\InternalDomainService;
use ILIAS\Taxonomy\InternalGUIService;
use ILIAS\Repository\RetrievalInterface;
use ILIAS\Repository\Table\CommonTableBuilder;
use ILIAS\Repository\Table\TableAdapterGUI;

class TableBuilder extends CommonTableBuilder
{
    public function __construct(
        protected InternalDomainService $domain,
        protected InternalGUIService $gui,
        protected \ilTaxonomyTree $tree,
        protected int $parent_node_id,
        protected \ilObjTaxonomy $taxonomy,
        object $parent_gui,
        string $parent_cmd
    ) {
        parent::__construct($parent_gui, $parent_cmd);
    }

    protected function getId(): string
    {
        return "taxonomy_nodes";
    }

    protected function getTitle(): string
    {
        return $this->domain->lng()->txt("tax_nodes");
    }

    protected function getRetrieval(): RetrievalInterface
    {
        return new Retrieval(
            $this->tree,
            $this->parent_node_id,
            $this->taxonomy->getSortingMode() === \ilObjTaxonomy::SORT_MANUAL
        );
    }

    protected function transformRow(array $data_row): array
    {
        $node_id = (int) $data_row["child"];
        $ctrl = $this->gui->ctrl();
        $ctrl->setParameter($this->parent_gui, "tax_node", $node_id);
        $url = $ctrl->getLinkTarget($this->parent_gui, $this->parent_cmd);
        $ctrl->setParameter($this->parent_gui, "tax_node", $this->parent_node_id);

        return [
            "id" => $node_id,
            "title" => $this->gui->ui()->factory()->link()->standard(
                (string) $data_row["title"],
                $url
            )
        ];
    }

    protected function getOrderingCommand(): string
    {
        return $this->taxonomy->getSortingMode() === \ilObjTaxonomy::SORT_MANUAL
            ? "saveSorting"
            : "";
    }

    protected function build(TableAdapterGUI $table): TableAdapterGUI
    {
        $lng = $this->domain->lng();

        return $table
            ->linkColumn("title", $lng->txt("title"))
            ->singleAction("editTaxNodeTitle", $lng->txt("edit"), true)
            ->singleAction("confirmDeleteTaxNode", $lng->txt("delete"), true)
            ->standardAction("moveTaxNode", $lng->txt("move"));
    }
}
