<?php declare(strict_types=1);

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
 * Class ilObjectActivation
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id: class.ilCourseItems.php 30321 2011-08-22 12:05:03Z jluetzen $
 */
class ilObjectActivation
{
    const ERR_SUG_START_END = 1;

    const TIMINGS_ACTIVATION = 0;
    const TIMINGS_DEACTIVATED = 1;
    const TIMINGS_PRESETTING = 2;
    const TIMINGS_FIXED = 3; // session only => obsolete?

    protected static array $preloaded_data = array();

    protected ilErrorHandling $error;
    protected ilLanguage $lng;
    protected ilDBInterface $db;

    protected int $timing_type = 0;
    protected ?int $timing_start = null;
    protected ?int $timing_end = null;
    protected int $suggestion_start = 0;
    protected int $suggestion_end = 0;
    protected int $visible = 0;
    protected int $changeable = 0;
    protected int $earliest_start_rel = 0;
    protected int $earliest_start = 0;

    protected int $suggestion_start_rel = 0;
    protected int $suggestion_end_rel = 0;

    public function __construct()
    {
        global $DIC;

        $this->error = $DIC["ilErr"];
        $this->lng = $DIC->language();
        $this->db = $DIC->database();
    }

    public function setTimingType(int $type) : void
    {
        $this->timing_type = $type;
    }

    public function getTimingType() : int
    {
        return $this->timing_type;
    }

    public function setTimingStart(?int $start) : void
    {
        $this->timing_start = $start;
    }

    public function getTimingStart() : ?int
    {
        return $this->timing_start;
    }

    public function setTimingEnd(?int $end) : void
    {
        $this->timing_end = $end;
    }

    public function getTimingEnd() : ?int
    {
        return $this->timing_end;
    }

    public function setSuggestionStart(int $start) : void
    {
        $this->suggestion_start = $start;
    }

    public function getSuggestionStart() : int
    {
        return $this->suggestion_start;
    }

    public function getSuggestionStartRelative() : int
    {
        return $this->suggestion_start_rel;
    }

    public function setSuggestionStartRelative(int $start) : void
    {
        $this->suggestion_start_rel = $start;
    }

    public function getSuggestionEndRelative() : int
    {
        return $this->suggestion_end_rel;
    }

    public function setSuggestionEndRelative(int $end) : void
    {
        $this->suggestion_end_rel = $end;
    }

    public function getEaliestStartRelative() : int
    {
        return $this->earliest_start_rel;
    }

    public function setEarliestStartRelative(int $start) : void
    {
        $this->earliest_start_rel = $start;
    }

    public function setSuggestionEnd(int $end) : void
    {
        $this->suggestion_end = $end;
    }

    public function getSuggestionEnd() : int
    {
        return $this->suggestion_end;
    }

    public function setEarliestStart(int $start) : void
    {
        $this->earliest_start = $start;
    }

    public function getEarliestStart() : int
    {
        return $this->earliest_start;
    }


    public function toggleVisible(bool $status) : void
    {
        $this->visible = (int) $status;
    }

    public function enabledVisible() : bool
    {
        return (bool) $this->visible;
    }

    public function toggleChangeable(bool $status) : void
    {
        $this->changeable = (int) $status;
    }

    public function enabledChangeable() : bool
    {
        return (bool) $this->changeable;
    }

    // Validate current properties
    public function validateActivation() : bool
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

    // TODO: found no usages, can this be removed in the next iteration?
    public function validateRelativePlaning() : array
    {
        $errors = array();

        if ($this->getSuggestionStartRelative() >= $this->getSuggestionEndRelative()) {
            $errors[] = self::ERR_SUG_START_END;
        } elseif ($this->getSuggestionStartRelative() < 0) {
            $errors[] = self::ERR_SUG_START_END;
        }
        return $errors;
    }

