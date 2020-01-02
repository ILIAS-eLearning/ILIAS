<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Modules/TestQuestionPool/classes/class.assQuestion.php';
require_once './Modules/Test/classes/inc.AssessmentConstants.php';
require_once './Modules/TestQuestionPool/interfaces/interface.ilObjQuestionScoringAdjustable.php';
require_once './Modules/TestQuestionPool/interfaces/interface.iQuestionCondition.php';
require_once './Modules/TestQuestionPool/classes/class.ilUserQuestionResult.php';

/**
 * Class for Java Applet Questions
 *
 * assJavaApplet is a class for Java Applet Questions.
 *
 * @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @author		Björn Heyser <bheyser@databay.de>
 * @author		Maximilian Becker <mbecker@databay.de>
 *
 * @version		$Id$
 *
 * @ingroup		ModulesTestQuestionPool
 */
class assJavaApplet extends assQuestion implements ilObjQuestionScoringAdjustable, iQuestionCondition
{
    /**
    * Java applet file name
    *
    * The file name of the java applet
    *
    * @var string
    */
    public $javaapplet_filename;

    /**
    * Java Applet code parameter
    *
    * Java Applet code parameter
    *
    * @var string
    */
    public $java_code;

    /**
    * Java Applet codebase parameter
    *
    * Java Applet codebase parameter
    *
    * @var string
    */
    public $java_codebase;

    /**
    * Java Applet archive parameter
    *
    * Java Applet archive parameter
    *
    * @var string
    */
    public $java_archive;

    /**
    * Java Applet width parameter
    *
    * Java Applet width parameter
    *
    * @var integer
    */
    public $java_width;

    /**
    * Java Applet height parameter
    *
    * Java Applet height parameter
    *
    * @var integer
    */
    public $java_height;

    /**
    * Additional java applet parameters
    *
    * Additional java applet parameters
    *
    * @var array
    */
    public $parameters;

    /**
     * assJavaApplet constructor
     *
     * The constructor takes possible arguments an creates an instance of the assJavaApplet object.
     *
     * @param string  $title    			A title string to describe the question.
     * @param string  $comment  			A comment string to describe the question.
     * @param string  $author   			A string containing the name of the questions author.
     * @param integer $owner    			A numerical ID to identify the owner/creator.
     * @param string  $question 			The question string of the multiple choice question.
     * @param string  $javaapplet_filename	Applet filename.
     *
     * @return \assJavaApplet
     */
    public function __construct(
        $title = "",
        $comment = "",
        $author = "",
        $owner = -1,
        $question = "",
        $javaapplet_filename = ""
    ) {
        parent::__construct($title, $comment, $author, $owner, $question);
        $this->javaapplet_filename = $javaapplet_filename;
        $this->parameters = array();
    }

    /**
     * Sets the applet parameters from a parameter string containing all parameters in a list
     *
     * Sets the applet parameters from a parameter string containing all parameters in a list
     *
     * @param string $params All applet parameters in a list
     */
    public function splitParams($params = "")
    {
        $params_array = explode("<separator>", $params);
        foreach ($params_array as $pair) {
            if (preg_match("/(.*?)\=(.*)/", $pair, $matches)) {
                switch ($matches[1]) {
                    case "java_code":
                        $this->java_code = $matches[2];
                        break;
                    case "java_codebase":
                        $this->java_codebase = $matches[2];
                        break;
                    case "java_archive":
                        $this->java_archive = $matches[2];
                        break;
                    case "java_width":
                        $this->java_width = $matches[2];
                        break;
                    case "java_height":
                        $this->java_height = $matches[2];
                        break;
                }
                if (preg_match("/param_name_(\d+)/", $matches[1], $found_key)) {
                    $this->parameters[$found_key[1]]["name"] = $matches[2];
                }
                if (preg_match("/param_value_(\d+)/", $matches[1], $found_key)) {
                    $this->parameters[$found_key[1]]["value"] = $matches[2];
                }
            }
        }
    }

