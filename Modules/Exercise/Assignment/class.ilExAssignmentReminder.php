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
 *
 * TODO: import/export reminder data with the exercise/assignment.
 * TODO: Delete reminders from exc_ass_reminders when the assignment is deleted.
 *
 * Exercise Assignment Reminders
 *
 * @author Jesús López <lopez@leifos.com>
 * @author Alexander Killing <killing@leifos.de>
 */
class ilExAssignmentReminder
{
    public const SUBMIT_REMINDER = "submit";
    public const GRADE_REMINDER = "grade";
    public const FEEDBACK_REMINDER = "peer";

    protected ilDBInterface $db;
    protected ilTree $tree;

    protected ?bool $rmd_status = null;
    protected int $rmd_start = 0;
    protected int $rmd_end = 0;
    protected int $rmd_frequency = 0;
    protected int $rmd_last_send = 0;
    protected int $rmd_tpl_id = 0;

    protected int $ass_id = 0;
    protected int $exc_id = 0;
    protected string $rmd_type = "";

    protected ilLogger $log;
    protected ilAccessHandler $access;

    //todo remove the params as soon as possible.
    public function __construct(
        int $a_exc_id = 0,
        int $a_ass_id = 0,
        string $a_type = ""
    ) {
        global $DIC;
        $this->db = $DIC->database();
        $this->tree = $DIC->repositoryTree();
        $this->access = $DIC->access();
        $this->log = ilLoggerFactory::getLogger("exc");

        if ($a_ass_id) {
            $this->ass_id = $a_ass_id;
        }
        if ($a_exc_id) {
            $this->exc_id = $a_exc_id;
        }
        if ($a_type) {
            $this->rmd_type = $a_type;
        }
        if ($a_exc_id and $a_ass_id and $a_type) {
            $this->read();
        }
    }

    public function getReminderType(): string
    {
        return $this->rmd_type;
    }

    /**
     * Set reminder for users without submission.
     * @param bool $a_status activated?
     */
    public function setReminderStatus(?bool $a_status): void
    {
        $this->rmd_status = $a_status;
    }

    public function getReminderStatus(): ?bool
    {
        return $this->rmd_status;
    }

    // Set num days before the deadline to start sending notifications.
    public function setReminderStart(int $a_num_days): void
    {
        $this->rmd_start = $a_num_days;
    }

    public function getReminderStart(): int
    {
        return $this->rmd_start;
    }

    public function setReminderEnd(int $a_date): void
    {
        $this->rmd_end = $a_date;
    }

    public function getReminderEnd(): int
    {
        return $this->rmd_end;
    }

    // Set frequency in days
    public function setReminderFrequency(int $a_num_days): void
    {
        $this->rmd_frequency = $a_num_days;
    }

    public function getReminderFrequency(): int
    {
        return $this->rmd_frequency;
    }

    public function setReminderLastSend(int $a_timestamp): void
    {
        $this->rmd_last_send = $a_timestamp;
    }

    public function getReminderLastSend(): int
    {
        return $this->rmd_last_send;
    }

    public function setReminderMailTemplate(int $a_tpl_id): void
    {
        $this->rmd_tpl_id = $a_tpl_id;
    }

    public function getReminderMailTemplate(): int
    {
        return $this->rmd_tpl_id;
    }

    public function save(): void
    {
        $this->db->insert("exc_ass_reminders", array(
            "type" => array("text", $this->rmd_type),
            "ass_id" => array("integer", $this->ass_id),
            "exc_id" => array("integer", $this->exc_id),
            "status" => array("integer", $this->getReminderStatus()),
            "start" => array("integer", $this->getReminderStart()),
            "end" => array("integer", $this->getReminderEnd()),
            "freq" => array("integer", $this->getReminderFrequency()),
            "last_send" => array("integer", $this->getReminderLastSend()),
            "template_id" => array("integer", $this->getReminderMailTemplate())
        ));
    }

    public function update(): void
    {
        $this->db->update(
            "exc_ass_reminders",
            array(
            "status" => array("integer", $this->getReminderStatus()),
            "start" => array("integer", $this->getReminderStart()),
            "end" => array("integer", $this->getReminderEnd()),
            "freq" => array("integer", $this->getReminderFrequency()),
            "last_send" => array("integer", $this->getReminderLastSend()),
            "template_id" => array("integer", $this->getReminderMailTemplate())
        ),
            array(
            "type" => array("text", $this->rmd_type),
            "exc_id" => array("integer", $this->exc_id),
            "ass_id" => array("integer", $this->ass_id)
        )
        );
    }


