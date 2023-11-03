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

use ILIAS\FileDelivery\Delivery\Disposition;

require_once './components/ILIAS/Test/classes/inc.AssessmentConstants.php';

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
    public const REUSE_FILES_TBL_POSTVAR = 'reusefiles';
    public const DELETE_FILES_TBL_POSTVAR = 'deletefiles';
    // hey.

    protected const HAS_SPECIFIC_FEEDBACK = false;
    private \ILIAS\ResourceStorage\Services $irss;
    private \ILIAS\FileDelivery\Services $file_delivery;
    private \ILIAS\FileUpload\FileUpload $file_upload;

    protected ?int $maxsize = null;

    protected string $allowedextensions = '';

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
        $title = '',
        $comment = '',
        $author = '',
        $owner = -1,
        $question = ''
    ) {
        parent::__construct($title, $comment, $author, $owner, $question);
        global $DIC;
        $this->irss = $DIC->resourceStorage();
        $this->file_delivery = $DIC->fileDelivery();
        $this->file_upload = $DIC->upload();
    }

    /**
     * Returns true, if the question is complete for use
     *
     * @return boolean True, if the question is complete for use, otherwise false
     */
    public function isComplete(): bool
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
    public function saveToDb($original_id = ''): void
    {
        if ($original_id == '') {
            $this->saveQuestionDataToDb();
        } else {
            $this->saveQuestionDataToDb($original_id);
        }

        $this->saveAdditionalQuestionDataToDb();
        parent::saveToDb();
    }

    public function saveAdditionalQuestionDataToDb()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $ilDB->manipulateF(
            'DELETE FROM ' . $this->getAdditionalTableName() . ' WHERE question_fi = %s',
            ['integer'],
            [$this->getId()]
        );
        $ilDB->manipulateF(
            'INSERT INTO ' . $this->getAdditionalTableName(
            ) . ' (question_fi, maxsize, allowedextensions, compl_by_submission) VALUES (%s, %s, %s, %s)',
            ['integer', 'float', 'text', 'integer' ],
            [
                $this->getId(),
                $this->getMaxSize(),
                (strlen($this->getAllowedExtensions())) ? $this->getAllowedExtensions() : null,
                (int) $this->isCompletionBySubmissionEnabled()
            ]
        );
    }

    /**
     * Loads a assFileUpload object from a database
     *
     * @param integer $question_id A unique key which defines the question in the database
     */
    public function loadFromDb($question_id): void
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $result = $ilDB->queryF(
            'SELECT qpl_questions.*, ' . $this->getAdditionalTableName()
            . '.* FROM qpl_questions LEFT JOIN ' . $this->getAdditionalTableName()
            . ' ON ' . $this->getAdditionalTableName()
            . '.question_fi = qpl_questions.question_id WHERE qpl_questions.question_id = %s',
            ['integer'],
            [$question_id]
        );
        if ($result->numRows() == 1) {
            $data = $ilDB->fetchAssoc($result);
            $this->setId($question_id);
            $this->setTitle((string) $data['title']);
            $this->setComment((string) $data['description']);
            $this->setNrOfTries($data['nr_of_tries']);
            $this->setOriginalId($data['original_id']);
            $this->setObjId($data['obj_fi']);
            $this->setAuthor($data['author']);
            $this->setOwner($data['owner']);
            $this->setPoints($data['points']);

            $this->setQuestion(ilRTE::_replaceMediaObjectImageSrc((string) $data['question_text'], 1));
            $this->setMaxSize(($data['maxsize'] ?? null) ? (int) $data['maxsize'] : null);
            $this->setAllowedExtensions($data['allowedextensions'] ?? '');
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
    public function duplicate(
        bool $for_test = true,
        string $title = '',
        string $author = '',
        string $owner = '',
        $testObjId = null
    ): int {
        if ($this->id <= 0) {
            // The question has not been saved. It cannot be duplicated
            return -1;
        }
        // duplicate the question in database
        $this_id = $this->getId();
        $thisObjId = $this->getObjId();

        $clone = $this;
        $original_id = $this->questioninfo->getOriginalId($this->id);
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
    public function copyObject($target_questionpool_id, $title = ''): int
    {
        if ($this->getId() <= 0) {
            throw new RuntimeException('The question has not been saved. It cannot be duplicated');
        }
        // duplicate the question in database
        $clone = $this;
        $original_id = $this->questioninfo->getOriginalId($this->id);
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

    public function createNewOriginalFromThisDuplicate($targetParentId, $targetQuestionTitle = ''): int
    {
        if ($this->getId() <= 0) {
            throw new RuntimeException('The question has not been saved. It cannot be duplicated');
        }

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
    public function getMaximumPoints(): float
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
    public function calculateReachedPoints($active_id, $pass = null, $authorizedSolution = true, $returndetails = false): int
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
        if ($this->isCompletionBySubmissionEnabled() &&
            is_array($userSolution) &&
            count($userSolution)) {
            return $this->getPoints();
        }

        return 0;
    }

    /**
    * Check file upload
    *
    * @return	boolean Input ok, true/false
    */
    public function checkUpload(): bool
    {
        $this->lng->loadLanguageModule('form');

        foreach (
            $this->file_upload->getResults() as $upload_result
        ) { // only one supported at the moment, but we check all
            if (!$upload_result->isOK()) {
                return false;
            }

            // check file size
            $size_bytes = $upload_result->getSize();
            if ($size_bytes > $this->getMaxFilesizeInBytes()) {
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt('form_msg_file_size_exceeds'), true);
                return false;
            }

            // check suffixes
            if (count($this->getAllowedExtensionsArray())) {
                $filename_arr = pathinfo($upload_result->getName());
                $suffix = $filename_arr['extension'];
                $mimetype = $upload_result->getMimeType();
                if ($suffix === '') {
                    $this->tpl->setOnScreenMessage('failure', $this->lng->txt('form_msg_file_missing_file_ext'), true);
                    return false;
                }

                if (!in_array(strtolower($suffix), $this->getAllowedExtensionsArray(), true)) {
                    $this->tpl->setOnScreenMessage('failure', $this->lng->txt('form_msg_file_wrong_file_type'), true);
                    return false;
                }
            }
            // virus handling already done in upload-service
        }
        return true;
    }

    /**
    * Returns the filesystem path for file uploads
    */
    public function getFileUploadPath($test_id, $active_id, $question_id = null): string
    {
        if (is_null($question_id)) {
            $question_id = $this->getId();
        }
        return CLIENT_WEB_DIR . "/assessment/tst_{$test_id}/{$active_id}/{$question_id}/files/";
    }

    /**
     * Returns the filesystem path for file uploads
     */
    protected function getPreviewFileUploadPath($userId): string
    {
        return CLIENT_WEB_DIR . "/assessment/qst_preview/{$userId}/{$this->getId()}/fileuploads/";
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
        $webdir = ilFileUtils::removeTrailingPathSeparators(CLIENT_WEB_DIR)
            . "/assessment/tst_{$test_id}/{$active_id}/{$question_id}/files/";
        return str_replace(
            ilFileUtils::removeTrailingPathSeparators(ILIAS_ABSOLUTE_PATH),
            ilFileUtils::removeTrailingPathSeparators(ILIAS_HTTP_PATH),
            $webdir
        );
    }

    /**
     * Returns the filesystem path for file uploads
     */
    protected function getPreviewFileUploadPathWeb($userId)
    {
        $webdir = ilFileUtils::removeTrailingPathSeparators(CLIENT_WEB_DIR)
            . "/assessment/qst_preview/{$userId}/{$this->getId()}/fileuploads/";
        return str_replace(
            ilFileUtils::removeTrailingPathSeparators(ILIAS_ABSOLUTE_PATH),
            ilFileUtils::removeTrailingPathSeparators(ILIAS_HTTP_PATH),
            $webdir
        );
    }

    /**
    * Returns the uploaded files for an active user in a given pass
    *
    * @return array Results
    */
    public function getUploadedFiles($active_id, $pass = null, $authorized = true): array
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        if (is_null($pass)) {
            $pass = $this->getSolutionMaxPass($active_id);
        }
        // fau: testNav - check existing value1 because the intermediate solution will have a dummy entry
        $result = $ilDB->queryF(
            'SELECT * FROM tst_solutions WHERE active_fi = %s '
            . 'AND question_fi = %s AND pass = %s AND authorized = %s '
            . 'AND value1 IS NOT NULL ORDER BY tstamp',
            ['integer', 'integer', 'integer', 'integer'],
            [$active_id, $this->getId(), $pass, (int) $authorized]
        );
        // fau.
        $found = [];

        while ($data = $ilDB->fetchAssoc($result)) {
            array_push($found, $data);
        }

        return $found;
    }

    public function getPreviewFileUploads(ilAssQuestionPreviewSession $previewSession): array
    {
        if ($previewSession->getParticipantsSolution() === false || $previewSession->getParticipantsSolution() === null) {
            return [];
        }

        return $previewSession->getParticipantsSolution();
    }

    /**
    * Returns the web accessible uploaded files for an active user in a given pass
    *
    * @return array Results
    */
    public function getUploadedFilesForWeb($active_id, $pass): array
    {
        $found = $this->getUploadedFiles($active_id, $pass);
        $result = $this->db->queryF(
            'SELECT test_fi FROM tst_active WHERE active_id = %s',
            ['integer'],
            [$active_id]
        );
        if ($result->numRows() == 1) {
            $row = $this->db->fetchAssoc($result);
            $test_id = $row['test_fi'];
            $path = $this->getFileUploadPathWeb($test_id, $active_id);
            foreach ($found as $idx => $data) {
                // depending on whether the files are already stored in the IRSS or not, the files are compiled differently here.
                // this can be removed with ILIAs 10 and switched exclusively to the IRSS variant.
                // We recommend then to revise the whole handling of files

                if ($data['value2'] === 'rid') {
                    $rid = $this->irss->manage()->find($data['value1']);
                    if($rid === null) {
                        continue;
                    }
                    $revision = $this->irss->manage()->getCurrentRevision($rid);
                    $stream = $this->irss->consume()->stream($rid)->getStream();
                    $url = $this->file_delivery->buildTokenURL(
                        $stream,
                        $revision->getTitle(),
                        Disposition::ATTACHMENT,
                        $this->current_user->getId(),
                        1
                    );

                    $path = (string) $url;
                    $found[$idx]['webpath'] = $path;
                    $found[$idx]['value2'] = $revision->getTitle();
                } else {
                    $found[$idx]['webpath'] = $path;
                }
            }
        }
        return $found;
    }

    // fau: testNav new function deleteUnusedFiles()
    /**
     * Delete all files that are neither used in an authorized or intermediate solution
     * @param int	$test_id
     * @param int	$active_id
     * @param int	$pass
     */
    protected function deleteUnusedFiles(array $rids_to_delete, $test_id, $active_id, $pass): void
    {
        // Remove Resources from IRSS
        if ($rids_to_delete !== []) {
            foreach ($rids_to_delete as $rid_to_delete) {
                $rid_to_delete = $this->irss->manage()->find($rid_to_delete);
                if ($rid_to_delete === null) {
                    continue;
                }
                $this->irss->manage()->remove(
                    $rid_to_delete,
                    new assFileUploadStakeholder()
                );
            }
        }

        // Legacy implementation for not yet migrated files

        // read all solutions (authorized and intermediate) from all steps
        $step = $this->getStep();
        $this->setStep(null);
        $solutions = array_merge(
            $this->getSolutionValues($active_id, $pass, true),
            $this->getSolutionValues($active_id, $pass, false)
        );
        $this->setStep($step);

        // get the used files from these solutions
        $used_files = [];
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
    public function getMaxFilesizeAsString(): string
    {
        $size = $this->getMaxFilesizeInBytes();
        if ($size < 1024) {
            $max_filesize = sprintf('%d Bytes', $size);
        } elseif ($size < 1024 * 1024) {
            $max_filesize = sprintf('%.1f KB', $size / 1024);
        } else {
            $max_filesize = sprintf('%.1f MB', $size / 1024 / 1024);
        }

        return $max_filesize;
    }

    public function getMaxFilesizeInBytes(): int
    {
        if ($this->getMaxSize() > 0) {
            return $this->getMaxSize();
        }

        // get the value for the maximal uploadable filesize from the php.ini (if available)
        $umf = get_cfg_var('upload_max_filesize');
        // get the value for the maximal post data from the php.ini (if available)
        $pms = get_cfg_var('post_max_size');

        //convert from short-string representation to 'real' bytes
        $multiplier_a = ['K' => 1024, 'M' => 1024 * 1024, 'G' => 1024 * 1024 * 1024];

        $umf_parts = preg_split('/(\d+)([K|G|M])/', $umf, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        $pms_parts = preg_split('/(\d+)([K|G|M])/', $pms, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        if (count($umf_parts) === 2) {
            $umf = $umf_parts[0] * $multiplier_a[$umf_parts[1]];
        }
        if (count($pms_parts) === 2) {
            $pms = $pms_parts[0] * $multiplier_a[$pms_parts[1]];
        }

        // use the smaller one as limit
        $max_filesize = min($umf, $pms);

        if (!$max_filesize) {
            $max_filesize = max($umf, $pms);
        }

        return (int) $max_filesize;
    }

    /**
     * @access public
     * @param integer $active_id Active id of the user
     * @param integer $pass Test pass
     */
    public function saveWorkingData($active_id, $pass = null, $authorized = true): bool
    {
        if ($pass === null || $pass < 0) {
            $pass = \ilObjTest::_getPass($active_id);
        }

        $test_id = $this->testParticipantInfo->lookupTestIdByActiveId($active_id);

        $upload_handling_required = $this->isFileUploadAvailable() && $this->checkUpload();
        $rid = null;

        if ($upload_handling_required) {
            // upload new file to storage
            $upload_results = $this->file_upload->getResults();
            $upload_result = end($upload_results); // only one supported at the moment
            $rid = $this->irss->manage()->upload(
                $upload_result,
                new assFileUploadStakeholder()
            );
        }

        $entered_values = false;

        // RIDS to delete
        // Unfortunately, at the moment it is not possible to delete the files from the IRSS, because the process takes
        // place within the ProcessLocker and the IRSS tables cannot be used. we have to remove them after the lock.
        // therefore we store the rids to delete in an array for later deletion.

        $rids_to_delete = $this->resolveRIDStoDelete();

        $this->getProcessLocker()->executeUserSolutionUpdateLockOperation(
            function () use (
                &$entered_values,
                $upload_handling_required,
                $test_id,
                $active_id,
                $pass,
                $authorized,
                $rid
            ) {
                if ($authorized === false) {
                    $this->forceExistingIntermediateSolution($active_id, $pass, true);
                }

                if ($this->isFileDeletionAction()) {
                    if ($this->isFileDeletionSubmitAvailable()) {
                        foreach ($_POST[self::DELETE_FILES_TBL_POSTVAR] as $solution_id) {
                            $this->removeSolutionRecordById($solution_id);
                        }
                    } else {
                        $this->tpl->setOnScreenMessage('info', $this->lng->txt('no_checkbox'), true);
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

                    if ($upload_handling_required && $rid !== null) {

                        $revision = $this->irss->manage()->getCurrentRevision($rid);

                        $this->saveCurrentSolution(
                            $active_id,
                            $pass,
                            $rid->serialize(),
                            'rid',
                            false,
                            time()
                        );

                        $entered_values = true;
                    }
                }

                if ($authorized === true && $this->intermediateSolutionExists($active_id, $pass)) {
                    // remove the dummy record of the intermediate solution
                    $this->deleteDummySolutionRecord($active_id, $pass);

                    // delete the authorized solution and make the intermediate solution authorized (keeping timestamps)
                    $this->removeCurrentSolution($active_id, $pass, true);
                    $this->updateCurrentSolutionsAuthorization($active_id, $pass, true, true);
                }

            }
        );

        $this->deleteUnusedFiles(
            $rids_to_delete,
            $test_id,
            $active_id,
            $pass
        );

        if ($entered_values) {
            if (ilObjAssessmentFolder::_enabledAssessmentLogging()) {
                assQuestion::logAction($this->lng->txtlng(
                    'assessment',
                    'log_user_entered_values',
                    ilObjAssessmentFolder::_getLogLanguage()
                ), $active_id, $this->getId());
            }
        } else {
            if (ilObjAssessmentFolder::_enabledAssessmentLogging()) {
                assQuestion::logAction($this->lng->txtlng(
                    'assessment',
                    'log_user_not_entered_values',
                    ilObjAssessmentFolder::_getLogLanguage()
                ), $active_id, $this->getId());
            }
        }

        return true;
    }

    protected function resolveRIDStoDelete(): array
    {
        $rids_to_delete = [];
        if ($this->isFileDeletionAction() && $this->isFileDeletionSubmitAvailable()) {
            $res = $this->db->query(
                "SELECT value1 FROM tst_solutions WHERE value2 = 'rid' AND " . $this->db->in(
                    'solution_id',
                    $_POST[self::DELETE_FILES_TBL_POSTVAR],
                    false,
                    'integer'
                )
            );
            while ($d = $this->db->fetchAssoc($res)) {
                $rids_to_delete[] = $d['value1'];
            }
        }
        return $rids_to_delete;
    }

    protected function removeSolutionRecordById(int $solution_id): int
    {
        return parent::removeSolutionRecordById($solution_id);
    }

    /**
     * @param int		$active_id
     * @param int|null 	$pass
     */
    public function getUserSolutionPreferingIntermediate($active_id, $pass = null): array
    {
        $solution = $this->getSolutionValues($active_id, $pass, false);

        if (!count($solution)) {
            $solution = $this->getSolutionValues($active_id, $pass, true);
        } else {
            $cleaned = [];
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

    public function removeIntermediateSolution(int $active_id, int $pass): void
    {
        parent::removeIntermediateSolution($active_id, $pass);

        $test_id = $this->testParticipantInfo->lookupTestIdByActiveId($active_id);
        if ($test_id !== -1) {
            // TODO: This can be removed with ILIAS 10
            $this->deleteUnusedFiles([], $test_id, $active_id, $pass);
        }
    }


    protected function savePreviewData(ilAssQuestionPreviewSession $previewSession): void
    {
        $userSolution = $previewSession->getParticipantsSolution();

        if (!is_array($userSolution)) {
            $userSolution = [];
        }

        // hey: prevPassSolutions - readability spree - get a chance to understand the code
        if ($this->isFileDeletionAction()) {
            // hey.
            // hey: prevPassSolutions - readability spree - get a chance to understand the code
            if ($this->isFileDeletionSubmitAvailable()) {
                // hey.
                $userSolution = $this->deletePreviewFileUploads($previewSession->getUserId(), $userSolution, $_POST['deletefiles']);
            } else {
                $this->tpl->setOnScreenMessage('info', $this->lng->txt('no_checkbox'), true);
            }
        } else {
            // hey: prevPassSolutions - readability spree - get a chance to understand the code
            if ($this->isFileUploadAvailable()) {
                // hey.
                if ($this->checkUpload()) {
                    if (!@file_exists($this->getPreviewFileUploadPath($previewSession->getUserId()))) {
                        ilFileUtils::makeDirParents($this->getPreviewFileUploadPath($previewSession->getUserId()));
                    }

                    $version = time();
                    $filename_arr = pathinfo($_FILES['upload']['name']);
                    $extension = $filename_arr['extension'];
                    $newfile = 'file_' . md5($_FILES['upload']['name']) . '_' . $version . '.' . $extension;
                    ilFileUtils::moveUploadedFile(
                        $_FILES['upload']['tmp_name'],
                        $_FILES['upload']['name'],
                        $this->getPreviewFileUploadPath($previewSession->getUserId()) . $newfile
                    );

                    $userSolution[$newfile] = [
                        'solution_id' => $newfile,
                        'value1' => $newfile,
                        'value2' => $_FILES['upload']['name'],
                        'tstamp' => $version,
                        'webpath' => $this->getPreviewFileUploadPathWeb($previewSession->getUserId())
                    ];
                }
            }
        }

        $previewSession->setParticipantsSolution($userSolution);
    }

    /**
     * This method is called after an user submitted one or more files.
     * It should handle the setting 'Completion by Submission' and, if enabled, set the status of
     * the current user.
     *
     * @param	integer
     * @param	integer
     */
    protected function handleSubmission($active_id, $pass, $obligationsAnswered, $authorized): void
    {
        if (!$authorized) {
            return;
        }

        if ($this->isCompletionBySubmissionEnabled()) {
            $maxpoints = $this->questioninfo->getMaximumPoints($this->getId());

            if ($this->getUploadedFiles($active_id, $pass, $authorized)) {
                $points = $maxpoints;
            } else {
                // fau: testNav - don't set reached points if no file is available
                return;
                // fau.
            }

            assQuestion::_setReachedPoints($active_id, $this->getId(), $points, $maxpoints, $pass, true, $obligationsAnswered);

            ilLPStatusWrapper::_updateStatus(
                ilObjTest::_getObjectIDFromActiveID((int) $active_id),
                ilObjTestAccess::_getParticipantId((int) $active_id)
            );
        }
    }

    public function getQuestionType(): string
    {
        return 'assFileUpload';
    }

    public function getAdditionalTableName(): string
    {
        return 'qpl_qst_fileupload';
    }

    public function getAnswerTableName(): string
    {
        return '';
    }

    /**
    * @param integer $question_id The question id which should be deleted in the answers table
    */
    public function deleteAnswers($question_id): void
    {
    }

    /**
    * Collects all text in the question which could contain media objects
    * which were created with the Rich Text Editor
    */
    public function getRTETextWithMediaObjects(): string
    {
        $text = parent::getRTETextWithMediaObjects();
        return $text;
    }

    /**
     * {@inheritdoc}
     */
    public function setExportDetailsXLSX(ilAssExcelFormatHelper $worksheet, int $startrow, int $col, int $active_id, int $pass): int
    {
        parent::setExportDetailsXLSX($worksheet, $startrow, $col, $active_id, $pass);

        $i = 1;
        $solutions = $this->getSolutionValues($active_id, $pass);
        foreach ($solutions as $solution) {
            $worksheet->setCell($startrow + $i, $col, $this->lng->txt('result'));
            $worksheet->setBold($worksheet->getColumnCoord($col) . ($startrow + $i));
            if (strlen($solution['value1'])) {
                $worksheet->setCell($startrow + $i, $col + 2, $solution['value1']);
                $worksheet->setCell($startrow + $i, $col + 3, $solution['value2']);
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
    * @param ilQTIItem $item The QTI item object
    * @param integer $questionpool_id The id of the parent questionpool
    * @param integer $tst_id The id of the parent test if the question is part of a test
    * @param object $tst_object A reference to the parent test object
    * @param integer $question_counter A reference to a question counter to count the questions of an imported question pool
    * @param array $import_mapping An array containing references to included ILIAS objects
    */
    public function fromXML($item, int $questionpool_id, ?int $tst_id, &$tst_object, int &$question_counter, array $import_mapping, array &$solutionhints = []): array
    {
        $import = new assFileUploadImport($this);
        return $import->fromXML($item, $questionpool_id, $tst_id, $tst_object, $question_counter, $import_mapping);
    }

    public function toXML($a_include_header = true, $a_include_binary = true, $a_shuffle = false, $test_output = false, $force_image_references = false): string
    {
        $export = new assFileUploadExport($this);
        return $export->toXML($a_include_header, $a_include_binary, $a_shuffle, $test_output, $force_image_references);
    }

    public function getBestSolution($active_id, $pass): array
    {
        $user_solution = [];
        return $user_solution;
    }

    public function getMaxSize(): ?int
    {
        return $this->maxsize;
    }

    public function setMaxSize(?int $a_value): void
    {
        $this->maxsize = $a_value;
    }

    public function getAllowedExtensionsArray(): array
    {
        if ($this->allowedextensions === '') {
            return [];
        }

        return array_filter(array_map('trim', explode(',', $this->allowedextensions)));
    }

    public function getAllowedExtensions(): string
    {
        return $this->allowedextensions;
    }

    /**
    * Set allowed file extensions
    *
    * @param string $a_value Allowed file extensions
    */
    public function setAllowedExtensions(string $a_value): void
    {
        $this->allowedextensions = strtolower(trim($a_value));
    }

    /**
     * Checks if file uploads exist for a given test and the original id of the question
     *
     * @param int $test_id
     */
    public function hasFileUploads($test_id): bool
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $query = '
		SELECT tst_solutions.solution_id
		FROM tst_solutions, tst_active, qpl_questions
		WHERE tst_solutions.active_fi = tst_active.active_id
		AND tst_solutions.question_fi = qpl_questions.question_id
		AND tst_solutions.question_fi = %s AND tst_active.test_fi = %s
		AND tst_solutions.value1 is not null';
        $result = $ilDB->queryF(
            $query,
            ['integer', 'integer'],
            [$this->getId(), $test_id]
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
     * @param int $ref_id
     * @param int $test_id
     * @param string $test_title
     */
    public function deliverFileUploadZIPFile($ref_id, $test_id, $test_title): void
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $lng = $DIC['lng'];

        $exporter = new ilAssFileUploadUploadsExporter(
            $ilDB,
            $lng,
            $ref_id,
            $test_id
        );

        $exporter->setTestTitle($test_title);
        $exporter->setQuestion($this);

        $exporter->buildAndDownload();
    }

    public function isCompletionBySubmissionEnabled(): bool
    {
        return $this->completion_by_submission;
    }

    /**
     * Enabled/Disable completion by submission
     *
     * @param boolean $bool
     */
    public function setCompletionBySubmission($bool): assFileUpload
    {
        $this->completion_by_submission = (bool) $bool;
        return $this;
    }

    public function isAnswered(int $active_id, int $pass): bool
    {
        $numExistingSolutionRecords = assQuestion::getNumExistingSolutionRecords($active_id, $pass, $this->getId());

        return $numExistingSolutionRecords > 0;
    }

    public static function isObligationPossible(int $questionId): bool
    {
        return true;
    }

    public function buildTestPresentationConfig(): ilTestQuestionConfig
    {
        return parent::buildTestPresentationConfig()
            ->setFormChangeDetectionEnabled(false);
    }

    protected function isFileDeletionAction(): bool
    {
        return $this->getQuestionAction() == ilAssFileUploadFileTableDeleteButton::ACTION;
    }

    protected function isFileDeletionSubmitAvailable(): bool
    {
        return $this->isNonEmptyItemListPostSubmission(self::DELETE_FILES_TBL_POSTVAR);
    }

    protected function isFileReuseSubmitAvailable(): bool
    {
        return $this->isNonEmptyItemListPostSubmission(self::REUSE_FILES_TBL_POSTVAR);
    }

    protected function isFileReuseHandlingRequired(): bool
    {
        if (!$this->getTestPresentationConfig()->isPreviousPassSolutionReuseAllowed()) {
            return false;
        }

        if (!$this->isFileReuseSubmitAvailable()) {
            return false;
        }

        return true;
    }

    protected function isFileUploadAvailable(): bool
    {
        if (!$this->file_upload->hasBeenProcessed()) {
            $this->file_upload->process();
        }
        return $this->file_upload->hasUploads();
    }
}
