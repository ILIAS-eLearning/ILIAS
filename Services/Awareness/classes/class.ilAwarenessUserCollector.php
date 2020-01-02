<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Collects users from all providers
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesAwareness
 */
class ilAwarenessUserCollector
{
    protected static $instances = array();
    protected static $online_users = false;
    protected static $online_user_ids = array();

    /**
     * @var ilAwarenessUserCollection
     */
    protected $collection;
    protected $collections;
    protected $user_id;
    protected $ref_id;

    /**
     * Constructor
     *
     * @param int $a_user_id user id
     */
    protected function __construct($a_user_id)
    {
        $this->user_id = $a_user_id;
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
     * Get instance (for a user)
     *
     * @param int $a_user_id user id
     * @return ilAwarenessAct actor class
     */
    public static function getInstance($a_user_id)
    {
        if (!isset(self::$instances[$a_user_id])) {
            self::$instances[$a_user_id] = new ilAwarenessUserCollector($a_user_id);
        }

        return self::$instances[$a_user_id];
    }

    /**
     * Get online users
     *
     * @param
     * @return
     */
    public static function getOnlineUsers()
    {
        if (self::$online_users === false) {
            self::$online_user_ids = array();
            self::$online_users = array();
            include_once("./Services/User/classes/class.ilObjUser.php");
            foreach (ilObjUser::_getUsersOnline() as $u) {
                // ask context $u["context"] if it supports pushMessages
                if ($u["context"] &&
                    ilContext::directCall($u["context"], "supportsPushMessages")) {
                    self::$online_users[$u["user_id"]] = $u;
                    self::$online_user_ids[] = $u["user_id"];
                }
            }
        }
        return self::$online_users;
    }


    /**
     * Collect users
     *
     * @return ilAwarenessUserCollection user collection
     */
    public function collectUsers($a_online_only = false)
    {
        global $rbacreview;

        $this->collections = array();

        $awrn_logger = ilLoggerFactory::getLogger('awrn');

        $awrn_logger->debug("Start, Online Only: " . $a_online_only . ", Current User: " . $this->user_id);

        self::getOnlineUsers();
        include_once("./Services/Awareness/classes/class.ilAwarenessUserProviderFactory.php");
        $all_users = array();
        foreach (ilAwarenessUserProviderFactory::getAllProviders() as $prov) {
            $awrn_logger->debug("Provider: " . $prov->getProviderId() . ", Activation Mode: " . $prov->getActivationMode() . ", Current User: " . $this->user_id);

            // overall collection of users
            include_once("./Services/Awareness/classes/class.ilAwarenessUserCollection.php");
            $collection = ilAwarenessUserCollection::getInstance();

            if ($prov->getActivationMode() != ilAwarenessUserProvider::MODE_INACTIVE) {
                $prov->setUserId($this->user_id);
                $prov->setRefId($this->ref_id);
                $prov->setOnlineUserFilter(false);
                if ($prov->getActivationMode() == ilAwarenessUserProvider::MODE_ONLINE_ONLY || $a_online_only) {
                    $awrn_logger->debug("Provider: " . $prov->getProviderId() . ", Online Filter Users: " . count(self::$online_user_ids) . ", Current User: " . $this->user_id);
                    $prov->setOnlineUserFilter(self::$online_user_ids);
                }

                $coll = $prov->collectUsers();
                $awrn_logger->debug("Provider: " . $prov->getProviderId() . ", Collected Users: " . count($coll) . ", Current User: " . $this->user_id);

                foreach ($coll->getUsers() as $user_id) {
                    // filter out the anonymous user
                    if ($user_id == ANONYMOUS_USER_ID) {
                        continue;
                    }

                    $awrn_logger->debug("Current User: " . $this->user_id . ", " .
                        "Provider: " . $prov->getProviderId() . ", Collected User: " . $user_id);

                    // cross check online, filter out offline users (if necessary)
                    if ((!$a_online_only && $prov->getActivationMode() == ilAwarenessUserProvider::MODE_INCL_OFFLINE)
                        || in_array($user_id, self::$online_user_ids)) {
                        $collection->addUser($user_id);
                        if (!in_array($user_id, $all_users)) {
                            $all_users[] = $user_id;
                        }
                    }
                }
            }
            $this->collections[] = array(
                "uc_title" => $prov->getTitle(),
                "highlighted" => $prov->isHighlighted(),
                "collection" => $collection
            );
        }

        $remove_users = array();

        // remove all users that hide their online status
        foreach (ilObjUser::getUserSubsetByPreferenceValue($all_users, "hide_own_online_status", "y") as $u) {
            $remove_users[] = $u;
        }

        // remove all users that have not accepted the terms of service yet
        require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceHelper.php';
        if (ilTermsOfServiceHelper::isEnabled()) {
            foreach (ilObjUser::getUsersAgreed(false, $all_users) as $u) {
                if ($u != SYSTEM_USER_ID && !$rbacreview->isAssigned($u, SYSTEM_ROLE_ID)) {
                    //if ($u != SYSTEM_USER_ID)
                    $remove_users[] = $u;
                }
            }
        }

        $this->removeUsersFromCollections($remove_users);

        return $this->collections;
    }

    /**
     * Remove users from collection
     *
     * @param array $a_remove_users array of user IDs
     */
    protected function removeUsersFromCollections($a_remove_users)
    {
        foreach ($this->collections as $c) {
            reset($a_remove_users);
            foreach ($a_remove_users as $u) {
                $c["collection"]->removeUser($u);
            }
        }
    }
}
