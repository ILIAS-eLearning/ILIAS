<?php declare(strict_types=0);
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
 * Handle user timings
 * @author  Stefan Meyer <smeyer.ilias@gmx.de>
 * @ingroup ModulesCourse
 */
class ilTimingsUser
{
    private static array $instances = array();

    private int $container_obj_id = 0;
    private int $container_ref_id = 0;

    private bool $initialized = false;
    private array $item_ids = array();

    protected ilTree $tree;
    protected ilDBInterface $db;

    /**
     * Singleton constructor
     */
    protected function __construct(int $a_container_obj_id)
    {
        global $DIC;

        $this->tree = $DIC->repositoryTree();
        $this->db = $DIC->database();

        $this->container_obj_id = $a_container_obj_id;
        $refs = ilObject::_getAllReferences($a_container_obj_id);
        $this->container_ref_id = end($refs);
    }

    public static function getInstanceByContainerId(int $a_container_obj_id) : self
    {
        if (array_key_exists($a_container_obj_id, self::$instances)) {
            return self::$instances[$a_container_obj_id];
        }
        return self::$instances[$a_container_obj_id] = new self($a_container_obj_id);
    }

    public function getContainerObjId() : int
    {
        return $this->container_obj_id;
    }

    public function getContainerRefId() : int
    {
        return $this->container_ref_id;
    }

    public function getItemIds() : array
    {
        return $this->item_ids;
    }

    public function init() : void
    {
        if ($this->initialized) {
            return;
        }
        $this->item_ids = $this->tree->getSubTreeIds($this->getContainerRefId());
        ilObjectActivation::preloadData($this->item_ids);
        $this->initialized = true;
    }

    public function handleNewMembership(int $a_usr_id, ilDateTime $sub_date) : void
    {
        foreach ($this->getItemIds() as $item_ref_id) {
            $item = ilObjectActivation::getItem($item_ref_id);

            if ($item['timing_type'] != ilObjectActivation::TIMINGS_PRESETTING) {
                continue;
            }
            $user_item = new ilTimingUser($item['obj_id'], $a_usr_id);
            $user_start = clone $sub_date;
            $user_start->increment(IL_CAL_DAY, $item['suggestion_start_rel']);
            $user_item->getStart()->setDate($user_start->get(IL_CAL_UNIX), IL_CAL_UNIX);
            $user_end = clone $sub_date;
            $user_end->increment(IL_CAL_DAY, $item['suggestion_end_rel']);
            $user_item->getEnd()->setDate($user_end->get(IL_CAL_UNIX), IL_CAL_UNIX);
            $user_item->update();
        }
    }

    public function handleUnsubscribe(int $a_usr_id) : void
    {
        $query = 'DELETE FROM crs_timings_user WHERE ' . $this->db->in(
            'ref_id',
            $this->item_ids,
            false,
            'integer'
        ) . ' ' .
            'AND usr_id = ' . $this->db->quote($a_usr_id, 'integer');
        $this->db->manipulate($query);
    }

    /**
     * Check if users currently exceeded ANY object
     * @param int[] $a_user_ids
     */
    public static function lookupTimingsExceededByUser(array $a_user_ids) : array
    {
        $res = array();

        $meta = [];
        foreach (self::lookupTimings($a_user_ids, $meta, true) as $user_ids) {
            foreach ($user_ids as $user_id) {
                $res[$user_id] = $user_id;
            }
        }
        return array_values($res);
    }

