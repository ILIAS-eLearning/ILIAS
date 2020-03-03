<?php
use ILIAS\BackgroundTasks\Implementation\Tasks\AbstractJob;
use ILIAS\BackgroundTasks\Value;
use ILIAS\BackgroundTasks\Observer;
use ILIAS\BackgroundTasks\Types\SingleType;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\StringValue;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\IntegerValue;

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Description of class class
 *
 * @author Jesús López <lopez@leifos.com>
 *
 */
class ilExerciseManagementCollectFilesJob extends AbstractJob
{
    /**
     * @var ilLogger
     */
    private $logger = null;
    /**
     * @var string
     */
    protected $target_directory;
    protected $submissions_directory;
    protected $assignment;
    protected $user_id;
    protected $exercise_id;
    protected $exercise_ref_id;
    protected $temp_dir;
    protected $lng;
    protected $sanitized_title; //sanitized file name/sheet title
    protected $excel; //ilExcel
    protected $criteria_items; //array
    protected $title_columns;
    protected $ass_types_with_files; //TODO will be deprecated when use the new assignment type interface
    protected $participant_id;

    const FBK_DIRECTORY = "Feedback_files";
    const LINK_COLOR = "0,0,255";
    const BG_COLOR = "255,255,255";
    //Column number incremented in ilExcel
    const PARTICIPANT_LASTNAME_COLUMN = 0;
    const PARTICIPANT_FIRSTNAME_COLUMN = 1;
    const PARTICIPANT_LOGIN_COLUMN = 2;
    const SUBMISSION_DATE_COLUMN = 3;
    const FIRST_DEFAULT_SUBMIT_COLUMN = 4;
    const FIRST_DEFAULT_REVIEW_COLUMN = 5;

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
        $this->logger = $DIC->logger()->exc();
    }

    /**
     * @return array
     */
    public function getInputTypes()
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

    /**
     * @return SingleType
     */
    public function getOutputType()
    {
        return new SingleType(StringValue::class);
    }

    public function isStateless()
    {
        return true;
    }

    /**
     * run the job
     * @param array $input
     * @param Observer $observer
     * @return StringValue
     */
    public function run(array $input, Observer $observer)
    {
        $this->exercise_id = $input[0]->getValue();
        $this->exercise_ref_id = $input[1]->getValue();
        $assignment_id = $input[2]->getValue();
        $participant_id = $input[3]->getValue();
        $this->user_id = $input[4]->getValue();

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
     * @param $a_directory string
     * @param $a_file string
     */
    public function copyFileToSubDirectory($a_directory, $a_file)
    {
        $dir = $this->target_directory . "/" . $a_directory;

        if (!is_dir($dir)) {
            ilUtil::createDirectory($dir);
        }

        copy($a_file, $dir . "/" . basename($a_file));

        /*global $DIC;
        $fs = $DIC->filesystem();

        $fs->storage()->copy($a_file, $this->temp_dir."/".basename($a_file));*/
    }

    /**
     * @inheritdoc
     */
    public function getExpectedTimeOfTaskInSeconds()
    {
        return 30;
    }

    /**
     * Set the Excel column titles.
     */
    protected function addColumnTitles()
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
    protected function createUniqueTempDirectory()
    {
        $this->temp_dir = ilUtil::ilTempnam();
        ilUtil::makeDirParents($this->temp_dir);
    }

    /**
     * Create the directory with the assignment title.
     */
    protected function createTargetDirectory()
    {
        $path = $this->temp_dir . DIRECTORY_SEPARATOR;
        if ($this->participant_id > 0) {
            $user_dir = ilExSubmission::getDirectoryNameFromUserData($this->participant_id);
            $path .= $user_dir . DIRECTORY_SEPARATOR;
        }
        $this->target_directory = $path . $this->sanitized_title;

        ilUtil::makeDirParents($this->target_directory);
    }
    /**
     * Create the directory with the assignment title.
     */
    protected function createSubmissionsDirectory()
    {
        $this->logger->debug("lang key => " . $this->lng->getLangKey());
        $this->submissions_directory = $this->target_directory . DIRECTORY_SEPARATOR . $this->lng->txt("exc_ass_submission_zip");
        ilUtil::createDirectory($this->submissions_directory);
    }

    /**
     * Store the zip file which contains all submission files in the target directory.
     * TODO -> put the reference of the original code.
     * Possible TODO -> extract this to another BT job.
     */
    public function collectSubmissionFiles()
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

                $tmp_obj =&ilObjectFactory::getInstanceByObjId($member_id);
                $members[$member_id]["name"] = $tmp_obj->getFirstname() . " " . $tmp_obj->getLastname();
                unset($tmp_obj);
            }
        }

        ilExSubmission::downloadAllAssignmentFiles($this->assignment, $members, $this->submissions_directory);
    }

    /**
     * @param $a_ass_type string
     * @param $a_has_fbk bool
     * @return bool
     */
    protected function isExcelNeeded($a_ass_type, $a_has_fbk)
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
     * @param $feedback_giver
     * @param $participant_id
     * @param $row
     * @param $col
     */
    protected function addCriteriaToExcel($feedback_giver, $participant_id, $row, $col)
    {
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
                        $crit_file_obj = ilExcCriteriaFile::getInstanceById($crit_id);
                    } else {
                        $crit_file_obj = ilExcCriteriaFile::getInstanceByType($crit_type);
                    }
                    $crit_file_obj->setPeerReviewContext($this->assignment, $feedback_giver, $participant_id);
                    $files = $crit_file_obj->getFiles();

                    $extra_crit_column = 0;
                    foreach ($files as $file) {
                        if ($extra_crit_column) {
                            $this->title_columns[] = $crit_title . "_" . $extra_crit_column;
                        }
                        $extra_crit_column++;
                        $this->copyFileToSubDirectory(self::FBK_DIRECTORY, $file);
                        $this->excel->setCell($row, $col, "./" . self::FBK_DIRECTORY . DIRECTORY_SEPARATOR . basename($file));
                        $this->excel->addLink($row, $col, './' . self::FBK_DIRECTORY . DIRECTORY_SEPARATOR . basename($file));
                        $this->excel->setColors($this->excel->getCoordByColumnAndRow($col, $row), self::BG_COLOR, self::LINK_COLOR);
                    }
                    break;
            }
        }
    }

    /**
     * Get the number of max amount of files submitted by a single user in the assignment.
     * Used to add columns to the excel.
     * @param $a_obj_id
     * @param $a_ass_id
     * @return mixed
     */
    public function getExtraColumnsForSubmissionFiles($a_obj_id, $a_ass_id)
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
        return $row['max'];
    }

    /**
     * Mapping the links to use them on the excel.
     * @param $a_row int
     * @param $a_col int
     * @param $a_submission ilExSubmission
     * @param $a_submission_file array
     */
    public function addLink($a_row, $a_col, $a_submission_file)
    {
        $user_id = $a_submission_file['user_id'];
        $targetdir = ilExSubmission::getDirectoryNameFromUserData($user_id);

        $filepath = './' . $this->lng->txt("exc_ass_submission_zip") . DIRECTORY_SEPARATOR . $targetdir . DIRECTORY_SEPARATOR;
        switch ($this->assignment->getType()) {
            case ilExAssignment::TYPE_UPLOAD:
                $filepath .= $a_submission_file['filetitle'];
                break;

            case ilExAssignment::TYPE_BLOG:
                include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceTree.php";
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

    protected function collectAssignmentData($assignment_id)
    {
        $ass_has_feedback = false;
        $ass_has_criteria = false;

        //assignment object
        $this->assignment = new ilExAssignment($assignment_id);
        $assignment_type = $this->assignment->getType();

        //Sanitized title for excel file and target directory.
        $this->sanitized_title = ilUtil::getASCIIFilename($this->assignment->getTitle());

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

        if ($this->assignment->getPeerReview()) {
            $ass_has_feedback = true;
            //obj to get the reviews in the foreach below.
            $peer_review = new ilExPeerReview($this->assignment);
            //default start column for revisions.
            $first_excel_column_for_review = self::FIRST_DEFAULT_REVIEW_COLUMN;
        }

        /**
         * TODO refactor when 0 bugs:
         *  - extract the excel related code from this method.
         *  - incrementing/decrementing columns/rows management
         */
        if ($this->isExcelNeeded($assignment_type, $ass_has_feedback)) {
            // PhpSpreadsheet object
            include_once "./Services/Excel/classes/class.ilExcel.php";
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

                    $first_excel_column_for_review += $num_columns_submission -1;
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

            //SET THE ROW AT SECOND POSITION TO START ENTERING VALUES BELOW THE TITLE.
            $row = 2;
            // Fill the excel
            foreach ($participants as $participant_id) {
                $submission = new ilExSubmission($this->assignment, $participant_id);
                $submission_files = $submission->getFiles();

                if ($submission_files) {
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
                            $this->excel->setColors($this->excel->getCoordByColumnAndRow($col+1, $row), self::BG_COLOR, self::LINK_COLOR);
                            $this->addLink($row, $col, $submission_file);
                            $col++; //does not affect blogs and portfolios.
                        }
                    }

                    if ($ass_has_feedback) {
                        if ($col < $first_excel_column_for_review) {
                            $col = $first_excel_column_for_review;
                        }
                        $reviews = $peer_review->getPeerReviewsByPeerId($participant_id);

                        //extra lines
                        $current_review_row = 0;
                        foreach ($reviews as $review) {
                            //not all reviews are done, we check it via date of review.
                            if ($review['tstamp']) {
                                $current_review_row++;
                                if ($current_review_row > 1) {
                                    for ($i = 0; $i < $first_excel_column_for_review; $i++) {
                                        $cell_to_copy = $this->excel->getCell($row, $i);
                                        // $i-1 because ilExcel setCell increments the column by 1
                                        $this->excel->setCell($row +1, $i-1, $cell_to_copy);
                                        if ($i > self::FIRST_DEFAULT_SUBMIT_COLUMN) {
                                            $this->excel->setColors($this->excel->getCoordByColumnAndRow($i, $row+1), self::BG_COLOR, self::LINK_COLOR);
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

                                $this->excel->setCell($row, $col+1, $review['tstamp']);

                                if ($ass_has_criteria) {
                                    $this->addCriteriaToExcel($feedback_giver, $participant_id, $row, $col+1);
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

    // get ONLY the members ids for this assignment
    public function getAssignmentMembersIds()
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
