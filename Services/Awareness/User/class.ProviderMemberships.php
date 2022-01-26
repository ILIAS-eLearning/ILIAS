<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

namespace ILIAS\Awareness\User;

use ILIAS\DI\Container;

/**
 * All members of the same courses/groups as the user
 * @author Alexander Killing <killing@leifos.de>
 */
class ProviderMemberships implements Provider
{
    protected \ilObjUser $user;
    protected \ilLanguage $lng;
    protected \ilDBInterface $db;

    public function __construct(Container $DIC)
    {
        $this->db = $DIC->database();
        $this->lng = $DIC->language();
        $this->user = $DIC->user();
    }

    /**
     * Get provider id
     * @return string provider id
     */
    public function getProviderId() : string
    {
        return "mmbr_user_grpcrs";
    }

    /**
     * Provider title (used in awareness overlay and in administration settings)
     * @return string provider title
     */
    public function getTitle() : string
    {
        $this->lng->loadLanguageModule("mmbr");
        return $this->lng->txt("mmbr_awrn_my_groups_courses");
    }

    /**
     * Provider info (used in administration settings)
     * @return string provider info text
     */
    public function getInfo() : string
    {
        $this->lng->loadLanguageModule("crs");
        return $this->lng->txt("mmbr_awrn_my_groups_courses_info");
    }

    /**
     * Get initial set of users
     * @param ?int[] $user_ids
     * @return int[] array of user IDs
     */
    public function getInitialUserSet(?array $user_ids = null) : array
    {
        $ilDB = $this->db;

        $groups_and_courses_of_user = \ilParticipants::_getMembershipByType(
            $this->user->getId(),
            ["grp", "crs"]
        );
        //$this->log->debug("user: " . $this->getUserId() . ", courses and groups: " . implode(",", $groups_and_courses_of_user));

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
                $ub[] = (int) $rec["usr_id"];
                /*
                if ($this->log->isHandling(ilLogLevel::DEBUG)) {
                    // cross-check if user is in course
                    $ref_ids = ilObject::_getAllReferences($rec["obj_id"]);
                    $ref_id = current($ref_ids);
                    $this->log->debug("Cross-checking all members...");
                    if (!ilParticipants::_isParticipant($ref_id, $rec["usr_id"])) {
                        $this->log->debug("ERROR: obj_members has entry for user id: " . $rec["usr_id"] .
                            ", user : " . ilObject::_lookupTitle($rec["usr_id"]) . ", course ref: " . $ref_id . ", course: " .
                            ilObject::_lookupTitle($rec["obj_id"]) . ", but ilParticipants does not list this user as a member.");
                    }
                }*/
            }
        }

        //$this->log->debug("Got " . count($ub) . " distinct members.");

        return $ub;
    }

    public function isHighlighted() : bool
    {
        return false;
    }
}
