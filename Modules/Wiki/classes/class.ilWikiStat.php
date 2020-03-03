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
    const KEY_FIGURE_WIKI_USER_EDIT_PAGES = 8;
    const KEY_FIGURE_WIKI_USER_EDIT_PAGES_AVG = 9;
    const KEY_FIGURE_WIKI_NUM_RATING = 10;
    const KEY_FIGURE_WIKI_NUM_RATING_AVG = 11;
    const KEY_FIGURE_WIKI_RATING_AVG = 12;
    const KEY_FIGURE_WIKI_INTERNAL_LINKS = 13;
    const KEY_FIGURE_WIKI_INTERNAL_LINKS_AVG = 14;
    const KEY_FIGURE_WIKI_EXTERNAL_LINKS = 15;
    const KEY_FIGURE_WIKI_EXTERNAL_LINKS_AVG = 16;
    const KEY_FIGURE_WIKI_WORDS = 17;
    const KEY_FIGURE_WIKI_WORDS_AVG = 18;
    const KEY_FIGURE_WIKI_CHARS = 19;
    const KEY_FIGURE_WIKI_CHARS_AVG = 20;
    const KEY_FIGURE_WIKI_FOOTNOTES = 21;
    const KEY_FIGURE_WIKI_FOOTNOTES_AVG = 22;
    
    const KEY_FIGURE_WIKI_PAGE_CHANGES = 23;
    const KEY_FIGURE_WIKI_PAGE_CHANGES_AVG = 24;
    const KEY_FIGURE_WIKI_PAGE_USER_EDIT = 25;
    const KEY_FIGURE_WIKI_PAGE_READ = 26;
    const KEY_FIGURE_WIKI_PAGE_INTERNAL_LINKS = 27;
    const KEY_FIGURE_WIKI_PAGE_EXTERNAL_LINKS = 28;
    const KEY_FIGURE_WIKI_PAGE_WORDS = 29;
    const KEY_FIGURE_WIKI_PAGE_CHARS = 30;
    const KEY_FIGURE_WIKI_PAGE_FOOTNOTES = 31;
    const KEY_FIGURE_WIKI_PAGE_RATINGS = 32;
    
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
        global $DIC;

        $ilUser = $DIC->user();
        
        if (!$a_user_id) {
            $a_user_id = $ilUser->getId();
        }
        if (!$a_user_id || $a_user_id == ANONYMOUS_USER_ID) {
            return;
        }
        
        switch ((int) $a_event) {
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
                self::handlePageDeletion($a_page_obj, $a_user_id);
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
        global $DIC;

        $ilDB = $DIC->database();
        
        $tstamp = self::getTimestamp();
        $a_primary["ts"] = array("timestamp", $tstamp);

        $ilAtomQuery = $ilDB->buildAtomQuery();
        $ilAtomQuery->addTableLock($a_table);

        $ilAtomQuery->addQueryCallable(
            function (ilDBInterface $ilDB) use ($a_table,  $a_primary, $a_values, $tstamp, &$is_update) {
                $primary = array();
                foreach ($a_primary as $column => $value) {
                    $primary[] = $column . " = " . $ilDB->quote($value[1], $value[0]);
                }
                $primary = implode(" AND ", $primary);

                $set = $ilDB->query("SELECT ts FROM " . $a_table .
                " WHERE " . $primary);

                $is_update = (bool) $ilDB->numRows($set);

                // update (current timeframe)
                if ($is_update) {
                    $values = array();
                    foreach ($a_values as $column => $value) {
                        if ($value[0] == "increment") {
                            $values[] = $column . " = " . $column . "+1";
                        } elseif ($value[0] == "decrement") {
                            $values[] = $column . " = " . $column . "-1";
                        } else {
                            $values[] = $column . " = " . $ilDB->quote($value[1], $value[0]);
                        }
                    }
                    $values = implode(", ", $values);

                    $sql = "UPDATE " . $a_table .
                    " SET " . $values .
                    " WHERE " . $primary;
                }
                // insert (no entry yet for current time frame)
                else {
                    $a_values = array_merge($a_primary, $a_values);
                    $a_values["ts_day"] = array("text", substr($tstamp, 0, 10));
                    $a_values["ts_hour"] = array("integer", (int) substr($tstamp, 11, 2));

                    $values = array();
                    foreach ($a_values as $column => $value) {
                        $columns[] = $column;
                        if ($value[0] == "increment") {
                            $value[0] = "integer";
                        } elseif ($value[0] == "decrement") {
                            $value[0] = "integer";
                            $value[1] = 0;
                        }
                        $values[] = $ilDB->quote($value[1], $value[0]);
                    }
                    $values = implode(", ", $values);
                    $columns = implode(", ", $columns);

                    $sql = "INSERT INTO " . $a_table .
                    " (" . $columns . ")" .
                    " VALUES (" . $values . ")";
                }
                $ilDB->manipulate($sql);
            }
        );
        $ilAtomQuery->run();
        
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
        return sizeof(ilWikiPage::getAllWikiPages($a_wiki_id));
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
        
        if (!$a_page_id) {
            return ilRating::getOverallRatingForObject(
                $a_wiki_id,
                "wiki"
            );
        } else {
            return ilRating::getOverallRatingForObject(
                $a_wiki_id,
                "wiki",
                $a_page_id,
                "wpg"
            );
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
        self::writeStat(
            $a_page_obj->getWikiId(),
            array(
                "num_pages" => array("integer", self::countPages($a_page_obj->getWikiId())),
                "del_pages" => array("integer", 0),
                "avg_rating" => array("integer", 0)
            )
        );
        
        // user: new_pages+1
        self::writeStatUser(
            $a_page_obj->getWikiId(),
            $a_user_id,
            array(
                "new_pages" => array("increment", 1)
            )
        );
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
        self::writeStatPageUser(
            $a_page_obj->getWikiId(),
            $a_page_obj->getId(),
            $a_user_id,
            array(
                "changes" => array("increment", 1)
            )
        );
        
        // page: see ilWikiPage::afterUpdate()
        $values = array(
            "int_links" => array("integer", $a_page_data["int_links"]),
            "ext_links" => array("integer", $a_page_data["ext_links"]),
            "footnotes" => array("integer", $a_page_data["footnotes"]),
            "num_words" => array("integer", $a_page_data["num_words"]),
            "num_chars" => array("integer", $a_page_data["num_chars"]),
            "num_ratings" => array("integer", 0),
            "avg_rating" => array("integer", 0)
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
        self::writeStatPageUser(
            $a_page_obj->getWikiId(),
            $a_page_obj->getId(),
            $a_user_id,
            array(
                "read_events" => array("increment", 1)
            )
        );
    }
    
    /**
     * Handle wiki page deletion
     *
     * @param ilWikiPage $a_page_obj
     * @param int $a_user_id
     */
    public static function handlePageDeletion(ilWikiPage $a_page_obj, $a_user_id)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        // copy last entry to have deletion timestamp
        $sql = "SELECT * " .
            " FROM wiki_stat_page" .
            " WHERE wiki_id = " . $ilDB->quote($a_page_obj->getWikiId(), "integer") .
            " AND page_id = " . $ilDB->quote($a_page_obj->getId(), "integer") .
            " ORDER BY ts DESC";
        $ilDB->setLimit(1);
        $set = $ilDB->query($sql);
        
        // #15748
        if ($ilDB->numRows($set)) {
            $data = $ilDB->fetchAssoc($set);

            // see self::handlePageUpdated()
            $values = array(
                "int_links" => array("integer", $data["int_links"]),
                "ext_links" => array("integer", $data["ext_links"]),
                "footnotes" => array("integer", $data["footnotes"]),
                "num_words" => array("integer", $data["num_words"]),
                "num_chars" => array("integer", $data["num_chars"]),
                "num_ratings" => array("integer", $data["num_ratings"]),
                "avg_rating" => array("integer", $data["avg_rating"]),
            );
            self::writeStatPage((int) $a_page_obj->getWikiId(), $a_page_obj->getId(), $values);
        }
        
        // mark all page entries as deleted
        $ilDB->manipulate("UPDATE wiki_stat_page" .
            " SET deleted = " . $ilDB->quote(1, "integer") .
            " WHERE page_id = " . $ilDB->quote($a_page_obj->getId(), "integer") .
            " AND wiki_id = " . $ilDB->quote($a_page_obj->getWikiId(), "integer"));
        
        // wiki: del_pages+1, num_pages (count), avg_rating
        $rating = self::getAverageRating($a_page_obj->getWikiId());
        self::writeStat(
            $a_page_obj->getWikiId(),
            array(
                "del_pages" => array("increment", 1),
                "num_pages" => array("integer", self::countPages($a_page_obj->getWikiId())),
                "avg_rating" => array("integer", $rating["avg"]*100)
            )
        );
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
        self::writeStatPage(
            $a_page_obj->getWikiId(),
            $a_page_obj->getId(),
            array(
                "num_ratings" => array("integer", $rating["cnt"]),
                "avg_rating" => array("integer", $rating["avg"]*100),
            )
        );
        
        $rating = self::getAverageRating($a_page_obj->getWikiId());
        
        // wiki_stat: avg_rating
        $is_update = self::writeStat(
            $a_page_obj->getWikiId(),
            array(
                "avg_rating" => array("integer", $rating["avg"]*100)
            )
        );
        
        if (!$is_update) {
            // wiki: num_pages (count)
            self::writeStat(
                $a_page_obj->getWikiId(),
                array(
                    "num_pages" => array("integer", self::countPages($a_page_obj->getWikiId()))
                )
            );
        }
    }
    
    
    //
    // READ HELPER
    //

    protected static function getWikiAggr($a_wiki_id, $a_day_from, $a_day_to, $a_table, $a_field, $a_aggr_value, $a_sub_field = null, $a_sub_id = null, $a_build_full_period = false)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $res = array();
        $deleted = null;
        
        $sql = "SELECT ts_day, " . sprintf($a_aggr_value, $a_field) . " " . $a_field;
        if ($a_table == "wiki_stat_page" && $a_sub_field) {
            $sql .= ", MAX(deleted) deleted";
        }
        $sql .= " FROM " . $a_table .
            " WHERE wiki_id = " . $ilDB->quote($a_wiki_id, "integer") .
            " AND ts_day >= " . $ilDB->quote($a_day_from, "text") .
            " AND ts_day <= " . $ilDB->quote($a_day_to, "text");
        if (!$a_build_full_period) {
            // to build full period data we need all values in DB
            $sql .= " AND " . $a_field . " > " . $ilDB->quote(0, "integer") .
            " AND " . $a_field . " IS NOT NULL";
        }
        if ($a_sub_field) {
            $sql .= " AND " . $a_sub_field . " = " . $ilDB->quote($a_sub_id, "integer");
        }
        $sql .= " GROUP BY ts_day" .
            " ORDER BY ts_day";
        $set = $ilDB->query($sql);
        while ($row = $ilDB->fetchAssoc($set)) {
            $res[$row["ts_day"]] = $row[$a_field];
            
            $deleted = max($row["deleted"], $deleted);
        }
        
        if ($a_build_full_period) {
            $period_first = $a_day_from;
            $period_last = $a_day_to;
            
            // check if sub was deleted in period
            if ($a_table == "wiki_stat_page" && $a_sub_field && $deleted) {
                $sql = "SELECT MAX(ts_day) last_day, MIN(ts_day) first_day" .
                    " FROM " . $a_table .
                    " WHERE wiki_id = " . $ilDB->quote($a_wiki_id, "integer") .
                    " AND " . $a_sub_field . " = " . $ilDB->quote($a_sub_id, "integer");
                $set = $ilDB->query($sql);
                $row = $ilDB->fetchAssoc($set);
                $last_day = $row["last_day"];
                if ($last_day < $period_last) {
                    $period_last = $last_day;
                }
                $first_day = $row["first_day"];
                if ($first_day > $period_first) {
                    $period_first = $first_day;
                }
            }
            
            $last_before_period = null;
            if (!$res[$a_day_from]) {
                $last_before_period = self::getWikiLast($a_wiki_id, $a_day_from, $a_table, $a_field, $a_sub_field, $a_sub_id);
            }
            
            // no need to allow zero here as we are not building averages
            self::buildFullPeriodData($res, $period_first, $period_last, $last_before_period);
        }
        
        return $res;
    }
    
    protected static function getWikiLast($a_wiki_id, $a_day_from, $a_table, $a_field, $a_sub_field = null, $a_sub_id = null)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        // get last existing value before period (zero is valid)
        $sql = "SELECT MAX(" . $a_field . ") latest" .
            " FROM " . $a_table .
            " WHERE wiki_id = " . $ilDB->quote($a_wiki_id, "integer") .
            " AND ts_day < " . $ilDB->quote($a_day_from, "text");
        if ($a_sub_field) {
            $sql .= " AND " . $a_sub_field . " = " . $ilDB->quote($a_sub_id, "integer");
        }
        $sql .= " GROUP BY ts_day" .
            " ORDER BY ts_day DESC";
        $ilDB->setLimit(1);
        $set = $ilDB->query($sql);
        $last_before_period = $ilDB->fetchAssoc($set);
        return $last_before_period["latest"];
    }
    
    protected static function getWikiAggrSub($a_wiki_id, $a_day_from, $a_day_to, $a_table, $a_field, $a_aggr_by, $a_aggr_value, $a_aggr_sub, $a_sub_field = null, $a_sub_id = null, $a_build_full_period = false)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $res = array();
        
        if (!$a_build_full_period) {
            $sql = "SELECT ts_day, " . sprintf($a_aggr_value, $a_field) . " " . $a_field .
                " FROM (" .
                    // subquery to build average per $a_aggr_by
                    " SELECT ts_day, " . sprintf($a_aggr_sub, $a_field) . " " . $a_field .
                    " FROM " . $a_table .
                    " WHERE wiki_id = " . $ilDB->quote($a_wiki_id, "integer") .
                    " AND ts_day >= " . $ilDB->quote($a_day_from, "text") .
                    " AND ts_day <= " . $ilDB->quote($a_day_to, "text") .
                    " AND " . $a_field . " > " . $ilDB->quote(0, "integer") .
                    " AND " . $a_field . " IS NOT NULL";
            if ($a_sub_field) {
                $sql .= " AND " . $a_sub_field . " = " . $ilDB->quote($a_sub_id, "integer");
            }
            $sql .= " GROUP BY ts_day, " . $a_aggr_by .
                ") aggr_sub" .
                " GROUP BY ts_day" .
                " ORDER BY ts_day";
            $set = $ilDB->query($sql);
            while ($row = $ilDB->fetchAssoc($set)) {
                $res[$row["ts_day"]] = $row[$a_field];
            }
        } else {
            $tmp = $all_aggr_ids = $deleted_in_period = $first_day_in_period = array();
            
            if ($a_table != "wiki_stat_page") {
                echo "can only build full period averages for wiki_stat_page";
                exit();
            }
            
            // as current period can be totally empty, gather existing subs
            $sql = " SELECT *" .
                " FROM (" .
                    " SELECT " . $a_aggr_by . ", MAX(deleted) deleted, MAX(ts_day) last_day, MIN(ts_day) first_day" .
                    " FROM " . $a_table .
                    " WHERE wiki_id = " . $ilDB->quote($a_wiki_id, "integer") .
                    " GROUP BY " . $a_aggr_by .
                ") aggr_sub" .
                " WHERE first_day <= " . $ilDB->quote($a_day_to, "text") . // not created after period
                " AND (last_day >= " . $ilDB->quote($a_day_from, "text") . // (deleted in/after period
                " OR deleted = " . $ilDB->quote(0, "integer") . ")";		// or still existing)
            $set = $ilDB->query($sql);
            while ($row = $ilDB->fetchAssoc($set)) {
                $all_aggr_ids[] = $row[$a_aggr_by];
                                
                // if deleted in period we need the last day
                if ($row["deleted"] && $row["last_day"] < $a_day_to) {
                    $deleted_in_period[$row[$a_aggr_by]] = $row["last_day"];
                }
                // if created in period we need the first day
                if ($row["first_day"] > $a_day_from) {
                    $first_day_in_period[$row[$a_aggr_by]] = $row["first_day"];
                }
            }
            
            // we need to build average manually after completing period data (zero is valid)
            $sql = " SELECT ts_day, " . $a_aggr_by . ", " . sprintf($a_aggr_sub, $a_field) . " " . $a_field .
                " FROM " . $a_table .
                " WHERE wiki_id = " . $ilDB->quote($a_wiki_id, "integer") .
                " AND ts_day >= " . $ilDB->quote($a_day_from, "text") .
                " AND ts_day <= " . $ilDB->quote($a_day_to, "text");
            $sql .= " GROUP BY ts_day, " . $a_aggr_by;
            $set = $ilDB->query($sql);
            while ($row = $ilDB->fetchAssoc($set)) {
                if (!in_array($row[$a_aggr_by], $all_aggr_ids)) {
                    var_dump("unexpected wiki_stat_page_entry", $row);
                }
                $tmp[$row[$a_aggr_by]][$row["ts_day"]] = $row[$a_field];
            }
            
            // build full period for each sub
            foreach ($all_aggr_ids as $aggr_by_id) {
                // last of entry of sub is before period
                if (!is_array($tmp[$aggr_by_id])) {
                    $tmp[$aggr_by_id] = array();
                }
                
                // get last value before period to add missing entries in period
                $last_before_period = null;
                if (!$tmp[$aggr_by_id][$a_day_from]) {
                    $last_before_period = self::getWikiLast($a_wiki_id, $a_day_from, $a_table, $a_field, $a_aggr_by, $aggr_by_id);
                }
                
                // if sub was created in period (see above), shorten period accordingly
                $first_period_day = isset($first_day_in_period[$aggr_by_id])
                    ? $first_day_in_period[$aggr_by_id]
                    : $a_day_from;
                
                // if sub was deleted in period (see above), shorten period accordingly
                $last_period_day = isset($deleted_in_period[$aggr_by_id])
                    ? $deleted_in_period[$aggr_by_id]
                    : $a_day_to;
                
                // allow zero as we need to correct number of valid subs per day (see below - AVG)
                self::buildFullPeriodData($tmp[$aggr_by_id], $first_period_day, $last_period_day, $last_before_period, true);
                
                // distribute sub to days
                foreach ($tmp[$aggr_by_id] as $day => $value) {
                    $res[$day][$aggr_by_id] = $value;
                }
            }
            
            // build average over subs
            foreach ($res as $day => $values) {
                switch ($a_aggr_value) {
                    case "AVG(%s)":
                        $res[$day] = array_sum($values)/sizeof($values);
                        break;
                    
                    case "SUM(%s)":
                        $res[$day] = array_sum($values);
                        break;
                    
                    default:
                        var_dump("unsupport aggr " . $a_aggr_value);
                        break;
                }
            }
        }
        
        return $res;
    }
        
    protected static function buildFullPeriodData(array &$a_res, $a_day_from, $a_day_to, $a_last_before_period, $a_allow_zero = false)
    {
        // build full data for period
        $safety = 0;
        $last = null;
        $today = date("Y-m-d");
        $current = explode("-", $a_day_from);
        $current = date("Y-m-d", mktime(0, 0, 1, $current[1], $current[2], $current[0]));
        while ($current <= $a_day_to &&
            ++$safety < 1000) {
            if (!isset($a_res[$current])) {
                if ($current <= $today) {
                    // last existing value in period
                    if ($last !== null) {
                        $a_res[$current] = $last;
                    }
                    // last existing value before period
                    elseif ($a_last_before_period || $a_allow_zero) {
                        $a_res[$current] = $a_last_before_period;
                    }
                }
            } else {
                $last = $a_res[$current];
            }
            
            $current = explode("-", $current);
            $current = date("Y-m-d", mktime(0, 0, 1, $current[1], $current[2]+1, $current[0]));
        }
    }
    
    
    //
    // READ WIKI
    //
    
    protected static function getWikiNumPages($a_wiki_id, $a_day_from, $a_day_to)
    {
        return self::getWikiAggr($a_wiki_id, $a_day_from, $a_day_to, "wiki_stat", "num_pages", "MAX(%s)", null, null, true);
    }
    
    protected static function getWikiNewPagesSum($a_wiki_id, $a_day_from, $a_day_to)
    {
        return self::getWikiAggr($a_wiki_id, $a_day_from, $a_day_to, "wiki_stat_user", "new_pages", "SUM(%s)");
    }
    
    protected static function getWikiNewPagesAvg($a_wiki_id, $a_day_from, $a_day_to)
    {
        return self::getWikiAggrSub($a_wiki_id, $a_day_from, $a_day_to, "wiki_stat_user", "new_pages", "user_id", "AVG(%s)", "SUM(%s)");
    }
    
    protected static function getWikiDeletedPages($a_wiki_id, $a_day_from, $a_day_to)
    {
        return self::getWikiAggr($a_wiki_id, $a_day_from, $a_day_to, "wiki_stat", "del_pages", "SUM(%s)");
    }
    
    protected static function getWikiReadPages($a_wiki_id, $a_day_from, $a_day_to)
    {
        return self::getWikiAggr($a_wiki_id, $a_day_from, $a_day_to, "wiki_stat_page_user", "read_events", "SUM(%s)");
    }
    
    protected static function getWikiEditPagesSum($a_wiki_id, $a_day_from, $a_day_to)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $res = array();
        
        $sql = "SELECT ts_day, COUNT(DISTINCT(page_id)) num_changed_pages" .
            " FROM wiki_stat_page_user" .
            " WHERE wiki_id = " . $ilDB->quote($a_wiki_id, "integer") .
            " AND ts_day >= " . $ilDB->quote($a_day_from, "text") .
            " AND ts_day <= " . $ilDB->quote($a_day_to, "text") .
            " AND changes > " . $ilDB->quote(0, "integer") .
            " AND changes IS NOT NULL" .
            " GROUP BY ts_day" .
            " ORDER BY ts_day";
        $set = $ilDB->query($sql);
        while ($row = $ilDB->fetchAssoc($set)) {
            $res[$row["ts_day"]] = $row["num_changed_pages"];
        }
        
        return $res;
    }
    
    protected static function getWikiEditPagesAvg($a_wiki_id, $a_day_from, $a_day_to)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $res = array();
        
        $sql = "SELECT ts_day, AVG(num_changed_pages) num_changed_pages" .
            " FROM (" .
                // subquery to build average per user
                " SELECT ts_day, COUNT(DISTINCT(page_id)) num_changed_pages" .
                " FROM wiki_stat_page_user" .
                " WHERE wiki_id = " . $ilDB->quote($a_wiki_id, "integer") .
                " AND ts_day >= " . $ilDB->quote($a_day_from, "text") .
                " AND ts_day <= " . $ilDB->quote($a_day_to, "text") .
                " AND changes > " . $ilDB->quote(0, "integer") .
                " AND changes IS NOT NULL" .
                " GROUP BY ts_day, user_id" .
            ") aggr_user" .
            " GROUP BY ts_day" .
            " ORDER BY ts_day";
        $set = $ilDB->query($sql);
        while ($row = $ilDB->fetchAssoc($set)) {
            $res[$row["ts_day"]] = $row["num_changed_pages"];
        }
        
        return $res;
    }
    
    protected static function getWikiUserEditPages($a_wiki_id, $a_day_from, $a_day_to, $a_sub_field = null, $a_sub_id = null)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $res = array();
        
        $sql = "SELECT ts_day, COUNT(DISTINCT(user_id)) num_changed_users" .
            " FROM wiki_stat_page_user" .
            " WHERE wiki_id = " . $ilDB->quote($a_wiki_id, "integer") .
            " AND ts_day >= " . $ilDB->quote($a_day_from, "text") .
            " AND ts_day <= " . $ilDB->quote($a_day_to, "text") .
            " AND changes > " . $ilDB->quote(0, "integer") .
            " AND changes IS NOT NULL";
        if ($a_sub_field) {
            $sql .= " AND " . $a_sub_field . " = " . $ilDB->quote($a_sub_id, "integer");
        }
        $sql .= " GROUP BY ts_day" .
            " ORDER BY ts_day";
        $set = $ilDB->query($sql);
        while ($row = $ilDB->fetchAssoc($set)) {
            $res[$row["ts_day"]] = $row["num_changed_users"];
        }
        
        return $res;
    }
    
    protected static function getWikiUserEditPagesAvg($a_wiki_id, $a_day_from, $a_day_to)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $res = array();
        
        $sql = "SELECT ts_day, AVG(num_changed_users) num_changed_users" .
            " FROM (" .
                // subquery to build average per page
                " SELECT ts_day, COUNT(DISTINCT(user_id)) num_changed_users" .
                " FROM wiki_stat_page_user" .
                " WHERE wiki_id = " . $ilDB->quote($a_wiki_id, "integer") .
                " AND ts_day >= " . $ilDB->quote($a_day_from, "text") .
                " AND ts_day <= " . $ilDB->quote($a_day_to, "text") .
                " AND changes > " . $ilDB->quote(0, "integer") .
                " AND changes IS NOT NULL" .
                " GROUP BY ts_day, page_id" .
            ") aggr_user" .
            " GROUP BY ts_day" .
            " ORDER BY ts_day";
        $set = $ilDB->query($sql);
        while ($row = $ilDB->fetchAssoc($set)) {
            $res[$row["ts_day"]] = $row["num_changed_users"];
        }
        
        return $res;
    }
        
    protected static function getWikiNumRating($a_wiki_id, $a_day_from, $a_day_to)
    {
        return self::getWikiAggr($a_wiki_id, $a_day_from, $a_day_to, "wiki_stat_page", "num_ratings", "SUM(%s)");
    }
    
    protected static function getWikiNumRatingAvg($a_wiki_id, $a_day_from, $a_day_to)
    {
        return self::getWikiAggrSub($a_wiki_id, $a_day_from, $a_day_to, "wiki_stat_page", "num_ratings", "page_id", "AVG(%s)", "SUM(%s)");
    }
    
    protected static function getWikiRatingAvg($a_wiki_id, $a_day_from, $a_day_to)
    {
        $res = self::getWikiAggr($a_wiki_id, $a_day_from, $a_day_to, "wiki_stat", "avg_rating", "AVG(%s)");
        
        foreach (array_keys($res) as $day) {
            // int-to-float
            $res[$day] = $res[$day]/100;
        }
        
        return $res;
    }
    
    protected static function getWikiInternalLinks($a_wiki_id, $a_day_from, $a_day_to)
    {
        return self::getWikiAggrSub($a_wiki_id, $a_day_from, $a_day_to, "wiki_stat_page", "int_links", "page_id", "SUM(%s)", "MAX(%s)", null, null, true);
    }
    
    protected static function getWikiInternalLinksAvg($a_wiki_id, $a_day_from, $a_day_to)
    {
        return self::getWikiAggrSub($a_wiki_id, $a_day_from, $a_day_to, "wiki_stat_page", "int_links", "page_id", "AVG(%s)", "MAX(%s)", null, null, true);
    }
    
    protected static function getWikiExternalLinks($a_wiki_id, $a_day_from, $a_day_to)
    {
        return self::getWikiAggrSub($a_wiki_id, $a_day_from, $a_day_to, "wiki_stat_page", "ext_links", "page_id", "SUM(%s)", "MAX(%s)", null, null, true);
    }
    
    protected static function getWikiExternalLinksAvg($a_wiki_id, $a_day_from, $a_day_to)
    {
        return self::getWikiAggrSub($a_wiki_id, $a_day_from, $a_day_to, "wiki_stat_page", "ext_links", "page_id", "AVG(%s)", "MAX(%s)", null, null, true);
    }
    
    protected static function getWikiWords($a_wiki_id, $a_day_from, $a_day_to)
    {
        return self::getWikiAggrSub($a_wiki_id, $a_day_from, $a_day_to, "wiki_stat_page", "num_words", "page_id", "SUM(%s)", "MAX(%s)", null, null, true);
    }
    
    protected static function getWikiWordsAvg($a_wiki_id, $a_day_from, $a_day_to)
    {
        return self::getWikiAggrSub($a_wiki_id, $a_day_from, $a_day_to, "wiki_stat_page", "num_words", "page_id", "AVG(%s)", "MAX(%s)", null, null, true);
    }
    
    protected static function getWikiCharacters($a_wiki_id, $a_day_from, $a_day_to)
    {
        return self::getWikiAggrSub($a_wiki_id, $a_day_from, $a_day_to, "wiki_stat_page", "num_chars", "page_id", "SUM(%s)", "MAX(%s)", null, null, true);
    }
    
    protected static function getWikiCharactersAvg($a_wiki_id, $a_day_from, $a_day_to)
    {
        return self::getWikiAggrSub($a_wiki_id, $a_day_from, $a_day_to, "wiki_stat_page", "num_chars", "page_id", "AVG(%s)", "MAX(%s)", null, null, true);
    }
    
    protected static function getWikiFootnotes($a_wiki_id, $a_day_from, $a_day_to)
    {
        return self::getWikiAggrSub($a_wiki_id, $a_day_from, $a_day_to, "wiki_stat_page", "footnotes", "page_id", "SUM(%s)", "MAX(%s)", null, null, true);
    }
    
    protected static function getWikiFootnotesAvg($a_wiki_id, $a_day_from, $a_day_to)
    {
        return self::getWikiAggrSub($a_wiki_id, $a_day_from, $a_day_to, "wiki_stat_page", "footnotes", "page_id", "AVG(%s)", "MAX(%s)", null, null, true);
    }
    
    
    //
    // READ PAGE
    //
    
    protected static function getWikiPageChanges($a_wiki_id, $a_page_id, $a_day_from, $a_day_to)
    {
        return self::getWikiAggr($a_wiki_id, $a_day_from, $a_day_to, "wiki_stat_page_user", "changes", "SUM(%s)", "page_id", $a_page_id);
    }
    
    protected static function getWikiPageChangesAvg($a_wiki_id, $a_page_id, $a_day_from, $a_day_to)
    {
        return self::getWikiAggrSub($a_wiki_id, $a_day_from, $a_day_to, "wiki_stat_page_user", "changes", "user_id", "AVG(%s)", "SUM(%s)", "page_id", $a_page_id);
    }
    
    protected static function getWikiPageUserEdit($a_wiki_id, $a_page_id, $a_day_from, $a_day_to)
    {
        return self::getWikiUserEditPages($a_wiki_id, $a_day_from, $a_day_to, "page_id", $a_page_id);
    }
    
    protected static function getWikiPageRead($a_wiki_id, $a_page_id, $a_day_from, $a_day_to)
    {
        return self::getWikiAggr($a_wiki_id, $a_day_from, $a_day_to, "wiki_stat_page_user", "read_events", "SUM(%s)", "page_id", $a_page_id);
    }
    
    protected static function getWikiPageInternalLinks($a_wiki_id, $a_page_id, $a_day_from, $a_day_to)
    {
        return self::getWikiAggr($a_wiki_id, $a_day_from, $a_day_to, "wiki_stat_page", "int_links", "MAX(%s)", "page_id", $a_page_id, true);
    }
    
    protected static function getWikiPageExternalLinks($a_wiki_id, $a_page_id, $a_day_from, $a_day_to)
    {
        return self::getWikiAggr($a_wiki_id, $a_day_from, $a_day_to, "wiki_stat_page", "ext_links", "MAX(%s)", "page_id", $a_page_id, true);
    }
    
    protected static function getWikiPageWords($a_wiki_id, $a_page_id, $a_day_from, $a_day_to)
    {
        return self::getWikiAggr($a_wiki_id, $a_day_from, $a_day_to, "wiki_stat_page", "num_words", "MAX(%s)", "page_id", $a_page_id, true);
    }
    
    protected static function getWikiPageCharacters($a_wiki_id, $a_page_id, $a_day_from, $a_day_to)
    {
        return self::getWikiAggr($a_wiki_id, $a_day_from, $a_day_to, "wiki_stat_page", "num_chars", "MAX(%s)", "page_id", $a_page_id, true);
    }
    
    protected static function getWikiPageFootnotes($a_wiki_id, $a_page_id, $a_day_from, $a_day_to)
    {
        return self::getWikiAggr($a_wiki_id, $a_day_from, $a_day_to, "wiki_stat_page", "footnotes", "MAX(%s)", "page_id", $a_page_id, true);
    }
    
    protected static function getWikiPageRatings($a_wiki_id, $a_page_id, $a_day_from, $a_day_to)
    {
        return self::getWikiAggr($a_wiki_id, $a_day_from, $a_day_to, "wiki_stat_page", "num_ratings", "SUM(%s)", "page_id", $a_page_id);
    }
    
    
    //
    // GUI HELPER
    //

    public static function getAvailableMonths($a_wiki_id)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $res = array();
        
        // because of read_events this db table is updated most often
        $set = $ilDB->query("SELECT DISTINCT(SUBSTR(ts_day, 1, 7)) " . $ilDB->quoteIdentifier("month") .
            " FROM wiki_stat_page_user" .
            " WHERE wiki_id = " . $ilDB->quote($a_wiki_id, "integer") .
            " AND ts_day IS NOT NULL");
        while ($row = $ilDB->fetchAssoc($set)) {
            $res[] = $row["month"];
        }
        
        return $res;
    }
            
    public static function getFigures()
    {
        return array(
            self::KEY_FIGURE_WIKI_NUM_PAGES
            ,self::KEY_FIGURE_WIKI_NEW_PAGES
            ,self::KEY_FIGURE_WIKI_NEW_PAGES_AVG
            ,self::KEY_FIGURE_WIKI_EDIT_PAGES
            ,self::KEY_FIGURE_WIKI_EDIT_PAGES_AVG
            ,self::KEY_FIGURE_WIKI_DELETED_PAGES
            ,self::KEY_FIGURE_WIKI_READ_PAGES
            ,self::KEY_FIGURE_WIKI_USER_EDIT_PAGES
            ,self::KEY_FIGURE_WIKI_USER_EDIT_PAGES_AVG
            ,self::KEY_FIGURE_WIKI_NUM_RATING
            ,self::KEY_FIGURE_WIKI_NUM_RATING_AVG
            ,self::KEY_FIGURE_WIKI_RATING_AVG
            ,self::KEY_FIGURE_WIKI_INTERNAL_LINKS
            ,self::KEY_FIGURE_WIKI_INTERNAL_LINKS_AVG
            ,self::KEY_FIGURE_WIKI_EXTERNAL_LINKS
            ,self::KEY_FIGURE_WIKI_EXTERNAL_LINKS_AVG
            ,self::KEY_FIGURE_WIKI_WORDS
            ,self::KEY_FIGURE_WIKI_WORDS_AVG
            ,self::KEY_FIGURE_WIKI_CHARS
            ,self::KEY_FIGURE_WIKI_CHARS_AVG
            ,self::KEY_FIGURE_WIKI_FOOTNOTES
            ,self::KEY_FIGURE_WIKI_FOOTNOTES_AVG
        );
    }
    
    public static function getFiguresPage()
    {
        return array(
            self::KEY_FIGURE_WIKI_PAGE_CHANGES
            ,self::KEY_FIGURE_WIKI_PAGE_CHANGES_AVG
            ,self::KEY_FIGURE_WIKI_PAGE_USER_EDIT
            ,self::KEY_FIGURE_WIKI_PAGE_READ
            ,self::KEY_FIGURE_WIKI_PAGE_INTERNAL_LINKS
            ,self::KEY_FIGURE_WIKI_PAGE_EXTERNAL_LINKS
            ,self::KEY_FIGURE_WIKI_PAGE_WORDS
            ,self::KEY_FIGURE_WIKI_PAGE_CHARS
            ,self::KEY_FIGURE_WIKI_PAGE_FOOTNOTES
            ,self::KEY_FIGURE_WIKI_PAGE_RATINGS
        );
    }
    
    public static function getFigureTitle($a_figure)
    {
        global $DIC;

        $lng = $DIC->language();
        
        $map = array(
            // wiki
            self::KEY_FIGURE_WIKI_NUM_PAGES => $lng->txt("wiki_stat_num_pages")
            ,self::KEY_FIGURE_WIKI_NEW_PAGES => $lng->txt("wiki_stat_new_pages")
            ,self::KEY_FIGURE_WIKI_NEW_PAGES_AVG => $lng->txt("wiki_stat_new_pages_avg")
            ,self::KEY_FIGURE_WIKI_EDIT_PAGES => $lng->txt("wiki_stat_edit_pages")
            ,self::KEY_FIGURE_WIKI_EDIT_PAGES_AVG => $lng->txt("wiki_stat_edit_pages_avg")
            ,self::KEY_FIGURE_WIKI_DELETED_PAGES => $lng->txt("wiki_stat_deleted_pages")
            ,self::KEY_FIGURE_WIKI_READ_PAGES => $lng->txt("wiki_stat_read_pages")
            ,self::KEY_FIGURE_WIKI_USER_EDIT_PAGES => $lng->txt("wiki_stat_user_edit_pages")
            ,self::KEY_FIGURE_WIKI_USER_EDIT_PAGES_AVG => $lng->txt("wiki_stat_user_edit_pages_avg")
            ,self::KEY_FIGURE_WIKI_NUM_RATING => $lng->txt("wiki_stat_num_rating")
            ,self::KEY_FIGURE_WIKI_NUM_RATING_AVG => $lng->txt("wiki_stat_num_rating_avg")
            ,self::KEY_FIGURE_WIKI_RATING_AVG => $lng->txt("wiki_stat_rating_avg")
            ,self::KEY_FIGURE_WIKI_INTERNAL_LINKS => $lng->txt("wiki_stat_internal_links")
            ,self::KEY_FIGURE_WIKI_INTERNAL_LINKS_AVG => $lng->txt("wiki_stat_internal_links_avg")
            ,self::KEY_FIGURE_WIKI_EXTERNAL_LINKS => $lng->txt("wiki_stat_external_links")
            ,self::KEY_FIGURE_WIKI_EXTERNAL_LINKS_AVG => $lng->txt("wiki_stat_external_links_avg")
            ,self::KEY_FIGURE_WIKI_WORDS => $lng->txt("wiki_stat_words")
            ,self::KEY_FIGURE_WIKI_WORDS_AVG => $lng->txt("wiki_stat_words_avg")
            ,self::KEY_FIGURE_WIKI_CHARS => $lng->txt("wiki_stat_chars")
            ,self::KEY_FIGURE_WIKI_CHARS_AVG => $lng->txt("wiki_stat_chars_avg")
            ,self::KEY_FIGURE_WIKI_FOOTNOTES => $lng->txt("wiki_stat_footnotes")
            ,self::KEY_FIGURE_WIKI_FOOTNOTES_AVG => $lng->txt("wiki_stat_footnotes_avg")
            // page
            ,self::KEY_FIGURE_WIKI_PAGE_CHANGES => $lng->txt("wiki_stat_page_changes")
            ,self::KEY_FIGURE_WIKI_PAGE_CHANGES_AVG => $lng->txt("wiki_stat_page_changes_avg")
            ,self::KEY_FIGURE_WIKI_PAGE_USER_EDIT => $lng->txt("wiki_stat_page_user_edit")
            ,self::KEY_FIGURE_WIKI_PAGE_READ => $lng->txt("wiki_stat_page_read")
            ,self::KEY_FIGURE_WIKI_PAGE_INTERNAL_LINKS => $lng->txt("wiki_stat_page_internal_links")
            ,self::KEY_FIGURE_WIKI_PAGE_EXTERNAL_LINKS => $lng->txt("wiki_stat_page_external_links")
            ,self::KEY_FIGURE_WIKI_PAGE_WORDS => $lng->txt("wiki_stat_page_words")
            ,self::KEY_FIGURE_WIKI_PAGE_CHARS => $lng->txt("wiki_stat_page_characters")
            ,self::KEY_FIGURE_WIKI_PAGE_FOOTNOTES => $lng->txt("wiki_stat_page_footnotes")
            ,self::KEY_FIGURE_WIKI_PAGE_RATINGS => $lng->txt("wiki_stat_page_ratings")
        );
        
        return $map[$a_figure];
    }
    
    public static function getFigureData($a_wiki_id, $a_figure, $a_from, $a_to)
    {
        switch ($a_figure) {
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
                
            case self::KEY_FIGURE_WIKI_USER_EDIT_PAGES:
                return self::getWikiUserEditPages($a_wiki_id, $a_from, $a_to);
                
            case self::KEY_FIGURE_WIKI_USER_EDIT_PAGES_AVG:
                return self::getWikiUserEditPages($a_wiki_id, $a_from, $a_to);
                
            case self::KEY_FIGURE_WIKI_NUM_RATING:
                return self::getWikiNumRating($a_wiki_id, $a_from, $a_to);
                
            case self::KEY_FIGURE_WIKI_NUM_RATING_AVG:
                return self::getWikiNumRatingAvg($a_wiki_id, $a_from, $a_to);
                
            case self::KEY_FIGURE_WIKI_RATING_AVG:
                return self::getWikiRatingAvg($a_wiki_id, $a_from, $a_to);
                
            case self::KEY_FIGURE_WIKI_INTERNAL_LINKS:
                return self::getWikiInternalLinks($a_wiki_id, $a_from, $a_to);
                
            case self::KEY_FIGURE_WIKI_INTERNAL_LINKS_AVG:
                return self::getWikiInternalLinksAvg($a_wiki_id, $a_from, $a_to);
                
            case self::KEY_FIGURE_WIKI_EXTERNAL_LINKS:
                return self::getWikiExternalLinks($a_wiki_id, $a_from, $a_to);
                
            case self::KEY_FIGURE_WIKI_EXTERNAL_LINKS_AVG:
                return self::getWikiExternalLinksAvg($a_wiki_id, $a_from, $a_to);
                
            case self::KEY_FIGURE_WIKI_WORDS:
                return self::getWikiWords($a_wiki_id, $a_from, $a_to);
                
            case self::KEY_FIGURE_WIKI_WORDS_AVG:
                return self::getWikiWordsAvg($a_wiki_id, $a_from, $a_to);
                
            case self::KEY_FIGURE_WIKI_CHARS:
                return self::getWikiCharacters($a_wiki_id, $a_from, $a_to);
                
            case self::KEY_FIGURE_WIKI_CHARS_AVG:
                return self::getWikiCharactersAvg($a_wiki_id, $a_from, $a_to);
                
            case self::KEY_FIGURE_WIKI_FOOTNOTES:
                return self::getWikiFootnotes($a_wiki_id, $a_from, $a_to);
                
            case self::KEY_FIGURE_WIKI_FOOTNOTES_AVG:
                return self::getWikiFootnotesAvg($a_wiki_id, $a_from, $a_to);
        }
    }
    
    public static function getFigureDataPage($a_wiki_id, $a_page_id, $a_figure, $a_from, $a_to)
    {
        switch ($a_figure) {
            case self::KEY_FIGURE_WIKI_PAGE_CHANGES:
                return self::getWikiPageChanges($a_wiki_id, $a_page_id, $a_from, $a_to);
                
            case self::KEY_FIGURE_WIKI_PAGE_CHANGES_AVG:
                return self::getWikiPageChangesAvg($a_wiki_id, $a_page_id, $a_from, $a_to);
                
            case self::KEY_FIGURE_WIKI_PAGE_USER_EDIT:
                return self::getWikiPageUserEdit($a_wiki_id, $a_page_id, $a_from, $a_to);
                
            case self::KEY_FIGURE_WIKI_PAGE_READ:
                return self::getWikiPageRead($a_wiki_id, $a_page_id, $a_from, $a_to);
                
            case self::KEY_FIGURE_WIKI_PAGE_INTERNAL_LINKS:
                return self::getWikiPageInternalLinks($a_wiki_id, $a_page_id, $a_from, $a_to);
                
            case self::KEY_FIGURE_WIKI_PAGE_EXTERNAL_LINKS:
                return self::getWikiPageExternalLinks($a_wiki_id, $a_page_id, $a_from, $a_to);
                
            case self::KEY_FIGURE_WIKI_PAGE_FOOTNOTES:
                return self::getWikiPageFootnotes($a_wiki_id, $a_page_id, $a_from, $a_to);
                
            case self::KEY_FIGURE_WIKI_PAGE_WORDS:
                return self::getWikiPageWords($a_wiki_id, $a_page_id, $a_from, $a_to);
                
            case self::KEY_FIGURE_WIKI_PAGE_CHARS:
                return self::getWikiPageCharacters($a_wiki_id, $a_page_id, $a_from, $a_to);
                
            case self::KEY_FIGURE_WIKI_PAGE_RATINGS:
                return self::getWikiPageRatings($a_wiki_id, $a_page_id, $a_from, $a_to);
        }
    }
    
    public static function getFigureOptions()
    {
        $res = array();
        
        foreach (self::getFigures() as $figure) {
            $res[$figure] = self::getFigureTitle($figure);
        }
        
        return $res;
    }
    
    public static function getFigureOptionsPage()
    {
        $res = array();
        
        foreach (self::getFiguresPage() as $figure) {
            $res[$figure] = self::getFigureTitle($figure);
        }
        
        return $res;
    }
}
