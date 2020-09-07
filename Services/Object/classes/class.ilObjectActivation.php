<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilObjectActivation
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id: class.ilCourseItems.php 30321 2011-08-22 12:05:03Z jluetzen $
*
* @extends Object
*/
class ilObjectActivation
{
    const ERR_SUG_START_END = 1;


    /**
     * @var ilErrorHandling
     */
    protected $error;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilDB
     */
    protected $db;

    protected $timing_type;
    protected $timing_start;
    protected $timing_end;
    protected $suggestion_start;
    protected $suggestion_end;
    protected $visible;
    protected $changeable;
    
    protected $suggestion_start_rel;
    protected $suggestion_end_rel;

    protected static $preloaded_data = array();
    
    const TIMINGS_ACTIVATION = 0;
    const TIMINGS_DEACTIVATED = 1;
    const TIMINGS_PRESETTING = 2;
    const TIMINGS_FIXED = 3; // session only => obsolete?
    
    public function __construct()
    {
        global $DIC;

        $this->error = $DIC["ilErr"];
        $this->lng = $DIC->language();
        $this->db = $DIC->database();
    }
    
    /**
     * Set timing type
     *
     * @see class constants
     * @param int $a_type
     */
    public function setTimingType($a_type)
    {
        $this->timing_type = $a_type;
    }
    
    /**
     * get timing type
     *
     * @see class constants
     * @return int
     */
    public function getTimingType()
    {
        return $this->timing_type;
    }
    
    /**
     * Set timing start
     *
     * @param timestamp $a_start
     */
    public function setTimingStart($a_start)
    {
        $this->timing_start = $a_start;
    }
    
    /**
     * Get timing start
     *
     * @return timestamp
     */
    public function getTimingStart()
    {
        return $this->timing_start;
    }
    
    /**
     * Set timing end
     *
     * @param timestamp $a_end
     */
    public function setTimingEnd($a_end)
    {
        $this->timing_end = $a_end;
    }
    
    /**
     * Get timing end
     *
     * @return timestamp
     */
    public function getTimingEnd()
    {
        return $this->timing_end;
    }
    
    /**
     * Set suggestion start
     *
     * @param timestamp $a_start
     */
    public function setSuggestionStart($a_start)
    {
        $this->suggestion_start = $a_start;
    }
    
    /**
     * Get suggestion start
     *
     * @return timestamp
     */
    public function getSuggestionStart()
    {
        return $this->suggestion_start;
    }
    
    public function getSuggestionStartRelative()
    {
        return $this->suggestion_start_rel;
    }
    
    public function setSuggestionStartRelative($a_start)
    {
        $this->suggestion_start_rel = $a_start;
    }
    
    public function getSuggestionEndRelative()
    {
        return $this->suggestion_end_rel;
    }
    
    public function setSuggestionEndRelative($a_end)
    {
        $this->suggestion_end_rel = $a_end;
    }
    
    public function getEaliestStartRelative()
    {
        return $this->earliest_start_rel;
    }
    
    public function setEarliestStartRelative($a_start)
    {
        $this->earliest_start_rel = $a_start;
    }
    
    /**
     * Set suggestion end
     *
     * @param int $a_end
     */
    public function setSuggestionEnd($a_end)
    {
        $this->suggestion_end = $a_end;
    }
    
    /**
     * Get suggestion end
     *
     * @return int
     */
    public function getSuggestionEnd()
    {
        return $this->suggestion_end;
    }
    
    /**
     * Set earliest start
     *
     * @param int $a_start
     */
    public function setEarliestStart($a_start)
    {
        $this->earliest_start = $a_start;
    }
    
    /**
     * Get earliest start
     *
     * @return int
     */
    public function getEarliestStart()
    {
        return $this->earliest_start;
    }
    

    /**
     * Set visible status
     *
     * @param bool $a_status
     */
    public function toggleVisible($a_status)
    {
        $this->visible = (int) $a_status;
    }
    
    /**
     * Get visible status
     *
     * @return bool
     */
    public function enabledVisible()
    {
        return (bool) $this->visible;
    }
    
    /**
     * Set changeable status
     *
     * @param bool $a_status
     */
    public function toggleChangeable($a_status)
    {
        $this->changeable = (int) $a_status;
    }
    
