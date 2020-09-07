<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * A class that provides a collection of users for the awareness tool
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesAwareness
 */
abstract class ilAwarenessUserProvider
{
    const MODE_INACTIVE = 0;
    const MODE_ONLINE_ONLY = 1;
    const MODE_INCL_OFFLINE = 2;

    protected $user_id;
    protected $ref_id;
    protected $lng;
    protected $db;
    protected $online_user_filter = false;

    /**
     * @var ilLogger
     */
    protected $log;

    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;

        $lng = $DIC->language();
        $ilDB = $DIC->database();

        $this->lng = $lng;
        $this->db = $ilDB;
        $this->settings = new ilSetting("awrn");
        $this->log = ilLoggerFactory::getLogger('awrn');
    }
    
    /**
     * Activate provider
     *
     * @param boolean $a_val activate provider
     */
    public function setActivationMode($a_val)
    {
        $this->settings->set("up_act_" . $this->getProviderId(), (int) $a_val);
    }

    /**
     * Get Activate provider
     *
     * @return boolean activate provider
     */
    public function getActivationMode()
    {
        return (int) $this->settings->get("up_act_" . $this->getProviderId());
    }

    /**
     * Set user id
     *
     * @param int $a_val user id
     */
    public function setUserId($a_val)
    {
        $this->user_id = $a_val;
    }
    
    /**
     * Get user id
     *
     * @return int user id
     */
    public function getUserId()
    {
        return $this->user_id;
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
     * Set online user filter
     *
     * @param array $a_val array of user IDs | false if online status is not relevant
     */
    public function setOnlineUserFilter($a_val)
    {
        $this->online_user_filter = $a_val;
    }

    /**
     * Get online user filter
     *
     * @return array array of user IDs | false if online status is not relevant
     */
    public function getOnlineUserFilter()
    {
        return $this->online_user_filter;
    }

    /**
     * This should return a unique id for the provider
     * The ID should start with the service/module id, separated by "_" and a second part
     * that is unique within the module/service, e.g. "user_all"
     *
     * @return string provider id
     */
    abstract public function getProviderId();

    /**
     * Provider title (used in awareness overlay and in administration settings)
     *
     * @return string provider title
     */
    abstract public function getTitle();

    /**
     * Provider info (used in administration settings)
     *
     * @return string provider info text
     */
    abstract public function getInfo();

    /**
     * Get initial set of users
     *
     * @return array array of user IDs
     */
    abstract public function getInitialUserSet();

    /**
     * Collect all users
     *
     * @return \ilAwarenessUserCollection
     */
    public function collectUsers() : \ilAwarenessUserCollection
    {
        $coll = ilAwarenessUserCollection::getInstance();

        foreach ($this->getInitialUserSet() as $u) {
            $this->addUserToCollection($u, $coll);
        }

        return $coll;
    }

    /**
     * Add user to collection
     *
     * @param int $a_user_id user id
     * @param ilAwarenessUserCollection $a_collection collection
     */
    protected function addUserToCollection($a_user_id, ilAwarenessUserCollection $a_collection)
    {
        $ou = $this->getOnlineUserFilter();
        if ($this->getUserId() != $a_user_id && ($ou === false || in_array($a_user_id, $ou))) {
            $a_collection->addUser($a_user_id);
        }
    }

    /**
     * Is highlighted
     *
     * @return bool return true, if user group should be highlighted (using extra highlighted number)
     */
    public function isHighlighted()
    {
        return false;
    }
}
