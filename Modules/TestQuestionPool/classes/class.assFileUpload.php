<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Modules/TestQuestionPool/classes/class.assQuestion.php';
require_once './Modules/Test/classes/inc.AssessmentConstants.php';
require_once './Modules/TestQuestionPool/interfaces/interface.ilObjQuestionScoringAdjustable.php';
require_once './Modules/TestQuestionPool/interfaces/interface.ilObjFileHandlingQuestionType.php';

/**
 * Class for file upload questions
 *
 * @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @author		Björn Heyser <bheyser@databay.de>
 * @author		Maximilian Becker <mbecker@databay.de>
 *
 * @version		$Id$
 *
 * @ingroup		ModulesTestQuestionPool
 */
class assFileUpload extends assQuestion implements ilObjQuestionScoringAdjustable, ilObjFileHandlingQuestionType
{
    // hey: prevPassSolutions - support reusing selected files
    const REUSE_FILES_TBL_POSTVAR = 'reusefiles';
    const DELETE_FILES_TBL_POSTVAR = 'deletefiles';
    // hey.
    
    protected $maxsize;
    
    protected $allowedextensions;
    
    /** @var boolean Indicates whether completion by submission is enabled or not */
    protected $completion_by_submission = false;
    
    /**
     * assFileUpload constructor
     *
     * The constructor takes possible arguments an creates an instance of the assFileUpload object.
     *
     * @param string 	$title 		A title string to describe the question
     * @param string 	$comment 	A comment string to describe the question
     * @param string 	$author 	A string containing the name of the questions author
     * @param integer 	$owner 		A numerical ID to identify the owner/creator
     * @param string 	$question 	The question string of the single choice question
     *
     * @see assQuestion:__construct()
     */
    public function __construct(
        $title = "",
        $comment = "",
        $author = "",
        $owner = -1,
        $question = ""
    ) {
        parent::__construct($title, $comment, $author, $owner, $question);
    }
    
    /**
     * Returns true, if the question is complete for use
     *
     * @return boolean True, if the question is complete for use, otherwise false
     */
    public function isComplete()
    {
        if (
            strlen($this->title)
            && ($this->author)
            && ($this->question)
            && ($this->getMaximumPoints() >= 0)
            && is_numeric($this->getMaximumPoints())) {
            return true;
        }
        return false;
    }

    /**
     * Saves a assFileUpload object to a database
     */
    public function saveToDb($original_id = "")
    {
        $this->saveQuestionDataToDb($original_id);
        $this->saveAdditionalQuestionDataToDb();
        parent::saveToDb();
    }