    public function read(): void
    {
        $set = $this->db->queryF(
            "SELECT status, start, freq, end, last_send, template_id" .
            " FROM exc_ass_reminders" .
            " WHERE type = %s AND ass_id = %s AND exc_id = %s",
            ["text", "integer", "integer"],
            [$this->rmd_type, $this->ass_id, $this->exc_id]
        );

        $rec = $this->db->fetchAssoc($set);
        if (is_array($rec)) {
            $this->initFromDB($rec);
        }
    }

    protected function initFromDB(array $a_set): void
    {
        $this->setReminderStatus((bool) $a_set["status"]);
        $this->setReminderStart((int) $a_set["start"]);
        $this->setReminderEnd((int) $a_set["end"]);
        $this->setReminderFrequency((int) $a_set["freq"]);
        $this->setReminderLastSend((int) $a_set["last_send"]);
        $this->setReminderMailTemplate((int) $a_set["template_id"]);
    }


    // Specific Methods to be used via Cron Job.

    /**
     * Get reminders available by date/frequence.
     * @throws Exception
     */
    public function getReminders(string $a_type = ""): array
    {
        $now = time();
        $today = date("Y-m-d");

        $this->log->debug("Get reminders $a_type.");

        //remove time from the timestamp (86400 = 24h)
        //$now = floor($now/86400)*86400;
        $and_type = "";
        if ($a_type == self::SUBMIT_REMINDER || $a_type == self::GRADE_REMINDER || $a_type == self::FEEDBACK_REMINDER) {
            $and_type = " AND type = '" . $a_type . "'";
        }

        $query = "SELECT last_send_day, ass_id, exc_id, status, start, freq, end, type, last_send, template_id" .
            " FROM exc_ass_reminders" .
            " WHERE status = 1" .
            " AND start <= " . $now .
            " AND end > " . ($now - 86400) .
            $and_type;


        $result = $this->db->query($query);

        $array_data = array();
        while ($rec = $this->db->fetchAssoc($result)) {
            $rem = array(
                "ass_id" => $rec["ass_id"],
                "exc_id" => $rec["exc_id"],
                "start" => $rec["start"],
                "end" => $rec["end"],
                "freq" => $rec["freq"],
                "type" => $rec["type"],
                "last_send" => $rec["last_send"],
                "last_send_day" => $rec["last_send_day"],
                "template_id" => $rec["template_id"]
            );

            $end_day = date("Y-m-d", $rec["end"]);

            //frequency
            $next_send = "";
            if ($rec["last_send_day"] != "") {
                $date = new DateTime($rec["last_send_day"]);
                $date->add(new DateInterval('P' . $rec["freq"] . 'D'));
                $next_send = $date->format('Y-m-d');
            }
            $this->log->debug("ass: " . $rec["ass_id"] . ", last send: " . $rec["last_send_day"] .
                ", freq: " . $rec["freq"] . ", end_day: $end_day, today: " . $today . ", next send: $next_send");
            if ($rec["last_send_day"] == "" || $next_send <= $today) {
                if ($end_day >= $today) {
                    $this->log->debug("included");
                    $array_data[] = $rem;
                }
            }
        }

        return $array_data;
    }

