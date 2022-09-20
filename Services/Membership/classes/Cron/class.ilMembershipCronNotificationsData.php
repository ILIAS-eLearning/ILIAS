<?php

declare(strict_types=1);



/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

use ILIAS\Notes\Service;

/**
 * Manage data for ilMembershipCronNotifications cron job
 * @author  Alex Killing <killing@leifos.de>
 * @ingroup ServicesMembership
 */
class ilMembershipCronNotificationsData
{
    protected Service $notes;
    /**
     * @todo convert to DateTime
     */
    protected int $last_run_unix;
    protected string $last_run_date;
    protected string $cron_id;
    protected ilLogger $log;
    protected array $objects;
    protected array $news = array();

    /**
     * @var int[]
     */
    protected array $news_per_user;

    /**
     * news array (may include aggregated news which contains news as subitems)
     */
    protected array $user_news_aggr = array();
    protected array $likes = array();
    protected array $comments = array();
    protected array $missing_news_per_user = array();
    protected array $missing_news = array();

    protected ilAccessHandler $access;

    public function __construct(int $last_run, string $cron_id)
    {
        global $DIC;

        $this->access = $DIC->access();

        $this->last_run_unix = $last_run;
        $this->last_run_date = date('Y-m-d H:i:s', $last_run);
        $this->cron_id = $cron_id;
        $this->log = ilLoggerFactory::getLogger("mmbr");
        $this->load();
        $this->notes = $DIC->notes();
    }

    /**
     * Load
     */
    protected function load(): void
    {
        $ilAccess = $this->access;

        // all group/course notifications: ref id => user ids
        $this->objects = ilMembershipNotifications::getActiveUsersforAllObjects();

        if (count($this->objects)) {
            $this->log->debug("nr of objects: " . count($this->objects));

            // gather news for each user over all objects
            $this->user_news_aggr = array();

            foreach ($this->objects as $ref_id => $user_ids) {
                $this->log->debug("handle ref id " . $ref_id . ", users: " . count($user_ids));

                // gather news per object
                $news_item = new ilNewsItem();
                $objs = $this->getObjectsForRefId($ref_id);
                if (
                    isset($objs["obj_id"]) &&
                    is_array($objs["obj_id"]) &&
                    $news_item->checkNewsExistsForObjects($objs["obj_id"], $this->last_run_unix)
                ) {
                    $this->log->debug("Got news");
                    foreach ($user_ids as $user_id) {
                        // gather news for user
                        $user_news = $news_item->getNewsForRefId(
                            $ref_id,
                            false,
                            false,
                            $this->last_run_unix,
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
                                if (isset($agg_news["aggregation"]) && is_array($agg_news["aggregation"]) && $agg_news["aggregation"] !== []) {
                                    foreach ($agg_news["aggregation"] as $n) {
                                        $this->news[$n["id"]] = $n;
                                        $this->news_per_user[$user_id][$ref_id][$n["id"]] = $n["id"];
                                    }
                                } elseif (is_array($agg_news)) {
                                    if (isset($agg_news["id"])) {
                                        $this->news[$agg_news["id"]] = $agg_news;
                                        $this->news_per_user[$user_id][$ref_id][$agg_news["id"]] = $agg_news["id"];
                                    } else {
                                        foreach ($agg_news as $agg_news_items) {
                                            foreach ($agg_news_items as $agg_news_item) {
                                                if (isset($agg_news_item["id"])) {
                                                    $this->news[$agg_news_item["id"]] = $agg_news_item;
                                                    $this->news_per_user[$user_id][$ref_id][$agg_news_item["id"]] = $agg_news_item["id"];
                                                }
                                            }
                                        }
                                    }
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
                $like_data = new ilLikeData(array_keys($objs["obj_id"]));
                foreach (array_keys($objs["obj_id"]) as $obj_id) {
                    $this->log->debug("Get like data for obj_id: " . $obj_id);
                    foreach ($like_data->getExpressionEntriesForObject($obj_id, $this->last_run_unix) as $like) {
                        reset($user_ids);
                        foreach ($user_ids as $user_id) {
                            $has_perm = false;
                            foreach ($ref_for_obj_id[$obj_id] as $perm_ref_id) {
                                if ($ilAccess->checkAccessOfUser($user_id, "read", "", $perm_ref_id)) {
                                    $has_perm = true;
                                    break;
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
                    $coms = $this->notes
                        ->domain()
                        ->getAllCommentsForObjId(
                            $obj_id,
                            $this->last_run_date
                        );
                    foreach ($coms as $c) {
                        $comment_context = $c->getContext();
                        if ($comment_context->getNewsId() === 0) {
                            continue;
                        }
                        reset($user_ids);
                        foreach ($user_ids as $user_id) {
                            $has_perm = false;
                            foreach ($ref_for_obj_id[$obj_id] as $perm_ref_id) {
                                if ($ilAccess->checkAccessOfUser($user_id, "read", "", $perm_ref_id)) {
                                    $has_perm = true;
                                    break;
                                }
                            }
                            if ($has_perm) {
                                $this->comments[$user_id][$comment_context->getNewsId()][] = $c;

                                // get news data for news that are not included above
                                $this->checkMissingNews($user_id, $ref_id, $comment_context->getNewsId());
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
     * Get missing news*
     */
    protected function checkMissingNews(int $user_id, int $ref_id, int $news_id): void
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
    protected function loadMissingNews(): void
    {
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
     */
    protected function getObjectsForRefId(int $a_ref_id): array
    {
        global $DIC;

        $tree = $DIC->repositoryTree();
        $nodes = array();

        if (!$tree->isDeleted($a_ref_id)) {
            // parse repository branch of group

            $node = $tree->getNodeData($a_ref_id);
            foreach ($tree->getSubTree($node) as $child) {
                if ($child["type"] !== "rolf") {
                    $nodes["obj_id"][$child["obj_id"]] = array(
                        "obj_id" => $child["obj_id"],
                        "type" => $child["type"]
                    );
                    $nodes["ref_id"][$child["child"]] = array(
                        "ref_id" => $child["child"],
                        "obj_id" => $child["obj_id"],
                        "type" => $child["type"]
                    );
                }
            }
        }

        return $nodes;
    }

    /**
     * Ping
     */
    protected function ping(): void
    {
        global $DIC;

        $DIC->cron()->manager()->ping($this->cron_id);
    }

    /**
     * Get aggregated news
     */
    public function getAggregatedNews(): array
    {
        return $this->user_news_aggr;
    }

    /**
     * Get likes for a news and user
     */
    public function getLikes(int $news_id, int $user_id): array
    {
        if (is_array($this->likes[$user_id][$news_id])) {
            return $this->likes[$user_id][$news_id];
        }
        return [];
    }

    /**
     * Get comments for a news and user
     **/
    public function getComments(int $news_id, int $user_id): array
    {
        if (is_array($this->comments[$user_id][$news_id])) {
            return $this->comments[$user_id][$news_id];
        }
        return [];
    }
}
