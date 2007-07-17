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

include_once("./Services/News/classes/class.ilNewsItemGen.php");

/**
* @defgroup ServicesNews Services/News
*
* A news item can be created by different sources. E.g. when
* a new forum posting is created, or when a change in a
* learning module is announced.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesNews
*/
class ilNewsItem extends ilNewsItemGen
{
	private $limitation;
	
	/**
	* Constructor.
	*
	* @param	int	$a_id	
	*/
	public function __construct($a_id = 0)
	{
		parent::__construct($a_id);
		$this->limitation = true;
	}

	
	/**
	* Set Limitation for number of items.
	*
	* @param	boolean	$a_limitation	Limitation for number of items
	*/
	function setLimitation($a_limitation)
	{
		$this->limitation = $a_limitation;
	}

	/**
	* Get Limitation for number of items.
	*
	* @return	boolean	Limitation for number of items
	*/
	function getLimitation()
	{
		return $this->limitation;
	}

	/**
	* Create
	*/
	function create()
	{
		global $ilDB;
		
		parent::create();
		
		$news_set = new ilSetting("news");
		$max_items = $news_set->get("max_items");
		if ($max_items <= 0)
		{
			$max_items = 50;
		}
		
		// limit number of news
		if ($this->getLimitation())
		{
			// Determine how many rows should be deleted
			$query = "SELECT count(*) AS cnt ".
				"FROM il_news_item ".
				"WHERE ".
					"context_obj_id = ".$ilDB->quote($this->getContextObjId()).
					" AND context_obj_type = ".$ilDB->quote($this->getContextObjType()).
					" AND context_sub_obj_id = ".$ilDB->quote($this->getContextSubObjId()).
					" AND context_sub_obj_type = ".$ilDB->quote($this->getContextSubObjType());
	
			$set = $ilDB->query($query);
			$rec = $set->fetchRow(DB_FETCHMODE_ASSOC);
					
			// if we have more records than allowed, delete them
			if (($rec["cnt"] > $max_items) && $this->getContextObjId() > 0)
			{
				$query = "SELECT * ".
					"FROM il_news_item ".
					"WHERE ".
						"context_obj_id = ".$ilDB->quote($this->getContextObjId()).
						" AND context_obj_type = ".$ilDB->quote($this->getContextObjType()).
						" AND context_sub_obj_id = ".$ilDB->quote($this->getContextSubObjId()).
						" AND context_sub_obj_type = ".$ilDB->quote($this->getContextSubObjType()).
						" ORDER BY creation_date ASC".
						" LIMIT ".($rec["cnt"] - $max_items);
	
				$del_set = $ilDB->query($query);
				while ($del_item = $del_set->fetchRow(DB_FETCHMODE_ASSOC))
				{
					$del_news = new ilNewsItem($del_item["id"]);
					$del_news->delete();
				}
			}
		}
	}

	/**
	* Get all news items for a user.
	*/
	static function _getNewsItemsOfUser($a_user_id, $a_only_public = false)
	{
		global $ilAccess, $ilUser, $ilBench;
		
		$ilBench->start("News", "getNewsItemsOfUser");
		
		$news_item = new ilNewsItem();

		include_once("./Services/News/classes/class.ilNewsSubscription.php");
		include_once("./Services/Block/classes/class.ilBlockSetting.php");
		
		$ilBench->start("News", "getNewsItemsOfUser_getRefIds");
		$ref_ids = ilNewsSubscription::_getSubscriptionsOfUser($a_user_id);
		if (ilObjUser::_lookupPref($a_user_id, "pd_items_news") != "n")
		{
			$pd_items = ilObjUser::_lookupDesktopItems($a_user_id);
			foreach($pd_items as $item)
			{
				if (!in_array($item["ref_id"], $ref_ids))
				{
					$ref_ids[] = $item["ref_id"];
				}
			}
		}
		$ilBench->stop("News", "getNewsItemsOfUser_getRefIds");
		
		$data = array();

		foreach($ref_ids as $ref_id)
		{
			$ilBench->start("News", "getNewsForRefId");
			if (!$a_only_public && !$ilAccess->checkAccess("read", "", $ref_id))
			{
				continue;
			}

			$ilBench->start("News", "getNewsForRefId_getNews");
			$obj_id = ilObject::_lookupObjId($ref_id);
			$obj_type = ilObject::_lookupType($obj_id);
			$news = $news_item->getNewsForRefId($ref_id, $a_only_public);
			$ilBench->stop("News", "getNewsForRefId_getNews");

			$ilBench->start("News", "getNewsForRefId_mergeNews");
			$data = ilNewsItem::mergeNews($data, $news);
			$ilBench->stop("News", "getNewsForRefId_mergeNews");
			
			$ilBench->stop("News", "getNewsForRefId");
		}

		$data = ilUtil::sortArray($data, "creation_date", "desc", false, true);

		$ilBench->stop("News", "getNewsItemsOfUser");
		
//var_dump($data);
		return $data;
	}
	