    public function update(int $ref_id, ?int $parent_id = null) : bool
    {
        $db = $this->db;

        // #10110
        $values = [
            "timing_type" => ["integer", $this->getTimingType()],
            "timing_start" => ["integer", $this->getTimingStart() ?? 0],
            "timing_end" => ["integer", $this->getTimingEnd() ?? 0],
            "suggestion_start" => ["integer", $this->getSuggestionStart()],
            "suggestion_end" => ["integer", $this->getSuggestionEnd()],
            "changeable" => ["integer", $this->enabledChangeable()],
            "suggestion_start_rel" => ["integer", $this->getSuggestionStartRelative()],
            "suggestion_end_rel" => ["integer", $this->getSuggestionEndRelative()],
            "visible" => ["integer", $this->enabledVisible()]
        ];

        if (!is_null($parent_id)) {
            $values["parent_id"] = ["integer", $parent_id];
        }

        $where = [
            "obj_id" => ["integer", $ref_id]
        ];

        $db->update("crs_items", $values, $where);

        unset(self::$preloaded_data[$ref_id]);

        return true;
    }

    /**
     * Preload data to internal cache
     */
    public static function preloadData(array $ref_ids) : void
    {
        global $DIC;
        $db = $DIC->database();

        $sql =
            "SELECT parent_id, obj_id, timing_type, timing_start, timing_end, suggestion_start," . PHP_EOL
            . "suggestion_end, changeable, visible, position, suggestion_start_rel, suggestion_end_rel" . PHP_EOL
            . "FROM crs_items" . PHP_EOL
            . "WHERE " . $db->in("obj_id", $ref_ids, false, "integer") . PHP_EOL
        ;
        $set = $db->query($sql);
        while ($row = $db->fetchAssoc($set)) {
            self::$preloaded_data[$row["obj_id"]] = $row;
        }
    }

    public static function getItem(int $ref_id) : array
    {
        global $DIC;

        $db = $DIC->database();

        if (isset(self::$preloaded_data[$ref_id])) {
            return self::$preloaded_data[$ref_id];
        }

        $sql =
            "SELECT parent_id, obj_id, timing_type, timing_start, timing_end, suggestion_start," . PHP_EOL
            . "suggestion_end, changeable, visible, position, suggestion_start_rel, suggestion_end_rel" . PHP_EOL
            . "FROM crs_items" . PHP_EOL
            . "WHERE obj_id = " . $db->quote($ref_id, "integer") . PHP_EOL
        ;
        $set = $db->query($sql);
        $row = $db->fetchAssoc($set);

        if (!isset($row["obj_id"])) {
            $row = self::createDefaultEntry($ref_id);
        } else {
            self::$preloaded_data[$row["obj_id"]] = $row;
        }
        return $row;
    }

    /**
     * Parse item data for list entries
     */
    public static function addAdditionalSubItemInformation(array &$item) : void
    {
        global $DIC;
        $ilUser = $DIC->user();

        $item_array = self::getItem((int) $item['ref_id']);

        $item['obj_id'] = ($item['obj_id'] > 0)
            ? $item['obj_id']
            : ilObject::_lookupObjId((int) $item['ref_id']);
        $item['type'] = ($item['type'] != '')
            ? $item['type']
            : ilObject::_lookupType((int) $item['obj_id']);

        $item['timing_type'] = $item_array['timing_type'] ?? 0;

        if (($item_array['changeable'] ?? false) &&
            $item_array['timing_type'] == self::TIMINGS_PRESETTING) {
            // cognos-blu-patch: begin
            $user_data = new ilTimingUser((int) $item['ref_id'], $ilUser->getId());
            if ($user_data->isScheduled()) {
                $item['start'] = $user_data->getStart()->get(IL_CAL_UNIX);
                $item['end'] = $user_data->getEnd()->get(IL_CAL_UNIX);
                $item['activation_info'] = 'crs_timings_planed_info';
            } else {
                $item['start'] = $item_array['suggestion_start'] ?? "";
                $item['end'] = $item_array['suggestion_end'] ?? "";
                $item['activation_info'] = 'crs_timings_suggested_info';
            }
            // cognos-blu-patch: end
        } elseif (($item_array['timing_type'] ?? 0) == self::TIMINGS_PRESETTING) {
            $item['start'] = $item_array['suggestion_start'] ?? "";
            $item['end'] = $item_array['suggestion_end'] ?? "";
            $item['activation_info'] = 'crs_timings_suggested_info';
        } elseif (($item_array['timing_type'] ?? 0) == self::TIMINGS_ACTIVATION) {
            $item['start'] = $item_array['timing_start'] ?? "";
            $item['end'] = $item_array['timing_end'] ?? "";
            $item['activation_info'] = 'obj_activation_list_gui';
        } else {
            $item['start'] = 'abc';
        }

        // #7359 - session sorting should always base on appointment date
        if ($item['type'] == 'sess') {
            $info = ilSessionAppointment::_lookupAppointment((int) $item['obj_id']);
            // #11987
            $item['masked_start'] = $item['start'];
            $item['masked_end'] = $item['end'] ?? "";
            $item['start'] = $info['start'];
            $item['end'] = $info['end'];
        }
    }

