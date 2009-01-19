<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

/** 
* Validate Lucene search results
* Do access checks, create ref_ids from obj_ids...
* 
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
*
* @ingroup ServicesSearch
*/
class ilLuceneSearchResultFilter
{
	protected static $instance = null;
	
	protected $user_id = null;
	protected $result = array();
	protected $checked = array();
	protected $settings;
	protected $required_permission = 'visible';
	protected $limit_reached = false;


	/**
	 * Singleton constructor 
	 * @param int $a_usr_id user id
	 * @return
	 */
	protected function __construct($a_user_id)
	{
		$this->user_id = $a_user_id;
		$this->settings = ilSearchSettings::getInstance();
	}
	
	/**
	 * 
	 * @param int $a_user_id user_id
	 * @return
	 * @static
	 */
	public static function getInstance($a_user_id)
	{
		if(self::$instance == null)
		{
			return self::$instance = new ilLuceneSearchResultFilter($a_user_id);
		}
		return self::$instance;
	}
	
	/**
	 * Set result ids 
	 * @param array $a_ids Lucene result ids
	 * @return void
	 */
	public function setResultIds($a_ids)
	{
		$this->result = $a_ids;
	}
	
	/**
	 * get result ids 
	 * @return array result ids
	 */
	public function getResultIds()
	{
		return $this->result ? $this->result : array();
	}
	
	/**
	 * Get user id 
	 * @return int user_id
	 */
	public function getUserId()
	{
		return $this->user_id;
	}
	
	/**
	 * Get required permission 
	 * @return string required rbac permission
	 */
	public function getRequiredPermission()
	{
		return $this->required_permission;
	}
	
	/**
	 * Check if search max hits is reached 
	 * @return bool max hits reached
	 */
	public function isLimitReached()
	{
		return (bool) $this->limit_reached;
	}
	
	/**
	 * get filtered ids 
	 * @return array array of filtered ref_ids
	 */
	public function getFilteredIds()
	{
		return $this->checked ? $this->checked : array();
	}
	
	/**
	 * get filtered ids 
	 * @return array array of filtered ref_ids
	 */
	public function getFilteredObjIds()
	{
		foreach($this->checked as $obj_id)
		{
			$obj_ids[] = $obj_id;
		}
		return $obj_ids ? $obj_ids : array();
	}
	
	/**
	 * Filter search results.
	 * Do RBAC checks.
	 * 
	 *
	 * @access public
	 * @param int root node id
	 * @param bool check and boolean search
	 * @return bool success status
	 * 
	 */
	public function filter()
	{
		global $ilAccess;

		// get ref_ids and check access
		$counter = 0;
		foreach($this->getResultIds() as $obj_id)
		{
			// Check referenced objects
			foreach(ilObject::_getAllReferences($obj_id) as $ref_id)
			{
				// TODO: search cache

				// RBAC check
				if($ilAccess->checkAccessOfUser($this->getUserId(),
													  $this->getRequiredPermission(),
													  '',
													  $ref_id,
													  '',
													  $obj_id))
				{
					++$counter;
					$this->append($ref_id,$obj_id);
					break;
				}
			}
			if($counter >= $this->settings->getMaxHits())
			{
				$this->limit_reached = true;
				return false;
			}
		}
		return true;
	}
	
	/**
	 * Append to filtered results 
	 * @param int ref_id
	 * @param int obj_id
	 */
	protected function append($a_ref_id,$a_obj_id)
	{
		$this->checked[$a_ref_id] = $a_obj_id;
	}
}
?>