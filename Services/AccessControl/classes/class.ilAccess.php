<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @defgroup ServicesAccessControl Services/AccessControl
 */

/**
* Class ilAccessHandler
*
* Checks access for ILIAS objects
*
* @author Alex Killing <alex.killing@gmx.de>
* @author Sascha Hofmann <saschahofmann@gmx.de>
* @version $Id$
*
* @ingroup ServicesAccessControl
*/
class ilAccess implements ilAccessHandler
{

    /**
     * @var \ilOrgUnitPositionAccess
     */
    protected $ilOrgUnitPositionAccess;
    /**
     * @var array
     */
    protected $obj_tree_cache;
    /**
     * @var array
     */
    protected $obj_type_cache;
    /**
     * @var array
     */
    protected $obj_id_cache;
    /**
     * @var bool
     */
    protected $status;
    /**
     * @var bool
     */
    protected $path;
    /**
     * @var bool
     */
    protected $condition;
    /**
     * @var bool
     */
    protected $tree;
    /**
     * @var bool
     */
    protected $rbac;
    /**
     * @var bool Whether use cache or not
     */
    protected $cache;
    /**
     * @var \ilAccessInfo
     */
    protected $current_info;
    /**
     * @var array
     */
    protected $results;
    /**
     * @var \ilRbacSystem
     */
    protected $rbacsystem;
    /**
     * @var array
     */
    protected $stored_rbac_access = array();


    /**
     * @var ilLogger
     */
    protected $ac_logger;

    public function __construct()
    {
        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];

        $this->rbacsystem = $rbacsystem;
        $this->results = array();
        $this->current_info = new ilAccessInfo();

        // use function enable to switch on/off tests (only cache is used so far)
        $this->cache = true;
        $this->rbac = true;
        $this->tree = true;
        $this->condition = true;
        $this->path = true;
        $this->status = true;
        $this->obj_id_cache = array();
        $this->obj_type_cache = array();
        $this->obj_tree_cache = array();

        $this->ilOrgUnitPositionAccess = new ilOrgUnitPositionAccess();

