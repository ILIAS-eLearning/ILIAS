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

require_once './Modules/Test/classes/inc.AssessmentConstants.php';

/**
 * Class for image map questions
 *
 * assImagemapQuestion is a class for imagemap question.
 *
 * @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @author		Björn Heyser <bheyser@databay.de>
 * @author		Maximilian Becker <mbecker@databay.de>
 *
 * @version		$Id$
 *
 * @ingroup		ModulesTestQuestionPool
 */
class assImagemapQuestion extends assQuestion implements ilObjQuestionScoringAdjustable, ilObjAnswerScoringAdjustable, iQuestionCondition
{
    private \ILIAS\TestQuestionPool\InternalRequestService $request; // Hate it.

    // hey: prevPassSolutions - wtf is imagemap ^^
    public $currentSolution = array();
    // hey.

    public const MODE_SINGLE_CHOICE = 0;
    public const MODE_MULTIPLE_CHOICE = 1;

    public const AVAILABLE_SHAPES = [
        'RECT' => 'rect',
        'CIRCLE' => 'circle',
        'POLY' => 'poly'];

    /** @var $answers array The possible answers of the imagemap question. */
    public $answers;

    /** @var $image_filename string The image file containing the name of image file. */
    public $image_filename;

    /** @var $imagemap_contents string The variable containing contents of an imagemap file. */
    public $imagemap_contents;

    /** @var $coords array */
    public $coords;

    /** @var $is_multiple_choice bool Defines weather the Question is a Single or a Multiplechoice question. */
    protected $is_multiple_choice = false;

    /**
     * assImagemapQuestion constructor
     *
     * The constructor takes possible arguments an creates an instance of the assImagemapQuestion object.
     *
     * @param string  $title    		A title string to describe the question.
     * @param string  $comment  		A comment string to describe the question.
     * @param string  $author   		A string containing the name of the questions author.
     * @param integer $owner    		A numerical ID to identify the owner/creator.
     * @param string  $question 		The question string of the imagemap question.
     * @param string  $image_filename
     *
     */
    public function __construct(
        $title = "",
        $comment = "",
        $author = "",
        $owner = -1,
        $question = "",
        $image_filename = ""
    ) {
        parent::__construct($title, $comment, $author, $owner, $question);
        $this->image_filename = $image_filename;
        $this->answers = array();
        $this->coords = array();

        global $DIC;
        $this->request = $DIC->testQuestionPool()->internal()->request();
    }

    /**
     * Set true if the Imagemapquestion is a multiplechoice Question
     *
     * @param bool $is_multiple_choice
     */
    public function setIsMultipleChoice($is_multiple_choice): void
    {
        $this->is_multiple_choice = $is_multiple_choice;
    }

    /**
     * Returns true, if the imagemap question is a multiplechoice question
     *
     * @return bool
     */
    public function getIsMultipleChoice(): bool
    {
        return $this->is_multiple_choice;
    }

    /**
    * Returns true, if a imagemap question is complete for use
    *
    * @return boolean True, if the imagemap question is complete for use, otherwise false
    * @access public
    */
    public function isComplete(): bool
    {
        if (strlen($this->title)
            && ($this->author)
            && ($this->question)
            && ($this->image_filename)
            && (count($this->answers))
            && ($this->getMaximumPoints() > 0)
        ) {
            return true;
        }
        return false;
    }

    /**
     * Saves an assImagemapQuestion object to a database
     *
     * Saves an assImagemapQuestion object to a database
     *
     * @param string $original_id
     *
     * @return mixed|void
     */
    public function saveToDb($original_id = ""): void
    {
        if ($original_id == '') {
            $this->saveQuestionDataToDb();
        } else {
            $this->saveQuestionDataToDb($original_id);
        }
        $this->saveAdditionalQuestionDataToDb();
        $this->saveAnswerSpecificDataToDb();
        parent::saveToDb($original_id);
    }

    public function saveAnswerSpecificDataToDb()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $ilDB->manipulateF(
            "DELETE FROM qpl_a_imagemap WHERE question_fi = %s",
            array( "integer" ),
            array( $this->getId() )
        );

