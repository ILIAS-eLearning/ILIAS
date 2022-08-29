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

use ILIAS\Filesystem\Filesystem;
use ILIAS\Filesystem\Exception\DirectoryNotFoundException;
use ILIAS\Filesystem\Exception\FileAlreadyExistsException;
use ILIAS\Filesystem\Exception\FileNotFoundException;
use ILIAS\Filesystem\Exception\IOException;

use ILIAS\Exercise\InternalService;
use ILIAS\Exercise\Assignment\Mandatory\MandatoryAssignmentsManager;

/**
 * Class ilObjExercise
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @author Michael Jansen <mjansen@databay.de>
 * @author Alexander Killing <killing@leifos.de>
 */
class ilObjExercise extends ilObject
{
    public const TUTOR_FEEDBACK_MAIL = 1;
    public const TUTOR_FEEDBACK_TEXT = 2;
    public const TUTOR_FEEDBACK_FILE = 4;

    public const PASS_MODE_NR = "nr";
    public const PASS_MODE_ALL = "all";
    public const PASS_MODE_RANDOM = "random";

    protected ilObjUser $user;
    protected ilFileDataMail $file_obj;
    public ?ilExerciseMembers $members_obj = null;
    protected int $timestamp = 0;
    protected int  $hour = 0;
    protected int  $minutes = 0;
    protected int  $day = 0;
    protected int  $month = 0;
    protected int  $year = 0;
    protected string  $instruction = "";
    protected int $certificate_visibility = 0;
    protected int $tutor_feedback = 7; // [int]
    protected int $nr_random_mand = 0; // number of mandatory assignments in random pass mode
    protected bool $completion_by_submission = false; // completion by submission is enabled or not
    protected Filesystem $webFilesystem;
    protected MandatoryAssignmentsManager $mandatory_manager;
    protected int $pass_nr = 0;
    protected InternalService $service;
    protected string $pass_mode = self::PASS_MODE_ALL;
    protected bool $show_submissions = false;

    /**
     * @throws ilExcUnknownAssignmentTypeException
     */
    public function __construct(int $a_id = 0, bool $a_call_by_reference = true)
    {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;

        $this->db = $DIC->database();
        $this->app_event_handler = $DIC["ilAppEventHandler"];
        $this->lng = $DIC->language();
        $this->user = $DIC->user();
        $this->setPassMode("all");
        $this->type = "exc";
        $this->webFilesystem = $DIC->filesystem()->web();
        $this->service = $DIC->exercise()->internal();

        parent::__construct($a_id, $a_call_by_reference);
        $this->mandatory_manager = $this->service->domain()->assignment()->mandatoryAssignments($this);
    }

    /**
     * @throws ilExcUnknownAssignmentTypeException
     */
    public function setId(int $a_id): void
    {
        parent::setId($a_id);
        // this is needed, since e.g. ilObjectFactory initialises the object with id 0 and later sets the id
        $this->mandatory_manager = $this->service->domain()->assignment()->mandatoryAssignments($this);
    }

    public function setDate(
        int $a_hour,
        int $a_minutes,
        int $a_day,
        int $a_month,
        int $a_year
    ): void {
        $this->hour = $a_hour;
        $this->minutes = $a_minutes;
        $this->day = $a_day;
        $this->month = $a_month;
        $this->year = $a_year;
        $this->timestamp = mktime($this->hour, $this->minutes, 0, $this->month, $this->day, $this->year);
    }

    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    public function setTimestamp(
        int $a_timestamp
    ): void {
        $this->timestamp = $a_timestamp;
    }

    public function setInstruction(
        string $a_instruction
    ): void {
        $this->instruction = $a_instruction;
    }

    public function getInstruction(): string
    {
        return $this->instruction;
    }

    /**
     * @param string $a_val (self::PASS_MODE_NR, self::PASS_MODE_ALL, self::PASS_MODE_RANDOM)
     */
    public function setPassMode(string $a_val): void
    {
        $this->pass_mode = $a_val;
    }

