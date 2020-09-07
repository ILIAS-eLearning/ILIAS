<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("Services/Table/classes/class.ilTable2GUI.php");

/**
* TableGUI class for image map editor
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesMediaObjects
*/
class ilImageMapTableGUI extends ilTable2GUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilAccessHandler
     */
    protected $access;

    
    /**
    * Constructor
    */
    public function __construct($a_parent_obj, $a_parent_cmd, $a_media_object)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        $ilAccess = $DIC->access();
        $lng = $DIC->language();
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->media_object = $a_media_object;
        
        include_once("./Services/MediaObjects/classes/class.ilMapArea.php");
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
    
    /**
     * Init columns
     */
    public function initColumns()
    {
        $this->addColumn("", "", "1");	// checkbox
        $this->addColumn($this->lng->txt("cont_name"), "title", "");
        $this->addColumn($this->lng->txt("cont_shape"), "", "");
        $this->addColumn($this->lng->txt("cont_coords"), "", "");
        $this->addColumn($this->lng->txt("cont_highlight_mode"));
        $this->addColumn($this->lng->txt("cont_highlight_class"));
        $this->addColumn($this->lng->txt("cont_link"), "", "");
    }
    
    /**
     * Init actions
     */
    public function initActions()
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
    

    /**
    * Get items of current folder
    */
    public function getItems()
    {
        $st_item = $this->media_object->getMediaItem("Standard");
        $max = ilMapArea::_getMaxNr($st_item->getId());
        $areas = array();
        
        include_once("./Services/MediaObjects/classes/class.ilMapArea.php");
        for ($i = 1; $i <= $max; $i++) {
            $area = new ilMapArea($st_item->getId(), $i);
            $areas[] = array("nr" => $i, "area" => $area, "title" => $area->getTitle());
        }

        $this->setData($areas);
    }
    
    /**
    * Standard Version of Fill Row. Most likely to
    * be overwritten by derived class.
    */
    protected function fillRow($a_set)
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilAccess = $this->access;

        $area = $a_set["area"];
        $i = $a_set["nr"];
        $this->tpl->setVariable(
            "CHECKBOX",
            ilUtil::formCheckBox("", "area[]", $i)
        );
        $this->tpl->setVariable("VAR_NAME", "name_" . $i);
        $this->tpl->setVariable("VAL_NAME", ilUtil::prepareFormOutput($area->getTitle()));
        $this->tpl->setVariable("VAL_SHAPE", $area->getShape());
        
        $this->tpl->setVariable(
            "VAL_HIGHL_MODE",
            ilUtil::formSelect(
                $area->getHighlightMode(),
                "hl_mode_" . $i,
                $this->highl_modes,
                false,
                true
            )
        );
        $this->tpl->setVariable(
            "VAL_HIGHL_CLASS",
            ilUtil::formSelect(
                $area->getHighlightClass(),
                "hl_class_" . $i,
                $this->highl_classes,
                false,
                true
            )
        );
        
        $this->tpl->setVariable(
            "VAL_COORDS",
            implode(explode(",", $area->getCoords()), ", ")
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