    /**
     * Lookup references, users with exceeded timings
     *
     * @param int[]  $a_user_ids
     */
    public static function lookupTimings(array $a_user_ids, array &$a_meta = null, bool $a_only_exceeded = true) : array
    {
        global $DIC;

        $ilDB = $DIC->database();
        $logger = $DIC->logger()->crs();

        $res = array();
        $now = time();

        // get all relevant courses
        $course_members_map = ilParticipants::getUserMembershipAssignmentsByType($a_user_ids, ['crs'], true);
        $logger->debug('Course membership assignments');
        $logger->dump($course_members_map, \ilLogLevel::DEBUG);

        // lookup (course) timing settings
        $query = 'SELECT crsi.obj_id sub_ref_id, oref.ref_id, oref.obj_id, crsi.suggestion_start' .
            ',crsi.suggestion_end,crsi.changeable, crss.timing_mode' .
            ' FROM crs_settings crss' .
            ' JOIN object_reference oref ON (oref.obj_id = crss.obj_id AND oref.deleted IS NULL) ' .
            ' JOIN crs_items crsi ON (crsi.parent_id = oref.ref_id)' .
            ' JOIN object_reference iref ON (crsi.obj_id = iref.ref_id AND iref.deleted IS NULL) ' .
            ' WHERE crss.view_mode = ' . $ilDB->quote(ilCourseConstants::IL_CRS_VIEW_TIMING, 'integer') .
            ' AND ' . $ilDB->in('crss.obj_id', array_keys($course_members_map), false, 'integer') .
            ' AND crsi.timing_type = ' . $ilDB->quote(ilObjectActivation::TIMINGS_PRESETTING, 'integer');

        $logger->debug($query);

        $set = $ilDB->query($query);
        $user_relevant = $course_map = $course_parent_map = [];
        while ($row = $ilDB->fetchAssoc($set)) {
            $obj_id = (int) $row['obj_id'];
            $sub_ref_id = (int) $row['sub_ref_id'];
            $mode = (int) $row['timing_mode'];

            // needed for course_map-lookup for user-relevant data (see below)
            $course_parent_map[(int) $row['sub_ref_id']] = (int) $row['obj_id'];
            $course_map[(int) $row['obj_id']] = (int) $row['ref_id'];

            // gather meta data
            if (is_array($a_meta)) {
                foreach ($a_user_ids as $user_id) {
                    // only if course member
                    if (in_array($user_id, $course_members_map[$obj_id])) {
                        $a_meta[$user_id][$sub_ref_id] = array(
                            'parent' => $row['ref_id']
                        );
                    }
                }
            }

            // preset all users with object setting
            if ($mode == \ilCourseConstants::IL_CRS_VIEW_TIMING_ABSOLUTE) {
                // gather meta data
                if (is_array($a_meta)) {
                    foreach ($a_user_ids as $user_id) {
                        // only if course member
                        if (in_array($user_id, $course_members_map[$obj_id])) {
                            $a_meta[$user_id][$sub_ref_id]['start'] = (int) $row['suggestion_start'];
                            $a_meta[$user_id][$sub_ref_id]['end'] = (int) $row['suggestion_end'];
                        }
                    }
                }

                if (
                    ($a_only_exceeded && ((int) $row['suggestion_end'] && (int) $row['suggestion_end'] < $now)) ||
                    (!$a_only_exceeded && ((int) $row['suggestion_start'] && (int) $row['suggestion_start'] < $now))
                ) {
                    foreach ($a_user_ids as $user_id) {
                        // only if course member
                        if (in_array($user_id, $course_members_map[$obj_id])) {
                            $res[$sub_ref_id][$user_id] = $user_id;
                        }
                    }
                }
            }

            // gather all objects which might have user-specific settings
            if ($row['changeable'] ||
                $mode == \ilCourseConstants::IL_CRS_VIEW_TIMING_RELATIVE) {
                $user_relevant[] = $sub_ref_id;
            }
        }

        if ($user_relevant !== []) {
            // get user-specific data
            $query = 'SELECT * FROM crs_timings_user' .
                ' WHERE ' . $ilDB->in('usr_id', $a_user_ids, false, 'integer') .
                ' AND ' . $ilDB->in('ref_id', $user_relevant, false, 'integer');
            $set = $ilDB->query($query);
            while ($row = $ilDB->fetchAssoc($set)) {
                $ref_id = (int) $row['ref_id'];
                $user_id = (int) $row['usr_id'];

                // only if course member
                $crs_obj_id = $course_parent_map[$ref_id];
                if (!in_array($user_id, $course_members_map[$crs_obj_id])) {
                    continue;
                }

                // gather meta data
                if (is_array($a_meta)) {
                    $a_meta[$user_id][$ref_id]['start'] = (int) $row['sstart'];
                    $a_meta[$user_id][$ref_id]['end'] = (int) $row['ssend'];
                }

                if (
                    ($a_only_exceeded && (int) $row['ssend'] && (int) $row['ssend'] < $now) ||
                    (!$a_only_exceeded && (int) $row['sstart'] && (int) $row['sstart'] < $now)
                ) {
                    $res[$ref_id][$user_id] = $user_id;
                } else {
                    // if not exceeded remove preset data
                    unset($res[$ref_id][$user_id]);
                }
            }
        }

        // clean-up/minimize the result
        foreach (array_keys($res) as $ref_id) {
            if (count($res[$ref_id]) === 0) {
                if (isset($res['ref_id']) && !count($res['ref_id'])) {
                    unset($res[$ref_id]);
                } else {
                    $res[$ref_id] = array_values($res[$ref_id]);
                }
            }
        }

        if (isset($res) && count($res)) {
            $obj_map = array();
            $invalid_lp = self::getObjectsWithInactiveLP(array_keys($res), $obj_map);

            foreach (array_keys($res) as $ref_id) {
                // invalid LP?
                if (in_array($ref_id, $invalid_lp)) {
                    $res[$ref_id] = array();
                } // LP completed?
                else {
                    $user_ids = $res[$ref_id];
                    if ($user_ids !== []) {
                        $res[$ref_id] = array_diff(
                            $user_ids,
                            ilLPStatus::_lookupCompletedForObject($obj_map[$ref_id], $user_ids)
                        );
                    }
                }

                // delete reference array, if no users are given anymore
                if ($res[$ref_id] === []) {
                    unset($res[$ref_id]);
                }
            }
        }

        // #2176 - add course entries (1 exceeded sub-item is enough)
        foreach ($res as $ref_id => $user_ids) {
            // making sure one last time
            if ($user_ids === [] && isset($res['ref_id'])) {
                unset($res[$ref_id]);
            } else {
                $crs_obj_id = $course_parent_map[$ref_id];
                $crs_ref_id = $course_map[$crs_obj_id];
                if (!array_key_exists($crs_ref_id, $res)) {
                    $res[$crs_ref_id] = $user_ids;
                } else {
                    $res[$crs_ref_id] = array_unique(array_merge($user_ids, $res[$crs_ref_id]));
                }
            }
        }
        return $res;
    }

