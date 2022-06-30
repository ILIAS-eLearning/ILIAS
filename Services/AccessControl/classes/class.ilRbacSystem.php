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

use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Refinery\Factory;

/**
 * class ilRbacSystem
 * system function like checkAccess, addActiveRole ...
 * Supporting system functions are required for session management and in making access control decisions.
 * This class depends on the session since we offer the possiblility to add or delete active roles during one session.
 * @author  Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 * @ingroup ServicesAccessControl
 */
class ilRbacSystem
{
    private const MAX_CACHE_ENTRIES = 1000;

    protected static ?ilRbacSystem $instance = null;

    protected array $mem_view = [];

    protected static array $user_role_cache = [];

    // Cache accesses to RBAC PA
    private static array $_paCache = [];

    // Cache outcomes of calls to checkAccessOfuser
    private static array $_checkAccessOfUserCache = [];

    protected ilObjUser $user;
    protected ilDBInterface $db;
    protected ilRbacReview $review;
    protected ilObjectDataCache $objectDataCache;
    protected ilTree $tree;
    protected GlobalHttpState $http;
    protected Factory $refinery;

    /**
     * Constructor
     */
    protected function __construct()
    {
        global $DIC;

        $this->user = $DIC->user();
        $this->db = $DIC->database();
        $this->review = $DIC->rbac()->review();
        $this->objectDataCache = $DIC['ilObjDataCache'];
        $this->tree = $DIC->repositoryTree();
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
    }

    public static function getInstance() : ilRbacSystem
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Reset internal caches
     */
    public static function resetCaches() : void
    {
        self::$user_role_cache = [];
        self::$_paCache = [];
        self::$_checkAccessOfUserCache = [];
    }

    /**
     *  checkAccess represents the main method of the RBAC-system in ILIAS3 developers want to use
     *  With this method you check the permissions a use may have due to its roles
     *  on an specific object.
     *  The first parameter are the operation(s) the user must have
     *  The second & third parameter specifies the object where the operation(s) may apply to
     *  The last parameter is only required, if you ask for the 'create' operation. Here you specify
     *  the object type which you want to create.
     *  example: $rbacSystem->checkAccess("visible,read",23);
     *  Here you ask if the user is allowed to see ('visible') and access the object by reading it ('read').
     *  The reference_id is 23 in the tree structure.
     * @access    public
     * @param string        one or more operations, separated by commas (i.e.: visible,read,join)
     * @param int        the child_id in tree (usually a reference_id, no object_id !!)
     * @param string        the type definition abbreviation (i.e.: frm,grp,crs)
     * @return    bool        returns true if ALL passed operations are given, otherwise false
     */
    public function checkAccess(string $a_operations, int $a_ref_id, string $a_type = "") : bool
    {
        return $this->checkAccessOfUser($this->user->getId(), $a_operations, $a_ref_id, $a_type);
    }

