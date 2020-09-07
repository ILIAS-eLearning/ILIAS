<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Claiming permission helper base class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ingroup ServicesComponent
 */
abstract class ilClaimingPermissionHelper
{
    protected $user_id; // [int]
    protected $ref_id; // [int]
    protected $map; // [array]
    protected $context_ids; // [array]
    protected $plugins; // [array]
    
    protected static $instances; // [array]
        
    
    // constructor
    
    /**
     * Constructor
     *
     * @param int $a_user_id
     * @param int $a_ref_id
     * @return self
     */
    protected function __construct($a_user_id, $a_ref_id)
    {
        $this->setUserId($a_user_id);
        $this->setRefId($a_ref_id);
        $this->map = $this->buildPermissionMap();
        $this->reset();
    }
    
    /**
     * Factory
     *
     * @param int $a_user_id
     * @param int $a_ref_id
     * @return self
     */
    public static function getInstance($a_user_id = null, $a_ref_id = null)
    {
        global $DIC;
        $ilUser = $DIC->user();
        
        if (!$a_user_id) {
            $a_user_id = $ilUser->getId();
        }
        if (!$a_ref_id) {
            $a_ref_id = (int) $_REQUEST["ref_id"];
        }
        if (!isset(self::$instances[$a_user_id][$a_ref_id])) {
            self::$instances[$a_user_id][$a_ref_id] = new static($a_user_id, $a_ref_id);
        }
        return self::$instances[$a_user_id][$a_ref_id];
    }
    
    /**
     * Reset (internal caches)
     */
    public function reset()
    {
        $this->context_ids = array();
    }
    
    
    // properties
    
    /**
     * Set user id
     *
     * @param int $a_value
     */
    protected function setUserId($a_value)
    {
        $this->user_id = (int) $a_value;
    }
    
    /**
     * Get user id
     *
     * @return int
     */
    protected function getUserId()
    {
        return $this->user_id;
    }
        
    /**
     * Set ref id
     *
     * @param int $a_value
     */
    protected function setRefId($a_value)
    {
        $this->ref_id = (int) $a_value;
    }
    
    /**
     * Get ref id
     *
     * @return int
     */
    protected function getRefId()
    {
        return $this->ref_id;
    }
    
    
    // caching
    
    /**
     * Get all context ids for context type (from DB, is cached)
     *
     * @see self::getValidContextIds()
     * @param int $a_context_type
     * @return array
     */
    abstract protected function readContextIds($a_context_type);
    
    
    // permissions
    
    /**
     * Build map of context and actions
     *
     * @return array
     */
    abstract protected function buildPermissionMap();
        
    /**
     * Check if given combination of context and action is valid
     *
     * @param int $a_context_type
     * @param mixed $a_context_id
     * @param int $a_action_id
     * @param int $a_action_sub_id
     * @return boolean
     */
    protected function isValidContextAndAction($a_context_type, $a_context_id, $a_action_id, $a_action_sub_id = null)
    {
        $valid = false;
        
        if (array_key_exists($a_context_type, $this->map)) {
            if (!$a_action_sub_id) {
                if (in_array($a_action_id, $this->map[$a_context_type]["actions"])) {
                    $valid = true;
                }
            } else {
                if (array_key_exists($a_action_id, $this->map[$a_context_type]["subactions"]) &&
                    in_array($a_action_sub_id, $this->map[$a_context_type]["subactions"][$a_action_id])) {
                    $valid = true;
                }
            }
        }
        
        if ($valid &&
            $a_context_id &&
            !in_array($a_context_id, $this->getValidContextIds($a_context_type))) {
            $valid = false;
        }
        
        if (DEVMODE && !$valid) {
            trigger_error("INVALID permission context - " . $a_context_type . ":" . $a_context_id . ":" . $a_action_id . ":" . $a_action_sub_id, E_USER_WARNING);
        }
        
        return $valid;
    }
    
    /**
     * Get context ids for context type (uses cache)
     *
     * @see self::readContextIds()
     * @param int $a_context_type
     * @return array
     */
    protected function getValidContextIds($a_context_type)
    {
        if (!array_key_exists($a_context_type, $this->context_ids)) {
            $this->context_ids[$a_context_type] = $this->readContextIds($a_context_type);
        }
        return (array) $this->context_ids[$a_context_type];
    }
            
    /**
     * Check permission
     *
     * @param int $a_context_type
     * @param int $a_context_id
     * @param int $a_action_id
     * @param int $a_action_sub_id
     * @return bool
     */
    public function hasPermission($a_context_type, $a_context_id, $a_action_id, $a_action_sub_id = null)
    {
        if ($this->isValidContextAndAction($a_context_type, $a_context_id, $a_action_id, $a_action_sub_id)) {
            return $this->checkPermission($a_context_type, $a_context_id, $a_action_id, $a_action_sub_id);
        }
        // :TODO: exception?
    }
    
    /**
     * Check permissions
     *
     * @param int $a_context_type
     * @param int $a_context_id
     * @param array $a_action_ids
     * @return bool
     */
    public function hasPermissions($a_context_type, $a_context_id, array $a_action_ids)
    {
        $res = array();
        
        foreach ($a_action_ids as $action_id) {
            if (is_array($action_id)) {
                $action_sub_id = $action_id[1];
                $action_id = $action_id[0];
                
                $res[$action_id][$action_sub_id] = $this->hasPermission($a_context_type, $a_context_id, $action_id, $action_sub_id);
            } else {
                $res[$action_id] = $this->hasPermission($a_context_type, $a_context_id, $action_id);
            }
        }
    
        return $res;
    }
    
    /**
     * Check permission (helper: rbac, plugins)
     *
     * @param int $a_context_type
     * @param int $a_context_id
     * @param int $a_action_id
     * @param int $a_action_sub_id
     * @return bool
     */
    protected function checkPermission($a_context_type, $a_context_id, $a_action_id, $a_action_sub_id = null)
    {
        return ($this->checkRBAC() &&
            $this->checkPlugins($a_context_type, $a_context_id, $a_action_id, $a_action_sub_id));
    }
    
    /**
     * Check permission against RBAC
     *
     * @return bool
     */
    protected function checkRBAC()
    {
        global $DIC;
        $ilAccess = $DIC->access();
        
        // we are currently only supporting write operations
        return $ilAccess->checkAccessOfUser($this->getUserId(), "write", "", $this->getRefId());
    }
    
    /**
     * Get active plugins (for current slot)
     *
     * @return array ilPlugin
     */
    abstract protected function getActivePlugins();
    
    /**
     * Check permission against plugins
     *
     * @param int $a_context_type
     * @param int $a_context_id
     * @param int $a_action_id
     * @param int $a_action_sub_id
     * @return boolean
     */
    protected function checkPlugins($a_context_type, $a_context_id, $a_action_id, $a_action_sub_id = null)
    {
        $valid = true;
        
        if (!is_array($this->plugins)) {
            $this->plugins = (array) $this->getActivePlugins();
        }
        
        foreach ($this->plugins as $plugin) {
            if (!$plugin->checkPermission($this->getUserId(), $a_context_type, $a_context_id, $a_action_id, $a_action_sub_id)) {
                $valid = false;
                break;
            }
        }
        
        return $valid;
    }
}
