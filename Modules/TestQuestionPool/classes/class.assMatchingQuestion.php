<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Modules/TestQuestionPool/classes/class.assQuestion.php';
require_once './Modules/Test/classes/inc.AssessmentConstants.php';
require_once './Modules/TestQuestionPool/interfaces/interface.ilObjQuestionScoringAdjustable.php';
require_once './Modules/TestQuestionPool/interfaces/interface.ilObjAnswerScoringAdjustable.php';
require_once './Modules/TestQuestionPool/interfaces/interface.iQuestionCondition.php';
require_once './Modules/TestQuestionPool/classes/class.ilUserQuestionResult.php';

/**
 * Class for matching questions
 *
 * assMatchingQuestion is a class for matching questions.
 *
 * @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @author		Björn Heyser <bheyser@databay.de>
 * @author		Maximilian Becker <mbecker@databay.de>
 *
 * @version		$Id$
 *
 * @ingroup		ModulesTestQuestionPool
 */
class assMatchingQuestion extends assQuestion implements ilObjQuestionScoringAdjustable, ilObjAnswerScoringAdjustable, iQuestionCondition
{
    /**
    * The possible matching pairs of the matching question
    *
    * $matchingpairs is an array of the predefined matching pairs of the matching question
    *
    * @var array
    */
    public $matchingpairs;
    /**
    * Type of matching question
    *
    * There are two possible types of matching questions: Matching terms and definitions (=1)
    * and Matching terms and pictures (=0).
    *
    * @var integer
    */
    public $matching_type;

    /**
    * The terms of the matching question
    *
    * @var array
    */
    protected $terms;

    protected $definitions;
    /**
    * Maximum thumbnail geometry
    *
    * @var integer
    */
    public $thumb_geometry = 100;

    /**
    * Minimum element height
    *
    * @var integer
    */
    public $element_height;

    const MATCHING_MODE_1_ON_1 = '1:1';
    const MATCHING_MODE_N_ON_N = 'n:n';

    protected $matchingMode = self::MATCHING_MODE_1_ON_1;

    /**
     * assMatchingQuestion constructor
     *
     * The constructor takes possible arguments an creates an instance of the assMatchingQuestion object.
     *
     * @param string  $title    A title string to describe the question
     * @param string  $comment  A comment string to describe the question
     * @param string  $author   A string containing the name of the questions author
     * @param integer $owner    A numerical ID to identify the owner/creator
     * @param string  $question The question string of the matching question
     * @param int     $matching_type
     *
     * @return \assMatchingQuestion
     */
    public function __construct(
        $title = "",
        $comment = "",
        $author = "",
        $owner = -1,
        $question = "",
        $matching_type = MT_TERMS_DEFINITIONS
    ) {
        parent::__construct($title, $comment, $author, $owner, $question);
        $this->matchingpairs = array();
        $this->matching_type = $matching_type;
        $this->terms = array();
        $this->definitions = array();
    }

    /**
    * Returns true, if a matching question is complete for use
    *
    * @return boolean True, if the matching question is complete for use, otherwise false
    */
    public function isComplete()
    {
        if (strlen($this->title)
            && $this->author
            && $this->question
            && count($this->matchingpairs)
            && $this->getMaximumPoints() > 0
        ) {
            return true;
        }
        return false;
    }

    /**
     * Saves a assMatchingQuestion object to a database
     *
     * @param string $original_id
     *
     */
    public function saveToDb($original_id = "")
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $this->saveQuestionDataToDb($original_id);
        $this->saveAdditionalQuestionDataToDb();
        $this->saveAnswerSpecificDataToDb($ilDB);


