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


/**
* class ilCourseObjectiveMaterials
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id:class.ilCourseObjectiveMaterials.php 13383 2007-03-02 10:54:46 +0000 (Fr, 02 Mrz 2007) smeyer $
*
*/

class ilCourseObjectiveMaterials
{
    public $db = null;

    public $objective_id = null;
    public $lms;

    public function __construct($a_objective_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $this->db = &$ilDB;
    
        $this->objective_id = $a_objective_id;

        $this->__read();
    }
    
    /**
     * clone objective materials
     *
     * @access public
     *
     * @param int source objective
     * @param int target objective
     * @param int copy id
     */
    public function cloneDependencies($a_new_objective, $a_copy_id)
    {
        global $DIC;

        $ilObjDataCache = $DIC['ilObjDataCache'];
        $ilLog = $DIC['ilLog'];
        
        include_once('Services/CopyWizard/classes/class.ilCopyWizardOptions.php');
        $cwo = ilCopyWizardOptions::_getInstance($a_copy_id);
        $mappings = $cwo->getMappings();
        #$ilLog->write(__METHOD__.': 1');
        foreach ($this->getMaterials() as $material) {
            #$ilLog->write(__METHOD__.': 2');
            // Copy action omit ?
            if (!isset($mappings[$material['ref_id']]) or !$mappings[$material['ref_id']]) {
                continue;
            }
            #$ilLog->write(__METHOD__.': 3');
            $material_ref_id = $material['ref_id'];
            $material_rbac_obj_id = $ilObjDataCache->lookupObjId($material_ref_id);
            $material_obj_id = $material['obj_id'];
            $new_ref_id = $mappings[$material_ref_id];
            $new_rbac_obj_id = $ilObjDataCache->lookupObjId($new_ref_id);
            #$ilLog->write(__METHOD__.': 4');
            
            // Link
            if ($new_rbac_obj_id == $material_rbac_obj_id) {
                #$ilLog->write(__METHOD__.': 5');
                $ilLog->write(__METHOD__ . ': Material has been linked. Keeping object id.');
                $new_obj_id = $material_obj_id;
            } elseif ($material['type'] == 'st' or $material['type'] == 'pg') {
                
                #$GLOBALS['DIC']['ilLog']->write(__METHOD__.': '.print_r($material,TRUE));
                #$GLOBALS['DIC']['ilLog']->write(__METHOD__.': '.print_r($mappings,TRUE));
                
                #$ilLog->write(__METHOD__.': 6');
                // Chapter assignment
                $new_material_info = isset($mappings[$material_ref_id . '_' . $material_obj_id]) ?
                    $mappings[$material_ref_id . '_' . $material_obj_id] :
                    '';
                $new_material_arr = explode('_', $new_material_info);
                if (!isset($new_material_arr[1]) or !$new_material_arr[1]) {
                    $ilLog->write(__METHOD__ . ': No mapping found for chapter: ' . $material_obj_id);
                    continue;
                }
                $new_obj_id = $new_material_arr[1];
                $ilLog->write(__METHOD__ . ': New material id is: ' . $new_obj_id);
            } else {
                #$ilLog->write(__METHOD__.': 7');
                // Any type
                $new_obj_id = $ilObjDataCache->lookupObjId($mappings[$material_ref_id]);
            }
    
            #$ilLog->write(__METHOD__.': 8');
            $new_material = new ilCourseObjectiveMaterials($a_new_objective);
            #$ilLog->write(__METHOD__.': 8.1');
            $new_material->setLMRefId($new_ref_id);
            #$ilLog->write(__METHOD__.': 8.2');
            $new_material->setLMObjId($new_obj_id);
            #$ilLog->write(__METHOD__.': 8.3');
            $new_material->setType($material['type']);
            #$ilLog->write(__METHOD__.': 8.4');
            $new_material->add();
            #$ilLog->write(__METHOD__.': 9');
        }
    }
    
