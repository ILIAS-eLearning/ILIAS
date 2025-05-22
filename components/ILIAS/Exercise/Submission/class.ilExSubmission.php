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

use ILIAS\Exercise\Submission\Submission;

/**
 * Exercise submission
 * //TODO: This class has many static methods related to delivered "files". Extract them to classes.
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @author Alexander Killing <killing@leifos.de>
 */
class ilExSubmission
{
    public const TYPE_FILE = "File";
    public const TYPE_OBJECT = "Object";	// Blogs in WSP/Portfolio
    public const TYPE_TEXT = "Text";
    public const TYPE_REPO_OBJECT = "RepoObject";	// Wikis
    protected \ILIAS\Exercise\Submission\SubmissionManager $sub_manager;
    protected \ILIAS\Exercise\InternalDomainService $domain;

    protected ilObjUser $user;
    protected ilDBInterface $db;
    protected ilLanguage $lng;
    protected ilCtrl $ctrl;
    protected ilExAssignment $assignment;
    protected int $user_id;
    protected ?ilExAssignmentTeam $team = null;
    protected ?ilExPeerReview $peer_review = null;
    protected bool $is_tutor;
    protected bool $public_submissions;
    protected ilExAssignmentTypeInterface $ass_type;
    protected ilExAssignmentTypes $ass_types;
    protected ilExcAssMemberState $state;
    private \ilGlobalTemplateInterface $main_tpl;

    public function __construct(
        ilExAssignment $a_ass,
        int $a_user_id,
        ?ilExAssignmentTeam $a_team = null,      // did not find any place that sets this....
        bool $a_is_tutor = false,
        bool $a_public_submissions = false
    ) {
        global $DIC;
        $this->main_tpl = $DIC->ui()->mainTemplate();

        $this->user = $DIC->user();
        $this->db = $DIC->database();
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();

        $this->assignment = $a_ass;
        $this->ass_type = $this->assignment->getAssignmentType();
        $this->ass_types = ilExAssignmentTypes::getInstance();

        $this->user_id = $a_user_id;
        $this->is_tutor = $a_is_tutor;
        $this->public_submissions = $a_public_submissions;

        $this->state = ilExcAssMemberState::getInstanceByIds($a_ass->getId(), $a_user_id);

        if ($a_ass->hasTeam()) {        // ass type uses teams...
            if (!$a_team) {
                // this might be a team with no id (since the create on demand parameter is not set)
                $this->team = ilExAssignmentTeam::getInstanceByUserId($this->assignment->getId(), $this->user_id);
            } else {
                $this->team = $a_team;
            }
        }

        if ($this->assignment->getPeerReview()) {
            $this->peer_review = new ilExPeerReview($this->assignment);
        }
        $this->domain = $DIC->exercise()->internal()->domain();
        $this->sub_manager = $DIC->exercise()->internal()->domain()->submission(
            $a_ass->getId()
        );
    }

    public function getSubmissionType(): string
    {
        return $this->assignment->getAssignmentType()->getSubmissionType();
    }

    public function getAssignment(): ilExAssignment
    {
        return $this->assignment;
    }

    public function getTeam(): ?ilExAssignmentTeam
    {
        return $this->team;
    }

    public function getPeerReview(): ?ilExPeerReview
    {
        return $this->peer_review;
    }

