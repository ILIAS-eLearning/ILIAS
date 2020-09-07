<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Awareness/classes/class.ilAwarenessUserProvider.php");

/**
 * All members of the same courses/groups as the user
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesAwareness
 */
class ilAwarenessUserProviderMemberships extends ilAwarenessUserProvider
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
        return "mmbr_user_grpcrs";
    }

    /**
     * Provider title (used in awareness overlay and in administration settings)
     *
     * @return string provider title
     */
    public function getTitle()
    {
        $this->lng->loadLanguageModule("mmbr");
        return $this->lng->txt("mmbr_awrn_my_groups_courses");
    }

    /**
     * Provider info (used in administration settings)
     *
     * @return string provider info text
     */
    public function getInfo()
    {
        $this->lng->loadLanguageModule("crs");
        return $this->lng->txt("mmbr_awrn_my_groups_courses_info");
    }

    /**
     * Get initial set of users
     *
     * @return array array of user IDs
     */
    public function getInitialUserSet()
    {
        $ilDB = $this->db;


        include_once("./Services/Membership/classes/class.ilParticipants.php");
        $groups_and_courses_of_user = ilParticipants::_getMembershipByType($this->getUserId(), array("grp", "crs"));
        $this->log->debug("user: " . $this->getUserId() . ", courses and groups: " . implode(",", $groups_and_courses_of_user));

        $set = $ilDB->query(
            "SELECT DISTINCT usr_id, obj_id FROM obj_members " .
            " WHERE " . $ilDB->in("obj_id", $groups_and_courses_of_user, false, "integer") . ' ' .
            'AND (admin > ' . $ilDB->quote(0, 'integer') . ' ' .
            'OR tutor > ' . $ilDB->quote(0, 'integer') . ' ' .
            'OR member > ' . $ilDB->quote(0, 'integer') . ")"
        );
        $ub = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            if (!in_array($rec["usr_id"], $ub)) {
                $ub[] = $rec["usr_id"];
                if ($this->log->isHandling(ilLogLevel::DEBUG)) {
                    // cross-check if user is in course
                    include_once("./Services/Membership/classes/class.ilParticipants.php");
                    $ref_ids = ilObject::_getAllReferences($rec["obj_id"]);
                    $ref_id = current($ref_ids);
                    $this->log->debug("Cross-checking all members...");
                    if (!ilParticipants::_isParticipant($ref_id, $rec["usr_id"])) {
                        $this->log->debug("ERROR: obj_members has entry for user id: " . $rec["usr_id"] .
                            ", user : " . ilObject::_lookupTitle($rec["usr_id"]) . ", course ref: " . $ref_id . ", course: " .
                            ilObject::_lookupTitle($rec["obj_id"]) . ", but ilParticipants does not list this user as a member.");
                    }
                }
            }
        }

        $this->log->debug("Got " . count($ub) . " distinct members.");

        return $ub;
    }
}