    /**
     * Get changeable status
     *
     * @return bool
     */
    public function enabledChangeable()
    {
        return (bool) $this->changeable;
    }
        
    /**
     * Validate current properties
     *
     * @return boolean
     */
    public function validateActivation()
    {
        $ilErr = $this->error;
        $lng = $this->lng;
        
        $ilErr->setMessage('');

        if ($this->getTimingType() == self::TIMINGS_ACTIVATION) {
            if ($this->getTimingStart() > $this->getTimingEnd()) {
                $ilErr->appendMessage($lng->txt("crs_activation_start_invalid"));
            }
        } elseif ($this->getTimingType() == self::TIMINGS_PRESETTING) {
            if ($this->getSuggestionStart() > $this->getSuggestionEnd()) {
                $ilErr->appendMessage($lng->txt('crs_timing_err_sug_start_end'));
            }
        }
    
        if ($ilErr->getMessage()) {
            return false;
        }
        return true;
    }

    /**
     * @return array
     */
    public function validateRelativePlaning()
    {
        $errors = array();
        
        if ($this->getSuggestionStartRelative() >= $this->getSuggestionEndRelative()) {
            $errors[] = self::ERR_SUG_START_END;
        } elseif ($this->getSuggestionStartRelative() < 0) {
            $errors[] = self::ERR_SUG_START_END;
        }
        return $errors;
    }

    /**
     * Update db entry
     *
     * @param int $a_ref_id
     * @param int $a_parent_id
     */
    public function update($a_ref_id, $a_parent_id = null)
    {
        $ilDB = $this->db;
        
        // #10110
        $query = "UPDATE crs_items SET " .
            "timing_type = " . $ilDB->quote($this->getTimingType(), 'integer') . ", " .
            "timing_start = " . $ilDB->quote((int) $this->getTimingStart(), 'integer') . ", " .
            "timing_end = " . $ilDB->quote((int) $this->getTimingEnd(), 'integer') . ", " .
            "suggestion_start = " . $ilDB->quote((int) $this->getSuggestionStart(), 'integer') . ", " .
            "suggestion_end = " . $ilDB->quote((int) $this->getSuggestionEnd(), 'integer') . ", " .
            "changeable = " . $ilDB->quote($this->enabledChangeable(), 'integer') . ", " .
            'suggestion_start_rel = ' . $ilDB->quote($this->getSuggestionStartRelative(), 'integer') . ', ' .
            'suggestion_end_rel = ' . $ilDB->quote($this->getSuggestionEndRelative(), 'integer') . ', ';

        if ($a_parent_id) {
            $query .= "parent_id = " . $ilDB->quote($a_parent_id, 'integer') . ", ";
        }
        
        $query .= "visible = " . $ilDB->quote($this->enabledVisible(), 'integer') . " " .
            "WHERE obj_id = " . $ilDB->quote($a_ref_id, 'integer');
        $ilDB->manipulate($query);
        
        unset(self::$preloaded_data[$a_ref_id]);
    
        return true;
    }

