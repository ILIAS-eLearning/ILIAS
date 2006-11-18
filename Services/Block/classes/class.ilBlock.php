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
* Superclass for all blocks.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*/
class ilBlock 
{

	private $id;
	private $type;
	private $title;

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
	* @param	string	$a_title	Title of the block.
	*/
	public function setTitle($a_title)
	{
		$this->title = $a_title;
	}

	/**
	* Get Title.
	*
	* @return	string	Title of the block.
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

		$query = "INSERT INTO il_block". 
			"(type,".
			"title) VALUES (".
			$ilDB->quote($this->getType()).",".
			$ilDB->quote($this->getTitle()).")";
		$ilDB->query($query);
	}

	/**
	* Read item from database.
	*
	*/
	public function read()
	{
		global $ilDB;
		
		$query = "SELECT * FROM il_block WHERE id = ".
			$ilDB->quote($this->getId());
		$set = $ilDB->query($query);
		$rec = $set->fetchRow(DB_FETCHMODE_ASSOC);

		$this->setType($rec["type"]);
		$this->setTitle($rec["title"]);

	}

	/**
	* Update item from database.
	*
	*/
	public function update()
	{
		global $ilDB;
		
		$query = "UPDATE il_block SET ".
			" type = ".$ilDB->quote($this->getType().
			", title = ".$ilDB->quote($this->getTitle().
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
		
		$query = "DELETE FROM il_block".
			" WHERE id = ".$ilDB->quote($this->getId());
		
		$ilDB->query($query);

	}


}
?>
