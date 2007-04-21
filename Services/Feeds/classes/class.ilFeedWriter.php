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

include_once("Services/Feeds/classes/class.ilFeedItem.php");

/** @defgroup ServicesFeeds Services/Feeds
 */

/**
* Feed writer class.
*
* how to make it "secure"
* alternative 1:
* - hash for all objects
* - feature "mail me rss link"
* - link includes ref id, user id, combined hash (kind of password)
* - combined hash = hash(user hash + object hash)
* - ilias checks whether ref id / user id / combined hash match
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* @ingroup ServicesFeeds
*/
class ilFeedWriter
{
	var $encoding = "UTF-8";
	var $ch_about = "";
	var $ch_title = "";
	var $ch_link = "";
	var $ch_description = "";
	var $items = array();
	
	function ilFeedWriter()
	{
	}
	
	/**
	* Set feed encoding. Default is UTF-8.
	*/
	function setEncoding($a_enc)
	{
		$this->encoding = $a_enc;
	}
	
	function getEncoding()
	{
		return $this->encoding;
	}

	/**
	* Unique URI that defines the channel
	*/
	function setChannelAbout($a_ab)
	{
		$this->ch_about = $a_ab;
	}
	
	function getChannelAbout()
	{
		return $this->ch_about;
	}

	/**
	* Channel Title
	*/
	function setChannelTitle($a_title)
	{
		$this->ch_title = $a_title;
	}
	
	function getChannelTitle()
	{
		return $this->ch_title;
	}

	/**
	* Channel Link
	* URL to which an HTML rendering of the channel title will link
	*/
	function setChannelLink($a_link)
	{
		$this->ch_link = $a_link;
	}
	
	function getChannelLink()
	{
		return $this->ch_link;
	}

	/**
	* Channel Description
	*/
	function setChannelDescription($a_desc)
	{
		$this->ch_desc = $a_desc;
	}
	
	function getChannelDescription()
	{
		return $this->ch_desc;
	}

	/**
	* Add Item
	* Item is an object of type ilFeedItem
	*/
	function addItem($a_item)
	{
		$this->items[] = $a_item;
	}
	
	function getItems()
	{
		return $this->items;
	}

	function prepareStr($a_str)
	{
		$a_str = str_replace("&", "&amp;", $a_str);
		$a_str = str_replace("<", "&lt;", $a_str);
		$a_str = str_replace(">", "&gt;", $a_str);
		return $a_str;
	}

	/**
	* get feed xml
	*/
	function getFeed()
	{
		include_once("classes/class.ilTemplate.php");
		$this->tpl = new ilTemplate("tpl.rss_2_0.xml", true, true, "Services/Feeds");
		
		$this->tpl->setVariable("XML", "xml");
		$this->tpl->setVariable("CONTENT_ENCODING", $this->getEncoding());
		$this->tpl->setVariable("CHANNEL_ABOUT", $this->getChannelAbout());
		$this->tpl->setVariable("CHANNEL_TITLE", $this->getChannelTitle());
		$this->tpl->setVariable("CHANNEL_LINK", $this->getChannelLink());
		$this->tpl->setVariable("CHANNEL_DESCRIPTION", $this->getChannelDescription());
		
		foreach($this->items as $item)
		{
			$this->tpl->setCurrentBlock("rdf_seq");
			$this->tpl->setVariable("RESOURCE", $item->getAbout());
			$this->tpl->parseCurrentBlock();
			
			// Date
			if ($item->getDate() != "")
			{
				$this->tpl->setCurrentBlock("date");
				$d = $item->getDate();
				$yyyy = substr($d, 0, 4);
				$mm = substr($d, 5, 2);
				$dd = substr($d, 8, 2);
				$h = substr($d, 11, 2);
				$m = substr($d, 14, 2);
				$s = substr($d, 17, 2);
				$this->tpl->setVariable("ITEM_DATE",
					date("r", mktime($h, $m, $s, $mm, $dd, $yyyy)));
				$this->tpl->parseCurrentBlock();
			}
			
			// Enclosure
			if ($item->getEnclosureUrl() != "")
			{
				$this->tpl->setCurrentBlock("enclosure");
				$this->tpl->setVariable("ENC_URL", $item->getEnclosureUrl());
				$this->tpl->setVariable("ENC_LENGTH", $item->getEnclosureLength());
				$this->tpl->setVariable("ENC_TYPE", $item->getEnclosureType());
				$this->tpl->parseCurrentBlock();
			}
			
			$this->tpl->setCurrentBlock("item");
			$this->tpl->setVariable("ITEM_ABOUT", $item->getAbout());
			$this->tpl->setVariable("ITEM_TITLE", $item->getTitle());
			$this->tpl->setVariable("ITEM_DESCRIPTION", $item->getDescription());
			$this->tpl->setVariable("ITEM_LINK", $item->getLink());
			$this->tpl->parseCurrentBlock();
			
		}
		
		$this->tpl->parseCurrentBlock();
		return $this->tpl->get();
	}
	
	function showFeed()
	{
		echo $this->getFeed();
	}
}
?>
