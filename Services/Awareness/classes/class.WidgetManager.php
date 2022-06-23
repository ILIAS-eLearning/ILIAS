<?php

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

namespace ILIAS\Awareness;

use ILIAS\Notifications\Repository\ilNotificationOSDRepository;
use ILIAS\Notifications\Model\ilNotificationConfig;
use ILIAS\Notifications\Model\ilNotificationLink;
use ILIAS\Notifications\ilNotificationOSDHandler;
use ILIAS\Notifications\Model\ilNotificationParameter;
use ilArrayUtil;

/**
 * High level business class, interface to front ends
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class WidgetManager
{
    protected InternalDataService $data_service;
    protected \ilSetting $settings;
    protected AwarenessSessionRepository $session_repo;
    protected \ilLanguage $lng;

    protected int $user_id;
    protected int $ref_id = 0;
    protected User\Collector $user_collector;
    protected \ilUserActionCollector $action_collector;
    protected array $user_collections;
    protected ?array $data = null;
    protected ?array $online_user_data = null;
    protected static array $instances = array();

    public function __construct(
        int $a_user_id,
        int $ref_id,
        InternalDataService $data_service,
        InternalRepoService $repo_service,
        InternalDomainService $domain_service
    ) {
        $this->lng = $domain_service->lng();
        $this->user_id = $a_user_id;
        $this->ref_id = $ref_id;
        $this->session_repo = $repo_service->awarenessSession();
        $this->settings = $domain_service->awarenessSettings();
        $this->data_service = $data_service;
        $this->user_collector = $domain_service->userCollector($a_user_id, $ref_id);
        $this->action_collector = \ilUserActionCollector::getInstance($a_user_id, new \ilAwarenessUserActionContext());
    }

    /**
     * Maximum for online user data
     */
    public function getMaxOnlineUserCnt() : int
    {
        return 20;
    }

    /**
     * Send OSD notification on new users
     */
    public function notifyOnNewOnlineContacts() : void
    {
        $lng = $this->lng;
        $awrn_set = $this->settings;
        if (!$awrn_set->get("use_osd", true)) {
            return;
        }
        $ts = $this->session_repo->getOnlineUsersTS();

        $d = $this->getOnlineUserData($ts);
        $new_online_users = array();
        $no_ids = array();
        foreach ($d as $u) {
            $uname = "[" . $u->login . "]";
            if ($u->public_profile) {
                $uname = $u->lastname . ", " . $u->firstname . " " . $uname;
            }
            if (!in_array($u->id, $no_ids)) {
                $new_online_users[] = new ilNotificationLink(
                    new ilNotificationParameter($uname),
                    './goto.php?target=usr_' . $u->id
                );
                $no_ids[] = $u->id;
            }
        }
        if (count($new_online_users) == 0) {
            return;
        }

        $notification = new ilNotificationConfig('who_is_online');
        $notification->setTitleVar('awareness_now_online', [], 'awrn');
        $notification->setShortDescriptionVar('');
        $notification->setLongDescriptionVar('');
        $notification->setLinks($new_online_users);
        $notification->setIconPath('templates/default/images/icon_usr.svg');
        $notification->setValidForSeconds(ilNotificationConfig::TTL_SHORT);
        $notification->setVisibleForSeconds(ilNotificationConfig::DEFAULT_TTS);

        $this->session_repo->setOnlineUsersTS(date("Y-m-d H:i:s", time()));

        $notification->notifyByUsers(array($this->user_id));
    }

    public function isWidgetVisible() : bool
    {
        $awrn_set = $this->settings;
        if (!$awrn_set->get("awrn_enabled", "0") ||
            ANONYMOUS_USER_ID == $this->user_id ||
            $this->user_id == 0) {
            return false;
        }
        return true;
    }
    
    public function processMetaBar() : Counter
    {
        $cache_period = (int) $this->settings->get("caching_period");
        $last_update = $this->session_repo->getLastUpdate();
        $now = time();

        if ($last_update == "" || ($now - $last_update) >= $cache_period) {
            $counter = $this->getUserCounter();
            $hcnt = $counter->getHighlightCount();
            $cnt = $counter->getCount();
            $this->notifyOnNewOnlineContacts();
            $this->session_repo->setLastUpdate($now);
            $this->session_repo->setCount($cnt);
            $this->session_repo->setHighlightCount($hcnt);
        } else {
            $cnt = $this->session_repo->getCount();
            $hcnt = $this->session_repo->getHighlightCount();
        }
        return $this->data_service->counter($cnt, $hcnt);
    }

    /**
     * Get user collections
     * @param bool $a_online_only true, if only online users should be collected
     * @return array array of collections
     */
    public function getUserCollections(bool $a_online_only = false) : array
    {
        if (!isset($this->user_collections[(int) $a_online_only])) {
            $this->user_collections[(int) $a_online_only] = $this->user_collector->collectUsers($a_online_only);
        }
        return $this->user_collections[(int) $a_online_only];
    }

    public function getUserCounter() : Counter
    {
        $all_user_ids = array();
        $hall_user_ids = array();

        $user_collections = $this->getUserCollections();

        foreach ($user_collections as $uc) {
            $user_collection = $uc["collection"];
            $user_ids = $user_collection->getUsers();

            foreach ($user_ids as $uid) {
                if (!in_array($uid, $all_user_ids)) {
                    if ($uc["highlighted"]) {
                        $hall_user_ids[] = $uid;
                    } else {
                        $all_user_ids[] = $uid;
                    }
                }
            }
        }

        return $this->data_service->counter(
            count($all_user_ids),
            count($hall_user_ids)
        );
    }

    /**
     * Get online user data
     * @param string $a_ts timestamp
     * @return array array of data objects
     */
    public function getOnlineUserData(string $a_ts = "") : array
    {
        $online_user_data = array();
        $online_users = $this->user_collector->getOnlineUsers();
        $user_collections = $this->getUserCollections(true);			// get user collections with online users only
        $all_online_user_ids = array();

        foreach ($user_collections as $uc) {
            $user_collection = $uc["collection"];
            $user_ids = $user_collection->getUsers();
            foreach ($user_ids as $u) {
                if (!in_array($u, $all_online_user_ids)) {
                    // check timestamp and limit the max number of user data records received
                    if (($a_ts == "" || $online_users[$u]["last_login"] > $a_ts)
                        && count($all_online_user_ids) < $this->getMaxOnlineUserCnt()) {
                        $all_online_user_ids[] = $u;
                    }
                }
            }
        }

        $names = \ilUserUtil::getNamePresentation(
            $all_online_user_ids,
            true,
            false,
            "",
            false,
            false,
            true,
            true
        );

        // sort and add online information
        foreach ($names as $k => $n) {
            $names[$k]["online"] = true;
            $names[$k]["last_login"] = $online_users[$n["id"]]["last_login"];
            $sort_str = "";
            if ($n["public_profile"]) {
                $sort_str .= $n["lastname"] . " " . $n["firstname"];
            } else {
                $sort_str .= $n["login"];
            }
            $names[$k]["sort_str"] = $sort_str;
        }

        $names = ilArrayUtil::sortArray($names, "sort_str", "asc", false, true);

        foreach ($names as $n) {
            $obj = new \stdClass();
            $obj->lastname = $n["lastname"];
            $obj->firstname = $n["firstname"];
            $obj->login = $n["login"];
            $obj->id = $n["id"];
            $obj->public_profile = $n["public_profile"];
            $obj->online = $n["online"];
            $obj->last_login = $n["last_login"];

            $online_user_data[] = $obj;
        }

        return $online_user_data;
    }

    /**
     * Get data
     * @param string $filter
     * @return array array of data objects
     * @throws \ilWACException
     */
    public function getListData(string $filter = "") : array
    {
        if ($this->user_id == ANONYMOUS_USER_ID) {
            return [
                "data" => [],
                "cnt" => "0:0"
            ];
        }
        $awrn_set = $this->settings;
        $max = $awrn_set->get("max_nr_entries");

        $all_user_ids = array();
        $hall_user_ids = array();

        if ($this->data == null) {
            $online_users = $this->user_collector->getOnlineUsers();

            $user_collections = $this->getUserCollections();

            $this->data = array();

            foreach ($user_collections as $uc) {

                // limit part 1
                if (count($this->data) >= $max) {
                    continue;
                }

                $user_collection = $uc["collection"];
                $user_ids = $user_collection->getUsers();

                foreach ($user_ids as $uid) {
                    if (!in_array($uid, $all_user_ids)) {
                        if ($uc["highlighted"]) {
                            $hall_user_ids[] = $uid;
                        } else {
                            $all_user_ids[] = $uid;
                        }
                    }
                }

                $names = \ilUserUtil::getNamePresentation(
                    $user_ids,
                    true,
                    false,
                    "",
                    false,
                    false,
                    true,
                    true
                );

                // sort and add online information
                foreach ($names as $k => $n) {
                    if (isset($online_users[$n["id"]])) {
                        $names[$k]["online"] = true;
                        $names[$k]["last_login"] = $online_users[$n["id"]]["last_login"];
                        $sort_str = "1";
                    } else {
                        $names[$k]["online"] = false;
                        $names[$k]["last_login"] = "";
                        $sort_str = "2";
                    }
                    if ($n["public_profile"]) {
                        $sort_str .= $n["lastname"] . " " . $n["firstname"];
                    } else {
                        $sort_str .= $n["login"];
                    }
                    $names[$k]["sort_str"] = $sort_str;
                }

                $names = ilArrayUtil::sortArray($names, "sort_str", "asc", false, true);

                foreach ($names as $n) {
                    // limit part 2
                    if (count($this->data) >= $max) {
                        continue;
                    }

                    // filter
                    $filter = trim($filter);
                    if ($filter != "" &&
                        !is_int(stripos($n["login"], $filter)) &&
                        (
                            !$n["public_profile"] || (
                                !is_int(stripos($n["firstname"], $filter)) &&
                                !is_int(stripos($n["lastname"], $filter))
                            )
                        )
                    ) {
                        continue;
                    }

                    $obj = new \stdClass();
                    $obj->lastname = $n["lastname"];
                    $obj->firstname = $n["firstname"];
                    $obj->login = $n["login"];
                    $obj->id = $n["id"];
                    $obj->collector = $uc["uc_title"];
                    $obj->highlighted = $uc["highlighted"];

                    //$obj->img = $n["img"];
                    $obj->img = \ilObjUser::_getPersonalPicturePath($n["id"], "xsmall");
                    $obj->public_profile = $n["public_profile"];

                    $obj->online = $n["online"];
                    $obj->last_login = $n["last_login"];

                    // get actions
                    $action_collection = $this->action_collector->getActionsForTargetUser($n["id"]);
                    $obj->actions = array();
                    foreach ($action_collection->getActions() as $action) {
                        $f = new \stdClass();
                        $f->text = $action->getText();
                        $f->href = $action->getHref();
                        $f->data = $action->getData();
                        $obj->actions[] = $f;
                    }

                    $this->data[] = $obj;
                }
            }
        }

        // update counter
        $this->updateCounter(
            count($all_user_ids),
            count($hall_user_ids)
        );

        return array("data" => $this->data, "cnt" => count($all_user_ids) . ":" . count($hall_user_ids));
    }

    protected function updateCounter(
        int $cnt,
        int $hcnt
    ) : void {
        // update counter
        $now = time();
        $this->session_repo->setLastUpdate($now);
        $this->session_repo->setCount($cnt);
        $this->session_repo->setHighlightCount($hcnt);
    }
}
