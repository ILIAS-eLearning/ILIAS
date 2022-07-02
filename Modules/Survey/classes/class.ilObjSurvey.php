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

use ILIAS\Survey\Participants;
use ILIAS\Survey\Mode;

/**
 * @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
 */
class ilObjSurvey extends ilObject
{
    public const EVALUATION_ACCESS_OFF = "0";
    public const EVALUATION_ACCESS_ALL = "1";
    public const EVALUATION_ACCESS_PARTICIPANTS = "2";

    public const ANONYMIZE_OFF = 0; // personalized, no codes
    public const ANONYMIZE_ON = 1; // anonymized, codes
    public const ANONYMIZE_FREEACCESS = 2; // anonymized, no codes
    public const ANONYMIZE_CODE_ALL = 3; // personalized, codes

    public const QUESTIONTITLES_HIDDEN = 0;
    public const QUESTIONTITLES_VISIBLE = 1;

    // constants to define the print view values.
    public const PRINT_HIDE_LABELS = 1; // Show only the titles in print view
    public const PRINT_SHOW_LABELS = 3; // Show titles and labels in print view

    //MODE TYPES
    public const MODE_STANDARD = 0;
    public const MODE_360 = 1;
    public const MODE_SELF_EVAL = 2;
    public const MODE_IND_FEEDB = 3;

    //self evaluation only access to results
    public const RESULTS_SELF_EVAL_NONE = 0;
    public const RESULTS_SELF_EVAL_OWN = 1;
    public const RESULTS_SELF_EVAL_ALL = 2;

    public const RESULTS_360_NONE = 0;
    public const RESULTS_360_OWN = 1;
    public const RESULTS_360_ALL = 2;

    public const NOTIFICATION_PARENT_COURSE = 1;
    public const NOTIFICATION_INVITED_USERS = 2;
    public const NOTIFICATION_APPRAISEES = 3;
    public const NOTIFICATION_RATERS = 4;
    public const NOTIFICATION_APPRAISEES_AND_RATERS = 5;


    protected bool $activation_limited = false;
    protected ilObjUser $user;
    protected ilAccessHandler $access;
    protected ilPluginAdmin $plugin_admin;
    protected bool $calculate_sum_score = false;
    /**
     * A unique positive numerical ID which identifies the survey.
     * This is the primary key from a database table.
     */
    public int $survey_id = 0;
    /**
     * A text representation of the authors name. The name of the author must
     * not necessary be the name of the owner.
     */
    public string $author = "";
    public string $introduction = "";
    public string $outro = "";
    // Indicates the evaluation access for learners
    public string $evaluation_access = self::EVALUATION_ACCESS_OFF;
    // The start date of the survey
    public string $start_date = "";
    // The end date of the survey
    public string $end_date = "";
    // The questions contained in this survey
    public array $questions = [];
    // Indicates the anonymization of the survey
    public int $anonymize = 0;
    // Indicates if the question titles are shown during a query
    public int $display_question_titles = 0;
    // Indicates if a survey code may be exported with the survey statistics
    public bool $surveyCodeSecurity = false;
    
    public bool $mailnotification = false;
    public string $mailaddresses = "";
    public string $mailparticipantdata = "";
    public bool $pool_usage = false;

    protected bool $activation_visibility = false;
    protected ?string $activation_starting_time = null;
    protected ?string $activation_ending_time = null;
    
    // 360°
    protected bool $mode_360_self_eval = false;
    protected bool $mode_360_self_appr = false;
    protected bool $mode_360_self_rate = false;
    protected int $mode_360_results = 0;
    protected bool $mode_skill_service = false;

    // reminder/notification
    protected bool $reminder_status = false;
    protected ?ilDate $reminder_start = null;
    protected ?ilDate $reminder_end = null;
    protected int $reminder_frequency = 0;
    protected int $reminder_target = 0;
    protected ?string $reminder_last_sent = null;
    protected ?int $reminder_tmpl = null;
    protected bool $tutor_ntf_status = false;
    protected array $tutor_ntf_recipients = [];
    protected int $tutor_ntf_target = 0;
    protected bool $tutor_res_status = false;
    protected array $tutor_res_recipients = [];

    protected bool $view_own_results = false;
    protected bool $mail_own_results = false;
    protected bool $mail_confirmation = false;
    
    protected bool $anon_user_list = false;
    protected int $mode = 0;
    protected int $mode_self_eval_results = 0;

    protected Participants\InvitationsManager $invitation_manager;
    protected \ILIAS\Survey\InternalService $survey_service;
    protected \ILIAS\Survey\Code\CodeManager $code_manager;
    protected \ILIAS\Survey\InternalDataService $data_manager;
    protected ?Mode\FeatureConfig $feature_config;
    protected \ILIAS\SurveyQuestionPool\Export\ImportManager $import_manager;

    public function __construct(
        int $a_id = 0,
        bool $a_call_by_reference = true
    ) {
        global $DIC;

        $this->survey_service = $DIC->survey()->internal();

        $this->user = $DIC->user();
        $this->lng = $DIC->language();
        $this->db = $DIC->database();
        $this->access = $DIC->access();
        $this->log = $DIC["ilLog"];
        $this->plugin_admin = $DIC["ilPluginAdmin"];
        $this->tree = $DIC->repositoryTree();
        $ilUser = $DIC->user();
        $lng = $DIC->language();
        
        $this->type = "svy";
        $this->survey_id = -1;
        $this->introduction = "";
        $this->outro = $lng->txt("survey_finished");
        $this->author = $ilUser->getFullname();
        $this->evaluation_access = self::EVALUATION_ACCESS_OFF;
        $this->questions = array();
        $this->anonymize = self::ANONYMIZE_OFF;
        $this->display_question_titles = self::QUESTIONTITLES_VISIBLE;
        $this->surveyCodeSecurity = true;
        $this->pool_usage = true;
        $this->log = ilLoggerFactory::getLogger("svy");
        $this->mode = self::MODE_STANDARD;
        $this->mode_self_eval_results = self::RESULTS_SELF_EVAL_OWN;

        $this->invitation_manager = $this
            ->survey_service
            ->domain()
            ->participants()
            ->invitations();

        $this->import_manager = $DIC->surveyQuestionPool()
            ->internal()
            ->domain()
            ->import();

        parent::__construct($a_id, $a_call_by_reference);
        $this->initServices();
    }

    protected function initServices() : void
    {
        if ($this->getRefId() > 0) {
            $this->code_manager = $this
                ->survey_service
                ->domain()
                ->code($this, $this->user->getId());
        }
        $this->feature_config = $this
            ->survey_service
            ->domain()
            ->modeFeatureConfig($this->getMode());

        $this->data_manager = $this
            ->survey_service
            ->data();
    }

    public function create($a_upload = false) : int
    {
        $id = parent::create();
        if (!$a_upload) {
            $this->createMetaData();
        }
        $this->setOfflineStatus(true);
        $this->update($a_upload);
        return $id;
    }

    protected function doCreateMetaData() : void
    {
        $this->saveAuthorToMetadata();
    }

    public function update($a_upload = false) : bool
    {
        if (!$a_upload) {
            $this->updateMetaData();
        }

        if (!parent::update()) {
            return false;
        }

        // put here object specific stuff
        
        return true;
    }

    public function createReference() : int
    {
        $result = parent::createReference();
        $this->saveToDb();
        return $result;
    }

    public function read() : void
    {
        parent::read();
        $this->loadFromDb();
        $this->initServices();
    }
    
    /**
     * Adds a question to the survey (used in importer!)
     */
    public function addQuestion(int $question_id) : void
    {
        $this->questions[] = $question_id;
    }
    
    public function delete() : bool
    {
        if ($this->countReferences() === 1) {
            $this->deleteMetaData();

            // Delete all survey questions, constraints and materials
            foreach ($this->questions as $question_id) {
                $this->removeQuestion($question_id);
            }
            $this->deleteSurveyRecord();

            ilFileUtils::delDir($this->getImportDirectory());
        }

        $remove = parent::delete();

        // always call parent delete function first!!
        if (!$remove) {
            return false;
        }
        return true;
    }

    /**
     * Deletes the survey from the database
     * @todo move to survey manager/repo
     */
    public function deleteSurveyRecord() : void
    {
        $ilDB = $this->db;
        
        $ilDB->manipulateF(
            "DELETE FROM svy_svy WHERE survey_id = %s",
            array('integer'),
            array($this->getSurveyId())
        );

        $result = $ilDB->queryF(
            "SELECT questionblock_fi FROM svy_qblk_qst WHERE survey_fi = %s",
            array('integer'),
            array($this->getSurveyId())
        );
        $questionblocks = array();
        while ($row = $ilDB->fetchAssoc($result)) {
            $questionblocks[] = $row["questionblock_fi"];
        }
        if (count($questionblocks)) {
            $affectedRows = $ilDB->manipulate("DELETE FROM svy_qblk WHERE " . $ilDB->in('questionblock_id', $questionblocks, false, 'integer'));
        }
        $ilDB->manipulateF(
            "DELETE FROM svy_qblk_qst WHERE survey_fi = %s",
            array('integer'),
            array($this->getSurveyId())
        );
        $this->deleteAllUserData(false);

        $this->code_manager->deleteAll();

        // delete export files
        $svy_data_dir = ilFileUtils::getDataDir() . "/svy_data";
        $directory = $svy_data_dir . "/svy_" . $this->getId();
        if (is_dir($directory)) {
            ilFileUtils::delDir($directory);
        }

        $mobs = ilObjMediaObject::_getMobsOfObject("svy:html", $this->getId());
        // remaining usages are not in text anymore -> delete them
        // and media objects (note: delete method of ilObjMediaObject
        // checks whether object is used in another context; if yes,
        // the object is not deleted!)
        foreach ($mobs as $mob) {
            ilObjMediaObject::_removeUsage($mob, "svy:html", $this->getId());
            $mob_obj = new ilObjMediaObject($mob);
            $mob_obj->delete();
        }
    }

    /**
     * Deletes all user data of a survey
     * @param bool $reset_LP notice that the LP can only be reset it the determining components still exist
     * @todo move to run manager/repo
     */
    public function deleteAllUserData(
        bool $reset_LP = true
    ) : void {
        $ilDB = $this->db;
        
        $result = $ilDB->queryF(
            "SELECT finished_id FROM svy_finished WHERE survey_fi = %s",
            array('integer'),
            array($this->getSurveyId())
        );
        $active_array = array();
        while ($row = $ilDB->fetchAssoc($result)) {
            $active_array[] = $row["finished_id"];
        }

        $affectedRows = $ilDB->manipulateF(
            "DELETE FROM svy_finished WHERE survey_fi = %s",
            array('integer'),
            array($this->getSurveyId())
        );

        foreach ($active_array as $active_fi) {
            $affectedRows = $ilDB->manipulateF(
                "DELETE FROM svy_answer WHERE active_fi = %s",
                array('integer'),
                array($active_fi)
            );
            $affectedRows = $ilDB->manipulateF(
                "DELETE FROM svy_times WHERE finished_fi = %s",
                array('integer'),
                array($active_fi)
            );
        }

        if ($reset_LP) {
            $lp_obj = ilObjectLP::getInstance($this->getId());
            $lp_obj->resetLPDataForCompleteObject();
        }
    }
    
    /**
     * Deletes the user data of a given array of survey participants
     * @todo move to run manager/repo
     */
    public function removeSelectedSurveyResults(
        array $finished_ids
    ) : void {
        $ilDB = $this->db;
        
        $user_ids[] = array();
        
        foreach ($finished_ids as $finished_id) {
            $result = $ilDB->queryF(
                "SELECT finished_id FROM svy_finished WHERE finished_id = %s",
                array('integer'),
                array($finished_id)
            );
            $row = $ilDB->fetchAssoc($result);
            
            if ($row["user_fi"]) {
                $user_ids[] = $row["user_fi"];
            }

            $affectedRows = $ilDB->manipulateF(
                "DELETE FROM svy_answer WHERE active_fi = %s",
                array('integer'),
                array($row["finished_id"])
            );

            $affectedRows = $ilDB->manipulateF(
                "DELETE FROM svy_finished WHERE finished_id = %s",
                array('integer'),
                array($finished_id)
            );

            $affectedRows = $ilDB->manipulateF(
                "DELETE FROM svy_times WHERE finished_fi = %s",
                array('integer'),
                array($row["finished_id"])
            );
        }
        
        if (count($user_ids)) {
            $lp_obj = ilObjectLP::getInstance($this->getId());
            $lp_obj->resetLPDataForUserIds($user_ids);
        }
    }

    /**
     * @todo move to run/participant manager/repo
     */
    public function getSurveyParticipants(
        ?array $finished_ids = null,
        bool $force_non_anonymous = false,
        bool $include_invites = false
    ) : array {
        $ilDB = $this->db;
        $sql = "SELECT * FROM svy_finished" .
            " WHERE survey_fi = " . $ilDB->quote($this->getSurveyId(), "integer");
        if ($finished_ids) {
            $sql .= " AND " . $ilDB->in("finished_id", $finished_ids, "", "integer");
        }
        
        $result = $ilDB->query($sql);
        $participants = array();
        if ($result->numRows() > 0) {
            while ($row = $ilDB->fetchAssoc($result)) {
                $userdata = $this->getUserDataFromActiveId($row["finished_id"], $force_non_anonymous);
                $userdata["finished"] = (bool) $row["state"];
                $userdata["finished_tstamp"] = $row["tstamp"];
                $participants[$userdata["sortname"] . $userdata["active_id"]] = $userdata;
            }
        }
        $participant_ids = array_column($participants, "usr_id");
        if ($include_invites) {
            foreach ($this->invitation_manager->getAllForSurvey($this->getSurveyId()) as $usr_id) {
                if (!in_array($usr_id, $participant_ids)) {
                    $name = ilObjUser::_lookupName($usr_id);
                    $participants[$name["lastname"] . "," . $name["firstname"] . $usr_id] = [
                        "fullname" => ilObjUser::_lookupFullname($usr_id),
                        "sortname" => $name["lastname"] . "," . $name["firstname"],
                        "fistname" => $name["firstname"],
                        "lastname" => $name["lastname"],
                        "login" => $name["login"],
                        "gender" => "",
                        "usr_id" => $usr_id,
                        "finished" => false,
                        "finished_tstamp" => 0,
                        "invited" => true
                    ];
                }
            }
        }
        return $participants;
    }

    /**
     * Check if survey is complete for use
     * @todo move to survey manager
     */
    public function isComplete() : bool
    {
        return ($this->getTitle() && count($this->questions));
    }

    /**
     * Saves the completion status of the survey
     * @todo move to survey manager/repo
     */
    public function saveCompletionStatus() : void
    {
        $ilDB = $this->db;
        
        if ($this->getSurveyId() > 0) {
            $ilDB->manipulateF(
                "UPDATE svy_svy SET complete = %s, tstamp = %s WHERE survey_id = %s",
                array('text','integer','integer'),
                array($this->isComplete(), time(), $this->getSurveyId())
            );
        }
    }

    /**
     * Takes a question and creates a copy of the question for use in the survey
     * @todo move to survey question manager/repo
     */
    public function duplicateQuestionForSurvey(
        int $question_id,
        bool $a_force = false
    ) : int {
        $questiontype = $this->getQuestionType($question_id);
        $question_gui = $this->getQuestionGUI($questiontype, $question_id);

        // check if question is a pool question at all, if not do nothing
        if ($this->getId() === $question_gui->object->getObjId() && !$a_force) {
            return $question_id;
        }

        $duplicate_id = $question_gui->object->duplicate(true, "", "", 0, $this->getId());
        return $duplicate_id;
    }

    /**
     * Inserts a question in the survey and saves the relation to the database
     * @todo move to survey question manager/repo
     */
    public function insertQuestion(
        int $question_id
    ) : bool {
        $ilDB = $this->db;

        $this->log->debug("insert question, id:" . $question_id);

        if (!SurveyQuestion::_isComplete($question_id)) {
            $this->log->debug("question is not complete");
            return false;
        } else {
            // get maximum sequence index in test
            $result = $ilDB->queryF(
                "SELECT survey_question_id FROM svy_svy_qst WHERE survey_fi = %s",
                array('integer'),
                array($this->getSurveyId())
            );
            $sequence = $result->numRows();
            $duplicate_id = $this->duplicateQuestionForSurvey($question_id);
            $this->log->debug("duplicate, id: " . $question_id . ", duplicate id: " . $duplicate_id);

            // check if question is not already in the survey, see #22018
            if ($this->isQuestionInSurvey($duplicate_id)) {
                return false;
            }

            $next_id = $ilDB->nextId('svy_svy_qst');
            $affectedRows = $ilDB->manipulateF(
                "INSERT INTO svy_svy_qst (survey_question_id, survey_fi, question_fi, sequence, tstamp) VALUES (%s, %s, %s, %s, %s)",
                array('integer', 'integer', 'integer', 'integer', 'integer'),
                array($next_id, $this->getSurveyId(), $duplicate_id, $sequence, time())
            );

            $this->log->debug("added entry to svy_svy_qst, id: " . $next_id . ", question id: " . $duplicate_id . ", sequence: " . $sequence);

            $this->loadQuestionsFromDb();
            return true;
        }
    }

    // Check if a question is already in the survey
    public function isQuestionInSurvey(
        int $a_question_fi
    ) : bool {
        global $DIC;
        //return false;
        $ilDB = $DIC->database();

        $set = $ilDB->query("SELECT * FROM svy_svy_qst " .
            " WHERE survey_fi = " . $ilDB->quote($this->getSurveyId(), "integer") .
            " AND question_fi = " . $ilDB->quote($a_question_fi, "integer"));
        if ($rec = $ilDB->fetchAssoc($set)) {
            return true;
        }
        return false;
    }

    // Inserts a questionblock in the survey
    public function insertQuestionblock(
        int $questionblock_id
    ) : void {
        $ilDB = $this->db;
        $result = $ilDB->queryF(
            "SELECT svy_qblk.title, svy_qblk.show_questiontext, svy_qblk.show_blocktitle," .
            " svy_qblk_qst.question_fi FROM svy_qblk, svy_qblk_qst, svy_svy_qst" .
            " WHERE svy_qblk.questionblock_id = svy_qblk_qst.questionblock_fi" .
            " AND svy_svy_qst.question_fi = svy_qblk_qst.question_fi" .
            " AND svy_qblk.questionblock_id = %s" .
            " ORDER BY svy_svy_qst.sequence",
            array('integer'),
            array($questionblock_id)
        );
        $questions = array();
        $show_questiontext = 0;
        $show_blocktitle = 0;
        $title = "";
        while ($row = $ilDB->fetchAssoc($result)) {
            $duplicate_id = $this->duplicateQuestionForSurvey($row["question_fi"]);
            $questions[] = $duplicate_id;
            $title = $row["title"];
            $show_questiontext = $row["show_questiontext"];
            $show_blocktitle = $row["show_blocktitle"];
        }
        $this->createQuestionblock($title, $show_questiontext, $show_blocktitle, $questions);
    }

    // seems to be only be used for code mails
    public function saveUserSettings(
        int $usr_id,
        string $key,
        string $title,
        string $value
    ) : void {
        $ilDB = $this->db;
        
        $next_id = $ilDB->nextId('svy_settings');
        $affectedRows = $ilDB->insert("svy_settings", array(
            "settings_id" => array("integer", $next_id),
            "usr_id" => array("integer", $usr_id),
            "keyword" => array("text", $key),
            "title" => array("text", $title),
            "value" => array("clob", $value)
        ));
    }
    
    public function deleteUserSettings(
        int $id
    ) : void {
        $ilDB = $this->db;
        
        $ilDB->manipulateF(
            "DELETE FROM svy_settings WHERE settings_id = %s",
            array('integer'),
            array($id)
        );
    }
    
    public function getUserSettings(
        int $usr_id,
        string $key
    ) : array {
        $ilDB = $this->db;

        $result = $ilDB->queryF(
            "SELECT * FROM svy_settings WHERE usr_id = %s AND keyword = %s",
            array('integer', 'text'),
            array($usr_id, $key)
        );
        $found = array();
        if ($result->numRows()) {
            while ($row = $ilDB->fetchAssoc($result)) {
                $found[$row['settings_id']] = $row;
            }
        }
        return $found;
    }

