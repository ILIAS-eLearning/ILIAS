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
	private $user;
	private $setting;

	/**
	* Constructor.
	*
	* @param	int	$a_id	
	*/
	public function __construct()
	{
	}

	/**
	* Set Type.
	*
	* @param	string	$a_type	
	*/
	public function setType($a_type)
	{
		$this->type = $a_type;
	}

	/**
	* Get Type.
	*
	* @return	string	
	*/
	public function getType()
	{
		return $this->type;
	}

	/**
	* Set User.
	*
	* @param	int	$a_user	
	*/
	public function setUser($a_user)
	{
		$this->user = $a_user;
	}

	/**
	* Get User.
	*
	* @return	int	
	*/
	public function getUser()
	{
		return $this->user;
	}

	/**
	* Set Setting.
	*
	* @param	string	$a_setting	
	*/
	public function setSetting($a_setting)
	{
		$this->setting = $a_setting;
	}

	/**
	* Get Setting.
	*
	* @return	string	
	*/
	public function getSetting()
	{
		return $this->setting;
	}

	/**
	* Create new item.
	*
	*/
	public function create()
	{
		global $ilDB;
		
		$query = "INSERT INTO il_block (".
			" type".
			", user".
			", setting".
			" ) VALUES (".
			$ilDB->quote($this->getType())
			.",".$ilDB->quote($this->getUser())
			.",".$ilDB->quote($this->getSetting()).")";
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
		$this->setUser($rec["user"]);
		$this->setSetting($rec["setting"]);

	}

	/**
	* Update item from database.
	*
	*/
	public function update()
	{
		global $ilDB;
		
		$query = "UPDATE il_block SET ".
			" type = ".$ilDB->quote($this->getType()).
			", user = ".$ilDB->quote($this->getUser()).
			", setting = ".$ilDB->quote($this->getSetting()).
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
