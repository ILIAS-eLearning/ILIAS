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

namespace ILIAS\Portfolio\Administration;

use ILIAS\Portfolio\InternalDomainService;
use ILIAS\Portfolio\InternalGUIService;
use ILIAS\Repository\RetrievalInterface;
use ILIAS\Repository\Table\CommonTableBuilder;
use ILIAS\Repository\Table\TableAdapterGUI;

class PortfolioRoleAssignmentTableBuilder extends CommonTableBuilder
{
    public function __construct(
        protected InternalDomainService $domain,
        protected InternalGUIService $gui,
        protected bool $has_write_permission,
        object $parent_gui,
        string $parent_cmd
    ) {
        parent::__construct($parent_gui, $parent_cmd, false);
    }

    protected function getId(): string
    {
        return "portfolio_role_assignments";
    }

    protected function getTitle(): string
    {
        return $this->domain->lng()->txt("prtf_role_assignment");
    }

    protected function getRetrieval(): RetrievalInterface
    {
        return $this->domain->portfolioRoleAssignmentRetrieval();
    }

    protected function transformRow(array $data_row): array
    {
        return [
            "id" => $data_row["id"],
            "role_title" => $data_row["role_title"],
            "template_title" => $data_row["template_title"]
        ];
    }

    protected function build(TableAdapterGUI $table): TableAdapterGUI
    {
        $lng = $this->domain->lng();

        $table = $table
            ->textColumn("role_title", $lng->txt("prtf_role_title"))
            ->textColumn("template_title", $lng->txt("prtf_template_title"));

        if ($this->has_write_permission) {
            $table = $table->singleAction(
                "confirmAssignmentDeletion",
                $lng->txt("prtf_delete_assignment"),
                true
            );
        }

        return $table;
    }
}
