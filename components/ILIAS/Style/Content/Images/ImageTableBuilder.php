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

namespace ILIAS\Style\Content\Images;

use ILIAS\Repository\RetrievalInterface;
use ILIAS\Repository\Table\CommonTableBuilder;
use ILIAS\Repository\Table\TableAdapterGUI;
use ILIAS\Style\Content\Access\StyleAccessManager;
use ILIAS\Style\Content\ImageManager;
use ILIAS\Style\Content\InternalDomainService;
use ILIAS\Style\Content\InternalGUIService;

class ImageTableBuilder extends CommonTableBuilder
{
    public function __construct(
        protected InternalDomainService $domain,
        protected InternalGUIService $gui,
        protected StyleAccessManager $access_manager,
        protected ImageManager $image_manager,
        object $parent_gui,
        string $parent_cmd
    ) {
        parent::__construct($parent_gui, $parent_cmd, false);
    }

    protected function getId(): string
    {
        return "style_images";
    }

    protected function getTitle(): string
    {
        return $this->domain->lng()->txt("sty_images");
    }

    protected function getRetrieval(): RetrievalInterface
    {
        return $this->domain->imageRetrieval($this->image_manager);
    }

    protected function transformRow(array $data_row): array
    {
        $thumbnail = "";
        if (is_file($data_row["thumbnail"]) || str_starts_with($data_row["thumbnail"], "http")) {
            $image = $this->gui->ui()->factory()->image()->responsive(
                $data_row["thumbnail"],
                $data_row["filename"]
            );
            $thumbnail = str_replace(
                "<img ",
                "<img style='max-width:100px;' ",
                $this->gui->ui()->renderer()->render($image)
            );
        }

        return [
            "id" => $data_row["id"],
            "thumbnail" => $thumbnail,
            "filename" => $data_row["filename"],
            "width_height" => $data_row["width_height"],
            "size" => $data_row["size"],
        ];
    }

    protected function activeAction(string $action, array $data_row): bool
    {
        if ($action === "resizeImageForm") {
            return (bool) ($data_row["supports_resize"] ?? false);
        }
        return true;
    }

    protected function build(TableAdapterGUI $table): TableAdapterGUI
    {
        $lng = $this->domain->lng();

        $table = $table
            ->textColumn("thumbnail", $lng->txt("thumbnail"))
            ->textColumn("filename", $lng->txt("file"), true)
            ->textColumn("width_height", $lng->txt("sty_width_height"))
            ->textColumn("size", $lng->txt("size"));

        if ($this->access_manager->checkWrite()) {
            $table = $table
                ->singleAction("resizeImageForm", $lng->txt("sty_resize"))
                ->multiAction("confirmDeleteImages", $lng->txt("delete"), true);
        }

        return $table;
    }
}