    public function getPassMode(): string
    {
        return $this->pass_mode;
    }

    /**
     * @param int $a_val number of assignments that must be passed to pass the exercise
     */
    public function setPassNr(int $a_val): void
    {
        $this->pass_nr = $a_val;
    }

    public function getPassNr(): int
    {
        return $this->pass_nr;
    }

    /**
     * @param bool $a_val whether submissions of learners should be shown to other learners after deadline
     */
    public function setShowSubmissions(bool $a_val): void
    {
        $this->show_submissions = $a_val;
    }

    public function getShowSubmissions(): bool
    {
        return $this->show_submissions;
    }

    /**
     * @param int $a_val number of mandatory assignments in random pass mode
     */
    public function setNrMandatoryRandom(int $a_val): void
    {
        $this->nr_random_mand = $a_val;
    }

    public function getNrMandatoryRandom(): int
    {
        return $this->nr_random_mand;
    }

    public function checkDate(): bool
    {
        return	$this->hour == (int) date("H", $this->timestamp) and
            $this->minutes == (int) date("i", $this->timestamp) and
            $this->day == (int) date("d", $this->timestamp) and
            $this->month == (int) date("m", $this->timestamp) and
            $this->year == (int) date("Y", $this->timestamp);
    }

    public function hasTutorFeedbackText(): int
    {
        return $this->tutor_feedback & self::TUTOR_FEEDBACK_TEXT;
    }

    public function hasTutorFeedbackMail(): int
    {
        return $this->tutor_feedback & self::TUTOR_FEEDBACK_MAIL;
    }

    public function hasTutorFeedbackFile(): int
    {
        return $this->tutor_feedback & self::TUTOR_FEEDBACK_FILE;
    }

    protected function getTutorFeedback(): int
    {
        return $this->tutor_feedback;
    }

    public function setTutorFeedback(int $a_value): void
    {
        $this->tutor_feedback = $a_value;
    }

    public function saveData(): void
    {
        $ilDB = $this->db;

        $ilDB->insert("exc_data", array(
            "obj_id" => array("integer", $this->getId()),
            "instruction" => array("clob", $this->getInstruction()),
            "time_stamp" => array("integer", $this->getTimestamp()),
            "pass_mode" => array("text", $this->getPassMode()),
            "nr_mandatory_random" => array("integer", $this->getNrMandatoryRandom()),
            "pass_nr" => array("text", $this->getPassNr()),
            "show_submissions" => array("integer", (int) $this->getShowSubmissions()),
            'compl_by_submission' => array('integer', (int) $this->isCompletionBySubmissionEnabled()),
            "certificate_visibility" => array("integer", $this->getCertificateVisibility()),
            "tfeedback" => array("integer", $this->getTutorFeedback())
            ));
    }