    // Saves a survey object to a database
    public function saveToDb() : void
    {
        $ilDB = $this->db;

        // date handling
        $rmd_start = $this->getReminderStart();
        if (is_object($rmd_start)) {
            $rmd_start = $rmd_start->get(IL_CAL_DATE);
        }
        $rmd_end = $this->getReminderEnd();
        if (is_object($rmd_end)) {
            $rmd_end = $rmd_end->get(IL_CAL_DATE);
        }
        if ($this->getSurveyId() < 1) {
            $next_id = $ilDB->nextId('svy_svy');
            $affectedRows = $ilDB->insert("svy_svy", array(
                "survey_id" => array("integer", $next_id),
                "obj_fi" => array("integer", $this->getId()),
                "author" => array("text", $this->getAuthor()),
                "introduction" => array("clob", ilRTE::_replaceMediaObjectImageSrc($this->getIntroduction(), 0)),
                "outro" => array("clob", ilRTE::_replaceMediaObjectImageSrc($this->getOutro(), 0)),
                "startdate" => array("text", $this->getStartDate()),
                "enddate" => array("text", $this->getEndDate()),
                "evaluation_access" => array("text", $this->getEvaluationAccess()),
                "complete" => array("text", $this->isComplete()),
                "created" => array("integer", time()),
                "anonymize" => array("text", $this->getAnonymize()),
                "show_question_titles" => array("text", $this->getShowQuestionTitles()),
                "mailnotification" => array('integer', ($this->getMailNotification()) ? 1 : 0),
                "mailaddresses" => array('text', $this->getMailAddresses()),
                "mailparticipantdata" => array('text', $this->getMailParticipantData()),
                "tstamp" => array("integer", time()),
                "pool_usage" => array("integer", $this->getPoolUsage()),
                // Mode type
                "mode" => array("integer", $this->getMode()),
                // 360°
                "mode_360_self_eval" => array("integer", $this->get360SelfEvaluation()),
                "mode_360_self_rate" => array("integer", $this->get360SelfRaters()),
                "mode_360_self_appr" => array("integer", $this->get360SelfAppraisee()),
                "mode_360_results" => array("integer", $this->get360Results()),
                // competences
                "mode_skill_service" => array("integer", (int) $this->getSkillService()),
                // Self Evaluation Only
                "mode_self_eval_results" => array("integer", self::RESULTS_SELF_EVAL_OWN),
                // reminder/notification
                "reminder_status" => array("integer", (int) $this->getReminderStatus()),
                "reminder_start" => array("datetime", $rmd_start),
                "reminder_end" => array("datetime", $rmd_end),
                "reminder_frequency" => array("integer", $this->getReminderFrequency()),
                "reminder_target" => array("integer", $this->getReminderTarget()),
                "reminder_last_sent" => array("datetime", $this->getReminderLastSent()),
                "reminder_tmpl" => array("text", $this->getReminderTemplate(true)),
                "tutor_ntf_status" => array("integer", (int) $this->getTutorNotificationStatus()),
                "tutor_ntf_reci" => array("text", implode(";", $this->getTutorNotificationRecipients())),
                "tutor_ntf_target" => array("integer", $this->getTutorNotificationTarget()),
                "own_results_view" => array("integer", $this->hasViewOwnResults()),
                "own_results_mail" => array("integer", $this->hasMailOwnResults()),
                "tutor_res_status" => array("integer", (int) $this->getTutorResultsStatus()),
                "tutor_res_reci" => array("text", implode(";", $this->getTutorResultsRecipients())),
                "confirmation_mail" => array("integer", $this->hasMailConfirmation()),
                "anon_user_list" => array("integer", $this->hasAnonymousUserList()),
                "calculate_sum_score" => array("integer", $this->getCalculateSumScore())
            ));
            $this->setSurveyId($next_id);
        } else {
            $affectedRows = $ilDB->update("svy_svy", array(
                "author" => array("text", $this->getAuthor()),
                "introduction" => array("clob", ilRTE::_replaceMediaObjectImageSrc($this->getIntroduction(), 0)),
                "outro" => array("clob", ilRTE::_replaceMediaObjectImageSrc($this->getOutro(), 0)),
                "startdate" => array("text", $this->getStartDate()),
                "enddate" => array("text", $this->getEndDate()),
                "evaluation_access" => array("text", $this->getEvaluationAccess()),
                "complete" => array("text", $this->isComplete()),
                "anonymize" => array("text", $this->getAnonymize()),
                "show_question_titles" => array("text", $this->getShowQuestionTitles()),
                "mailnotification" => array('integer', ($this->getMailNotification()) ? 1 : 0),
                "mailaddresses" => array('text', $this->getMailAddresses()),
                "mailparticipantdata" => array('text', $this->getMailParticipantData()),
                "tstamp" => array("integer", time()),
                "pool_usage" => array("integer", $this->getPoolUsage()),
                //MODE TYPE
                "mode" => array("integer", $this->getMode()),
                // 360°
                "mode_360_self_eval" => array("integer", $this->get360SelfEvaluation()),
                "mode_360_self_rate" => array("integer", $this->get360SelfRaters()),
                "mode_360_self_appr" => array("integer", $this->get360SelfAppraisee()),
                "mode_360_results" => array("integer", $this->get360Results()),
                // Competences
                "mode_skill_service" => array("integer", (int) $this->getSkillService()),
                // Self Evaluation Only
                "mode_self_eval_results" => array("integer", $this->getSelfEvaluationResults()),
                // reminder/notification
                "reminder_status" => array("integer", $this->getReminderStatus()),
                "reminder_start" => array("datetime", $rmd_start),
                "reminder_end" => array("datetime", $rmd_end),
                "reminder_frequency" => array("integer", $this->getReminderFrequency()),
                "reminder_target" => array("integer", $this->getReminderTarget()),
                "reminder_last_sent" => array("datetime", $this->getReminderLastSent()),
                "reminder_tmpl" => array("text", $this->getReminderTemplate()),
                "tutor_ntf_status" => array("integer", $this->getTutorNotificationStatus()),
                "tutor_ntf_reci" => array("text", implode(";", $this->getTutorNotificationRecipients())),
                "tutor_ntf_target" => array("integer", $this->getTutorNotificationTarget()),
                "own_results_view" => array("integer", $this->hasViewOwnResults()),
                "own_results_mail" => array("integer", $this->hasMailOwnResults()),
                "tutor_res_status" => array("integer", (int) $this->getTutorResultsStatus()),
                "tutor_res_reci" => array("text", implode(";", $this->getTutorResultsRecipients())),
                "confirmation_mail" => array("integer", $this->hasMailConfirmation()),
                "anon_user_list" => array("integer", $this->hasAnonymousUserList()),
                "calculate_sum_score" => array("integer", $this->getCalculateSumScore())
            ), array(
            "survey_id" => array("integer", $this->getSurveyId())
            ));
        }
        if ($affectedRows) {
            // save questions to db
            $this->saveQuestionsToDb();
        }
        
        // moved activation to ilObjectActivation
        if ($this->ref_id) {
            ilObjectActivation::getItem($this->ref_id);
            
            $item = new ilObjectActivation();
            if (!$this->isActivationLimited()) {
                $item->setTimingType(ilObjectActivation::TIMINGS_DEACTIVATED);
            } else {
                $item->setTimingType(ilObjectActivation::TIMINGS_ACTIVATION);
                $item->setTimingStart($this->getActivationStartDate());
                $item->setTimingEnd($this->getActivationEndDate());
                $item->toggleVisible($this->getActivationVisibility());
            }
            
            $item->update($this->ref_id);
        }
    }

    // Saves the survey questions to db
    public function saveQuestionsToDb() : void
    {
        $ilDB = $this->db;

        $this->log->debug("save questions");

        // gather old questions state
        $old_questions = array();
        $result = $ilDB->queryF(
            "SELECT survey_question_id,question_fi,sequence" .
            " FROM svy_svy_qst WHERE survey_fi = %s",
            array('integer'),
            array($this->getSurveyId())
        );
        while ($row = $ilDB->fetchAssoc($result)) {
            $old_questions[$row["question_fi"]] = $row;		// problem, as soon as duplicates exist, they will be hidden here
        }
        
        // #15231 - diff with current questions state
        $insert = $update = $delete = array();
        foreach ($this->questions as $seq => $fi) {
            if (!array_key_exists($fi, $old_questions)) {		// really new fi IDs
                $insert[] = $fi;							// this should be ok, should not create duplicates here
            } elseif ($old_questions[$fi]["sequence"] != $seq) {				// we are updating one of the duplicates (if any)
                $update[$fi] = $old_questions[$fi]["survey_question_id"];
            }
            // keep track of still relevant questions
            unset($old_questions[$fi]);						// deleting old question, if they are not in current array
        }
        
        // delete obsolete question relations
        if (count($old_questions)) {
            $del_ids = array();
            foreach ($old_questions as $fi => $old) {
                $del_ids[] = $old["survey_question_id"];
            }
            $ilDB->manipulate($q = "DELETE FROM svy_svy_qst" .
                " WHERE " . $ilDB->in("survey_question_id", $del_ids, "", "integer"));
            $this->log->debug("delete: " . $q);
        }
        unset($old_questions);
        
        // create/update question relations
        foreach ($this->questions as $seq => $fi) {
            if (in_array($fi, $insert)) {
                // check if question is not already in the survey, see #22018
                if (!$this->isQuestionInSurvey($fi)) {
                    $next_id = $ilDB->nextId('svy_svy_qst');
                    $ilDB->manipulateF(
                        "INSERT INTO svy_svy_qst" .
                        " (survey_question_id, survey_fi, question_fi, heading, sequence, tstamp)" .
                        " VALUES (%s, %s, %s, %s, %s, %s)",
                        array('integer', 'integer', 'integer', 'text', 'integer', 'integer'),
                        array($next_id, $this->getSurveyId(), $fi, null, $seq, time())
                    );
                    $this->log->debug("insert svy_svy_qst, id:" . $next_id . ", fi: " . $fi . ", seq:" . $seq);
                }
            } elseif (array_key_exists($fi, $update)) {
                $ilDB->manipulate("UPDATE svy_svy_qst" .
                    " SET sequence = " . $ilDB->quote($seq, "integer") .
                    ", tstamp = " . $ilDB->quote(time(), "integer") .
                    " WHERE survey_question_id = " . $ilDB->quote($update[$fi], "integer"));
                $this->log->debug("update svy_svy_qst, id:" . $update[$fi] . ", fi: " . $fi . ", seq:" . $seq);
            }
        }
    }

    // Returns a question gui object to a given questiontype and question id
    public function getQuestionGUI(
        string $questiontype,
        int $question_id
    ) : SurveyQuestionGUI {
        return SurveyQuestionGUI::_getQuestionGUI($questiontype, $question_id);
    }
    
    // Returns the question type of a question with a given id
    public function getQuestionType(
        int $question_id
    ) : string {
        $ilDB = $this->db;
        if ($question_id < 1) {
            return -1;
        }
        $result = $ilDB->queryF(
            "SELECT type_tag FROM svy_question, svy_qtype WHERE svy_question.question_id = %s AND " .
            "svy_question.questiontype_fi = svy_qtype.questiontype_id",
            array('integer'),
            array($question_id)
        );
        if ($result->numRows() === 1) {
            $data = $ilDB->fetchAssoc($result);
            return $data["type_tag"];
        } else {
            return "";
        }
    }

    public function getSurveyId() : int
    {
        return $this->survey_id;
    }
    
    /**
     * set anonymize status
     */
    public function setAnonymize(int $a_anonymize) : void
    {
        switch ($a_anonymize) {
            case self::ANONYMIZE_OFF:
            case self::ANONYMIZE_ON:
            case self::ANONYMIZE_FREEACCESS:
            case self::ANONYMIZE_CODE_ALL:
                $this->anonymize = $a_anonymize;
                break;
            default:
                $this->anonymize = self::ANONYMIZE_OFF;
                break;
        }
    }

    public function getAnonymize() : int
    {
        return $this->anonymize;
    }

    public function setCalculateSumScore(
        bool $a_val
    ) : void {
        $this->calculate_sum_score = $a_val;
    }

    public function getCalculateSumScore() : bool
    {
        return $this->calculate_sum_score;
    }

    // Checks if the survey is accessible without a survey code
    public function isAccessibleWithoutCode() : bool
    {
        return ($this->getAnonymize() === self::ANONYMIZE_OFF ||
            $this->getAnonymize() === self::ANONYMIZE_FREEACCESS);
    }
    
    // Checks if the survey results are to be anonymized
    public function hasAnonymizedResults() : bool
    {
        return ($this->getAnonymize() === self::ANONYMIZE_ON ||
            $this->getAnonymize() === self::ANONYMIZE_FREEACCESS);
    }

    public function loadFromDb() : void
    {
        $ilDB = $this->db;
        $result = $ilDB->queryF(
            "SELECT * FROM svy_svy WHERE obj_fi = %s",
            array('integer'),
            array($this->getId())
        );
        if ($result->numRows() === 1) {
            $data = $ilDB->fetchAssoc($result);
            $this->setSurveyId($data["survey_id"]);
            $this->setAuthor($data["author"]);
            $this->setIntroduction(ilRTE::_replaceMediaObjectImageSrc((string) $data["introduction"], 1));
            if (strcmp($data["outro"], "survey_finished") === 0) {
                $this->setOutro($this->lng->txt("survey_finished"));
            } else {
                $this->setOutro(ilRTE::_replaceMediaObjectImageSrc($data["outro"], 1));
            }
            $this->setShowQuestionTitles($data["show_question_titles"]);
            $this->setStartDate((string) $data["startdate"]);
            $this->setEndDate((string) $data["enddate"]);
            $this->setAnonymize($data["anonymize"]);
            $this->setEvaluationAccess($data["evaluation_access"]);
            $this->loadQuestionsFromDb();
            $this->setMailNotification((bool) $data['mailnotification']);
            $this->setMailAddresses((string) $data['mailaddresses']);
            $this->setMailParticipantData((string) $data['mailparticipantdata']);
            $this->setPoolUsage($data['pool_usage']);
            // Mode
            $this->setMode($data['mode']);
            // 360°
            $this->set360SelfEvaluation($data['mode_360_self_eval']);
            $this->set360SelfRaters($data['mode_360_self_rate']);
            $this->set360SelfAppraisee($data['mode_360_self_appr']);
            $this->set360Results($data['mode_360_results']);
            // Mode self evaluated
            $this->setSelfEvaluationResults($data['mode_self_eval_results']);
            // Competences
            $this->setSkillService($data['mode_skill_service']);
            // reminder/notification
            $this->setReminderStatus($data["reminder_status"]);
            $this->setReminderStart($data["reminder_start"] ? new ilDate($data["reminder_start"], IL_CAL_DATE) : null);
            $this->setReminderEnd($data["reminder_end"] ? new ilDate($data["reminder_end"], IL_CAL_DATE) : null);
            $this->setReminderFrequency($data["reminder_frequency"]);
            $this->setReminderTarget($data["reminder_target"]);
            $this->setReminderLastSent((string) $data["reminder_last_sent"]);
            $this->setReminderTemplate((int) $data["reminder_tmpl"]);
            $this->setTutorNotificationStatus($data["tutor_ntf_status"]);
            $this->setTutorNotificationRecipients(explode(";", $data["tutor_ntf_reci"]));
            $this->setTutorNotificationTarget($data["tutor_ntf_target"]);
            $this->setTutorResultsStatus((bool) $data["tutor_res_status"]);
            $this->setTutorResultsRecipients(explode(";", $data["tutor_res_reci"]));
            
            $this->setViewOwnResults($data["own_results_view"]);
            $this->setMailOwnResults($data["own_results_mail"]);
            $this->setMailConfirmation((bool) $data["confirmation_mail"]);
            $this->setCalculateSumScore($data["calculate_sum_score"]);
            
            $this->setAnonymousUserList($data["anon_user_list"]);
        }
        
        // moved activation to ilObjectActivation
        if (isset($this->ref_id) && $this->ref_id !== 0) {
            $activation = ilObjectActivation::getItem($this->ref_id);
            switch ($activation["timing_type"]) {
                case ilObjectActivation::TIMINGS_ACTIVATION:
                    $this->setActivationLimited(true);
                    $this->setActivationStartDate($activation["timing_start"]);
                    $this->setActivationEndDate($activation["timing_end"]);
                    $this->setActivationVisibility($activation["visible"]);
                    break;
                
                default:
                    $this->setActivationLimited(false);
                    break;
            }
        }
    }

    public function loadQuestionsFromDb() : void
    {
        $ilDB = $this->db;
        $this->questions = array();
        $result = $ilDB->queryF(
            "SELECT * FROM svy_svy_qst WHERE survey_fi = %s ORDER BY sequence",
            array('integer'),
            array($this->getSurveyId())
        );
        while ($data = $ilDB->fetchAssoc($result)) {
            $this->questions[$data["sequence"]] = $data["question_fi"];
        }
    }

    // Remove duplicate sequence entries, see #22018
    public function fixSequenceStructure() : void
    {
        global $DIC;

        $ilDB = $DIC->database();
        //return;
        // we keep all survey question ids with their lowest sequence
        $result = $ilDB->queryF(
            "SELECT * FROM svy_svy_qst WHERE survey_fi = %s ORDER BY sequence",
            array('integer'),
            array($this->getSurveyId())
        );

        // step 1: find duplicates -> $to_delete_ids
        $fis = array();
        $to_delete_ids = array();
        while ($data = $ilDB->fetchAssoc($result)) {
            if (in_array($data["question_fi"], $fis)) {		// found a duplicate
                $to_delete_ids[] = $data["survey_question_id"];
            } else {
                $fis[] = $data["question_fi"];
            }
        }

        // step 2: we delete the duplicates
        if (count($to_delete_ids) > 0) {
            $ilDB->manipulate($q = "DELETE FROM svy_svy_qst" .
                " WHERE " . $ilDB->in("survey_question_id", $to_delete_ids, false, "integer") .
                " AND survey_fi = " . $ilDB->quote($this->getSurveyId(), "integer"));
            $this->log->debug("delete: " . $q);

            $ilDB->manipulate($q = "DELETE FROM svy_qblk_qst " .
                " WHERE " . $ilDB->in("question_fi", $fis, true, "integer") .
                " AND survey_fi = " . $ilDB->quote($this->getSurveyId(), "integer"));
            $this->log->debug("delete: " . $q);
        }

        // step 3: we fix the sequence
        $set = $ilDB->query("SELECT * FROM svy_svy_qst " .
            " WHERE survey_fi = " . $ilDB->quote($this->getSurveyId(), "integer") . " ORDER BY sequence");
        $seq = 0;
        while ($rec = $ilDB->fetchAssoc($set)) {
            $ilDB->manipulate(
                $q = "UPDATE svy_svy_qst SET " .
                " sequence = " . $ilDB->quote($seq++, "integer") .
                " WHERE survey_question_id = " . $ilDB->quote($rec["survey_question_id"], "integer")
            );
            $this->log->debug("update: " . $q);
        }
    }

    public function setAuthor(
        string $author = ""
    ) : void {
        $this->author = $author;
    }

    /**
     * Saves an authors name into the lifecycle metadata if no lifecycle metadata exists
     * This will only be called for conversion of "old" surveys where the author hasn't been
     * stored in the lifecycle metadata
     */
    public function saveAuthorToMetadata(
        string $a_author = ""
    ) : void {
        $md = new ilMD($this->getId(), 0, $this->getType());
        $md_life = $md->getLifecycle();
        if (!$md_life) {
            if ($a_author === '') {
                $ilUser = $this->user;
                $a_author = $ilUser->getFullname();
            }
            
            $md_life = $md->addLifecycle();
            $md_life->save();
            $con = $md_life->addContribute();
            $con->setRole("Author");
            $con->save();
            $ent = $con->addEntity();
            $ent->setEntity($a_author);
            $ent->save();
        }
    }
    
    // Gets the authors name from metadata
    public function getAuthor() : string
    {
        $author = array();
        $md = new ilMD($this->getId(), 0, $this->getType());
        $md_life = $md->getLifecycle();
        if ($md_life) {
            $ids = $md_life->getContributeIds();
            foreach ($ids as $id) {
                $md_cont = $md_life->getContribute($id);
                if (strcmp($md_cont->getRole(), "Author") === 0) {
                    $entids = $md_cont->getEntityIds();
                    foreach ($entids as $entid) {
                        $md_ent = $md_cont->getEntity($entid);
                        $author[] = $md_ent->getEntity();
                    }
                }
            }
        }
        return implode(",", $author);
    }

    public function getShowQuestionTitles() : bool
    {
        return (bool) $this->display_question_titles;
    }

    public function setShowQuestionTitles(bool $a_show) : void
    {
        $this->display_question_titles = $a_show;
    }

    public function setIntroduction(
        string $introduction = ""
    ) : void {
        $this->introduction = $introduction;
    }

    public function setOutro(
        string $outro = ""
    ) : void {
        $this->outro = $outro;
    }

    public function getStartDate() : string
    {
        return $this->start_date;
    }

    /**
     * @param string $start_date (YYYYMMDDHHMMSS)
     */
    public function setStartDate(
        string $start_date = ""
    ) : void {
        $this->start_date = $start_date;
    }

    /**
     * @param string $start_date (YYYY-MM-DD)
     * @param string $start_time (HH:MM:SS)
     */
    public function setStartDateAndTime(
        string $start_date,
        string $start_time
    ) : void {
        $y = '';
        $m = '';
        $d = '';
        $h = '';
        $i = '';
        $s = '';
        if (preg_match("/(\d{4})-(\d{2})-(\d{2})/", $start_date, $matches)) {
            $y = $matches[1];
            $m = $matches[2];
            $d = $matches[3];
        }
        if (preg_match("/(\d{2}):(\d{2}):(\d{2})/", $start_time, $matches)) {
            $h = $matches[1];
            $i = $matches[2];
            $s = $matches[3];
        }
        $this->start_date = sprintf('%04d%02d%02d%02d%02d%02d', $y, $m, $d, $h, $i, $s);
    }

    public function getEndDate() : string
    {
        return $this->end_date;
    }

    /**
     * @param string $end_date (YYYYMMDDHHMMSS)
     */
    public function setEndDate(
        string $end_date = ""
    ) : void {
        $this->end_date = $end_date;
    }

    public function hasStarted() : bool
    {
        $start = $this->getStartDate();
        if ($start) {
            $start_date = new ilDateTime($start, IL_CAL_TIMESTAMP);
            return ($start_date->get(IL_CAL_UNIX) < time());
        }
        return true;
    }

    public function hasEnded() : bool
    {
        $end = $this->getEndDate();
        if ($end) {
            $end_date = new ilDateTime($end, IL_CAL_TIMESTAMP);
            return ($end_date->get(IL_CAL_UNIX) < time());
        }
        return false;
    }

    /**
     * @param string $end_date (YYYY-MM-DD)
     * @param string $end_time (HH:MM:SS)
     */
    public function setEndDateAndTime(
        string $end_date,
        string $end_time
    ) : void {
        $y = '';
        $m = '';
        $d = '';
        $h = '';
        $i = '';
        $s = '';
        if (preg_match("/(\d{4})-(\d{2})-(\d{2})/", $end_date, $matches)) {
            $y = $matches[1];
            $m = $matches[2];
            $d = $matches[3];
        }
        if (preg_match("/(\d{2}):(\d{2}):(\d{2})/", $end_time, $matches)) {
            $h = $matches[1];
            $i = $matches[2];
            $s = $matches[3];
        }
        $this->end_date = sprintf('%04d%02d%02d%02d%02d%02d', $y, $m, $d, $h, $i, $s);
    }

    // Gets the learners evaluation access
    public function getEvaluationAccess() : string
    {
        return $this->evaluation_access;
    }

    public function setEvaluationAccess(
        string $evaluation_access = self::EVALUATION_ACCESS_OFF
    ) : void {
        $this->evaluation_access = $evaluation_access;
    }
    
    public function setActivationVisibility(
        bool $a_value
    ) : void {
        $this->activation_visibility = $a_value;
    }
    
    public function getActivationVisibility() : bool
    {
        return $this->activation_visibility;
    }
    
    public function isActivationLimited() : bool
    {
        return $this->activation_limited;
    }
    
    public function setActivationLimited(bool $a_value) : void
    {
        $this->activation_limited = $a_value;
    }

    public function getIntroduction() : string
    {
        return $this->introduction;
    }

    public function getOutro() : string
    {
        return $this->outro;
    }

    /**
     * Gets the question id's of the questions which are already in the survey
     * @return int[]
     */
    public function getExistingQuestions() : array
    {
        $ilDB = $this->db;
        $existing_questions = array();
        $result = $ilDB->queryF(
            "SELECT svy_question.original_id FROM svy_question, svy_svy_qst WHERE " .
            "svy_svy_qst.survey_fi = %s AND svy_svy_qst.question_fi = svy_question.question_id",
            array('integer'),
            array($this->getSurveyId())
        );
        while ($data = $ilDB->fetchAssoc($result)) {
            if ($data["original_id"]) {
                $existing_questions[] = (int) $data["original_id"];
            }
        }
        return $existing_questions;
    }

    /**
     * Returns the available question pools for the active user
     * @return array<int, string> keys are obj IDs, values are titles
     */
    public function getQuestionpoolTitles(
        bool $could_be_offline = false,
        bool $showPath = false
    ) : array {
        return ilObjSurveyQuestionPool::_getAvailableQuestionpools(true, $could_be_offline, $showPath);
    }
    
