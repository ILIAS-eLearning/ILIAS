<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Modules/TestQuestionPool/classes/class.assQuestion.php';
require_once './Modules/Test/classes/inc.AssessmentConstants.php';
require_once './Modules/TestQuestionPool/interfaces/interface.ilObjQuestionScoringAdjustable.php';
require_once './Modules/TestQuestionPool/interfaces/interface.ilObjAnswerScoringAdjustable.php';
require_once './Modules/TestQuestionPool/interfaces/interface.iQuestionCondition.php';
require_once './Modules/TestQuestionPool/classes/class.ilUserQuestionResult.php';

/**
 * Class for error text questions
 *
 * @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @author		Björn Heyser <bheyser@databay.de>
 * @author		Grégory Saive <gsaive@databay.de>
 * @author		Maximilian Becker <mbecker@databay.de>
 *
 * @version		$Id$
 *
 * @ingroup		ModulesTestQuestionPool
 */
class assErrorText extends assQuestion implements ilObjQuestionScoringAdjustable, ilObjAnswerScoringAdjustable, iQuestionCondition
{
    protected $errortext;
    protected $textsize;
    protected $errordata;
    protected $points_wrong;

    /**
     * assErorText constructor
     *
     * @param string 	$title 		A title string to describe the question.
     * @param string 	$comment 	A comment string to describe the question.
     * @param string 	$author 	A string containing the name of the questions author.
     * @param integer 	$owner 		A numerical ID to identify the owner/creator.
     * @param string 	$question 	The question string of the single choice question.
     *
     * @return \assErrorText
    */
    public function __construct(
        $title = '',
        $comment = '',
        $author = '',
        $owner = -1,
        $question = ''
    ) {
        parent::__construct($title, $comment, $author, $owner, $question);
        $this->errortext = '';
        $this->textsize = 100.0;
        $this->errordata = array();
    }

    /**
    * Returns true, if a single choice question is complete for use
    *
    * @return boolean True, if the single choice question is complete for use, otherwise false
    */
    public function isComplete()
    {
        if (strlen($this->title)
            && ($this->author)
            && ($this->question)
            && ($this->getMaximumPoints() > 0)) {
            return true;
        } else {
            return false;
        }
    }

    /**
    * Saves a the object to the database
    *
    */
    public function saveToDb($original_id = "")
    {
        $this->saveQuestionDataToDb($original_id);
        $this->saveAdditionalQuestionDataToDb();
        $this->saveAnswerSpecificDataToDb();
        parent::saveToDb();
    }

    public function saveAnswerSpecificDataToDb()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $ilDB->manipulateF(
            "DELETE FROM qpl_a_errortext WHERE question_fi = %s",
            array( 'integer' ),
            array( $this->getId() )
        );

