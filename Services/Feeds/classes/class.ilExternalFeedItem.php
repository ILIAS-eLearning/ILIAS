<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Wraps $item arrays from magpie
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* @ingroup ServicesFeeds
*/
class ilExternalFeedItem
{
	function __construct()
	{
	}
	
	/**
	* Set Magpie Item and read it into internal variables
	*/
	function setMagpieItem($a_item)
	{
		$this->magpie_item = $a_item;

		//var_dump($a_item);
		
		// title
		$this->setTitle(
			$this->secureString($a_item["title"]));
		
		// link
		if (isset($a_item["link_"]))
		{
			$this->setLink(
				ilUtil::secureLink($this->secureString($a_item["link_"])));
		}
		else
		{
			if (isset($a_item["link"]))
			{
				$this->setLink(
					ilUtil::secureLink($this->secureString($a_item["link"])));
			}
		}
		
		// summary
		if (isset($a_item["atom_content"]))
		{
			$this->setSummary(
				$this->secureString($a_item["atom_content"]));
		}
		else if (isset($a_item["summary"]))
		{
			$this->setSummary(
				$this->secureString($a_item["summary"]));
		}
		else if (isset($a_item["description"]))
		{
			$this->setSummary(
				$this->secureString($a_item["description"]));
		}
		
		// date
		if (isset($a_item["pubdate"]))
		{
			$this->setDate(
				$this->secureString($a_item["pubdate"]));
		}
		else if (isset($a_item["updated"]))
		{
			$this->setDate(
				$this->secureString($a_item["updated"]));
		}

		// Author
		if (isset($a_item["dc"]["creator"]))
		{
			$this->setAuthor(
				$this->secureString($a_item["dc"]["creator"]));
		}

		// id
		$this->setId(md5($this->getTitle().$this->getSummary()));

	}
	
	function secureString($a_str)
	{
		$a_str = ilUtil::secureString($a_str, true, "<b><i><em><strong><br><ol><li><ul><a><img>");
		$old_str = "";
		
		// set target to blank for all links
		while($old_str != $a_str)
		{
			$old_str = $a_str;
			$a_str = preg_replace("/<a href=\"([^\"]*)\">/i",
				"/<a href=\"\\1\" target=\"_blank\">/", $a_str);
		}
		return $a_str;
	}
	
	/**
	* Get Magpie Item
	*
	* @return	object	Magpie Item
	*/
	function getMagpieItem()
	{
		return $this->magpie_item;
	}

	/**
	* Set Title.
	*
	* @param	string	$a_title	Title
	*/
	function setTitle($a_title)
	{
		$this->title = $a_title;
	}

	/**
	* Get Title.
	*
	* @return	string	Title
	*/
	function getTitle()
	{
		return $this->title;
	}

	/**
	* Set Link.
	*
	* @param	string	$a_link	Link
	*/
	function setLink($a_link)
	{
		$this->link = $a_link;
	}

	/**
	* Get Link.
	*
	* @return	string	Link
	*/
	function getLink()
	{
		return $this->link;
	}

	/**
	* Set Summary.
	*
	* @param	string	$a_summary	Summary
	*/
	function setSummary($a_summary)
	{
		$this->summary = $a_summary;
	}

	/**
	* Get Summary.
	*
	* @return	string	Summary
	*/
	function getSummary()
	{
		return $this->summary;
	}

	/**
	* Set Date.
	*
	* @param	string	$a_date	Date
	*/
	function setDate($a_date)
	{
		$this->date = $a_date;
	}

	/**
	* Get Date.
	*
	* @return	string	Date
	*/
	function getDate()
	{
		return $this->date;
	}

	/**
	* Set Id.
	*
	* @param	string	$a_id	Id
	*/
	function setId($a_id)
	{
		$this->id = $a_id;
	}

	/**
	* Get Id.
	*
	* @return	string	Id
	*/
	function getId()
	{
		return $this->id;
	}

	/**
	* Set Author.
	*
	* @param	string	$a_author	Author
	*/
	function setAuthor($a_author)
	{
		$this->author = $a_author;
	}

	/**
	* Get Author.
	*
	* @return	string	Author
	*/
	function getAuthor()
	{
		return $this->author;
	}
}
