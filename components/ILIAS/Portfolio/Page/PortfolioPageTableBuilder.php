<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of the said license along with
 * the source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\Portfolio\Page;

use ILIAS\Portfolio\InternalDomainService;
use ILIAS\Portfolio\InternalGUIService;
use ILIAS\Repository\RetrievalInterface;
use ILIAS\Repository\Table\CommonTableBuilder;
use ILIAS\Repository\Table\TableAdapterGUI;

class PortfolioPageTableBuilder extends CommonTableBuilder
{
    public function __construct(
        protected InternalDomainService $domain,
        protected InternalGUIService $gui,
        protected int $portfolio_id,
        object $parent_gui,
        string $parent_cmd
    ) {
        parent::__construct($parent_gui, $parent_cmd);
    }

    protected function getId(): string
    {
        return "portfolio_pages";
    }

    protected function getTitle(): string
    {
        return $this->domain->lng()->txt("content");
    }

    protected function getRetrieval(): RetrievalInterface
    {
        return $this->domain->portfolioPageRetrieval($this->portfolio_id);
    }

    protected function getOrderingCommand(): string
    {
        return "savePortfolioPagesOrdering";
    }

    protected function transformRow(array $data_row): array
    {
        $page_gui = $this->parent_gui->getPageGUIClassName();
        $ctrl = $this->gui->ctrl();
        $ctrl->setParameterByClass($page_gui, "ppage", $data_row["id"]);
        $title = $this->gui->ui()->factory()->link()->standard(
            $data_row["title"],
            $ctrl->getLinkTargetByClass($page_gui, "edit")
        );
        $ctrl->setParameterByClass($page_gui, "ppage", "");

        return [
            "id" => $data_row["id"],
            "title" => $title,
            "type" => $this->domain->lng()->txt("page")
        ];
    }

    protected function build(TableAdapterGUI $table): TableAdapterGUI
    {
        $lng = $this->domain->lng();
        $page_gui = $this->parent_gui->getPageGUIClassName();

        return $table
            ->linkColumn("title", $lng->txt("title"))
            ->textColumn("type", $lng->txt("type"))
            ->singleAction("editTitle", $lng->txt("prtf_edit_title"), true)
            ->singleRedirectAction(
                "editPage",
                $lng->txt("prtf_edit_content"),
                [$page_gui],
                "edit",
                "ppage"
            )
            ->singleAction("copyPageForm", $lng->txt("prtf_copy_pg"))
            ->singleAction("confirmPortfolioPageDeletion", $lng->txt("delete"), true);
    }
}
