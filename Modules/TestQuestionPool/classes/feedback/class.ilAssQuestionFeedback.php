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

/**
 * abstract parent feedback class for all question types
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/TestQuestionPool
 *
 * @abstract
 */
abstract class ilAssQuestionFeedback
{
    public const CSS_CLASS_FEEDBACK_CORRECT = 'ilc_qfeedr_FeedbackRight';
    public const CSS_CLASS_FEEDBACK_WRONG = 'ilc_qfeedw_FeedbackWrong';

    /**
     * type for generic feedback page objects
     */
    public const PAGE_OBJECT_TYPE_GENERIC_FEEDBACK = 'qfbg';

    /**
     * type for specific feedback page objects
     */
    public const PAGE_OBJECT_TYPE_SPECIFIC_FEEDBACK = 'qfbs';

    /**
     * id for page object relating to generic incomplete solution feedback
     */
    public const FEEDBACK_SOLUTION_INCOMPLETE_PAGE_OBJECT_ID = 1;

    /**
     * id for page object relating to generic complete solution feedback
     */
    public const FEEDBACK_SOLUTION_COMPLETE_PAGE_OBJECT_ID = 2;

    public const TABLE_NAME_GENERIC_FEEDBACK = 'qpl_fb_generic';

    protected assQuestion $questionOBJ;

    protected ilCtrl $ctrl;

    protected ilDBInterface $db;

    protected ilLanguage $lng;

    protected string $page_obj_output_mode = "presentation";

    /**
     * constructor
     *
     * @final
     * @access public
     * @param assQuestion $questionOBJ
     * @param ilCtrl $ctrl
     * @param ilDBInterface $db
     * @param ilLanguage $lng
     */
    final public function __construct(assQuestion $questionOBJ, ilCtrl $ctrl, ilDBInterface $db, ilLanguage $lng)
    {
        $this->questionOBJ = $questionOBJ;
        $this->ctrl = $ctrl;
        $this->lng = $lng;
        $this->db = $db;
    }

    /**
     * returns the html of GENERIC feedback for the given question id for test presentation
     * (either for the complete solution or for the incomplete solution)
     */
    public function getGenericFeedbackTestPresentation(int $question_id, bool $solution_completed): string
    {
        if ($this->page_obj_output_mode == "edit") {
            return '';
        }
        if ($this->questionOBJ->isAdditionalContentEditingModePageObject()) {
            return $this->cleanupPageContent(
                $this->getPageObjectContent(
                    $this->getGenericFeedbackPageObjectType(),
                    $this->getGenericFeedbackPageObjectId($question_id, $solution_completed)
                )
            );
        }
        return $this->getGenericFeedbackContent($question_id, $solution_completed);
    }

    /**
     * returns the html of SPECIFIC feedback for the given question id
     * and answer index for test presentation
     *
     * @abstract
     * @access public
     * @param integer $question_id
     * @param integer $question_index
     * @param integer $answer_index
     * @return string $specificAnswerFeedbackTestPresentationHTML
     */
    abstract public function getSpecificAnswerFeedbackTestPresentation(int $question_id, int $question_index, int $answer_index): string;

    /**
     * completes a given form object with the GENERIC form properties
     * required by all question types
     */
    final public function completeGenericFormProperties(ilPropertyFormGUI $form): void
    {
        $form->addItem($this->buildFeedbackContentFormProperty(
            $this->lng->txt('feedback_complete_solution'),
            'feedback_complete',
            $this->questionOBJ->isAdditionalContentEditingModePageObject()
        ));

        $form->addItem($this->buildFeedbackContentFormProperty(
            $this->lng->txt('feedback_incomplete_solution'),
            'feedback_incomplete',
            $this->questionOBJ->isAdditionalContentEditingModePageObject()
        ));
    }

    /**
     * completes a given form object with the SPECIFIC form properties
     * required by this question type
     *
     * @abstract
     * @access public
     * @param ilPropertyFormGUI $form
     */
    abstract public function completeSpecificFormProperties(ilPropertyFormGUI $form): void;

