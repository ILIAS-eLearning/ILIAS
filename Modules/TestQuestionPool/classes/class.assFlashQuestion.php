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

require_once './Modules/TestQuestionPool/classes/class.assQuestion.php';
require_once './Modules/Test/classes/inc.AssessmentConstants.php';
require_once './Modules/TestQuestionPool/interfaces/interface.ilObjQuestionScoringAdjustable.php';
require_once './Modules/TestQuestionPool/interfaces/interface.iQuestionCondition.php';

/**
 * Class for Flash based questions
 *
 * @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @author		Björn Heyser <bheyser@databay.de>
 * @author		Maximilian Becker <mbecker@databay.de>
 *
 * @version		$Id$
 *
 * @ingroup		ModulesTestQuestionPool
 */
class assFlashQuestion extends assQuestion implements ilObjQuestionScoringAdjustable
{
    private $width;
    private $height;
    private $parameters;
    private $applet;

    /**
    * assFlashQuestion constructor
    *
    * The constructor takes possible arguments an creates an instance of the assFlashQuestion object.
    *
    * @param string $title A title string to describe the question
    * @param string $comment A comment string to describe the question
    * @param string $author A string containing the name of the questions author
    * @param integer $owner A numerical ID to identify the owner/creator
    * @param string $question The question string of the single choice question
    * @access public
    * @see assQuestion:assQuestion()
    */
    public function __construct(
        $title = "",
        $comment = "",
        $author = "",
        $owner = -1,
        $question = ""
    ) {
        parent::__construct($title, $comment, $author, $owner, $question);
        $this->parameters = array();
        $this->width = 540;
        $this->height = 400;
        $this->applet = "";
    }

    /**
    * Returns true, if a single choice question is complete for use
    *
    * @return boolean True, if the single choice question is complete for use, otherwise false
    * @access public
    */
    public function isComplete(): bool
    {
        if (strlen($this->title)
            && ($this->author)
            && ($this->question)
            && ($this->getMaximumPoints() > 0)
            && (strlen($this->getApplet()))
        ) {
            return true;
        }
        return false;
    }

