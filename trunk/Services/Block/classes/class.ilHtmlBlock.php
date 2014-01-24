<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

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
			$ilDB->quote($this->getId(), "integer")
			.",".$ilDB->quote($this->getContent(), "text").")";
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
		
		$query = "SELECT * FROM il_html_block WHERE id = ".
			$ilDB->quote($this->getId(), "integer");
		$set = $ilDB->query($query);
		$rec = $ilDB->fetchAssoc($set);

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
			" content = ".$ilDB->quote($this->getContent(), "text").
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
		
		$query = "DELETE FROM il_html_block".
			" WHERE id = ".$ilDB->quote($this->getId(), "integer");
		
		$ilDB->manipulate($query);

	}


}
?>
