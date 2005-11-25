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


class ilLPFilter
{
	// Default values for filter
	var $root_node = ROOT_FOLDER_ID;
	var $filter_type = 'lm';
	var $hidden = array();

	var $usr_id = null;
	var $db = null;

	function ilLPFilter($a_usr_id)
	{
		global $ilDB;

		$this->usr_id = $a_usr_id;
		$this->db =& $ilDB;
		$this->__read();
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
		

	function update()
	{
		$query = "UPDATE ut_lp_filter ".
			"SET filter_type = '".$this->getFilterType()."', ".
			"root_node = '".$this->getRootNode()."', ".
			"hidden = '".addslashes(serialize($this->getHidden()))."', ".
			"query_string = '".addslashes($this->getQueryString())."' ".
			"WHERE usr_id = '".$this->getUserId()."'";

		$res = $this->db->query($query);
		return true;
	}

	function getObjects()
	{
		if(strlen($this->getQueryString()))
		{
			return $this->__searchObjects();
		}
		else
		{
			return $this->__getAllObjects();
		}
	}


	// Static
	function _delete($a_usr_id)
	{
		global $ilDB;

		$query = "DELETE FROM ut_lp_filter ".
			"WHERE usr_id = '".$a_usr_id."'";
		$ilDB->query($query);

		return true;
	}
		
	// Private
	function __add()
	{
		$query = "INSERT INTO ut_lp_filter ".
			"SET usr_id = '".$this->getUserId()."', ".
			"filter_type = '".$this->getFilterType()."', ".
			"root_node = '".$this->getRootNode()."', ".
			"hidden = '".addslashes(serialize($this->getHidden()))."', ".
			"query_string = '".addslashes($this->getQueryString())."'";

		$this->db->query($query);

		return true;
	}


	function __read()
	{
		$query = "SELECT * FROM ut_lp_filter ".
			"WHERE usr_id = '".$this->getUserId()."'";

		$res = $this->db->query($query);

		if(!$res->numRows())
		{
			$this->__add();
		}

		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->filter_type = $row->filter_type;
			$this->root_node = $row->root_node;
			$this->hidden = unserialize(ilUtil::stripSlashes($row->hidden));
			$this->query_string = $row->query_string;
		}
	}

	function __getAllObjects()
	{
		global $tree,$ilObjDataCache;

		$objects = array();
		foreach(ilUtil::_getObjectsByOperations($this->getFilterType(),'write',$this->getUserId()) as $ref_id)
		{
			$obj_id = $ilObjDataCache->lookupObjId($ref_id);
			if($this->isHidden($obj_id))
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

		include_once './Services/Search/classes/class.ilObjectSearchFactory.php';

		$object_search =& ilObjectSearchFactory::_getObjectSearchInstance($query_parser);
		$object_search->setFilter(array($this->getFilterType()));

		$res =& $object_search->performSearch();
		#if($user_id)
		#{
		#	$res->setUserId($user_id);
		#}
		$res->filter(ROOT_FOLDER_ID,false);
		foreach($res->getResults() as $obj_data)
		{
			$objects[$obj_data['obj_id']]['ref_ids'][] = $obj_data['ref_id'];
			$objects[$obj_data['obj_id']]['title'] = $ilObjDataCache->lookupTitle($obj_data['obj_id']);
			$objects[$obj_data['obj_id']]['description'] = $ilObjDataCache->lookupDescription($obj_data['obj_id']);
		}
		return $objects ? $objects : array();
	}
}	
?>