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

use ILIAS\TA\Questions\Ordering\assOrderingQuestionDatabaseRepository as OQRepository;

/**
 * Class for ordering questions
 *
 * assOrderingQuestion is a class for ordering questions.
 *
 * @author  Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @author  Björn Heyser <bheyser@databay.de>
 * @author  Maximilian Becker <mbecker@databay.de>
 * @author  Nils Haagen <nils.haagen@concepts-and-training.de>
 *
 * @version     $Id$
 *
 * @ingroup     ModulesTestQuestionPool
 */
class assOrderingQuestion extends assQuestion implements ilObjQuestionScoringAdjustable, ilObjAnswerScoringAdjustable, iQuestionCondition
{
    public const ORDERING_ELEMENT_FORM_FIELD_POSTVAR = 'order_elems';

    public const ORDERING_ELEMENT_FORM_CMD_UPLOAD_IMG = 'uploadElementImage';
    public const ORDERING_ELEMENT_FORM_CMD_REMOVE_IMG = 'removeElementImage'; //might actually go away - use ORDERING_ELEMENT_FORM_CMD_UPLOAD_IMG

    public const OQ_PICTURES = 0;
    public const OQ_TERMS = 1;
    public const OQ_NESTED_PICTURES = 2;
    public const OQ_NESTED_TERMS = 3;

    public const OQ_CT_PICTURES = 'pics';
    public const OQ_CT_TERMS = 'terms';

    public const VALID_UPLOAD_SUFFIXES = ["jpg", "jpeg", "png", "gif"];


    /**
     * @var ilAssOrderingElementList
     */
    protected $orderingElementList;

    /**
    * Type of ordering question
    * @var integer
    */
    protected $ordering_type;

    /**
    * Maximum thumbnail geometry
    *
    * @var integer
    */
    public $thumb_geometry = 100;

    public ?int $element_height = null;

    public $old_ordering_depth = array();
    public $leveled_ordering = array();

    /**
     * @var OQRepository
     */
    protected $oq_repository = null;

    /**
     * assOrderingQuestion constructor
     *
     * The constructor takes possible arguments an creates an instance of the assOrderingQuestion object.
     *
     * @param string  $title    A title string to describe the question
     * @param string  $comment  A comment string to describe the question
     * @param string  $author   A string containing the name of the questions author
     * @param integer $owner    A numerical ID to identify the owner/creator
     * @param string  $question The question string of the ordering test
     * @param int     $ordering_type
     */
    public function __construct(
        $title = "",
        $comment = "",
        $author = "",
        $owner = -1,
        $question = "",
        $ordering_type = self::OQ_TERMS
    ) {
        parent::__construct($title, $comment, $author, $owner, $question);
        $this->orderingElementList = new ilAssOrderingElementList();
        $this->ordering_type = $ordering_type;
    }

    /**
    * Returns true, if a ordering question is complete for use
    *
    * @return boolean True, if the ordering question is complete for use, otherwise false
    */
    public function isComplete(): bool
    {
        $elements = array_filter(
            $this->getOrderingElementList()->getElements(),
            fn ($element) => trim($element->getContent()) != ''
        );
        $has_at_least_two_elements = count($elements) > 1;

        $complete = $this->getAuthor()
            && $this->getTitle()
            && $this->getQuestion()
            && $this->getMaximumPoints()
            && $has_at_least_two_elements;

        return $complete;
    }



    protected function getRepository(): ILIAS\TA\Questions\Ordering\assOrderingQuestionDatabaseRepository
    {
        if (is_null($this->oq_repository)) {
            global $DIC;
            $ilDB = $DIC['ilDB'];
            $this->oq_repository = new OQRepository($ilDB);
        }
        return $this->oq_repository;
    }


    /**
     * Saves a assOrderingQuestion object to a database
     *
     * @param string $original_id
     *
     * @internal param object $db A pear DB object
     */
    public function saveToDb($original_id = ""): void
    {
        if ($original_id == '') {
            $this->saveQuestionDataToDb();
        } else {
            $this->saveQuestionDataToDb((int) $original_id);
        }
        $this->saveAdditionalQuestionDataToDb();
        parent::saveToDb();
    }

    /**
    * Loads a assOrderingQuestion object from a database
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
            $this->setAuthor($data["author"]);
            $this->setNrOfTries($data['nr_of_tries']);
            $this->setPoints($data["points"]);
            $this->setOwner($data["owner"]);
            include_once("./Services/RTE/classes/class.ilRTE.php");
            $this->setQuestion(ilRTE::_replaceMediaObjectImageSrc((string) $data["question_text"], 1));
            $this->ordering_type = strlen($data["ordering_type"]) ? $data["ordering_type"] : OQ_TERMS;
            $this->thumb_geometry = $data["thumb_geometry"];
            $this->element_height = $data["element_height"] ? (int) $data['element_height'] : null;
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
        }

        parent::loadFromDb($question_id);
    }

    /**
    * Duplicates an assOrderingQuestion
    *
    * @access public
    */
    public function duplicate(
        bool $for_test = true,
        ?string $title = "",
        ?string $author = "",
        ?string $owner = "",
        $testObjId = null
    ): int {
        if ($this->id <= 0) {
            // The question has not been saved. It cannot be duplicated
            return -1;
        }
        // duplicate the question in database
        $this_id = $this->getId();
        $thisObjId = $this->getObjId();

        $clone = clone $this;
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

        //$list = $this->getRepository()->getOrderingList($original_id)
        $list = $this->getRepository()->getOrderingList($this_id)
            ->withQuestionId($clone->getId());
        $list->distributeNewRandomIdentifiers();
        $clone->setOrderingElementList($list);
        $clone->saveToDb();

        $clone->copyPageOfQuestion($this_id);
        $clone->copyXHTMLMediaObjectsOfQuestion($this_id);
        $clone->duplicateImages($this_id, $thisObjId, $clone->getId(), $testObjId);

        $clone->onDuplicate($thisObjId, $this_id, $clone->getObjId(), $clone->getId());
        return $clone->getId();
    }

