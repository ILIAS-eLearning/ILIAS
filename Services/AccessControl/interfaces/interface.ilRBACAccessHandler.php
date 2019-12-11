<?php

/**
 * Interface ilRBACAccessHandler
 *
 * Checks access for ILIAS objects
 *
 * @author  Alex Killing <alex.killing@gmx.de>
 * @author  Sascha Hofmann <saschahofmann@gmx.de>
 * @version $Id$
 *
 * @ingroup ServicesAccessControl
 */
interface ilRBACAccessHandler
{

    /**
     * store access result
     *
     * @access    private
     *
     * @param    string  $a_permission     permission
     * @param    string  $a_cmd            command string
     * @param    int     $a_ref_id         reference id
     * @param    boolean $a_access_granted true if access is granted
     * @param    int     $a_user_id        user id (if no id passed, current user id)
     */
    public function storeAccessResult($a_permission, $a_cmd, $a_ref_id, $a_access_granted, $a_user_id = null, $a_info = "");


    /**
     * Set prevent caching last result.
     *
     * @param    boolean        true if last result should not be cached
     */
    public function setPreventCachingLastResult($a_val);


    /**
     * Get prevent caching last result.
     *
     * @return    boolean        true if last result should not be cached
     */
    public function getPreventCachingLastResult();


    /**
     * get stored access result
     *
     * @access    private
     *
     * @param    string $a_permission permission
     * @param    string $a_cmd        command string
     * @param    int    $a_ref_id     reference id
     * @param    int    $a_user_id    user id (if no id passed, current user id)
     *
     * @return    array        result array:
     *                        "granted" (boolean) => true if access is granted
     *                        "info" (object)    => info object
     */
    public function getStoredAccessResult($a_permission, $a_cmd, $a_ref_id, $a_user_id = "");


    public function storeCache();


    public function readCache($a_secs = 0);


    public function getResults();


    public function setResults($a_results);


    /**
     * add an info item to current info object
     */
    public function addInfoItem($a_type, $a_text, $a_data = "");


    /**
     * check access for an object
     * (provide $a_type and $a_obj_id if available for better performance)
     *
     * @param    string $a_permission
     * @param    string $a_cmd
     * @param    int    $a_ref_id
     * @param    string $a_type    (optional)
     * @param    int    $a_obj_id  (optional)
     * @param    int    $a_tree_id (optional)
     *
     */
    public function checkAccess($a_permission, $a_cmd, $a_ref_id, $a_type = "", $a_obj_id = null, $a_tree_id = null);


    /**
     * check access for an object
     * (provide $a_type and $a_obj_id if available for better performance)
     *
     * @param    integer $a_user_id
     * @param    string  $a_permission
     * @param    string  $a_cmd
     * @param    int     $a_ref_id
     * @param    string  $a_type    (optional)
     * @param    int     $a_obj_id  (optional)
     * @param    int     $a_tree_id (optional)
     *
     */
    public function checkAccessOfUser($a_user_id, $a_permission, $a_cmd, $a_ref_id, $a_type = "", $a_obj_id = null, $a_tree_id = null);


    /**
     * get last info object
     */
    public function getInfo();


    /**
     * get last info object
     */
    public function getResultLast();


    public function getResultAll($a_ref_id = "");


    /**
     * look if result for current query is already in cache
     *
     * @param string $a_permission
     * @param string $a_cmd
     * @param int    $a_ref_id
     * @param int    $a_user_id
     *
     * @return bool
     */
    public function doCacheCheck($a_permission, $a_cmd, $a_ref_id, $a_user_id);


    /**
     * check if object is in tree and not deleted
     *
     * @param string $a_permission
     * @param string $a_cmd
     * @param int    $a_ref_id
     * @param int    $a_user_id
     *
     * @return bool
     */
    public function doTreeCheck($a_permission, $a_cmd, $a_ref_id, $a_user_id);


    /**
     * rbac check for current object
     * -> type should be used for create permission
     *
     * @param string $a_permission
     * @param string $a_cmd
     * @param int    $a_ref_id
     * @param int    $a_user_id
     * @param string $a_type
     *
     * @return bool
     */
    public function doRBACCheck($a_permission, $a_cmd, $a_ref_id, $a_user_id, $a_type);


    /**
     * check read permission for all parents
     *
     * @param      string $a_permission
     * @param      string $a_cmd
     * @param      int    $a_ref_id
     * @param      int    $a_user_id
     * @param bool        $a_all
     *
     * @return bool
     */
    public function doPathCheck($a_permission, $a_cmd, $a_ref_id, $a_user_id, $a_all = false);


    /**
     * check for activation and centralized offline status.
     *
     * @param      string $a_permission
     * @param      string $a_cmd
     * @param      int    $a_ref_id
     * @param      int    $a_user_id
     * @param      int    $a_obj_id
     * @param      string $a_type
     *
     * @return bool
     */
    public function doActivationCheck($a_permission, $a_cmd, $a_ref_id, $a_user_id, $a_obj_id, $a_type);


    /**
     * condition check (currently only implemented for read permission)
     *
     * @param string $a_permission
     * @param string $a_cmd
     * @param int $a_ref_id
     * @param int $a_user_id
     * @param int $a_obj_id
     * @param string $a_type
     *
     * @return bool
     */
    public function doConditionCheck($a_permission, $a_cmd, $a_ref_id, $a_user_id, $a_obj_id, $a_type);


    /**
     * object type specific check
     *
     * @param string $a_permission
     * @param string $a_cmd
     * @param int $a_ref_id
     * @param int $a_user_id
     * @param int $a_obj_id
     * @param string $a_type
     *
     * @return bool
     */
    public function doStatusCheck($a_permission, $a_cmd, $a_ref_id, $a_user_id, $a_obj_id, $a_type);


    /**
     *
     */
    public function clear();


    /**
     * @param $a_str
     * @param $a_bool
     */
    public function enable($a_str, $a_bool);
}
