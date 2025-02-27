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

use ILIAS\LearningModule\InternalGUIService;
use ILIAS\LearningModule\InternalDomainService;

class GUIService
{
    protected array $page_layouts;

    public function __construct(
        protected InternalDomainService $domain,
        protected InternalGUIService $gui
    ) {
        $this->page_layouts = \ilPageLayout::activeLayouts(
            \ilPageLayout::MODULE_LM
        );
    }

    public function request(
        ?array $passed_query_params = null,
        ?array $passed_post_data = null
    ): EditingGUIRequest {
        return new EditingGUIRequest(
            $this->gui->http(),
            $this->domain->refinery(),
            $passed_query_params,
            $passed_post_data
        );
    }

    public function editSubObjectsGUI(
        string $sub_type,
        \ilObjLearningModule $lm,
        string $table_title
    ): EditSubObjectsGUI {
        return new EditSubObjectsGUI(
            $this->domain,
            $this->gui,
            $sub_type,
            $lm,
            $table_title
        );
    }

    public function subObjectTableGUI(
        string $title,
        int $lm_id,
        string $type,
        object $parent_gui
    ): \ILIAS\LearningModule\Table\TableAdapterGUI {
        $lng = $this->domain->lng();
        $user = $this->domain->user();
        $transl = $this->request()->getTranslation();
        $table = new \ILIAS\LearningModule\Table\TableAdapterGUI(
            "subobj",
            $title,
            $this->domain->subObjectRetrieval(
                $lm_id,
                $type,
                $this->request()->getObjId(),
                $transl
            ),
            $parent_gui
        );
        $table = $table
            ->ordering("saveOrder")
            ->iconColumn("type", $lng->txt("type"))
            ->textColumn("title", $lng->txt("title"));
        if (!in_array($transl, ["-", ""])) {
            $table = $table->textColumn("trans_title", $lng->txt("title") .
            " (" . $lng->txt("meta_l_" . $transl) . ")");
        }

        if ($type === "st") {
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
            $table = $table->singleAction($a[0], $a[1])
                           ->redirect($a[2], $a[3], $a[4]);
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
        if ($type === "pg") {
            $table = $table->standardAction(
                "activatePages",
                $lng->txt("cont_de_activate")
            );
        }
        return $table;
    }
}