    /**
     * Move questions and/or questionblocks to another position
     * @param array $move_questions An array with the question id's of the questions to move
     * @param int $target_index The question id of the target position
     * @param int $insert_mode 0, if insert before the target position, 1 if insert after the target position
     * @todo move to survey question manager/repo
     */
    public function moveQuestions(
        array $move_questions,
        int $target_index,
        int $insert_mode
    ) : void {
        $array_pos = array_search($target_index, $this->questions);
        $part1 = $part2 = [];
        if ($insert_mode === 0) {
            $part1 = array_slice($this->questions, 0, $array_pos);
            $part2 = array_slice($this->questions, $array_pos);
        } elseif ($insert_mode === 1) {
            $part1 = array_slice($this->questions, 0, $array_pos + 1);
            $part2 = array_slice($this->questions, $array_pos + 1);
        }
        $found = 0;
        foreach ($move_questions as $question_id) {
            if (!(!in_array($question_id, $part1))) {
                unset($part1[array_search($question_id, $part1)]);
                $found++;
            }
            if (!(!in_array($question_id, $part2))) {
                unset($part2[array_search($question_id, $part2)]);
                $found++;
            }
        }
        // sanity check: do not move questions if they have not be found in the array
        if ($found !== count($move_questions)) {
            return;
        }
        $part1 = array_values($part1);
        $part2 = array_values($part2);
        $this->questions = array_values(array_merge($part1, $move_questions, $part2));
        foreach ($move_questions as $question_id) {
            $constraints = $this->getConstraints($question_id);
            foreach ($constraints as $idx => $constraint) {
                foreach ($part2 as $next_question_id) {
                    if ($constraint["question"] == $next_question_id) {
                        // constraint concerning a question that follows -> delete constraint
                        $this->deleteConstraint($constraint["id"]);
                    }
                }
            }
        }
        $this->saveQuestionsToDb();
    }

    /**
     * @todo move to survey question manager/repo
     */
    public function removeQuestion(
        int $question_id
    ) : void {
        $question = self::_instanciateQuestion($question_id);
        #20610 if no question found, do nothing.
        if ($question) {
            $question->delete($question_id);
            $this->removeConstraintsConcerningQuestion($question_id);
        }
    }
    
    /**
     * Remove constraints concerning a question with a given question_id
     * @todo move to constraint manager/repo
     */
    public function removeConstraintsConcerningQuestion(
        int $question_id
    ) : void {
        $ilDB = $this->db;
        $result = $ilDB->queryF(
            "SELECT constraint_fi FROM svy_qst_constraint WHERE question_fi = %s AND survey_fi = %s",
            array('integer','integer'),
            array($question_id, $this->getSurveyId())
        );
        if ($result->numRows() > 0) {
            $remove_constraints = array();
            while ($row = $ilDB->fetchAssoc($result)) {
                $remove_constraints[] = $row["constraint_fi"];
            }
            $affectedRows = $ilDB->manipulateF(
                "DELETE FROM svy_qst_constraint WHERE question_fi = %s AND survey_fi = %s",
                array('integer','integer'),
                array($question_id, $this->getSurveyId())
            );
            foreach ($remove_constraints as $key => $constraint_id) {
                $affectedRows = $ilDB->manipulateF(
                    "DELETE FROM svy_constraint WHERE constraint_id = %s",
                    array('integer'),
                    array($constraint_id)
                );
            }
        }
    }
        
    /**
     * @param int[] $remove_questions question ids of the questions to remove
     * @param int[] $remove_questionblocks questionblock ids of the questions blocks to remove
     * @todo move to survey question manager/repo
     */
    public function removeQuestions(
        array $remove_questions,
        array $remove_questionblocks
    ) : void {
        $ilDB = $this->db;

        $block_sizes = array();
        foreach ($this->getSurveyQuestions() as $question_id => $data) {
            if (in_array($question_id, $remove_questions) or in_array($data["questionblock_id"], $remove_questionblocks)) {
                unset($this->questions[array_search($question_id, $this->questions)]);
                $this->removeQuestion($question_id);
            } elseif ($data["questionblock_id"]) {
                $block_sizes[$data["questionblock_id"]]++;
            }
        }
        
        // blocks with just 1 question need to be deleted
        foreach ($block_sizes as $block_id => $size) {
            if ($size < 2) {
                $remove_questionblocks[] = $block_id;
            }
        }

        foreach (array_unique($remove_questionblocks) as $questionblock_id) {
            $affectedRows = $ilDB->manipulateF(
                "DELETE FROM svy_qblk WHERE questionblock_id = %s",
                array('integer'),
                array($questionblock_id)
            );
            $affectedRows = $ilDB->manipulateF(
                "DELETE FROM svy_qblk_qst WHERE questionblock_fi = %s AND survey_fi = %s",
                array('integer','integer'),
                array($questionblock_id, $this->getSurveyId())
            );
        }
        
        $this->questions = array_values($this->questions);
        $this->saveQuestionsToDb();
    }
        
    /**
     * @param int[] $questionblocks question block ids
     * @todo move to survey question manager/repo
     */
    public function unfoldQuestionblocks(
        array $questionblocks
    ) : void {
        $ilDB = $this->db;
        foreach ($questionblocks as $index) {
            $affectedRows = $ilDB->manipulateF(
                "DELETE FROM svy_qblk WHERE questionblock_id = %s",
                array('integer'),
                array($index)
            );
            $affectedRows = $ilDB->manipulateF(
                "DELETE FROM svy_qblk_qst WHERE questionblock_fi = %s AND survey_fi = %s",
                array('integer','integer'),
                array($index, $this->getSurveyId())
            );
        }
    }

    /**
     * @todo move to survey question manager/repo
     */
    public function removeQuestionFromBlock(
        int $question_id,
        int $questionblock_id
    ) : void {
        $ilDB = $this->db;
        
        $ilDB->manipulateF(
            "DELETE FROM svy_qblk_qst WHERE questionblock_fi = %s AND survey_fi = %s AND question_fi = %s",
            array('integer','integer','integer'),
            array($questionblock_id, $this->getSurveyId(), $question_id)
        );
    }

    /**
     * @todo move to survey question manager/repo
     */
    public function addQuestionToBlock(
        int $question_id,
        int $questionblock_id
    ) : void {
        $ilDB = $this->db;

        // see #22018
        if (!$this->isQuestionInAnyBlock($question_id)) {
            $next_id = $ilDB->nextId('svy_qblk_qst');
            $affectedRows = $ilDB->manipulateF(
                "INSERT INTO svy_qblk_qst (qblk_qst_id, survey_fi, questionblock_fi, " .
                "question_fi) VALUES (%s, %s, %s, %s)",
                array('integer', 'integer', 'integer', 'integer'),
                array($next_id, $this->getSurveyId(), $questionblock_id, $question_id)
            );

            $this->deleteConstraints($question_id); // #13713
        }
    }

    /**
     * @todo move to survey question manager/repo
     */
    public function isQuestionInAnyBlock(
        int $a_question_fi
    ) : bool {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->query("SELECT * FROM svy_qblk_qst " .
            " WHERE survey_fi = " . $ilDB->quote($this->getSurveyId(), "integer") .
            " AND question_fi = " . $ilDB->quote($a_question_fi, "integer"));
        if ($rec = $ilDB->fetchAssoc($set)) {
            return true;
        }
        return false;
    }


    /**
     * @param int $questionblock_id
     * @return string[] question titles
     * @todo move to survey question manager/repo
     */
    public function getQuestionblockQuestions(
        int $questionblock_id
    ) : array {
        $ilDB = $this->db;
        $titles = array();
        $result = $ilDB->queryF(
            "SELECT svy_question.title, svy_qblk_qst.question_fi, svy_qblk_qst.survey_fi FROM " .
            "svy_qblk, svy_qblk_qst, svy_question WHERE svy_qblk.questionblock_id = svy_qblk_qst.questionblock_fi AND " .
            "svy_question.question_id = svy_qblk_qst.question_fi AND svy_qblk.questionblock_id = %s",
            array('integer'),
            array($questionblock_id)
        );
        $survey_id = "";
        while ($row = $ilDB->fetchAssoc($result)) {
            $titles[$row["question_fi"]] = $row["title"];
            $survey_id = $row["survey_fi"];
        }
        $result = $ilDB->queryF(
            "SELECT question_fi, sequence FROM svy_svy_qst WHERE survey_fi = %s ORDER BY sequence",
            array('integer'),
            array($survey_id)
        );
        $resultarray = array();
        $counter = 1;
        while ($row = $ilDB->fetchAssoc($result)) {
            if (array_key_exists($row["question_fi"], $titles)) {
                $resultarray[$counter++] = $titles[$row["question_fi"]];
            }
        }
        return $resultarray;
    }
    
    /**
     * @param int $questionblock_id
     * @return int[]
     * @todo move to survey question manager/repo
     */
    public function getQuestionblockQuestionIds(
        int $questionblock_id
    ) : array {
        $ilDB = $this->db;

        // we need a correct order here, see #22011
        $result = $ilDB->queryF(
            "SELECT a.question_fi FROM svy_qblk_qst a JOIN svy_svy_qst b ON (a.question_fi = b.question_fi) " .
            " WHERE a.questionblock_fi = %s ORDER BY b.sequence",
            array("integer"),
            array($questionblock_id)
        );
        $ids = array();
        if ($result->numRows()) {
            while ($data = $ilDB->fetchAssoc($result)) {
                if (!in_array($data['question_fi'], $ids)) {		// no duplicates, see #22018
                    $ids[] = (int) $data['question_fi'];
                }
            }
        }
        return $ids;
    }

    /**
     * get question block properties
     * @todo move to survey question manager/repo
     */
    public static function _getQuestionblock(
        int $questionblock_id
    ) : array {
        global $DIC;

        $ilDB = $DIC->database();
        $result = $ilDB->queryF(
            "SELECT * FROM svy_qblk WHERE questionblock_id = %s",
            array('integer'),
            array($questionblock_id)
        );
        $row = $ilDB->fetchAssoc($result);
        return $row;
    }

    /**
     * @todo move to survey question manager/repo
     */
    public static function _addQuestionblock(
        string $title = "",
        int $owner = 0,
        bool $show_questiontext = true,
        bool $show_blocktitle = false,
        bool $compress_view = false
    ) : int {
        global $DIC;

        $ilDB = $DIC->database();
        $next_id = $ilDB->nextId('svy_qblk');
        $ilDB->manipulateF(
            "INSERT INTO svy_qblk (questionblock_id, title, show_questiontext," .
            " show_blocktitle, owner_fi, tstamp, compress_view) " .
            "VALUES (%s, %s, %s, %s, %s, %s, %s)",
            array('integer','text','integer','integer','integer','integer','integer'),
            array($next_id, $title, $show_questiontext, $show_blocktitle, $owner, time(),$compress_view)
        );
        return $next_id;
    }
    
    /**
     * @param int[]  $questions array with question ids
     */
    public function createQuestionblock(
        string $title,
        bool $show_questiontext,
        bool $show_blocktitle,
        array $questions,
        bool $compress_view = false
    ) : void {
        $ilDB = $this->db;
        
        // if the selected questions are not in a continous selection, move all questions of the
        // questionblock at the position of the first selected question
        $this->moveQuestions($questions, $questions[0], 0);
        
        // now save the question block
        $ilUser = $this->user;
        $next_id = $ilDB->nextId('svy_qblk');
        $affectedRows = $ilDB->manipulateF(
            "INSERT INTO svy_qblk (questionblock_id, title, show_questiontext," .
            " show_blocktitle, owner_fi, tstamp, compress_view) VALUES (%s, %s, %s, %s, %s, %s, %s)",
            array('integer','text','text','text','integer','integer','integer'),
            array($next_id, $title, $show_questiontext, $show_blocktitle, $ilUser->getId(), time(), $compress_view)
        );
        if ($affectedRows) {
            $questionblock_id = $next_id;
            foreach ($questions as $index) {
                if (!$this->isQuestionInAnyBlock($index)) {
                    $next_id = $ilDB->nextId('svy_qblk_qst');	// #22018
                    $affectedRows = $ilDB->manipulateF(
                        "INSERT INTO svy_qblk_qst (qblk_qst_id, survey_fi, questionblock_fi, " .
                        "question_fi) VALUES (%s, %s, %s, %s)",
                        array('integer', 'integer', 'integer', 'integer'),
                        array($next_id, $this->getSurveyId(), $questionblock_id, $index)
                    );
                    $this->deleteConstraints($index);
                }
            }
        }
    }

    /**
     * @todo move to survey question manager/repo
     */
    public function modifyQuestionblock(
        int $questionblock_id,
        string $title,
        bool $show_questiontext,
        bool $show_blocktitle,
        bool $compress_view = false
    ) : void {
        $ilDB = $this->db;
        $ilDB->manipulateF(
            "UPDATE svy_qblk SET title = %s, show_questiontext = %s," .
            " show_blocktitle = %s, compress_view = %s WHERE questionblock_id = %s",
            array('text','text','text','integer', 'integer'),
            array($title, $show_questiontext, $show_blocktitle, $compress_view, $questionblock_id)
        );
    }

    /**
     * Deletes the constraints for a question
     * @todo move to constraint manager/repo
     */
    public function deleteConstraints(
        int $question_id
    ) : void {
        $ilDB = $this->db;
        $result = $ilDB->queryF(
            "SELECT constraint_fi FROM svy_qst_constraint WHERE question_fi = %s AND survey_fi = %s",
            array('integer','integer'),
            array($question_id, $this->getSurveyId())
        );
        $constraints = array();
        while ($row = $ilDB->fetchAssoc($result)) {
            $constraints[] = $row["constraint_fi"];
        }
        foreach ($constraints as $constraint_id) {
            $this->deleteConstraint($constraint_id);
        }
    }

    /**
     * Deletes a single constraint
     * @todo move to constraint manager/repo
     */
    public function deleteConstraint(
        int $constraint_id
    ) : void {
        $ilDB = $this->db;
        $affectedRows = $ilDB->manipulateF(
            "DELETE FROM svy_constraint WHERE constraint_id = %s",
            array('integer'),
            array($constraint_id)
        );
        $affectedRows = $ilDB->manipulateF(
            "DELETE FROM svy_qst_constraint WHERE constraint_fi = %s",
            array('integer'),
            array($constraint_id)
        );
    }

    /**
     * Returns the survey questions and questionblocks in an array
     * @todo move to survey question manager/repo, use dto
     */
    public function getSurveyQuestions(
        bool $with_answers = false
    ) : array {
        $ilDB = $this->db;
        // get questionblocks
        $all_questions = array();
        $result = $ilDB->queryF(
            "SELECT svy_qtype.type_tag, svy_qtype.plugin, svy_question.question_id, " .
            "svy_svy_qst.heading FROM svy_qtype, svy_question, svy_svy_qst WHERE svy_svy_qst.survey_fi = %s AND " .
            "svy_svy_qst.question_fi = svy_question.question_id AND svy_question.questiontype_fi = svy_qtype.questiontype_id " .
            "ORDER BY svy_svy_qst.sequence",
            array('integer'),
            array($this->getSurveyId())
        );
        while ($row = $ilDB->fetchAssoc($result)) {
            $add = true;
            if ($row["plugin"]) {
                if (!$this->isPluginActive($row["type_tag"])) {
                    $add = false;
                }
            }
            if ($add) {
                $question = self::_instanciateQuestion($row["question_id"]);
                $questionrow = $question->getQuestionDataArray($row["question_id"]);
                foreach ($row as $key => $value) {
                    $questionrow[$key] = $value;
                }
                $all_questions[$row["question_id"]] = $questionrow;
                $all_questions[$row["question_id"]]["usableForPrecondition"] = $question->usableForPrecondition();
                $all_questions[$row["question_id"]]["availableRelations"] = $question->getAvailableRelations();
            }
        }
        // get all questionblocks
        $questionblocks = array();
        if (count($all_questions)) {
            $result = $ilDB->queryF(
                "SELECT svy_qblk.*, svy_qblk_qst.question_fi FROM svy_qblk, svy_qblk_qst WHERE " .
                "svy_qblk.questionblock_id = svy_qblk_qst.questionblock_fi AND svy_qblk_qst.survey_fi = %s " .
                "AND " . $ilDB->in('svy_qblk_qst.question_fi', array_keys($all_questions), false, 'integer'),
                array('integer'),
                array($this->getSurveyId())
            );
            while ($row = $ilDB->fetchAssoc($result)) {
                $questionblocks[$row['question_fi']] = $row;
            }
        }
        
        foreach ($all_questions as $question_id => $row) {
            $constraints = $this->getConstraints($question_id);
            if (isset($questionblocks[$question_id])) {
                $all_questions[$question_id]["questionblock_title"] = $questionblocks[$question_id]['title'];
                $all_questions[$question_id]["questionblock_id"] = $questionblocks[$question_id]['questionblock_id'];
            } else {
                $all_questions[$question_id]["questionblock_title"] = "";
                $all_questions[$question_id]["questionblock_id"] = "";
            }
            $all_questions[$question_id]["constraints"] = $constraints;
            if ($with_answers) {
                $answers = array();
                $result = $ilDB->queryF(
                    "SELECT svy_variable.*, svy_category.title FROM svy_variable, svy_category " .
                    "WHERE svy_variable.question_fi = %s AND svy_variable.category_fi = svy_category.category_id " .
                    "ORDER BY sequence ASC",
                    array('integer'),
                    array($question_id)
                );
                if ($result->numRows() > 0) {
                    while ($data = $ilDB->fetchAssoc($result)) {
                        $answers[] = $data["title"];
                    }
                }
                $all_questions[$question_id]["answers"] = $answers;
            }
        }
        return $all_questions;
    }
    
    /**
     * Sets the obligatory states for questions in a survey from the questions form
     * @param array $obligatory_questions key is question id, value should be bool value
     * @todo move to survey question manager/repo
     */
    public function setObligatoryStates(
        array $obligatory_questions
    ) : void {
        $ilDB = $this->db;
        $result = $ilDB->queryF(
            "SELECT * FROM svy_svy_qst WHERE survey_fi = %s",
            array('integer'),
            array($this->getSurveyId())
        );
        if ($result->numRows()) {
            while ($row = $ilDB->fetchAssoc($result)) {
                if (!array_key_exists($row["question_fi"], $obligatory_questions)) {
                    $obligatory_questions[$row["question_fi"]] = 0;
                }
            }
        }
        // set the obligatory states in the database
        foreach ($obligatory_questions as $question_fi => $obligatory) {
            // #12420
            $ilDB->manipulate("UPDATE svy_question" .
                " SET obligatory = " . $ilDB->quote($obligatory, "integer") .
                " WHERE question_id = " . $ilDB->quote($question_fi, "integer"));
        }
    }

    /**
     * Returns the survey pages in an array (a page contains one or more questions)
     * @todo move to survey question manager/repo, use dto
     */
    public function getSurveyPages() : array
    {
        $ilDB = $this->db;
        // get questionblocks
        $all_questions = array();
        $result = $ilDB->queryF(
            "SELECT svy_question.*, svy_qtype.type_tag, svy_svy_qst.heading FROM " .
            "svy_question, svy_qtype, svy_svy_qst WHERE svy_svy_qst.survey_fi = %s AND " .
            "svy_svy_qst.question_fi = svy_question.question_id AND svy_question.questiontype_fi = svy_qtype.questiontype_id " .
            "ORDER BY svy_svy_qst.sequence",
            array('integer'),
            array($this->getSurveyId())
        );
        while ($row = $ilDB->fetchAssoc($result)) {
            $all_questions[$row["question_id"]] = $row;
        }
        // get all questionblocks
        $questionblocks = array();
        if (count($all_questions)) {
            $result = $ilDB->queryF(
                "SELECT svy_qblk.*, svy_qblk_qst.question_fi FROM svy_qblk, svy_qblk_qst " .
                "WHERE svy_qblk.questionblock_id = svy_qblk_qst.questionblock_fi AND svy_qblk_qst.survey_fi = %s " .
                "AND " . $ilDB->in('svy_qblk_qst.question_fi', array_keys($all_questions), false, 'integer'),
                array('integer'),
                array($this->getSurveyId())
            );
            while ($row = $ilDB->fetchAssoc($result)) {
                $questionblocks[$row['question_fi']] = $row;
            }
        }
        
        $all_pages = array();
        $pageindex = -1;
        $currentblock = "";
        foreach ($all_questions as $question_id => $row) {
            $constraints = array();
            if (isset($questionblocks[$question_id])) {
                if (!$currentblock or ($currentblock != $questionblocks[$question_id]['questionblock_id'])) {
                    $pageindex++;
                }
                $all_questions[$question_id]['page'] = $pageindex;
                $all_questions[$question_id]["questionblock_title"] = $questionblocks[$question_id]['title'];
                $all_questions[$question_id]["questionblock_id"] = $questionblocks[$question_id]['questionblock_id'];
                $all_questions[$question_id]["questionblock_show_questiontext"] = $questionblocks[$question_id]['show_questiontext'];
                $all_questions[$question_id]["questionblock_show_blocktitle"] = $questionblocks[$question_id]['show_blocktitle'];
                $all_questions[$question_id]["questionblock_compress_view"] = $questionblocks[$question_id]['compress_view'];
                $currentblock = $questionblocks[$question_id]['questionblock_id'];
            } else {
                $pageindex++;
                $all_questions[$question_id]['page'] = $pageindex;
                $all_questions[$question_id]["questionblock_title"] = "";
                $all_questions[$question_id]["questionblock_id"] = "";
                $all_questions[$question_id]["questionblock_show_questiontext"] = 1;
                $all_questions[$question_id]["questionblock_show_blocktitle"] = 1;
                $all_questions[$question_id]["questionblock_compress_view"] = false;
                $currentblock = "";
            }
            $constraints = $this->getConstraints($question_id);
            $all_questions[$question_id]["constraints"] = $constraints;
            if (!isset($all_pages[$pageindex])) {
                $all_pages[$pageindex] = array();
            }
            $all_pages[$pageindex][] = $all_questions[$question_id];
        }
        // calculate position percentage for every page
        $max = count($all_pages);
        $counter = 1;
        foreach ($all_pages as $index => $block) {
            foreach ($block as $blockindex => $question) {
                $all_pages[$index][$blockindex]["position"] = $counter / $max;
            }
            $counter++;
        }

        return $all_pages;
    }
    
    /**
     * Get current, previous or next page
     * @param int $active_page_question_id The database id of one of the questions on that page
     * @param int $direction The direction of the next page (-1 = previous page, 1 = next page)
     * @return ?array question data or null, if there is no previous/next page
     * @throws ilSurveyException
     * @todo move to execution manager
     */
    public function getNextPage(
        int $active_page_question_id,
        int $direction
    ) : ?array {
        $foundpage = -1;
        $pages = $this->getSurveyPages();
        if ($active_page_question_id === 0) {
            return $pages[0];
        }
        foreach ($pages as $key => $question_array) {
            foreach ($question_array as $question) {
                if ($active_page_question_id == $question["question_id"]) {
                    $foundpage = $key;
                }
            }
        }
        if ($foundpage === -1) {
            throw new ilSurveyException("nextPage: Current page not found.");
        } else {
            $foundpage += $direction;
            if ($foundpage < 0) {
                return null;
            }
            if ($foundpage >= count($pages)) {
                return null;
            }
            return $pages[$foundpage];
        }
    }
        
    /**
     * Returns the available question pools for the active user
     */
    public function getAvailableQuestionpools(
        bool $use_obj_id = false,
        bool $could_be_offline = false,
        bool $showPath = false,
        string $permission = "read"
    ) : array {
        return ilObjSurveyQuestionPool::_getAvailableQuestionpools($use_obj_id, $could_be_offline, $showPath, $permission);
    }
    
