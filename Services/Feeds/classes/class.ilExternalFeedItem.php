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

define("MAGPIE_DIR", "./Services/Feeds/magpierss/");
define("MAGPIE_CACHE_ON", true);
define("MAGPIE_CACHE_DIR", "./".ILIAS_WEB_DIR."/".CLIENT_ID."/magpie_cache");
define('MAGPIE_OUTPUT_ENCODING', "UTF-8");
define('MAGPIE_CACHE_AGE', 900);			// 900 seconds = 15 minutes
include_once(MAGPIE_DIR."/rss_fetch.inc");

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
		if ($a_item["link_"] != "")
		{
			$this->setLink(
				ilUtil::secureLink($this->secureString($a_item["link_"])));
		}
		else
		{
			$this->setLink(
				ilUtil::secureLink($this->secureString($a_item["link"])));
		}
		
		// summary
		if ($a_item["atom_content"] != "")
		{
			$this->setSummary(
				$this->secureString($a_item["atom_content"]));
		}
		else if ($a_item["summary"] != "")
		{
			$this->setSummary(
				$this->secureString($a_item["summary"]));
		}
		else
		{
			$this->setSummary(
				$this->secureString($a_item["description"]));
		}
		
		// date
		if ($a_item["pubdate"] != "")
		{
			$this->setDate(
				$this->secureString($a_item["pubdate"]));
		}
		else
		{
			$this->setDate(
				$this->secureString($a_item["updated"]));
		}

		// Author
		if ($a_item["dc"]["creator"] != "")
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
		
		// set target to blank for all links
		while($old_str != $a_str)
		{
			$old_str = $a_str;
			$a_str = eregi_replace("<a href=\"([^\"]*)\">",
				"<a href=\"\\1\" target=\"_blank\">", $a_str);
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
