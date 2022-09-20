<?php

declare(strict_types=0);
/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Class ilObj<module_name>
 * @author  Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 */
class ilObjCourseGrouping
{
    protected static array $assignedObjects = array();

    private int $id = 0;
    private int $ref_id = 0;
    private int $obj_id = 0;
    private string $container_type = '';
    private string $type = '';
    private string $title = '';
    private string $description = '';
    private string $unique_field = '';

    private ilLogger $logger;
    protected ilDBInterface $db;
    protected ilTree $tree;
    protected ilObjUser $user;
    protected ilObjectDataCache $objectDataCache;
    protected ilAccessHandler $access;

    public function __construct(int $a_id = 0)
    {
        global $DIC;

        $this->logger = $DIC->logger()->crs();
        $this->setType('crsg');
        $this->db = $DIC->database();
        $this->tree = $DIC->repositoryTree();
        $this->user = $DIC->user();
        $this->objectDataCache = $DIC['ilObjDataCache'];
        $this->access = $DIC->access();
        $this->setId($a_id);

        if ($a_id) {
            $this->read();
        }
    }

    public function setId(int $a_id): void
    {
        $this->id = $a_id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setContainerRefId(int $a_ref_id): void
    {
        $this->ref_id = $a_ref_id;
    }

    public function getContainerRefId(): int
    {
        return $this->ref_id;
    }

    public function setContainerObjId(int $a_obj_id): void
    {
        $this->obj_id = $a_obj_id;
    }

    public function getContainerObjId(): int
    {
        return $this->obj_id;
    }

    public function getContainerType(): string
    {
        return $this->container_type;
    }

    public function setContainerType(string $a_type): void
    {
        $this->container_type = $a_type;
    }

    public function setType(string $a_type): void
    {
        $this->type = $a_type;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setTitle(string $a_title): void
    {
        $this->title = $a_title;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setDescription(string $a_desc): void
    {
        $this->description = $a_desc;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setUniqueField(string $a_uni): void
    {
        $this->unique_field = $a_uni;
    }

    public function getUniqueField(): string
    {
        return $this->unique_field;
    }

    public function getCountAssignedItems(): int
    {
        return count($this->getAssignedItems());
    }

    public function getAssignedItems(): array
    {
        $condition_data = ilConditionHandler::_getPersistedConditionsOfTrigger($this->getType(), $this->getId());
        $conditions = array();
        foreach ($condition_data as $condition) {
            if ($this->tree->isDeleted($condition['target_ref_id'])) {
                continue;
            }
            $conditions[] = $condition;
        }
        return count($conditions) ? $conditions : array();
    }

    public function delete(): void
    {
        if ($this->getId() && $this->getType() === 'crsg') {
            $query = "DELETE FROM object_data WHERE obj_id = " . $this->db->quote($this->getId(), 'integer') . " ";
            $res = $this->db->manipulate($query);

            $query = "DELETE FROM crs_groupings " .
                "WHERE crs_grp_id = " . $this->db->quote($this->getId(), 'integer') . " ";
            $res = $this->db->manipulate($query);

            // Delete conditions
            $condh = new ilConditionHandler();
            $condh->deleteByObjId($this->getId());
        }
    }

    public function create(int $a_course_ref_id, int $a_course_id): void
    {
        // INSERT IN object_data
        $this->setId($this->db->nextId("object_data"));
        $query = "INSERT INTO object_data " .
            "(obj_id, type,title,description,owner,create_date,last_update) " .
            "VALUES " .
            "(" .
            $this->db->quote($this->getId(), "integer") . "," .
            $this->db->quote($this->type, "text") . "," .
            $this->db->quote($this->getTitle(), "text") . "," .
            $this->db->quote($this->getDescription(), "text") . "," .
            $this->db->quote($this->user->getId(), "integer") . "," .
            $this->db->now() . "," .
            $this->db->now() .
            ')';

        $this->db->manipulate($query);

        // INSERT in crs_groupings
        $query = "INSERT INTO crs_groupings (crs_grp_id,crs_ref_id,crs_id,unique_field) " .
            "VALUES (" .
            $this->db->quote($this->getId(), 'integer') . ", " .
            $this->db->quote($a_course_ref_id, 'integer') . ", " .
            $this->db->quote($a_course_id, 'integer') . ", " .
            $this->db->quote($this->getUniqueField(), 'text') . " " .
            ")";
        $res = $this->db->manipulate($query);
    }

    public function update(): void
    {
        if ($this->getId() && $this->getType() === 'crsg') {
            // UPDATe object_data
            $query = "UPDATE object_data " .
                "SET title = " . $this->db->quote($this->getTitle(), 'text') . ", " .
                "description = " . $this->db->quote($this->getDescription(), 'text') . " " .
                "WHERE obj_id = " . $this->db->quote($this->getId(), 'integer') . " " .
                "AND type = " . $this->db->quote($this->getType(), 'text') . " ";
            $res = $this->db->manipulate($query);

            // UPDATE crs_groupings
            $query = "UPDATE crs_groupings " .
                "SET unique_field = " . $this->db->quote($this->getUniqueField(), 'text') . " " .
                "WHERE crs_grp_id = " . $this->db->quote($this->getId(), 'integer') . " ";
            $res = $this->db->manipulate($query);

            // UPDATE conditions
            $query = "UPDATE conditions " .
                "SET value = " . $this->db->quote($this->getUniqueField(), 'text') . " " .
                "WHERE trigger_obj_id = " . $this->db->quote($this->getId(), 'integer') . " " .
                "AND trigger_type = 'crsg'";
            $res = $this->db->manipulate($query);
        }
    }

    public function isAssigned(int $a_course_id): bool
    {
        foreach ($this->getAssignedItems() as $condition_data) {
            if ($a_course_id == $condition_data['target_obj_id']) {
                return true;
            }
        }
        return false;
    }

    public function read(): void
    {
        $query = "SELECT * FROM object_data " .
            "WHERE obj_id = " . $this->db->quote($this->getId(), 'integer') . " ";

        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->setTitle((string) $row->title);
            $this->setDescription((string) $row->description);
        }

        $query = "SELECT * FROM crs_groupings " .
            "WHERE crs_grp_id = " . $this->db->quote($this->getId(), 'integer') . " ";
        $res = $this->db->query($query);

        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->setUniqueField((string) $row->unique_field);
            $this->setContainerRefId((int) $row->crs_ref_id);
            $this->setContainerObjId((int) $row->crs_id);
            $this->setContainerType($this->objectDataCache->lookupType($this->getContainerObjId()));
        }
    }

    public function _checkAccess(int $grouping_id): bool
    {
        $tmp_grouping_obj = new ilObjCourseGrouping($grouping_id);

        $found_invisible = false;
        foreach ($tmp_grouping_obj->getAssignedItems() as $condition) {
            if (!$this->access->checkAccess('write', '', $condition['target_ref_id'])) {
                $found_invisible = true;
                break;
            }
        }
        return !$found_invisible;
    }

    /**
     * Returns a list of all groupings for which the current user hast write permission on all assigned objects. Or groupings
     * the given object id is assigned to.
     * @return int[]
     */
    public static function _getVisibleGroupings(int $a_obj_id): array
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
            $groupings[] = (int) $row->obj_id;
        }

        //check access
        $visible_groupings = [];
        foreach ($groupings as $grouping_id) {
            $tmp_grouping_obj = new ilObjCourseGrouping($grouping_id);

            // Check container type
            if ($tmp_grouping_obj->getContainerType() != $container_type) {
                continue;
            }
            // Check if container is current container
            if ($tmp_grouping_obj->getContainerObjId() === $a_obj_id) {
                $visible_groupings[] = $grouping_id;
                continue;
            }
            // check if items are assigned
            if (($items = $tmp_grouping_obj->getAssignedItems()) !== []) {
                foreach ($items as $condition_data) {
                    if ($ilAccess->checkAccess('write', '', $condition_data['target_ref_id'])) {
                        $visible_groupings[] = $grouping_id;
                        break;
                    }
                }
            }
        }
        return $visible_groupings;
    }