    /**
     * initialises a given form object's GENERIC form properties
     * relating to all question types
     */
    final public function initGenericFormProperties(ilPropertyFormGUI $form): void
    {
        if ($this->questionOBJ->isAdditionalContentEditingModePageObject()) {
            $page_object_type = $this->getGenericFeedbackPageObjectType();

            $page_object_id = $this->getGenericFeedbackId($this->questionOBJ->getId(), true);

            if($page_object_id === -1) {
                $this->ctrl->setParameterByClass(ilAssQuestionFeedbackEditingGUI::class, 'feedback_type', $page_object_type);
                $this->ctrl->setParameterByClass(ilAssQuestionFeedbackEditingGUI::class, 'fb_mode', 'complete');
                $link = $this->ctrl->getLinkTargetByClass(ilAssQuestionFeedbackEditingGUI::class, 'createFeedbackPage');
                $value_feedback_solution_complete = sprintf(
                    '<a href="%s">%s</a>',
                    $link,
                    $this->lng->txt('tst_question_feedback_edit_page')
                );
                $this->ctrl->setParameterByClass(ilAssQuestionFeedbackEditingGUI::class, 'fb_mode', 'incomplete');
                $link = $this->ctrl->getLinkTargetByClass(ilAssQuestionFeedbackEditingGUI::class, 'createFeedbackPage');
                $value_feedback_solution_incomplete = sprintf(
                    '<a href="%s">%s</a>',
                    $link,
                    $this->lng->txt('tst_question_feedback_edit_page')
                );
            } else {
                $this->ensurePageObjectExists($page_object_type, $page_object_id);

                $value_feedback_solution_complete = $this->getPageObjectNonEditableValueHTML(
                    $page_object_type,
                    $this->getGenericFeedbackPageObjectId($this->questionOBJ->getId(), true)
                );
                $value_feedback_solution_incomplete = $this->getPageObjectNonEditableValueHTML(
                    $page_object_type,
                    $this->getGenericFeedbackPageObjectId($this->questionOBJ->getId(), false)
                );
            }

        } else {
            $value_feedback_solution_complete = $this->getGenericFeedbackContent(
                $this->questionOBJ->getId(),
                true
            );

            $value_feedback_solution_incomplete = $this->getGenericFeedbackContent(
                $this->questionOBJ->getId(),
                false
            );
        }

        $form->getItemByPostVar('feedback_complete')->setValue($value_feedback_solution_complete);
        $form->getItemByPostVar('feedback_incomplete')->setValue($value_feedback_solution_incomplete);
    }

    /**
     * initialises a given form object's SPECIFIC form properties
     * relating to this question type
     */
    abstract public function initSpecificFormProperties(ilPropertyFormGUI $form): void;

    /**
     * saves a given form object's GENERIC form properties
     * relating to all question types
     */
    final public function saveGenericFormProperties(ilPropertyFormGUI $form): void
    {
        if (!$this->questionOBJ->isAdditionalContentEditingModePageObject()) {
            $this->saveGenericFeedbackContent($this->questionOBJ->getId(), false, (string) $form->getInput('feedback_incomplete'));
            $this->saveGenericFeedbackContent($this->questionOBJ->getId(), true, (string) $form->getInput('feedback_complete'));
        }
    }

    /**
     * saves a given form object's SPECIFIC form properties
     * relating to this question type
     */
    abstract public function saveSpecificFormProperties(ilPropertyFormGUI $form): void;

    /**
     * returns the fact wether the feedback editing form is saveable in page object editing or not.
     * by default all properties are edited as page object unless there are additional settings
     * (this method can be overwritten per question type if required)
     */
    public function isSaveableInPageObjectEditingMode(): bool
    {
        return false;
    }

