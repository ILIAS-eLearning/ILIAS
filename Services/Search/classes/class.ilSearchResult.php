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
	// OBJECT VARIABLES
	var $ilias;
	var $ilAccess;


	var $result;
	/**
	* Constructor
	* @access	public
	*/
	function ilSearchResult()
	{
		global $ilias,$ilAccess;

		$this->ilAccess =& $ilAccess;
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
	 * @param integer rbac_id in case of pages, chapters add the id of the rbac object (the lm id)
	 * @access	public
	 */

	function addEntry($a_obj_id,$a_type,$a_rbac_id = 0)
	{
		$this->entries[$a_obj_id]['obj_id'] = $a_obj_id;
		$this->entries[$a_obj_id]['type'] = $a_type;
		$this->entries[$a_obj_id]['rbac_id'] = $a_rbac_id ? $a_rbac_id : $a_obj_id;

		return true;
	}

	/**
	 *
	 * add search result
	 * Results are stored with 'ref_id'. This method is typically called after checking access of entries.
	 * @param integer ref_id
	 * @param integer obj_id e.g. id og structure object
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
		foreach($this->results as $result)
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

	function filter()
	{
		$this->__initSearchSettingsObject();

		// get ref_ids and check access
		$counter = 0;
		foreach($this->getEntries() as $entry)
		{
			foreach(ilObject::_getAllReferences($entry['rbac_id']) as $ref_id)
			{
				if($this->ilAccess->checkAccess('visible','',$ref_id,$entry['type'],$entry['obj_id']))
				{
					$this->addResult($ref_id,$entry['obj_id'],$entry['type']);
					// Stop if maximum of hits is reached
					if(++$counter == $this->search_settings->getMaxHits())
					{
						return true;
					}
				}
			}
		}
		return false;
	}

	
	// PRIVATE
	function __initSearchSettingsObject()
	{
		include_once 'Services/Search/classes/class.ilSearchSettings.php';

		$this->search_settings = new ilSearchSettings();
	}


} // END class.Search
?>