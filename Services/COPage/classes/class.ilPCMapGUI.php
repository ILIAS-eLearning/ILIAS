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
 * Class ilPCMapGUI
 * User Interface for Map Editing
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPCMapGUI extends ilPageContentGUI
{
    protected ilPropertyFormGUI $form;

    public function __construct(
        ilPageObject $a_pg_obj,
        ?ilPageContent $a_content_obj,
        string $a_hier_id,
        string $a_pc_id = ""
    ) {
        global $DIC;

        $this->tpl = $DIC["tpl"];
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        parent::__construct($a_pg_obj, $a_content_obj, $a_hier_id, $a_pc_id);
    }

    public function executeCommand() : void
    {
        // get next class that processes or forwards current command
        $next_class = $this->ctrl->getNextClass($this);

        // get current command
        $cmd = $this->ctrl->getCmd();

        switch ($next_class) {
            default:
                $this->$cmd();
                break;
        }
    }

    public function insert() : void
    {
        $tpl = $this->tpl;
        
        $this->displayValidationError();
        $this->initForm("create");
        $tpl->setContent($this->form->getHTML());
    }

    public function edit() : void
    {
        $tpl = $this->tpl;
        $this->displayValidationError();
        $this->initForm("update");
        $this->getValues();
        $tpl->setContent($this->form->getHTML());
    }

    public function getValues() : void
    {
        $values = array();
        
        $values["location"]["latitude"] = $this->content_obj->getLatitude();
        $values["location"]["longitude"] = $this->content_obj->getLongitude();
        $values["location"]["zoom"] = $this->content_obj->getZoom();
        $values["width"] = $this->content_obj->getWidth();
        $values["height"] = $this->content_obj->getHeight();
        $values["caption"] = ilPCMap::handleCaptionFormOutput($this->content_obj->getCaption());
        $values["horizontal_align"] = $this->content_obj->getHorizontalAlign();
        
        $this->form->setValuesByArray($values);
    }

    public function initForm(string $a_mode) : void
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
    }

    public function create() : void
    {
        $tpl = $this->tpl;
        
        $this->initForm("create");
        if ($this->form->checkInput()) {
            $this->content_obj = new ilPCMap($this->getPage());
            $location = $this->form->getInput("location");
            $this->content_obj->create($this->pg_obj, $this->hier_id, $this->pc_id);
            $this->content_obj->setLatitude($location["latitude"]);
            $this->content_obj->setLongitude($location["longitude"]);
            $this->content_obj->setZoom((int) $location["zoom"]);
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

    public function update() : void
    {
        $tpl = $this->tpl;
        
        $this->initForm("update");
        if ($this->form->checkInput()) {
            $location = $this->form->getInput("location");
            $this->content_obj->setLatitude($location["latitude"]);
            $this->content_obj->setLongitude($location["longitude"]);
            $this->content_obj->setZoom((int) $location["zoom"]);
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
