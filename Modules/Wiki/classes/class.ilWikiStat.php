<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Wiki statistics class
 *
 *
 * Timestamp / Current Record
 *
 * - If an event occurs the current timestamp is calculated, for not this is the timestamp without minuts/seconds
 *   2014-01-14 07:34:45 -> 2014-01-14 07:00:00 (i.e. we count "per hour" and the numbers represent 07:00:00 to 07:59:59)
 *
 *
 * Table / Primary Key / Stats / Events
 *
 * - wiki_stat					pk: wiki_id, timestamp
 *
 * 		(1) number of pages		ev: page created, page deleted
 * 								action: count pages of wiki and replace number in current record
 *
 * 		(2) deleted pages		ev: page deleted
 * 								action: increment number in current record +1
 *
 * 		(3) average rating		ev: rating saved
 * 								[action: do (10), then for current records in wiki_stat_page: sum average rating / number of records where average rating is > 0]
 *								REVISION action: do (10), then build average rating from wiki page rating records NOT wiki_stat_page							
 *
 * - wiki_stat_page				pk: wiki_id, page_id, timestamp
 *
 * 		(4) internal links		ev: page saved
 * 								action: count internal links and replace number in current record
 *
 * 		(5) external links		see internal links
 *
 * 		(6) footnotes			see internal links
 *
 * 		(7) ratings				ev: rating saved
 * 								action: count ratings and replace number in current record
 *
 * 		(8)	words				see internal links
 *
 * 		(9) characters			see internal links
 *
 * 		(10) average rating		ev: rating saved
 * 								sum ratings / number of ratings (0 if no rating given)
 *
 * - wiki_stat_user				pk: wiki_id, user_id, timestamp
 *
 * 		(11) new pages			ev: page created
 * 								action: increment number of user in current record + 1
 *
 * - wiki_stat_page_user		pk: wiki_id, page_id, user_id, timestamp
 *
 *		(12) changes			ev: page saved
 *								action: increment number of user/page in current record + 1
 *
 *		(13) read				ev: page read
 *								action: increment number of user/page in current record + 1
 *
 *
 * Events
 *
 * - page created (empty)		(1) (11)
 * - page deleted				(1) (2)
 * - page saved (content)		(4) (5) (6) (8) (9) (12)
 * - page read					(13)
 * - rating saved				(3) (10)
 *
 *
 * Deleted pages
 *
 * All historic records are kept. A current wiki_stat_page record with all values 0 is replaced/created. (?)
 *
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.de>
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ModulesWiki
 */
class ilWikiStat
{
	const EVENT_PAGE_CREATED = 1;
	const EVENT_PAGE_UPDATED = 2;
	const EVENT_PAGE_READ = 3;
	const EVENT_PAGE_DELETED = 4;
	const EVENT_PAGE_RATING = 5;
	
	const KEY_FIGURE_WIKI_NUM_PAGES = 1;
	const KEY_FIGURE_WIKI_NEW_PAGES = 2;
	const KEY_FIGURE_WIKI_NEW_PAGES_AVG = 3;
	const KEY_FIGURE_WIKI_EDIT_PAGES = 4;
	const KEY_FIGURE_WIKI_EDIT_PAGES_AVG = 5;
	const KEY_FIGURE_WIKI_DELETED_PAGES = 6;
	const KEY_FIGURE_WIKI_READ_PAGES = 7;
	
	// 
	// WRITE
	//
	
	/**
	 * Handle wiki page event
	 * 
	 * @param int $a_event
	 * @param ilWikiPage $a_page_obj
	 * @param int $a_user_id
	 * @param int $a_additional_data
	 */
	public static function handleEvent($a_event, ilWikiPage $a_page_obj, $a_user_id = null, array $a_additional_data = null)
	{
		global $ilUser;
		
		if(!$a_user_id)
		{
			$a_user_id = $ilUser->getId();
		}
		if(!$a_user_id || $a_user_id == ANONYMOUS_USER_ID)
		{
			return;
		}
		
		switch((int)$a_event)
		{
			case self::EVENT_PAGE_CREATED:
				self::handlePageCreated($a_page_obj, $a_user_id);
				break;
			
			case self::EVENT_PAGE_UPDATED:
				self::handlePageUpdated($a_page_obj, $a_user_id, $a_additional_data);
				break;
			
			case self::EVENT_PAGE_READ:
				self::handlePageRead($a_page_obj, $a_user_id);
				break;
			
			case self::EVENT_PAGE_DELETED:
				self::handlePageDeleted($a_page_obj, $a_user_id);
				break;
			
			case self::EVENT_PAGE_RATING:
				self::handlePageRating($a_page_obj, $a_user_id);
				break;
			
			default:
				return;
		}						
	}
	