    /**
    * Copies an assOrderingQuestion object
    *
    * @access public
    */
    public function copyObject($target_questionpool_id, $title = ""): int
    {
        if ($this->getId() <= 0) {
            throw new RuntimeException('The question has not been saved. It cannot be duplicated');
        }
        // duplicate the question in database
        $clone = clone $this;
        $this_id = $this->getId();
        $original_id = assQuestion::_getOriginalId($this_id);
        $clone->id = -1;
        $source_questionpool_id = $this->getObjId();
        $clone->setObjId($target_questionpool_id);
        if ($title) {
            $clone->setTitle($title);
        }
        $clone->saveToDb();

        $list = $this->getRepository()->getOrderingList($this_id)
            ->withQuestionId($clone->getId());
        $list->distributeNewRandomIdentifiers();
        $clone->setOrderingElementList($list);
        $clone->saveToDb();

        $clone->copyPageOfQuestion($original_id);
        $clone->copyXHTMLMediaObjectsOfQuestion($original_id);
        $clone->duplicateImages($original_id, $source_questionpool_id, $clone->getId(), $target_questionpool_id);

        $clone->onCopy($source_questionpool_id, $original_id, $clone->getObjId(), $clone->getId());

        return $clone->getId();
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
        $clone->duplicateImages($sourceQuestionId, $sourceParentId, $clone->getId(), $clone->getObjId());

        $clone->onCopy($sourceParentId, $sourceQuestionId, $clone->getObjId(), $clone->getId());

        return $clone->id;
    }

    public function duplicateImages($src_question_id, $src_object_id, $dest_question_id, $dest_object_id): void
    {
        global $DIC;
        $ilLog = $DIC['ilLog'];
        if ($this->isImageOrderingType()) {
            $imagepath_original = $this->getImagePath($src_question_id, $src_object_id);
            $imagepath = $this->getImagePath($dest_question_id, $dest_object_id);

            if (!file_exists($imagepath)) {
                ilFileUtils::makeDirParents($imagepath);
            }
            foreach ($this->getOrderingElementList() as $element) {
                $filename = $element->getContent();
                //if (!@copy($imagepath_original . $filename, $imagepath . $filename)) {
                if (!copy($imagepath_original . $filename, $imagepath . $filename)) {
                    $ilLog->write("image could not be duplicated!!!!");
                    $ilLog->write($imagepath_original . $filename);
                    $ilLog->write($imagepath . $filename);
                }
                if (@file_exists($imagepath_original . $this->getThumbPrefix() . $filename)) {
                    if (!@copy($imagepath_original . $this->getThumbPrefix() . $filename, $imagepath . $this->getThumbPrefix() . $filename)) {
                        $ilLog->write("image thumbnail could not be duplicated!!!!");
                    }
                }
            }
        }
    }

    /**
     * @deprecated (!)
     * simply use the working method duplicateImages(), we do not search the difference here
     * and we will delete this soon (!) currently no usage found, remove for il5.3
     */
    public function copyImages($question_id, $source_questionpool): void
    {
        global $DIC;
        $ilLog = $DIC['ilLog'];
        if ($this->getOrderingType() == OQ_PICTURES) {
            $imagepath = $this->getImagePath();
            $imagepath_original = str_replace("/$this->id/images", "/$question_id/images", $imagepath);
            $imagepath_original = str_replace("/$this->obj_id/", "/$source_questionpool/", $imagepath_original);
            if (!file_exists($imagepath)) {
                ilFileUtils::makeDirParents($imagepath);
            }
            foreach ($this->getOrderingElementList() as $element) {
                $filename = $element->getContent();
                if (!@copy($imagepath_original . $filename, $imagepath . $filename)) {
                    $ilLog->write("Ordering Question image could not be copied: ${imagepath_original}${filename}");
                }
                if (@file_exists($imagepath_original . $this->getThumbPrefix() . $filename)) {
                    if (!@copy($imagepath_original . $this->getThumbPrefix() . $filename, $imagepath . $this->getThumbPrefix() . $filename)) {
                        $ilLog->write("Ordering Question image thumbnail could not be copied: $imagepath_original" . $this->getThumbPrefix() . $filename);
                    }
                }
            }
        }
    }

    protected function getValidOrderingTypes(): array
    {
        return [
            self::OQ_PICTURES,
            self::OQ_TERMS,
            self::OQ_NESTED_PICTURES,
            self::OQ_NESTED_TERMS
        ];
    }

    public function setOrderingType($ordering_type = self::OQ_TERMS)
    {
        if (!in_array($ordering_type, $this->getValidOrderingTypes())) {
            throw new \InvalidArgumentException('Must be valid ordering type.');
        }
        $this->ordering_type = $ordering_type;
    }

    public function getOrderingType(): int
    {
        return $this->ordering_type;
    }

    public function isOrderingTypeNested(): bool
    {
        $nested = [
            self::OQ_NESTED_TERMS,
            self::OQ_NESTED_PICTURES
        ];
        return in_array($this->getOrderingType(), $nested);
    }

    public function isImageOrderingType(): bool
    {
        $with_images = [
            self::OQ_PICTURES,
            self::OQ_NESTED_PICTURES
        ];
        return in_array($this->getOrderingType(), $with_images);
    }