        parent::saveToDb($original_id);
    }

    public function saveAnswerSpecificDataToDb()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        // delete old terms
        $ilDB->manipulateF(
            "DELETE FROM qpl_a_mterm WHERE question_fi = %s",
            array( 'integer' ),
            array( $this->getId() )
        );

        // delete old definitions
        $ilDB->manipulateF(
            "DELETE FROM qpl_a_mdef WHERE question_fi = %s",
            array( 'integer' ),
            array( $this->getId() )
        );

        $termids = array();
        // write terms
        foreach ($this->terms as $key => $term) {
            $next_id = $ilDB->nextId('qpl_a_mterm');
            $ilDB->insert('qpl_a_mterm', array(
                'term_id' => array('integer', $next_id),
                'question_fi' => array('integer', $this->getId()),
                'picture' => array('text', $term->picture),
                'term' => array('text', $term->text),
                'ident' => array('integer', $term->identifier)
            ));
            $termids[$term->identifier] = $next_id;
        }

        $definitionids = array();
        // write definitions
        foreach ($this->definitions as $key => $definition) {
            $next_id = $ilDB->nextId('qpl_a_mdef');
            $ilDB->insert('qpl_a_mdef', array(
                'def_id' => array('integer', $next_id),
                'question_fi' => array('integer', $this->getId()),
                'picture' => array('text', $definition->picture),
                'definition' => array('text', $definition->text),
                'ident' => array('integer', $definition->identifier)
            ));
            $definitionids[$definition->identifier] = $next_id;
        }

        $ilDB->manipulateF(
            "DELETE FROM qpl_a_matching WHERE question_fi = %s",
            array( 'integer' ),
            array( $this->getId() )
        );
        $matchingpairs = $this->getMatchingPairs();
        foreach ($matchingpairs as $key => $pair) {
            $next_id = $ilDB->nextId('qpl_a_matching');
            $ilDB->manipulateF(
                "INSERT INTO qpl_a_matching (answer_id, question_fi, points, term_fi, definition_fi) VALUES (%s, %s, %s, %s, %s)",
                array( 'integer', 'integer', 'float', 'integer', 'integer' ),
                array(
                                    $next_id,
                                    $this->getId(),
                                    $pair->points,
                                    $termids[$pair->term->identifier],
                                    $definitionids[$pair->definition->identifier]
                                )
            );
        }

        $this->rebuildThumbnails();
    }

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

        $ilDB->insert($this->getAdditionalTableName(), array(
            'question_fi' => array('integer', $this->getId()),
            'shuffle' => array('text', $this->shuffle),
            'matching_type' => array('text', $this->matching_type),
            'thumb_geometry' => array('integer', $this->getThumbGeometry()),
            'matching_mode' => array('text', $this->getMatchingMode())
        ));
    }

    /**
    * Loads a assMatchingQuestion object from a database
    *
    * @param object $db A pear DB object
    * @param integer $question_id A unique key which defines the multiple choice test in the database
    */
    public function loadFromDb($question_id)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $query = "
			SELECT		qpl_questions.*,
						{$this->getAdditionalTableName()}.*
			FROM		qpl_questions
			LEFT JOIN	{$this->getAdditionalTableName()}
			ON			{$this->getAdditionalTableName()}.question_fi = qpl_questions.question_id
			WHERE		qpl_questions.question_id = %s
		";

        $result = $ilDB->queryF(
            $query,
            array('integer'),
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
            $this->setThumbGeometry($data["thumb_geometry"]);
            $this->setShuffle($data["shuffle"]);
            $this->setMatchingMode($data['matching_mode'] === null ? self::MATCHING_MODE_1_ON_1 : $data['matching_mode']);
            $this->setEstimatedWorkingTime(substr($data["working_time"], 0, 2), substr($data["working_time"], 3, 2), substr($data["working_time"], 6, 2));
            
            try {
                $this->setAdditionalContentEditingMode($data['add_cont_edit_mode']);
            } catch (ilTestQuestionPoolException $e) {
            }
        }

        $termids = array();
        $result = $ilDB->queryF(
            "SELECT * FROM qpl_a_mterm WHERE question_fi = %s ORDER BY term_id ASC",
            array('integer'),
            array($question_id)
        );
        include_once "./Modules/TestQuestionPool/classes/class.assAnswerMatchingTerm.php";
        $this->terms = array();
        if ($result->numRows() > 0) {
            while ($data = $ilDB->fetchAssoc($result)) {
                $term = new assAnswerMatchingTerm($data['term'], $data['picture'], $data['ident']);
                array_push($this->terms, $term);
                $termids[$data['term_id']] = $term;
            }
        }

        $definitionids = array();
        $result = $ilDB->queryF(
            "SELECT * FROM qpl_a_mdef WHERE question_fi = %s ORDER BY def_id ASC",
            array('integer'),
            array($question_id)
        );
        include_once "./Modules/TestQuestionPool/classes/class.assAnswerMatchingDefinition.php";
        $this->definitions = array();
        if ($result->numRows() > 0) {
            while ($data = $ilDB->fetchAssoc($result)) {
                $definition = new assAnswerMatchingDefinition($data['definition'], $data['picture'], $data['ident']);
                array_push($this->definitions, $definition);
                $definitionids[$data['def_id']] = $definition;
            }
        }

        $this->matchingpairs = array();
        $result = $ilDB->queryF(
            "SELECT * FROM qpl_a_matching WHERE question_fi = %s ORDER BY answer_id",
            array('integer'),
            array($question_id)
        );
        include_once "./Modules/TestQuestionPool/classes/class.assAnswerMatchingPair.php";
        if ($result->numRows() > 0) {
            while ($data = $ilDB->fetchAssoc($result)) {
                array_push($this->matchingpairs, new assAnswerMatchingPair($termids[$data['term_fi']], $definitionids[$data['definition_fi']], $data['points']));
            }
        }
        parent::loadFromDb($question_id);
    }

    
    /**
    * Duplicates an assMatchingQuestion
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
        $clone->duplicateImages($this_id, $thisObjId, $clone->getId(), $testObjId);

        $clone->onDuplicate($thisObjId, $this_id, $clone->getObjId(), $clone->getId());
        
        return $clone->id;
    }

    /**
    * Copies an assMatchingQuestion
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
        $clone->copyImages($original_id, $source_questionpool_id);
        
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
        $clone->copyImages($sourceQuestionId, $sourceParentId);

        $clone->onCopy($sourceParentId, $sourceQuestionId, $clone->getObjId(), $clone->getId());

        return $clone->id;
    }

    public function duplicateImages($question_id, $objectId = null)
    {
        global $DIC;
        $ilLog = $DIC['ilLog'];
        $imagepath = $this->getImagePath();
        $imagepath_original = str_replace("/$this->id/images", "/$question_id/images", $imagepath);
        
        if ((int) $objectId > 0) {
            $imagepath_original = str_replace("/$this->obj_id/", "/$objectId/", $imagepath_original);
        }
        
        foreach ($this->terms as $term) {
            if (strlen($term->picture)) {
                $filename = $term->picture;
                if (!file_exists($imagepath)) {
                    ilUtil::makeDirParents($imagepath);
                }
                if (!@copy($imagepath_original . $filename, $imagepath . $filename)) {
                    $ilLog->write("matching question image could not be duplicated: $imagepath_original$filename");
                }
                if (@file_exists($imagepath_original . $this->getThumbPrefix() . $filename)) {
                    if (!@copy($imagepath_original . $this->getThumbPrefix() . $filename, $imagepath . $this->getThumbPrefix() . $filename)) {
                        $ilLog->write("matching question image thumbnail could not be duplicated: $imagepath_original" . $this->getThumbPrefix() . $filename);
                    }
                }
            }
        }
        foreach ($this->definitions as $definition) {
            if (strlen($definition->picture)) {
                $filename = $definition->picture;
                if (!file_exists($imagepath)) {
                    ilUtil::makeDirParents($imagepath);
                }
                if (!@copy($imagepath_original . $filename, $imagepath . $filename)) {
                    $ilLog->write("matching question image could not be duplicated: $imagepath_original$filename");
                }
                if (@file_exists($imagepath_original . $this->getThumbPrefix() . $filename)) {
                    if (!@copy($imagepath_original . $this->getThumbPrefix() . $filename, $imagepath . $this->getThumbPrefix() . $filename)) {
                        $ilLog->write("matching question image thumbnail could not be duplicated: $imagepath_original" . $this->getThumbPrefix() . $filename);
                    }
                }
            }
        }
    }

    public function copyImages($question_id, $source_questionpool)
    {
        global $DIC;
        $ilLog = $DIC['ilLog'];
        
        $imagepath = $this->getImagePath();
        $imagepath_original = str_replace("/$this->id/images", "/$question_id/images", $imagepath);
        $imagepath_original = str_replace("/$this->obj_id/", "/$source_questionpool/", $imagepath_original);
        foreach ($this->terms as $term) {
            if (strlen($term->picture)) {
                if (!file_exists($imagepath)) {
                    ilUtil::makeDirParents($imagepath);
                }
                $filename = $term->picture;
                if (!@copy($imagepath_original . $filename, $imagepath . $filename)) {
                    $ilLog->write("matching question image could not be copied: $imagepath_original$filename");
                }
                if (!@copy($imagepath_original . $this->getThumbPrefix() . $filename, $imagepath . $this->getThumbPrefix() . $filename)) {
                    $ilLog->write("matching question image thumbnail could not be copied: $imagepath_original" . $this->getThumbPrefix() . $filename);
                }
            }
        }
        foreach ($this->definitions as $definition) {
            if (strlen($definition->picture)) {
                $filename = $definition->picture;
                if (!file_exists($imagepath)) {
                    ilUtil::makeDirParents($imagepath);
                }

                if (assQuestion::isFileAvailable($imagepath_original . $filename)) {
                    copy($imagepath_original . $filename, $imagepath . $filename);
                } else {
                    $ilLog->write("matching question image could not be copied: $imagepath_original$filename");
                }
                
                if (assQuestion::isFileAvailable($imagepath_original . $this->getThumbPrefix() . $filename)) {
                    copy($imagepath_original . $this->getThumbPrefix() . $filename, $imagepath . $this->getThumbPrefix() . $filename);
                } else {
                    $ilLog->write("matching question image thumbnail could not be copied: $imagepath_original" . $this->getThumbPrefix() . $filename);
                }
            }
        }
    }

    /**
    * Inserts a matching pair for an matching choice question. The students have to fill in an order for the matching pair.
    * The matching pair is an ASS_AnswerMatching object that will be created and assigned to the array $this->matchingpairs.
    *
    * @param integer $position The insert position in the matching pairs array
    * @param object $term A matching term
    * @param object $definition A matching definition
    * @param double $points The points for selecting the matching pair (even negative points can be used)
    * @see $matchingpairs
    */
    public function insertMatchingPair($position, $term = null, $definition = null, $points = 0.0)
    {
        include_once "./Modules/TestQuestionPool/classes/class.assAnswerMatchingPair.php";
        include_once "./Modules/TestQuestionPool/classes/class.assAnswerMatchingTerm.php";
        include_once "./Modules/TestQuestionPool/classes/class.assAnswerMatchingDefinition.php";
        if (is_null($term)) {
            $term = new assAnswerMatchingTerm();
        }
        if (is_null($definition)) {
            $definition = new assAnswerMatchingDefinition();
        }
        $pair = new assAnswerMatchingPair($term, $definition, $points);
        if ($position < count($this->matchingpairs)) {
            $part1 = array_slice($this->matchingpairs, 0, $position);
            $part2 = array_slice($this->matchingpairs, $position);
            $this->matchingpairs = array_merge($part1, array($pair), $part2);
        } else {
            array_push($this->matchingpairs, $pair);
        }
    }

    /**
     * Adds an matching pair for an matching choice question. The students have to fill in an order for the
     * matching pair. The matching pair is an ASS_AnswerMatching object that will be created and assigned to the
     * array $this->matchingpairs.
     *
     * @param assAnswerMatchingTerm|null		$term       A matching term
     * @param assAnswerMatchingDefinition|null	$definition A matching definition
     * @param float 							$points     The points for selecting the matching pair, incl. negative.
     *
     * @see $matchingpairs
     */
    public function addMatchingPair($term = null, $definition = null, $points = 0.0)
    {
        require_once './Modules/TestQuestionPool/classes/class.assAnswerMatchingPair.php';
        require_once './Modules/TestQuestionPool/classes/class.assAnswerMatchingTerm.php';
        require_once './Modules/TestQuestionPool/classes/class.assAnswerMatchingDefinition.php';
        if (is_null($term)) {
            $term = new assAnswerMatchingTerm();
        }
        if (is_null($definition)) {
            $definition = new assAnswerMatchingDefinition();
        }
        $pair = new assAnswerMatchingPair($term, $definition, $points);
        array_push($this->matchingpairs, $pair);
    }

    /**
    * Returns a term with a given identifier
    */
    public function getTermWithIdentifier($a_identifier)
    {
        foreach ($this->terms as $term) {
            if ($term->identifier == $a_identifier) {
                return $term;
            }
        }
        return null;
    }

    /**
    * Returns a definition with a given identifier
    */
    public function getDefinitionWithIdentifier($a_identifier)
    {
        foreach ($this->definitions as $definition) {
            if ($definition->identifier == $a_identifier) {
                return $definition;
            }
        }
        return null;
    }

    /**
    * Returns a matching pair with a given index. The index of the first
    * matching pair is 0, the index of the second matching pair is 1 and so on.
    *
    * @param integer $index A nonnegative index of the n-th matching pair
    * @return object ASS_AnswerMatching-Object
    * @see $matchingpairs
    */
    public function getMatchingPair($index = 0)
    {
        if ($index < 0) {
            return null;
        }
        if (count($this->matchingpairs) < 1) {
            return null;
        }
        if ($index >= count($this->matchingpairs)) {
            return null;
        }
        return $this->matchingpairs[$index];
    }

    /**
    * Deletes a matching pair with a given index. The index of the first
    * matching pair is 0, the index of the second matching pair is 1 and so on.
    *
    * @param integer $index A nonnegative index of the n-th matching pair
    * @see $matchingpairs
    */
    public function deleteMatchingPair($index = 0)
    {
        if ($index < 0) {
            return;
        }
        if (count($this->matchingpairs) < 1) {
            return;
        }
        if ($index >= count($this->matchingpairs)) {
            return;
        }
        unset($this->matchingpairs[$index]);
        $this->matchingpairs = array_values($this->matchingpairs);
    }

    /**
    * Deletes all matching pairs
    * @see $matchingpairs
    */
    public function flushMatchingPairs()
    {
        $this->matchingpairs = array();
    }

    /**
    * Returns the number of matching pairs
    *
    * @return integer The number of matching pairs of the matching question
    * @see $matchingpairs
    */
    public function getMatchingPairCount()
    {
        return count($this->matchingpairs);
    }

    /**
    * Returns the terms of the matching question
    *
    * @return array An array containing the terms
    * @see $terms
    */
    public function getTerms()
    {
        return $this->terms;
    }
    
    /**
    * Returns the definitions of the matching question
    *
    * @return array An array containing the definitions
    * @see $terms
    */
    public function getDefinitions()
    {
        return $this->definitions;
    }
    
    /**
    * Returns the number of terms
    *
    * @return integer The number of terms
    * @see $terms
    */
    public function getTermCount()
    {
        return count($this->terms);
    }
    
    /**
    * Returns the number of definitions
    *
    * @return integer The number of definitions
    * @see $definitions
    */
    public function getDefinitionCount()
    {
        return count($this->definitions);
    }
    
    /**
    * Adds a term
    *
    * @param string $term The text of the term
    * @see $terms
    */
    public function addTerm($term)
    {
        array_push($this->terms, $term);
    }
    
    /**
    * Adds a definition
    *
    * @param object $definition The definition
    * @see $definitions
    */
    public function addDefinition($definition)
    {
        array_push($this->definitions, $definition);
    }
    
    /**
    * Inserts a term
    *
    * @param string $term The text of the term
    * @see $terms
    */
    public function insertTerm($position, $term = null)
    {
        if (is_null($term)) {
            include_once "./Modules/TestQuestionPool/classes/class.assAnswerMatchingTerm.php";
            $term = new assAnswerMatchingTerm();
        }
        if ($position < count($this->terms)) {
            $part1 = array_slice($this->terms, 0, $position);
            $part2 = array_slice($this->terms, $position);
            $this->terms = array_merge($part1, array($term), $part2);
        } else {
            array_push($this->terms, $term);
        }
    }
    
    /**
    * Inserts a definition
    *
    * @param object $definition The definition
    * @see $definitions
    */
    public function insertDefinition($position, $definition = null)
    {
        if (is_null($definition)) {
            include_once "./Modules/TestQuestionPool/classes/class.assAnswerMatchingDefinition.php";
            $definition = new assAnswerMatchingDefinition();
        }
        if ($position < count($this->definitions)) {
            $part1 = array_slice($this->definitions, 0, $position);
            $part2 = array_slice($this->definitions, $position);
            $this->definitions = array_merge($part1, array($definition), $part2);
        } else {
            array_push($this->definitions, $definition);
        }
    }
    
    /**
    * Deletes all terms
    * @see $terms
    */
    public function flushTerms()
    {
        $this->terms = array();
    }

    /**
    * Deletes all definitions
    * @see $definitions
    */
    public function flushDefinitions()
    {
        $this->definitions = array();
    }

    /**
    * Deletes a term
    *
    * @param string $term_id The id of the term to delete
    * @see $terms
    */
    public function deleteTerm($position)
    {
        unset($this->terms[$position]);
        $this->terms = array_values($this->terms);
    }

    /**
    * Deletes a definition
    *
    * @param integer $position The position of the definition in the definition array
    * @see $definitions
    */
    public function deleteDefinition($position)
    {
        unset($this->definitions[$position]);
        $this->definitions = array_values($this->definitions);
    }

    /**
    * Sets a specific term
    *
    * @param string $term The text of the term
    * @param string $index The index of the term
    * @see $terms
    */
    public function setTerm($term, $index)
    {
        $this->terms[$index] = $term;
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
        
        $found_values = array();
        if (is_null($pass)) {
            $pass = $this->getSolutionMaxPass($active_id);
        }
        $result = $this->getCurrentSolutionResultSet($active_id, $pass, $authorizedSolution);
        while ($data = $ilDB->fetchAssoc($result)) {
            if (strcmp($data["value1"], "") != 0) {
                if (!isset($found_values[$data['value2']])) {
                    $found_values[$data['value2']] = array();
                }
                
                $found_values[$data['value2']][] = $data['value1'];
            }
        }
        
        $points = $this->calculateReachedPointsForSolution($found_values);

        return $points;
    }

    /**
     * Calculates and Returns the maximum points, a learner can reach answering the question
     */
    public function getMaximumPoints()
    {
        $points = 0;

        foreach ($this->getMaximumScoringMatchingPairs() as $pair) {
            $points += $pair->points;
        }

        return $points;
    }

    public function getMaximumScoringMatchingPairs()
    {
        if ($this->getMatchingMode() == self::MATCHING_MODE_N_ON_N) {
            return $this->getPositiveScoredMatchingPairs();
        } elseif ($this->getMatchingMode() == self::MATCHING_MODE_1_ON_1) {
            return $this->getMostPositiveScoredUniqueTermMatchingPairs();
        }

        return array();
    }

    private function getPositiveScoredMatchingPairs()
    {
        $matchingPairs = array();

        foreach ($this->matchingpairs as $pair) {
            if ($pair->points <= 0) {
                continue;
            }

            $matchingPairs[] = $pair;
        }

        return $matchingPairs;
    }

    private function getMostPositiveScoredUniqueTermMatchingPairs()
    {
        $matchingPairsByDefinition = array();

        foreach ($this->matchingpairs as $pair) {
            if ($pair->points <= 0) {
                continue;
            }

            $defId = $pair->definition->identifier;

            if (!isset($matchingPairsByDefinition[$defId])) {
                $matchingPairsByDefinition[$defId] = $pair;
            } elseif ($pair->points > $matchingPairsByDefinition[$defId]->points) {
                $matchingPairsByDefinition[$defId] = $pair;
            }
        }

        return $matchingPairsByDefinition;
    }
    
    /**
     * @param array $valuePairs
     * @return array $indexedValues
     */
    public function fetchIndexedValuesFromValuePairs(array $valuePairs)
    {
        $indexedValues = array();
        
        foreach ($valuePairs as $valuePair) {
            if (!isset($indexedValues[$valuePair['value2']])) {
                $indexedValues[$valuePair['value2']] = array();
            }
            
            $indexedValues[$valuePair['value2']][] = $valuePair['value1'];
        }
        
        return $indexedValues;
    }

    /**
    * Returns the encrypted save filename of a matching picture
    * Images are saved with an encrypted filename to prevent users from
    * cheating by guessing the solution from the image filename
    *
    * @param string $filename Original filename
    * @return string Encrypted filename
    */
    public function getEncryptedFilename($filename)
    {
        $extension = "";
        if (preg_match("/.*\\.(\\w+)$/", $filename, $matches)) {
            $extension = $matches[1];
        }
        return md5($filename) . "." . $extension;
    }

    public function removeTermImage($index)
    {
        $term = $this->terms[$index];
        if (is_object($term)) {
            $this->deleteImagefile($term->picture);
            $term->picture = null;
        }
    }
    
    public function removeDefinitionImage($index)
    {
        $definition = $this->definitions[$index];
        if (is_object($definition)) {
            $this->deleteImagefile($definition->picture);
            $definition->picture = null;
        }
    }
    

    /**
    * Deletes an imagefile from the system if the file is deleted manually
    *
    * @param string $filename Image file filename
    * @return boolean Success
    */
    public function deleteImagefile($filename)
    {
        $deletename = $filename;
        $result = @unlink($this->getImagePath() . $deletename);
        $result = $result & @unlink($this->getImagePath() . $this->getThumbPrefix() . $deletename);
        return $result;
    }

    /**
    * Sets the image file and uploads the image to the object's image directory.
    *
    * @param string $image_filename Name of the original image file
    * @param string $image_tempfilename Name of the temporary uploaded image file
    * @return integer An errorcode if the image upload fails, 0 otherwise
    * @access public
    */
    public function setImageFile($image_tempfilename, $image_filename, $previous_filename = '')
    {
        $result = true;
        if (strlen($image_tempfilename)) {
            $image_filename = str_replace(" ", "_", $image_filename);
            $imagepath = $this->getImagePath();
            if (!file_exists($imagepath)) {
                ilUtil::makeDirParents($imagepath);
            }
            $savename = $image_filename;
            if (!ilUtil::moveUploadedFile($image_tempfilename, $savename, $imagepath . $savename)) {
                $result = false;
            } else {
                // create thumbnail file
                $thumbpath = $imagepath . $this->getThumbPrefix() . $savename;
                ilUtil::convertImage($imagepath . $savename, $thumbpath, "JPEG", $this->getThumbGeometry());
            }
            if ($result && (strcmp($image_filename, $previous_filename) != 0) && (strlen($previous_filename))) {
                $this->deleteImagefile($previous_filename);
            }
        }
        return $result;
    }

    private function fetchSubmittedMatchingsFromPost()
    {
        $postData = $_POST['matching'][$this->getId()];

        $matchings = array();

        foreach ($this->getDefinitions() as $definition) {
            if (isset($postData[$definition->identifier])) {
                foreach ($this->getTerms() as $term) {
                    if (isset($postData[$definition->identifier][$term->identifier])) {
                        if (!is_array($postData[$definition->identifier])) {
                            $postData[$definition->identifier] = array();
                        }

                        $matchings[$definition->identifier][] = $term->identifier;
                    }
                }
            }
        }

        return $matchings;
    }

    private function checkSubmittedMatchings($submittedMatchings)
    {
        if ($this->getMatchingMode() == self::MATCHING_MODE_N_ON_N) {
            return true;
        }

        $handledTerms = array();

        foreach ($submittedMatchings as $definition => $terms) {
            if (count($terms) > 1) {
                ilUtil::sendFailure($this->lng->txt("multiple_matching_values_selected"), true);
                return false;
            }

            foreach ($terms as $i => $term) {
                if (isset($handledTerms[$term])) {
                    ilUtil::sendFailure($this->lng->txt("duplicate_matching_values_selected"), true);
                    return false;
                }

                $handledTerms[$term] = $term;
            }
        }

        return true;
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

        $submittedMatchings = $this->fetchSubmittedMatchingsFromPost();
        $submittedMatchingsValid = $this->checkSubmittedMatchings($submittedMatchings);

        $matchingsExist = false;

        if ($submittedMatchingsValid) {
            if (is_null($pass)) {
                include_once "./Modules/Test/classes/class.ilObjTest.php";
                $pass = ilObjTest::_getPass($active_id);
            }

            $this->getProcessLocker()->executeUserSolutionUpdateLockOperation(function () use (&$matchingsExist, $submittedMatchings, $active_id, $pass, $authorized) {
                $this->removeCurrentSolution($active_id, $pass, $authorized);

                foreach ($submittedMatchings as $definition => $terms) {
                    foreach ($terms as $i => $term) {
                        $this->saveCurrentSolution($active_id, $pass, $term, $definition, $authorized);
                        $matchingsExist = true;
                    }
                }
            });

            $saveWorkingDataResult = true;
        } else {
            $saveWorkingDataResult = false;
        }

        include_once("./Modules/Test/classes/class.ilObjAssessmentFolder.php");
        if (ilObjAssessmentFolder::_enabledAssessmentLogging()) {
            if ($matchingsExist) {
                assQuestion::logAction($this->lng->txtlng("assessment", "log_user_entered_values", ilObjAssessmentFolder::_getLogLanguage()), $active_id, $this->getId());
            } else {
                assQuestion::logAction($this->lng->txtlng("assessment", "log_user_not_entered_values", ilObjAssessmentFolder::_getLogLanguage()), $active_id, $this->getId());
            }
        }

        return $saveWorkingDataResult;
    }

    protected function savePreviewData(ilAssQuestionPreviewSession $previewSession)
    {
        $submittedMatchings = $this->fetchSubmittedMatchingsFromPost();

        if ($this->checkSubmittedMatchings($submittedMatchings)) {
            $previewSession->setParticipantsSolution($submittedMatchings);
        }
    }

    public function getRandomId()
    {
        mt_srand((double) microtime()*1000000);
        $random_number = mt_rand(1, 100000);
        $found = false;
        while ($found) {
            $found = false;
            foreach ($this->matchingpairs as $key => $pair) {
                if (($pair->term->identifier == $random_number) || ($pair->definition->identifier == $random_number)) {
                    $found = true;
                    $random_number++;
                }
            }
        }
        return $random_number;
    }

    /**
    * Sets the shuffle flag
    *
    * @param integer $shuffle A flag indicating whether the answers are shuffled or not
    * @see $shuffle
    */
    public function setShuffle($shuffle = true)
    {
        switch ($shuffle) {
            case 0:
            case 1:
            case 2:
            case 3:
                $this->shuffle = $shuffle;
                break;
            default:
                $this->shuffle = 1;
                break;
        }
    }

    /**
    * Returns the question type of the question
    *
    * @return integer The question type of the question
    */
    public function getQuestionType()
    {
        return "assMatchingQuestion";
    }
    
    /**
    * Returns the name of the additional question data table in the database
    *
    * @return string The additional table name
    */
    public function getAdditionalTableName()
    {
        return "qpl_qst_matching";
    }

    /**
    * Returns the name of the answer table in the database
    *
    * @return string The answer table name
    */
    public function getAnswerTableName()
    {
        return array("qpl_a_matching", "qpl_a_mterm");
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
    * Returns the matchingpairs array
    */
    public function &getMatchingPairs()
    {
        return $this->matchingpairs;
    }

    /**
     * {@inheritdoc}
     */
    public function setExportDetailsXLS($worksheet, $startrow, $active_id, $pass)
    {
        parent::setExportDetailsXLS($worksheet, $startrow, $active_id, $pass);

        $solutions = $this->getSolutionValues($active_id, $pass);

        $imagepath = $this->getImagePath();
        $i = 1;
        foreach ($solutions as $solution) {
            $matches_written = false;
            foreach ($this->getMatchingPairs() as $idx => $pair) {
                if (!$matches_written) {
                    $worksheet->setCell($startrow + $i, 1, $this->lng->txt("matches"));
                }
                $matches_written = true;
                if ($pair->definition->identifier == $solution["value2"]) {
                    if (strlen($pair->definition->text)) {
                        $worksheet->setCell($startrow + $i, 0, $pair->definition->text);
                    } else {
                        $worksheet->setCell($startrow + $i, 0, $pair->definition->picture);
                    }
                }
                if ($pair->term->identifier == $solution["value1"]) {
                    if (strlen($pair->term->text)) {
                        $worksheet->setCell($startrow + $i, 2, $pair->term->text);
                    } else {
                        $worksheet->setCell($startrow + $i, 2, $pair->term->picture);
                    }
                }
            }
            $i++;
        }

        return $startrow + $i + 1;
    }
    
    /**
    * Get the thumbnail geometry
    *
    * @return integer Geometry
    */
    public function getThumbGeometry()
    {
        return $this->thumb_geometry;
    }
    
    /**
    * Get the thumbnail geometry
    *
    * @return integer Geometry
    */
    public function getThumbSize()
    {
        return $this->getThumbGeometry();
    }
    
    /**
    * Set the thumbnail geometry
    *
    * @param integer $a_geometry Geometry
    */
    public function setThumbGeometry($a_geometry)
    {
        $this->thumb_geometry = ($a_geometry < 1) ? 100 : $a_geometry;
    }

    /**
    * Rebuild the thumbnail images with a new thumbnail size
    */
    public function rebuildThumbnails()
    {
        foreach ($this->terms as $term) {
            if (strlen($term->picture)) {
                $this->generateThumbForFile($this->getImagePath(), $term->picture);
            }
        }
        foreach ($this->definitions as $definition) {
            if (strlen($definition->picture)) {
                $this->generateThumbForFile($this->getImagePath(), $definition->picture);
            }
        }
    }
    
    public function getThumbPrefix()
    {
        return "thumb.";
    }
    
    protected function generateThumbForFile($path, $file)
    {
        $filename = $path . $file;
        if (@file_exists($filename)) {
            $thumbpath = $path . $this->getThumbPrefix() . $file;
            $path_info = @pathinfo($filename);
            $ext = "";
            switch (strtoupper($path_info['extension'])) {
                case 'PNG':
                    $ext = 'PNG';
                    break;
                case 'GIF':
                    $ext = 'GIF';
                    break;
                default:
                    $ext = 'JPEG';
                    break;
            }
            ilUtil::convertImage($filename, $thumbpath, $ext, $this->getThumbGeometry());
        }
    }

    /**
    * Returns a JSON representation of the question
    * TODO
    */
    public function toJSON()
    {
        $result = array();
        
        $result['id'] = (int) $this->getId();
        $result['type'] = (string) $this->getQuestionType();
        $result['title'] = (string) $this->getTitle();
        $result['question'] =  $this->formatSAQuestion($this->getQuestion());
        $result['nr_of_tries'] = (int) $this->getNrOfTries();
        $result['matching_mode'] = $this->getMatchingMode();
        $result['shuffle'] = true;
        $result['feedback'] = array(
            'onenotcorrect' => $this->formatSAQuestion($this->feedbackOBJ->getGenericFeedbackTestPresentation($this->getId(), false)),
            'allcorrect' => $this->formatSAQuestion($this->feedbackOBJ->getGenericFeedbackTestPresentation($this->getId(), true))
        );
        
        require_once 'Services/Randomization/classes/class.ilArrayElementShuffler.php';
        $this->setShuffler(new ilArrayElementShuffler());
        $seed = $this->getShuffler()->getSeed();
        
        $terms = array();
        $this->getShuffler()->setSeed($this->getShuffler()->buildSeedFromString($seed . 'terms'));
        foreach ($this->getShuffler()->shuffle($this->getTerms()) as $term) {
            $terms[] = array(
                "text" => $this->formatSAQuestion($term->text),
                "id" =>(int) $this->getId() . $term->identifier
            );
        }
        $result['terms'] = $terms;

        // alex 9.9.2010 as a fix for bug 6513 I added the question id
        // to the "def_id" in the array. The $pair->definition->identifier is not
        // unique, since it gets it value from the morder table field
        // this value is not changed, when a question is copied.
        // thus copying the same question on a page results in problems
        // when the second one (the copy) is answered.

        $definitions = array();
        $this->getShuffler()->setSeed($this->getShuffler()->buildSeedFromString($seed . 'definitions'));
        foreach ($this->getShuffler()->shuffle($this->getDefinitions()) as $def) {
            $definitions[] = array(
                "text" => $this->formatSAQuestion((string) $def->text),
                "id" => (int) $this->getId() . $def->identifier
            );
        }
        $result['definitions'] = $definitions;
        
        // #10353
        $matchings = array();
        foreach ($this->getMatchingPairs() as $pair) {
            // fau: fixLmMatchingPoints - ignore matching pairs with 0 or negative points
            if ($pair->points <= 0) {
                continue;
            }
            // fau.

            $pid = $pair->definition->identifier;
            if ($this->getMatchingMode() == self::MATCHING_MODE_N_ON_N) {
                $pid .= '::' . $pair->term->identifier;
            }
            
            if (!isset($matchings[$pid]) || $matchings[$pid]["points"] < $pair->points) {
                $matchings[$pid] = array(
                    "term_id" => (int) $this->getId() . $pair->term->identifier,
                    "def_id" => (int) $this->getId() . $pair->definition->identifier,
                    "points" => (int) $pair->points
                );
            }
        }
        
        $result['matchingPairs'] = array_values($matchings);
            
        $mobs = ilObjMediaObject::_getMobsOfObject("qpl:html", $this->getId());
        $result['mobs'] = $mobs;
        
        global $DIC;
        $lng = $DIC['lng'];
        $lng->loadLanguageModule('assessment');
        $result['reset_button_label'] = $lng->txt("reset_terms");

        return json_encode($result);
    }
    
    public function supportsJavascriptOutput()
    {
        return true;
    }

    public function supportsNonJsOutput()
    {
        return false;
    }

    public function setMatchingMode($matchingMode)
    {
        $this->matchingMode = $matchingMode;
    }

    public function getMatchingMode()
    {
        return $this->matchingMode;
    }

    /**
     * @param $found_values
     * @return int
     */
    protected function calculateReachedPointsForSolution($found_values)
    {
        $points = 0;
        foreach ($found_values as $definition => $terms) {
            foreach ($terms as $term) {
                foreach ($this->matchingpairs as $pair) {
                    if ($pair->definition->identifier == $definition && $pair->term->identifier == $term) {
                        $points += $pair->points;
                    }
                }
            }
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
            iQuestionCondition::NumericResultExpression,
            iQuestionCondition::MatchingResultExpression,
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
        /** @var ilDBInterface $ilDB */
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $result = new ilUserQuestionResult($this, $active_id, $pass);

        $data = $ilDB->queryF(
            "SELECT ident FROM qpl_a_mdef WHERE question_fi = %s ORDER BY def_id",
            array("integer"),
            array($this->getId())
        );

        $definitions = array();
        for ($index=1; $index <= $ilDB->numRows($data); ++$index) {
            $row = $ilDB->fetchAssoc($data);
            $definitions[$row["ident"]] = $index;
        }

        $data = $ilDB->queryF(
            "SELECT ident FROM qpl_a_mterm WHERE question_fi = %s ORDER BY term_id",
            array("integer"),
            array($this->getId())
        );

        $terms = array();
        for ($index=1; $index <= $ilDB->numRows($data); ++$index) {
            $row = $ilDB->fetchAssoc($data);
            $terms[$row["ident"]] = $index;
        }

        $maxStep = $this->lookupMaxStep($active_id, $pass);

        if ($maxStep !== null) {
            $data = $ilDB->queryF(
                "SELECT value1, value2 FROM tst_solutions WHERE active_fi = %s AND pass = %s AND question_fi = %s AND step = %s",
                array("integer", "integer", "integer","integer"),
                array($active_id, $pass, $this->getId(), $maxStep)
            );
        } else {
            $data = $ilDB->queryF(
                "SELECT value1, value2 FROM tst_solutions WHERE active_fi = %s AND pass = %s AND question_fi = %s",
                array("integer", "integer", "integer"),
                array($active_id, $pass, $this->getId())
            );
        }

        while ($row = $ilDB->fetchAssoc($data)) {
            if ($row["value1"] > 0) {
                $result->addKeyValue($definitions[$row["value2"]], $terms[$row["value1"]]);
            }
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
        if ($index !== null) {
            return $this->getMatchingPair($index);
        } else {
            return $this->getMatchingPairs();
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function afterSyncWithOriginal($origQuestionId, $dupQuestionId, $origParentObjId, $dupParentObjId)
    {
        parent::afterSyncWithOriginal($origQuestionId, $dupQuestionId, $origParentObjId, $dupParentObjId);

        $origImagePath = $this->buildImagePath($origQuestionId, $origParentObjId);
        $dupImagePath  = $this->buildImagePath($dupQuestionId, $dupParentObjId);

        ilUtil::delDir($origImagePath);
        if (is_dir($dupImagePath)) {
            ilUtil::makeDirParents($origImagePath);
            ilUtil::rCopy($dupImagePath, $origImagePath);
        }
    }
}