    /**
     * builds and returns a form property gui object with the given label and postvar
     * that is addable to property forms
     * depending on the given flag "asNonEditable" it returns a ...
     * - non editable gui
     * - textarea input gui
     * @return ilTextAreaInputGUI|ilNonEditableValueGUI
     */
    final protected function buildFeedbackContentFormProperty(string $label, string $post_var, bool $as_non_editable): ilSubEnabledFormPropertyGUI
    {
        if ($as_non_editable) {
            $property = new ilNonEditableValueGUI($label, $post_var, true);
        } else {
            $property = new ilTextAreaInputGUI($label, $post_var);
            $property->setRequired(false);
            $property->setRows(10);
            $property->setCols(80);

            if (!$this->questionOBJ->getPreventRteUsage()) {
                $property->setUseRte(true);
                $property->addPlugin("latex");
                $property->addButton("latex");
                $property->addButton("pastelatex");

                $property->setRteTags(ilObjAdvancedEditing::_getUsedHTMLTags("assessment"));
                $property->setRTESupport($this->questionOBJ->getId(), "qpl", "assessment");
            } else {
                $property->setRteTags(ilAssSelfAssessmentQuestionFormatter::getSelfAssessmentTags());
                $property->setUseTagsForRteOnly(false);
            }

            $property->setRTESupport($this->questionOBJ->getId(), "qpl", "assessment");
        }

        return $property;
    }

    /**
     * returns the GENERIC feedback content for a given question state.
     * the state is either the completed solution (all answers correct)
     * of the question or at least one incorrect answer.
     */
    final public function getGenericFeedbackContent(int $question_id, bool $solution_completed): string
    {
        $res = $this->db->queryF(
            "SELECT * FROM {$this->getGenericFeedbackTableName()} WHERE question_fi = %s AND correctness = %s",
            ['integer', 'text'],
            [$question_id, (int) $solution_completed]
        );

        $feedback_content = '';

        if ($this->db->numRows($res) > 0) {
            $row = $this->db->fetchAssoc($res);
            $feedback_content = ilRTE::_replaceMediaObjectImageSrc(
                $this->questionOBJ->getHtmlQuestionContentPurifier()->purify($row['feedback'] ?? ''),
                1
            );
        }
        return $feedback_content;
    }

    abstract public function getSpecificAnswerFeedbackContent(int $question_id, int $question_index, int $answer_index): string;

    abstract public function getAllSpecificAnswerFeedbackContents(int $question_id): string;

    public function isSpecificAnswerFeedbackAvailable(int $question_id): bool
    {
        $res = $this->db->queryF(
            "SELECT answer FROM {$this->getSpecificFeedbackTableName()} WHERE question_fi = %s",
            ['integer'],
            [$question_id]
        );

        $all_feedback_contents = '';

        while ($row = $this->db->fetchAssoc($res)) {
            $all_feedback_contents .= $this->getSpecificAnswerFeedbackExportPresentation(
                $this->questionOBJ->getId(),
                0,
                $row['answer']
            );
        }

        return trim(strip_tags($all_feedback_contents)) !== '';
    }

    /**
     * saves GENERIC feedback content for the given question id to the database.
     * Generic feedback is either feedback for the completed solution (all answers correct)
     * of the question or at least onen incorrect answer.
     */
    final public function saveGenericFeedbackContent(int $question_id, bool $solution_completed, string $feedback_content): int
    {
        $feedbackId = $this->getGenericFeedbackId($question_id, $solution_completed);

        if ($feedback_content !== '') {
            $feedback_content = $this->questionOBJ->getHtmlQuestionContentPurifier()->purify($feedback_content);
            $feedback_content = ilRTE::_replaceMediaObjectImageSrc($feedback_content, 0);
        }

        if ($feedbackId != -1) {
            $this->db->update(
                $this->getGenericFeedbackTableName(),
                [
                    'feedback' => ['clob', $feedback_content],
                    'tstamp' => ['integer', time()]
                ],
                [
                    'feedback_id' => ['integer', $feedbackId]
                ]
            );
        } else {
            $feedbackId = $this->db->nextId($this->getGenericFeedbackTableName());

            $this->db->insert($this->getGenericFeedbackTableName(), [
                'feedback_id' => ['integer', $feedbackId],
                'question_fi' => ['integer', $question_id],
                'correctness' => ['text', (int) $solution_completed], // text ?
                'feedback' => ['clob', $feedback_content],
                'tstamp' => ['integer', time()]
            ]);
        }

        return $feedbackId;
    }

