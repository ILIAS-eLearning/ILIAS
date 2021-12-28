<?php declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Repository Explorer
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilConditionSelector extends ilRepositorySelectorExplorerGUI
{
    protected ?int $highlighted_parent = null;
    protected ?int $ref_id = null;

    /**
     * Construct
     * @inheritDoc
     */
    public function __construct(
        $a_parent_obj,
        string $a_parent_cmd,
        $a_selection_gui = null,
        string $a_selection_cmd = "add",
        string $a_selection_par = "source_id"
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
     * @inheritDoc
     */
    public function isNodeVisible($a_node) : bool
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
     * @inheritDoc
     */
    public function isNodeClickable($a_node) : bool
    {
        if (!parent::isNodeClickable($a_node)) {
            return false;
        }

        if ($a_node["child"] == $this->getRefId()) {
            return false;
        }

        return true;
    }

    public function setRefId(int $a_ref_id) : void
    {
        $this->ref_id = $a_ref_id;

        //can target object be highlighted?
        $target_type = ilObject::_lookupType($a_ref_id, true);

        if (!in_array($target_type, $this->getTypeWhiteList())) {
            $this->highlighted_parent = $this->tree->getParentId($a_ref_id);
        }
    }

    public function getRefId() : ?int
    {
        return $this->ref_id;
    }

    /**
     * @inheritDoc
     */
    public function isNodeHighlighted($a_node) : bool
    {
        //highlight parent if target object cant be highlighted
        if ($this->highlighted_parent == $a_node["child"]) {
            return true;
        }

        return parent::isNodeHighlighted($a_node);
    }
}
