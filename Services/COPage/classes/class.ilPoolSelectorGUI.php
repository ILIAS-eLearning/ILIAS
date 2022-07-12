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
 * Select media pool for adding objects into pages
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPoolSelectorGUI extends ilRepositorySelectorExplorerGUI
{
    protected string $selection_subcmd = "";


    /**
     * @param object|array $a_parent_obj parent gui class or class array
     * @param object|string $a_selection_gui gui class that should be called for the selection command
     */
    public function __construct(
        $a_parent_obj,
        string $a_parent_cmd,
        $a_selection_gui = null,
        string $a_selection_cmd = "insert",
        string $a_selection_subcmd = "selectPool",
        string $a_selection_par = "pool_ref_id"
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        if ($a_selection_gui == null) {
            $a_selection_gui = $a_parent_obj;
        }

        $this->selection_subcmd = $a_selection_subcmd;
        parent::__construct(
            $a_parent_obj,
            $a_parent_cmd,
            $a_selection_gui,
            $a_selection_cmd,
            $a_selection_par
        );

        $this->setAjax(true);
    }

    /**
     * Get href for node
     * @param mixed $a_node node object/array
     * @return string href attribute
     */
    public function getNodeHref($a_node) : string
    {
        $ilCtrl = $this->ctrl;
        
        $ilCtrl->setParameterByClass($this->selection_gui, "subCmd", $this->selection_subcmd);
        $link = parent::getNodeHref($a_node);
        $ilCtrl->setParameterByClass($this->selection_gui, "subCmd", "");
        return $link;
    }

    /**
     * Is node visible
     * @param array $a_node node data
     * @return bool visible true/false
     */
    public function isNodeVisible($a_node) : bool
    {
        if (parent::isNodeVisible($a_node)) {
            //hide empty container
            if (count($this->getChildsOfNode($a_node["child"])) > 0 || $this->isNodeClickable($a_node)) {
                // #16523
                if ($a_node["type"] == "qpl") {
                    return ilObjQuestionPool::_lookupOnline($a_node["obj_id"]);
                }

                return true;
            }
        }
        
        return false;
    }
}
