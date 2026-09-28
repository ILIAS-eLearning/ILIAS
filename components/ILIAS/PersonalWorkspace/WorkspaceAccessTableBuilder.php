<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of the license along with the
 * source code, too.
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\PersonalWorkspace;

use ILIAS\Repository\RetrievalInterface;
use ILIAS\Repository\Table\CommonTableBuilder;
use ILIAS\Repository\Table\TableAdapterGUI;

class WorkspaceAccessTableBuilder extends CommonTableBuilder
{
    public function __construct(
        protected InternalDomainService $domain,
        protected InternalGUIService $gui,
        protected \ilWorkspaceAccessHandler|\ilPortfolioAccessHandler $handler,
        protected int $node_id,
        object $parent_gui,
        string $parent_cmd
    ) {
        parent::__construct($parent_gui, $parent_cmd);
    }

    protected function getId(): string
    {
        return "workspace_access";
    }

    protected function getTitle(): string
    {
        return $this->domain->lng()->txt("wsp_shared_table_title");
    }

    protected function getRetrieval(): RetrievalInterface
    {
        return $this->domain->workspaceAccessRetrieval($this->handler, $this->node_id);
    }

    protected function transformRow(array $data_row): array
    {
        return [
            "id" => $data_row["id"],
            "title" => $data_row["caption"],
            "type" => $data_row["type"]
        ];
    }

    protected function build(TableAdapterGUI $table): TableAdapterGUI
    {
        $lng = $this->domain->lng();

        return $table
            ->textColumn("title", $lng->txt("wsp_shared_with"), true)
            ->textColumn("type", $lng->txt("details"))
            ->singleAction("removePermission", $lng->txt("remove"));
    }
}
