<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Modules/TestQuestionPool/classes/class.assQuestion.php';
require_once './Modules/Test/classes/inc.AssessmentConstants.php';
require_once './Modules/TestQuestionPool/interfaces/interface.ilObjQuestionScoringAdjustable.php';
require_once './Modules/TestQuestionPool/interfaces/interface.iQuestionCondition.php';
require_once './Modules/TestQuestionPool/classes/class.ilUserQuestionResult.php';


class assLongMenu extends assQuestion implements ilObjQuestionScoringAdjustable
{
    private $answerType;
    private $long_menu_text;
    private $json_structure;
    private $ilDB;
    private $specificFeedbackSetting;
    private $minAutoComplete;
    private $identical_scoring;

    const ANSWER_TYPE_SELECT_VAL = 0;
    const ANSWER_TYPE_TEXT_VAL = 1;
    const GAP_PLACEHOLDER = 'Longmenu';
    const MIN_LENGTH_AUTOCOMPLETE = 3;
    const MAX_INPUT_FIELDS = 500;

    /** @var array */
    private $correct_answers = [];
    
    /** @var array */
    private $answers = [];

    public function __construct(
        $title = "",
        $comment = "",
        $author = "",
        $owner = -1,
        $question = ""
    ) {
        global $DIC;
        require_once 'Modules/TestQuestionPool/classes/feedback/class.ilAssConfigurableMultiOptionQuestionFeedback.php';
        $this->specificFeedbackSetting = ilAssConfigurableMultiOptionQuestionFeedback::FEEDBACK_SETTING_ALL;
        $this->minAutoComplete = self::MIN_LENGTH_AUTOCOMPLETE;
        parent::__construct($title, $comment, $author, $owner, $question);
        $this->ilDB = $DIC->database();
        $this->identical_scoring = 1;
    }
    
    /**
     * @return mixed
     */
    public function getAnswerType()
    {
        return $this->answerType;
    }

    /**
     * @param mixed $answerType
     */
    public function setAnswerType($answerType)
    {
        $this->answerType = $answerType;
    }
    
    /**
     * @return mixed
     */
    public function getCorrectAnswers()
    {
        return $this->correct_answers;
    }


    public function setCorrectAnswers($correct_answers)
    {
        $this->correct_answers = $correct_answers;
    }

    private function buildFolderName()
    {
        return ilUtil::getDataDir() . '/assessment/longMenuQuestion/' . $this->getId() . '/' ;
    }

    public function getAnswerTableName()
    {
        return "qpl_a_lome";
    }
    
    private function buildFileName($gap_id)
    {
        try {
            $this->assertDirExists();
            return $this->buildFolderName() . $gap_id . '.txt';
        } catch (ilException $e) {
        }
    }

    public function setLongMenuTextValue($long_menu_text = "")
    {
        $this->long_menu_text = $long_menu_text;
    }

    public function getLongMenuTextValue()
    {
        return $this->long_menu_text;
    }
        
    public function setAnswers($answers)
    {
        $this->answers = $answers;
    }

    public function getAnswers()
    {
        return $this->answers;
    }

    /**
     * @return mixed
     */
    public function getJsonStructure()
    {
        return $this->json_structure;
    }

    /**
     * @param mixed $json_structure
     */
    public function setJsonStructure($json_structure)
    {
        $this->json_structure = $json_structure;
    }
    
    public function setSpecificFeedbackSetting($specificFeedbackSetting)
    {
        $this->specificFeedbackSetting = $specificFeedbackSetting;
    }

    public function getSpecificFeedbackSetting()
    {
        return $this->specificFeedbackSetting;
    }

    public function setMinAutoComplete($minAutoComplete)
    {
        $this->minAutoComplete = $minAutoComplete;
    }

    public function getMinAutoComplete()
    {
        return $this->minAutoComplete ? $this->minAutoComplete  : self::MIN_LENGTH_AUTOCOMPLETE;
    }
    