    public function setContentType($ct)
    {
        if (!in_array($ct, [
            self::OQ_CT_PICTURES,
            self::OQ_CT_TERMS
        ])) {
            throw new \InvalidArgumentException("use OQ content-type", 1);
        }
        if ($ct == self::OQ_CT_PICTURES) {
            if ($this->isOrderingTypeNested()) {
                $this->setOrderingType(self::OQ_NESTED_PICTURES);
            } else {
                $this->setOrderingType(self::OQ_PICTURES);
            }
            $this->setThumbGeometry($this->getThumbGeometry());
        }
        if ($ct == self::OQ_CT_TERMS) {
            if ($this->isOrderingTypeNested()) {
                $this->setOrderingType(self::OQ_NESTED_TERMS);
            } else {
                $this->setOrderingType(self::OQ_TERMS);
            }
        }
    }

    public function setNestingType(bool $nesting)
    {
        if ($nesting) {
            if ($this->isImageOrderingType()) {
                $this->setOrderingType(self::OQ_NESTED_PICTURES);
            } else {
                $this->setOrderingType(self::OQ_NESTED_TERMS);
            }
        } else {
            if ($this->isImageOrderingType()) {
                $this->setOrderingType(self::OQ_PICTURES);
            } else {
                $this->setOrderingType(self::OQ_TERMS);
            }
        }
    }

    public function hasOrderingTypeUploadSupport(): bool
    {
        return $this->isImageOrderingType();
    }

    /**
     * @param $forceCorrectSolution
     * @param $activeId
     * @param $passIndex
     * @return ilAssOrderingElementList
     */
    public function getOrderingElementListForSolutionOutput($forceCorrectSolution, $activeId, $passIndex): ilAssOrderingElementList
    {
        if ($forceCorrectSolution || !$activeId || $passIndex === null) {
            return $this->getOrderingElementList();
        }

        $solutionValues = $this->getSolutionValues($activeId, $passIndex);

        if (!count($solutionValues)) {
            return $this->getShuffledOrderingElementList();
        }

        return $this->getSolutionOrderingElementList($this->fetchIndexedValuesFromValuePairs($solutionValues));
    }

    /**
     * @param ilAssNestedOrderingElementsInputGUI $inputGUI
     * @param array $lastPost
     * @param integer $activeId
     * @param integer $pass
     * @return ilAssOrderingElementList
     * @throws ilTestException
     * @throws ilTestQuestionPoolException
     */
    public function getSolutionOrderingElementListForTestOutput(ilAssNestedOrderingElementsInputGUI $inputGUI, $lastPost, $activeId, $pass): ilAssOrderingElementList
    {
        if ($inputGUI->isPostSubmit($lastPost)) {
            return $this->fetchSolutionListFromFormSubmissionData($lastPost);
        }
        $indexedSolutionValues = $this->fetchIndexedValuesFromValuePairs(
            // hey: prevPassSolutions - obsolete due to central check
            $this->getTestOutputSolutions($activeId, $pass)
            // hey.
        );

        if (count($indexedSolutionValues)) {
            return $this->getSolutionOrderingElementList($indexedSolutionValues);
        }

        return $this->getShuffledOrderingElementList();
    }

    /**
     * @param string $value1
     * @param string $value2
     * @return ilAssOrderingElement
     */
    protected function getSolutionValuePairBrandedOrderingElementByRandomIdentifier($value1, $value2): ilAssOrderingElement
    {
        $value2 = explode(':', $value2);

        $randomIdentifier = $value2[0];
        $selectedPosition = $value1;
        $selectedIndentation = $value2[1];

        $element = $this->getOrderingElementList()->getElementByRandomIdentifier($randomIdentifier)->getClone();

        $element->setPosition($selectedPosition);
        $element->setIndentation($selectedIndentation);

        return $element;
    }

    /**
     * @param string $value1
     * @param string $value2
     * @return ilAssOrderingElement
     */
    protected function getSolutionValuePairBrandedOrderingElementBySolutionIdentifier($value1, $value2): ilAssOrderingElement
    {
        $solutionIdentifier = $value1;
        $selectedPosition = ($value2 - 1);
        $selectedIndentation = 0;

        $element = $this->getOrderingElementList()->getElementBySolutionIdentifier($solutionIdentifier)->getClone();

        $element->setPosition($selectedPosition);
        $element->setIndentation($selectedIndentation);

        return $element;
    }

    /**
     * @param array $valuePairs
     * @return ilAssOrderingElementList
     * @throws ilTestQuestionPoolException
     */
    public function getSolutionOrderingElementList($indexedSolutionValues): ilAssOrderingElementList
    {
        $solutionOrderingList = new ilAssOrderingElementList();
        $solutionOrderingList->setQuestionId($this->getId());

        foreach ($indexedSolutionValues as $value1 => $value2) {
            if ($this->isOrderingTypeNested()) {
                $element = $this->getSolutionValuePairBrandedOrderingElementByRandomIdentifier($value1, $value2);
            } else {
                $element = $this->getSolutionValuePairBrandedOrderingElementBySolutionIdentifier($value1, $value2);
            }

            $solutionOrderingList->addElement($element);
        }

        if (!$this->getOrderingElementList()->hasSameElementSetByRandomIdentifiers($solutionOrderingList)) {
            throw new ilTestQuestionPoolException('inconsistent solution values given');
        }

        return $solutionOrderingList;
    }

    /**
     * @param $active_id
     * @param $pass
     * @return ilAssOrderingElementList
     */
    public function getShuffledOrderingElementList(): ilAssOrderingElementList
    {
        $shuffledRandomIdentifierIndex = $this->getShuffler()->transform(
            $this->getOrderingElementList()->getRandomIdentifierIndex()
        );

        $shuffledElementList = $this->getOrderingElementList()->getClone();
        $shuffledElementList->reorderByRandomIdentifiers($shuffledRandomIdentifierIndex);
        $shuffledElementList->resetElementsIndentations();

        return $shuffledElementList;
    }