	/**
	 * Get current time frame (hourly)
	 * 
	 * @return string
	 */
	protected static function getTimestamp()
	{
		return date("Y-m-d H:00:00");
	}
	
	/**
	 * Write data to DB
	 * 
	 * - Handles update/insert depending on time frame
	 * - supports increment/decrement custom values
	 * 
	 * @param string $a_table
	 * @param array $a_primary
	 * @param array $a_values	 
	 */
	protected static function writeData($a_table, array $a_primary, array $a_values)
	{
		global $ilDB;
		
		$tstamp = self::getTimestamp();		
		$a_primary["ts"] = array("timestamp", $tstamp);
		
		$ilDB->lockTables(array(0 => array('name' => $a_table, 'type' => ilDB::LOCK_WRITE)));
		
		$primary = array();
		foreach($a_primary as $column => $value)
		{
			$primary[] = $column." = ".$ilDB->quote($value[1], $value[0]);
		}		
		$primary = implode(" AND ", $primary);
		
		$set = $ilDB->query("SELECT ts FROM ".$a_table.
			" WHERE ".$primary);		
		
		$is_update = (bool)$ilDB->numRows($set);
		
		// update (current timeframe)
		if($is_update)
		{
			$values = array();
			foreach($a_values as $column => $value)
			{
				if($value[0] == "increment")
				{
					$values[] = $column." = ".$column."+1";
				}
				else if($value[0] == "decrement")
				{
					$values[] = $column." = ".$column."-1";
				}			
				else
				{
					$values[] = $column." = ".$ilDB->quote($value[1], $value[0]);
				}				
			}		
			$values = implode(", ", $values);
			
			$sql = "UPDATE ".$a_table.
				" SET ".$values.
				" WHERE ".$primary;						
		}
		// insert (no entry yet for current time frame)
		else
		{					
			$a_values = array_merge($a_primary, $a_values);
			$a_values["ts_day"] = array("text", substr($tstamp, 0, 10));
			$a_values["ts_hour"] = array("integer", (int)substr($tstamp, 11, 2));
			
			$values = array();
			foreach($a_values as $column => $value)
			{	
				$columns[] = $column;		
				if($value[0] == "increment")
				{
					$value[0] = "integer";
				}
				else if($value[0] == "decrement")
				{
					$value[0] = "integer";
					$value[1] = 0;
				}								
				$values[] = $ilDB->quote($value[1], $value[0]);						
			}		
			$values = implode(", ", $values);
			$columns = implode(", ", $columns);
			
			$sql = "INSERT INTO ".$a_table.
				" (".$columns.")".
				" VALUES (".$values.")";
		}
		$ilDB->manipulate($sql);
		
		$ilDB->unlockTables();
		
		return $is_update;
	}
	
	/**
	 * Write data to wiki_stat
	 * 
	 * @param int $a_wiki_id
	 * @param array $a_values
	 */
	protected static function writeStat($a_wiki_id, $a_values)
	{
		$primary = array(
			"wiki_id" => array("integer", $a_wiki_id)
		);
		self::writeData("wiki_stat", $primary, $a_values);
	}
	
	/**
	 * Write data to wiki_stat_page
	 * 
	 * @param int $a_wiki_id
	 * @param int $a_page_id
	 * @param array $a_values
	 */
	protected static function writeStatPage($a_wiki_id, $a_page_id, $a_values)
	{
		$primary = array(
			"wiki_id" => array("integer", $a_wiki_id),
			"page_id" => array("integer", $a_page_id),
		);
		self::writeData("wiki_stat_page", $primary, $a_values);
	}
	
	/**
	 * Write data to wiki_stat_page_user
	 * 
	 * @param int $a_wiki_id
	 * @param int $a_page_id
	 * @param int $a_user_id
	 * @param array $a_values
	 */
	protected static function writeStatPageUser($a_wiki_id, $a_page_id, $a_user_id, $a_values)
	{
		$primary = array(
			"wiki_id" => array("integer", $a_wiki_id),
			"page_id" => array("integer", $a_page_id),
			"user_id" => array("integer", $a_user_id)
		);
		self::writeData("wiki_stat_page_user", $primary, $a_values);
	}
	
