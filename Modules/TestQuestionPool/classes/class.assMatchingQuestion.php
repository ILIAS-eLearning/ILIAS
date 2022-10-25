<?php

declare(strict_types=1);

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

use ILIAS\Refinery\Random\Group as RandomGroup;
use ILIAS\Refinery\Random\Seed\RandomSeed;

require_once './Modules/Test/classes/inc.AssessmentConstants.php';

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
    private int $shufflemode = 0;

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
    * @var assAnswerMatchingTerm[]
    */
    protected array $terms = [];

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

    public const MATCHING_MODE_1_ON_1 = '1:1';
    public const MATCHING_MODE_N_ON_N = 'n:n';

    protected $matchingMode = self::MATCHING_MODE_1_ON_1;

    private RandomGroup $randomGroup;

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
     */
    public function __construct(
        $title = "",
        $comment = "",
        $author = "",
        $owner = -1,
        $question = "",
        $matching_type = MT_TERMS_DEFINITIONS
    ) {
        global $DIC;

        parent::__construct($title, $comment, $author, $owner, $question);
        $this->matchingpairs = array();
        $this->matching_type = $matching_type;
        $this->terms = array();
        $this->definitions = array();
        $this->randomGroup = $DIC->refinery()->random();
    }

    public function getShuffleMode(): int
    {
        return $this->shufflemode;
    }

    public function setShuffleMode(int $shuffle)
    {
        $this->shufflemode = $shuffle;
    }

    /**
    * Returns true, if a matching question is complete for use
    *
    * @return boolean True, if the matching question is complete for use, otherwise false
    */
    public function isComplete(): bool
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
    public function saveToDb($original_id = ""): void
    {
        if ($original_id == "") {
            $this->saveQuestionDataToDb();
        } else {
            $this->saveQuestionDataToDb($original_id);
        }

        $this->saveAdditionalQuestionDataToDb();
        $this->saveAnswerSpecificDataToDb();

        parent::saveToDb();
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
                'picture' => array('text', $term->getPicture()),
                'term' => array('text', $term->getText()),
                'ident' => array('integer', $term->getIdentifier())
            ));
            $termids[$term->getIdentifier()] = $next_id;
        }

        $definitionids = array();
        // write definitions
        foreach ($this->definitions as $key => $definition) {
            $next_id = $ilDB->nextId('qpl_a_mdef');
            $ilDB->insert('qpl_a_mdef', array(
                'def_id' => array('integer', $next_id),
                'question_fi' => array('integer', $this->getId()),
                'picture' => array('text', $definition->getPicture()),
                'definition' => array('text', $definition->getText()),
                'ident' => array('integer', $definition->getIdentifier())
            ));
            $definitionids[$definition->getIdentifier()] = $next_id;
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
                                    $pair->getPoints(),
                                    $termids[$pair->getTerm()->getIdentifier()],
                                    $definitionids[$pair->getDefinition()->getIdentifier()]
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
            'shuffle' => array('text', $this->getShuffleMode()),
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
    public function loadFromDb($question_id): void
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
            $this->setId((int)$question_id);
            $this->setObjId((int)$data["obj_fi"]);
            $this->setTitle((string) $data["title"]);
            $this->setComment((string) $data["description"]);
            $this->setOriginalId((int)$data["original_id"]);
            $this->setNrOfTries((int)$data['nr_of_tries']);
            $this->setAuthor($data["author"]);
            $this->setPoints((float)$data["points"]);
            $this->setOwner((int)$data["owner"]);
            include_once("./Services/RTE/classes/class.ilRTE.php");
            $this->setQuestion(ilRTE::_replaceMediaObjectImageSrc((string) $data["question_text"], 1));
            $this->setThumbGeometry((int)$data["thumb_geometry"]);
            $this->setShuffle($data["shuffle"] != '0');
            $this->setShuffleMode((int)$data['shuffle']);
            $this->setMatchingMode($data['matching_mode'] === null ? self::MATCHING_MODE_1_ON_1 : $data['matching_mode']);
            $this->setEstimatedWorkingTime(
                (int)substr($data["working_time"], 0, 2),
                (int)substr($data["working_time"], 3, 2),
                (int)substr($data["working_time"], 6, 2)
            );

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

        $termids = array();
        $result = $ilDB->queryF(
            "SELECT * FROM qpl_a_mterm WHERE question_fi = %s ORDER BY term_id ASC",
            array('integer'),
            array($question_id)
        );
        $this->terms = [];
        if ($result->numRows() > 0) {
            while ($data = $ilDB->fetchAssoc($result)) {
                $term = $this->createMatchingTerm($data['term'] ?? '', $data['picture'] ?? '', (int)$data['ident']);
                $this->terms[] = $term;
                $termids[$data['term_id']] = $term;
            }
        }

        $definitionids = array();
        $result = $ilDB->queryF(
            "SELECT * FROM qpl_a_mdef WHERE question_fi = %s ORDER BY def_id ASC",
            array('integer'),
            array($question_id)
        );

        $this->definitions = array();
        if ($result->numRows() > 0) {
            while ($data = $ilDB->fetchAssoc($result)) {
                $definition = $this->createMatchingDefinition($data['definition'] ?? '', $data['picture'] ?? '', (int)$data['ident']);
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
        if ($result->numRows() > 0) {
            while ($data = $ilDB->fetchAssoc($result)) {
                $pair = $this->createMatchingPair(
                    $termids[$data['term_fi']],
                    $definitionids[$data['definition_fi']],
                    (float)$data['points']
                );
                array_push($this->matchingpairs, $pair);
            }
        }
        parent::loadFromDb((int)$question_id);
    }


    /**
    * Duplicates an assMatchingQuestion
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
        $clone->duplicateImages($this_id, $thisObjId, $clone->getId(), $testObjId);

        $clone->onDuplicate($thisObjId, $this_id, $clone->getObjId(), $clone->getId());

        return $clone->id;
    }

    /**
    * Copies an assMatchingQuestion
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
        $clone->copyImages($original_id, $source_questionpool_id);

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
        $clone->copyImages($sourceQuestionId, $sourceParentId);

        $clone->onCopy($sourceParentId, $sourceQuestionId, $clone->getObjId(), $clone->getId());

        return $clone->id;
    }

    public function duplicateImages($question_id, $objectId = null): void
    {
        global $DIC;
        $ilLog = $DIC['ilLog'];
        $imagepath = $this->getImagePath();
        $imagepath_original = str_replace("/$this->id/images", "/$question_id/images", $imagepath);

        if ((int) $objectId > 0) {
            $imagepath_original = str_replace("/$this->obj_id/", "/$objectId/", $imagepath_original);
        }

        foreach ($this->terms as $term) {
            if (strlen($term->getPicture())) {
                $filename = $term->getPicture();
                if (!file_exists($imagepath)) {
                    ilFileUtils::makeDirParents($imagepath);
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
            if (strlen($definition->getPicture())) {
                $filename = $definition->getPicture();
                if (!file_exists($imagepath)) {
                    ilFileUtils::makeDirParents($imagepath);
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

    public function copyImages($question_id, $source_questionpool): void
    {
        global $DIC;
        $ilLog = $DIC['ilLog'];

        $imagepath = $this->getImagePath();
        $imagepath_original = str_replace("/$this->id/images", "/$question_id/images", $imagepath);
        $imagepath_original = str_replace("/$this->obj_id/", "/$source_questionpool/", $imagepath_original);
        foreach ($this->terms as $term) {
            if (strlen($term->getPicture())) {
                if (!file_exists($imagepath)) {
                    ilFileUtils::makeDirParents($imagepath);
                }
                $filename = $term->getPicture();
                if (!@copy($imagepath_original . $filename, $imagepath . $filename)) {
                    $ilLog->write("matching question image could not be copied: $imagepath_original$filename");
                }
                if (!@copy($imagepath_original . $this->getThumbPrefix() . $filename, $imagepath . $this->getThumbPrefix() . $filename)) {
                    $ilLog->write("matching question image thumbnail could not be copied: $imagepath_original" . $this->getThumbPrefix() . $filename);
                }
            }
        }
        foreach ($this->definitions as $definition) {
            if (strlen($definition->getPicture())) {
                $filename = $definition->getPicture();
                if (!file_exists($imagepath)) {
                    ilFileUtils::makeDirParents($imagepath);
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
    public function insertMatchingPair($position, $term = null, $definition = null, $points = 0.0): void
    {
        $pair = $this->createMatchingPair($term, $definition, $points);

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
    public function addMatchingPair(assAnswerMatchingTerm $term = null, assAnswerMatchingDefinition $definition = null, $points = 0.0): void
    {
        $pair = $this->createMatchingPair($term, $definition, $points);
        array_push($this->matchingpairs, $pair);
    }

    /**
    * Returns a term with a given identifier
    */
    public function getTermWithIdentifier($a_identifier)
    {
        foreach ($this->terms as $term) {
            if ($term->getIdentifier() == $a_identifier) {
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
            if ($definition->getIdentifier() == $a_identifier) {
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
    public function getMatchingPair($index = 0): ?object
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
    public function deleteMatchingPair($index = 0): void
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
    public function flushMatchingPairs(): void
    {
        $this->matchingpairs = array();
    }

    /**
    * Returns the number of matching pairs
    *
    * @return integer The number of matching pairs of the matching question
    * @see $matchingpairs
    */
    public function getMatchingPairCount(): int
    {
        return count($this->matchingpairs);
    }

    /**
     * Returns the terms of the matching question
     *
     * @return assAnswerMatchingTerm[] An array containing the terms
     * @see $terms
     */
    public function getTerms(): array
    {
        return $this->terms;
    }

    /**
    * Returns the definitions of the matching question
    *
    * @return array An array containing the definitions
    * @see $terms
    */
    public function getDefinitions(): array
    {
        return $this->definitions;
    }

    /**
    * Returns the number of terms
    *
    * @return integer The number of terms
    * @see $terms
    */
    public function getTermCount(): int
    {
        return count($this->terms);
    }

    /**
    * Returns the number of definitions
    *
    * @return integer The number of definitions
    * @see $definitions
    */
    public function getDefinitionCount(): int
    {
        return count($this->definitions);
    }

    public function addTerm(assAnswerMatchingTerm $term): void
    {
        $this->terms[] = $term;
    }

    /**
    * Adds a definition
    *
    * @param object $definition The definition
    * @see $definitions
    */
    public function addDefinition($definition): void
    {
        array_push($this->definitions, $definition);
    }

    /**
    * Inserts a term
    *
    * @param string $term The text of the term
    * @see $terms
    */
    public function insertTerm($position, assAnswerMatchingTerm $term = null): void
    {
        if (is_null($term)) {
            $term = $this->createMatchingTerm();
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
    public function insertDefinition($position, assAnswerMatchingDefinition $definition = null): void
    {
        if (is_null($definition)) {
            $definition = $this->createMatchingDefinition();
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
    public function flushTerms(): void
    {
        $this->terms = array();
    }

    /**
    * Deletes all definitions
    * @see $definitions
    */
    public function flushDefinitions(): void
    {
        $this->definitions = array();
    }

    /**
    * Deletes a term
    *
    * @param string $term_id The id of the term to delete
    * @see $terms
    */
    public function deleteTerm($position): void
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
    public function deleteDefinition($position): void
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
    public function setTerm($term, $index): void
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
    public function calculateReachedPoints($active_id, $pass = null, $authorizedSolution = true, $returndetails = false): float
    {
        if ($returndetails) {
            throw new ilTestException('return details not implemented for ' . __METHOD__);
        }

        global $DIC;
        $ilDB = $DIC['ilDB'];

        $found_values = [];
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
    public function getMaximumPoints(): float
    {
        $points = 0;

        foreach ($this->getMaximumScoringMatchingPairs() as $pair) {
            $points += $pair->getPoints();
        }

        return $points;
    }

    public function getMaximumScoringMatchingPairs(): array
    {
        if ($this->getMatchingMode() == self::MATCHING_MODE_N_ON_N) {
            return $this->getPositiveScoredMatchingPairs();
        } elseif ($this->getMatchingMode() == self::MATCHING_MODE_1_ON_1) {
            return $this->getMostPositiveScoredUniqueTermMatchingPairs();
        }

        return array();
    }

    private function getPositiveScoredMatchingPairs(): array
    {
        $matchingPairs = array();

        foreach ($this->matchingpairs as $pair) {
            if ($pair->getPoints() <= 0) {
                continue;
            }

            $matchingPairs[] = $pair;
        }

        return $matchingPairs;
    }

    private function getMostPositiveScoredUniqueTermMatchingPairs(): array
    {
        $matchingPairsByDefinition = array();

        foreach ($this->matchingpairs as $pair) {
            if ($pair->getPoints() <= 0) {
                continue;
            }

            $defId = $pair->getDefinition()->getIdentifier();

            if (!isset($matchingPairsByDefinition[$defId])) {
                $matchingPairsByDefinition[$defId] = $pair;
            } elseif ($pair->getPoints() > $matchingPairsByDefinition[$defId]->getPoints()) {
                $matchingPairsByDefinition[$defId] = $pair;
            }
        }

        return $matchingPairsByDefinition;
    }

    /**
     * @param array $valuePairs
     * @return array $indexedValues
     */
    public function fetchIndexedValuesFromValuePairs(array $valuePairs): array
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
    public function getEncryptedFilename($filename): string
    {
        $extension = "";
        if (preg_match("/.*\\.(\\w+)$/", $filename, $matches)) {
            $extension = $matches[1];
        }
        return md5($filename) . "." . $extension;
    }

    public function removeTermImage($index): void
    {
        $term = $this->terms[$index] ?? null;
        if (is_object($term)) {
            $this->deleteImagefile($term->getPicture());
            $term = $term->withPicture('');
        }
    }

    public function removeDefinitionImage($index): void
    {
        $definition = $this->definitions[$index] ?? null;
        if (is_object($definition)) {
            $this->deleteImagefile($definition->getPicture());
            $definition = $definition->withPicture('');
        }
    }


    /**
    * Deletes an imagefile from the system if the file is deleted manually
    *
    * @param string $filename Image file filename
    * @return boolean Success
    */
    public function deleteImagefile(string $filename): bool
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
                ilFileUtils::makeDirParents($imagepath);
            }
            $savename = $image_filename;
            if (!ilFileUtils::moveUploadedFile($image_tempfilename, $savename, $imagepath . $savename)) {
                $result = false;
            } else {
                // create thumbnail file
                $thumbpath = $imagepath . $this->getThumbPrefix() . $savename;
                ilShellUtil::convertImage($imagepath . $savename, $thumbpath, "JPEG", $this->getThumbGeometry());
            }
            if ($result && (strcmp($image_filename, $previous_filename) != 0) && (strlen($previous_filename))) {
                $this->deleteImagefile($previous_filename);
            }
        }
        return $result;
    }

    private function fetchSubmittedMatchingsFromPost(): array
    {
        $request = $this->dic->testQuestionPool()->internal()->request();
        $post = $request->getParsedBody();

        $matchings = array();
        if (array_key_exists('matching', $post)) {
            $postData = $post['matching'][$this->getId()];
            foreach ($this->getDefinitions() as $definition) {
                if (isset($postData[$definition->getIdentifier()])) {
                    foreach ($this->getTerms() as $term) {
                        if (isset($postData[$definition->getIdentifier()][$term->getIdentifier()])) {
                            if (!is_array($postData[$definition->getIdentifier()])) {
                                $postData[$definition->getIdentifier()] = array();
                            }
                            $matchings[$definition->getIdentifier()][] = $term->getIdentifier();
                        }
                    }
                }
            }
        }

        return $matchings;
    }

    private function checkSubmittedMatchings($submittedMatchings): bool
    {
        if ($this->getMatchingMode() == self::MATCHING_MODE_N_ON_N) {
            return true;
        }

        $handledTerms = array();

        foreach ($submittedMatchings as $definition => $terms) {
            if (count($terms) > 1) {
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt("multiple_matching_values_selected"), true);
                return false;
            }

            foreach ($terms as $i => $term) {
                if (isset($handledTerms[$term])) {
                    $this->tpl->setOnScreenMessage('failure', $this->lng->txt("duplicate_matching_values_selected"), true);
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
    public function saveWorkingData($active_id, $pass = null, $authorized = true): bool
    {
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

        return $saveWorkingDataResult;
    }

    protected function savePreviewData(ilAssQuestionPreviewSession $previewSession): void
    {
        $submittedMatchings = $this->fetchSubmittedMatchingsFromPost();

        if ($this->checkSubmittedMatchings($submittedMatchings)) {
            $previewSession->setParticipantsSolution($submittedMatchings);
        }
    }

    public function getRandomId(): int
    {
        mt_srand((float) microtime() * 1000000);
        $random_number = mt_rand(1, 100000);
        $found = false;
        while ($found) {
            $found = false;
            foreach ($this->matchingpairs as $key => $pair) {
                if (($pair->getTerm()->getIdentifier() == $random_number) || ($pair->getDefinition()->getIdentifier() == $random_number)) {
                    $found = true;
                    $random_number++;
                }
            }
        }
        return $random_number;
    }

    public function setShuffle($shuffle = true): void
    {
        $this->shuffle = (bool) $shuffle;
    }

    /**
    * Returns the question type of the question
    *
    * @return integer The question type of the question
    */
    public function getQuestionType(): string
    {
        return "assMatchingQuestion";
    }

    public function getAdditionalTableName(): string
    {
        return "qpl_qst_matching";
    }

    public function getAnswerTableName(): array
    {
        return array("qpl_a_matching", "qpl_a_mterm");
    }

    /**
    * Collects all text in the question which could contain media objects
    * which were created with the Rich Text Editor
    */
    public function getRTETextWithMediaObjects(): string
    {
        return parent::getRTETextWithMediaObjects();
    }

    /**
    * Returns the matchingpairs array
    */
    public function &getMatchingPairs(): array
    {
        return $this->matchingpairs;
    }

    /**
     * {@inheritdoc}
     */
    public function setExportDetailsXLS(ilAssExcelFormatHelper $worksheet, int $startrow, int $active_id, int $pass): int
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
                if ($pair->getDefinition()->getIdentifier() == $solution["value2"]) {
                    if (strlen($pair->getDefinition()->getText())) {
                        $worksheet->setCell($startrow + $i, 0, $pair->getDefinition()->getText());
                    } else {
                        $worksheet->setCell($startrow + $i, 0, $pair->getDefinition()->getPicture());
                    }
                }
                if ($pair->getTerm()->getIdentifier() == $solution["value1"]) {
                    if (strlen($pair->getTerm()->getText())) {
                        $worksheet->setCell($startrow + $i, 2, $pair->getTerm()->getText());
                    } else {
                        $worksheet->setCell($startrow + $i, 2, $pair->getTerm()->getPicture());
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
    public function getThumbGeometry(): int
    {
        return $this->thumb_geometry;
    }

    /**
    * Get the thumbnail geometry
    *
    * @return integer Geometry
    */
    public function getThumbSize(): int
    {
        return $this->getThumbGeometry();
    }

    /**
    * Set the thumbnail geometry
    *
    * @param integer $a_geometry Geometry
    */
    public function setThumbGeometry(int $a_geometry): void
    {
        $this->thumb_geometry = ($a_geometry < 1) ? 100 : $a_geometry;
    }

    /**
    * Rebuild the thumbnail images with a new thumbnail size
    */
    public function rebuildThumbnails(): void
    {
        foreach ($this->terms as $term) {
            if (strlen($term->getPicture())) {
                $this->generateThumbForFile($this->getImagePath(), $term->getPicture());
            }
        }
        foreach ($this->definitions as $definition) {
            if (strlen($definition->getPicture())) {
                $this->generateThumbForFile($this->getImagePath(), $definition->getPicture());
            }
        }
    }

    public function getThumbPrefix(): string
    {
        return "thumb.";
    }

    protected function generateThumbForFile($path, $file): void
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
            ilShellUtil::convertImage($filename, $thumbpath, $ext, $this->getThumbGeometry());
        }
    }

    /**
    * Returns a JSON representation of the question
    */
    public function toJSON(): string
    {
        $result = array();

        $result['id'] = $this->getId();
        $result['type'] = (string) $this->getQuestionType();
        $result['title'] = $this->getTitle();
        $result['question'] = $this->formatSAQuestion($this->getQuestion());
        $result['nr_of_tries'] = $this->getNrOfTries();
        $result['matching_mode'] = $this->getMatchingMode();
        $result['shuffle'] = true;
        $result['feedback'] = array(
            'onenotcorrect' => $this->formatSAQuestion($this->feedbackOBJ->getGenericFeedbackTestPresentation($this->getId(), false)),
            'allcorrect' => $this->formatSAQuestion($this->feedbackOBJ->getGenericFeedbackTestPresentation($this->getId(), true))
        );

        $this->setShuffler($this->randomGroup->shuffleArray(new RandomSeed()));

        $terms = array();
        foreach ($this->getShuffler()->transform($this->getTerms()) as $term) {
            $terms[] = array(
                "text" => $this->formatSAQuestion($term->getText()),
                "id" => $this->getId() . $term->getIdentifier()
            );
        }
        $result['terms'] = $terms;

        // alex 9.9.2010 as a fix for bug 6513 I added the question id
        // to the "def_id" in the array. The $pair->getDefinition()->getIdentifier() is not
        // unique, since it gets it value from the morder table field
        // this value is not changed, when a question is copied.
        // thus copying the same question on a page results in problems
        // when the second one (the copy) is answered.

        $definitions = array();
        foreach ($this->getShuffler()->transform($this->getDefinitions()) as $def) {
            $definitions[] = array(
                "text" => $this->formatSAQuestion((string) $def->getText()),
                "id" => $this->getId() . $def->getIdentifier()
            );
        }
        $result['definitions'] = $definitions;

        // #10353
        $matchings = array();
        foreach ($this->getMatchingPairs() as $pair) {
            // fau: fixLmMatchingPoints - ignore matching pairs with 0 or negative points
            if ($pair->getPoints() <= 0) {
                continue;
            }
            // fau.

            $pid = $pair->getDefinition()->getIdentifier();
            if ($this->getMatchingMode() == self::MATCHING_MODE_N_ON_N) {
                $pid .= '::' . $pair->getTerm()->getIdentifier();
            }

            if (!isset($matchings[$pid]) || $matchings[$pid]["points"] < $pair->getPoints()) {
                $matchings[$pid] = array(
                    "term_id" => $this->getId() . $pair->getTerm()->getIdentifier(),
                    "def_id" => $this->getId() . $pair->getDefinition()->getIdentifier(),
                    "points" => (int) $pair->getPoints()
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

    public function supportsJavascriptOutput(): bool
    {
        return true;
    }

    public function supportsNonJsOutput(): bool
    {
        return false;
    }

    public function setMatchingMode($matchingMode): void
    {
        $this->matchingMode = $matchingMode;
    }

    public function getMatchingMode(): string
    {
        return $this->matchingMode;
    }

    /**
     * @param $found_values
     * @return int
     */
    protected function calculateReachedPointsForSolution($found_values): float
    {
        $points = 0;
        if (! is_array($found_values)) {
            return $points;
        }
        foreach ($found_values as $definition => $terms) {
            if (!is_array($terms)) {
                continue;
            }
            foreach ($terms as $term) {
                foreach ($this->matchingpairs as $pair) {
                    if ($pair->getDefinition()->getIdentifier() == $definition && $pair->getTerm()->getIdentifier() == $term) {
                        $points += $pair->getPoints();
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
    public function getOperators($expression): array
    {
        require_once "./Modules/TestQuestionPool/classes/class.ilOperatorsExpressionMapping.php";
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
    public function getUserQuestionResult($active_id, $pass): ilUserQuestionResult
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
        for ($index = 1; $index <= $ilDB->numRows($data); ++$index) {
            $row = $ilDB->fetchAssoc($data);
            $definitions[$row["ident"]] = $index;
        }

        $data = $ilDB->queryF(
            "SELECT ident FROM qpl_a_mterm WHERE question_fi = %s ORDER BY term_id",
            array("integer"),
            array($this->getId())
        );

        $terms = array();
        for ($index = 1; $index <= $ilDB->numRows($data); ++$index) {
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

        $result->setReachedPercentage(($points / $max_points) * 100);

        return $result;
    }

    /**
     * If index is null, the function returns an array with all anwser options
     * Else it returns the specific answer option
     *
     * @param null|int $index
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
    protected function afterSyncWithOriginal($origQuestionId, $dupQuestionId, $origParentObjId, $dupParentObjId): void
    {
        parent::afterSyncWithOriginal($origQuestionId, $dupQuestionId, $origParentObjId, $dupParentObjId);

        $origImagePath = $this->buildImagePath($origQuestionId, $origParentObjId);
        $dupImagePath = $this->buildImagePath($dupQuestionId, $dupParentObjId);

        ilFileUtils::delDir($origImagePath);
        if (is_dir($dupImagePath)) {
            ilFileUtils::makeDirParents($origImagePath);
            ilFileUtils::rCopy($dupImagePath, $origImagePath);
        }
    }

    protected function createMatchingTerm(string $term = '', string $picture = '', int $identifier = 0): assAnswerMatchingTerm
    {
        return new assAnswerMatchingTerm($term, $picture, $identifier);
    }
    protected function createMatchingDefinition(string $term = '', string $picture = '', int $identifier = 0): assAnswerMatchingDefinition
    {
        return new assAnswerMatchingDefinition($term, $picture, $identifier);
    }
    protected function createMatchingPair(
        assAnswerMatchingTerm $term = null,
        assAnswerMatchingDefinition $definition = null,
        float $points = 0.0
    ): assAnswerMatchingPair {
        $term = $term ?? $this->createMatchingTerm();
        $definition = $definition ?? $this->createMatchingDefinition();
        return new assAnswerMatchingPair($term, $definition, $points);
    }
}