    /**
     * @throws DirectoryNotFoundException
     * @throws FileAlreadyExistsException
     * @throws FileNotFoundException
     * @throws IOException
     * @throws ilDatabaseException
     * @throws ilDateTimeException
     * @throws ilExcUnknownAssignmentTypeException
     * @throws ilException
     */
    public function cloneObject(int $a_target_id, int $a_copy_id = 0, bool $a_omit_tree = false): ?ilObject
    {
        $ilDB = $this->db;

        // Copy settings
        /** @var  $new_obj ilObjExercise */
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
        $templateRepository = new ilCertificateTemplateDatabaseRepository($ilDB);

        $cloneAction = new ilCertificateCloneAction(
            $ilDB,
            $pathFactory,
            $templateRepository,
            $this->webFilesystem,
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
     * @return bool true if all object data were removed; false if only a references were removed
     */
    public function delete(): bool
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

    /**
     * @throws ilObjectNotFoundException
     * @throws ilObjectTypeMismatchException
     */
    public function read(): void
    {
        $ilDB = $this->db;

        parent::read();

        $query = "SELECT * FROM exc_data " .
            "WHERE obj_id = " . $ilDB->quote($this->getId(), "integer");

        $res = $ilDB->query($query);
        while ($row = $ilDB->fetchObject($res)) {
            $this->setInstruction((string) $row->instruction);
            $this->setTimestamp((int) $row->time_stamp);
            $pm = ($row->pass_mode == "")
                ? "all"
                : $row->pass_mode;
            $this->setPassMode((string) $pm);
            $this->setShowSubmissions((bool) $row->show_submissions);
            if ($row->pass_mode == "nr") {
                $this->setPassNr((int) $row->pass_nr);
            }
            $this->setNrMandatoryRandom((int) $row->nr_mandatory_random);
            $this->setCompletionBySubmission($row->compl_by_submission == 1);
            $this->setCertificateVisibility((int) $row->certificate_visibility);
            $this->setTutorFeedback((int) $row->tfeedback);
        }

        $this->members_obj = new ilExerciseMembers($this);
    }

    /**
     * @throws ilExcUnknownAssignmentTypeException
     */
    public function update(): bool
    {
        $ilDB = $this->db;

        parent::update();

        $ilDB->update("exc_data", array(
            "instruction" => array("clob", $this->getInstruction()),
            "time_stamp" => array("integer", $this->getTimestamp()),
            "pass_mode" => array("text", $this->getPassMode()),
            "pass_nr" => array("integer", $this->getPassNr()),
            "nr_mandatory_random" => array("integer", $this->getNrMandatoryRandom()),
            "show_submissions" => array("integer", (int) $this->getShowSubmissions()),
            'compl_by_submission' => array('integer', (int) $this->isCompletionBySubmissionEnabled()),
            'tfeedback' => array('integer', $this->getTutorFeedback()),
            ), array(
            "obj_id" => array("integer", $this->getId())
            ));

        $this->updateAllUsersStatus();

        return true;
    }

    // send exercise per mail to members

    /**
     * @throws ilObjectNotFoundException
     * @throws ilDatabaseException
     * @throws ilExcUnknownAssignmentTypeException
     * @throws ilDateTimeException
     */
    public function sendAssignment(ilExAssignment $a_ass, array $a_members): void
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
        $mfile_obj = null;
        if ($files !== []) {
            $mfile_obj = new ilFileDataMail($GLOBALS['DIC']['ilUser']->getId());
            foreach ($files as $file) {
                $mfile_obj->copyAttachmentFile($file["fullpath"], $file["name"]);
                $file_names[] = $file["name"];
            }
        }

        // recipients
        $recipients = array();
        foreach ($a_members as $member_id) {
            /** @var $tmp_obj ilObjUser */
            $tmp_obj = ilObjectFactory::getInstanceByObjId($member_id);
            $recipients[] = $tmp_obj->getLogin();
            unset($tmp_obj);
        }
        $recipients = implode(",", $recipients);

        // send mail
        $tmp_mail_obj = new ilMail($ilUser->getId());
        $tmp_mail_obj->enqueue(
            $recipients,
            "",
            "",
            $subject,
            $body,
            $file_names
        );
        unset($tmp_mail_obj);

        // remove tmp files
        if (count($file_names) && $mfile_obj) {
            $mfile_obj->unlinkFiles($file_names);
            unset($mfile_obj);
        }

        // set recipients mail status
        foreach ($a_members as $member_id) {
            $member_status = $a_ass->getMemberStatus($member_id);
            $member_status->setSent(true);
            $member_status->update();
        }
    }

    /**
     * Determine status of user
     * @throws ilExcUnknownAssignmentTypeException
     */
    public function determinStatusOfUser(int $a_user_id = 0): array
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
        $overall_stat = "notgraded";
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

        return array(
            "overall_status" => $overall_stat,
            "failed_a_mandatory" => $failed_a_mandatory);
    }

