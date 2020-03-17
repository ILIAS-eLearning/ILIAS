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
* Repository Explorer
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
* @package core
*/

require_once("./Services/UIComponent/Explorer/classes/class.ilExplorer.php");

class ilSearchRootSelector extends ilExplorer
{

    /**
     * id of root folder
     * @var int root folder id
     * @access private
     */
    public $root_id;
    public $output;
    public $ctrl;

    public $selectable_type;
    public $ref_id;
    /**
    * Constructor
    * @access	public
    * @param	string	scriptname
    * @param    int user_id
    */
    public function __construct($a_target)
    {
        global $DIC;

        $tree = $DIC['tree'];
        $ilCtrl = $DIC['ilCtrl'];

        $this->ctrl = $ilCtrl;

        parent::__construct($a_target);
        $this->tree = $tree;
        $this->root_id = $this->tree->readRootId();
        $this->order_column = "title";

        $this->setSessionExpandVariable("search_root_expand");

        // add here all container objects
        $this->addFilter("root");
        $this->addFilter("cat");
        $this->addFilter("grp");
        $this->addFilter("fold");
        $this->addFilter("crs");
        $this->setClickableTypes(array("root", "cat", "grp", "fold", "crs"));

        $this->setFiltered(true);
        $this->setFilterMode(IL_FM_POSITIVE);
        
        $this->setTitleLength(ilObject::TITLE_LENGTH);
    }
    
    public function setClickableTypes($a_types)
    {
        $this->clickable_types = $a_types;
    }
    
    public function isClickable($a_type, $a_ref_id = 0)
    {
        if (in_array($a_type, $this->clickable_types)) {
            return true;
        }
        return false;
    }

    public function setTargetClass($a_class)
    {
        $this->target_class = $a_class;
    }
    public function getTargetClass()
    {
        return $this->target_class ? $this->target_class : 'ilsearchgui';
    }
    public function setCmd($a_cmd)
    {
        $this->cmd = $a_cmd;
    }
    public function getCmd()
    {
        return $this->cmd ? $this->cmd : 'selectRoot';
    }

    public function setSelectableType($a_type)
    {
        $this->selectable_type = $a_type;
    }
    public function setRefId($a_ref_id)
    {
        $this->ref_id = $a_ref_id;
    }
    

    public function buildLinkTarget($a_node_id, $a_type)
    {
        $this->ctrl->setParameterByClass($this->getTargetClass(), "root_id", $a_node_id);
        
        return $this->ctrl->getLinkTargetByClass($this->getTargetClass(), $this->getCmd());
    }

    public function buildFrameTarget($a_type, $a_child = 0, $a_obj_id = 0)
    {
        return '';
    }

    public function showChilds($a_ref_id)
    {
        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];

        if ($a_ref_id == 0) {
            return true;
        }

        if ($rbacsystem->checkAccess("read", $a_ref_id)) {
            return true;
        } else {
            return false;
        }
    }


    /**
    * overwritten method from base class
    * @access	public
    * @param	integer obj_id
    * @param	integer array options
    * @return	string
    */
    public function formatHeader($tpl, $a_obj_id, $a_option)
    {
        global $DIC;

        $lng = $DIC['lng'];
        $ilias = $DIC['ilias'];

        #$tpl = new ilTemplate("tpl.tree.html", true, true, "Services/UIComponent/Explorer");

        if (in_array("root", $this->clickable_types)) {
            $tpl->setCurrentBlock("link");
            //$tpl->setVariable("LINK_NAME",$lng->txt('repository'));
            
            $this->ctrl->setParameterByClass($this->getTargetClass(), 'root_id', ROOT_FOLDER_ID);
            $tpl->setVariable("LINK_TARGET", $this->ctrl->getLinkTargetByClass($this->getTargetClass(), $this->getCmd()));
            $tpl->setVariable("TITLE", $lng->txt("repository"));
            
            $tpl->parseCurrentBlock();
        }

        #$this->output[] = $tpl->get();

        return true;
    }
} // END class ilRepositoryExplorer