        $sequence = 0;
        foreach ($this->errordata as $object) {
            $next_id = $ilDB->nextId('qpl_a_errortext');
            $ilDB->manipulateF(
                "INSERT INTO qpl_a_errortext (answer_id, question_fi, text_wrong, text_correct, points, sequence) VALUES (%s, %s, %s, %s, %s, %s)",
                array( 'integer', 'integer', 'text', 'text', 'float', 'integer' ),
                array(
                                    $next_id,
                                    $this->getId(),
                                    $object->text_wrong,
                                    $object->text_correct,
                                    $object->points,
                                    $sequence++
                                )
            );
        }
    }

    /**
     * Saves the data for the additional data table.
     *
     * This method uses the ugly DELETE-INSERT. Here, this does no harm.
     */
    public function saveAdditionalQuestionDataToDb()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        // save additional data
        $ilDB->manipulateF(
            "DELETE FROM " . $this->getAdditionalTableName() . " WHERE question_fi = %s",
            array( "integer" ),
            array( $this->getId() )
        );
        
        $ilDB->manipulateF(
            "INSERT INTO " . $this->getAdditionalTableName() . " (question_fi, errortext, textsize, points_wrong) VALUES (%s, %s, %s, %s)",
            array("integer", "text", "float", "float"),
            array(
                               $this->getId(),
                               $this->getErrorText(),
                               $this->getTextSize(),
                               $this->getPointsWrong()
                           )
        );
    }

    /**
    * Loads the object from the database
    *
    * @param object $db A pear DB object
    * @param integer $question_id A unique key which defines the multiple choice test in the database
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
            $this->setObjId($data["obj_fi"]);
            $this->setTitle($data["title"]);
            $this->setComment($data["description"]);
            $this->setOriginalId($data["original_id"]);
            $this->setNrOfTries($data['nr_of_tries']);
            $this->setAuthor($data["author"]);
            $this->setPoints($data["points"]);
            $this->setOwner($data["owner"]);
            include_once("./Services/RTE/classes/class.ilRTE.php");
            $this->setQuestion(ilRTE::_replaceMediaObjectImageSrc($data["question_text"], 1));
            $this->setErrorText($data["errortext"]);
            $this->setTextSize($data["textsize"]);
            $this->setPointsWrong($data["points_wrong"]);
            $this->setEstimatedWorkingTime(substr($data["working_time"], 0, 2), substr($data["working_time"], 3, 2), substr($data["working_time"], 6, 2));
            
            try {
                $this->setAdditionalContentEditingMode($data['add_cont_edit_mode']);
            } catch (ilTestQuestionPoolException $e) {
            }
        }

        $result = $ilDB->queryF(
            "SELECT * FROM qpl_a_errortext WHERE question_fi = %s ORDER BY sequence ASC",
            array('integer'),
            array($question_id)
        );
        include_once "./Modules/TestQuestionPool/classes/class.assAnswerErrorText.php";
        if ($result->numRows() > 0) {
            while ($data = $ilDB->fetchAssoc($result)) {
                array_push($this->errordata, new assAnswerErrorText($data["text_wrong"], $data["text_correct"], $data["points"]));
            }
        }

        parent::loadFromDb($question_id);
    }

    /**
    * Duplicates the object
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
    * Copies an object
    */
    public function copyObject($target_questionpool_id, $title = "")
    {
        if ($this->id <= 0) {
            // The question has not been saved. It cannot be duplicated
            return;
        }
        // duplicate the question in database
        
        $thisId = $this->getId();
        $thisObjId = $this->getObjId();
        
        $clone = $this;
        include_once("./Modules/TestQuestionPool/classes/class.assQuestion.php");
        $original_id = assQuestion::_getOriginalId($this->id);
        $clone->id = -1;
        $clone->setObjId($target_questionpool_id);
        if ($title) {
            $clone->setTitle($title);
        }
        $clone->saveToDb();

        // copy question page content
        $clone->copyPageOfQuestion($original_id);
        // copy XHTML media objects
        $clone->copyXHTMLMediaObjectsOfQuestion($original_id);

        $clone->onCopy($thisObjId, $thisId, $clone->getObjId(), $clone->getId());

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
        $maxpoints = 0.0;
        foreach ($this->errordata as $object) {
            if ($object->points > 0) {
                $maxpoints += $object->points;
            }
        }
        return $maxpoints;
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

        global $DIC;
        $ilDB = $DIC['ilDB'];

        /* First get the positions which were selected by the user. */
        $positions = array();
        if (is_null($pass)) {
            $pass = $this->getSolutionMaxPass($active_id);
        }
        $result = $this->getCurrentSolutionResultSet($active_id, $pass, $authorizedSolution);

        while ($row = $ilDB->fetchAssoc($result)) {
            array_push($positions, $row['value1']);
        }
        $points = $this->getPointsForSelectedPositions($positions);
        return $points;
    }

    public function calculateReachedPointsFromPreviewSession(ilAssQuestionPreviewSession $previewSession)
    {
        $reachedPoints = $this->getPointsForSelectedPositions($previewSession->getParticipantsSolution());
        $reachedPoints = $this->deductHintPointsFromReachedPoints($previewSession, $reachedPoints);
        return $this->ensureNonNegativePoints($reachedPoints);
    }

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
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $ilUser = $DIC['ilUser'];

        if (is_null($pass)) {
            include_once "./Modules/Test/classes/class.ilObjTest.php";
            $pass = ilObjTest::_getPass($active_id);
        }

        $entered_values = false;

        $this->getProcessLocker()->executeUserSolutionUpdateLockOperation(function () use (&$entered_values, $active_id, $pass, $authorized) {
            $this->removeCurrentSolution($active_id, $pass, $authorized);

            if (strlen($_POST["qst_" . $this->getId()])) {
                $selected = explode(",", $_POST["qst_" . $this->getId()]);
                foreach ($selected as $position) {
                    $this->saveCurrentSolution($active_id, $pass, $position, null, $authorized);
                }
                $entered_values = true;
            }
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

    public function savePreviewData(ilAssQuestionPreviewSession $previewSession)
    {
        if (strlen($_POST["qst_" . $this->getId()])) {
            $selection = explode(',', $_POST["qst_{$this->getId()}"]);
        } else {
            $selection = array();
        }
        
        $previewSession->setParticipantsSolution($selection);
    }

    /**
    * Returns the question type of the question
    *
    * @return integer The question type of the question
    */
    public function getQuestionType()
    {
        return "assErrorText";
    }

    /**
    * Returns the name of the additional question data table in the database
    *
    * @return string The additional table name
    */
    public function getAdditionalTableName()
    {
        return "qpl_qst_errortext";
    }

    /**
    * Returns the name of the answer table in the database
    *
    * @return string The answer table name
    */
    public function getAnswerTableName()
    {
        return "qpl_a_errortext";
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

        $i= 0;
        $selections = array();
        $solutions =&$this->getSolutionValues($active_id, $pass);
        if (is_array($solutions)) {
            foreach ($solutions as $solution) {
                array_push($selections, $solution['value1']);
            }
            $errortext_value = join(",", $selections);
        }
        $errortext = $this->createErrorTextExport($selections);
        $i++;
        $worksheet->setCell($startrow+$i, 0, $errortext);
        $i++;

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
        include_once "./Modules/TestQuestionPool/classes/import/qti12/class.assErrorTextImport.php";
        $import = new assErrorTextImport($this);
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
        include_once "./Modules/TestQuestionPool/classes/export/qti12/class.assErrorTextExport.php";
        $export = new assErrorTextExport($this);
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

    public function getErrorsFromText($a_text = "")
    {
        if (strlen($a_text) == 0) {
            $a_text = $this->getErrorText();
        }

        /* Workaround to allow '(' and ')' in passages.
           The beginning- and ending- Passage delimiters are
           replaced by a ~ (Tilde) symbol. */
        $a_text = str_replace(array("((", "))"), array("~", "~"), $a_text);

        /* Match either Passage delimited by double brackets
           or single words marked with a hash (#). */
        $r_passage = "/(~([^~]+)~|#([^\s]+))/";

        preg_match_all($r_passage, $a_text, $matches);

        if (is_array($matches[0]) && !empty($matches[0])) {
            /* At least one match. */

            /* We need only groups 2 and 3, respectively representing
               passage matches and single word matches. */
            $matches = array_intersect_key($matches, array(2 => '', 3 => ''));

            /* Remove empty values. */
            $matches[2] = array_diff($matches[2], array(''));
            $matches[3] = array_diff($matches[3], array(''));

            return array(
                "passages"  => $matches[2],
                "words"		=> $matches[3],);
        }

        return array();
    }

    public function setErrorData($a_data)
    {
        include_once "./Modules/TestQuestionPool/classes/class.assAnswerErrorText.php";
        $temp = $this->errordata;
        $this->errordata = array();
        foreach ($a_data as $err_type => $errors) {
            /* Iterate through error types (Passages|single words) */

            foreach ($errors as $idx => $error) {
                /* Iterate through errors of this type. */
                $text_correct = "";
                $points = 0.0;
                foreach ($temp as $object) {
                    if (strcmp($object->text_wrong, $error) == 0) {
                        $text_correct = $object->text_correct;
                        $points = $object->points;
                        continue;
                    }
                }
                $this->errordata[$idx] = new assAnswerErrorText($error, $text_correct, $points);
            }
        }
        ksort($this->errordata);
    }

    public function createErrorTextOutput($selections = null, $graphicalOutput = false, $correct_solution = false, $use_link_tags = true)
    {
        $counter = 0;
        $errorcounter = 0;
        include_once "./Services/Utilities/classes/class.ilStr.php";
        if (!is_array($selections)) {
            $selections = array();
        }
        $textarray = preg_split("/[\n\r]+/", $this->getErrorText());

        foreach ($textarray as $textidx => $text) {
            $in_passage	 = false;
            $passage_end = false;
            $items = preg_split("/\s+/", $text);
            foreach ($items as $idx => $item) {
                $img = '';

                if (
                    ($posHash = strpos($item, '#')) === 0 ||
                    ($posOpeningBrackets = strpos($item, '((')) === 0 ||
                    ($posClosingBrackets = strpos($item, '))')) !== false
                ) {
                    /* (Word|Passage)-Marking delimiter found. */

                    if ($posHash !== false) {
                        $item = ilStr::substr($item, 1, ilStr::strlen($item) - 1);
                        $passage_end = false;
                    } elseif ($posOpeningBrackets !== false) {
                        $in_passage  = true;
                        $passage_start_idx = $counter;
                        $items_in_passage = array();
                        $passage_end = false;
                        $item = ilStr::substr($item, 2, ilStr::strlen($item) - 2);

                        /* Sometimes a closing bracket group needs
                           to be removed as well. */
                        if (strpos($item, '))') !== false) {
                            $item = str_replace("))", "", $item);
                            $passage_end = true;
                        }
                    } else {
                        $passage_end = true;
                        $item = str_replace("))", "", $item);
                    }

                    if ($correct_solution && !$in_passage) {
                        $errorobject = $this->errordata[$errorcounter];
                        if (is_object($errorobject)) {
                            $item = strlen($errorobject->text_correct) ? $errorobject->text_correct : '&nbsp;';
                        }
                        $errorcounter++;
                    }
                }
                
                if ($in_passage && !$passage_end) {
                    $items_in_passage[$idx] = $item;
                    $items[$idx] = '';
                    $counter++;
                    continue;
                }

                if ($in_passage && $passage_end) {
                    $in_passage  = false;
                    $passage_end = false;
                    if ($correct_solution) {
                        $class = (
                            $this->isTokenSelected($counter, $selections) ?
                            "ilc_qetitem_ErrorTextSelected" : "ilc_qetitem_ErrorTextItem"
                        );
                        
                        $errorobject = $this->errordata[$errorcounter];
                        if (is_object($errorobject)) {
                            $item = strlen($errorobject->text_correct) ? $errorobject->text_correct : '&nbsp;';
                        }
                        $errorcounter++;
                        $items[$idx] = $this->getErrorTokenHtml($item, $class, $use_link_tags) . $img;
                        $counter++;
                        continue;
                    }
                    
                    $group_selected = true;
                    if ($graphicalOutput) {
                        $start_idx = $passage_start_idx;
                        foreach ($items_in_passage as $tmp_idx => $tmp_item) {
                            if (!$this->isTokenSelected($start_idx, $selections)) {
                                $group_selected = false;
                                break;
                            }
                            
                            ++$start_idx;
                        }
                        if ($group_selected) {
                            if (!$this->isTokenSelected($counter, $selections)) {
                                $group_selected = false;
                            }
                        }
                    }

                    $item_stack = array();
                    $start_idx = $passage_start_idx;
                    foreach ($items_in_passage as $tmp_idx => $tmp_item) {
                        $class = (
                            $this->isTokenSelected($counter, $selections) ?
                            "ilc_qetitem_ErrorTextSelected" : "ilc_qetitem_ErrorTextItem"
                        );
                        $item_stack[] = $this->getErrorTokenHtml($tmp_item, $class, $use_link_tags) . $img;
                        $start_idx++;
                    }
                    $class = (
                        $this->isTokenSelected($counter, $selections) ?
                        "ilc_qetitem_ErrorTextSelected" : "ilc_qetitem_ErrorTextItem"
                    );
                    if ($graphicalOutput) {
                        if ($group_selected) {
                            $img = ' <img src="' . ilUtil::getImagePath("icon_ok.svg") . '" alt="' . $this->lng->txt("answer_is_right") . '" title="' . $this->lng->txt("answer_is_right") . '" /> ';
                        } else {
                            $img = ' <img src="' . ilUtil::getImagePath("icon_not_ok.svg") . '" alt="' . $this->lng->txt("answer_is_wrong") . '" title="' . $this->lng->txt("answer_is_wrong") . '" /> ';
                        }
                    }
                    
                    $item_stack[] = $this->getErrorTokenHtml($item, $class, $use_link_tags) . $img;
                    $item_stack = trim(implode(" ", $item_stack));
                    $item_stack = strlen($item_stack) ? $item_stack : '&nbsp;';
                    
                    if ($graphicalOutput) {
                        $items[$idx] = '<span class="selGroup">' . $item_stack . '</span>';
                    } else {
                        $items[$idx] = $item_stack;
                    }
                    
                    $counter++;
                    continue;
                }

                // Errors markes with #, group errors (()) are handled above
                $class = 'ilc_qetitem_ErrorTextItem';
                $img = '';
                if ($this->isTokenSelected($counter, $selections)) {
                    $class = "ilc_qetitem_ErrorTextSelected";
                    if ($graphicalOutput) {
                        if ($this->getPointsForSelectedPositions(array($counter)) > 0) {
                            $img = ' <img src="' . ilUtil::getImagePath("icon_ok.svg") . '" alt="' . $this->lng->txt("answer_is_right") . '" title="' . $this->lng->txt("answer_is_right") . '" /> ';
                        } else {
                            $img = ' <img src="' . ilUtil::getImagePath("icon_not_ok.svg") . '" alt="' . $this->lng->txt("answer_is_wrong") . '" title="' . $this->lng->txt("answer_is_wrong") . '" /> ';
                        }
                    }
                }

                $items[$idx] = $this->getErrorTokenHtml($item, $class, $use_link_tags) . $img;
                $counter++;
            }
            $textarray[$textidx] = '<p>' . implode(" ", $items) . '</p>';
        }
        
        return implode("\n", $textarray);
    }
    
    protected function isTokenSelected($counter, array $selection)
    {
        foreach ($selection as $data) {
            if (!is_array($data)) {
                if ($counter == $data) {
                    return true;
                }
            } elseif (in_array($counter, $data)) {
                return true;
            }
        }
        
        return false;
    }

    public function createErrorTextExport($selections = null)
    {
        $counter = 0;
        $errorcounter = 0;
        include_once "./Services/Utilities/classes/class.ilStr.php";
        if (!is_array($selections)) {
            $selections = array();
        }
        $textarray = preg_split("/[\n\r]+/", $this->getErrorText());
        foreach ($textarray as $textidx => $text) {
            $items = preg_split("/\s+/", $text);
            foreach ($items as $idx => $item) {
                if (($posHash = strpos($item, '#')) === 0
                    || ($posOpeningBrackets = strpos($item, '((')) === 0
                    || ($posClosingBrackets = strpos($item, '))')) !== false) {
                    /* (Word|Passage)-Marking delimiter found. */

                    if ($posHash !== false) {
                        $item = ilStr::substr($item, 1, ilStr::strlen($item) - 1);
                    } elseif ($posOpeningBrackets !== false) {
                        $item = ilStr::substr($item, 2, ilStr::strlen($item) - 2);

                        /* Sometimes a closing bracket group needs
                           to be removed as well. */
                        if (strpos($item, '))') !== false) {
                            $item = ilStr::substr($item, 0, ilStr::strlen($item) - 2);
                        }
                    } else {
                        $appendComma = "";
                        if ($item{$posClosingBrackets+2} == ',') {
                            $appendComma = ",";
                        }

                        $item = ilStr::substr($item, 0, $posClosingBrackets) . $appendComma;
                    }
                }

                $word = "";
                if (in_array($counter, $selections)) {
                    $word .= '#';
                }
                $word .= ilUtil::prepareFormOutput($item);
                if (in_array($counter, $selections)) {
                    $word .= '#';
                }
                $items[$idx] = $word;
                $counter++;
            }
            $textarray[$textidx] = join($items, " ");
        }
        return join($textarray, "\n");
    }

    public function getBestSelection($withPositivePointsOnly = true)
    {
        $passages	= array();
        $words		= array();
        $counter	= 0;
        $errorcounter = 0;
        $textarray = preg_split("/[\n\r]+/", $this->getErrorText());
        foreach ($textarray as $textidx => $text) {
            $items		= preg_split("/\s+/", $text);
            $inPassage  = false;
            foreach ($items as $word) {
                $points = $this->getPointsWrong();
                $isErrorItem = false;
                if (strpos($word, '#') === 0) {
                    /* Word selection detected */
                    $errorobject = $this->errordata[$errorcounter];
                    if (is_object($errorobject)) {
                        $points = $errorobject->points;
                        $isErrorItem = true;
                    }
                    $errorcounter++;
                } elseif (($posOpeningBracket = strpos($word, '((')) === 0
                        || ($posClosingBracket = strpos($word, '))')) !== false
                        || $inPassage) {
                    /* Passage selection detected */

                    if ($posOpeningBracket !== false) {
                        $passages[] = array('begin_pos' => $counter, 'cnt_words' => 0);
                        $inPassage  = true;
                    } elseif ($posClosingBracket !== false) {
                        $inPassage = false;
                        $cur_pidx  = count($passages) - 1;
                        $passages[$cur_pidx]['end_pos'] = $counter;

                        $errorobject = $this->errordata[$errorcounter];
                        if (is_object($errorobject)) {
                            $passages[$cur_pidx]['score'] = $errorobject->points;
                            $passages[$cur_pidx]['isError'] = true;
                        }
                        
                        $errorcounter++;
                    }

                    $cur_pidx = count($passages) - 1;
                    $passages[$cur_pidx]['cnt_words']++;
                    $points = 0;
                }

                $words[$counter] = array("word" => $word, "points" => $points, "isError" => $isErrorItem);
                $counter++;
            }
        }

        $selections = array();
        foreach ($passages as $cnt => $pdata) {
            if (!$withPositivePointsOnly && $pdata['isError'] || $withPositivePointsOnly && $pdata['score'] > 0) {
                $indexes = range($pdata['begin_pos'], $pdata['end_pos']);
                $selections[$pdata['begin_pos']] = $indexes;
            }
        }

        foreach ($words as $idx => $word) {
            if (!$withPositivePointsOnly && $word['isError'] || $withPositivePointsOnly && $word['points'] > 0) {
                $selections[$idx] = array($idx);
            }
        }

        ksort($selections);
        
        $selections = array_values($selections);
        
        return $selections;
    }

    protected function getPointsForSelectedPositions($positions)
    {
        $passages	= array();
        $words		= array();
        $counter	= 0;
        $errorcounter = 0;
        $textarray	  = preg_split("/[\n\r]+/", $this->getErrorText());
        foreach ($textarray as $textidx => $text) {
            $items		= preg_split("/\s+/", $text);
            $inPassage  = false;
            foreach ($items as $word) {
                $points  = $this->getPointsWrong();
                if (strpos($word, '#') === 0) {
                    /* Word selection detected */
                    $errorobject = $this->errordata[$errorcounter];
                    if (is_object($errorobject)) {
                        $points = $errorobject->points;
                    }
                    $errorcounter++;
                } elseif (($posOpeningBracket = strpos($word, '((')) === 0
                        || ($posClosingBracket = strpos($word, '))')) !== false
                        || $inPassage) {
                    /* Passage selection detected */

                    if ($posOpeningBracket !== false) {
                        $passages[] = array('begin_pos' => $counter, 'cnt_words' => 0);
                        $inPassage  = true;
                    } elseif ($posClosingBracket !== false) {
                        $inPassage = false;
                        $cur_pidx  = count($passages) - 1;
                        $passages[$cur_pidx]['end_pos'] = $counter;

                        $errorobject = $this->errordata[$errorcounter];
                        if (is_object($errorobject)) {
                            $passages[$cur_pidx]['score'] = $errorobject->points;
                        }
                        $errorcounter++;
                    }

                    $cur_pidx = count($passages) - 1;
                    $passages[$cur_pidx]['cnt_words']++;
                    $points = 0;
                }

                $words[$counter] = array("word" => $word, "points" => $points);
                $counter++;
            }
        }

        /* Calculate reached points */
        $total 		 = 0;
        foreach ($positions as $position) {
            /* First iterate through positions
               to identify single-word-selections. */

            $total += $words[$position]['points'];
        }

        foreach ($passages as $cnt => $p_data) {
            /* Iterate through configured passages to check
               wether the entire passage is selected or not.
               The total points is incremented by the passage's
               score only if the entire passage is selected. */
            $isSelected = in_array($p_data['begin_pos'], $positions);

            for ($i = 0; $i < $p_data['cnt_words']; $i++) {
                $current_pos = $p_data['begin_pos'] + $i;
                $isSelected  = $isSelected && in_array($current_pos, $positions);
            }

            $total += $isSelected ? $p_data['score'] : 0;
        }

        return $total;
    }

    /**
    * Flush error data
    */
    public function flushErrorData()
    {
        $this->errordata = array();
    }

    public function addErrorData($text_wrong, $text_correct, $points)
    {
        include_once "./Modules/TestQuestionPool/classes/class.assAnswerErrorText.php";
        array_push($this->errordata, new assAnswerErrorText($text_wrong, $text_correct, $points));
    }

    /**
    * Get error data
    *
    * @return array Error data
    */
    public function getErrorData()
    {
        return $this->errordata;
    }

    /**
    * Get error text
    *
    * @return string Error text
    */
    public function getErrorText()
    {
        return $this->errortext;
    }

    /**
    * Set error text
    *
    * @param string $a_value Error text
    */
    public function setErrorText($a_value)
    {
        $this->errortext = $a_value;
    }

    /**
    * Set text size in percent
    *
    * @return double Text size in percent
    */
    public function getTextSize()
    {
        return $this->textsize;
    }

    /**
    * Set text size in percent
    *
    * @param double $a_value text size in percent
    */
    public function setTextSize($a_value)
    {
        // in self-assesment-mode value should always be set (and must not be null)
        if ($a_value === null) {
            $a_value = 100;
        }
        $this->textsize = $a_value;
    }

    /**
    * Get wrong points
    *
    * @return double Points for wrong selection
    */
    public function getPointsWrong()
    {
        return $this->points_wrong;
    }

    /**
    * Set wrong points
    *
    * @param double $a_value Points for wrong selection
    */
    public function setPointsWrong($a_value)
    {
        $this->points_wrong = $a_value;
    }

    /**
    * Object getter
    */
    public function __get($value)
    {
        switch ($value) {
            case "errortext":
                return $this->getErrorText();
                break;
            case "textsize":
                return $this->getTextSize();
                break;
            case "points_wrong":
                return $this->getPointsWrong();
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
            case "errortext":
                $this->setErrorText($value);
                break;
            case "textsize":
                $this->setTextSize($value);
                break;
            case "points_wrong":
                $this->setPointsWrong($value);
                break;
            default:
                parent::__set($key, $value);
                break;
        }
    }


    /**
    * Returns a JSON representation of the question
    */
    public function toJSON()
    {
        include_once("./Services/RTE/classes/class.ilRTE.php");
        $result = array();
        $result['id'] = (int) $this->getId();
        $result['type'] = (string) $this->getQuestionType();
        $result['title'] = (string) $this->getTitle();
        $result['question'] =  $this->formatSAQuestion($this->getQuestion());
        $result['text'] =  (string) ilRTE::_replaceMediaObjectImageSrc($this->getErrorText(), 0);
        $result['nr_of_tries'] = (int) $this->getNrOfTries();
        $result['shuffle'] = (bool) $this->getShuffle();
        $result['feedback'] = array(
            'onenotcorrect' => $this->formatSAQuestion($this->feedbackOBJ->getGenericFeedbackTestPresentation($this->getId(), false)),
            'allcorrect' => $this->formatSAQuestion($this->feedbackOBJ->getGenericFeedbackTestPresentation($this->getId(), true))
        );

        $answers = array();
        foreach ($this->getErrorData() as $idx => $answer_obj) {
            array_push($answers, array(
                "answertext_wrong" => (string) $answer_obj->text_wrong,
                "answertext_correct" => (string) $answer_obj->text_correct,
                "points" => (float) $answer_obj->points,
                "order" => (int) $idx+1
            ));
        }
        $result['correct_answers'] = $answers;

        $answers = array();
        $textarray = preg_split("/[\n\r]+/", $this->getErrorText());
        foreach ($textarray as $textidx => $text) {
            $items = preg_split("/\s+/", trim($text));
            foreach ($items as $idx => $item) {
                if (substr($item, 0, 1) == "#") {
                    $item = substr($item, 1);
                    
                    // #14115 - add position to correct answer
                    foreach ($result["correct_answers"] as $aidx => $answer) {
                        if ($answer["answertext_wrong"] == $item && !$answer["pos"]) {
                            $result["correct_answers"][$aidx]["pos"] = $this->getId() . "_" . $textidx . "_" . ($idx+1);
                            break;
                        }
                    }
                }
                array_push($answers, array(
                    "answertext" => (string) ilUtil::prepareFormOutput($item),
                    "order" => $this->getId() . "_" . $textidx . "_" . ($idx+1)
                ));
            }
            if ($textidx != sizeof($textarray)-1) {
                array_push($answers, array(
                        "answertext" => "###",
                        "order" => $this->getId() . "_" . $textidx . "_" . ($idx+2)
                    ));
            }
        }
        $result['answers'] = $answers;

        $mobs = ilObjMediaObject::_getMobsOfObject("qpl:html", $this->getId());
        $result['mobs'] = $mobs;
        
        return json_encode($result);
    }

    /**
     * Get all available operations for a specific question
     *
     * @param string $expression
     *
     * @internal param string $expression_type
     * @return array
     */
    public function getOperators($expression)
    {
        require_once "./Modules/TestQuestionPool/classes/class.ilOperatorsExpressionMapping.php";
        return ilOperatorsExpressionMapping::getOperatorsByExpression($expression);
    }

    /**
     * Get all available expression types for a specific question
     * @return array
     */
    public function getExpressionTypes()
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
    public function getUserQuestionResult($active_id, $pass)
    {
        /** @var ilDBInterface $ilDB */
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $result = new ilUserQuestionResult($this, $active_id, $pass);

        $data = $ilDB->queryF(
            "SELECT value1+1 as value1 FROM tst_solutions WHERE active_fi = %s AND pass = %s AND question_fi = %s AND step = (
				SELECT MAX(step) FROM tst_solutions WHERE active_fi = %s AND pass = %s AND question_fi = %s
			)",
            array("integer", "integer", "integer","integer", "integer", "integer"),
            array($active_id, $pass, $this->getId(), $active_id, $pass, $this->getId())
        );

        while ($row = $ilDB->fetchAssoc($data)) {
            $result->addKeyValue($row["value1"], $row["value1"]);
        }

        $points = $this->calculateReachedPoints($active_id, $pass);
        $max_points = $this->getMaximumPoints();

        $result->setReachedPercentage(($points/$max_points) * 100);

        return $result;
    }

    /**
     * If index is null, the function returns an array with all anwser options
     * Else it returns the specific answer option
     *
     * @param null|int $index
     *
     * @return array|ASS_AnswerSimple
     */
    public function getAvailableAnswerOptions($index = null)
    {
        $error_text_array = explode(' ', $this->errortext);
        
        if ($index !== null) {
            if (array_key_exists($index, $error_text_array)) {
                return $error_text_array[$index];
            }
            return null;
        } else {
            return $error_text_array;
        }
    }

    /**
     * @param $item
     * @param $class
     * @return string
     */
    private function getErrorTokenHtml($item, $class, $useLinkTags)
    {
        if ($useLinkTags) {
            return '<a class="' . $class . '" href="#">' . ($item == '&nbsp;' ? $item : ilUtil::prepareFormOutput($item)) . '</a>';
        }
        
        return '<span class="' . $class . '">' . ($item == '&nbsp;' ? $item : ilUtil::prepareFormOutput($item)) . '</span>';
    }
}
