<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "./Services/Repository/classes/class.ilRepositorySelectorExplorerGUI.php";

/**
* Repository Explorer
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*/
class ilConditionSelector extends ilRepositorySelectorExplorerGUI
{
    protected $highlighted_parent = null;
    protected $clickable_types = array();
    protected $ref_id = null;

    /**
     * Construct
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
        $a_selection_cmd = "add",
        $a_selection_par = "source_id"
    ) {
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
     * Is node visible
     *
     * @param array $a_node node data
     * @return bool visible true/false
     */
    public function isNodeVisible($a_node)
    {
        global $DIC;

        $ilAccess = $DIC['ilAccess'];
        $tree = $DIC['tree'];

        if (!$ilAccess->checkAccess('read', '', $a_node["child"])) {
            return false;
        }
        //remove childs of target object
        if ($tree->getParentId($a_node["child"]) == $this->getRefId()) {
            return false;
        }

        return true;
    }

    /**
     * Is node clickable?
     *
     * @param array $a_node node data
     * @return boolean node clickable true/false
     */
    public function isNodeClickable($a_node)
    {
        if (!parent::isNodeClickable($a_node)) {
            return false;
        }

        if ($a_node["child"] == $this->getRefId()) {
            return false;
        }

        return true;
    }

    /**
     * set ref id of target object
     *
     * @param $a_ref_id
     */
    public function setRefId($a_ref_id)
    {
        global $DIC;

        $tree = $DIC['tree'];

        $this->ref_id = $a_ref_id;

        //can target object be highlighted?
        $target_type = ilObject::_lookupType($a_ref_id, true);

        if (!in_array($target_type, $this->getTypeWhiteList())) {
            $this->highlighted_parent = $tree->getParentId($a_ref_id);
        }
    }

    /**
     * get ref id of target object
     *
     * @return mixed
     */
    public function getRefId()
    {
        return $this->ref_id;
    }

    /**
     * Is node highlighted?
     *
     * @param mixed $a_node node object/array
     * @return boolean node visible true/false
     */
    public function isNodeHighlighted($a_node)
    {
        //highlight parent if target object cant be highlighted
        if ($this->highlighted_parent == $a_node["child"]) {
            return true;
        }

        return parent::isNodeHighlighted($a_node);
    }
}
