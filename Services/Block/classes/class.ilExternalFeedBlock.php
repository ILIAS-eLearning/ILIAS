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
* Custom block for external feeds.
*
* @author alex killing <alex.killing@gmx.de
* @version $Id$
*/
class ilExternalFeedBlock extends ilCustomBlock
{

	protected $feed_url;

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
	* Set FeedUrl.
	*
	* @param	string	$a_feed_url	URL of the external news feed.
	*/
	public function setFeedUrl($a_feed_url)
	{
		$this->feed_url = $a_feed_url;
	}

	/**
	* Get FeedUrl.
	*
	* @return	string	URL of the external news feed.
	*/
	public function getFeedUrl()
	{
		return $this->feed_url;
	}

	/**
	* Create new item.
	*
	*/
	public function create()
	{
		global $ilDB, $ilLog;
		
		parent::create();
		
		$query = "INSERT INTO il_external_feed_block (".
			" id".
			", feed_url".
			" ) VALUES (".
			$ilDB->quote($this->getId())
			.",".$ilDB->quote($this->getFeedUrl()).")";
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
		
		$query = "SELECT * FROM il_external_feed_block WHERE id = ".
			$ilDB->quote($this->getId());
		$set = $ilDB->query($query);
		$rec = $set->fetchRow(MDB2_FETCHMODE_ASSOC);

		$this->setFeedUrl($rec["feed_url"]);

	}

	/**
	* Update item in database.
	*
	*/
	public function update()
	{
		global $ilDB;
		
		parent::update();
		
		$query = "UPDATE il_external_feed_block SET ".
			" feed_url = ".$ilDB->quote($this->getFeedUrl()).
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
		
		$query = "DELETE FROM il_external_feed_block".
			" WHERE id = ".$ilDB->quote($this->getId());
		
		$ilDB->query($query);

	}


}
?>
