<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * Class ilObjFolder
 *
 * @author Wolfgang Merkens <wmerkens@databay.de>
 */
class ilObjFolder extends ilContainer
{
    public ilTree $folder_tree;
    
    public function __construct(
        int $a_id = 0,
        bool $a_call_by_reference = true
    ) {
        global $DIC;

        $this->tree = $DIC->repositoryTree();
        $this->lng = $DIC->language();
        $this->rbacsystem = $DIC->rbac()->system();
        $this->access = $DIC->access();
        $this->type = "fold";
        parent::__construct($a_id, $a_call_by_reference);
        $this->lng->loadLanguageModule('fold');
    }

    public function setFolderTree(ilTree $a_tree) : void
    {
        $this->folder_tree = $a_tree;
    }
    
    public function cloneObject($a_target_id, $a_copy_id = 0, $a_omit_tree = false)
    {
        $new_obj = parent::cloneObject($a_target_id, $a_copy_id, $a_omit_tree);
        
        // Copy learning progress settings
        $obj_settings = new ilLPObjSettings($this->getId());
        $obj_settings->cloneSettings($new_obj->getId());
        unset($obj_settings);
        
        return $new_obj;
    }

    public function putInTree($a_parent_ref)
    {
        $tree = $this->tree;
        
        if (!is_object($this->folder_tree)) {
            $this->folder_tree = &$tree;
        }

        if ($this->withReferences()) {
            // put reference id into tree
            $this->folder_tree->insertNode($this->getRefId(), $a_parent_ref);
        } else {
            // put object id into tree
            $this->folder_tree->insertNode($this->getId(), $a_parent_ref);
        }
    }
    
    public function cloneDependencies($a_target_id, $a_copy_id)
    {
        parent::cloneDependencies($a_target_id, $a_copy_id);

        ilObjectActivation::cloneDependencies($this->getRefId(), $a_target_id, $a_copy_id);
        
        return true;
    }

    public function getViewMode() : int
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

    public function addAdditionalSubItemInformation(array &$object) : void
    {
        ilObjectActivation::addAdditionalSubItemInformation($object);
    }
    
    public function read()
    {
        parent::read();
        
        // Inherit order type from parent course (if exists)
        $this->setOrderType(ilContainerSortingSettings::_lookupSortMode($this->getId()));
    }
}