    /**
     * Filter the reminders by object(crs,grp) by active status and if have members.
     * @throws ilExcUnknownAssignmentTypeException
     * @throws Exception
     */
    public function parseSubmissionReminders(array $a_reminders): array
    {
        $reminders = $a_reminders;
        $users_to_remind = array();

        foreach ($reminders as $rem) {
            $ass_id = $rem["ass_id"];
            $ass_obj = new ilExAssignment($ass_id);

            $exc_id = $rem["exc_id"];

            $exc_refs = ilObject::_getAllReferences($exc_id);
            foreach ($exc_refs as $exc_ref) {
                // check if we have an upper course
                if ($course_ref_id = $this->tree->checkForParentType($exc_ref, 'crs')) {
                    $obj = new ilObjCourse($course_ref_id);
                    $participants_class = ilCourseParticipants::class;
                    $parent_ref_id = $course_ref_id;
                    $parent_obj_type = 'crs';

                // check if we have an upper group
                } elseif ($group_ref_id = $this->tree->checkForParentType($exc_ref, 'grp')) {
                    $obj = new ilObjGroup($group_ref_id);
                    $participants_class = ilGroupParticipants::class;
                    $parent_ref_id = $group_ref_id;
                    $parent_obj_type = 'grp';
                } else {
                    continue;
                }

                // get participants
                $parent_obj_id = $obj->getId();
                /** @var ilParticipants $participants_class */
                $participants_ids = $participants_class::getInstance($parent_ref_id)->getMembers();

                foreach ($participants_ids as $member_id) {
                    $this->log->debug("submission reminder: ass: $ass_id, member: $member_id.");

                    // check read permission
                    if ($this->access->checkAccessOfUser($member_id, "read", "", $exc_ref)) {
                        $state = ilExcAssMemberState::getInstanceByIds($ass_id, $member_id);

                        $deadline_day = date("Y-m-d", $state->getOfficialDeadline());
                        $today = date("Y-m-d");
                        $date = new DateTime($deadline_day);
                        $date->sub(new DateInterval('P' . $rem["start"] . 'D'));
                        $send_from = $date->format('Y-m-d');
                        $this->log->debug("today: $today, send from: $send_from, start: " . $rem["start"] . ", submission allowed: " . $state->isSubmissionAllowed());

                        // check if user can submit and difference in days is smaller than reminder start
                        if ($state->isSubmissionAllowed() && $send_from <= $today) {
                            $submission = new ilExSubmission($ass_obj, $member_id);

                            // check if user has submitted anything
                            if (!$submission->getLastSubmission()) {
                                $member_data = array(
                                    "parent_type" => $parent_obj_type,
                                    "parent_id" => $parent_obj_id,
                                    "exc_id" => $exc_id,
                                    "exc_ref" => $exc_ref,
                                    "ass_id" => $ass_id,
                                    "member_id" => $member_id,
                                    "reminder_type" => $rem["type"],
                                    "template_id" => $rem["template_id"]
                                );
                                $users_to_remind[] = $member_data;
                            }
                        }
                    }
                }
            }
        }
        return $users_to_remind;
    }

    /**
     * @throws ilExcUnknownAssignmentTypeException
     */
    public function parseGradeReminders(array $a_reminders): array
    {
        $reminders = $a_reminders;
        $users_to_remind = array();

        $has_pending_to_grade = false;

        foreach ($reminders as $rem) {
            //$this->log->debug("---- parse grade reminder with values -> ",$rem);
            $ass_obj = new ilExAssignment($rem["ass_id"]);
            $members_data = $ass_obj->getMemberListData();

            //$this->log->debug("--- get members list data  => ",$members_data);
            foreach ($members_data as $assignment_data) {
                if ($assignment_data["status"] == ilExerciseManagementGUI::GRADE_NOT_GRADED) {
                    //at least there is one submission pending to grade.
                    $has_pending_to_grade = true;
                }
            }

            if ($has_pending_to_grade) {
                //get tutor of this exercise.
                $users = ilNotification::getNotificationsForObject(ilNotification::TYPE_EXERCISE_SUBMISSION, $rem["exc_id"]);

                foreach ($users as $user_id) {
                    $exc_refs = ilObject::_getAllReferences($rem["exc_id"]);
                    $unike_usr_id = array();
                    foreach ($exc_refs as $exc_ref) {
                        if ($this->access->checkAccessOfUser($user_id, "write", "", $exc_ref)) {
                            if (!in_array($user_id, $unike_usr_id)) {
                                $member_data = array(
                                    "exc_id" => $rem["exc_id"],
                                    "exc_ref" => $exc_ref,
                                    "ass_id" => $rem["ass_id"],
                                    "member_id" => $user_id,
                                    "reminder_type" => $rem["type"],
                                    "template_id" => $rem["template_id"]
                                );
                                $users_to_remind[] = $member_data;
                                $unike_usr_id[] = $user_id;
                            }
                        }
                    }
                }
            }
        }

        return $users_to_remind;
    }

