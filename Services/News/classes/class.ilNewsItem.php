<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

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

	private static $privFeedId = false;
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
	 * Set content text ist lang var
	 *
	 * @param boolean $a_content_is_lang_var
	 */
	public function setContentTextIsLangVar($a_val = 0)
	{
		$this->content_text_is_lang_var = $a_val;
	}

	/**
	 * Get content text ist lang var
	 *
	 * @return	boolean
	 */
	public function getContentTextIsLangVar()
	{
		return $this->content_text_is_lang_var;
	}

	/**
	 * Set mob play counter
	 *
	 * @param int $a_val counter	
	 */
	function setMobPlayCounter($a_val)
	{
		$this->mob_cnt_play = $a_val;
	}
	
	/**
	 * Get mob play counter
	 *
	 * @return int counter
	 */
	function getMobPlayCounter()
	{
		return $this->mob_cnt_play;
	}

	/**
	 * Set mob download counter
	 *
	 * @param int $a_val counter	
	 */
	function setMobDownloadCounter($a_val)
	{
		$this->mob_cnt_download = $a_val;
	}
	
	/**
	 * Get mob download counter
	 *
	 * @return int counter
	 */
	function getMobDownloadCounter()
	{
		return $this->mob_cnt_download;
	}
	
	/**
	 * Read item from database.
	 */
	public function read()
	{
		global $ilDB;

		$query = "SELECT * FROM il_news_item WHERE id = ".
			$ilDB->quote($this->getId(), "integer");
		$set = $ilDB->query($query);
		$rec = $ilDB->fetchAssoc($set);

		$this->setTitle($rec["title"]);
		$this->setContent($rec["content"]);
		$this->setContextObjId((int) $rec["context_obj_id"]);
		$this->setContextObjType($rec["context_obj_type"]);
		$this->setContextSubObjId((int) $rec["context_sub_obj_id"]);
		$this->setContextSubObjType($rec["context_sub_obj_type"]);
		$this->setContentType($rec["content_type"]);
		$this->setCreationDate($rec["creation_date"]);
		$this->setUpdateDate($rec["update_date"]);
		$this->setUserId($rec["user_id"]);
		$this->setVisibility($rec["visibility"]);
		$this->setContentLong($rec["content_long"]);
		$this->setPriority($rec["priority"]);
		$this->setContentIsLangVar($rec["content_is_lang_var"]);
		$this->setContentTextIsLangVar((int) $rec["content_text_is_lang_var"]);
		$this->setMobId($rec["mob_id"]);
		$this->setPlaytime($rec["playtime"]);
		$this->setMobPlayCounter($rec["mob_cnt_play"]);
		$this->setMobDownloadCounter($rec["mob_cnt_download"]);

	}

	/**
	 * Create
	 */
	function create()
	{
		global $ilDB;

		// insert new record into db
		$this->setId($ilDB->nextId("il_news_item"));
		$ilDB->insert("il_news_item", array(
			"id" => array("integer", $this->getId()),
			"title" => array("text", $this->getTitle()),
			"content" => array("clob", $this->getContent()),
			"context_obj_id" => array("integer", (int) $this->getContextObjId()),
			"context_obj_type" => array("text", $this->getContextObjType()),
			"context_sub_obj_id" => array("integer", (int) $this->getContextSubObjId()),
			"context_sub_obj_type" => array("text", $this->getContextSubObjType()),
			"content_type" => array("text", $this->getContentType()),
			"creation_date" => array("timestamp", ilUtil::now()),
			"update_date" => array("timestamp", ilUtil::now()),
			"user_id" => array("integer", $this->getUserId()),
			"visibility" => array("text", $this->getVisibility()),
			"content_long" => array("clob", $this->getContentLong()),
			"priority" => array("integer", $this->getPriority()),
			"content_is_lang_var" => array("integer", $this->getContentIsLangVar()),
			"content_text_is_lang_var" => array("integer", (int) $this->getContentTextIsLangVar()),
			"mob_id" => array("integer", $this->getMobId()),
            "playtime" => array("text", $this->getPlaytime())
		));

		
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
			$query = "SELECT count(*) cnt ".
				"FROM il_news_item ".
				"WHERE ".
					"context_obj_id = ".$ilDB->quote($this->getContextObjId(), "integer").
					" AND context_obj_type = ".$ilDB->quote($this->getContextObjType(), "text").
					" AND context_sub_obj_id = ".$ilDB->quote($this->getContextSubObjId(), "integer").
					" AND ".$ilDB->equals("context_sub_obj_type", $this->getContextSubObjType(), "text", true)." ";
	
			$set = $ilDB->query($query);
			$rec = $ilDB->fetchAssoc($set);
					
			// if we have more records than allowed, delete them
			if (($rec["cnt"] > $max_items) && $this->getContextObjId() > 0)
			{
				$query = "SELECT * ".
					"FROM il_news_item ".
					"WHERE ".
						"context_obj_id = ".$ilDB->quote($this->getContextObjId(), "integer").
						" AND context_obj_type = ".$ilDB->quote($this->getContextObjType(), "text").
						" AND context_sub_obj_id = ".$ilDB->quote($this->getContextSubObjId(), "integer").
						" AND ".$ilDB->equals("context_sub_obj_type", $this->getContextSubObjType(), "text", true).
                        " ORDER BY creation_date ASC";
	
				$ilDB->setLimit($rec["cnt"] - $max_items);
				$del_set = $ilDB->query($query);
				while ($del_item = $ilDB->fetchAssoc($del_set))
				{
					$del_news = new ilNewsItem($del_item["id"]);
					$del_news->delete();
				}
			}
		}
	}

	/**
	 * Update item in database
	 *
	 * @param boolean $a_as_new If true, creation date is set "now"
	 */
	public function update($a_as_new = false)
	{
		global $ilDB;

		$fields = array(
			"title" => array("text", $this->getTitle()),
			"content" => array("clob", $this->getContent()),
			"context_obj_id" => array("integer", $this->getContextObjId()),
			"context_obj_type" => array("text", $this->getContextObjType()),
			"context_sub_obj_id" => array("integer", $this->getContextSubObjId()),
			"context_sub_obj_type" => array("text", $this->getContextSubObjType()),
			"content_type" => array("text", $this->getContentType()),
			"user_id" => array("integer", $this->getUserId()),
			"visibility" => array("text", $this->getVisibility()),
			"content_long" => array("clob", $this->getContentLong()),
			"priority" => array("integer", $this->getPriority()),
			"content_is_lang_var" => array("integer", $this->getContentIsLangVar()),
			"content_text_is_lang_var" => array("integer", (int) $this->getContentTextIsLangVar()),
			"mob_id" => array("integer", $this->getMobId()),
			"mob_cnt_play" => array("integer", $this->getMobPlayCounter()),
			"mob_cnt_download" => array("integer", $this->getMobDownloadCounter()),
            "playtime" => array("text", $this->getPlaytime())
		);

		$now = ilUtil::now();
		if ($a_as_new)
		{
			$fields["creation_date"] = array("timestamp", $now);
			$fields["update_date"] = array("timestamp", $now);
		}
		else
		{
			$fields["update_date"] = array("timestamp", $now);
		}

		$ilDB->update("il_news_item", $fields, array(
			"id" => array("integer", $this->getId())
		));

	}


	/**
	* Get all news items for a user.
	*/
	static function _getNewsItemsOfUser($a_user_id, $a_only_public = false,
		$a_prevent_aggregation = false, $a_per = 0, &$a_cnt = NULL)
	{
		global $ilAccess;

		$news_item = new ilNewsItem();
		$news_set = new ilSetting("news");
		
		$per = $a_per;

		include_once("./Services/News/classes/class.ilNewsSubscription.php");
		include_once("./Services/Block/classes/class.ilBlockSetting.php");
		
		// this is currently not used
		$ref_ids = ilNewsSubscription::_getSubscriptionsOfUser($a_user_id);
		
		if (ilObjUser::_lookupPref($a_user_id, "pd_items_news") != "n")
		{
			// get all items of the personal desktop
			$pd_items = ilObjUser::_lookupDesktopItems($a_user_id);
			foreach($pd_items as $item)
			{
				if (!in_array($item["ref_id"], $ref_ids))
				{
					$ref_ids[] = $item["ref_id"];
				}
			}
			
			// get all memberships
			include_once 'Services/Membership/classes/class.ilParticipants.php';
			$crs_mbs = ilParticipants::_getMembershipByType($a_user_id, 'crs');
			$grp_mbs = ilParticipants::_getMembershipByType($a_user_id, 'grp');
			$items = array_merge($crs_mbs, $grp_mbs);
			foreach($items as $i)
			{
				$item_references = ilObject::_getAllReferences($i);
				if(is_array($item_references) && count($item_references))
				{
					foreach($item_references as $ref_id)
					{
						if (!in_array($ref_id, $ref_ids))
						{
							$ref_ids[] = $ref_id;
						}
					}
				}
			}
		}
		
		$data = array();

		foreach($ref_ids as $ref_id)
		{
			if (!$a_only_public)
			{
				// this loop should not cost too much performance
				$acc = $ilAccess->checkAccessOfUser($a_user_id, "read", "", $ref_id);
				
				if (!$acc)
				{
					continue;
				}
			}
			if (ilNewsItem::getPrivateFeedId() != false) {
				global $rbacsystem;
				$acc = $rbacsystem->checkAccessOfUser(ilNewsItem::getPrivateFeedId(),"read", $ref_id);
			
				if (!$acc)
				{
					continue;
				}
			}

			$obj_id = ilObject::_lookupObjId($ref_id);
			$obj_type = ilObject::_lookupType($obj_id);
			$news = $news_item->getNewsForRefId($ref_id, $a_only_public, false,
				$per, $a_prevent_aggregation, false, false, false, $a_user_id);
			
			// counter
			if (!is_null($a_cnt))
			{
				$a_cnt[$ref_id] = count($news);
			}

			$data = ilNewsItem::mergeNews($data, $news);
		}

        $data = ilUtil::sortArray($data, "creation_date", "desc", false, true);

		return $data;
	}
	
	/**
	* Get News For Ref Id.
	*
	* $a_user_id does only work for groups and courses so far
	*/
	function getNewsForRefId($a_ref_id, $a_only_public = false, $a_stopnesting = false,
		$a_time_period = 0, $a_prevent_aggregation = true, $a_forum_group_sequences = false,
		$a_no_auto_generated = false, $a_ignore_date_filter = false, $a_user_id = null)
	{
		$obj_id = ilObject::_lookupObjId($a_ref_id);
		$obj_type = ilObject::_lookupType($obj_id);
		
		// get starting date
        $starting_date = "";
		if ($obj_type == "grp" || $obj_type == "crs" || $obj_type == "cat")
		{
			include_once("./Services/Block/classes/class.ilBlockSetting.php");
			$hide_news_per_date = ilBlockSetting::_lookup("news", "hide_news_per_date",
				0, $obj_id);
			if ($hide_news_per_date && !$a_ignore_date_filter)
			{
				$starting_date = ilBlockSetting::_lookup("news", "hide_news_date",
					0, $obj_id);
			}
		}

		if ($obj_type == "cat" && !$a_stopnesting)
		{
			$news = $this->getAggregatedChildNewsData($a_ref_id, $a_only_public, $a_time_period,
                $a_prevent_aggregation, $starting_date, $a_no_auto_generated);
		}
		else if (($obj_type == "grp" || $obj_type == "crs") &&
			!$a_stopnesting)
		{
			$news = $this->getAggregatedNewsData($a_ref_id, $a_only_public, $a_time_period,
                $a_prevent_aggregation, $starting_date, $a_no_auto_generated, $a_user_id);
		}
		else
		{
			$news_item = new ilNewsItem();
			$news_item->setContextObjId($obj_id);
			$news_item->setContextObjType($obj_type);
			$news = $news_item->queryNewsForContext($a_only_public, $a_time_period,
                $starting_date, $a_no_auto_generated);
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
		}
		
		if (!$a_prevent_aggregation)
		{
			$news = $this->aggregateForums($news);
		}
		else if ($a_forum_group_sequences)
		{
			$news = $this->aggregateForums($news, true);
		}
		
		return $news;
	}
	
	/**
	* Get news aggregation (e.g. for courses, groups)
	*/
	function getAggregatedNewsData($a_ref_id, $a_only_public = false, $a_time_period = 0,
        $a_prevent_aggregation = false, $a_starting_date = "", $a_no_auto_generated = false,
		$a_user_id = null)
	{
		global $tree, $ilAccess, $ilObjDataCache;
		
		// get news of parent object
		
		$data = array();
		
		// get subtree
		$cur_node = $tree->getNodeData($a_ref_id);

		// do not check for lft (materialized path)
		if($cur_node)
		{
			$nodes = (array) $tree->getSubTree($cur_node,true);
		}
		else
		{
			$nodes = array();
		}
		
		// preload object data cache
		$ref_ids = array();
		$obj_ids = array();
		foreach($nodes as $node)
		{
			$ref_ids[] = $node["child"];
			$obj_ids[] = $node["obj_id"];
		}

		$ilObjDataCache->preloadReferenceCache($ref_ids);
		if (!$a_only_public)
		{
			include_once "Services/Object/classes/class.ilObjectActivation.php";
			ilObjectActivation::preloadData($ref_ids);
		}
		
		// no check, for which of the objects any news are available
        $news_obj_ids = ilNewsItem::filterObjIdsPerNews($obj_ids, $a_time_period, $a_starting_date);
		//$news_obj_ids = $obj_ids;
		
		// get news for all subtree nodes
		$contexts = array();
		foreach($nodes as $node)
		{
			// only go on, if news are available
			if (!in_array($node["obj_id"], $news_obj_ids))
			{
				continue;
			}
			
			if (!$a_only_public)
			{
				if(!$a_user_id)
				{
					$acc = $ilAccess->checkAccess("read", "", $node["child"]);
				}
				else
				{
					$acc = $ilAccess->checkAccessOfUser($a_user_id, "read", "",
						$node["child"]);
				}				
				if (!$acc)
				{
					continue;
				}
			}
			
			$ref_id[$node["obj_id"]] = $node["child"];
			$contexts[] = array("obj_id" => $node["obj_id"],
				"obj_type" => $node["type"]);
		}
		
		// sort and return
		$news = $this->queryNewsForMultipleContexts($contexts, $a_only_public, $a_time_period,
            $a_starting_date, $a_no_auto_generated, $a_user_id);
				
		$to_del = array();
		foreach ($news as $k => $v)
		{
			$news[$k]["ref_id"] = $ref_id[$v["context_obj_id"]];
		}
		
		$data = ilNewsItem::mergeNews($data, $news);
        $data = ilUtil::sortArray($data, "creation_date", "desc", false, true);
		
		if (!$a_prevent_aggregation)
		{
			$data = $this->aggregateFiles($data, $a_ref_id);
		}
				
		return $data;
	}
	
	function aggregateForums($news, $a_group_posting_sequence = false)
	{
		$to_del = array();
		$forums = array();
		
		// aggregate
		foreach ($news as $k => $v)
		{
			if ($a_group_posting_sequence && $last_aggregation_forum > 0 &&
				$last_aggregation_forum != $news[$k]["context_obj_id"])
			{
				$forums[$last_aggregation_forum] = "";
			}

			if ($news[$k]["context_obj_type"] == "frm")
			{
				if ($forums[$news[$k]["context_obj_id"]] == "")
				{
					// $forums[forum_id] = news_id;
					$forums[$news[$k]["context_obj_id"]] = $k;
					$last_aggregation_forum = $news[$k]["context_obj_id"];
				}
				else
				{
					$to_del[] = $k;
				}
				
				$news[$k]["no_context_title"] = true;
				
				// aggregate every forum into it's "k" news
				$news[$forums[$news[$k]["context_obj_id"]]]["aggregation"][$k]
					= $news[$k];
				$news[$k]["agg_ref_id"]
					= $news[$k]["ref_id"];
				$news[$k]["content"] = "";
				$news[$k]["content_long"] = "";
			}
		}
		
		// delete double entries
		foreach($to_del as $k)
		{
			unset($news[$k]);
		}
//var_dump($news[14]["aggregation"]);

		
		return $news;
	}
	
	function aggregateFiles($news, $a_ref_id)
	{
		$first_file = "";
		$to_del = array();
		foreach ($news as $k => $v)
		{
			// aggregate file related news
			if ($news[$k]["context_obj_type"] == "file")
			{
				if ($first_file == "")
				{
					$first_file = $k;
				}
				else
				{
					$to_del[] = $k;
				}
				$news[$first_file]["aggregation"][$k] = $news[$k];
				$news[$first_file]["agg_ref_id"] = $a_ref_id;
				$news[$first_file]["ref_id"] = $a_ref_id;
			}
		}
		
		foreach($to_del as $v)
		{
			unset($news[$v]);
		}
		
		return $news;
	}

	
	/**
	* Get news aggregation for child objects (e.g. for categories)
	*/
	function getAggregatedChildNewsData($a_ref_id, $a_only_public = false,
		$a_time_period = 0, $a_prevent_aggregation = false, $a_starting_date = "",
        $a_no_auto_generated = false)
	{
		global $tree, $ilAccess;
		
		// get news of parent object
		$data = $this->getNewsForRefId($a_ref_id, $a_only_public, true, $a_time_period,
			true, false, false, $a_no_auto_generated);
		foreach ($data as $k => $v)
		{
			$data[$k]["ref_id"] = $a_ref_id;
		}

		// get childs
		$nodes = $tree->getChilds($a_ref_id);
		
		// no check, for which of the objects any news are available
		$obj_ids = array();
		foreach($nodes as $node)
		{
			$obj_ids[] = $node["obj_id"];
		}
        $news_obj_ids = ilNewsItem::filterObjIdsPerNews($obj_ids, $a_time_period, $a_starting_date);
		//$news_obj_ids = $obj_ids;

		// get news for all subtree nodes
		$contexts = array();
		foreach($nodes as $node)
		{
			// only go on, if news are available
			if (!in_array($node["obj_id"], $news_obj_ids))
			{
				continue;
			}

			if (!$a_only_public && !$ilAccess->checkAccess("read", "", $node["child"]))
			{
				continue;
			}
			$ref_id[$node["obj_id"]] = $node["child"];
			$contexts[] = array("obj_id" => $node["obj_id"],
				"obj_type" => $node["type"]);
		}
		
		$news = $this->queryNewsForMultipleContexts($contexts, $a_only_public, $a_time_period,
            $a_starting_date, $a_no_auto_generated);
		foreach ($news as $k => $v)
		{
			$news[$k]["ref_id"] = $ref_id[$v["context_obj_id"]];
		}
		$data = ilNewsItem::mergeNews($data, $news);
		
		// sort and return
        $data = ilUtil::sortArray($data, "creation_date", "desc", false, true);
		
		if (!$a_prevent_aggregation)
		{
			$data = $this->aggregateFiles($data, $a_ref_id);
		}
		
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
	 * Convert time period for DB-queries
	 * 
	 * @param mixed $a_time_period
	 * @return string
	 */
	protected static function handleTimePeriod($a_time_period)
	{
		// time period is number of days
		if(is_numeric($a_time_period))
		{
			if($a_time_period > 0)
			{
				return date('Y-m-d H:i:s', time() - ($a_time_period * 24 * 60 * 60));
			}
		}
		// time period is datetime
		else if(preg_match("/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/", $a_time_period))
		{			
			return $a_time_period;
		}
		// :TODO: what to return?		
	}

	/**
	 * Query news for a context
	 *
     * @param    boolean        query for outgoing rss feed
     * @param    int            time period in seconds
     * @param    string        startind date
     * @param    boolean        do not include auto generated news items
	 */
	public function queryNewsForContext($a_for_rss_use = false, $a_time_period = 0,
        $a_starting_date = "", $a_no_auto_generated = false, $a_oldest_first = false, $a_limit = 0)
	{
		global $ilDB, $ilUser, $lng;

		$and = "";
		if ($a_time_period > 0)
		{
			$limit_ts = self::handleTimePeriod($a_time_period);
			$and = " AND creation_date >= ".$ilDB->quote($limit_ts, "timestamp")." ";
		}
		
		if ($a_starting_date != "")
		{
			$and.= " AND creation_date > ".$ilDB->quote($a_starting_date, "timestamp")." ";
		}

		if ($a_no_auto_generated)
		{
			$and.= " AND priority = 1 AND content_type = ".$ilDB->quote("text", "text")." ";
		}

		// this is changed with 4.1 (news table for lm pages)
		if ($this->getContextSubObjId() > 0)
		{
			$and.= " AND context_sub_obj_id = ".$ilDB->quote($this->getContextSubObjId(), "integer").
				" AND context_sub_obj_type = ".$ilDB->quote($this->getContextSubObjType(), "text");
		}

		$ordering = ($a_oldest_first)
            ? " creation_date ASC, id ASC "
            : " creation_date DESC, id DESC ";

		if ($a_for_rss_use && ilNewsItem::getPrivateFeedId() == false)
		{
			$query = "SELECT * ".
				"FROM il_news_item ".
				" WHERE ".
					"context_obj_id = ".$ilDB->quote($this->getContextObjId(), "integer").
					" AND context_obj_type = ".$ilDB->quote($this->getContextObjType(), "text").
					$and.
					" ORDER BY ".$ordering;
		}
		elseif (ilNewsItem::getPrivateFeedId() != false) 
		{
			$query = "SELECT il_news_item.* ".
				", il_news_read.user_id user_read ".
				"FROM il_news_item LEFT JOIN il_news_read ".
				"ON il_news_item.id = il_news_read.news_id AND ".
				" il_news_read.user_id = ".$ilDB->quote(ilNewsItem::getPrivateFeedId(), "integer").
				" WHERE ".
					"context_obj_id = ".$ilDB->quote($this->getContextObjId(), "integer").
					" AND context_obj_type = ".$ilDB->quote($this->getContextObjType(), "text").
					$and.
					" ORDER BY ".$ordering;
		}
		else
		{
			$query = "SELECT il_news_item.* ".
				", il_news_read.user_id as user_read ".
				"FROM il_news_item LEFT JOIN il_news_read ".
				"ON il_news_item.id = il_news_read.news_id AND ".
				" il_news_read.user_id = ".$ilDB->quote($ilUser->getId(), "integer").
				" WHERE ".
					"context_obj_id = ".$ilDB->quote($this->getContextObjId(), "integer").
					" AND context_obj_type = ".$ilDB->quote($this->getContextObjType(), "text").
					$and.
					" ORDER BY ".$ordering;
		}
//echo $query;
		$set = $ilDB->query($query);
		$result = array();
		while($rec = $ilDB->fetchAssoc($set))
		{
			if ($a_limit > 0 && count($result) >= $a_limit)
			{
				continue;
			}
			if (!$a_for_rss_use || 	(ilNewsItem::getPrivateFeedId() != false) || ($rec["visibility"] == NEWS_PUBLIC ||
				($rec["priority"] == 0 &&
				ilBlockSetting::_lookup("news", "public_notifications",
				0, $rec["context_obj_id"]))))
			{
				$result[$rec["id"]] = $rec;
			}
		}

		// do we get data for rss and may the time limit by an issue?
		// do a second query without time limit.
		// this is not very performant, but I do not have a better
		// idea. The keep_rss_min setting is currently (Jul 2012) only set
		// by mediacasts
		if ($a_time_period != "" && $a_for_rss_use)
		{
			include_once("./Services/Block/classes/class.ilBlockSetting.php");
			$keep_rss_min = ilBlockSetting::_lookup("news", "keep_rss_min",
				0, $this->getContextObjId());
			if ($keep_rss_min > 0)
			{
				return $this->queryNewsForContext(true, 0,
					$a_starting_date, $a_no_auto_generated, $a_oldest_first, $keep_rss_min);
			}
		}

		return $result;

	}

	/**
	 *
	 *
	 * @param int $a_ref_id
	 * @param int $a_time_period hours
	 * @return array news item ids
	 */
	public function checkNewsExistsForGroupCourse($a_ref_id, $a_time_period = 1)
	{
		global $tree, $ilDB;
		
		$all = array();

		if(!$tree->isDeleted($a_ref_id))
		{
			// parse repository branch of group
			$nodes = array();
			$node = $tree->getNodeData($a_ref_id);					
			foreach($tree->getSubTree($node) as $child)
			{
				if($child["type"] != "rolf")
				{
					$nodes[$child["obj_id"]] = $child["type"];
				}
			}

			$limit_ts = self::handleTimePeriod($a_time_period);

			// are there any news items for relevant objects and?
			$query = $ilDB->query("SELECT id,context_obj_id,context_obj_type".
				" FROM il_news_item".
				" WHERE ".$ilDB->in("context_obj_id", array_keys($nodes), false, "integer").
				" AND creation_date >= ".$ilDB->quote($limit_ts, "timestamp"));
			while($rec = $ilDB->fetchAssoc($query))
			{
				if ($nodes[$rec["context_obj_id"]] == $rec["context_obj_type"])
				{
					$all[] = $rec["id"];
				}
			}		
		}
		
		return $all;		
	}
	
	/**
	* Query News for multiple Contexts
	*
	* @param	array	$a_contexts		array of array("obj_id", "obj_type")
	*/
	public function queryNewsForMultipleContexts($a_contexts, $a_for_rss_use = false,
        $a_time_period = 0, $a_starting_date = "", $a_no_auto_generated = false,
		$a_user_id = null)
	{
		global $ilDB, $ilUser, $lng, $ilCtrl;

		$and = "";
		if ($a_time_period > 0)
		{
			$limit_ts = self::handleTimePeriod($a_time_period);
			$and = " AND creation_date >= ".$ilDB->quote($limit_ts, "timestamp")." ";
		}
			
		if ($a_starting_date != "")
		{
			$and.= " AND creation_date > ".$ilDB->quote($a_starting_date, "timestamp")." ";
		}

		if ($a_no_auto_generated)
		{
			$and.= " AND priority = 1 AND content_type = ".$ilDB->quote("text", "text")." ";
		}
		
		$ids = array();
		$type = array();
		foreach($a_contexts as $cont)
		{
			$ids[] = $cont["obj_id"];
			$type[$cont["obj_id"]] = $cont["obj_type"];
		}
		
		if ($a_for_rss_use && ilNewsItem::getPrivateFeedId() == false)
		{
			$query = "SELECT * ".
				"FROM il_news_item ".
				" WHERE ".
					$ilDB->in("context_obj_id", $ids, false, "integer")." ".
					$and.
                    " ORDER BY creation_date DESC ";
		}
		elseif (ilNewsItem::getPrivateFeedId() != false) 
		{
			$query = "SELECT il_news_item.* ".
				", il_news_read.user_id as user_read ".
				"FROM il_news_item LEFT JOIN il_news_read ".
				"ON il_news_item.id = il_news_read.news_id AND ".
				" il_news_read.user_id = ".$ilDB->quote(ilNewsItem::getPrivateFeedId(), "integer").
				" WHERE ".
					$ilDB->in("context_obj_id", $ids, false, "integer")." ".
					$and.
                    " ORDER BY creation_date DESC ";
		}		
		else
		{
			if($a_user_id)
			{
				$user_id = $a_user_id;
			}
			else
			{
				$user_id = $ilUser->getId();
			}
			$query = "SELECT il_news_item.* ".
				", il_news_read.user_id as user_read ".
				"FROM il_news_item LEFT JOIN il_news_read ".
				"ON il_news_item.id = il_news_read.news_id AND ".
				" il_news_read.user_id = ".$ilDB->quote($user_id, "integer").
				" WHERE ".
					$ilDB->in("context_obj_id", $ids, false, "integer")." ".
					$and.
                    " ORDER BY creation_date DESC ";
		}

		$set = $ilDB->query($query);
		$result = array();
		while($rec = $ilDB->fetchAssoc($set))
		{
			if ($type[$rec["context_obj_id"]] == $rec["context_obj_type"])
			{
				if (!$a_for_rss_use || ilNewsItem::getPrivateFeedId() != false || ($rec["visibility"] == NEWS_PUBLIC ||
					($rec["priority"] == 0 &&
					ilBlockSetting::_lookup("news", "public_notifications",
					0, $rec["context_obj_id"]))))
				{
					$result[$rec["id"]] = $rec;
				}
			}
		}

		return $result;

	}


	/**
	* Set item read.
	*/
	function _setRead($a_user_id, $a_news_id)
	{
		global $ilDB, $ilAppEventHandler;
		
		$ilDB->replace("il_news_read",
			array(
				"user_id" => array("integer", $a_user_id),
				"news_id" => array("integer", $a_news_id)
				),
			array()
			);
		
		/*
		$ilDB->manipulate("DELETE FROM il_news_read WHERE ".
			"user_id = ".$ilDB->quote($a_user_id, "integer").
			" AND news_id = ".$ilDB->quote($a_news_id, "integer"));
		$ilDB->manipulate("INSERT INTO il_news_read (user_id, news_id) VALUES (".
			$ilDB->quote($a_user_id, "integer").",".
			$ilDB->quote($a_news_id, "integer").")");*/

		$ilAppEventHandler->raise("Services/News", "readNews",
			array("user_id" => $a_user_id, "news_ids" => array($a_news_id)));
	}
	
	/**
	* Set item unread.
	*/
	function _setUnread($a_user_id, $a_news_id)
	{
		global $ilDB, $ilAppEventHandler;
		
		$ilDB->manipulate("DELETE FROM il_news_read (user_id, news_id) VALUES (".
			" WHERE user_id = ".$ilDB->quote($a_user_id, "integer").
			" AND news_id = ".$ilDB->quote($a_news_id, "integer"));

		$ilAppEventHandler->raise("Services/News", "unreadNews",
			array("user_id" => $a_user_id, "news_ids" => array($a_news_id)));
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
				if (!in_array($row["type"], array("root", "cat","crs", "fold", "grp")))
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
		$ilDB->manipulate("DELETE FROM il_news_read ".
			" WHERE news_id = ".$ilDB->quote($this->getId(), "integer"));
		
		// delete multimedia object
		$mob = $this->getMobId();
		
		// delete 
		parent::delete();
		
		// delete mob after news, to have a "mob usage" of 0
		if ($mob > 0 and ilObject::_exists($mob))
		{
			include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
			$mob = new ilObjMediaObject($mob);
			$mob->delete();
		}
	}
	
	/**
	* Delete all news of a context
	*
	*/
	static public function deleteNewsOfContext($a_context_obj_id,
		$a_context_obj_type, $a_context_sub_obj_id = 0, $a_context_sub_obj_type = "")
	{
		global $ilDB;
		
		if ($a_context_obj_id == 0 || $a_context_obj_type == "")
		{
			return;
		}

		if ($a_context_sub_obj_id > 0)
		{
			$and = " AND context_sub_obj_id = ".$ilDB->quote($a_context_sub_obj_id, "integer").
				" AND context_sub_obj_type = ".$ilDB->quote($a_context_sub_obj_type, "text");
		}
		
		// get news records
		$query = "SELECT * FROM il_news_item".
			" WHERE context_obj_id = ".$ilDB->quote($a_context_obj_id, "integer").
			" AND context_obj_type = ".$ilDB->quote($a_context_obj_type, "text").
			$and;

		$news_set = $ilDB->query($query);
		
		while ($news = $ilDB->fetchAssoc($news_set))
		{
			$news_obj = new ilNewsItem($news["id"]);
			$news_obj->delete();
		}
	}

	/**
	* Lookup News Title
	*/
	static function _lookupTitle($a_news_id)
	{
		global $ilDB;
		
		$query = "SELECT title FROM il_news_item WHERE id = ".
			$ilDB->quote($a_news_id, "integer");
		$set = $ilDB->query($query);
		$rec = $ilDB->fetchAssoc($set);
		return $rec["title"];
	}

	/**
	* Lookup News Visibility
	*/
	static function _lookupVisibility($a_news_id)
	{
		global $ilDB;
		
		$query = "SELECT visibility FROM il_news_item WHERE id = ".
			$ilDB->quote($a_news_id, "integer");
		$set = $ilDB->query($query);
		$rec = $ilDB->fetchAssoc($set);

		return $rec["visibility"];
	}

	/**
	* Lookup mob id
	*/
	static function _lookupMobId($a_news_id)
	{
		global $ilDB;

		$query = "SELECT mob_id FROM il_news_item WHERE id = ".
			$ilDB->quote($a_news_id, "integer");
		$set = $ilDB->query($query);
		$rec = $ilDB->fetchAssoc($set);
		return $rec["mob_id"];
	}

	/**
	* Checks whether news are available for
	*/
	static function filterObjIdsPerNews($a_obj_ids, $a_time_period = 0, $a_starting_date = "",$a_ending_date = '', $ignore_period = false)
	{
		global $ilDB;

		$and = "";
		if ($a_time_period > 0)
		{
			$limit_ts = self::handleTimePeriod($a_time_period);
			$and = " AND creation_date >= ".$ilDB->quote($limit_ts, "timestamp")." ";
		}

		if ($a_starting_date != "")
		{
			$and.= " AND creation_date >= ".$ilDB->quote($a_starting_date, "timestamp");
		}

		$query = "SELECT DISTINCT(context_obj_id) AS obj_id FROM il_news_item".
			" WHERE ".$ilDB->in("context_obj_id", $a_obj_ids, false, "integer")." ".$and;
			//" WHERE context_obj_id IN (".implode(ilUtil::quoteArray($a_obj_ids),",").")".$and;

		$set = $ilDB->query($query);
		$objs = array();
		while($rec = $ilDB->fetchAssoc($set))
		{
			$objs[] = $rec["obj_id"];
		}

		return $objs;
	}
	
	/**
	 * Determine title for news item entry
	 */
	static function determineNewsTitle($a_context_obj_type, $a_title, $a_content_is_lang_var,
		$a_agg_ref_id = 0, $a_aggregation = "")
	{
		global $lng;

		if ($a_agg_ref_id > 0)
		{
			$cnt = count($a_aggregation);
			
			// forums
			if ($a_context_obj_type == "frm")
			{
				if ($cnt > 1)
				{
					return sprintf($lng->txt("news_x_postings"), $cnt);
				}
				else
				{
					return $lng->txt("news_1_postings");
				}
			}
			else	// files
			{
				$up_cnt = $cr_cnt = 0;
				foreach($a_aggregation as $item)
				{
					if ($item["title"] == "file_updated")
					{
						$up_cnt++;
					}
					else
					{
						$cr_cnt++;
					}
				}
				$sep = "";
				if ($cr_cnt == 1)
				{
					$tit = $lng->txt("news_1_file_created");
					$sep = "<br />";
				}
				else if ($cr_cnt > 1)
				{
					$tit = sprintf($lng->txt("news_x_files_created"), $cr_cnt);
					$sep = "<br />";
				}
				if ($up_cnt == 1)
				{
					$tit .= $sep.$lng->txt("news_1_file_updated");
				}
				else if ($up_cnt > 1)
				{
					$tit .= $sep.sprintf($lng->txt("news_x_files_updated"), $up_cnt);
				}
				return $tit;
			}
		}
		else
		{
			if ($a_content_is_lang_var)
			{
				return $lng->txt($a_title);
			}
			else
			{
				return $a_title;
			}
		}
		
		return "";
	}

	/**
	 * Determine new content
	 */
	static function determineNewsContent($a_context_obj_type, $a_content, $a_is_lang_var)
	{
		global $lng;

		if ($a_is_lang_var)
		{
			$lng->loadLanguageModule($a_context_obj_type);
			return $lng->txt($a_content);
		}
		else
		{
			return $a_content;
		}
	}

	
	
	/**
	* Get first new id of news set related to a certain context
	*/
	static function getFirstNewsIdForContext($a_context_obj_id,
		$a_context_obj_type, $a_context_sub_obj_id = "", $a_context_sub_obj_type = "")
	{
		global $ilDB;
		
		// Determine how many rows should be deleted
		$query = "SELECT * ".
			"FROM il_news_item ".
			"WHERE ".
				"context_obj_id = ".$ilDB->quote($a_context_obj_id, "integer").
				" AND context_obj_type = ".$ilDB->quote($a_context_obj_type, "text").
				" AND context_sub_obj_id = ".$ilDB->quote($a_context_sub_obj_id, "integer").
				" AND ".$ilDB->equals("context_sub_obj_type", $a_context_sub_obj_type, "text", true);
				
		$set = $ilDB->query($query);
		$rec = $ilDB->fetchAssoc($set);
		
		return $rec["id"];
	}

	/**
	 * Get last news id of news set related to a certain context
	 */
	static function getLastNewsIdForContext($a_context_obj_id,
		$a_context_obj_type, $a_context_sub_obj_id = "", $a_context_sub_obj_type = "",
		$a_only_today = false)
	{
		global $ilDB;

		// Determine how many rows should be deleted
		$query = "SELECT id, update_date ".
			"FROM il_news_item ".
			"WHERE ".
				"context_obj_id = ".$ilDB->quote($a_context_obj_id, "integer").
				" AND context_obj_type = ".$ilDB->quote($a_context_obj_type, "text").
				" AND context_sub_obj_id = ".$ilDB->quote($a_context_sub_obj_id, "integer").
				" AND ".$ilDB->equals("context_sub_obj_type", $a_context_sub_obj_type, "text", true).
			" ORDER BY update_date DESC";

		$ilDB->setLimit(1);
		$set = $ilDB->query($query);
		$rec = $ilDB->fetchAssoc($set);

		$id = (int) $rec["id"];
		if ($a_only_today)
		{
			$now = ilUtil::now();
			if (substr($now, 0, 10) != substr($rec["update_date"], 0, 10))
			{
				$id = 0;
			}
		}

		return $id;
	}


	/**
	* Lookup media object usage(s)
	*/
	static function _lookupMediaObjectUsages($a_mob_id)
	{
		global $ilDB;
		
		$query = "SELECT * ".
			"FROM il_news_item ".
			"WHERE ".
				" mob_id = ".$ilDB->quote($a_mob_id, "integer");
				
		$usages = array();
		$set = $ilDB->query($query);
		while ($rec = $ilDB->fetchAssoc($set))
		{
			$usages[$rec["id"]] = array("type" => "news", "id" => $rec["id"]);
		}
		
		return $usages;
	}

	/**
	* Context Object ID
	*/
	static function _lookupContextObjId($a_news_id)
	{
		global $ilDB;
		
		$query = "SELECT * ".
			"FROM il_news_item ".
			"WHERE ".
				" id = ".$ilDB->quote($a_news_id, "integer");
		$set = $ilDB->query($query);
		$rec = $ilDB->fetchAssoc($set);
		
		return $rec["context_obj_id"];
	}

	function _lookupDefaultPDPeriod()
	{
		$news_set = new ilSetting("news");
		$per = $news_set->get("pd_period");
		if ($per == 0)
		{
			$per = 30;
		}
		
		return $per;
	}
	
	function _lookupUserPDPeriod($a_user_id)
	{
		global $ilSetting;
		
		$news_set = new ilSetting("news");
		$allow_shorter_periods = $news_set->get("allow_shorter_periods");
		$allow_longer_periods = $news_set->get("allow_longer_periods");
		$default_per = ilNewsItem::_lookupDefaultPDPeriod();
		
		include_once("./Services/Block/classes/class.ilBlockSetting.php");
		$per = ilBlockSetting::_lookup("pdnews", "news_pd_period",
			$a_user_id, 0);

		// news period information
		if ($per <= 0 ||
			(!$allow_shorter_periods && ($per < $default_per)) ||
			(!$allow_longer_periods && ($per > $default_per))
			)
		{
			$per = $default_per;
		}
		
		return $per;
	}
	
	function _lookupRSSPeriod()
	{
		$news_set = new ilSetting("news");
		$rss_period = $news_set->get("rss_period");
		if ($rss_period == 0)		// default to two weeks
		{
			$rss_period = 14;
		}
		return $rss_period;
	}
	function setPrivateFeedId ($a_userId) 
	{
		ilNewsItem::$privFeedId = $a_userId;
	}

	function getPrivateFeedId () {

		return ilNewsItem::$privFeedId;
	}
	
	/**
	 * Deliver mob file
	 *
	 * @param
	 * @return
	 */
	function deliverMobFile($a_purpose = "Standard", $a_increase_download_cnt = false)
	{
		$mob = $this->getMobId();
		include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
		$mob = new ilObjMediaObject($mob);
		$mob_dir = ilObjMediaObject::_getDirectory($mob->getId());
		
		// check purpose
		if (!$mob->hasPurposeItem($a_purpose))
		{
			return false;
		}
		
		$m_item = $mob->getMediaItem($a_purpose);
		if ($m_item->getLocationType() != "Reference")
		{
		    $file = $mob_dir."/".$m_item->getLocation();
		    if (file_exists($file) && is_file($file))
		    {
		    	if ($a_increase_download_cnt)
		    	{
		    		$this->increaseDownloadCounter();
		    	}
		        ilUtil::deliverFile($file, $m_item->getLocation(), "", false, false, false);
				return true;
		    }
		    else
		    {
		        ilUtil::sendFailure("File not found!",true);
		        return false;
		    }
		}
		else 
		{
			if ($a_increase_download_cnt)
			{
				$this->increaseDownloadCounter();
			}
		    ilUtil::redirect($m_item->getLocation());
		}
	}
	
	/**
	 * Increase download counter
	 *
	 * @param
	 * @return
	 */
	function increaseDownloadCounter()
	{
		global $ilDB;

		$cnt = $this->getMobDownloadCounter();
		$cnt++;
		$this->setMobDownloadCounter($cnt);
		$ilDB->manipulate("UPDATE il_news_item SET ".
			" mob_cnt_download = ".$ilDB->quote($cnt, "integer").
			" WHERE id = ".$ilDB->quote($this->getId(), "integer")
			);
	}
	
	/**
	 * Increase play counter
	 *
	 * @param
	 * @return
	 */
	function increasePlayCounter()
	{
		global $ilDB;

		$cnt = $this->getMobPlayCounter();
		$cnt++;
		$this->setMobPlayCounter($cnt);
		$ilDB->manipulate("UPDATE il_news_item SET ".
			" mob_cnt_play = ".$ilDB->quote($cnt, "integer").
			" WHERE id = ".$ilDB->quote($this->getId(), "integer")
			);		
	}

	/**
	 * Prepare news data from cache
	 *
	 * @param string $a_cres cache string
	 * @return array news array
	 */
	static function prepareNewsDataFromCache($a_cres)
	{
		global $ilDB;

		$data = unserialize($a_cres);
		$news_ids = array_keys($data);
		$set = $ilDB->query("SELECT id FROM il_news_item ".
			" WHERE ".$ilDB->in("id", $news_ids, false, "integer"));
		$existing_ids = array();
		while ($rec = $ilDB->fetchAssoc($set))
		{
			$existing_ids[] = $rec["id"];
		}
		//var_dump($existing_ids);
		$existing_news = array();
		foreach ($data as $k => $v)
		{
			if (in_array($k, $existing_ids))
			{
				$existing_news[$k] = $v;
			}
		}

		//var_dump($data);
		//var_dump($existing_news);

		return $existing_news;
	}

}
?>
