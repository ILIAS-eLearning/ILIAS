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

use ILIAS\TestQuestionPool\QuestionPoolDIC;
use ILIAS\TestQuestionPool\RequestDataCollector;
use ILIAS\TestQuestionPool\Questions\QuestionLMExportable;

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
class assImagemapQuestion extends assQuestion implements ilObjQuestionScoringAdjustable, ilObjAnswerScoringAdjustable, iQuestionCondition, QuestionLMExportable
{
    private RequestDataCollector $request; // Hate it.

    // hey: prevPassSolutions - wtf is imagemap ^^
    public $currentSolution = [];
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
        $title = '',
        $comment = '',
        $author = '',
        $owner = -1,
        $question = '',
        $image_filename = ''
    ) {
        parent::__construct($title, $comment, $author, $owner, $question);
        $this->image_filename = $image_filename;
        $this->answers = [];
        $this->coords = [];

        $local_dic = QuestionPoolDIC::dic();
        $this->request = $local_dic['request_data_collector'];
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

    public function saveToDb(?int $original_id = null): void
    {
        $this->saveQuestionDataToDb($original_id);
        $this->saveAdditionalQuestionDataToDb();
        $this->saveAnswerSpecificDataToDb();
        parent::saveToDb($original_id);
    }

    public function saveAnswerSpecificDataToDb(): void
    {
        $this->db->manipulateF(
            'DELETE FROM qpl_a_imagemap WHERE question_fi = %s',
            [ 'integer' ],
            [ $this->getId() ]
        );

        // Anworten wegschreiben
        foreach ($this->answers as $key => $value) {
            $answer_obj = $this->answers[$key];
            $answer_obj->setOrder($key);
            $next_id = $this->db->nextId('qpl_a_imagemap');
            $this->db->manipulateF(
                'INSERT INTO qpl_a_imagemap (answer_id, question_fi, answertext, points, aorder, coords, area, points_unchecked) VALUES (%s, %s, %s, %s, %s, %s, %s, %s)',
                [ 'integer', 'integer', 'text', 'float', 'integer', 'text', 'text', 'float' ],
                [ $next_id, $this->id, $answer_obj->getAnswertext(
                ), $answer_obj->getPoints(), $answer_obj->getOrder(
                ), $answer_obj->getCoords(), $answer_obj->getArea(
                ), $answer_obj->getPointsUnchecked() ]
            );
        }
    }

    public function saveAdditionalQuestionDataToDb(): void
    {
        $this->db->manipulateF(
            'DELETE FROM ' . $this->getAdditionalTableName() . ' WHERE question_fi = %s',
            [ 'integer' ],
            [ $this->getId() ]
        );

        $this->db->manipulateF(
            'INSERT INTO ' . $this->getAdditionalTableName(
            ) . ' (question_fi, image_file, is_multiple_choice) VALUES (%s, %s, %s)',
            [ 'integer', 'text', 'integer' ],
            [
                                $this->getId(),
                                $this->image_filename,
                                (int) $this->is_multiple_choice
                            ]
        );
    }

    protected function cloneQuestionTypeSpecificProperties(
        \assQuestion $target
    ): \assQuestion {
        $this->copyImagemapFiles($this->getId(), $this->getObjId(), $target->getId(), $target->getObjId());
        return $target;
    }

    public function copyImagemapFiles(
        int $source_question_id,
        int $source_parent_id,
        int $target_question_id,
        int $target_parent_id
    ): void {
        $image_source_path = $this->getImagePath($source_question_id, $source_parent_id);
        $image_target_path = $this->getImagePath($target_question_id, $target_parent_id);

        if (!file_exists($image_target_path)) {
            ilFileUtils::makeDirParents($image_target_path);
        } else {
            $this->removeAllImageFiles($image_target_path);
        }

        $src = opendir($image_source_path);
        while($src_file = readdir($src)) {
            if ($src_file === '.' || $src_file === '..') {
                continue;
            }
            copy(
                $image_source_path . DIRECTORY_SEPARATOR . $src_file,
                $image_target_path . DIRECTORY_SEPARATOR . $src_file
            );
        }
    }

    public function loadFromDb(int $question_id): void
    {
        $result = $this->db->queryF(
            'SELECT qpl_questions.*, ' . $this->getAdditionalTableName() . '.* FROM qpl_questions LEFT JOIN ' . $this->getAdditionalTableName() . ' ON ' . $this->getAdditionalTableName() . '.question_fi = qpl_questions.question_id WHERE qpl_questions.question_id = %s',
            ['integer'],
            [$question_id]
        );
        if ($result->numRows() == 1) {
            $data = $this->db->fetchAssoc($result);
            $this->setId($question_id);
            $this->setObjId($data['obj_fi']);
            $this->setTitle((string) $data['title']);
            $this->setComment((string) $data['description']);
            $this->setOriginalId($data['original_id']);
            $this->setNrOfTries($data['nr_of_tries']);
            $this->setAuthor($data['author']);
            $this->setPoints($data['points']);
            $this->setOwner($data['owner']);
            $this->setIsMultipleChoice($data['is_multiple_choice'] == self::MODE_MULTIPLE_CHOICE);
            $this->setQuestion(ilRTE::_replaceMediaObjectImageSrc((string) $data['question_text'], 1));
            $this->setImageFilename($data['image_file']);

            try {
                $this->setLifecycle(ilAssQuestionLifecycle::getInstance($data['lifecycle']));
            } catch (ilTestQuestionPoolInvalidArgumentException $e) {
                $this->setLifecycle(ilAssQuestionLifecycle::getDraftInstance());
            }

            try {
                $this->setAdditionalContentEditingMode($data['add_cont_edit_mode']);
            } catch (ilTestQuestionPoolException $e) {
            }

            $result = $this->db->queryF(
                'SELECT * FROM qpl_a_imagemap WHERE question_fi = %s ORDER BY aorder ASC',
                ['integer'],
                [$question_id]
            );
            if ($result->numRows() > 0) {
                while ($data = $this->db->fetchAssoc($result)) {
                    $image_map_question = new ASS_AnswerImagemap($data['answertext'] ?? '', $data['points'], $data['aorder']);
                    $image_map_question->setCoords($data['coords']);
                    $image_map_question->setArea($data['area']);
                    $image_map_question->setPointsUnchecked($data['points_unchecked']);
                    array_push($this->answers, $image_map_question);
                }
            }
        }
        parent::loadFromDb($question_id);
    }

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

    public function setImageFilename(
        string $image_filename,
        string $image_tempfilename = ''
    ): void {
        if (!empty($image_filename)) {
            $image_filename = str_replace(' ', '_', $image_filename);
            $this->image_filename = $image_filename;
        }
        if (!empty($image_tempfilename)) {
            $imagepath = $this->getImagePath();
            if (!file_exists($imagepath)) {
                ilFileUtils::makeDirParents($imagepath);
            }
            if (!ilFileUtils::moveUploadedFile($image_tempfilename, $image_filename, $imagepath . $image_filename)) {
                $this->tpl->setOnScreenMessage('failure', 'The image could not be uploaded!');
                return;
            }
            $this->log->write('gespeichert: ' . $imagepath . $image_filename);
        }
    }

    public function get_imagemap_contents(string $href = '#'): string
    {
        $imagemap_contents = '<map name=\'' . $this->title . '\'> ';
        for ($i = 0; $i < count($this->answers); $i++) {
            $imagemap_contents .= '<area alt=\'' . $this->answers[$i]->getAnswertext() . '\' ';
            $imagemap_contents .= 'shape=\'' . $this->answers[$i]->getArea() . '\' ';
            $imagemap_contents .= 'coords=\'' . $this->answers[$i]->getCoords() . '\' ';
            $imagemap_contents .= "href=\"{$href}&selimage=" . $this->answers[$i]->getOrder() . "\" /> ";
        }
        $imagemap_contents .= '</map>';
        return $imagemap_contents;
    }

    public function addAnswer(
        string $answertext = '',
        float $points = 0.0,
        int $order = 0,
        string $coords = '',
        string $area = '',
        float $points_unchecked = 0.0
    ): void {
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

    public function getAnswerCount(): int
    {
        return count($this->answers);
    }

    public function getAnswer(int $index = 0): ?object
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

    public function &getAnswers(): array
    {
        return $this->answers;
    }

    public function deleteArea(int $index = 0): void
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

    public function flushAnswers(): void
    {
        $this->answers = [];
    }

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

    public function calculateReachedPoints(
        int $active_id,
        ?int $pass = null,
        bool $authorized_solution = true
    ): float {
        if ($pass === null) {
            $pass = $this->getSolutionMaxPass($active_id);
        }
        $result = $this->getCurrentSolutionResultSet($active_id, $pass, $authorized_solution);
        $found_values = [];
        while ($data = $this->db->fetchAssoc($result)) {
            if ($data['value1'] !== '') {
                $found_values[] = $data['value1'];
            }
        }

        $points = $this->calculateReachedPointsForSolution($found_values);

        return $points;
    }

    public function calculateReachedPointsFromPreviewSession(ilAssQuestionPreviewSession $preview_session): float
    {
        $solution_data = $preview_session->getParticipantsSolution();

        $reached_points = $this->calculateReachedPointsForSolution(is_array($solution_data) ? array_values($solution_data) : []);

        return $this->ensureNonNegativePoints(
            $this->deductHintPointsFromReachedPoints($preview_session, $reached_points)
        );
    }

    /**
     * Saves the learners input of the question to the database.
     *
     * @access public
     * @param integer $active_id Active id of the user
     * @param integer $pass Test pass
     * @return boolean $status
     */
    public function saveWorkingData(
        int $active_id,
        ?int $pass = null,
        bool $authorized = true
    ): bool {
        if (is_null($pass)) {
            $pass = ilObjTest::_getPass($active_id);
        }

        $this->getProcessLocker()->executeUserSolutionUpdateLockOperation(
            function () use ($active_id, $pass, $authorized) {
                if ($authorized) {
                    // remove the dummy record of the intermediate solution
                    $this->deleteDummySolutionRecord($active_id, $pass);

                    // delete the authorized solution and make the intermediate solution authorized (keeping timestamps)
                    $this->removeCurrentSolution($active_id, $pass, true);
                    $this->updateCurrentSolutionsAuthorization($active_id, $pass, true, true);
                    return;
                }

                $this->forceExistingIntermediateSolution(
                    $active_id,
                    $pass,
                    $this->is_multiple_choice
                );

                if ($this->isReuseSolutionSelectionRequest()) {
                    $selection = $this->getReuseSolutionSelectionParameter();

                    foreach ($selection as $selectedIndex) {
                        $this->saveCurrentSolution($active_id, $pass, (int) $selectedIndex, null, $authorized);
                    }
                    return;
                }

                if ($this->isRemoveSolutionSelectionRequest()) {
                    $selection = $this->getRemoveSolutionSelectionParameter();
                    $this->deleteSolutionRecordByValues($active_id, $pass, $authorized, [
                        'value1' => (int) $selection
                    ]);
                    return;
                }

                if (!$this->isAddSolutionSelectionRequest()) {
                    return;
                }
                $selection = $this->getAddSolutionSelectionParameter();

                if ($this->is_multiple_choice) {
                    $this->deleteSolutionRecordByValues($active_id, $pass, $authorized, [
                        'value1' => (int) $this->request->raw('selImage')
                    ]);
                } else {
                    $this->removeCurrentSolution($active_id, $pass, $authorized);
                }

                $this->saveCurrentSolution($active_id, $pass, $this->request->raw('selImage'), null, $authorized);
            }
        );

        return true;
    }

    protected function savePreviewData(ilAssQuestionPreviewSession $previewSession): void
    {
        $solution = $previewSession->getParticipantsSolution();

        if ($this->is_multiple_choice
            && $this->request->isset('remImage')) {
            unset($solution[$this->request->int('remImage')]);
        }

        if ($this->request->isset('selImage')) {
            if (!$this->is_multiple_choice) {
                $solution = [];
            }

            $solution[$this->request->int('selImage')] = $this->request->int('selImage');
        }

        $previewSession->setParticipantsSolution($solution);
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
        return 'assImagemapQuestion';
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
        return 'qpl_qst_imagemap';
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
        return 'qpl_a_imagemap';
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
    public function setExportDetailsXLSX(ilAssExcelFormatHelper $worksheet, int $startrow, int $col, int $active_id, int $pass): int
    {
        parent::setExportDetailsXLSX($worksheet, $startrow, $col, $active_id, $pass);

        $solution = $this->getSolutionValues($active_id, $pass);

        $i = 1;
        foreach ($this->getAnswers() as $id => $answer) {
            $worksheet->setCell($startrow + $i, $col, $answer->getArea() . ': ' . $answer->getCoords());
            $worksheet->setBold($worksheet->getColumnCoord($col) . ($startrow + $i));

            $cellValue = 0;
            foreach ($solution as $solIndex => $sol) {
                if ($sol['value1'] == $id) {
                    $cellValue = 1;
                    break;
                }
            }

            $worksheet->setCell($startrow + $i, $col + 2, $cellValue);

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
        $this->image_filename = '';
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
        $result['shuffle'] = $this->getShuffle();
        $result['is_multiple'] = $this->getIsMultipleChoice();
        $result['feedback'] = [
            'onenotcorrect' => $this->formatSAQuestion($this->feedbackOBJ->getGenericFeedbackTestPresentation($this->getId(), false)),
            'allcorrect' => $this->formatSAQuestion($this->feedbackOBJ->getGenericFeedbackTestPresentation($this->getId(), true))
        ];
        $result['image'] = $this->getImagePathWeb() . $this->getImageFilename();

        $answers = [];
        $order = 0;
        foreach ($this->getAnswers() as $key => $answer_obj) {
            array_push($answers, [
                'answertext' => (string) $answer_obj->getAnswertext(),
                'points' => (float) $answer_obj->getPoints(),
                'points_unchecked' => (float) $answer_obj->getPointsUnchecked(),
                'order' => $order,
                'coords' => $answer_obj->getCoords(),
                'state' => $answer_obj->getState(),
                'area' => $answer_obj->getArea(),
                'feedback' => $this->formatSAQuestion(
                    $this->feedbackOBJ->getSpecificAnswerFeedbackExportPresentation($this->getId(), 0, $key)
                )
            ]);
            $order++;
        }
        $result['answers'] = $answers;

        $mobs = ilObjMediaObject::_getMobsOfObject('qpl:html', $this->getId());
        $result['mobs'] = $mobs;

        return json_encode($result);
    }

    protected function calculateReachedPointsForSolution(?array $found_values): float
    {
        if ($found_values === null) {
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

    public function getOperators(string $expression): array
    {
        return ilOperatorsExpressionMapping::getOperatorsByExpression($expression);
    }

    public function getExpressionTypes(): array
    {
        return [
            iQuestionCondition::PercentageResultExpression,
            iQuestionCondition::NumberOfResultExpression,
            iQuestionCondition::EmptyAnswerExpression,
            iQuestionCondition::ExclusiveResultExpression
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
                'SELECT value1+1 as value1 FROM tst_solutions WHERE active_fi = %s AND pass = %s AND question_fi = %s AND step = %s',
                ['integer', 'integer', 'integer', 'integer'],
                [$active_id, $pass, $this->getId(), $maxStep]
            );
        } else {
            $data = $this->db->queryF(
                'SELECT value1+1 as value1 FROM tst_solutions WHERE active_fi = %s AND pass = %s AND question_fi = %s AND step IS NULL',
                ['integer', 'integer', 'integer'],
                [$active_id, $pass, $this->getId()]
            );
        }

        while ($row = $this->db->fetchAssoc($data)) {
            $result->addKeyValue($row['value1'], $row['value1']);
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

    public function getTestOutputSolutions($activeId, $pass): array
    {
        $solution = parent::getTestOutputSolutions($activeId, $pass);

        $this->currentSolution = [];
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
        if (!$this->request->isset('selImage')) {
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

        if (!$this->request->isset('remImage')) {
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

        return assQuestion::explodeKeyValues($this->request->raw('reuseSelection'));
    }
    protected function isReuseSolutionSelectionRequest(): bool
    {
        if (!$this->getTestPresentationConfig()->isPreviousPassSolutionReuseAllowed()) {
            return false;
        }

        if (!$this->request->isset('reuseSelection')) {
            return false;
        }

        if (!strlen($this->request->raw('reuseSelection'))) {
            return false;
        }

        if (!preg_match('/\d(,\d)*/', $this->request->raw('reuseSelection'))) {
            return false;
        }

        return true;
    }

    public function toLog(): array
    {
        $result = [
            'question_id' => $this->getId(),
            'question_type' => (string) $this->getQuestionType(),
            'question_title' => $this->getTitle(),
            'tst_question' => $this->formatSAQuestion($this->getQuestion()),
            'cloze_text' => $this->formatSAQuestion($this->getClozeText()),
            'shuffle_answers' => $this->getShuffle() ? '{{ enabled }}' : '{{ disabled }}',
            'tst_imap_qst_mode' => $this->getIsMultipleChoice() ? '{{ tst_imap_qst_mode_mc }}' : '{{ tst_imap_qst_mode_sc }}',
            'image' => $this->getImagePathWeb() . $this->getImageFilename(),
            'tst_feedback' => [
                'feedback_incomplete_solution' => $this->formatSAQuestion($this->feedbackOBJ->getGenericFeedbackTestPresentation($this->getId(), false)),
                'feedback_complete_solution' => $this->formatSAQuestion($this->feedbackOBJ->getGenericFeedbackTestPresentation($this->getId(), true))
            ]
        ];

        $answers = [];
        $order = 0;
        foreach ($this->getAnswers() as $key => $answer_obj) {
            array_push($answers, [
                'answertext' => (string) $answer_obj->getAnswertext(),
                'points' => (float) $answer_obj->getPoints(),
                'points_unchecked' => (float) $answer_obj->getPointsUnchecked(),
                'order' => $order,
                'coords' => $answer_obj->getCoords(),
                'state' => $answer_obj->getState(),
                'feedback' => $this->formatSAQuestion(
                    $this->feedbackOBJ->getSpecificAnswerFeedbackExportPresentation($this->getId(), 0, $key)
                )
            ]);
            $order++;
        }
        $result['answers'] = $answers;

        return $result;
    }
}
