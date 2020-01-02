<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 *
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesAwareness
 */
class ilAwarenessData
{
    protected $user_id;
    protected $ref_id = 0;
    protected $user_collector;
    protected $action_collector;
    protected $user_collection;
    protected $data = null;
    protected $online_user_data = null;
    protected static $instances = array();
    protected $filter = "";

    /**
     * Constructor
     *
     * @param
     * @return
     */
    protected function __construct($a_user_id)
    {
        $this->user_id = $a_user_id;

        include_once("./Services/Awareness/classes/class.ilAwarenessUserCollector.php");
        $this->user_collector = ilAwarenessUserCollector::getInstance($a_user_id);
        include_once("./Services/User/Actions/classes/class.ilUserActionCollector.php");
        include_once("./Services/Awareness/classes/class.ilAwarenessUserActionContext.php");
        $this->action_collector = ilUserActionCollector::getInstance($a_user_id, new ilAwarenessUserActionContext());
    }

    /**
     * Set ref id
     *
     * @param int $a_val ref id
     */
    public function setRefId($a_val)
    {
        $this->ref_id = $a_val;
    }
    
    /**
     * Get ref id
     *
     * @return int ref id
     */
    public function getRefId()
    {
        return $this->ref_id;
    }
    
    /**
     * Set filter
     *
     * @param string $a_val filter string
     */
    public function setFilter($a_val)
    {
        $this->filter = $a_val;
    }
    
    /**
     * Get filter
     *
     * @return string filter string
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * Maximum for online user data
     */
    public function getMaxOnlineUserCnt()
    {
        return 20;
    }

    
    /**
     * Get instance (for a user)
     *
     * @param int $a_user_id user id
     * @return ilAwarenessData actor class
     */
    public static function getInstance($a_user_id)
    {
        if (!isset(self::$instances[$a_user_id])) {
            self::$instances[$a_user_id] = new ilAwarenessData($a_user_id);
        }

        return self::$instances[$a_user_id];
    }



    /**
     * Get user collections
     *
     * @param bool $a_online_only true, if only online users should be collected
     * @return array array of collections
     */
    public function getUserCollections($a_online_only = false)
    {
        if (!isset($this->user_collections[(int) $a_online_only])) {
            $this->user_collector->setRefId($this->getRefId());
            $this->user_collections[(int) $a_online_only] = $this->user_collector->collectUsers($a_online_only);
        }

        return $this->user_collections[(int) $a_online_only];
    }

    /**
     * Get user counter
     */
    public function getUserCounter()
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

        return count($all_user_ids) . ":" . count($hall_user_ids);
    }

    /**
     * Get online user data
     *
     * @param string $a_ts timestamp
     * @return array array of data objects
     */
    public function getOnlineUserData($a_ts = "")
    {
        $online_user_data = array();
        $online_users = ilAwarenessUserCollector::getOnlineUsers();
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

        include_once("./Services/User/classes/class.ilUserUtil.php");
        $names = ilUserUtil::getNamePresentation(
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
                $sort_str.= $n["lastname"] . " " . $n["firstname"];
            } else {
                $sort_str.= $n["login"];
            }
            $names[$k]["sort_str"] = $sort_str;
        }

        $names = ilUtil::sortArray($names, "sort_str", "asc", false, true);

        foreach ($names as $n) {
            $obj = new stdClass;
            $obj->lastname = $n["lastname"];
            $obj->firstname = $n["firstname"];
            $obj->login = $n["login"];
            $obj->id = $n["id"];
            $obj->public_profile = $n["public_profile"];
            $obj->online = $n["online"];
            $obj->last_login = $n["last_login"];
            ;

            $online_user_data[] = $obj;
        }

        return $online_user_data;
    }

    /**
     * Get data
     *
     * @return array array of data objects
     */
    public function getData()
    {
        $awrn_set = new ilSetting("awrn");
        $max = $awrn_set->get("max_nr_entries");

        $all_user_ids = array();
        $hall_user_ids = array();

        if ($this->data == null) {
            $online_users = ilAwarenessUserCollector::getOnlineUsers();

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

                include_once("./Services/User/classes/class.ilUserUtil.php");
                $names = ilUserUtil::getNamePresentation(
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
                        $sort_str.= $n["lastname"] . " " . $n["firstname"];
                    } else {
                        $sort_str.= $n["login"];
                    }
                    $names[$k]["sort_str"] = $sort_str;
                }

                $names = ilUtil::sortArray($names, "sort_str", "asc", false, true);

                foreach ($names as $n) {
                    // limit part 2
                    if (count($this->data) >= $max) {
                        continue;
                    }

                    // filter
                    $filter = trim($this->getFilter());
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

                    $obj = new stdClass;
                    $obj->lastname = $n["lastname"];
                    $obj->firstname = $n["firstname"];
                    $obj->login = $n["login"];
                    $obj->id = $n["id"];
                    $obj->collector = $uc["uc_title"];
                    $obj->highlighted = $uc["highlighted"];

                    //$obj->img = $n["img"];
                    $obj->img = ilObjUser::_getPersonalPicturePath($n["id"], "xsmall");
                    $obj->public_profile = $n["public_profile"];

                    $obj->online = $n["online"];
                    $obj->last_login = $n["last_login"];
                    ;

                    // get actions
                    $action_collection = $this->action_collector->getActionsForTargetUser($n["id"]);
                    $obj->actions = array();
                    foreach ($action_collection->getActions() as $action) {
                        $f = new stdClass;
                        $f->text = $action->getText();
                        $f->href = $action->getHref();
                        $f->data = $action->getData();
                        $obj->actions[] = $f;
                    }

                    $this->data[] = $obj;
                }
            }
        }

        return array("data" => $this->data, "cnt" => count($all_user_ids) . ":" . count($hall_user_ids));
    }
}