	/**
	 * Write to wiki_stat_user
	 * 
	 * @param int $a_wiki_id
	 * @param int $a_user_id
	 * @param array $a_values
	 */
	protected static function writeStatUser($a_wiki_id, $a_user_id, $a_values)
	{
		$primary = array(
			"wiki_id" => array("integer", $a_wiki_id),
			"user_id" => array("integer", $a_user_id)
		);
		self::writeData("wiki_stat_user", $primary, $a_values);
	}
	
	/**
	 * Count pages in wiki
	 * 
	 * @param int $a_wiki_id
	 * @return int
	 */
	protected static function countPages($a_wiki_id)
	{
		return sizeof(ilWikiPage::getAllPages($a_wiki_id));		
	}
	
	/**
	 * Get average rating for wiki or wiki page
	 * 
	 * @param int $a_wiki_id
	 * @param int $a_page_id
	 * @return array
	 */
	protected static function getAverageRating($a_wiki_id, $a_page_id = null)
	{				
		include_once "Services/Rating/classes/class.ilRating.php";
		
		if(!$a_page_id)
		{
			return ilRating::getOverallRatingForObject(
				$a_wiki_id,
				"wiki");		
		}
		else
		{
			return ilRating::getOverallRatingForObject(
				$a_wiki_id,
				"wiki",
				$a_page_id,
				"wpg");		
		}		
	}
	
	/**
	 * Handle wiki page creation
	 * 
	 * @param ilWikiPage $a_page_obj
	 * @param int $a_user_id
	 */
	public static function handlePageCreated(ilWikiPage $a_page_obj, $a_user_id)
	{			
		// wiki: num_pages (count)
		self::writeStat($a_page_obj->getWikiId(), 
			array(
				"num_pages" => array("integer", self::countPages($a_page_obj->getWikiId()))
			));
		
		// user: new_pages+1
		self::writeStatUser($a_page_obj->getWikiId(), $a_user_id, 
			array(
				"new_pages" => array("increment", 1)
			));				
	}
	
	/**
	 * Handle wiki page update
	 * 
	 * @param ilWikiPage $a_page_obj
	 * @param int $a_user_id
	 * @param array $a_page_data
	 */
	public static function handlePageUpdated(ilWikiPage $a_page_obj, $a_user_id, array $a_page_data = null)
	{
		// page_user: changes+1
		self::writeStatPageUser($a_page_obj->getWikiId(), $a_page_obj->getId(), $a_user_id, 
			array(
				"changes" => array("increment", 1)
			));		
		
		// page: see ilWikiPage::afterUpdate()
		$values = array(
			"int_links" => array("integer", $a_page_data["int_links"]),
			"ext_links" => array("integer", $a_page_data["ext_links"]),
			"footnotes" => array("integer", $a_page_data["footnotes"]),
			"num_words" => array("integer", $a_page_data["num_words"]),
			"num_chars" => array("integer", $a_page_data["num_chars"])
		);
		self::writeStatPage($a_page_obj->getWikiId(), $a_page_obj->getId(), $values);
	}
	
	/**
	 * Handle wiki page read
	 * 
	 * @param ilWikiPage $a_page_obj
	 * @param int $a_user_id
	 */
	public static function handlePageRead(ilWikiPage $a_page_obj, $a_user_id)
	{
		// page_user: read_events+1
		self::writeStatPageUser($a_page_obj->getWikiId(), $a_page_obj->getId(), $a_user_id, 
			array(
				"read_events" => array("increment", 1)
			));				
	}
	
	/**
	 * Handle wiki page deletion
	 * 
	 * @param ilWikiPage $a_page_obj
	 * @param int $a_user_id
	 */
	public static function handlePageDeletion(ilWikiPage $a_page_obj, $a_user_id)
	{
		// wiki: del_pages+1, num_pages (count), avg_rating
		self::writeStat($a_page_obj->getWikiId(), 
			array(
				"del_pages" => array("increment", 1),
				"num_pages" => array("integer", self::countPages($a_page_obj->getWikiId())),
				"avg_rating" => array("integer", self::getWikiRating($a_page_obj->getWikiId())*100)
			));
	}
	
