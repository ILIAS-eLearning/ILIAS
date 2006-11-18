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
* Column for Repository
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*/
class ilColumn 
{

	private $id;
	private $blocks = array();

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
	* Set Blocks.
	*
	* @param	array of object	$a_blocks	
	*/
	public function setBlocks($a_blocks = array())
	{
		$this->blocks = $a_blocks;
	}

	/**
	* Get Blocks.
	*
	* @return	array of object	
	*/
	public function getBlocks()
	{
		return $this->blocks;
	}

	/**
	* Add Block.
	*
	* @param	object	$a_block	
	*/
	public function addBlock(&$a_block)
	{
		$this->blocks[] = $a_block;
	}

	/**
	* Clear Blocks.
	*
	*/
	public function clearBlocks()
	{
		$this->blocks = array();
	}

	/**
	* Create new item.
	*
	*/
	public function create()
	{
		global $ilDB;

		$query = "INSERT INTO il_column". 
			"(blocks) VALUES (".
			$ilDB->quote($this->getBlocks()).")";
		$ilDB->query($query);
	}

	/**
	* Read item from database.
	*
	*/
	public function read()
	{
		global $ilDB;
		
		$query = "SELECT * FROM il_column WHERE id = ".
			$ilDB->quote($this->getId());
		$set = $ilDB->query($query);
		$rec = $set->fetchRow(DB_FETCHMODE_ASSOC);

		$this->setBlocks($rec["blocks"]);

	}

	/**
	* Update item from database.
	*
	*/
	public function update()
	{
		global $ilDB;
		
		$query = "UPDATE il_column SET ".
			" blocks = ".$ilDB->quote($this->getBlocks().
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
		
		$query = "DELETE FROM il_column".
			" WHERE id = ".$ilDB->quote($this->getId());
		
		$ilDB->query($query);

	}


}
?>