    abstract public function saveSpecificAnswerFeedbackContent(int $question_id, int $question_index, int $answer_index, string $feedback_content): int;

    /**
     * deletes all GENERIC feedback contents (and page objects if required)
     * for the given question id
     */
    final public function deleteGenericFeedbacks(int $question_id, bool $isAdditionalContentEditingModePageObject): void
    {
        if ($isAdditionalContentEditingModePageObject) {
            $this->ensurePageObjectDeleted(
                $this->getGenericFeedbackPageObjectType(),
                $this->getGenericFeedbackPageObjectId($question_id, true)
            );

            $this->ensurePageObjectDeleted(
                $this->getGenericFeedbackPageObjectType(),
                $this->getGenericFeedbackPageObjectId($question_id, false)
            );
        }

        $this->db->manipulateF(
            "DELETE FROM {$this->getGenericFeedbackTableName()} WHERE question_fi = %s",
            ['integer'],
            [$question_id]
        );
    }

    abstract public function deleteSpecificAnswerFeedbacks(int $question_id, bool $isAdditionalContentEditingModePageObject): void;

    /**
     * duplicates the feedback relating to the given original question id
     * and saves it for the given duplicate question id
     */
    final public function duplicateFeedback(int $originalQuestionId, int $duplicateQuestionId): void
    {
        $this->duplicateGenericFeedback($originalQuestionId, $duplicateQuestionId);
        $this->duplicateSpecificFeedback($originalQuestionId, $duplicateQuestionId);
    }

    /**
     * duplicates the GENERIC feedback relating to the given original question id
     * and saves it for the given duplicate question id
     */
    private function duplicateGenericFeedback(int $originalQuestionId, int $duplicateQuestionId): void
    {
        $res = $this->db->queryF(
            "SELECT * FROM {$this->getGenericFeedbackTableName()} WHERE question_fi = %s",
            ['integer'],
            [$originalQuestionId]
        );

        while ($row = $this->db->fetchAssoc($res)) {
            $feedbackId = $this->db->nextId($this->getGenericFeedbackTableName());

            $this->db->insert($this->getGenericFeedbackTableName(), [
                'feedback_id' => ['integer', $feedbackId],
                'question_fi' => ['integer', $duplicateQuestionId],
                'correctness' => ['text', $row['correctness']],
                'feedback' => ['clob', $row['feedback']],
                'tstamp' => ['integer', time()]
            ]);

            if ($this->questionOBJ->isAdditionalContentEditingModePageObject()) {
                $page_object_type = $this->getGenericFeedbackPageObjectType();
                $this->duplicatePageObject($page_object_type, $row['feedback_id'], $feedbackId, $duplicateQuestionId);
            }
        }
    }

    /**
     * duplicates the SPECIFIC feedback relating to the given original question id
     * and saves it for the given duplicate question id
     */
    abstract protected function duplicateSpecificFeedback(int $originalQuestionId, int $duplicateQuestionId): void;

    /**
     * syncs the feedback from a duplicated question back to the original question
     */
    final public function syncFeedback(int $originalQuestionId, int $duplicateQuestionId): void
    {
        $this->syncGenericFeedback($originalQuestionId, $duplicateQuestionId);
        $this->syncSpecificFeedback($originalQuestionId, $duplicateQuestionId);
    }

    /**
     * syncs the GENERIC feedback from a duplicated question back to the original question
     */
    private function syncGenericFeedback(int $originalQuestionId, int $duplicateQuestionId): void
    {
        // delete generic feedback of the original question
        $this->db->manipulateF(
            "DELETE FROM {$this->getGenericFeedbackTableName()} WHERE question_fi = %s",
            ['integer'],
            [$originalQuestionId]
        );

        // get generic feedback of the actual (duplicated) question
        $result = $this->db->queryF(
            "SELECT * FROM {$this->getGenericFeedbackTableName()} WHERE question_fi = %s",
            ['integer'],
            [$duplicateQuestionId]
        );

        // save generic feedback to the original question
        while ($row = $this->db->fetchAssoc($result)) {
            $nextId = $this->db->nextId($this->getGenericFeedbackTableName());

            $this->db->insert($this->getGenericFeedbackTableName(), [
                'feedback_id' => ['integer', $nextId],
                'question_fi' => ['integer', $originalQuestionId],
                'correctness' => ['text', $row['correctness']],
                'feedback' => ['clob', $row['feedback']],
                'tstamp' => ['integer', time()]
            ]);
        }
    }

