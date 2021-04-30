<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
* class ilRbacSystem
* system function like checkAccess, addActiveRole ...
* Supporting system functions are required for session management and in making access control decisions.
* This class depends on the session since we offer the possiblility to add or delete active roles during one session.
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
* @ingroup ServicesAccessControl
*/
class ilRbacSystem
{
    protected static $instance = null;
    
    protected $mem_view;
    
    protected static $user_role_cache = array();
    public $ilias;

    // Cache accesses to RBAC PA
    private static $_paCache = null;

    // Cache outcomes of calls to checkAccessOfuser
    private static $_checkAccessOfUserCache = null;

    /**
    * Constructor
    * @access	public
    */
    protected function __construct()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $ilErr = $DIC['ilErr'];
        $ilias = $DIC['ilias'];

        $this->ilias = &$ilias;

        // set db & error handler
        (isset($ilDB)) ? $this->ilDB = &$ilDB : $this->ilDB = &$ilias->db;
        
        if (!isset($ilErr)) {
            $ilErr = new ilErrorHandling();
            $ilErr->setErrorHandling(PEAR_ERROR_CALLBACK, array($ilErr,'errorHandler'));
        } else {
            $this->ilErr = &$ilErr;
        }
    }
    
    public static function getInstance()
    {
        if (self::$instance) {
            return self::$instance;
        }
        return self::$instance = new ilRbacSystem();
    }
    
    /**
     * Reset internal caches
     */
    public static function resetCaches()
    {
        self::$user_role_cache = array();
        self::$_paCache = null;
        self::$_checkAccessOfUserCache = null;
    }
    
    /**
    * checkAccess represents the main method of the RBAC-system in ILIAS3 developers want to use
    *  With this method you check the permissions a use may have due to its roles
    *  on an specific object.
    *  The first parameter are the operation(s) the user must have
    *  The second & third parameter specifies the object where the operation(s) may apply to
    *  The last parameter is only required, if you ask for the 'create' operation. Here you specify
    *  the object type which you want to create.
    *
    *  example: $rbacSystem->checkAccess("visible,read",23);
    *  Here you ask if the user is allowed to see ('visible') and access the object by reading it ('read').
    *  The reference_id is 23 in the tree structure.
    *
    * @access	public
    * @param	string		one or more operations, separated by commas (i.e.: visible,read,join)
    * @param	integer		the child_id in tree (usually a reference_id, no object_id !!)
    * @param	string		the type definition abbreviation (i.e.: frm,grp,crs)
    * @return	boolean		returns true if ALL passed operations are given, otherwise false
    */
    public function checkAccess($a_operations, $a_ref_id, $a_type = "")
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        $ilBench = $DIC['ilBench'];
        
        $ilBench->start("RBAC", "system_checkAccess");

        $result = $this->checkAccessOfUser($ilUser->getId(), $a_operations, $a_ref_id, $a_type);

        $ilBench->stop("RBAC", "system_checkAccess");
        
        return $result;
    }
    
    public function checkAccessOfUser($a_user_id, $a_operations, $a_ref_id, $a_type = "")
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        $rbacreview = $DIC['rbacreview'];
        $ilObjDataCache = $DIC['ilObjDataCache'];
        $ilDB = $DIC['ilDB'];
        $ilLog = $DIC['ilLog'];

        // Create the user cache key
        $cacheKey = $a_user_id . ':' . $a_operations . ':' . $a_ref_id . ':' . $a_type;

        // Create the cache if it does not yet exist
        if (!is_array(self::$_checkAccessOfUserCache)) {
            self::$_checkAccessOfUserCache = array();
        }
        
        // Try to return result from cache
        if (array_key_exists($cacheKey, self::$_checkAccessOfUserCache)) {
            return self::$_checkAccessOfUserCache[$cacheKey];
        }


        // DISABLED
        // Check For owner
        // Owners do always have full access to their objects
        // Excluded are the permissions create and perm
        // This method call return all operations that are NOT granted by the owner status
        if (!$a_operations = $this->__filterOwnerPermissions($a_user_id, $a_operations, $a_ref_id)) {
            // Store positive outcome in cache.
            // Note: we only cache up to 1000 results to avoid memory overflows
            if (count(self::$_checkAccessOfUserCache) < 1000) {
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
            if (count(self::$_checkAccessOfUserCache) < 1000) {
                self::$_checkAccessOfUserCache[$cacheKey] = true;
            }
            return true;
        }

        if (!isset($a_operations) or !isset($a_ref_id)) {
            $GLOBALS['DIC']['ilLog']->logStack();
            $this->ilErr->raiseError(get_class($this) . "::checkAccess(): Missing parameter! " .
                            "ref_id: " . $a_ref_id . " operations: " . $a_operations, $this->ilErr->WARNING);
        }

        if (!is_string($a_operations)) {
            $GLOBALS['DIC']['ilLog']->logStack();
            $this->ilErr->raiseError(get_class($this) . "::checkAccess(): Wrong datatype for operations!", $this->ilErr->WARNING);
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
                     "WHERE ref_id = " . $ilDB->quote($a_ref_id, 'integer');

            $r = $this->ilDB->query($q);

            $ops = array();

            while ($row = $r->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                if (in_array($row->rol_id, $roles)) {
                    $ops = array_merge($ops, unserialize(stripslashes($row->ops_id)));
                }
            }
            // Cache up to 1000 entries in the PA cache
            if (count(self::$_paCache) < 1000) {
                self::$_paCache[$paCacheKey] = $ops;
            }
        }

        $operations = explode(",", $a_operations);
        foreach ($operations as $operation) {
            if ($operation == "create") {
                if (empty($a_type)) {
                    $this->ilErr->raiseError(
                        get_class($this) . "::CheckAccess(): Expect a type definition for checking a 'create' permission",
                        $this->ilErr->WARNING
                    );
                }

                $ops_id = ilRbacReview::_getOperationIdByName($operation . "_" . $a_type);
            } else {
                $ops_id = ilRbacReview::_getOperationIdByName($operation);
            }
            if (!in_array($ops_id, (array) $ops)) {
                //$ilLog->write('PERMISSION: '.$a_ref_id.' -> '.$a_ops_id.' failed');
                // Store negative outcome in cache.
                // Note: we only cache up to 1000 results to avoid memory overflows
                if (count(self::$_checkAccessOfUserCache) < 1000) {
                    self::$_checkAccessOfUserCache[$cacheKey] = false;
                }
                return false;
            }
        }

        // Store positive outcome in cache.
        // Note: we only cache up to 1000 results to avoid memory overflows
        if (count(self::$_checkAccessOfUserCache) < 1000) {
            //$ilLog->write('PERMISSION: '.$a_ref_id.' -> '.$ops_id.' granted');
            self::$_checkAccessOfUserCache[$cacheKey] = true;
        }
        return true;
    }

    /**
     * Preload rbac_pa cache
     *
     * @param
     * @return
     */
    public function preloadRbacPaCache($a_ref_ids, $a_user_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        if (!is_array($a_ref_ids)) {
            return;
        }

        $ref_ids = array();
        foreach ($a_ref_ids as $ref_id) {
            if (!isset(self::$_paCache[$a_user_id . ":" . $ref_id])) {
                $roles[$ref_id] = $this->fetchAssignedRoles($a_user_id, $ref_id);
                $ops[$ref_id] = array();
                $ref_ids[] = $ref_id;
            }
        }

        if (count($ref_ids) > 0) {

            // Data is not in PA cache, perform database query
            $q = "SELECT * FROM rbac_pa " .
                "WHERE " . $ilDB->in("ref_id", $ref_ids, false, "integer");

            $r = $this->ilDB->query($q);

            while ($row = $r->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                if (in_array($row->rol_id, $roles[$row->ref_id])) {
                    $ops[$row->ref_id] = array_merge(
                        $ops[$row->ref_id],
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
    * @access	public
    * @param	integer		reference id of object
    * @param	integer		role id
    * @param	string		the permission to check
    * @return	boolean
    */
    public function checkPermission($a_ref_id, $a_rol_id, $a_operation)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $ops = array();

        $query = 'SELECT ops_id FROM rbac_operations ' .
            'WHERE operation = ' . $ilDB->quote($a_operation, 'text');
        $res = $ilDB->query($query);
        while ($row = $ilDB->fetchObject($res)) {
            $ops_id = $row->ops_id;
        }
    
        $query = "SELECT * FROM rbac_pa " .
             "WHERE rol_id = " . $ilDB->quote($a_rol_id, 'integer') . " " .
             "AND ref_id = " . $ilDB->quote($a_ref_id, 'integer') . " ";
        $res = $ilDB->query($query);

        while ($row = $ilDB->fetchObject($res)) {
            $ops = array_merge($ops, unserialize($row->ops_id));
        }
        return in_array($ops_id, $ops);
    }

    public function __filterOwnerPermissions($a_user_id, $a_operations, $a_ref_id)
    {
        global $DIC;

        $ilObjDataCache = $DIC['ilObjDataCache'];
        $ilUser = $DIC['ilUser'];

        // member view constraints
        if ($this->mem_view['active'] and $a_user_id == $ilUser->getId()) {
            if (in_array($a_ref_id, $this->mem_view['items'])) {
                return $a_operations;
            }
        }

        if ($a_user_id != $ilObjDataCache->lookupOwner($ilObjDataCache->lookupObjId($a_ref_id))) {
            return $a_operations;
        }
        // Is owner
        $new_ops = false;
        foreach (explode(",", $a_operations) as $operation) {
            if ($operation != 'cat_administrate_users' and $operation != 'edit_permission' and $operation != 'edit_learning_progress' and $operation != 'read_learning_progress' and !preg_match('/^create/', $operation) and $operation != 'read_outcomes') {
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
     *
     * @access private
     * @param int user id
     *
     */
    private function fetchAssignedRoles($a_usr_id, $a_ref_id)
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        $rbacreview = $DIC['rbacreview'];
        
        // Member view constraints
        if ($this->mem_view['active'] and $a_usr_id == $ilUser->getId()) {
            // check if current ref_id is subitem of active container
            if (in_array($a_ref_id, $this->mem_view['items']) and $this->mem_view['role']) {
                // Return default member role
                return array($this->mem_view['role']);
            }
        }

        if (isset(self::$user_role_cache[$a_usr_id]) and is_array(self::$user_role_cache)) {
            return self::$user_role_cache[$a_usr_id];
        }



        return self::$user_role_cache[$a_usr_id] = $rbacreview->assignedRoles($a_usr_id);
    }
    
    /**
     * Init member view
     * @return
     */
    public function initMemberView()
    {
        include_once './Services/Container/classes/class.ilMemberViewSettings.php';
        $settings = ilMemberViewSettings::getInstance();
        
        // disable member view
        if (
            isset($_GET['mv']) &&
            $_GET['mv'] == 0
        ) {
            // force deactivation
            $settings->toggleActivation((int) $_GET['ref_id'], false);
        }
        if (
            isset($_GET['mv']) &&
            $_GET['mv'] == 1
        ) {
            if ($this->checkAccess('write', (int) $_GET['ref_id'])) {
                $settings->toggleActivation((int) $_GET['ref_id'], true);
                // reset caches
                self::resetCaches();
            }
        }
        
        if (!$settings->isActive()) {
            $this->mem_view['active'] = false;
            $this->mem_view['items'] = array();
            $this->mem_view['role'] = 0;
        } else {
            global $DIC;

            $tree = $DIC['tree'];
            
            $this->mem_view['active'] = true;
            $this->mem_view['items'] = $tree->getSubTreeIds($settings->getContainer());
            $this->mem_view['items'] = array_merge($this->mem_view['items'], array($settings->getContainer()));
            
            include_once './Services/Membership/classes/class.ilParticipants.php';
            $this->mem_view['role'] = ilParticipants::getDefaultMemberRole($settings->getContainer());
        }
        return true;
    }
    
    public function addTemporaryRole($a_usr_id, $a_role_id)
    {
        if (!in_array($a_role_id, self::$user_role_cache[$a_usr_id])) {
            self::$user_role_cache[$a_usr_id][] = $a_role_id;
        }
    }
    
    public function resetPACache($a_usr_id, $a_ref_id)
    {
        $paCacheKey = $a_usr_id . ':' . $a_ref_id;
        unset(self::$_paCache[$paCacheKey]);
    }
} // END class.RbacSystem