    /**
     * get assigned materials
     *
     * @access public
     * @param int objective_id
     * @return
     * @static
     */
    public static function _getAssignedMaterials($a_objective_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "SELECT DISTINCT(ref_id) ref_id FROM crs_objective_lm " .
            "WHERE objective_id = " . $ilDB->quote($a_objective_id, 'integer');
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $ref_ids[] = $row->ref_id;
        }
        return $ref_ids ? $ref_ids : array();
    }
    
    

    /**
     * Get an array of course material ids that can be assigned to learning objectives
     * No tst, fold and grp.
     *
     * @access public
     * @static
     *
     * @param int obj id of course
     * @return array data of course materials
     */
    public static function _getAssignableMaterials($a_container_id)
    {
        global $DIC;

        $tree = $DIC['tree'];
        $ilDB = $DIC['ilDB'];
        
        $container_obj_id = ilObject::_lookupObjId($a_container_id);
        
        $all_materials = $tree->getSubTree($tree->getNodeData($a_container_id), true);
        $all_materials = ilUtil::sortArray($all_materials, 'title', 'asc');
        
        // Filter
        foreach ($all_materials as $material) {
            switch ($material['type']) {
                case 'tst':
                    
                    include_once './Modules/Course/classes/class.ilCourseObjectiveMaterials.php';
                    $type = ilLOTestAssignments::getInstance($container_obj_id)->getTypeByTest($material['child']);
                    if ($type != ilLOSettings::TYPE_TEST_UNDEFINED) {
                        continue;
                    } else {
                        $assignable[] = $material;
                    }
                    break;
                    
                case 'crs':
                case 'rolf':
                case 'itgr':
                    continue;
                
                default:
                    $assignable[] = $material;
                    break;
            }
        }
        return $assignable ? $assignable : array();
    }
    
    /**
     * Get all assigned materials
     *
     * @access public
     * @static
     *
     * @param in
     */
    public static function _getAllAssignedMaterials($a_container_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "SELECT DISTINCT(com.ref_id) ref_id FROM crs_objectives co " .
            "JOIN crs_objective_lm com ON co.objective_id = com.objective_id " .
            "JOIN object_reference obr ON com.ref_id = obr.ref_id " .
            "JOIN object_data obd ON obr.obj_id = obd.obj_id " .
            "WHERE co.crs_id = " . $ilDB->quote($a_container_id, 'integer') . " " .
            "ORDER BY obd.title ";
            
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $ref_ids[] = $row->ref_id;
        }
        return $ref_ids ? $ref_ids : array();
    }

    public function getMaterials()
    {
        return $this->lms ? $this->lms : array();
    }

    public function getChapters()
    {
        foreach ($this->lms as $lm_data) {
            if ($lm_data['type'] == 'st') {
                $chapters[] = $lm_data;
            }
            if ($lm_data['type'] == 'pg') {
                $chapters[] = $lm_data;
            }
        }
        return $chapters ? $chapters : array();
    }
    
    public function getLM($lm_id)
    {
        return $this->lms[$lm_id] ? $this->lms[$lm_id] : array();
    }

    public function getObjectiveId()
    {
        return $this->objective_id;
    }

    public function setLMRefId($a_ref_id)
    {
        $this->lm_ref_id = $a_ref_id;
    }
    public function getLMRefId()
    {
        return $this->lm_ref_id ? $this->lm_ref_id : 0;
    }
    public function setLMObjId($a_obj_id)
    {
        $this->lm_obj_id = $a_obj_id;
    }
    public function getLMObjId()
    {
        return $this->lm_obj_id ? $this->lm_obj_id : 0;
    }
    public function setType($a_type)
    {
        $this->type = $a_type;
    }
    public function getType()
    {
        return $this->type;
    }
    
    /**
     * Check if material is assigned
     *
     * @access public
     *
     * @param int ref id
     * @return bool
     */
    public function isAssigned($a_ref_id, $a_get_id = false)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "SELECT * FROM crs_objective_lm " .
            "WHERE ref_id = " . $this->db->quote($a_ref_id, 'integer') . " " .
            "AND objective_id = " . $this->db->quote($this->getObjectiveId(), 'integer') . " " .
            "AND type != 'st' AND type != 'pg' ";
        $res = $this->db->query($query);
        
        // begin-patch lok
        if (!$a_get_id) {
            return $res->numRows() ? true : false;
        } else {
            $row = $this->db->fetchAssoc($res);
            return $row["lm_ass_id"];
        }
        // end-patch lok
    }

    /**
     * Check if chapter is assigned
     *
     * @access public
     *
     * @param int ref id
     * @return bool
     */
    public function isChapterAssigned($a_ref_id, $a_obj_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "SELECT * FROM crs_objective_lm " .
            "WHERE ref_id = " . $this->db->quote($a_ref_id, 'integer') . " " .
            "AND obj_id = " . $this->db->quote($a_obj_id, 'integer') . " " .
            "AND objective_id = " . $this->db->quote($this->getObjectiveId(), 'integer') . " " .
            "AND (type = 'st' OR type = 'pg')";
        $res = $this->db->query($query);
        return $res->numRows() ? true : false;
    }
    public function checkExists()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        if ($this->getLMObjId()) {
            $query = "SELECT * FROM crs_objective_lm " .
                "WHERE objective_id = " . $ilDB->quote($this->getObjectiveId(), 'integer') . " " .
                "AND ref_id = " . $ilDB->quote($this->getLMRefId(), 'integer') . " " .
                "AND obj_id = " . $ilDB->quote($this->getLMObjId(), 'integer') . " ";
        } else {
            $query = "SELECT * FROM crs_objective_lm " .
                "WHERE objective_id = " . $ilDB->quote($this->getObjectiveId(), 'integer') . " " .
                "AND ref_id = " . $ilDB->quote($this->getLMRefId(), 'integer') . " ";
        }

        $res = $this->db->query($query);

        return $res->numRows() ? true : false;
    }

    public function add()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $next_id = $ilDB->nextId('crs_objective_lm');
        $query = "INSERT INTO crs_objective_lm (lm_ass_id,objective_id,ref_id,obj_id,type) " .
            "VALUES( " .
            $ilDB->quote($next_id, 'integer') . ", " .
            $ilDB->quote($this->getObjectiveId(), 'integer') . ", " .
            $ilDB->quote($this->getLMRefId(), 'integer') . ", " .
            $ilDB->quote($this->getLMObjId(), 'integer') . ", " .
            $ilDB->quote($this->getType(), 'text') .
            ")";
        $res = $ilDB->manipulate($query);
        
        return (int) $next_id;
    }
    public function delete($lm_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        if (!$lm_id) {
            return false;
        }

        $query = "DELETE FROM crs_objective_lm " .
            "WHERE lm_ass_id = " . $ilDB->quote($lm_id, 'integer') . " ";
        $res = $ilDB->manipulate($query);

        return true;
    }

    public function deleteAll()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "DELETE FROM crs_objective_lm " .
            "WHERE objective_id = " . $ilDB->quote($this->getObjectiveId(), 'integer') . " ";
        $res = $ilDB->manipulate($query);
        return true;
    }
    
    // begin-patch lok
    
    /**
     * write position
     *
     * @access public
     * @param int new position
     * @return
     */
    public function writePosition($a_ass_id, $a_position)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "UPDATE crs_objective_lm " .
            "SET position = " . $this->db->quote((string) $a_position, 'integer') . " " .
            "WHERE objective_id = " . $this->db->quote($this->getObjectiveId(), 'integer') . " " .
            "AND lm_ass_id = " . $ilDB->quote($a_ass_id, "integer");
        $res = $ilDB->manipulate($query);
    }
    
    // end-patch lok

    // PRIVATE
    public function __read()
    {
        global $DIC;

        $tree = $DIC['tree'];
        $ilDB = $DIC['ilDB'];
        
        include_once('Modules/Course/classes/class.ilCourseObjective.php');
        $container_ref_ids = ilObject::_getAllReferences(ilCourseObjective::_lookupContainerIdByObjectiveId($this->objective_id));
        $container_ref_id = current($container_ref_ids);
        
        // begin-patch lok
        
        $this->lms = array();
        $query = "SELECT position,lm_ass_id,lm.ref_id,lm.obj_id,lm.type FROM crs_objective_lm lm " .
            "JOIN object_reference obr ON lm.ref_id = obr.ref_id " .
            "JOIN object_data obd ON obr.obj_id = obd.obj_id " .
            "LEFT JOIN lm_data lmd ON lmd.obj_id = lm.obj_id " .
            "WHERE objective_id = " . $ilDB->quote($this->getObjectiveId(), 'integer') . " " .
            "ORDER BY position,obd.title,lmd.title";
            
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            if (!$tree->isInTree($row->ref_id) or !$tree->isGrandChild($container_ref_id, $row->ref_id)) {
                $this->delete($row->lm_ass_id);
                continue;
            }
            $lm['ref_id'] = $row->ref_id;
            $lm['obj_id'] = $row->obj_id;
            $lm['type'] = $row->type;
            $lm['lm_ass_id'] = $row->lm_ass_id;
            $lm['position'] = $row->position;

            $this->lms[$row->lm_ass_id] = $lm;
        }
        
        // end-patch lok
        
        return true;
    }
    
    // begin-patch optes_lok_export
    
    /**
     *
     * @param ilXmlWriter $writer
     */
    public function toXml(ilXmlWriter $writer)
    {
        foreach ($this->getMaterials() as $material) {
            $writer->xmlElement(
                'Material',
                array(
                    'refId' => $material['ref_id'],
                    'objId' => $material['obj_id'],
                    'type' => $material['type'],
                    'position' => $material['position']
                )
            );
        }
        return true;
    }
}