    /**
     * returns the SPECIFIC answer feedback ID for a given question id and answer index.
     */
    final protected function getGenericFeedbackId(int $question_id, bool $solution_completed): int
    {
        $res = $this->db->queryF(
            "SELECT feedback_id FROM {$this->getGenericFeedbackTableName()} WHERE question_fi = %s AND correctness = %s",
            ['integer','text'],
            [$question_id, (int) $solution_completed]
        );

        $feedbackId = -1;
        if ($this->db->numRows($res)) {
            $row = $this->db->fetchAssoc($res);
            $feedbackId = (int) $row['feedback_id'];
        }

        return $feedbackId;
    }

    protected function isGenericFeedbackId(int $feedbackId): bool
    {
        $row = $this->db->fetchAssoc($this->db->queryF(
            "SELECT COUNT(feedback_id) cnt FROM {$this->getGenericFeedbackTableName()}
					WHERE question_fi = %s AND feedback_id = %s",
            ['integer','integer'],
            [$this->questionOBJ->getId(), $feedbackId]
        ));


        return (bool) $row['cnt'];
    }

    abstract protected function isSpecificAnswerFeedbackId(int $feedbackId): bool;

    final public function checkFeedbackParent(int $feedbackId): bool
    {
        if ($this->isGenericFeedbackId($feedbackId)) {
            return true;
        }

        if ($this->isSpecificAnswerFeedbackId($feedbackId)) {
            return true;
        }

        return false;
    }

    /**
     * syncs the SPECIFIC feedback from a duplicated question back to the original question
     */
    abstract protected function syncSpecificFeedback(int $originalQuestionId, int $duplicateQuestionId): void;

    final protected function getGenericFeedbackTableName(): string
    {
        return self::TABLE_NAME_GENERIC_FEEDBACK;
    }

    /**
     * returns html content to be used as value for non editable value form properties
     * in feedback editing form
     */
    final protected function getPageObjectNonEditableValueHTML(string $page_object_type, int $page_object_id): string
    {
        $link = $this->getPageObjectEditingLink($page_object_type, $page_object_id);
        $content = $this->getPageObjectContent($page_object_type, $page_object_id);
        return sprintf(
            '<a href="%s">%s</a><br /><br />%s',
            $link,
            $this->lng->txt('tst_question_feedback_edit_page'),
            $content
        );
    }

    public function getClassNameByType(string $a_type, bool $a_gui = false): string
    {
        $gui = ($a_gui) ? "GUI" : "";

        if ($a_type == ilAssQuestionFeedback::PAGE_OBJECT_TYPE_GENERIC_FEEDBACK) {
            return "ilAssGenFeedbackPage" . $gui;
        }

        //if ($a_type == ilAssQuestionFeedback::PAGE_OBJECT_TYPE_SPECIFIC_FEEDBACK) {
        return "ilAssSpecFeedbackPage" . $gui;
    }

    private function getPageObjectEditingLink(string $page_object_type, int $page_object_id): string
    {
        $cl = $this->getClassNameByType($page_object_type, true);
        $this->ctrl->setParameterByClass($cl, 'feedback_type', $page_object_type);
        $this->ctrl->setParameterByClass($cl, 'feedback_id', $page_object_id);

        return $this->ctrl->getLinkTargetByClass($cl, 'edit');
    }

    final public function setPageObjectOutputMode(string $page_obj_output_mode): void
    {
        $this->page_obj_output_mode = $page_obj_output_mode;
    }

    final public function getPageObjectOutputMode(): string
    {
        return $this->page_obj_output_mode;
    }

