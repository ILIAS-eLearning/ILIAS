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
* Class ilObjLinkResourceGUI
*
* @author Stefan Meyer <smeyer@databay.de> 
* @version $Id$
* 
* @extends ilObjectGUI
* @package ilias-core
*/

include_once "./classes/class.ilObjectGUI.php";

class ilLinkResourceItems
{
	/**
	* Constructor
	* @access public
	*/
	function ilLinkResourceItems($webr_id)
	{
		global $ilDB;

		$this->webr_ref_id = $webr_ref_id;
		$this->webr_id = $webr_id;

		$this->db =& $ilDB;
	}

	// SET GET
	function setLinkResourceRefId($a_ref_id)
	{
		$this->webr_ref_id = $a_ref_id;
	}
	function getLinkResourceRefId()
	{
		return $this->webr_ref_id;
	}
	function setLinkResourceId($a_id)
	{
		$this->webr_id = $a_id;
	}
	function getLinkResourceId()
	{
		return $this->webr_id;
	}
	function setLinkId($a_id)
	{
		$this->id = $a_id;
	}
	function getLinkId()
	{
		return $this->id;
	}
	function setTitle($a_title)
	{
		$this->title = $a_title;
	}
	function getTitle()
	{
		return $this->title;
	}
	function setTarget($a_target)
	{
		$this->target = $a_target;
	}
	function getTarget()
	{
		return $this->target;
	}
	function setActiveStatus($a_status)
	{
		$this->status = (int) $a_status;
	}
	function getActiveStatus()
	{
		return (bool) $this->status;
	}
	function setDisableCheckStatus($a_status)
	{
		$this->check = (int) $a_status;
	}
	function getDisableCheckStatus()
	{
		return (bool) $this->check;
	}
	// PRIVATE
	function __setCreateDate($a_date)
	{
		$this->c_date = $a_date;
	}
	function getCreateDate()
	{
		return $this->c_date;
	}
	// PRIVATE
	function __setLastUpdateDate($a_date)
	{
		$this->m_date = $a_date;
	}
	function getLastUpdateDate()
	{
		return $this->m_date;
	}
	function setLastCheckDate($a_date)
	{
		$this->check_date = $a_date;
	}
	function getLastCheckDate()
	{
		return $this->check_date;
	}
	function setValidStatus($a_status)
	{
		$this->valid = (int) $a_status;
	}
	function getValidStatus()
	{
		return (bool) $this->valid;
	}

	function delete($a_item_id,$a_update_history = true)
	{
		$query = "DELETE FROM webr_items ".
			"WHERE webr_id = '".$this->getLinkResourceId()."' ".
			"AND link_id = '".$a_item_id."'";

		$this->db->query($query);

		if($a_update_history)
		{
			include_once("classes/class.ilHistory.php");
			ilHistory::_createEntry($this->getLinkResourceId(), "delete",
									$this->getTitle());
		}

		return true;
	}

	function update($a_update_history = true)
	{
		if(!$this->getLinkId())
		{
			return false;
		}

		$this->__setLastUpdateDate(time());
		$query = "UPDATE webr_items ".
			"SET title = '".ilUtil::prepareDBString($this->getTitle())."', ".
			"target = '".ilUtil::prepareDBString($this->getTarget())."', ".
			"active = '".$this->getActiveStatus()."', ".
			"valid = '".$this->getValidStatus()."', ".
			"disable_check = '".$this->getDisableCheckStatus()."', ".
			"last_update = '".$this->getLastUpdateDate()."' ".
			"WHERE link_id = '".$this->getLinkId()."' ".
			"AND webr_id = '".$this->getLinkResourceId()."'";

		$this->db->query($query);

		if($a_update_history)
		{
			include_once("classes/class.ilHistory.php");
			ilHistory::_createEntry($this->getLinkResourceId(), "update",
									$this->getTitle());
		}

		return true;
	}

	function updateValid($a_status)
	{
		$query = "UPDATE webr_items ".
			"SET valid = '".$a_status."' ".
			"WHERE link_id = '".$this->getLinkId()."'";

		$this->db->query($query);

		return true;
	}

