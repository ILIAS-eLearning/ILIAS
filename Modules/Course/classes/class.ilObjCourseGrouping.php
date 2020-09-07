<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2005 ILIAS open source, University of Cologne            |
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
* Class ilObj<module_name>
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*/
class ilObjCourseGrouping
{
    public $db;
    
    protected static $assignedObjects = array();

    /**
     * @var null | \ilLogger
     */
    private $logger = null;

    /**
     * Constructor
     * @access	public
     * @param	int	reference_id or object_id
     */
    public function __construct($a_id = 0)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $this->logger = $DIC->logger()->crs();

        $this->setType('crsg');
        $this->db = $ilDB;

        $this->setId($a_id);

        if ($a_id) {
            $this->read();
        }
    }
    public function setId($a_id)
    {
        $this->id = $a_id;
    }
    public function getId()
    {
        return $this->id;
    }

    public function setContainerRefId($a_ref_id)
    {
        $this->ref_id = $a_ref_id;
    }
    public function getContainerRefId()
    {
        return $this->ref_id;
    }
    public function setContainerObjId($a_obj_id)
    {
        $this->obj_id = $a_obj_id;
    }
    public function getContainerObjId()
    {
        return $this->obj_id;
    }
    public function getContainerType()
    {
        return $this->container_type;
    }
    public function setContainerType($a_type)
    {
        $this->container_type = $a_type;
    }

    public function setType($a_type)
    {
        $this->type = $a_type;
    }
    public function getType()
    {
        return $this->type;
    }

    public function setTitle($a_title)
    {
        $this->title = $a_title;
    }
    public function getTitle()
    {
        return $this->title;
    }
    public function setDescription($a_desc)
    {
        $this->description = $a_desc;
    }
    public function getDescription()
    {
        return $this->description;
    }
    public function setUniqueField($a_uni)
    {
        $this->unique_field = $a_uni;
    }
    public function getUniqueField()
    {
        return $this->unique_field;
    }

    public function getCountAssignedItems()
    {
        return count($this->getAssignedItems());
    }

    public function getAssignedItems()
    {
        global $DIC;

        $tree = $DIC['tree'];

        include_once './Services/Conditions/classes/class.ilConditionHandler.php';
        $condition_data = ilConditionHandler::_getPersistedConditionsOfTrigger($this->getType(), $this->getId());
        $conditions = array();
        foreach ($condition_data as $condition) {
            if ($tree->isDeleted($condition['target_ref_id'])) {
                continue;
            }
            $conditions[] = $condition;
        }
        return count($conditions) ? $conditions : array();
    }

    public function delete()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        include_once './Services/Conditions/classes/class.ilConditionHandler.php';

        if ($this->getId() and $this->getType() === 'crsg') {
            $query = "DELETE FROM object_data WHERE obj_id = " . $ilDB->quote($this->getId(), 'integer') . " ";
            $res = $ilDB->manipulate($query);

            $query = "DELETE FROM crs_groupings " .
                "WHERE crs_grp_id = " . $ilDB->quote($this->getId(), 'integer') . " ";
            $res = $ilDB->manipulate($query);

            // Delete conditions
            $condh = new ilConditionHandler();
            $condh->deleteByObjId($this->getId());

            return true;
        }
        return false;
    }

    public function create($a_course_ref_id, $a_course_id)
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        $ilDB = $DIC['ilDB'];

        // INSERT IN object_data
        $this->setId($ilDB->nextId("object_data"));
        $query = "INSERT INTO object_data " .
            "(obj_id, type,title,description,owner,create_date,last_update) " .
            "VALUES " .
            "(" .
            $ilDB->quote($this->getId(), "integer") . "," .
            $ilDB->quote($this->type, "text") . "," .
            $ilDB->quote($this->getTitle(), "text") . "," .
            $ilDB->quote($this->getDescription(), "text") . "," .
            $ilDB->quote($ilUser->getId(), "integer") . "," .
            $ilDB->now() . "," .
            $ilDB->now() .
            ')';
            
        $ilDB->manipulate($query);

        // INSERT in crs_groupings
        $query = "INSERT INTO crs_groupings (crs_grp_id,crs_ref_id,crs_id,unique_field) " .
            "VALUES (" .
            $ilDB->quote($this->getId(), 'integer') . ", " .
            $ilDB->quote($a_course_ref_id, 'integer') . ", " .
            $ilDB->quote($a_course_id, 'integer') . ", " .
            $ilDB->quote($this->getUniqueField(), 'text') . " " .
            ")";
        $res = $ilDB->manipulate($query);
        
        return $this->getId();
    }

    public function update()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        if ($this->getId() and $this->getType() === 'crsg') {
            // UPDATe object_data
            $query = "UPDATE object_data " .
                "SET title = " . $ilDB->quote($this->getTitle(), 'text') . ", " .
                "description = " . $ilDB->quote($this->getDescription(), 'text') . " " .
                "WHERE obj_id = " . $ilDB->quote($this->getId(), 'integer') . " " .
                "AND type = " . $ilDB->quote($this->getType(), 'text') . " ";
            $res = $ilDB->manipulate($query);

            // UPDATE crs_groupings
            $query = "UPDATE crs_groupings " .
                "SET unique_field = " . $ilDB->quote($this->getUniqueField(), 'text') . " " .
                "WHERE crs_grp_id = " . $ilDB->quote($this->getId(), 'integer') . " ";
            $res = $ilDB->manipulate($query);

            // UPDATE conditions
            $query = "UPDATE conditions " .
                "SET value = " . $ilDB->quote($this->getUniqueField(), 'text') . " " .
                "WHERE trigger_obj_id = " . $ilDB->quote($this->getId(), 'integer') . " " .
                "AND trigger_type = 'crsg'";
            $res = $ilDB->manipulate($query);

            return true;
        }
        return false;
    }

    public function isAssigned($a_course_id)
    {
        foreach ($this->getAssignedItems() as $condition_data) {
            if ($a_course_id == $condition_data['target_obj_id']) {
                return true;
            }
        }
        return false;
    }

    public function read()
    {
        global $DIC;

        $ilObjDataCache = $DIC['ilObjDataCache'];
        $ilDB = $DIC['ilDB'];

        $query = "SELECT * FROM object_data " .
            "WHERE obj_id = " . $ilDB->quote($this->getId(), 'integer') . " ";

        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->setTitle($row->title);
            $this->setDescription($row->description);
        }

        $query = "SELECT * FROM crs_groupings " .
            "WHERE crs_grp_id = " . $ilDB->quote($this->getId(), 'integer') . " ";
        $res = $ilDB->query($query);

        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->setUniqueField($row->unique_field);
            $this->setContainerRefId($row->crs_ref_id);
            $this->setContainerObjId($row->crs_id);
            $this->setContainerType($ilObjDataCache->lookupType($this->getContainerObjId()));
        }

        return true;
    }

    public function _checkAccess($grouping_id)
    {
        global $DIC;

        $ilAccess = $DIC['ilAccess'];
        $tree = $DIC['tree'];

        $tmp_grouping_obj = new ilObjCourseGrouping($grouping_id);

        $found_invisible = false;
        foreach ($tmp_grouping_obj->getAssignedItems() as $condition) {
            if (!$ilAccess->checkAccess('write', '', $condition['target_ref_id'])) {
                $found_invisible = true;
                break;
            }
        }
        return $found_invisible ? false : true;
    }


    /**
     * @param int $a_obj_id
     * @return array
     *
     * Returns a list of all groupings for which the current user hast write permission on all assigned objects. Or groupings
     * the given object id is assigned to.
     */
    public static function _getVisibleGroupings($a_obj_id)
    {
        global $DIC;

        $ilObjDataCache = $DIC['ilObjDataCache'];
        $ilAccess = $DIC['ilAccess'];
        $ilDB = $DIC['ilDB'];

        $container_type = $ilObjDataCache->lookupType($a_obj_id) == 'grp' ? 'grp' : 'crs';


        // First get all groupings
        $query = "SELECT * FROM object_data WHERE type = 'crsg' ORDER BY title";
        $res = $ilDB->query($query);
        $groupings = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $groupings[] = $row->obj_id;
        }

        //check access
        foreach ($groupings as $grouping_id) {
            $tmp_grouping_obj = new ilObjCourseGrouping($grouping_id);

            // Check container type
            if ($tmp_grouping_obj->getContainerType() != $container_type) {
                continue;
            }
            // Check if container is current container
            if ($tmp_grouping_obj->getContainerObjId() == $a_obj_id) {
                $visible_groupings[] = $grouping_id;
                continue;
            }
            // check if items are assigned
            if (count($items = $tmp_grouping_obj->getAssignedItems())) {
                foreach ($items as $condition_data) {
                    if ($ilAccess->checkAccess('write', '', $condition_data['target_ref_id'])) {
                        $visible_groupings[] = $grouping_id;
                        break;
                    }
                }
            }
        }
        return $visible_groupings ? $visible_groupings : array();
    }

    public function assign($a_crs_ref_id, $a_course_id)
    {
        // Add the parent course of grouping
        $this->__addCondition($this->getContainerRefId(), $this->getContainerObjId());
        $this->__addCondition($a_crs_ref_id, $a_course_id);

        return true;
    }

    public function deassign($a_crs_ref_id, $a_course_id)
    {
        include_once './Services/Conditions/classes/class.ilConditionHandler.php';


        $condh = new ilConditionHandler();

        // DELETE also original course if its the last
        if ($this->getCountAssignedCourses() == 2) {
            $condh->deleteByObjId($this->getId());

            return true;
        }
        
        foreach (ilConditionHandler::_getPersistedConditionsOfTrigger('crsg', $this->getId()) as $cond_data) {
            if ($cond_data['target_ref_id'] == $a_crs_ref_id and
               $cond_data['target_obj_id'] == $a_course_id) {
                $condh->deleteCondition($cond_data['id']);
            }
        }

        return true;
    }

    /**
     * @param int $a_target_id
     * @param int $a_copy_id
     */
    public function cloneGrouping($a_target_id, $a_copy_id)
    {
        $this->logger->debug('Start cloning membership limitations...');
        $mappings = \ilCopyWizardOptions::_getInstance($a_copy_id)->getMappings();
        $target_ref_id = 0;
        $target_obj_id = 0;

        if (array_key_exists($this->getContainerRefId(), $mappings) && $mappings[$this->getContainerRefId()]) {
            $target_ref_id = $mappings[$this->getContainerRefId()];
            $target_obj_id = \ilObject::_lookupObjId($target_ref_id);
            $this->logger->dump($target_ref_id);
            $this->logger->dump($target_obj_id);
        }
        if (!$target_ref_id || !$target_obj_id) {
            $this->logger->debug('No target ref_id found.');
            return false;
        }

        $new_grouping = new \ilObjCourseGrouping();
        $new_grouping->setTitle($this->getTitle());
        $new_grouping->setDescription($this->getDescription());
        $new_grouping->setContainerRefId($target_ref_id);
        $new_grouping->setContainerObjId($target_obj_id);
        $new_grouping->setContainerType(\ilObject::_lookupType($target_obj_id));
        $new_grouping->setUniqueField($this->getUniqueField());
        $new_grouping->create($target_ref_id, $target_obj_id);

        $obj_instance = \ilObjectFactory::getInstanceByRefId($this->getContainerRefId(), false);
        if (!$obj_instance instanceof \ilObject) {
            $this->logger->info('Cannot create object instance for membership limitation');
            return false;
        }
        $limitation_items = self::_getGroupingItems($obj_instance);
        $this->logger->dump($limitation_items);

        foreach ($limitation_items as $item_ref_id) {
            $target_item_ref_id = 0;
            $target_item_obj_id = 0;
            if (array_key_exists($item_ref_id, $mappings) && $mappings[$item_ref_id]) {
                $target_item_ref_id = $mappings[$item_ref_id];
                $target_item_obj_id = \ilObject::_lookupObjId($target_item_ref_id);
            }
            if (!$target_item_ref_id || !$target_item_obj_id) {
                $this->logger->info('No mapping found for: ' . $item_ref_id);
                continue;
            }
            $new_grouping->assign($target_item_ref_id, $target_item_obj_id);
        }

        return true;
    }


    // PRIVATE
    public function __addCondition($a_target_ref_id, $a_target_obj_id)
    {
        include_once './Services/Conditions/classes/class.ilConditionHandler.php';

        $tmp_condh = new ilConditionHandler();
        $tmp_condh->enableAutomaticValidation(false);

        $tmp_condh->setTargetRefId($a_target_ref_id);
        $tmp_condh->setTargetObjId($a_target_obj_id);
        $tmp_condh->setTargetType(\ilObject::_lookupType($a_target_obj_id));
        $tmp_condh->setTriggerRefId(0);
        $tmp_condh->setTriggerObjId($this->getId());
        $tmp_condh->setTriggerType('crsg');
        $tmp_condh->setOperator('not_member');
        $tmp_condh->setValue($this->getUniqueField());

        if (!$tmp_condh->checkExists()) {
            $tmp_condh->storeCondition();

            return true;
        }
        return false;
    }
    
    // STATIC
    public static function _deleteAll($a_course_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        // DELETE CONDITIONS
        foreach ($groupings = ilObjCourseGrouping::_getGroupings($a_course_id) as $grouping_id) {
            include_once './Services/Conditions/classes/class.ilConditionHandler.php';

            $condh = new ilConditionHandler();
            $condh->deleteByObjId($grouping_id);
        }

        $query = "DELETE FROM crs_groupings " .
            "WHERE crs_id = " . $ilDB->quote($a_course_id, 'integer') . " ";
        $res = $ilDB->manipulate($query);

        return true;
    }

    /**
     * @param $a_course_id
     * @return int[]
     */
    public static function _getGroupings($a_course_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT * FROM crs_groupings " .
            "WHERE crs_id = " . $ilDB->quote($a_course_id, 'integer') . " ";

        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $groupings[] = $row->crs_grp_id;
        }
        return $groupings ? $groupings : array();
    }

    public static function _checkCondition($trigger_obj_id, $operator, $value, $a_usr_id = 0)
    {
        // in the moment i alway return true, there are some problems with presenting the condition if it fails,
        // only course register class check manually if this condition is fullfilled
        return true;
    }


    /**
    * Get all ids of courses that are grouped with another course
    * @access	static
    * @param	integer	 object_id of one course
    * @param	array integer ids of courses or empty array if course is not in grouping
    */
    public static function _getGroupingCourseIds($a_course_ref_id, $a_course_id)
    {
        global $DIC;

        $tree = $DIC['tree'];

        include_once './Services/Conditions/classes/class.ilConditionHandler.php';

        // get all grouping ids the course is assigned to
        foreach (ilConditionHandler::_getPersistedConditionsOfTarget($a_course_ref_id, $a_course_id, 'crs') as $condition) {
            if ($condition['trigger_type'] == 'crsg') {
                foreach (ilConditionHandler::_getPersistedConditionsOfTrigger('crsg', $condition['trigger_obj_id']) as $target_condition) {
                    if ($tree->isDeleted($target_condition['target_ref_id'])) {
                        continue;
                    }
                    $course_ids[] = array('id' => $target_condition['target_obj_id'],
                                          'unique' => $target_condition['value']);
                }
            }
        }
        return $course_ids ? $course_ids : array();
    }
    
    
    /**
     * Alway call checkGroupingDependencies before
     * @return array Assigned objects
     */
    public static function getAssignedObjects()
    {
        return self::$assignedObjects ? self::$assignedObjects : array();
    }

    public static function _checkGroupingDependencies(&$container_obj, $a_user_id = null)
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        $lng = $DIC['lng'];
        $tree = $DIC['tree'];

        include_once './Services/Conditions/classes/class.ilConditionHandler.php';
        
        $user_id = is_null($a_user_id) ? $ilUser->getId() : $a_user_id;
        

        $trigger_ids = array();
        foreach (ilConditionHandler::_getPersistedConditionsOfTarget(
            $container_obj->getRefId(),
            $container_obj->getId(),
            $container_obj->getType()
        ) as $condition) {
            if ($condition['operator'] == 'not_member') {
                $trigger_ids[] = $condition['trigger_obj_id'];
                break;
            }
        }
        if (!count($trigger_ids)) {
            return true;
        }
        $matriculation_message = $assigned_message = '';
        self::$assignedObjects = array();
        foreach ($trigger_ids as $trigger_id) {
            foreach (ilConditionHandler::_getPersistedConditionsOfTrigger('crsg', $trigger_id) as $condition) {
                // Handle deleted items
                if ($tree->isDeleted($condition['target_ref_id'])) {
                    continue;
                }
                if ($condition['operator'] == 'not_member') {
                    switch ($condition['value']) {
                        case 'matriculation':
                            if (!strlen(ilObjUser::lookupMatriculation($user_id))) {
                                if (!$matriculation_message) {
                                    $matriculation_message = $lng->txt('crs_grp_matriculation_required');
                                }
                            }
                    }
                    if ($container_obj->getType() == 'crs') {
                        include_once('Modules/Course/classes/class.ilCourseParticipants.php');
                        $members = ilCourseParticipants::_getInstanceByObjId($condition['target_obj_id']);
                        if ($members->isGroupingMember($user_id, $condition['value'])) {
                            if (!$assigned_message) {
                                self::$assignedObjects[] = $condition['target_obj_id'];
                                $assigned_message = $lng->txt('crs_grp_already_assigned');
                            }
                        }
                    } elseif ($container_obj->getType() == 'grp') {
                        include_once('Modules/Group/classes/class.ilGroupParticipants.php');
                        $members = ilGroupParticipants::_getInstanceByObjId($condition['target_obj_id']);
                        if ($members->isGroupingMember($user_id, $condition['value'])) {
                            if (!$assigned_message) {
                                self::$assignedObjects[] = $condition['target_obj_id'];
                                $assigned_message = $lng->txt('grp_grp_already_assigned');
                            }
                        }
                    } else {
                        if (ilObjGroup::_isMember($user_id, $condition['target_ref_id'], $condition['value'])) {
                            if (!$assigned_message) {
                                self::$assignedObjects[] = $condition['target_obj_id'];
                                $assigned_message = $lng->txt('crs_grp_already_assigned');
                            }
                        }
                    }
                }
            }
        }
        if ($matriculation_message) {
            $container_obj->appendMessage($matriculation_message);
            return false;
        } elseif ($assigned_message) {
            $container_obj->appendMessage($assigned_message);
            return false;
        }
        return true;
    }

    
    /**
     * Get courses/groups that are assigned to the same membership limitation
     *
     * @param object container object
     * @return array array of reference ids
     */
    public static function _getGroupingItems($container_obj)
    {
        global $DIC;

        $tree = $DIC['tree'];
        $ilObjDataCache = $DIC['ilObjDataCache'];
        $ilAccess = $DIC['ilAccess'];
        $tree = $DIC['tree'];

        include_once './Services/Conditions/classes/class.ilConditionHandler.php';

        $trigger_ids = array();
        foreach (ilConditionHandler::_getPersistedConditionsOfTarget(
            $container_obj->getRefId(),
            $container_obj->getId(),
            $container_obj->getType()
        ) as $condition) {
            if ($condition['operator'] == 'not_member') {
                $trigger_ids[] = $condition['trigger_obj_id'];
            }
        }
        if (!count($trigger_ids)) {
            return false;
        }
        $hash_table = array();
        foreach ($trigger_ids as $trigger_id) {
            foreach (ilConditionHandler::_getPersistedConditionsOfTrigger('crsg', $trigger_id) as $condition) {
                // Continue if trigger is deleted
                if ($tree->isDeleted($condition['target_ref_id'])) {
                    continue;
                }

                if ($condition['operator'] == 'not_member') {
                    if (!$hash_table[$condition['target_ref_id']]) {
                        $items[] = $condition['target_ref_id'];
                    }
                    $hash_table[$condition['target_ref_id']] = true;
                }
            }
        }
        return $items ? $items : array();
    }
} // END class.ilObjCourseGrouping