    final protected function getPageObjectContent(string $page_object_type, int $page_object_id): string
    {
        $cl = $this->getClassNameByType($page_object_type, true);

        $this->ensurePageObjectExists($page_object_type, $page_object_id);

        $mode = ($this->ctrl->isAsynch()) ? "presentation" : $this->getPageObjectOutputMode();

        /** @var ilPageObjectGUI $pageObjectGUI */
        $pageObjectGUI = new $cl($page_object_id);
        return $pageObjectGUI->presentation($mode);
    }

    final protected function getPageObjectXML(string $page_object_type, int $page_object_id): string
    {
        $cl = $this->getClassNameByType($page_object_type);

        $this->ensurePageObjectExists($page_object_type, $page_object_id);

        $pageObject = new $cl($page_object_id);
        return $pageObject->getXMLContent();
    }

    private function ensurePageObjectExists(string $page_object_type, int $page_object_id): void
    {
        if ($page_object_type == ilAssQuestionFeedback::PAGE_OBJECT_TYPE_GENERIC_FEEDBACK
            && !ilAssGenFeedbackPage::_exists($page_object_type, $page_object_id, '', true)) {
            $pageObject = new ilAssGenFeedbackPage();
            $pageObject->setParentId($this->questionOBJ->getId());
            $pageObject->setId($page_object_id);
            $pageObject->createFromXML();
        }
        if ($page_object_type == ilAssQuestionFeedback::PAGE_OBJECT_TYPE_SPECIFIC_FEEDBACK
            && !ilAssSpecFeedbackPage::_exists($page_object_type, $page_object_id, '', true)) {
            $pageObject = new ilAssSpecFeedbackPage();
            $pageObject->setParentId($this->questionOBJ->getId());
            $pageObject->setId($page_object_id);
            $pageObject->createFromXML();
        }
    }

    final protected function createPageObject(string $page_object_type, int $page_object_id, string $page_object_content): void
    {
        $cl = $this->getClassNameByType($page_object_type);

        $pageObject = new $cl();
        $pageObject->setParentId($this->questionOBJ->getId());
        $pageObject->setId($page_object_id);
        $pageObject->setXMLContent($page_object_content);
        $pageObject->createFromXML();
    }

    final protected function duplicatePageObject(string $page_object_type, int $original_page_object_id, int $duplicate_page_object_id, int $duplicate_page_object_parent_id): void
    {
        $this->ensurePageObjectExists($page_object_type, $original_page_object_id);

        $cl = $this->getClassNameByType($page_object_type);

        $pageObject = new $cl($original_page_object_id);
        $pageObject->setParentId($duplicate_page_object_parent_id);
        $pageObject->setId($duplicate_page_object_id);
        $pageObject->createFromXML();
    }

    final protected function ensurePageObjectDeleted(string $page_object_type, int $page_object_id): void
    {
        if ($page_object_type == ilAssQuestionFeedback::PAGE_OBJECT_TYPE_GENERIC_FEEDBACK) {
            if (ilAssGenFeedbackPage::_exists($page_object_type, $page_object_id)) {
                $pageObject = new ilAssGenFeedbackPage($page_object_id);
                $pageObject->delete();
            }
        }
        if ($page_object_type == ilAssQuestionFeedback::PAGE_OBJECT_TYPE_SPECIFIC_FEEDBACK) {
            if (ilAssSpecFeedbackPage::_exists($page_object_type, $page_object_id)) {
                $pageObject = new ilAssSpecFeedbackPage($page_object_id);
                $pageObject->delete();
            }
        }
    }

    final protected function getGenericFeedbackPageObjectType(): string
    {
        return self::PAGE_OBJECT_TYPE_GENERIC_FEEDBACK;
    }

    final protected function getSpecificAnswerFeedbackPageObjectType(): string
    {
        return self::PAGE_OBJECT_TYPE_SPECIFIC_FEEDBACK;
    }

    /**
     * returns the fact whether the given page object type
     * relates to generic or specific feedback page objects
     */
    final public static function isValidFeedbackPageObjectType(string $feedbackPageObjectType): bool
    {
        switch ($feedbackPageObjectType) {
            case self::PAGE_OBJECT_TYPE_GENERIC_FEEDBACK:
            case self::PAGE_OBJECT_TYPE_SPECIFIC_FEEDBACK:
                return true;
        }

        return false;
    }

