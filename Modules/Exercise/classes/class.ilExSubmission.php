<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Exercise submission
 * //TODO: This class has to much static methods related to delivered "files". Extract them to classes.
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ModulesExercise
 */
class ilExSubmission
{
    const TYPE_FILE = "File";
    const TYPE_OBJECT = "Object";	// Blogs in WSP/Portfolio
    const TYPE_TEXT = "Text";
    const TYPE_REPO_OBJECT = "RepoObject";	// Wikis

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var ilDB
     */
    protected $db;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    protected $assignment; // [ilExAssignment]
    protected $user_id; // [int]
    protected $team; // [ilExAssignmentTeam]
    protected $peer_review; // [ilExPeerReview]
    protected $is_tutor; // [bool]
    protected $public_submissions; // [bool]

    /**
     * @var ilExAssignmentTypeInterface
     */
    protected $ass_type;

    /**
     * @var ilExAssignmentTypes
     */
    protected $ass_types;
    
    public function __construct(ilExAssignment $a_ass, $a_user_id, ilExAssignmentTeam $a_team = null, $a_is_tutor = false, $a_public_submissions = false)
    {
        global $DIC;

        $this->user = $DIC->user();
        $this->db = $DIC->database();
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $ilUser = $DIC->user();
        
        $this->assignment = $a_ass;
        $this->ass_type = $this->assignment->getAssignmentType();
        $this->ass_types = ilExAssignmentTypes::getInstance();

        $this->user_id = $a_user_id;
        $this->is_tutor = (bool) $a_is_tutor;
        $this->public_submissions = (bool) $a_public_submissions;

        include_once("./Modules/Exercise/classes/class.ilExcAssMemberState.php");
        $this->state = ilExcAssMemberState::getInstanceByIds($a_ass->getId(), $a_user_id);
        
        if ($a_ass->hasTeam()) {
            if (!$a_team) {
                include_once "Modules/Exercise/classes/class.ilExAssignmentTeam.php";
                $this->team = ilExAssignmentTeam::getInstanceByUserId($this->assignment->getId(), $this->user_id);
            } else {
                $this->team = $a_team;
            }
        }
        
        if ($this->assignment->getPeerReview()) {
            include_once "Modules/Exercise/classes/class.ilExPeerReview.php";
            $this->peer_review = new ilExPeerReview($this->assignment);
        }
    }
        
    public function getSubmissionType()
    {
        return $this->assignment->getAssignmentType()->getSubmissionType();
        /*switch($this->assignment->getType())
        {
            case ilExAssignment::TYPE_UPLOAD_TEAM:
            case ilExAssignment::TYPE_UPLOAD:
                return "File";

            case ilExAssignment::TYPE_BLOG:
            case ilExAssignment::TYPE_PORTFOLIO:
                return "Object";

            case ilExAssignment::TYPE_TEXT:
                return "Text";
        };*/
    }
    
    
    /**
     * @return \ilExAssignment
     */
    public function getAssignment()
    {
        return $this->assignment;
    }
    
    /**
     * @return \ilExAssignmentTeam
     */
    public function getTeam()
    {
        return $this->team;
    }
    
    /**
     * @return \ilExPeerReview
     */
    public function getPeerReview()
    {
        return $this->peer_review;
    }
    
    public function validatePeerReviews()
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
    
    public function getUserId()
    {
        return $this->user_id;
    }
    
    public function getUserIds()
    {
        if ($this->team &&
            !$this->hasNoTeamYet()) {
            return $this->team->getMembers();
        }
        
        // if has no team currently there still might be uploads attached
        return array($this->user_id);
    }
    
    public function getFeedbackId()
    {
        if ($this->team) {
            return "t" . $this->team->getId();
        } else {
            return $this->getUserId();
        }
    }

    public function hasSubmitted()
    {
        return (bool) sizeof($this->getFiles(null, true));
    }
    
    public function getSelectedObject()
    {
        $files = $this->getFiles();
        if (sizeof($files)) {
            return array_pop($files);
        }
    }
    
    public function canSubmit()
    {
        return ($this->isOwner() &&
            $this->state->isSubmissionAllowed());
    }
    
    public function canView()
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
    
    public function isTutor()
    {
        return $this->is_tutor;
    }
    
    public function hasNoTeamYet()
    {
        if ($this->assignment->hasTeam() &&
            !$this->team->getId()) {
            return true;
        }
        return false;
    }
    
    public function isInTeam($a_user_id = null)
    {
        $ilUser = $this->user;
        
        if (!$a_user_id) {
            $a_user_id = $ilUser->getId();
        }
        return in_array($a_user_id, $this->getUserIds());
    }
    
    public function isOwner()
    {
        $ilUser = $this->user;
        
        return ($ilUser->getId() == $this->getUserId());
    }
    
    public function hasPeerReviewAccess()
    {
        return ($this->peer_review &&
            $this->peer_review->hasPeerReviewAccess($this->user_id));
    }
    
