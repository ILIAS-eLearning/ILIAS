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

define("MCST_USERS", "users");
define("MCST_PUBLIC", "public");

/**
* Item of a media case
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*/
class ilMediaCastItem 
{

	protected $id;
	protected $mcst_id = 0;
	protected $mob_id = 0;
	protected $creation_date;
	protected $update_date;
	protected $update_user;
	protected $length;
	protected $title;
	protected $description;
	protected $visibility = "users";

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
	* Set McstId.
	*
	* @param	int	$a_mcst_id	Media Cast Object ID
	*/
	public function setMcstId($a_mcst_id = 0)
	{
		$this->mcst_id = $a_mcst_id;
	}

	/**
	* Get McstId.
	*
	* @return	int	Media Cast Object ID
	*/
	public function getMcstId()
	{
		return $this->mcst_id;
	}

	/**
	* Set MobId.
	*
	* @param	int	$a_mob_id	Media Object ID
	*/
	public function setMobId($a_mob_id = 0)
	{
		$this->mob_id = $a_mob_id;
	}

	/**
	* Get MobId.
	*
	* @return	int	Media Object ID
	*/
	public function getMobId()
	{
		return $this->mob_id;
	}

	/**
	* Set CreationDate.
	*
	* @param	string	$a_creation_date	Creation Date
	*/
	public function setCreationDate($a_creation_date)
	{
		$this->creation_date = $a_creation_date;
	}

	/**
	* Get CreationDate.
	*
	* @return	string	Creation Date
	*/
	public function getCreationDate()
	{
		return $this->creation_date;
	}

	/**
	* Set UpdateDate.
	*
	* @param	string	$a_update_date	Last Update Date
	*/
	public function setUpdateDate($a_update_date)
	{
		$this->update_date = $a_update_date;
	}

	/**
	* Get UpdateDate.
	*
	* @return	string	Last Update Date
	*/
	public function getUpdateDate()
	{
		return $this->update_date;
	}

	/**
	* Set UpdateUser.
	*
	* @param	int	$a_update_user	Update User
	*/
	public function setUpdateUser($a_update_user)
	{
		$this->update_user = $a_update_user;
	}

	/**
	* Get UpdateUser.
	*
	* @return	int	Update User
	*/
	public function getUpdateUser()
	{
		return $this->update_user;
	}

	/**
	* Set Length.
	*
	* @param	string	$a_length	Length (hh:mm:ss)
	*/
	public function setLength($a_length)
	{
		$this->length = $a_length;
	}

	/**
	* Get Length.
	*
	* @return	string	Length (hh:mm:ss)
	*/
	public function getLength()
	{
		return $this->length;
	}

	/**
	* Set Title.
	*
	* @param	string	$a_title	Title
	*/
	public function setTitle($a_title)
	{
		$this->title = $a_title;
	}

	/**
	* Get Title.
	*
	* @return	string	Title
	*/
	public function getTitle()
	{
		return $this->title;
	}

	/**
	* Set Description.
	*
	* @param	string	$a_description	Description
	*/
	public function setDescription($a_description)
	{
		$this->description = $a_description;
	}

	/**
	* Get Description.
	*
	* @return	string	Description
	*/
	public function getDescription()
	{
		return $this->description;
	}

	/**
	* Set Visibility.
	*
	* @param	string	$a_visibility	Access level for item
	*/
	public function setVisibility($a_visibility = "users")
	{
		$this->visibility = $a_visibility;
	}

	/**
	* Get Visibility.
	*
	* @return	string	Access level for item
	*/
	public function getVisibility()
	{
		return $this->visibility;
	}

	/**
	* Create new item.
	*
	*/
	public function create()
	{
		global $ilDB;
		
		$query = "INSERT INTO il_media_cast_item (".
			" mcst_id".
			", mob_id".
			", creation_date".
			", update_date".
			", update_user".
			", length".
			", title".
			", description".
			", visibility".
			" ) VALUES (".
			$ilDB->quote($this->getMcstId())
			.",".$ilDB->quote($this->getMobId())
			.","."now()"
			.","."now()"
			.",".$ilDB->quote($this->getUpdateUser())
			.",".$ilDB->quote($this->getLength())
			.",".$ilDB->quote($this->getTitle())
			.",".$ilDB->quote($this->getDescription())
			.",".$ilDB->quote($this->getVisibility()).")";
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
		
		$query = "SELECT * FROM il_media_cast_item WHERE id = ".
			$ilDB->quote($this->getId());
		$set = $ilDB->query($query);
		$rec = $set->fetchRow(DB_FETCHMODE_ASSOC);

		$this->setMcstId($rec["mcst_id"]);
		$this->setMobId($rec["mob_id"]);
		$this->setCreationDate($rec["creation_date"]);
		$this->setUpdateDate($rec["update_date"]);
		$this->setUpdateUser($rec["update_user"]);
		$this->setLength($rec["length"]);
		$this->setTitle($rec["title"]);
		$this->setDescription($rec["description"]);
		$this->setVisibility($rec["visibility"]);

	}

	/**
	* Update item in database.
	*
	*/
	public function update()
	{
		global $ilDB;
		
		$query = "UPDATE il_media_cast_item SET ".
			" mcst_id = ".$ilDB->quote($this->getMcstId()).
			", mob_id = ".$ilDB->quote($this->getMobId()).
			", creation_date = ".$ilDB->quote($this->getCreationDate()).
			", update_date = now()".
			", update_user = ".$ilDB->quote($this->getUpdateUser()).
			", length = ".$ilDB->quote($this->getLength()).
			", title = ".$ilDB->quote($this->getTitle()).
			", description = ".$ilDB->quote($this->getDescription()).
			", visibility = ".$ilDB->quote($this->getVisibility()).
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
		
		$query = "DELETE FROM il_media_cast_item".
			" WHERE id = ".$ilDB->quote($this->getId());
		
		$ilDB->query($query);

	}

	/**
	* Query ItemsForCast
	*
	*/
	public function queryItemsForCast()
	{
		global $ilDB;
		
		$query = "SELECT id, mob_id, creation_date, update_date, update_user, length, title, description, visibility ".
			"FROM il_media_cast_item ".
			"WHERE ".
				"mcst_id = ".$ilDB->quote($this->getMcstId()).
				" ORDER BY creation_date DESC ".
				"";
				
		$set = $ilDB->query($query);
		$result = array();
		while($rec = $set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$result[] = $rec;
		}
		
		return $result;

	}


}
?>