    public function validatePeerReviews(): array
    {
        $res = array();
        foreach ($this->getUserIds() as $user_id) {
            $valid = true;

            // no peer review == valid
            if ($this->peer_review) {
                $valid = $this->peer_review->isFeedbackValidForPassed($user_id);
            }

            $res[$user_id] = $valid;
        }
        return $res;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getUserIds(): array
    {
        if ($this->team &&
            !$this->hasNoTeamYet()) {
            return $this->team->getMembers();
        }

        // if has no team currently there still might be uploads attached
        return array($this->user_id);
    }

    /**
     * used for the legacy storage path of feedbacks only
     */
    public function getFeedbackId(): string
    {
        if ($this->team) {
            return "t" . $this->team->getId();
        } else {
            return (string) $this->getUserId();
        }
    }

    public function hasSubmitted(): bool
    {
        return (bool) $this->sub_manager->getSubmissionsOfUser(
            $this->getUserId(),
            null,
            true
        )->current();
    }

    public function hasSubmittedPrintVersion(): bool
    {
        return ($this->getSubmittedEntry(true)?->getRid() != "");
    }

    public function getSubmittedEntry(bool $print = false): ?Submission
    {
        return $this->sub_manager->getSubmissionsOfUser(
            $this->getUserId(),
            null,
            false,
            null,
            $print
        )->current();
    }

    public function getSelectedObject(): ?Submission
    {
        return $this->sub_manager->getSubmissionsOfUser($this->getUserId())->current();
    }

    public function canSubmit(): bool
    {
        return ($this->isOwner() &&
            $this->state->isSubmissionAllowed());
    }

    public function canView(): bool
    {
        $ilUser = $this->user;

        if ($this->canSubmit() ||
            $this->isTutor() ||
            $this->isInTeam() ||
            $this->public_submissions) {
            return true;
        }

        // #16115
        if ($this->peer_review) {
            // peer review givers may view peer submissions
            foreach ($this->peer_review->getPeerReviewsByPeerId($this->getUserId()) as $giver) {
                if ($giver["giver_id"] == $ilUser->getId()) {
                    return true;
                }
            }
        }

        return false;
    }

    public function isTutor(): bool
    {
        return $this->is_tutor;
    }

    public function hasNoTeamYet(): bool
    {
        if ($this->assignment->hasTeam() &&
            !$this->team->getId()) {
            return true;
        }
        return false;
    }

    public function isInTeam(?int $a_user_id = null): bool
    {
        $ilUser = $this->user;

        if (!$a_user_id) {
            $a_user_id = $ilUser->getId();
        }
        return in_array($a_user_id, $this->getUserIds());
    }

    public function isOwner(): bool
    {
        $ilUser = $this->user;

        return ($ilUser->getId() == $this->getUserId());
    }

    public function hasPeerReviewAccess(): bool
    {
        return ($this->peer_review &&
            $this->peer_review->hasPeerReviewAccess($this->user_id));
    }

    public function canAddFile(): bool
    {
        if (!$this->canSubmit()) {
            return false;
        }

        $max = $this->getAssignment()->getMaxFile();
        $cnt_sub = $this->sub_manager->countSubmissionsOfUser(
            $this->getUserId()
        );
        if ($max &&
            $max <= $cnt_sub) {
            return false;
        }

        return true;
    }


    //
    // FILES
    //

    public function isLate(): bool
    {
        $dl = $this->state->getOfficialDeadline();
        //$dl = $this->assignment->getPersonalDeadline($this->getUserId());
        return ($dl && $dl < time());
    }

    protected function getStorageId(): int
    {
        if ($this->ass_type->isSubmissionAssignedToTeam()) {
            $storage_id = $this->getTeam()->getId();
        } else {
            $storage_id = $this->getUserId();
        }
        return $storage_id;
    }

    /**
     * Check how much files have been uploaded by the learner
     * after the last download of the tutor.
     */
    public function lookupNewFiles(
        ?int $a_tutor = null
    ): array {
        $ilDB = $this->db;
        $ilUser = $this->user;

        $tutor = ($a_tutor)
            ?: $ilUser->getId();

        $where = " AND " . $this->getTableUserWhere();

        $q = "SELECT exc_returned.returned_id AS id " .
            "FROM exc_usr_tutor, exc_returned " .
            "WHERE exc_returned.ass_id = exc_usr_tutor.ass_id " .
            " AND exc_returned.user_id = exc_usr_tutor.usr_id " .
            " AND exc_returned.ass_id = " . $ilDB->quote($this->getAssignment()->getId(), "integer") .
            $where .
            " AND exc_usr_tutor.tutor_id = " . $ilDB->quote($tutor, "integer") .
            " AND exc_usr_tutor.download_time < exc_returned.ts ";

        $new_up_set = $ilDB->query($q);

        $new_up = array();
        while ($new_up_rec = $ilDB->fetchAssoc($new_up_set)) {
            $new_up[] = $new_up_rec["id"];
        }

        return $new_up;
    }

    /**
     * Get exercise from submission id (used in ilObjMediaObject)
     */
    public static function lookupExerciseIdForReturnedId(
        int $a_returned_id
    ): int {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->query("SELECT obj_id" .
            " FROM exc_returned" .
            " WHERE returned_id = " . $ilDB->quote($a_returned_id, "integer"));
        $row = $ilDB->fetchAssoc($set);
        return (int) $row["obj_id"];
    }

    /**
     * Check if given file was assigned
     * Used in Blog/Portfolio
     */
    public static function findUserFiles(
        int $a_user_id,
        string $a_filetitle
    ): array {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->query("SELECT obj_id, ass_id" .
            " FROM exc_returned" .
            " WHERE user_id = " . $ilDB->quote($a_user_id, "integer") .
            " AND filetitle = " . $ilDB->quote($a_filetitle, "text"));
        $res = array();
        while ($row = $ilDB->fetchAssoc($set)) {
            $res[$row["ass_id"]] = $row;
        }
        return $res;
    }

    public function deleteAllFiles(): void
    {
        $this->sub_manager->deleteAllSubmissionsOfUser($this->getUserId());
    }

    /**
    * Deletes already delivered files
    * @param array $file_id_array An array containing database ids of the delivered files
    */
    /*           --> use manager deleteSubmissions()
    public function deleteSelectedFiles(
        array $file_id_array
    ): void {
        $ilDB = $this->db;


        $where = " AND " . $this->getTableUserWhere(true);


        if ($file_id_array === []) {
            return;
        }

        if ($file_id_array !== []) {
            $result = $ilDB->query("SELECT * FROM exc_returned" .
                " WHERE " . $ilDB->in("returned_id", $file_id_array, false, "integer") .
                $where);

            if ($ilDB->numRows($result)) {
                $result_array = array();
                while ($row = $ilDB->fetchAssoc($result)) {
                    $row["timestamp"] = $row["ts"];
                    $result_array[] = $row;
                }

                // delete the entries in the database
                $ilDB->manipulate("DELETE FROM exc_returned" .
                    " WHERE " . $ilDB->in("returned_id", $file_id_array, false, "integer") .
                    $where);

                // delete the files
                $path = $this->initStorage()->getAbsoluteSubmissionPath();
                foreach ($result_array as $value) {
                    if ($value["filename"]) {
                        if ($this->team) {
                            $this->team->writeLog(
                                ilExAssignmentTeam::TEAM_LOG_REMOVE_FILE,
                                $value["filetitle"]
                            );
                        }

                        if ($this->getAssignment()->getAssignmentType()->isSubmissionAssignedToTeam()) {
                            $storage_id = $value["team_id"];
                        } else {
                            $storage_id = $value["user_id"];
                        }

                        $filename = $path . "/" . $storage_id . "/" . basename($value["filename"]);
                        if (file_exists($filename)) {
                            unlink($filename);
                        }
                    }
                }
            }
        }
    }*/

    /**
     * Delete all delivered files of user
     * @throws ilExcUnknownAssignmentTypeException
     */
    public static function deleteUser(
        int $a_exc_id,
        int $a_user_id
    ): void {
        global $DIC;

        $db = $DIC->database();

        foreach (ilExAssignment::getInstancesByExercise($a_exc_id) as $ass) {
            $submission = new self($ass, $a_user_id);
            $submission->deleteAllFiles();

            // remove from any team
            $team = $submission->getTeam();
            if ($team) {
                $team->removeTeamMember($a_user_id);
            }

            // #14900
            $member_status = $ass->getMemberStatus($a_user_id);
            $member_status->setStatus("notgraded");
            $member_status->update();

            $db->manipulateF(
                "DELETE FROM exc_usr_tutor " .
                "WHERE ass_id = %s AND usr_id = %s",
                array("integer", "integer"),
                array($ass->getId(), $a_user_id)
            );
        }
    }

    /**
     * @param array $a_user_ids
     * @return string "Y-m-d H:i:s"
     */
    protected function getLastDownloadTime(
        array $a_user_ids
    ): string {
        $ilDB = $this->db;
        $ilUser = $this->user;

        $q = "SELECT download_time FROM exc_usr_tutor WHERE " .
            " ass_id = " . $ilDB->quote($this->getAssignment()->getId(), "integer") . " AND " .
            $ilDB->in("usr_id", $a_user_ids, "", "integer") . " AND " .
            " tutor_id = " . $ilDB->quote($ilUser->getId(), "integer") .
            " ORDER BY download_time DESC";
        $lu_set = $ilDB->query($q);
        $lu_rec = $ilDB->fetchAssoc($lu_set);
        return $lu_rec["download_time"] ?? "";
    }

    public function downloadFiles(
        ?array $a_file_ids = null,
        bool $a_only_new = false,
        bool $a_peer_review_mask_filename = false
    ): bool {
        $ilUser = $this->user;
        $lng = $this->lng;

        $user_ids = $this->getUserIds();
        $is_team = $this->assignment->hasTeam();
        // get last download time
        $download_time = null;
        if ($a_only_new) {
            $download_time = $this->getLastDownloadTime($user_ids);
        }

        if ($this->is_tutor) {
            $this->updateTutorDownloadTime();
        }

        if ($a_peer_review_mask_filename) {
            // process peer review sequence id
            $peer_id = null;
            foreach ($this->peer_review->getPeerReviewsByGiver($ilUser->getId()) as $idx => $item) {
                if ($item["peer_id"] == $this->getUserId()) {
                    $peer_id = $idx + 1;
                    break;
                }
            }
            // this will remove personal info from zip-filename
            $is_team = true;
        }

        $subs = iterator_to_array($this->sub_manager->getSubmissionsOfUser(
            $this->getUserId(),
            $a_file_ids,
            false,
            $download_time
        ));

        if (count($subs) > 0) {
            if (count($subs) == 1) {
                /** @var Submission $sub */
                $sub = current($subs);

                $title = $sub->getTitle();

                switch ($this->assignment->getType()) {
                    case ilExAssignment::TYPE_BLOG:
                    case ilExAssignment::TYPE_PORTFOLIO:
                        $name = ilObjUser::_lookupName($sub->getUserId());
                        $title = ilObject::_lookupTitle($this->assignment->getExerciseId()) . " - " .
                            $this->assignment->getTitle() . " - " .
                            $name["firstname"] . " " .
                            $name["lastname"] . " (" .
                            $name["login"] . ").zip";
                        break;

                        // @todo: generalize
                    case ilExAssignment::TYPE_WIKI_TEAM:
                        $title = ilObject::_lookupTitle($this->assignment->getExerciseId()) . " - " .
                            $this->assignment->getTitle() . " (Team " . $this->getTeam()->getId() . ").zip";
                        break;

                    default:
                        break;
                }

                if ($a_peer_review_mask_filename) {
                    $title_a = explode(".", $sub->getTitle());
                    $suffix = array_pop($title_a);
                    $title = $this->assignment->getTitle() . "_peer" . $peer_id . "." . $suffix;
                } elseif ($sub->getLate()) {
                    $title = $lng->txt("exc_late_submission") . " - " .
                        $title;
                }

                $this->downloadSingleFile($sub, $title);
            } else {
                $this->sub_manager->deliverSubmissions(
                    $subs,
                    $this->getUserId(),
                    $a_peer_review_mask_filename,
                    $peer_id ?? 0
                );
            }
        } else {
            return false;
        }

        return true;
    }

    // Update the timestamp of the last download of current user (=tutor)
    public function updateTutorDownloadTime(): void
    {
        $ilUser = $this->user;
        $ilDB = $this->db;

        $exc_id = $this->assignment->getExerciseId();
        $ass_id = $this->assignment->getId();

        foreach ($this->getUserIds() as $user_id) {
            $ilDB->manipulateF(
                "DELETE FROM exc_usr_tutor " .
                "WHERE ass_id = %s AND usr_id = %s AND tutor_id = %s",
                array("integer", "integer", "integer"),
                array($ass_id, $user_id, $ilUser->getId())
            );

            $ilDB->manipulateF(
                "INSERT INTO exc_usr_tutor (ass_id, obj_id, usr_id, tutor_id, download_time) VALUES " .
                "(%s, %s, %s, %s, %s)",
                array("integer", "integer", "integer", "integer", "timestamp"),
                array($ass_id, $exc_id, $user_id, $ilUser->getId(), ilUtil::now())
            );
        }
    }

    protected function downloadSingleFile(
        Submission $sub,
        string $title
    ): void {
        $this->domain->submission($this->assignment->getId())->deliverFile(
            $sub->getUserId(),
            $sub->getRid(),
            $title
        );
    }

    // Get user/team where clause
    public function getTableUserWhere(): string
    {
        $ilDB = $this->db;

        if ($this->getAssignment()->getAssignmentType()->isSubmissionAssignedToTeam()) {
            $team_id = $this->getTeam()->getId();
            $where = " team_id = " . $ilDB->quote($team_id, "integer") . " ";
        } else {
            $where = " " . $ilDB->in("user_id", $this->getUserIds(), "", "integer") . " ";
        }
        return $where;
    }


    /**
     * TODO -> get rid of getTableUserWhere and move to repository class
     * Get the date of the last submission of a user for the assignment
     */
    public function getLastSubmission(): ?string
    {
        $ilDB = $this->db;

        $ilDB->setLimit(1, 0);

        $q = "SELECT obj_id,user_id,ts FROM exc_returned" .
            " WHERE ass_id = " . $ilDB->quote($this->assignment->getId(), "integer") .
            " AND " . $this->getTableUserWhere() .
            " AND (filename IS NOT NULL OR atext IS NOT NULL)" .
            " AND ts IS NOT NULL" .
            " ORDER BY ts DESC";
        $usr_set = $ilDB->query($q);
        $array = $ilDB->fetchAssoc($usr_set);
        return ($array["ts"] ?? null);
    }

    /**
     * TODO -> get rid of getTableUserWhere and move to repository class
     * Get a mysql timestamp from the last HTML view opening.
     */
    public function getLastOpeningHTMLView(): ?string
    {
        $this->db->setLimit(1, 0);

        $q = "SELECT web_dir_access_time FROM exc_returned" .
            " WHERE ass_id = " . $this->db->quote($this->assignment->getId(), "integer") .
            " AND (filename IS NOT NULL OR atext IS NOT NULL)" .
            " AND web_dir_access_time IS NOT NULL" .
            " AND " . $this->getTableUserWhere() .
            " ORDER BY web_dir_access_time DESC";

        $res = $this->db->query($q);

        $data = $this->db->fetchAssoc($res);

        return $data["web_dir_access_time"] ?? null;
    }


    //
    // OBJECTS
    //

    /**
     * Add personal resource or repository object (ref_id) to assigment
     * @throws ilExcUnknownAssignmentTypeException
     * @throws ilExerciseException
     */
    public function addResourceObject(
        string $a_wsp_id,                   // note: text assignments currently call this with "TEXT"
        ?string $a_text = null
    ): int {
        $ilDB = $this->db;

        if ($this->getAssignment()->getAssignmentType()->isSubmissionAssignedToTeam()) {
            $user_id = 0;
            $team_id = $this->getTeam()->getId();
        } else {
            $user_id = $this->getUserId();
            $team_id = 0;
        }

        // repository objects must be unique in submissions
        // the same repo object cannot be used in different submissions or even different assignment/exercises
        // why? -> the access handling would fail, since the access depends e.g. on teams or even phase of the
        // assignment
        if ($this->getAssignment()->getAssignmentType()->getSubmissionType() == ilExSubmission::TYPE_REPO_OBJECT) {
            $repos_ass_type_ids = $this->ass_types->getIdsForSubmissionType(ilExSubmission::TYPE_REPO_OBJECT);
            $subs = $this->getSubmissionsForFilename($a_wsp_id, $repos_ass_type_ids);
            if ($subs !== []) {
                throw new ilExerciseException("Repository object $a_wsp_id is already assigned to another assignment.");
            }
        }

        $next_id = $ilDB->nextId("exc_returned");
        $query = sprintf(
            "INSERT INTO exc_returned " .
                         "(returned_id, obj_id, user_id, filetitle, ass_id, ts, atext, late, team_id) " .
                         "VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)",
            $ilDB->quote($next_id, "integer"),
            $ilDB->quote($this->assignment->getExerciseId(), "integer"),
            $ilDB->quote($user_id, "integer"),
            $ilDB->quote($a_wsp_id, "text"),
            $ilDB->quote($this->assignment->getId(), "integer"),
            $ilDB->quote(ilUtil::now(), "timestamp"),
            $ilDB->quote($a_text, "text"),
            $ilDB->quote($this->isLate(), "integer"),
            $ilDB->quote($team_id, "integer")
        );
        $ilDB->manipulate($query);

        return $next_id;
    }

    /*
     * Remove ressource from assignement (and delete
     * its submission): Note: The object itself will not be deleted.
     */
    public function deleteResourceObject(): void
    {
        $this->deleteAllFiles();
    }

    /**
     * Handle text assignment submissions
     * @throws ilExcUnknownAssignmentTypeException
     * @throws ilExerciseException
     */
    public function updateTextSubmission(string $a_text): ?int
    {
        $ilDB = $this->db;

        // no text = remove submission
        if (!trim($a_text)) {
            $this->sub_manager->deleteAllSubmissionsOfUser($this->getUserId());
            return null;
        }

        $sub = $this->sub_manager->getSubmissionsOfUser($this->getUserId())->current();

        if (!$sub) {
            return $this->addResourceObject("TEXT", $a_text);
        } else {
            $id = $sub->getId();
            if ($id) {
                $ilDB->manipulate("UPDATE exc_returned" .
                    " SET atext = " . $ilDB->quote($a_text, "text") .
                    ", ts = " . $ilDB->quote(ilUtil::now(), "timestamp") .
                    ", late = " . $ilDB->quote($this->isLate(), "integer") .
                    " WHERE returned_id = " . $ilDB->quote($id, "integer"));
                return $id;
            }
        }
        return null;
    }

    //
    // GUI helper
    //

    /**
     * @throws ilDateTimeException
     */
    public function getDownloadedFilesInfoForTableGUIS(): array
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $result = array();
        $result["files"]["count"] = "---";

        // submission:
        // see if files have been resubmmited after solved
        $last_sub = $this->getLastSubmission();
        if ($last_sub) {
            $last_sub = ilDatePresentation::formatDate(new ilDateTime($last_sub, IL_CAL_DATETIME));
        } else {
            $last_sub = "---";
        }
        $result["last_submission"]["txt"] = $lng->txt("exc_last_submission");
        $result["last_submission"]["value"] = $last_sub;

        // #16994
        $ilCtrl->setParameterByClass("ilexsubmissionfilegui", "member_id", $this->getUserId());

        // assignment type specific
        switch ($this->assignment->getType()) {
            case ilExAssignment::TYPE_UPLOAD_TEAM:
                // data is merged by team - see above
                // fallthrough

            case ilExAssignment::TYPE_UPLOAD:
                $subs = $this->sub_manager->getSubmissionsOfUser($this->getUserId());
                $late_files = 0;
                $cnt_all = 0;
                foreach ($subs as $sub) {
                    if ($sub->getLate()) {
                        $late_files++;
                    }
                    $cnt_all++;
                }

                // nr of submitted files
                $result["files"]["txt"] = $lng->txt("exc_files_returned");
                if ($late_files !== 0) {
                    $result["files"]["txt"] .= ' - <span class="warning">' . $lng->txt("exc_late_submission") . " (" . $late_files . ")</span>";
                }
                $sub_cnt = $cnt_all;
                $new = $this->lookupNewFiles();
                if ($new !== []) {
                    $sub_cnt .= " " . sprintf($lng->txt("cnt_new"), count($new));
                }

                $result["files"]["count"] = $sub_cnt;

                // download command
                if ($sub_cnt > 0) {
                    $result["files"]["download_url"] =
                        $ilCtrl->getLinkTargetByClass("ilexsubmissionfilegui", "downloadReturned");

                    if (count($new) <= 0) {
                        $result["files"]["download_txt"] = $lng->txt("exc_tbl_action_download_files");
                    } else {
                        $result["files"]["download_txt"] = $lng->txt("exc_tbl_action_download_all_files");
                    }

                    // download new files only
                    if ($new !== []) {
                        $result["files"]["download_new_url"] =
                            $ilCtrl->getLinkTargetByClass("ilexsubmissionfilegui", "downloadNewReturned");

                        $result["files"]["download_new_txt"] = $lng->txt("exc_tbl_action_download_new_files");
                    }
                }
                break;

            case ilExAssignment::TYPE_BLOG:
                $result["files"]["txt"] = $lng->txt("exc_blog_returned");
                /** @var Submission $sub */
                $sub = $this->sub_manager->getSubmissionsOfUser($this->getUserId())->current();
                if ($sub) {
                    if ($sub->getRid() != "") {
                        if ($sub->getLate()) {
                            $result["files"]["txt"] .= ' - <span class="warning">' . $lng->txt("exc_late_submission") . "</span>";
                        }

                        $result["files"]["count"] = 1;

                        $result["files"]["download_url"] =
                            $ilCtrl->getLinkTargetByClass("ilexsubmissionfilegui", "downloadReturned");

                        $result["files"]["download_txt"] = $lng->txt("exc_tbl_action_download_files");
                    }
                }
                break;

            case ilExAssignment::TYPE_PORTFOLIO:
                $result["files"]["txt"] = $lng->txt("exc_portfolio_returned");
                /** @var Submission $sub */
                $sub = $this->sub_manager->getSubmissionsOfUser($this->getUserId())->current();
                if ($sub) {
                    if ($sub->getRid() != "") {
                        if ($sub->getLate()) {
                            $result["files"]["txt"] .= ' - <span class="warning">' . $lng->txt("exc_late_submission") . "</span>";
                        }

                        $result["files"]["count"] = 1;

                        $result["files"]["download_url"] =
                            $ilCtrl->getLinkTargetByClass("ilexsubmissionfilegui", "downloadReturned");

                        $result["files"]["download_txt"] = $lng->txt("exc_tbl_action_download_files");
                    }
                }
                break;

            case ilExAssignment::TYPE_TEXT:
                $result["files"]["txt"] = $lng->txt("exc_files_returned_text");
                $sub = $this->sub_manager->getSubmissionsOfUser($this->getUserId())->current();
                if ($sub) {
                    $result["files"]["count"] = 1;

                    if (trim($sub->getText()) !== '' && trim($sub->getText()) !== '0') {
                        if ($sub->getLate()) {
                            $result["files"]["txt"] .= ' - <span class="warning">' . $lng->txt("exc_late_submission") . "</span>";
                        }

                        $result["files"]["download_url"] =
                            $ilCtrl->getLinkTargetByClass("ilexsubmissiontextgui", "showAssignmentText");

                        $result["files"]["download_txt"] = $lng->txt("exc_tbl_action_text_assignment_show");
                    }
                }
                break;

            case ilExAssignment::TYPE_WIKI_TEAM:
                $result["files"]["txt"] = $lng->txt("exc_wiki_returned");
                $sub = $this->sub_manager->getSubmissionsOfUser($this->getUserId())->current();
                if ($sub) {
                    if ($sub->getRid() != "") {
                        if ($sub->getLate()) {
                            $result["files"]["txt"] .= ' - <span class="warning">' . $lng->txt("exc_late_submission") . "</span>";
                        }

                        $result["files"]["count"] = 1;

                        $result["files"]["download_url"] =
                            $ilCtrl->getLinkTargetByClass("ilexsubmissionfilegui", "downloadReturned");

                        $result["files"]["download_txt"] = $lng->txt("exc_tbl_action_download_files");
                    }
                }
                break;
        }

        $ilCtrl->setParameterByClass("ilexsubmissionfilegui", "member_id", "");

        return $result;
    }