    /**
     * Get timing details for list gui
     */
    public static function addListGUIActivationProperty(ilObjectListGUI $list_gui, array &$item) : void
    {
        self::addAdditionalSubItemInformation($item);
        if (isset($item['timing_type'])) {
            if (!isset($item['masked_start'])) {
                $start = $item['start'] ?? 0;
                $end = $item['end'] ?? 0;
            } else {
                $start = $item['masked_start'];
                $end = $item['masked_end'];
            }
            $activation = '';
            switch ($item['timing_type']) {
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

                $list_gui->addCustomProperty(
                    $lng->txt($item['activation_info']),
                    $activation,
                    false,
                    true
                );
            }
        }
    }

    /**
     * Create db entry with default values
     */
    protected static function createDefaultEntry(int $ref_id) : array
    {
        global $DIC;

        $db = $DIC->database();
        $tree = $DIC->repositoryTree();

        $parent_id = $tree->getParentId($ref_id);
        if (!$parent_id) {
            return [];
        }

        $ilAtomQuery = $db->buildAtomQuery();
        $ilAtomQuery->addTableLock("crs_items");

        $ilAtomQuery->addQueryCallable(function (ilDBInterface $db) use ($ref_id, $parent_id, &$item) : void {
            $sql =
                "SELECT parent_id, obj_id, timing_type, timing_start, timing_end, suggestion_start," . PHP_EOL
                . "suggestion_end, changeable, visible, position, suggestion_start_rel, suggestion_end_rel" . PHP_EOL
                . "FROM crs_items" . PHP_EOL
                . "WHERE obj_id = " . $db->quote($ref_id, "integer") . PHP_EOL
            ;
            $set = $db->query($sql);
            if (!$db->numRows($set)) {
                $now = time();

                $item = array();
                $item["timing_type"] = self::TIMINGS_DEACTIVATED;
                $item["timing_start"] = $now;
                $item["timing_end"] = $now;
                $item["suggestion_start"] = $now;
                $item["suggestion_end"] = $now;
                $item['visible'] = 0;
                $item['changeable'] = 0;

                $values = [
                    "parent_id" => ["integer", $parent_id],
                    "obj_id" => ["integer", $ref_id],
                    "timing_type" => ["integer", $item["timing_type"]],
                    "timing_start" => ["integer", $item["timing_start"]],
                    "timing_end" => ["integer", $item["timing_end"]],
                    "suggestion_start" => ["integer", $item["suggestion_start"]],
                    "suggestion_end" => ["integer", $item["suggestion_end"]],
                    "changeable" => ["integer", $item["changeable"]],
                    "visible" => ["integer", $item["visible"]],
                    "suggestion_start_rel" => ["integer", $item["suggestion_start_rel"] ?? 0],
                    "suggestion_end_rel" => ["integer", $item["suggestion_end_rel"] ?? 0],
                    "position" => ["integer", 0]
                ];
                $db->insert("crs_items", $values);
            }
        });

        $ilAtomQuery->run();

        // #9982 - to make getItem()-cache work
        $item["obj_id"] = $ref_id;
        $item["parent_id"] = $parent_id;

        return $item;
    }

    /**
     * Delete all db entries for ref id
     */
    public static function deleteAllEntries(int $ref_id) : bool
    {
        global $DIC;

        $db = $DIC->database();

        if (!$ref_id) {
            return false;
        }

        $sql =
            "DELETE FROM crs_items " . PHP_EOL
            . "WHERE obj_id = " . $db->quote($ref_id, 'integer') . PHP_EOL
        ;
        $db->manipulate($sql);

        $sql =
            "DELETE FROM crs_items " . PHP_EOL
            . "WHERE parent_id = " . $db->quote($ref_id, 'integer') . PHP_EOL
        ;
        $db->manipulate($sql);

        return true;
    }

