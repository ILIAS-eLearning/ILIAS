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
* This is the super class of all custom blocks.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*/
class ilCustomBlock 
{

	protected $id;
	protected $context_obj_id;
	protected $context_obj_type;
	protected $context_sub_obj_id;
	protected $context_sub_obj_type;
	protected $type;
	protected $title;

	/**
	* Constructor.
	*
	* @param	int	$a_id	
	*/
	public function __construct($a_id = 0)
	{
		if ($a_id > 0)
		{
			$this->setId($a_id);
			$this->read();
		}

	}

	/**
	* Set Id.
	*
	* @param	int	$a_id	
	*/
	public function setId($a_id)
	{
		$this->id = $a_id;
	}

	/**
	* Get Id.
	*
	* @return	int	
	*/
	public function getId()
	{
		return $this->id;
	}

	/**
	* Set ContextObjId.
	*
	* @param	int	$a_context_obj_id	
	*/
	public function setContextObjId($a_context_obj_id)
	{
		$this->context_obj_id = $a_context_obj_id;
	}

	/**
	* Get ContextObjId.
	*
	* @return	int	
	*/
	public function getContextObjId()
	{
		return $this->context_obj_id;
	}

	/**
	* Set ContextObjType.
	*
	* @param	int	$a_context_obj_type	
	*/
	public function setContextObjType($a_context_obj_type)
	{
		$this->context_obj_type = $a_context_obj_type;
	}

	/**
	* Get ContextObjType.
	*
	* @return	int	
	*/
	public function getContextObjType()
	{
		return $this->context_obj_type;
	}

	/**
	* Set ContextSubObjId.
	*
	* @param	int	$a_context_sub_obj_id	
	*/
	public function setContextSubObjId($a_context_sub_obj_id)
	{
		$this->context_sub_obj_id = $a_context_sub_obj_id;
	}

	/**
	* Get ContextSubObjId.
	*
	* @return	int	
	*/
	public function getContextSubObjId()
	{
		return $this->context_sub_obj_id;
	}

	/**
	* Set ContextSubObjType.
	*
	* @param	int	$a_context_sub_obj_type	
	*/
	public function setContextSubObjType($a_context_sub_obj_type)
	{
		$this->context_sub_obj_type = $a_context_sub_obj_type;
	}

	/**
	* Get ContextSubObjType.
	*
	* @return	int	
	*/
	public function getContextSubObjType()
	{
		return $this->context_sub_obj_type;
	}

	/**
	* Set Type.
	*
	* @param	string	$a_type	Type of block.
	*/
	public function setType($a_type)
	{
		$this->type = $a_type;
	}

	/**
	* Get Type.
	*
	* @return	string	Type of block.
	*/
	public function getType()
	{
		return $this->type;
	}

	/**
	* Set Title.
	*
	* @param	string	$a_title	Title of block
	*/
	public function setTitle($a_title)
	{
		$this->title = $a_title;
	}

	/**
	* Get Title.
	*
	* @return	string	Title of block
	*/
	public function getTitle()
	{
		return $this->title;
	}

	/**
	* Create new item.
	*
	*/
	public function create()
	{
		global $ilDB;
		
		$query = "INSERT INTO il_custom_block (".
			" context_obj_id".
			", context_obj_type".
			", context_sub_obj_id".
			", context_sub_obj_type".
			", type".
			", title".
			" ) VALUES (".
			$ilDB->quote($this->getContextObjId())
			.",".$ilDB->quote($this->getContextObjType())
			.",".$ilDB->quote($this->getContextSubObjId())
			.",".$ilDB->quote($this->getContextSubObjType())
			.",".$ilDB->quote($this->getType())
			.",".$ilDB->quote($this->getTitle()).")";
		$ilDB->query($query);
		$this->setId($ilDB->getLastInsertId());
		

	}

	/**
	* Read item from database.
	*
	*/
	public function read()
	{
		global $ilDB;
		
		$query = "SELECT * FROM il_custom_block WHERE id = ".
			$ilDB->quote($this->getId());
		$set = $ilDB->query($query);
		$rec = $set->fetchRow(MDB2_FETCHMODE_ASSOC);

		$this->setContextObjId($rec["context_obj_id"]);
		$this->setContextObjType($rec["context_obj_type"]);
		$this->setContextSubObjId($rec["context_sub_obj_id"]);
		$this->setContextSubObjType($rec["context_sub_obj_type"]);
		$this->setType($rec["type"]);
		$this->setTitle($rec["title"]);

	}