    /**
     * Check object LP modes
     * @param int[]  $a_ref_ids
     */
    public static function getObjectsWithInactiveLP(array $a_ref_ids, array &$a_obj_map = null) : array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $res = array();
        $query = 'SELECT oref.ref_id, oref.obj_id, od.type' .
            ' FROM object_reference oref' .
            ' JOIN object_data od ON (oref.obj_id = od.obj_id)' .
            ' WHERE ' . $ilDB->in('oref.ref_id', $a_ref_ids, false, 'integer');
        $set = $ilDB->query($query);
        $item_map = $item_types = array();
        while ($row = $ilDB->fetchAssoc($set)) {
            $item_map[(int) $row['ref_id']] = (int) $row['obj_id'];
            $item_types[(int) $row['obj_id']] = $row['type'];
        }

        $a_obj_map = $item_map;

        // LP modes
        $db_modes = ilLPObjSettings::_lookupDBModeForObjects(array_values($item_map));

        $type_modes = array();
        foreach ($a_ref_ids as $ref_id) {
            $obj_id = $item_map[$ref_id];
            $type = $item_types[$obj_id];

            if (!ilObjectLP::isSupportedObjectType($type)) {
                $res[] = $ref_id;
                continue;
            }

            // use db mode
            if (array_key_exists($obj_id, $db_modes)) {
                $mode = $db_modes[$obj_id];
            } // use default
            else {
                if (!array_key_exists($type, $type_modes)) {
                    $type_modes[$type] = ilObjectLP::getInstance($obj_id);
                    $type_modes[$type] = $type_modes[$type]->getDefaultMode();
                }
                $mode = $type_modes[$type];
            }

            if ($mode == ilLPObjSettings::LP_MODE_DEACTIVATED ||
                $mode == ilLPObjSettings::LP_MODE_UNDEFINED) {
                $res[] = $ref_id;
            }
        }
        return $res;
    }
}
