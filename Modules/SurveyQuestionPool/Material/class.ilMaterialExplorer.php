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
 * Material Explorer for survey question pools
 * @author Helmut Schottmüller <helmut.schottmueller@mac.com>
 */
class ilMaterialExplorer extends ilTreeExplorerGUI
{
    protected string $current_type;
    
    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        string $a_selectable_type
    ) {
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
    
    public function getNodeContent($a_node) : string
    {
        return $a_node["title"];
    }
    
    public function getNodeIcon($a_node) : string
    {
        $obj_id = ilObject::_lookupObjId($a_node["child"]);
        return ilObject::_getIcon($obj_id, "tiny", $a_node["type"]);
    }
    
    public function getNodeHref($a_node) : string
    {
        $ilCtrl = $this->ctrl;

        $ilCtrl->setParameter($this->parent_obj, 'source_id', $a_node["child"]);
        return $ilCtrl->getLinkTarget($this->parent_obj, 'linkChilds');
    }

    public function isNodeClickable($a_node) : bool
    {
        return ($a_node["type"] === $this->current_type);
    }
}