    /**
     * @return ilAssOrderingElementList
     */
    public function getOrderingElementList(): ilAssOrderingElementList
    {
        return $this->getRepository()->getOrderingList($this->getId());
    }

    /**
     * @param ilAssOrderingElementList $orderingElementList
     */
    public function setOrderingElementList(ilAssOrderingElementList $list): void
    {
        $list = $list->withQuestionId($this->getId());
        $elements = $list->getElements();
        $nu = [];
        foreach ($elements as $e) {
            $nu[] = $list->ensureValidIdentifiers($e);
        }
        $list = $list->withElements($nu);
        $this->getRepository()->updateOrderingList($list);
    }

    /**
     * Returns the ordering element from the given position.
     *
     * @param int $position
     * @return ilAssOrderingElement|null
     */
    public function getAnswer($index = 0): ?ilAssOrderingElement
    {
        if (!$this->getOrderingElementList()->elementExistByPosition($index)) {
            return null;
        }

        return $this->getOrderingElementList()->getElementByPosition($index);
    }

    /**
    * Deletes an answer with a given index. The index of the first
    * answer is 0, the index of the second answer is 1 and so on.
    *
    * @param integer $index A nonnegative index of the n-th answer
    * @access public
    * @see $answers
    */
    public function deleteAnswer($randomIdentifier): void
    {
        $this->getOrderingElementList()->removeElement(
            $this->getOrderingElementList()->getElementByRandomIdentifier($randomIdentifier)
        );
        $this->getOrderingElementList()->saveToDb();
    }

    /**
    * Returns the number of answers
    *
    * @return integer The number of answers of the ordering question
    * @access public
    * @see $answers
    */
    public function getAnswerCount(): int
    {
        return $this->getOrderingElementList()->countElements();
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

        if (is_null($pass)) {
            $pass = $this->getSolutionMaxPass($active_id);
        }

        $solutionValuePairs = $this->getSolutionValues($active_id, $pass, $authorizedSolution);

        if (!count($solutionValuePairs)) {
            return 0;
        }

        $indexedSolutionValues = $this->fetchIndexedValuesFromValuePairs($solutionValuePairs);
        $solutionOrderingElementList = $this->getSolutionOrderingElementList($indexedSolutionValues);

        return $this->calculateReachedPointsForSolution($solutionOrderingElementList);
    }

    public function calculateReachedPointsFromPreviewSession(ilAssQuestionPreviewSession $previewSession)
    {
        if (!$previewSession->hasParticipantSolution()) {
            return 0;
        }

        $solutionOrderingElementList = unserialize(
            $previewSession->getParticipantsSolution(),
            ["allowed_classes" => true]
        );

        $reachedPoints = $this->calculateReachedPointsForSolution($solutionOrderingElementList);
        $reachedPoints = $this->deductHintPointsFromReachedPoints($previewSession, $reachedPoints);

        return $this->ensureNonNegativePoints($reachedPoints);
    }

    /**
    * Returns the maximum points, a learner can reach answering the question
    *
    * @return double Points
    * @see $points
    */
    public function getMaximumPoints(): float
    {
        return $this->getPoints();
    }

    /*
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

    protected function cleanImagefiles(): void
    {
        if ($this->getOrderingType() == self::OQ_PICTURES) {
            if (@file_exists($this->getImagePath())) {
                $contents = ilFileUtils::getDir($this->getImagePath());
                foreach ($contents as $f) {
                    if (strcmp($f['type'], 'file') == 0) {
                        $found = false;
                        foreach ($this->getOrderingElementList() as $orderElement) {
                            if (strcmp($f['entry'], $orderElement->getContent()) == 0) {
                                $found = true;
                            }
                            if (strcmp($f['entry'], $this->getThumbPrefix() . $orderElement->getContent()) == 0) {
                                $found = true;
                            }
                        }
                        if (!$found) {
                            if (@file_exists($this->getImagePath() . $f['entry'])) {
                                @unlink($this->getImagePath() . $f['entry']);
                            }
                        }
                    }
                }
            }
        } else {
            if (@file_exists($this->getImagePath())) {
                ilFileUtils::delDir($this->getImagePath());
            }
        }
    }

    /*
    * Deletes an imagefile from the system if the file is deleted manually
    *
    * @param string $filename Image file filename
    * @return boolean Success
    */
    public function dropImageFile($imageFilename)
    {
        if (!strlen($imageFilename)) {
            return false;
        }

        $result = @unlink($this->getImagePath() . $imageFilename);
        $result = $result & @unlink($this->getImagePath() . $this->getThumbPrefix() . $imageFilename);

        return $result;
    }

    public function isImageFileStored($imageFilename): bool
    {
        if (!strlen($imageFilename)) {
            return false;
        }

        if (!file_exists($this->getImagePath() . $imageFilename)) {
            return false;
        }

        return is_file($this->getImagePath() . $imageFilename);
    }

    public function isImageReplaced(ilAssOrderingElement $newElement, ilAssOrderingElement $oldElement): bool
    {
        if (!$this->hasOrderingTypeUploadSupport()) {
            return false;
        }

        if (!$newElement->getContent()) {
            return false;
        }

        return $newElement->getContent() != $oldElement->getContent();
    }