    /**
    * Saves a assFlashQuestion object to a database
    *
    * @access public
    */
    public function saveToDb($original_id = ""): void
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
            ) . " (question_fi, width, height, applet, params) VALUES (%s, %s, %s, %s, %s)",
            array( "integer", "integer", "integer", "text", "text" ),
            array(
                                $this->getId(),
                                (strlen($this->getWidth())) ? $this->getWidth() : 550,
                                (strlen($this->getHeight())) ? $this->getHeight() : 400,
                                $this->getApplet(),
                                serialize($this->getParameters())
                            )
        );

        try {
            $this->moveAppletIfExists();
        } catch (\ilFileUtilsException $e) {
            \ilLoggerFactory::getRootLogger()->error($e->getMessage());
        }
    }

    /**
     * Moves an applet file (maybe stored in the PHP session) to its final filesystem destination
     * @throws \ilFileUtilsException
     */
    protected function moveAppletIfExists(): void
    {
        if (ilSession::get('flash_upload_filename') != null &&
            file_exists(ilSession::get('flash_upload_filename')) && is_file(ilSession::get('flash_upload_filename'))
        ) {
            $path = $this->getFlashPath();
            ilFileUtils::makeDirParents($path);

            \ilFileUtils::rename(ilSession::get('flash_upload_filename'), $path . $this->getApplet());
            ilSession::clear('flash_upload_filename');
        }
    }

    /**
    * Loads a assFlashQuestion object from a database
    *
    * @param object $db A pear DB object
    * @param integer $question_id A unique key which defines the multiple choice test in the database
    * @access public
    */
    public function loadFromDb($question_id): void
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
            $this->setNrOfTries($data['nr_of_tries']);
            $this->setTitle((string) $data["title"]);
            $this->setComment((string) $data["description"]);
            //$this->setSuggestedSolution($data["solution_hint"]);
            $this->setOriginalId($data["original_id"]);
            $this->setObjId($data["obj_fi"]);
            $this->setAuthor($data["author"]);
            $this->setOwner($data["owner"]);
            $this->setPoints($data["points"]);

            include_once("./Services/RTE/classes/class.ilRTE.php");
            $this->setQuestion(ilRTE::_replaceMediaObjectImageSrc((string) $data["question_text"], 1));
            $this->setEstimatedWorkingTime(substr($data["working_time"], 0, 2), substr($data["working_time"], 3, 2), substr($data["working_time"], 6, 2));

            try {
                $this->setLifecycle(ilAssQuestionLifecycle::getInstance($data['lifecycle']));
            } catch (ilTestQuestionPoolInvalidArgumentException $e) {
                $this->setLifecycle(ilAssQuestionLifecycle::getDraftInstance());
            }

            try {
                $this->setAdditionalContentEditingMode($data['add_cont_edit_mode']);
            } catch (ilTestQuestionPoolException $e) {
            }

            // load additional data
            $result = $ilDB->queryF(
                "SELECT * FROM " . $this->getAdditionalTableName() . " WHERE question_fi = %s",
                array("integer"),
                array($question_id)
            );
            if ($result->numRows() == 1) {
                $data = $ilDB->fetchAssoc($result);
                $this->setWidth($data["width"]);
                $this->setHeight($data["height"]);
                $this->setApplet($data["applet"]);
                $this->parameters = unserialize($data["params"], false);
                if (!is_array($this->parameters)) {
                    $this->clearParameters();
                }
                ilSession::clear('flash_upload_filename');
            }
        }
        parent::loadFromDb($question_id);
    }

    /**
    * Duplicates an assFlashQuestion
    *
    * Duplicates an assFlashQuestion
    *
    * @access public
    */
    public function duplicate(bool $for_test = true, string $title = "", string $author = "", string $owner = "", $testObjId = null): int
    {
        if ($this->id <= 0) {
            // The question has not been saved. It cannot be duplicated
            return -1;
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
        // duplicate the applet
        $clone->duplicateApplet($this_id, $thisObjId);

        $clone->onDuplicate($thisObjId, $this_id, $clone->getObjId(), $clone->getId());

        return $clone->id;
    }

    /**
    * Copies an assFlashQuestion object
    *
    * Copies an assFlashQuestion object
    *
    * @access public
    */
    public function copyObject($target_questionpool_id, $title = ""): int
    {
        if ($this->getId() <= 0) {
            throw new RuntimeException('The question has not been saved. It cannot be duplicated');
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
        // duplicate the applet
        $clone->copyApplet($original_id, $source_questionpool_id);

        $clone->onCopy($source_questionpool_id, $original_id, $clone->getObjId(), $clone->getId());

        return $clone->id;
    }

    public function createNewOriginalFromThisDuplicate($targetParentId, $targetQuestionTitle = ""): int
    {
        if ($this->getId() <= 0) {
            throw new RuntimeException('The question has not been saved. It cannot be duplicated');
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
        // duplicate the applet
        $clone->copyApplet($sourceQuestionId, $sourceParentId);

        $clone->onCopy($sourceParentId, $sourceQuestionId, $clone->getObjId(), $clone->getId());

        return $clone->id;
    }

    /**

     */
    protected function duplicateApplet(int $question_id, $objectId = null): void
    {
        $flashpath = $this->getFlashPath();
        $flashpath_original = preg_replace("/([^\d])$this->id([^\d])/", "\${1}$question_id\${2}", $flashpath);

        if ((int) $objectId > 0) {
            $flashpath_original = str_replace("/$this->obj_id/", "/$objectId/", $flashpath_original);
        }

        if (!file_exists($flashpath)) {
            ilFileUtils::makeDirParents($flashpath);
        }
        $filename = $this->getApplet();
        if (!copy($flashpath_original . $filename, $flashpath . $filename)) {
            print "flash applet could not be duplicated!!!! ";
        }
    }

    /**
    * Copy the flash applet
    *
    * @access public
    * @see $points
    */
    protected function copyApplet($question_id, $source_questionpool): void
    {
        $flashpath = $this->getFlashPath();
        $flashpath_original = preg_replace("/([^\d])$this->id([^\d])/", "\${1}$question_id\${2}", $flashpath);
        $flashpath_original = str_replace("/$this->obj_id/", "/$source_questionpool/", $flashpath_original);
        if (!file_exists($flashpath)) {
            ilFileUtils::makeDirParents($flashpath);
        }
        $filename = $this->getApplet();
        if (!copy($flashpath_original . $filename, $flashpath . $filename)) {
            print "flash applet could not be copied!!!! ";
        }
    }

    /**
    * Returns the maximum points, a learner can reach answering the question
    *
    * @access public
    * @see $points
    */
    public function getMaximumPoints(): float
    {
        return $this->points;
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

        global $DIC;
        $ilDB = $DIC['ilDB'];

        $found_values = array();
        if (is_null($pass)) {
            $pass = $this->getSolutionMaxPass($active_id);
        }

        $result = $this->getCurrentSolutionResultSet($active_id, $pass, $authorizedSolution);

        $points = 0;
        while ($data = $ilDB->fetchAssoc($result)) {
            $points += $data["points"];
        }

        return $points;
    }

    public function calculateReachedPointsFromPreviewSession(ilAssQuestionPreviewSession $previewSession)
    {
        $points = 0;
        foreach ($previewSession->getParticipantsSolution() as $solution) {
            if (isset($solution['points'])) {
                $points += $solution['points'];
            }
        }

        $reachedPoints = $this->deductHintPointsFromReachedPoints($previewSession, $points);

        return $this->ensureNonNegativePoints($reachedPoints);
    }

    public function sendToHost($url, $data, $optional_headers = null): string
    {
        $params = array('http' => array(
            'method' => 'POST',
            'content' => $data
        ));
        if ($optional_headers !== null) {
            $params['http']['header'] = $optional_headers;
        }
        $ctx = stream_context_create($params);
        $fp = @fopen($url, 'rb', false, $ctx);
        if (!$fp) {
            throw new Exception("Problem with $url, " . error_get_last());
        }
        $response = @stream_get_contents($fp);
        if ($response === false) {
            throw new Exception("Problem reading data from $url, " . error_get_last());
        }
        return $response;
    }

    /**
    * Uploads a flash file
    *
    * @param string $flashfile Name of the original flash file
    * @param string $tmpfile Name of the temporary uploaded flash file
    * @return string Name of the file
    * @access public
    */
    public function moveUploadedFile($tmpfile, $flashfile): string
    {
        $result = "";
        if (!empty($tmpfile)) {
            $flashfile = str_replace(" ", "_", $flashfile);
            $flashpath = $this->getFlashPath();
            if (!file_exists($flashpath)) {
                ilFileUtils::makeDirParents($flashpath);
            }
            if (ilFileUtils::moveUploadedFile($tmpfile, $flashfile, $flashpath . $flashfile)) {
                $result = $flashfile;
            }
        }
        return $result;
    }

    public function deleteApplet(): void
    {
        @unlink($this->getFlashPath() . $this->getApplet());
        $this->applet = "";
    }

    /**
     * Saves the learners input of the question to the database.
     *
     * @access public
     * @param integer $active_id Active id of the user
     * @param integer $pass Test pass
     * @return boolean $status
     */
    public function saveWorkingData($active_id, $pass = null, $authorized = true): bool
    {
        // nothing to save!

        //$this->getProcessLocker()->requestUserSolutionUpdateLock();
        // store in tst_solutions
        //$this->getProcessLocker()->releaseUserSolutionUpdateLock();

        return true;
    }

    protected function savePreviewData(ilAssQuestionPreviewSession $previewSession): void
    {
    }

    /**
    * Returns the question type of the question
    *
    * Returns the question type of the question
    *
    * @return integer The question type of the question
    * @access public
    */
    public function getQuestionType(): string
    {
        return "assFlashQuestion";
    }

    /**
    * Returns the name of the additional question data table in the database
    *
    * Returns the name of the additional question data table in the database
    *
    * @return string The additional table name
    * @access public
    */
    public function getAdditionalTableName(): string
    {
        return "qpl_qst_flash";
    }

    /**
    * Returns the name of the answer table in the database
    *
    * Returns the name of the answer table in the database
    *
    * @return string The answer table name
    * @access public
    */
    public function getAnswerTableName(): string
    {
        return "";
    }

    /**
    * Deletes datasets from answers tables
    *
    * @param integer $question_id The question id which should be deleted in the answers table
    * @access public
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
    public function setExportDetailsXLS(ilAssExcelFormatHelper $worksheet, int $startrow, int $active_id, int $pass): int
    {
        parent::setExportDetailsXLS($worksheet, $startrow, $active_id, $pass);

        return $startrow + 1;
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
    * @access public
    */
    public function fromXML($item, int $questionpool_id, ?int $tst_id, $tst_object, int $question_counter, array $import_mapping, array $solutionhints = []): void
    {
        include_once "./Modules/TestQuestionPool/classes/import/qti12/class.assFlashQuestionImport.php";
        $import = new assFlashQuestionImport($this);
        $import->fromXML($item, $questionpool_id, $tst_id, $tst_object, $question_counter, $import_mapping);
    }

    /**
    * Returns a QTI xml representation of the question and sets the internal
    * domxml variable with the DOM XML representation of the QTI xml representation
    *
    * @return string The QTI xml representation of the question
    * @access public
    */
    public function toXML($a_include_header = true, $a_include_binary = true, $a_shuffle = false, $test_output = false, $force_image_references = false): string
    {
        include_once "./Modules/TestQuestionPool/classes/export/qti12/class.assFlashQuestionExport.php";
        $export = new assFlashQuestionExport($this);
        return $export->toXML($a_include_header, $a_include_binary, $a_shuffle, $test_output, $force_image_references);
    }

    /**
    * Returns the best solution for a given pass of a participant
    *
    * @return array An associated array containing the best solution
    * @access public
    */
    public function getBestSolution($active_id, $pass): array
    {
        $user_solution = array();
        return $user_solution;
    }

    public function setHeight($a_height): void
    {
        if (!$a_height) {
            $a_height = 400;
        }
        $this->height = $a_height;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function setWidth($a_width): void
    {
        if (!$a_width) {
            $a_width = 550;
        }
        $this->width = $a_width;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function setApplet($a_applet): void
    {
        $this->applet = $a_applet;
    }

    public function getApplet(): string
    {
        return $this->applet;
    }

    public function addParameter($name, $value): void
    {
        $this->parameters[$name] = $value;
    }

    public function setParameters($params): void
    {
        if (is_array($params)) {
            $this->parameters = $params;
        } else {
            $this->parameters = array();
        }
    }

    public function removeParameter($name): void
    {
        unset($this->parameters[$name]);
    }

    public function clearParameters(): void
    {
        $this->parameters = array();
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function isAutosaveable(): bool
    {
        return false;
    }

    // fau: testNav - new function getTestQuestionConfig()
    /**
     * Get the test question configuration
     * @return ilTestQuestionConfig
     */
    // hey: refactored identifiers
    public function buildTestPresentationConfig(): ilTestQuestionConfig
    // hey.
    {
        // hey: refactored identifiers
        return parent::buildTestPresentationConfig()
        // hey.
            ->setFormChangeDetectionEnabled(false)
            ->setBackgroundChangeDetectionEnabled(true);
    }
    // fau.
}
