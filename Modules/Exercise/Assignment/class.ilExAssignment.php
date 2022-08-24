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

use ILIAS\Filesystem\Exception\DirectoryNotFoundException;
use ILIAS\Filesystem\Exception\FileNotFoundException;
use ILIAS\Filesystem\Exception\IOException;

/**
 * Exercise assignment
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilExAssignment
{
    /**
     * direct checks against const should be avoided, use type objects instead
     */
    public const TYPE_UPLOAD = 1;
    public const TYPE_BLOG = 2;
    public const TYPE_PORTFOLIO = 3;
    public const TYPE_UPLOAD_TEAM = 4;
    public const TYPE_TEXT = 5;
    public const TYPE_WIKI_TEAM = 6;

    public const FEEDBACK_DATE_DEADLINE = 1;
    public const FEEDBACK_DATE_SUBMISSION = 2;
    public const FEEDBACK_DATE_CUSTOM = 3;

    public const PEER_REVIEW_VALID_NONE = 1;
    public const PEER_REVIEW_VALID_ONE = 2;
    public const PEER_REVIEW_VALID_ALL = 3;

    public const TEAMS_FORMED_BY_PARTICIPANTS = 0;
    public const TEAMS_FORMED_BY_TUTOR = 1;
    public const TEAMS_FORMED_BY_RANDOM = 2;
    public const TEAMS_FORMED_BY_ASSIGNMENT = 3;

    public const DEADLINE_ABSOLUTE = 0;
    public const DEADLINE_RELATIVE = 1;
    protected \ILIAS\Refinery\String\Group $string_transform;

    protected ilDBInterface $db;
    protected ilLanguage $lng;
    protected ilObjUser $user;
    protected ilAppEventHandler $app_event_handler;
    protected ilAccessHandler $access;

    protected int $id = 0;
    protected int $exc_id = 0;
    protected int $type = 0;
    protected ?int $start_time = null;
    protected ?int $deadline = null;
    protected ?int $deadline2 = null;
    protected string $instruction = "";
    protected string $title = "";
    protected bool $mandatory = false;
    protected int $order_nr = 0;
    protected bool $peer = false;       // peer review activated
    protected int $peer_min = 0;
    protected bool $peer_unlock = false;
    protected int $peer_dl = 0;
    protected int $peer_valid;  // passed after submission, one or all peer feedbacks
    protected bool $peer_file = false;
    protected bool $peer_personal = false;   // personalised peer review
    protected ?int $peer_char = null;           // minimun number of characters for peer review
    protected bool $peer_text = false;
    protected bool $peer_rating = false;
    protected int $peer_crit_cat = 0;
    protected ?string $feedback_file = null;
    protected bool $feedback_cron = false;
    protected int $feedback_date = 0;
    protected int $feedback_date_custom = 0;
    protected bool $team_tutor = false;
    protected ?int $max_file = null;
    protected int $portfolio_template = 0;
    protected int $min_char_limit = 0;
    protected int $max_char_limit = 0;
    protected ilExAssignmentTypes $types;
    protected ilExAssignmentTypeInterface $ass_type;
    protected int $deadline_mode = 0;
    protected int $relative_deadline = 0;
    protected int $rel_deadline_last_subm = 0;
    protected array $member_status = [];
    protected ilLogger $log;
    protected ?int $crit_cat = 0;

    /**
     * Constructor
     * @throws ilExcUnknownAssignmentTypeException
     */
    public function __construct($a_id = 0)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->lng = $DIC->language();
        $this->user = $DIC->user();
        $this->app_event_handler = $DIC["ilAppEventHandler"];
        $this->types = ilExAssignmentTypes::getInstance();
        $this->access = $DIC->access();

        $this->setType(self::TYPE_UPLOAD);
        $this->setFeedbackDate(self::FEEDBACK_DATE_DEADLINE);

        $this->log = ilLoggerFactory::getLogger("exc");

        if ($a_id > 0) {
            $this->setId($a_id);
            $this->read();
        }
        $this->string_transform = $DIC->refinery()
            ->string();
    }

    /**
     * @param int $a_exc_id
     * @return ilExAssignment[]
     * @throws ilExcUnknownAssignmentTypeException
     */
    public static function getInstancesByExercise(int $a_exc_id): array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->query("SELECT * FROM exc_assignment " .
            " WHERE exc_id = " . $ilDB->quote($a_exc_id, "integer") .
            " ORDER BY order_nr");
        $data = array();

        $order_val = 10;
        while ($rec = $ilDB->fetchAssoc($set)) {
            // ???
            $rec["order_val"] = $order_val;

            $ass = new self();
            $ass->initFromDB($rec);
            $data[] = $ass;

            $order_val += 10;
        }

        return $data;
    }

    /**
     * @param array $a_file_data
     * @param int   $a_ass_id assignment id
     * @return int[]
     */
    public static function instructionFileGetFileOrderData(
        array $a_file_data,
        int $a_ass_id
    ): array {
        global $DIC;

        $db = $DIC->database();
        $db->setLimit(1, 0);

        $result_order_val = $db->query("
				SELECT id, order_nr
				FROM exc_ass_file_order
				WHERE assignment_id = {$db->quote($a_ass_id, 'integer')}
				AND filename = {$db->quote($a_file_data['entry'], 'string')}
			");

        $order_val = 0;
        $order_id = 0;
        while ($row = $db->fetchAssoc($result_order_val)) {
            $order_val = (int) $row['order_nr'];
            $order_id = (int) $row['id'];
        }
        return array($order_val, $order_id);
    }

    public function hasTeam(): bool
    {
        return $this->ass_type->usesTeams();
    }

    public function setId(int $a_val): void
    {
        $this->id = $a_val;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setExerciseId(int $a_val): void
    {
        $this->exc_id = $a_val;
    }

    public function getExerciseId(): int
    {
        return $this->exc_id;
    }

    public function setStartTime(?int $a_val): void
    {
        $this->start_time = $a_val;
    }

    public function getStartTime(): ?int
    {
        return $this->start_time;
    }

    public function setDeadline(?int $a_val): void
    {
        $this->deadline = $a_val;
    }

    public function getDeadline(): ?int
    {
        return $this->deadline;
    }

    /**
     * Set deadline mode
     * @param int $a_val deadline mode (self::DEADLINE_ABSOLUTE | self::DEADLINE_ABSOLUTE)
     */
    public function setDeadlineMode(int $a_val): void
    {
        $this->deadline_mode = $a_val;
    }

    public function getDeadlineMode(): int
    {
        return $this->deadline_mode;
    }

    public function setRelativeDeadline(int $a_val): void
    {
        $this->relative_deadline = $a_val;
    }

    public function getRelativeDeadline(): int
    {
        return $this->relative_deadline;
    }

    public function setRelDeadlineLastSubmission(int $a_val): void
    {
        $this->rel_deadline_last_subm = $a_val;
    }

    public function getRelDeadlineLastSubmission(): int
    {
        return $this->rel_deadline_last_subm;
    }


    // Get individual deadline (max of common or idl (team) deadline = Official Deadline)
    public function getPersonalDeadline(int $a_user_id): int
    {
        $ilDB = $this->db;

        $is_team = false;
        if ($this->ass_type->usesTeams()) {
            $team_id = ilExAssignmentTeam::getTeamId($this->getId(), $a_user_id);
            if (!$team_id) {
                // #0021043
                return $this->getDeadline();
            }
            $a_user_id = $team_id;
            $is_team = true;
        }

        $set = $ilDB->query("SELECT tstamp FROM exc_idl" .
            " WHERE ass_id = " . $ilDB->quote($this->getId(), "integer") .
            " AND member_id = " . $ilDB->quote($a_user_id, "integer") .
            " AND is_team = " . $ilDB->quote($is_team, "integer"));
        $row = $ilDB->fetchAssoc($set);

        // use assignment deadline if no direct personal
        return max(($row["tstamp"] ?? 0), $this->getDeadline());
    }

    // Get last/final personal deadline (of assignment)
    public function getLastPersonalDeadline(): int
    {
        $ilDB = $this->db;

        $set = $ilDB->query("SELECT MAX(tstamp) FROM exc_idl" .
            " WHERE ass_id = " . $ilDB->quote($this->getId(), "integer"));
        $row = $ilDB->fetchAssoc($set);
        return $row["tstamp"] ?? 0;
    }

    // Set extended deadline (timestamp)
    public function setExtendedDeadline(?int $a_val): void
    {
        $this->deadline2 = $a_val;
    }

    public function getExtendedDeadline(): ?int
    {
        return $this->deadline2;
    }

    public function setInstruction(string $a_val): void
    {
        $this->instruction = $a_val;
    }

    public function getInstruction(): string
    {
        return $this->instruction;
    }

    public function getInstructionPresentation(): string
    {
        $inst = $this->getInstruction();
        if (trim($inst)) {
            $is_html = (strlen($inst) != strlen(strip_tags($inst)));
            if (!$is_html) {
                $inst = nl2br(
                    $this->string_transform->makeClickable()->transform($inst)
                );
            }
        }
        return $inst;
    }

    public function setTitle(string $a_val): void
    {
        $this->title = $a_val;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setMandatory(bool $a_val): void
    {
        $this->mandatory = $a_val;
    }

    public function getMandatory(): bool
    {
        return $this->mandatory;
    }

    public function setOrderNr(int $a_val): void
    {
        $this->order_nr = $a_val;
    }

    public function getOrderNr(): int
    {
        return $this->order_nr;
    }

    /**
     * Set type
     * this will most probably become an non public function in the future (or become obsolete)
     * @throws ilExcUnknownAssignmentTypeException
     */
    public function setType(int $a_value): void
    {
        if ($this->isValidType($a_value)) {
            $this->type = $a_value;

            $this->ass_type = $this->types->getById($a_value);

            if ($this->ass_type->usesTeams()) {
                $this->setPeerReview(false);
            }
        }
    }

    public function getAssignmentType(): ilExAssignmentTypeInterface
    {
        return $this->ass_type;
    }


    /**
     * Get type
     * this will most probably become an non public function in the future (or become obsolete)
     */
    public function getType(): int
    {
        return $this->type;
    }

    public function isValidType(int $a_value): bool
    {
        return $this->types->isValidId($a_value);
    }

    public function setPeerReview(bool $a_value): void
    {
        $this->peer = $a_value;
    }

    public function getPeerReview(): bool
    {
        return $this->peer;
    }

    public function setPeerReviewMin(int $a_value): void
    {
        $this->peer_min = $a_value;
    }

    public function getPeerReviewMin(): int
    {
        return $this->peer_min;
    }

    public function setPeerReviewSimpleUnlock(bool $a_value)
    {
        $this->peer_unlock = $a_value;
    }

    public function getPeerReviewSimpleUnlock(): bool
    {
        return $this->peer_unlock;
    }

    /**
     * @param	int		deadline (timestamp)
     */
    public function setPeerReviewDeadline(int $a_val): void
    {
        $this->peer_dl = $a_val;
    }

    public function getPeerReviewDeadline(): int
    {
        return $this->peer_dl;
    }

    /**
     * Set peer review validation
     * @param int $a_value (self::PEER_REVIEW_VALID_NONE, self::PEER_REVIEW_VALID_ONE,
     *                     self::PEER_REVIEW_VALID_ALL)
     */
    public function setPeerReviewValid(int $a_value): void
    {
        $this->peer_valid = $a_value;
    }

    public function getPeerReviewValid(): int
    {
        return $this->peer_valid;
    }

    public function setPeerReviewRating(bool $a_val): void
    {
        $this->peer_rating = $a_val;
    }

    public function hasPeerReviewRating(): bool
    {
        return $this->peer_rating;
    }

    public function setPeerReviewText(bool $a_val): void
    {
        $this->peer_text = $a_val;
    }

    public function hasPeerReviewText(): bool
    {
        return $this->peer_text;
    }

    public function setPeerReviewFileUpload(bool $a_val): void
    {
        $this->peer_file = $a_val;
    }

    public function hasPeerReviewFileUpload(): bool
    {
        return $this->peer_file;
    }

    public function setPeerReviewPersonalized(bool $a_val): void
    {
        $this->peer_personal = $a_val;
    }

    public function hasPeerReviewPersonalized(): bool
    {
        return $this->peer_personal;
    }

    public function setPeerReviewChars(?int $a_value): void
    {
        $a_value = (is_numeric($a_value) && (int) $a_value > 0)
            ? (int) $a_value
            : null;
        $this->peer_char = $a_value;
    }

    public function getPeerReviewChars(): ?int
    {
        return $this->peer_char;
    }

    public function setPeerReviewCriteriaCatalogue(?int $a_value): void
    {
        $this->crit_cat = $a_value;
    }

    public function getPeerReviewCriteriaCatalogue(): ?int
    {
        return $this->crit_cat;
    }

    public function getPeerReviewCriteriaCatalogueItems(): array
    {
        if ($this->crit_cat) {
            return ilExcCriteria::getInstancesByParentId($this->crit_cat);
        } else {
            $res = array();

            if ($this->peer_rating) {
                $res[] = ilExcCriteria::getInstanceByType("rating");
            }

            if ($this->peer_text) {
                /** @var $crit ilExcCriteriaText */
                $crit = ilExcCriteria::getInstanceByType("text");
                if ($this->peer_char) {
                    $crit->setMinChars($this->peer_char);
                }
                $res[] = $crit;
            }

            if ($this->peer_file) {
                $res[] = ilExcCriteria::getInstanceByType("file");
            }

            return $res;
        }
    }

    public function setFeedbackFile(?string $a_value): void
    {
        $this->feedback_file = $a_value;
    }

    public function getFeedbackFile(): ?string
    {
        return $this->feedback_file;
    }

    /**
     * Toggle (global) feedback file cron
     */
    public function setFeedbackCron(bool $a_value): void
    {
        $this->feedback_cron = $a_value;
    }

    public function hasFeedbackCron(): bool
    {
        return $this->feedback_cron;
    }

    // Set (global) feedback file availability date
    public function setFeedbackDate(int $a_value): void
    {
        $this->feedback_date = $a_value;
    }

    public function getFeedbackDate(): int
    {
        return $this->feedback_date;
    }

    /**
     * Set (global) feedback file availability using a custom date.
     * @param int $a_value timestamp
     */
    public function setFeedbackDateCustom(int $a_value): void
    {
        $this->feedback_date_custom = $a_value;
    }

    public function getFeedbackDateCustom(): int
    {
        return $this->feedback_date_custom;
    }

    // Set team management by tutor
    public function setTeamTutor(bool $a_value): void
    {
        $this->team_tutor = $a_value;
    }

    public function getTeamTutor(): bool
    {
        return $this->team_tutor;
    }

    // Set max number of uploads
    public function setMaxFile(?int $a_value): void
    {
        $this->max_file = $a_value;
    }

    public function getMaxFile(): ?int
    {
        return $this->max_file;
    }

    // Set portfolio template id
    public function setPortfolioTemplateId(int $a_val): void
    {
        $this->portfolio_template = $a_val;
    }

    public function getPortfolioTemplateId(): int
    {
        return $this->portfolio_template;
    }

    /**
     * @throws ilExcUnknownAssignmentTypeException
     */
    public function read(): void
    {
        $ilDB = $this->db;

        $set = $ilDB->query(
            "SELECT * FROM exc_assignment " .
            " WHERE id = " . $ilDB->quote($this->getId(), "integer")
        );
        $rec = $ilDB->fetchAssoc($set);

        // #16172 - might be deleted
        if (is_array($rec)) {
            $this->initFromDB($rec);
        }
    }

    /**
     * Import DB record
     * @param array $a_set
     * @throws ilExcUnknownAssignmentTypeException
     */
    protected function initFromDB(array $a_set): void
    {
        $this->setId((int) $a_set["id"]);
        $this->setExerciseId((int) $a_set["exc_id"]);
        $this->setDeadline((int) $a_set["time_stamp"]);
        $this->setExtendedDeadline((int) $a_set["deadline2"]);
        $this->setInstruction((string) $a_set["instruction"]);
        $this->setTitle((string) $a_set["title"]);
        $this->setStartTime((int) $a_set["start_time"]);
        $this->setOrderNr((int) $a_set["order_nr"]);
        $this->setMandatory((bool) $a_set["mandatory"]);
        $this->setType((int) $a_set["type"]);
        $this->setPeerReview((bool) $a_set["peer"]);
        $this->setPeerReviewMin((int) $a_set["peer_min"]);
        $this->setPeerReviewSimpleUnlock((bool) $a_set["peer_unlock"]);
        $this->setPeerReviewDeadline((int) $a_set["peer_dl"]);
        $this->setPeerReviewValid((int) $a_set["peer_valid"]);
        $this->setPeerReviewFileUpload((bool) $a_set["peer_file"]);
        $this->setPeerReviewPersonalized((bool) $a_set["peer_prsl"]);
        $this->setPeerReviewChars((int) $a_set["peer_char"]);
        $this->setPeerReviewText((bool) $a_set["peer_text"]);
        $this->setPeerReviewRating((bool) $a_set["peer_rating"]);
        $this->setPeerReviewCriteriaCatalogue((int) $a_set["peer_crit_cat"]);
        $this->setFeedbackFile((string) $a_set["fb_file"]);
        $this->setFeedbackDate((int) $a_set["fb_date"]);
        $this->setFeedbackDateCustom((int) $a_set["fb_date_custom"]);
        $this->setFeedbackCron((bool) $a_set["fb_cron"]);
        $this->setTeamTutor((bool) $a_set["team_tutor"]);
        $this->setMaxFile((int) $a_set["max_file"]);
        $this->setPortfolioTemplateId((int) $a_set["portfolio_template"]);
        $this->setMinCharLimit((int) $a_set["min_char_limit"]);
        $this->setMaxCharLimit((int) $a_set["max_char_limit"]);
        $this->setDeadlineMode((int) $a_set["deadline_mode"]);
        $this->setRelativeDeadline((int) $a_set["relative_deadline"]);
        $this->setRelDeadlineLastSubmission((int) $a_set["rel_deadline_last_subm"]);
    }

    /**
     * @throws ilDateTimeException
     */
    public function save(): void
    {
        $ilDB = $this->db;

        if ($this->getOrderNr() == 0) {
            $this->setOrderNr(
                self::lookupMaxOrderNrForEx($this->getExerciseId())
                + 10
            );
        }

        $next_id = $ilDB->nextId("exc_assignment");
        $ilDB->insert("exc_assignment", array(
            "id" => array("integer", $next_id),
            "exc_id" => array("integer", $this->getExerciseId()),
            "time_stamp" => array("integer", $this->getDeadline()),
            "deadline2" => array("integer", $this->getExtendedDeadline()),
            "instruction" => array("clob", $this->getInstruction()),
            "title" => array("text", $this->getTitle()),
            "start_time" => array("integer", $this->getStartTime()),
            "order_nr" => array("integer", $this->getOrderNr()),
            "mandatory" => array("integer", $this->getMandatory()),
            "type" => array("integer", $this->getType()),
            "peer" => array("integer", $this->getPeerReview()),
            "peer_min" => array("integer", $this->getPeerReviewMin()),
            "peer_unlock" => array("integer", $this->getPeerReviewSimpleUnlock()),
            "peer_dl" => array("integer", $this->getPeerReviewDeadline()),
            "peer_valid" => array("integer", $this->getPeerReviewValid()),
            "peer_file" => array("integer", $this->hasPeerReviewFileUpload()),
            "peer_prsl" => array("integer", $this->hasPeerReviewPersonalized()),
            "peer_char" => array("integer", $this->getPeerReviewChars()),
            "peer_text" => array("integer", (int) $this->hasPeerReviewText()),
            "peer_rating" => array("integer", (int) $this->hasPeerReviewRating()),
            "peer_crit_cat" => array("integer", $this->getPeerReviewCriteriaCatalogue()),
            "fb_file" => array("text", $this->getFeedbackFile()),
            "fb_date" => array("integer", $this->getFeedbackDate()),
            "fb_date_custom" => array("integer", $this->getFeedbackDateCustom()),
            "fb_cron" => array("integer", $this->hasFeedbackCron()),
            "team_tutor" => array("integer", $this->getTeamTutor()),
            "max_file" => array("integer", $this->getMaxFile()),
            "portfolio_template" => array("integer", $this->getPortfolioTemplateId()),
            "min_char_limit" => array("integer", $this->getMinCharLimit()),
            "max_char_limit" => array("integer", $this->getMaxCharLimit()),
            "relative_deadline" => array("integer", $this->getRelativeDeadline()),
            "rel_deadline_last_subm" => array("integer", $this->getRelDeadlineLastSubmission()),
            "deadline_mode" => array("integer", $this->getDeadlineMode())
            ));
        $this->setId($next_id);
        $exc = new ilObjExercise($this->getExerciseId(), false);
        $exc->updateAllUsersStatus();
        self::createNewAssignmentRecords($next_id, $exc);

        $this->handleCalendarEntries("create");
    }

    /**
     * @throws ilDateTimeException
     */
    public function update(): void
    {
        $ilDB = $this->db;

        $ilDB->update(
            "exc_assignment",
            array(
            "exc_id" => array("integer", $this->getExerciseId()),
            "time_stamp" => array("integer", $this->getDeadline()),
            "deadline2" => array("integer", $this->getExtendedDeadline()),
            "instruction" => array("clob", $this->getInstruction()),
            "title" => array("text", $this->getTitle()),
            "start_time" => array("integer", $this->getStartTime()),
            "order_nr" => array("integer", $this->getOrderNr()),
            "mandatory" => array("integer", $this->getMandatory()),
            "type" => array("integer", $this->getType()),
            "peer" => array("integer", $this->getPeerReview()),
            "peer_min" => array("integer", $this->getPeerReviewMin()),
            "peer_unlock" => array("integer", $this->getPeerReviewSimpleUnlock()),
            "peer_dl" => array("integer", $this->getPeerReviewDeadline()),
            "peer_valid" => array("integer", $this->getPeerReviewValid()),
            "peer_file" => array("integer", $this->hasPeerReviewFileUpload()),
            "peer_prsl" => array("integer", $this->hasPeerReviewPersonalized()),
            "peer_char" => array("integer", $this->getPeerReviewChars()),
            "peer_text" => array("integer", (int) $this->hasPeerReviewText()),
            "peer_rating" => array("integer", (int) $this->hasPeerReviewRating()),
            "peer_crit_cat" => array("integer", $this->getPeerReviewCriteriaCatalogue()),
            "fb_file" => array("text", $this->getFeedbackFile()),
            "fb_date" => array("integer", $this->getFeedbackDate()),
            "fb_date_custom" => array("integer", $this->getFeedbackDateCustom()),
            "fb_cron" => array("integer", $this->hasFeedbackCron()),
            "team_tutor" => array("integer", $this->getTeamTutor()),
            "max_file" => array("integer", $this->getMaxFile()),
            "portfolio_template" => array("integer", $this->getPortfolioTemplateId()),
            "min_char_limit" => array("integer", $this->getMinCharLimit()),
            "max_char_limit" => array("integer", $this->getMaxCharLimit()),
            "deadline_mode" => array("integer", $this->getDeadlineMode()),
            "relative_deadline" => array("integer", $this->getRelativeDeadline()),
            "rel_deadline_last_subm" => array("integer", $this->getRelDeadlineLastSubmission())
            ),
            array(
            "id" => array("integer", $this->getId()),
            )
        );
        $exc = new ilObjExercise($this->getExerciseId(), false);
        $exc->updateAllUsersStatus();

        $this->handleCalendarEntries("update");
    }

    /**
     * @throws ilDateTimeException
     */
    public function delete(): void
    {
        $ilDB = $this->db;

        $this->deleteGlobalFeedbackFile();

        $ilDB->manipulate(
            "DELETE FROM exc_assignment WHERE " .
            " id = " . $ilDB->quote($this->getId(), "integer")
        );
        $exc = new ilObjExercise($this->getExerciseId(), false);
        $exc->updateAllUsersStatus();

        $this->handleCalendarEntries("delete");

        $reminder = new ilExAssignmentReminder();
        $reminder->deleteReminders($this->getId());
    }


    // Get assignments data of an exercise in an array
    public static function getAssignmentDataOfExercise(int $a_exc_id): array
    {
        global $DIC;

        $ilDB = $DIC->database();

        // should be changed to self::getInstancesByExerciseId()

        $set = $ilDB->query("SELECT * FROM exc_assignment " .
            " WHERE exc_id = " . $ilDB->quote($a_exc_id, "integer") .
            " ORDER BY order_nr");
        $data = array();

        $order_val = 10;
        while ($rec = $ilDB->fetchAssoc($set)) {
            $data[] = array(
                "id" => (int) $rec["id"],
                "exc_id" => (int) $rec["exc_id"],
                "deadline" => (int) $rec["time_stamp"],
                "deadline2" => (int) $rec["deadline2"],
                "instruction" => (string) $rec["instruction"],
                "title" => (string) $rec["title"],
                "start_time" => (int) $rec["start_time"],
                "order_val" => $order_val,
                "mandatory" => (bool) $rec["mandatory"],
                "type" => (int) $rec["type"],
                "peer" => (bool) $rec["peer"],
                "peer_min" => (int) $rec["peer_min"],
                "peer_dl" => (int) $rec["peer_dl"],
                "peer_file" => (bool) $rec["peer_file"],
                "peer_prsl" => (bool) $rec["peer_prsl"],
                "fb_file" => (string) $rec["fb_file"],
                "fb_date" => (int) $rec["fb_date"],
                "fb_cron" => (bool) $rec["fb_cron"],
                "deadline_mode" => (int) $rec["deadline_mode"],
                "relative_deadline" => (int) $rec["relative_deadline"],
                "rel_deadline_last_subm" => (int) $rec["rel_deadline_last_subm"]
                );
            $order_val += 10;
        }
        return $data;
    }

    /**
     * Clone assignments of exercise
     * @throws DirectoryNotFoundException
     * @throws FileNotFoundException
     * @throws IOException
     * @throws ilExcUnknownAssignmentTypeException|ilDateTimeException
     */
    public static function cloneAssignmentsOfExercise(
        int $a_old_exc_id,
        int $a_new_exc_id,
        array $a_crit_cat_map
    ): void {
        $ass_data = self::getInstancesByExercise($a_old_exc_id);
        foreach ($ass_data as $d) {
            // clone assignment
            $new_ass = new ilExAssignment();
            $new_ass->setExerciseId($a_new_exc_id);
            $new_ass->setTitle($d->getTitle());
            $new_ass->setDeadline($d->getDeadline());
            $new_ass->setExtendedDeadline($d->getExtendedDeadline());
            $new_ass->setInstruction($d->getInstruction());
            $new_ass->setMandatory($d->getMandatory());
            $new_ass->setOrderNr($d->getOrderNr());
            $new_ass->setStartTime($d->getStartTime());
            $new_ass->setType($d->getType());
            $new_ass->setPeerReview($d->getPeerReview());
            $new_ass->setPeerReviewMin($d->getPeerReviewMin());
            $new_ass->setPeerReviewDeadline($d->getPeerReviewDeadline());
            $new_ass->setPeerReviewFileUpload($d->hasPeerReviewFileUpload());
            $new_ass->setPeerReviewPersonalized($d->hasPeerReviewPersonalized());
            $new_ass->setPeerReviewValid($d->getPeerReviewValid());
            $new_ass->setPeerReviewChars($d->getPeerReviewChars());
            $new_ass->setPeerReviewText($d->hasPeerReviewText());
            $new_ass->setPeerReviewRating($d->hasPeerReviewRating());
            $new_ass->setPeerReviewCriteriaCatalogue($d->getPeerReviewCriteriaCatalogue());
            $new_ass->setPeerReviewSimpleUnlock($d->getPeerReviewSimpleUnlock());
            $new_ass->setFeedbackFile($d->getFeedbackFile());
            $new_ass->setFeedbackDate($d->getFeedbackDate());
            $new_ass->setFeedbackDateCustom($d->getFeedbackDateCustom());
            $new_ass->setFeedbackCron($d->hasFeedbackCron()); // #16295
            $new_ass->setTeamTutor($d->getTeamTutor());
            $new_ass->setMaxFile($d->getMaxFile());
            $new_ass->setMinCharLimit($d->getMinCharLimit());
            $new_ass->setMaxCharLimit($d->getMaxCharLimit());
            $new_ass->setPortfolioTemplateId($d->getPortfolioTemplateId());
            $new_ass->setDeadlineMode($d->getDeadlineMode());
            $new_ass->setRelativeDeadline($d->getRelativeDeadline());
            $new_ass->setRelDeadlineLastSubmission($d->getRelDeadlineLastSubmission());

            // criteria catalogue(s)
            if ($d->getPeerReviewCriteriaCatalogue() &&
                array_key_exists($d->getPeerReviewCriteriaCatalogue(), $a_crit_cat_map)) {
                $new_ass->setPeerReviewCriteriaCatalogue($a_crit_cat_map[$d->getPeerReviewCriteriaCatalogue()]);
            }

            $new_ass->save();


            // clone assignment files
            $old_web_storage = new ilFSWebStorageExercise($a_old_exc_id, $d->getId());
            $new_web_storage = new ilFSWebStorageExercise($a_new_exc_id, $new_ass->getId());
            $new_web_storage->create();
            if (is_dir($old_web_storage->getPath())) {
                ilFileUtils::rCopy($old_web_storage->getPath(), $new_web_storage->getPath());
            }
            $order = $d->getInstructionFilesOrder();
            foreach ($order as $file) {
                ilExAssignment::insertFileOrderNr($new_ass->getId(), $file["filename"], $file["order_nr"]);
            }

            // clone global feedback file
            $old_storage = new ilFSStorageExercise($a_old_exc_id, $d->getId());
            $new_storage = new ilFSStorageExercise($a_new_exc_id, $new_ass->getId());
            $new_storage->create();
            if (is_dir($old_storage->getGlobalFeedbackPath())) {
                ilFileUtils::rCopy($old_storage->getGlobalFeedbackPath(), $new_storage->getGlobalFeedbackPath());
            }

            // clone reminders
            foreach ([ilExAssignmentReminder::SUBMIT_REMINDER,
                      ilExAssignmentReminder::GRADE_REMINDER,
                      ilExAssignmentReminder::FEEDBACK_REMINDER] as $rem_type) {
                $rmd_sub = new ilExAssignmentReminder($a_old_exc_id, $d->getId(), $rem_type);
                if ($rmd_sub->getReminderStatus()) {
                    $new_rmd_sub = new ilExAssignmentReminder($a_new_exc_id, $new_ass->getId(), $rem_type);
                    $new_rmd_sub->setReminderStatus($rmd_sub->getReminderStatus());
                    $new_rmd_sub->setReminderStart($rmd_sub->getReminderStart());
                    $new_rmd_sub->setReminderEnd($rmd_sub->getReminderEnd());
                    $new_rmd_sub->setReminderFrequency($rmd_sub->getReminderFrequency());
                    $new_rmd_sub->setReminderMailTemplate($rmd_sub->getReminderMailTemplate());
                    $new_rmd_sub->save();
                }
            }


            // type specific properties
            $ass_type = $d->getAssignmentType();
            $ass_type->cloneSpecificProperties($d, $new_ass);
        }
    }

    public function getFiles(): array
    {
        $this->log->debug("getting files from class.ilExAssignment using ilFSWebStorageExercise");
        $storage = new ilFSWebStorageExercise($this->getExerciseId(), $this->getId());
        return $storage->getFiles();
    }

    public function getInstructionFilesOrder(): array
    {
        $ilDB = $this->db;

        $set = $ilDB->query(
            "SELECT filename, order_nr, id FROM exc_ass_file_order " .
            " WHERE assignment_id  = " . $ilDB->quote($this->getId(), "integer")
        );

        $data = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $data[$rec['filename']] = $rec;
        }

        return $data;
    }

    // Select the maximum order nr for an exercise
    public static function lookupMaxOrderNrForEx(int $a_exc_id): int
    {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->query(
            "SELECT MAX(order_nr) mnr FROM exc_assignment " .
            " WHERE exc_id = " . $ilDB->quote($a_exc_id, "integer")
        );
        if ($rec = $ilDB->fetchAssoc($set)) {
            return (int) $rec["mnr"];
        }
        return 0;
    }

    public static function lookupAssignmentOnline(int $a_ass_id): bool
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT id FROM exc_assignment " .
            "WHERE start_time <= " . $ilDB->quote(time(), 'integer') . ' ' .
            "AND time_stamp >= " . $ilDB->quote(time(), 'integer') . ' ' .
            "AND id = " . $ilDB->quote($a_ass_id, 'integer');
        $res = $ilDB->query($query);

        return (bool) $res->numRows();
    }

    public static function lookupExerciseId(int $a_ass_id): int
    {
        global $DIC;

        $ilDB = $DIC->database();
        $query = "SELECT exc_id FROM exc_assignment " .
            "WHERE id = " . $ilDB->quote($a_ass_id, 'integer');
        $res = $ilDB->fetchAssoc($ilDB->query($query));

        return (int) ($res["exc_id"] ?? 0);
    }

    private static function lookup(int $a_id, string $a_field): string
    {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->query(
            "SELECT " . $a_field . " FROM exc_assignment " .
            " WHERE id = " . $ilDB->quote($a_id, "integer")
        );

        $rec = $ilDB->fetchAssoc($set);

        return $rec[$a_field] ?? "";
    }

    public static function lookupTitle(int $a_id): string
    {
        return self::lookup($a_id, "title");
    }

    public static function lookupType(int $a_id): string
    {
        return self::lookup($a_id, "type");
    }

    // Save ordering of all assignments of an exercise
    public static function saveAssOrderOfExercise(int $a_ex_id, array $a_order): void
    {
        global $DIC;

        $ilDB = $DIC->database();

        asort($a_order);
        $nr = 10;
        foreach ($a_order as $k => $v) {
            // the check for exc_id is for security reasons. ass ids are unique.
            $ilDB->manipulate(
                "UPDATE exc_assignment SET " .
                " order_nr = " . $ilDB->quote($nr, "integer") .
                " WHERE id = " . $ilDB->quote((int) $k, "integer") .
                " AND exc_id = " . $ilDB->quote($a_ex_id, "integer")
            );
            $nr += 10;
        }
    }

    // Order assignments by deadline date
    public static function orderAssByDeadline(int $a_ex_id): void
    {
        global $DIC;
        $ilDB = $DIC->database();

        $set = $ilDB->query(
            "SELECT id FROM exc_assignment " .
            " WHERE exc_id = " . $ilDB->quote($a_ex_id, "integer") .
            " ORDER BY time_stamp"
        );
        $nr = 10;
        while ($rec = $ilDB->fetchAssoc($set)) {
            $ilDB->manipulate(
                "UPDATE exc_assignment SET " .
                " order_nr = " . $ilDB->quote($nr, "integer") .
                " WHERE id = " . $ilDB->quote($rec["id"], "integer")
            );
            $nr += 10;
        }
    }

    // Count the number of mandatory assignments
    public static function countMandatory(int $a_ex_id): int
    {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->query(
            "SELECT count(*) cntm FROM exc_assignment " .
            " WHERE exc_id = " . $ilDB->quote($a_ex_id, "integer") .
            " AND mandatory = " . $ilDB->quote(1, "integer")
        );
        $rec = $ilDB->fetchAssoc($set);
        return (int) $rec["cntm"];
    }

    // Count assignments
    public static function count(int $a_ex_id): int
    {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->query(
            "SELECT count(*) cntm FROM exc_assignment " .
            " WHERE exc_id = " . $ilDB->quote($a_ex_id, "integer")
        );
        $rec = $ilDB->fetchAssoc($set);
        return $rec["cntm"];
    }

    // Is assignment in exercise?
    public static function isInExercise(int $a_ass_id, int $a_ex_id): bool
    {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->query(
            "SELECT * FROM exc_assignment " .
            " WHERE exc_id = " . $ilDB->quote($a_ex_id, "integer") .
            " AND id = " . $ilDB->quote($a_ass_id, "integer")
        );
        if ($ilDB->fetchAssoc($set)) {
            return true;
        }
        return false;
    }

    public function getMemberListData(): array
    {
        $ilDB = $this->db;

        $mem = array();

        // first get list of members from member table
        $set = $ilDB->query("SELECT ud.usr_id, ud.lastname, ud.firstname, ud.login" .
            " FROM exc_members excm" .
            " JOIN usr_data ud ON (ud.usr_id = excm.usr_id)" .
            " WHERE excm.obj_id = " . $ilDB->quote($this->getExerciseId(), "integer"));
        while ($rec = $ilDB->fetchAssoc($set)) {
            $mem[$rec["usr_id"]] =
                array(
                "name" => $rec["lastname"] . ", " . $rec["firstname"],
                "login" => $rec["login"],
                "usr_id" => $rec["usr_id"],
                "lastname" => $rec["lastname"],
                "firstname" => $rec["firstname"]
                );
        }

        $q = "SELECT * FROM exc_mem_ass_status " .
            "WHERE ass_id = " . $ilDB->quote($this->getId(), "integer");
        $set = $ilDB->query($q);
        while ($rec = $ilDB->fetchAssoc($set)) {
            if (isset($mem[$rec["usr_id"]])) {
                $sub = new ilExSubmission($this, $rec["usr_id"]);

                $mem[$rec["usr_id"]]["sent_time"] = $rec["sent_time"];
                $mem[$rec["usr_id"]]["submission"] = $sub->getLastSubmission();
                $mem[$rec["usr_id"]]["status_time"] = $rec["status_time"];
                $mem[$rec["usr_id"]]["feedback_time"] = $rec["feedback_time"];
                $mem[$rec["usr_id"]]["notice"] = $rec["notice"];
                $mem[$rec["usr_id"]]["status"] = $rec["status"];
                $mem[$rec["usr_id"]]["mark"] = $rec["mark"];
                $mem[$rec["usr_id"]]["comment"] = $rec["u_comment"];
            }
        }
        return $mem;
    }

    /**
     * Get submission data for an specific user,exercise and assignment.
     * todo we can refactor a bit the method getMemberListData to use this and remove duplicate code.
     */
    public function getExerciseMemberAssignmentData(
        int $a_user_id,
        string $a_grade = ""
    ): array {
        global $DIC;
        $ilDB = $DIC->database();

        $and_grade = "";
        if (in_array($a_grade, array("notgraded", "passed", "failed"))) {
            $and_grade = " AND status = " . $ilDB->quote($a_grade, "text");
        }

        $q = "SELECT * FROM exc_mem_ass_status " .
            "WHERE ass_id = " . $ilDB->quote($this->getId(), "integer") .
            " AND usr_id = " . $ilDB->quote($a_user_id, "integer") .
            $and_grade;

        $set = $ilDB->query($q);

        $data = [];
        while ($rec = $ilDB->fetchAssoc($set)) {
            $sub = new ilExSubmission($this, $a_user_id);

            $data["sent_time"] = $rec["sent_time"];
            $data["submission"] = $sub->getLastSubmission();
            $data["status_time"] = $rec["status_time"];
            $data["feedback_time"] = $rec["feedback_time"];
            $data["notice"] = $rec["notice"];
            $data["status"] = $rec["status"];
            $data["mark"] = $rec["mark"];
            $data["comment"] = $rec["u_comment"];
        }

        return $data;
    }

    // Create member status record for a new participant for all assignments
    public static function createNewUserRecords(
        int $a_user_id,
        int $a_exc_id
    ): void {
        global $DIC;

        $ilDB = $DIC->database();

        $ass_data = self::getAssignmentDataOfExercise($a_exc_id);
        foreach ($ass_data as $ass) {
            //echo "-".$ass["id"]."-".$a_user_id."-";
            $ilDB->replace("exc_mem_ass_status", array(
                "ass_id" => array("integer", $ass["id"]),
                "usr_id" => array("integer", $a_user_id)
                ), array(
                "status" => array("text", "notgraded")
                ));
        }
    }

    // Create member status record for a new assignment for all participants
    public static function createNewAssignmentRecords(
        int $a_ass_id,
        ilObjExercise $a_exc
    ): void {
        global $DIC;

        $ilDB = $DIC->database();

        $exmem = new ilExerciseMembers($a_exc);
        $mems = $exmem->getMembers();

        foreach ($mems as $mem) {
            $ilDB->replace("exc_mem_ass_status", array(
                "ass_id" => array("integer", $a_ass_id),
                "usr_id" => array("integer", $mem)
                ), array(
                "status" => array("text", "notgraded")
                ));
        }
    }

    /**
     * Upload assignment files
     * (from creation form)
     */
    public function uploadAssignmentFiles(array $a_files): void
    {
        ilLoggerFactory::getLogger("exc")->debug("upload assignment files files = ", $a_files);
        $storage = new ilFSWebStorageExercise($this->getExerciseId(), $this->getId());
        $storage->create();
        $storage->uploadAssignmentFiles($a_files);
    }


    ////
    //// Multi-Feedback
    ////

    /**
     * Create member status record for a new assignment for all participants
     */
    public function sendMultiFeedbackStructureFile(ilObjExercise $exercise): void
    {
        $access = $this->access;

        // send and delete the zip file
        $deliverFilename = trim(str_replace(" ", "_", $this->getTitle() . "_" . $this->getId()));
        $deliverFilename = ilFileUtils::getASCIIFilename($deliverFilename);
        $deliverFilename = "multi_feedback_" . $deliverFilename;

        $exc = new ilObjExercise($this->getExerciseId(), false);

        $cdir = getcwd();

        // create temporary directoy
        $tmpdir = ilFileUtils::ilTempnam();
        ilFileUtils::makeDir($tmpdir);
        $mfdir = $tmpdir . "/" . $deliverFilename;
        ilFileUtils::makeDir($mfdir);

        // create subfolders <lastname>_<firstname>_<id> for each participant
        $exmem = new ilExerciseMembers($exc);
        $mems = $exmem->getMembers();

        $mems = $access->filterUserIdsByRbacOrPositionOfCurrentUser(
            'edit_submissions_grades',
            'edit_submissions_grades',
            $exercise->getRefId(),
            $mems
        );
        foreach ($mems as $mem) {
            $name = ilObjUser::_lookupName($mem);
            $subdir = $name["lastname"] . "_" . $name["firstname"] . "_" . $name["login"] . "_" . $name["user_id"];
            $subdir = ilFileUtils::getASCIIFilename($subdir);
            ilFileUtils::makeDir($mfdir . "/" . $subdir);
        }

        // create the zip file
        chdir($tmpdir);
        $tmpzipfile = $tmpdir . "/multi_feedback.zip";
        ilFileUtils::zip($tmpdir, $tmpzipfile, true);
        chdir($cdir);


        ilFileDelivery::deliverFileLegacy($tmpzipfile, $deliverFilename . ".zip", "", false, true);
    }

    /**
     * @throws ilException
     * @throws ilExerciseException
     */
    public function uploadMultiFeedbackFile(array $a_file): void
    {
        $lng = $this->lng;
        $ilUser = $this->user;

        if (!is_file($a_file["tmp_name"])) {
            throw new ilExerciseException($lng->txt("exc_feedback_file_could_not_be_uploaded"));
        }

        $storage = new ilFSStorageExercise($this->getExerciseId(), $this->getId());
        $mfu = $storage->getMultiFeedbackUploadPath($ilUser->getId());
        ilFileUtils::delDir($mfu, true);
        ilFileUtils::moveUploadedFile($a_file["tmp_name"], "multi_feedback.zip", $mfu . "/" . "multi_feedback.zip");
        ilFileUtils::unzip($mfu . "/multi_feedback.zip", true);
        $subdirs = ilFileUtils::getDir($mfu);
        $subdir = "notfound";
        foreach ($subdirs as $s => $j) {
            if ($j["type"] == "dir" && substr($s, 0, 14) == "multi_feedback") {
                $subdir = $s;
            }
        }

        if (!is_dir($mfu . "/" . $subdir)) {
            throw new ilExerciseException($lng->txt("exc_no_feedback_dir_found_in_zip"));
        }
    }

    /**
     * Get multi feedback files (of uploader)
     *
     * @param int $a_user_id user id of uploader
     * @return array array of user files (keys: lastname, firstname, user_id, login, file)
     */
    public function getMultiFeedbackFiles(int $a_user_id = 0): array
    {
        $ilUser = $this->user;

        if ($a_user_id == 0) {
            $a_user_id = $ilUser->getId();
        }

        $mf_files = array();

        // get members
        $exc = new ilObjExercise($this->getExerciseId(), false);
        $exmem = new ilExerciseMembers($exc);
        $mems = $exmem->getMembers();

        // read mf directory
        $storage = new ilFSStorageExercise($this->getExerciseId(), $this->getId());
        $mfu = $storage->getMultiFeedbackUploadPath($a_user_id);

        // get subdir that starts with multi_feedback
        $subdirs = ilFileUtils::getDir($mfu);
        $subdir = "notfound";
        foreach ($subdirs as $s => $j) {
            if ($j["type"] == "dir" && substr($s, 0, 14) == "multi_feedback") {
                $subdir = $s;
            }
        }

        $items = ilFileUtils::getDir($mfu . "/" . $subdir);
        foreach ($items as $k => $i) {
            // check directory
            if ($i["type"] == "dir" && !in_array($k, array(".", ".."))) {
                // check if valid member id is given
                $parts = explode("_", $i["entry"]);
                $user_id = (int) $parts[count($parts) - 1];
                if (in_array($user_id, $mems)) {
                    // read dir of user
                    $name = ilObjUser::_lookupName($user_id);
                    $files = ilFileUtils::getDir($mfu . "/" . $subdir . "/" . $k);
                    foreach ($files as $k2 => $f) {
                        // append files to array
                        if ($f["type"] == "file" && substr($k2, 0, 1) != ".") {
                            $mf_files[] = array(
                                "lastname" => $name["lastname"],
                                "firstname" => $name["firstname"],
                                "login" => $name["login"],
                                "user_id" => $name["user_id"],
                                "full_path" => $mfu . "/" . $subdir . "/" . $k . "/" . $k2,
                                "file" => $k2);
                        }
                    }
                }
            }
        }
        return $mf_files;
    }

    /**
     * Clear multi feedback directory
     */
    public function clearMultiFeedbackDirectory(): void
    {
        $ilUser = $this->user;

        $storage = new ilFSStorageExercise($this->getExerciseId(), $this->getId());
        $mfu = $storage->getMultiFeedbackUploadPath($ilUser->getId());
        ilFileUtils::delDir($mfu);
    }

    public function saveMultiFeedbackFiles(
        array $a_files,
        ilObjExercise $a_exc
    ): void {
        if ($this->getExerciseId() != $a_exc->getId()) {
            return;
        }

        $fstorage = new ilFSStorageExercise($this->getExerciseId(), $this->getId());
        $fstorage->create();

        $mf_files = $this->getMultiFeedbackFiles();
        foreach ($mf_files as $f) {
            $user_id = $f["user_id"];
            $file_path = $f["full_path"];
            $file_name = $f["file"];

            // if checked in confirmation gui
            if (is_array($a_files[$user_id]) && in_array(md5($file_name), $a_files[$user_id])) {
                $submission = new ilExSubmission($this, $user_id);
                $feedback_id = $submission->getFeedbackId();
                $noti_rec_ids = $submission->getUserIds();

                if ($feedback_id) {
                    $fb_path = $fstorage->getFeedbackPath($feedback_id);
                    $target = $fb_path . "/" . $file_name;
                    if (is_file($target)) {
                        unlink($target);
                    }
                    // rename file
                    rename($file_path, $target);

                    if ($noti_rec_ids) {
                        foreach ($noti_rec_ids as $user_id) {
                            $member_status = $this->getMemberStatus($user_id);
                            $member_status->setFeedback(true);
                            $member_status->update();
                        }

                        $a_exc->sendFeedbackFileNotification(
                            $file_name,
                            $noti_rec_ids,
                            $this->getId()
                        );
                    }
                }
            }
        }

        $this->clearMultiFeedbackDirectory();
    }

    /**
     * Handle calendar entries for deadline(s)
     * @throws ilDateTimeException
     */
    protected function handleCalendarEntries(string $a_event): void
    {
        $ilAppEventHandler = $this->app_event_handler;

        $dl_id = $this->getId() . "0";
        $fbdl_id = $this->getId() . "1";

        $context_ids = array($dl_id, $fbdl_id);
        $apps = array();

        if ($a_event != "delete") {
            // deadline or relative deadline given
            if ($this->getDeadline() || $this->getDeadlineMode() == ilExAssignment::DEADLINE_RELATIVE) {
                $app = new ilCalendarAppointmentTemplate($dl_id);
                $app->setTranslationType(ilCalendarEntry::TRANSLATION_SYSTEM);
                $app->setSubtitle("cal_exc_deadline");
                $app->setTitle($this->getTitle());
                $app->setFullday(false);
                // note: in the case of a relative deadline this will be set to 0 / 1970...)
                // see ilCalendarScheduleFilterExercise for appointment modification
                $app->setStart(new ilDateTime($this->getDeadline(), IL_CAL_UNIX));

                $apps[] = $app;
            }

            if ($this->getPeerReview() &&
                $this->getPeerReviewDeadline()) {
                $app = new ilCalendarAppointmentTemplate($fbdl_id);
                $app->setTranslationType(ilCalendarEntry::TRANSLATION_SYSTEM);
                $app->setSubtitle("cal_exc_peer_review_deadline");
                $app->setTitle($this->getTitle());
                $app->setFullday(false);
                $app->setStart(new ilDateTime($this->getPeerReviewDeadline(), IL_CAL_UNIX));

                $apps[] = $app;
            }
        }

        $exc = new ilObjExercise($this->getExerciseId(), false);

        $ilAppEventHandler->raise(
            'Modules/Exercise',
            $a_event . 'Assignment',
            array(
            'object' => $exc,
            'obj_id' => $exc->getId(),
            'context_ids' => $context_ids,
            'appointments' => $apps)
        );
    }

    public static function getPendingFeedbackNotifications(): array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $res = array();

        $set = $ilDB->query("SELECT id,fb_file,time_stamp,deadline2,fb_date FROM exc_assignment" .
            " WHERE fb_cron = " . $ilDB->quote(1, "integer") .
            " AND (fb_date = " . $ilDB->quote(self::FEEDBACK_DATE_DEADLINE, "integer") .
                " AND time_stamp IS NOT NULL" .
                " AND time_stamp > " . $ilDB->quote(0, "integer") .
                " AND time_stamp < " . $ilDB->quote(time(), "integer") .
                " AND fb_cron_done = " . $ilDB->quote(0, "integer") .
            ") OR (fb_date = " . $ilDB->quote(self::FEEDBACK_DATE_CUSTOM, "integer") .
                " AND fb_date_custom IS NOT NULL" .
                " AND fb_date_custom > " . $ilDB->quote(0, "integer") .
                " AND fb_date_custom < " . $ilDB->quote(time(), "integer") .
                " AND fb_cron_done = " . $ilDB->quote(0, "integer") . ")");



        while ($row = $ilDB->fetchAssoc($set)) {
            if ($row['fb_date'] == self::FEEDBACK_DATE_DEADLINE) {
                $max = max($row['time_stamp'], $row['deadline2']);
                if (trim($row["fb_file"]) && $max <= time()) {
                    $res[] = $row["id"];
                }
            } elseif ($row['fb_date'] == self::FEEDBACK_DATE_CUSTOM) {
                if (trim($row["fb_file"]) && $row['fb_date_custom'] <= time()) {
                    $res[] = $row["id"];
                }
            }
        }

        return $res;
    }

    /**
     * @throws ilExcUnknownAssignmentTypeException
     */
    public static function sendFeedbackNotifications(
        int $a_ass_id,
        int $a_user_id = null
    ): bool {
        global $DIC;

        $ilDB = $DIC->database();

        $ass = new self($a_ass_id);

        // valid assignment?
        if (!$ass->hasFeedbackCron() || !$ass->getFeedbackFile()) {
            return false;
        }

        if (!$a_user_id) {
            // already done?
            $set = $ilDB->query("SELECT fb_cron_done" .
                " FROM exc_assignment" .
                " WHERE id = " . $ilDB->quote($a_ass_id, "integer"));
            $row = $ilDB->fetchAssoc($set);
            if ($row["fb_cron_done"]) {
                return false;
            }
        }

        $ntf = new ilSystemNotification();
        $ntf->setLangModules(array("exc"));
        $ntf->setObjId($ass->getExerciseId());
        $ntf->setSubjectLangId("exc_feedback_notification_subject");
        $ntf->setIntroductionLangId("exc_feedback_notification_body");
        $ntf->addAdditionalInfo("exc_assignment", $ass->getTitle());
        $ntf->setGotoLangId("exc_feedback_notification_link");
        $ntf->setReasonLangId("exc_feedback_notification_reason");

        if (!$a_user_id) {
            $ntf->sendMail(ilExerciseMembers::_getMembers($ass->getExerciseId()));

            $ilDB->manipulate("UPDATE exc_assignment" .
                " SET fb_cron_done = " . $ilDB->quote(1, "integer") .
                " WHERE id = " . $ilDB->quote($a_ass_id, "integer"));
        } else {
            $ntf->sendMail(array($a_user_id));
        }

        return true;
    }


    // status

    // like: after effective deadline (for single user), no deadline: true
    public function afterDeadline(): bool
    {
        $ilUser = $this->user;

        // :TODO: always current user?
        $idl = $this->getPersonalDeadline($ilUser->getId());		// official deadline

        // no deadline === true
        $deadline = max($this->deadline, $this->deadline2, $idl);	// includes grace period
        return ($deadline - time() <= 0);
    }

    public function afterDeadlineStrict(bool $a_include_personal = true): bool
    {
        // :TODO: this means that peer feedback, global feedback is available
        // after LAST personal deadline
        // team management is currently ignoring personal deadlines
        $idl = $a_include_personal
            ? $this->getLastPersonalDeadline()
            : null;

        // no deadline === false
        $deadline = max($this->deadline, $this->deadline2, $idl);

        // #18271 - afterDeadline() does not handle last personal deadline
        // after effective deadline of all users
        if ($idl && $deadline == $idl) {
            return ($deadline - time() <= 0);
        }

        // like: after effective deadline (for single user), except: no deadline false
        return ($deadline > 0 &&
            $this->afterDeadline());
    }

    /**
     * @return bool return if sample solution is available using a custom date.
     */
    public function afterCustomDate(): bool
    {
        $date_custom = $this->getFeedbackDateCustom();
        //if the solution will be displayed only after reach all the deadlines.
        //$final_deadline = $this->afterDeadlineStrict();
        //$dl = max($final_deadline, time());
        //return ($date_custom - $dl <= 0);
        return ($date_custom - time() <= 0);
    }

    // like: before effective deadline (for all users), no deadline: true
    public function beforeDeadline(): bool
    {
        // no deadline === true
        return !$this->afterDeadlineStrict();
    }

    public function notStartedYet(): bool
    {
        return (time() - $this->start_time <= 0);
    }


    //
    // FEEDBACK FILES
    //

    public function getGlobalFeedbackFileStoragePath(): string
    {
        $storage = new ilFSStorageExercise($this->getExerciseId(), $this->getId());
        return $storage->getGlobalFeedbackPath();
    }

    public function deleteGlobalFeedbackFile(): void
    {
        ilFileUtils::delDir($this->getGlobalFeedbackFileStoragePath());
    }

    /**
     * @throws ilException
     */
    public function handleGlobalFeedbackFileUpload(array $a_file): bool
    {
        $path = $this->getGlobalFeedbackFileStoragePath();
        ilFileUtils::delDir($path, true);
        if (ilFileUtils::moveUploadedFile($a_file["tmp_name"], $a_file["name"], $path . "/" . $a_file["name"])) {
            $this->setFeedbackFile($a_file["name"]);
            return true;
        }
        return false;
    }

    public function getGlobalFeedbackFilePath(): string
    {
        $file = $this->getFeedbackFile();
        if ($file) {
            $path = $this->getGlobalFeedbackFileStoragePath();
            return $path . "/" . $file;
        }
        return "";
    }

    public function getMemberStatus(?int $a_user_id = null): ilExAssignmentMemberStatus
    {
        $ilUser = $this->user;

        if (!$a_user_id) {
            $a_user_id = $ilUser->getId();
        }
        if (!array_key_exists($a_user_id, $this->member_status)) {
            $this->member_status[$a_user_id] = new ilExAssignmentMemberStatus($this->getId(), $a_user_id);
        }
        return $this->member_status[$a_user_id];
    }

    /**
     * @throws ilDateTimeException
     */
    public function recalculateLateSubmissions(): void
    {
        $ilDB = $this->db;

        // see JF, 2015-05-11

        $ext_deadline = $this->getExtendedDeadline();

        foreach (ilExSubmission::getAllAssignmentFiles($this->exc_id, $this->getId()) as $file) {
            $id = $file["returned_id"];
            $uploaded = new ilDateTime($file["ts"], IL_CAL_DATETIME);
            $uploaded = $uploaded->get(IL_CAL_UNIX);

            $deadline = $this->getPersonalDeadline($file["user_id"]);
            $last_deadline = max($deadline, $this->getExtendedDeadline());

            $late = null;

            // upload is not late anymore
            if ($file["late"] &&
                (!$last_deadline ||
                !$ext_deadline ||
                $uploaded < $deadline)) {
                $late = false;
            }
            // upload is now late
            elseif (!$file["late"] &&
                $ext_deadline &&
                $deadline &&
                $uploaded > $deadline) {
                $late = true;
            }

            if ($late !== null) {
                $ilDB->manipulate("UPDATE exc_returned" .
                    " SET late = " . $ilDB->quote($late, "integer") .
                    " WHERE returned_id = " . $ilDB->quote($id, "integer"));
            }
        }
    }


    //
    // individual deadlines
    //

    public function setIndividualDeadline(
        int $id,
        ilDateTime $date
    ): void {
        $is_team = false;
        if (!is_numeric($id)) {
            $id = substr($id, 1);
            $is_team = true;
        }

        $idl = ilExcIndividualDeadline::getInstance($this->getId(), $id, $is_team);
        $idl->setIndividualDeadline($date->get(IL_CAL_UNIX));
        $idl->save();
    }

    public function getIndividualDeadlines(): array
    {
        $ilDB = $this->db;

        $res = array();

        $set = $ilDB->query("SELECT * FROM exc_idl" .
            " WHERE ass_id = " . $ilDB->quote($this->getId(), "integer"));
        while ($row = $ilDB->fetchAssoc($set)) {
            if ($row["is_team"]) {
                $row["member_id"] = "t" . $row["member_id"];
            }

            $res[$row["member_id"]] = $row["tstamp"];
        }

        return $res;
    }

    public function hasActiveIDl(): bool
    {
        return (bool) $this->getDeadline();
    }

    public function hasReadOnlyIDl(): bool
    {
        if (!$this->ass_type->usesTeams() &&
            $this->getPeerReview()) {
            // all deadlines are read-only if we have peer feedback
            $peer_review = new ilExPeerReview($this);
            if ($peer_review->hasPeerReviewGroups()) {
                return true;
            }
        }

        return false;
    }

    public static function saveInstructionFilesOrderOfAssignment(
        int $a_ass_id,
        array $a_order
    ): void {
        global $DIC;

        $db = $DIC->database();

        asort($a_order, SORT_NUMERIC);

        $nr = 10;
        foreach (array_keys($a_order) as $k) {
            // the check for exc_id is for security reasons. ass ids are unique.
            $db->manipulate(
                "UPDATE exc_ass_file_order SET " .
                " order_nr = " . $db->quote($nr, "integer") .
                " WHERE id = " . $db->quote((int) $k, "integer") .
                " AND assignment_id = " . $db->quote($a_ass_id, "integer")
            );
            $nr += 10;
        }
    }

    public static function insertFileOrderNr(
        int $a_ass_id,
        string $a_filename,
        int $a_order_nr
    ): void {
        global $DIC;
        $db = $DIC->database();
        $id = $db->nextId("exc_ass_file_order");
        $db->insert(
            "exc_ass_file_order",
            [
                "id" => ["integer", $id],
                "order_nr" => ["integer", $a_order_nr],
                "assignment_id" => ["integer", $a_ass_id],
                "filename" => ["text", $a_filename]
            ]
        );
    }

    // Store the order nr of a file in the database
    public static function instructionFileInsertOrder(
        string $a_filename,
        int $a_ass_id,
        int $a_order_nr = 0
    ): void {
        global $DIC;

        $db = $DIC->database();

        if ($a_ass_id) {
            //first of all check the suffix and change if necessary
            $filename = ilFileUtils::getSafeFilename($a_filename);

            if (self::instructionFileExistsInDb($filename, $a_ass_id) == 0) {
                if ($a_order_nr == 0) {
                    $order_val = self::instructionFileOrderGetMax($a_ass_id);
                    $order = $order_val + 10;
                } else {
                    $order = $a_order_nr;
                }

                $id = $db->nextID('exc_ass_file_order');
                $db->manipulate("INSERT INTO exc_ass_file_order " .
                    "(id, assignment_id, filename, order_nr) VALUES (" .
                    $db->quote($id, "integer") . "," .
                    $db->quote($a_ass_id, "integer") . "," .
                    $db->quote($filename, "text") . "," .
                    $db->quote($order, "integer") .
                    ")");
            }
        }
    }

    public static function instructionFileDeleteOrder(
        int $a_ass_id,
        array $a_file
    ): void {
        global $DIC;

        $db = $DIC->database();

        //now its done by filename. We need to figure how to get the order id in the confirmdelete method
        foreach ($a_file as $v) {
            $db->manipulate(
                "DELETE FROM exc_ass_file_order " .
                //"WHERE id = " . $ilDB->quote((int)$k, "integer") .
                "WHERE filename = " . $db->quote($v, "string") .
                " AND assignment_id = " . $db->quote($a_ass_id, 'integer')
            );
        }
    }

    public static function renameInstructionFile(
        string $a_old_name,
        string $a_new_name,
        int $a_ass_id
    ): void {
        global $DIC;

        $db = $DIC->database();

        if ($a_ass_id) {
            $db->manipulate(
                "DELETE FROM exc_ass_file_order" .
                " WHERE assignment_id = " . $db->quote($a_ass_id, 'integer') .
                " AND filename = " . $db->quote($a_new_name, 'string')
            );

            $db->manipulate(
                "UPDATE exc_ass_file_order SET" .
                " filename = " . $db->quote($a_new_name, 'string') .
                " WHERE assignment_id = " . $db->quote($a_ass_id, 'integer') .
                " AND filename = " . $db->quote($a_old_name, 'string')
            );
        }
    }

    public static function instructionFileExistsInDb(
        string $a_filename,
        int $a_ass_id
    ): int {
        global $DIC;

        $db = $DIC->database();

        if ($a_ass_id) {
            $result = $db->query(
                "SELECT id FROM exc_ass_file_order" .
                " WHERE assignment_id = " . $db->quote($a_ass_id, 'integer') .
                " AND filename = " . $db->quote($a_filename, 'string')
            );

            return $db->numRows($result);
        }

        return 0;
    }

    public function fixInstructionFileOrdering(): void
    {
        $db = $this->db;

        $files = array_map(function ($v) {
            return $v["name"];
        }, $this->getFiles());

        $set = $db->query("SELECT * FROM exc_ass_file_order " .
            " WHERE assignment_id = " . $db->quote($this->getId(), "integer") .
            " ORDER BY order_nr");
        $order_nr = 10;
        $numbered_files = array();
        while ($rec = $db->fetchAssoc($set)) {
            // file exists, set correct order nr
            if (in_array($rec["filename"], $files)) {
                $db->manipulate(
                    "UPDATE exc_ass_file_order SET " .
                    " order_nr = " . $db->quote($order_nr, "integer") .
                    " WHERE assignment_id = " . $db->quote($this->getId(), "integer") .
                    " AND id = " . $db->quote($rec["id"], "integer")
                );
                $order_nr += 10;
                $numbered_files[] = $rec["filename"];
            } else {	// file does not exist, delete entry
                $db->manipulate(
                    "DELETE FROM exc_ass_file_order " .
                    " WHERE assignment_id = " . $db->quote($this->getId(), "integer") .
                    " AND id = " . $db->quote($rec["id"], "integer")
                );
            }
        }
        foreach ($files as $f) {
            if (!in_array($f, $numbered_files)) {
                self::instructionFileInsertOrder($f, $this->getId());
            }
        }
    }

    public function fileAddOrder(
        array $a_entries = array()
    ): array {
        $this->fixInstructionFileOrdering();

        $order = $this->getInstructionFilesOrder();
        foreach ($a_entries as $k => $e) {
            $a_entries[$k]["order_val"] = $order[$e["file"]]["order_nr"];
            $a_entries[$k]["order_id"] = $order[$e["file"]]["id"];
        }

        return $a_entries;
    }

    public static function instructionFileOrderGetMax(int $a_ass_id): int
    {
        global $DIC;

        $db = $DIC->database();

        //get max order number
        $result = $db->queryF(
            "SELECT max(order_nr) as max_order FROM exc_ass_file_order WHERE assignment_id = %s",
            array('integer'),
            array($db->quote($a_ass_id, 'integer'))
        );

        $order_val = 0;
        while ($row = $db->fetchAssoc($result)) {
            $order_val = (int) $row['max_order'];
        }
        return $order_val;
    }


    // Set limit minimum characters
    public function setMinCharLimit(int $a_val): void
    {
        $this->min_char_limit = $a_val;
    }

    public function getMinCharLimit(): int
    {
        return $this->min_char_limit;
    }

    // Set limit maximum characters
    public function setMaxCharLimit(int $a_val): void
    {
        $this->max_char_limit = $a_val;
    }

    public function getMaxCharLimit(): int
    {
        return $this->max_char_limit;
    }

    /**
     * Get calculated deadlines for user/team members.
     * These arrays will contain no entries, if team or user
     * has not started the assignment yet.
     * @return array[array] contains two arrays one with key "user",
     *          second with key "team", each one has
     *          member id as keys and calculated deadline as value
     */
    public function getCalculatedDeadlines(): array
    {
        $calculated_deadlines = array(
            "user" => array(),
            "team" => array()
        );

        if ($this->getRelativeDeadline() && $this->getDeadlineMode() == self::DEADLINE_RELATIVE) {
            foreach (ilExcIndividualDeadline::getStartingTimestamps($this->getId()) as $ts) {
                $type = $ts["is_team"]
                    ? "team"
                    : "user";

                $calculated_deadlines[$type][$ts["member_id"]] = array(
                    "calculated_deadline" => $ts["starting_ts"] + ($this->getRelativeDeadline() * 24 * 60 * 60)
                );
            }
        }
        return $calculated_deadlines;
    }
}
