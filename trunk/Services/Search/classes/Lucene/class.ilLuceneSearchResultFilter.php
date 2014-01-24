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
	protected $cache;
	protected $required_permission = 'visible';
	protected $limit_reached = false;
	protected $offset = 0;
	
	protected $filters = array();


	/**
	 * Singleton constructor 
	 * @param int $a_usr_id user id
	 * @return
	 */
	protected function __construct($a_user_id)
	{
		$this->user_id = $a_user_id;
		$this->settings = ilSearchSettings::getInstance();

	 	include_once('Services/Search/classes/class.ilUserSearchCache.php');
	 	$this->cache = ilUserSearchCache::_getInstance($this->getUserId());
	 	
	 	$this->offset = $this->settings->getMaxHits() * ($this->cache->getResultPageNumber() - 1);
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
	 * add filter 
	 * @param
	 * @return
	 */
	public function addFilter(ilLuceneResultFilter $filter)
	{
		$this->filters[] = $filter;
	}
	
	/**
	 * Set result ids 
	 * @param mixed $a_ids Lucene result ids or instance of Iterator
	 * @return void
	 */
	public function setCandidates($a_ids)
	{
		$this->result = $a_ids;
	}
	
	/**
	 * get result ids 
	 * @return array result ids
	 */
	public function getCandidates()
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
	public function getResultIds()
	{
		return $this->checked ? $this->checked : array();
	}
	
	/**
	 * get filtered ids 
	 * @return array array of filtered ref_ids
	 */
	public function getResultObjIds()
	{
		foreach($this->checked as $obj_id)
		{
			$obj_ids[] = $obj_id;
		}
		return $obj_ids ? $obj_ids : array();
	}
	
	/**
	 * get results 
	 * @return array array of ref_ids
	 */
	public function getResults()
	{
		return $this->checked ? $this->checked : array();
	}
	
	/**
	 * get max hits 
	 * @return int max hits per page
	 */
	public function getMaxHits()
	{
		return $this->settings->getMaxHits();		 
	}
	
	/**
	 * Load results from db 
	 * @return
	 */
	public function loadFromDb()
	{
		$this->checked = $this->cache->getResults();
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
		global $ilAccess,$ilLog,$tree;

		// get ref_ids and check access
		$counter = 0;
		$offset_counter = 0;
		
		foreach($this->getCandidates() as $obj_id)
		{
			// Check referenced objects
			foreach(ilObject::_getAllReferences($obj_id) as $ref_id)
			{
				// Check filter
				if(!$this->checkFilter($ref_id))
				{
					$this->cache->appendToFailed($ref_id);
					continue;
				}

				// Access failed by prior check
				if($this->cache->isFailed($ref_id))
				{
					continue;
				}
				// Offset check
				if($this->cache->isChecked($ref_id) and !$this->isOffsetReached($offset_counter))
				{
					$ilLog->write(__METHOD__.': Result was checked.');
					$offset_counter++;
					break;
				}
				
				// RBAC check
				if($ilAccess->checkAccessOfUser($this->getUserId(),
													  $this->getRequiredPermission(),
													  '',
													  $ref_id,
													  '',
													  $obj_id))
				{
					++$counter;
					$offset_counter++;
					$this->append($ref_id,$obj_id);
					$this->cache->appendToChecked($ref_id,$obj_id);
					break;
				}
				else
				{
					$this->cache->appendToFailed($ref_id);
				}
			}
			if($counter >= $this->settings->getMaxHits())
			{
				$this->limit_reached = true;
				$this->cache->setResults($this->getResultIds());
				$this->cache->save();
				return false;
			}
		}
		$this->cache->setResults($this->getResultIds());
		$this->cache->save();
		return true;
	}
	
	/**
	 * check appended filter 
	 * @param int $a_ref_id reference id
	 * @return bool
	 */
	protected function checkFilter($a_ref_id)
	{
		foreach($this->filters as $filter)
		{
			if(!$filter->filter($a_ref_id))
			{
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
	
	/**
	 * Check if offset is reached 
	 * @param int $a_current_nr Current result number
	 * @return bool
	 */
	protected function isOffsetReached($a_current_nr)
	{
		return $a_current_nr < $this->offset ? false : true;
	}
}
?>