	/**
	* Get News For Ref Id.
	*/
	function getNewsForRefId($a_ref_id, $a_only_public = false, $a_stopnesting = false)
	{
		$obj_id = ilObject::_lookupObjId($a_ref_id);
		$obj_type = ilObject::_lookupType($obj_id);
		if ($obj_type == "cat" && !$a_stopnesting)
		{
			return $this->getAggregatedChildNewsData($a_ref_id, $a_only_public);
		}
		else if (($obj_type == "grp" || $obj_type == "crs") &&
			!$a_stopnesting)
		{
			return $this->getAggregatedNewsData($a_ref_id, $a_only_public);
		}
		else
		{
			$news_item = new ilNewsItem();
			$news_item->setContextObjId($obj_id);
			$news_item->setContextObjType($obj_type);
			$news = $news_item->queryNewsForContext($a_only_public);
			$unset = array();
			foreach ($news as $k => $v)
			{
				if (!$a_only_public || $v["visibility"] == NEWS_PUBLIC ||
					($v["priority"] == 0 &&
						ilBlockSetting::_lookup("news", "public_notifications",
						0, $obj_id)))
				{
					$news[$k]["ref_id"] = $a_ref_id;
				}
				else
				{
					$unset[] = $k;
				}
			}
			foreach ($unset as $un)
			{
				unset($news[$un]);
			}
			return $news;
		}
	}
	
	/**
	* Get news aggregation (e.g. for courses)
	*/
	function getAggregatedNewsData($a_ref_id, $a_only_public = false)
	{
		global $tree, $ilAccess, $ilBench, $ilObjDataCache;
		
		$ilBench->start("News", "getAggregatedNewsData");
		
		// get news of parent object
		
		$data = array();
		
		// get subtree
		$ilBench->start("News", "getAggregatedNewsData_getSubTree");
		$cur_node = $tree->getNodeData($a_ref_id);
		$nodes = $tree->getSubTree($cur_node, true);
		
		// preload object data cache
		$ref_ids = array();
		foreach($nodes as $node)
		{
			$ref_ids[] = $node["child"];
		}
		$ilObjDataCache->preloadReferenceCache($ref_ids);
		
		$ilBench->stop("News", "getAggregatedNewsData_getSubTree");
		
		// get news for all subtree nodes
		$ilBench->start("News", "getAggregatedNewsData_getContexts");
		$contexts = array();
		foreach($nodes as $node)
		{
			$ilBench->start("News", "getAggregatedNewsData_getContexts_checkAccess");
			$acc = $ilAccess->checkAccess("read", "", $node["child"]);
			$ilBench->stop("News", "getAggregatedNewsData_getContexts_checkAccess");
			
			if (!$a_only_public && !$acc)
			{
				continue;
			}
			$ref_id[$node["obj_id"]] = $node["child"];
			$contexts[] = array("obj_id" => $node["obj_id"],
				"obj_type" => $node["type"]);
		}
		$ilBench->stop("News", "getAggregatedNewsData_getContexts");
		
		// sort and return
		$news = $this->queryNewsForMultipleContexts($contexts, $a_only_public);
		
		$ilBench->start("News", "getAggregatedNewsData_mergeAndSort");
		foreach ($news as $k => $v)
		{
			$news[$k]["ref_id"] = $ref_id[$v["context_obj_id"]];
		}
		$data = ilNewsItem::mergeNews($data, $news);
		$data = ilUtil::sortArray($data, "creation_date", "desc", false, true);
		$ilBench->stop("News", "getAggregatedNewsData_mergeAndSort");
		
		$ilBench->stop("News", "getAggregatedNewsData");
		
		return $data;
	}
	