	/**
	 * Handle wiki page rating
	 * 
	 * @param ilWikiPage $a_page_obj
	 * @param int $a_user_id
	 */
	public static function handlePageRating(ilWikiPage $a_page_obj, $a_user_id)
	{
		// do page first!						
		$rating = self::getAverageRating($a_page_obj->getWikiId(), $a_page_obj->getId());	
		
		// wiki_stat_page: num_ratings, avg_rating
		self::writeStatPage($a_page_obj->getWikiId(), $a_page_obj->getId(), 
			array(
				"num_ratings" => array("integer", $rating["cnt"]),
				"avg_rating" => array("integer", $rating["avg"]*100),
			));
		
		$rating = self::getAverageRating($a_page_obj->getWikiId());		
		
		// wiki_stat: avg_rating
		$is_update = self::writeStat($a_page_obj->getWikiId(), 
			array(
				"avg_rating" => array("integer", $rating["avg"]*100)
			));
		
		if(!$is_update)
		{
			// wiki: num_pages (count)
			self::writeStat($a_page_obj->getWikiId(), 
				array(
					"num_pages" => array("integer", self::countPages($a_page_obj->getWikiId()))
				));
		}
	}
	
	
	// 
	// READ
	//
	
	protected static function getWikiNumPages($a_wiki_id, $a_day_from, $a_day_to)
	{
		global $ilDB;
		
		$res = array();
		
		$sql = "SELECT ts_day, MAX(num_pages) num_pages".
			" FROM wiki_stat".
			" WHERE wiki_id = ".$ilDB->quote($a_wiki_id, "integer").
			" AND ts_day >= ".$ilDB->quote($a_day_from, "text").
			" AND ts_day <= ".$ilDB->quote($a_day_to, "text").
			" AND num_pages > ".$ilDB->quote(0, "integer").
			" AND num_pages IS NOT NULL".
			" GROUP BY ts_day".
			" ORDER BY ts_day";
		$set = $ilDB->query($sql);
		while($row = $ilDB->fetchAssoc($set))
		{
			$res[$row["ts_day"]] = $row["num_pages"];
		}
		
		// get last existing value before period
		$sql = "SELECT MAX(num_pages) num_pages".
			" FROM wiki_stat".
			" WHERE wiki_id = ".$ilDB->quote($a_wiki_id, "integer").
			" AND ts_day < ".$ilDB->quote($a_day_from, "text").
			" AND num_pages > ".$ilDB->quote(0, "integer").
			" AND num_pages IS NOT NULL".
			" GROUP BY ts_day".
			" ORDER BY ts_day DESC";
		$ilDB->setLimit(1);
		$set = $ilDB->query($sql);
		$last_before_period = $ilDB->fetchAssoc($set);
		$last_before_period = $last_before_period["num_pages"];
		
		$safety = 0;
		$last = null;
		$today = date("Y-m-d");
		$current = explode("-", $a_day_from);
		$current = date("Y-m-d", mktime(0, 0, 1, $current[1], $current[2], $current[0]));
		while($current <= $a_day_to && 
			++$safety < 1000)
		{
			if(!isset($res[$current]))
			{
				if($current <= $today)
				{
					// last existing value in period
					if($last !== null)
					{
						$res[$current] = $last;
					}	
					// last existing value before period
					else if($last_before_period)
					{
						$res[$current] = $last_before_period;
					}
				}
			}
			else
			{
				$last = $res[$current];
			}
			
			$current = explode("-", $current);
			$current = date("Y-m-d", mktime(0, 0, 1, $current[1], $current[2]+1, $current[0]));
		}
		
		return $res;			
	}
	
	protected static function getWikiNewPagesSum($a_wiki_id, $a_day_from, $a_day_to)
	{
		global $ilDB;
		
		$res = array();
		
		$sql = "SELECT ts_day, SUM(new_pages) new_pages".
			" FROM wiki_stat_user".
			" WHERE wiki_id = ".$ilDB->quote($a_wiki_id, "integer").
			" AND ts_day >= ".$ilDB->quote($a_day_from, "text").
			" AND ts_day <= ".$ilDB->quote($a_day_to, "text").
			" AND new_pages > ".$ilDB->quote(0, "integer").
			" AND new_pages IS NOT NULL".
			" GROUP BY ts_day".
			" ORDER BY ts_day";
		$set = $ilDB->query($sql);
		while($row = $ilDB->fetchAssoc($set))
		{
			$res[$row["ts_day"]] = $row["new_pages"];
		}
		
		return $res;			
	}
	
