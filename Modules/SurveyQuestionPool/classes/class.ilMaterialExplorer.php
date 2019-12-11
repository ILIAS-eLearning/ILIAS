<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

/*
* Material Explorer for survey question pools
*
* @author Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version $Id$
*
* @ingroup ModulesSurveyQuestionPool
*/

include_once("./Services/UIComponent/Explorer2/classes/class.ilTreeExplorerGUI.php");

class ilMaterialExplorer extends ilTreeExplorerGUI
{
    protected $current_type; // [string]
    
    public function __construct($a_parent_obj, $a_parent_cmd, $a_selectable_type)
    {
        global $DIC;

        $this->tree = $DIC->repositoryTree();
        $this->ctrl = $DIC->ctrl();
        $tree = $DIC->repositoryTree();
                
        parent::__construct("rep_exp", $a_parent_obj, $a_parent_cmd, $tree);
        
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
