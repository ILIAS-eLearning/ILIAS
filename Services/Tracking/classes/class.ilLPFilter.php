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
* class for learning progress filter functionality
* Used for object and learning progress presentation
* Reads and stores user specific filter settings. E.g root node, object types and hide list.
* 
*
* @author Stefan Meyer <smeyer@databay.de>
*
* @version $Id$
*
* @package ilias-tracking
*
*/
include_once './Services/Tracking/classes/class.ilLPObjSettings.php';


class ilLPFilter
{
	var $permission = 'edit_learning_progress';
	var $limit = 0;
	var $limit_reached = false;

	var $anonymized_check = false;

	// Default values for filter
	var $root_node = ROOT_FOLDER_ID;
	var $filter_type = 'lm';
	var $hidden = array();

	var $usr_id = null;
	var $db = null;

	function ilLPFilter($a_usr_id)
	{
		global $ilDB,$ilias;

		$this->usr_id = $a_usr_id;
		$this->db =& $ilDB;
		$this->__read();

		// Limit of filtered objects is search max hits
		$this->limit = $ilias->getSetting('search_max_hits',50);
	}

	function getLimit()
	{
		return $this->limit;
	}

	function limitReached()
	{
		return $this->limit_reached;
	}

	function setRequiredPermission($a_permission)
	{
		$this->permission = $a_permission;
	}
	function getRequiredPermission()
	{
		return $this->permission;
	}
	
	function getUserId()
	{
		return $this->usr_id;
	}
	
	function getFilterType()
	{
		return $this->filter_type ? $this->filter_type : 'lm';
	}
	function setFilterType($a_type)
	{
		return $this->filter_type = $a_type;
	}

	function getRootNode()
	{
		return $this->root_node ? $this->root_node : ROOT_FOLDER_ID;
	}
	function setRootNode($a_root)
	{
		$this->root_node = $a_root;
	}

	function getQueryString()
	{
		return $this->query_string;
	}
	function setQueryString($a_query)
	{
		$this->query_string = $a_query;
	}

	function getHidden()
	{
		return $this->hidden ? $this->hidden : array();
	}
	function isHidden($a_obj_id)
	{
		return in_array($a_obj_id,$this->hidden);
	}
	function setHidden($a_hidden)
	{
		$this->hidden = $a_hidden;
	}
	function addHidden($a_hide)
	{
		if(!in_array($a_hide,$this->hidden))
		{
			$this->hidden[] = $a_hide;
			return true;
		}
		return false;
	}

	function removeHidden($a_show)
	{
		foreach($this->hidden as $obj_id)
		{
			if($obj_id != $a_show)
			{
				$tmp[] = $obj_id;
			}
		}
		$this->hidden = $tmp ? $tmp : array();
	}

	function toggleAnonymizedCheck($a_status)
	{
		$this->anonymized_check = $a_status;
	}
	function checkItemAnonymized()
	{
		return $this->anonymized_check;
	}

	function update()
	{
		global $ilDB;
		
		$query = "UPDATE ut_lp_filter ".
			"SET filter_type = ".$ilDB->quote($this->getFilterType() ,'text').", ".
			"root_node = ".$ilDB->quote($this->getRootNode() ,'integer').", ".
			"hidden = ".$ilDB->quote(serialize($this->getHidden()) ,'text').", ".
			"query_string = ".$ilDB->quote($this->getQueryString() ,'text')." ".
			"WHERE usr_id = ".$ilDB->quote($this->getUserId() ,'integer');
		$res = $ilDB->manipulate($query);
		return true;
	}

	function getObjects()
	{
		return $this->__searchObjects();


		// All is done by search class
		/*
		if(strlen($this->getQueryString()))
		{
			return $this->__searchObjects();
		}
		else
		{
			return $this->__getAllObjects();
		}
		*/
	}


	// Static
	function _delete($a_usr_id)
	{
		global $ilDB;

		$query = "DELETE FROM ut_lp_filter ".
			"WHERE usr_id = ".$ilDB->quote($a_usr_id ,'integer');
		$res = $ilDB->manipulate($query);

		return true;
	}
		
	// Private
	function __add()
	{
		global $ilDB;
		
		$query = "INSERT INTO ut_lp_filter (usr_id,filter_type,root_node,hidden,query_string) ".
			"VALUES( ".
			$ilDB->quote($this->getUserId() ,'integer').", ".
			$ilDB->quote($this->getFilterType() ,'text').", ".
			$ilDB->quote($this->getRootNode() ,'integer').", ".
			$ilDB->quote(serialize($this->getHidden()) ,'text').", ".
			$ilDB->quote($this->getQueryString() ,'text').
			")";
		$res = $ilDB->manipulate($query);
		return true;
	}