    /**
     * Returns a precondition with a given id
     * @todo move to constraint manager, introduce dto
     */
    public function getPrecondition(
        int $constraint_id
    ) : array {
        $ilDB = $this->db;
        
        $result = $ilDB->queryF(
            "SELECT svy_constraint.*, svy_relation.*, svy_qst_constraint.question_fi ref_question_fi FROM svy_qst_constraint, svy_constraint, " .
            "svy_relation WHERE svy_constraint.relation_fi = svy_relation.relation_id AND " .
            "svy_qst_constraint.constraint_fi = svy_constraint.constraint_id AND svy_constraint.constraint_id = %s",
            array('integer'),
            array($constraint_id)
        );
        $pc = array();
        if ($result->numRows()) {
            $pc = $ilDB->fetchAssoc($result);
        }
        return $pc;
    }
    
    /**
     * Returns the constraints to a given question or questionblock
     * @todo move to constraint manager, introduce dto
     */
    public function getConstraints(
        int $question_id
    ) : array {
        $ilDB = $this->db;
        
        $result_array = array();
        $result = $ilDB->queryF(
            "SELECT svy_constraint.*, svy_relation.* FROM svy_qst_constraint, svy_constraint, svy_relation " .
            "WHERE svy_constraint.relation_fi = svy_relation.relation_id AND " .
            "svy_qst_constraint.constraint_fi = svy_constraint.constraint_id AND svy_qst_constraint.question_fi = %s " .
            "AND svy_qst_constraint.survey_fi = %s",
            array('integer','integer'),
            array($question_id, $this->getSurveyId())
        );
        while ($row = $ilDB->fetchAssoc($result)) {
            $question_type = SurveyQuestion::_getQuestionType($row["question_fi"]);
            SurveyQuestion::_includeClass($question_type);
            $question = new $question_type();
            $question->loadFromDb($row["question_fi"]);
            $valueoutput = $question->getPreconditionValueOutput($row["value"]);
            $result_array[] = array("id" => $row["constraint_id"],
                                    "question" => $row["question_fi"],
                                    "short" => $row["shortname"],
                                    "long" => $row["longname"],
                                    "value" => $row["value"],
                                    "conjunction" => $row["conjunction"],
                                    "valueoutput" => $valueoutput
            );
        }
        return $result_array;
    }

    /**
     * @todo move to constraint manager/repo, use dto
     */
    public static function _getConstraints(
        int $survey_id
    ) : array {
        global $DIC;

        $ilDB = $DIC->database();
        $result_array = array();
        $result = $ilDB->queryF(
            "SELECT svy_qst_constraint.question_fi as for_question, svy_constraint.*, svy_relation.* " .
            "FROM svy_qst_constraint, svy_constraint, svy_relation WHERE svy_constraint.relation_fi = svy_relation.relation_id " .
            "AND svy_qst_constraint.constraint_fi = svy_constraint.constraint_id AND svy_qst_constraint.survey_fi = %s",
            array('integer'),
            array($survey_id)
        );
        while ($row = $ilDB->fetchAssoc($result)) {
            $result_array[] = array("id" => $row["constraint_id"],
                                    "for_question" => $row["for_question"],
                                    "question" => $row["question_fi"],
                                    "short" => $row["shortname"],
                                    "long" => $row["longname"],
                                    "relation_id" => $row["relation_id"],
                                    "value" => $row["value"],
                                    'conjunction' => $row['conjunction']
            );
        }
        return $result_array;
    }


    /**
     * Returns all variables (answer options/scale values) of a question
     * @todo move to (pool?) question manager/repo
     */
    public function getVariables(
        int $question_id
    ) : array {
        $ilDB = $this->db;
        
        $result_array = array();
        $result = $ilDB->queryF(
            "SELECT svy_variable.*, svy_category.title FROM svy_variable LEFT JOIN " .
            "svy_category ON svy_variable.category_fi = svy_category.category_id WHERE svy_variable.question_fi = %s " .
            "ORDER BY svy_variable.sequence",
            array('integer'),
            array($question_id)
        );
        while ($row = $ilDB->fetchObject($result)) {
            $result_array[$row->sequence] = $row;
        }
        return $result_array;
    }
    
    /**
     * Adds a constraint
     * @param int   $if_question_id question id of the question which defines a precondition
     * @param int   $relation relation id
     * @param float $value value compared with the relation
     * @param int   $conjunction
     * @return ?int
     * @todo move to constraint manager/repo
     */
    public function addConstraint(
        int $if_question_id,
        int $relation,
        float $value,
        int $conjunction
    ) : ?int {
        $ilDB = $this->db;

        $next_id = $ilDB->nextId('svy_constraint');
        $affectedRows = $ilDB->manipulateF(
            "INSERT INTO svy_constraint (constraint_id, question_fi, relation_fi, value, conjunction) VALUES " .
            "(%s, %s, %s, %s, %s)",
            array('integer','integer','integer','float', 'integer'),
            array($next_id, $if_question_id, $relation, $value, $conjunction)
        );
        if ($affectedRows) {
            return $next_id;
        } else {
            return null;
        }
    }


    /**
     * @todo move to constraint manager/repo
     */
    public function addConstraintToQuestion(
        int $to_question_id,
        int $constraint_id
    ) : void {
        $ilDB = $this->db;
        
        $next_id = $ilDB->nextId('svy_qst_constraint');
        $ilDB->manipulateF(
            "INSERT INTO svy_qst_constraint (question_constraint_id, survey_fi, question_fi, " .
            "constraint_fi) VALUES (%s, %s, %s, %s)",
            array('integer','integer','integer','integer'),
            array($next_id, $this->getSurveyId(), $to_question_id, $constraint_id)
        );
    }
    
    /**
     * @todo move to constraint manager/repo
     */
    public function updateConstraint(
        int $precondition_id,
        int $if_question_id,
        int $relation,
        float $value,
        int $conjunction
    ) : void {
        $ilDB = $this->db;
        $ilDB->manipulateF(
            "UPDATE svy_constraint SET question_fi = %s, relation_fi = %s, value = %s, conjunction = %s " .
            "WHERE constraint_id = %s",
            array('integer','integer','float','integer','integer'),
            array($if_question_id, $relation, $value, $conjunction, $precondition_id)
        );
    }

    /**
     * @todo move to constraint manager/repo
     */
    public function updateConjunctionForQuestions(
        array $questions,
        int $conjunction
    ) : void {
        $ilDB = $this->db;
        foreach ($questions as $question_id) {
            $ilDB->manipulateF(
                "UPDATE svy_constraint SET conjunction = %s " .
                "WHERE constraint_id IN (SELECT constraint_fi FROM svy_qst_constraint WHERE svy_qst_constraint.question_fi = %s)",
                array('integer','integer'),
                array($conjunction, $question_id)
            );
        }
    }

    /**
     * @todo move to constraint manager/repo
     */
    public function getAllRelations(
        bool $short_as_key = false
    ) : array {
        $ilDB = $this->db;
        
        // #7987
        $custom_order = array("equal", "not_equal", "less", "less_or_equal", "more", "more_or_equal");
        $custom_order = array_flip($custom_order);
        
        $result_array = array();
        $result = $ilDB->query("SELECT * FROM svy_relation");
        while ($row = $ilDB->fetchAssoc($result)) {
            if ($short_as_key) {
                $result_array[$row["shortname"]] = array("short" => $row["shortname"], "long" => $row["longname"], "id" => $row["relation_id"], "order" => $custom_order[$row["longname"]]);
            } else {
                $result_array[$row["relation_id"]] = array("short" => $row["shortname"], "long" => $row["longname"], "order" => $custom_order[$row["longname"]]);
            }
        }
        
        $result_array = ilArrayUtil::sortArray($result_array, "order", "ASC", true, true);
        foreach ($result_array as $idx => $item) {
            unset($result_array[$idx]["order"]);
        }
        
        return $result_array;
    }




    /**
     * Deletes the working data of a question in the database
     * @todo move to run(?) manager/repo
     */
    public function deleteWorkingData(
        int $question_id,
        int $active_id
    ) : void {
        $ilDB = $this->db;
        
        $affectedRows = $ilDB->manipulateF(
            "DELETE FROM svy_answer WHERE question_fi = %s AND active_fi = %s",
            array('integer','integer'),
            array($question_id, $active_id)
        );
    }
    
    /**
     * Gets the given answer data of a question in a run
     * @todo move to run(?) manager/repo
     */
    public function loadWorkingData(
        int $question_id,
        int $active_id
    ) : array {
        $ilDB = $this->db;
        $result_array = array();
        $result = $ilDB->queryF(
            "SELECT * FROM svy_answer WHERE question_fi = %s AND active_fi = %s",
            array('integer','integer'),
            array($question_id, $active_id)
        );
        if ($result->numRows() >= 1) {
            while ($row = $ilDB->fetchAssoc($result)) {
                $result_array[] = $row;
            }
        }
        return $result_array;
    }

    /**
     * Finishes the survey creating an entry in the database
     * @todo move to run manager
     */
    public function finishSurvey(
        int $finished_id,
        int $appr_id = 0
    ) : void {
        $ilDB = $this->db;
        
        $ilDB->manipulateF(
            "UPDATE svy_finished SET state = %s, tstamp = %s" .
            " WHERE survey_fi = %s AND finished_id = %s",
            array('text','integer','integer','integer'),
            array(1, time(), $this->getSurveyId(), $finished_id)
        );

        // self eval writes skills on finishing
        if ($this->getMode() === self::MODE_SELF_EVAL) {
            $user = $this->getUserDataFromActiveId($finished_id);
            $sskill = new ilSurveySkill($this);
            $sskill->writeAndAddSelfEvalSkills($user['usr_id']);
        }

        // self eval writes skills on finishing
        if ($this->getMode() === self::MODE_IND_FEEDB) {

            // we use a rater id like "a27" for anonymous or
            // "123" for non anonymous user
            // @todo: move this e.g. to participant manager
            $raters = $this->getRatersData($appr_id);
            $run_manager = $this->survey_service
                ->domain()
                ->execution()
                ->run($this, $this->user->getId());
            $run = $run_manager->getById($finished_id);
            $rater_id = "";
            if ($run->getUserId() !== 0 && $run->getUserId() !== ANONYMOUS_USER_ID) {
                $rater_id = $run->getUserId();
            } else {
                foreach ($raters as $id => $rater) {
                    if ($rater["code"] === $run->getCode()) {
                        $rater_id = $id;
                    }
                }
            }
            $sskill = new ilSurveySkill($this);
            //$sskill->writeAndAddSelfEvalSkills($user['usr_id']);
            $sskill->writeAndAddIndFeedbackSkills($finished_id, $appr_id, $rater_id);
        }

        $this->checkTutorNotification();
    }

    /**
     * Sets the number of the active survey page
     * @param int $finished_id id of the active user
     * @param int $page_id index of the page
     * @todo move to run manager/repo
     */
    public function setPage(
        int $finished_id,
        int $page_id
    ) : void {
        $ilDB = $this->db;

        $ilDB->manipulateF(
            "UPDATE svy_finished SET lastpage = %s WHERE finished_id = %s",
            array('integer','integer'),
            array(($page_id) ?: 0, $finished_id)
        );
    }

    /**
     * These mails are sent to tutors for each single participant
     * that finishes a survey. It includes the "additional participant data" text
     * that can be set in the settings screen.
     * @throws ilWACException
     */
    public function sendNotificationMail(
        int $a_user_id,
        string $a_anonymize_id,
        int $a_appr_id = 0
    ) : void {
        // #12755
        $placeholders = array(
            "FIRST_NAME" => "firstname",
            "LAST_NAME" => "lastname",
            "LOGIN" => "login",
            // old style
            "firstname" => "firstname"
        );

        //mailaddresses is just text split by commas.
        //sendMail can send emails if it gets an user id or an email as first parameter.
        $recipients = explode(",", $this->mailaddresses);
        foreach ($recipients as $recipient) {
            // #11298
            $ntf = new ilSystemNotification();
            $ntf->setLangModules(array("survey"));
            $ntf->setRefId($this->getRefId());
            $ntf->setSubjectLangId('finished_mail_subject');
                                
            $messagetext = $this->mailparticipantdata;
            $data = [];
            if (trim($messagetext)) {
                if (!$this->hasAnonymizedResults()) {
                    $data = ilObjUser::_getUserData(array($a_user_id));
                    $data = $data[0];
                }
                foreach ($placeholders as $key => $mapping) {
                    if ($this->hasAnonymizedResults()) { // #16480
                        $messagetext = str_replace('[' . $key . ']', '', $messagetext);
                    } else {
                        $messagetext = str_replace('[' . $key . ']', trim($data[$mapping]), $messagetext);
                    }
                }
                $ntf->setIntroductionDirect($messagetext);
            } else {
                $ntf->setIntroductionLangId('survey_notification_finished_introduction');
            }
                        
            // 360°? add appraisee data
            if ($a_appr_id) {
                $ntf->addAdditionalInfo(
                    'survey_360_appraisee',
                    ilUserUtil::getNamePresentation($a_appr_id)
                );
            }
            
            $active_id = $this->getActiveID($a_user_id, $a_anonymize_id, $a_appr_id);
            $ntf->addAdditionalInfo(
                'results',
                $this->getParticipantTextResults($active_id),
                true
            );
                                    
            $ntf->setGotoLangId('survey_notification_tutor_link');
            $ntf->setReasonLangId('survey_notification_finished_reason');

            if (is_numeric($recipient)) {
                $lng = $ntf->getUserLanguage($recipient);
                $ntf->sendMail(array($recipient), null);
            } else {
                $recipient = trim($recipient);
                $user_ids = ilObjUser::getUserIdsByEmail($recipient);
                if (empty($user_ids)) {
                    $ntf->sendMail(array($recipient), null);
                } else {
                    foreach ($user_ids as $user_id) {
                        $lng = $ntf->getUserLanguage($user_id);
                        $ntf->sendMail(array($user_id), null);
                    }
                }
            }
        }
    }

    protected function getParticipantTextResults(
        int $active_id
    ) : string {
        $textresult = "";
        $userResults = $this->getUserSpecificResults(array($active_id));
        $questions = $this->getSurveyQuestions(true);
        $questioncounter = 1;
        foreach ($questions as $question_id => $question_data) {
            $textresult .= $questioncounter++ . ". " . $question_data["title"] . "\n";
            $found = $userResults[$question_id][$active_id];
            $text = "";
            if (is_array($found)) {
                $text = implode("\n", $found);
            } else {
                $text = $found;
            }
            if ($text === '') {
                $text = self::getSurveySkippedValue();
            }
            $text = str_replace("<br />", "\n", $text);
            $textresult .= $text . "\n\n";
        }
        return $textresult;
    }

    /**
     * Get run id
     * @todo move to run manager/repo
     */
    public function getActiveID(
        int $user_id,
        string $anonymize_id,
        int $appr_id
    ) : ?int {
        $ilDB = $this->db;

        // #15031 - should not matter if code was used by registered or anonymous (each code must be unique)
        if ($anonymize_id) {
            $result = $ilDB->queryF(
                "SELECT finished_id FROM svy_finished" .
                " WHERE survey_fi = %s AND anonymous_id = %s AND appr_id = %s",
                array('integer','text','integer'),
                array($this->getSurveyId(), $anonymize_id, $appr_id)
            );
        } else {
            $result = $ilDB->queryF(
                "SELECT finished_id FROM svy_finished" .
                " WHERE survey_fi = %s AND user_fi = %s AND appr_id = %s",
                array('integer','integer','integer'),
                array($this->getSurveyId(), $user_id, $appr_id)
            );
        }
        if ($result->numRows() === 0) {
            return null;
        } else {
            $row = $ilDB->fetchAssoc($result);
            return (int) $row["finished_id"];
        }
    }
    
    /**
     * Returns the question id of the last active page a user visited in a survey
     * @todo move to run manager/repo
     */
    public function getLastActivePage(
        int $active_id
    ) : ?int {
        $ilDB = $this->db;
        $result = $ilDB->queryF(
            "SELECT lastpage FROM svy_finished WHERE finished_id = %s",
            array('integer'),
            array($active_id)
        );
        if ($row = $ilDB->fetchAssoc($result)) {
            return (int) $row["lastpage"];
        }
        return null;
    }

    /**
     * Checks if a constraint is valid
     *
     * @param array $constraint_data constraint data
     * @param ?array $working_data user input of the related question
     * @return bool true if the constraint is valid, otherwise false
     * @todo move to run/constraint manager
     */
    public function checkConstraint(
        array $constraint_data,
        ?array $working_data
    ) : bool {
        if (!is_array($working_data) || count($working_data) === 0) {
            return 0;
        }
        
        if ((count($working_data) === 1) and (strcmp($working_data[0]["value"], "") === 0)) {
            return 0;
        }
        
        $found = false;
        foreach ($working_data as $data) {
            switch ($constraint_data["short"]) {
                case "<":
                    if ($data["value"] < $constraint_data["value"]) {
                        $found = true;
                    }
                    break;
                    
                case "<=":
                    if ($data["value"] <= $constraint_data["value"]) {
                        $found = true;
                    }
                    break;
                
                case "=":
                    if ($data["value"] == $constraint_data["value"]) {
                        $found = true;
                    }
                    break;
                                                                            
                case "<>":
                    if ($data["value"] <> $constraint_data["value"]) {
                        $found = true;
                    }
                    break;
                    
                case ">=":
                    if ($data["value"] >= $constraint_data["value"]) {
                        $found = true;
                    }
                    break;
                    
                case ">":
                    if ($data["value"] > $constraint_data["value"]) {
                        $found = true;
                    }
                    break;
            }
            if ($found) {
                break;
            }
        }
        
        return (int) $found;
    }

    /**
     * @todo move to run manager
     */
    public static function _hasDatasets(
        int $survey_id
    ) : bool {
        global $DIC;

        $ilDB = $DIC->database();
        
        $result = $ilDB->queryF(
            "SELECT finished_id FROM svy_finished WHERE survey_fi = %s",
            array('integer'),
            array($survey_id)
        );
        return (bool) $result->numRows();
    }

    /**
     * Get run ids
     * @todo move to run manager
     * @return int[]
     */
    public function getSurveyFinishedIds() : array
    {
        $ilDB = $this->db;

        $users = array();
        $result = $ilDB->queryF(
            "SELECT * FROM svy_finished WHERE survey_fi = %s",
            array('integer'),
            array($this->getSurveyId())
        );
        if ($result->numRows()) {
            while ($row = $ilDB->fetchAssoc($result)) {
                $users[] = (int) $row["finished_id"];
            }
        }
        return $users;
    }
    
    /**
     * Calculates the evaluation data for the user specific results
     * @param int[] $finished_ids
     * @todo move to evaluation manager
     */
    public function getUserSpecificResults(
        array $finished_ids
    ) : array {
        $evaluation = array();
        
        foreach (array_keys($this->getSurveyQuestions()) as $question_id) {
            // get question instance
            $question_type = SurveyQuestion::_getQuestionType($question_id);
            SurveyQuestion::_includeClass($question_type);
            $question = new $question_type();
            $question->loadFromDb($question_id);
            
            $q_eval = SurveyQuestion::_instanciateQuestionEvaluation($question_id, $finished_ids);
            $q_res = $q_eval->getResults();
            
            $data = array();
            foreach ($finished_ids as $user_id) {
                $data[$user_id] = $q_eval->parseUserSpecificResults($q_res, $user_id);
            }
            
            $evaluation[$question_id] = $data;
        }
        
        return $evaluation;
    }
    
    /**
     * Returns run information
     * @todo move to run manager/repo
     */
    public function getUserDataFromActiveId(
        int $active_id,
        bool $force_non_anonymous = false
    ) : array {
        $ilDB = $this->db;

        $surveySetting = new ilSetting("survey");
        $use_anonymous_id = $surveySetting->get("use_anonymous_id");
        $result = $ilDB->queryF(
            "SELECT * FROM svy_finished WHERE finished_id = %s",
            array('integer'),
            array($active_id)
        );
        $row = array();
        $foundrows = $result->numRows();
        if ($foundrows) {
            $row = $ilDB->fetchAssoc($result);
        }
        $name = ($use_anonymous_id) ? $row["anonymous_id"] : $this->lng->txt("anonymous");
        $userdata = array(
            "fullname" => $name,
            "sortname" => $name,
            "firstname" => "",
            "lastname" => "",
            "login" => "",
            "gender" => "",
            "active_id" => (string) $active_id
        );
        if ($foundrows) {
            if (($row["user_fi"] > 0) &&
                    (($row["user_fi"] != ANONYMOUS_USER_ID &&
                        !$this->hasAnonymizedResults() &&
                        !$this->get360Mode()) ||  // 360° uses ANONYMIZE_CODE_ALL which is wrong - see ilObjSurveyGUI::afterSave()
                        $force_non_anonymous)) {
                if (ilObjUser::_lookupLogin($row["user_fi"]) === '') {
                    $userdata["fullname"] = $userdata["sortname"] = $this->lng->txt("deleted_user");
                } else {
                    $user = new ilObjUser($row["user_fi"]);
                    $userdata['usr_id'] = $row['user_fi'];
                    $userdata["fullname"] = $user->getFullname();
                    $gender = $user->getGender();
                    if (strlen($gender) === 1) {
                        $gender = $this->lng->txt("gender_$gender");
                    }
                    $userdata["gender"] = $gender;
                    $userdata["firstname"] = $user->getFirstname();
                    $userdata["lastname"] = $user->getLastname();
                    $userdata["sortname"] = $user->getLastname() . ", " . $user->getFirstname();
                    $userdata["login"] = $user->getLogin();
                }
            }
            if ($row["user_fi"] == 0 || $row["user_fi"] == ANONYMOUS_USER_ID) {
                $code = $this->code_manager->getByUserKey($row["anonymous_id"]);
                if (!is_null($code) && $this->feature_config->usesAppraisees()) {
                    $userdata["firstname"] = $code->getFirstName();
                    $userdata["lastname"] = $code->getLastName();
                    $userdata["sortname"] = $code->getLastName() . ", " . $code->getFirstName();
                }
            }
        }
        return $userdata;
    }
    
    /**
     * Calculates the evaluation data for a given run
     * @todo move to evaluation manager
     */
    public function getEvaluationByUser(
        array $questions,
        int $active_id
    ) : array {
        $ilDB = $this->db;
        
        // collect all answers
        $answers = array();
        $result = $ilDB->queryF(
            "SELECT * FROM svy_answer WHERE active_fi = %s",
            array('integer'),
            array($active_id)
        );
        while ($row = $ilDB->fetchAssoc($result)) {
            if (!is_array($answers[$row["question_fi"]])) {
                $answers[$row["question_fi"]] = array();
            }
            $answers[$row["question_fi"]][] = $row;
        }
        $userdata = $this->getUserDataFromActiveId($active_id);
        $resultset = array(
            "name" => $userdata["fullname"],
            "firstname" => $userdata["firstname"],
            "lastname" => $userdata["lastname"],
            "login" => $userdata["login"],
            "gender" => $userdata["gender"],
            "answers" => array()
        );
        foreach ($questions as $key => $question) {
            if (array_key_exists($key, $answers)) {
                $resultset["answers"][$key] = $answers[$key];
            } else {
                $resultset["answers"][$key] = array();
            }
            sort($resultset["answers"][$key]);
        }
        return $resultset;
    }
    