    public function canAddFile()
    {
        if (!$this->canSubmit()) {
            return false;
        }
        
        $max = $this->getAssignment()->getMaxFile();
        if ($max &&
            $max <= sizeof($this->getFiles())) {
            return false;
        }
        
        return true;
    }
    
    
    //
    // FILES
    //
    
    protected function isLate()
    {
        $dl = $this->state->getOfficialDeadline();
        //$dl = $this->assignment->getPersonalDeadline($this->getUserId());
        return ($dl && $dl < time());
    }
    
    protected function initStorage()
    {
        include_once("./Modules/Exercise/classes/class.ilFSStorageExercise.php");
        return new ilFSStorageExercise($this->assignment->getExerciseId(), $this->assignment->getId());
    }

    /**
     * Get storage id
     *
     * @return int
     */
    protected function getStorageId()
    {
        if ($this->ass_type->isSubmissionAssignedToTeam()) {
            $storage_id = $this->getTeam()->getId();
        } else {
            $storage_id = $this->getUserId();
        }
        return $storage_id;
    }


    /**
     * Save submitted file of user
     */
    public function uploadFile($a_http_post_files, $unzip = false)
    {
        $ilDB = $this->db;

        if (!$this->canAddFile()) {
            return false;
        }

        if ($this->ass_type->isSubmissionAssignedToTeam()) {
            $team_id = $this->getTeam()->getId();
            $user_id = 0;
            if ($team_id == 0) {
                return false;
            }
        } else {
            $team_id = 0;
            $user_id = $this->getUserId();
        }
        $storage_id = $this->getStorageId();

        $deliver_result = $this->initStorage()->uploadFile($a_http_post_files, $storage_id, $unzip);

        if ($deliver_result) {
            $next_id = $ilDB->nextId("exc_returned");
            $query = sprintf(
                "INSERT INTO exc_returned " .
                             "(returned_id, obj_id, user_id, filename, filetitle, mimetype, ts, ass_id, late, team_id) " .
                             "VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
                $ilDB->quote($next_id, "integer"),
                $ilDB->quote($this->assignment->getExerciseId(), "integer"),
                $ilDB->quote($user_id, "integer"),
                $ilDB->quote($deliver_result["fullname"], "text"),
                $ilDB->quote(ilFileUtils::getValidFilename($a_http_post_files["name"]), "text"),
                $ilDB->quote($deliver_result["mimetype"], "text"),
                $ilDB->quote(ilUtil::now(), "timestamp"),
                $ilDB->quote($this->assignment->getId(), "integer"),
                $ilDB->quote($this->isLate(), "integer"),
                $ilDB->quote($team_id, "integer")
            );
            $ilDB->manipulate($query);
        
            if ($this->team) {
                $this->team->writeLog(
                    ilExAssignmentTeam::TEAM_LOG_ADD_FILE,
                    $a_http_post_files["name"]
                );
            }
            
            return true;
        }
        return false;
    }
    
    /**
    * processes errorhandling etc for uploaded archive
    * @param string $tmpFile path and filename to uploaded file
    */
    public function processUploadedZipFile($fileTmp)
    {
        $lng = $this->lng;
        
        // Create unzip-directory
        $newDir = ilUtil::ilTempnam();
        ilUtil::makeDir($newDir);

        include_once("Services/Utilities/classes/class.ilFileUtils.php");
        
        $success = true;
        
        try {
            ilFileUtils::processZipFile($newDir, $fileTmp, false);
            ilFileUtils::recursive_dirscan($newDir, $filearray);

            // #18441 - check number of files in zip
            $max_num = $this->assignment->getMaxFile();
            if ($max_num) {
                $current_num = sizeof($this->getFiles());
                $zip_num = sizeof($filearray["file"]);
                if ($current_num + $zip_num > $max_num) {
                    $success = false;
                    ilUtil::sendFailure($lng->txt("exc_upload_error") . " [Zip1]", true);
                }
            }
            
            if ($success) {
                foreach ($filearray["file"] as $key => $filename) {
                    $a_http_post_files["name"] = ilFileUtils::utf8_encode($filename);
                    $a_http_post_files["type"] = "other";
                    $a_http_post_files["tmp_name"] = $filearray["path"][$key] . "/" . $filename;
                    $a_http_post_files["error"] = 0;
                    $a_http_post_files["size"] = filesize($filearray["path"][$key] . "/" . $filename);

                    if (!$this->uploadFile($a_http_post_files, true)) {
                        $success = false;
                        ilUtil::sendFailure($lng->txt("exc_upload_error") . " [Zip2]", true);
                    }
                }
            }
        } catch (ilFileUtilsException $e) {
            $success = false;
            ilUtil::sendFailure($e->getMessage());
        }
        
        ilUtil::delDir($newDir);
        return $success;
    }
    
