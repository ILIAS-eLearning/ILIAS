<?php

/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Repository/classes/class.ilRepositorySelectorExplorerGUI.php");

/**
 * Select files for file list
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @ingroup ServicesCOPage
 */
class ilPCFileItemFileSelectorGUI extends ilRepositorySelectorExplorerGUI
{
    /**
     * @inheritdoc
     */
    public function __construct(
        $a_parent_obj,
        $a_parent_cmd,
        $a_selection_gui = null,
        $a_selection_cmd = "selectObject",
        $a_selection_par = "sel_ref_id",
        $a_id = "rep_exp_sel"
    ) {
        parent::__construct($a_parent_obj, $a_parent_cmd, $a_selection_gui, $a_selection_cmd, $a_selection_par, $a_id);
        $this->setTypeWhiteList(array("root", "cat", "grp", "crs", "file", "fold"));
        $this->setClickableTypes(array("file"));
    }


    /**
     * @inheritdoc
     */
    public function getNodeHref($a_node)
    {
        $ctrl = $this->ctrl;

        $ctrl->setParameterByClass($this->selection_gui, "subCmd", "selectFile");

        return parent::getNodeHref($a_node);
    }

    /**
     * @inheritdoc
     */
    public function isNodeClickable($a_node)
    {
        $access = $this->access;

        if (!$access->checkAccess("write", "", $a_node["child"])) {
            return false;
        }

        return parent::isNodeClickable($a_node);
    }
}
