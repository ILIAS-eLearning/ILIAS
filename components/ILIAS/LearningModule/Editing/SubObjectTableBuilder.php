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

namespace ILIAS\LearningModule\Editing;

use ILIAS\Repository\RetrievalInterface;
use ILIAS\LearningModule\InternalDomainService;
use ILIAS\LearningModule\InternalGUIService;
use ILIAS\Repository\Table\CommonTableBuilder;
use ILIAS\Repository\Table\TableAdapterGUI;

class SubObjectTableBuilder extends CommonTableBuilder
{
    protected array $page_layouts;

    public function __construct(
        protected InternalDomainService $domain,
        protected InternalGUIService $gui,
        protected string $title,
        protected int $lm_id,
        protected string $type,
        object $parent_gui,
        string $parent_cmd
    ) {
        $this->page_layouts = \ilPageLayout::activeLayouts(
            \ilPageLayout::MODULE_LM
        );
        parent::__construct($parent_gui, $parent_cmd);
    }

    protected function getId(): string
    {
        return "subobj";
    }

    protected function getTitle(): string
    {
        return $this->title;
    }

    protected function getRetrieval(): RetrievalInterface
    {
        $request = $this->gui->editing()->request();
        return $this->domain->subObjectRetrieval(
            $this->lm_id,
            $this->type,
            $request->getObjId(),
            $request->getTranslation()
        );
    }

    protected function getOrderingCommand(): string
    {
        return "saveOrder";
    }

    protected function transformRow(array $data_row): array
    {
        $lng = $this->domain->lng();
        $f = $this->gui->ui()->factory();
        $ctrl = $this->gui->ctrl();
        if ($data_row["type"] === "pg") {
            $img_sc = $data_row["scheduled"]
                ? "_sc"
                : "";

            if (!$data_row["active"]) {
                $img = "standard/icon_pg_d" . $img_sc . ".svg";
                $alt = $lng->txt("cont_page_deactivated");
            } else {
                if ($data_row["deactivated_elements"]) {
                    $img = "standard/icon_pg_del" . $img_sc . ".svg";
                    $alt = $lng->txt("cont_page_deactivated_elements");
                } else {
                    $img = "standard/icon_pg" . $img_sc . ".svg";
                    $alt = $lng->txt("pg");
                }
            }
        } else {
            $img = "standard/icon_st.svg";
            $alt = $lng->txt("st");
        }
        $target = "#";
        if ($data_row["type"] === "pg") {
            $ctrl->setParameterByClass(\ilLMPageGUI::class, "obj_id", $data_row["id"]);
            $target = $ctrl->getLinkTargetByClass([
                \ilObjLearningModuleGUI::class,
                \ilLMPageObjectGUI::class,
                \ilLMPageGUI::class
            ], "edit");
        } elseif ($data_row["type"] === "st") {
            $ctrl->setParameterByClass(\ilStructureObjectGUI::class, "obj_id", $data_row["id"]);
            $target = $ctrl->getLinkTargetByClass([
                \ilObjLearningModuleGUI::class,
                \ilStructureObjectGUI::class,
                EditSubObjectsGUI::class
            ], "editPages");
        }

        $title = $f->link()->standard($data_row["title"], $target);
        return [
            "id" => $data_row["id"],
            "type" => $f->symbol()->icon()->custom(\ilUtil::getImagePath($img), $alt),
            "title" => $title,
            "trans_title" => $data_row["trans_title"],
        ];
    }

    protected function build(TableAdapterGUI $table): TableAdapterGUI
    {
        $lng = $this->domain->lng();
        $user = $this->domain->user();
        $transl = $this->gui->editing()->request()->getTranslation();
        $table = $table
            ->iconColumn("type", $lng->txt("type"))
            ->linkColumn("title", $lng->txt("title"));
        if (!in_array($transl, ["-", ""])) {
            $table = $table->textColumn("trans_title", $lng->txt("title") .
                " (" . $lng->txt("meta_l_" . $transl) . ")");
        }
        if ($this->type === "st") {
            $table = $table->singleRedirectAction(
                "editPages",
                $lng->txt("lm_list_pages"),
                [\ilObjLearningModuleGUI::class, \ilStructureObjectGUI::class, EditSubObjectsGUI::class],
                "editPages",
                "obj_id"
            );
            $table = $table->singleAction(
                "editTitle",
                $lng->txt("cont_edit_title"),
                true
            );
            $table = $table->singleAction(
                "insertChapterAfter",
                $lng->txt("lm_insert_chapter_after"),
                true
            );
            $table = $table->singleAction(
                "insertChapterBefore",
                $lng->txt("lm_insert_chapter_before"),
                true
            );
            if ($user->clipboardHasObjectsOfType("st")) {
                $table = $table->singleRedirectAction(
                    "insertChapterClipAfter",
                    $lng->txt("lm_insert_chapter_clip_after"),
                    [EditSubObjectsGUI::class],
                    "insertChapterClipAfter",
                    "target_id"
                );
                $table = $table->singleRedirectAction(
                    "insertChapterClipBefore",
                    $lng->txt("lm_insert_chapter_clip_before"),
                    [EditSubObjectsGUI::class],
                    "insertChapterClipBefore",
                    "target_id"
                );
            }
        } else {
            $table = $table->singleRedirectAction(
                "editPage",
                $lng->txt("lm_edit_content"),
                [\ilObjLearningModuleGUI::class, \ilLMPageObjectGUI::class],
                "edit",
                "obj_id"
            );
            $table = $table->singleAction(
                "editTitle",
                $lng->txt("cont_edit_title"),
                true
            );
            $table = $table->singleAction(
                "insertPageAfter",
                $lng->txt("lm_insert_page_after"),
                true
            );
            $table = $table->singleAction(
                "insertPageBefore",
                $lng->txt("lm_insert_page_before"),
                true
            );
            if ($user->clipboardHasObjectsOfType("pg")) {
                $table = $table->singleRedirectAction(
                    "insertPageClipAfter",
                    $lng->txt("lm_insert_page_clip_after"),
                    [EditSubObjectsGUI::class],
                    "insertPageClipAfter",
                    "target_id"
                );
                $table = $table->singleRedirectAction(
                    "insertPageClipBefore",
                    $lng->txt("lm_insert_page_clip_before"),
                    [EditSubObjectsGUI::class],
                    "insertPageClipBefore",
                    "target_id"
                );
            }
        }
        $table = $table
            ->standardAction(
                "delete",
                $lng->txt("delete")
            )
            ->standardAction(
                "cutItems",
                $lng->txt("cut")
            )
            ->standardAction(
                "copyItems",
                $lng->txt("copy")
            );
        if ($this->type === "pg") {
            $table = $table->standardAction(
                "activatePages",
                $lng->txt("cont_de_activate")
            );
        }
        return $table;
    }

}
