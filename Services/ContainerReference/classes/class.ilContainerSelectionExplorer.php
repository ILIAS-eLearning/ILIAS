<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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

include_once('./Services/UIComponent/Explorer/classes/class.ilExplorer.php');

/**
*
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*
* @ingroup
*/
class ilContainerSelectionExplorer extends ilExplorer
{
    /**
     * @var ilAccessHandler
     */
    protected $access;

    /**
     * @var ilLanguage
     */
    protected $lng;

    protected $target_type;
    
    /**
     * Constructor
     */
    public function __construct($a_target)
    {
        global $DIC;

        $this->access = $DIC->access();
        $this->lng = $DIC->language();
        $tree = $DIC->repositoryTree();
        
        parent::__construct($a_target);
         
        $this->tree = $tree;
        $this->root_id = $this->tree->readRootId();
        $this->order_column = "title";

        $this->setSessionExpandVariable("ref_repexpand");
         
         
        $this->addFilter("root");
        $this->addFilter("cat");
        $this->addFilter("grp");
        #$this->addFilter("fold");
        $this->addFilter("crs");

        $this->setFilterMode(IL_FM_POSITIVE);
        $this->setFiltered(true);
        $this->setTitleLength(ilObject::TITLE_LENGTH);
        
        $this->checkPermissions(true);
    }
    
    /**
     * set target type
     * @param
     * @return
     */
    public function setTargetType($a_type)
    {
        $this->target_type = $a_type;
    }
    
    /**
     * get target type
     * @param
     * @return
     */
    public function getTargetType()
    {
        return $this->target_type;
    }
    
    /**
     * check if item is clickable
     * @param
     * @return
     */
    public function isClickable($a_type, $a_id = 0)
    {
        $ilAccess = $this->access;
        
        if ($this->getTargetType() == $a_type) {
            if ($ilAccess->checkAccess('visible', '', $a_id)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Visible permission is sufficient
     * @param type $a_ref_id
     * @param type $a_type
     * @return type
     */
    public function isVisible($a_ref_id, $a_type)
    {
        $ilAccess = $this->access;
        
        return $ilAccess->checkAccess('visible', '', $a_ref_id);
    }
    
    /**
    * overwritten method from base class
    * @access	public
    * @param	integer obj_id
    * @param	integer array options
    */
    public function formatHeader($a_tpl, $a_obj_id, $a_option)
    {
        $lng = $this->lng;

        $tpl = new ilTemplate("tpl.tree.html", true, true, "Services/UIComponent/Explorer");

        $tpl->setCurrentBlock("text");
        $tpl->setVariable("OBJ_TITLE", $lng->txt("repository"));
        $tpl->parseCurrentBlock();

        $this->output[] = $tpl->get();
    }
}