    public static function cloneDependencies(int $ref_id, int $target_id, int $copy_id) : void
    {
        global $DIC;

        $ilLog = $DIC["ilLog"];

        $ilLog->write(__METHOD__ . ': Begin course items...' . $ref_id);

        $items = self::getItems($ref_id, false);
        if (!$items) {
            $ilLog->write(__METHOD__ . ': No course items found.');
            return;
        }

        // new course item object
        if (!is_object(ilObjectFactory::getInstanceByRefId($target_id, false))) {
            $ilLog->write(__METHOD__ . ': Cannot create target object.');
            return;
        }

        $cp_options = ilCopyWizardOptions::_getInstance($copy_id);
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
            $new_item->setTimingType((int) $item['timing_type']);
            $new_item->setTimingStart((int) $item['timing_start']);
            $new_item->setTimingEnd((int) $item['timing_end']);
            $new_item->setSuggestionStart((int) $item['suggestion_start']);
            $new_item->setSuggestionEnd((int) $item['suggestion_end']);
            $new_item->toggleChangeable((bool) $item['changeable']);
            $new_item->toggleVisible((bool) $item['visible']);
            $new_item->update($new_item_id, $new_parent);
            $new_item->setSuggestionStartRelative((int) $item['suggestion_start_rel']);
            $new_item->setSuggestionEndRelative((int) $item['suggestion_end_rel']);
            $new_item->createDefaultEntry($new_item_id);
            $new_item->update($new_item_id);
        }
    }


    //
    // TIMINGS VIEW RELATED (COURSE ONLY)
    //

    /**
     * Check if there is any active timing (in subtree)
     */
    public static function hasTimings(int $ref_id) : bool
    {
        global $DIC;

        $tree = $DIC->repositoryTree();
        $db = $DIC->database();

        $subtree = $tree->getSubTree($tree->getNodeData($ref_id));
        $ref_ids = array();
        foreach ($subtree as $node) {
            $ref_ids[] = $node['ref_id'];
        }

        $sql =
            "SELECT parent_id" . PHP_EOL
            . "FROM crs_items" . PHP_EOL
            . "WHERE timing_type = " . $db->quote(self::TIMINGS_PRESETTING, 'integer') . PHP_EOL
            . "AND " . $db->in('obj_id', $ref_ids, false, 'integer') . PHP_EOL
        ;
        $res = $db->query($sql);
        return (bool) $res->numRows();
    }

    /**
     * Check if there is any active changeable timing (in subtree)
     */
    public static function hasChangeableTimings(int $ref_id) : bool
    {
        global $DIC;

        $tree = $DIC->repositoryTree();
        $db = $DIC->database();

        $subtree = $tree->getSubTree($tree->getNodeData($ref_id));
        $ref_ids = array();
        foreach ($subtree as $node) {
            $ref_ids[] = $node['ref_id'];
        }

        $sql =
            "SELECT parent_id" . PHP_EOL
            . "FROM crs_items" . PHP_EOL
            . "WHERE timing_type = " . $db->quote(self::TIMINGS_PRESETTING, 'integer') . PHP_EOL
            . "AND changeable = " . $db->quote(1, 'integer') . PHP_EOL
            . "AND " . $db->in('obj_id', $ref_ids, false, 'integer') . PHP_EOL
        ;
        $res = $db->query($sql);
        return (bool) $res->numRows();
    }

    /**
     * Validate ref ids and add list data
     */
    protected static function processListItems(array $ref_ids) : array
    {
        global $DIC;

        $tree = $DIC->repositoryTree();

        $res = array();

        foreach (array_map('intval', $ref_ids) as $item_ref_id) {
            if ($tree->isDeleted($item_ref_id)) {
                continue;
            }
            // #7571: when node is removed from system, e.g. inactive trashcan, an empty array is returned
            $node = $tree->getNodeData($item_ref_id);
            if ((int) $node["ref_id"] !== $item_ref_id) {
                continue;
            }
            $res[$item_ref_id] = $node;
        }

        if (count($res)) {
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
     */
    public static function getItemsByEvent(int $event_id) : array
    {
        $event_items = new ilEventItems($event_id);
        return self::processListItems($event_items->getItems());
    }

    /**
     * Get materials of item group
     */
    public static function getItemsByItemGroup(int $item_group_ref_id) : array
    {
        $ig_items = new ilItemGroupItems($item_group_ref_id);
        $items = $ig_items->getValidItems();
        return self::processListItems($items);
    }

    /**
     * Get objective items
     */
    public static function getItemsByObjective(int $objective_id) : array
    {
        $item_ids = ilCourseObjectiveMaterials::_getAssignedMaterials($objective_id);
        return self::processListItems($item_ids);
    }

    /**
     * Get sub item data
     */
    public static function getItems(int $parent_id, bool $with_list_data = true) : array
    {
        global $DIC;

        $tree = $DIC->repositoryTree();

        $items = array();

        $ref_ids = array();
        foreach ($tree->getChilds($parent_id) as $item) {
            if ($item['type'] !== 'rolf') {
                $items[] = $item;
                $ref_ids[] = (int) $item['ref_id'];
            }
        }

        if ($ref_ids) {
            self::preloadData($ref_ids);

            foreach ($items as $idx => $item) {
                if (!$with_list_data) {
                    $items[$idx] = array_merge($item, self::getItem((int) $item['ref_id']));
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
     */
    public static function getTimingsAdministrationItems(int $parent_id) : array
    {
        $items = self::getItems($parent_id, false);
        $active = $availability = $inactive = [];
        foreach ($items as $item) {
            if ($item['timing_type'] == self::TIMINGS_DEACTIVATED) {
                $inactive[] = $item;
            } elseif ($item['timing_type'] == self::TIMINGS_ACTIVATION) {
                $availability[] = $item;
            } else {
                $active[] = $item;
            }
        }
        $active = ilArrayUtil::sortArray($active, 'suggestion_start');
        $availability = ilArrayUtil::sortArray($availability, 'timing_start');
        $inactive = ilArrayUtil::sortArray($inactive, 'title');

        $items = array_merge($active, $availability, $inactive);
        return $items;
    }

    /**
     * Get (sub) item data for timings view (no session material, no side blocks)
     */
    public static function getTimingsItems(int $container_ref_id) : array
    {
        global $DIC;

        $objDefinition = $DIC["objDefinition"];

        $filtered = array();

        $event_items = ilEventItems::_getItemsOfContainer($container_ref_id);
        foreach (self::getTimingsAdministrationItems($container_ref_id) as $item) {
            if (!in_array($item['ref_id'], $event_items) &&
                !$objDefinition->isSideBlock($item['type'])) {
                $filtered[] = $item;
            }
        }

        return $filtered;
    }

    public function read(int $ref_id, int $parent_id = 0) : void
    {
        global $DIC;
        $db = $DIC->database();

        $sql =
            "SELECT parent_id, obj_id, timing_type, timing_start, timing_end, suggestion_start," . PHP_EOL
            . "suggestion_end, changeable, visible, position, suggestion_start_rel, suggestion_end_rel" . PHP_EOL
            . "FROM crs_items" . PHP_EOL
            . "WHERE obj_id = " . $db->quote($ref_id, 'integer') . PHP_EOL
        ;

        if ($parent_id) {
            $sql .= "AND parent_id = " . $db->quote($parent_id, "integer") . PHP_EOL;
        }

        $res = $db->query($sql);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->setSuggestionStart((int) $row->suggestion_start);
            $this->setSuggestionEnd((int) $row->suggestion_end);
            $this->setSuggestionStartRelative((int) $row->suggestion_start_rel);
            $this->setSuggestionEndRelative((int) $row->suggestion_end_rel);
            $this->toggleVisible((bool) $row->visible);
            $this->toggleChangeable((bool) $row->changeable);
            $this->setTimingType((int) $row->timing_type);
            $this->setTimingStart((int) $row->timing_start);
            $this->setTimingEnd((int) $row->timing_end);
        }
    }
}
