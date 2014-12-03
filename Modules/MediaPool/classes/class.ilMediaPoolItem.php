<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Media Pool Item
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ModulesMediaPool
 */
class ilMediaPoolItem
{
	/**
	 * Construtor
	 *
	 * @param	int		media pool item id
	 */
	function __construct($a_id = 0)
	{
		if ($a_id > 0)
		{
			$this->setId($a_id);
			$this->read();
		}
	}
	
	/**
	 * Set id
	 *
	 * @param	int	id
	 */
	function setId($a_val)
	{
		$this->id = $a_val;
	}
	
	/**
	 * Get id
	 *
	 * @return	int	id
	 */
	function getId()
	{
		return $this->id;
	}

	/**
	 * Set type
	 *
	 * @param	string	type
	 */
	function setType($a_val)
	{
		$this->type = $a_val;
	}
	
	/**
	 * Get type
	 *
	 * @return	string	type
	 */
	function getType()
	{
		return $this->type;
	}
	
	/**
	 * Set foreign id
	 *
	 * @param	int	foreign id
	 */
	function setForeignId($a_val)
	{
		$this->foreign_id = $a_val;
	}
	
	/**
	 * Get foreign id
	 *
	 * @return	int	foreign id
	 */
	function getForeignId()
	{
		return $this->foreign_id;
	}

	/**
	 * Set title
	 *
	 * @param	string	title
	 */
	function setTitle($a_val)
	{
		$this->title = $a_val;
	}
	
	/**
	 * Get title
	 *
	 * @return	string	title
	 */
	function getTitle()
	{
		return $this->title;
	}
	
	/**
	 * Create
	 */
	function create()
	{
		global $ilDB;
		
		$nid = $ilDB->nextId("mep_item");
		$ilDB->manipulate("INSERT INTO mep_item ".
			"(obj_id, type, foreign_id, title) VALUES (".
			$ilDB->quote($nid, "integer").",".
			$ilDB->quote($this->getType(), "text").",".
			$ilDB->quote($this->getForeignId(), "integer").",".
			$ilDB->quote($this->getTitle(), "text").
			")");
		$this->setId($nid);
	}
	
	/**
	 * Read
	 */
	function read()
	{
		global $ilDB;
		
		$set = $ilDB->query("SELECT * FROM mep_item WHERE ".
			"obj_id = ".$ilDB->quote($this->getId(), "integer")
			);
		if ($rec  = $ilDB->fetchAssoc($set))
		{
			$this->setType($rec["type"]);
			$this->setForeignId($rec["foreign_id"]);
			$this->setTitle($rec["title"]);
		}
	}
	
	/**
	 * Update
	 *
	 * @param
	 * @return
	 */
	function update()
	{
		global $ilDB;
	
		$ilDB->manipulate("UPDATE mep_item SET ".
			" type = ".$ilDB->quote($this->getType(), "text").",".
			" foreign_id = ".$ilDB->quote($this->getForeignId(), "integer").",".
			" title = ".$ilDB->quote($this->getTitle(), "text").
			" WHERE obj_id = ".$ilDB->quote($this->getId(), "integer")
			);
	}
	
	/**
	 * Delete
	 *
	 * @param
	 * @return
	 */
	function delete()
	{
		global $ilDB;
	
		$ilDB->manipulate("DELETE FROM mep_item WHERE "
			." obj_id = ".$ilDB->quote($this->getId(), "integer")
			);
	}
	
	/**
	 * Lookup
	 *
	 * @param
	 * @return
	 */
	private static function lookup($a_id, $a_field)
	{
		global $ilDB;
		
		$set = $ilDB->query("SELECT ".$a_field." FROM mep_item WHERE ".
			" obj_id = ".$ilDB->quote($a_id, "integer"));
		if ($rec = $ilDB->fetchAssoc($set))
		{
			return $rec[$a_field];
		}
		return false;
	}
	
	/**
	 * Lookup Foreign Id
	 *
	 * @param	int		mep item id
	 */
	static function lookupForeignId($a_id)
	{
		return self::lookup($a_id, "foreign_id");
	}

	/**
	 * Lookup type
	 *
	 * @param	int		mep item id
	 */
	static function lookupType($a_id)
	{
		return self::lookup($a_id, "type");
	}

	/**
	 * Lookup title
	 *
	 * @param	int		mep item id
	 */
	static function lookupTitle($a_id)
	{
		return self::lookup($a_id, "title");
	}
	
	/**
	 * Update object title
	 *
	 * @param
	 * @return
	 */
	static function updateObjectTitle($a_obj)
	{
		global $ilDB;

		if (ilObject::_lookupType($a_obj) == "mob")
		{
			$title = ilObject::_lookupTitle($a_obj);
			$ilDB->manipulate("UPDATE mep_item SET ".
				" title = ".$ilDB->quote($title, "text").
				" WHERE foreign_id = ".$ilDB->quote($a_obj, "integer").
				" AND type = ".$ilDB->quote("mob", "text")
				);
		}
	}
	
	/**
	 * Get media pools for item id
	 */
	static function getPoolForItemId($a_id)
	{
		global $ilDB;
		
		$set = $ilDB->query("SELECT * FROM mep_tree ".
			" WHERE child = ".$ilDB->quote($a_id, "integer")
			);
		$pool_ids = array();
		while ($rec  = $ilDB->fetchAssoc($set))
		{
			$pool_ids[] = $rec["mep_id"];
		}
		return $pool_ids;		// currently this array should contain only one id
	}

	/**
	 * Get all ids for type
	 *
	 * @param
	 * @return
	 */
	static function getIdsForType($a_id, $a_type)
	{
		global $ilDB;

		$set = $ilDB->query("SELECT mep_tree.child as id".
			" FROM mep_tree JOIN mep_item ON (mep_tree.child = mep_item.obj_id) WHERE ".
			" mep_tree.mep_id = ".$ilDB->quote($a_id, "integer")." AND ".
			" mep_item.type = ".$ilDB->quote($a_type, "text")
		);

		$ids = array();
		while ($rec = $ilDB->fetchAssoc($set))
		{
			$ids[] = $rec["id"];
		}
		return $ids;
	}

}
?>