    public function checkAccessOfUser(int $a_user_id, string $a_operations, int $a_ref_id, string $a_type = "") : bool
    {
        // Create the user cache key
        $cacheKey = $a_user_id . ':' . $a_operations . ':' . $a_ref_id . ':' . $a_type;

        // Create the cache if it does not yet exist
        if (!is_array(self::$_checkAccessOfUserCache)) {
            self::$_checkAccessOfUserCache = [];
        }
        // Try to return result from cache
        if (array_key_exists($cacheKey, self::$_checkAccessOfUserCache)) {
            return self::$_checkAccessOfUserCache[$cacheKey];
        }

        // Check For owner
        // Owners do always have full access to their objects
        // Excluded are some of the permissions like create, perm, learning progress.
        // This method call return all operations that are NOT granted by the owner status
        if (!$a_operations = $this->filterOwnerPermissions($a_user_id, $a_operations, $a_ref_id)) {
            // Store positive outcome in cache.
            // Note: we only cache up to 1000 results to avoid memory overflows
            if (count(self::$_checkAccessOfUserCache) < self::MAX_CACHE_ENTRIES) {
                self::$_checkAccessOfUserCache[$cacheKey] = true;
            }
            return true;
        }

        // get roles using role cache
        $roles = $this->fetchAssignedRoles($a_user_id, $a_ref_id);

        // exclude system role from rbac
        if (in_array(SYSTEM_ROLE_ID, $roles)) {
            // Store positive outcome in cache.
            // Note: we only cache up to 1000 results to avoid memory overflows
            if (count(self::$_checkAccessOfUserCache) < self::MAX_CACHE_ENTRIES) {
                self::$_checkAccessOfUserCache[$cacheKey] = true;
            }
            return true;
        }

        // Create the PA cache if it does not exist yet
        $paCacheKey = $a_user_id . ':' . $a_ref_id;
        if (!is_array(self::$_paCache)) {
            self::$_paCache = array();
        }

        if (array_key_exists($paCacheKey, self::$_paCache)) {
            // Return result from PA cache
            $ops = self::$_paCache[$paCacheKey];
        } else {
            // Data is not in PA cache, perform database query
            $q = "SELECT * FROM rbac_pa " .
                "WHERE ref_id = " . $this->db->quote($a_ref_id, 'integer');

            $r = $this->db->query($q);

            $ops = [];
            while ($row = $r->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                if (in_array((int) $row->rol_id, $roles)) {
                    $ops = array_merge($ops, unserialize(stripslashes($row->ops_id)));
                }
            }
            // Cache up to 1000 entries in the PA cache
            if (count(self::$_paCache) < self::MAX_CACHE_ENTRIES) {
                self::$_paCache[$paCacheKey] = $ops;
            }
        }

        $operations = explode(",", $a_operations);
        foreach ($operations as $operation) {
            if ($operation == "create") {
                if (empty($a_type)) {
                    throw new DomainException(
                        'checkAccess(): ' . "Expect a type definition for checking a 'create' permission"
                    );
                }
                $ops_id = ilRbacReview::_getOperationIdByName($operation . "_" . $a_type);
            } else {
                $ops_id = ilRbacReview::_getOperationIdByName($operation);
            }
            if (!in_array($ops_id, (array) $ops)) {
                if (count(self::$_checkAccessOfUserCache) < self::MAX_CACHE_ENTRIES) {
                    self::$_checkAccessOfUserCache[$cacheKey] = false;
                }
                return false;
            }
        }

        // Store positive outcome in cache.
        // Note: we only cache up to 1000 results to avoid memory overflows
        if (count(self::$_checkAccessOfUserCache) < self::MAX_CACHE_ENTRIES) {
            //$ilLog->write('PERMISSION: '.$a_ref_id.' -> '.$ops_id.' granted');
            self::$_checkAccessOfUserCache[$cacheKey] = true;
        }
        return true;
    }

    public function preloadRbacPaCache(array $a_ref_ids, int $a_user_id) : void
    {
        $ref_ids = [];
        $roles = $ops = [];
        foreach ($a_ref_ids as $ref_id) {
            if (!isset(self::$_paCache[$a_user_id . ":" . $ref_id])) {
                $roles[$ref_id] = $this->fetchAssignedRoles($a_user_id, $ref_id);
                $ops[$ref_id] = [];
                $ref_ids[] = $ref_id;
            }
        }

        if ($ref_ids !== []) {
            // Data is not in PA cache, perform database query
            $q = "SELECT * FROM rbac_pa " .
                "WHERE " . $this->db->in("ref_id", $ref_ids, false, "integer");

            $r = $this->db->query($q);

            while ($row = $r->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                if (in_array($row->rol_id, $roles[(int) $row->ref_id])) {
                    $ops[(int) $row->ref_id] = array_merge(
                        $ops[(int) $row->ref_id],
                        unserialize(stripslashes($row->ops_id))
                    );
                }
            }
            foreach ($a_ref_ids as $ref_id) {
                // #11313
                if (!isset(self::$_paCache[$a_user_id . ":" . $ref_id])) {
                    self::$_paCache[$a_user_id . ":" . $ref_id] = $ops[$ref_id];
                }
            }
        }
    }