    /**
     * @throws ilExcUnknownAssignmentTypeException
     */
    public function parsePeerReminders(array $a_reminders): array
    {
        $reminders = $a_reminders;
        $users_to_remind = array();

        $this->log->debug("Peer Reminders: " . count($a_reminders));

        foreach ($reminders as $reminder) {
            $this->log->debug("Init peer review: " . $reminder["ass_id"]);
            $pr = new ilExPeerReview(new ilExAssignment($reminder["ass_id"]));
            $pr->initPeerReviews();
            $giver_ids = array_unique(ilExPeerReview::lookupGiversWithPendingFeedback($reminder["ass_id"]));
            foreach ($giver_ids as $giver_id) {
                $state = ilExcAssMemberState::getInstanceByIds($reminder["ass_id"], $giver_id);
                $days_diff = (($state->getPeerReviewDeadline() - time()) / (60 * 60 * 24));

                if ($state->isPeerReviewAllowed() && $days_diff < $reminder["start"]) {
                    $exc_refs = ilObject::_getAllReferences($reminder["exc_id"]);
                    foreach ($exc_refs as $exc_ref) {
                        if ($this->access->checkAccessOfUser($giver_id, "read", "", $exc_ref)) {
                            $member_data = array(
                                "exc_id" => $reminder["exc_id"],
                                "exc_ref" => $exc_ref,
                                "ass_id" => $reminder["ass_id"],
                                "member_id" => $giver_id,
                                "reminder_type" => $reminder["type"],
                                "template_id" => $reminder["template_id"]
                            );
                            $users_to_remind[] = $member_data;
                        }
                    }
                }
            }
        }

        return $users_to_remind;
    }

    /**
     * @return int number of reminders sent
     * @throws ilExcUnknownAssignmentTypeException
     * @throws Exception
     */
    public function checkReminders(): int
    {
        $submit_reminders = $this->getReminders(self::SUBMIT_REMINDER);
        $parsed_submit_reminders = $this->parseSubmissionReminders($submit_reminders);

        $grade_reminders = $this->getReminders(self::GRADE_REMINDER);
        $parsed_grade_reminders = $this->parseGradeReminders($grade_reminders);

        $peer_reminders = $this->getReminders(self::FEEDBACK_REMINDER);
        $parsed_peer_reminders = $this->parsePeerReminders($peer_reminders);

        /* //DEBUG
        $this->log->debug("ALL SUBMIT REMINDERS");
        $this->log->dump($submit_reminders);
        $this->log->debug("PARSED SUBMIT REMINDERS");
        $this->log->dump($parsed_submit_reminders);
        $this->log->debug("GRADE REMINDERS ARRAY");
        $this->log->dump($grade_reminders);
        $this->log->debug("PARSED GRADE REMINDERS");
        $this->log->dump($parsed_grade_reminders);
        $this->log->debug("PEER REMINDERS ARRAY");
        $this->log->dump($peer_reminders);
        $this->log->debug("PARSED PEER REMINDERS");
        $this->log->dump($parsed_peer_reminders);
        */

        $reminders = array_merge($parsed_submit_reminders, $parsed_grade_reminders, $parsed_peer_reminders);

        return $this->sendReminders($reminders);
    }

