<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/** @defgroup ModulesExercise Modules/Exercise
 */

/**
* Class ilObjExercise
*
* @author Stefan Meyer <meyer@leifos.com>
* @author Michael Jansen <mjansen@databay.de>
*
* @ingroup ModulesExercise
*/
class ilObjExercise extends ilObject
{
    /**
     * @var ilObjUser
     */
    protected $user;

    public $file_obj;
    public $members_obj;
    public $files;

    public $timestamp;
    public $hour;
    public $minutes;
    public $day;
    public $month;
    public $year;
    public $instruction;
    public $certificate_visibility;
    
    public $tutor_feedback = 7; // [int]

    /**
     * @var int number of mandatory assignments in random pass mode
     */
    protected $nr_random_mand;
    
    const TUTOR_FEEDBACK_MAIL = 1;
    const TUTOR_FEEDBACK_TEXT = 2;
    const TUTOR_FEEDBACK_FILE = 4;

    const PASS_MODE_NR = "nr";
    const PASS_MODE_ALL = "all";
    const PASS_MODE_RANDOM = "random";

    /**
     *
     * Indicates whether completion by submission is enabled or not
     *
     * @var boolean
     * @access protected
     *
     */
    protected $completion_by_submission = false;

    /**
     * @var \ILIAS\Filesystem\Filesystem
     */
    private $webFilesystem;

    /**
     * @var ilExcMandatoryAssignmentManager
     */
    protected $mandatory_manager;

    /**
    * Constructor
    * @access	public
    * @param	integer	reference_id or object_id
    * @param	boolean	treat the id as reference_id (true) or object_id (false)
    */
    public function __construct($a_id = 0, $a_call_by_reference = true)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->app_event_handler = $DIC["ilAppEventHandler"];
        $this->lng = $DIC->language();
        $this->user = $DIC->user();
        $this->setPassMode("all");
        $this->type = "exc";
        $this->webFilesystem = $DIC->filesystem()->web();

