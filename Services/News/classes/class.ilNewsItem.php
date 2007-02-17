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
* A news item can be created by different sources. E.g. when
* a new forum posting is created, or when a change in a
* learning module is announced.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*/
class ilNewsItem extends ilNewsItemGen
{
	/**
	* Constructor.
	*
	* @param	int	$a_id	
	*/
	public function __construct($a_id = 0)
	{
		parent::__construct($a_id);
	}

	/**
	* Get all news items for a user.
	*/
	static function _getNewsItemsOfUser($a_user_id, $a_only_public = false)
	{
		global $ilAccess;
		
		$news_item = new ilNewsItem();
		
		include_once("./Services/News/classes/class.ilNewsSubscription.php");
		$ref_ids = ilNewsSubscription::_getSubscriptionsOfUser($a_user_id);
		$data = array();

		foreach($ref_ids as $ref_id)
		{
			if (!$a_only_public && !$ilAccess->checkAccess("visible", "", $ref_id))
			{
				continue;
			}

			$obj_id = ilObject::_lookupObjId($ref_id);
			$obj_type = ilObject::_lookupType($obj_id);
			$news_item->setContextObjId($obj_id);
			$news_item->setContextObjType($obj_type);
			if ($a_only_public)
			{
				$news_item->setVisibility(NEWS_PUBLIC);
				$news = $news_item->queryNewsForVisibility();
			}
			else
			{
				$news = $news_item->queryNewsForContext();
			}
			foreach ($news as $k => $v)
			{
				$news[$k]["ref_id"] = $ref_id;
			}
			$data = array_merge($data, $news);
		}
		$data = ilUtil::sortArray($data, "creation_date", "desc");
		return $data;
	}
	
	/**
	* Get news aggregation (e.g. for courses)
	*/
	function getAggregatedNewsData($a_ref_id)
	{
		global $tree, $ilAccess;
		
		// get news of parent object
		/*
		$data = $this->queryNewsForContext();
		foreach ($data as $k => $v)
		{
			$data[$k]["ref_id"] = $a_ref_id;
		}*/
		
		$data = array();
		
		// get subtree
		$cur_node = $tree->getNodeData($a_ref_id);
		$nodes = $tree->getSubTree($cur_node, true);
		
		// get news for all subtree nodes
		foreach($nodes as $node)
		{
			if (!$ilAccess->checkAccess("visible", "", $node["child"]))
			{
				continue;
			}

			$news_item = new ilNewsItem();
			$news_item->setContextObjId($node["obj_id"]);
			$news_item->setContextObjType($node["type"]);
			$news = $news_item->queryNewsForContext();

			foreach ($news as $k => $v)
			{
				$news[$k]["ref_id"] = $node["child"];
			}
			$data = array_merge($data, $news);
		}
		
		// sort and return
		$data = ilUtil::sortArray($data, "creation_date", "desc");
		return $data;
	}
	
	/**
	* Get news aggregation for child objects (e.g. for categories)
	*/
	function getAggregatedChildNewsData($a_ref_id)
	{
		global $tree, $ilAccess;
		
		// get news of parent object
		$data = $this->queryNewsForContext();
		foreach ($data as $k => $v)
		{
			$data[$k]["ref_id"] = $a_ref_id;
		}
		
		// get childs
		$nodes = $tree->getChilds($a_ref_id);
		
		// get news for all subtree nodes
		foreach($nodes as $node)
		{
			if (!$ilAccess->checkAccess("visible", "", $node["child"]))
			{
				continue;
			}

			$news_item = new ilNewsItem();
			$news_item->setContextObjId($node["obj_id"]);
			$news_item->setContextObjType($node["type"]);
			$news = $news_item->queryNewsForContext();

			foreach ($news as $k => $v)
			{
				$news[$k]["ref_id"] = $node["child"];
			}
			$data = array_merge($data, $news);
		}
		
		// sort and return
		$data = ilUtil::sortArray($data, "creation_date", "desc");
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

}
?>