    protected function sendReminders(array $reminders): int
    {
        global $DIC;

        $tpl = null;

        foreach ($reminders as $reminder) {
            $template_id = $reminder['template_id'];

            $rmd_type = $reminder["reminder_type"];
            $this->log->debug("Sending reminder type = " . $rmd_type);

            //if the template exists (can be deleted via Administration/Mail)
            if ($template_id) {
                /** @var \ilMailTemplateService $templateService */
                $templateService = $DIC['mail.texttemplates.service'];
                $tpl = $templateService->loadTemplateForId((int) $template_id);
            }
            $subject = "";
            if ($tpl) {
                $this->log->debug("** send reminder WITH template.");
                $subject = $tpl->getSubject();

                $placeholder_params = array(
                    "exc_id" => $reminder["exc_id"],
                    "exc_ref" => $reminder["exc_ref"],
                    "ass_id" => $reminder["ass_id"],
                    "member_id" => $reminder["member_id"]
                );
                $message = $this->sentReminderPlaceholders($tpl->getMessage(), $placeholder_params, $rmd_type);
            } else {
                $this->log->debug("** send reminder WITHOUT template.");

                $ass_title = ilExAssignment::lookupTitle($reminder["ass_id"]);
                $exc_title = ilObjExercise::_lookupTitle($reminder["exc_id"]);

                // use language of recipient to compose message
                $ulng = ilLanguageFactory::_getLanguageOfUser($reminder["member_id"]);
                $ulng->loadLanguageModule('exc');

                $link = ilLink::_getLink($reminder["exc_ref"], "exc", array(), "_" . $reminder["ass_id"]);

                $message = sprintf($ulng->txt('exc_reminder_salutation'), ilObjUser::_lookupFullname($reminder["member_id"])) . "\n\n";

                $this->log->debug("send: MAIL TYPE = " . $rmd_type . ", user: " . $reminder["member_id"] . ", ass: " . $reminder["ass_id"]);

                switch ($rmd_type) {
                    case "submit":
                        $subject = sprintf($ulng->txt('exc_reminder_submit_subject'), $ass_title);
                        $message .= $ulng->txt('exc_reminder_submit_body') . ":\n\n";
                        break;

                    case "grade":
                        $subject = sprintf($ulng->txt('exc_reminder_grade_subject'), $ass_title);
                        $message .= $ulng->txt('exc_reminder_grade_body') . ":\n\n";
                        break;

                    case "peer":
                        $subject = sprintf($ulng->txt('exc_reminder_peer_subject'), $ass_title);
                        $message .= $ulng->txt('exc_reminder_peer_body') . ":\n\n";
                        break;
                }

                $message .= $ulng->txt('obj_exc') . ": " . $exc_title . "\n";
                $message .= $ulng->txt('obj_ass') . ": " . $ass_title . "\n";
                $message .= "\n" . $ulng->txt('exc_reminder_link') . ": " . $link;
            }
            $mail_obj = new ilMail(ANONYMOUS_USER_ID);
            $mail_obj->appendInstallationSignature(true);
            $mail_obj->enqueue(
                ilObjUser::_lookupLogin($reminder["member_id"]),
                "",
                "",
                $subject,
                $message,
                array()
            );
        }

        $this->updateRemindersLastDate($reminders);
        return count($reminders);
    }

    //see ilObjSurvey.
    protected function sentReminderPlaceholders(
        string $a_message,
        array $a_reminder_data,
        string $a_reminder_type
    ): string {
        // see ilMail::replacePlaceholders()
        try {
            switch ($a_reminder_type) {
                case ilExAssignmentReminder::SUBMIT_REMINDER:
                    $context = ilMailTemplateContextService::getTemplateContextById(ilExcMailTemplateSubmitReminderContext::ID);
                    break;
                case ilExAssignmentReminder::GRADE_REMINDER:
                    $context = ilMailTemplateContextService::getTemplateContextById(ilExcMailTemplateGradeReminderContext::ID);
                    break;
                case ilExAssignmentReminder::FEEDBACK_REMINDER:
                    $context = ilMailTemplateContextService::getTemplateContextById(ilExcMailTemplatePeerReminderContext::ID);
                    break;
                default:
                    exit();
            }

            $user = new ilObjUser($a_reminder_data["member_id"]);

            $processor = new ilMailTemplatePlaceholderResolver($context, $a_message);
            $a_message = $processor->resolve($user, $a_reminder_data);
        } catch (Exception $e) {
            ilLoggerFactory::getLogger('mail')->error(__METHOD__ . ' has been called with invalid context.');
        }

        return $a_message;
    }

    // Update reminders last_send value with the current timestamp.
    protected function updateRemindersLastDate(array $a_reminders): void
    {
        $today = date("Y-m-d");
        foreach ($a_reminders as $reminder) {
            $sql = "UPDATE exc_ass_reminders" .
                " SET last_send = " . $this->db->quote(time(), 'integer') .
                " , last_send_day = " . $this->db->quote($today, 'date') .
                " WHERE type = " . $this->db->quote($reminder["reminder_type"], 'text') .
                " AND ass_id = " . $this->db->quote($reminder["ass_id"], 'integer') .
                " AND exc_id = " . $this->db->quote($reminder["exc_id"], 'integer');

            $this->db->manipulate($sql);
        }
    }

    // remove reminders from DB when the parent assignment is deleted.
    public function deleteReminders(int $a_ass_id): void
    {
        $sql = "DELETE FROM exc_ass_reminders" .
            " WHERE ass_id = " . $a_ass_id;

        $this->db->manipulate($sql);
    }
}