    /**
     * Retrieve data for question browser
     * @todo move to survey question manager, use dto
     */
    public function getQuestionsTable(
        array $arrFilter
    ) : array {
        $ilDB = $this->db;
        $where = "";
        if (array_key_exists('title', $arrFilter) && strlen($arrFilter['title'])) {
            $where .= " AND " . $ilDB->like('svy_question.title', 'text', "%%" . $arrFilter['title'] . "%%");
        }
        if (array_key_exists('description', $arrFilter) && strlen($arrFilter['description'])) {
            $where .= " AND " . $ilDB->like('svy_question.description', 'text', "%%" . $arrFilter['description'] . "%%");
        }
        if (array_key_exists('author', $arrFilter) && strlen($arrFilter['author'])) {
            $where .= " AND " . $ilDB->like('svy_question.author', 'text', "%%" . $arrFilter['author'] . "%%");
        }
        if (array_key_exists('type', $arrFilter) && strlen($arrFilter['type'])) {
            $where .= " AND svy_qtype.type_tag = " . $ilDB->quote($arrFilter['type'], 'text');
        }
        if (array_key_exists('spl', $arrFilter) && strlen($arrFilter['spl'])) {
            $where .= " AND svy_question.obj_fi = " . $ilDB->quote($arrFilter['spl'], 'integer');
        }

        $spls = $this->getAvailableQuestionpools(true, false, false);
        $forbidden = " AND " . $ilDB->in('svy_question.obj_fi', array_keys($spls), false, 'integer');
        $forbidden .= " AND svy_question.complete = " . $ilDB->quote("1", 'text');
        $existing = "";
        $existing_questions = $this->getExistingQuestions();
        if (count($existing_questions)) {
            $existing = " AND " . $ilDB->in('svy_question.question_id', $existing_questions, true, 'integer');
        }
        
        $trans = ilObjSurveyQuestionPool::_getQuestionTypeTranslations();
        
        $query_result = $ilDB->query("SELECT svy_question.*, svy_qtype.type_tag, svy_qtype.plugin, object_reference.ref_id" .
            " FROM svy_question, svy_qtype, object_reference" .
            " WHERE svy_question.original_id IS NULL" . $forbidden . $existing .
            " AND svy_question.obj_fi = object_reference.obj_id AND svy_question.tstamp > 0" .
            " AND svy_question.questiontype_fi = svy_qtype.questiontype_id " . $where);

        $rows = array();
        if ($query_result->numRows()) {
            while ($row = $ilDB->fetchAssoc($query_result)) {
                if (array_key_exists('spl_txt', $arrFilter) && strlen($arrFilter['spl_txt'])) {
                    if (stripos($spls[$row["obj_fi"]], $arrFilter['spl_txt']) === false) {
                        continue;
                    }
                }
                
                $row['ttype'] = $trans[$row['type_tag']];
                if ($row["plugin"]) {
                    if ($this->isPluginActive($row["type_tag"])) {
                        $rows[] = $row;
                    }
                } else {
                    $rows[] = $row;
                }
            }
        }
        return $rows;
    }

    /**
     * Retrieve data for question block browser
     * @todo move to survey question manager, use dto
     */
    public function getQuestionblocksTable(
        array $arrFilter
    ) : array {
        $ilDB = $this->db;
        
        $where = "";
        if (array_key_exists('title', $arrFilter) && strlen($arrFilter['title'])) {
            $where .= " AND " . $ilDB->like('svy_qblk.title', 'text', "%%" . $arrFilter['title'] . "%%");
        }

        $query_result = $ilDB->query("SELECT svy_qblk.*, svy_svy.obj_fi FROM svy_qblk , svy_qblk_qst, svy_svy WHERE " .
            "svy_qblk.questionblock_id = svy_qblk_qst.questionblock_fi AND svy_svy.survey_id = svy_qblk_qst.survey_fi " .
            "$where GROUP BY svy_qblk.questionblock_id, svy_qblk.title, svy_qblk.show_questiontext,  svy_qblk.show_blocktitle, " .
            "svy_qblk.owner_fi, svy_qblk.tstamp, svy_svy.obj_fi");
        $rows = array();
        if ($query_result->numRows()) {
            $survey_ref_ids = ilUtil::_getObjectsByOperations("svy", "write");
            $surveytitles = array();
            foreach ($survey_ref_ids as $survey_ref_id) {
                $survey_id = ilObject::_lookupObjId($survey_ref_id);
                $surveytitles[$survey_id] = ilObject::_lookupTitle($survey_id);
            }
            while ($row = $ilDB->fetchAssoc($query_result)) {
                $questions_array = $this->getQuestionblockQuestions($row["questionblock_id"]);
                $counter = 1;
                foreach ($questions_array as $key => $value) {
                    $questions_array[$key] = "$counter. $value";
                    $counter++;
                }
                if (strlen($surveytitles[$row["obj_fi"]])) { // only questionpools which are not in trash
                    $rows[$row["questionblock_id"]] = array(
                        "questionblock_id" => $row["questionblock_id"],
                        "title" => $row["title"],
                        "svy" => $surveytitles[$row["obj_fi"]],
                        "contains" => implode(", ", $questions_array),
                        "owner" => $row["owner_fi"]
                    );
                }
            }
        }
        return $rows;
    }

