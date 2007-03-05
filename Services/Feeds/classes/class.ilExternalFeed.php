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

include_once("./Services/Feeds/classes/class.ilExternalFeedItem.php");

/**
* Handles external Feeds via Magpie libaray.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* @ingroup ServicesFeeds
*/
class ilExternalFeed
{
	protected $items = array();
	
	/**
	* Constructor
	*/
	function ilExternalFeed()
	{
		$this->createCacheDirectory();
	}

	/**
	* Set Url.
	*
	* @param	string	$a_url	Url
	*/
	function setUrl($a_url)
	{
		$this->url = $a_url;
	}

	/**
	* Get Url.
	*
	* @return	string	Url
	*/
	function getUrl()
	{
		return $this->url;
	}

	/**
	* Create magpie cache directorry (if not existing)
	*/
	function createCacheDirectory()
	{
		if (!is_dir(ilUtil::getWebspaceDir()."/magpie_cache"))
		{
			ilUtil::makeDir(ilUtil::getWebspaceDir()."/magpie_cache");
		}
		
//echo "<br/>./".ILIAS_WEB_DIR."/".CLIENT_ID."/magpie_cache";
//echo "<br>".ilUtil::getWebspaceDir()."/magpie_cache";

	}
	
	/**
	* Fetch the feed
	*/
	function fetch()
	{
		$this->feed = fetch_rss($this->getUrl());
		
		if (is_array($this->feed->items))
		{
			foreach($this->feed->items as $item)
			{
				$item_obj = new ilExternalFeedItem();
				$item_obj->setMagpieItem($item);
				$this->items[] = $item_obj;
			}
		}
	}
	
	/**
	* Get Channel
	*/
	function getChannelTitle()
	{
		return $this->feed->channel["title"];
	}

	/**
	* Get Description
	*/
	function getChannelDescription()
	{
		return $this->feed->channel["description"];
	}

	/**
	* Get Items
	*/
	function getItems()
	{
		return $this->items;
	}
	
}
?>
