<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Manage data for ilMembershipCronNotifications cron job
 *
 * @author Alex Killing <killing@leifos.de>
 * @ingroup ServicesMembership
 */
class ilMembershipCronNotificationsData
{
    protected $last_run;

    protected $cron_id;

    protected $log;

    protected $objects;

    /**
     * @var array[]
     */
    protected $news = array();

    /**
     * @var int[]
     */
    protected $news_per_user;

    /**
     * news array (may include aggregated news which contains news as subitems)
     * @var array[]
     */
    protected $user_news_aggr = array();

    /**
     * @var array[]
     */
    protected $likes = array();

    /**
     * @var array
     */
    protected $comments = array();

    protected $missing_news_per_user = array();

    protected $missing_news = array();

    /**
     *
     *
     * @param
     */
    public function __construct($last_run, $cron_id)
    {
        global $DIC;

        $this->access = $DIC->access();

        $this->last_run = $last_run;
        $this->cron_id = $cron_id;
        $this->log = ilLoggerFactory::getLogger("mmbr");
        $this->load();
    }

    /**
     * Load
     */
    protected function load()
    {
        $ilAccess = $this->access;

        include_once "Services/Membership/classes/class.ilMembershipNotifications.php";

        // all group/course notifications: ref id => user ids
        $this->objects = ilMembershipNotifications::getActiveUsersforAllObjects();

        if (sizeof($this->objects)) {
            $this->log->debug("nr of objects: " . count($this->objects));

            // gather news for each user over all objects
            $this->user_news_aggr = array();

            include_once "Services/News/classes/class.ilNewsItem.php";
            foreach ($this->objects as $ref_id => $user_ids) {
                $this->log->debug("handle ref id " . $ref_id . ", users: " . count($user_ids));

                // gather news per object
                $news_item = new ilNewsItem();
                $objs = $this->getObjectsForRefId($ref_id);
                if ($news_item->checkNewsExistsForObjects($objs["obj_id"], $this->last_run)) {
                    $this->log->debug("Got news");
                    foreach ($user_ids as $user_id) {
                        // gather news for user
                        $user_news = $news_item->getNewsForRefId(
                            $ref_id,
                            false,
                            false,
                            $this->last_run,
                            false,
                            false,
                            false,
                            false,
                            $user_id
                        );
                        if ($user_news) {
                            $this->user_news_aggr[$user_id][$ref_id] = $user_news;

                            // store all single news
                            foreach ($this->user_news_aggr as $agg_news) {
                                if (is_array($agg_news["aggregation"]) && count($agg_news["aggregation"]) > 0) {
                                    foreach ($agg_news["aggregation"] as $n) {
                                        $this->news[$n["id"]] = $n;
                                        $this->news_per_user[$user_id][$ref_id][$n["id"]] = $n["id"];
                                    }
                                } else {
                                    $this->news[$agg_news["id"]] = $agg_news;
                                    $this->news_per_user[$user_id][$ref_id][$agg_news["id"]] = $agg_news["id"];
                                }
                            }

                            $this->ping();
                        }
                    }
                } else {
                    $this->log->debug("Got no news");
                }

                // gather likes per object and store them "per news item"
                // currently only news can be liked
                $ref_for_obj_id = array();
                foreach ($objs["ref_id"] as $i) {
                    $ref_for_obj_id[$i["obj_id"]][$i["ref_id"]] = $i["ref_id"];
                }
                include_once("./Services/Like/classes/class.ilLikeData.php");
                $like_data = new ilLikeData(array_keys($objs["obj_id"]));
                foreach (array_keys($objs["obj_id"]) as $obj_id) {
                    $this->log->debug("Get like data for obj_id: " . $obj_id);
                    foreach ($like_data->getExpressionEntriesForObject($obj_id, $this->last_run) as $like) {
                        reset($user_ids);
                        foreach ($user_ids as $user_id) {
                            $has_perm = false;
                            foreach ($ref_for_obj_id[$obj_id] as $ref_id) {
                                if ($has_perm || $ilAccess->checkAccessOfUser($user_id, "read", "", $ref_id)) {
                                    $has_perm = true;
                                }
                            }
                            if ($has_perm) {
                                $this->likes[$user_id][$like["news_id"]][] = $like;

                                // get news data for news that are not included above
                                $this->checkMissingNews($user_id, $ref_id, $like["news_id"]);
                                $this->ping();
                            }
                        }
                    }
                }

                // gather comments
                foreach (array_keys($objs["obj_id"]) as $obj_id) {
                    $coms = ilNote::_getAllNotesOfSingleRepObject(
                        $obj_id,
                        IL_NOTE_PUBLIC,
                        false,
                        false,
                        $this->last_run
                    );
                    foreach ($coms as $c) {
                        if ($c->getNewsId() == 0) {
                            continue;
                        }
                        reset($user_ids);
                        foreach ($user_ids as $user_id) {
                            $has_perm = false;
                            foreach ($ref_for_obj_id[$obj_id] as $ref_id) {
                                if ($has_perm || $ilAccess->checkAccessOfUser($user_id, "read", "", $ref_id)) {
                                    $has_perm = true;
                                }
                            }
                            if ($has_perm) {
                                $this->comments[$user_id][$c->getNewsId()][] = $c;

                                // get news data for news that are not included above
                                $this->checkMissingNews($user_id, $ref_id, $c->getNewsId());
                                $this->ping();
                            }
                        }
                    }
                }
            }
            $this->loadMissingNews();
        }
    }
    