    /**
     * check if a specific role has the permission '$a_operation' of an object
     */
    public function checkPermission(int $a_ref_id, int $a_rol_id, string $a_operation) : bool
    {
        $ops = [];
        $query = 'SELECT ops_id FROM rbac_operations ' .
            'WHERE operation = ' . $this->db->quote($a_operation, 'text');
        $res = $this->db->query($query);
        $ops_id = 0;
        while ($row = $this->db->fetchObject($res)) {
            $ops_id = (int) $row->ops_id;
        }

        $query = "SELECT * FROM rbac_pa " .
            "WHERE rol_id = " . $this->db->quote($a_rol_id, 'integer') . " " .
            "AND ref_id = " . $this->db->quote($a_ref_id, 'integer') . " ";
        $res = $this->db->query($query);

        while ($row = $this->db->fetchObject($res)) {
            $ops = array_merge($ops, unserialize($row->ops_id));
        }
        return in_array($ops_id, $ops);
    }

    protected function filterOwnerPermissions(int $a_user_id, string $a_operations, int $a_ref_id) : string
    {
        // member view constraints
        if (($this->mem_view['active'] ?? null) and $a_user_id == $this->user->getId()) {
            if (in_array($a_ref_id, $this->mem_view['items'])) {
                return $a_operations;
            }
        }

        if ($a_user_id != $this->objectDataCache->lookupOwner($this->objectDataCache->lookupObjId($a_ref_id))) {
            return $a_operations;
        }
        // Is owner
        $new_ops = '';
        foreach (explode(",", $a_operations) as $operation) {
            if ($operation != 'cat_administrate_users' &&
                $operation != 'edit_permission' &&
                $operation != 'edit_learning_progress' &&
                $operation != 'read_learning_progress' &&
                !preg_match('/^create/', $operation) &&
                $operation != 'read_outcomes'
            ) {
                continue;
            }
            if (!strlen($new_ops)) {
                $new_ops = $operation;
            } else {
                $new_ops .= (',' . $operation);
            }
        }
        return $new_ops;
    }

    /**
     * Fetch assigned roles
     * This method caches the assigned roles per user
     */
    private function fetchAssignedRoles(int $a_usr_id, int $a_ref_id) : array
    {
        // Member view constraints
        if ($this->mem_view['active'] && $a_usr_id == $this->user->getId()) {
            // check if current ref_id is subitem of active container
            if (in_array($a_ref_id, $this->mem_view['items']) && $this->mem_view['role']) {
                // Return default member role
                return [$this->mem_view['role']];
            }
        }

        if (isset(self::$user_role_cache[$a_usr_id]) and is_array(self::$user_role_cache)) {
            return self::$user_role_cache[$a_usr_id];
        }
        return self::$user_role_cache[$a_usr_id] = $this->review->assignedRoles($a_usr_id);
    }

    public function initMemberView() : void
    {
        $settings = ilMemberViewSettings::getInstance();
        $member_view_activation = null;
        if ($this->http->wrapper()->query()->has('mv')) {
            $member_view_activation = $this->http->wrapper()->query()->retrieve(
                'mv',
                $this->refinery->kindlyTo()->bool()
            );
        }
        $ref_id = 0;
        if ($this->http->wrapper()->query()->has('ref_id')) {
            $ref_id = $this->http->wrapper()->query()->retrieve(
                'ref_id',
                $this->refinery->kindlyTo()->int()
            );
        }
        if ($member_view_activation === true) {
            if ($this->checkAccess('write', $ref_id)) {
                $settings->toggleActivation($ref_id, true);
                self::resetCaches();
            }
        }
        if ($member_view_activation === false) {
            $settings->toggleActivation($ref_id, false);
        }
        if (!$settings->isActive()) {
            $this->mem_view['active'] = false;
            $this->mem_view['items'] = [];
            $this->mem_view['role'] = 0;
        } else {
            $this->mem_view['active'] = true;
            $this->mem_view['items'] = $this->tree->getSubTreeIds($settings->getContainer());
            $this->mem_view['items'] = array_merge($this->mem_view['items'], array($settings->getContainer()));
            $this->mem_view['role'] = ilParticipants::getDefaultMemberRole($settings->getContainer());
        }
    }

    public function addTemporaryRole(int $a_usr_id, int $a_role_id) : void
    {
        if (!in_array($a_role_id, self::$user_role_cache[$a_usr_id])) {
            self::$user_role_cache[$a_usr_id][] = $a_role_id;
        }
    }

    public function resetPACache(int $a_usr_id, int $a_ref_id) : void
    {
        $paCacheKey = $a_usr_id . ':' . $a_ref_id;
        unset(self::$_paCache[$paCacheKey]);
    }
} // END class.RbacSystem
