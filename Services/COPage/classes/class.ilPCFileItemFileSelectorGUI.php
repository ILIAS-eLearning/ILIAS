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
 * Select files for file list
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPCFileItemFileSelectorGUI extends ilRepositorySelectorExplorerGUI
{
    /**
     * @param object|array $a_parent_obj parent gui class or class array
     * @param object|string $a_selection_gui gui class that should be called for the selection command
     */
    public function __construct(
        $a_parent_obj,
        string $a_parent_cmd,
        $a_selection_gui = null,
        string $a_selection_cmd = "selectObject",
        string $a_selection_par = "sel_ref_id",
        string $a_id = "rep_exp_sel"
    ) {
        parent::__construct($a_parent_obj, $a_parent_cmd, $a_selection_gui, $a_selection_cmd, $a_selection_par, $a_id);
        $this->setTypeWhiteList(array("root", "cat", "grp", "crs", "file", "fold"));
        $this->setClickableTypes(array("file"));
    }

    /**
     * @param array|object $a_node
     */
    public function getNodeHref($a_node) : string
    {
        $ctrl = $this->ctrl;

        $ctrl->setParameterByClass($this->selection_gui, "subCmd", "selectFile");

        return parent::getNodeHref($a_node);
    }

    /**
     * @param array|object $a_node
     */
    public function isNodeClickable($a_node) : bool
    {
        $access = $this->access;

        if (!$access->checkAccess("write", "", $a_node["child"])) {
            return false;
        }

        return parent::isNodeClickable($a_node);
    }
}