        parent::__construct($a_id, $a_call_by_reference);
        $this->mandatory_manager = $DIC->exercise()->internal()->service()->getMandatoryAssignmentManager($this);
    }

    /**
     * Set id
     * @param int $id
     */
    public function setId($id)
    {
        global $DIC;
        parent::setId($id);
        // this is needed, since e.g. ilObjectFactory initialises the object with id 0 and later sets the id
        $this->mandatory_manager = $DIC->exercise()->internal()->service()->getMandatoryAssignmentManager($this);
    }

    // SET, GET METHODS
    public function setDate($a_hour, $a_minutes, $a_day, $a_month, $a_year)
    {
        $this->hour = (int) $a_hour;
        $this->minutes = (int) $a_minutes;
        $this->day = (int) $a_day;
        $this->month = (int) $a_month;
        $this->year = (int) $a_year;
        $this->timestamp = mktime($this->hour, $this->minutes, 0, $this->month, $this->day, $this->year);
        return true;
    }
    public function getTimestamp()
    {
        return $this->timestamp;
    }
    public function setTimestamp($a_timestamp)
    {
        $this->timestamp = $a_timestamp;
    }
    public function setInstruction($a_instruction)
    {
        $this->instruction = $a_instruction;
    }
    public function getInstruction()
    {
        return $this->instruction;
    }
    
    /**
     * Set pass mode (all | nr)
     *
     * @param	string		pass mode
     */
    public function setPassMode($a_val)
    {
        $this->pass_mode = $a_val;
    }
    
    /**
     * Get pass mode (all | nr)
     *
     * @return	string		pass mode
     */
    public function getPassMode()
    {
        return $this->pass_mode;
    }
    
    /**
     * Set number of assignments that must be passed to pass the exercise
     *
     * @param	integer		pass nr
     */
    public function setPassNr($a_val)
    {
        $this->pass_nr = $a_val;
    }
    
    /**
     * Get number of assignments that must be passed to pass the exercise
     *
     * @return	integer		pass nr
     */
    public function getPassNr()
    {
        return $this->pass_nr;
    }
    
    /**
     * Set whether submissions of learners should be shown to other learners after deadline
     *
     * @param	boolean		show submissions
     */
    public function setShowSubmissions($a_val)
    {
        $this->show_submissions = $a_val;
    }
    
    /**
     * Get whether submissions of learners should be shown to other learners after deadline
     *
     * @return	integer		show submissions
     */
    public function getShowSubmissions()
    {
        return $this->show_submissions;
    }
    
    /**
     * Set number of mandatory assignments in random pass mode
     *
     * @param int $a_val
     */
    public function setNrMandatoryRandom($a_val)
    {
        $this->nr_random_mand = $a_val;
    }
    
    /**
     * Get number of mandatory assignments in random pass mode
     *
     * @return int
     */
    public function getNrMandatoryRandom()
    {
        return $this->nr_random_mand;
    }
    

    /*	function getFiles()
        {
            return $this->files;
        }*/

    public function checkDate()
    {
        return	$this->hour == (int) date("H", $this->timestamp) and
            $this->minutes == (int) date("i", $this->timestamp) and
            $this->day == (int) date("d", $this->timestamp) and
            $this->month == (int) date("m", $this->timestamp) and
            $this->year == (int) date("Y", $this->timestamp);
    }

    public function hasTutorFeedbackText()
    {
        return $this->tutor_feedback & self::TUTOR_FEEDBACK_TEXT;
    }
    
    public function hasTutorFeedbackMail()
    {
        return $this->tutor_feedback & self::TUTOR_FEEDBACK_MAIL;
    }
    
    public function hasTutorFeedbackFile()
    {
        return $this->tutor_feedback & self::TUTOR_FEEDBACK_FILE;
    }
    
    protected function getTutorFeedback()
    {
        return $this->tutor_feedback;
    }
    
    public function setTutorFeedback($a_value)
    {
        $this->tutor_feedback = $a_value;
    }
    
    public function saveData()
    {
        $ilDB = $this->db;
        
        $ilDB->insert("exc_data", array(
            "obj_id" => array("integer", $this->getId()),
            "instruction" => array("clob", $this->getInstruction()),
            "time_stamp" => array("integer", $this->getTimestamp()),
            "pass_mode" => array("text", $this->getPassMode()),
            "nr_mandatory_random" => array("integer", (int) $this->getNrMandatoryRandom()),
            "pass_nr" => array("text", $this->getPassNr()),
            "show_submissions" => array("integer", (int) $this->getShowSubmissions()),
            'compl_by_submission' => array('integer', (int) $this->isCompletionBySubmissionEnabled()),
            "certificate_visibility" => array("integer", (int) $this->getCertificateVisibility()),
            "tfeedback" => array("integer", (int) $this->getTutorFeedback())
            ));
        return true;
    }
    
    /**
     * Clone exercise (no member data)
     *
     * @access public
     * @param int target ref_id
     * @param int copy id
     */
    public function cloneObject($a_target_id, $a_copy_id = 0, $a_omit_tree = false)
    {
        $ilDB = $this->db;
        
        // Copy settings
        $new_obj = parent::cloneObject($a_target_id, $a_copy_id, $a_omit_tree);
        $new_obj->setInstruction($this->getInstruction());
        $new_obj->setTimestamp($this->getTimestamp());
        $new_obj->setPassMode($this->getPassMode());
        $new_obj->setNrMandatoryRandom($this->getNrMandatoryRandom());
        $new_obj->saveData();
        $new_obj->setPassNr($this->getPassNr());
        $new_obj->setShowSubmissions($this->getShowSubmissions());
        $new_obj->setCompletionBySubmission($this->isCompletionBySubmissionEnabled());
        $new_obj->setTutorFeedback($this->getTutorFeedback());
        $new_obj->setCertificateVisibility($this->getCertificateVisibility());
        $new_obj->update();

        $new_obj->saveCertificateVisibility($this->getCertificateVisibility());
        
        // Copy criteria catalogues
        $crit_cat_map = array();
        foreach (ilExcCriteriaCatalogue::getInstancesByParentId($this->getId()) as $crit_cat) {
            $new_id = $crit_cat->cloneObject($new_obj->getId());
            $crit_cat_map[$crit_cat->getId()] = $new_id;
        }
            
        // Copy assignments
        ilExAssignment::cloneAssignmentsOfExercise($this->getId(), $new_obj->getId(), $crit_cat_map);
        
        // Copy learning progress settings
        $obj_settings = new ilLPObjSettings($this->getId());
        $obj_settings->cloneSettings($new_obj->getId());
        unset($obj_settings);

        $pathFactory = new ilCertificatePathFactory();
        $templateRepository = new ilCertificateTemplateRepository($ilDB);

        $cloneAction = new ilCertificateCloneAction(
            $ilDB,
            $pathFactory,
            $templateRepository,
            $this->webFilesystem,
            $this->log,
            new ilCertificateObjectHelper()
        );

        $cloneAction->cloneCertificate($this, $new_obj);

        // additional features
        foreach (ilContainer::_getContainerSettings($this->getId()) as $keyword => $value) {
            ilContainer::_writeContainerSetting($new_obj->getId(), $keyword, $value);
        }

        // org unit setting
        $orgu_object_settings = new ilOrgUnitObjectPositionSetting($new_obj->getId());
        $orgu_object_settings->setActive(
            (int) ilOrgUnitGlobalSettings::getInstance()->isPositionAccessActiveForObject($this->getId())
        );
        $orgu_object_settings->update();

        return $new_obj;
    }
    
    /**
    * delete course and all related data
    *
    * @access	public
    * @return	boolean	true if all object data were removed; false if only a references were removed
    */
    public function delete()
    {
        $ilDB = $this->db;
        $ilAppEventHandler = $this->app_event_handler;

        // always call parent delete function first!!
        if (!parent::delete()) {
            return false;
        }
        // put here course specific stuff
        $ilDB->manipulate("DELETE FROM exc_data " .
            "WHERE obj_id = " . $ilDB->quote($this->getId(), "integer"));

        ilExcCriteriaCatalogue::deleteByParent($this->getId());

        // remove all notifications
        ilNotification::removeForObject(ilNotification::TYPE_EXERCISE_SUBMISSION, $this->getId());
            
        $ilAppEventHandler->raise(
            'Modules/Exercise',
            'delete',
            array('obj_id' => $this->getId())
        );

        return true;
    }

    public function read()
    {
        $ilDB = $this->db;

        parent::read();

        $query = "SELECT * FROM exc_data " .
            "WHERE obj_id = " . $ilDB->quote($this->getId(), "integer");

        $res = $ilDB->query($query);
        while ($row = $ilDB->fetchObject($res)) {
            $this->setInstruction($row->instruction);
            $this->setTimestamp($row->time_stamp);
            $pm = ($row->pass_mode == "")
                ? "all"
                : $row->pass_mode;
            $this->setPassMode($pm);
            $this->setShowSubmissions($row->show_submissions);
            if ($row->pass_mode == "nr") {
                $this->setPassNr($row->pass_nr);
            }
            $this->setNrMandatoryRandom($row->nr_mandatory_random);
            $this->setCompletionBySubmission($row->compl_by_submission == 1 ? true : false);
            $this->setCertificateVisibility($row->certificate_visibility);
            $this->setTutorFeedback($row->tfeedback);
        }
        
        $this->members_obj = new ilExerciseMembers($this);

        return true;
    }

    public function update()
    {
        $ilDB = $this->db;

        parent::update();

        if ($this->getPassMode() == "all") {
            $pass_nr = null;
        } else {
            $pass_nr = $this->getPassNr();
        }

        $ilDB->update("exc_data", array(
            "instruction" => array("clob", $this->getInstruction()),
            "time_stamp" => array("integer", $this->getTimestamp()),
            "pass_mode" => array("text", $this->getPassMode()),
            "pass_nr" => array("integer", $this->getPassNr()),
            "nr_mandatory_random" => array("integer", (int) $this->getNrMandatoryRandom()),
            "show_submissions" => array("integer", (int) $this->getShowSubmissions()),
            'compl_by_submission' => array('integer', (int) $this->isCompletionBySubmissionEnabled()),
            'tfeedback' => array('integer', (int) $this->getTutorFeedback()),
            ), array(
            "obj_id" => array("integer", $this->getId())
            ));

        $this->updateAllUsersStatus();
        
        return true;
    }

    /**
     * send exercise per mail to members
     */
    public function sendAssignment(ilExAssignment $a_ass, $a_members)
    {
        $lng = $this->lng;
        $ilUser = $this->user;
        
        $lng->loadLanguageModule("exc");
        
        // subject
        $subject = $a_ass->getTitle()
            ? $this->getTitle() . ": " . $a_ass->getTitle()
            : $this->getTitle();
        
        
        // body
        
        $body = $a_ass->getInstruction();
        $body .= "\n\n";
        
        $body .= $lng->txt("exc_edit_until") . ": ";
        $body .= (!$a_ass->getDeadline())
          ? $lng->txt("exc_no_deadline_specified")
          : ilDatePresentation::formatDate(new ilDateTime($a_ass->getDeadline(), IL_CAL_UNIX));
        $body .= "\n\n";
        
        $body .= ilLink::_getLink($this->getRefId(), "exc");
        

        // files
        $file_names = array();
        $storage = new ilFSStorageExercise($a_ass->getExerciseId(), $a_ass->getId());
        $files = $storage->getFiles();
        if (count($files)) {
            $mfile_obj = new ilFileDataMail($GLOBALS['DIC']['ilUser']->getId());
            foreach ($files as $file) {
                $mfile_obj->copyAttachmentFile($file["fullpath"], $file["name"]);
                $file_names[] = $file["name"];
            }
        }
        
        // recipients
        $recipients = array();
        foreach ($a_members as $member_id) {
            $tmp_obj = ilObjectFactory::getInstanceByObjId($member_id);
            $recipients[] = $tmp_obj->getLogin();
            unset($tmp_obj);
        }
        $recipients = implode(",", $recipients);
    
        // send mail
        $tmp_mail_obj = new ilMail($ilUser->getId());
        $errors = $tmp_mail_obj->enqueue(
            $recipients,
            "",
            "",
            $subject,
            $body,
            $file_names
        );
        unset($tmp_mail_obj);

        // remove tmp files
        if (sizeof($file_names)) {
            $mfile_obj->unlinkFiles($file_names);
            unset($mfile_obj);
        }

        // set recipients mail status
        foreach ($a_members as $member_id) {
            $member_status = $a_ass->getMemberStatus($member_id);
            $member_status->setSent(true);
            $member_status->update();
        }

        return true;
    }

    /**
     * Determine status of user
     */
    public function determinStatusOfUser($a_user_id = 0)
    {
        $ilUser = $this->user;

        $mandatory_manager = $this->mandatory_manager;

        if ($a_user_id == 0) {
            $a_user_id = $ilUser->getId();
        }
        
        $ass = ilExAssignment::getInstancesByExercise($this->getId());
        
        $passed_all_mandatory = true;
        $failed_a_mandatory = false;
        $cnt_passed = 0;
        $cnt_notgraded = 0;

        /** @var ilExAssignment $a */
        foreach ($ass as $a) {
            $stat = $a->getMemberStatus($a_user_id)->getStatus();
            $mandatory = $mandatory_manager->isMandatoryForUser($a->getId(), $a_user_id);
            if ($mandatory && ($stat == "failed" || $stat == "notgraded")) {
                $passed_all_mandatory = false;
            }
            if ($mandatory && ($stat == "failed")) {
                $failed_a_mandatory = true;
            }
            if ($stat == "passed") {
                $cnt_passed++;
            }
            if ($stat == "notgraded") {
                $cnt_notgraded++;
            }
        }
        
        if (count($ass) == 0) {
            $passed_all_mandatory = false;
        }
        
        if ($this->getPassMode() == self::PASS_MODE_ALL) {
            $overall_stat = "notgraded";
            if ($failed_a_mandatory) {
                $overall_stat = "failed";
            } elseif ($passed_all_mandatory && $cnt_passed > 0) {
                $overall_stat = "passed";
            }
        } elseif ($this->getPassMode() == self::PASS_MODE_NR) {
            $min_nr = $this->getPassNr();
            $overall_stat = "notgraded";
            if ($failed_a_mandatory || ($cnt_passed + $cnt_notgraded < $min_nr)) {
                $overall_stat = "failed";
            } elseif ($passed_all_mandatory && $cnt_passed >= $min_nr) {
                $overall_stat = "passed";
            }
        } elseif ($this->getPassMode() == self::PASS_MODE_RANDOM) {
            $overall_stat = "notgraded";
            if ($failed_a_mandatory) {
                $overall_stat = "failed";
            } elseif ($passed_all_mandatory && $cnt_passed > 0) {
                $overall_stat = "passed";
            }
        }

        $ret = array(
            "overall_status" => $overall_stat,
            "failed_a_mandatory" => $failed_a_mandatory);
        //echo "<br>p:".$cnt_passed.":ng:".$cnt_notgraded;
        //var_dump($ret); exit;
        return $ret;
    }
    
    /**
     * Update exercise status of user
     */
    public function updateUserStatus($a_user_id = 0)
    {
        $ilUser = $this->user;
        
        if ($a_user_id == 0) {
            $a_user_id = $ilUser->getId();
        }

        $st = $this->determinStatusOfUser($a_user_id);

        ilExerciseMembers::_writeStatus(
            $this->getId(),
            $a_user_id,
            $st["overall_status"]
        );
    }
    
    /**
     * Update status of all users
     */
    public function updateAllUsersStatus()
    {
        if (!is_object($this->members_obj)) {
            $this->members_obj = new ilExerciseMembers($this);
        }
        
        $mems = $this->members_obj->getMembers();
        foreach ($mems as $mem) {
            $this->updateUserStatus($mem);
        }
    }
    
    /**
     * Exports grades as excel
     */
    public function exportGradesExcel()
    {
        $ass_data = ilExAssignment::getInstancesByExercise($this->getId());
        
        $excel = new ilExcel();
        $excel->addSheet($this->lng->txt("exc_status"));
        
        
        //
        // status
        //
        
        // header row
        $row = $cnt = 1;
        $excel->setCell($row, 0, $this->lng->txt("name"));
        foreach ($ass_data as $ass) {
            $excel->setCell($row, $cnt++, $cnt - 1);
        }
        $excel->setCell($row, $cnt++, $this->lng->txt("exc_total_exc"));
        $excel->setCell($row, $cnt++, $this->lng->txt("exc_mark"));
        $excel->setCell($row++, $cnt, $this->lng->txt("exc_comment_for_learner"));
        $excel->setBold("A1:" . $excel->getColumnCoord($cnt) . "1");
        
        // data rows
        $mem_obj = new ilExerciseMembers($this);
        
        $filtered_members = $GLOBALS['DIC']->access()->filterUserIdsByRbacOrPositionOfCurrentUser(
            'edit_submissions_grades',
            'edit_submissions_grades',
            $this->getRefId(),
            (array) $mem_obj->getMembers()
        );
        
        foreach ((array) $filtered_members as $user_id) {
            $mems[$user_id] = ilObjUser::_lookupName($user_id);
        }
        $mems = ilUtil::sortArray($mems, "lastname", "asc", false, true);
        
        foreach ($mems as $user_id => $d) {
            $col = 0;

            // name
            $excel->setCell($row, $col++, $d["lastname"] . ", " . $d["firstname"] . " [" . $d["login"] . "]");

            reset($ass_data);
            foreach ($ass_data as $ass) {
                $status = $ass->getMemberStatus($user_id)->getStatus();
                $excel->setCell($row, $col++, $this->lng->txt("exc_" . $status));
            }
            
            // total status
            $status = ilExerciseMembers::_lookupStatus($this->getId(), $user_id);
            $excel->setCell($row, $col++, $this->lng->txt("exc_" . $status));
            
            // #18096
            $marks_obj = new ilLPMarks($this->getId(), $user_id);
            $excel->setCell($row, $col++, $marks_obj->getMark());
            $excel->setCell($row++, $col, $marks_obj->getComment());
        }
        
        
        //
        // mark
        //
        
        $excel->addSheet($this->lng->txt("exc_mark"));
        
        // header row
        $row = $cnt = 1;
        $excel->setCell($row, 0, $this->lng->txt("name"));
        foreach ($ass_data as $ass) {
            $excel->setCell($row, $cnt++, $cnt - 1);
        }
        $excel->setCell($row++, $cnt++, $this->lng->txt("exc_total_exc"));
        $excel->setBold("A1:" . $excel->getColumnCoord($cnt) . "1");
        
        // data rows
        reset($mems);
        foreach ($mems as $user_id => $d) {
            $col = 0;
            
            // name
            $d = ilObjUser::_lookupName($user_id);
            $excel->setCell($row, $col++, $d["lastname"] . ", " . $d["firstname"] . " [" . $d["login"] . "]");

            reset($ass_data);
            foreach ($ass_data as $ass) {
                $excel->setCell($row, $col++, $ass->getMemberStatus($user_id)->getMark());
            }
            
            // total mark
            $excel->setCell($row++, $col, ilLPMarks::_lookupMark($user_id, $this->getId()));
        }
        
        $exc_name = ilUtil::getASCIIFilename(preg_replace("/\s/", "_", $this->getTitle()));
        $excel->sendToClient($exc_name);
    }
    
    /**
     * Send feedback file notification to user
     */
    public function sendFeedbackFileNotification($a_feedback_file, $a_user_id, $a_ass_id, $a_is_text_feedback = false)
    {
        $user_ids = $a_user_id;
        if (!is_array($user_ids)) {
            $user_ids = array($user_ids);
        }
        
        $type = (bool) $a_is_text_feedback
            ? ilExerciseMailNotification::TYPE_FEEDBACK_TEXT_ADDED
            : ilExerciseMailNotification::TYPE_FEEDBACK_FILE_ADDED;
                
        $not = new ilExerciseMailNotification();
        $not->setType($type);
        $not->setAssignmentId($a_ass_id);
        $not->setObjId($this->getId());
        if ($this->getRefId() > 0) {
            $not->setRefId($this->getRefId());
        }
        $not->setRecipients($user_ids);
        $not->send();
    }
    
    /**
     *
     * Checks whether completion by submission is enabled or not
     *
     * @return	boolean
     * @access	public
     *
     */
    public function isCompletionBySubmissionEnabled()
    {
        return $this->completion_by_submission;
    }
    
    /**
     *
     * Enabled/Disable completion by submission
     *
     * @param	boolean
     * @return	ilObjExercise
     * @access	public
     *
     */
    public function setCompletionBySubmission($bool)
    {
        $this->completion_by_submission = (bool) $bool;
        
        return $this;
    }
    
    public function processExerciseStatus(ilExAssignment $a_ass, array $a_user_ids, $a_has_submitted, array $a_valid_submissions = null)
    {
        $a_has_submitted = (bool) $a_has_submitted;
        
        foreach ($a_user_ids as $user_id) {
            $member_status = $a_ass->getMemberStatus($user_id);
            $member_status->setReturned($a_has_submitted);
            $member_status->update();
            
            ilExerciseMembers::_writeReturned($this->getId(), $user_id, $a_has_submitted);
        }
                
        // re-evaluate exercise status
        if ($this->isCompletionBySubmissionEnabled()) {
            foreach ($a_user_ids as $user_id) {
                $status = 'notgraded';
                if ($a_has_submitted) {
                    if (!is_array($a_valid_submissions) ||
                        $a_valid_submissions[$user_id]) {
                        $status = 'passed';
                    }
                }
                                    
                $member_status = $a_ass->getMemberStatus($user_id);
                $member_status->setStatus($status);
                $member_status->update();
            }
        }
    }
    
    /**
     * Get all exercises for user
     *
     * @param <type> $a_user_id
     * @return array (exercise id => passed)
     */
    public static function _lookupFinishedUserExercises($a_user_id)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->query("SELECT obj_id, status FROM exc_members" .
            " WHERE usr_id = " . $ilDB->quote($a_user_id, "integer") .
            " AND (status = " . $ilDB->quote("passed", "text") .
            " OR status = " . $ilDB->quote("failed", "text") . ")");

        $all = array();
        while ($row = $ilDB->fetchAssoc($set)) {
            $all[$row["obj_id"]] = ($row["status"] == "passed");
        }
        return $all;
    }
    

    /**
    * Returns the visibility settings of the certificate
    *
    * @return integer The value for the visibility settings (0 = always, 1 = only passed,  2 = never)
    * @access public
    */
    public function getCertificateVisibility()
    {
        return (strlen($this->certificate_visibility)) ? $this->certificate_visibility : 0;
    }

    /**
    * Sets the visibility settings of the certificate
    *
    * @param integer $a_value The value for the visibility settings (0 = always, 1 = only passed,  2 = never)
    * @access public
    */
    public function setCertificateVisibility($a_value)
    {
        $this->certificate_visibility = $a_value;
    }
    
    /**
    * Saves the visibility settings of the certificate
    *
    * @param integer $a_value The value for the visibility settings (0 = always, 1 = only passed,  2 = never)
    * @access private
    */
    public function saveCertificateVisibility($a_value)
    {
        $ilDB = $this->db;

        $affectedRows = $ilDB->manipulateF(
            "UPDATE exc_data SET certificate_visibility = %s WHERE obj_id = %s",
            array('integer', 'integer'),
            array($a_value, $this->getId())
        );
    }

    /**
     * Add to desktop after hand-in
     *
     * @return bool
     */
    public function hasAddToDesktop()
    {
        $exc_set = new ilSetting("excs");
        return (bool) $exc_set->get("add_to_pd", true);
    }
}