    public function storeImageFile(string $upload_file, string $upload_name): ?string
    {
        $name_parts = explode(".", $upload_name);
        $suffix = strtolower(array_pop($name_parts));
        if (!in_array($suffix, self::VALID_UPLOAD_SUFFIXES)) {
            return null;
        }

        $this->ensureImagePathExists();
        $target_filename = $this->buildHashedImageFilename($upload_name, true);
        $target_filepath = $this->getImagePath() . $target_filename;
        if (ilFileUtils::moveUploadedFile($upload_file, $target_filename, $target_filepath)) {
            $thumb_path = $this->getImagePath() . $this->getThumbPrefix() . $target_filename;
            if ($this->getThumbGeometry()) {
                ilShellUtil::convertImage($target_filepath, $thumb_path, "JPEG", $this->getThumbGeometry());
            }
            return $target_filename;
        }

        return null;
    }

    /**
    * Checks the data to be saved for consistency
    *
  * @return boolean True, if the check was ok, False otherwise
    * @access public
    * @see $answers
    */
    public function validateSolutionSubmit(): bool
    {
        $submittedSolutionList = $this->getSolutionListFromPostSubmit();

        if (!$submittedSolutionList->hasElements()) {
            return true;
        }

        return $this->getOrderingElementList()->hasSameElementSetByRandomIdentifiers($submittedSolutionList);
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
        $entered_values = 0;

        if (is_null($pass)) {
            include_once "./Modules/Test/classes/class.ilObjTest.php";
            $pass = ilObjTest::_getPass($active_id);
        }

        $this->getProcessLocker()->executeUserSolutionUpdateLockOperation(
            function () use (&$entered_values, $active_id, $pass, $authorized) {
                $this->removeCurrentSolution($active_id, $pass, $authorized);

                foreach ($this->getSolutionListFromPostSubmit() as $orderingElement) {
                    $value1 = $orderingElement->getStorageValue1($this->getOrderingType());
                    $value2 = $orderingElement->getStorageValue2($this->getOrderingType());

                    $this->saveCurrentSolution($active_id, $pass, $value1, trim($value2), $authorized);

                    $entered_values++;
                }
            }
        );

        if ($entered_values) {
            $this->log($active_id, 'log_user_entered_values');
        } else {
            $this->log($active_id, 'log_user_not_entered_values');
        }

        return true;
    }

    protected function savePreviewData(ilAssQuestionPreviewSession $previewSession): void
    {
        if ($this->validateSolutionSubmit()) {
            $previewSession->setParticipantsSolution(serialize($this->getSolutionListFromPostSubmit()));
        }
    }

    public function saveAdditionalQuestionDataToDb()
    {
        /** @var ilDBInterface $ilDB */
        global $DIC;
        $ilDB = $DIC['ilDB'];

        // save additional data
        $ilDB->manipulateF(
            "DELETE FROM " . $this->getAdditionalTableName() . " WHERE question_fi = %s",
            array( "integer" ),
            array( $this->getId() )
        );

        $ilDB->manipulateF(
            "INSERT INTO " . $this->getAdditionalTableName() . " (question_fi, ordering_type, thumb_geometry, element_height) 
                            VALUES (%s, %s, %s, %s)",
            array( "integer", "text", "integer", "integer" ),
            array(
                                $this->getId(),
                                $this->ordering_type,
                                $this->getThumbGeometry(),
                                ($this->getElementHeight() > 20) ? $this->getElementHeight() : null
                            )
        );
    }