	protected static function getWikiNewPagesAvg($a_wiki_id, $a_day_from, $a_day_to)
	{
		global $ilDB;
		
		$res = array();
		
		$sql = "SELECT ts_day, AVG(new_pages) new_pages".
			" FROM (".
				// subquery to build average per user
				" SELECT ts_day, AVG(new_pages) new_pages".
				" FROM wiki_stat_user".
				" WHERE wiki_id = ".$ilDB->quote($a_wiki_id, "integer").
				" AND ts_day >= ".$ilDB->quote($a_day_from, "text").
				" AND ts_day <= ".$ilDB->quote($a_day_to, "text").
				" AND new_pages > ".$ilDB->quote(0, "integer").
				" AND new_pages IS NOT NULL".
				" GROUP BY ts_day, user_id".				
			") aggr_user".
			" GROUP BY ts_day".
			" ORDER BY ts_day";
		$set = $ilDB->query($sql);
		while($row = $ilDB->fetchAssoc($set))
		{
			$res[$row["ts_day"]] = $row["new_pages"];
		}
		
		return $res;			
	}
	
	protected static function getWikiEditPagesSum($a_wiki_id, $a_day_from, $a_day_to)
	{
		global $ilDB;
		
		$res = array();
		
		$sql = "SELECT ts_day, COUNT(DISTINCT(page_id)) num_changed_pages".
			" FROM wiki_stat_page_user".
			" WHERE wiki_id = ".$ilDB->quote($a_wiki_id, "integer").
			" AND ts_day >= ".$ilDB->quote($a_day_from, "text").
			" AND ts_day <= ".$ilDB->quote($a_day_to, "text").
			" AND changes > ".$ilDB->quote(0, "integer").
			" AND changes IS NOT NULL".
			" GROUP BY ts_day".
			" ORDER BY ts_day";
		$set = $ilDB->query($sql);
		while($row = $ilDB->fetchAssoc($set))
		{
			$res[$row["ts_day"]] = $row["num_changed_pages"];
		}
		
		return $res;			
	}
	
	protected static function getWikiEditPagesAvg($a_wiki_id, $a_day_from, $a_day_to)
	{
		global $ilDB;
		
		$res = array();
		
		$sql = "SELECT ts_day, AVG(num_changed_pages) num_changed_pages".
			" FROM (".
				// subquery to build average per user
				" SELECT ts_day, COUNT(DISTINCT(page_id)) num_changed_pages".
				" FROM wiki_stat_page_user".
				" WHERE wiki_id = ".$ilDB->quote($a_wiki_id, "integer").
				" AND ts_day >= ".$ilDB->quote($a_day_from, "text").
				" AND ts_day <= ".$ilDB->quote($a_day_to, "text").
				" AND changes > ".$ilDB->quote(0, "integer").
				" AND changes IS NOT NULL".
				" GROUP BY ts_day, user_id".				
			") aggr_user".
			" GROUP BY ts_day".
			" ORDER BY ts_day";
		$set = $ilDB->query($sql);
		while($row = $ilDB->fetchAssoc($set))
		{
			$res[$row["ts_day"]] = $row["num_changed_pages"];
		}
		
		return $res;			
	}
	
	protected static function getWikiDeletedPages($a_wiki_id, $a_day_from, $a_day_to)
	{
		global $ilDB;
		
		$res = array();
		
		$sql = "SELECT ts_day, MAX(del_pages) del_pages".
			" FROM wiki_stat".
			" WHERE wiki_id = ".$ilDB->quote($a_wiki_id, "integer").
			" AND ts_day >= ".$ilDB->quote($a_day_from, "text").
			" AND ts_day <= ".$ilDB->quote($a_day_to, "text").
			" AND del_pages > ".$ilDB->quote(0, "integer").
			" AND del_pages IS NOT NULL".
			" GROUP BY ts_day".
			" ORDER BY ts_day";
		$set = $ilDB->query($sql);
		while($row = $ilDB->fetchAssoc($set))
		{
			$res[$row["ts_day"]] = $row["del_pages"];
		}
		
		return $res;			
	}
	