	function updateActive($a_status)
	{
		$query = "UPDATE webr_items ".
			"SET active = '".$a_status."' ".
			"WHERE link_id = '".$this->getLinkId()."'";

		$this->db->query($query);

		return true;
	}
	function updateDisableCheck($a_status)
	{
		$query = "UPDATE webr_items ".
			"SET disable_check = '".$a_status."' ".
			"WHERE link_id = '".$this->getLinkId()."'";

		$this->db->query($query);

		return true;
	}

	function updateLastCheck()
	{
		$query = "UPDATE webr_items ".
			"SET last_check = '".time()."' ".
			"WHERE webr_id = '".$this->getLinkResourceId()."' ".
			"AND disable_check = '0'";

		$this->db->query($query);

		return true;
	}

	function add($a_update_history = true)
	{
		$this->__setLastUpdateDate(time());
		$this->__setCreateDate(time());

		$query = "INSERT INTO webr_items ".
			"SET title = '".ilUtil::prepareDBString($this->getTitle())."', ".
			"target = '".ilUtil::prepareDBString($this->getTarget())."', ".
			"active = '".$this->getActiveStatus()."', ".
			"disable_check = '".$this->getDisableCheckStatus()."', ".
			"last_update = '".$this->getLastUpdateDate()."', ".
			"create_date = '".$this->getCreateDate()."', ".
			"webr_id = '".$this->getLinkResourceId()."'";

		$this->db->query($query);

		if($a_update_history)
		{
			include_once("classes/class.ilHistory.php");
			ilHistory::_createEntry($this->getLinkResourceId(), "add",
									$this->getTitle());
		}

		return true;
	}
	function readItem($a_link_id)
	{
		$query = "SELECT * FROM webr_items ".
			"WHERE link_id = '".$a_link_id."'";

		$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->setTitle($row->title);
			$this->setTarget($row->target);
			$this->setActiveStatus($row->active);
			$this->setDisableCheckStatus($row->disable_check);
			$this->__setCreateDate($row->create_date);
			$this->__setLastUpdateDate($row->last_update);
			$this->setValidStatus($row->valid);
			$this->setLinkId($row->link_id);
		}
		return true;
	}


	function getItem($a_link_id)
	{

		$query = "SELECT * FROM webr_items ".
			"WHERE webr_id = '".$this->getLinkResourceId()."' ".
			"AND link_id = '".$a_link_id."'";

		$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$item['title']				= $row->title;
			$item['target']				= $row->target;
			$item['active']				= (bool) $row->active;
			$item['disable_check']		= $row->disable_check;
			$item['create_date']		= $row->create_date;
			$item['last_update']		= $row->last_update;
			$item['last_check']			= $row->last_check;
			$item['valid']				= $row->valid;
			$item['link_id']			= $row->link_id;
		}
		return $item ? $item : array();
	}
		
		
	function getAllItems()
	{
		$query = "SELECT * FROM webr_items ".
			"WHERE webr_id = '".$this->getLinkResourceId()."'";

		$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$items[$row->link_id]['title']				= $row->title;
			$items[$row->link_id]['target']				= $row->target;
			$items[$row->link_id]['active']				= (bool) $row->active;
			$items[$row->link_id]['disable_check']		= $row->disable_check;
			$items[$row->link_id]['create_date']		= $row->create_date;
			$items[$row->link_id]['last_update']		= $row->last_update;
			$items[$row->link_id]['last_check']			= $row->last_check;
			$items[$row->link_id]['valid']				= $row->valid;
			$items[$row->link_id]['link_id']			= $row->link_id;
		}
		return $items ? $items : array();
	}
	function getActivatedItems()
	{
		foreach($this->getAllItems() as $id => $item_data)
		{
			if($item_data['active'])
			{
				$active_items[$id] = $item_data;
			}
		}
		return $active_items ? $active_items : array();
	}

	function getCheckItems()
	{
		foreach($this->getAllItems() as $id => $item_data)
		{
			if(!$item_data['disable_check'])
			{
				$check_items[$id] = $item_data;
			}
		}
		return $check_items ? $check_items : array();
	}
		


	// STATIC
	function _deleteAll($webr_id)
	{
		global $ilDB;
		
		$ilDB->query("DELETE FROM webr_items WHERE webr_id = '".$webr_id."'");

		return true;
	}
}
		
?>