    /**
     * Returns a QTI xml representation of the survey
     * @todo move to export sub-service
     */
    public function toXML() : string
    {
        $a_xml_writer = new ilXmlWriter();
        // set xml header
        $a_xml_writer->xmlHeader();
        $attrs = array(
            "xmlns:xsi" => "http://www.w3.org/2001/XMLSchema-instance",
            "xsi:noNamespaceSchemaLocation" => "https://www.ilias.de/download/xsd/ilias_survey_4_2.xsd"
        );
        $a_xml_writer->xmlStartTag("surveyobject", $attrs);
        $attrs = array(
            "id" => $this->getSurveyId(),
            "title" => $this->getTitle()
        );
        $a_xml_writer->xmlStartTag("survey", $attrs);
        
        $a_xml_writer->xmlElement("description", null, $this->getDescription());
        $a_xml_writer->xmlElement("author", null, $this->getAuthor());
        $a_xml_writer->xmlStartTag("objectives");
        $attrs = array(
            "label" => "introduction"
        );
        $this->addMaterialTag($a_xml_writer, $this->getIntroduction(), true, true, $attrs);
        $attrs = array(
            "label" => "outro"
        );
        $this->addMaterialTag($a_xml_writer, $this->getOutro(), true, true, $attrs);
        $a_xml_writer->xmlEndTag("objectives");

        if ($this->getAnonymize()) {
            $attribs = array("enabled" => "1");
        } else {
            $attribs = array("enabled" => "0");
        }
        $a_xml_writer->xmlElement("anonymisation", $attribs);
        $a_xml_writer->xmlStartTag("restrictions");
        if ($this->getAnonymize() === 2) {
            $attribs = array("type" => "free");
        } else {
            $attribs = array("type" => "restricted");
        }
        $a_xml_writer->xmlElement("access", $attribs);
        if ($this->getStartDate()) {
            $attrs = array("type" => "date");
            preg_match("/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/", $this->getStartDate(), $matches);
            $a_xml_writer->xmlElement("startingtime", $attrs, sprintf("%04d-%02d-%02dT%02d:%02d:00", $matches[1], $matches[2], $matches[3], $matches[4], $matches[5]));
        }
        if ($this->getEndDate()) {
            $attrs = array("type" => "date");
            preg_match("/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/", $this->getEndDate(), $matches);
            $a_xml_writer->xmlElement("endingtime", $attrs, sprintf("%04d-%02d-%02dT%02d:%02d:00", $matches[1], $matches[2], $matches[3], $matches[4], $matches[5]));
        }
        $a_xml_writer->xmlEndTag("restrictions");
        
        // constraints
        $pages = $this->getSurveyPages();
        $hasconstraints = false;
        foreach ($pages as $question_array) {
            foreach ($question_array as $question) {
                if (count($question["constraints"])) {
                    $hasconstraints = true;
                }
            }
        }
        
        if ($hasconstraints) {
            $a_xml_writer->xmlStartTag("constraints");
            foreach ($pages as $question_array) {
                foreach ($question_array as $question) {
                    if (count($question["constraints"])) {
                        // found constraints
                        foreach ($question["constraints"] as $constraint) {
                            $attribs = array(
                                "sourceref" => $question["question_id"],
                                "destref" => $constraint["question"],
                                "relation" => $constraint["short"],
                                "value" => $constraint["value"],
                                "conjunction" => $constraint["conjunction"]
                            );
                            $a_xml_writer->xmlElement("constraint", $attribs);
                        }
                    }
                }
            }
            $a_xml_writer->xmlEndTag("constraints");
        }
        
        // add the rest of the preferences in qtimetadata tags, because there is no correspondent definition in QTI
        $a_xml_writer->xmlStartTag("metadata");

        $custom_properties = array();
        $custom_properties["evaluation_access"] = $this->getEvaluationAccess();
        $custom_properties["status"] = !$this->getOfflineStatus();
        $custom_properties["display_question_titles"] = $this->getShowQuestionTitles();
        $custom_properties["pool_usage"] = (int) $this->getPoolUsage();
        
        $custom_properties["own_results_view"] = (int) $this->hasViewOwnResults();
        $custom_properties["own_results_mail"] = (int) $this->hasMailOwnResults();
        $custom_properties["confirmation_mail"] = (int) $this->hasMailConfirmation();
        
        $custom_properties["anon_user_list"] = (int) $this->hasAnonymousUserList();
        $custom_properties["mode"] = $this->getMode();
        $custom_properties["mode_360_self_eval"] = (int) $this->get360SelfEvaluation();
        $custom_properties["mode_360_self_rate"] = (int) $this->get360SelfRaters();
        $custom_properties["mode_360_self_appr"] = (int) $this->get360SelfAppraisee();
        $custom_properties["mode_360_results"] = $this->get360Results();
        $custom_properties["mode_skill_service"] = (int) $this->getSkillService();
        $custom_properties["mode_self_eval_results"] = $this->getSelfEvaluationResults();
        
        
        // :TODO: skills?
                
        // reminder/tutor notification are (currently?) not exportable
        
        foreach ($custom_properties as $label => $value) {
            $a_xml_writer->xmlStartTag("metadatafield");
            $a_xml_writer->xmlElement("fieldlabel", null, $label);
            $a_xml_writer->xmlElement("fieldentry", null, $value);
            $a_xml_writer->xmlEndTag("metadatafield");
        }

        $a_xml_writer->xmlStartTag("metadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "SCORM");
        $md = new ilMD($this->getId(), 0, $this->getType());
        $writer = new ilXmlWriter();
        $md->toXML($writer);
        $metadata = $writer->xmlDumpMem();
        $a_xml_writer->xmlElement("fieldentry", null, $metadata);
        $a_xml_writer->xmlEndTag("metadatafield");
        
        $a_xml_writer->xmlEndTag("metadata");
        $a_xml_writer->xmlEndTag("survey");

        $attribs = array("id" => $this->getId());
        $a_xml_writer->xmlStartTag("surveyquestions", $attribs);
        // add questionblock descriptions
        foreach ($pages as $question_array) {
            if (count($question_array) > 1) {
                $attribs = array("id" => $question_array[0]["question_id"]);
                $attribs = array(
                    "showQuestiontext" => $question_array[0]["questionblock_show_questiontext"],
                    "showBlocktitle" => $question_array[0]["questionblock_show_blocktitle"],
                    "compressView" => $question_array[0]["questionblock_compress_view"]
                );
                $a_xml_writer->xmlStartTag("questionblock", $attribs);
                if (strlen($question_array[0]["questionblock_title"])) {
                    $a_xml_writer->xmlElement("questionblocktitle", null, $question_array[0]["questionblock_title"]);
                }
            }
            foreach ($question_array as $question) {
                if (strlen($question["heading"])) {
                    $a_xml_writer->xmlElement("textblock", null, $question["heading"]);
                }
                $questionObject = self::_instanciateQuestion($question["question_id"]);
                //questionObject contains all the fields from the database. (loadFromDb)
                //we don't need the value from svy_qst_oblig table, we already have the values from svy_question table.
                //if ($questionObject !== FALSE) $questionObject->insertXML($a_xml_writer, FALSE, $obligatory_states[$question["question_id"]]);
                if ($questionObject !== false) {
                    $questionObject->insertXML($a_xml_writer, false);
                }
            }
            if (count($question_array) > 1) {
                $a_xml_writer->xmlEndTag("questionblock");
            }
        }

        $a_xml_writer->xmlEndTag("surveyquestions");
        $a_xml_writer->xmlEndTag("surveyobject");
        $xml = $a_xml_writer->xmlDumpMem(false);
        return $xml;
    }

    /**
     * Creates an instance of a question with a given question id
     * @todo move to factory in pool module
     */
    public static function _instanciateQuestion(
        int $question_id
    ) : ?SurveyQuestion {
        if ($question_id < 1) {
            return null;
        }
        $question_type = SurveyQuestion::_getQuestionType($question_id);
        if ($question_type === '') {
            return null;
        }
        SurveyQuestion::_includeClass($question_type);
        $question = new $question_type();
        $question->loadFromDb($question_id);
        return $question;
    }

    /**
     * Locates the import directory and the xml file in a directory with an unzipped import file
     * @return array An associative array containing "dir" (import directory) and "xml" (xml file)
     * @todo move to export sub-service
     */
    public function locateImportFiles(
        string $a_dir
    ) : ?array {
        if (!is_dir($a_dir) || is_int(strpos($a_dir, ".."))) {
            return null;
        }
        $importDirectory = "";
        $xmlFile = "";

        $current_dir = opendir($a_dir);
        $files = array();
        while ($entryname = readdir($current_dir)) {
            $files[] = $entryname;
        }

        foreach ($files as $file) {
            if (is_dir($a_dir . "/" . $file) and ($file !== "." and $file !== "..")) {
                // found directory created by zip
                $importDirectory = $a_dir . "/" . $file;
            }
        }
        closedir($current_dir);
        if ($importDirectory !== '') {
            // find the xml file
            $current_dir = opendir($importDirectory);
            $files = array();
            while ($entryname = readdir($current_dir)) {
                $files[] = $entryname;
            }
            foreach ($files as $file) {
                if (is_file($importDirectory . "/" . $file) &&
                    ($file !== "." && $file !== "..") &&
                    (preg_match("/^[0-9]{10}__[0-9]+__(svy_)*[0-9]+\.[A-Za-z]{1,3}$/", $file) ||
                        preg_match("/^[0-9]{10}__[0-9]+__(survey__)*[0-9]+\.[A-Za-z]{1,3}$/", $file))) {
                    // found xml file
                    $xmlFile = $importDirectory . "/" . $file;
                }
            }
        }
        return array("dir" => $importDirectory, "xml" => $xmlFile);
    }

    /**
     * @throws ilException
     * @throws ilImportException
     * @throws ilInvalidSurveyImportFileException
     * @return string error
     * @todo move to export sub-service
     */
    public function importObject(
        array $file_info,
        int $svy_qpl_id
    ) : string {
        if ($svy_qpl_id < 1) {
            $svy_qpl_id = -1;
        }
        // check if file was uploaded
        $source = $file_info["tmp_name"];
        $error = "";
        if (($source === 'none') || (!$source) || $file_info["error"] > UPLOAD_ERR_OK) {
            $error = $this->lng->txt("import_no_file_selected");
        }
        // check correct file type
        $isXml = false;
        $isZip = false;
        if ((strcmp($file_info["type"], "text/xml") === 0) || (strcmp($file_info["type"], "application/xml") === 0)) {
            $this->log->debug("isXML");
            $isXml = true;
        }
        // too many different mime-types, so we use the suffix
        $suffix = pathinfo($file_info["name"]);
        if (strcmp(strtolower($suffix["extension"]), "zip") === 0) {
            $this->log->debug("isZip");
            $isZip = true;
        }
        if (!$isXml && !$isZip) {
            $error = $this->lng->txt("import_wrong_file_type");
            $this->log->debug("Survey: Import error. Filetype was \"" . $file_info["type"] . "\"");
        }
        if ($error === '') {
            // import file as a survey
            $import_dir = $this->getImportDirectory();
            $import_subdir = "";
            $importfile = "";
            if ($isZip) {
                $importfile = $import_dir . "/" . $file_info["name"];
                ilFileUtils::moveUploadedFile($source, $file_info["name"], $importfile);
                ilFileUtils::unzip($importfile);
                $found = $this->locateImportFiles($import_dir);
                if (!((strlen($found["dir"]) > 0) && (strlen($found["xml"]) > 0))) {
                    $error = $this->lng->txt("wrong_import_file_structure");
                    return $error;
                }
                $importfile = $found["xml"];
                $import_subdir = $found["dir"];
            } else {
                $importfile = tempnam($import_dir, "survey_import");
                ilFileUtils::moveUploadedFile($source, $file_info["name"], $importfile);
            }

            $this->log->debug("Import file = $importfile");
            $this->log->debug("Import subdir = $import_subdir");

            $fh = fopen($importfile, 'rb');
            if (!$fh) {
                $error = $this->lng->txt("import_error_opening_file");
                return $error;
            }
            $xml = fread($fh, filesize($importfile));
            $result = fclose($fh);
            if (!$result) {
                $error = $this->lng->txt("import_error_closing_file");
                return $error;
            }

            $this->import_manager->clearMobs();
            if (strpos($xml, "questestinterop")) {
                throw new ilInvalidSurveyImportFileException("Unsupported survey version (< 3.8) found.");
            } else {
                $this->log->debug("survey id = " . $this->getId());
                $this->log->debug("question pool id = " . $svy_qpl_id);

                $imp = new ilImport();
                $config = $imp->getConfig("Modules/Survey");
                $config->setQuestionPoolID($svy_qpl_id);
                $imp->getMapping()->addMapping("Modules/Survey", "svy", 0, $this->getId());
                $imp->importFromDirectory($import_subdir, "svy", "Modules/Survey");
                $this->log->debug("config(Modules/survey)->getQuestionPoolId =" . $config->getQuestionPoolID());
            }
        }
        return $error;
    }

    public function cloneObject(int $target_id, int $copy_id = 0, bool $omit_tree = false) : ?ilObject
    {
        $ilDB = $this->db;
        
        $this->loadFromDb();

        //survey mode
        $svy_type = $this->getMode();

        /** @var ilObjSurvey $newObj */
        $newObj = parent::cloneObject($target_id, $copy_id, $omit_tree);
        $this->cloneMetaData($newObj);
        $newObj->updateMetaData();
        
        $newObj->setAuthor($this->getAuthor());
        $newObj->setIntroduction($this->getIntroduction());
        $newObj->setOutro($this->getOutro());
        $newObj->setEvaluationAccess($this->getEvaluationAccess());
        $newObj->setStartDate($this->getStartDate());
        $newObj->setEndDate($this->getEndDate());
        $newObj->setAnonymize($this->getAnonymize());
        $newObj->setShowQuestionTitles($this->getShowQuestionTitles());
        $newObj->setPoolUsage($this->getPoolUsage());
        $newObj->setViewOwnResults($this->hasViewOwnResults());
        $newObj->setMailOwnResults($this->hasMailOwnResults());
        $newObj->setMailConfirmation($this->hasMailConfirmation());
        $newObj->setAnonymousUserList($this->hasAnonymousUserList());

        $newObj->setMode($this->getMode());
        $newObj->set360SelfEvaluation($this->get360SelfEvaluation());
        $newObj->set360SelfAppraisee($this->get360SelfAppraisee());
        $newObj->set360SelfRaters($this->get360SelfRaters());
        $newObj->set360Results($this->get360Results());
        $newObj->setSkillService($this->getSkillService());

        // reminder/notification
        $newObj->setReminderStatus($this->getReminderStatus());
        $newObj->setReminderStart($this->getReminderStart());
        $newObj->setReminderEnd($this->getReminderEnd());
        $newObj->setReminderFrequency($this->getReminderFrequency());
        $newObj->setReminderTarget($this->getReminderTarget());
        $newObj->setReminderTemplate($this->getReminderTemplate());
        // reminder_last_sent must not be copied!
        $newObj->setTutorNotificationStatus($this->getTutorNotificationStatus());
        $newObj->setTutorNotificationRecipients($this->getTutorNotificationRecipients());
        $newObj->setTutorNotificationTarget($this->getTutorNotificationTarget());
        $newObj->setTutorResultsStatus($this->getTutorResultsStatus());
        $newObj->setTutorResultsRecipients($this->getTutorResultsRecipients());

        $newObj->setMailNotification($this->getMailNotification());
        $newObj->setMailAddresses($this->getMailAddresses());
        $newObj->setMailParticipantData($this->getMailParticipantData());

        $question_pointer = array();
        // clone the questions
        $mapping = array();

        foreach ($this->questions as $key => $question_id) {
            /** @var $question SurveyQuestion */
            $question = self::_instanciateQuestion($question_id);
            if ($question) { // #10824
                $question->id = -1;
                $original_id = SurveyQuestion::_getOriginalId($question_id, false);
                $question->setObjId($newObj->getId());
                $question->saveToDb($original_id);
                $newObj->questions[$key] = $question->getId();
                $question_pointer[$question_id] = $question->getId();
                $mapping[$question_id] = $question->getId();
            }
        }

        //copy online status if object is not the root copy object
        $cp_options = ilCopyWizardOptions::_getInstance($copy_id);

        if (!$cp_options->isRootNode($this->getRefId())) {
            $newObj->setOfflineStatus($this->getOfflineStatus());
        }

        $newObj->saveToDb();
        $newObj->cloneTextblocks($mapping);
        
        // #14929
        if (($svy_type === self::MODE_360 || $svy_type === self::MODE_SELF_EVAL) &&
            $this->getSkillService()) {
            $src_skills = new ilSurveySkill($this);
            $tgt_skills = new ilSurveySkill($newObj);
            
            foreach ($mapping as $src_qst_id => $tgt_qst_id) {
                $qst_skill = $src_skills->getSkillForQuestion($src_qst_id);
                if ($qst_skill) {
                    $tgt_skills->addQuestionSkillAssignment($tgt_qst_id, $qst_skill["base_skill_id"], $qst_skill["tref_id"]);
                }
            }
        }

        // clone the questionblocks
        $questionblocks = array();
        $questionblock_questions = array();
        $result = $ilDB->queryF(
            "SELECT * FROM svy_qblk_qst WHERE survey_fi = %s",
            array('integer'),
            array($this->getSurveyId())
        );
        if ($result->numRows() > 0) {
            while ($row = $ilDB->fetchAssoc($result)) {
                $questionblock_questions[] = $row;
                $questionblocks[$row["questionblock_fi"]] = $row["questionblock_fi"];
            }
        }
        // create new questionblocks
        foreach ($questionblocks as $key => $value) {
            $questionblock = self::_getQuestionblock($key);
            $questionblock_id = self::_addQuestionblock(
                $questionblock["title"],
                $questionblock["owner_fi"],
                $questionblock["show_questiontext"],
                $questionblock["show_blocktitle"],
                $questionblock["compress_view"]
            );
            $questionblocks[$key] = $questionblock_id;
        }
        // create new questionblock questions
        foreach ($questionblock_questions as $key => $value) {
            if ($questionblocks[$value["questionblock_fi"]] &&
                $question_pointer[$value["question_fi"]]) {
                $next_id = $ilDB->nextId('svy_qblk_qst');
                $affectedRows = $ilDB->manipulateF(
                    "INSERT INTO svy_qblk_qst (qblk_qst_id, survey_fi, questionblock_fi, question_fi) " .
                    "VALUES (%s, %s, %s, %s)",
                    array('integer','integer','integer','integer'),
                    array($next_id, $newObj->getSurveyId(), $questionblocks[$value["questionblock_fi"]], $question_pointer[$value["question_fi"]])
                );
            }
        }
        
        // clone the constraints
        $constraints = self::_getConstraints($this->getSurveyId());
        $newConstraints = array();
        foreach ($constraints as $key => $constraint) {
            if ($question_pointer[$constraint["for_question"]] &&
                $question_pointer[$constraint["question"]]) {
                if (!array_key_exists($constraint['id'], $newConstraints)) {
                    $constraint_id = $newObj->addConstraint($question_pointer[$constraint["question"]], $constraint["relation_id"], $constraint["value"], $constraint['conjunction']);
                    $newConstraints[$constraint['id']] = $constraint_id;
                }
                $newObj->addConstraintToQuestion($question_pointer[$constraint["for_question"]], $newConstraints[$constraint['id']]);
            }
        }

        // #16210 - clone LP settings
        $obj_settings = new ilLPObjSettings($this->getId());
        $obj_settings->cloneSettings($newObj->getId());
        unset($obj_settings);
        
        return $newObj;
    }

    /**
     * @todo move to survey question manager
     */
    public function getTextblock(
        int $question_id
    ) : string {
        $ilDB = $this->db;
        $result = $ilDB->queryF(
            "SELECT * FROM svy_svy_qst WHERE question_fi = %s",
            array('integer'),
            array($question_id)
        );
        if ($row = $ilDB->fetchAssoc($result)) {
            return $row["heading"];
        } else {
            return "";
        }
    }

    /**
     * Clones the text blocks of survey questions
     * @todo move to survey question manager
     * @param array<int,int> $mapping (key is original, value is new id)
     */
    public function cloneTextblocks(
        array $mapping
    ) : void {
        foreach ($mapping as $original_id => $new_id) {
            $textblock = $this->getTextblock($original_id);
            $this->saveHeading(ilUtil::stripSlashes($textblock, true, ilObjAdvancedEditing::_getUsedHTMLTagsAsString("survey")), $new_id);
        }
    }

    /**
     * creates data directory for export files
     * (data_dir/svy_data/svy_<id>/export)
     * @todo move to export sub-service
     * @throws ilSurveyException
     */
    public function createExportDirectory() : void
    {
        $svy_data_dir = ilFileUtils::getDataDir() . "/svy_data";
        ilFileUtils::makeDir($svy_data_dir);
        if (!is_writable($svy_data_dir)) {
            throw new ilSurveyException("Survey Data Directory (" . $svy_data_dir . ") not writeable.");
        }
        
        // create learning module directory (data_dir/lm_data/lm_<id>)
        $svy_dir = $svy_data_dir . "/svy_" . $this->getId();
        ilFileUtils::makeDir($svy_dir);
        if (!is_dir($svy_dir)) {
            throw new ilSurveyException("Creation of Survey Directory failed.");
        }
        // create Export subdirectory (data_dir/lm_data/lm_<id>/Export)
        $export_dir = $svy_dir . "/export";
        ilFileUtils::makeDir($export_dir);
        if (!is_dir($export_dir)) {
            throw new ilSurveyException("Creation of Export Directory failed.");
        }
    }

    /**
     * @todo move to export sub-service
     */
    public function getExportDirectory() : string
    {
        $export_dir = ilFileUtils::getDataDir() . "/svy_data" . "/svy_" . $this->getId() . "/export";

        return $export_dir;
    }
    
    /**
     * creates data directory for import files
     * (data_dir/svy_data/svy_<id>/import)
     * @todo move to export sub-service
     * @throws ilSurveyException
     */
    public function createImportDirectory() : void
    {
        $svy_data_dir = ilFileUtils::getDataDir() . "/svy_data";
        ilFileUtils::makeDir($svy_data_dir);
        
        if (!is_writable($svy_data_dir)) {
            throw new ilSurveyException("Survey Data Directory (" . $svy_data_dir . ") not writeable.");
        }

        // create test directory (data_dir/svy_data/svy_<id>)
        $svy_dir = $svy_data_dir . "/svy_" . $this->getId();
        ilFileUtils::makeDir($svy_dir);
        if (!is_dir($svy_dir)) {
            throw new ilSurveyException("Creation of Survey Directory failed.");
        }

        // create import subdirectory (data_dir/svy_data/svy_<id>/import)
        $import_dir = $svy_dir . "/import";
        ilFileUtils::makeDir($import_dir);
        if (!is_dir($import_dir)) {
            throw new ilSurveyException("Creation of Import Directory failed.");
        }
    }

    /**
     * get import directory of survey
     * @todo move to export sub-service
     */
    public function getImportDirectory() : string
    {
        $import_dir = ilFileUtils::getDataDir() . "/svy_data" .
            "/svy_" . $this->getId() . "/import";
        if (!is_dir($import_dir)) {
            ilFileUtils::makeDirParents($import_dir);
        }
        if (is_dir($import_dir)) {
            return $import_dir;
        } else {
            return "";
        }
    }

    /**
     * @todo move to survey question manager
     * @param string $heading
     * @param int    $insertbefore question id which gets the heading (before)
     */
    public function saveHeading(
        string $heading,
        int $insertbefore
    ) : void {
        $ilDB = $this->db;
        if ($heading) {
            $ilDB->manipulateF(
                "UPDATE svy_svy_qst SET heading=%s WHERE survey_fi=%s AND question_fi=%s",
                array('text','integer','integer'),
                array($heading, $this->getSurveyId(), $insertbefore)
            );
        } else {
            $ilDB->manipulateF(
                "UPDATE svy_svy_qst SET heading=%s WHERE survey_fi=%s AND question_fi=%s",
                array('text','integer','integer'),
                array(null, $this->getSurveyId(), $insertbefore)
            );
        }
    }

    /**
     * @param string $key (access code)
     * @todo move to participant manager
     */
    public function isAnonymizedParticipant(string $key) : bool
    {
        $ilDB = $this->db;
        
        $result = $ilDB->queryF(
            "SELECT finished_id FROM svy_finished WHERE anonymous_id = %s AND survey_fi = %s",
            array('text','integer'),
            array($key, $this->getSurveyId())
        );
        return $result->numRows() === 1;
    }

    /**
     * Return a list of survey codes for file export
     * @param ?string[] $a_codes array of all survey codes that should be exported, if null...
     * @param ?int[] $a_ids array of anonymous ids that should be exported
     * @return string comma separated list of survey codes an URLs for file export
     * @todo move to code manager
     * @throws ilDateTimeException
     */
    public function getSurveyCodesForExport(
        array $a_codes = null,
        array $a_ids = null
    ) : string {
        $ilDB = $this->db;
        $ilUser = $this->user;
        $lng = $this->lng;
        
        $sql = "SELECT svy_anonymous.*, svy_finished.state" .
            " FROM svy_anonymous" .
            " LEFT JOIN svy_finished ON (svy_anonymous.survey_key = svy_finished.anonymous_id)" .
            " WHERE svy_anonymous.survey_fi = " . $ilDB->quote($this->getSurveyId(), "integer") .
            " AND svy_anonymous.user_key IS NULL";
        
        if ($a_codes) {
            $sql .= " AND " . $ilDB->in("svy_anonymous.survey_key", $a_codes, "", "text");
        } elseif ($a_ids) {
            $sql .= " AND " . $ilDB->in("svy_anonymous.anonymous_id", $a_ids, "", "text");
        }
        
        $export = array();
        
        // #14905
        $titles = array();
        $titles[] = '"' . $lng->txt("survey_code") . '"';
        $titles[] = '"' . $lng->txt("email") . '"';
        $titles[] = '"' . $lng->txt("lastname") . '"';
        $titles[] = '"' . $lng->txt("firstname") . '"';
        $titles[] = '"' . $lng->txt("create_date") . '"';
        $titles[] = '"' . $lng->txt("used") . '"';
        $titles[] = '"' . $lng->txt("mail_sent_short") . '"';
        $titles[] = '"' . $lng->txt("survey_code_url") . '"';
        $export[] = implode(";", $titles);
                
        $result = $ilDB->query($sql);
        $default_lang = $ilUser->getPref("survey_code_language");
        while ($row = $ilDB->fetchAssoc($result)) {
            $item = array();
            $item[] = $row["survey_key"];
            
            if ($row["externaldata"]) {
                $ext = unserialize($row["externaldata"], ['allowed_classes' => false]);
                $item[] = $ext["email"];
                $item[] = $ext["lastname"];
                $item[] = $ext["firstname"];
            } else {
                $item[] = "";
                $item[] = "";
                $item[] = "";
            }
                        
            // No relative (today, tomorrow...) dates in export.
            $date = new ilDateTime($row['tstamp'], IL_CAL_UNIX);
            $item[] = $date->get(IL_CAL_DATETIME);
            
            $item[] = ($this->isSurveyCodeUsed($row["survey_key"])) ? 1 : 0;
            $item[] = ($row["sent"]) ? 1 : 0;
            
            $params = array("accesscode" => $row["survey_key"]);
            if ($default_lang) {
                $params["lang"] = $default_lang;
            }
            $item[] = ilLink::_getLink($this->getRefId(), "svy", $params);
                
            $export[] = '"' . implode('";"', $item) . '"';
        }
        return implode("\n", $export);
    }
    
    /**
     * Fetches the data for the survey codes table
     * @param ?string $lang Language for the survey code URL
     * @todo move to code manager
     */
    public function getSurveyCodesTableData(
        array $ids = null,
        string $lang = null
    ) : array {
        $ilDB = $this->db;
        
        $codes = array();

        $sql = "SELECT svy_anonymous.*, svy_finished.state" .
            " FROM svy_anonymous" .
            " LEFT JOIN svy_finished ON (svy_anonymous.survey_key = svy_finished.anonymous_id)" .
            " WHERE svy_anonymous.survey_fi = " . $ilDB->quote($this->getSurveyId(), "integer") /*.
            " AND svy_anonymous.user_key IS NULL" */; // #15860
        
        if ($ids) {
            $sql .= " AND " . $ilDB->in("svy_anonymous.anonymous_id", $ids, "", "integer");
        }
        
        $sql .= " ORDER BY tstamp, survey_key ASC";
        $result = $ilDB->query($sql);
        if ($result->numRows() > 0) {
            while ($row = $ilDB->fetchAssoc($result)) {
                $href = "";
                $used = false;
                if ($this->isSurveyCodeUsed($row["survey_key"])) {
                    $used = true;
                } else {
                    $params = array("accesscode" => $row["survey_key"]);
                    if ($lang) {
                        $params["lang"] = $lang;
                    }
                    $href = ilLink::_getLink($this->getRefId(), "svy", $params);
                }
                
                
                $item = array(
                    'id' => $row["anonymous_id"],
                    'code' => $row["survey_key"],
                    'date' => $row["tstamp"],
                    'used' => $used,
                    'sent' => $row['sent'],
                    'href' => $href,
                    'email' => '',
                    'last_name' => '',
                    'first_name' => ''
                );
                
                if ($row["externaldata"]) {
                    $ext = unserialize($row["externaldata"], ['allowed_classes' => false]);
                    $item['email'] = $ext['email'];
                    $item['last_name'] = $ext['lastname'];
                    $item['first_name'] = $ext['firstname'];
                }

                $codes[] = $item;
            }
        }
        return $codes;
    }

    /**
     * @todo move to code manager
     */
    public function isSurveyCodeUsed(
        string $code
    ) : bool {
        $ilDB = $this->db;
        $result = $ilDB->queryF(
            "SELECT finished_id FROM svy_finished WHERE survey_fi = %s AND anonymous_id = %s",
            array('integer','text'),
            array($this->getSurveyId(), $code)
        );
        return $result->numRows() > 0;
    }

    /**
     * @todo move to code manager
     */
    public function isSurveyCodeUnique(
        string $code
    ) : bool {
        $ilDB = $this->db;
        $result = $ilDB->queryF(
            "SELECT anonymous_id FROM svy_anonymous WHERE survey_fi = %s AND survey_key = %s",
            array('integer','text'),
            array($this->getSurveyId(), $code)
        );
        return !(($result->numRows() > 0));
    }

    /**
     * @todo move to code manager
     */
    public function importSurveyCode(
        string $a_anonymize_key,
        int $a_created,
        array $a_data
    ) : void {
        $ilDB = $this->db;
        
        $next_id = $ilDB->nextId('svy_anonymous');
        $ilDB->manipulateF(
            "INSERT INTO svy_anonymous (anonymous_id, survey_key, survey_fi, externaldata, tstamp) " .
            "VALUES (%s, %s, %s, %s, %s)",
            array('integer','text','integer','text','integer'),
            array($next_id, $a_anonymize_key, $this->getSurveyId(), serialize($a_data), $a_created)
        );
    }

    /**
     * @todo move to code manager
     */
    public function sendCodes(
        int $not_sent,
        string $subject,
        string $message,
        string $lang
    ) : void {
        /*
         * 0 = all
         * 1 = not sent
         * 2 = finished
         * 3 = not finished
         */
        $check_finished = ($not_sent > 1);
        

        $mail = new ilMail(ANONYMOUS_USER_ID);
        $recipients = $this->getExternalCodeRecipients($check_finished);
        foreach ($recipients as $data) {
            if ($data['email'] && $data['code']) {
                $do_send = false;
                switch ($not_sent) {
                    case 1:
                        $do_send = !(bool) $data['sent'];
                        break;
                    
                    case 2:
                        $do_send = $data['finished'];
                        break;
                    
                    case 3:
                        $do_send = !$data['finished'];
                        break;
                    
                    default:
                        $do_send = true;
                        break;
                }
                if ($do_send) {
                    // build text
                    $messagetext = $message;
                    $url = ilLink::_getLink(
                        $this->getRefId(),
                        "svy",
                        array(
                            "accesscode" => $data["code"],
                            "lang" => $lang
                        )
                    );
                    $messagetext = str_replace('[url]', $url, $messagetext);
                    foreach ($data as $key => $value) {
                        $messagetext = str_replace('[' . $key . ']', $value, $messagetext);
                    }
                    
                    // send mail
                    $mail->enqueue(
                        $data['email'], // to
                        "", // cc
                        "", // bcc
                        $subject, // subject
                        $messagetext, // message
                        array() // attachments
                    );
                }
            }
        }

        $ilDB = $this->db;
        $ilDB->manipulateF(
            "UPDATE svy_anonymous SET sent = %s WHERE survey_fi = %s AND externaldata IS NOT NULL",
            array('integer','integer'),
            array(1, $this->getSurveyId())
        );
    }

    /**
     * @todo move to code manager
     */
    public function getExternalCodeRecipients(
        bool $a_check_finished = false
    ) : array {
        $ilDB = $this->db;
        $result = $ilDB->queryF(
            "SELECT survey_key code, externaldata, sent FROM svy_anonymous WHERE survey_fi = %s",
            array('integer'),
            array($this->getSurveyId())
        );
        $res = array();
        while ($row = $ilDB->fetchAssoc($result)) {
            if (!$row['externaldata']) {
                continue;
            }
            
            $externaldata = unserialize($row['externaldata'], ['allowed_classes' => false]);
            if (!$externaldata['email']) {
                continue;
            }
            
            $externaldata['code'] = $row['code'];
            $externaldata['sent'] = $row['sent'];
            
            if ($a_check_finished) {
                #23294
                //$externaldata['finished'] =  $this->isSurveyCodeUsed($row['code']);
                $externaldata['finished'] = $this->isSurveyFinishedByCode($row['code']);
            }

            $res[] = $externaldata;
        }
        return $res;
    }

    /**
     * Get if survey is finished for a specific anonymous user code
     * @param string $a_code anonymous user code
     * @return bool
     * @todo move to run manager
     */
    public function isSurveyFinishedByCode(string $a_code) : bool
    {
        $result = $this->db->queryF(
            "SELECT state FROM svy_finished WHERE survey_fi = %s AND anonymous_id = %s",
            array('integer','text'),
            array($this->getSurveyId(), $a_code)
        );

        $row = $this->db->fetchAssoc($result);

        return $row['state'];
    }
    
    /**
     * @todo move to code manager
     */
    public function deleteSurveyCode(
        string $survey_code
    ) : void {
        $ilDB = $this->db;
        
        if ($survey_code !== '') {
            $affectedRows = $ilDB->manipulateF(
                "DELETE FROM svy_anonymous WHERE survey_fi = %s AND survey_key = %s",
                array('integer', 'text'),
                array($this->getSurveyId(), $survey_code)
            );
        }
    }
    
    /**
     * Returns a survey access code that was saved for a registered user
     * @param int $user_id	The database id of the user
     * @return string The survey access code of the user
     * @todo move to code manager
     */
    public function getUserAccessCode(int $user_id) : string
    {
        $ilDB = $this->db;
        $access_code = "";
        $result = $ilDB->queryF(
            "SELECT survey_key FROM svy_anonymous WHERE survey_fi = %s AND user_key = %s",
            array('integer','text'),
            array($this->getSurveyId(), md5($user_id))
        );
        if ($result->numRows()) {
            $row = $ilDB->fetchAssoc($result);
            $access_code = $row["survey_key"];
        }
        return $access_code;
    }
    
    /**
     * Saves a survey access code for a registered user to the database
     * @param int $user_id	The database id of the user
     * @param string $access_code The survey access code
     * @todo move to code manager
     */
    public function saveUserAccessCode(
        int $user_id,
        string $access_code
    ) : void {
        $ilDB = $this->db;
        
        // not really sure what to do about ANONYMOUS_USER_ID
        
        $next_id = $ilDB->nextId('svy_anonymous');
        $affectedRows = $ilDB->manipulateF(
            "INSERT INTO svy_anonymous (anonymous_id, survey_key, survey_fi, user_key, tstamp) " .
            "VALUES (%s, %s, %s, %s, %s)",
            array('integer','text', 'integer', 'text', 'integer'),
            array($next_id, $access_code, $this->getSurveyId(), md5($user_id), time())
        );
    }


    /**
     * @todo move to run manager
     */
    public function getLastAccess(
        int $finished_id
    ) : ?int {
        $ilDB = $this->db;
        
        $result = $ilDB->queryF(
            "SELECT tstamp FROM svy_answer WHERE active_fi = %s ORDER BY tstamp DESC",
            array('integer'),
            array($finished_id)
        );
        if ($result->numRows()) {
            $row = $ilDB->fetchAssoc($result);
            return (int) $row["tstamp"];
        } else {
            $result = $ilDB->queryF(
                "SELECT tstamp FROM svy_finished WHERE finished_id = %s",
                array('integer'),
                array($finished_id)
            );
            if ($result->numRows()) {
                $row = $ilDB->fetchAssoc($result);
                return (int) $row["tstamp"];
            }
        }
        return null;
    }

    /**
     * Prepares a string for a text area output in surveys
     */
    public function prepareTextareaOutput(
        string $txt_output
    ) : string {
        return ilLegacyFormElementsUtil::prepareTextareaOutput($txt_output);
    }

    /**
     * Checks if a given string contains HTML or not
     * @todo move to export sub-service
     */
    public function isHTML(
        string $a_text
    ) : bool {
        if (preg_match("/<[^>]*?>/", $a_text)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Creates an XML material tag from a plain text or xhtml text
     * @todo move to export sub-service
     */
    public function addMaterialTag(
        ilXmlWriter $a_xml_writer,
        string $a_material,
        bool $close_material_tag = true,
        bool $add_mobs = true,
        array $attribs = null
    ) : void {
        $a_xml_writer->xmlStartTag("material", $attribs);
        $attrs = array(
            "type" => "text/plain"
        );
        if ($this->isHTML($a_material)) {
            $attrs["type"] = "text/xhtml";
        }
        $mattext = ilRTE::_replaceMediaObjectImageSrc($a_material, 0);
        $a_xml_writer->xmlElement("mattext", $attrs, $mattext);

        if ($add_mobs) {
            $mobs = ilObjMediaObject::_getMobsOfObject("svy:html", $this->getId());
            foreach ($mobs as $mob) {
                $mob_id = "il_" . IL_INST_ID . "_mob_" . $mob;
                if (strpos($mattext, $mob_id) !== false) {
                    $mob_obj = new ilObjMediaObject($mob);
                    $imgattrs = array(
                        "label" => $mob_id,
                        "uri" => "objects/" . "il_" . IL_INST_ID . "_mob_" . $mob . "/" . $mob_obj->getTitle(),
                        "type" => "svy:html",
                        "id" => $this->getId()
                    );
                    $a_xml_writer->xmlElement("matimage", $imgattrs, null);
                }
            }
        }
        if ($close_material_tag) {
            $a_xml_writer->xmlEndTag("material");
        }
    }

    /**
     * Checks if the survey code can be exported with the survey evaluation. In some cases this may be
     * necessary but usually you should prevent it because people who sent the survey codes could connect
     * real people with the survey code in the evaluation and undermine the anonymity
     * @return bool true if the survey is anonymized and the survey code may be shown in the export file
     * @todo move to code manager
     */
    public function canExportSurveyCode() : bool
    {
        if ($this->getAnonymize() !== self::ANONYMIZE_OFF) {
            if ($this->surveyCodeSecurity === false) {
                return true;
            }
        }
        return false;
    }

    /**
     * @todo deprecate / abandon
     */
    public function isPluginActive(string $a_pname) : bool
    {
        $ilPluginAdmin = $this->plugin_admin;
        if ($ilPluginAdmin->isActive(ilComponentInfo::TYPE_MODULES, "SurveyQuestionPool", "svyq", $a_pname)) {
            return true;
        } else {
            return false;
        }
    }
    
    public function setSurveyId(int $survey_id) : void
    {
        $this->survey_id = $survey_id;
    }

    /**
     * Returns a data of all users specified by id list
     *
     * @param int[] $ids array of user id's
     * @return array The user data "usr_id, login, lastname, firstname, clientip" of the users with id as key
     * @todo use user service, remove direct access to usr_data table
     */
    public function getUserData(
        array $ids
    ) : array {
        $ilDB = $this->db;

        if (count($ids) === 0) {
            return array();
        }

        $result = $ilDB->query("SELECT usr_id, login, lastname, firstname FROM usr_data WHERE " . $ilDB->in('usr_id', $ids, false, 'integer') . " ORDER BY login");
        $result_array = array();
        while ($row = $ilDB->fetchAssoc($result)) {
            $result_array[$row["usr_id"]] = $row;
        }
        return $result_array;
    }

    /**
     * Send mail to tutors each time a participant finishes the survey?
     */
    public function getMailNotification() : bool
    {
        return $this->mailnotification;
    }

    /**
     * Activate mail to tutors each time a participant finishes the survey
     */
    public function setMailNotification(bool $a_notification) : void
    {
        $this->mailnotification = $a_notification;
    }

    /**
     * (Tutor) Recipients of "single participant has finished" mails
     */
    public function getMailAddresses() : string
    {
        return $this->mailaddresses;
    }

    /**
     * Set (Tutor) Recipients of "single participant has finished" mails
     */
    public function setMailAddresses(string $a_addresses) : void
    {
        $this->mailaddresses = $a_addresses;
    }

    /**
     * Preceding text (incl. placeholders) for "single participant has finished" mails
     */
    public function getMailParticipantData() : string
    {
        return $this->mailparticipantdata;
    }

    /**
     * Set preceding text (incl. placeholders) for "single participant has finished" mails
     */
    public function setMailParticipantData(string $a_data) : void
    {
        $this->mailparticipantdata = $a_data;
    }

    /**
     * @todo move to run manager?
     */
    public function getWorkingtimeForParticipant(
        int $finished_id
    ) : int {
        $ilDB = $this->db;

        $result = $ilDB->queryF(
            "SELECT * FROM svy_times WHERE finished_fi = %s",
            array('integer'),
            array($finished_id)
        );
        $total = 0;
        while ($row = $ilDB->fetchAssoc($result)) {
            if ($row['left_page'] > 0 && $row['entered_page'] > 0) {
                $total += $row['left_page'] - $row['entered_page'];
            }
        }
        return $total;
    }

    public function updateOrder(
        array $a_order
    ) : void {
        if (count($this->questions) === count($a_order)) {
            $this->questions = array_flip($a_order);
            $this->saveQuestionsToDb();
        }
    }

    public function getPoolUsage() : bool
    {
        return $this->pool_usage;
    }

    public function setPoolUsage(bool $a_value) : void
    {
        $this->pool_usage = $a_value;
    }

    /**
     * @todo move to code manager
     */
    public function updateCode(
        int $a_id,
        string $a_email,
        string $a_last_name,
        string $a_first_name,
        int $a_sent
    ) : bool {
        $ilDB = $this->db;
        
        $a_email = trim($a_email);

        // :TODO:
        if (($a_email && !ilUtil::is_email($a_email)) || $a_email === "") {
            return false;
        }
        
        $data = array("email" => $a_email,
            "lastname" => trim($a_last_name),
            "firstname" => trim($a_first_name));
        
        $fields = array(
            "externaldata" => array("text", serialize($data)),
            "sent" => array("integer", $a_sent)
        );
        
        $ilDB->update(
            "svy_anonymous",
            $fields,
            array("anonymous_id" => array("integer", $a_id))
        );

        return true;
    }
    
    
    //
    // 360°
    //

    public function get360Mode() : bool
    {
        if ($this->getMode() === self::MODE_360) {
            return true;
        }
        return false;
    }

    public function set360SelfEvaluation(bool $a_value) : void
    {
        $this->mode_360_self_eval = $a_value;
    }

    public function get360SelfEvaluation() : bool
    {
        return $this->mode_360_self_eval;
    }
    
    public function set360SelfAppraisee(bool $a_value) : void
    {
        $this->mode_360_self_appr = $a_value;
    }
    
    public function get360SelfAppraisee() : bool
    {
        return $this->mode_360_self_appr;
    }
    
    public function set360SelfRaters(bool $a_value) : void
    {
        $this->mode_360_self_rate = $a_value;
    }
    
    public function get360SelfRaters() : bool
    {
        return $this->mode_360_self_rate;
    }
    
    public function set360Results(int $a_value) : void
    {
        $this->mode_360_results = $a_value;
    }
    
    public function get360Results() : int
    {
        return $this->mode_360_results;
    }

    /**
     * @todo move to appraisal manager
     */
    public function addAppraisee(
        int $a_user_id
    ) : void {
        global $DIC;

        $ilDB = $DIC->database();
        $access = $DIC->access();
        
        if (!$this->isAppraisee($a_user_id) &&
            $a_user_id !== ANONYMOUS_USER_ID) {
            $fields = array(
                "obj_id" => array("integer", $this->getSurveyId()),
                "user_id" => array("integer", $a_user_id)
            );
            $ilDB->insert("svy_360_appr", $fields);

            // send notification and add to desktop
            if ($access->checkAccessOfUser($a_user_id, "read", "", $this->getRefId())) {
                $this->sendAppraiseeNotification($a_user_id);
            }
        }
    }

    /**
     * @todo move to appraisal manager
     */
    public function sendAppraiseeNotification(
        int $a_user_id
    ) : void {
        $ntf = new ilSystemNotification();
        $ntf->setLangModules(array("svy", "survey"));
        $ntf->setRefId($this->getRefId());
        $ntf->setGotoLangId('url');

        // user specific language
        $lng = $ntf->getUserLanguage($a_user_id);

        $ntf->setIntroductionLangId("svy_user_added_360_appraisee_mail");
        $subject = str_replace("%1", $this->getTitle(), $lng->txt("svy_user_added_360_appraisee"));

        // #10044
        $mail = new ilMail(ANONYMOUS_USER_ID);
        $mail->enqueue(
            ilObjUser::_lookupLogin($a_user_id),
            null,
            null,
            $subject,
            $ntf->composeAndGetMessage($a_user_id, null, "read", true),
            []
        );
    }

    /**
     * @todo move to appraisal manager
     */
    public function sendAppraiseeCloseNotification(
        int $a_user_id
    ) : void {
        $ntf = new ilSystemNotification();
        $ntf->setLangModules(array("svy", "survey"));
        $ntf->setRefId($this->getRefId());
        $ntf->setGotoLangId('url');

        // user specific language
        $lng = $ntf->getUserLanguage($a_user_id);

        $ntf->setIntroductionLangId("svy_user_added_360_appraisee_close_mail");
        $subject = str_replace("%1", $this->getTitle(), $lng->txt("svy_user_added_360_appraisee"));

        // #10044
        $mail = new ilMail(ANONYMOUS_USER_ID);
        $mail->enqueue(
            ilObjUser::_lookupLogin($a_user_id),
            null,
            null,
            $subject,
            $ntf->composeAndGetMessage($a_user_id, null, "read", true),
            []
        );
    }

    /**
     * @todo move to appraisal manager
     */
    public function sendRaterNotification(
        int $a_user_id,
        int $a_appraisee_id
    ) : void {
        $ntf = new ilSystemNotification();
        $ntf->setLangModules(array("svy", "survey"));
        $ntf->setRefId($this->getRefId());
        $ntf->setGotoLangId('url');

        // user specific language
        $lng = $ntf->getUserLanguage($a_user_id);

        $ntf->setIntroductionLangId("svy_user_added_360_rater_mail");
        $subject = str_replace("%1", $this->getTitle(), $lng->txt("svy_user_added_360_rater"));
        $ntf->addAdditionalInfo("survey_360_appraisee", ilUserUtil::getNamePresentation($a_appraisee_id, false, false, "", true));

        // #10044
        $mail = new ilMail(ANONYMOUS_USER_ID);
        $mail->enqueue(
            ilObjUser::_lookupLogin($a_user_id),
            null,
            null,
            $subject,
            $ntf->composeAndGetMessage($a_user_id, null, "read", true),
            []
        );
    }

    /**
     * @todo move to appraisal manager
     */
    public function isAppraisee(
        int $a_user_id
    ) : bool {
        $ilDB = $this->db;
        $set = $ilDB->query("SELECT user_id" .
            " FROM svy_360_appr" .
            " WHERE obj_id = " . $ilDB->quote($this->getSurveyId(), "integer") .
            " AND user_id = " . $ilDB->quote($a_user_id, "integer"));
        return (bool) $ilDB->numRows($set);
    }

    /**
     * @todo move to appraisal manager
     */
    public function isAppraiseeClosed(
        int $a_user_id
    ) : bool {
        $ilDB = $this->db;
        
        $set = $ilDB->query("SELECT has_closed" .
            " FROM svy_360_appr" .
            " WHERE obj_id = " . $ilDB->quote($this->getSurveyId(), "integer") .
            " AND user_id = " . $ilDB->quote($a_user_id, "integer"));
        $row = $ilDB->fetchAssoc($set);
        return (bool) $row["has_closed"];
    }

    /**
     * @todo move to appraisal manager
     */
    public function deleteAppraisee(
        int $a_user_id
    ) : void {
        $ilDB = $this->db;
        
        $ilDB->manipulate("DELETE FROM svy_360_appr" .
            " WHERE obj_id = " . $ilDB->quote($this->getSurveyId(), "integer") .
            " AND user_id = " . $ilDB->quote($a_user_id, "integer"));
                
        $set = $ilDB->query("SELECT user_id" .
            " FROM svy_360_rater" .
            " WHERE obj_id = " . $ilDB->quote($this->getSurveyId(), "integer") .
            " AND appr_id = " . $ilDB->quote($a_user_id, "integer"));
        while ($row = $ilDB->fetchAssoc($set)) {
            $this->deleteRater($a_user_id, $row["user_id"]);
        }
        // appraisee will not be part of raters table
        if ($this->get360SelfEvaluation()) {
            $this->deleteRater($a_user_id, $a_user_id);
        }
    }

    /**
     * @todo move to appraisal manager, use dto
     */
    public function getAppraiseesData() : array
    {
        $ilDB = $this->db;
        
        $res = array();
        
        $set = $ilDB->query("SELECT * FROM svy_360_appr" .
            " WHERE obj_id = " . $ilDB->quote($this->getSurveyId(), "integer"));
        while ($row = $ilDB->fetchAssoc($set)) {
            $name = ilObjUser::_lookupName($row["user_id"]);
            $name["email"] = ilObjUser::_lookupEmail($row["user_id"]);
            $name["name"] = $name["lastname"] . ", " . $name["firstname"];
            $res[$row["user_id"]] = $name;
            
            $finished = 0;
            $raters = $this->getRatersData($row["user_id"]);
            foreach ($raters as $rater) {
                if ($rater["finished"]) {
                    $finished++;
                }
            }
            $res[$row["user_id"]]["finished"] = $finished . "/" . count($raters);
            $res[$row["user_id"]]["closed"] = $row["has_closed"];
        }
        
        return $res;
    }

    /**
     * @todo move to appraisal manager
     */
    public function addRater(
        int $a_appraisee_id,
        int $a_user_id,
        int $a_anonymous_id = 0
    ) : void {
        global $DIC;

        $ilDB = $DIC->database();
        $access = $DIC->access();
        
        if ($this->isAppraisee($a_appraisee_id) &&
            !$this->isRater($a_appraisee_id, $a_user_id, $a_anonymous_id)) {
            $fields = array(
                "obj_id" => array("integer", $this->getSurveyId()),
                "appr_id" => array("integer", $a_appraisee_id),
                "user_id" => array("integer", $a_user_id),
                "anonymous_id" => array("integer", $a_anonymous_id)
            );
            $ilDB->insert("svy_360_rater", $fields);

            // send notification and add to desktop
            if ($access->checkAccessOfUser($a_user_id, "read", "", $this->getRefId())) {
                $this->sendRaterNotification($a_user_id, $a_appraisee_id);
            }
        }
    }

    /**
     * @todo move to appraisal manager
     */
    public function isRater(
        int $a_appraisee_id,
        int $a_user_id,
        int $a_anonymous_id = 0
    ) : bool {
        $ilDB = $this->db;
        
        // user is rater if already appraisee and active self-evaluation
        if ($this->isAppraisee($a_user_id) &&
            $this->get360SelfEvaluation() &&
            (!$a_appraisee_id || $a_appraisee_id === $a_user_id)) {
            return true;
        }
                
        // :TODO: should we get rid of code as well?
        
        $sql = "SELECT user_id" .
            " FROM svy_360_rater" .
            " WHERE obj_id = " . $ilDB->quote($this->getSurveyId(), "integer") .
            " AND user_id = " . $ilDB->quote($a_user_id, "integer") .
            " AND anonymous_id = " . $ilDB->quote($a_anonymous_id, "integer");
        if ($a_appraisee_id) {
            $sql .= " AND appr_id = " . $ilDB->quote($a_appraisee_id, "integer");
        }
        $set = $ilDB->query($sql);
        return (bool) $ilDB->numRows($set);
    }

    /**
     * @todo move to appraisal manager
     */
    public function deleteRater(
        int $a_appraisee_id,
        int $a_user_id,
        int $a_anonymous_id = 0
    ) : void {
        $ilDB = $this->db;
        
        $finished_id = $this->getFinishedIdForAppraiseeIdAndRaterId($a_appraisee_id, $a_user_id);
        if ($finished_id) {
            $this->removeSelectedSurveyResults(array($finished_id));
        }
        
        $ilDB->manipulate("DELETE FROM svy_360_rater" .
            " WHERE obj_id = " . $ilDB->quote($this->getSurveyId(), "integer") .
            " AND appr_id = " . $ilDB->quote($a_appraisee_id, "integer") .
            " AND user_id = " . $ilDB->quote($a_user_id, "integer") .
            " AND anonymous_id = " . $ilDB->quote($a_anonymous_id, "integer"));
    }

    /**
     * @todo move to appraisal manager
     */
    public function getRatersData(
        int $a_appraisee_id
    ) : array {
        $ilDB = $this->db;
        
        $res = $anonymous_ids = array();
        
        $set = $ilDB->query("SELECT * FROM svy_360_rater" .
            " WHERE obj_id = " . $ilDB->quote($this->getSurveyId(), "integer") .
            " AND appr_id = " . $ilDB->quote($a_appraisee_id, "integer"));
        while ($row = $ilDB->fetchAssoc($set)) {
            if ($row["anonymous_id"]) {
                $res["a" . $row["anonymous_id"]] = array(
                    "lastname" => "unknown code " . $row["anonymous_id"],
                    "sent" => $row["mail_sent"],
                    "finished" => null
                );
                $anonymous_ids[] = $row["anonymous_id"];
            } else {
                $name = ilObjUser::_lookupName($row["user_id"]);
                $name["name"] = $name["lastname"] . ", " . $name["firstname"];
                $name["user_id"] = "u" . $name["user_id"];
                $name["email"] = ilObjUser::_lookupEmail($row["user_id"]);
                $name["sent"] = $row["mail_sent"];
                $name["finished"] = (bool) $this->is360SurveyStarted($a_appraisee_id, $row["user_id"]);
                $res["u" . $row["user_id"]] = $name;
            }
        }
        
        if (count($anonymous_ids)) {
            $data = $this->getSurveyCodesTableData($anonymous_ids);
            foreach ($data as $item) {
                if (isset($res["a" . $item["id"]])) {
                    $res["a" . $item["id"]] = array(
                        "user_id" => "a" . $item["id"],
                        "lastname" => $item["last_name"],
                        "firstname" => $item["first_name"],
                        "name" => $item["last_name"] . ", " . $item["first_name"],
                        "login" => "",
                        "email" => $item["email"],
                        "code" => $item["code"],
                        "href" => $item["href"],
                        "sent" => $res["a" . $item["id"]]["sent"],
                        "finished" => (bool) $this->is360SurveyStarted($a_appraisee_id, null, $item["code"])
                    );
                }
            }
        }
        
        return $res;
    }

    /**
     * @todo move to appraisal manager
     * @return int[]
     */
    public function getAppraiseesToRate(
        ?int $a_user_id,
        int $a_anonymous_id = null
    ) : array {
        $ilDB = $this->db;
    
        $res = array();
        
        $sql = "SELECT appr_id FROM svy_360_rater" .
            " WHERE obj_id = " . $ilDB->quote($this->getSurveyId(), "integer");
        
        if ($a_user_id) {
            $sql .= " AND user_id = " . $ilDB->quote($a_user_id, "integer");
        } else {
            $sql .= " AND anonymous_id = " . $ilDB->quote($a_anonymous_id, "integer");
        }
                
        $set = $ilDB->query($sql);
        while ($row = $ilDB->fetchAssoc($set)) {
            $res[] = (int) $row["appr_id"];
        }
                
        // user may evaluate himself if already appraisee
        if ($this->get360SelfEvaluation() &&
            $this->isAppraisee($a_user_id) &&
            !in_array($a_user_id, $res)) {
            $res[] = $a_user_id;
        }
        
        return $res;
    }

    /**
     * @todo move to code manager
     */
    public function getAnonymousIdByCode(
        string $a_code
    ) : int {
        $ilDB = $this->db;
        
        $set = $ilDB->query("SELECT anonymous_id FROM svy_anonymous" .
                " WHERE survey_fi = " . $ilDB->quote($this->getSurveyId(), "integer") .
                " AND survey_key = " . $ilDB->quote($a_code, "text"));
        $res = $ilDB->fetchAssoc($set);
        return $res["anonymous_id"];
    }

    /**
     * @todo move to appraisal manager
     */
    public function is360SurveyStarted(
        int $appr_id,
        int $user_id,
        string $anonymous_code = null
    ) : ?int {
        $ilDB = $this->db;

        $sql = "SELECT * FROM svy_finished" .
            " WHERE survey_fi =" . $ilDB->quote($this->getSurveyId(), "integer") .
            " AND appr_id = " . $ilDB->quote($appr_id, "integer");
        if ($user_id) {
            $sql .= " AND user_fi = " . $ilDB->quote($user_id, "integer");
        } else {
            $sql .= " AND anonymous_id = " . $ilDB->quote($anonymous_code, "text");
        }
        $result = $ilDB->query($sql);
        if ($result->numRows() === 0) {
            return null;
        } else {
            $row = $ilDB->fetchAssoc($result);
            return (int) $row["state"];
        }
    }

    /**
     * Get current user execution status.
     * Generates a code in svy_anonymous for logged in raters, if codes are used
     * Generates a code for the anonymous user, if the survey is set to anonymous and no codes are used
     * @param ?string $a_code
     * @return array|null
     * @todo move to run manager
     */
    public function getUserSurveyExecutionStatus(
        string $a_code = null
    ) : ?array {
        $ilUser = $this->user;
        $ilDB = $this->db;
            
        $user_id = $ilUser->getId();
        // code is obligatory?
        if (!$this->isAccessibleWithoutCode()) {
            if (!$a_code) {
                // registered raters do not need code
                if ($this->feature_config->usesAppraisees() &&
                    $user_id !== ANONYMOUS_USER_ID &&
                    $this->isRater(0, $user_id)) {
                    // auto-generate code
                    $code = $this->data_manager->code("")
                        ->withUserId($user_id);
                    $this->code_manager->add($code);
                    $a_code = $this->code_manager->getByUserId($user_id);
                } else {
                    return null;
                }
            }
        } elseif ($user_id === ANONYMOUS_USER_ID ||
            $this->getAnonymize() === self::ANONYMIZE_FREEACCESS) {
            if (!$a_code) {
                // auto-generate code
                $code = $this->data_manager->code("")
                    ->withUserId($user_id);
                $this->code_manager->add($code);
            }
        } else {
            $a_code = null;
        }
            
        $res = array();

        // get runs for user id / code
        $sql = "SELECT * FROM svy_finished" .
            " WHERE survey_fi = " . $ilDB->quote($this->getSurveyId(), "integer");
        // if proper user id is given, use it or current code
        if ($user_id !== ANONYMOUS_USER_ID) {
            $sql .= " AND (user_fi = " . $ilDB->quote($user_id, "integer") .
                " OR anonymous_id = " . $ilDB->quote($a_code, "text") . ")";
        }
        // use anonymous code to find finished id(s)
        else {
            $sql .= " AND anonymous_id = " . $ilDB->quote($a_code, "text");
        }
        $set = $ilDB->query($sql);
        while ($row = $ilDB->fetchAssoc($set)) {
            $res[$row["finished_id"]] = array("appr_id" => $row["appr_id"],
                "user_id" => $row["user_fi"],
                "code" => $row["anonymous_id"],
                "finished" => (bool) $row["state"]);
        }
        return array("code" => $a_code, "runs" => $res);
    }

    /**
     * @todo move to code manager
     */
    public function findCodeForUser(
        int $a_user_id
    ) : string {
        $ilDB = $this->db;
        
        if ($a_user_id !== ANONYMOUS_USER_ID) {
            $set = $ilDB->query("SELECT sf.anonymous_id FROM svy_finished sf" .
                " WHERE sf.survey_fi = " . $ilDB->quote($this->getSurveyId(), "integer") .
                " AND sf.user_fi = " . $ilDB->quote($a_user_id, "integer"));
            $a_code = $ilDB->fetchAssoc($set);
            return (string) ($a_code["anonymous_id"] ?? "");
        }
        return "";
    }

    /**
     * @todo move to code manager
     */
    public function isUnusedCode(
        string $a_code,
        int $a_user_id
    ) : bool {
        $ilDB = $this->db;
        
        $set = $ilDB->query("SELECT user_fi FROM svy_finished" .
            " WHERE survey_fi = " . $ilDB->quote($this->getSurveyId(), "integer") .
            " AND anonymous_id = " . $ilDB->quote($a_code, "text"));
        $user_id = $ilDB->fetchAssoc($set);
        $user_id = (int) $user_id["user_fi"];
        
        if ($user_id && ($user_id !== $a_user_id || $user_id === ANONYMOUS_USER_ID)) {
            return false;
        }
        return true;
    }

    /**
     * @return int[]
     * @todo move to run manager
     */
    public function getFinishedIdsForAppraiseeId(
        int $a_appr_id,
        bool $a_exclude_appraisee = false
    ) : array {
        $ilDB = $this->db;
        
        $res = array();
        
        $set = $ilDB->query("SELECT finished_id, user_fi FROM svy_finished" .
            " WHERE survey_fi = " . $ilDB->quote($this->getSurveyId(), "integer") .
            " AND appr_id = " . $ilDB->quote($a_appr_id, "integer"));
        while ($row = $ilDB->fetchAssoc($set)) {
            if ($a_exclude_appraisee && $row["user_fi"] == $a_appr_id) {
                continue;
            }
            $res[] = (int) $row["finished_id"];
        }
        
        return $res;
    }
    
    /**
     * Get finished id for an appraisee and a rater
     * @todo move to run manager
     */
    public function getFinishedIdForAppraiseeIdAndRaterId(
        int $a_appr_id,
        int $a_rat_id
    ) : ?int {
        $ilDB = $this->db;
        
        $set = $ilDB->query("SELECT finished_id, user_fi FROM svy_finished" .
            " WHERE survey_fi = " . $ilDB->quote($this->getSurveyId(), "integer") .
            " AND appr_id = " . $ilDB->quote($a_appr_id, "integer") .
            " AND user_fi = " . $ilDB->quote($a_rat_id, "integer"));
        if ($row = $ilDB->fetchAssoc($set)) {
            return (int) $row["finished_id"];
        }
        return  null;
    }
    
    
    // 360° using competence/skill service
    
    public function setSkillService(bool $a_val) : void
    {
        $this->mode_skill_service = $a_val;
    }
    
    public function getSkillService() : bool
    {
        return $this->mode_skill_service;
    }

    /**
     * @todo move to appraisal manager
     */
    public function set360RaterSent(
        int $a_appraisee_id,
        int $a_user_id,
        int $a_anonymous_id,
        int $a_tstamp = null
    ) : void {
        $ilDB = $this->db;
        
        if (!$a_tstamp) {
            $a_tstamp = time();
        }
        
        $ilDB->manipulate("UPDATE svy_360_rater" .
            " SET mail_sent = " . $ilDB->quote($a_tstamp, "integer") .
            " WHERE obj_id = " . $ilDB->quote($this->getSurveyId(), "integer") .
            " AND appr_id = " . $ilDB->quote($a_appraisee_id, "integer") .
            " AND user_id = " . $ilDB->quote($a_user_id, "integer") .
            " AND anonymous_id = " . $ilDB->quote($a_anonymous_id, "integer"));
    }

    /**
     * @todo move to appraisal/run manager/mode
     */
    public function closeAppraisee(
        int $a_user_id
    ) : void {
        global $DIC;

        $ilDB = $DIC->database();
        $user = $DIC->user();
        
        // close the appraisee
        $ilDB->manipulate("UPDATE svy_360_appr" .
            " SET has_closed = " . $ilDB->quote(time(), "integer") .
            " WHERE obj_id = " . $ilDB->quote($this->getSurveyId(), "integer") .
            " AND user_id = " . $ilDB->quote($a_user_id, "integer"));
        
        // write competences
        $skmg_set = new ilSkillManagementSettings();
        if ($this->getSkillService() && $skmg_set->isActivated()) {
            $sskill = new ilSurveySkill($this);
            $sskill->writeAndAddAppraiseeSkills($a_user_id);
        }

        // send notification
        if ($user->getId() !== $a_user_id) {
            $this->sendAppraiseeCloseNotification($a_user_id);
        }
    }

    /**
     * @todo move to appraisal manager
     */
    public function openAllAppraisees() : void
    {
        $ilDB = $this->db;
        
        $ilDB->manipulate("UPDATE svy_360_appr" .
            " SET has_closed = " . $ilDB->quote(null, "integer") .
            " WHERE obj_id = " . $ilDB->quote($this->getSurveyId(), "integer"));
    }

    /**
     * @todo move to code manager
     */
    public static function validateExternalRaterCode(
        int $a_ref_id,
        string $a_code
    ) : bool {
        /** @var ILIAS\DI\Container $DIC */
        global $DIC;

        $anonym_repo = $DIC->survey()
                           ->internal()
                           ->repo()
                           ->execution()
                           ->runSession();

        if (!$anonym_repo->isExternalRaterValidated($a_ref_id)) {
            $svy = new self($a_ref_id);
            $svy->loadFromDb();

            $domain_service = $DIC->survey()->internal()->domain();
            $code_manager = $domain_service->code($svy, 0);
            $feature_config = $domain_service->modeFeatureConfig($svy->getMode());
            $access_manager = $domain_service->access($a_ref_id, 0);

            if ($access_manager->canStartSurvey() &&
                $feature_config->usesAppraisees() &&
                $code_manager->exists($a_code)) {
                $anonymous_id = $svy->getAnonymousIdByCode($a_code);
                if ($anonymous_id) {
                    if (count($svy->getAppraiseesToRate(null, $anonymous_id))) {
                        $anonym_repo->setExternalRaterValidation($a_ref_id, true);
                        return true;
                    }
                }
            }
            $anonym_repo->setExternalRaterValidation($a_ref_id, false);
            return false;
        }

        return $anonym_repo->isExternalRaterValidated($a_ref_id);
    }
    
    
    //
    // reminder/notification
    //
    
    public function getReminderStatus() : bool
    {
        return $this->reminder_status;
    }
    
    public function setReminderStatus(bool $a_value) : void
    {
        $this->reminder_status = $a_value;
    }
    
    public function getReminderStart() : ?ilDate
    {
        return $this->reminder_start;
    }
    
    public function setReminderStart(?ilDate $a_value = null) : void
    {
        $this->reminder_start = $a_value;
    }
    
    public function getReminderEnd() : ?ilDate
    {
        return $this->reminder_end;
    }
    
    public function setReminderEnd(?ilDate $a_value = null) : void
    {
        $this->reminder_end = $a_value;
    }
    
    public function getReminderFrequency() : int
    {
        return $this->reminder_frequency;
    }
    
    public function setReminderFrequency(int $a_value) : void
    {
        $this->reminder_frequency = $a_value;
    }
    
    public function getReminderTarget() : int
    {
        return $this->reminder_target;
    }
    
    public function setReminderTarget(int $a_value) : void
    {
        $this->reminder_target = $a_value;
    }
    
    public function getReminderLastSent() : ?string
    {
        return $this->reminder_last_sent;
    }
    
    public function setReminderLastSent(?string $a_value) : void
    {
        if ($a_value == "") {
            $a_value = null;
        }
        $this->reminder_last_sent = $a_value;
    }

    public function getReminderTemplate(
        bool $selectDefault = false
    ) : ?int {
        if ($selectDefault) {
            $defaultTemplateId = 0;
            $this->getReminderMailTemplates($defaultTemplateId);

            if ($defaultTemplateId > 0) {
                return $defaultTemplateId;
            }
        }

        return $this->reminder_tmpl;
    }
    
    public function setReminderTemplate(?int $a_value) : void
    {
        $this->reminder_tmpl = $a_value;
    }
    
    public function getTutorNotificationStatus() : bool
    {
        return $this->tutor_ntf_status;
    }

    /**
     * Activates mail being sent to tutors after all participants have finished the survey
     */
    public function setTutorNotificationStatus(bool $a_value) : void
    {
        $this->tutor_ntf_status = $a_value;
    }

    /**
     * Mail being sent to tutors after all participants have finished the survey?
     */
    public function getTutorNotificationRecipients() : array
    {
        return $this->tutor_ntf_recipients;
    }

    /**
     * Set tutor recipients for "all participants have finished" mail
     */
    public function setTutorNotificationRecipients(array $a_value) : void
    {
        $this->tutor_ntf_recipients = $a_value;
    }

    /**
     * Group that is checked for the "all participants have finished" mail
     * Either
     * a) all members of a parent group/course or
     * b) all users which have been invited (svy_invitation) (= task on dashboard)
     */
    public function getTutorNotificationTarget() : int
    {
        return $this->tutor_ntf_target;
    }
    
    public function setTutorNotificationTarget(int $a_value) : void
    {
        $this->tutor_ntf_target = $a_value;
    }
    
    public function getTutorResultsStatus() : bool
    {
        return $this->tutor_res_status;
    }
    
    public function setTutorResultsStatus(bool $a_value) : void
    {
        $this->tutor_res_status = $a_value;
    }
    
    public function getTutorResultsRecipients() : array
    {
        return $this->tutor_res_recipients;
    }
    
    public function setTutorResultsRecipients(array $a_value) : void
    {
        $this->tutor_res_recipients = $a_value;
    }

    /**
     * Check, if mail to tutors after all participants have finished the survey
     * should be sent.
     */
    protected function checkTutorNotification() : void
    {
        $ilDB = $this->db;
        
        if ($this->getTutorNotificationStatus()) {

            // get target users, either parent course/group members or
            // user with the survey on the dashboard
            $user_ids = $this->getNotificationTargetUserIds(($this->getTutorNotificationTarget() === self::NOTIFICATION_INVITED_USERS));
            if ($user_ids) {
                $set = $ilDB->query("SELECT COUNT(*) numall FROM svy_finished" .
                    " WHERE survey_fi = " . $ilDB->quote($this->getSurveyId(), "integer") .
                    " AND state = " . $ilDB->quote(1, "integer") .
                    " AND " . $ilDB->in("user_fi", $user_ids, "", "integer"));
                $row = $ilDB->fetchAssoc($set);

                // all users finished the survey -> send notifications
                if ((int) $row["numall"] === count($user_ids)) {
                    $this->sendTutorNotification();
                }
            }
        }
    }

    /**
     * Send 360 reminders
     */
    public function sent360Reminders() : void
    {
        global $DIC;

        $access = $DIC->access();

        // collect all open ratings
        $rater_ids = array();
        foreach ($this->getAppraiseesData() as $app) {
            $this->log->debug("Handle appraisee " . $app['user_id']);

            if (!$this->isAppraiseeClosed($app['user_id'])) {
                $this->log->debug("Check self evaluation, self: " . $this->get360SelfAppraisee() . ", target: " . $this->getReminderTarget());

                // self evaluation?
                if ($this->get360SelfEvaluation() &&
                    in_array($this->getReminderTarget(), array(self::NOTIFICATION_APPRAISEES, self::NOTIFICATION_APPRAISEES_AND_RATERS))) {
                    $this->log->debug("...1");
                    // did user already finished self evaluation?
                    if (!$this->is360SurveyStarted($app['user_id'], $app['user_id'])) {
                        $this->log->debug("...2");
                        if (!is_array($rater_ids[$app['user_id']])) {
                            $rater_ids[$app['user_id']] = array();
                        }
                        if (!in_array($app["user_id"], $rater_ids[$app['user_id']])) {
                            $rater_ids[$app['user_id']][] = $app["user_id"];
                        }
                    }
                }

                $this->log->debug("Check raters.");

                // should raters be notified?
                if (
                    in_array(
                        $this->getReminderTarget(),
                        array(self::NOTIFICATION_RATERS, self::NOTIFICATION_APPRAISEES_AND_RATERS),
                        true
                    )
                ) {
                    foreach ($this->getRatersData($app['user_id']) as $rater) {
                        // is rater not anonymous and did not rate yet?
                        if (!$rater["anonymous_id"] && !$rater["finished"]) {
                            if (!is_array($rater_ids[$rater["user_id"]])) {
                                $rater_ids[$rater["user_id"]] = array();
                            }
                            if (!in_array($app["user_id"], $rater_ids[$rater["user_id"]])) {
                                $rater_ids[$rater["user_id"]][] = $app["user_id"];
                            }
                        }
                    }
                }
            }
        }

        $this->log->debug("Found raters:" . count($rater_ids));

        foreach ($rater_ids as $id => $app) {
            if ($access->checkAccessOfUser($id, "read", "", $this->getRefId())) {
                $this->send360ReminderToUser($id, $app);
            }
        }
    }

    public function send360ReminderToUser(
        int $a_user_id,
        array $a_appraisee_ids
    ) : void {
        $this->log->debug("Send mail to:" . $a_user_id);

        $ntf = new ilSystemNotification();
        $ntf->setLangModules(array("svy", "survey"));
        $ntf->setRefId($this->getRefId());
        $ntf->setGotoLangId('url');

        // user specific language
        $lng = $ntf->getUserLanguage($a_user_id);

        $ntf->setIntroductionLangId("svy_user_added_360_rater_reminder_mail");
        $subject = str_replace("%1", $this->getTitle(), $lng->txt("svy_user_added_360_rater"));

        foreach ($a_appraisee_ids as $appraisee_id) {
            $ntf->addAdditionalInfo("survey_360_appraisee", ilUserUtil::getNamePresentation($appraisee_id, false, false, "", true));
        }

        // #10044
        $mail = new ilMail(ANONYMOUS_USER_ID);
        $mail->enqueue(
            ilObjUser::_lookupLogin($a_user_id),
            null,
            null,
            $subject,
            $ntf->composeAndGetMessage($a_user_id, null, "read", true),
            []
        );
    }

    /**
     * These users must finish the survey to trigger the
     * tutor notification "all users finished the survey"
     * a) members of a parent group/course or
     * b) all members that have been invited (svy_invitation)
     *    invitations are presented as tasks on the dashboard
     */
    public function getNotificationTargetUserIds(
        bool $a_use_invited
    ) : array {
        $tree = $this->tree;

        $user_ids = [];
        if ($a_use_invited) {
            $user_ids = $this->invitation_manager->getAllForSurvey($this->getSurveyId());
        } else {
            $parent_grp_ref_id = $tree->checkForParentType($this->getRefId(), "grp");
            if ($parent_grp_ref_id) {
                $part = new ilGroupParticipants(ilObject::_lookupObjId($parent_grp_ref_id));
                $user_ids = $part->getMembers();
            } else {
                $parent_crs_ref_id = $tree->checkForParentType($this->getRefId(), "crs");
                if ($parent_crs_ref_id) {
                    $part = new ilCourseParticipants(ilObject::_lookupObjId($parent_crs_ref_id));
                    $user_ids = $part->getMembers();
                }
            }
        }
        return $user_ids;
    }

    /**
     * Send mail to tutors after all participants have finished the survey
     */
    protected function sendTutorNotification() : void
    {
        $link = ilLink::_getStaticLink($this->getRefId(), "svy");

        // get tutors being set in the setting
        foreach ($this->getTutorNotificationRecipients() as $user_id) {
            // use language of recipient to compose message
            $ulng = ilLanguageFactory::_getLanguageOfUser($user_id);
            $ulng->loadLanguageModule('survey');

            $subject = sprintf($ulng->txt('survey_notification_tutor_subject'), $this->getTitle());
            $message = sprintf($ulng->txt('survey_notification_tutor_salutation'), ilObjUser::_lookupFullname($user_id)) . "\n\n";

            $message .= $ulng->txt('survey_notification_tutor_body') . ":\n\n";
            $message .= $ulng->txt('obj_svy') . ": " . $this->getTitle() . "\n";
            $message .= "\n" . $ulng->txt('survey_notification_tutor_link') . ": " . $link;

            $mail_obj = new ilMail(ANONYMOUS_USER_ID);
            $mail_obj->appendInstallationSignature(true);
            $mail_obj->enqueue(
                ilObjUser::_lookupLogin($user_id),
                "",
                "",
                $subject,
                $message,
                array()
            );
        }
    }
    
    public function checkReminder() : ?int
    {
        $ilDB = $this->db;
        $ilAccess = $this->access;
        
        $now = time();
        $now_with_format = date("YmdHis", $now);
        $today = date("Y-m-d");

        $this->log->debug("Check status and dates.");
        
        // object settings / participation period
        if (
            $this->getOfflineStatus() ||
            !$this->getReminderStatus() ||
            ($this->getStartDate() && $now_with_format < $this->getStartDate()) ||
            ($this->getEndDate() && $now_with_format > $this->getEndDate())) {
            return null;
        }
                        
        // reminder period
        $start = $this->getReminderStart();
        if ($start) {
            $start = $start->get(IL_CAL_DATE);
        }
        $end = $this->getReminderEnd();
        if ($end) {
            $end = $end->get(IL_CAL_DATE);
        }
        if ($today < $start ||
            ($end && $today > $end)) {
            return null;
        }

        $this->log->debug("Check access period.");

        // object access period
        $item_data = ilObjectActivation::getItem($this->getRefId());
        if ((int) $item_data["timing_type"] === ilObjectActivation::TIMINGS_ACTIVATION &&
            ($now < $item_data["timing_start"] ||
            $now > $item_data["timing_end"])) {
            return null;
        }

        $this->log->debug("Check frequency.");

        // check frequency
        $cut = new ilDate($today, IL_CAL_DATE);
        $cut->increment(IL_CAL_DAY, $this->getReminderFrequency() * -1);
        if (!$this->getReminderLastSent() ||
            $cut->get(IL_CAL_DATE) >= substr($this->getReminderLastSent(), 0, 10)) {
            $missing_ids = array();

            if (!$this->feature_config->usesAppraisees()) {
                $this->log->debug("Entering survey mode.");

                // #16871
                $user_ids = $this->getNotificationTargetUserIds(($this->getReminderTarget() === self::NOTIFICATION_INVITED_USERS));
                if ($user_ids) {
                    // gather participants who already finished
                    $finished_ids = array();
                    $set = $ilDB->query("SELECT user_fi FROM svy_finished" .
                        " WHERE survey_fi = " . $ilDB->quote($this->getSurveyId(), "integer") .
                        " AND state = " . $ilDB->quote(1, "text") .
                        " AND " . $ilDB->in("user_fi", $user_ids, "", "integer"));
                    while ($row = $ilDB->fetchAssoc($set)) {
                        $finished_ids[] = $row["user_fi"];
                    }

                    // some users missing out?
                    $missing_ids = array_diff($user_ids, $finished_ids);
                    if ($missing_ids) {
                        foreach ($missing_ids as $idx => $user_id) {
                            // should be able to participate
                            if (!$ilAccess->checkAccessOfUser($user_id, "read", "", $this->getRefId(), "svy", $this->getId())) {
                                unset($missing_ids[$idx]);
                            }
                        }
                    }
                    if ($missing_ids) {
                        $this->sentReminder($missing_ids);
                    }
                }
            } else {
                $this->log->debug("Entering 360 mode.");

                $this->sent360Reminders();
            }


            $this->setReminderLastSent($today);
            $this->saveToDb();

            return count($missing_ids);
        }
        
        return null;
    }
    
    protected function sentReminder(
        array $a_recipient_ids
    ) : void {
        global $DIC;

        $link = "";

        // use mail template
        if ($this->getReminderTemplate() &&
            array_key_exists($this->getReminderTemplate(), $this->getReminderMailTemplates())) {
            /** @var \ilMailTemplateService $templateService */
            $templateService = $DIC['mail.texttemplates.service'];
            $tmpl = $templateService->loadTemplateForId($this->getReminderTemplate());

            $tmpl_params = array(
                "ref_id" => $this->getRefId(),
                "ts" => time()
            );
        } else {
            $tmpl = null;
            $tmpl_params = null;
            $link = ilLink::_getStaticLink($this->getRefId(), "svy");
        }
            
        foreach ($a_recipient_ids as $user_id) {
            if ($tmpl) {
                $subject = $tmpl->getSubject();
                $message = $this->sentReminderPlaceholders($tmpl->getMessage(), $user_id, $tmpl_params);
            }
            // use lng
            else {
                // use language of recipient to compose message
                $ulng = ilLanguageFactory::_getLanguageOfUser($user_id);
                $ulng->loadLanguageModule('survey');
            
                $subject = sprintf($ulng->txt('survey_reminder_subject'), $this->getTitle());
                $message = sprintf($ulng->txt('survey_reminder_salutation'), ilObjUser::_lookupFullname($user_id)) . "\n\n";

                $message .= $ulng->txt('survey_reminder_body') . ":\n\n";
                $message .= $ulng->txt('obj_svy') . ": " . $this->getTitle() . "\n";
                $message .= "\n" . $ulng->txt('survey_reminder_link') . ": " . $link;
            }

            $mail_obj = new ilMail(ANONYMOUS_USER_ID);
            $mail_obj->appendInstallationSignature(true);
            $mail_obj->enqueue(
                ilObjUser::_lookupLogin($user_id),
                "",
                "",
                $subject,
                $message,
                array()
            );
        }
    }
    
    public function setActivationStartDate(
        string $starting_time = null
    ) : void {
        $this->activation_starting_time = $starting_time;
    }

    public function setActivationEndDate(
        string $ending_time = null
    ) : void {
        $this->activation_ending_time = $ending_time;
    }
    
    public function getActivationStartDate() : ?string
    {
        return $this->activation_starting_time;
    }

    public function getActivationEndDate() : ?string
    {
        return $this->activation_ending_time;
    }
    
    public function setViewOwnResults(bool $a_value) : void
    {
        $this->view_own_results = $a_value;
    }
    
    public function hasViewOwnResults() : bool
    {
        return $this->view_own_results;
    }
    
    public function setMailOwnResults(bool $a_value) : void
    {
        $this->mail_own_results = $a_value;
    }
    
    public function hasMailOwnResults() : bool
    {
        return $this->mail_own_results;
    }
    
    public function setMailConfirmation(bool $a_value) : void
    {
        $this->mail_confirmation = $a_value;
    }
    
    public function hasMailConfirmation() : bool
    {
        return $this->mail_confirmation;
    }
    
    public function setAnonymousUserList(bool $a_value) : void
    {
        $this->anon_user_list = $a_value;
    }
    
    public function hasAnonymousUserList() : bool
    {
        return $this->anon_user_list;
    }
    
    public static function getSurveySkippedValue() : string
    {
        global $DIC;

        $lng = $DIC->language();
        
        // #13541
        
        $surveySetting = new ilSetting("survey");
        if (!$surveySetting->get("skipped_is_custom", false)) {
            return $lng->txt("skipped");
        } else {
            return $surveySetting->get("skipped_custom_value", "");
        }
    }
    
    public function getReminderMailTemplates(
        int &$defaultTemplateId = null
    ) : array {
        global $DIC;

        $res = array();

        /** @var \ilMailTemplateService $templateService */
        $templateService = $DIC['mail.texttemplates.service'];
        foreach ($templateService->loadTemplatesForContextId(ilSurveyMailTemplateReminderContext::ID) as $tmpl) {
            $res[$tmpl->getTplId()] = $tmpl->getTitle();
            if (null !== $defaultTemplateId && $tmpl->isDefault()) {
                $defaultTemplateId = $tmpl->getTplId();
            }
        }

        return $res;
    }
        
    protected function sentReminderPlaceholders(
        string $a_message,
        int $a_user_id,
        array $a_context_params
    ) : string {
        // see ilMail::replacePlaceholders()
        try {
            $context = \ilMailTemplateContextService::getTemplateContextById(ilSurveyMailTemplateReminderContext::ID);

            $user = new \ilObjUser($a_user_id);

            $processor = new \ilMailTemplatePlaceholderResolver($context, $a_message);
            $a_message = $processor->resolve($user, $a_context_params);
        } catch (\Exception $e) {
            ilLoggerFactory::getLogger('mail')->error(__METHOD__ . ' has been called with invalid context.');
        }

        return $a_message;
    }

    public function setMode(int $a_value) : void
    {
        $this->mode = $a_value;
    }

    public function getMode() : int
    {
        return $this->mode;
    }

    public function setSelfEvaluationResults(int $a_value) : void
    {
        $this->mode_self_eval_results = $a_value;
    }

    public function getSelfEvaluationResults() : int
    {
        return $this->mode_self_eval_results;
    }

    /**
     * @return int[]
     */
    public static function getSurveysWithTutorResults() : array
    {
        global $ilDB;
        
        $res = array();

        $log = ilLoggerFactory::getLogger("svy");


        $q = "SELECT obj_fi FROM svy_svy" .
            " WHERE tutor_res_cron IS NULL" .
            " AND tutor_res_status = " . $ilDB->quote(1, "integer") .
            " AND enddate < " . $ilDB->quote(date("Ymd000000"), "text");

        if (DEVMODE) {
            $q = "SELECT obj_fi FROM svy_svy" .
                " WHERE tutor_res_status = " . $ilDB->quote(1, "integer") .
                " AND enddate < " . $ilDB->quote(date("Ymd000000"), "text");
        }

        $set = $ilDB->query($q);

        $log->debug($q);

        while ($row = $ilDB->fetchAssoc($set)) {
            $res[] = (int) $row["obj_fi"];
        }
        
        return $res;
    }

    public function getMaxSumScore() : int
    {
        $sum_score = 0;
        foreach (ilObjSurveyQuestionPool::_getQuestionClasses() as $c) {
            $sum_score += call_user_func([$c, "getMaxSumScore"], $this->getSurveyId());
        }
        return $sum_score;
    }
}