        // Anworten wegschreiben
        foreach ($this->answers as $key => $value) {
            $answer_obj = $this->answers[$key];
            $answer_obj->setOrder($key);
            $next_id = $ilDB->nextId('qpl_a_imagemap');
            $ilDB->manipulateF(
                "INSERT INTO qpl_a_imagemap (answer_id, question_fi, answertext, points, aorder, coords, area, points_unchecked) VALUES (%s, %s, %s, %s, %s, %s, %s, %s)",
                array( "integer", "integer", "text", "float", "integer", "text", "text", "float" ),
                array( $next_id, $this->id, $answer_obj->getAnswertext(
                ), $answer_obj->getPoints(), $answer_obj->getOrder(
                ), $answer_obj->getCoords(), $answer_obj->getArea(
                ), $answer_obj->getPointsUnchecked() )
            );
        }
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
            ) . " (question_fi, image_file, is_multiple_choice) VALUES (%s, %s, %s)",
            array( "integer", "text", 'integer' ),
            array(
                                $this->getId(),
                                $this->image_filename,
                                (int) $this->is_multiple_choice
                            )
        );
    }

    /**
* Duplicates an assImagemapQuestion
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
        // duplicate the image
        $clone->duplicateImage($this_id, $thisObjId);

        $clone->onDuplicate($thisObjId, $this_id, $clone->getObjId(), $clone->getId());

        return $clone->id;
    }

    /**
    * Copies an assImagemapQuestion object
    *
    * Copies an assImagemapQuestion object
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
        // duplicate the image
        $clone->copyImage($original_id, $source_questionpool_id);

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
        // duplicate the image
        $clone->copyImage($sourceQuestionId, $sourceParentId);

        $clone->onCopy($sourceParentId, $sourceQuestionId, $clone->getObjId(), $clone->getId());

        return $clone->id;
    }

    public function duplicateImage($question_id, $objectId = null): void
    {
        global $DIC;
        $ilLog = $DIC['ilLog'];

        $imagepath = $this->getImagePath();
        $imagepath_original = str_replace("/$this->id/images", "/$question_id/images", $imagepath);

        if ((int) $objectId > 0) {
            $imagepath_original = str_replace("/$this->obj_id/", "/$objectId/", $imagepath_original);
        }

        if (!file_exists($imagepath)) {
            ilFileUtils::makeDirParents($imagepath);
        }
        $filename = $this->getImageFilename();

        // #18755
        if (!file_exists($imagepath_original . $filename)) {
            $ilLog->write("Could not find an image map file when trying to duplicate image: " . $imagepath_original . $filename);
            $imagepath_original = str_replace("/$this->obj_id/", "/$objectId/", $imagepath_original);
            $ilLog->write("Using fallback source directory:" . $imagepath_original);
        }

        if (!file_exists($imagepath_original . $filename) || !copy($imagepath_original . $filename, $imagepath . $filename)) {
            $ilLog->write("Could not duplicate image for image map question: " . $imagepath_original . $filename);
        }
    }

    public function copyImage($question_id, $source_questionpool): void
    {
        $imagepath = $this->getImagePath();
        $imagepath_original = str_replace("/$this->id/images", "/$question_id/images", $imagepath);
        $imagepath_original = str_replace("/$this->obj_id/", "/$source_questionpool/", $imagepath_original);
        if (!file_exists($imagepath)) {
            ilFileUtils::makeDirParents($imagepath);
        }
        $filename = $this->getImageFilename();
        if (!copy($imagepath_original . $filename, $imagepath . $filename)) {
            print "image could not be copied!!!! ";
        }
    }

    /**
    * Loads a assImagemapQuestion object from a database
    *
    * Loads a assImagemapQuestion object from a database (experimental)
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
            $this->setObjId($data["obj_fi"]);
            $this->setTitle((string) $data["title"]);
            $this->setComment((string) $data["description"]);
            $this->setOriginalId($data["original_id"]);
            $this->setNrOfTries($data['nr_of_tries']);
            $this->setAuthor($data["author"]);
            $this->setPoints($data["points"]);
            $this->setOwner($data["owner"]);
            $this->setIsMultipleChoice($data["is_multiple_choice"] == self::MODE_MULTIPLE_CHOICE);
            include_once("./Services/RTE/classes/class.ilRTE.php");
            $this->setQuestion(ilRTE::_replaceMediaObjectImageSrc((string) $data["question_text"], 1));
            $this->setImageFilename($data["image_file"]);
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

            $result = $ilDB->queryF(
                "SELECT * FROM qpl_a_imagemap WHERE question_fi = %s ORDER BY aorder ASC",
                array("integer"),
                array($question_id)
            );
            include_once "./Modules/TestQuestionPool/classes/class.assAnswerImagemap.php";
            if ($result->numRows() > 0) {
                while ($data = $ilDB->fetchAssoc($result)) {
                    $image_map_question = new ASS_AnswerImagemap($data["answertext"] ?? '', $data["points"], $data["aorder"]);
                    $image_map_question->setCoords($data["coords"]);
                    $image_map_question->setArea($data["area"]);
                    $image_map_question->setPointsUnchecked($data['points_unchecked']);
                    array_push($this->answers, $image_map_question);
                }
            }
        }
        parent::loadFromDb($question_id);
    }

    /**
     * Uploads an image map and takes over the areas
     *
     * @param ASS_AnswerImagemap[] $shapes
     * @return integer number of areas added
     */
    public function uploadImagemap(array $shapes): int
    {
        $added = 0;

        if (count($shapes) > 0) {
            foreach ($shapes as $shape) {
                $this->addAnswer($shape->getAnswertext(), 0.0, count($this->answers), $shape->getCoords(), $shape->getArea());
                $added++;
            }
        }

        return $added;
    }

    public function getImageFilename(): string
    {
        return $this->image_filename;
    }

    /**
    * Sets the image file name
    *
    * @param string $image_file name.
    * @access public
    * @see $image_filename
    */
    public function setImageFilename($image_filename, $image_tempfilename = ""): void
    {
        if (!empty($image_filename)) {
            $image_filename = str_replace(" ", "_", $image_filename);
            $this->image_filename = $image_filename;
        }
        if (!empty($image_tempfilename)) {
            $imagepath = $this->getImagePath();
            if (!file_exists($imagepath)) {
                ilFileUtils::makeDirParents($imagepath);
            }
            if (!ilFileUtils::moveUploadedFile($image_tempfilename, $image_filename, $imagepath . $image_filename)) {
                $this->ilias->raiseError("The image could not be uploaded!", $this->ilias->error_obj->MESSAGE);
            }
            global $DIC;
            $ilLog = $DIC['ilLog'];
            $ilLog->write("gespeichert: " . $imagepath . $image_filename);
        }
    }

    /**
    * Gets the imagemap file contents
    *
    * Gets the imagemap file contents
    *
    * @return string The imagemap file contents of the assImagemapQuestion object
    * @access public
    * @see $imagemap_contents
    */
    public function get_imagemap_contents($href = "#"): string
    {
        $imagemap_contents = "<map name=\"" . $this->title . "\"> ";
        for ($i = 0; $i < count($this->answers); $i++) {
            $imagemap_contents .= "<area alt=\"" . $this->answers[$i]->getAnswertext() . "\" ";
            $imagemap_contents .= "shape=\"" . $this->answers[$i]->getArea() . "\" ";
            $imagemap_contents .= "coords=\"" . $this->answers[$i]->getCoords() . "\" ";
            $imagemap_contents .= "href=\"$href&selimage=" . $this->answers[$i]->getOrder() . "\" /> ";
        }
        $imagemap_contents .= "</map>";
        return $imagemap_contents;
    }

    /**
    * Adds a possible answer for a imagemap question
    *
    * Adds a possible answer for a imagemap question. A ASS_AnswerImagemap object will be
    * created and assigned to the array $this->answers.
    *
    * @param string $answertext The answer text
    * @param double $points The points for selecting the answer (even negative points can be used)
    * @param integer $status The state of the answer (set = 1 or unset = 0)
    * @param integer $order A possible display order of the answer
    * @access public
    * @see $answers
    * @see ASS_AnswerImagemap
    */
    public function addAnswer(
        $answertext = "",
        $points = 0.0,
        $order = 0,
        $coords = "",
        $area = "",
        $points_unchecked = 0.0
    ): void {
        include_once "./Modules/TestQuestionPool/classes/class.assAnswerImagemap.php";
        if (array_key_exists($order, $this->answers)) {
            // Insert answer
            $answer = new ASS_AnswerImagemap($answertext, $points, $order, 0, -1);
            $answer->setCoords($coords);
            $answer->setArea($area);
            $answer->setPointsUnchecked($points_unchecked);
            for ($i = count($this->answers) - 1; $i >= $order; $i--) {
                $this->answers[$i + 1] = $this->answers[$i];
                $this->answers[$i + 1]->setOrder($i + 1);
            }
            $this->answers[$order] = $answer;
        } else {
            // Append answer
            $answer = new ASS_AnswerImagemap($answertext, $points, count($this->answers), 0, -1);
            $answer->setCoords($coords);
            $answer->setArea($area);
            $answer->setPointsUnchecked($points_unchecked);
            array_push($this->answers, $answer);
        }
    }

    /**
    * Returns the number of answers
    *
    * Returns the number of answers
    *
    * @return integer The number of answers of the multiple choice question
    * @access public
    * @see $answers
    */
    public function getAnswerCount(): int
    {
        return count($this->answers);
    }

    /**
    * Returns an answer
    *
    * Returns an answer with a given index. The index of the first
    * answer is 0, the index of the second answer is 1 and so on.
    *
    * @param integer $index A nonnegative index of the n-th answer
    * @return object ASS_AnswerImagemap-Object containing the answer
    * @access public
    * @see $answers
    */
    public function getAnswer($index = 0): ?object
    {
        if ($index < 0) {
            return null;
        }
        if (count($this->answers) < 1) {
            return null;
        }
        if ($index >= count($this->answers)) {
            return null;
        }
        return $this->answers[$index];
    }

    /**
    * Returns the answer array
    *
    * Returns the answer array
    *
    * @return array The answer array
    * @access public
    * @see $answers
    */
    public function &getAnswers(): array
    {
        return $this->answers;
    }

    /**
    * Deletes an answer
    *
    * Deletes an area with a given index. The index of the first
    * area is 0, the index of the second area is 1 and so on.
    *
    * @param integer $index A nonnegative index of the n-th answer
    * @access public
    * @see $answers
    */
    public function deleteArea($index = 0): void
    {
        if ($index < 0) {
            return;
        }
        if (count($this->answers) < 1) {
            return;
        }
        if ($index >= count($this->answers)) {
            return;
        }
        unset($this->answers[$index]);
        $this->answers = array_values($this->answers);
        for ($i = 0; $i < count($this->answers); $i++) {
            if ($this->answers[$i]->getOrder() > $index) {
                $this->answers[$i]->setOrder($i);
            }
        }
    }

    /**
    * Deletes all answers
    *
    * Deletes all answers
    *
    * @access public
    * @see $answers
    */
    public function flushAnswers(): void
    {
        $this->answers = array();
    }

    /**
    * Returns the maximum points, a learner can reach answering the question
    *
    * Returns the maximum points, a learner can reach answering the question
    *
    * @access public
    * @see $points
    */
    public function getMaximumPoints(): float
    {
        $points = 0;
        foreach ($this->answers as $key => $value) {
            if ($this->is_multiple_choice) {
                if ($value->getPoints() > $value->getPointsUnchecked()) {
                    $points += $value->getPoints();
                } else {
                    $points += $value->getPointsUnchecked();
                }
            } else {
                if ($value->getPoints() > $points) {
                    $points = $value->getPoints();
                }
            }
        }
        return $points;
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
        while ($data = $ilDB->fetchAssoc($result)) {
            if (strcmp($data["value1"], "") != 0) {
                array_push($found_values, $data["value1"]);
            }
        }

        $points = $this->calculateReachedPointsForSolution($found_values);

        return $points;
    }

    public function calculateReachedPointsFromPreviewSession(ilAssQuestionPreviewSession $previewSession)
    {
        $solutionData = $previewSession->getParticipantsSolution();

        $reachedPoints = $this->calculateReachedPointsForSolution(is_array($solutionData) ? array_values($solutionData) : array());
        $reachedPoints = $this->deductHintPointsFromReachedPoints($previewSession, $reachedPoints);

        return $this->ensureNonNegativePoints($reachedPoints);
    }

    public function isAutosaveable(): bool
    {
        return false; // #15217
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
        global $DIC;
        $ilDB = $DIC['ilDB'];

        if (is_null($pass)) {
            include_once "./Modules/Test/classes/class.ilObjTest.php";
            $pass = ilObjTest::_getPass($active_id);
        }

        $solutionSelectionChanged = false;

        $this->getProcessLocker()->executeUserSolutionUpdateLockOperation(function () use (&$solutionSelectionChanged, $ilDB, $active_id, $pass, $authorized) {
            if ($authorized) {
                // remove the dummy record of the intermediate solution
                $this->deleteDummySolutionRecord($active_id, $pass);

                // delete the authorized solution and make the intermediate solution authorized (keeping timestamps)
                $this->removeCurrentSolution($active_id, $pass, true);
                $this->updateCurrentSolutionsAuthorization($active_id, $pass, true, true);

                $solutionSelectionChanged = true;
            } else {
                $this->forceExistingIntermediateSolution(
                    $active_id,
                    $pass,
                    $this->is_multiple_choice
                );

                if ($this->isReuseSolutionSelectionRequest()) {
                    $selection = $this->getReuseSolutionSelectionParameter();

                    foreach ($selection as $selectedIndex) {
                        $this->saveCurrentSolution($active_id, $pass, (int) $selectedIndex, null, $authorized);
                        $solutionSelectionChanged = true;
                    }
                } elseif ($this->isRemoveSolutionSelectionRequest()) {
                    $selection = $this->getRemoveSolutionSelectionParameter();

                    $this->deleteSolutionRecordByValues($active_id, $pass, $authorized, array(
                        'value1' => (int) $selection
                    ));

                    $solutionSelectionChanged = true;
                } elseif ($this->isAddSolutionSelectionRequest()) {
                    $selection = $this->getAddSolutionSelectionParameter();

                    if ($this->is_multiple_choice) {
                        $this->deleteSolutionRecordByValues($active_id, $pass, $authorized, array(
                            'value1' => (int) $this->request->raw('selImage')
                        ));
                    } else {
                        $this->removeCurrentSolution($active_id, $pass, $authorized);
                    }

                    $this->saveCurrentSolution($active_id, $pass, $this->request->raw('selImage'), null, $authorized);

                    $solutionSelectionChanged = true;
                }
            }
        });

        require_once 'Modules/Test/classes/class.ilObjAssessmentFolder.php';
        if (ilObjAssessmentFolder::_enabledAssessmentLogging()) {
            if ($solutionSelectionChanged) {
                assQuestion::logAction($this->lng->txtlng(
                    "assessment",
                    "log_user_entered_values",
                    ilObjAssessmentFolder::_getLogLanguage()
                ), $active_id, $this->getId());
            } else {
                assQuestion::logAction($this->lng->txtlng(
                    "assessment",
                    "log_user_not_entered_values",
                    ilObjAssessmentFolder::_getLogLanguage()
                ), $active_id, $this->getId());
            }
        }

        return true;
    }

    protected function savePreviewData(ilAssQuestionPreviewSession $previewSession): void
    {
        $solution = $previewSession->getParticipantsSolution();

        if ($this->is_multiple_choice && strlen($this->request->raw('remImage'))) {
            unset($solution[(int) $this->request->raw('remImage')]);
        }

        if (strlen($this->request->raw('selImage'))) {
            if (!$this->is_multiple_choice) {
                $solution = array();
            }

            $solution[(int) $this->request->raw('selImage')] = (int) $this->request->raw('selImage');
        }

        $previewSession->setParticipantsSolution($solution);
    }

    public function syncWithOriginal(): void
    {
        if ($this->getOriginalId()) {
            parent::syncWithOriginal();
        }
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
        return "assImagemapQuestion";
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
        return "qpl_qst_imagemap";
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
        return "qpl_a_imagemap";
    }

    /**
    * Collects all text in the question which could contain media objects
    * which were created with the Rich Text Editor
    */
    public function getRTETextWithMediaObjects(): string
    {
        $text = parent::getRTETextWithMediaObjects();
        foreach ($this->answers as $index => $answer) {
            $text .= $this->feedbackOBJ->getSpecificAnswerFeedbackContent($this->getId(), 0, $index);
        }
        return $text;
    }

    /**
     * {@inheritdoc}
     */
    public function setExportDetailsXLS(ilAssExcelFormatHelper $worksheet, int $startrow, int $col, int $active_id, int $pass): int
    {
        parent::setExportDetailsXLS($worksheet, $startrow, $col, $active_id, $pass);

        $solution = $this->getSolutionValues($active_id, $pass);

        $i = 1;
        foreach ($this->getAnswers() as $id => $answer) {
            $worksheet->setCell($startrow + $i, $col, $answer->getArea() . ": " . $answer->getCoords());
            $worksheet->setBold($worksheet->getColumnCoord($col) . ($startrow + $i));

            $cellValue = 0;
            foreach ($solution as $solIndex => $sol) {
                if ($sol['value1'] == $id) {
                    $cellValue = 1;
                    break;
                }
            }

            $worksheet->setCell($startrow + $i, $col + 1, $cellValue);

            $i++;
        }

        return $startrow + $i + 1;
    }

    /**
    * Deletes the image file
    */
    public function deleteImage(): void
    {
        $file = $this->getImagePath() . $this->getImageFilename();
        @unlink($file);
        $this->flushAnswers();
        $this->image_filename = "";
    }

    /**
    * Returns a JSON representation of the question
    */
    public function toJSON(): string
    {
        include_once("./Services/RTE/classes/class.ilRTE.php");
        $result = array();
        $result['id'] = $this->getId();
        $result['type'] = (string) $this->getQuestionType();
        $result['title'] = $this->getTitle();
        $result['question'] = $this->formatSAQuestion($this->getQuestion());
        $result['nr_of_tries'] = $this->getNrOfTries();
        $result['shuffle'] = $this->getShuffle();
        $result['is_multiple'] = $this->getIsMultipleChoice();
        $result['feedback'] = array(
            'onenotcorrect' => $this->formatSAQuestion($this->feedbackOBJ->getGenericFeedbackTestPresentation($this->getId(), false)),
            'allcorrect' => $this->formatSAQuestion($this->feedbackOBJ->getGenericFeedbackTestPresentation($this->getId(), true))
        );
        $result['image'] = $this->getImagePathWeb() . $this->getImageFilename();

        $answers = array();
        $order = 0;
        foreach ($this->getAnswers() as $key => $answer_obj) {
            array_push($answers, array(
                "answertext" => (string) $answer_obj->getAnswertext(),
                "points" => (float) $answer_obj->getPoints(),
                "points_unchecked" => (float) $answer_obj->getPointsUnchecked(),
                "order" => $order,
                "coords" => $answer_obj->getCoords(),
                "state" => $answer_obj->getState(),
                "area" => $answer_obj->getArea(),
                "feedback" => $this->formatSAQuestion(
                    $this->feedbackOBJ->getSpecificAnswerFeedbackExportPresentation($this->getId(), 0, $key)
                )
            ));
            $order++;
        }
        $result['answers'] = $answers;

        $mobs = ilObjMediaObject::_getMobsOfObject("qpl:html", $this->getId());
        $result['mobs'] = $mobs;

        return json_encode($result);
    }

    /**
     * @param $found_values
     * @return float
     */
    protected function calculateReachedPointsForSolution($found_values): float
    {
        if ($found_values == null) {
            $found_values = [];
        }
        $points = 0;
        if (count($found_values) > 0) {
            foreach ($this->answers as $key => $answer) {
                if (in_array($key, $found_values)) {
                    $points += $answer->getPoints();
                } elseif ($this->getIsMultipleChoice()) {
                    $points += $answer->getPointsUnchecked();
                }
            }
            return $points;
        }
        return $points;
    }

    /**
     * Get all available operations for a specific question
     *
     * @param $expression
     *
     * @internal param string $expression_type
     * @return array
     */
    public function getOperators($expression): array
    {
        return ilOperatorsExpressionMapping::getOperatorsByExpression($expression);
    }

    /**
     * Get all available expression types for a specific question
     * @return array
     */
    public function getExpressionTypes(): array
    {
        return array(
            iQuestionCondition::PercentageResultExpression,
            iQuestionCondition::NumberOfResultExpression,
            iQuestionCondition::EmptyAnswerExpression,
            iQuestionCondition::ExclusiveResultExpression
        );
    }

    /**
    * Get the user solution for a question by active_id and the test pass
    *
    * @param int $active_id
    * @param int $pass
    *
    * @return ilUserQuestionResult
    */
    public function getUserQuestionResult($active_id, $pass): ilUserQuestionResult
    {
        /** @var ilDBInterface $ilDB */
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $result = new ilUserQuestionResult($this, $active_id, $pass);

        $maxStep = $this->lookupMaxStep($active_id, $pass);

        if ($maxStep !== null) {
            $data = $ilDB->queryF(
                "SELECT value1+1 as value1 FROM tst_solutions WHERE active_fi = %s AND pass = %s AND question_fi = %s AND step = %s",
                array("integer", "integer", "integer", "integer"),
                array($active_id, $pass, $this->getId(), $maxStep)
            );
        } else {
            $data = $ilDB->queryF(
                "SELECT value1+1 as value1 FROM tst_solutions WHERE active_fi = %s AND pass = %s AND question_fi = %s AND step IS NULL",
                array("integer", "integer", "integer"),
                array($active_id, $pass, $this->getId())
            );
        }

        while ($row = $ilDB->fetchAssoc($data)) {
            $result->addKeyValue($row["value1"], $row["value1"]);
        }

        $points = $this->calculateReachedPoints($active_id, $pass);
        $max_points = $this->getMaximumPoints();

        $result->setReachedPercentage(($points / $max_points) * 100);

        return $result;
    }

    /**
     * If index is null, the function returns an array with all anwser options
     * Else it returns the specific answer option
     *
     * @param null|int $index
     *
     */
    public function getAvailableAnswerOptions($index = null)
    {
        if ($index !== null) {
            return $this->getAnswer($index);
        } else {
            return $this->getAnswers();
        }
    }

    // hey: prevPassSolutions - wtf is imagemap ^^
    public function getTestOutputSolutions($activeId, $pass): array
    {
        $solution = parent::getTestOutputSolutions($activeId, $pass);

        $this->currentSolution = array();
        foreach ($solution as $record) {
            $this->currentSolution[] = $record['value1'];
        }

        return $solution;
    }
    protected function getAddSolutionSelectionParameter()
    {
        if (!$this->isAddSolutionSelectionRequest()) {
            return null;
        }

        return $this->request->raw('selImage');
    }
    protected function isAddSolutionSelectionRequest(): bool
    {
        if (!$this->request->isset("selImage")) {
            return false;
        }

        if (!strlen($this->request->raw('selImage'))) {
            return false;
        }

        return true;
    }
    protected function getRemoveSolutionSelectionParameter()
    {
        if (!$this->isRemoveSolutionSelectionRequest()) {
            return null;
        }

        return $this->request->raw('remImage');
    }
    protected function isRemoveSolutionSelectionRequest(): bool
    {
        if (!$this->is_multiple_choice) {
            return false;
        }

        if (!$this->request->isset("remImage")) {
            return false;
        }

        if (!strlen($this->request->raw('remImage'))) {
            return false;
        }

        return true;
    }
    protected function getReuseSolutionSelectionParameter(): ?array
    {
        if (!$this->isReuseSolutionSelectionRequest()) {
            return null;
        }

        return assQuestion::explodeKeyValues($this->request->raw("reuseSelection"));
    }
    protected function isReuseSolutionSelectionRequest(): bool
    {
        if (!$this->getTestPresentationConfig()->isPreviousPassSolutionReuseAllowed()) {
            return false;
        }

        if (!$this->request->isset("reuseSelection")) {
            return false;
        }

        if (!strlen($this->request->raw("reuseSelection"))) {
            return false;
        }

        if (!preg_match('/\d(,\d)*/', $this->request->raw("reuseSelection"))) {
            return false;
        }

        return true;
    }
    // hey.
}
