<?php

/* Copyright (c) 1998-2023 ILIAS open source, Extended GPL, see docs/LICENSE */

declare(strict_types=1);

namespace ILIAS\COPage\Layout;

use ILIAS\Repository\RetrievalInterface;
use ILIAS\COPage\InternalDomainService;
use ILIAS\COPage\InternalGUIService;
use ILIAS\Repository\Table\CommonTableBuilder;
use ILIAS\Repository\Table\TableAdapterGUI;
use ilPageLayout;
use ilUtil;
use ilPageLayoutGUI;

class PageLayoutTableBuilder extends CommonTableBuilder
{
    public function __construct(
        protected InternalDomainService $domain,
        protected InternalGUIService $gui,
        object $parent_gui,
        string $parent_cmd
    ) {
        parent::__construct($parent_gui, $parent_cmd);
    }

    protected function getId(): string
    {
        return "pglayout";
    }

    protected function getTitle(): string
    {
        return $this->domain->lng()->txt("page_layouts");
    }

    protected function getRetrieval(): RetrievalInterface
    {
        return $this->domain->layoutRetrieval();
    }

    protected function transformRow(array $data_row): array
    {
        $lng = $this->domain->lng();
        $all_mods = ilPageLayout::getAvailableModules();

        $modules = [];
        foreach ($all_mods as $mod_id => $mod_caption) {
            if (($mod_id == ilPageLayout::MODULE_SCORM && $data_row["mod_scorm"]) ||
                ($mod_id == ilPageLayout::MODULE_PORTFOLIO && $data_row["mod_portfolio"]) ||
                ($mod_id == ilPageLayout::MODULE_LM && $data_row["mod_lm"])) {
                $modules[] = $mod_caption;
            }
        }

        $active_img = $data_row['active']
            ? ilUtil::getImagePath("standard/icon_ok.svg")
            : ilUtil::getImagePath("standard/icon_not_ok.svg");
        $active_txt = $data_row['active']
            ? $lng->txt("active")
            : $lng->txt("inactive");

        $pgl_obj = new ilPageLayout((int) $data_row['layout_id']);

        return [
            "id" => $data_row["id"],
            "active" => $this->gui->ui()->factory()->symbol()->icon()->custom($active_img, $active_txt),
            "preview" => $pgl_obj->getPreview(),
            "title" => $data_row["title"],
            "description" => $data_row["description"],
            "modules" => implode(", ", $modules)
        ];
    }

    protected function build(TableAdapterGUI $table): TableAdapterGUI
    {
        $lng = $this->domain->lng();
        $lng->loadLanguageModule("copg");
        $table = $table
            ->iconColumn("active", $lng->txt("active"))
            ->textColumn("preview", $lng->txt("thumbnail"), false)
            ->textColumn("title", $lng->txt("title"), true)
            ->textColumn("description", $lng->txt("description"), true)
            ->textColumn("modules", $lng->txt("copg_obj_types"), false)
            ->singleAction("editPg", $lng->txt("edit"))
            ->singleRedirectAction(
                "properties",
                $lng->txt("settings"),
                [ilPageLayoutGUI::class],
                "properties",
                "obj_id"
            )
            ->singleAction("exportLayout", $lng->txt("export"))
            ->singleAction("deletePgl", $lng->txt("delete"), true)
            ->multiAction("activate", $lng->txt("activate"))
            ->multiAction("deactivate", $lng->txt("deactivate"))
            ->multiAction("deletePgl", $lng->txt("delete"), true);

        return $table;
    }
}