	protected static function getWikiReadPages($a_wiki_id, $a_day_from, $a_day_to)
	{
		global $ilDB;
		
		$res = array();
		
		$sql = "SELECT ts_day, SUM(read_events) read_events".
			" FROM wiki_stat_page_user".
			" WHERE wiki_id = ".$ilDB->quote($a_wiki_id, "integer").
			" AND ts_day >= ".$ilDB->quote($a_day_from, "text").
			" AND ts_day <= ".$ilDB->quote($a_day_to, "text").
			" AND read_events > ".$ilDB->quote(0, "integer").
			" AND read_events IS NOT NULL".
			" GROUP BY ts_day";
		$set = $ilDB->query($sql);
		while($row = $ilDB->fetchAssoc($set))
		{
			$res[$row["ts_day"]] = $row["read_events"];
		}
	
		return $res;			
	}

	public static function getAvailableMonths($a_wiki_id)
	{
		global $ilDB;
		
		$res = array();
		
		$set = $ilDB->query("SELECT DISTINCT(SUBSTR(ts_day, 1, 7)) month".
			" FROM wiki_stat_page_user".
			" WHERE wiki_id = ".$ilDB->quote($a_wiki_id, "integer").
			" AND ts_day IS NOT NULL");
		while($row = $ilDB->fetchAssoc($set))
		{			
			$res[] = $row["month"];
		}
		
		return $res;				
	}
	
		
	public static function getFigures()
	{
		return array(				
			self::KEY_FIGURE_WIKI_NUM_PAGES,
			self::KEY_FIGURE_WIKI_NEW_PAGES,
			self::KEY_FIGURE_WIKI_NEW_PAGES_AVG,
			self::KEY_FIGURE_WIKI_EDIT_PAGES,
			self::KEY_FIGURE_WIKI_EDIT_PAGES_AVG,
			self::KEY_FIGURE_WIKI_DELETED_PAGES,
			self::KEY_FIGURE_WIKI_READ_PAGES
		);
	}
	
	public static function getFigureTitle($a_figure)
	{
		global $lng;
		
		$map = array(				
			self::KEY_FIGURE_WIKI_NUM_PAGES => $lng->txt("wiki_stat_num_pages"),
			self::KEY_FIGURE_WIKI_NEW_PAGES => $lng->txt("wiki_stat_new_pages"),
			self::KEY_FIGURE_WIKI_NEW_PAGES_AVG => $lng->txt("wiki_stat_new_pages_avg"),
			self::KEY_FIGURE_WIKI_EDIT_PAGES => $lng->txt("wiki_stat_edit_pages"),
			self::KEY_FIGURE_WIKI_EDIT_PAGES_AVG => $lng->txt("wiki_stat_edit_pages_avg"),
			self::KEY_FIGURE_WIKI_DELETED_PAGES => $lng->txt("wiki_stat_deleted_pages"),
			self::KEY_FIGURE_WIKI_READ_PAGES => $lng->txt("wiki_stat_read_pages")
		);
		
		return $map[$a_figure];		
	}
	
	public static function getFigureData($a_wiki_id, $a_figure, $a_from, $a_to)
	{
		switch($a_figure)
		{				
			case self::KEY_FIGURE_WIKI_NUM_PAGES:
				return self::getWikiNumPages($a_wiki_id, $a_from, $a_to);							

			case self::KEY_FIGURE_WIKI_NEW_PAGES:
				return self::getWikiNewPagesSum($a_wiki_id, $a_from, $a_to);					

			case self::KEY_FIGURE_WIKI_NEW_PAGES_AVG:
				return self::getWikiNewPagesAvg($a_wiki_id, $a_from, $a_to);				

			case self::KEY_FIGURE_WIKI_EDIT_PAGES:
				return self::getWikiEditPagesSum($a_wiki_id, $a_from, $a_to);					

			case self::KEY_FIGURE_WIKI_EDIT_PAGES_AVG:
				return self::getWikiEditPagesAvg($a_wiki_id, $a_from, $a_to);				
				
			case self::KEY_FIGURE_WIKI_DELETED_PAGES:
				return self::getWikiDeletedPages($a_wiki_id, $a_from, $a_to);			
				
			case self::KEY_FIGURE_WIKI_READ_PAGES:
				return self::getWikiReadPages($a_wiki_id, $a_from, $a_to);			
		}
	}
	
	public static function getFigureOptions()
	{
		$res = array();
		
		foreach(self::getFigures() as $figure)
		{
			$res[$figure] = self::getFigureTitle($figure);
		};
		
		return $res;		
	}
}

?>