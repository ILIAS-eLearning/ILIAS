<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * ilForumMoveTopicsExplorer
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilForumMoveTopicsExplorer extends ilRepositorySelectorExplorerGUI
{
    /**
     * @var int
     */
    protected $current_frm_ref_id = 0;

    /**
     * {@inheritdoc}
     */
    public function __construct(
        $a_parent_obj,
        $a_parent_cmd,
        $a_selection_gui = null,
        $a_selection_cmd = "selectObject",
        $a_selection_par = "sel_ref_id"
    ) {
        parent::__construct($a_parent_obj, $a_parent_cmd, $a_selection_gui, $a_selection_cmd, $a_selection_par);
        $this->setTypeWhiteList(array('root', 'cat', 'fold', 'crs', 'grp', 'frm'));
        $this->setSelectMode('frm_ref_id');
    }

    /**
     * @return int
     */
    public function getCurrentFrmRefId()
    {
        return $this->current_frm_ref_id;
    }

    /**
     * @param int $current_frm_ref_id
     */
    public function setCurrentFrmRefId($current_frm_ref_id)
    {
        $this->current_frm_ref_id = $current_frm_ref_id;
    }

    /**
     * {@inheritdoc}
     */
    public function isNodeClickable($a_node)
    {
        global $DIC;

        if ($a_node['type'] == 'frm') {
            if ($this->getCurrentFrmRefId() && $this->getCurrentFrmRefId() == $a_node['child']) {
                return false;
            }

            return $DIC->access()->checkAccess('moderate_frm', '', $a_node['child']) && parent::isNodeClickable($a_node);
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isNodeVisible($a_node)
    {
        return parent::isNodeVisible($a_node);
    }

    /**
     * {@inheritdoc}
     */
    protected function isNodeSelectable($a_node)
    {
        global $DIC;
        
        if ($a_node['type'] == 'frm') {
            if ($this->getCurrentFrmRefId() && $this->getCurrentFrmRefId() == $a_node['child']) {
                return false;
            }

            return $DIC->access()->checkAccess('moderate_frm', '', $a_node['child']) && parent::isNodeSelectable($a_node);
        }

        return false;
    }
}