    public function assign(int $a_crs_ref_id, int $a_course_id): void
    {
        // Add the parent course of grouping
        $this->__addCondition($this->getContainerRefId(), $this->getContainerObjId());
        $this->__addCondition($a_crs_ref_id, $a_course_id);
    }

    public function cloneGrouping(int $a_target_id, int $a_copy_id): void
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
            return;
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
            return;
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
    }

    public function __addCondition(int $a_target_ref_id, int $a_target_obj_id): void
    {
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
        }
    }

    public static function _deleteAll(int $a_course_id): void
    {
        global $DIC;

        $ilDB = $DIC->database();

        // DELETE CONDITIONS
        foreach ($groupings = ilObjCourseGrouping::_getGroupings($a_course_id) as $grouping_id) {
            $condh = new ilConditionHandler();
            $condh->deleteByObjId($grouping_id);
        }

        $query = "DELETE FROM crs_groupings " .
            "WHERE crs_id = " . $ilDB->quote($a_course_id, 'integer') . " ";
        $res = $ilDB->manipulate($query);
    }

    /**
     * @param $a_course_id
     * @return int[]
     */
    public static function _getGroupings(int $a_course_id): array
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT * FROM crs_groupings " .
            "WHERE crs_id = " . $ilDB->quote($a_course_id, 'integer') . " ";

        $res = $ilDB->query($query);
        $groupings = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $groupings[] = (int) $row->crs_grp_id;
        }
        return $groupings;
    }

    public static function _checkCondition(int $trigger_obj_id, string $operator, $value, int $a_usr_id = 0): bool
    {
        // in the moment i alway return true, there are some problems with presenting the condition if it fails,
        // only course register class check manually if this condition is fullfilled
        return true;
    }

    /**
     * Get all ids of courses that are grouped with another course
     */
    public static function _getGroupingCourseIds(int $a_course_ref_id, int $a_course_id): array
    {
        global $DIC;

        $tree = $DIC->repositoryTree();
        // get all grouping ids the course is assigned to
        $course_ids = [];
        foreach (ilConditionHandler::_getPersistedConditionsOfTarget(
            $a_course_ref_id,
            $a_course_id,
            'crs'
        ) as $condition) {
            if ($condition['trigger_type'] == 'crsg') {
                foreach (ilConditionHandler::_getPersistedConditionsOfTrigger(
                    'crsg',
                    $condition['trigger_obj_id']
                ) as $target_condition) {
                    if ($tree->isDeleted($target_condition['target_ref_id'])) {
                        continue;
                    }
                    $course_ids[] = array('id' => $target_condition['target_obj_id'],
                                          'unique' => $target_condition['value']
                    );
                }
            }
        }
        return $course_ids;
    }

    public static function getAssignedObjects(): array
    {
        return self::$assignedObjects;
    }

    public static function _checkGroupingDependencies(ilObject $container_obj, ?int $a_user_id = null): bool
    {
        global $DIC;

        $ilUser = $DIC->user();
        $lng = $DIC->language();
        $tree = $DIC->repositoryTree();

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
        if (count($trigger_ids) === 0) {
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
                        $members = ilCourseParticipants::_getInstanceByObjId($condition['target_obj_id']);
                        if ($members->isGroupingMember($user_id, $condition['value'])) {
                            if (!$assigned_message) {
                                self::$assignedObjects[] = $condition['target_obj_id'];
                                $assigned_message = $lng->txt('crs_grp_already_assigned');
                            }
                        }
                    } elseif ($container_obj->getType() == 'grp') {
                        $members = ilGroupParticipants::_getInstanceByObjId($condition['target_obj_id']);
                        if ($members->isGroupingMember($user_id, $condition['value'])) {
                            if (!$assigned_message) {
                                self::$assignedObjects[] = $condition['target_obj_id'];
                                $assigned_message = $lng->txt('grp_grp_already_assigned');
                            }
                        }
                    } elseif (ilObjGroup::_isMember($user_id, $condition['target_ref_id'], $condition['value'])) {
                        if (!$assigned_message) {
                            self::$assignedObjects[] = $condition['target_obj_id'];
                            $assigned_message = $lng->txt('crs_grp_already_assigned');
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
     */
    public static function _getGroupingItems(ilObject $container_obj): array
    {
        global $DIC;

        $tree = $DIC->repositoryTree();
        $ilObjDataCache = $DIC['ilObjDataCache'];
        $ilAccess = $DIC->access();
        $tree = $DIC->repositoryTree();

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
        if ($trigger_ids === []) {
            return [];
        }
        $hash_table = array();
        $items = [];
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
        return $items;
    }
} // END class.ilObjCourseGrouping
