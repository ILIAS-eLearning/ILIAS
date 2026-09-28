<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with
 * the source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\LearningModule\Editing;

use ILIAS\LearningModule\InternalDomainService;
use ILIAS\LearningModule\InternalGUIService;
use ILIAS\Repository\RetrievalInterface;
use ILIAS\Repository\Table\CommonTableBuilder;
use ILIAS\Repository\Table\TableAdapterGUI;

class PagesTableBuilder extends CommonTableBuilder
{
    public function __construct(
        protected InternalDomainService $domain,
        protected InternalGUIService $gui,
        protected \ilObjLearningModule $lm,
        object $parent_gui,
        string $parent_cmd
    ) {
        parent::__construct($parent_gui, $parent_cmd);
    }

    protected function getId(): string
    {
        return "lm_pages";
    }

    protected function getTitle(): string
    {
        return $this->domain->lng()->txt("cont_pages");
    }

    protected function getRetrieval(): RetrievalInterface
    {
        return $this->domain->pagesRetrieval(
            $this->lm->getId(),
            $this->lm->getType(),
            $this->lm->getLayoutPerPage()
        );
    }

    protected function transformRow(array $data_row): array
    {
        $lng = $this->domain->lng();
        $f = $this->gui->ui()->factory();
        $ctrl = $this->gui->ctrl();
        $id = (int) $data_row["id"];

        $img_sc = $data_row["scheduled"] ? "_sc" : "";
        if (!$data_row["active"]) {
            $img = "standard/icon_pg_d" . $img_sc . ".svg";
            $alt = $lng->txt("cont_page_deactivated");
        } elseif ($data_row["deactivated_elements"]) {
            $img = "standard/icon_pg_del" . $img_sc . ".svg";
            $alt = $lng->txt("cont_page_deactivated_elements");
        } else {
            $img = "standard/icon_pg" . $img_sc . ".svg";
            $alt = $lng->txt("pg");
        }

        $ctrl->setParameterByClass(\ilLMPageObjectGUI::class, "obj_id", $id);
        $target = $ctrl->getLinkTargetByClass(\ilLMPageObjectGUI::class, "edit");

        $usage = $this->lm->lm_tree->isInTree($id)
            ? $this->parent_gui->getContextPath($id)
            : "---";
        if ($id === $this->lm->getHeaderPage()) {
            $usage .= " (" . $lng->txt("cont_header") . ")";
        }
        if ($id === $this->lm->getFooterPage()) {
            $usage .= " (" . $lng->txt("cont_footer") . ")";
        }

        $row = [
            "id" => $id,
            "type" => $f->symbol()->icon()->custom(
                \ilUtil::getImagePath($img),
                $alt
            ),
            "title" => $f->link()->standard($data_row["title"], $target),
            "usage" => $usage
        ];

        if ($this->lm->getLayoutPerPage()) {
            $row["layout"] = $data_row["layout"] !== ""
                ? $lng->txt("cont_layout_" . $data_row["layout"])
                : "";
        }

        return $row;
    }

    protected function build(TableAdapterGUI $table): TableAdapterGUI
    {
        $lng = $this->domain->lng();

        $table = $table
            ->iconColumn("type", $lng->txt(""))
            ->linkColumn("title", $lng->txt("title"))
            ->textColumn("usage", $lng->txt("cont_usage"))
            ->singleAction("activatePages", $lng->txt("cont_de_activate"))
            ->singleAction("movePage", $lng->txt("movePage"))
            ->singleAction("copyPage", $lng->txt("copyPage"))
            ->singleAction("deletePage", $lng->txt("delete"))
            ->singleAction("selectHeader", $lng->txt("selectHeader"))
            ->singleAction("selectFooter", $lng->txt("selectFooter"));

        if (\ilEditClipboard::getContentObjectType() === "pg" &&
            \ilEditClipboard::getAction() === "copy") {
            $table = $table->singleAction("pastePage", $lng->txt("pastePage"));
        }

        if ($this->lm->getLayoutPerPage()) {
            $table = $table
                ->textColumn("layout", $lng->txt("cont_layout"))
                ->singleAction("setPageLayout", $lng->txt("cont_set_layout"));
        }

        return $table;
    }
}