    /**
     * Preload data to internal cache
     *
     * @param array $a_ref_ids
     */
    public static function preloadData(array $a_ref_ids)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $sql = "SELECT * FROM crs_items" .
            " WHERE " . $ilDB->in("obj_id", $a_ref_ids, "", "integer");
        $set = $ilDB->query($sql);
        while ($row = $ilDB->fetchAssoc($set)) {
            self::$preloaded_data[$row["obj_id"]] = $row;
        }
    }
        
    /**
     * Get item data
     *
     * @param int $a_ref_id
     * @return array
     */
    public static function getItem($a_ref_id)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        if (isset(self::$preloaded_data[$a_ref_id])) {
            return self::$preloaded_data[$a_ref_id];
        }
        
        $sql = "SELECT * FROM crs_items" .
            " WHERE obj_id = " . $ilDB->quote($a_ref_id, "integer");
        $set = $ilDB->query($sql);
        $row = $ilDB->fetchAssoc($set);
    
        if (!isset($row["obj_id"])) {
            $row = self::createDefaultEntry($a_ref_id);
        }
        if ($row["obj_id"]) {
            self::$preloaded_data[$row["obj_id"]] = $row;
        }
        return $row;
    }

    /**
     * Parse item data for list entries
     *
     * @param array &$a_item
     */
    public static function addAdditionalSubItemInformation(array &$a_item)
    {
        global $DIC;

        $ilUser = $DIC->user();
        
        $item = self::getItem($a_item['ref_id']);
        
        $a_item['obj_id'] = ($a_item['obj_id'] > 0)
            ? $a_item['obj_id']
            : ilObject::_lookupObjId($a_item['ref_id']);
        $a_item['type'] = ($a_item['type'] != '')
            ? $a_item['type']
            : ilObject::_lookupType($a_item['obj_id']);
        
        $a_item['timing_type'] = $item['timing_type'];
        
        if ($item['changeable'] &&
            $item['timing_type'] == self::TIMINGS_PRESETTING) {
            // cognos-blu-patch: begin
            include_once './Modules/Course/classes/Timings/class.ilTimingUser.php';
            $user_data = new ilTimingUser($a_item['ref_id'], $ilUser->getId());
            if ($user_data->isScheduled()) {
                $a_item['start'] = $user_data->getStart()->get(IL_CAL_UNIX);
                $a_item['end'] = $user_data->getEnd()->get(IL_CAL_UNIX);
                $a_item['activation_info'] = 'crs_timings_planed_info';
            } else {
                $a_item['start'] = $item['suggestion_start'];
                $a_item['end'] = $item['suggestion_end'];
                $a_item['activation_info'] = 'crs_timings_suggested_info';
            }
            // cognos-blu-patch: end
        } elseif ($item['timing_type'] == self::TIMINGS_PRESETTING) {
            $a_item['start'] = $item['suggestion_start'];
            $a_item['end'] = $item['suggestion_end'];
            $a_item['activation_info'] = 'crs_timings_suggested_info';
        } elseif ($item['timing_type'] == self::TIMINGS_ACTIVATION) {
            $a_item['start'] = $item['timing_start'];
            $a_item['end'] = $item['timing_end'];
            $a_item['activation_info'] = 'obj_activation_list_gui';
        } else {
            $a_item['start'] = 'abc';
        }
        
        // #7359 - session sorting should always base on appointment date
        if ($a_item['type'] == 'sess') {
            include_once('./Modules/Session/classes/class.ilSessionAppointment.php');
            $info = ilSessionAppointment::_lookupAppointment($a_item['obj_id']);

            // #11987
            $a_item['masked_start'] = $a_item['start'];
            $a_item['masked_end'] = $a_item['end'];
            $a_item['start'] = $info['start'];
            $a_item['end'] = $info['end'];
        }
    }
    
    /**
     * Get timing details for list gui
     *
     * @param ilObjectListGUI $a_list_gui
     * @param array &$a_item
     * @return array caption, value
     */
    public static function addListGUIActivationProperty(ilObjectListGUI $a_list_gui, array &$a_item)
    {
        global $DIC;

        $lng = $DIC->language();
        
        self::addAdditionalSubItemInformation($a_item);
        if (isset($a_item['timing_type'])) {
            if (!isset($a_item['masked_start'])) {
                $start = $a_item['start'];
                $end = $a_item['end'];
            } else {
                $start = $a_item['masked_start'];
                $end = $a_item['masked_end'];
            }
            $activation = '';
            switch ($a_item['timing_type']) {
                case ilObjectActivation::TIMINGS_ACTIVATION:
                    $activation = ilDatePresentation::formatPeriod(
                        new ilDateTime($start, IL_CAL_UNIX),
                        new ilDateTime($end, IL_CAL_UNIX)
                    );
                    break;
                        
                case ilObjectActivation::TIMINGS_PRESETTING:
                    $activation = ilDatePresentation::formatPeriod(
                        new ilDate($start, IL_CAL_UNIX),
                        new ilDate($end, IL_CAL_UNIX)
                    );
                    break;
            }
            if ($activation != "") {
                global $DIC;

                $lng = $DIC->language();
                $lng->loadLanguageModule('crs');
                
                $a_list_gui->addCustomProperty(
                    $lng->txt($a_item['activation_info']),
                    $activation,
                    false,
                    true
                );
            }
        }
    }
        
    /**
     * Create db entry with default values
     *
     * @param int $a_ref_id
     * @return array
     */
    protected static function createDefaultEntry($a_ref_id)
    {
        global $DIC;

        $ilDB = $DIC->database();
        $tree = $DIC->repositoryTree();
        
        $parent_id = $tree->getParentId($a_ref_id);
        if (!$parent_id) {
            return;
        }
        

        $ilAtomQuery = $ilDB->buildAtomQuery();
        $ilAtomQuery->addTableLock("crs_items");

        $ilAtomQuery->addQueryCallable(function (ilDBInterface $ilDB) use ($a_ref_id, $parent_id, &$a_item) {
            $sql = "SELECT * FROM crs_items" .
                " WHERE obj_id = " . $ilDB->quote($a_ref_id, "integer");
            $set = $ilDB->query($sql);
            if (!$ilDB->numRows($set)) {
                $now = time();
                $now_parts = getdate($now);

                $a_item = array();
                $a_item["timing_type"] = self::TIMINGS_DEACTIVATED;
                $a_item["timing_start"] = $now;
                $a_item["timing_end"] = $now;
                $a_item["suggestion_start"] = $now;
                $a_item["suggestion_end"] = $now;
                $a_item['visible'] = 0;
                $a_item['changeable'] = 0;
                
                $query = "INSERT INTO crs_items (parent_id,obj_id,timing_type,timing_start,timing_end," .
                    "suggestion_start,suggestion_end, " .
                    "changeable,visible,suggestion_start_rel, suggestion_end_rel, position) " .
                    "VALUES( " .
                    $ilDB->quote($parent_id, 'integer') . "," .
                    $ilDB->quote($a_ref_id, 'integer') . "," .
                    $ilDB->quote($a_item["timing_type"], 'integer') . "," .
                    $ilDB->quote($a_item["timing_start"], 'integer') . "," .
                    $ilDB->quote($a_item["timing_end"], 'integer') . "," .
                    $ilDB->quote($a_item["suggestion_start"], 'integer') . "," .
                    $ilDB->quote($a_item["suggestion_end"], 'integer') . "," .
                    $ilDB->quote($a_item["changeable"], 'integer') . "," .
                    $ilDB->quote($a_item["visible"], 'integer') . ", " .
                    $ilDB->quote($a_item["suggestion_start_rel"], 'integer') . "," .
                    $ilDB->quote($a_item['suggestion_end_rel'], 'integer') . ", " .
                    $ilDB->quote(0, 'integer') . ")";
                $ilDB->manipulate($query);
            }
        });
        
        $ilAtomQuery->run();

        // #9982 - to make getItem()-cache work
        $a_item["obj_id"] = $a_ref_id;
        $a_item["parent_id"] = $parent_id;
    
        return $a_item;
    }
        
    /**
     * Delete all db entries for ref id
     *
     * @param int $a_ref_id
     */
    public static function deleteAllEntries($a_ref_id)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        if (!$a_ref_id) {
            return;
        }
        
        $query = "DELETE FROM crs_items " .
            "WHERE obj_id = " . $ilDB->quote($a_ref_id, 'integer');
        $ilDB->manipulate($query);
        
        $query = "DELETE FROM crs_items " .
            "WHERE parent_id = " . $ilDB->quote($a_ref_id, 'integer');
        $ilDB->manipulate($query);

        return true;
    }
    
    /**
     * Clone dependencies
     *
     * @param int $a_ref_id
     * @param int $a_target_id
     * @param int $a_copy_id
     */
    public static function cloneDependencies($a_ref_id, $a_target_id, $a_copy_id)
    {
        global $DIC;

        $ilLog = $DIC["ilLog"];
        
        $ilLog->write(__METHOD__ . ': Begin course items...' . $a_ref_id);
                
        $items = self::getItems($a_ref_id, false);
        if (!$items) {
            $ilLog->write(__METHOD__ . ': No course items found.');
            return true;
        }
        
        // new course item object
        if (!is_object($new_container = ilObjectFactory::getInstanceByRefId($a_target_id, false))) {
            $ilLog->write(__METHOD__ . ': Cannot create target object.');
            return false;
        }
        
        include_once('Services/CopyWizard/classes/class.ilCopyWizardOptions.php');
        $cp_options = ilCopyWizardOptions::_getInstance($a_copy_id);
        $mappings = $cp_options->getMappings();
             
        foreach ($items as $item) {
            if (!isset($mappings[$item['parent_id']]) or !$mappings[$item['parent_id']]) {
                $ilLog->write(__METHOD__ . ': No mapping for parent nr. ' . $item['parent_id']);
                continue;
            }
            if (!isset($mappings[$item['obj_id']]) or !$mappings[$item['obj_id']]) {
                $ilLog->write(__METHOD__ . ': No mapping for item nr. ' . $item['obj_id']);
                continue;
            }
            $new_item_id = $mappings[$item['obj_id']];
            $new_parent = $mappings[$item['parent_id']];
            
            $new_item = new self();
            $new_item->setTimingType($item['timing_type']);
            $new_item->setTimingStart($item['timing_start']);
            $new_item->setTimingEnd($item['timing_end']);
            $new_item->setSuggestionStart($item['suggestion_start']);
            $new_item->setSuggestionEnd($item['suggestion_end']);
            $new_item->toggleChangeable($item['changeable']);
            $new_item->toggleVisible($item['visible']);
            $new_item->update($new_item_id, $new_parent);
            $new_item->setSuggestionStartRelative($item['suggestion_start_rel']);
            $new_item->setSuggestionEndRelative($item['suggestion_end_rel']);
            $new_item->createDefaultEntry($new_item_id);
            $new_item->update($new_item_id);
        }
    }
    
    
    //
    // TIMINGS VIEW RELATED (COURSE ONLY)
    //
    
    /**
     * Check if there is any active timing (in subtree)
     *
     * @param int ref_id
     * @return bool
     */
    public static function hasTimings($a_ref_id)
    {
        global $DIC;

        $tree = $DIC->repositoryTree();
        $ilDB = $DIC->database();

        $subtree = $tree->getSubTree($tree->getNodeData($a_ref_id));
        $ref_ids = array();
        foreach ($subtree as $node) {
            $ref_ids[] = $node['ref_id'];
        }

        $query = "SELECT * FROM crs_items " .
            "WHERE timing_type = " . $ilDB->quote(self::TIMINGS_PRESETTING, 'integer') . " " .
            "AND " . $ilDB->in('obj_id', $ref_ids, false, 'integer');
        $res = $ilDB->query($query);
        return $res->numRows() ? true :false;
    }

    /**
     * Check if there is any active changeable timing (in subtree)
     *
     * @param int ref_id
     * @return bool
     */
    public static function hasChangeableTimings($a_ref_id)
    {
        global $DIC;

        $tree = $DIC->repositoryTree();
        $ilDB = $DIC->database();

        $subtree = $tree->getSubTree($tree->getNodeData($a_ref_id));
        $ref_ids = array();
        foreach ($subtree as $node) {
            $ref_ids[] = $node['ref_id'];
        }

        $query = "SELECT * FROM crs_items " .
            "WHERE timing_type = " . $ilDB->quote(self::TIMINGS_PRESETTING, 'integer') . " " .
            "AND changeable = " . $ilDB->quote(1, 'integer') . " " .
            "AND " . $ilDB->in('obj_id', $ref_ids, false, 'integer');
        $res = $ilDB->query($query);
        return $res->numRows() ? true : false;
    }
    
    /**
     * Validate ref ids and add list data
     *
     * @param array $a_ref_ids
     * @return array
     */
    protected static function processListItems(array $a_ref_ids)
    {
        global $DIC;

        $tree = $DIC->repositoryTree();
        
        $res = array();
        
        foreach ($a_ref_ids as $item_ref_id) {
            if ($tree->isDeleted($item_ref_id)) {
                continue;
            }
            // #7571: when node is removed from system, e.g. inactive trashcan, an empty array is returned
            $node = $tree->getNodeData($item_ref_id);
            if ($node["ref_id"] != $item_ref_id) {
                continue;
            }
            $res[$item_ref_id] = $node;
        }
                    
        if (sizeof($res)) {
            self::preloadData(array_keys($res));
            foreach ($res as $idx => $item) {
                self::addAdditionalSubItemInformation($item);
                $res[$idx] = $item;
            }
        }
        
        return array_values($res);
    }

    /**
     * Get session material / event items
     *
     * @param int $a_event_id (object id)
     * @return array
     */
    public static function getItemsByEvent($a_event_id)
    {
        include_once 'Modules/Session/classes/class.ilEventItems.php';
        $event_items = new ilEventItems($a_event_id);
        return self::processListItems($event_items->getItems());
    }
        
    /**
     * Get materials of item group
     *
     * @param int $a_item_group_id (object id)
     * @return array
     */
    public static function getItemsByItemGroup($a_item_group_ref_id)
    {
        include_once 'Modules/ItemGroup/classes/class.ilItemGroupItems.php';
        $ig_items = new ilItemGroupItems($a_item_group_ref_id);
        $items = $ig_items->getValidItems();
        return self::processListItems($items);
    }
        
    /**
     * Get objective items
     *
     * @param int $a_objective_id
     * @return array
     */
    public static function getItemsByObjective($a_objective_id)
    {
        include_once('./Modules/Course/classes/class.ilCourseObjectiveMaterials.php');
        $item_ids = ilCourseObjectiveMaterials::_getAssignedMaterials($a_objective_id);
        return self::processListItems($item_ids);
    }
    
    /**
     * Get sub item data
     *
     * @param int $a_parent_id
     * @param bool $a_with_list_data
     * @return array
     */
    public static function getItems($a_parent_id, $a_with_list_data = true)
    {
        global $DIC;

        $tree = $DIC->repositoryTree();
        
        $items = array();
        
        $ref_ids = array();
        foreach ($tree->getChilds($a_parent_id) as $item) {
            if ($item['type'] != 'rolf') {
                $items[] = $item;
                $ref_ids[] = $item['ref_id'];
            }
        }
        
        if ($ref_ids) {
            self::preloadData($ref_ids);
            
            foreach ($items as $idx => $item) {
                if (!$a_with_list_data) {
                    $items[$idx] = array_merge($item, self::getItem($item['ref_id']));
                } else {
                    self::addAdditionalSubItemInformation($item);
                    $items[$idx] = $item;
                }
            }
        }
        return $items;
    }
    
    /**
     * Get (sub) item data for timings administration view (active/inactive)
     *
     * @param int $a_parent_id
     * @return array
     */
    public static function getTimingsAdministrationItems($a_parent_id)
    {
        $items = self::getItems($a_parent_id, false);
        
        if ($items) {
            $active = $inactive = array();
            foreach ($items as $item) {
                // active should be first in order
                if ($item['timing_type'] == self::TIMINGS_DEACTIVATED) {
                    $inactive[] = $item;
                } else {
                    $active[] = $item;
                }
            }
            
            $active = ilUtil::sortArray($active, 'start', 'asc');
            $inactive = ilUtil::sortArray($inactive, 'title', 'asc');
            $items = array_merge($active, $inactive);
        }
        
        return $items;
    }
    
    /**
     * Get (sub) item data for timings view (no session material, no side blocks)
     *
     * @param int $a_container_ref_id
     * @return array
     */
    public static function getTimingsItems($a_container_ref_id)
    {
        global $DIC;

        $objDefinition = $DIC["objDefinition"];
        
        $filtered = array();
        
        include_once 'Modules/Session/classes/class.ilEventItems.php';
        $event_items = ilEventItems::_getItemsOfContainer($a_container_ref_id);
        foreach (self::getTimingsAdministrationItems($a_container_ref_id) as $item) {
            if (!in_array($item['ref_id'], $event_items) &&
                !$objDefinition->isSideBlock($item['type'])) {
                $filtered[] = $item;
            }
        }
        
        return $filtered;
    }

    /**
     * @param $a_ref_id
     * @param int $a_parent_id
     * @throws ilDatabaseException
     */
    public function read($a_ref_id, $a_parent_id = 0)
    {
        global $DIC;
        $ilDB = $DIC->database();
        
        $query = 'SELECT * FROM crs_items ' .
                'WHERE obj_id = ' . $ilDB->quote($a_ref_id, 'integer') . ' ';
        
        if ($a_parent_id) {
            $query .= ('AND parent_id = ' . $ilDB->quote($a_parent_id, 'integer') . ' ');
        }
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->setSuggestionStart($row->suggestion_start);
            $this->setSuggestionEnd($row->suggestion_end);
            $this->setSuggestionStartRelative($row->suggestion_start_rel);
            $this->setSuggestionEndRelative($row->suggestion_end_rel);
            $this->toggleVisible($row->visible);
            $this->toggleChangeable($row->changeable);
            $this->setTimingType($row->timing_type);
            $this->setTimingStart($row->timing_start);
            $this->setTimingEnd($row->timing_end);
        }
    }
}
