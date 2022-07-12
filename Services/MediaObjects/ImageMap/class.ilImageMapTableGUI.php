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

/**
 * TableGUI class for image map editor
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilImageMapTableGUI extends ilTable2GUI
{
    protected ilObjMediaObject $media_object;
    protected array $highl_classes;
    protected array $highl_modes;
    protected ilAccessHandler $access;

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        ilObjMediaObject $a_media_object
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $ilCtrl = $DIC->ctrl();

        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->media_object = $a_media_object;
        
        $this->highl_modes = ilMapArea::getAllHighlightModes();
        $this->highl_classes = ilMapArea::getAllHighlightClasses();
        
        $this->initColumns();
        $this->setEnableHeader(true);
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.image_map_table_row.html", "Services/MediaObjects");
        $this->getItems();

        // action commands
        $this->initActions();

        $this->setDefaultOrderField("title");
        $this->setDefaultOrderDirection("asc");
        $this->setEnableTitle(false);
    }
    
    public function initColumns() : void
    {
        $this->addColumn("", "", "1");	// checkbox
        $this->addColumn($this->lng->txt("cont_name"), "title", "");
        $this->addColumn($this->lng->txt("cont_shape"), "", "");
        $this->addColumn($this->lng->txt("cont_coords"), "", "");
        $this->addColumn($this->lng->txt("cont_highlight_mode"));
        $this->addColumn($this->lng->txt("cont_highlight_class"));
        $this->addColumn($this->lng->txt("cont_link"), "", "");
    }
    
    public function initActions() : void
    {
        $lng = $this->lng;
        
        // action commands
        $this->addMultiCommand("deleteAreas", $lng->txt("delete"));
        $this->addMultiCommand("editLink", $lng->txt("cont_set_link"));
        $this->addMultiCommand("editShapeWholePicture", $lng->txt("cont_edit_shape_whole_picture"));
        $this->addMultiCommand("editShapeRectangle", $lng->txt("cont_edit_shape_rectangle"));
        $this->addMultiCommand("editShapeCircle", $lng->txt("cont_edit_shape_circle"));
        $this->addMultiCommand("editShapePolygon", $lng->txt("cont_edit_shape_polygon"));
        
        $data = $this->getData();
        if (count($data) > 0) {
            $this->addCommandButton("updateAreas", $lng->txt("save"));
        }
    }

    public function getItems() : void
    {
        $st_item = $this->media_object->getMediaItem("Standard");
        $max = ilMapArea::_getMaxNr($st_item->getId());
        $areas = array();
        
        for ($i = 1; $i <= $max; $i++) {
            $area = new ilMapArea($st_item->getId(), $i);
            $areas[] = array("nr" => $i, "area" => $area, "title" => $area->getTitle());
        }

        $this->setData($areas);
    }

    protected function fillRow(array $a_set) : void
    {
        $area = $a_set["area"];
        $i = $a_set["nr"];
        $this->tpl->setVariable(
            "CHECKBOX",
            ilLegacyFormElementsUtil::formCheckbox("", "area[]", $i)
        );
        $this->tpl->setVariable("VAR_NAME", "name_" . $i);
        $this->tpl->setVariable("VAL_NAME", ilLegacyFormElementsUtil::prepareFormOutput($area->getTitle()));
        $this->tpl->setVariable("VAL_SHAPE", $area->getShape());
        
        $this->tpl->setVariable(
            "VAL_HIGHL_MODE",
            ilLegacyFormElementsUtil::formSelect(
                $area->getHighlightMode(),
                "hl_mode_" . $i,
                $this->highl_modes,
                false,
                true
            )
        );
        $this->tpl->setVariable(
            "VAL_HIGHL_CLASS",
            ilLegacyFormElementsUtil::formSelect(
                $area->getHighlightClass(),
                "hl_class_" . $i,
                $this->highl_classes,
                false,
                true
            )
        );
        
        $this->tpl->setVariable(
            "VAL_COORDS",
            implode(", ", explode(",", $area->getCoords()))
        );
        switch ($area->getLinkType()) {
            case "ext":
                $this->tpl->setVariable("VAL_LINK", $area->getHRef());
                break;

            case "int":
                $link_str = $this->parent_obj->getMapAreaLinkString(
                    $area->getTarget(),
                    $area->getType(),
                    $area->getTargetFrame()
                );
                $this->tpl->setVariable("VAL_LINK", $link_str);
                break;
        }
    }
}