    /**
     * Update exercise status of user
     * @throws ilExcUnknownAssignmentTypeException
     */
    public function updateUserStatus(int $a_user_id = 0): void
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
     * @throws ilExcUnknownAssignmentTypeException
     */
    public function updateAllUsersStatus(): void
    {
        if (!isset($this->members_obj)) {
            $this->members_obj = new ilExerciseMembers($this);
        }

        $mems = $this->members_obj->getMembers();
        foreach ($mems as $mem) {
            $this->updateUserStatus($mem);
        }
    }

    /**
     * Exports grades as excel
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws ilExcUnknownAssignmentTypeException
     */
    public function exportGradesExcel(): void
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
            $excel->setCell($row, $cnt++, ($cnt / 2) . " - " . $this->lng->txt("exc_tbl_status"));
            $excel->setCell($row, $cnt++, (($cnt - 1) / 2) . " - " . $this->lng->txt("exc_tbl_mark"));
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
            $mem_obj->getMembers()
        );
        $mems = [];
        foreach ((array) $filtered_members as $user_id) {
            $mems[$user_id] = ilObjUser::_lookupName($user_id);
        }
        $mems = ilArrayUtil::sortArray($mems, "lastname", "asc", false, true);

        foreach ($mems as $user_id => $d) {
            $col = 0;

            // name
            $excel->setCell($row, $col++, $d["lastname"] . ", " . $d["firstname"] . " [" . $d["login"] . "]");

            reset($ass_data);
            foreach ($ass_data as $ass) {
                $status = $ass->getMemberStatus($user_id)->getStatus();
                $mark = $ass->getMemberStatus($user_id)->getMark();
                $excel->setCell($row, $col++, $this->lng->txt("exc_" . $status));
                $excel->setCell($row, $col++, $mark);
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

        $exc_name = ilFileUtils::getASCIIFilename(preg_replace("/\s/", "_", $this->getTitle()));
        $excel->sendToClient($exc_name);
    }

    // Send feedback file notification to user
    public function sendFeedbackFileNotification(
        string $a_feedback_file,
        array $user_ids,
        int $a_ass_id,
        bool $a_is_text_feedback = false
    ): void {
        $type = $a_is_text_feedback
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

    // Checks whether completion by submission is enabled or not
    public function isCompletionBySubmissionEnabled(): bool
    {
        return $this->completion_by_submission;
    }

    // Enabled/Disable completion by submission
    public function setCompletionBySubmission(bool $bool): self
    {
        $this->completion_by_submission = $bool;

        return $this;
    }

    /**
     * @throws ilExcUnknownAssignmentTypeException
     */
    public function processExerciseStatus(
        ilExAssignment $a_ass,
        array $a_user_ids,
        bool $a_has_submitted,
        array $a_valid_submissions = null
    ): void {
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
     * Get all finished exercises for user
     * @param int $a_user_id
     * @return bool[] (exercise id => passed)
     */
    public static function _lookupFinishedUserExercises(int $a_user_id): array
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
     * @return int visibility settings (0 = always, 1 = only passed,  2 = never)
     */
    public function getCertificateVisibility(): int
    {
        return (strlen($this->certificate_visibility) !== 0) ? $this->certificate_visibility : 0;
    }

    /**
     * @param int $a_value visibility settings (0 = always, 1 = only passed,  2 = never)
     */
    public function setCertificateVisibility(int $a_value): void
    {
        $this->certificate_visibility = $a_value;
    }

    /**
     * @param int $a_value visibility settings (0 = always, 1 = only passed,  2 = never)
     */
    public function saveCertificateVisibility(
        int $a_value
    ): void {
        $ilDB = $this->db;

        $ilDB->manipulateF(
            "UPDATE exc_data SET certificate_visibility = %s WHERE obj_id = %s",
            array('integer', 'integer'),
            array($a_value, $this->getId())
        );
    }
}