    public static function hasAnySubmissions($a_ass_id)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $query = "SELECT * FROM exc_returned" .
            " WHERE ass_id = " . $ilDB->quote($a_ass_id, "integer") .
            " AND (filename IS NOT NULL OR atext IS NOT NULL)" .
            " AND ts IS NOT NULL";
        $res = $ilDB->query($query);
        return $res->numRows($res);
    }
    
    public static function getAllAssignmentFiles($a_exc_id, $a_ass_id)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        include_once("./Modules/Exercise/classes/class.ilFSStorageExercise.php");
        $storage = new ilFSStorageExercise($a_exc_id, $a_ass_id);
        $path = $storage->getAbsoluteSubmissionPath();

        include_once("./Modules/Exercise/AssignmentTypes/classes/class.ilExAssignmentTypes.php");
        $ass_type = ilExAssignmentTypes::getInstance()->getById(ilExAssignment::lookupType($a_ass_id));

        $query = "SELECT * FROM exc_returned WHERE ass_id = " .
            $ilDB->quote($a_ass_id, "integer");

        $res = $ilDB->query($query);
        while ($row = $ilDB->fetchAssoc($res)) {
            if ($ass_type->isSubmissionAssignedToTeam()) {
                $storage_id = $row["team_id"];
            } else {
                $storage_id = $row["user_id"];
            }

            $row["timestamp"] = $row["ts"];
            $row["filename"] = $path . "/" . $storage_id . "/" . basename($row["filename"]);
            $delivered[] = $row;
        }
        
        return $delivered ? $delivered : array();
    }

    public static function getAssignmentFilesByUsers(int $a_exc_id, int $a_ass_id, array $a_users) : array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $storage = new ilFSStorageExercise($a_exc_id, $a_ass_id);
        $path = $storage->getAbsoluteSubmissionPath();

        $ass_type = ilExAssignmentTypes::getInstance()->getById(ilExAssignment::lookupType($a_ass_id));

        $query = "SELECT * FROM exc_returned WHERE ass_id = " .
            $ilDB->quote($a_ass_id, "integer") .
            " AND user_id IN (" . implode(',', $a_users) . ")";

        $res = $ilDB->query($query);
        while ($row = $ilDB->fetchAssoc($res)) {
            if ($ass_type->isSubmissionAssignedToTeam()) {
                $storage_id = $row["team_id"];
            } else {
                $storage_id = $row["user_id"];
            }

            $row["timestamp"] = $row["ts"];
            $row["filename"] = $path . "/" . $storage_id . "/" . basename($row["filename"]);
            $delivered[] = $row;
        }

        return $delivered ? $delivered : array();
    }

    /**
     * Get submission items (not only files)
     * @todo this also returns non-file entries, rename this, see dev.txt.php
     * @param array|null $a_file_ids
     * @param bool $a_only_valid
     * @param null $a_min_timestamp
     * @return array
     */
    public function getFiles(array $a_file_ids = null, $a_only_valid = false, $a_min_timestamp = null)
    {
        $ilDB = $this->db;
        
        $sql = "SELECT * FROM exc_returned" .
            " WHERE ass_id = " . $ilDB->quote($this->getAssignment()->getId(), "integer");

        $sql .= " AND " . $this->getTableUserWhere(true);


        if ($a_file_ids) {
            $sql .= " AND " . $ilDB->in("returned_id", $a_file_ids, false, "integer");
        }
        
        if ($a_min_timestamp) {
            $sql .= " AND ts > " . $ilDB->quote($a_min_timestamp, "timestamp");
        }
        
        $result = $ilDB->query($sql);
        
        $delivered_files = array();
        if ($ilDB->numRows($result)) {
            $path = $this->initStorage()->getAbsoluteSubmissionPath();
        
            while ($row = $ilDB->fetchAssoc($result)) {
                // blog/portfolio/text submissions
                if ($a_only_valid &&
                    !$row["filename"] &&
                    !(trim($row["atext"]))) {
                    continue;
                }
                
                $row["owner_id"] = $row["user_id"];
                $row["timestamp"] = $row["ts"];
                $row["timestamp14"] = substr($row["ts"], 0, 4) .
                    substr($row["ts"], 5, 2) . substr($row["ts"], 8, 2) .
                    substr($row["ts"], 11, 2) . substr($row["ts"], 14, 2) .
                    substr($row["ts"], 17, 2);

                if ($this->getAssignment()->getAssignmentType()->isSubmissionAssignedToTeam()) {
                    $storage_id = $row["team_id"];
                } else {
                    $storage_id = $row["user_id"];
                }


                $row["filename"] = $path .
                    "/" . $storage_id . "/" . basename($row["filename"]);

                // see 22301, 22719
                if (is_file($row["filename"]) || (!$this->assignment->getAssignmentType()->usesFileUpload())) {
                    array_push($delivered_files, $row);
                }
            }
        }
                
        return $delivered_files;
    }
        
    /**
     * Check how much files have been uploaded by the learner
     * after the last download of the tutor.
     * @param tutor integer
     * @return array
     */
    public function lookupNewFiles($a_tutor = null)
    {
        $ilDB = $this->db;
        $ilUser = $this->user;

        $tutor = ($a_tutor)
            ? $a_tutor
            : $ilUser->getId();

        $where = " AND " . $this->getTableUserWhere(true);

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
     *
     * @param int $a_returned_id
     * @return int
     */
    public static function lookupExerciseIdForReturnedId($a_returned_id)
    {
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
     *
     * Used in Blog/Portfolio
     *
     * @param int $a_user_id
     * @param string $a_filetitle
     */
    public static function findUserFiles($a_user_id, $a_filetitle)
    {
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
    
    public function deleteAllFiles()
    {
        $files = array();
        foreach ($this->getFiles() as $item) {
            $files[] = $item["returned_id"];
        }
        if (sizeof($files)) {
            $this->deleteSelectedFiles($files);
        }
    }

    /**
    * Deletes already delivered files
    * @param array $file_id_array An array containing database ids of the delivered files
    * @param numeric $user_id The database id of the user
    * @access	public
    */
    public function deleteSelectedFiles(array $file_id_array)
    {
        $ilDB = $this->db;


        $where = " AND " . $this->getTableUserWhere(true);


        if (!sizeof($file_id_array)) {
            return;
        }
        
        if (count($file_id_array)) {
            $result = $ilDB->query("SELECT * FROM exc_returned" .
                " WHERE " . $ilDB->in("returned_id", $file_id_array, false, "integer") .
                $where);
            
            if ($ilDB->numRows($result)) {
                $result_array = array();
                while ($row = $ilDB->fetchAssoc($result)) {
                    $row["timestamp"] = $row["ts"];
                    array_push($result_array, $row);
                }
                
                // delete the entries in the database
                $ilDB->manipulate("DELETE FROM exc_returned" .
                    " WHERE " . $ilDB->in("returned_id", $file_id_array, false, "integer") .
                    $where);
                
                // delete the files
                $path = $this->initStorage()->getAbsoluteSubmissionPath();
                foreach ($result_array as $key => $value) {
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
                        unlink($filename);
                    }
                }
            }
        }
    }
    
    /**
     * Delete all delivered files of user
     *
     * @param int $a_exc_id excercise id
     * @param int $a_user_id user id
     */
    public static function deleteUser($a_exc_id, $a_user_id)
    {
        include_once("./Modules/Exercise/classes/class.ilExAssignment.php");
        
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
        }
    }
    
    protected function getLastDownloadTime(array $a_user_ids)
    {
        $ilDB = $this->db;
        $ilUser = $this->user;
    
        $q = "SELECT download_time FROM exc_usr_tutor WHERE " .
            " ass_id = " . $ilDB->quote($this->getAssignment()->getId(), "integer") . " AND " .
            $ilDB->in("usr_id", $a_user_ids, "", "integer") . " AND " .
            " tutor_id = " . $ilDB->quote($ilUser->getId(), "integer");
        $lu_set = $ilDB->query($q);
        $lu_rec = $ilDB->fetchAssoc($lu_set);
        return $lu_rec["download_time"];
    }
    
    public function downloadFiles(array $a_file_ids = null, $a_only_new = false, $a_peer_review_mask_filename = false)
    {
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
    
        $files = $this->getFiles($a_file_ids, false, $download_time);
        if ($files) {
            if (sizeof($files) == 1) {
                $file = array_pop($files);

                switch ($this->assignment->getType()) {
                    case ilExAssignment::TYPE_BLOG:
                    case ilExAssignment::TYPE_PORTFOLIO:
                        $file["filetitle"] = ilObjUser::_lookupName($file["user_id"]);
                        $file["filetitle"] = ilObject::_lookupTitle($this->assignment->getExerciseId()) . " - " .
                            $this->assignment->getTitle() . " - " .
                            $file["filetitle"]["firstname"] . " " .
                            $file["filetitle"]["lastname"] . " (" .
                            $file["filetitle"]["login"] . ").zip";
                        break;

                    // @todo: generalize
                    case ilExAssignment::TYPE_WIKI_TEAM:
                        $file["filetitle"] = ilObject::_lookupTitle($this->assignment->getExerciseId()) . " - " .
                            $this->assignment->getTitle() . " (Team " . $this->getTeam()->getId() . ").zip";
                        break;

                    default:
                        break;
                }

                if ($a_peer_review_mask_filename) {
                    $suffix = array_pop(explode(".", $file["filetitle"]));
                    $file["filetitle"] = $this->assignment->getTitle() . "_peer" . $peer_id . "." . $suffix;
                } elseif ($file["late"]) {
                    $file["filetitle"] = $lng->txt("exc_late_submission") . " - " .
                        $file["filetitle"];
                }

                $this->downloadSingleFile($file["user_id"], $file["filename"], $file["filetitle"], $file["team_id"]);
            } else {
                $array_files = array();
                foreach ($files as $seq => $file) {
                    if ($this->assignment->getAssignmentType()->isSubmissionAssignedToTeam()) {
                        $storage_id = $file["team_id"];
                    } else {
                        $storage_id = $file["user_id"];
                    }

                    $src = basename($file["filename"]);
                    if ($a_peer_review_mask_filename) {
                        $suffix = array_pop(explode(".", $src));
                        $tgt = $this->assignment->getTitle() . "_peer" . $peer_id .
                            "_" . (++$seq) . "." . $suffix;
                        
                        $array_files[$storage_id][] = array(
                            "src" => $src,
                            "tgt" => $tgt
                        );
                    } else {
                        $array_files[$storage_id][] = array(
                            "src" => $src,
                            "late" => $file["late"]
                        );
                    }
                }
                                
                $this->downloadMultipleFiles(
                    $array_files,
                    ($is_team ? null : $this->getUserId()),
                    $is_team
                );
            }
        } else {
            return false;
        }

        return true;
    }

    // Update the timestamp of the last download of current user (=tutor)
    public function updateTutorDownloadTime()
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

    protected function downloadSingleFile($a_user_id, $filename, $filetitle, $a_team_id)
    {
        if ($this->ass_type->isSubmissionAssignedToTeam()) {
            $storage_id = $a_team_id;
        } else {
            $storage_id = $a_user_id;
        }

        $filename = $this->initStorage()->getAbsoluteSubmissionPath() .
            "/" . $storage_id . "/" . basename($filename);

        ilUtil::deliverFile($filename, $filetitle);
    }

    protected function downloadMultipleFiles($a_filenames, $a_user_id, $a_multi_user = false)
    {
        $lng = $this->lng;
        
        $path = $this->initStorage()->getAbsoluteSubmissionPath();
        
        $cdir = getcwd();

        $zip = PATH_TO_ZIP;
        $tmpdir = ilUtil::ilTempnam();
        $tmpfile = ilUtil::ilTempnam();
        $tmpzipfile = $tmpfile . ".zip";

        ilUtil::makeDir($tmpdir);
        chdir($tmpdir);

        $assTitle = ilExAssignment::lookupTitle($this->assignment->getId());
        $deliverFilename = str_replace(" ", "_", $assTitle);
        if ($a_user_id > 0 && !$a_multi_user) {
            $userName = ilObjUser::_lookupName($a_user_id);
            $deliverFilename .= "_" . $userName["lastname"] . "_" . $userName["firstname"];
        } else {
            $deliverFilename .= "_files";
        }
        $orgDeliverFilename = trim($deliverFilename);
        $deliverFilename = ilUtil::getASCIIFilename($orgDeliverFilename);
        ilUtil::makeDir($tmpdir . "/" . $deliverFilename);
        chdir($tmpdir . "/" . $deliverFilename);
            
        //copy all files to a temporary directory and remove them afterwards
        $parsed_files = $duplicates = array();
        foreach ($a_filenames as $storage_id => $files) {
            $pathname = $path . "/" . $storage_id;

            foreach ($files as $filename) {
                // peer review masked filenames, see deliverReturnedFiles()
                if (isset($filename["tgt"])) {
                    $newFilename = $filename["tgt"];
                    $filename = $filename["src"];
                } else {
                    $late = $filename["late"];
                    $filename = $filename["src"];
                    
                    // remove timestamp
                    $newFilename = trim($filename);
                    $pos = strpos($newFilename, "_");
                    if ($pos !== false) {
                        $newFilename = substr($newFilename, $pos + 1);
                    }
                    // #11070
                    $chkName = strtolower($newFilename);
                    if (array_key_exists($chkName, $duplicates)) {
                        $suffix = strrpos($newFilename, ".");
                        $newFilename = substr($newFilename, 0, $suffix) .
                            " (" . (++$duplicates[$chkName]) . ")" .
                            substr($newFilename, $suffix);
                    } else {
                        $duplicates[$chkName] = 1;
                    }
                    
                    if ($late) {
                        $newFilename = $lng->txt("exc_late_submission") . " - " .
                            $newFilename;
                    }
                }
                
                $newFilename = ilUtil::getASCIIFilename($newFilename);
                $newFilename = $tmpdir . DIRECTORY_SEPARATOR . $deliverFilename . DIRECTORY_SEPARATOR . $newFilename;
                // copy to temporal directory
                $oldFilename = $pathname . DIRECTORY_SEPARATOR . $filename;
                if (!copy($oldFilename, $newFilename)) {
                    echo 'Could not copy ' . $oldFilename . ' to ' . $newFilename;
                }
                touch($newFilename, filectime($oldFilename));
                $parsed_files[] = ilUtil::escapeShellArg($deliverFilename . DIRECTORY_SEPARATOR . basename($newFilename));
            }
        }
        
        chdir($tmpdir);
        $zipcmd = $zip . " " . ilUtil::escapeShellArg($tmpzipfile) . " " . join(" ", $parsed_files);

        exec($zipcmd);
        ilUtil::delDir($tmpdir);
        
        chdir($cdir);
        ilUtil::deliverFile($tmpzipfile, $orgDeliverFilename . ".zip", "", false, true);
        exit;
    }

    /**
     * Download all submitted files of an assignment (all user)
     * @param $a_ass ilExAssignment
     * @param	$members		array of user names, key is user id
     * @param $to_path string
     * @throws ilExerciseException
     * @return void
     */
    public static function downloadAllAssignmentFiles(ilExAssignment $a_ass, array $members, $to_path)
    {
        global $DIC;

        $lng = $DIC->language();
        
        include_once("./Modules/Exercise/classes/class.ilFSStorageExercise.php");
        
        $storage = new ilFSStorageExercise($a_ass->getExerciseId(), $a_ass->getId());
        $storage->create();
        
        ksort($members);
        //$savepath = $this->getExercisePath() . "/" . $this->obj_id . "/";
        $savepath = $storage->getAbsoluteSubmissionPath();
        $cdir = getcwd();


        // important check: if the directory does not exist
        // ILIAS stays in the current directory (echoing only a warning)
        // and the zip command below archives the whole ILIAS directory
        // (including the data directory) and sends a mega file to the user :-o
        if (!is_dir($savepath)) {
            return;
        }
        // Safe mode fix
        //		chdir($this->getExercisePath());

        $tmpdir = $storage->getTempPath();
        chdir($tmpdir);
        $zip = PATH_TO_ZIP;

        // check free diskspace
        $dirsize = 0;
        foreach (array_keys($members) as $id) {
            $directory = $savepath . DIRECTORY_SEPARATOR . $id;
            $dirsize += ilUtil::dirsize($directory);
        }
        if ($dirsize > disk_free_space($tmpdir)) {
            return -1;
        }
        
        $ass_type = $a_ass->getType();

        // copy all member directories to the temporary folder
        // switch from id to member name and append the login if the member name is double
        // ensure that no illegal filenames will be created
        // remove timestamp from filename
        if ($a_ass->hasTeam()) {
            $team_dirs = array();
            $team_map = ilExAssignmentTeam::getAssignmentTeamMap($a_ass->getId());
        }
        foreach ($members as $id => $item) {
            $user = $item["name"];
            $user_files = $item["files"];
            $sourcedir = $savepath . DIRECTORY_SEPARATOR . $id;
            if (!is_dir($sourcedir)) {
                continue;
            }

            // group by teams
            $team_dir = "";
            if (is_array($team_map) &&
                array_key_exists($id, $team_map)) {
                $team_id = $team_map[$id];
                if (!array_key_exists($team_id, $team_dirs)) {
                    $team_dir = $lng->txt("exc_team") . " " . $team_id;
                    ilUtil::makeDir($team_dir);
                    $team_dirs[$team_id] = $team_dir;
                }
                $team_dir = $team_dirs[$team_id] . DIRECTORY_SEPARATOR;
            }

            if ($a_ass->getAssignmentType()->isSubmissionAssignedToTeam()) {
                $targetdir = $team_dir . ilUtil::getASCIIFilename(
                    $item["name"]
                );
                if ($targetdir == "") {
                    continue;
                }
            } else {
                $targetdir = self::getDirectoryNameFromUserData($id);
                if ($a_ass->getAssignmentType()->usesTeams()) {
                    $targetdir = $team_dir . $targetdir;
                }
            }
            ilUtil::makeDir($targetdir);
                        
            $sourcefiles = scandir($sourcedir);
            $duplicates = array();
            foreach ($sourcefiles as $sourcefile) {
                if ($sourcefile == "." || $sourcefile == "..") {
                    continue;
                }
                
                $targetfile = trim(basename($sourcefile));
                $pos = strpos($targetfile, "_");
                if ($pos !== false) {
                    $targetfile = substr($targetfile, $pos + 1);
                }

                if ($a_ass->getAssignmentType()->getSubmissionType() == self::TYPE_REPO_OBJECT) {
                    $obj_id = ilObject::_lookupObjId($targetfile);
                    $obj_type = ilObject::_lookupType($obj_id);
                    $targetfile = $obj_type . "_" . $obj_id . ".zip";
                }

                
                // #14536
                if (array_key_exists($targetfile, $duplicates)) {
                    $suffix = strrpos($targetfile, ".");
                    $targetfile = substr($targetfile, 0, $suffix) .
                        " (" . (++$duplicates[$targetfile]) . ")" .
                        substr($targetfile, $suffix);
                } else {
                    $duplicates[$targetfile] = 1;
                }
                
                // late submission?
                if (is_array($user_files)) {	// see #23900
                    foreach ($user_files as $file) {
                        if (basename($file["filename"]) == $sourcefile) {
                            if ($file["late"]) {
                                $targetfile = $lng->txt("exc_late_submission") . " - " .
                                    $targetfile;
                            }
                            break;
                        }
                    }
                }
                
                $targetfile = ilUtil::getASCIIFilename($targetfile);
                $targetfile = $targetdir . DIRECTORY_SEPARATOR . $targetfile;
                $sourcefile = $sourcedir . DIRECTORY_SEPARATOR . $sourcefile;

                if (!copy($sourcefile, $targetfile)) {
                    include_once "Modules/Exercise/exceptions/class.ilExerciseException.php";
                    throw new ilExerciseException("Could not copy " . basename($sourcefile) . " to '" . $targetfile . "'.");
                } else {
                    // preserve time stamp
                    touch($targetfile, filectime($sourcefile));
                    
                    // blogs and portfolios are stored as zip and have to be unzipped
                    if ($ass_type == ilExAssignment::TYPE_PORTFOLIO ||
                        $ass_type == ilExAssignment::TYPE_BLOG) {
                        ilUtil::unzip($targetfile);
                        unlink($targetfile);
                    }
                }
            }
        }
        $tmpzipfile = ilUtil::getASCIIFilename($lng->txt("exc_ass_submission_zip")) . ".zip";
        // Safe mode fix
        $zipcmd = $zip . " -r " . ilUtil::escapeShellArg($tmpzipfile) . " .";
        exec($zipcmd);
        //$path_final_zip_file = $to_path.DIRECTORY_SEPARATOR."Submissions/".$tmpzipfile;
        $path_final_zip_file = $to_path . DIRECTORY_SEPARATOR . $tmpzipfile;

        if (file_exists($tmpdir . DIRECTORY_SEPARATOR . $tmpzipfile)) {
            copy($tmpzipfile, $path_final_zip_file);
            ilUtil::delDir($tmpdir);

            //unzip the submissions zip file.(decided to unzip to allow the excel link the files more obvious when blog/portfolio)
            chdir($to_path);
            //TODO Bug in ilUtil -> if flat unzip fails. We can get rid of creating Submissions directory
            //ilUtil::unzip($path_final_zip_file,FALSE, TRUE);
            ilUtil::unzip($path_final_zip_file);
            unlink($path_final_zip_file);
        }

        chdir($cdir);
    }


    /**
     * Get user/team where clause
     *
     * @param
     * @return
     */
    protected function getTableUserWhere($a_team_mode = false)
    {
        $ilDB = $this->db;

        if ($this->getAssignment()->getAssignmentType()->isSubmissionAssignedToTeam()) {
            $team_id = $this->getTeam()->getId();
            $where = " team_id = " . $ilDB->quote($team_id, "integer") . " ";
        } else {
            if ($a_team_mode) {
                $where = " " . $ilDB->in("user_id", $this->getUserIds(), "", "integer") . " ";
            } else {
                $where = " user_id = " . $ilDB->quote($this->getUserId(), "integer");
            }
        }
        return $where;
    }


    /**
     * Get the date of the last submission of a user for the assignment
     *
     * @return	mixed	false or mysql timestamp of last submission
     */
    public function getLastSubmission()
    {
        $ilDB = $this->db;
    
        $ilDB->setLimit(1);

        $q = "SELECT obj_id,user_id,ts FROM exc_returned" .
            " WHERE ass_id = " . $ilDB->quote($this->assignment->getId(), "integer") .
            " AND " . $this->getTableUserWhere(true) .
            " AND (filename IS NOT NULL OR atext IS NOT NULL)" .
            " AND ts IS NOT NULL" .
            " ORDER BY ts DESC";
        $usr_set = $ilDB->query($q);
        $array = $ilDB->fetchAssoc($usr_set);
        return ilUtil::getMySQLTimestamp($array["ts"]);
    }

    
    //
    // OBJECTS
    //
    
    /**
     * Add personal resource or repository object (ref_id) to assigment
     *
     * @param int $a_wsp_id
     * @param string $a_text
     * @return int
     * @throws ilExerciseException
     */
    public function addResourceObject($a_wsp_id, $a_text = null)
    {
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
            if (count($subs) > 0) {
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
    
    /**
     * Remove personal resource to assigment
     *
     * @param int $a_returned_id
     */
    public function deleteResourceObject($a_returned_id)
    {
        $ilDB = $this->db;
        
        $ilDB->manipulate("DELETE FROM exc_returned" .
            " WHERE obj_id = " . $ilDB->quote($this->assignment->getExerciseId(), "integer") .
            " AND " . $this->getTableUserWhere(false) .
            " AND ass_id = " . $ilDB->quote($this->assignment->getId(), "integer") .
            " AND returned_id = " . $ilDB->quote($a_returned_id, "integer"));
    }
    
    /**
     * Handle text assignment submissions
     *
     * @param string $a_text
     * @return int
     */
    public function updateTextSubmission($a_text)
    {
        $ilDB = $this->db;
        
        $files = $this->getFiles();
        
        // no text = remove submission
        if (!trim($a_text)) {
            $this->deleteAllFiles();
            return;
        }
                
        if (!$files) {
            return $this->addResourceObject("TEXT", $a_text);
        } else {
            $files = array_shift($files);
            $id = $files["returned_id"];
            if ($id) {
                $ilDB->manipulate("UPDATE exc_returned" .
                    " SET atext = " . $ilDB->quote($a_text, "text") .
                    ", ts = " . $ilDB->quote(ilUtil::now(), "timestamp") .
                    ", late = " . $ilDB->quote($this->isLate(), "integer") .
                    " WHERE returned_id = " . $ilDB->quote($id, "integer"));
                return $id;
            }
        }
    }
    
    
    //
    // GUI helper
    //
    
    // :TODO:
    
    public function getDownloadedFilesInfoForTableGUIS($a_parent_obj, $a_parent_cmd = null)
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
        /* #13741 - status_time has been reduced to grading (mark/status)
        if (self::lookupUpdatedSubmission($a_ass_id, $a_user_id) == 1)
        {
            $last_sub = "<b>".$last_sub."</b>";
        }
        */
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
                $all_files = $this->getFiles();
                $late_files = 0;
                foreach ($all_files as $file) {
                    if ($file["late"]) {
                        $late_files++;
                    }
                }
                
                // nr of submitted files
                $result["files"]["txt"] = $lng->txt("exc_files_returned");
                if ($late_files) {
                    $result["files"]["txt"] .= ' - <span class="warning">' . $lng->txt("exc_late_submission") . " (" . $late_files . ")</span>";
                }
                $sub_cnt = count($all_files);
                $new = $this->lookupNewFiles();
                if (count($new) > 0) {
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
                    if (count($new) > 0) {
                        $result["files"]["download_new_url"] =
                            $ilCtrl->getLinkTargetByClass("ilexsubmissionfilegui", "downloadNewReturned");
                        
                        $result["files"]["download_new_txt"] = $lng->txt("exc_tbl_action_download_new_files");
                    }
                }
                break;
                
            case ilExAssignment::TYPE_BLOG:
                $result["files"]["txt"] = $lng->txt("exc_blog_returned");
                $blogs = $this->getFiles();
                if ($blogs) {
                    $blogs = array_pop($blogs);
                    if ($blogs && substr($blogs["filename"], -1) != "/") {
                        if ($blogs["late"]) {
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
                $portfolios = $this->getFiles();
                if ($portfolios) {
                    $portfolios = array_pop($portfolios);
                    if ($portfolios && substr($portfolios["filename"], -1) != "/") {
                        if ($portfolios["late"]) {
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
                $files = $this->getFiles();
                if ($files) {
                    $result["files"]["count"] = 1;
                    
                    $files = array_shift($files);
                    if (trim($files["atext"])) {
                        if ($files["late"]) {
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
                $objs = $this->getFiles();
                if ($objs) {
                    $objs = array_pop($objs);
                    if ($objs && substr($objs["filename"], -1) != "/") {
                        if ($objs["late"]) {
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
     *
     * @param string $a_filename
     * @param int[] $a_assignment_types
     * @return array
     */
    public static function getSubmissionsForFilename($a_filename, $a_assignment_types = array())
    {
        global $DIC;

        $db = $DIC->database();

        $query = "SELECT * FROM exc_returned r LEFT JOIN exc_assignment a" .
            " ON (r.ass_id = a.id) " .
            " WHERE r.filetitle = " . $db->quote($a_filename, "string");

        if (is_array($a_assignment_types) && count($a_assignment_types) > 0) {
            $query .= " AND " . $db->in("a.type", $a_assignment_types, false, "integer");
        }

        $set = $db->query($query);
        $rets = array();
        while ($rec = $db->fetchAssoc($set)) {
            $rets[] = $rec;
        }


        return $rets;
    }
    
    /*
     * @param $a_user_id
     * @return string
     */
    public static function getDirectoryNameFromUserData($a_user_id)
    {
        $userName = ilObjUser::_lookupName($a_user_id);
        $targetdir = ilUtil::getASCIIFilename(
            trim($userName["lastname"]) . "_" .
            trim($userName["firstname"]) . "_" .
            trim($userName["login"]) . "_" .
            $userName["user_id"]
        );

        return $targetdir;
    }

    /**
     * @param $a_exercise_id
     * @param $a_ass_id
     * @return array
     */
    public static function getAssignmentParticipants(int $a_exercise_id, int $a_ass_id) : array
    {
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
}