        $this->ac_logger = ilLoggerFactory::getLogger('ac');
    }


    /**
     * @inheritdoc
     */
    public function storeAccessResult($a_permission, $a_cmd, $a_ref_id, $a_access_granted, $a_user_id = "", $a_info = "")
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];

        if ($a_user_id == "") {
            $a_user_id = $ilUser->getId();
        }

        if ($a_info == "") {
            $a_info = $this->current_info;
        }

        //var_dump("<pre>",$a_permission,"</pre>");

        if ($this->cache) {
            $this->results[$a_ref_id][$a_permission][$a_cmd][$a_user_id] =
                    array("granted" => $a_access_granted, "info" => $a_info,
                    "prevent_db_cache" => $this->getPreventCachingLastResult());
            //echo "<br>write-$a_ref_id-$a_permission-$a_cmd-$a_user_id-$a_access_granted-";
            $this->current_result_element = array($a_access_granted,$a_ref_id,$a_permission,$a_cmd,$a_user_id);
            $this->last_result = $this->results[$a_ref_id][$a_permission][$a_cmd][$a_user_id];
            $this->last_info = $a_info;
        }

        // get new info object
        $this->current_info = new ilAccessInfo();
    }

    /**
     * @inheritdoc
     */
    public function setPreventCachingLastResult($a_val)
    {
        $this->prevent_caching_last_result = $a_val;
    }

    /**
     * @inheritdoc
     */
    public function getPreventCachingLastResult()
    {
        return $this->prevent_caching_last_result;
    }

    /**
     * @inheritdoc
     */
    public function getStoredAccessResult($a_permission, $a_cmd, $a_ref_id, $a_user_id = "")
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];

        if ($a_user_id == "") {
            $a_user_id = $ilUser->getId();
        }

        /*if (is_object($this->results[$a_ref_id][$a_permission][$a_cmd][$a_user_id]['info']))
        {
            $this->current_info = $this->results[$a_ref_id][$a_permission][$a_cmd][$a_user_id]['info'];
        }*/

        if (isset($this->results[$a_ref_id][$a_permission][$a_cmd][$a_user_id])) {
            return $this->results[$a_ref_id][$a_permission][$a_cmd][$a_user_id];
        }
        return false;
    }
    /**
     * @inheritdoc
     */
    public function storeCache()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $ilUser = $DIC['ilUser'];

        $query = "DELETE FROM acc_cache WHERE user_id = " . $ilDB->quote($ilUser->getId(), 'integer');
        $res = $ilDB->manipulate($query);

        $ilDB->insert('acc_cache', array(
            'user_id'	=>	array('integer',$ilUser->getId()),
            'time'		=>	array('integer',time()),
            'result'	=>	array('clob',serialize($this->results))
            ));
    }
    /**
     * @inheritdoc
     */
    public function readCache($a_secs = 0)
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        $ilDB = $DIC['ilDB'];

        if ($a_secs > 0) {
            $query = "SELECT * FROM acc_cache WHERE user_id = " .
                $ilDB->quote($ilUser->getId(), 'integer');
            $set = $ilDB->query($query);
            $rec = $set->fetchRow(ilDBConstants::FETCHMODE_ASSOC);
            if ((time() - $rec["time"]) < $a_secs) {
                $this->results = unserialize($rec["result"]);
                //var_dump($this->results);
                return true;
            }
        }
        return false;
    }
    /**
     * @inheritdoc
     */
    public function getResults()
    {
        return $this->results;
    }
    /**
     * @inheritdoc
     */
    public function setResults($a_results)
    {
        $this->results = $a_results;
    }

    /**
     * @inheritdoc
     */
    public function addInfoItem($a_type, $a_text, $a_data = "")
    {
        $this->current_info->addInfoItem($a_type, $a_text, $a_data);
    }

    /**
     * @inheritdoc
     */
    public function checkAccess($a_permission, $a_cmd, $a_ref_id, $a_type = "", $a_obj_id = "", $a_tree_id="")
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];

        return $this->checkAccessOfUser($ilUser->getId(), $a_permission, $a_cmd, $a_ref_id, $a_type, $a_obj_id, $a_tree_id);
    }

    /**
     * @inheritdoc
     */
    public function checkAccessOfUser($a_user_id, $a_permission, $a_cmd, $a_ref_id, $a_type = "", $a_obj_id = "", $a_tree_id="")
    {
        global $DIC;

        $ilBench = $DIC['ilBench'];
        $lng = $DIC['lng'];

        $this->setPreventCachingLastResult(false);	// for external db based caches

        $ilBench->start("AccessControl", "0400_clear_info");
        $this->current_info->clear();
        $ilBench->stop("AccessControl", "0400_clear_info");


        // get stored result (internal memory based cache)
        $cached = $this->doCacheCheck($a_permission, $a_cmd, $a_ref_id, $a_user_id);
        if ($cached["hit"]) {
            // Store access result
            if (!$cached["granted"]) {
                $this->current_info->addInfoItem(IL_NO_PERMISSION, $lng->txt("status_no_permission"));
            }
            if ($cached["prevent_db_cache"]) {
                $this->setPreventCachingLastResult(true);	// should have been saved in previous call already
            }
            return $cached["granted"];
        }

        $ilBench->start("AccessControl", "0500_lookup_id_and_type");
        // get object id if not provided
        if ($a_obj_id == "") {
            if (isset($this->obj_id_cache[$a_ref_id]) && $this->obj_id_cache[$a_ref_id] > 0) {
                $a_obj_id = $this->obj_id_cache[$a_ref_id];
            } else {
                $a_obj_id = ilObject::_lookupObjId($a_ref_id);
                $this->obj_id_cache[$a_ref_id] = $a_obj_id;
            }
        }
        if ($a_type == "") {
            if (isset($this->obj_type_cache[$a_ref_id]) && $this->obj_type_cache[$a_ref_id] != "") {
                $a_type = $this->obj_type_cache[$a_ref_id];
            } else {
                $a_type = ilObject::_lookupType($a_ref_id, true);
                $this->obj_type_cache[$a_ref_id] = $a_type;
            }
        }

        $ilBench->stop("AccessControl", "0500_lookup_id_and_type");

        // if supplied tree id is not = 1 (= repository main tree),
        // check if object is in tree and not deleted
        if ($a_tree_id != 1 &&
            !$this->doTreeCheck($a_permission, $a_cmd, $a_ref_id, $a_user_id)) {
            $this->current_info->addInfoItem(IL_NO_PERMISSION, $lng->txt("status_no_permission"));
            $this->storeAccessResult($a_permission, $a_cmd, $a_ref_id, false, $a_user_id);
            return false;
        }

        // rbac check for current object
        if (!$this->doRBACCheck($a_permission, $a_cmd, $a_ref_id, $a_user_id, $a_type)) {
            $this->current_info->addInfoItem(IL_NO_PERMISSION, $lng->txt("status_no_permission"));
            $this->storeAccessResult($a_permission, $a_cmd, $a_ref_id, false, $a_user_id);
            return false;
        }

        // Check object activation
        $act_check = $this->doActivationCheck(
            $a_permission,
            $a_cmd,
            $a_ref_id,
            $a_user_id,
            $a_obj_id,
            $a_type
        );

        if (!$act_check) {
            $this->current_info->addInfoItem(IL_NO_PERMISSION, $lng->txt('status_no_permission'));
            $this->storeAccessResult($a_permission, $a_cmd, $a_ref_id, false, $a_user_id);
            return false;
        }

        // check read permission for all parents
        $par_check = $this->doPathCheck($a_permission, $a_cmd, $a_ref_id, $a_user_id);
        if (!$par_check) {
            $this->current_info->addInfoItem(IL_NO_PERMISSION, $lng->txt("status_no_permission"));
            $this->storeAccessResult($a_permission, $a_cmd, $a_ref_id, false, $a_user_id);
            return false;
        }

        // condition check (currently only implemented for read permission)
        if (!$this->doConditionCheck($a_permission, $a_cmd, $a_ref_id, $a_user_id, $a_obj_id, $a_type)) {
            $this->current_info->addInfoItem(IL_NO_PERMISSION, $lng->txt("status_no_permission"));
            $this->storeAccessResult($a_permission, $a_cmd, $a_ref_id, false, $a_user_id);
            $this->setPreventCachingLastResult(true);		// do not store this in db, since condition updates are not monitored
            return false;
        }

        // object type specific check
        if (!$this->doStatusCheck($a_permission, $a_cmd, $a_ref_id, $a_user_id, $a_obj_id, $a_type)) {
            $this->current_info->addInfoItem(IL_NO_PERMISSION, $lng->txt("status_no_permission"));
            $this->storeAccessResult($a_permission, $a_cmd, $a_ref_id, false, $a_user_id);
            $this->setPreventCachingLastResult(true);		// do not store this in db, since status updates are not monitored
            return false;
        }

        // all checks passed
        $this->storeAccessResult($a_permission, $a_cmd, $a_ref_id, true, $a_user_id);
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getInfo()
    {
        //return $this->last_result;
        //$this->last_info->setQueryData($this->current_result_element);
        //var_dump("<pre>",$this->results,"</pre>");
        return is_object($this->last_info) ? $this->last_info->getInfoItems() : array();
    }

    /**
     * @inheritdoc
     */
    public function getResultLast()
    {
        return $this->last_result;
    }
    /**
     * @inheritdoc
     */
    public function getResultAll($a_ref_id = "")
    {
        if ($a_ref_id == "") {
            return $this->results;
        }

        return $this->results[$a_ref_id];
    }

    /**
     * @inheritdoc
     */
    public function doCacheCheck($a_permission, $a_cmd, $a_ref_id, $a_user_id)
    {
        global $DIC;

        $ilBench = $DIC['ilBench'];
        //echo "cacheCheck<br/>";

        $ilBench->start("AccessControl", "1000_checkAccess_get_cache_result");
        $stored_access = $this->getStoredAccessResult($a_permission, $a_cmd, $a_ref_id, $a_user_id);
        //var_dump($stored_access);
        if (is_array($stored_access)) {
            $this->current_info = $stored_access["info"];
            //var_dump("cache-treffer:");
            $ilBench->stop("AccessControl", "1000_checkAccess_get_cache_result");
            return array("hit" => true, "granted" => $stored_access["granted"],
                "prevent_db_cache" => $stored_access["prevent_db_cache"]);
        }

        // not in cache
        $ilBench->stop("AccessControl", "1000_checkAccess_get_cache_result");
        return array("hit" => false, "granted" => false,
            "prevent_db_cache" => false);
    }

    /**
     * @inheritdoc
     */
    public function doTreeCheck($a_permission, $a_cmd, $a_ref_id, $a_user_id)
    {
        global $DIC;

        $tree = $DIC['tree'];
        $lng = $DIC['lng'];
        $ilBench = $DIC['ilBench'];
        //echo "treeCheck<br/>";

        // Get stored result
        $tree_cache_key = $a_user_id . ':' . $a_ref_id;
        if (array_key_exists($tree_cache_key, $this->obj_tree_cache)) {
            // Store access result
            if (!$this->obj_tree_cache[$tree_cache_key]) {
                $this->current_info->addInfoItem(IL_NO_PERMISSION, $lng->txt("status_no_permission"));
            }
            $this->storeAccessResult($a_permission, $a_cmd, $a_ref_id, $this->obj_tree_cache[$tree_cache_key], $a_user_id);

            return $this->obj_tree_cache[$tree_cache_key];
        }

        $ilBench->start("AccessControl", "2000_checkAccess_in_tree");

        if (!$tree->isInTree($a_ref_id) or $tree->isDeleted($a_ref_id)) {
            // Store negative access results

            // Store in tree cache
            // Note, we only store up to 1000 results to avoid memory overflow.
            if (count($this->obj_tree_cache) < 1000) {
                $this->obj_tree_cache[$tree_cache_key] = false;
            }

            // Store in result cache
            $this->current_info->addInfoItem(IL_DELETED, $lng->txt("object_deleted"));
            $this->storeAccessResult($a_permission, $a_cmd, $a_ref_id, false, $a_user_id);

            $ilBench->stop("AccessControl", "2000_checkAccess_in_tree");

            return false;
        }

        // Store positive access result.

        // Store in tree cache
        // Note, we only store up to 1000 results to avoid memory overflow.
        if (count($this->obj_tree_cache) < 1000) {
            $this->obj_tree_cache[$tree_cache_key] = true;
        }

        // Store in result cache
        $this->storeAccessResult($a_permission, $a_cmd, $a_ref_id, true, $a_user_id);

        $ilBench->stop("AccessControl", "2000_checkAccess_in_tree");
        return true;
    }

    /**
     * @inheritdoc
     */
    public function doRBACCheck($a_permission, $a_cmd, $a_ref_id, $a_user_id, $a_type)
    {
        global $DIC;

        $lng = $DIC['lng'];
        $ilBench = $DIC['ilBench'];
        $ilErr = $DIC['ilErr'];
        $ilLog = $DIC['ilLog'];

        $ilBench->start("AccessControl", "2500_checkAccess_rbac_check");

        if ($a_permission == "") {
            $message = sprintf(
                    '%s::doRBACCheck(): No operations given! $a_ref_id: %s',
                    get_class($this),
                    $a_ref_id
                );
            $ilLog->write($message, $ilLog->FATAL);
            $ilErr->raiseError($message, $ilErr->MESSAGE);
        }

        if (isset($this->stored_rbac_access[$a_user_id . "-" . $a_permission . "-" . $a_ref_id])) {
            $access = $this->stored_rbac_access[$a_user_id . "-" . $a_permission . "-" . $a_ref_id];
        } else {
            $access = $this->rbacsystem->checkAccessOfUser($a_user_id, $a_permission, $a_ref_id, $a_type);
            if (!is_array($this->stored_rbac_access) || count($this->stored_rbac_access) < 1000) {
                if ($a_permission != "create") {
                    $this->stored_rbac_access[$a_user_id . "-" . $a_permission . "-" . $a_ref_id] = $access;
                }
            }
        }

        // Store in result cache
        if (!$access) {
            $this->current_info->addInfoItem(IL_NO_PERMISSION, $lng->txt("status_no_permission"));
        }
        if ($a_permission != "create") {
            $this->storeAccessResult($a_permission, $a_cmd, $a_ref_id, true, $a_user_id);
        }
        $ilBench->stop("AccessControl", "2500_checkAccess_rbac_check");

        return $access;
    }

    /**
     * @inheritdoc
     */
    public function doPathCheck($a_permission, $a_cmd, $a_ref_id, $a_user_id, $a_all = false)
    {
        global $DIC;

        $tree = $DIC['tree'];
        $lng = $DIC['lng'];
        $ilBench = $DIC['ilBench'];
        $ilObjDataCache = $DIC['ilObjDataCache'];
        //echo "<br>dopathcheck";
        //echo "pathCheck<br/>";
        $ilBench->start("AccessControl", "3100_checkAccess_check_parents_get_path");

        //		if (isset($this->stored_path[$a_ref_id]))
        //		{
        //			$path = $this->stored_path[$a_ref_id];
        //		}
        //		else
        //		{
        $path = $tree->getPathId($a_ref_id);
        //			$this->stored_path[$a_ref_id] = $path;
        //		}
        $ilBench->stop("AccessControl", "3100_checkAccess_check_parents_get_path");

        foreach ($path as $id) {
            if ($a_ref_id == $id) {
                continue;
            }

            $access = $this->checkAccessOfUser($a_user_id, "read", "info", $id);

            if ($access == false) {

                //$this->doCacheCheck($a_permission, $a_cmd, $a_ref_id, $a_user_id);
                $this->current_info->addInfoItem(IL_NO_PARENT_ACCESS, $lng->txt("no_parent_access"), $id);

                if ($a_all == false) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function doActivationCheck($a_permission, $a_cmd, $a_ref_id, $a_user_id, $a_obj_id, $a_type)
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        /**
         * @var ilObjectDefinition
         */
        $objDefinition = $DIC['objDefinition'];


        $cache_perm = ($a_permission == "visible")
            ? "visible"
            : "other";


        if (isset($this->ac_cache[$cache_perm][$a_ref_id][$a_user_id])) {
            return $this->ac_cache[$cache_perm][$a_ref_id][$a_user_id];
        }

        // nothings needs to be done if current permission is write permission
        if ($a_permission == 'write') {
            return true;
        }

        // #10852 - member view check
        if ($a_user_id == $ilUser->getId()) {
            // #10905 - activate parent container ONLY
            include_once './Services/Container/classes/class.ilMemberViewSettings.php';
            $memview = ilMemberViewSettings::getInstance();
            if ($memview->isActiveForRefId($a_ref_id) &&
                $memview->getContainer() == $a_ref_id) {
                return true;
            }
        }

        // in any case, if user has write permission return true
        if ($this->checkAccessOfUser($a_user_id, "write", "", $a_ref_id)) {
            $this->ac_cache[$cache_perm][$a_ref_id][$a_user_id] = true;
            return true;
        }

        // no write access => check centralized offline status
        if (
            $objDefinition->supportsOfflineHandling($a_type) &&
            ilObject::lookupOfflineStatus($a_obj_id)
        ) {
            $this->ac_cache[$cache_perm][$a_ref_id][$a_user_id] = false;
            return false;
        }

        include_once 'Services/Object/classes/class.ilObjectActivation.php';
        $item_data = ilObjectActivation::getItem($a_ref_id);

        // if activation isn't enabled
        if ($item_data === null ||
            $item_data['timing_type'] != ilObjectActivation::TIMINGS_ACTIVATION) {
            $this->ac_cache[$cache_perm][$a_ref_id][$a_user_id] = true;
            return true;
        }

        // if within activation time
        if ((time() >= $item_data['timing_start']) and
           (time() <= $item_data['timing_end'])) {
            $this->ac_cache[$cache_perm][$a_ref_id][$a_user_id] = true;
            return true;
        }

        // if user has write permission
        if ($this->checkAccessOfUser($a_user_id, "write", "", $a_ref_id)) {
            $this->ac_cache[$cache_perm][$a_ref_id][$a_user_id] = true;
            $ilBench->stop("AccessControl", "3150_checkAccess_check_course_activation");
            return true;
        }

        // if current permission is visible and visible is set in activation
        if ($a_permission == 'visible' and $item_data['visible']) {
            $this->ac_cache[$cache_perm][$a_ref_id][$a_user_id] = true;
            return true;
        }

        // learning progress must be readable, regardless of the activation
        if ($a_permission == 'read_learning_progress') {
            $this->ac_cache[$cache_perm][$a_ref_id][$a_user_id] = true;
            $ilBench->stop("AccessControl", "3150_checkAccess_check_course_activation");
            return true;
        }

        // no access
        $this->ac_cache[$cache_perm][$a_ref_id][$a_user_id] = false;
        return false;
    }

    /**
     * @inheritdoc
     */
    public function doConditionCheck($a_permission, $a_cmd, $a_ref_id, $a_user_id, $a_obj_id, $a_type)
    {
        //echo "conditionCheck<br/>";
        global $DIC;

        $lng = $DIC['lng'];
        $ilBench = $DIC['ilBench'];

        if (
            ($a_permission == 'visible') and
            !$this->checkAccessOfUser($a_user_id, "write", "", $a_ref_id, $a_type, $a_obj_id)
        ) {
            if (ilConditionHandler::lookupEffectiveHiddenStatusByTarget($a_ref_id)) {
                if (!ilConditionHandler::_checkAllConditionsOfTarget($a_ref_id, $a_obj_id, $a_type, $a_user_id)) {
                    $conditions = ilConditionHandler::_getEffectiveConditionsOfTarget($a_ref_id, $a_obj_id, $a_type);
                    foreach ($conditions as $condition) {
                        $this->current_info->addInfoItem(
                            IL_MISSING_PRECONDITION,
                            $lng->txt("missing_precondition") . ": " .
                            ilObject::_lookupTitle($condition["trigger_obj_id"]) . " " .
                            $lng->txt("condition_" . $condition["operator"]) . " " .
                            $condition["value"],
                            $condition
                        );
                    }
                    return false;
                }
                $ilBench->stop("AccessControl", "4000_checkAccess_condition_check");
            }
        }


        if (($a_permission == "read" or $a_permission == 'join') &&
            !$this->checkAccessOfUser($a_user_id, "write", "", $a_ref_id, $a_type, $a_obj_id)) {
            $ilBench->start("AccessControl", "4000_checkAccess_condition_check");
            if (!ilConditionHandler::_checkAllConditionsOfTarget($a_ref_id, $a_obj_id, $a_type, $a_user_id)) {
                $conditions = ilConditionHandler::_getEffectiveConditionsOfTarget($a_ref_id, $a_obj_id, $a_type);
                foreach ($conditions as $condition) {
                    $this->current_info->addInfoItem(
                        IL_MISSING_PRECONDITION,
                        $lng->txt("missing_precondition") . ": " .
                        ilObject::_lookupTitle($condition["trigger_obj_id"]) . " " .
                        $lng->txt("condition_" . $condition["operator"]) . " " .
                        $condition["value"],
                        $condition
                    );
                }
                $ilBench->stop("AccessControl", "4000_checkAccess_condition_check");
                return false;
            }
            $ilBench->stop("AccessControl", "4000_checkAccess_condition_check");
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function doStatusCheck($a_permission, $a_cmd, $a_ref_id, $a_user_id, $a_obj_id, $a_type)
    {
        global $DIC;

        $objDefinition = $DIC['objDefinition'];
        $ilBench = $DIC['ilBench'];
        $ilPluginAdmin = $DIC['ilPluginAdmin'];
        //echo "statusCheck<br/>";
        $ilBench->start("AccessControl", "5000_checkAccess_object_check");

        // check for a deactivated plugin
        if ($objDefinition->isPluginTypeName($a_type) && !$objDefinition->isPlugin($a_type)) {
            return false;
        }
        if (!$a_type) {
            return false;
        }

        $class = $objDefinition->getClassName($a_type);
        $location = $objDefinition->getLocation($a_type);
        $full_class = "ilObj" . $class . "Access";

        if ($class == "") {
            $this->ac_logger->error("Cannot find class for object type $a_type, obj id $a_obj_id, ref id $a_ref_id. Abort status check.");
            return false;
        }

        include_once($location . "/class." . $full_class . ".php");
        // static call to ilObj..::_checkAccess($a_cmd, $a_permission, $a_ref_id, $a_obj_id)

        $full_class = new $full_class();

        $obj_access = call_user_func(
            array($full_class, "_checkAccess"),
            $a_cmd,
            $a_permission,
            $a_ref_id,
            $a_obj_id,
            $a_user_id
        );
        if (!($obj_access === true)) {
            //Note: We must not add an info item here, because one is going
            //      to be added by the user function we just called a few
            //      lines above.
            //$this->current_info->addInfoItem(IL_NO_OBJECT_ACCESS, $obj_access);

            $this->storeAccessResult($a_permission, $a_cmd, $a_ref_id, false, $a_user_id);
            $ilBench->stop("AccessControl", "5000_checkAccess_object_check");
            return false;
        }

        $this->storeAccessResult($a_permission, $a_cmd, $a_ref_id, true, $a_user_id);
        $ilBench->stop("AccessControl", "5000_checkAccess_object_check");
        return true;
    }

    /**
     * @inheritdoc
     */
    public function clear()
    {
        $this->results = array();
        $this->last_result = "";
        $this->current_info = new ilAccessInfo();
        $this->stored_rbac_access = [];
    }
    /**
     * @inheritdoc
     */
    public function enable($a_str, $a_bool)
    {
        $this->$a_str = $a_bool;
    }



    //
    // OrgUnit Positions
    //

    /**
     * @inheritdoc
     */
    public function filterUserIdsForCurrentUsersPositionsAndPermission(array $user_ids, $permission)
    {
        return $this->ilOrgUnitPositionAccess->filterUserIdsForCurrentUsersPositionsAndPermission($user_ids, $permission);
    }

    /**
     * @inheritdoc
     */
    public function filterUserIdsForUsersPositionsAndPermission(array $user_ids, $for_user_id, $permission)
    {
        return $this->ilOrgUnitPositionAccess->filterUserIdsForUsersPositionsAndPermission($user_ids, $for_user_id, $permission);
    }

    /**
     * @inheritdoc
     */
    public function isCurrentUserBasedOnPositionsAllowedTo($permission, array $on_user_ids)
    {
        return $this->ilOrgUnitPositionAccess->isCurrentUserBasedOnPositionsAllowedTo($permission, $on_user_ids);
    }

    /**
     * @inheritdoc
     */
    public function isUserBasedOnPositionsAllowedTo($which_user_id, $permission, array $on_user_ids)
    {
        return $this->ilOrgUnitPositionAccess->isUserBasedOnPositionsAllowedTo($which_user_id, $permission, $on_user_ids);
    }

    /**
     * @inheritdoc
     */
    public function checkPositionAccess($pos_perm, $ref_id)
    {
        return $this->ilOrgUnitPositionAccess->checkPositionAccess($pos_perm, $ref_id);
    }

    /**
     * @inheritdoc
     */
    public function checkRbacOrPositionPermissionAccess($rbac_perm, $pos_perm, $ref_id)
    {
        return $this->ilOrgUnitPositionAccess->checkRbacOrPositionPermissionAccess($rbac_perm, $pos_perm, $ref_id);
    }

    /**
     * @inheritdoc
     */
    public function filterUserIdsByPositionOfCurrentUser($pos_perm, $ref_id, array $user_ids)
    {
        return $this->ilOrgUnitPositionAccess->filterUserIdsByPositionOfCurrentUser($pos_perm, $ref_id, $user_ids);
    }

    /**
     * @inheritdoc
     */
    public function filterUserIdsByPositionOfUser($user_id, $pos_perm, $ref_id, array $user_ids)
    {
        return $this->ilOrgUnitPositionAccess->filterUserIdsByPositionOfUser($user_id, $pos_perm, $ref_id, $user_ids);
    }

    /**
     * @inheritdoc
     */
    public function filterUserIdsByRbacOrPositionOfCurrentUser($rbac_perm, $pos_perm, $ref_id, array $user_ids)
    {
        return $this->ilOrgUnitPositionAccess->filterUserIdsByRbacOrPositionOfCurrentUser($rbac_perm, $pos_perm, $ref_id, $user_ids);
    }

    /**
     * @inheritdoc
     */
    public function hasCurrentUserAnyPositionAccess($ref_id)
    {
        return $this->ilOrgUnitPositionAccess->hasCurrentUserAnyPositionAccess($ref_id);
    }

    /**
     * @inheritdoc
     */
    public function hasUserRBACorAnyPositionAccess($rbac_perm, $ref_id)
    {
        return $this->ilOrgUnitPositionAccess->hasUserRBACorAnyPositionAccess($rbac_perm, $ref_id);
    }
}