    protected function getQuestionRepository(): ILIAS\TA\Questions\Ordering\assOrderingQuestionDatabaseRepository
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        return new \ILIAS\TA\Questions\Ordering\assOrderingQuestionDatabaseRepository($ilDB);
    }

    public function saveAnswerSpecificDataToDb()
    {
    }

    /**
    * Returns the question type of the question
    *
    * @return integer The question type of the question
    * @access public
    */
    public function getQuestionType(): string
    {
        return "assOrderingQuestion";
    }

    /**
    * Returns the name of the additional question data table in the database
    *
    * @return string The additional table name
    * @access public
    */
    public function getAdditionalTableName(): string
    {
        return "qpl_qst_ordering";
    }

    /**
    * Returns the name of the answer table in the database
    *
    * @return string The answer table name
    * @access public
    */
    public function getAnswerTableName(): string
    {
        return "qpl_a_ordering";
    }

    /**
    * Collects all text in the question which could contain media objects
    * which were created with the Rich Text Editor
    */
    public function getRTETextWithMediaObjects(): string
    {
        $text = parent::getRTETextWithMediaObjects();

        foreach ($this->getOrderingElementList() as $orderingElement) {
            $text .= $orderingElement->getContent();
        }

        return $text;
    }

    /**
     * Returns the answers array
     * @deprecated seriously, stop looking for this kind data at this point (!) look where it comes from and learn (!)
     */
    public function getOrderElements(): array
    {
        return $this->getOrderingElementList()->getRandomIdentifierIndexedElements();
    }

    /**
    * Returns true if the question type supports JavaScript output
    *
    * @return boolean TRUE if the question type supports JavaScript output, FALSE otherwise
    * @access public
    */
    public function supportsJavascriptOutput(): bool
    {
        return true;
    }

    public function supportsNonJsOutput(): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function setExportDetailsXLS(ilAssExcelFormatHelper $worksheet, int $startrow, int $active_id, int $pass): int
    {
        parent::setExportDetailsXLS($worksheet, $startrow, $active_id, $pass);

        $solutions = $this->getSolutionValues($active_id, $pass);
        $sol = array();
        foreach ($solutions as $solution) {
            $sol[$solution["value1"]] = $solution["value2"];
        }
        asort($sol);
        $sol = array_keys($sol);

        $i = 1;
        foreach ($sol as $idx) {
            foreach ($solutions as $solution) {
                if ($solution["value1"] == $idx) {
                    $worksheet->setCell($startrow + $i, 0, $solution["value2"]);
                }
            }
            $element = $this->getOrderingElementList()->getElementBySolutionIdentifier($idx);
            $worksheet->setCell($startrow + $i, 1, $element->getContent());
            $i++;
        }

        return $startrow + $i + 1;
    }

    /*
    * Get the thumbnail geometry
    *
    * @return integer Geometry
    */
    public function getThumbGeometry(): ?int
    {
        return $this->thumb_geometry;
    }

    public function getThumbSize(): ?int
    {
        return $this->getThumbGeometry();
    }

    /*
    * Set the thumbnail geometry
    *
    * @param integer $a_geometry Geometry
    */
    public function setThumbGeometry($a_geometry): void
    {
        $this->thumb_geometry = ((int)$a_geometry < 1) ? 100 : $a_geometry;
    }

    public function getElementHeight(): ?int
    {
        return $this->element_height;
    }

    public function setElementHeight(?int $a_height): void
    {
        $this->element_height = ($a_height < 20) ? null : $a_height;
    }

    /*
    * Rebuild the thumbnail images with a new thumbnail size
    */
    public function rebuildThumbnails(): void
    {
        if ($this->isImageOrderingType()) {
            foreach ($this->getOrderElements() as $orderingElement) {
                $this->generateThumbForFile($this->getImagePath(), $orderingElement->getContent());
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
        include_once("./Services/RTE/classes/class.ilRTE.php");
        $result = array();
        $result['id'] = $this->getId();
        $result['type'] = (string) $this->getQuestionType();
        $result['title'] = $this->getTitle();
        $result['question'] = $this->formatSAQuestion($this->getQuestion());
        $result['nr_of_tries'] = $this->getNrOfTries();
        $result['shuffle'] = true;
        $result['points'] = $this->getPoints();
        $result['feedback'] = array(
            'onenotcorrect' => $this->formatSAQuestion($this->feedbackOBJ->getGenericFeedbackTestPresentation($this->getId(), false)),
            'allcorrect' => $this->formatSAQuestion($this->feedbackOBJ->getGenericFeedbackTestPresentation($this->getId(), true))
        );
        if ($this->getOrderingType() == self::OQ_PICTURES) {
            $result['path'] = $this->getImagePathWeb();
        }

        $counter = 1;
        $answers = array();
        foreach ($this->getOrderingElementList() as $orderingElement) {
            $answers[$counter] = $orderingElement->getContent();
            $counter++;
        }
        $answers = $this->getShuffler()->transform($answers);
        $arr = array();
        foreach ($answers as $order => $answer) {
            array_push($arr, array(
                "answertext" => (string) $answer,
                "order" => (int) $order
            ));
        }
        $result['answers'] = $arr;

        $mobs = ilObjMediaObject::_getMobsOfObject("qpl:html", $this->getId());
        $result['mobs'] = $mobs;

        return json_encode($result);
    }

    /**
     * @return ilAssNestedOrderingElementsInputGUI|ilAssOrderingImagesInputGUI|ilAssOrderingTextsInputGUI
     * @throws ilTestQuestionPoolException
     */
    public function buildOrderingElementInputGui()
    {
        if ($this->isImageOrderingType()) {
            return $this->buildOrderingImagesInputGui();
        }
        return $this->buildOrderingTextsInputGui();
    }


    /**
     * @param ilAssOrderingTextsInputGUI|ilAssOrderingImagesInputGUI|ilAssNestedOrderingElementsInputGUI $formField
     */
    public function initOrderingElementAuthoringProperties(ilFormPropertyGUI $formField): void
    {
        switch (true) {
            case $formField instanceof ilAssNestedOrderingElementsInputGUI:
                $formField->setInteractionEnabled(true);
                $formField->setNestingEnabled($this->isOrderingTypeNested());
                break;

            case $formField instanceof ilAssOrderingTextsInputGUI:
            case $formField instanceof ilAssOrderingImagesInputGUI:
            default:

                $formField->setEditElementOccuranceEnabled(true);
                $formField->setEditElementOrderEnabled(true);
        }
    }

    /**
     * @param ilFormPropertyGUI $formField
     */
    public function initOrderingElementFormFieldLabels(ilFormPropertyGUI $formField): void
    {
        $formField->setInfo($this->lng->txt('ordering_answer_sequence_info'));
        $formField->setTitle($this->lng->txt('answers'));
    }

    /**
     * @return ilAssOrderingTextsInputGUI
     */
    public function buildOrderingTextsInputGui(): ilAssOrderingTextsInputGUI
    {
        $formDataConverter = $this->buildOrderingTextsFormDataConverter();

        require_once 'Modules/TestQuestionPool/classes/forms/class.ilAssOrderingTextsInputGUI.php';

        $orderingElementInput = new ilAssOrderingTextsInputGUI(
            $formDataConverter,
            self::ORDERING_ELEMENT_FORM_FIELD_POSTVAR
        );

        $this->initOrderingElementFormFieldLabels($orderingElementInput);

        return $orderingElementInput;
    }

    /**
     * @return ilAssOrderingImagesInputGUI
     */
    public function buildOrderingImagesInputGui(): ilAssOrderingImagesInputGUI
    {
        $formDataConverter = $this->buildOrderingImagesFormDataConverter();

        require_once 'Modules/TestQuestionPool/classes/forms/class.ilAssOrderingImagesInputGUI.php';

        $orderingElementInput = new ilAssOrderingImagesInputGUI(
            $formDataConverter,
            self::ORDERING_ELEMENT_FORM_FIELD_POSTVAR
        );

        $orderingElementInput->setImageUploadCommand(self::ORDERING_ELEMENT_FORM_CMD_UPLOAD_IMG);
        $orderingElementInput->setImageRemovalCommand(self::ORDERING_ELEMENT_FORM_CMD_REMOVE_IMG);

        $this->initOrderingElementFormFieldLabels($orderingElementInput);

        return $orderingElementInput;
    }

    /**
     * @return ilAssNestedOrderingElementsInputGUI
     */
    public function buildNestedOrderingElementInputGui(): ilAssNestedOrderingElementsInputGUI
    {
        $formDataConverter = $this->buildNestedOrderingFormDataConverter();

        require_once 'Modules/TestQuestionPool/classes/forms/class.ilAssNestedOrderingElementsInputGUI.php';

        $orderingElementInput = new ilAssNestedOrderingElementsInputGUI(
            $formDataConverter,
            self::ORDERING_ELEMENT_FORM_FIELD_POSTVAR
        );

        $orderingElementInput->setUniquePrefix($this->getId());
        $orderingElementInput->setOrderingType($this->getOrderingType());
        $orderingElementInput->setElementImagePath($this->getImagePathWeb());
        $orderingElementInput->setThumbPrefix($this->getThumbPrefix());

        $this->initOrderingElementFormFieldLabels($orderingElementInput);

        return $orderingElementInput;
    }


    /**
     * @param array $userSolutionPost
     * @return ilAssOrderingElementList
     * @throws ilTestException
     */
    public function fetchSolutionListFromFormSubmissionData($userSolutionPost): ilAssOrderingElementList
    {
        $orderingGUI = $this->buildNestedOrderingElementInputGui();
        $orderingGUI->setContext(ilAssNestedOrderingElementsInputGUI::CONTEXT_USER_SOLUTION_SUBMISSION);
        $orderingGUI->setValueByArray($userSolutionPost);

        if (!$orderingGUI->checkInput()) {
            require_once 'Modules/Test/exceptions/class.ilTestException.php';
            throw new ilTestException('error on validating user solution post');
        }

        require_once 'Modules/TestQuestionPool/classes/questions/class.ilAssOrderingElementList.php';
        $solutionOrderingElementList = ilAssOrderingElementList::buildInstance($this->getId());

        $storedElementList = $this->getOrderingElementList();

        foreach ($orderingGUI->getElementList($this->getId()) as $submittedElement) {
            $solutionElement = $storedElementList->getElementByRandomIdentifier(
                $submittedElement->getRandomIdentifier()
            )->getClone();

            $solutionElement->setPosition($submittedElement->getPosition());

            if ($this->isOrderingTypeNested()) {
                $solutionElement->setIndentation($submittedElement->getIndentation());
            }

            $solutionOrderingElementList->addElement($solutionElement);
        }

        return $solutionOrderingElementList;
    }

    /**
     * @var ilAssOrderingElementList
     */
    private $postSolutionOrderingElementList = null;

    /**
     * @return ilAssOrderingElementList
     */
    public function getSolutionListFromPostSubmit(): ilAssOrderingElementList
    {
        if ($this->postSolutionOrderingElementList === null) {
            $post_array = $_POST;
            if (! is_array($post_array)) {
                global $DIC;
                $request = $DIC->http()->request();
                $post_array = $request->getParsedBody();
            }

            $list = $this->fetchSolutionListFromFormSubmissionData($post_array);
            $this->postSolutionOrderingElementList = $list;
        }

        return $this->postSolutionOrderingElementList;
    }

    /**
     * @return array
     */
    public function getSolutionPostSubmit(): array
    {
        return $this->fetchSolutionSubmit($_POST);
    }

    /**
     * @param $user_order
     * @param $nested_solution
     * @return int
     */
    protected function calculateReachedPointsForSolution(ilAssOrderingElementList $solutionOrderingElementList): int
    {
        $reachedPoints = $this->getPoints();

        foreach ($this->getOrderingElementList() as $correctElement) {
            $userElement = $solutionOrderingElementList->getElementByPosition($correctElement->getPosition());

            if (!$correctElement->isSameElement($userElement)) {
                $reachedPoints = 0;
                break;
            }
        }

        return $reachedPoints;
    }

    /**
     * Get all available operations for a specific question
     *
     * @param string $expression
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
            iQuestionCondition::OrderingResultExpression,
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

        $maxStep = $this->lookupMaxStep($active_id, $pass);

        if ($maxStep !== null) {
            $data = $ilDB->queryF(
                "SELECT value1, value2 FROM tst_solutions WHERE active_fi = %s AND pass = %s AND question_fi = %s AND step = %s ORDER BY value1 ASC ",
                array("integer", "integer", "integer","integer"),
                array($active_id, $pass, $this->getId(), $maxStep)
            );
        } else {
            $data = $ilDB->queryF(
                "SELECT value1, value2 FROM tst_solutions WHERE active_fi = %s AND pass = %s AND question_fi = %s ORDER BY value1 ASC ",
                array("integer", "integer", "integer"),
                array($active_id, $pass, $this->getId())
            );
        }

        $elements = array();
        while ($row = $ilDB->fetchAssoc($data)) {
            $newKey = explode(":", $row["value2"]);

            foreach ($this->getOrderingElementList() as $answer) {
                // Images nut supported
                if (!$this->isOrderingTypeNested()) {
                    if ($answer->getSolutionIdentifier() == $row["value1"]) {
                        $elements[$row["value2"]] = $answer->getSolutionIdentifier() + 1;
                        break;
                    }
                } else {
                    if ($answer->getRandomIdentifier() == $newKey[0]) {
                        $elements[$row["value1"]] = $answer->getSolutionIdentifier() + 1;
                        break;
                    }
                }
            }
        }

        ksort($elements);

        foreach (array_values($elements) as $element) {
            $result->addKeyValue($element, $element);
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
            return $this->getOrderingElementList()->getElementByPosition($index);
        }

        return $this->getOrderingElementList()->getElements();
    }

    /**
     * {@inheritdoc}
     */
    protected function afterSyncWithOriginal($origQuestionId, $dupQuestionId, $origParentObjId, $dupParentObjId): void
    {
        parent::afterSyncWithOriginal($origQuestionId, $dupQuestionId, $origParentObjId, $dupParentObjId);
        $this->duplicateImages($dupQuestionId, $dupParentObjId, $origQuestionId, $origParentObjId);
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
            ->setIsUnchangedAnswerPossible(true)
            ->setUseUnchangedAnswerLabel($this->lng->txt('tst_unchanged_order_is_correct'));
    }
    // fau.

    protected function ensureImagePathExists(): void
    {
        if (!file_exists($this->getImagePath())) {
            ilFileUtils::makeDirParents($this->getImagePath());
        }
    }

    /**
     * @return array
     */
    public function fetchSolutionSubmit($formSubmissionDataStructure): array
    {
        $solutionSubmit = array();

        if (isset($formSubmissionDataStructure['orderresult'])) {
            $orderresult = $formSubmissionDataStructure['orderresult'];

            if (strlen($orderresult)) {
                $orderarray = explode(":", $orderresult);
                $ordervalue = 1;
                foreach ($orderarray as $index) {
                    $idmatch = null;
                    if (preg_match("/id_(\\d+)/", $index, $idmatch)) {
                        $randomid = $idmatch[1];
                        foreach ($this->getOrderingElementList() as $answeridx => $answer) {
                            if ($answer->getRandomIdentifier() == $randomid) {
                                $solutionSubmit[$answeridx] = $ordervalue;
                                $ordervalue++;
                            }
                        }
                    }
                }
            }
        } elseif ($this->getOrderingType() == OQ_NESTED_TERMS || $this->getOrderingType() == OQ_NESTED_PICTURES) {
            $index = 0;
            foreach ($formSubmissionDataStructure['content'] as $randomId => $content) {
                $indentation = $formSubmissionDataStructure['indentation'];

                $value1 = $index++;
                $value2 = implode(':', array($randomId, $indentation));

                $solutionSubmit[$value1] = $value2;
            }
        } else {
            foreach ($formSubmissionDataStructure as $key => $value) {
                $matches = null;
                if (preg_match("/^order_(\d+)/", $key, $matches)) {
                    if (!(preg_match("/initial_value_\d+/", $value))) {
                        if (strlen($value)) {
                            foreach ($this->getOrderingElementList() as $answeridx => $answer) {
                                if ($answer->getRandomIdentifier() == $matches[1]) {
                                    $solutionSubmit[$answeridx] = $value;
                                }
                            }
                        }
                    }
                }
            }
        }

        return $solutionSubmit;
    }

    /**
     * @return ilAssOrderingFormValuesObjectsConverter
     */
    protected function buildOrderingElementFormDataConverter(): ilAssOrderingFormValuesObjectsConverter
    {
        require_once 'Modules/TestQuestionPool/classes/forms/class.ilAssOrderingFormValuesObjectsConverter.php';
        $converter = new ilAssOrderingFormValuesObjectsConverter();
        $converter->setPostVar(self::ORDERING_ELEMENT_FORM_FIELD_POSTVAR);

        return $converter;
    }

    /**
     * @return ilAssOrderingFormValuesObjectsConverter
     */
    protected function buildOrderingImagesFormDataConverter(): ilAssOrderingFormValuesObjectsConverter
    {
        $formDataConverter = $this->buildOrderingElementFormDataConverter();
        $formDataConverter->setContext(ilAssOrderingFormValuesObjectsConverter::CONTEXT_MAINTAIN_ELEMENT_IMAGE);

        $formDataConverter->setImageRemovalCommand(self::ORDERING_ELEMENT_FORM_CMD_REMOVE_IMG);
        $formDataConverter->setImageUrlPath($this->getImagePathWeb());
        $formDataConverter->setImageFsPath($this->getImagePath());

        if ($this->getThumbSize() && $this->getThumbPrefix()) {
            $formDataConverter->setThumbnailPrefix($this->getThumbPrefix());
        }
        return $formDataConverter;
    }

    /**
     * @return ilAssOrderingFormValuesObjectsConverter
     */
    protected function buildOrderingTextsFormDataConverter(): ilAssOrderingFormValuesObjectsConverter
    {
        $formDataConverter = $this->buildOrderingElementFormDataConverter();
        $formDataConverter->setContext(ilAssOrderingFormValuesObjectsConverter::CONTEXT_MAINTAIN_ELEMENT_TEXT);
        return $formDataConverter;
    }

    /**
     * @return ilAssOrderingFormValuesObjectsConverter
     */
    protected function buildNestedOrderingFormDataConverter(): ilAssOrderingFormValuesObjectsConverter
    {
        $formDataConverter = $this->buildOrderingElementFormDataConverter();
        $formDataConverter->setContext(ilAssOrderingFormValuesObjectsConverter::CONTEXT_MAINTAIN_HIERARCHY);

        if ($this->getOrderingType() == OQ_NESTED_PICTURES) {
            $formDataConverter->setImageRemovalCommand(self::ORDERING_ELEMENT_FORM_CMD_REMOVE_IMG);
            $formDataConverter->setImageUrlPath($this->getImagePathWeb());
            $formDataConverter->setThumbnailPrefix($this->getThumbPrefix());
        }

        return $formDataConverter;
    }
}
