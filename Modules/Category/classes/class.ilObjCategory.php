<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Container/classes/class.ilContainer.php";


/** @defgroup ModulesCategory Modules/Category
 */

/**
* Class ilObjCategory
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
* @ingroup ModulesCategory
*/
class ilObjCategory extends ilContainer
{
    /**
    * Constructor
    * @access	public
    * @param	integer	reference_id or object_id
    * @param	boolean	treat the id as reference_id (true) or object_id (false)
    */
    public function __construct($a_id = 0, $a_call_by_reference = true)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->app_event_handler = $DIC["ilAppEventHandler"];
        $this->log = $DIC["ilLog"];
        $this->user = $DIC->user();
        $this->type = "cat";
        parent::__construct($a_id, $a_call_by_reference);
    }

    /**
    * delete category and all related data
    *
    * @access	public
    * @return	boolean	true if all object data were removed; false if only a references were removed
    */
    public function delete()
    {
        $ilDB = $this->db;
        $ilAppEventHandler = $this->app_event_handler;
        
        // always call parent delete function first!!
        if (!parent::delete()) {
            return false;
        }
        
        // put here category specific stuff
        include_once('./Services/User/classes/class.ilObjUserFolder.php');
        ilObjUserFolder::_updateUserFolderAssignment($this->ref_id, USER_FOLDER_ID);

        // taxonomies
        include_once "Services/Taxonomy/classes/class.ilObjTaxonomy.php";
        foreach (ilObjTaxonomy::getUsageOfObject($this->getId()) as $tax_id) {
            if ($tax_id) {
                $tax = new ilObjTaxonomy($tax_id);
                $tax->delete();
            }
        }
        
        $ilAppEventHandler->raise(
            'Modules/Category',
            'delete',
            array('object' => $this,
                'obj_id' => $this->getId())
        );
        
        return true;
    }

    /**
     * Clone course (no member data)
     *
     * @access public
     * @param int target ref_id
     * @param int copy id
     *
     */
    public function cloneObject($a_target_id, $a_copy_id = 0, $a_omit_tree = false)
    {
        $new_obj = parent::cloneObject($a_target_id, $a_copy_id, $a_omit_tree);

        return $new_obj;
    }
    
    public function cloneDependencies($a_target_id, $a_copy_id)
    {
        parent::cloneDependencies($a_target_id, $a_copy_id);
    
                                
        // clone taxonomies
            
        include_once("./Services/Taxonomy/classes/class.ilObjTaxonomy.php");
        $all_tax = ilObjTaxonomy::getUsageOfObject($this->getId());
        if (sizeof($all_tax)) {
            include_once("./Services/Taxonomy/classes/class.ilTaxNodeAssignment.php");
            
            $cwo = ilCopyWizardOptions::_getInstance($a_copy_id);
            $mappings = $cwo->getMappings();
            
            foreach ($all_tax as $old_tax_id) {
                if ($old_tax_id) {
                    // clone it
                    $old_tax = new ilObjTaxonomy($old_tax_id);
                    $new_tax = $old_tax->cloneObject(0, 0, true);
                    $tax_map = $old_tax->getNodeMapping();
                
                    // assign new taxonomy to new category
                    ilObjTaxonomy::saveUsage($new_tax->getId(), ilObject::_lookupObjId($a_target_id));
                                                        
                    // clone assignments (for all sub-items)
                    foreach ($mappings as $old_ref_id => $new_ref_id) {
                        if ($old_ref_id != $new_ref_id) {
                            $old_obj_id = ilObject::_lookupObjId($old_ref_id);
                            $new_obj_id = ilObject::_lookupObjId($new_ref_id);
                            $obj_type = ilObject::_lookupType($old_obj_id);
                                                                    
                            $tax_ass = new ilTaxNodeAssignment($obj_type, $old_obj_id, "obj", $old_tax_id);
                            $assignmts = $tax_ass->getAssignmentsOfItem($old_obj_id);
                            if (sizeof($assignmts)) {
                                $new_tax_ass = new ilTaxNodeAssignment($obj_type, $new_obj_id, "obj", $new_tax->getId());
                                foreach ($assignmts as $a) {
                                    if ($tax_map[$a["node_id"]]) {
                                        $new_tax_ass->addAssignment($tax_map[$a["node_id"]], $new_obj_id);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
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
} // END class.ilObjCategory
