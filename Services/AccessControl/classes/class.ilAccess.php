<?php

declare(strict_types=1);

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
 * Class ilAccessHandler
 * Checks access for ILIAS objects
 * @author Alex Killing <alex.killing@gmx.de>
 * @author Sascha Hofmann <saschahofmann@gmx.de>
 */
class ilAccess implements ilAccessHandler
{
    private const MAX_CACHE_SIZE = 1000;

    protected ilOrgUnitPositionAccess $ilOrgUnitPositionAccess;
    protected array $obj_tree_cache;
    protected array $obj_type_cache;
    protected array $obj_id_cache;
    protected array $ac_cache;

    protected bool $status;
    protected bool $path;
    protected bool $condition;
    protected bool $tree;
    protected bool $rbac;
    protected bool $cache;

    private bool $prevent_caching_last_result = false;

    protected ilAccessInfo $current_info;
    protected ?ilAccessInfo $last_info = null;
    protected array $results = [];
    protected array $last_result = [];
    protected array $stored_rbac_access = [];
    protected array $current_result_element = [];

    protected ilRbacSystem $rbacsystem;
    protected ilObjUser $user;
    protected ilLogger $ac_logger;
    protected ilDBInterface $db;
    protected ilTree $repositoryTree;
    protected ilLanguage $language;
    protected ilObjectDefinition $objDefinition;

    public function __construct()
    {
        global $DIC;

        $rbacsystem = $DIC->rbac()->system();

        $this->user = $DIC->user();
        $this->db = $DIC->database();
        $this->rbacsystem = $rbacsystem;
        $this->results = array();
        $this->current_info = new ilAccessInfo();
        $this->repositoryTree = $DIC->repositoryTree();
        $this->language = $DIC->language();
        $this->objDefinition = $DIC['objDefinition'];

        // use function enable to switch on/off tests (only cache is used so far)
        $this->cache = true;
        $this->rbac = true;
        $this->tree = true;
        $this->condition = true;
        $this->path = true;
        $this->status = true;
        $this->obj_id_cache = [];
        $this->obj_type_cache = [];
        $this->obj_tree_cache = [];
        $this->ac_cache = [];

        $this->ilOrgUnitPositionAccess = new ilOrgUnitPositionAccess($this);

        $this->ac_logger = ilLoggerFactory::getLogger('ac');
    }

    /**
     * @inheritdoc
     */
    public function storeAccessResult(
        string $a_permission,
        string $a_cmd,
        int $a_ref_id,
        bool $a_access_granted,
        ?int $a_user_id = null,
        ?ilAccessInfo $a_info = null
    ): void {
        if ($a_user_id === null) {
            $a_user_id = $this->user->getId();
        }
        if ($a_info === null) {
            $a_info = $this->current_info;
        }
        if ($this->cache) {
            $this->results[$a_ref_id][$a_permission][$a_cmd][$a_user_id] = [
                "granted" => $a_access_granted,
                "info" => $a_info,
                "prevent_db_cache" => $this->getPreventCachingLastResult()
            ];
            $this->current_result_element = [$a_access_granted, $a_ref_id, $a_permission, $a_cmd, $a_user_id];
            $this->last_result = $this->results[$a_ref_id][$a_permission][$a_cmd][$a_user_id];
            $this->last_info = $a_info;
        }
        // get new info object
        $this->current_info = new ilAccessInfo();
    }

    /**
     * @inheritdoc
     */
    public function setPreventCachingLastResult(bool $a_val): void
    {
        $this->prevent_caching_last_result = $a_val;
    }

    /**
     * @inheritdoc
     */
    public function getPreventCachingLastResult(): bool
    {
        return $this->prevent_caching_last_result;
    }

    /**
     * @inheritdoc
     */
    public function getStoredAccessResult(
        string $a_permission,
        string $a_cmd,
        int $a_ref_id,
        ?int $a_user_id = null
    ): array {
        if ($a_user_id === null) {
            $a_user_id = $this->user->getId();
        }
        if (isset($this->results[$a_ref_id][$a_permission][$a_cmd][$a_user_id])) {
            return $this->results[$a_ref_id][$a_permission][$a_cmd][$a_user_id];
        }
        return [];
    }

