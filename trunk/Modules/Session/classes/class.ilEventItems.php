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
* class ilEvent
*
* @author Stefan Meyer <meyer@leifos.com> 
* @version $Id: class.ilEventItems.php 15697 2008-01-08 20:04:33Z hschottm $
* 
*/


class ilEventItems
{
	var $ilErr;
	var $ilDB;
	var $tree;
	var $lng;

	var $event_id = null;
	var $items = array();


	function ilEventItems($a_event_id)
	{
		global $ilErr,$ilDB,$lng,$tree;

		$this->ilErr =& $ilErr;
		$this->db  =& $ilDB;
		$this->lng =& $lng;

		$this->event_id = $a_event_id;
		$this->__read();
	}

	function getEventId()
	{
		return $this->event_id;
	}
	function setEventId($a_event_id)
	{
		$this->event_id = $a_event_id;
	}
	
	/**
	 * get assigned items
	 * @return array	$items	Assigned items.
	 */
	function getItems()
	{
		return $this->items ? $this->items : array();
	}
	function setItems($a_items)
	{
		$this->items = array();
		foreach($a_items as $item_id)
		{
			$this->items[] = (int) $item_id;
		}
	}
	
	
	/**
	 * Add one item
	 * @param object $a_item_ref_id
	 * @return 
	 */
	public function addItem($a_item_ref_id)
	{
		$this->items[] = (int) $a_item_ref_id;
	}
	
	
	function delete()
	{
		return ilEventItems::_delete($this->getEventId());
	}
	
	function _delete($a_event_id)
	{
		global $ilDB;

		$query = "DELETE FROM event_items ".
			"WHERE event_id = ".$ilDB->quote($a_event_id ,'integer')." ";
		$res = $ilDB->manipulate($query);
		return true;
	}
	
	function update()
	{
		global $ilDB;
		
		$this->delete();
		
		foreach($this->items as $item)
		{
			$query = "INSERT INTO event_items (event_id,item_id) ".
				"VALUES( ".
				$ilDB->quote($this->getEventId() ,'integer').", ".
				$ilDB->quote($item ,'integer')." ".
				")";
			$res = $ilDB->manipulate($query);
		}
		return true;
	}
	
	function _getItemsOfContainer($a_ref_id)
	{
		global $ilDB,$tree;
		
		$session_nodes = $tree->getChildsByType($a_ref_id,'sess');
		foreach($session_nodes as $node)
		{
			$session_ids[] = $node['obj_id'];
		}
		$query = "SELECT item_id FROM event_items ".
			"WHERE ".$ilDB->in('event_id',$session_ids,false,'integer');
			

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$items[] = $row->item_id;
		}
		return $items ? $items : array();
	}
	
	/**
	 * Get items by event 
	 *
	 * @access public
	 * @static
	 *
	 * @param int event id
	 */
	public static function _getItemsOfEvent($a_event_id)
	{
		global $ilDB;
		
		$query = "SELECT * FROM event_items ".
			"WHERE event_id = ".$ilDB->quote($a_event_id ,'integer');
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$items[] = $row->item_id;
		}
		return $items ? $items : array();
	}

	function _isAssigned($a_item_id)
	{
		global $ilDB;

		$query = "SELECT * FROM event_items ".
			"WHERE item_id = ".$ilDB->quote($a_item_id ,'integer')." ";
		$res = $ilDB->query($query);

		return $res->numRows() ? true : false;
	}
	
	/**
	 * Clone items
	 *
	 * @access public
	 *
	 * @param int source event id
	 * @param int copy id
	 */
	public function cloneItems($a_source_id,$a_copy_id)
	{
		global $ilObjDataCache,$ilLog;
		
		$ilLog->write(__METHOD__.': Begin cloning session materials ...');
		
	 	include_once('Services/CopyWizard/classes/class.ilCopyWizardOptions.php');
	 	$cwo = ilCopyWizardOptions::_getInstance($a_copy_id);
	 	$mappings = $cwo->getMappings();
		
		$new_items = array(); 
		foreach(ilEventItems::_getItemsOfEvent($a_source_id) as $item_id)
		{
	 		if(isset($mappings[$item_id]) and $mappings[$item_id])
	 		{
				$ilLog->write(__METHOD__.': Clone session material nr. '.$item_id);
				$new_items[] = $mappings[$item_id];
	 		}
	 		else
	 		{
				$ilLog->write(__METHOD__.': No mapping found for session material nr. '.$item_id);
	 		}
		}
		$this->setItems($new_items);
		$this->update();
		$ilLog->write(__METHOD__.': Finished cloning session materials ...');
		return true;
	}


	// PRIVATE
	function __read()
	{
		global $ilDB,$tree;
		
		$query = "SELECT * FROM event_items ".
			"WHERE event_id = ".$ilDB->quote($this->getEventId() ,'integer')." ";

		$res = $this->db->query($query);
		$this->items = array();
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			if($tree->isDeleted($row->item_id))
			{
				continue;
			}
			if(!$tree->isInTree($row->item_id))
			{
				$query = "DELETE FROM event_items ".
					"WHERE item_id = ".$ilDB->quote($row->item_id ,'integer');
				$ilDB->manipulate($query);
				continue;
			}
			
			$this->items[] = (int) $row->item_id;
		}
		return true;
	}
		
}
?>