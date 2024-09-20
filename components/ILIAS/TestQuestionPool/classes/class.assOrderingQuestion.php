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

declare(strict_types=1);

use ILIAS\TestQuestionPool\Questions\QuestionLMExportable;
use ILIAS\TestQuestionPool\Questions\QuestionAutosaveable;
use ILIAS\TestQuestionPool\Questions\Ordering\OrderingQuestionDatabaseRepository as OQRepository;
use ILIAS\Test\Logging\AdditionalInformationGenerator;

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
 * @ingroup components\ILIASTestQuestionPool
 */
class assOrderingQuestion extends assQuestion implements ilObjQuestionScoringAdjustable, ilObjAnswerScoringAdjustable, iQuestionCondition, QuestionLMExportable, QuestionAutosaveable
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
    protected const HAS_SPECIFIC_FEEDBACK = false;

    public ?int $element_height = null;
    public $old_ordering_depth = [];
    public $leveled_ordering = [];
    protected ?OQRepository $oq_repository = null;

    protected ?ilAssOrderingElementList $element_list_for_deferred_saving = null;

    public function __construct(
        string $title = "",
        string $comment = "",
        string $author = "",
        int $owner = -1,
        string $question = "",
        protected int $ordering_type = self::OQ_TERMS
    ) {
        parent::__construct($title, $comment, $author, $owner, $question);
    }

    public function isComplete(): bool
    {
        $elements = array_filter(
            $this->getOrderingElementList()->getElements(),
            fn($element) => trim($element->getContent()) != ''
        );
        $has_at_least_two_elements = count($elements) > 1;

        $complete = $this->getAuthor()
            && $this->getTitle()
            && $this->getQuestion()
            && $this->getMaximumPoints()
            && $has_at_least_two_elements;

        return $complete;
    }

    protected function getRepository(): OQRepository
    {
        if (is_null($this->oq_repository)) {
            $this->oq_repository = new OQRepository($this->db);
        }
        return $this->oq_repository;
    }

    public function saveToDb(?int $original_id = null): void
    {
        $this->saveQuestionDataToDb($original_id);
        $this->saveAdditionalQuestionDataToDb();
        parent::saveToDb();
        if ($this->element_list_for_deferred_saving !== null) {
            $this->setOrderingElementList($this->element_list_for_deferred_saving);
        }
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
        $result = $this->db->queryF(
            "SELECT qpl_questions.*, " . $this->getAdditionalTableName() . ".* FROM qpl_questions LEFT JOIN " . $this->getAdditionalTableName() . " ON " . $this->getAdditionalTableName() . ".question_fi = qpl_questions.question_id WHERE qpl_questions.question_id = %s",
            ["integer"],
            [$question_id]
        );
        if ($result->numRows() == 1) {
            $data = $this->db->fetchAssoc($result);
            $this->setId($question_id);
            $this->setObjId($data["obj_fi"]);
            $this->setTitle((string) $data["title"]);
            $this->setComment((string) $data["description"]);
            $this->setOriginalId($data["original_id"]);
            $this->setAuthor($data["author"]);
            $this->setNrOfTries($data['nr_of_tries']);
            $this->setPoints($data["points"]);
            $this->setOwner($data["owner"]);
            $this->setQuestion(ilRTE::_replaceMediaObjectImageSrc((string) $data["question_text"], 1));
            $this->ordering_type = $data["ordering_type"] !== null ? (int) $data["ordering_type"] : self::OQ_TERMS;
            if ($data['thumb_geometry'] !== null && $data['thumb_geometry'] >= self::MINIMUM_THUMB_SIZE) {
                $this->setThumbSize($data['thumb_geometry']);
            }
            $this->element_height = $data["element_height"] ? (int) $data['element_height'] : null;

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

    protected function cloneQuestionTypeSpecificProperties(
        \assQuestion $target
    ): \assQuestion {
        $list = $this->getRepository()->getOrderingList($this->getId())
            ->withQuestionId($target->getId());
        $list->distributeNewRandomIdentifiers();
        $target->setOrderingElementList($list);
        $this->cloneImages($this->getId(), $this->getObjId(), $target->getId(), $target->getObjId());
        return $target;
    }

    public function cloneImages(
        int $source_question_id,
        int $source_parent_id,
        int $target_question_id,
        int $target_parent_id
    ): void {
        if (!$this->isImageOrderingType()) {
            return;
        }

        $image_source_path = $this->getImagePath($source_question_id, $source_parent_id);
        $image_target_path = $this->getImagePath($target_question_id, $target_parent_id);

        if (!file_exists($image_target_path)) {
            ilFileUtils::makeDirParents($image_target_path);
        } else {
            $this->removeAllImageFiles($image_target_path);
        }
        foreach ($this->getOrderingElementList() as $element) {
            $filename = $element->getContent();

            if ($filename === '') {
                continue;
            }

            if (!file_exists($image_source_path . $filename)
                || !copy($image_source_path . $filename, $image_target_path . $filename)) {
                $this->log->root()->warning('Image could not be cloned for object for question: ' . $target_question_id);
            }
            if (!file_exists($image_source_path . $this->getThumbPrefix() . $filename)
                || !copy($image_source_path . $this->getThumbPrefix() . $filename, $image_target_path . $this->getThumbPrefix() . $filename)) {
                $this->log->root()->warning('Image thumbnails could not be cloned for object for question: ' . $target_question_id);
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

    public function setOrderingType(int $ordering_type = self::OQ_TERMS)
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
            $this->setThumbSize($this->getThumbSize());
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

    public function getOrderingElementListForSolutionOutput(
        bool $force_correct_solution,
        int $active_id,
        ?int $pass_index
    ): ilAssOrderingElementList {
        if ($force_correct_solution || !$active_id || $pass_index === null) {
            return $this->getOrderingElementList();
        }

        $solution_values = $this->getSolutionValues($active_id, $pass_index);

        if (!count($solution_values)) {
            return $this->getShuffledOrderingElementList();
        }

        return $this->getSolutionOrderingElementList($this->fetchIndexedValuesFromValuePairs($solution_values));
    }

    public function getSolutionOrderingElementListForTestOutput(
        ilAssNestedOrderingElementsInputGUI $input_gui,
        array $last_post,
        int $active_id,
        int $pass
    ): ilAssOrderingElementList {
        if ($input_gui->isPostSubmit($last_post)) {
            return $this->fetchSolutionListFromFormSubmissionData($last_post);
        }
        $indexedSolutionValues = $this->fetchIndexedValuesFromValuePairs(
            // hey: prevPassSolutions - obsolete due to central check
            $this->getTestOutputSolutions($active_id, $pass)
            // hey.
        );

        if (count($indexedSolutionValues)) {
            return $this->getSolutionOrderingElementList($indexedSolutionValues);
        }

        return $this->getShuffledOrderingElementList();
    }

    protected function getSolutionValuePairBrandedOrderingElementByRandomIdentifier(
        int $value1,
        string $value2
    ): ilAssOrderingElement {
        $value = explode(':', $value2);

        $random_identifier = (int) $value[0];
        $selected_position = $value1;
        $selected_indentation = (int) $value[1];

        $element = $this->getOrderingElementList()->getElementByRandomIdentifier($random_identifier)->getClone();

        $element->setPosition($selected_position);
        $element->setIndentation($selected_indentation);

        return $element;
    }

    protected function getSolutionValuePairBrandedOrderingElementBySolutionIdentifier(
        int $value1,
        string $value2
    ): ilAssOrderingElement {
        $solution_identifier = $value1;
        $selected_position = ($value2 - 1);
        $selected_indentation = 0;

        $element = $this->getOrderingElementList()->getElementBySolutionIdentifier($solution_identifier)->getClone();

        $element->setPosition($selected_position);
        $element->setIndentation($selected_indentation);

        return $element;
    }

    /**
     * @throws ilTestQuestionPoolException
     */
    public function getSolutionOrderingElementList(array $indexed_solution_values): ilAssOrderingElementList
    {
        $solution_ordering_list = new ilAssOrderingElementList();
        $solution_ordering_list->setQuestionId($this->getId());

        foreach ($indexed_solution_values as $value1 => $value2) {
            if ($this->isOrderingTypeNested()) {
                $element = $this->getSolutionValuePairBrandedOrderingElementByRandomIdentifier($value1, $value2);
            } else {
                $element = $this->getSolutionValuePairBrandedOrderingElementBySolutionIdentifier($value1, $value2);
            }

            $solution_ordering_list->addElement($element);
        }

        if (!$this->getOrderingElementList()->hasSameElementSetByRandomIdentifiers($solution_ordering_list)) {
            throw new ilTestQuestionPoolException('inconsistent solution values given');
        }

        return $solution_ordering_list;
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
        if ($this->getId() <= 0) {
            $this->element_list_for_deferred_saving = $list;
            return;
        }
        $list = $list->withQuestionId($this->getId());
        $elements = $list->getElements();
        $nu = [];
        foreach ($elements as $e) {
            $nu[] = $list->ensureValidIdentifiers($e);
        }
        $this->getRepository()->updateOrderingList(
            $list->withElements($nu)
        );
    }

    /**
     * Returns the ordering element from the given position.
     *
     * @param int $position
     * @return ilAssOrderingElement|null
     */
    public function getAnswer(int $index = 0): ?ilAssOrderingElement
    {
        if (!$this->getOrderingElementList()->elementExistByPosition($index)) {
            return null;
        }

        return $this->getOrderingElementList()->getElementByPosition($index);
    }

    public function deleteAnswer(int $random_identifier): void
    {
        $this->getOrderingElementList()->removeElement(
            $this->getOrderingElementList()->getElementByRandomIdentifier($random_identifier)
        );
        $this->getOrderingElementList()->saveToDb();
    }

    public function getAnswerCount(): int
    {
        return $this->getOrderingElementList()->countElements();
    }

    public function calculateReachedPoints(
        int $active_id,
        ?int $pass = null,
        bool $authorized_solution = true
    ): float {
        if ($pass === null) {
            $pass = $this->getSolutionMaxPass($active_id);
        }

        $solution_value_pairs = $this->getSolutionValues($active_id, $pass, $authorized_solution);

        if ($solution_value_pairs === []) {
            return 0.0;
        }

        $solution_ordering_element_list = $this->getSolutionOrderingElementList(
            $this->fetchIndexedValuesFromValuePairs($solution_value_pairs)
        );

        return $this->calculateReachedPointsForSolution($solution_ordering_element_list);
    }

    public function calculateReachedPointsFromPreviewSession(ilAssQuestionPreviewSession $preview_session): float
    {
        if (!$preview_session->hasParticipantSolution()) {
            return 0.0;
        }

        $solution_ordering_element_list = unserialize(
            $preview_session->getParticipantsSolution(),
            ['allowed_classes' => true]
        );

        $reached_points = $this->deductHintPointsFromReachedPoints(
            $preview_session,
            $this->calculateReachedPointsForSolution($solution_ordering_element_list)
        );

        return $this->ensureNonNegativePoints($reached_points);
    }

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
        $result = $result && @unlink($this->getImagePath() . $this->getThumbPrefix() . $imageFilename);

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
            ilShellUtil::convertImage($target_filepath, $thumb_path, "JPEG", (string) $this->getThumbSize());

            return $target_filename;
        }

        return null;
    }

    public function updateImageFile(string $existing_image_name): ?string
    {
        $existing_image_path = $this->getImagePath() . $existing_image_name;
        $target_filename = $this->buildHashedImageFilename($existing_image_name, true);
        $target_filepath = $this->getImagePath() . $target_filename;
        if (ilFileUtils::rename($existing_image_path, $target_filepath)) {
            unlink($this->getImagePath() . $this->getThumbPrefix() . $existing_image_name);
            $thumb_path = $this->getImagePath() . $this->getThumbPrefix() . $target_filename;
            ilShellUtil::convertImage($target_filepath, $thumb_path, "JPEG", (string) $this->getThumbSize());

            return $target_filename;
        }

        return $existing_image_name;
    }

    public function validateSolutionSubmit(): bool
    {
        $submittedSolutionList = $this->getSolutionListFromPostSubmit();

        if (!$submittedSolutionList->hasElements()) {
            return true;
        }

        return $this->getOrderingElementList()->hasSameElementSetByRandomIdentifiers($submittedSolutionList);
    }

    public function saveWorkingData(
        int $active_id,
        ?int $pass = null,
        bool $authorized = true
    ): bool {
        if ($this->questionpool_request->raw('test_answer_changed') === null) {
            return true;
        }

        if (is_null($pass)) {
            $pass = ilObjTest::_getPass($active_id);
        }

        $this->getProcessLocker()->executeUserSolutionUpdateLockOperation(
            function () use ($active_id, $pass, $authorized) {
                $this->removeCurrentSolution($active_id, $pass, $authorized);

                foreach ($this->getSolutionListFromPostSubmit() as $orderingElement) {
                    $value1 = $orderingElement->getStorageValue1($this->getOrderingType());
                    $value2 = $orderingElement->getStorageValue2($this->getOrderingType());

                    $this->saveCurrentSolution($active_id, $pass, $value1, trim((string) $value2), $authorized);
                }
            }
        );

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
        // save additional data
        $this->db->manipulateF(
            "DELETE FROM " . $this->getAdditionalTableName() . " WHERE question_fi = %s",
            ["integer"],
            [$this->getId()]
        );

        $this->db->manipulateF(
            "INSERT INTO " . $this->getAdditionalTableName() . " (question_fi, ordering_type, thumb_geometry, element_height)
                            VALUES (%s, %s, %s, %s)",
            ["integer", "text", "integer", "integer"],
            [
                $this->getId(),
                $this->ordering_type,
                $this->getThumbSize(),
                ($this->getElementHeight() > 20) ? $this->getElementHeight() : null
            ]
        );
    }


    protected function getQuestionRepository(): OQRepository
    {
        return new OQRepository($this->db);
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
                if ($orderingElement->getContent() !== '') {
                    $this->generateThumbForFile($this->getImagePath(), $orderingElement->getContent());
                }
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
            ilShellUtil::convertImage($filename, $thumbpath, $ext, (string) $this->getThumbSize());
        }
    }

    /**
    * Returns a JSON representation of the question
    */
    public function toJSON(): string
    {
        $result = [];
        $result['id'] = $this->getId();
        $result['type'] = (string) $this->getQuestionType();
        $result['title'] = $this->getTitle();
        $result['question'] = $this->formatSAQuestion($this->getQuestion());
        $result['nr_of_tries'] = $this->getNrOfTries();
        $result['shuffle'] = true;
        $result['points'] = $this->getPoints();
        $result['feedback'] = [
            'onenotcorrect' => $this->formatSAQuestion($this->feedbackOBJ->getGenericFeedbackTestPresentation($this->getId(), false)),
            'allcorrect' => $this->formatSAQuestion($this->feedbackOBJ->getGenericFeedbackTestPresentation($this->getId(), true))
        ];
        if ($this->getOrderingType() == self::OQ_PICTURES) {
            $result['path'] = $this->getImagePathWeb();
        }

        $counter = 1;
        $answers = [];
        foreach ($this->getOrderingElementList() as $orderingElement) {
            $answers[$counter] = $orderingElement->getContent();
            $counter++;
        }
        $answers = $this->getShuffler()->transform($answers);
        $arr = [];
        foreach ($answers as $order => $answer) {
            array_push($arr, [
                "answertext" => (string) $answer,
                "order" => (int) $order
            ]);
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
        $form_data_converter = $this->buildNestedOrderingFormDataConverter();

        $ordering_element_input = new ilAssNestedOrderingElementsInputGUI(
            $form_data_converter,
            self::ORDERING_ELEMENT_FORM_FIELD_POSTVAR
        );

        $ordering_element_input->setUniquePrefix($this->getId());
        $ordering_element_input->setOrderingType($this->getOrderingType());
        $ordering_element_input->setElementImagePath($this->getImagePathWeb());
        $ordering_element_input->setThumbPrefix($this->getThumbPrefix());

        $this->initOrderingElementFormFieldLabels($ordering_element_input);

        return $ordering_element_input;
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
            throw new ilTestException('error on validating user solution post');
        }

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

    private ?ilAssOrderingElementList $postSolutionOrderingElementList = null;

    /**
     * @return ilAssOrderingElementList
     */
    public function getSolutionListFromPostSubmit(): ilAssOrderingElementList
    {
        if ($this->postSolutionOrderingElementList === null) {
            $post_array = $this->http->request()->getParsedBody();
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
    protected function calculateReachedPointsForSolution(ilAssOrderingElementList $solutionOrderingElementList): float
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

    public function getOperators(string $expression): array
    {
        return ilOperatorsExpressionMapping::getOperatorsByExpression($expression);
    }

    public function getExpressionTypes(): array
    {
        return [
            iQuestionCondition::PercentageResultExpression,
            iQuestionCondition::NumericResultExpression,
            iQuestionCondition::OrderingResultExpression,
            iQuestionCondition::EmptyAnswerExpression,
        ];
    }

    public function getUserQuestionResult(
        int $active_id,
        int $pass
    ): ilUserQuestionResult {
        $result = new ilUserQuestionResult($this, $active_id, $pass);

        $maxStep = $this->lookupMaxStep($active_id, $pass);
        if ($maxStep > 0) {
            $data = $this->db->queryF(
                "SELECT value1, value2 FROM tst_solutions WHERE active_fi = %s AND pass = %s AND question_fi = %s AND step = %s ORDER BY value1 ASC ",
                ["integer", "integer", "integer","integer"],
                [$active_id, $pass, $this->getId(), $maxStep]
            );
        } else {
            $data = $this->db->queryF(
                "SELECT value1, value2 FROM tst_solutions WHERE active_fi = %s AND pass = %s AND question_fi = %s ORDER BY value1 ASC ",
                ["integer", "integer", "integer"],
                [$active_id, $pass, $this->getId()]
            );
        }

        $elements = [];
        while ($row = $this->db->fetchAssoc($data)) {
            $newKey = explode(":", $row["value2"]);

            foreach ($this->getOrderingElementList() as $answer) {
                // Images not supported
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
    public function fetchSolutionSubmit(array $form_submission_data_structure): array
    {
        $solution_submit = [];

        if (isset($form_submission_data_structure['orderresult'])) {
            $orderresult = $form_submission_data_structure['orderresult'];

            if (strlen($orderresult)) {
                $orderarray = explode(":", $orderresult);
                $ordervalue = 1;
                foreach ($orderarray as $index) {
                    $idmatch = null;
                    if (preg_match("/id_(\\d+)/", $index, $idmatch)) {
                        $randomid = $idmatch[1];
                        foreach ($this->getOrderingElementList() as $answeridx => $answer) {
                            if ($answer->getRandomIdentifier() == $randomid) {
                                $solution_submit[$answeridx] = $ordervalue;
                                $ordervalue++;
                            }
                        }
                    }
                }
            }
        } elseif ($this->getOrderingType() == OQ_NESTED_TERMS || $this->getOrderingType() == OQ_NESTED_PICTURES) {
            $index = 0;
            foreach ($form_submission_data_structure['content'] as $randomId => $content) {
                $indentation = $form_submission_data_structure['indentation'];

                $value1 = $index++;
                $value2 = implode(':', [$randomId, $indentation]);

                $solution_submit[$value1] = $value2;
            }
        } else {
            foreach ($form_submission_data_structure as $key => $value) {
                $matches = null;
                if (preg_match("/^order_(\d+)/", $key, $matches)) {
                    if (!(preg_match("/initial_value_\d+/", $value))) {
                        if (strlen($value)) {
                            foreach ($this->getOrderingElementList() as $answeridx => $answer) {
                                if ($answer->getRandomIdentifier() == $matches[1]) {
                                    $solution_submit[$answeridx] = $value;
                                }
                            }
                        }
                    }
                }
            }
        }

        return $solution_submit;
    }

    /**
     * @return ilAssOrderingFormValuesObjectsConverter
     */
    protected function buildOrderingElementFormDataConverter(): ilAssOrderingFormValuesObjectsConverter
    {
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

        if ($this->getThumbPrefix()) {
            $formDataConverter->setThumbnailPrefix($this->getThumbPrefix());
        }
        return $formDataConverter;
    }

    /**
     * @return ilAssOrderingFormValuesObjectsConverter
     */
    protected function buildOrderingTextsFormDataConverter(): ilAssOrderingFormValuesObjectsConverter
    {
        $form_data_converter = $this->buildOrderingElementFormDataConverter();
        $form_data_converter->setContext(ilAssOrderingFormValuesObjectsConverter::CONTEXT_MAINTAIN_ELEMENT_TEXT);
        return $form_data_converter;
    }

    /**
     * @return ilAssOrderingFormValuesObjectsConverter
     */
    protected function buildNestedOrderingFormDataConverter(): ilAssOrderingFormValuesObjectsConverter
    {
        $form_data_converter = $this->buildOrderingElementFormDataConverter();
        $form_data_converter->setContext(ilAssOrderingFormValuesObjectsConverter::CONTEXT_MAINTAIN_HIERARCHY);

        if ($this->getOrderingType() === self::OQ_NESTED_PICTURES) {
            $form_data_converter->setImageRemovalCommand(self::ORDERING_ELEMENT_FORM_CMD_REMOVE_IMG);
            $form_data_converter->setImageUrlPath($this->getImagePathWeb());
            $form_data_converter->setThumbnailPrefix($this->getThumbPrefix());
        }

        return $form_data_converter;
    }

    public function toLog(AdditionalInformationGenerator $additional_info): array
    {
        return [
            AdditionalInformationGenerator::KEY_QUESTION_TYPE => (string) $this->getQuestionType(),
            AdditionalInformationGenerator::KEY_QUESTION_TITLE => $this->getTitle(),
            AdditionalInformationGenerator::KEY_QUESTION_TEXT => $this->formatSAQuestion($this->getQuestion()),
            AdditionalInformationGenerator::KEY_QUESTION_ORDERING_NESTING_TYPE => array_reduce(
                $this->getOrderingTypeLangVars($this->getOrderingType()),
                static fn(string $string, string $lang_var) => $string . $additional_info->getTagForLangVar($lang_var),
                ''
            ),
            AdditionalInformationGenerator::KEY_QUESTION_REACHABLE_POINTS => $this->getPoints(),
            AdditionalInformationGenerator::KEY_QUESTION_ANSWER_OPTION => $this->getSolutionOutputForLog(),
            AdditionalInformationGenerator::KEY_FEEDBACK => [
                AdditionalInformationGenerator::KEY_QUESTION_FEEDBACK_ON_INCOMPLETE => $this->formatSAQuestion($this->feedbackOBJ->getGenericFeedbackTestPresentation($this->getId(), false)),
                AdditionalInformationGenerator::KEY_QUESTION_FEEDBACK_ON_COMPLETE => $this->formatSAQuestion($this->feedbackOBJ->getGenericFeedbackTestPresentation($this->getId(), true))
            ]
        ];
    }

    private function getOrderingTypeLangVars(int $ordering_type): array
    {
        switch ($ordering_type) {
            case self::OQ_PICTURES:
                return ['qst_nested_nested_answers_off', 'oq_btn_use_order_pictures'];
            case self::OQ_TERMS:
                return ['qst_nested_nested_answers_off', 'oq_btn_use_order_terms'];
            case self::OQ_NESTED_PICTURES:
                return ['qst_nested_nested_answers_on', 'oq_btn_use_order_pictures'];
            case self::OQ_NESTED_TERMS:
                return ['qst_nested_nested_answers_on', 'oq_btn_use_order_terms'];
            default:
                return ['', ''];
        }
    }

    private function getSolutionOutputForLog(): string
    {
        $solution_ordering_list = $this->getOrderingElementList();

        $answers_gui = $this->buildNestedOrderingElementInputGui();
        $answers_gui->setContext(ilAssNestedOrderingElementsInputGUI::CONTEXT_CORRECT_SOLUTION_PRESENTATION);
        $answers_gui->setInteractionEnabled(false);
        $answers_gui->setElementList($solution_ordering_list);

        return $answers_gui->getHTML();
    }

    protected function solutionValuesToLog(
        AdditionalInformationGenerator $additional_info,
        array $solution_values
    ): array {
        return $this->getElementArrayWithIdentationsForTextOutput(
            $this->getSolutionOrderingElementList(
                $this->fetchIndexedValuesFromValuePairs($solution_values)
            )->getElements()
        );
    }

    public function solutionValuesToText(array $solution_values): array
    {
        if ($solution_values === []) {
            return [];
        }
        return $this->getElementArrayWithIdentationsForTextOutput(
            $this->getSolutionOrderingElementList(
                $this->fetchIndexedValuesFromValuePairs($solution_values)
            )->getElements()
        );
    }

    public function getCorrectSolutionForTextOutput(int $active_id, int $pass): array
    {
        return $this->getElementArrayWithIdentationsForTextOutput(
            $this->getOrderingElementList()->getElements()
        );
    }

    /**
     *
     * @param array<ilAssOrderingElement> $elements
     * @return array
     */
    private function getElementArrayWithIdentationsForTextOutput(array $elements): array
    {
        usort(
            $elements,
            static fn(ilAssOrderingElement $a, ilAssOrderingElement $b): int
                => $a->getPosition() - $b->getPosition()
        );

        return array_map(
            function (ilAssOrderingElement $v): string {
                $indentation = '';
                for ($i = 0;$i < $v->getIndentation();$i++) {
                    $indentation .= ' |';
                }
                return $indentation . $v->getContent();
            },
            $elements
        );
    }
}
