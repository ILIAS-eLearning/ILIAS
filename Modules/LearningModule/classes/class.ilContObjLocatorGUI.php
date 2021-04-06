<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Content Object Locator GUI
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilContObjLocatorGUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    public $mode;
    public $temp_var;
    public $tree;
    public $lng;
    public $tpl;


    public function __construct($a_tree)
    {
        global $DIC;

        $lng = $DIC->language();
        $tpl = $DIC["tpl"];
        $ilCtrl = $DIC->ctrl();

        $this->ctrl = $ilCtrl;
        $this->tree = $a_tree;
        $this->mode = "std";
        $this->temp_var = "LOCATOR";
        $this->lng = $lng;
        $this->tpl = $tpl;
        $this->show_user = false;
    }

    public function setTemplateVariable($a_temp_var)
    {
        $this->temp_var = $a_temp_var;
    }

    public function setObjectID($a_obj_id)
    {
        $this->obj_id = $a_obj_id;
    }

    public function setContentObject($a_cont_obj)
    {
        $this->cont_obj = $a_cont_obj;
    }

    /**
    * display locator
    */
    public function display($a_gui_class)
    {
        $lng = $this->lng;

        $this->tpl->addBlockFile($this->temp_var, "locator", "tpl.locator.html", "Services/Locator");

        if (($this->obj_id != 0) && $this->tree->isInTree($this->obj_id)) {
            $path = $this->tree->getPathFull($this->obj_id);
        } else {
            $path = $this->tree->getPathFull($this->tree->getRootId());
            if ($this->obj_id != 0) {
                $path[] = array("type" => "pg", "child" => $this->obj_id,
                    "title" => ilLMPageObject::_getPresentationTitle($this->obj_id));
            }
        }

        $modifier = 1;

        foreach ($path as $key => $row) {
            if ($key < count($path) - $modifier) {
                $this->tpl->touchBlock("locator_separator");
            }

            $this->tpl->setCurrentBlock("locator_item");
            $transit = "";
            if ($row["child"] == 1) {
                $title = $this->cont_obj->getTitle();
                $cmd = "properties";
                $cmdClass = $a_gui_class;
            } else {
                $title = $row["title"];
                switch ($row["type"]) {
                    case "st":
                        $cmdClass = "ilStructureObjectGUI";
                        $cmd = "view";
                        if ($this->ctrl->getCmdClass() != "ilstructureobjectgui") {
                            $transit = array($a_gui_class);
                        }
                        break;

                    case "pg":
                        $cmdClass = "ilLMPageObjectGUI";
                        $cmd = "view";
                        if ($this->ctrl->getCmdClass() != "illmpageobjectgui") {
                            $transit = array($a_gui_class);
                        }
                        break;
                }
            }
            $this->tpl->setVariable("ITEM", $title);
            $obj_str = ($row["child"] == 1)
                ? ""
                : "&obj_id=" . $row["child"];

            $this->ctrl->setParameterByClass($cmdClass, "obj_id", $row["child"]);
            $link = $this->ctrl->getLinkTargetByClass($cmdClass, $cmd, $transit);
            $this->ctrl->setParameterByClass($cmdClass, "obj_id", $_GET["obj_id"]);
            $this->tpl->setVariable("LINK_ITEM", $link);
            $this->tpl->parseCurrentBlock();
        }

        $this->tpl->setCurrentBlock("locator");
        $this->tpl->parseCurrentBlock();
    }
}
