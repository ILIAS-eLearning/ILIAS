<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Class ilPCMapGUI
 *
 * User Interface for Map Editing
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilPCMapGUI extends ilPageContentGUI
{

    /**
    * Constructor
    * @access	public
    */
    public function __construct(&$a_pg_obj, &$a_content_obj, $a_hier_id, $a_pc_id = "")
    {
        global $DIC;

        $this->tpl = $DIC["tpl"];
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        parent::__construct($a_pg_obj, $a_content_obj, $a_hier_id, $a_pc_id);
    }

    /**
    * execute command
    */
    public function executeCommand()
    {
        // get next class that processes or forwards current command
        $next_class = $this->ctrl->getNextClass($this);

        // get current command
        $cmd = $this->ctrl->getCmd();

        switch ($next_class) {
            default:
                $ret = $this->$cmd();
                break;
        }

        return $ret;
    }

    /**
    * Insert new map form.
    */
    public function insert()
    {
        $tpl = $this->tpl;
        
        $this->displayValidationError();
        $this->initForm("create");
        $tpl->setContent($this->form->getHTML());
    }

    /**
    * Edit map form.
    */
    public function edit($a_insert = false)
    {
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $lng = $this->lng;
        
        $this->displayValidationError();
        $this->initForm("update");
        $this->getValues();
        $tpl->setContent($this->form->getHTML());

        return $ret;
    }

    /**
    * Get values from object into form
    */
    public function getValues()
    {
        $values = array();
        
        $values["location"]["latitude"] = $this->content_obj->getLatitude();
        $values["location"]["longitude"] = $this->content_obj->getLongitude();
        $values["location"]["zoom"] = $this->content_obj->getZoom();
        $values["width"] = $this->content_obj->getWidth();
        $values["height"] = $this->content_obj->getHeight();
        $values["caption"] = $this->content_obj->handleCaptionFormOutput($this->content_obj->getCaption());
        $values["horizontal_align"] = $this->content_obj->getHorizontalAlign();
        
        $this->form->setValuesByArray($values);
    }
    
    /**
    * Init map creation/update form
    */
    public function initForm($a_mode)
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        
        // edit form
        $this->form = new ilPropertyFormGUI();
        $this->form->setFormAction($ilCtrl->getFormAction($this));
        if ($a_mode == "create") {
            $this->form->setTitle($this->lng->txt("cont_insert_map"));
        } else {
            $this->form->setTitle($this->lng->txt("cont_update_map"));
        }
        
        // location
        $loc_prop = new ilLocationInputGUI(
            $this->lng->txt("cont_location"),
            "location"
        );
        $loc_prop->setRequired(true);
        $this->form->addItem($loc_prop);
        
        // width
        $width_prop = new ilNumberInputGUI(
            $this->lng->txt("cont_width"),
            "width"
        );
        $width_prop->setSize(4);
        $width_prop->setMaxLength(4);
        $width_prop->setRequired(true);
        $width_prop->setMinValue(250);
        $this->form->addItem($width_prop);
        
        // height
        $height_prop = new ilNumberInputGUI(
            $this->lng->txt("cont_height"),
            "height"
        );
        $height_prop->setSize(4);
        $height_prop->setMaxLength(4);
        $height_prop->setRequired(true);
        $height_prop->setMinValue(200);
        $this->form->addItem($height_prop);

        // horizonal align
        $align_prop = new ilSelectInputGUI(
            $this->lng->txt("cont_align"),
            "horizontal_align"
        );
        $options = array(
            "Left" => $lng->txt("cont_left"),
            "Center" => $lng->txt("cont_center"),
            "Right" => $lng->txt("cont_right"),
            "LeftFloat" => $lng->txt("cont_left_float"),
            "RightFloat" => $lng->txt("cont_right_float"));
        $align_prop->setOptions($options);
        $this->form->addItem($align_prop);
        
        // caption
        $caption_prop = new ilTextAreaInputGUI(
            $this->lng->txt("cont_caption"),
            "caption"
        );
        $this->form->addItem($caption_prop);

        // save/cancel buttons
        if ($a_mode == "create") {
            $this->form->addCommandButton("create_map", $lng->txt("save"));
            $this->form->addCommandButton("cancelCreate", $lng->txt("cancel"));
        } else {
            $this->form->addCommandButton("update_map", $lng->txt("save"));
            $this->form->addCommandButton("cancelUpdate", $lng->txt("cancel"));
        }
        //$html = $form->getHTML();
    }

    /**
    * Create new Map.
    */
    public function create()
    {
        $tpl = $this->tpl;
        
        $this->initForm("create");
        if ($this->form->checkInput()) {
            $this->content_obj = new ilPCMap($this->getPage());
            $location = $this->form->getInput("location");
            $this->content_obj->create($this->pg_obj, $this->hier_id, $this->pc_id);
            $this->content_obj->setLatitude($location["latitude"]);
            $this->content_obj->setLongitude($location["longitude"]);
            $this->content_obj->setZoom($location["zoom"]);
            $this->content_obj->setLayout(
                $this->form->getInput("width"),
                $this->form->getInput("height"),
                $this->form->getInput("horizontal_align")
            );
            $this->content_obj->setCaption(
                $this->content_obj->handleCaptionInput($this->form->getInput("caption"))
            );
            $this->updated = $this->pg_obj->update();
            if ($this->updated === true) {
                $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
                return;
            }
        }
        $this->displayValidationError();
        $this->form->setValuesByPost();
        $tpl->setContent($this->form->getHTML());
    }

    /**
    * Update Map.
    */
    public function update()
    {
        $tpl = $this->tpl;
        
        $this->initForm("update");
        if ($this->form->checkInput()) {
            $location = $this->form->getInput("location");
            $this->content_obj->setLatitude($location["latitude"]);
            $this->content_obj->setLongitude($location["longitude"]);
            $this->content_obj->setZoom($location["zoom"]);
            $this->content_obj->setLayout(
                $this->form->getInput("width"),
                $this->form->getInput("height"),
                $this->form->getInput("horizontal_align")
            );
            $this->content_obj->setCaption(
                $this->content_obj->handleCaptionInput($this->form->getInput("caption"))
            );
            $this->updated = $this->pg_obj->update();
            if ($this->updated === true) {
                $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
                return;
            }
        }
        $this->displayValidationError();
        $this->form->setValuesByPost();
        $tpl->setContent($this->form->getHTML());
    }
}