    /**
     * Returns a string containing the applet parameters
     *
     * Returns a string containing the applet parameters. This is used for saving the applet data to database
     *
     * @return string All applet parameters
     */
    public function buildParams()
    {
        $params_array = array();
        if ($this->java_code) {
            array_push($params_array, "java_code=$this->java_code");
        }
        if ($this->java_codebase) {
            array_push($params_array, "java_codebase=$this->java_codebase");
        }
        if ($this->java_archive) {
            array_push($params_array, "java_archive=$this->java_archive");
        }
        if ($this->java_width) {
            array_push($params_array, "java_width=$this->java_width");
        }
        if ($this->java_height) {
            array_push($params_array, "java_height=$this->java_height");
        }
        foreach ($this->parameters as $key => $value) {
            array_push($params_array, "param_name_$key=" . $value["name"]);
            array_push($params_array, "param_value_$key=" . $value["value"]);
        }
        
        return join($params_array, "<separator>");
    }

    /**
     * Returns a string containing the additional applet parameters
     *
     * @return string All additional applet parameters
     */
    public function buildParamsOnly()
    {
        $params_array = array();
        if ($this->java_code) {
            array_push($params_array, "java_code=$this->java_code");
            array_push($params_array, "java_codebase=$this->java_codebase");
            array_push($params_array, "java_archive=$this->java_archive");
        }
        foreach ($this->parameters as $key => $value) {
            array_push($params_array, "param_name_$key=" . $value["name"]);
            array_push($params_array, "param_value_$key=" . $value["value"]);
        }
        return join($params_array, "<separator>");
    }

    /**
     * Returns true, if a imagemap question is complete for use
     *
     * @return boolean True, if the imagemap question is complete for use, otherwise false
     */
    public function isComplete()
    {
        if (strlen($this->title)
            && $this->author
            && $this->question
            && $this->javaapplet_filename
            && $this->java_width
            && $this->java_height
            && $this->getPoints() > 0
        ) {
            return true;
        } elseif (strlen($this->title)
            && $this->author
            && $this->question
            && $this->getJavaArchive()
            && $this->getJavaCodebase()
            && $this->java_width
            && $this->java_height
            && $this->getPoints() > 0
        ) {
            return true;
        }
        return false;
    }


    /**
     * Saves a assJavaApplet object to a database
     *
     * @param string $original_id
     *
     * @return mixed|void
     */
    public function saveToDb($original_id = "")
    {
        $this->saveQuestionDataToDb($original_id);
        $this->saveAdditionalQuestionDataToDb();
        parent::saveToDb($original_id);
    }