    /**
     * @inheritdoc
     */
    public function storeCache(): void
    {
        $query = "DELETE FROM acc_cache WHERE user_id = " . $this->db->quote($this->user->getId(), 'integer');
        $res = $this->db->manipulate($query);

        $this->db->insert('acc_cache', array(
            'user_id' => array('integer', $this->user->getId()),
            'time' => array('integer', time()),
            'result' => array('clob', serialize($this->results))
        ));
    }

    /**
     * @inheritdoc
     */
    public function readCache(int $a_secs = 0): bool
    {
        if ($a_secs > 0) {
            $query = "SELECT * FROM acc_cache WHERE user_id = " .
                $this->db->quote($this->user->getId(), 'integer');
            $set = $this->db->query($query);
            $rec = $set->fetchRow(ilDBConstants::FETCHMODE_ASSOC);
            if ((time() - $rec["time"]) < $a_secs) {
                $this->results = unserialize($rec["result"]);
                return true;
            }
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getResults(): array
    {
        return $this->results;
    }

    /**
     * @inheritdoc
     */
    public function setResults(array $a_results): void
    {
        $this->results = $a_results;
    }

    /**
     * @inheritdoc
     */
    public function addInfoItem(string $a_type, string $a_text, string $a_data = ""): void
    {
        $this->current_info->addInfoItem($a_type, $a_text, $a_data);
    }

    /**
     * @inheritdoc
     */
    public function checkAccess(
        string $a_permission,
        string $a_cmd,
        int $a_ref_id,
        string $a_type = "",
        ?int $a_obj_id = null,
        ?int $a_tree_id = null
    ): bool {
        return $this->checkAccessOfUser(
            $this->user->getId(),
            $a_permission,
            $a_cmd,
            $a_ref_id,
            $a_type,
            $a_obj_id,
            $a_tree_id
        );
    }

    /**
     * @inheritdoc
     */
    public function checkAccessOfUser(
        int $a_user_id,
        string $a_permission,
        string $a_cmd,
        int $a_ref_id,
        string $a_type = "",
        ?int $a_obj_id = 0,
        ?int $a_tree_id = 0
    ): bool {
        global $DIC;

        $ilBench = $DIC['ilBench'];
        $lng = $DIC['lng'];

        $this->setPreventCachingLastResult(false);    // for external db based caches

        $ilBench->start("AccessControl", "0400_clear_info");
        $this->current_info->clear();
        $ilBench->stop("AccessControl", "0400_clear_info");

        // get stored result (internal memory based cache)
        $cached = $this->doCacheCheck($a_permission, $a_cmd, $a_ref_id, $a_user_id);
        if ($cached["hit"]) {
            // Store access result
            if (!$cached["granted"]) {
                $this->current_info->addInfoItem(ilAccessInfo::IL_NO_PERMISSION, $lng->txt("status_no_permission"));
            }
            if ($cached["prevent_db_cache"]) {
                $this->setPreventCachingLastResult(true);    // should have been saved in previous call already
            }
            return $cached["granted"];
        }

        $ilBench->start("AccessControl", "0500_lookup_id_and_type");
        // get object id if not provided
        if ($a_obj_id == 0) {
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
            $this->current_info->addInfoItem(ilAccessInfo::IL_NO_PERMISSION, $lng->txt("status_no_permission"));
            $this->storeAccessResult($a_permission, $a_cmd, $a_ref_id, false, $a_user_id);
            return false;
        }

        // rbac check for current object
        if (!$this->doRBACCheck($a_permission, $a_cmd, $a_ref_id, $a_user_id, $a_type)) {
            $this->current_info->addInfoItem(ilAccessInfo::IL_NO_PERMISSION, $lng->txt("status_no_permission"));
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
            $this->current_info->addInfoItem(ilAccessInfo::IL_NO_PERMISSION, $lng->txt('status_no_permission'));
            $this->storeAccessResult($a_permission, $a_cmd, $a_ref_id, false, $a_user_id);
            return false;
        }

        // check read permission for all parents
        $par_check = $this->doPathCheck($a_permission, $a_cmd, $a_ref_id, $a_user_id);
        if (!$par_check) {
            $this->current_info->addInfoItem(ilAccessInfo::IL_NO_PERMISSION, $lng->txt("status_no_permission"));
            $this->storeAccessResult($a_permission, $a_cmd, $a_ref_id, false, $a_user_id);
            return false;
        }

        // condition check (currently only implemented for read permission)
        if (!$this->doConditionCheck($a_permission, $a_cmd, $a_ref_id, $a_user_id, $a_obj_id, $a_type)) {
            $this->current_info->addInfoItem(ilAccessInfo::IL_NO_PERMISSION, $lng->txt("status_no_permission"));
            $this->storeAccessResult($a_permission, $a_cmd, $a_ref_id, false, $a_user_id);
            $this->setPreventCachingLastResult(true);        // do not store this in db, since condition updates are not monitored
            return false;
        }

        // object type specific check
        if (!$this->doStatusCheck($a_permission, $a_cmd, $a_ref_id, $a_user_id, $a_obj_id, $a_type)) {
            $this->current_info->addInfoItem(ilAccessInfo::IL_NO_PERMISSION, $lng->txt("status_no_permission"));
            $this->storeAccessResult($a_permission, $a_cmd, $a_ref_id, false, $a_user_id);
            $this->setPreventCachingLastResult(true);        // do not store this in db, since status updates are not monitored
            return false;
        }

        // all checks passed
        $this->storeAccessResult($a_permission, $a_cmd, $a_ref_id, true, $a_user_id);
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getInfo(): array
    {
        return is_object($this->last_info) ? $this->last_info->getInfoItems() : array();
    }

    /**
     * @inheritdoc
     */
    public function getResultLast(): array
    {
        return $this->last_result;
    }

    /**
     * @inheritdoc
     */
    public function getResultAll(int $a_ref_id = 0): array
    {
        if ($a_ref_id == "") {
            return $this->results;
        }

        return $this->results[$a_ref_id];
    }

    /**
     * @inheritdoc
     */
    public function doCacheCheck(string $a_permission, string $a_cmd, int $a_ref_id, int $a_user_id): array
    {
        $stored_access = $this->getStoredAccessResult($a_permission, $a_cmd, $a_ref_id, $a_user_id);

        //var_dump($stored_access);
        if ($stored_access !== []) {
            if (isset($stored_access['info']) && $stored_access['info'] instanceof ilAccessInfo) {
                $this->current_info = $stored_access["info"];
            }
            //var_dump("cache-treffer:");
            return [
                "hit" => true,
                "granted" => $stored_access["granted"],
                "prevent_db_cache" => $stored_access["prevent_db_cache"]
            ];
        }

        // not in cache
        return [
            "hit" => false,
            "granted" => false,
            "prevent_db_cache" => false
        ];
    }

    /**
     * @inheritdoc
     */
    public function doTreeCheck(string $a_permission, string $a_cmd, int $a_ref_id, int $a_user_id): bool
    {
        // Get stored result
        $tree_cache_key = $a_user_id . ':' . $a_ref_id;
        if (array_key_exists($tree_cache_key, $this->obj_tree_cache)) {
            // Store access result
            if (!$this->obj_tree_cache[$tree_cache_key]) {
                $this->current_info->addInfoItem(
                    ilAccessInfo::IL_NO_PERMISSION,
                    $this->language->txt("status_no_permission")
                );
            }
            $this->storeAccessResult(
                $a_permission,
                $a_cmd,
                $a_ref_id,
                $this->obj_tree_cache[$tree_cache_key],
                $a_user_id
            );

            return $this->obj_tree_cache[$tree_cache_key];
        }

        if (!$this->repositoryTree->isInTree($a_ref_id) || $this->repositoryTree->isDeleted($a_ref_id)) {
            // Store negative access results
            // Store in tree cache
            // Note, we only store up to 1000 results to avoid memory overflow.
            if (count($this->obj_tree_cache) < self::MAX_CACHE_SIZE) {
                $this->obj_tree_cache[$tree_cache_key] = false;
            }

            // Store in result cache
            $this->current_info->addInfoItem(ilAccessInfo::IL_DELETED, $this->language->txt("object_deleted"));
            $this->storeAccessResult($a_permission, $a_cmd, $a_ref_id, false, $a_user_id);
            return false;
        }

        // Store positive access result.
        // Store in tree cache
        // Note, we only store up to 1000 results to avoid memory overflow.
        if (count($this->obj_tree_cache) < self::MAX_CACHE_SIZE) {
            $this->obj_tree_cache[$tree_cache_key] = true;
        }
        // Store in result cache
        $this->storeAccessResult($a_permission, $a_cmd, $a_ref_id, true, $a_user_id);
        return true;
    }

    /**
     * @inheritdoc
     */
    public function doRBACCheck(
        string $a_permission,
        string $a_cmd,
        int $a_ref_id,
        int $a_user_id,
        string $a_type
    ): bool {
        if ($a_permission == "") {
            $message = sprintf(
                '%s::doRBACCheck(): No operations given! $a_ref_id: %s',
                get_class($this),
                $a_ref_id
            );
            $this->ac_logger->error($message);
            throw new ilPermissionException($message);
        }

        if (isset($this->stored_rbac_access[$a_user_id . "-" . $a_permission . "-" . $a_ref_id])) {
            $access = $this->stored_rbac_access[$a_user_id . "-" . $a_permission . "-" . $a_ref_id];
        } else {
            $access = $this->rbacsystem->checkAccessOfUser($a_user_id, $a_permission, $a_ref_id, $a_type);
            if (!is_array($this->stored_rbac_access) || count($this->stored_rbac_access) < self::MAX_CACHE_SIZE) {
                if ($a_permission != "create") {
                    $this->stored_rbac_access[$a_user_id . "-" . $a_permission . "-" . $a_ref_id] = $access;
                }
            }
        }
        // Store in result cache
        if (!$access) {
            $this->current_info->addInfoItem(
                ilAccessInfo::IL_NO_PERMISSION,
                $this->language->txt("status_no_permission")
            );
        }
        if ($a_permission != "create") {
            $this->storeAccessResult($a_permission, $a_cmd, $a_ref_id, true, $a_user_id);
        }
        return $access;
    }

    /**
     * @inheritdoc
     */
    public function doPathCheck(
        string $a_permission,
        string $a_cmd,
        int $a_ref_id,
        int $a_user_id,
        bool $a_all = false
    ): bool {
        $path = $this->repositoryTree->getPathId($a_ref_id);
        foreach ($path as $id) {
            if ($a_ref_id === $id) {
                continue;
            }
            $access = $this->checkAccessOfUser($a_user_id, "read", "info", $id);
            if ($access == false) {
                $this->current_info->addInfoItem(
                    ilAccessInfo::IL_NO_PARENT_ACCESS,
                    $this->language->txt("no_parent_access"),
                    (string) $id
                );
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
    public function doActivationCheck(
        string $a_permission,
        string $a_cmd,
        int $a_ref_id,
        int $a_user_id,
        int $a_obj_id,
        string $a_type
    ): bool {
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
        if ($a_user_id === $this->user->getId()) {
            // #10905 - activate parent container ONLY
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
            $this->objDefinition->supportsOfflineHandling($a_type) &&
            ilObject::lookupOfflineStatus($a_obj_id)
        ) {
            $this->ac_cache[$cache_perm][$a_ref_id][$a_user_id] = false;
            return false;
        }
        $item_data = ilObjectActivation::getItem($a_ref_id);
        // if activation isn't enabled
        if ($item_data === null || (is_array($item_data) && count($item_data) == 0) ||
            $item_data['timing_type'] != ilObjectActivation::TIMINGS_ACTIVATION) {
            $this->ac_cache[$cache_perm][$a_ref_id][$a_user_id] = true;
            return true;
        }
        // if within activation time
        if (($item_data['timing_start'] == 0 || time() >= $item_data['timing_start']) and
            ($item_data['timing_end'] == 0 || time() <= $item_data['timing_end'])) {
            $this->ac_cache[$cache_perm][$a_ref_id][$a_user_id] = true;
            return true;
        }

        // if user has write permission
        if ($this->checkAccessOfUser($a_user_id, "write", "", $a_ref_id)) {
            $this->ac_cache[$cache_perm][$a_ref_id][$a_user_id] = true;
            return true;
        }

        // if current permission is visible and visible is set in activation
        if ($a_permission == 'visible' && $item_data['visible']) {
            $this->ac_cache[$cache_perm][$a_ref_id][$a_user_id] = true;
            return true;
        }

        // learning progress must be readable, regardless of the activation
        if ($a_permission == 'read_learning_progress') {
            $this->ac_cache[$cache_perm][$a_ref_id][$a_user_id] = true;
            return true;
        }
        // no access
        $this->ac_cache[$cache_perm][$a_ref_id][$a_user_id] = false;
        return false;
    }

    /**
     * @inheritdoc
     */
    public function doConditionCheck(
        string $a_permission,
        string $a_cmd,
        int $a_ref_id,
        int $a_user_id,
        int $a_obj_id,
        string $a_type
    ): bool {
        if (
            ($a_permission == 'visible') &&
            !$this->checkAccessOfUser($a_user_id, "write", "", $a_ref_id, $a_type, $a_obj_id)
        ) {
            if (ilConditionHandler::lookupEffectiveHiddenStatusByTarget($a_ref_id)) {
                if (!ilConditionHandler::_checkAllConditionsOfTarget($a_ref_id, $a_obj_id, $a_type, $a_user_id)) {
                    $conditions = ilConditionHandler::_getEffectiveConditionsOfTarget($a_ref_id, $a_obj_id, $a_type);
                    foreach ($conditions as $condition) {
                        $this->current_info->addInfoItem(
                            ilAccessInfo::IL_MISSING_PRECONDITION,
                            $this->language->txt("missing_precondition") . ": " .
                            ilObject::_lookupTitle($condition["trigger_obj_id"]) . " " .
                            $this->language->txt("condition_" . $condition["operator"]) . " " .
                            $condition["value"],
                            serialize($condition)
                        );
                    }
                    return false;
                }
            }
        }

        if (($a_permission == "read" or $a_permission == 'join') &&
            !$this->checkAccessOfUser($a_user_id, "write", "", $a_ref_id, $a_type, $a_obj_id)) {
            if (!ilConditionHandler::_checkAllConditionsOfTarget($a_ref_id, $a_obj_id, $a_type, $a_user_id)) {
                $conditions = ilConditionHandler::_getEffectiveConditionsOfTarget($a_ref_id, $a_obj_id, $a_type);
                foreach ($conditions as $condition) {
                    $this->current_info->addInfoItem(
                        ilAccessInfo::IL_MISSING_PRECONDITION,
                        $this->language->txt("missing_precondition") . ": " .
                        ilObject::_lookupTitle($condition["trigger_obj_id"]) . " " .
                        $this->language->txt("condition_" . $condition["operator"]) . " " .
                        $condition["value"],
                        serialize($condition)
                    );
                }
                return false;
            }
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function doStatusCheck(
        string $a_permission,
        string $a_cmd,
        int $a_ref_id,
        int $a_user_id,
        int $a_obj_id,
        string $a_type
    ): bool {
        // check for a deactivated plugin
        if ($this->objDefinition->isPluginTypeName($a_type) && !$this->objDefinition->isPlugin($a_type)) {
            return false;
        }
        if (!$a_type) {
            return false;
        }

        $class = $this->objDefinition->getClassName($a_type);
        $location = $this->objDefinition->getLocation($a_type);
        $full_class = "ilObj" . $class . "Access";

        // use autoloader for standard objects
        if ($this->objDefinition->isPluginTypeName($a_type)) {
            /** @noRector */
            include_once($location . "/class." . $full_class . ".php");
        }

        if ($class == "") {
            $this->ac_logger->error("Cannot find class for object type $a_type, obj id $a_obj_id, ref id $a_ref_id. Abort status check.");
            return false;
        }

        $full_class = new $full_class();

        $obj_access = call_user_func(
            array($full_class, "_checkAccess"),
            $a_cmd,
            $a_permission,
            $a_ref_id,
            $a_obj_id,
            $a_user_id
        );
        if ($obj_access !== true) {
            //Note: We must not add an info item here, because one is going
            //      to be added by the user function we just called a few
            //      lines above.
            $this->storeAccessResult($a_permission, $a_cmd, $a_ref_id, false, $a_user_id);
            return false;
        }
        $this->storeAccessResult($a_permission, $a_cmd, $a_ref_id, true, $a_user_id);
        return true;
    }

    /**
     * @inheritdoc
     */
    public function clear(): void
    {
        $this->results = array();
        $this->last_result = [];
        $this->current_info = new ilAccessInfo();
        $this->stored_rbac_access = [];
    }

    /**
     * @inheritdoc
     * @todo check for valid properties
     */
    public function enable(string $a_str, bool $a_bool): void
    {
        $this->$a_str = $a_bool;
    }



    //
    // OrgUnit Positions
    //

    /**
     * @inheritdoc
     */
    public function filterUserIdsForCurrentUsersPositionsAndPermission(array $user_ids, string $permission): array
    {
        return $this->ilOrgUnitPositionAccess->filterUserIdsForCurrentUsersPositionsAndPermission(
            $user_ids,
            $permission
        );
    }

    /**
     * @inheritdoc
     */
    public function filterUserIdsForUsersPositionsAndPermission(array $user_ids, int $for_user_id, string $permission): array
    {
        return $this->ilOrgUnitPositionAccess->filterUserIdsForUsersPositionsAndPermission(
            $user_ids,
            $for_user_id,
            $permission
        );
    }

    /**
     * @inheritdoc
     */
    public function isCurrentUserBasedOnPositionsAllowedTo(string $permission, array $on_user_ids): bool
    {
        return $this->ilOrgUnitPositionAccess->isCurrentUserBasedOnPositionsAllowedTo($permission, $on_user_ids);
    }

    /**
     * @inheritdoc
     */
    public function isUserBasedOnPositionsAllowedTo(int $which_user_id, string $permission, array $on_user_ids): bool
    {
        return $this->ilOrgUnitPositionAccess->isUserBasedOnPositionsAllowedTo(
            $which_user_id,
            $permission,
            $on_user_ids
        );
    }

    /**
     * @inheritdoc
     */
    public function checkPositionAccess(string $pos_perm, int $ref_id): bool
    {
        return $this->ilOrgUnitPositionAccess->checkPositionAccess($pos_perm, $ref_id);
    }

    /**
     * @inheritdoc
     */
    public function checkRbacOrPositionPermissionAccess(string $rbac_perm, string $pos_perm, int $ref_id): bool
    {
        return $this->ilOrgUnitPositionAccess->checkRbacOrPositionPermissionAccess($rbac_perm, $pos_perm, $ref_id);
    }

    /**
     * @inheritdoc
     */
    public function filterUserIdsByPositionOfCurrentUser(string $pos_perm, int $ref_id, array $user_ids): array
    {
        return $this->ilOrgUnitPositionAccess->filterUserIdsByPositionOfCurrentUser($pos_perm, $ref_id, $user_ids);
    }

    /**
     * @inheritdoc
     */
    public function filterUserIdsByPositionOfUser(int $user_id, string $pos_perm, int $ref_id, array $user_ids): array
    {
        return $this->ilOrgUnitPositionAccess->filterUserIdsByPositionOfUser($user_id, $pos_perm, $ref_id, $user_ids);
    }

    /**
     * @inheritdoc
     */
    public function filterUserIdsByRbacOrPositionOfCurrentUser(string $rbac_perm, string $pos_perm, int $ref_id, array $user_ids): array
    {
        return $this->ilOrgUnitPositionAccess->filterUserIdsByRbacOrPositionOfCurrentUser(
            $rbac_perm,
            $pos_perm,
            $ref_id,
            $user_ids
        );
    }

    /**
     * @inheritdoc
     */
    public function hasCurrentUserAnyPositionAccess(int $ref_id): bool
    {
        return $this->ilOrgUnitPositionAccess->hasCurrentUserAnyPositionAccess($ref_id);
    }

    /**
     * @inheritdoc
     */
    public function hasUserRBACorAnyPositionAccess(string $rbac_perm, int $ref_id): bool
    {
        return $this->ilOrgUnitPositionAccess->hasUserRBACorAnyPositionAccess($rbac_perm, $ref_id);
    }
}
