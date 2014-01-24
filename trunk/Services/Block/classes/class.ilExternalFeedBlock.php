<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

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
			$ilDB->quote($this->getId(), "integer")
			.",".$ilDB->quote($this->getFeedUrl(), "text").")";
		$ilDB->manipulate($query);

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
			$ilDB->quote($this->getId(), "integer");
		$set = $ilDB->query($query);
		$rec = $ilDB->fetchAssoc($set);

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
			" feed_url = ".$ilDB->quote($this->getFeedUrl(), "text").
			" WHERE id = ".$ilDB->quote($this->getId(), "integer");
		
		$ilDB->manipulate($query);

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
			" WHERE id = ".$ilDB->quote($this->getId(), "integer");
		
		$ilDB->manipulate($query);

	}


}
?>