    /**
     * Get assignment return entries for a filename
     */
    public static function getSubmissionsForFilename(
        string $a_filename,
        array $a_assignment_types = array()
    ): array {
        global $DIC;

        $db = $DIC->database();

        $query = "SELECT * FROM exc_returned r LEFT JOIN exc_assignment a" .
            " ON (r.ass_id = a.id) " .
            " WHERE r.filetitle = " . $db->quote($a_filename, "string");

        if (is_array($a_assignment_types) && $a_assignment_types !== []) {
            $query .= " AND " . $db->in("a.type", $a_assignment_types, false, "integer");
        }

        $set = $db->query($query);
        $rets = array();
        while ($rec = $db->fetchAssoc($set)) {
            $rets[] = $rec;
        }


        return $rets;
    }

    /**
     * @deprecated see getDirectoryNameFromUserData in SubmissionManager
     */
    public static function getDirectoryNameFromUserData(int $a_user_id): string
    {
        $userName = ilObjUser::_lookupName($a_user_id);
        return ilFileUtils::getASCIIFilename(
            trim($userName["lastname"]) . "_" .
            trim($userName["firstname"]) . "_" .
            trim($userName["login"]) . "_" .
            $userName["user_id"]
        );
    }

    public static function getAssignmentParticipants(
        int $a_exercise_id,
        int $a_ass_id
    ): array {
        global $DIC;

        $ilDB = $DIC->database();

        $participants = array();
        $query = "SELECT user_id FROM exc_returned WHERE ass_id = " .
            $ilDB->quote($a_ass_id, "integer") .
            " AND obj_id = " .
            $ilDB->quote($a_exercise_id, "integer");

        $res = $ilDB->query($query);

        while ($row = $ilDB->fetchAssoc($res)) {
            $participants[] = $row['user_id'];
        }

        return $participants;
    }