    /**
     * returns a useable page object id for generic feedback page objects
     * for the given question id for either the complete or incomplete solution
     * (using the id sequence of non page object generic feedback)
     */
    final protected function getGenericFeedbackPageObjectId(int $question_id, bool $solution_completed): int
    {
        $page_object_id = $this->getGenericFeedbackId($question_id, $solution_completed);
        return $page_object_id;
    }

    /**
     * returns the generic feedback export presentation for given question id
     * either for solution completed or incompleted
     */
    public function getGenericFeedbackExportPresentation(int $question_id, bool $solution_completed): string
    {
        if ($this->questionOBJ->isAdditionalContentEditingModePageObject()) {
            $genericFeedbackExportPresentation = $this->getPageObjectXML(
                $this->getGenericFeedbackPageObjectType(),
                $this->getGenericFeedbackPageObjectId($question_id, $solution_completed)
            );
        } else {
            $genericFeedbackExportPresentation = $this->getGenericFeedbackContent($question_id, $solution_completed);
        }

        return $genericFeedbackExportPresentation;
    }

    /**
     * returns the generic feedback export presentation for given question id
     * either for solution completed or incompleted
     */
    abstract public function getSpecificAnswerFeedbackExportPresentation(int $question_id, int $question_index, int $answer_index): string;

    /**
     * imports the given feedback content as generic feedback for the given question id
     * for either the complete or incomplete solution
     */
    public function importGenericFeedback(int $question_id, bool $solution_completed, string $feedback_content): void
    {
        if ($this->questionOBJ->isAdditionalContentEditingModePageObject()) {
            $page_object_id = $this->saveGenericFeedbackContent($question_id, $solution_completed, '');
            $page_object_type = $this->getGenericFeedbackPageObjectType();

            $this->createPageObject($page_object_type, $page_object_id, $feedback_content);
        } else {
            $this->saveGenericFeedbackContent($question_id, $solution_completed, $feedback_content);
        }
    }

    abstract public function importSpecificAnswerFeedback(int $question_id, int $question_index, int $answer_index, string $feedback_content): void;

    public function migrateContentForLearningModule(ilAssSelfAssessmentMigrator $migrator, int $question_id): void
    {
        $this->saveGenericFeedbackContent($question_id, true, $migrator->migrateToLmContent(
            $this->getGenericFeedbackContent($question_id, true)
        ));

        $this->saveGenericFeedbackContent($question_id, false, $migrator->migrateToLmContent(
            $this->getGenericFeedbackContent($question_id, false)
        ));
    }

    protected function cleanupPageContent(string $content): string
    {
        $doc = new DOMDocument('1.0', 'UTF-8');
        if (@$doc->loadHTML('<html><body>' . $content . '</body></html>')) {
            $xpath = new DOMXPath($doc);
            $nodes_after_comments = $xpath->query('//comment()/following-sibling::*[1]');
            foreach ($nodes_after_comments as $node_after_comments) {
                if (trim($node_after_comments->nodeValue) === ''
                    && $node_after_comments->childElementCount === 0) {
                    return '';
                }
            }
        }
        return $content;
    }

    public function createFeedbackPages(string $mode): string
    {
        $page_object_type = ilAssQuestionFeedback::PAGE_OBJECT_TYPE_GENERIC_FEEDBACK;
        $page_object_id_complete = $this->saveGenericFeedbackContent(
            $this->questionOBJ->getId(),
            true,
            ''
        );
        $this->ensurePageObjectExists($page_object_type, $page_object_id_complete);

        $page_object_id_incomplete = $this->saveGenericFeedbackContent(
            $this->questionOBJ->getId(),
            false,
            ''
        );
        $this->ensurePageObjectExists($page_object_type, $page_object_id_incomplete);

        $page_object_id = ($mode === 'complete') ? $page_object_id_complete : $page_object_id_incomplete;
        return $this->getPageObjectEditingLink(
            $page_object_type,
            $page_object_id
        );
    }

}
