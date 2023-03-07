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

/**
 * Exercise assignment team
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @author Alexander Killing <killing@leifos.de>
 */
class ilExAssignmentTeam
{
    public const TEAM_LOG_CREATE_TEAM = 1;
    public const TEAM_LOG_ADD_MEMBER = 2;
    public const TEAM_LOG_REMOVE_MEMBER = 3;
    public const TEAM_LOG_ADD_FILE = 4;
    public const TEAM_LOG_REMOVE_FILE = 5;

    protected ilDBInterface $db;
    protected ilObjUser $user;
    protected ?int $id = null;
    protected int $assignment_id;
    protected array $members = array();

    public function __construct(?int $a_id = null)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->user = $DIC->user();
        if ($a_id) {
            $this->read($a_id);
        }
    }

    public static function getInstanceByUserId(
        int $a_assignment_id,
        int $a_user_id,
        bool $a_create_on_demand = false
    ): self {
        $id = self::getTeamId($a_assignment_id, $a_user_id, $a_create_on_demand);
        return new self($id);
    }

    public static function getInstancesFromMap(int $a_assignment_id): array
    {
        $teams = array();
        foreach (self::getAssignmentTeamMap($a_assignment_id) as $user_id => $team_id) {
            $teams[$team_id][] = $user_id;
        }

        $res = array();
        foreach ($teams as $team_id => $members) {
            $team = new self();
            $team->id = $team_id;
            $team->assignment_id = $a_assignment_id;
            $team->members = $members;
            $res[$team_id] = $team;
        }

        return $res;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    private function setId(?int $a_id): void
    {
        $this->id = $a_id;
    }

    protected function read(int $a_id): void
    {
        $ilDB = $this->db;

        // #18094
        $this->members = array();

        $sql = "SELECT * FROM il_exc_team" .
            " WHERE id = " . $ilDB->quote($a_id, "integer");
        $set = $ilDB->query($sql);
        if ($ilDB->numRows($set)) {
            $this->setId($a_id);

            while ($row = $ilDB->fetchAssoc($set)) {
                $this->assignment_id = $row["ass_id"];
                $this->members[] = $row["user_id"];
            }
        }
    }

    // Get team id for member id
    public static function getTeamId(
        int $a_assignment_id,
        int $a_user_id,
        bool $a_create_on_demand = false
    ): ?int {
        global $DIC;

        $ilDB = $DIC->database();

        $sql = "SELECT id FROM il_exc_team" .
            " WHERE ass_id = " . $ilDB->quote($a_assignment_id, "integer") .
            " AND user_id = " . $ilDB->quote($a_user_id, "integer");
        $set = $ilDB->query($sql);
        $id = null;
        if ($row = $ilDB->fetchAssoc($set)) {
            $id = $row["id"];
        }

        if (!$id && $a_create_on_demand) {
            $id = $ilDB->nextId("il_exc_team");

            // get starting timestamp (relative deadlines) from individual deadline
            $idl = ilExcIndividualDeadline::getInstance($a_assignment_id, $a_user_id);

            $fields = array("id" => array("integer", $id),
                "ass_id" => array("integer", $a_assignment_id),
                "user_id" => array("integer", $a_user_id));
            $ilDB->insert("il_exc_team", $fields);

            // set starting timestamp for created team
            if ($idl->getStartingTimestamp() > 0) {
                $idl_team = ilExcIndividualDeadline::getInstance($a_assignment_id, $id, true);
                $idl_team->setStartingTimestamp($idl->getStartingTimestamp());
                $idl_team->save();
            }

            self::writeTeamLog($id, self::TEAM_LOG_CREATE_TEAM);
            self::writeTeamLog(
                $id,
                self::TEAM_LOG_ADD_MEMBER,
                ilObjUser::_lookupFullname($a_user_id)
            );
        }

        return $id;
    }

    public function createTeam(
        int $a_assignment_id,
        int $a_user_id
    ): int {
        $ilDB = $this->db;
        $id = $ilDB->nextId("il_exc_team");
        $fields = array("id" => array("integer", $id),
            "ass_id" => array("integer", $a_assignment_id),
            "user_id" => array("integer", $a_user_id));
        $ilDB->insert("il_exc_team", $fields);
        self::writeTeamLog($id, self::TEAM_LOG_CREATE_TEAM);
        self::writeTeamLog(
            $id,
            self::TEAM_LOG_ADD_MEMBER,
            ilObjUser::_lookupFullname($a_user_id)
        );
        return $id;
    }

    // Get members of assignment team
    public function getMembers(): array
    {
        return $this->members;
    }

    /**
     * Get members for all teams of assignment
     * @return int[] user ids
     */
    public function getMembersOfAllTeams(): array
    {
        $ilDB = $this->db;

        $ids = array();

        $sql = "SELECT user_id" .
            " FROM il_exc_team" .
            " WHERE ass_id = " . $ilDB->quote($this->assignment_id, "integer");
        $set = $ilDB->query($sql);
        while ($row = $ilDB->fetchAssoc($set)) {
            $ids[] = $row["user_id"];
        }

        return $ids;
    }

    // Add new member to team

    /**
     * @throws ilExcUnknownAssignmentTypeException
     */
    public function addTeamMember(
        int $a_user_id,
        ?int $a_exc_ref_id = null
    ): bool {
        $ilDB = $this->db;

        if (!$this->id) {
            return false;
        }

        // must not be in any team already
        if (!in_array($a_user_id, $this->getMembersOfAllTeams())) {
            $fields = array("id" => array("integer", $this->id),
                "ass_id" => array("integer", $this->assignment_id),
                "user_id" => array("integer", $a_user_id));
            $ilDB->insert("il_exc_team", $fields);

            if ($a_exc_ref_id) {
                $this->sendNotification($a_exc_ref_id, $a_user_id, "add");
            }

            $this->writeLog(
                self::TEAM_LOG_ADD_MEMBER,
                ilObjUser::_lookupFullname($a_user_id)
            );

            $this->read($this->id);

            return true;
        }

        return false;
    }

    /**
     * @throws ilExcUnknownAssignmentTypeException
     */
    public function removeTeamMember(
        int $a_user_id,
        ?int $a_exc_ref_id = null
    ): void {
        $ilDB = $this->db;

        if (!$this->id) {
            return;
        }

        $sql = "DELETE FROM il_exc_team" .
            " WHERE ass_id = " . $ilDB->quote($this->assignment_id, "integer") .
            " AND id = " . $ilDB->quote($this->id, "integer") .
            " AND user_id = " . $ilDB->quote($a_user_id, "integer");
        $ilDB->manipulate($sql);

        if ($a_exc_ref_id) {
            $this->sendNotification($a_exc_ref_id, $a_user_id, "rmv");
        }

        $this->writeLog(
            self::TEAM_LOG_REMOVE_MEMBER,
            ilObjUser::_lookupFullname($a_user_id)
        );

        $this->read($this->id);
    }

    // Get team structure for assignment
    public static function getAssignmentTeamMap(int $a_ass_id): array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $map = array();

        $sql = "SELECT * FROM il_exc_team" .
            " WHERE ass_id = " . $ilDB->quote($a_ass_id, "integer");
        $set = $ilDB->query($sql);
        while ($row = $ilDB->fetchAssoc($set)) {
            $map[$row["user_id"]] = $row["id"];
        }

        return $map;
    }

    public function writeLog(
        string $a_action,
        string $a_details = null
    ): void {
        self::writeTeamLog($this->id, $a_action, $a_details);
    }

    /**
     * Add entry to team log
     */
    public static function writeTeamLog(
        int $a_team_id,
        string $a_action,
        string $a_details = null
    ): void {
        global $DIC;

        $ilDB = $DIC->database();
        $ilUser = $DIC->user();
        $id = $ilDB->nextId('il_exc_team_log');

        $fields = array(
            "log_id" => array("integer", $id),
            "team_id" => array("integer", $a_team_id),
            "user_id" => array("integer", $ilUser->getId()),
            "action" => array("integer", $a_action),
            "details" => array("text", $a_details),
            "tstamp" => array("integer", time())
        );

        $ilDB->insert("il_exc_team_log", $fields);
    }

    // Get all log entries for team
    public function getLog(): array
    {
        $ilDB = $this->db;

        $this->cleanLog();

        $res = array();

        $sql = "SELECT * FROM il_exc_team_log" .
            " WHERE team_id = " . $ilDB->quote($this->id, "integer") .
            " ORDER BY tstamp DESC";
        $set = $ilDB->query($sql);
        while ($row = $ilDB->fetchAssoc($set)) {
            $res[] = $row;
        }
        return $res;
    }

    /**
     * Remove obsolete log entries
     *
     * As there is no proper team deletion event, we are doing it this way
     */
    protected function cleanLog(): void
    {
        $ilDB = $this->db;

        // #18179

        // see also #31565
        $obsolete_teams = [];
        $set = $ilDB->query("SELECT DISTINCT l.team_id as id FROM il_exc_team_log as l LEFT JOIN il_exc_team as t ON (l.team_id = t.id) WHERE t.id IS NULL;");
        while ($row = $ilDB->fetchAssoc($set)) {
            $obsolete_teams[] = $row["id"];
        }

        if (count($obsolete_teams) > 0) {
            $q = "DELETE FROM il_exc_team_log" .
                " WHERE " . $ilDB->in("team_id", $obsolete_teams, false, "integer");
            $ilDB->manipulate($q);
        }
    }

    /**
     * Send notification about team status
     * @throws ilExcUnknownAssignmentTypeException
     */
    public function sendNotification(
        int $a_exc_ref_id,
        int $a_user_id,
        string $a_action
    ): void {
        $ilUser = $this->user;

        // no need to notify current user
        if (!$a_exc_ref_id ||
            $ilUser->getId() == $a_user_id) {
            return;
        }
        $ass = new ilExAssignment($this->assignment_id);

        $ntf = new ilSystemNotification();
        $ntf->setLangModules(array("exc"));
        $ntf->setRefId($a_exc_ref_id);
        $ntf->setChangedByUserId($ilUser->getId());
        $ntf->setSubjectLangId('exc_team_notification_subject_' . $a_action);
        $ntf->setIntroductionLangId('exc_team_notification_body_' . $a_action);
        $ntf->addAdditionalInfo("exc_assignment", $ass->getTitle());
        $ntf->setGotoLangId('exc_team_notification_link');
        $ntf->setReasonLangId('exc_team_notification_reason');
        $ntf->sendMailAndReturnRecipients(array($a_user_id));
    }


    public static function getAdoptableTeamAssignments(
        int $a_exercise_id,
        int $a_exclude_ass_id = null,
        int $a_user_id = null
    ): array {
        $res = array();

        $data = ilExAssignment::getAssignmentDataOfExercise($a_exercise_id);
        foreach ($data as $row) {
            if ($a_exclude_ass_id && $row["id"] == $a_exclude_ass_id) {
                continue;
            }

            if ($row["type"] == ilExAssignment::TYPE_UPLOAD_TEAM) {
                $map = self::getAssignmentTeamMap($row["id"]);

                if ($a_user_id && !array_key_exists($a_user_id, $map)) {
                    continue;
                }

                if ($map !== []) {
                    $user_team = null;
                    if ($a_user_id) {
                        $user_team_id = $map[$a_user_id];
                        $user_team = array();
                        foreach ($map as $user_id => $team_id) {
                            if ($user_id != $a_user_id &&
                                $user_team_id == $team_id) {
                                $user_team[] = $user_id;
                            }
                        }
                    }

                    if (!$a_user_id ||
                        count($user_team)) {
                        $res[$row["id"]] = array(
                            "title" => $row["title"],
                            "teams" => count(array_flip($map)),
                        );

                        if ($a_user_id) {
                            $res[$row["id"]]["user_team"] = $user_team;
                        }
                    }
                }
            }
        }

        return ilArrayUtil::sortArray($res, "title", "asc", false, true);
    }

    /**
     * @throws ilExcUnknownAssignmentTypeException
     */
    public static function adoptTeams(
        int $a_source_ass_id,
        int $a_target_ass_id,
        int $a_user_id = null,
        int $a_exc_ref_id = null
    ): void {
        $teams = array();

        $old_team = null;
        foreach (self::getAssignmentTeamMap($a_source_ass_id) as $user_id => $team_id) {
            $teams[$team_id][] = $user_id;

            if ($a_user_id && $user_id == $a_user_id) {
                $old_team = $team_id;
            }
        }

        if ($a_user_id) {
            // no existing team (in source) or user already in team (in current)
            if (!$old_team ||
                self::getInstanceByUserId($a_target_ass_id, $a_user_id)->getId()) {
                return;
            }
        }

        $current_map = self::getAssignmentTeamMap($a_target_ass_id);

        foreach ($teams as $team_id => $user_ids) {
            if (!$old_team || $team_id == $old_team) {
                // only not assigned users
                $missing = array();
                foreach ($user_ids as $user_id) {
                    if (!array_key_exists($user_id, $current_map)) {
                        $missing[] = $user_id;
                    }
                }

                if ($missing !== []) {
                    // create new team
                    $first = array_shift($missing);
                    $new_team = self::getInstanceByUserId($a_target_ass_id, $first, true);

                    // give new team starting time of original user
                    if ($a_user_id > 0 && $old_team > 0) {
                        $idl = ilExcIndividualDeadline::getInstance($a_target_ass_id, $a_user_id);
                        if ($idl->getStartingTimestamp()) {
                            $idl_team = ilExcIndividualDeadline::getInstance($a_target_ass_id, $new_team->getId(), true);
                            $idl_team->setStartingTimestamp($idl->getStartingTimestamp());
                            $idl_team->save();
                        }
                    }

                    if ($a_exc_ref_id) {
                        // getTeamId() does NOT send notification
                        $new_team->sendNotification($a_exc_ref_id, $first, "add");
                    }

                    foreach ($missing as $user_id) {
                        $new_team->addTeamMember($user_id, $a_exc_ref_id);
                    }
                }
            }
        }
    }

    //
    // GROUPS
    //

    /**
     * @param int $a_exc_ref_id
     * @return int[] group obj ids
     */
    public static function getAdoptableGroups(int $a_exc_ref_id): array
    {
        global $DIC;

        $tree = $DIC->repositoryTree();

        $res = array();

        $parent_ref_id = $tree->getParentId($a_exc_ref_id);
        if ($parent_ref_id) {
            foreach ($tree->getChildsByType($parent_ref_id, "grp") as $group) {
                $res[] = $group["obj_id"];
            }
        }

        return $res;
    }

    public static function getGroupMembersMap(int $a_exc_ref_id): array
    {
        $res = array();

        foreach (self::getAdoptableGroups($a_exc_ref_id) as $grp_obj_id) {
            $members_obj = new ilGroupParticipants($grp_obj_id);

            $res[$grp_obj_id] = array(
                "title" => ilObject::_lookupTitle($grp_obj_id)
                ,"members" => $members_obj->getMembers()
            );
        }

        return ilArrayUtil::sortArray($res, "title", "asc", false, true);
    }

    /**
     * Create random teams for assignment type "team upload" following specific rules.
     * example:
     *  - total exercise members : 9 members
     *  - total number of teams to create (defined via form): 4 groups
     *  - number of users per team --> 9 / 4 = 2 users
     *  - users to spread over groups --> 9 % 4 = 1 user
     *  - final teams: 3 teams of 2 users and 1 team of 3 users.
     * @throws ilExcUnknownAssignmentTypeException
     * @throws Exception
     */
    public function createRandomTeams(
        int $a_exercise_id,
        int $a_assignment_id,
        int $a_number_teams,
        int $a_min_participants
    ): void {
        //just in case...
        if (count(self::getAssignmentTeamMap($a_assignment_id))) {
            return;
        }
        $exercise = new ilObjExercise($a_exercise_id, false);
        $obj_exc_members = new ilExerciseMembers($exercise);
        $members = $obj_exc_members->getMembers();
        $total_exc_members = count($members);
        $number_of_teams = $a_number_teams;
        if (!$number_of_teams) {
            if ($a_min_participants) {
                $number_of_teams = round($total_exc_members / $a_min_participants);
            } else {
                $number_of_teams = random_int(1, $total_exc_members);
            }
        }
        $members_per_team = round($total_exc_members / $number_of_teams);
        shuffle($members);
        for ($i = 0;$i < $number_of_teams;$i++) {
            $members_counter = 0;
            while (!empty($members) && $members_counter < $members_per_team) {
                $member_id = array_pop($members);
                if ($members_counter == 0) {
                    $team_id = $this->createTeam($a_assignment_id, $member_id);
                    $this->setId($team_id);
                    $this->assignment_id = $a_assignment_id;
                } else {
                    $this->addTeamMember($member_id);
                }
                $members_counter++;
            }
        }
        //get the new teams, remove duplicates.
        $teams = array_unique(array_values(self::getAssignmentTeamMap($a_assignment_id)));
        shuffle($teams);
        while (!empty($members)) {
            $member_id = array_pop($members);
            $team_id = array_pop($teams);
            $this->setId($team_id);
            $this->addTeamMember($member_id);
        }
    }
}