    public function saveAdditionalQuestionDataToDb()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $params = $this->buildParams();
        // save additional data
        $ilDB->manipulateF(
            "DELETE FROM " . $this->getAdditionalTableName() . " WHERE question_fi = %s",
            array( "integer" ),
            array( $this->getId() )
        );
        $ilDB->manipulateF(
            "INSERT INTO " . $this->getAdditionalTableName(
                                                            ) . " (question_fi, image_file, params) VALUES (%s, %s, %s)",
            array( "integer", "text", "text" ),
            array(
                                $this->getId(),
                                $this->javaapplet_filename,
                                $params
                            )
        );
    }

    /**
     * Loads a assJavaApplet object from a database
     *
     * @param integer $question_id A unique key which defines the multiple choice test in the database
     *
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
            $this->setNrOfTries($data['nr_of_tries']);
            $this->setTitle($data["title"]);
            $this->setComment($data["description"]);
            $this->setOriginalId($data["original_id"]);
            $this->setAuthor($data["author"]);
            $this->setPoints($data["points"]);
            $this->setOwner($data["owner"]);
            include_once("./Services/RTE/classes/class.ilRTE.php");
            $this->setQuestion(ilRTE::_replaceMediaObjectImageSrc($data["question_text"], 1));
            $this->setJavaAppletFilename($data["image_file"]);
            $this->splitParams($data["params"]);
            $this->setEstimatedWorkingTime(substr($data["working_time"], 0, 2), substr($data["working_time"], 3, 2), substr($data["working_time"], 6, 2));
            
            try {
                $this->setAdditionalContentEditingMode($data['add_cont_edit_mode']);
            } catch (ilTestQuestionPoolException $e) {
            }
        }
        parent::loadFromDb($question_id);
    }

    /**
    * Duplicates an assJavaApplet
    *
    * @access public
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
        // duplicate the image
        $clone->duplicateApplet($this_id, $thisObjId);

        $clone->onDuplicate($thisObjId, $this_id, $clone->getObjId(), $clone->getId());
        
        return $clone->id;
    }

    /**
    * Copies an assJavaApplet object
    *
    * Copies an assJavaApplet object
    *
    * @access public
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
        // duplicate the image
        $clone->copyApplet($original_id, $source_questionpool_id);
        
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
        // duplicate the image
        $clone->copyApplet($sourceQuestionId, $sourceParentId);

        $clone->onCopy($sourceParentId, $sourceQuestionId, $clone->getObjId(), $clone->getId());

        return $clone->id;
    }

    public function duplicateApplet($question_id, $objectId = null)
    {
        $javapath = $this->getJavaPath();
        $javapath_original = preg_replace("/([^\d])$this->id([^\d])/", "\${1}$question_id\${2}", $javapath);
        
        if ((int) $objectId > 0) {
            $javapath_original = str_replace("/$this->obj_id/", "/$objectId/", $javapath_original);
        }
        
        if (!file_exists($javapath)) {
            ilUtil::makeDirParents($javapath);
        }
        $filename = $this->getJavaAppletFilename();
        if (!copy($javapath_original . $filename, $javapath . $filename)) {
            print "java applet could not be duplicated!!!! ";
        }
    }

    public function copyApplet($question_id, $source_questionpool)
    {
        $javapath = $this->getJavaPath();
        $javapath_original = preg_replace("/([^\d])$this->id([^\d])/", "\${1}$question_id\${2}", $javapath);
        $javapath_original = str_replace("/$this->obj_id/", "/$source_questionpool/", $javapath_original);
        if (!file_exists($javapath)) {
            ilUtil::makeDirParents($javapath);
        }
        $filename = $this->getJavaAppletFilename();
        if (!copy($javapath_original . $filename, $javapath . $filename)) {
            print "java applet could not be copied!!!! ";
        }
    }

    /**
    * Returns the java applet code parameter
    *
    * Returns the java applet code parameter
    *
    * @return string java applet code parameter
    * @access public
    */
    public function getJavaCode()
    {
        return $this->java_code;
    }

    /**
    * Returns the java applet codebase parameter
    *
    * Returns the java applet codebase parameter
    *
    * @return string java applet codebase parameter
    * @access public
    */
    public function getJavaCodebase()
    {
        return $this->java_codebase;
    }

    /**
    * Returns the java applet archive parameter
    *
    * Returns the java applet archive parameter
    *
    * @return string java applet archive parameter
    * @access public
    */
    public function getJavaArchive()
    {
        return $this->java_archive;
    }

    /**
    * Sets the java applet code parameter
    *
    * Sets the java applet code parameter
    *
    * @param string java applet code parameter
    * @access public
    */
    public function setJavaCode($java_code = "")
    {
        $this->java_code = $java_code;
    }

    /**
    * Sets the java applet codebase parameter
    *
    * Sets the java applet codebase parameter
    *
    * @param string java applet codebase parameter
    * @access public
    */
    public function setJavaCodebase($java_codebase = "")
    {
        $this->java_codebase = $java_codebase;
    }

    /**
    * Sets the java applet archive parameter
    *
    * Sets the java applet archive parameter
    *
    * @param string java applet archive parameter
    * @access public
    */
    public function setJavaArchive($java_archive = "")
    {
        $this->java_archive = $java_archive;
    }

    /**
    * Returns the java applet width parameter
    *
    * Returns the java applet width parameter
    *
    * @return integer java applet width parameter
    * @access public
    */
    public function getJavaWidth()
    {
        return $this->java_width;
    }

    /**
     * Sets the java applet width parameter
     *
     * Sets the java applet width parameter
     *
     * @param string $java_width applet width parameter
     */
    public function setJavaWidth($java_width = "")
    {
        $this->java_width = $java_width;
    }

    /**
    * Returns the java applet height parameter
    *
    * Returns the java applet height parameter
    *
    * @return integer java applet height parameter
    * @access public
    */
    public function getJavaHeight()
    {
        return $this->java_height;
    }

    /**
    * Sets the java applet height parameter
    *
    * Sets the java applet height parameter
    *
    * @param integer java applet height parameter
    * @access public
    */
    public function setJavaHeight($java_height = "")
    {
        $this->java_height = $java_height;
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
    
    // hey: prevPassSolutions - bypass intermediate solution requests and deligate
    //							to own implementation for requests to authorized solutions
    public function getSolutionValues($active_id, $pass = null, $authorized = true)
    {
        if (!$authorized) {
            return array();
        }
        
        return $this->getSolutionValuesRegardlessOfAuthorization($active_id, $pass);
    }
    
    public function getSolutionValuesRegardlessOfAuthorization($active_id, $pass = null)
    {
        // - similar to getSolutionValues in general
        // - does not consider "step" in any kind
        // - returns a customized associative array
        // - is the original implementation for qtype
        return $this->getReachedInformation($active_id, $pass);
    }
    // hey.

    /**
     * Returns the evaluation data, a learner has entered to answer the question
     *
     * @param      $active_id
     * @param null $pass
     *
     * @return array
     */
    public function getReachedInformation($active_id, $pass = null)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        
        $found_values = array();
        if (is_null($pass)) {
            $pass = $this->getSolutionMaxPass($active_id);
        }
        $result = $ilDB->queryF(
            "SELECT * FROM tst_solutions WHERE active_fi = %s AND question_fi = %s AND pass = %s",
            array('integer','integer','integer'),
            array($active_id, $this->getId(), $pass)
        );
        $counter = 1;
        $user_result = array();
        while ($data = $ilDB->fetchAssoc($result)) {
            $true = 0;
            if ($data["points"] > 0) {
                $true = 1;
            }
            $solution = array(
                "order" => $counter,
                "points" => $data["points"],
                "true" => $true,
                "value1" => $data["value1"],
                "value2" => $data["value2"],
            );
            $counter++;
            array_push($user_result, $solution);
        }
        return $user_result;
    }

    /**
    * Adds a new parameter value to the parameter list
    *
    * @param string $name The name of the parameter value
    * @param string $value The value of the parameter value
    * @access public
    * @see $parameters
    */
    public function addParameter($name = "", $value = "")
    {
        $index = $this->getParameterIndex($name);
        if ($index > -1) {
            $this->parameters[$index] = array("name" => $name, "value" => $value);
        } else {
            array_push($this->parameters, array("name" => $name, "value" => $value));
        }
    }

    public function addParameterAtIndex($index = 0, $name = "", $value = "")
    {
        if (array_key_exists($index, $this->parameters)) {
            // insert parameter
            $newparams = array();
            for ($i = 0; $i < $index; $i++) {
                array_push($newparams, $this->parameters[$i]);
            }
            array_push($newparams, array($name, $value));
            for ($i = $index; $i < count($this->parameters); $i++) {
                array_push($newparams, $this->parameters[$i]);
            }
            $this->parameters = $newparams;
        } else {
            array_push($this->parameters, array($name, $value));
        }
    }

    /**
    * Removes a parameter value from the parameter list
    *
    * @param integer $index The parameter index
    * @access public
    * @see $parameters
    */
    public function removeParameter($index)
    {
        if ($index < 0) {
            return;
        }
        if (count($this->parameters) < 1) {
            return;
        }
        if ($index >= count($this->parameters)) {
            return;
        }
        unset($this->parameters[$index]);
        $this->parameters = array_values($this->parameters);
    }

    /**
    * Returns the paramter at a given index
    *
    * @param intege $index The index value of the parameter
    * @return array The parameter at the given index
    * @access public
    * @see $parameters
    */
    public function getParameter($index)
    {
        if (($index < 0) or ($index >= count($this->parameters))) {
            return undef;
        }
        return $this->parameters[$index];
    }

    /**
    * Returns the index of an applet parameter
    *
    * @param string $name The name of the parameter value
    * @return integer The index of the applet parameter or -1 if the parameter wasn't found
    * @access private
    * @see $parameters
    */
    public function getParameterIndex($name)
    {
        foreach ($this->parameters as $key => $value) {
            if (array_key_exists($name, $value)) {
                return $key;
            }
        }
        return -1;
    }

    /**
    * Returns the number of additional applet parameters
    *
    * @return integer The number of additional applet parameters
    * @access public
    * @see $parameters
    */
    public function getParameterCount()
    {
        return count($this->parameters);
    }

    /**
    * Removes all applet parameters
    *
    * @access public
    * @see $parameters
    */
    public function flushParams()
    {
        $this->parameters = array();
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
        // nothing to save!

        //$this->getProcessLocker()->requestUserSolutionUpdateLock();
        // store in tst_solutions
        //$this->getProcessLocker()->releaseUserSolutionUpdateLock();
        
        return true;
    }

    protected function savePreviewData(ilAssQuestionPreviewSession $previewSession)
    {
        // nothing to save!

        return true;
    }

    /**
    * Gets the java applet file name
    *
    * @return string The java applet file of the assJavaApplet object
    * @access public
    * @see $javaapplet_filename
    */
    public function getJavaAppletFilename()
    {
        return $this->javaapplet_filename;
    }

    /**
     * Sets the java applet file name
     *
     * @param        $javaapplet_filename
     * @param string $javaapplet_tempfilename
     *
     * @see      $javaapplet_filename
     */
    public function setJavaAppletFilename($javaapplet_filename, $javaapplet_tempfilename = "")
    {
        if (!empty($javaapplet_filename)) {
            $this->javaapplet_filename = $javaapplet_filename;
        }
        if (!empty($javaapplet_tempfilename)) {
            $javapath = $this->getJavaPath();
            if (!file_exists($javapath)) {
                ilUtil::makeDirParents($javapath);
            }
            
            if (!ilUtil::moveUploadedFile($javaapplet_tempfilename, $javaapplet_filename, $javapath . $javaapplet_filename)) {
                $ilLog->write("ERROR: java applet question: java applet not uploaded: $javaapplet_filename");
            } else {
                $this->setJavaCodebase();
                $this->setJavaArchive();
            }
        }
    }
    
    public function deleteJavaAppletFilename()
    {
        @unlink($this->getJavaPath() . $this->getJavaAppletFilename());
        $this->javaapplet_filename = "";
    }

    /**
    * Returns the question type of the question
    *
    * @return integer The question type of the question
    */
    public function getQuestionType()
    {
        return "assJavaApplet";
    }

    /**
    * Returns the name of the additional question data table in the database
    *
    * @return string The additional table name
    */
    public function getAdditionalTableName()
    {
        return "qpl_qst_javaapplet";
    }

    /**
    * Collects all text in the question which could contain media objects
    * which were created with the Rich Text Editor
    */
    public function getRTETextWithMediaObjects()
    {
        return parent::getRTETextWithMediaObjects();
    }

    /**
     * {@inheritdoc}
     */
    public function setExportDetailsXLS($worksheet, $startrow, $active_id, $pass)
    {
        parent::setExportDetailsXLS($worksheet, $startrow, $active_id, $pass);

        $solutions = $this->getSolutionValues($active_id, $pass);

        $i = 1;
        foreach ($solutions as $solution) {
            $worksheet->setCell($startrow + $i, 1, $this->lng->txt("result") . " $i");
            if (strlen($solution["value1"])) {
                $worksheet->setCell($startrow + $i, 1, $solution["value1"]);
            }
            if (strlen($solution["value2"])) {
                $worksheet->setCell($startrow + $i, 2, $solution["value2"]);
            }
            $i++;
        }

        return $startrow + $i + 1;
    }

    public function isAutosaveable()
    {
        return false;
    }

    /**
     * Get all available operations for a specific question
     *
     * @param $expression
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
            iQuestionCondition::EmptyAnswerExpression,
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
        $result = new ilUserQuestionResult($this, $active_id, $pass);

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
        return array();
    }

    // fau: testNav - new function getTestQuestionConfig()
    /**
     * Get the test question configuration
     * @return ilTestQuestionConfig
     */
    // hey: refactored identifiers
    public function buildTestPresentationConfig()
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