    public function saveAdditionalQuestionDataToDb()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $ilDB->manipulateF(
            "DELETE FROM " . $this->getAdditionalTableName() . " WHERE question_fi = %s",
            array( "integer" ),
            array( $this->getId() )
        );
        $ilDB->manipulateF(
            "INSERT INTO " . $this->getAdditionalTableName(
                                                                                             ) . " (question_fi, maxsize, allowedextensions, compl_by_submission) VALUES (%s, %s, %s, %s)",
            array( "integer", "float", "text", "integer" ),
            array(
                                $this->getId(),
                                (strlen($this->getMaxSize())) ? $this->getMaxSize() : null,
                                (strlen($this->getAllowedExtensions())) ? $this->getAllowedExtensions() : null,
                                (int) $this->isCompletionBySubmissionEnabled()
                            )
        );
    }

    /**
     * Loads a assFileUpload object from a database
     *
     * @param integer $question_id A unique key which defines the question in the database
     */
    public function loadFromDb($question_id)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $result = $ilDB->queryF(
            "SELECT qpl_questions.*, " . $this->getAdditionalTableName() . ".* FROM qpl_questions LEFT JOIN " . $this->getAdditionalTableName() . " ON " . $this->getAdditionalTableName() . ".question_fi = qpl_questions.question_id WHERE qpl_questions.question_id = %s",
            array("integer"),
            array($question_id)
        );
        if ($result->numRows() == 1) {
            $data = $ilDB->fetchAssoc($result);
            $this->setId($question_id);
            $this->setTitle($data["title"]);
            $this->setComment($data["description"]);
            $this->setNrOfTries($data['nr_of_tries']);
            $this->setSuggestedSolution($data["solution_hint"]);
            $this->setOriginalId($data["original_id"]);
            $this->setObjId($data["obj_fi"]);
            $this->setAuthor($data["author"]);
            $this->setOwner($data["owner"]);
            $this->setPoints($data["points"]);

            include_once("./Services/RTE/classes/class.ilRTE.php");
            $this->setQuestion(ilRTE::_replaceMediaObjectImageSrc($data["question_text"], 1));
            $this->setEstimatedWorkingTime(substr($data["working_time"], 0, 2), substr($data["working_time"], 3, 2), substr($data["working_time"], 6, 2));
            $this->setMaxSize($data["maxsize"]);
            $this->setAllowedExtensions($data["allowedextensions"]);
            $this->setCompletionBySubmission($data['compl_by_submission'] == 1 ? true : false);
            
            try {
                $this->setLifecycle(ilAssQuestionLifecycle::getInstance($data['lifecycle']));
            } catch (ilTestQuestionPoolInvalidArgumentException $e) {
                $this->setLifecycle(ilAssQuestionLifecycle::getDraftInstance());
            }
            
            try {
                $this->setAdditionalContentEditingMode($data['add_cont_edit_mode']);
            } catch (ilTestQuestionPoolException $e) {
            }
        }
        parent::loadFromDb($question_id);
    }

    /**
    * Duplicates an assFileUpload
    */
    public function duplicate($for_test = true, $title = "", $author = "", $owner = "", $testObjId = null)
    {
        if ($this->id <= 0) {
            // The question has not been saved. It cannot be duplicated
            return;
        }
        // duplicate the question in database
        $this_id = $this->getId();
        $thisObjId = $this->getObjId();
        
        $clone = $this;
        include_once("./Modules/TestQuestionPool/classes/class.assQuestion.php");
        $original_id = assQuestion::_getOriginalId($this->id);
        $clone->id = -1;
        
        if ((int) $testObjId > 0) {
            $clone->setObjId($testObjId);
        }
        
        if ($title) {
            $clone->setTitle($title);
        }

        if ($author) {
            $clone->setAuthor($author);
        }
        if ($owner) {
            $clone->setOwner($owner);
        }

        if ($for_test) {
            $clone->saveToDb($original_id);
        } else {
            $clone->saveToDb();
        }

        // copy question page content
        $clone->copyPageOfQuestion($this_id);
        // copy XHTML media objects
        $clone->copyXHTMLMediaObjectsOfQuestion($this_id);

        $clone->onDuplicate($thisObjId, $this_id, $clone->getObjId(), $clone->getId());
        
        return $clone->id;
    }

    /**
    * Copies an assFileUpload object
    */
    public function copyObject($target_questionpool_id, $title = "")
    {
        if ($this->id <= 0) {
            // The question has not been saved. It cannot be duplicated
            return;
        }
        // duplicate the question in database
        $clone = $this;
        include_once("./Modules/TestQuestionPool/classes/class.assQuestion.php");
        $original_id = assQuestion::_getOriginalId($this->id);
        $clone->id = -1;
        $source_questionpool_id = $this->getObjId();
        $clone->setObjId($target_questionpool_id);
        if ($title) {
            $clone->setTitle($title);
        }
        $clone->saveToDb();

        // copy question page content
        $clone->copyPageOfQuestion($original_id);
        // copy XHTML media objects
        $clone->copyXHTMLMediaObjectsOfQuestion($original_id);

        $clone->onCopy($source_questionpool_id, $original_id, $clone->getObjId(), $clone->getId());

        return $clone->id;
    }

    public function createNewOriginalFromThisDuplicate($targetParentId, $targetQuestionTitle = "")
    {
        if ($this->id <= 0) {
            // The question has not been saved. It cannot be duplicated
            return;
        }

        include_once("./Modules/TestQuestionPool/classes/class.assQuestion.php");

        $sourceQuestionId = $this->id;
        $sourceParentId = $this->getObjId();

        // duplicate the question in database
        $clone = $this;
        $clone->id = -1;

        $clone->setObjId($targetParentId);

        if ($targetQuestionTitle) {
            $clone->setTitle($targetQuestionTitle);
        }

        $clone->saveToDb();
        // copy question page content
        $clone->copyPageOfQuestion($sourceQuestionId);
        // copy XHTML media objects
        $clone->copyXHTMLMediaObjectsOfQuestion($sourceQuestionId);

        $clone->onCopy($sourceParentId, $sourceQuestionId, $clone->getObjId(), $clone->getId());

        return $clone->id;
    }

    /**
    * Returns the maximum points, a learner can reach answering the question
    *
    * @see $points
    */
    public function getMaximumPoints()
    {
        return $this->getPoints();
    }

    /**
     * Returns the points, a learner has reached answering the question.
     * The points are calculated from the given answers.
     *
     * @access public
     * @param integer $active_id
     * @param integer $pass
     * @param boolean $returndetails (deprecated !!)
     * @return integer/array $points/$details (array $details is deprecated !!)
     */
    public function calculateReachedPoints($active_id, $pass = null, $authorizedSolution = true, $returndetails = false)
    {
        if ($returndetails) {
            throw new ilTestException('return details not implemented for ' . __METHOD__);
        }
        
        if ($this->isCompletionBySubmissionEnabled()) {
            if (is_null($pass)) {
                $pass = $this->getSolutionMaxPass($active_id);
            }
            
            global $DIC;
            
            $result = $this->getCurrentSolutionResultSet($active_id, $pass, $authorizedSolution);
            
            while ($data = $DIC->database()->fetchAssoc($result)) {
                if ($this->isDummySolutionRecord($data)) {
                    continue;
                }
                
                return $this->getPoints();
            }
        }
        
        return 0;
    }
    
    protected function calculateReachedPointsForSolution($userSolution)
    {
        if ($this->isCompletionBySubmissionEnabled() && count($userSolution)) {
            return $this->getPoints();
        }
        
        return 0;
    }
    
    /**
    * Check file upload
    *
    * @return	boolean Input ok, true/false
    */
    public function checkUpload()
    {
        $this->lng->loadLanguageModule("form");
        // remove trailing '/'
        $_FILES["upload"]["name"] = rtrim($_FILES["upload"]["name"], '/');

        $filename = $_FILES["upload"]["name"];
        $filename_arr = pathinfo($_FILES["upload"]["name"]);
        $suffix = $filename_arr["extension"];
        $mimetype = $_FILES["upload"]["type"];
        $size_bytes = $_FILES["upload"]["size"];
        $temp_name = $_FILES["upload"]["tmp_name"];
        $error = $_FILES["upload"]["error"];
        
        if ($size_bytes > $this->getMaxFilesizeInBytes()) {
            ilUtil::sendFailure($this->lng->txt("form_msg_file_size_exceeds"), true);
            return false;
        }

        // error handling
        if ($error > 0) {
            switch ($error) {
                case UPLOAD_ERR_INI_SIZE:
                    ilUtil::sendFailure($this->lng->txt("form_msg_file_size_exceeds"), true);
                    return false;
                    break;
                     
                case UPLOAD_ERR_FORM_SIZE:
                    ilUtil::sendFailure($this->lng->txt("form_msg_file_size_exceeds"), true);
                    return false;
                    break;
    
                case UPLOAD_ERR_PARTIAL:
                    ilUtil::sendFailure($this->lng->txt("form_msg_file_partially_uploaded"), true);
                    return false;
                    break;
    
                case UPLOAD_ERR_NO_FILE:
                    ilUtil::sendFailure($this->lng->txt("form_msg_file_no_upload"), true);
                    return false;
                    break;
     
                case UPLOAD_ERR_NO_TMP_DIR:
                    ilUtil::sendFailure($this->lng->txt("form_msg_file_missing_tmp_dir"), true);
                    return false;
                    break;
                     
                case UPLOAD_ERR_CANT_WRITE:
                    ilUtil::sendFailure($this->lng->txt("form_msg_file_cannot_write_to_disk"), true);
                    return false;
                    break;
     
                case UPLOAD_ERR_EXTENSION:
                    ilUtil::sendFailure($this->lng->txt("form_msg_file_upload_stopped_ext"), true);
                    return false;
                    break;
            }
        }
        
        // check suffixes
        if (count($this->getAllowedExtensionsArray())) {
            if (!strlen($suffix)) {
                ilUtil::sendFailure($this->lng->txt("form_msg_file_missing_file_ext"), true);
                return false;
            }
            
            if (!in_array(strtolower($suffix), $this->getAllowedExtensionsArray())) {
                ilUtil::sendFailure($this->lng->txt("form_msg_file_wrong_file_type"), true);
                return false;
            }
        }
        
        // virus handling
        if (strlen($temp_name)) {
            $vir = ilUtil::virusHandling($temp_name, $filename);
            if ($vir[0] == false) {
                ilUtil::sendFailure($this->lng->txt("form_msg_file_virus_found") . "<br />" . $vir[1], true);
                return false;
            }
        }
        return true;
    }

    /**
    * Returns the filesystem path for file uploads
    */
    public function getFileUploadPath($test_id, $active_id, $question_id = null)
    {
        if (is_null($question_id)) {
            $question_id = $this->getId();
        }
        return CLIENT_WEB_DIR . "/assessment/tst_$test_id/$active_id/$question_id/files/";
    }

    /**
     * Returns the filesystem path for file uploads
     */
    protected function getPreviewFileUploadPath($userId)
    {
        return CLIENT_WEB_DIR . "/assessment/qst_preview/$userId/{$this->getId()}/fileuploads/";
    }

    /**
    * Returns the file upload path for web accessible files of a question
    *
    * @access public
    */
    public function getFileUploadPathWeb($test_id, $active_id, $question_id = null)
    {
        if (is_null($question_id)) {
            $question_id = $this->getId();
        }
        include_once "./Services/Utilities/classes/class.ilUtil.php";
        $webdir = ilUtil::removeTrailingPathSeparators(CLIENT_WEB_DIR) . "/assessment/tst_$test_id/$active_id/$question_id/files/";
        return str_replace(ilUtil::removeTrailingPathSeparators(ILIAS_ABSOLUTE_PATH), ilUtil::removeTrailingPathSeparators(ILIAS_HTTP_PATH), $webdir);
    }

    /**
     * Returns the filesystem path for file uploads
     */
    protected function getPreviewFileUploadPathWeb($userId)
    {
        include_once "./Services/Utilities/classes/class.ilUtil.php";
        $webdir = ilUtil::removeTrailingPathSeparators(CLIENT_WEB_DIR) . "/assessment/qst_preview/$userId/{$this->getId()}/fileuploads/";
        return str_replace(ilUtil::removeTrailingPathSeparators(ILIAS_ABSOLUTE_PATH), ilUtil::removeTrailingPathSeparators(ILIAS_HTTP_PATH), $webdir);
    }

    /**
    * Returns the uploaded files for an active user in a given pass
    *
    * @return array Results
    */
    public function getUploadedFiles($active_id, $pass = null, $authorized = true)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        
        if (is_null($pass)) {
            $pass = $this->getSolutionMaxPass($active_id);
        }
        // fau: testNav - check existing value1 because the intermediate solution will have a dummy entry
        $result = $ilDB->queryF(
            "SELECT * FROM tst_solutions WHERE active_fi = %s AND question_fi = %s AND pass = %s AND authorized = %s AND value1 IS NOT NULL ORDER BY tstamp",
            array("integer", "integer", "integer", 'integer'),
            array($active_id, $this->getId(), $pass, (int) $authorized)
        );
        // fau.
        $found = array();
        
        while ($data = $ilDB->fetchAssoc($result)) {
            array_push($found, $data);
        }
        
        return $found;
    }
    
    public function getPreviewFileUploads(ilAssQuestionPreviewSession $previewSession)
    {
        return (array) $previewSession->getParticipantsSolution();
    }
    
    /**
    * Returns the web accessible uploaded files for an active user in a given pass
    *
    * @return array Results
    */
    public function getUploadedFilesForWeb($active_id, $pass)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        
        $found = $this->getUploadedFiles($active_id, $pass);
        $result = $ilDB->queryF(
            "SELECT test_fi FROM tst_active WHERE active_id = %s",
            array('integer'),
            array($active_id)
        );
        if ($result->numRows() == 1) {
            $row = $ilDB->fetchAssoc($result);
            $test_id = $row["test_fi"];
            $path = $this->getFileUploadPathWeb($test_id, $active_id);
            foreach ($found as $idx => $data) {
                $found[$idx]['webpath'] = $path;
            }
        }
        return $found;
    }

    /**
    * Delete uploaded files
    *
  * @param array Array with ID's of the file datasets
    */
    protected function deleteUploadedFiles($files, $test_id, $active_id, $authorized)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        
        $pass = null;
        $active_id = null;
        foreach ($files as $solution_id) {
            $result = $ilDB->queryF(
                "SELECT * FROM tst_solutions WHERE solution_id = %s AND authorized = %s",
                array("integer", 'integer'),
                array($solution_id, (int) $authorized)
            );
            if ($result->numRows() == 1) {
                $data = $ilDB->fetchAssoc($result);
                $pass = $data['pass'];
                $active_id = $data['active_fi'];
                @unlink($this->getFileUploadPath($test_id, $active_id) . $data['value1']);
            }
        }
        foreach ($files as $solution_id) {
            $affectedRows = $ilDB->manipulateF(
                "DELETE FROM tst_solutions WHERE solution_id = %s AND authorized = %s",
                array("integer", 'integer'),
                array($solution_id, $authorized)
            );
        }
    }

    // fau: testNav new function deleteUnusedFiles()
    /**
     * Delete all files that are neither used in an authorized or intermediate solution
     * @param int	$test_id
     * @param int	$active_id
     * @param int	$pass
     */
    protected function deleteUnusedFiles($test_id, $active_id, $pass)
    {
        // read all solutions (authorized and intermediate) from all steps
        $step = $this->getStep();
        $this->setStep(null);
        $solutions = array_merge(
            $this->getSolutionValues($active_id, $pass, true),
            $this->getSolutionValues($active_id, $pass, false)
        );
        $this->setStep($step);

        // get the used files from these solutions
        $used_files = array();
        foreach ($solutions as $solution) {
            $used_files[] = $solution['value1'];
        }

        // read the existing files for user and pass
        // delete all files that are not used in the solutions
        $uploadPath = $this->getFileUploadPath($test_id, $active_id);
        if (is_dir($uploadPath) && is_readable($uploadPath)) {
            $iter = new \RegexIterator(new \DirectoryIterator($uploadPath), '/^file_' . $active_id . '_' . $pass . '_(.*)/');
            foreach ($iter as $file) {
                /** @var $file \SplFileInfo */
                if ($file->isFile() && !in_array($file->getFilename(), $used_files)) {
                    unlink($file->getPathname());
                }
            }
        }
    }
    // fau.

    protected function deletePreviewFileUploads($userId, $userSolution, $files)
    {
        foreach ($files as $name) {
            if (isset($userSolution[$name])) {
                unset($userSolution[$name]);
                @unlink($this->getPreviewFileUploadPath($userId) . $name);
            }
        }

        return $userSolution;
    }
    
    /**
    * Return the maximum allowed file size as string
    *
  * @return string The number of bytes of the maximum allowed file size
    */
    public function getMaxFilesizeAsString()
    {
        $size = $this->getMaxFilesizeInBytes();
        if ($size < 1024) {
            $max_filesize = sprintf("%d Bytes", $size);
        } elseif ($size < 1024 * 1024) {
            $max_filesize = sprintf("%.1f KB", $size / 1024);
        } else {
            $max_filesize = sprintf("%.1f MB", $size / 1024 / 1024);
        }
        
        return $max_filesize;
    }

    /**
    * Return the maximum allowed file size in bytes
    *
  * @return integer The number of bytes of the maximum allowed file size
    */
    public function getMaxFilesizeInBytes()
    {
        if (strlen($this->getMaxSize())) {
            return $this->getMaxSize();
        } else {
            // get the value for the maximal uploadable filesize from the php.ini (if available)
            $umf = get_cfg_var("upload_max_filesize");
            // get the value for the maximal post data from the php.ini (if available)
            $pms = get_cfg_var("post_max_size");

            //convert from short-string representation to "real" bytes
            $multiplier_a = array("K" => 1024, "M" => 1024 * 1024, "G" => 1024 * 1024 * 1024);

            $umf_parts = preg_split("/(\d+)([K|G|M])/", $umf, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
            $pms_parts = preg_split("/(\d+)([K|G|M])/", $pms, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

            if (count($umf_parts) == 2) {
                $umf = $umf_parts[0] * $multiplier_a[$umf_parts[1]];
            }
            if (count($pms_parts) == 2) {
                $pms = $pms_parts[0] * $multiplier_a[$pms_parts[1]];
            }

            // use the smaller one as limit
            $max_filesize = min($umf, $pms);

            if (!$max_filesize) {
                $max_filesize = max($umf, $pms);
            }
            return $max_filesize;
        }
    }

    // hey: prevPassSolutions - refactored method to get intermediate/authorized
    //							as well as upload, delete and previous files working
    // BASED ON LAST FRED IMPLEMENTATION (@Fred: simply replace and solve unknown calls)
    /**
     * Saves the learners input of the question to the database.
     *
     * @access public
     * @param integer $active_id Active id of the user
     * @param integer $pass Test pass
     * @return boolean $status
     */
    public function saveWorkingData($active_id, $pass = null, $authorized = true)
    {
        $pass = $this->ensureCurrentTestPass($active_id, $pass);
        $test_id = $this->lookupTestId($active_id);
        
        $uploadHandlingRequired = $this->isFileUploadAvailable() && $this->checkUpload();
        
        $entered_values = false;

        $this->getProcessLocker()->executeUserSolutionUpdateLockOperation(function () use (&$entered_values, $uploadHandlingRequired, $test_id, $active_id, $pass, $authorized) {
            if ($authorized == false) {
                $this->forceExistingIntermediateSolution($active_id, $pass, true);
            }

            if ($this->isFileDeletionAction()) {
                if ($this->isFileDeletionSubmitAvailable()) {
                    foreach ($_POST[self::DELETE_FILES_TBL_POSTVAR] as $solution_id) {
                        $this->removeSolutionRecordById($solution_id);
                    }
                } else {
                    ilUtil::sendInfo($this->lng->txt('no_checkbox'), true);
                }
            } else {
                if ($this->isFileReuseHandlingRequired()) {
                    foreach ($_POST[self::REUSE_FILES_TBL_POSTVAR] as $solutionId) {
                        $solution = $this->getSolutionRecordById($solutionId);
                        
                        $this->saveCurrentSolution(
                            $active_id,
                            $pass,
                            $solution['value1'],
                            $solution['value2'],
                            false,
                            $solution['tstamp']
                        );
                    }
                }
                
                if ($uploadHandlingRequired) {
                    if (!@file_exists($this->getFileUploadPath($test_id, $active_id))) {
                        ilUtil::makeDirParents($this->getFileUploadPath($test_id, $active_id));
                    }
                    
                    $solutionFileVersioningUploadTS = time();
                    $filename_arr = pathinfo($_FILES["upload"]["name"]);
                    $extension = $filename_arr["extension"];
                    $newfile = "file_" . $active_id . "_" . $pass . "_" . $solutionFileVersioningUploadTS . "." . $extension;
                    
                    include_once 'Services/Utilities/classes/class.ilFileUtils.php';
                    $dispoFilename = ilFileUtils::getValidFilename($_FILES['upload']['name']);
                    $newfile = ilFileUtils::getValidFilename($newfile);
                    
                    ilUtil::moveUploadedFile($_FILES["upload"]["tmp_name"], $_FILES["upload"]["name"], $this->getFileUploadPath($test_id, $active_id) . $newfile);
                    
                    $this->saveCurrentSolution(
                        $active_id,
                        $pass,
                        $newfile,
                        $dispoFilename,
                        false,
                        $solutionFileVersioningUploadTS
                    );

                    $entered_values = true;
                }
            }

            if ($authorized == true && $this->intermediateSolutionExists($active_id, $pass)) {
                // remove the dummy record of the intermediate solution
                $this->deleteDummySolutionRecord($active_id, $pass);

                // delete the authorized solution and make the intermediate solution authorized (keeping timestamps)
                $this->removeCurrentSolution($active_id, $pass, true);
                $this->updateCurrentSolutionsAuthorization($active_id, $pass, true, true);
            }

            $this->deleteUnusedFiles($test_id, $active_id, $pass);
        });
        
        if ($entered_values) {
            include_once("./Modules/Test/classes/class.ilObjAssessmentFolder.php");
            if (ilObjAssessmentFolder::_enabledAssessmentLogging()) {
                assQuestion::logAction($this->lng->txtlng("assessment", "log_user_entered_values", ilObjAssessmentFolder::_getLogLanguage()), $active_id, $this->getId());
            }
        } else {
            include_once("./Modules/Test/classes/class.ilObjAssessmentFolder.php");
            if (ilObjAssessmentFolder::_enabledAssessmentLogging()) {
                assQuestion::logAction($this->lng->txtlng("assessment", "log_user_not_entered_values", ilObjAssessmentFolder::_getLogLanguage()), $active_id, $this->getId());
            }
        }
        
        return true;
    }
    // hey.
    
    // fau: testNav - remove dummy value when intermediate solution is got for test display
    /**
     * Get the user solution preferring the intermediate solution
     * @param int		$active_id
     * @param int|null 	$pass
     * @return array
     */
    public function getUserSolutionPreferingIntermediate($active_id, $pass = null)
    {
        $solution = $this->getSolutionValues($active_id, $pass, false);

        if (!count($solution)) {
            $solution = $this->getSolutionValues($active_id, $pass, true);
        } else {
            $cleaned = array();
            foreach ($solution as $row) {
                if (!empty($row['value1'])) {
                    $cleaned[] = $row;
                }
            }
            $solution = $cleaned;
        }

        return $solution;
    }
    // fau.


    // fau: testNav - remove unused files if an intermediate solution is removed
    /**
     * Remove an intermediate soluton (overridden to remove unused fies)
     * @param int $active_id
     * @param int $pass
     * @return int|object
     */
    public function removeIntermediateSolution($active_id, $pass)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $result = parent::removeIntermediateSolution($active_id, $pass);

        // get the current test id
        // hey: prevPassSolutions - exract until you drop :-D
        $test_id = $this->lookupTestId($active_id);
        // hey.

        $this->deleteUnusedFiles($test_id, $active_id, $pass);

        return $result;
    }
    // fau.


    protected function savePreviewData(ilAssQuestionPreviewSession $previewSession)
    {
        $userSolution = $previewSession->getParticipantsSolution();
        
        if (!is_array($userSolution)) {
            $userSolution = array();
        }
        
        // hey: prevPassSolutions - readability spree - get a chance to understand the code
        if ($this->isFileDeletionAction()) {
            // hey.
            // hey: prevPassSolutions - readability spree - get a chance to understand the code
            if ($this->isFileDeletionSubmitAvailable()) {
                // hey.
                $userSolution = $this->deletePreviewFileUploads($previewSession->getUserId(), $userSolution, $_POST['deletefiles']);
            } else {
                ilUtil::sendInfo($this->lng->txt('no_checkbox'), true);
            }
        } else {
            // hey: prevPassSolutions - readability spree - get a chance to understand the code
            if ($this->isFileUploadAvailable()) {
                // hey.
                if ($this->checkUpload()) {
                    if (!@file_exists($this->getPreviewFileUploadPath($previewSession->getUserId()))) {
                        ilUtil::makeDirParents($this->getPreviewFileUploadPath($previewSession->getUserId()));
                    }
                    
                    $version = time();
                    $filename_arr = pathinfo($_FILES["upload"]["name"]);
                    $extension = $filename_arr["extension"];
                    $newfile = "file_" . md5($_FILES["upload"]["name"]) . "_" . $version . "." . $extension;
                    ilUtil::moveUploadedFile($_FILES["upload"]["tmp_name"], $_FILES["upload"]["name"], $this->getPreviewFileUploadPath($previewSession->getUserId()) . $newfile);

                    $userSolution[$newfile] = array(
                        'solution_id' => $newfile,
                        'value1' => $newfile,
                        'value2' => $_FILES['upload']['name'],
                        'tstamp' => $version,
                        'webpath' => $this->getPreviewFileUploadPathWeb($previewSession->getUserId())
                    );
                }
            }
        }

        $previewSession->setParticipantsSolution($userSolution);
    }
    
    /**
     * This method is called after an user submitted one or more files.
     * It should handle the setting "Completion by Submission" and, if enabled, set the status of
     * the current user.
     *
     * @param	integer
     * @param	integer
     * @access	protected
     */
    protected function handleSubmission($active_id, $pass, $obligationsAnswered, $authorized)
    {
        if (!$authorized) {
            return;
        }

        if ($this->isCompletionBySubmissionEnabled()) {
            $maxpoints = assQuestion::_getMaximumPoints($this->getId());
    
            if ($this->getUploadedFiles($active_id, $pass, $authorized)) {
                $points = $maxpoints;
            } else {
                // fau: testNav - don't set reached points if no file is available
                return;
                // fau.
            }

            assQuestion::_setReachedPoints($active_id, $this->getId(), $points, $maxpoints, $pass, 1, $obligationsAnswered);
            
            // update learning progress
            include_once 'Modules/Test/classes/class.ilObjTestAccess.php';
            include_once 'Services/Tracking/classes/class.ilLPStatusWrapper.php';
            ilLPStatusWrapper::_updateStatus(
                ilObjTest::_getObjectIDFromActiveID((int) $active_id),
                ilObjTestAccess::_getParticipantId((int) $active_id)
            );
        }
    }

    /**
    * Returns the question type of the question
    *
    * @return integer The question type of the question
    */
    public function getQuestionType()
    {
        return "assFileUpload";
    }
    
    /**
    * Returns the name of the additional question data table in the database
    *
    * @return string The additional table name
    */
    public function getAdditionalTableName()
    {
        return "qpl_qst_fileupload";
    }
    
    /**
    * Returns the name of the answer table in the database
    *
    * @return string The answer table name
    */
    public function getAnswerTableName()
    {
        return "";
    }
    
    /**
    * Deletes datasets from answers tables
    *
    * @param integer $question_id The question id which should be deleted in the answers table
    */
    public function deleteAnswers($question_id)
    {
    }

    /**
    * Collects all text in the question which could contain media objects
    * which were created with the Rich Text Editor
    */
    public function getRTETextWithMediaObjects()
    {
        $text = parent::getRTETextWithMediaObjects();
        return $text;
    }
    
    /**
     * {@inheritdoc}
     */
    public function setExportDetailsXLS($worksheet, $startrow, $active_id, $pass)
    {
        parent::setExportDetailsXLS($worksheet, $startrow, $active_id, $pass);

        $i = 1;
        $solutions = $this->getSolutionValues($active_id, $pass);
        foreach ($solutions as $solution) {
            $worksheet->setCell($startrow + $i, 0, $this->lng->txt("result"));
            $worksheet->setBold($worksheet->getColumnCoord(0) . ($startrow + $i));
            if (strlen($solution["value1"])) {
                $worksheet->setCell($startrow + $i, 1, $solution["value1"]);
                $worksheet->setCell($startrow + $i, 2, $solution["value2"]);
            }
            $i++;
        }

        return $startrow + $i + 1;
    }
    
    /**
    * Creates a question from a QTI file
    *
    * Receives parameters from a QTI parser and creates a valid ILIAS question object
    *
    * @param object $item The QTI item object
    * @param integer $questionpool_id The id of the parent questionpool
    * @param integer $tst_id The id of the parent test if the question is part of a test
    * @param object $tst_object A reference to the parent test object
    * @param integer $question_counter A reference to a question counter to count the questions of an imported question pool
    * @param array $import_mapping An array containing references to included ILIAS objects
    */
    public function fromXML(&$item, &$questionpool_id, &$tst_id, &$tst_object, &$question_counter, &$import_mapping)
    {
        include_once "./Modules/TestQuestionPool/classes/import/qti12/class.assFileUploadImport.php";
        $import = new assFileUploadImport($this);
        $import->fromXML($item, $questionpool_id, $tst_id, $tst_object, $question_counter, $import_mapping);
    }
    
    /**
    * Returns a QTI xml representation of the question and sets the internal
    * domxml variable with the DOM XML representation of the QTI xml representation
    *
    * @return string The QTI xml representation of the question
    */
    public function toXML($a_include_header = true, $a_include_binary = true, $a_shuffle = false, $test_output = false, $force_image_references = false)
    {
        include_once "./Modules/TestQuestionPool/classes/export/qti12/class.assFileUploadExport.php";
        $export = new assFileUploadExport($this);
        return $export->toXML($a_include_header, $a_include_binary, $a_shuffle, $test_output, $force_image_references);
    }

    /**
    * Returns the best solution for a given pass of a participant
    *
    * @return array An associated array containing the best solution
    */
    public function getBestSolution($active_id, $pass)
    {
        $user_solution = array();
        return $user_solution;
    }
    
    /**
    * Get max file size
    *
    * @return double Max file size
    */
    public function getMaxSize()
    {
        return $this->maxsize;
    }
    
    /**
    * Set max file size
    *
    * @param double $a_value Max file size
    */
    public function setMaxSize($a_value)
    {
        $this->maxsize = $a_value;
    }
    
    /**
    * Get allowed file extensions
    *
    * @return array Allowed file extensions
    */
    public function getAllowedExtensionsArray()
    {
        if (strlen($this->allowedextensions)) {
            return array_filter(array_map('trim', explode(",", $this->allowedextensions)));
        }
        return array();
    }
    
    /**
    * Get allowed file extensions
    *
    * @return string Allowed file extensions
    */
    public function getAllowedExtensions()
    {
        return $this->allowedextensions;
    }
    
    /**
    * Set allowed file extensions
    *
    * @param string $a_value Allowed file extensions
    */
    public function setAllowedExtensions($a_value)
    {
        $this->allowedextensions = strtolower(trim($a_value));
    }
    
    /**
    * Object getter
    */
    public function __get($value)
    {
        switch ($value) {
            case "maxsize":
                return $this->getMaxSize();
                break;
            case "allowedextensions":
                return $this->getAllowedExtensions();
                break;
            case 'completion_by_submission':
                return $this->isCompletionBySubmissionEnabled();
                break;
            default:
                return parent::__get($value);
                break;
        }
    }

    /**
    * Object setter
    */
    public function __set($key, $value)
    {
        switch ($key) {
            case "maxsize":
                $this->setMaxSize($value);
                break;
            case "allowedextensions":
                $this->setAllowedExtensions($value);
                break;
            case 'completion_by_submission':
                $this->setCompletionBySubmission($value);
                break;
            default:
                parent::__set($key, $value);
                break;
        }
    }

    /**
     * Checks if file uploads exist for a given test and the original id of the question
     *
     * @param int $test_id
     *
     * @return boolean TRUE if file uploads exist, FALSE otherwise
     */
    public function hasFileUploads($test_id)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $query = "
		SELECT tst_solutions.solution_id 
		FROM tst_solutions, tst_active, qpl_questions 
		WHERE tst_solutions.active_fi = tst_active.active_id 
		AND tst_solutions.question_fi = qpl_questions.question_id 
		AND tst_solutions.question_fi = %s AND tst_active.test_fi = %s 
		AND tst_solutions.value1 is not null";
        $result = $ilDB->queryF(
            $query,
            array("integer", "integer"),
            array($this->getId(), $test_id)
        );
        if ($result->numRows() > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Generates a ZIP file containing all file uploads for a given test and the original id of the question
     *
     * @param int $test_id
     */
    public function deliverFileUploadZIPFile($ref_id, $test_id, $test_title)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $lng = $DIC['lng'];
        
        require_once 'Modules/TestQuestionPool/classes/class.ilAssFileUploadUploadsExporter.php';
        $exporter = new ilAssFileUploadUploadsExporter($ilDB, $lng);
        
        $exporter->setRefId($ref_id);
        $exporter->setTestId($test_id);
        $exporter->setTestTitle($test_title);
        $exporter->setQuestion($this);
        
        $exporter->build();

        ilUtil::deliverFile(
            $exporter->getFinalZipFilePath(),
            $exporter->getDispoZipFileName(),
            $exporter->getZipFileMimeType(),
            false,
            true
        );
    }
    
    /**
     *
     * Checks whether completion by submission is enabled or not
     *
     * @return boolean
     * @access public
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
     * @param boolean
     * @return assFileUpload
     * @access public
     *
     */
    public function setCompletionBySubmission($bool)
    {
        $this->completion_by_submission = (bool) $bool;
        return $this;
    }
    
    /**
     * returns boolean wether the question
     * is answered during test pass or not
     *
     * (overwrites method in class assQuestion)
     *
     * @global ilDBInterface $ilDB
     * @param integer $active_id
     * @param integer $pass
     * @return boolean $answered
     */
    public function isAnswered($active_id, $pass = null)
    {
        $numExistingSolutionRecords = assQuestion::getNumExistingSolutionRecords($active_id, $pass, $this->getId());
        
        return $numExistingSolutionRecords > 0;
    }
    
    /**
     * returns boolean wether it is possible to set
     * this question type as obligatory or not
     * considering the current question configuration
     *
     * (overwrites method in class assQuestion)
     *
     * @param integer $questionId
     * @return boolean $obligationPossible
     */
    public static function isObligationPossible($questionId)
    {
        return true;
    }
    
    public function isAutosaveable()
    {
        return false;
    }

    // fau: testNav - new function getTestQuestionConfig()
    /**
     * Get the test question configuration
     * Overridden from parent to disable the form change detection
     * Otherwise just checking a file would delete it at navigation
     * @return ilTestQuestionConfig
     */
    // hey: refactored identifiers
    public function buildTestPresentationConfig()
    // hey.
    {
        // hey: refactored identifiers
        return parent::buildTestPresentationConfig()
            // hey.
            ->setFormChangeDetectionEnabled(false);
    }
    // fau.
    
    // hey: prevPassSolutions - additional extractions to get a just chance to understand saveWorkingData()
    /**
     * @return bool
     */
    protected function isFileDeletionAction()
    {
        require_once 'Modules/TestQuestionPool/classes/questions/class.ilAssFileUploadFileTableDeleteButton.php';
        return $this->getQuestionAction() == ilAssFileUploadFileTableDeleteButton::ACTION;
    }
    
    /**
     * @return bool
     */
    protected function isFileDeletionSubmitAvailable()
    {
        return $this->isNonEmptyItemListPostSubmission(self::DELETE_FILES_TBL_POSTVAR);
    }
    
    /**
     * @return bool
     */
    protected function isFileReuseSubmitAvailable()
    {
        return $this->isNonEmptyItemListPostSubmission(self::REUSE_FILES_TBL_POSTVAR);
    }
    
    /**
     * @return bool
     */
    protected function isFileReuseHandlingRequired()
    {
        if (!$this->getTestPresentationConfig()->isPreviousPassSolutionReuseAllowed()) {
            return false;
        }
        
        if (!$this->isFileReuseSubmitAvailable()) {
            return false;
        }
        
        return true;
    }
    
    /**
     * @return bool
     */
    protected function isFileUploadAvailable()
    {
        if (!isset($_FILES['upload'])) {
            return false;
        }
        
        if (!isset($_FILES['upload']['tmp_name'])) {
            return false;
        }
        
        return strlen($_FILES['upload']['tmp_name']) > 0;
    }
    // hey.
}