    public static function processZipFile(
        string $a_directory,
        string $a_file,
        bool $structure
    ): void {
        global $DIC;

        $lng = $DIC->language();

        $pathinfo = pathinfo($a_file);
        $file = $pathinfo["basename"];

        // see 22727
        if (($pathinfo["extension"] ?? '') === '') {
            $file .= ".zip";
        }

        // Copy zip-file to new directory, unzip and remove it
        // TODO: check archive for broken file
        //copy ($a_file, $a_directory . "/" . $file);
        ilFileUtils::moveUploadedFile($a_file, $file, $a_directory . "/" . $file);
        $DIC->legacyArchives()->unzip(
            $a_directory . "/" . $file,
            null,
            false,
            true,
            false
        );
        unlink($a_directory . "/" . $file);
        //echo "-".$a_directory . "/" . $file."-";
        // Stores filename and paths into $filearray to check for viruses
        // Checks if filenames can be read, else -> throw exception and leave
        $filearray = [];
        ilFileUtils::recursive_dirscan($a_directory, $filearray);

        // if there are no files unziped (->broken file!)
        if (empty($filearray)) {
            throw new ilFileUtilsException(
                $lng->txt("archive_broken"),
                ilFileUtilsException::$BROKEN_FILE
            );
        }

        // virus handling
        foreach ($filearray["file"] as $key => $value) {
            // remove "invisible" files
            if (substr($value, 0, 1) == "." || stristr(
                $filearray["path"][$key],
                "/__MACOSX/"
            )) {
                unlink($filearray["path"][$key] . $value);
                unset($filearray["path"][$key]);
                unset($filearray["file"][$key]);
                continue;
            }

            $vir = ilVirusScanner::virusHandling($filearray["path"][$key], $value);
            if (!$vir[0]) {
                // Unlink file and throw exception
                unlink($filearray['path'][$key]);
                throw new ilFileUtilsException(
                    $lng->txt("file_is_infected") . "<br />" . $vir[1],
                    ilFileUtilsException::$INFECTED_FILE
                );
            } elseif ($vir[1] != "") {
                throw new ilFileUtilsException(
                    $vir[1],
                    ilFileUtilsException::$INFECTED_FILE
                );
            }
        }

        // If archive is to be used "flat"
        $doublettes = '';
        if (!$structure) {
            foreach (array_count_values($filearray["file"]) as $key => $value) {
                // Archive contains same filenames in different directories
                if ($value != "1") {
                    $doublettes .= " '" . ilFileUtils::utf8_encode($key) . "'";
                }
            }
            if (strlen($doublettes) > 0) {
                throw new ilFileUtilsException(
                    $lng->txt("exc_upload_error") . "<br />" . $lng->txt(
                        "zip_structure_error"
                    ) . $doublettes,
                    ilFileUtilsException::$DOUBLETTES_FOUND
                );
            }
        } else {
            $mac_dir = $a_directory . "/__MACOSX";
            if (file_exists($mac_dir)) {
                ilFileUtils::delDir($mac_dir);
            }
        }
    }
}
