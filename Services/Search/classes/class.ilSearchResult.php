<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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
* searchResult stores all result of a search query.
* Offers methods like mergeResults. To merge result sets of different queries.
* 
* 
* @author Stefan Meyer <smeyer@databay.de>
* @version Id$
* 
* @package ilias-search
*/
class ilSearchResult
{
	var $user_id;

	// OBJECT VARIABLES
	var $ilias;
	var $ilAccess;


	var $result;
	/**
	* Constructor
	* @access	public
	*/
	function ilSearchResult($a_user_id = 0)
	{
		global $ilias,$ilAccess,$ilDB;

		$this->ilAccess =& $ilAccess;
		$this->user_id = $a_user_id;
		$this->db =& $ilDB;
	}

	function setUserId($a_user_id)
	{
		$this->user_id = $a_user_id;
	}
	function getUserId()
	{
		return $this->user_id;
	}

	function getEntries()
	{
		return $this->entries ? $this->entries : array();
	}

	/**
	 *
	 * add search result entry
	 * Entries are stored with 'obj_id'. This method is typically called to store db query results.
	 * @param integer object object_id
	 * @param string obj_type 'lm' or 'crs' ...
	 * @param array value position of query parser words in query string
	 * @access	public
	 */
	function addEntry($a_obj_id,$a_type,$found)
	{
		// Create new entry if it not exists
		if(!$this->entries[$a_obj_id])
		{
			$this->entries[$a_obj_id]['obj_id'] = $a_obj_id;
			$this->entries[$a_obj_id]['type'] = $a_type;
			$this->entries[$a_obj_id]['found'] = $found;
		}
		else
		{
			// UPDATE FOUND
			$counter = 0;
			foreach($found as $position)
			{
				if($position)
				{
					$this->entries[$a_obj_id]['found'][$counter] = $position;
				}
				$counter++;
			}
		}
		return true;
	}

	/**
	 *
	 * merge entries of this instance and another result object
	 * @param object result_obj
	 * @access	public
	 */
	function mergeEntries(&$result_obj)
	{
		foreach($result_obj->getEntries() as $entry)
		{
			$this->addEntry($entry['obj_id'],$entry['type'],$entry['found']);
		}
		return true;
	}

	/**
	 *
	 * diff entries of this instance and another result object
	 * Used for search in results
	 * @param object result_obj
	 * @access	public
	 */
	function diffEntries(&$result_obj)
	{
		$new_entries = $this->getEntries();
		$this->entries = array();

		foreach($result_obj->getResults() as $result)
		{
			$obj_id = $result['obj_id'];
			if(isset($new_entries[$obj_id]))
			{
				$this->addEntry($new_entries[$obj_id]['obj_id'],
								$new_entries[$obj_id]['type'],
								$new_entries[$obj_id]['rbac_id']);
			}
		}
	}
	/**
	 *
	 * add search result
	 * Results are stored with 'ref_id'. This method is typically called after checking access of entries.
	 * @param integer ref_id
	 * @param integer obj_id 
	 * @param string obj_type 'lm' or 'crs' ...
	 * @access	public
	 */
	function addResult($a_ref_id,$a_obj_id,$a_type)
	{
		$this->results[$a_ref_id]['ref_id'] = $a_ref_id;
		$this->results[$a_ref_id]['obj_id'] = $a_obj_id;
		$this->results[$a_ref_id]['type']	= $a_type;
	}

	function getResults()
	{
		return $this->results ? $this->results : array();
	}

	function getResultsForPresentation()
	{
		foreach($this->getResults() as $result)
		{
			switch($result['type'])
			{
				// learning material
				case "sahs":
				case "lm":
				case "dbk":
				case "htlm":
					$type = "lres";
					break;

				default:
					$type = $result['type'];
					break;
			}
			$title = ilObject::_lookupTitle($result['obj_id']);
			$description = ilObject::_lookupDescription($result['obj_id']);

			$presentation_result[$type][] = array('ref_id' => $result['ref_id'],
												  'title' => $title,
												  'description' => $description,
												  'type' => $result['type'],
												  'obj_id' => $result['obj_id']);
		}
		return $presentation_result ? $presentation_result : array();
	}

	function filter($a_root_node,$check_and)
	{
		global $tree;

		$this->__initSearchSettingsObject();

		// get ref_ids and check access
		$counter = 0;
		foreach($this->getEntries() as $entry)
		{
			if($check_and and in_array(0,$entry['found']))
			{
				continue;
			}
			foreach(ilObject::_getAllReferences($entry['obj_id']) as $ref_id)
			{
				$type = ilObject::_lookupType($ref_id, true);
				if($this->ilAccess->checkAccess('visible','',$ref_id,$type,$entry['obj_id']))
				{
					if($a_root_node == ROOT_FOLDER_ID or $tree->isGrandChild($a_root_node,$ref_id))
					{
						$this->addResult($ref_id,$entry['obj_id'],$type);
						// Stop if maximum of hits is reached
						if(++$counter == $this->search_settings->getMaxHits())
						{
							return true;
						}
					}
				}
			}
		}
		return false;
	}

	function save()
	{
		if ($this->getUserId() and $this->getUserId() != ANONYMOUS_USER_ID)
		{
			$query = "REPLACE INTO usr_search ".
				"VALUES('".$this->getUserId()."','".addslashes(serialize($this->getResults()))."')";

			$res = $this->db->query($query);

			return true;
		}

		return false;
	}
	function read()
	{
		if($this->getUserId() and $this->getUserId() != ANONYMOUS_USER_ID)
		{
			$query = "SELECT search_result FROM usr_search ".
				"WHERE usr_id = '".$this->getUserId()."'";

			$res = $this->db->query($query);

			if($res->numRows())
			{
				$row = $res->fetchRow(DB_FETCHMODE_OBJECT);
				$this->results = unserialize(stripslashes($row->search_result));
			}
		}
	}
	// PRIVATE
	function __initSearchSettingsObject()
	{
		include_once 'Services/Search/classes/class.ilSearchSettings.php';

		$this->search_settings = new ilSearchSettings();
	}


} // END class.Search
?>