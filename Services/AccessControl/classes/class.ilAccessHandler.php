<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("Services/AccessControl/classes/class.ilAccessInfo.php");

/** @defgroup ServicesAccessControl Services/AccessControl
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
class ilAccessHandler
{
	/**
	* constructor
	*/
	function ilAccessHandler()
	{
		global $rbacsystem,$lng;

		$this->rbacsystem =& $rbacsystem;
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
        $this->obj_tree_cache=array();
	}

	/**
	* store access result
	*
	* @access	private
	* @param	string		$a_permission			permission
	* @param	string		$a_cmd					command string
	* @param	int			$a_ref_id				reference id
	* @param	boolean		$a_access_granted		true if access is granted
	* @param	int			$a_user_id				user id (if no id passed, current user id)
	*/
	function storeAccessResult($a_permission, $a_cmd, $a_ref_id, $a_access_granted, $a_user_id = "",$a_info = "")
	{
		global $ilUser;

		if ($a_user_id == "")
		{
			$a_user_id = $ilUser->getId();
		}
		
		if ($a_info == "")
		{
			$a_info = $this->current_info;
		}

		//var_dump("<pre>",$a_permission,"</pre>");

		if ($this->cache)
		{
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
	* Set prevent caching last result.
	*
	* @param	boolean		true if last result should not be cached
	*/
	function setPreventCachingLastResult($a_val)
	{
		$this->prevent_caching_last_result = $a_val;
	}
	
	/**
	* Get prevent caching last result.
	*
	* @return	boolean		true if last result should not be cached
	*/
	function getPreventCachingLastResult()
	{
		return $this->prevent_caching_last_result;
	}

	/**
	* get stored access result
	*
	* @access	private
	* @param	string		$a_permission			permission
	* @param	string		$a_cmd					command string
	* @param	int			$a_ref_id				reference id
	* @param	int			$a_user_id				user id (if no id passed, current user id)
	* @return	array		result array:
	*						"granted" (boolean) => true if access is granted
	*						"info" (object) 	=> info object
	*/
	function getStoredAccessResult($a_permission, $a_cmd, $a_ref_id, $a_user_id = "")
	{
		global $ilUser;

		if ($a_user_id == "")
		{
			$a_user_id = $ilUser->getId();
		}
		
		/*if (is_object($this->results[$a_ref_id][$a_permission][$a_cmd][$a_user_id]['info']))
		{
			$this->current_info = $this->results[$a_ref_id][$a_permission][$a_cmd][$a_user_id]['info'];
		}*/

		return $this->results[$a_ref_id][$a_permission][$a_cmd][$a_user_id];
	}

	function storeCache()
	{
		global $ilDB, $ilUser;
		
		$query = "DELETE FROM acc_cache WHERE user_id = ".$ilDB->quote($ilUser->getId(),'integer');
		$res = $ilDB->manipulate($query);
		
		$ilDB->insert('acc_cache', array(
			'user_id'	=>	array('integer',$ilUser->getId()),
			'time'		=>	array('integer',time()),
			'result'	=>	array('clob',serialize($this->results))
			));
	}
	
	function readCache($a_secs = 0)
	{
		global $ilUser, $ilDB;
		
		if ($a_secs > 0)
		{
			$query = "SELECT * FROM acc_cache WHERE user_id = ".
				$ilDB->quote($ilUser->getId() ,'integer');
			$set = $ilDB->query($query);
			$rec = $set->fetchRow(DB_FETCHMODE_ASSOC);
			if ((time() - $rec["time"]) < $a_secs)
			{
				$this->results = unserialize($rec["result"]);
//var_dump($this->results);
				return true;
			}
		}
		return false;
	}

	function getResults()
	{
		return $this->results;
	}
	
	function setResults($a_results)
	{
		$this->results = $a_results;
	}
	
	/**
	* add an info item to current info object
	*/
	function addInfoItem($a_type, $a_text, $a_data = "")
	{
		$this->current_info->addInfoItem($a_type, $a_text, $a_data);
	}

	/**
	* check access for an object
	* (provide $a_type and $a_obj_id if available for better performance)
	*
	* @param	string		$a_permission
	* @param	string		$a_cmd
	* @param	int			$a_ref_id
	* @param	string		$a_type (optional)
	* @param	int			$a_obj_id (optional)
	* @param	int			$a_tree_id (optional)
	*
	*/
	function checkAccess($a_permission, $a_cmd, $a_ref_id, $a_type = "", $a_obj_id = "", $a_tree_id="")
	{
		global $ilUser;

		return $this->checkAccessOfUser($ilUser->getId(),$a_permission, $a_cmd, $a_ref_id, $a_type, $a_obj_id, $a_tree_id);
	}

	/**
	* check access for an object
	* (provide $a_type and $a_obj_id if available for better performance)
	* 
	* @param	integer		$a_user_id
	* @param	string		$a_permission
	* @param	string		$a_cmd
	* @param	int			$a_ref_id
	* @param	string		$a_type (optional)
	* @param	int			$a_obj_id (optional)
	* @param	int			$a_tree_id (optional)
	*
	*/
	function checkAccessOfUser($a_user_id,$a_permission, $a_cmd, $a_ref_id, $a_type = "", $a_obj_id = "", $a_tree_id="")
	{
		global $ilBench, $lng;
		
		$this->setPreventCachingLastResult(false);	// for external db based caches
		
		$ilBench->start("AccessControl", "0400_clear_info");
		$this->current_info->clear();
		$ilBench->stop("AccessControl", "0400_clear_info");
		
		
        // get stored result (internal memory based cache)
		$cached = $this->doCacheCheck($a_permission, $a_cmd, $a_ref_id, $a_user_id);
		if ($cached["hit"])
		{
			// Store access result
			if (!$cached["granted"])
			{
				$this->current_info->addInfoItem(IL_NO_PERMISSION, $lng->txt("status_no_permission"));
			}
			if ($cached["prevent_db_cache"])
			{
				$this->setPreventCachingLastResult(true);	// should have been saved in previous call already
			}
			return $cached["granted"];
		}

		$ilBench->start("AccessControl", "0500_lookup_id_and_type");
		// get object id if not provided
		if ($a_obj_id == "")
		{
			if ($this->obj_id_cache[$a_ref_id] > 0)
			{
				$a_obj_id = $this->obj_id_cache[$a_ref_id];
			}
			else
			{
				$a_obj_id = ilObject::_lookupObjId($a_ref_id);
				$this->obj_id_cache[$a_ref_id] = $a_obj_id;
			}
		}
		if ($a_type == "")
		{
			if ($this->obj_type_cache[$a_ref_id] != "")
			{
				$a_type = $this->obj_type_cache[$a_ref_id];
			}
			else
			{
				$a_type = ilObject::_lookupType($a_ref_id, true);
				$this->obj_type_cache[$a_ref_id] = $a_type;
			}
		}
		$ilBench->stop("AccessControl", "0500_lookup_id_and_type");

		// to do: payment handling

        // if supplied tree id is not = 1 (= repository main tree),
        // check if object is in tree and not deleted
		if ($a_tree_id != 1 &&
            !$this->doTreeCheck($a_permission, $a_cmd, $a_ref_id, $a_user_id))
		{
			$this->current_info->addInfoItem(IL_NO_PERMISSION, $lng->txt("status_no_permission"));
			$this->storeAccessResult($a_permission, $a_cmd, $a_ref_id, false, $a_user_id);
			return false;
		}

		// rbac check for current object
		if (!$this->doRBACCheck($a_permission, $a_cmd, $a_ref_id, $a_user_id))
		{
			$this->current_info->addInfoItem(IL_NO_PERMISSION, $lng->txt("status_no_permission"));
			$this->storeAccessResult($a_permission, $a_cmd, $a_ref_id, false, $a_user_id);
			return false;
		}
		// check read permission for all parents
		$par_check = $this->doPathCheck($a_permission, $a_cmd, $a_ref_id, $a_user_id);
		if (!$par_check)
		{
			
			$this->current_info->addInfoItem(IL_NO_PERMISSION, $lng->txt("status_no_permission"));
			$this->storeAccessResult($a_permission, $a_cmd, $a_ref_id, false, $a_user_id);
			return false;
		}

		// condition check (currently only implemented for read permission)
		if (!$this->doConditionCheck($a_permission, $a_cmd, $a_ref_id, $a_user_id, $a_obj_id, $a_type))
		{
			$this->current_info->addInfoItem(IL_NO_PERMISSION, $lng->txt("status_no_permission"));
			$this->storeAccessResult($a_permission, $a_cmd, $a_ref_id, false, $a_user_id);
			$this->setPreventCachingLastResult(true);		// do not store this in db, since condition updates are not monitored
			return false;
		}

		// object type specific check
		if (!$this->doStatusCheck($a_permission, $a_cmd, $a_ref_id, $a_user_id, $a_obj_id, $a_type))
		{
			$this->current_info->addInfoItem(IL_NO_PERMISSION, $lng->txt("status_no_permission"));
			$this->storeAccessResult($a_permission, $a_cmd, $a_ref_id, false, $a_user_id);
			$this->setPreventCachingLastResult(true);		// do not store this in db, since status updates are not monitored
			return false;
		}

		// check for available licenses
		if (!$this->doLicenseCheck($a_permission, $a_cmd, $a_ref_id, $a_user_id, $a_obj_id, $a_type))
		{
			$this->setPreventCachingLastResult(true);		// do not store this in db, since status updates are not monitored
			return false;
		}

		// all checks passed
		$this->storeAccessResult($a_permission, $a_cmd, $a_ref_id, true, $a_user_id);
		return true;
	}

	/**
	* get last info object
	*/
	function getInfo()
	{
		//return $this->last_result;
		//$this->last_info->setQueryData($this->current_result_element);
		//var_dump("<pre>",$this->results,"</pre>");
		return is_object($this->last_info) ? $this->last_info->getInfoItems() : array();
	}
	
	/**
	* get last info object
	*/
	function getResultLast()
	{
		return $this->last_result;
	}
	
	function getResultAll($a_ref_id = "")
	{
		if ($a_ref_id == "")
		{
			return $this->results;
		}
		
		return $this->results[$a_ref_id];
	}
	
	/**
	 * look if result for current query is already in cache
	 * 
	 */
	function doCacheCheck($a_permission, $a_cmd, $a_ref_id,$a_user_id)
	{
		global $ilBench;
		//echo "cacheCheck<br/>";

		$ilBench->start("AccessControl", "1000_checkAccess_get_cache_result");
		$stored_access = $this->getStoredAccessResult($a_permission, $a_cmd, $a_ref_id,$a_user_id);
		//var_dump($stored_access);
		if (is_array($stored_access))
		{
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
	 * check if object is in tree and not deleted
	 * 
	 */
	function doTreeCheck($a_permission, $a_cmd, $a_ref_id, $a_user_id)
	{
		global $tree, $lng, $ilBench;
		//echo "treeCheck<br/>";

        // Get stored result
		$tree_cache_key = $a_user_id.':'.$a_ref_id;
        if (array_key_exists($tree_cache_key, $this->obj_tree_cache)) {
			// Store access result
			if (!$this->obj_tree_cache[$tree_cache_key])
			{
				$this->current_info->addInfoItem(IL_NO_PERMISSION, $lng->txt("status_no_permission"));
			}
			$this->storeAccessResult($a_permission, $a_cmd, $a_ref_id, $this->obj_tree_cache[$tree_cache_key], $a_user_id);

            return $this->obj_tree_cache[$tree_cache_key];
        }

		$ilBench->start("AccessControl", "2000_checkAccess_in_tree");

		if(!$tree->isInTree($a_ref_id) or $tree->isDeleted($a_ref_id))
		{
            // Store negative access results
			
			// Store in tree cache
            // Note, we only store up to 1000 results to avoid memory overflow.
            if (count($this->obj_tree_cache) < 1000) 
            {
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
        if (count($this->obj_tree_cache) < 1000)
        {
            $this->obj_tree_cache[$tree_cache_key] = true;
        }

		// Store in result cache
		$this->storeAccessResult($a_permission, $a_cmd, $a_ref_id, true, $a_user_id);

		$ilBench->stop("AccessControl", "2000_checkAccess_in_tree");
		return true;
	}
	
	/**
	 * rbac check for current object
	 * 
	 */
	function doRBACCheck($a_permission, $a_cmd, $a_ref_id, $a_user_id)
	{
		global $lng, $ilBench, $ilErr, $ilLog;

		$ilBench->start("AccessControl", "2500_checkAccess_rbac_check");
		
		if ($a_permission == "")
		{
				$message = sprintf('%s::doRBACCheck(): No operations given! $a_ref_id: %s',
								   get_class($this),
								   $a_ref_id);
				$ilLog->write($message,$ilLog->FATAL);
				$ilErr->raiseError($message,$ilErr->MESSAGE);
		}
		
		if (isset($this->stored_rbac_access[$a_user_id."-".$a_permission."-".$a_ref_id]))
		{
			$access = $this->stored_rbac_access[$a_user_id."-".$a_permission."-".$a_ref_id];
		}
		else
		{
			$access = $this->rbacsystem->checkAccessOfUser($a_user_id, $a_permission, $a_ref_id);
			if (!is_array($this->stored_rbac_access) || count($this->stored_rbac_access) < 1000)
			{
				$this->stored_rbac_access[$a_user_id."-".$a_permission."-".$a_ref_id] = $access;
			}
		}

		// Store in result cache
		if (!$access)
		{
			$this->current_info->addInfoItem(IL_NO_PERMISSION, $lng->txt("status_no_permission"));
		}
		$this->storeAccessResult($a_permission, $a_cmd, $a_ref_id, true, $a_user_id);
		$ilBench->stop("AccessControl", "2500_checkAccess_rbac_check");

		return $access;
	}
	
	/**
	 * check read permission for all parents
	 * 
	 */
	function doPathCheck($a_permission, $a_cmd, $a_ref_id, $a_user_id, $a_all = false)
	{
		global $tree, $lng, $ilBench,$ilObjDataCache;
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

		foreach ($path as $id)
		{
			if ($a_ref_id == $id)
			{
				continue;
			}

			// Check course activation
			if($ilObjDataCache->lookupType($ilObjDataCache->lookupObjId($id)) == 'crs')
			{
				if(!$this->doActivationCheck($a_permission,$a_cmd,$a_ref_id,$a_user_id,$a_all))
				{
					return false;
				}
			}
			
			$access = $this->checkAccessOfUser($a_user_id, "read", "info", $id);

			if ($access == false)
			{
				
				//$this->doCacheCheck($a_permission, $a_cmd, $a_ref_id, $a_user_id);
				$this->current_info->addInfoItem(IL_NO_PARENT_ACCESS, $lng->txt("no_parent_access"),$id);
				
				if ($a_all == false)
				{
					return false;
				}
			}
		}
		
		return true;
	}

	/**
	 * check for course activation 
	 * 
	 */
	function doActivationCheck($a_permission, $a_cmd, $a_ref_id, $a_user_id, $a_all = false)
	{
		global $ilBench,$ilObjDataCache;
		
		$ilBench->start("AccessControl", "3150_checkAccess_check_course_activation");

		$cache_perm = ($a_permission == "visible")
			? "visible"
			: "other";
			
//echo "<br>doActivationCheck-$cache_perm-$a_ref_id-$a_user_id-".$ilObjDataCache->lookupType($ilObjDataCache->lookupObjId($a_ref_id));

		if (isset($this->ac_cache[$cache_perm][$a_ref_id][$a_user_id]))
		{
			$ilBench->stop("AccessControl", "3150_checkAccess_check_course_activation");
			return $this->ac_cache[$cache_perm][$a_ref_id][$a_user_id];
		}
		
		// nothings needs to be done if current permission is write permission
		if($a_permission == 'write')
		{
			$ilBench->stop("AccessControl", "3150_checkAccess_check_course_activation");
			return true;
		}
		include_once 'Modules/Course/classes/class.ilCourseItems.php';
		
		$this->preloadActivationTimes(array($a_ref_id));
		if(isset($this->ac_times[$a_ref_id]))
		{
			// read preloaded
			$item_data = $this->ac_times[$a_ref_id];
		}
		else
		{
			global $ilLog;
			$ilLog->write(__METHOD__.': Error preloading activation times failed.');
			$item_data = ilCourseItems::_readActivationTimes(array($a_ref_id));
			$item_data = $item_data[$a_ref_id];
		}
		

		// if activation isn't enabled
		if($item_data['timing_type'] != IL_CRS_TIMINGS_ACTIVATION)
		{
			$this->ac_cache[$cache_perm][$a_ref_id][$a_user_id] = true;
			$ilBench->stop("AccessControl", "3150_checkAccess_check_course_activation");
			return true;
		}
		
		// if within activation time
		if((time() >= $item_data['timing_start']) and
		   (time() <= $item_data['timing_end']))
		{
			$this->ac_cache[$cache_perm][$a_ref_id][$a_user_id] = true;
			$ilBench->stop("AccessControl", "3150_checkAccess_check_course_activation");
			return true;
		}
		
		// if user has write permission
		if($this->checkAccessOfUser($a_user_id, "write", "", $a_ref_id))
		{
			$this->ac_cache[$cache_perm][$a_ref_id][$a_user_id] = true;
			$ilBench->stop("AccessControl", "3150_checkAccess_check_course_activation");
			return true;
		}
		// if current permission is visible and visible is set in activation
		if($a_permission == 'visible' and $item_data['visible'])
		{
			$this->ac_cache[$cache_perm][$a_ref_id][$a_user_id] = true;
			$ilBench->stop("AccessControl", "3150_checkAccess_check_course_activation");
			return true;
		}
		// no access
		$this->ac_cache[$cache_perm][$a_ref_id][$a_user_id] = false;
		$ilBench->stop("AccessControl", "3150_checkAccess_check_course_activation");
		return false;
	}
	
	/**
	 * preload activation times of course items
	 * loads all required timing data for the given ref ids 
	 *
	 * @access public
	 * @param array array(int) ref_id
	 * 
	 */
	public function preloadActivationTimes($a_ref_ids)
	{
		include_once('Modules/Course/classes/class.ilCourseItems.php');
		
		$read_arr = array();
		foreach($a_ref_ids as $ref_id)
		{
			if(!isset($this->ac_times[$ref_id]))
			{
				$read_arr[] = $ref_id;
			}
		}
		if(count($read_arr))
		{
			$this->ac_times = (array) $this->ac_times + ilCourseItems::_readActivationTimes($read_arr);
		}
	}
	
	/**
	 * condition check (currently only implemented for read permission)
	 * 
	 */
	function doConditionCheck($a_permission, $a_cmd, $a_ref_id,$a_user_id, $a_obj_id, $a_type)
	{
		//echo "conditionCheck<br/>";
		global $lng, $ilBench;

		if (($a_permission == "read" or $a_permission == 'join') &&
			!$this->checkAccessOfUser($a_user_id, "write", "", $a_ref_id, $a_type, $a_obj_id))
		{
			$ilBench->start("AccessControl", "4000_checkAccess_condition_check");
			if(!ilConditionHandler::_checkAllConditionsOfTarget($a_ref_id,$a_obj_id,$a_type,$a_user_id))
			{
				$conditions = ilConditionHandler::_getConditionsOfTarget($a_ref_id,$a_obj_id, $a_type);
				foreach ($conditions as $condition)
				{
					$this->current_info->addInfoItem(IL_MISSING_PRECONDITION,
						$lng->txt("missing_precondition").": ".
						ilObject::_lookupTitle($condition["trigger_obj_id"])." ".
						$lng->txt("condition_".$condition["operator"])." ".
						$condition["value"], $condition);
				}
				$ilBench->stop("AccessControl", "4000_checkAccess_condition_check");
				return false;
			}
			$ilBench->stop("AccessControl", "4000_checkAccess_condition_check");
		}

		return true;
	}
	
	/**
	 * object type specific check
	 * 
	 */
	function doStatusCheck($a_permission, $a_cmd, $a_ref_id,$a_user_id, $a_obj_id, $a_type)
	{
		global $objDefinition, $ilBench;
		//echo "statusCheck<br/>";
		$ilBench->start("AccessControl", "5000_checkAccess_object_check");

		$class = $objDefinition->getClassName($a_type);
		$location = $objDefinition->getLocation($a_type);
		$full_class = "ilObj".$class."Access";
		include_once($location."/class.".$full_class.".php");
		// static call to ilObj..::_checkAccess($a_cmd, $a_permission, $a_ref_id, $a_obj_id)

		$ilBench->start("AccessControl", "5001_checkAccess_".$full_class."_check");
		$obj_access = call_user_func(array($full_class, "_checkAccess"),
			$a_cmd, $a_permission, $a_ref_id, $a_obj_id, $a_user_id);
		$ilBench->stop("AccessControl", "5001_checkAccess_".$full_class."_check");
		if (!($obj_access === true))
		{
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
	* check for available licenses
	*/
	function doLicenseCheck($a_permission, $a_cmd, $a_ref_id,$a_user_id, $a_obj_id, $a_type)
	{
		global $lng;

		// simple checks first
		if (!in_array($a_type, array('sahs','htlm'))
		or  !in_array($a_permission, array('read')))
		{
			$has_access = true;
		}
		else
		{
			require_once("Services/License/classes/class.ilLicenseAccess.php");

			// licensing globally disabled => access granted
			if (!ilLicenseAccess::_isEnabled())
			{
				$has_access = true;
			}
			// no license needed, if permissions can be set
			// edit_permissions allows to change the numbers of licenses
			elseif ($this->rbacsystem->checkAccessOfUser($a_user_id, "edit_permissions", $a_ref_id))
			{
				$has_access = true;
			}
			// now do the real check
			else
			{
				$has_access = ilLicenseAccess::_checkAccess($a_user_id, $a_obj_id);
			}
		}

		if ($has_access)
		{
			$this->storeAccessResult($a_permission, $a_cmd, $a_ref_id, true, $a_user_id);
			return true;
		}
		else
		{
			$this->current_info->addInfoItem(IL_NO_LICENSE, $lng->txt("no_license_available"));
			$this->storeAccessResult($a_permission, $a_cmd, $a_ref_id, false, $a_user_id);
			return false;
		}
	}
	
	function clear()
	{
		$this->results = array();
		$this->last_result = "";
		$this->current_info = new ilAccessInfo();
	}
	
	function enable($a_str,$a_bool)
	{
		$this->$a_str = $a_bool;
	}
}
