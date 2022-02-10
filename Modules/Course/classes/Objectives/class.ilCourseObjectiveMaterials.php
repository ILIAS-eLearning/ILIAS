<?php declare(strict_types=0);
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
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilCourseObjectiveMaterials
{
    private int $objective_id = 0;
    private array $lms = [];
    private int $lm_ref_id = 0;
    private int $lm_obj_id = 0;
    private string $type = '';

    protected ilLogger $logger;
    protected ilDBInterface $db;
    protected ilObjectDataCache $objectDataCache;
    protected ilTree $tree;

    public function __construct(int $a_objective_id = 0)
    {
        global $DIC;

        $this->objectDataCache = $DIC['ilObjDataCache'];
        $this->tree = $DIC->repositoryTree();
        $this->db = $DIC->database();
        $this->objective_id = $a_objective_id;
        $this->__read();
    }

    public function cloneDependencies(int $a_new_objective, int $a_copy_id) : void
    {
        $cwo = ilCopyWizardOptions::_getInstance($a_copy_id);
        $mappings = $cwo->getMappings();
        foreach ($this->getMaterials() as $material) {
            // Copy action omit ?
            if (!isset($mappings[$material['ref_id']]) or !$mappings[$material['ref_id']]) {
                continue;
            }
            $material_ref_id = $material['ref_id'];
            $material_rbac_obj_id = $this->objectDataCache->lookupObjId($material_ref_id);
            $material_obj_id = $material['obj_id'];
            $new_ref_id = $mappings[$material_ref_id];
            $new_rbac_obj_id = $this->objectDataCache->lookupObjId($new_ref_id);

            if ($new_rbac_obj_id == $material_rbac_obj_id) {
                $this->logger->debug('Material has been linked. Keeping object id.');
                $new_obj_id = $material_obj_id;
            } elseif ($material['type'] == 'st' or $material['type'] == 'pg') {
                // Chapter assignment
                $new_material_info = $mappings[$material_ref_id . '_' . $material_obj_id] ?? '';
                $new_material_arr = explode('_', $new_material_info);
                if (!isset($new_material_arr[1]) or !$new_material_arr[1]) {
                    $this->logger->debug(': No mapping found for chapter: ' . $material_obj_id);
                    continue;
                }
                $new_obj_id = $new_material_arr[1];
                $this->logger->debug('New material id is: ' . $new_obj_id);
            } else {
                $new_obj_id = $this->objectDataCache->lookupObjId($mappings[$material_ref_id]);
            }

            $new_material = new ilCourseObjectiveMaterials($a_new_objective);
            $new_material->setLMRefId($new_ref_id);
            $new_material->setLMObjId($new_obj_id);
            $new_material->setType($material['type']);
            $new_material->add();
        }
    }

    public static function _getAssignedMaterials(int $a_objective_id) : array
    {
        global $DIC;

        $ilDB = $DIC->database();
        $query = "SELECT DISTINCT(ref_id) ref_id FROM crs_objective_lm " .
            "WHERE objective_id = " . $ilDB->quote($a_objective_id, 'integer');
        $res = $ilDB->query($query);
        $ref_ids = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $ref_ids[] = (int) $row->ref_id;
        }
        return $ref_ids;
    }

    /**
     * Get an array of course material ids that can be assigned to learning objectives
     * No tst, fold and grp.
     */
    public static function _getAssignableMaterials(int $a_container_id) : array
    {
        global $DIC;

        $tree = $DIC->repositoryTree();
        $container_obj_id = ilObject::_lookupObjId($a_container_id);

        $all_materials = $tree->getSubTree($tree->getNodeData($a_container_id), true);
        $all_materials = ilArrayUtil::sortArray($all_materials, 'title', 'asc');

        // Filter
        $assignable = [];
        foreach ($all_materials as $material) {
            switch ($material['type']) {
                case 'tst':
                    $type = ilLOTestAssignments::getInstance($container_obj_id)->getTypeByTest($material['child']);
                    if ($type != ilLOSettings::TYPE_TEST_UNDEFINED) {
                        break;
                    }

                    $assignable[] = $material;
                    break;

                case 'crs':
                case 'rolf':
                case 'itgr':
                    break;

                default:
                    $assignable[] = $material;
                    break;
            }
        }
        return $assignable;
    }

    public static function _getAllAssignedMaterials(int $a_container_id) : array
    {
        global $DIC;

        $ilDB = $DIC->database();
        $query = "SELECT DISTINCT(com.ref_id) ref_id FROM crs_objectives co " .
            "JOIN crs_objective_lm com ON co.objective_id = com.objective_id " .
            "JOIN object_reference obr ON com.ref_id = obr.ref_id " .
            "JOIN object_data obd ON obr.obj_id = obd.obj_id " .
            "WHERE co.crs_id = " . $ilDB->quote($a_container_id, 'integer') . " " .
            "ORDER BY obd.title ";

        $res = $ilDB->query($query);
        $ref_ids = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $ref_ids[] = (int) $row->ref_id;
        }
        return $ref_ids;
    }

    public function getMaterials() : array
    {
        return $this->lms;
    }

    public function getChapters() : array
    {
        $chapters = [];
        foreach ($this->lms as $lm_data) {
            if ($lm_data['type'] == 'st') {
                $chapters[] = $lm_data;
            }
            if ($lm_data['type'] == 'pg') {
                $chapters[] = $lm_data;
            }
        }
        return $chapters;
    }

    public function getLM(int $lm_id) : array
    {
        if ($this->lms[$lm_id]) {
            return $this->lms[$lm_id];
        } else {
            return [];
        }
    }

    public function getObjectiveId() : int
    {
        return $this->objective_id;
    }

    public function setLMRefId(int $a_ref_id) : void
    {
        $this->lm_ref_id = $a_ref_id;
    }

    public function getLMRefId() : int
    {
        return $this->lm_ref_id;
    }

    public function setLMObjId(int $a_obj_id) : void
    {
        $this->lm_obj_id = $a_obj_id;
    }

    public function getLMObjId() : int
    {
        return $this->lm_obj_id;
    }

    public function setType(string $a_type) : void
    {
        $this->type = $a_type;
    }

    public function getType() : string
    {
        return $this->type;
    }

    /**
     * @param int  $a_ref_id
     * @param bool $a_get_id
     * @return bool|int
     */
    public function isAssigned(int $a_ref_id, bool $a_get_id = false)
    {
        $query = "SELECT * FROM crs_objective_lm " .
            "WHERE ref_id = " . $this->db->quote($a_ref_id, 'integer') . " " .
            "AND objective_id = " . $this->db->quote($this->getObjectiveId(), 'integer') . " " .
            "AND type != 'st' AND type != 'pg' ";
        $res = $this->db->query($query);
        if (!$a_get_id) {
            return (bool) $res->numRows();
        } else {
            $row = $this->db->fetchAssoc($res);
            return (int) $row["lm_ass_id"];
        }
    }

    public function isChapterAssigned(int $a_ref_id, int $a_obj_id) : bool
    {
        $query = "SELECT * FROM crs_objective_lm " .
            "WHERE ref_id = " . $this->db->quote($a_ref_id, 'integer') . " " .
            "AND obj_id = " . $this->db->quote($a_obj_id, 'integer') . " " .
            "AND objective_id = " . $this->db->quote($this->getObjectiveId(), 'integer') . " " .
            "AND (type = 'st' OR type = 'pg')";
        $res = $this->db->query($query);
        return (bool) $res->numRows();
    }

    public function checkExists() : bool
    {
        if ($this->getLMObjId()) {
            $query = "SELECT * FROM crs_objective_lm " .
                "WHERE objective_id = " . $this->db->quote($this->getObjectiveId(), 'integer') . " " .
                "AND ref_id = " . $this->db->quote($this->getLMRefId(), 'integer') . " " .
                "AND obj_id = " . $this->db->quote($this->getLMObjId(), 'integer') . " ";
        } else {
            $query = "SELECT * FROM crs_objective_lm " .
                "WHERE objective_id = " . $this->db->quote($this->getObjectiveId(), 'integer') . " " .
                "AND ref_id = " . $this->db->quote($this->getLMRefId(), 'integer') . " ";
        }

        $res = $this->db->query($query);
        return (bool) $res->numRows();
    }

    public function add() : int
    {
        $next_id = $this->db->nextId('crs_objective_lm');
        $query = "INSERT INTO crs_objective_lm (lm_ass_id,objective_id,ref_id,obj_id,type) " .
            "VALUES( " .
            $this->db->quote($next_id, 'integer') . ", " .
            $this->db->quote($this->getObjectiveId(), 'integer') . ", " .
            $this->db->quote($this->getLMRefId(), 'integer') . ", " .
            $this->db->quote($this->getLMObjId(), 'integer') . ", " .
            $this->db->quote($this->getType(), 'text') .
            ")";
        $res = $this->db->manipulate($query);
        return $next_id;
    }

    public function delete(int $lm_id) : bool
    {
        if (!$lm_id) {
            return false;
        }

        $query = "DELETE FROM crs_objective_lm " .
            "WHERE lm_ass_id = " . $this->db->quote($lm_id, 'integer') . " ";
        $res = $this->db->manipulate($query);
        return true;
    }

    public function deleteAll() : bool
    {
        $query = "DELETE FROM crs_objective_lm " .
            "WHERE objective_id = " . $this->db->quote($this->getObjectiveId(), 'integer') . " ";
        $res = $this->db->manipulate($query);
        return true;
    }

    public function writePosition(int $a_ass_id, int $a_position)
    {
        $query = "UPDATE crs_objective_lm " .
            "SET position = " . $this->db->quote((string) $a_position, 'integer') . " " .
            "WHERE objective_id = " . $this->db->quote($this->getObjectiveId(), 'integer') . " " .
            "AND lm_ass_id = " . $this->db->quote($a_ass_id, "integer");
        $this->db->manipulate($query);
    }

    public function __read()
    {
        $container_ref_ids = ilObject::_getAllReferences(ilCourseObjective::_lookupContainerIdByObjectiveId($this->objective_id));
        $container_ref_id = current($container_ref_ids);

        $this->lms = array();
        $query = "SELECT position,lm_ass_id,lm.ref_id,lm.obj_id,lm.type FROM crs_objective_lm lm " .
            "JOIN object_reference obr ON lm.ref_id = obr.ref_id " .
            "JOIN object_data obd ON obr.obj_id = obd.obj_id " .
            "LEFT JOIN lm_data lmd ON lmd.obj_id = lm.obj_id " .
            "WHERE objective_id = " . $this->db->quote($this->getObjectiveId(), 'integer') . " " .
            "ORDER BY position,obd.title,lmd.title";

        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            if (
                !$this->tree->isInTree((int) $row->ref_id) ||
                !$this->tree->isGrandChild($container_ref_id, (int) $row->ref_id)
            ) {
                $this->delete((int) $row->lm_ass_id);
                continue;
            }
            if (
                $row->obj_id > 0 &&
                ($row->type == 'pg' || $row->type == 'st') &&
                !ilLMObject::_exists((int) $row->obj_id)
            ) {
                continue;
            }
            $lm['ref_id'] = (int) $row->ref_id;
            $lm['obj_id'] = (int) $row->obj_id;
            $lm['type'] = (string) $row->type;
            $lm['lm_ass_id'] = (int) $row->lm_ass_id;
            $lm['position'] = (int) $row->position;
            $this->lms[(int) $row->lm_ass_id] = $lm;
        }
        return true;
    }

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
