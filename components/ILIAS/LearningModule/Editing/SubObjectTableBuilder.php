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
        return [
            "id" => $data_row["id"],
            "type" => $f->symbol()->icon()->custom(\ilUtil::getImagePath($img), $alt),
            "title" => $data_row["title"],
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
            ->textColumn("title", $lng->txt("title"));
        if (!in_array($transl, ["-", ""])) {
            $table = $table->textColumn("trans_title", $lng->txt("title") .
                " (" . $lng->txt("meta_l_" . $transl) . ")");
        }
        if ($this->type === "st") {
            $acts = [
                [
                    "editPages",
                    $lng->txt("edit"),
                    [\ilObjLearningModuleGUI::class, \ilStructureObjectGUI::class, EditSubObjectsGUI::class],
                    "editPages",
                    "obj_id"
                ],
                [
                    "insertChapterAfter",
                    $lng->txt("lm_insert_chapter_after"),
                    [EditSubObjectsGUI::class],
                    "insertChapterAfter",
                    "target_id"
                ],
                [
                    "insertChapterBefore",
                    $lng->txt("lm_insert_chapter_before"),
                    [EditSubObjectsGUI::class],
                    "insertChapterBefore",
                    "target_id"
                ]
            ];
            if ($user->clipboardHasObjectsOfType("st")) {
                $acts[] = [
                    "insertChapterClipAfter",
                    $lng->txt("lm_insert_chapter_clip_after"),
                    [EditSubObjectsGUI::class],
                    "insertChapterClipAfter",
                    "target_id"
                ];
                $acts[] = [
                    "insertChapterClipBefore",
                    $lng->txt("lm_insert_chapter_clip_before"),
                    [EditSubObjectsGUI::class],
                    "insertChapterClipBefore",
                    "target_id"
                ];
            }
        } else {
            $acts = [
                [
                    "editPage",
                    $lng->txt("edit"),
                    [\ilObjLearningModuleGUI::class, \ilLMPageObjectGUI::class],
                    "edit",
                    "obj_id"
                ],
                [
                    "insertPageAfter",
                    $lng->txt("lm_insert_page_after"),
                    [EditSubObjectsGUI::class],
                    "insertPageAfter",
                    "target_id"
                ],
                [
                    "insertPageBefore",
                    $lng->txt("lm_insert_page_before"),
                    [EditSubObjectsGUI::class],
                    "insertPageBefore",
                    "target_id"
                ]
            ];
            if ($user->clipboardHasObjectsOfType("pg")) {
                $acts[] = [
                    "insertPageClipAfter",
                    $lng->txt("lm_insert_page_clip_after"),
                    [EditSubObjectsGUI::class],
                    "insertPageClipAfter",
                    "target_id"
                ];
                $acts[] = [
                    "insertPageClipBefore",
                    $lng->txt("lm_insert_page_clip_before"),
                    [EditSubObjectsGUI::class],
                    "insertPageClipBefore",
                    "target_id"
                ];
            }
            if (count($this->page_layouts) > 0) {
                $acts[] = [
                    "insertLayoutAfter",
                    $lng->txt("lm_insert_layout_after"),
                    [EditSubObjectsGUI::class],
                    "insertLayoutAfter",
                    "target_id"
                ];
                $acts[] = [
                    "insertLayoutBefore",
                    $lng->txt("lm_insert_layout_before"),
                    [EditSubObjectsGUI::class],
                    "insertLayoutBefore",
                    "target_id"
                ];
            }
        }
        foreach ($acts as $a) {
            $table = $table->singleRedirectAction(
                $a[0],
                $a[1],
                $a[2],
                $a[3],
                $a[4]
            );
        }
        $table = $table
            ->standardAction(
                "delete",
                $lng->txt("delete")
            )
            ->singleAction(
                "editTitle",
                $lng->txt("cont_edit_title"),
                true
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