	/**
	* Update item in database.
	*
	*/
	public function update()
	{
		global $ilDB;
		
		$query = "UPDATE il_custom_block SET ".
			" context_obj_id = ".$ilDB->quote($this->getContextObjId()).
			", context_obj_type = ".$ilDB->quote($this->getContextObjType()).
			", context_sub_obj_id = ".$ilDB->quote($this->getContextSubObjId()).
			", context_sub_obj_type = ".$ilDB->quote($this->getContextSubObjType()).
			", type = ".$ilDB->quote($this->getType()).
			", title = ".$ilDB->quote($this->getTitle()).
			" WHERE id = ".$ilDB->quote($this->getId());
		
		$ilDB->query($query);

	}

	/**
	* Delete item from database.
	*
	*/
	public function delete()
	{
		global $ilDB;
		
		$query = "DELETE FROM il_custom_block".
			" WHERE id = ".$ilDB->quote($this->getId());
		
		$ilDB->query($query);

	}

	/**
	* Query getBlocksForContext
	*
	*/
	public function querygetBlocksForContext()
	{
		global $ilDB;
		
		$query = "SELECT id, context_obj_id, context_obj_type, context_sub_obj_id, context_sub_obj_type, type, title ".
			"FROM il_custom_block ".
			"WHERE ".
				"context_obj_id = ".$ilDB->quote($this->getContextObjId()).
				" AND context_obj_type = ".$ilDB->quote($this->getContextObjType()).
				" AND context_sub_obj_id = ".$ilDB->quote($this->getContextSubObjId()).
				" AND context_sub_obj_type = ".$ilDB->quote($this->getContextSubObjType())."";
				
		$set = $ilDB->query($query);
		$result = array();
		while($rec = $set->fetchRow(MDB2_FETCHMODE_ASSOC))
		{
			$result[] = $rec;
		}
		
		return $result;

	}

	/**
	* Query BlocksForContext
	*
	*/
	public function queryBlocksForContext()
	{
		global $ilDB;
		
		$query = "SELECT id, context_obj_id, context_obj_type, context_sub_obj_id, context_sub_obj_type, type, title ".
			"FROM il_custom_block ".
			"WHERE ".
				"context_obj_id = ".$ilDB->quote($this->getContextObjId()).
				" AND context_obj_type = ".$ilDB->quote($this->getContextObjType()).
				" AND context_sub_obj_id = ".$ilDB->quote($this->getContextSubObjId()).
				" AND context_sub_obj_type = ".$ilDB->quote($this->getContextSubObjType())."";
				
		$set = $ilDB->query($query);
		$result = array();
		while($rec = $set->fetchRow(MDB2_FETCHMODE_ASSOC))
		{
			$result[] = $rec;
		}
		
		return $result;

	}

	/**
	* Query TitleForId
	*
	*/
	public function queryTitleForId()
	{
		global $ilDB;
		
		$query = "SELECT id ".
			"FROM il_custom_block ".
			"WHERE "."";
				
		$set = $ilDB->query($query);
		$result = array();
		while($rec = $set->fetchRow(MDB2_FETCHMODE_ASSOC))
		{
			$result[] = $rec;
		}
		
		return $result;

	}

	/**
	* Query CntBlockForContext
	*
	*/
	public function queryCntBlockForContext()
	{
		global $ilDB;
		
		$query = "SELECT count(*) as cnt ".
			"FROM il_custom_block ".
			"WHERE ".
				"context_obj_id = ".$ilDB->quote($this->getContextObjId()).
				" AND context_obj_type = ".$ilDB->quote($this->getContextObjType()).
				" AND context_sub_obj_id = ".$ilDB->quote($this->getContextSubObjId()).
				" AND context_sub_obj_type = ".$ilDB->quote($this->getContextSubObjType()).
				" AND type = ".$ilDB->quote($this->getType())."";
				
		$set = $ilDB->query($query);
		$result = array();
		while($rec = $set->fetchRow(MDB2_FETCHMODE_ASSOC))
		{
			$result[] = $rec;
		}
		
		return $result;

	}


}
?>