    public function isComplete()
    {
        if (strlen($this->title)
            && $this->author
            && $this->long_menu_text
            && sizeof($this->answers) > 0
            && sizeof($this->correct_answers) > 0
            && $this->getPoints() > 0
        ) {
            return true;
        }
        return false;
    }
    
    public function saveToDb($original_id = "")
    {
        $this->saveQuestionDataToDb($original_id);
        $this->saveAdditionalQuestionDataToDb();
        $this->saveAnswerSpecificDataToDb();
        parent::saveToDb($original_id);
    }

    /**
     * @param ilPropertyFormGUI|null $form
     * @return bool
     */
    public function checkQuestionCustomPart($form = null)
    {
        $hidden_text_files = $this->getAnswers();
        $correct_answers = $this->getCorrectAnswers();
        $points = array();
        if (sizeof($correct_answers) == 0 || sizeof($hidden_text_files) == 0) {
            return false;
        }
        if (sizeof($correct_answers) != sizeof($hidden_text_files)) {
            return false;
        }
        foreach ($correct_answers as $key => $correct_answers_row) {
            if ($this->correctAnswerDoesNotExistInAnswerOptions($correct_answers_row, $hidden_text_files[$key])) {
                return false;
            }
            if (!is_array($correct_answers_row[0]) || sizeof($correct_answers_row[0]) == 0) {
                return false;
            }
            if ($correct_answers_row[1] > 0) {
                array_push($points, $correct_answers_row[1]);
            }
        }
        if (sizeof($correct_answers) != sizeof($points)) {
            return false;
        }
        
        foreach ($points as $row) {
            if ($row <= 0) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param $answers
     * @param $answer_options
     * @return bool
     */
    private function correctAnswerDoesNotExistInAnswerOptions($answers, $answer_options)
    {
        foreach ($answers[0] as $key => $answer) {
            if (!in_array($answer, $answer_options)) {
                return true;
            }
        }
        return false;
    }


    /**
     * Returns the maximum points, a learner can reach answering the question
     *
     * @access public
     * @see $points
     */
    public function getMaximumPoints()
    {
        $sum = 0;
        $points = $this->getCorrectAnswers();
        if ($points) {
            foreach ($points as $add) {
                $sum += $add[1];
            }
        }
        return $sum;
    }
    
    public function saveAdditionalQuestionDataToDb()
    {
        // save additional data
        $this->ilDB->manipulateF(
            "DELETE FROM " . $this->getAdditionalTableName() . " WHERE question_fi = %s",
            array( "integer" ),
            array( $this->getId() )
        );
        $this->ilDB->manipulateF(
            "INSERT INTO " . $this->getAdditionalTableName(
            ) . " (question_fi, long_menu_text, feedback_setting, min_auto_complete, identical_scoring) VALUES (%s, %s, %s, %s, %s)",
            array( "integer", "text", "integer", "integer", "integer"),
            array(
                $this->getId(),
                $this->getLongMenuTextValue(),
                (int) $this->getSpecificFeedbackSetting(),
                (int) $this->getMinAutoComplete(),
                (int) $this->getIdenticalScoring()
            )
        );

        $this->createFileFromArray();
    }

    public function saveAnswerSpecificDataToDb()
    {
        $this->clearAnswerSpecificDataFromDb($this->getId());
        $type_array = $this->getAnswerType();
        $points = 0;
        foreach ($this->getCorrectAnswers() as $gap_number => $gap) {
            foreach ($gap[0] as $position => $answer) {
                if ($type_array == null) {
                    $type = $gap[2];
                } else {
                    $type = $type_array[$gap_number];
                }
                $this->db->replace(
                    $this->getAnswerTableName(),
                    array(
                                'question_fi' => array('integer', (int) $this->getId()),
                                'gap_number' => array('integer', (int) $gap_number),
                                'position' => array('integer', (int) $position)
                        ),
                    array(
                                'answer_text' => array('text', $answer),
                                'points' => array('float', $gap[1]),
                                'type' => array('integer', (int) $type)
                        )
                );
            }
            $points += $gap[1];
        }
        $this->setPoints($points);
    }
    
    private function createFileFromArray()
    {
        $array = $this->getAnswers();
        $this->clearFolder();
        foreach ($array as $gap => $values) {
            $file_content = '';
            if (is_array($values)) {
                foreach ($values as $key => $value) {
                    $file_content .= $value . "\n";
                }
                $file_content = rtrim($file_content, "\n");
                $file = fopen($this->buildFileName($gap), "w");
                fwrite($file, $file_content);
                fclose($file);
            }
        }
    }

    private function createArrayFromFile()
    {
        $files = glob($this->buildFolderName() . '*.txt');
        
        if ($files === false) {
            $files = array();
        }
        
        $answers = array();

        foreach ($files as $file) {
            $gap = str_replace('.txt', '', basename($file));
            $answers[(int) $gap] = explode("\n", file_get_contents($file));
        }
        // Sort by gap keys, to ensure the numbers are in ascending order.
        // Glob will report the keys in files order like 0, 1, 10, 11, 2,...
        // json_encoding the array with keys in order 0,1,10,11,2,.. will create
        // a json_object instead of a list when keys are numeric, sorted and start with 0
        ksort($answers);
        $this->setAnswers($answers);
        return $answers;
    }
    
    private function clearFolder($let_folder_exists = true)
    {
        ilUtil::delDir($this->buildFolderName(), $let_folder_exists);
    }
    
    private function assertDirExists()
    {
        $folder_name = $this->buildFolderName();
        if (!ilUtil::makeDirParents($folder_name)) {
            throw new ilException('Cannot create export directory');
        }

        if (
            !is_dir($folder_name) ||
            !is_readable($folder_name) ||
            !is_writable($folder_name)
        ) {
            throw new ilException('Cannot create export directory');
        }
    }
    
    public function loadFromDb($question_id)
    {
        $result = $this->ilDB->queryF(
            "SELECT qpl_questions.*, " . $this->getAdditionalTableName() . ".* FROM qpl_questions LEFT JOIN " . $this->getAdditionalTableName() . " ON " . $this->getAdditionalTableName() . ".question_fi = qpl_questions.question_id WHERE qpl_questions.question_id = %s",
            array("integer"),
            array($question_id)
        );
        if ($result->numRows() == 1) {
            $data = $this->ilDB->fetchAssoc($result);
            $this->setId($question_id);
            $this->setObjId($data["obj_fi"]);
            $this->setNrOfTries($data['nr_of_tries']);
            $this->setTitle($data["title"]);
            $this->setComment($data["description"]);
            $this->setOriginalId($data["original_id"]);
            $this->setAuthor($data["author"]);
            $this->setPoints($data["points"]);
            $this->setIdenticalScoring($data["identical_scoring"]);
            $this->setOwner($data["owner"]);
            include_once("./Services/RTE/classes/class.ilRTE.php");
            $this->setQuestion(ilRTE::_replaceMediaObjectImageSrc($data['question_text'], 1));
            $this->setEstimatedWorkingTime(substr($data["working_time"], 0, 2), substr($data["working_time"], 3, 2), substr($data["working_time"], 6, 2));
            $this->setLongMenuTextValue(ilRTE::_replaceMediaObjectImageSrc($data['long_menu_text'], 1));
            $this->loadCorrectAnswerData($question_id);
            $this->setMinAutoComplete($data["min_auto_complete"]);
            if (isset($data['feedback_setting'])) {
                $this->setSpecificFeedbackSetting((int) $data['feedback_setting']);
            }
            
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

        $this->loadCorrectAnswerData($question_id);
        $this->createArrayFromFile();
        parent::loadFromDb($question_id);
    }

    private function loadCorrectAnswerData($question_id)
    {
        $res = $this->db->queryF(
            "SELECT * FROM {$this->getAnswerTableName()} WHERE question_fi = %s ORDER BY gap_number, position ASC",
            array('integer'),
            array($question_id)
        );
        
        $correct_answers = array();
        while ($data = $this->ilDB->fetchAssoc($res)) {
            $correct_answers[$data['gap_number']][0][$data['position']] = rtrim($data['answer_text']);
            $correct_answers[$data['gap_number']][1] = $data['points'];
            $correct_answers[$data['gap_number']][2] = $data['type'];
        }
        $this->setJsonStructure(json_encode($correct_answers));
        $this->setCorrectAnswers($correct_answers);
    }

    public function getCorrectAnswersForQuestionSolution($question_id)
    {
        $correct_answers = array();
        $res = $this->db->queryF(
            'SELECT gap_number, answer_text FROM  ' . $this->getAnswerTableName() . ' WHERE question_fi = %s',
            array('integer'),
            array($question_id)
        );
        while ($data = $this->ilDB->fetchAssoc($res)) {
            if (array_key_exists($data['gap_number'], $correct_answers)) {
                $correct_answers[$data['gap_number']] .= ' ' . $this->lng->txt("or") . ' ';
                $correct_answers[$data['gap_number']] .= rtrim($data['answer_text']);
            } else {
                $correct_answers[$data['gap_number']] .= rtrim($data['answer_text']);
            }
        }
        return $correct_answers;
    }

    private function getCorrectAnswersForGap($question_id, $gap_id)
    {
        $correct_answers = array();
        $res = $this->db->queryF(
            'SELECT answer_text FROM  ' . $this->getAnswerTableName() . ' WHERE question_fi = %s AND gap_number = %s',
            array('integer', 'integer'),
            array($question_id, $gap_id)
        );
        while ($data = $this->ilDB->fetchAssoc($res)) {
            $correct_answers[] = rtrim($data['answer_text']);
        }
        return $correct_answers;
    }
    
    private function getPointsForGap($question_id, $gap_id)
    {
        $points = 0;
        $res = $this->db->queryF(
            'SELECT points FROM  ' . $this->getAnswerTableName() . ' WHERE question_fi = %s AND gap_number = %s GROUP BY gap_number, points',
            array('integer', 'integer'),
            array($question_id, $gap_id)
        );
        while ($data = $this->ilDB->fetchAssoc($res)) {
            $points = $data['points'];
        }
        return $points;
    }

    
    public function getAnswersObject()
    {
        return json_encode($this->createArrayFromFile());
    }
    
    public function getCorrectAnswersAsJson()
    {
        $this->loadCorrectAnswerData($this->getId());
        return $this->getJsonStructure();
    }
    
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
        
        $clone->copyPageOfQuestion($this_id);
        $clone->copyXHTMLMediaObjectsOfQuestion($this_id);
        $clone->onDuplicate($thisObjId, $this_id, $clone->getObjId(), $clone->getId());

        return $clone->id;
    }

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
        
        $clone->copyPageOfQuestion($original_id);
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
        $clone->copyPageOfQuestion($sourceQuestionId);
        $clone->copyXHTMLMediaObjectsOfQuestion($sourceQuestionId);

        $clone->onCopy($sourceParentId, $sourceQuestionId, $clone->getObjId(), $clone->getId());

        return $clone->id;
    }
    

    /**
     * Returns the points, a learner has reached answering the question.
     * The points are calculated from the given answers.
     *
     * @param integer $active_id
     * @param integer $pass
     * @param boolean $returndetails (deprecated !!)
     *
     * @throws ilTestException
     * @return integer/array $points/$details (array $details is deprecated !!)
     */
    public function calculateReachedPoints($active_id, $pass = null, $authorizedSolution = true, $returndetails = false)
    {
        if ($returndetails) {
            throw new ilTestException('return details not implemented for ' . __METHOD__);
        }

        $found_values = array();
        if (is_null($pass)) {
            $pass = $this->getSolutionMaxPass($active_id);
        }
        $result = $this->getCurrentSolutionResultSet($active_id, $pass, $authorizedSolution);
        while ($data = $this->ilDB->fetchAssoc($result)) {
            $found_values[(int) $data['value1']] = $data['value2'];
        }

        $points = $this->calculateReachedPointsForSolution($found_values, $active_id);

        return $points;
    }

    protected function calculateReachedPointsForSolution($found_values, $active_id = 0)
    {
        $points = 0;
        $solution_values_text = array();
        foreach ($found_values as $key => $answer) {
            if ($answer != '') {
                $correct_answers = $this->getCorrectAnswersForGap($this->id, $key);
                if (in_array($answer, $correct_answers)) {
                    $points_gap = $this->getPointsForGap($this->id, $key);
                    if (!$this->getIdenticalScoring()) {
                        // check if the same solution text was already entered
                        if ((in_array($answer, $solution_values_text)) && ($points > 0)) {
                            $points_gap = 0;
                        }
                    }
                    $points += $points_gap;
                    array_push($solution_values_text, $answer);
                }
            }
        }
        return $points;
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
        if (is_null($pass)) {
            include_once "./Modules/Test/classes/class.ilObjTest.php";
            $pass = ilObjTest::_getPass($active_id);
        }
        
        $entered_values = 0;

        $this->getProcessLocker()->executeUserSolutionUpdateLockOperation(function () use (&$entered_values, $active_id, $pass, $authorized) {
            $this->removeCurrentSolution($active_id, $pass, $authorized);

            foreach ($this->getSolutionSubmit() as $val1 => $val2) {
                $value = ilUtil::stripSlashes($val2, false);
                if (strlen($value)) {
                    $this->saveCurrentSolution($active_id, $pass, $val1, $value, $authorized);
                    $entered_values++;
                }
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

    // fau: testNav - overridden function lookupForExistingSolutions (specific for long menu question: ignore unselected values)
    /**
     * Lookup if an authorized or intermediate solution exists
     * @param 	int 		$activeId
     * @param 	int 		$pass
     * @return 	array		['authorized' => bool, 'intermediate' => bool]
     */
    public function lookupForExistingSolutions($activeId, $pass)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $return = array(
            'authorized' => false,
            'intermediate' => false
        );

        $query = "
			SELECT authorized, COUNT(*) cnt
			FROM tst_solutions
			WHERE active_fi = " . $ilDB->quote($activeId, 'integer') . "
			AND question_fi = " . $ilDB->quote($this->getId(), 'integer') . "
			AND pass = " . $ilDB->quote($pass, 'integer') . "
			AND value2 <> '-1'
		";

        if ($this->getStep() !== null) {
            $query .= " AND step = " . $ilDB->quote((int) $this->getStep(), 'integer') . " ";
        }

        $query .= "
			GROUP BY authorized
		";

        $result = $ilDB->query($query);

        while ($row = $ilDB->fetchAssoc($result)) {
            if ($row['authorized']) {
                $return['authorized'] = $row['cnt'] > 0;
            } else {
                $return['intermediate'] = $row['cnt'] > 0;
            }
        }
        return $return;
    }
    // fau.


    public function getSolutionSubmit()
    {
        $solutionSubmit = array();
        $answer = ilUtil::stripSlashesRecursive($_POST['answer']);
        
        foreach ($answer as $key => $value) {
            $solutionSubmit[$key] = $value;
        }

        return $solutionSubmit;
    }
    
    protected function savePreviewData(ilAssQuestionPreviewSession $previewSession)
    {
        if (array_key_exists('answer', $_POST)) {
            $previewSession->setParticipantsSolution($_POST['answer']);
        } else {
            $previewSession->setParticipantsSolution(null);
        }
    }
    
    /**
     * Returns the question type of the question
     *
     * @return integer The question type of the question
     */
    public function getQuestionType()
    {
        return "assLongMenu";
    }

    /**
     * Returns the name of the additional question data table in the database
     *
     * @return string The additional table name
     */
    public function getAdditionalTableName()
    {
        return 'qpl_qst_lome';
    }

    /**
     * Collects all text in the question which could contain media objects
     * which were created with the Rich Text Editor
     */
    public function getRTETextWithMediaObjects()
    {
        return parent::getRTETextWithMediaObjects() . $this->getLongMenuTextValue();
    }

    /**
     * {@inheritdoc}
     */
    public function setExportDetailsXLS($worksheet, $startrow, $active_id, $pass)
    {
        parent::setExportDetailsXLS($worksheet, $startrow, $active_id, $pass);

        $solution = $this->getSolutionValues($active_id, $pass);

        $i = 1;
        foreach ($this->getCorrectAnswers() as $gap_index => $gap) {
            $worksheet->setCell($startrow + $i, 0, $this->lng->txt('assLongMenu') . " $i");
            $worksheet->setBold($worksheet->getColumnCoord(0) . ($startrow + $i));
            foreach ($solution as $solutionvalue) {
                if ($gap_index == $solutionvalue["value1"]) {
                    switch ($gap[2]) {
                        case self::ANSWER_TYPE_SELECT_VAL:
                            $value = $solutionvalue["value2"];
                            if ($value == -1) {
                                $value = '';
                            }
                            $worksheet->setCell($startrow + $i, 1, $value);
                            break;
                        case self::ANSWER_TYPE_TEXT_VAL:
                            $worksheet->setCell($startrow + $i, 1, $solutionvalue["value2"]);
                            break;
                    }
                }
            }
            $i++;
        }

        return $startrow + $i + 1;
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
        $result = new ilUserQuestionResult($this, $active_id, $pass);

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
     * @return array|ASS_AnswerSimple
     */
    public function getAvailableAnswerOptions($index = null)
    {
        return $this->createArrayFromFile();
    }
    
    public function isShuffleAnswersEnabled()
    {
        return false;
    }

    public function clearAnswerSpecificDataFromDb($question_id)
    {
        $this->ilDB->manipulateF(
            'DELETE FROM ' . $this->getAnswerTableName() . ' WHERE question_fi = %s',
            array( 'integer' ),
            array( $question_id )
        );
    }

    public function delete($original_id)
    {
        parent::delete($original_id);
        $this->clearFolder(false);
    }
    
    /**
     * @param ilAssSelfAssessmentMigrator $migrator
     */
    protected function lmMigrateQuestionTypeSpecificContent(ilAssSelfAssessmentMigrator $migrator)
    {
        $this->setLongMenuTextValue($migrator->migrateToLmContent($this->getLongMenuTextValue()));
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
        $replaced_quesiton_text = $this->getLongMenuTextValue();
        $result['question'] = $this->formatSAQuestion($this->getQuestion());
        $result['lmtext'] = $this->formatSAQuestion($replaced_quesiton_text);
        $result['nr_of_tries'] = (int) $this->getNrOfTries();
        $result['shuffle'] = (bool) $this->getShuffle();
        $result['feedback'] = array(
            'onenotcorrect' => $this->formatSAQuestion($this->feedbackOBJ->getGenericFeedbackTestPresentation($this->getId(), false)),
            'allcorrect' => $this->formatSAQuestion($this->feedbackOBJ->getGenericFeedbackTestPresentation($this->getId(), true))
        );
        
        $mobs = ilObjMediaObject::_getMobsOfObject("qpl:html", $this->getId());
        $result['answers'] = $this->getAnswers();
        $result['correct_answers'] = $this->getCorrectAnswers();
        $result['mobs'] = $mobs;
        return json_encode($result);
    }

    public function getIdenticalScoring()
    {
        return ($this->identical_scoring) ? 1 : 0;
    }

    /**
     * @param $a_identical_scoring
     */
    public function setIdenticalScoring($a_identical_scoring)
    {
        $this->identical_scoring = ($a_identical_scoring) ? 1 : 0;
    }
}
