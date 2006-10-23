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

define("NEWS_NOTICE", 0);
define("NEWS_MESSAGE", 1);
define("NEWS_WARNING", 2);
define("NEWS_TEXT", "text");
define("NEWS_HTML", "html");

/**
* A news item can be created by different sources. E.g. when
* a new forum posting is created, or when a change in a
* learning module is announced.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*/
class ilNewsItem 
{

	private $creation_date;
	private $priority = 1;
	private $title = "tt";
	private $content;
	private $context_obj_id;
	private $context_obj_type;
	private $context_sub_obj_id;
	private $context_sub_obj_type;
	private $content_type;

	/**
	* Set CreationDate.
	*
	* @param	string	$a_creation_date	Date of Creation.
	*/
	public function setCreationDate($a_creation_date)
	{
		$this->creation_date = $a_creation_date;
	}

	/**
	* Get CreationDate.
	*
	* @return	string	Date of Creation.
	*/
	public function getCreationDate()
	{
		return $this->creation_date;
	}

	/**
	* Set Priority.
	*
	* @param	string	$a_priority	News Priority.
	*/
	public function setPriority($a_priority = 1)
	{
		$this->priority = $a_priority;
	}

	/**
	* Get Priority.
	*
	* @return	string	News Priority.
	*/
	public function getPriority()
	{
		return $this->priority;
	}

	/**
	* Set Title.
	*
	* @param	string	$a_title	Title of news item.
	*/
	public function setTitle($a_title = "tt")
	{
		$this->title = $a_title;
	}

	/**
	* Get Title.
	*
	* @return	string	Title of news item.
	*/
	public function getTitle()
	{
		return $this->title;
	}

	/**
	* Set Content.
	*
	* @param	string	$a_content	Content of news.
	*/
	public function setContent($a_content)
	{
		$this->content = $a_content;
	}

	/**
	* Get Content.
	*
	* @return	string	Content of news.
	*/
	public function getContent()
	{
		return $this->content;
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
	* Set ContentType.
	*
	* @param	string	$a_content_type	Content type.
	*/
	public function setContentType($a_content_type)
	{
		$this->content_type = $a_content_type;
	}

	/**
	* Get ContentType.
	*
	* @return	string	Content type.
	*/
	public function getContentType()
	{
		return $this->content_type;
	}

	/**
	* Create new item.
	*
	*/
	public function create()
	{
		global $ilDB;

		$query = "INSERT INTO il_news_item". 
			"(creation_date,".
			"priority,".
			"title,".
			"content,".
			"context_obj_id,".
			"context_obj_type,".
			"context_sub_obj_id,".
			"context_sub_obj_type,".
			"content_type) VALUES (".
			$ilDB->quote($this->getCreationDate()).",".
			$ilDB->quote($this->getPriority()).",".
			$ilDB->quote($this->getTitle()).",".
			$ilDB->quote($this->getContent()).",".
			$ilDB->quote($this->getContextObjId()).",".
			$ilDB->quote($this->getContextObjType()).",".
			$ilDB->quote($this->getContextSubObjId()).",".
			$ilDB->quote($this->getContextSubObjType()).",".
			$ilDB->quote($this->getContentType()).")";
		$ilDB->query($query);
	}

	/**
	* Read item from database.
	*
	*/
	public function read()
	{
		global $ilDB;

		$query = "SELECT * FROM il_news_item";
		$set = $ilDB->query($query);
		$rec = $set->fetchRow(DB_FETCHMODE_ASSOC);

		$this->setCreationDate($rec["creation_date"]);
		$this->setPriority($rec["priority"]);
		$this->setTitle($rec["title"]);
		$this->setContent($rec["content"]);
		$this->setContextObjId($rec["context_obj_id"]);
		$this->setContextObjType($rec["context_obj_type"]);
		$this->setContextSubObjId($rec["context_sub_obj_id"]);
		$this->setContextSubObjType($rec["context_sub_obj_type"]);
		$this->setContentType($rec["content_type"]);

	}

	/**
	* Update item from database.
	*
	*/
	public function update()
	{
		global $ilDB;

		$query = "UPDATE il_news_item SET ".
			"creation_date = ".$ilDB->quote($this->getCreationDate()).",".
			"priority = ".$ilDB->quote($this->getPriority()).",".
			"title = ".$ilDB->quote($this->getTitle()).",".
			"content = ".$ilDB->quote($this->getContent()).",".
			"context_obj_id = ".$ilDB->quote($this->getContextObjId()).",".
			"context_obj_type = ".$ilDB->quote($this->getContextObjType()).",".
			"context_sub_obj_id = ".$ilDB->quote($this->getContextSubObjId()).",".
			"context_sub_obj_type = ".$ilDB->quote($this->getContextSubObjType()).",".
			"content_type = ".$ilDB->quote($this->getContentType());
		$ilDB->query($query);

	}

	/**
	* Query NewsForContext
	*
	*/
	public function queryNewsForContext()
	{
		global $ilDB;
		
		$query = "SELECT creation_date, priority, title, content, content_type ".
			"FROM il_news_item ".
			"WHERE ".
				"context_obj_id = ".$ilDB->quote($this->getContextObjId()).
				" AND context_obj_type = ".$ilDB->quote($this->getContextObjType()).
				" AND context_sub_obj_id = ".$ilDB->quote($this->getContextSubObjId()).
				" AND context_sub_obj_type = ".$ilDB->quote($this->getContextSubObjType()).
			"ORDER BY creation_date DESC ".
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