    /**
     * Get missing news
     *
     * @param int $user_id
     * @param int $ref_id
     * @param int $news_id
     */
    protected function checkMissingNews($user_id, $ref_id, $news_id)
    {
        $this->log->debug("Check missing news: " . $user_id . "-" . $ref_id . "-" . $news_id);
        if (!is_array($this->news_per_user[$user_id][$ref_id]) ||
            !in_array($news_id, $this->news_per_user[$user_id][$ref_id])) {
            $this->log->debug("Add missing news: " . $news_id);
            $this->missing_news[$news_id] = $news_id;
            $this->missing_news_per_user[$user_id][$ref_id][$news_id] = $news_id;
        }
    }
    
    /**
     * Load missing news (news for new likes and/or comments)
     */
    protected function loadMissingNews()
    {
        include_once("./Services/News/classes/class.ilNewsItem.php");
        foreach (ilNewsItem::queryNewsByIds($this->missing_news) as $news) {
            $this->log->debug("Got missing news: " . $news["id"]);
            $this->news[$news["id"]] = $news;
        }
        foreach ($this->missing_news_per_user as $user_id => $r) {
            foreach ($r as $ref_id => $n) {
                foreach ($n as $news_id) {
                    $this->log->debug("Load missing news: " . $user_id . "-" . $ref_id . "-" . $news_id);
                    $this->user_news_aggr[$user_id][$ref_id][$news_id] = $this->news[$news_id];
                    $this->news_per_user[$user_id][$ref_id][$news_id] = $news_id;
                }
            }
        }
    }
    


    /**
     * Get subtree object IDs for ref id
     *
     * @param int
     * @return array
     */
    protected function getObjectsForRefId($a_ref_id)
    {
        global $DIC;

        $tree = $DIC->repositoryTree();
        $nodes = array();

        if (!$tree->isDeleted($a_ref_id)) {
            // parse repository branch of group

            $node = $tree->getNodeData($a_ref_id);
            foreach ($tree->getSubTree($node) as $child) {
                if ($child["type"] != "rolf") {
                    $nodes["obj_id"][$child["obj_id"]] = array(
                        "obj_id" => $child["obj_id"],
                        "type" => $child["type"]);
                    $nodes["ref_id"][$child["child"]] = array(
                        "ref_id" => $child["child"],
                        "obj_id" => $child["obj_id"],
                        "type" => $child["type"]);
                }
            }
        }

        return $nodes;
    }


    /**
     * Ping
     */
    protected function ping()
    {
        ilCronManager::ping($this->cron_id);
    }


    /**
     * Get aggregated news
     * @return array[]
     */
    public function getAggregatedNews()
    {
        return $this->user_news_aggr;
    }
    
    /**
     * Get likes for a news and user
     *
     * @param int $news_id
     * @param int $user_id
     * @return array
     */
    public function getLikes($news_id, $user_id)
    {
        if (is_array($this->likes[$user_id][$news_id])) {
            return $this->likes[$user_id][$news_id];
        }
        return [];
    }

    /**
     * Get comments for a news and user
     *
     * @param int $news_id
     * @param int $user_id
     * @return array[ilNote]
     */
    public function getComments($news_id, $user_id)
    {
        if (is_array($this->comments[$user_id][$news_id])) {
            return $this->comments[$user_id][$news_id];
        }
        return [];
    }
}
