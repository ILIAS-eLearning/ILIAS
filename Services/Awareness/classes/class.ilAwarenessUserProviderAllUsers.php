<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Awareness/classes/class.ilAwarenessUserProvider.php");

/**
 * Test provider, adds all users
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesAwareness
 */
class ilAwarenessUserProviderAllUsers extends ilAwarenessUserProvider
{

    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;

        parent::__construct();

        $this->db = $DIC->database();
    }

    /**
     * Get provider id
     *
     * @return string provider id
     */
    public function getProviderId()
    {
        return "user_all";
    }

    /**
     * Provider title (used in awareness overlay and in administration settings)
     *
     * @return string provider title
     */
    public function getTitle()
    {
        $this->lng->loadLanguageModule("user");
        return $this->lng->txt("user_awrn_all_users");
    }

    /**
     * Provider info (used in administration settings)
     *
     * @return string provider info text
     */
    public function getInfo()
    {
        $this->lng->loadLanguageModule("user");
        return $this->lng->txt("user_awrn_all_users_info");
    }

    /**
     * Get initial set of users
     *
     * @return array array of user IDs
     */
    public function getInitialUserSet()
    {
        $ilDB = $this->db;

        $ub = array();
        // all online users
        if ($this->getOnlineUserFilter() !== false) {
            foreach ($this->getOnlineUserFilter() as $u) {
                $ub[] = $u;
            }
        } else {	// all users
            $set = $ilDB->query("SELECT usr_id FROM usr_data ");
            while ($rec = $ilDB->fetchAssoc($set)) {
                $ub[] = $rec["usr_id"];
            }
        }
        return $ub;
    }
}