	function __read()
	{
		global $ilDB;
		
		$query = "SELECT * FROM ut_lp_filter ".
			"WHERE usr_id = ".$ilDB->quote($this->getUserId() ,'integer');
		$res = $ilDB->query($query);

		if(!$res->numRows())
		{
			$this->__add();
		}

		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->filter_type = $row->filter_type;
			$this->root_node = $row->root_node;
			$this->hidden = unserialize($row->hidden);
			$this->query_string = $row->query_string;
		}
	}

	// Function is disabled everything shouild be done by search class
	function __getAllObjects()
	{
		global $tree,$ilObjDataCache;

		$objects = array();
		foreach(ilUtil::_getObjectsByOperations($this->prepareType(),
												$this->getRequiredPermission(),
												$this->getUserId(),
												$this->getLimit()) as $ref_id)
		{
			$obj_id = $ilObjDataCache->lookupObjId($ref_id);
			if($this->isHidden($obj_id))
			{
				continue;
			}
			if(ilLPObjSettings::_lookupMode($obj_id) == LP_MODE_DEACTIVATED)
			{
				continue;
			}

			if($tree->isGrandChild($this->getRootNode(),$ref_id))
			{
				$objects[$obj_id]['ref_ids'][] = $ref_id;
				$objects[$obj_id]['title'] = $ilObjDataCache->lookupTitle($obj_id);
				$objects[$obj_id]['description'] = $ilObjDataCache->lookupDescription($obj_id);
			}
		}
		return $objects ? $objects : array();
	}


	function __searchObjects()
	{
		global $ilObjDataCache;

		include_once './Services/Search/classes/class.ilQueryParser.php';

		$query_parser =& new ilQueryParser($this->getQueryString());
		$query_parser->setMinWordLength(0);
		$query_parser->setCombination(QP_COMBINATION_OR);
		$query_parser->parse();
		if(!$query_parser->validate())
		{
			echo $query_parser->getMessage();
		}


		// only like search since fulltext does not support search with less than 3 characters
		include_once 'Services/Search/classes/Like/class.ilLikeObjectSearch.php';
		$object_search =& new ilLikeObjectSearch($query_parser);

		#include_once './Services/Search/classes/class.ilObjectSearchFactory.php';
		#$object_search =& ilObjectSearchFactory::_getObjectSearchInstance($query_parser);


		$object_search->setFilter($this->prepareType());
		$res =& $object_search->performSearch();
		$res->setRequiredPermission($this->getRequiredPermission());

		// Add callback functions to receive only search_max_hits valid results
		$res->addObserver($this,'searchFilterListener');
		$res->filter($this->getRootNode(),false);
		foreach($res->getResults() as $obj_data)
		{
			$objects[$obj_data['obj_id']]['ref_ids'][] = $obj_data['ref_id'];
			$objects[$obj_data['obj_id']]['title'] = $ilObjDataCache->lookupTitle($obj_data['obj_id']);
			$objects[$obj_data['obj_id']]['description'] = $ilObjDataCache->lookupDescription($obj_data['obj_id']);
		}

		// Check if search max hits is reached
		$this->limit_reached = $res->isLimitReached();

		return $objects ? $objects : array();
	}

	function prepareType()
	{
		switch($this->getFilterType())
		{
			case 'lm':
				return array('lm','sahs','htlm');

			default:
				return array($this->getFilterType());
		}
	}

	/**
	 * Listener for SearchResultFilter
	 * Checks wheather the object is hidden and mode is not LP_MODE_DEACTIVATED
	 * @access public
	 */
	function searchFilterListener($a_ref_id,$a_data)
	{
		if($this->checkItemAnonymized())
		{
			switch($a_data['type'])
			{
				case 'tst':
					include_once './Modules/Test/classes/class.ilObjTest.php';
					if(ilObjTest::_lookupAnonymity($a_data['obj_id']))
					{
						return false;
					}
			}
		}
		if($this->isHidden($a_data['obj_id']))
		{
			return false;
		}
		if(ilLPObjSettings::_lookupMode($a_data['obj_id']) == LP_MODE_DEACTIVATED)
		{
			return false;
		}
		return true;
	}
		
}	
?>