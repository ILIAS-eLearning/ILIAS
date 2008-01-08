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

include_once("./Services/Block/classes/class.ilCustomBlock.php");

/**
* A HTML block allows to present simple HTML within a block.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*/
class ilHtmlBlock extends ilCustomBlock
{

	protected $content;

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
	* Set Content.
	*
	* @param	string	$a_content	HTML content of the block.
	*/
	public function setContent($a_content)
	{
		$this->content = $a_content;
	}

	/**
	* Get Content.
	*
	* @return	string	HTML content of the block.
	*/
	public function getContent()
	{
		return $this->content;
	}

	/**
	* Create new item.
	*
	*/
	public function create()
	{
		global $ilDB;
		
		parent::create();
		
		$query = "INSERT INTO il_html_block (".
			" id".
			", content".
			" ) VALUES (".
			$ilDB->quote($this->getId())
			.",".$ilDB->quote($this->getContent()).")";
		$ilDB->query($query);
		

	}

	/**
	* Read item from database.
	*
	*/
	public function read()
	{
		global $ilDB;
		
		parent::read();
		
		$query = "SELECT * FROM il_html_block WHERE id = ".
			$ilDB->quote($this->getId());
		$set = $ilDB->query($query);
		$rec = $set->fetchRow(MDB2_FETCHMODE_ASSOC);

		$this->setContent($rec["content"]);

	}

	/**
	* Update item in database.
	*
	*/
	public function update()
	{
		global $ilDB;
		
		parent::update();
		
		$query = "UPDATE il_html_block SET ".
			" content = ".$ilDB->quote($this->getContent()).
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
		
		parent::delete();
		
		$query = "DELETE FROM il_html_block".
			" WHERE id = ".$ilDB->quote($this->getId());
		
		$ilDB->query($query);

	}


}
?>
