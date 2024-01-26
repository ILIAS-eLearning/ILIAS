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

use ILIAS\Filesystem\Security\Sanitizing\FilenameSanitizer;
use ILIAS\Filesystem\Util\LegacyPathHelper;

require_once "./Services/Container/classes/class.ilContainer.php";

/**
* Class ilObjFolder
*
* @author Wolfgang Merkens <wmerkens@databay.de>
* @version $Id: class.ilObjFolder.php 40448 2013-03-08 10:02:02Z jluetzen $
*
* @extends ilObject
*/
class ilObjFolder extends ilContainer
{
    public $folder_tree;
    
    /**
     * Constructor
     * @access	public
     * @param	integer	reference_id or object_id
     * @param	boolean	treat the id as reference_id (true) or object_id (false)
     */
    public function __construct($a_id = 0, $a_call_by_reference = true)
    {
        global $DIC;

        $this->tree = $DIC->repositoryTree();
        $this->lng = $DIC->language();
        $this->rbacsystem = $DIC->rbac()->system();
        $this->access = $DIC->access();
        $this->type = "fold";
        parent::__construct($a_id, $a_call_by_reference);
        $this->lng->loadLanguageModule('fold');
    }

    public function setFolderTree($a_tree)
    {
        $this->folder_tree = &$a_tree;
    }
    
    /**
     * Clone folder
     *
     * @access public
     * @param int target id
     * @param int copy id
     *
     */
    public function cloneObject($a_target_id, $a_copy_id = 0, $a_omit_tree = false)
    {
        $new_obj = parent::cloneObject($a_target_id, $a_copy_id, $a_omit_tree);
        
        // Copy learning progress settings
        include_once('Services/Tracking/classes/class.ilLPObjSettings.php');
        $obj_settings = new ilLPObjSettings($this->getId());
        $obj_settings->cloneSettings($new_obj->getId());
        unset($obj_settings);
        
        return $new_obj;
    }

    /**
    * insert folder into grp_tree
    *
    */
    public function putInTree($a_parent)
    {
        $tree = $this->tree;
        
        if (!is_object($this->folder_tree)) {
            $this->folder_tree = &$tree;
        }

        if ($this->withReferences()) {
            // put reference id into tree
            $this->folder_tree->insertNode($this->getRefId(), $a_parent);
        } else {
            // put object id into tree
            $this->folder_tree->insertNode($this->getId(), $a_parent);
        }
    }
    
    /**
     * Clone object dependencies (crs items, preconditions)
     *
     * @access public
     * @param int target ref id of new course
     * @param int copy id
     *
     */
    public function cloneDependencies($a_target_id, $a_copy_id)
    {
        parent::cloneDependencies($a_target_id, $a_copy_id);

        include_once('Services/Object/classes/class.ilObjectActivation.php');
        ilObjectActivation::cloneDependencies($this->getRefId(), $a_target_id, $a_copy_id);
        
        return true;
    }

    /**
    * Get container view mode
    */
    public function getViewMode()
    {
        $tree = $this->tree;
        $possible_view_modes = [
            ilContainer::VIEW_SESSIONS,
            ilContainer::VIEW_BY_TYPE,
            ilContainer::VIEW_SIMPLE
        ];

        // always try to inherit from grp container, then crs container
        $container_grp_ref_id = $tree->checkForParentType($this->ref_id, 'grp');
        if ($container_grp_ref_id) {
            $grp_view_mode = ilObjGroup::lookupViewMode(ilObject::_lookupObjId($container_grp_ref_id));
            if (in_array($grp_view_mode, $possible_view_modes)) {
                return $grp_view_mode;
            }
        }
        $container_crs_ref_id = $tree->checkForParentType($this->ref_id, 'crs');
        if ($container_crs_ref_id) {
            $crs_view_mode = ilObjCourseAccess::_lookupViewMode(ilObject::_lookupObjId($container_crs_ref_id));
            if (in_array($crs_view_mode, $possible_view_modes)) {
                return $crs_view_mode;
            }
        }

        // default: by type
        return ilContainer::VIEW_BY_TYPE;
    }

    /**
    * Add additional information to sub item, e.g. used in
    * courses for timings information etc.
    */
    public function addAdditionalSubItemInformation(&$a_item_data)
    {
        include_once './Services/Object/classes/class.ilObjectActivation.php';
        ilObjectActivation::addAdditionalSubItemInformation($a_item_data);
    }
    
    /**
     * Overwritten read method
     *
     * @access public
     * @param
     * @return
     */
    public function read()
    {
        $tree = $this->tree;
        
        parent::read();
        
        // Inherit order type from parent course (if exists)
        include_once('./Services/Container/classes/class.ilContainerSortingSettings.php');
        $this->setOrderType(ilContainerSortingSettings::_lookupSortMode($this->getId()));
    }
} // END class.ilObjFolder
