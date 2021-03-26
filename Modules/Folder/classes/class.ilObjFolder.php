<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

use ILIAS\Filesystem\Security\Sanitizing\FilenameSanitizer;
use ILIAS\Filesystem\Util\LegacyPathHelper;

/**
 * Class ilObjFolder
 *
 * @author Wolfgang Merkens <wmerkens@databay.de>
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

        ilObjectActivation::cloneDependencies($this->getRefId(), $a_target_id, $a_copy_id);
        
        return true;
    }

    /**
    * Get container view mode
    */
    public function getViewMode()
    {
        $tree = $this->tree;
        
        // default: by type
        $view = ilContainer::VIEW_BY_TYPE;

        // always inherit from
        $container_ref_id = $tree->checkForParentType($this->ref_id, 'grp');
        if (!$container_ref_id) {
            $container_ref_id = $tree->checkForParentType($this->ref_id, 'crs');
        }
        if ($container_ref_id) {
            $view_mode = ilObjCourseAccess::_lookupViewMode(ilObject::_lookupObjId($container_ref_id));
            if ($view_mode == ilContainer::VIEW_SESSIONS ||
                $view_mode == ilContainer::VIEW_BY_TYPE ||
                $view_mode == ilContainer::VIEW_SIMPLE) {
                $view = $view_mode;
            }
        }
        
        return $view;
    }

    /**
    * Add additional information to sub item, e.g. used in
    * courses for timings information etc.
    */
    public function addAdditionalSubItemInformation(&$a_item_data)
    {
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
        parent::read();
        
        // Inherit order type from parent course (if exists)
        $this->setOrderType(ilContainerSortingSettings::_lookupSortMode($this->getId()));
    }
}