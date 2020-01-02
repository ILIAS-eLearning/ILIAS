<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Select media pool for adding objects into pages
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesCOPage
*/

include_once "./Services/Repository/classes/class.ilRepositorySelectorExplorerGUI.php";

class ilPoolSelectorGUI extends ilRepositorySelectorExplorerGUI
{
    protected $clickable_types = array();
    protected $selection_subcmd = "";


    /**
     * Constructor
     *
     * @param object $a_parent_obj
     * @param string $a_parent_cmd
     * @param object $a_selection_gui
     * @param string $a_selection_cmd
     * @param string $a_selection_par
     */
    public function __construct(
        $a_parent_obj,
        $a_parent_cmd,
        $a_selection_gui = null,
        $a_selection_cmd = "insert",
        $a_selection_subcmd = "selectPool",
        $a_selection_par = "pool_ref_id"
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
     *
     * @param mixed $a_node node object/array
     * @return string href attribute
     */
    public function getNodeHref($a_node)
    {
        $ilCtrl = $this->ctrl;
        
        $ilCtrl->setParameterByClass($this->selection_gui, "subCmd", $this->selection_subcmd);
        $link = parent::getNodeHref($a_node);
        $ilCtrl->setParameterByClass($this->selection_gui, "subCmd", "");
        return $link;
    }

    /**
     * Is node visible
     *
     * @param array $a_node node data
     * @return bool visible true/false
     */
    public function isNodeVisible($a_node)
    {
        if (parent::isNodeVisible($a_node)) {
            //hide empty container
            if (count($this->getChildsOfNode($a_node["child"]))>0 || $this->isNodeClickable($a_node)) {
                // #16523
                if ($a_node["type"] == "qpl") {
                    include_once "Modules/TestQuestionPool/classes/class.ilObjQuestionPool.php";
                    return ilObjQuestionPool::_lookupOnline($a_node["obj_id"]);
                }

                return true;
            }
        }
        
        return false;
    }
}
