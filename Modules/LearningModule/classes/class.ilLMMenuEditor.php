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
* class for editing lm menu
*
* @author Sascha Hofmann <saschahofmann@gmx.de>
* @version $Id$
*
* @ingroup ModulesIliasLearningModule
*/
class ilLMMenuEditor
{
	function ilLMMenuEditor()
	{
		global $ilDB;

		$this->db =& $ilDB;
		$this->link_type = "extern";
		$this->link_ref_id = null;
	}

	function setObjId($a_obj_id)
	{
		$this->lm_id = $a_obj_id;
	}

	function getObjId()
	{
		return $this->lm_id;
	}

	function setEntryId($a_id)
	{
		$this->entry_id = $a_id;
	}

	function getEntryId()
	{
		return $this->entry_id;
	}

	function setLinkType($a_link_type)
	{
		$this->link_type = $a_link_type;	
	}
	
	function getLinkType()
	{
		return $this->link_type;
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
	
	function setLinkRefId($a_link_ref_id)
	{
		$this->link_ref_id = $a_link_ref_id;
	}

	function getLinkRefId()
	{
		return $this->link_ref_id;
	}

	function create()
	{
		$q = "INSERT INTO lm_menu (lm_id,link_type,title,target,link_ref_id) ".
			 "VALUES ".
			 "(".
			 $this->db->quote($this->getObjId()).",".
			 $this->db->quote($this->getLinkType()).",".
 			 $this->db->quote($this->getTitle()).",".
 			 $this->db->quote($this->getTarget()).",".
			 $this->db->quote($this->getLinkRefId()).")";
		$r = $this->db->query($q);
		
		return true;
	}
	
	function getMenuEntries($a_only_active = false)
	{
		global $ilDB;
		
		$entries = array();
		
		if ($a_only_active === true)
		{
			$and = " AND active = 'y'";
		}
		
		$q = "SELECT * FROM lm_menu ".
			 "WHERE lm_id = ".$ilDB->quote($this->lm_id).
			 $and;
			 
		$r = $this->db->query($q);

		while($row = $r->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$entries[] = array('id'		=> $row->id,
							   'title'	=> $row->title,
							   'link'	=> $row->target,
							   'type'	=> $row->link_type,
							   'ref_id'	=> $row->link_ref_id,
							   'active'	=> $row->active
							   );
		}

		return $entries;
	}
	
	/**
	 * delete menu entry
	 * 
	 */
	function delete($a_id)
	{
		if (!$a_id)
		{
			return false;
		}
		
		$q = "DELETE FROM lm_menu WHERE id = ".$this->db->quote($a_id);
		$this->db->query($q);
		
		return true;
	}
	
	/**
	 * update menu entry
	 * 
	 */
	function update()
	{
		global $ilDB;
		
		$q = "UPDATE lm_menu SET ".
			" link_type = ".$ilDB->quote($this->getLinkType()).",".
			" title = ".$ilDB->quote($this->getTitle()).",".
			" target = ".$ilDB->quote($this->getTarget()).",".
			" link_ref_id = ".$ilDB->quote($this->getLinkRefId()).
			" WHERE id = ".$ilDB->quote($this->getEntryId());
		$r = $this->db->query($q);
		
		return true;
	}
	
	function readEntry($a_id)
	{
		if (!$a_id)
		{
			return false;
		}
		
		$q = "SELECT * FROM lm_menu WHERE id = ".$this->db->quote($a_id);
		$r = $this->db->query($q);

		$row = $this->db->getRow($q,DB_FETCHMODE_OBJECT);
		
		$this->setTitle($row->title);
		$this->setTarget($row->target);
		$this->setLinkType($row->link_type);
		$this->setLinkRefId($row->link_ref_id);
		$this->setEntryid($a_id);
	}
	
	/**
	 * update active status of all menu entries of lm
	 * @param	array	entry ids
	 * 
	 */
	function updateActiveStatus($a_entries)
	{
		global $ilDB;
		
		if (!is_array($a_entries))
		{
			return false;
		}
		
		// update active status
		$q = "UPDATE lm_menu SET " .
			 "active = CASE " .
			 "WHEN id IN (".implode(',',ilUtil::quoteArray($a_entries)).") " .
			 "THEN 'y' ".
			 "ELSE 'n' ".
			 "END " .
			 "WHERE lm_id = ".$ilDB->quote($this->lm_id);
		$this->db->query($q);
	}
	
}
?>