	/**
	* Get news aggregation for child objects (e.g. for categories)
	*/
	function getAggregatedChildNewsData($a_ref_id, $a_only_public = false)
	{
		global $tree, $ilAccess, $ilBench;
		
		$ilBench->start("News", "getAggregatedChildNewsData");
		
		// get news of parent object
		$data = $this->getNewsForRefId($a_ref_id, $a_only_public, true);
		foreach ($data as $k => $v)
		{
			$data[$k]["ref_id"] = $a_ref_id;
		}

		// get childs
		$nodes = $tree->getChilds($a_ref_id);
		
		// get news for all subtree nodes
		$contexts = array();
		foreach($nodes as $node)
		{
			if (!$a_only_public && !$ilAccess->checkAccess("read", "", $node["child"]))
			{
				continue;
			}
			$ref_id[$node["obj_id"]] = $node["child"];
			$contexts[] = array("obj_id" => $node["obj_id"],
				"obj_type" => $node["type"]);
		}
		
		$news = $this->queryNewsForMultipleContexts($contexts, $a_only_public);
		foreach ($news as $k => $v)
		{
			$news[$k]["ref_id"] = $ref_id[$v["context_obj_id"]];
		}
		$data = ilNewsItem::mergeNews($data, $news);
		
		// sort and return
		$data = ilUtil::sortArray($data, "creation_date", "desc", false, true);
		
		$ilBench->stop("News", "getAggregatedChildNewsData");
		
		return $data;
	}

	/**
	* Convenient function to set the whole context information.
	*/
	function setContext($a_obj_id, $a_obj_type, $a_sub_obj_id = 0, $a_sub_obj_type = "")
	{
		$this->setContextObjId($a_obj_id);
		$this->setContextObjType($a_obj_type);
		$this->setContextSubObjId($a_sub_obj_id);
		$this->setContextSubObjType($a_sub_obj_type);
	}

	/**
	* Query NewsForContext
	*
	*/
	public function queryNewsForContext($a_for_rss_use = false)
	{
		global $ilDB, $ilUser;
		
		if ($a_for_rss_use)
		{
			$query = "SELECT * ".
				"FROM il_news_item ".
				" WHERE ".
					"context_obj_id = ".$ilDB->quote($this->getContextObjId()).
					" AND context_obj_type = ".$ilDB->quote($this->getContextObjType()).
					" ORDER BY creation_date DESC ";
		}
		else
		{
			$query = "SELECT il_news_item.* ".
				", il_news_read.user_id as user_read ".
				"FROM il_news_item LEFT JOIN il_news_read ".
				"ON il_news_item.id = il_news_read.news_id AND ".
				" il_news_read.user_id = ".$ilDB->quote($ilUser->getId()).
				" WHERE ".
					"context_obj_id = ".$ilDB->quote($this->getContextObjId()).
					" AND context_obj_type = ".$ilDB->quote($this->getContextObjType()).
					" ORDER BY creation_date DESC ";
		}
				
		$set = $ilDB->query($query);
		$result = array();
		while($rec = $set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			if (!$a_for_rss_use || ($rec["visibility"] == NEWS_PUBLIC ||
				($rec["priority"] == 0 &&
				ilBlockSetting::_lookup("news", "public_notifications",
				0, $rec["context_obj_id"]))))
			{
				$result[$rec["id"]] = $rec;
			}
		}

		return $result;

	}
	
