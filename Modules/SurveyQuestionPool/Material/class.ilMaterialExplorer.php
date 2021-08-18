<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Material Explorer for survey question pools
 *
 * @author Helmut Schottmüller <helmut.schottmueller@mac.com>
 */
class ilMaterialExplorer extends ilTreeExplorerGUI
{
    protected $current_type; // [string]
    
    public function __construct($a_parent_obj, $a_parent_cmd, $a_selectable_type)
    {
        global $DIC;

        $this->tree = $DIC->repositoryTree();
        $this->ctrl = $DIC->ctrl();
        $tree = $DIC->repositoryTree();
                
        parent::__construct("mat_rep_exp", $a_parent_obj, $a_parent_cmd, $tree);
        
        $this->current_type = $a_selectable_type;
        
        $this->setTypeWhiteList(array("root", "cat", "grp", "fold", "crs", $this->current_type));
        $this->setSkipRootNode(true);
        $this->setAjax(true);
    }
    
    public function getNodeContent($a_node)
    {
        return $a_node["title"];
    }
    
    public function getNodeIcon($a_node)
    {
        $obj_id = ilObject::_lookupObjId($a_node["child"]);
        return ilObject::_getIcon($obj_id, "tiny", $a_node["type"]);
    }
    
    public function getNodeHref($a_node)
    {
        $ilCtrl = $this->ctrl;

        $ilCtrl->setParameter($this->parent_obj, 'source_id', $a_node["child"]);
        return $ilCtrl->getLinkTarget($this->parent_obj, 'linkChilds');
    }
        
    public function isNodeClickable($a_node)
    {
        return ($a_node["type"] == $this->current_type);
    }
}
