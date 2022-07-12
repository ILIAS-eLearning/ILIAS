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
 
use ILIAS\BackgroundTasks\Implementation\Tasks\AbstractJob;
use ILIAS\BackgroundTasks\Observer;
use ILIAS\BackgroundTasks\Types\SingleType;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\StringValue;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\IntegerValue;
use ILIAS\BackgroundTasks\Types\Type;
use ILIAS\BackgroundTasks\Value;

/**
 * @author Jesús López <lopez@leifos.com>
 */
class ilExerciseManagementCollectFilesJob extends AbstractJob
{
    public const FBK_DIRECTORY = "Feedback_files";
    public const LINK_COLOR = "0,0,255";
    public const BG_COLOR = "255,255,255";
    //Column number incremented in ilExcel
    public const PARTICIPANT_LASTNAME_COLUMN = 0;
    public const PARTICIPANT_FIRSTNAME_COLUMN = 1;
    public const PARTICIPANT_LOGIN_COLUMN = 2;
    public const SUBMISSION_DATE_COLUMN = 3;
    public const FIRST_DEFAULT_SUBMIT_COLUMN = 4;
    public const FIRST_DEFAULT_REVIEW_COLUMN = 5;

    protected ilLogger $logger;
    protected string $target_directory = "";
    protected string $submissions_directory = "";
    protected ilExAssignment $assignment;
    protected int $user_id = 0;
    protected int $exercise_id = 0;
    protected int $exercise_ref_id = 0;
    protected ?string $temp_dir = null;
    protected ilLanguage $lng;
    protected string $sanitized_title = ""; //sanitized file name/sheet title
    protected ilExcel $excel;
    protected array $criteria_items = [];
    protected array $title_columns = [];
    protected array $ass_types_with_files = []; //TODO will be deprecated when use the new assignment type interface
    protected int $participant_id = 0;

    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule('exc');
        //TODO will be deprecated when use the new assignment type interface
        $this->ass_types_with_files = array(
            ilExAssignment::TYPE_UPLOAD,
            ilExAssignment::TYPE_UPLOAD_TEAM,
            ilExAssignment::TYPE_BLOG,
            ilExAssignment::TYPE_PORTFOLIO
        );
        /** @noinspection PhpUndefinedMethodInspection */
        $this->logger = $DIC->logger()->exc();
    }

    /**
     * @return \ILIAS\BackgroundTasks\Types\SingleType[]
     */
    public function getInputTypes() : array
    {
        return
            [
                new SingleType(IntegerValue::class),
                new SingleType(IntegerValue::class),
                new SingleType(IntegerValue::class),
                new SingleType(IntegerValue::class),
                new SingleType(IntegerValue::class)
            ];
    }

    public function getOutputType() : Type
    {
        return new SingleType(StringValue::class);
    }

    public function isStateless() : bool
    {
        return true;
    }

    /**
     * run the job
     * @throws \ILIAS\BackgroundTasks\Exceptions\InvalidArgumentException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws ilDatabaseException
     * @throws ilExerciseException
     * @throws ilObjectNotFoundException
     */
    public function run(
        array $input,
        Observer $observer
    ) : Value {
        $this->exercise_id = $input[0]->getValue();
        $this->exercise_ref_id = $input[1]->getValue();
        $assignment_id = $input[2]->getValue();
        $participant_id = $input[3]->getValue();
        $this->user_id = $input[4]->getValue();
        $final_directory = "";

        //if we have assignment
        if ($assignment_id > 0) {
            $this->collectAssignmentData($assignment_id);
            $final_directory = $this->target_directory;
        }

        if ($participant_id > 0) {
            $this->participant_id = $participant_id;
            $assignments = ilExAssignment::getInstancesByExercise($this->exercise_id);
            foreach ($assignments as $assignment) {
                $this->collectAssignmentData($assignment->getId());
            }
            $final_directory = $this->temp_dir . DIRECTORY_SEPARATOR . ilExSubmission::getDirectoryNameFromUserData($participant_id);
        }

        $out = new StringValue();
        $out->setValue($final_directory);
        return $out;
    }

    /**
     * Copy a file in the Feedback_files directory
     * TODO use the new filesystem.
     */
    public function copyFileToSubDirectory(string $a_directory, string $a_file) : void
    {
        $dir = $this->target_directory . "/" . $a_directory;

        if (!is_dir($dir)) {
            ilFileUtils::makeDirParents($dir);
        }

        copy($a_file, $dir . "/" . basename($a_file));

        /*global $DIC;
        $fs = $DIC->filesystem();

        $fs->storage()->copy($a_file, $this->temp_dir."/".basename($a_file));*/
    }

    public function getExpectedTimeOfTaskInSeconds() : int
    {
        return 30;
    }

    /**
     * Set the Excel column titles.
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    protected function addColumnTitles() : void
    {
        $col = 0;
        foreach ($this->title_columns as $title) {
            $this->excel->setCell(1, $col, $title);
            $col++;
        }
    }

    /**
     * @todo refactor to new file system access
     * Create unique temp directory
     */
    protected function createUniqueTempDirectory() : void
    {
        $this->temp_dir = ilFileUtils::ilTempnam();
        ilFileUtils::makeDirParents($this->temp_dir);
    }

    /**
     * Create the directory with the assignment title.
     */
    protected function createTargetDirectory() : void
    {
        $path = $this->temp_dir . DIRECTORY_SEPARATOR;
        if ($this->participant_id > 0) {
            $user_dir = ilExSubmission::getDirectoryNameFromUserData($this->participant_id);
            $path .= $user_dir . DIRECTORY_SEPARATOR;
        }
        $this->target_directory = $path . $this->sanitized_title;
        ilFileUtils::makeDirParents($this->target_directory);
    }

    /**
     * Create the directory with the assignment title.
     */
    protected function createSubmissionsDirectory() : void
    {
        $this->logger->dump("lang key => " . $this->lng->getLangKey());
        $this->submissions_directory = $this->target_directory . DIRECTORY_SEPARATOR . $this->lng->txt("exc_ass_submission_zip");
        ilFileUtils::createDirectory($this->submissions_directory);
    }

    /**
     * Store the zip file which contains all submission files in the target directory.
     * TODO -> put the reference of the original code.
     * Possible TODO -> extract this to another BT job.
     * @throws ilDatabaseException
     * @throws ilExerciseException
     * @throws ilObjectNotFoundException
     */
    public function collectSubmissionFiles() : void
    {
        $members = array();

        $exercise = new ilObjExercise($this->exercise_id, false);

        if ($this->participant_id > 0) {
            $exc_members_id = array($this->participant_id);
        } else {
            $exc_members_id = $exercise->members_obj->getMembers();
        }

        $filter = new ilExerciseMembersFilter($this->exercise_ref_id, $exc_members_id, $this->user_id);
        $exc_members_id = $filter->filterParticipantsByAccess();

        foreach ($exc_members_id as $member_id) {
            $submission = new ilExSubmission($this->assignment, $member_id);
            $submission->updateTutorDownloadTime();

            // get member object (ilObjUser)
            if (ilObject::_exists($member_id)) {
                // adding file metadata
                foreach ($submission->getFiles() as $file) {
                    $members[$file["user_id"]]["files"][$file["returned_id"]] = $file;
                }

                /** @var $tmp_obj ilObjUser */
                $tmp_obj = ilObjectFactory::getInstanceByObjId($member_id);
                $members[$member_id]["name"] = $tmp_obj->getFirstname() . " " . $tmp_obj->getLastname();
                unset($tmp_obj);
            }
        }
        ilExSubmission::downloadAllAssignmentFiles($this->assignment, $members, $this->submissions_directory);
    }

    protected function isExcelNeeded(int $a_ass_type, bool $a_has_fbk) : bool
    {
        if ($a_ass_type == ilExAssignment::TYPE_TEXT) {
            return true;
        } elseif ($a_has_fbk && $a_ass_type != ilExAssignment::TYPE_UPLOAD_TEAM) {
            return true;
        }
        return false;
    }

    /**
     * Add criteria data to the excel.
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    protected function addCriteriaToExcel(
        int $feedback_giver,
        int $participant_id,
        int $row,
        int $col
    ) : void {
        $submission = new ilExSubmission($this->assignment, $participant_id);

        //Possible TODO: This getPeerReviewValues doesn't return always the same array structure then the client classes have
        //to deal with this. Use only one data structure will avoid this extra work.
        //values can be [19] => "blablablab" or ["text"] => "blablabla"
        $values = $submission->getPeerReview()->getPeerReviewValues($feedback_giver, $participant_id);

        foreach ($this->criteria_items as $item) {
            $col++;

            //Criteria without catalog doesn't have ID nor TITLE. The criteria instance is given via "type" ilExcCriteria::getInstanceByType
            $crit_id = $item->getId();
            $crit_type = $item->getType();
            $crit_title = $item->getTitle();
            if ($crit_title == "") {
                $crit_title = $item->getTranslatedType();
            }

            if (!in_array($crit_title, $this->title_columns)) {
                $this->title_columns[] = $crit_title;
            }
            switch ($crit_type) {
                case 'bool':
                    if ($values[$crit_id] == 1) {
                        $this->excel->setCell($row, $col, $this->lng->txt("yes"));
                    } elseif ($values[$crit_id] == -1) {
                        $this->excel->setCell($row, $col, $this->lng->txt("no"));
                    }
                    break;
                case 'rating':
                    /*
                     * Get the rating data from the DB in the current less expensive way.
                     * assignment_id -> used in il_rating.obj_id
                     * object type as string ->  used in il_rating.obj_type
                     * participant id -> il_rating.sub_obj_id
                     * "peer_" + criteria_id -> il_rating.sub_obj_type (peer or e.g. peer_12)
                     * peer id -> il_rating.user_id
                     */
                    // Possible TODO: refactor ilExAssignment->getPeerReviewCriteriaCatalogueItems somehow to avoid client
                    // classes to deal with ilExCriteria instances with persistence (by id) or instances on the fly (by type)
                    $sub_obj_type = "peer";
                    if ($crit_id) {
                        $sub_obj_type .= "_" . $crit_id;
                    }
                    $rating = ilRating::getRatingForUserAndObject(
                        $this->assignment->getId(),
                        'ass',
                        $participant_id,
                        $sub_obj_type,
                        $feedback_giver
                    );
                    if ($rating_int = round((int) $rating)) {
                        $this->excel->setCell($row, $col, $rating_int);
                    }
                    break;
                case 'text':
                    //again another check for criteria id (if instantiated via type)
                    if ($crit_id) {
                        $this->excel->setCell($row, $col, $values[$crit_id]);
                    } else {
                        $this->excel->setCell($row, $col, $values['text']);
                    }
                    break;
                case 'file':
                    if ($crit_id) {
                        /** @var $crit_file_obj ilExcCriteriaFile */
                        $crit_file_obj = ilExcCriteriaFile::getInstanceById($crit_id);
                    } else {
                        $crit_file_obj = ilExcCriteriaFile::getInstanceByType($crit_type);
                    }
                    $crit_file_obj->setPeerReviewContext($this->assignment, $feedback_giver, $participant_id);
                    $files = $crit_file_obj->getFiles();

                    $extra_crit_column = 0;
                    foreach ($files as $file) {
                        if ($extra_crit_column !== 0) {
                            $this->title_columns[] = $crit_title . "_" . $extra_crit_column;
                        }
                        $extra_crit_column++;
                        $dir = $this->getFeedbackDirectory($participant_id, $feedback_giver);
                        $this->copyFileToSubDirectory($dir, $file);
                        $this->excel->setCell($row, $col, "./" . $dir . DIRECTORY_SEPARATOR . basename($file));
                        $this->excel->addLink($row, $col, './' . $dir . DIRECTORY_SEPARATOR . basename($file));
                        $this->excel->setColors($this->excel->getCoordByColumnAndRow($col, $row), self::BG_COLOR, self::LINK_COLOR);
                    }
                    break;
            }
        }
    }

    /**
     * see also bug https://mantis.ilias.de/view.php?id=30999
     */
    protected function getFeedbackDirectory(int $participant_id, int $feedback_giver) : string
    {
        $dir = self::FBK_DIRECTORY . DIRECTORY_SEPARATOR .
            "to_" . ilExSubmission::getDirectoryNameFromUserData($participant_id) . DIRECTORY_SEPARATOR .
            "from_" . ilExSubmission::getDirectoryNameFromUserData($feedback_giver);
        return $dir;
    }

    /**
     * Get the number of max amount of files submitted by a single user in the assignment.
     * Used to add columns to the excel.
     */
    public function getExtraColumnsForSubmissionFiles(int $a_obj_id, int $a_ass_id) : int
    {
        global $DIC;
        $ilDB = $DIC->database();

        $and = "";
        if ($this->participant_id > 0) {
            $and = " AND user_id = " . $this->participant_id;
        }

        $query = "SELECT MAX(max_num) AS max" .
            " FROM (SELECT COUNT(user_id) AS max_num FROM exc_returned" .
            " WHERE obj_id=" . $a_obj_id . ". AND ass_id=" . $a_ass_id . $and . " AND mimetype IS NOT NULL" .
            " GROUP BY user_id) AS COUNTS";

        $set = $ilDB->query($query);
        $row = $ilDB->fetchAssoc($set);
        return (int) $row['max'];
    }

    // Mapping the links to use them on the excel.
    public function addLink(
        int $a_row,
        int $a_col,
        array $a_submission_file
    ) : void {
        $user_id = $a_submission_file['user_id'];
        $targetdir = ilExSubmission::getDirectoryNameFromUserData($user_id);

        $filepath = './' . $this->lng->txt("exc_ass_submission_zip") . DIRECTORY_SEPARATOR . $targetdir . DIRECTORY_SEPARATOR;
        switch ($this->assignment->getType()) {
            case ilExAssignment::TYPE_UPLOAD:
                $filepath .= $a_submission_file['filetitle'];
                break;

            case ilExAssignment::TYPE_BLOG:
                $wsp_tree = new ilWorkspaceTree($user_id);
                // #12939
                if (!$wsp_tree->getRootId()) {
                    $wsp_tree->createTreeForUser($user_id);
                }
                $node = $wsp_tree->getNodeData((int) $a_submission_file['filetitle']);
                $filepath .= "blog_" . $node['obj_id'] . DIRECTORY_SEPARATOR . "index.html";
                break;

            case ilExAssignment::TYPE_PORTFOLIO:
                $filepath .= "prt_" . $a_submission_file['filetitle'] . DIRECTORY_SEPARATOR . "index.html";
                break;

            default:
                $filepath = "";
        }
        $this->excel->addLink($a_row, $a_col, $filepath);
    }

    /**
     * write assignment data to excel file
     * @todo Refactoring needed, long method...
     * @param int $assignment_id
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws ilDatabaseException
     * @throws ilExerciseException
     * @throws ilObjectNotFoundException
     */
    protected function collectAssignmentData(int $assignment_id) : void
    {
        $ass_has_feedback = false;
        $ass_has_criteria = false;

        //assignment object
        $this->assignment = new ilExAssignment($assignment_id);
        $assignment_type = $this->assignment->getType();

        //Sanitized title for excel file and target directory.
        $this->sanitized_title = ilFileUtils::getASCIIFilename($this->assignment->getTitle());

        // directories
        if (!isset($this->temp_dir)) {
            $this->createUniqueTempDirectory();
        }
        $this->createTargetDirectory();

        //Collect submission files if needed by assignment type.
        if (in_array($assignment_type, $this->ass_types_with_files)) {
            $this->createSubmissionsDirectory();
            $this->collectSubmissionFiles();
        }

        $first_excel_column_for_review = 0;
        $col = 0;
        $peer_review = null;
        if ($this->assignment->getPeerReview()) {
            $ass_has_feedback = true;
            //obj to get the reviews in the foreach below.
            $peer_review = new ilExPeerReview($this->assignment);
            //default start column for revisions.
            $first_excel_column_for_review = self::FIRST_DEFAULT_REVIEW_COLUMN;
        }

        if ($this->isExcelNeeded($assignment_type, $ass_has_feedback)) {
            // PhpSpreadsheet object
            $this->excel = new ilExcel();

            //Excel sheet title
            $this->excel->addSheet($this->sanitized_title);

            //add common excel Columns
            #25585
            $this->title_columns = array(
                $this->lng->txt('lastname'),
                $this->lng->txt('firstname'),
                $this->lng->txt('login'),
                $this->lng->txt('exc_last_submission')
            );
            switch ($assignment_type) {
                case ilExAssignment::TYPE_TEXT:
                    $this->title_columns[] = $this->lng->txt("exc_submission_text");
                    break;
                case ilExAssignment::TYPE_UPLOAD:
                    $num_columns_submission = $this->getExtraColumnsForSubmissionFiles($this->exercise_id, $assignment_id);
                    if ($num_columns_submission > 1) {
                        for ($i = 1; $i <= $num_columns_submission; $i++) {
                            $this->title_columns[] = $this->lng->txt("exc_submission_file") . " " . $i;
                        }
                    } else {
                        $this->title_columns[] = $this->lng->txt("exc_submission_file");
                    }

                    $first_excel_column_for_review += $num_columns_submission - 1;
                    break;
                default:
                    $this->title_columns[] = $this->lng->txt("exc_submission");
                    break;
            }
            if ($ass_has_feedback) {
                $this->title_columns[] = $this->lng->txt("exc_peer_review_giver");
                $this->title_columns[] = $this->lng->txt('exc_last_submission');
            }

            //criteria
            //Notice:getPeerReviewCriteriaCatalogueItems can return just an empty instance without data.
            if ($this->criteria_items = $this->assignment->getPeerReviewCriteriaCatalogueItems()) {
                $ass_has_criteria = true;
            }

            if ($this->participant_id > 0) {
                $participants = array($this->participant_id);
            } else {
                $participants = $this->getAssignmentMembersIds();
            }

            $filter = new ilExerciseMembersFilter($this->exercise_ref_id, $participants, $this->user_id);
            $participants = $filter->filterParticipantsByAccess();

            $row = 2;
            // Fill the excel
            foreach ($participants as $participant_id) {
                $submission = new ilExSubmission($this->assignment, $participant_id);
                $submission_files = $submission->getFiles();

                if ($submission_files !== []) {
                    $participant_name = ilObjUser::_lookupName($participant_id);
                    $this->excel->setCell($row, self::PARTICIPANT_LASTNAME_COLUMN, $participant_name['lastname']);
                    $this->excel->setCell($row, self::PARTICIPANT_FIRSTNAME_COLUMN, $participant_name['firstname']);
                    $this->excel->setCell($row, self::PARTICIPANT_LOGIN_COLUMN, $participant_name['login']);

                    //Get the submission Text
                    if (!in_array($assignment_type, $this->ass_types_with_files)) {
                        foreach ($submission_files as $submission_file) {
                            $this->excel->setCell($row, self::SUBMISSION_DATE_COLUMN, $submission_file['timestamp']);
                            $this->excel->setCell($row, self::FIRST_DEFAULT_SUBMIT_COLUMN, $submission_file['atext']);
                        }
                    } else {
                        $col = self::FIRST_DEFAULT_SUBMIT_COLUMN;
                        foreach ($submission_files as $submission_file) {
                            $this->excel->setCell($row, self::SUBMISSION_DATE_COLUMN, $submission_file['timestamp']);

                            if ($assignment_type == ilExAssignment::TYPE_PORTFOLIO || $assignment_type == ilExAssignment::TYPE_BLOG) {
                                $this->excel->setCell($row, $col, $this->lng->txt("open"));
                            } else {
                                $this->excel->setCell($row, $col, $submission_file['filetitle']);
                            }
                            $this->excel->setColors($this->excel->getCoordByColumnAndRow($col, $row), self::BG_COLOR, self::LINK_COLOR);
                            $this->addLink($row, $col, $submission_file);
                            $col++; //does not affect blogs and portfolios.
                        }
                    }

                    if ($ass_has_feedback) {
                        if ($col < $first_excel_column_for_review) {
                            $col = $first_excel_column_for_review;
                        }
                        $reviews = [];
                        if ($peer_review !== null) {
                            $reviews = $peer_review->getPeerReviewsByPeerId($participant_id);
                        }

                        //extra lines
                        $current_review_row = 0;
                        foreach ($reviews as $review) {
                            //not all reviews are done, we check it via date of review.
                            if ($review['tstamp']) {
                                $current_review_row++;
                                if ($current_review_row > 1) {
                                    for ($i = 0; $i < $first_excel_column_for_review; $i++) {
                                        $cell_to_copy = $this->excel->getCell($row, $i);
                                        $this->excel->setCell($row + 1, $i, $cell_to_copy);
                                        if ($i >= self::FIRST_DEFAULT_SUBMIT_COLUMN) {
                                            $this->excel->setColors($this->excel->getCoordByColumnAndRow($i, $row + 1), self::BG_COLOR, self::LINK_COLOR);
                                        }
                                    }
                                    ++$row;
                                }
                                
                                $feedback_giver = $review['giver_id']; // user who made the review.

                                $feedback_giver_name = ilObjUser::_lookupName($feedback_giver);

                                $this->excel->setCell(
                                    $row,
                                    $col,
                                    $feedback_giver_name['lastname'] . ", " . $feedback_giver_name['firstname'] . " [" . $feedback_giver_name['login'] . "]"
                                );

                                $this->excel->setCell($row, $col + 1, $review['tstamp']);

                                if ($ass_has_criteria) {
                                    $this->addCriteriaToExcel($feedback_giver, $participant_id, $row, $col + 1);
                                }
                            }
                        }
                    }

                    $row++;
                }
            }

            $this->addColumnTitles();
            $this->excel->writeToFile($this->target_directory . "/" . $this->sanitized_title);
        }
    }

    /**
     * get ONLY the members ids for this assignment
     * @return int[]
     */
    public function getAssignmentMembersIds() : array
    {
        global $DIC;

        $ilDB = $DIC->database();
        $members = array();

        $set = $ilDB->query("SELECT usr_id" .
            " FROM exc_mem_ass_status" .
            " WHERE ass_id = " . $ilDB->quote($this->assignment->getId(), "integer"));

        while ($rec = $ilDB->fetchAssoc($set)) {
            $members[] = $rec['usr_id'];
        }

        return $members;
    }
}
