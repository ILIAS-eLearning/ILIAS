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

namespace ILIAS\COPage\PC\MediaObject;

use ILIAS\COPage\InternalDomainService;
use ILIAS\COPage\InternalGUIService;
use ILIAS\Repository\RetrievalInterface;
use ILIAS\Repository\Table\CommonTableBuilder;
use ILIAS\Repository\Table\TableAdapterGUI;

class ImageMapTableBuilder extends CommonTableBuilder
{
    public function __construct(
        protected InternalDomainService $domain,
        protected InternalGUIService $gui,
        protected \ilPCInteractiveImage|\ilPCMediaObject $content_obj,
        protected string $parent_node_name,
        object $parent_gui,
        string $parent_cmd
    ) {
        parent::__construct($parent_gui, $parent_cmd);
    }

    protected function getId(): string
    {
        return "pc_image_map";
    }

    protected function getTitle(): string
    {
        return $this->domain->lng()->txt("cont_imagemap");
    }

    protected function getRetrieval(): RetrievalInterface
    {
        return $this->domain->pc()->imageMapRetrieval(
            $this->content_obj,
            $this->parent_node_name
        );
    }

    protected function transformRow(array $data_row): array
    {
        $link = "";
        if ($data_row["link_type"] === "ext") {
            $link = $data_row["href"];
        } elseif ($data_row["link_type"] === "int") {
            $link = $this->parent_gui->getMapAreaLinkString(
                $data_row["target"],
                $data_row["type"],
                $data_row["target_frame"]
            );
        }

        return [
            "id" => $data_row["id"],
            "title" => $data_row["title"],
            "shape" => $data_row["shape"],
            "coords" => $data_row["coords"],
            "highlight_mode" => $data_row["highlight_mode"],
            "highlight_class" => $data_row["highlight_class"],
            "link" => $link
        ];
    }

    protected function build(TableAdapterGUI $table): TableAdapterGUI
    {
        $lng = $this->domain->lng();
        $parent_class = get_class($this->parent_gui);

        return $table
            ->textColumn("title", $lng->txt("cont_name"), true)
            ->textColumn("shape", $lng->txt("cont_shape"))
            ->textColumn("coords", $lng->txt("cont_coords"))
            ->textColumn("highlight_mode", $lng->txt("cont_highlight_mode"))
            ->textColumn("highlight_class", $lng->txt("cont_highlight_class"))
            ->textColumn("link", $lng->txt("cont_link"))
            ->singleAction("editTitle", $lng->txt("title"), true)
            ->singleAction("editHighlightMode", $lng->txt("cont_highlight_mode"), true)
            ->singleAction("editHighlightClass", $lng->txt("cont_highlight_class"), true)
            ->singleRedirectAction(
                "deleteAreas",
                $lng->txt("delete"),
                [$parent_class],
                "deleteAreas",
                "area_nr"
            )
            ->singleRedirectAction(
                "editLink",
                $lng->txt("cont_set_link"),
                [$parent_class],
                "editLink",
                "area_nr"
            )
            ->singleRedirectAction(
                "editShapeWholePicture",
                $lng->txt("cont_edit_shape_whole_picture"),
                [$parent_class],
                "editShapeWholePicture",
                "area_nr"
            )
            ->singleRedirectAction(
                "editShapeRectangle",
                $lng->txt("cont_edit_shape_rectangle"),
                [$parent_class],
                "editShapeRectangle",
                "area_nr"
            )
            ->singleRedirectAction(
                "editShapeCircle",
                $lng->txt("cont_edit_shape_circle"),
                [$parent_class],
                "editShapeCircle",
                "area_nr"
            )
            ->singleRedirectAction(
                "editShapePolygon",
                $lng->txt("cont_edit_shape_polygon"),
                [$parent_class],
                "editShapePolygon",
                "area_nr"
            );
    }
}