	/**
	* Query News for multiple Contexts
	*
	* @param	array	$a_contexts		array of array("obj_id", "obj_type")
	*/
	public function queryNewsForMultipleContexts($a_contexts, $a_for_rss_use = false)
	{
		global $ilDB, $ilUser, $ilBench;
		
		$ilBench->start("News", "queryNewsForMultipleContexts");
		
		$ids = array();
		$type = array();
		foreach($a_contexts as $cont)
		{
			$ids[] = $cont["obj_id"];
			$type[$cont["obj_id"]] = $cont["obj_type"];
		}
		
		if ($a_for_rss_use)
		{
			$query = "SELECT * ".
				"FROM il_news_item ".
				" WHERE ".
					"context_obj_id IN (".implode(",",ilUtil::quoteArray($ids)).") ".
					" ORDER BY creation_date DESC ";
		}
		else
		{
			$query = "SELECT il_news_item.* ".
				", il_news_read.user_id as user_read ".
				"FROM il_news_item LEFT JOIN il_news_read ".
				"ON il_news_item.id = il_news_read.news_id AND ".
				" il_news_read.user_id = ".$ilDB->quote($ilUser->getId()).
				" WHERE ".
					"context_obj_id IN (".implode(",",ilUtil::quoteArray($ids)).") ".
					" ORDER BY creation_date DESC ";
		}

		$set = $ilDB->query($query);
		$result = array();
		while($rec = $set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			if ($type[$rec["context_obj_id"]] == $rec["context_obj_type"])
			{
				if (!$a_for_rss_use || ($rec["visibility"] == NEWS_PUBLIC ||
					($rec["priority"] == 0 &&
					ilBlockSetting::_lookup("news", "public_notifications",
					0, $rec["context_obj_id"]))))
				{
					$result[$rec["id"]] = $rec;
				}
			}
		}

		$ilBench->stop("News", "queryNewsForMultipleContexts");
		
		return $result;

	}


	/**
	* Set item read.
	*/
	function _setRead($a_user_id, $a_news_id)
	{
		global $ilDB;
		
		$q = "REPLACE INTO il_news_read (user_id, news_id) VALUES (".
			$ilDB->quote($a_user_id).",".$ilDB->quote($a_news_id).")";
		$ilDB->query($q);
	}
	
	
	/**
	* Merges two sets of news
	*
	* @param	array	$n1		Array of news
	* @param	array	$n2		Array of news
	*
	* @return	array			Array of news
	*/
	function mergeNews($n1, $n2)
	{
		foreach($n2 as $id => $news)
		{
			$n1[$id] = $news;
		}
		
		return $n1;
	}
	
	/**
	* Get default visibility for reference id
	*
	* @param	$a_ref_id		reference id
	*/
	static function _getDefaultVisibilityForRefId($a_ref_id)
	{
		global $tree, $ilSetting;

		include_once("./Services/Block/classes/class.ilBlockSetting.php");

		$news_set = new ilSetting("news");
		$default_visibility = ($news_set->get("default_visibility") != "")
				? $news_set->get("default_visibility")
				: "users";

		if ($tree->isInTree($a_ref_id))
		{
			$path = $tree->getPathFull($a_ref_id);
			
			foreach ($path as $key => $row)
			{
				if (!in_array($row["type"], array("root", "cat","crs", "fold", "grp", "icrs")))
				{
					continue;
				}

				$visibility = ilBlockSetting::_lookup("news", "default_visibility",
					0, $row["obj_id"]);
					
				if ($visibility != "")
				{
					$default_visibility = $visibility;
				}
			}
		}
		
		return $default_visibility;
	}
	
	
	/**
	* Delete news item
	*
	*/
	public function delete()
	{
		global $ilDB;
		
		// delete il_news_read entries
		$query = "DELETE FROM il_news_read ".
			" WHERE news_id = ".$ilDB->quote($this->getId());
		$ilDB->query($query);
		
		// delete multimedia object
		$mob = $this->getMobId();
		if ($mob > 0 and ilObject::_exists($mob))
		{
			include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
			$mob = new ilObjMediaObject($mob);
			$mob->delete();
		}
		
		// delete 
		parent::delete();
	}
	
	/**
	* Delete all news of a context
	*
	*/
	public function deleteNewsOfContext($a_context_obj_id,
		$a_context_obj_type)
	{
		global $ilDB;
		
		if ($a_context_obj_id == 0 || $a_context_obj_type == "")
		{
			return;
		}
		
		// get news records
		$query = "SELECT * FROM il_news_item".
			" WHERE context_obj_id = ".$ilDB->quote($a_context_obj_id).
			" AND context_obj_type = ".$ilDB->quote($a_context_obj_type);
			
		$news_set = $ilDB->query($query);
		
		while ($news = $news_set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$news_obj = new ilNewsItem($news["id"]);
			$news_obj->delete();
		}
	}

	static function _lookupTitle($a_news_id)
	{
		global $ilDB;
		
		$query = "SELECT title FROM il_news_item WHERE id = ".
			$ilDB->quote($a_news_id);
		$set = $ilDB->query($query);
		$rec = $set->fetchRow(DB_FETCHMODE_ASSOC);
		return $rec["title"];
	}
}
?>
