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

namespace ILIAS\MediaPool\Clipboard;

use ILIAS\MediaObjects\Thumbs\ThumbsGUI;
use ILIAS\MediaPool\InternalDomainService;
use ILIAS\MediaPool\InternalGUIService;
use ILIAS\Repository\RetrievalInterface;
use ILIAS\Repository\Table\CommonTableBuilder;
use ILIAS\Repository\Table\TableAdapterGUI;

class ClipboardTableBuilder extends CommonTableBuilder
{
    public function __construct(
        protected InternalDomainService $domain,
        protected InternalGUIService $gui,
        protected \ilObjUser $user,
        protected ThumbsGUI $thumbs_gui,
        object $parent_gui,
        string $parent_cmd
    ) {
        parent::__construct($parent_gui, $parent_cmd, false);
    }

    protected function getId(): string
    {
        return "clipboard";
    }

    protected function getTitle(): string
    {
        return $this->domain->lng()->txt("clipboard");
    }

    protected function getRetrieval(): RetrievalInterface
    {
        return new ClipboardRetrieval($this->user);
    }

    protected function transformRow(array $data_row): array
    {
        $type = (string) $data_row["type"];
        $id = (int) $data_row["source_id"];
        $title = htmlspecialchars(
            (string) $data_row["title"] . " [" . $id . "]",
            ENT_QUOTES,
            "UTF-8"
        );

        if ($type === "mob") {
            $mob = new \ilObjMediaObject($id);
            $thumbnail = $this->thumbs_gui->getThumbHtml($id);
            $title .= \ilObjMediaObjectGUI::_getMediaInfoHTML($mob);
        } else {
            $thumbnail = $this->gui->ui()->renderer()->render(
                $this->gui->ui()->factory()->symbol()->icon()->custom(
                    \ilUtil::getImagePath("standard/icon_pg.svg"),
                    $this->domain->lng()->txt("page")
                )->withSize("large")
            );
        }

        return [
            "id" => $data_row["id"],
            "thumbnail" => $thumbnail,
            "title" => $title
        ];
    }

    protected function build(TableAdapterGUI $table): TableAdapterGUI
    {
        $lng = $this->domain->lng();
        $table = $table
            ->textColumn("thumbnail", $lng->txt("mep_thumbnail"))
            ->textColumn("title", $lng->txt("mep_title_and_description"), true)
            ->multiAction("remove", $lng->txt("remove"))
            ->singleAction("removeSingle", $lng->txt("remove"));

        if ($this->parent_gui->mode === "getObject") {
            $table = $table
                ->multiAction("insert", $this->parent_gui->getInsertButtonTitle())
                ->singleAction("insertSingle", $this->parent_gui->getInsertButtonTitle());
        } else {
            $table = $table->singleRedirectAction(
                "edit",
                $lng->txt("edit"),
                [\ilObjMediaObjectGUI::class],
                "edit",
                "clip_item_id"
            );
        }

        return $table;
    }
}
