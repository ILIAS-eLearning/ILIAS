<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

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
    const CSS_CLASS_FEEDBACK_CORRECT = 'ilc_qfeedr_FeedbackRight';
    const CSS_CLASS_FEEDBACK_WRONG = 'ilc_qfeedw_FeedbackWrong';
    
    /**
     * type for generic feedback page objects
     */
    const PAGE_OBJECT_TYPE_GENERIC_FEEDBACK = 'qfbg';
    
    /**
     * type for specific feedback page objects
     */
    const PAGE_OBJECT_TYPE_SPECIFIC_FEEDBACK = 'qfbs';
    
    /**
     * id for page object relating to generic incomplete solution feedback
     */
    const FEEDBACK_SOLUTION_INCOMPLETE_PAGE_OBJECT_ID = 1;
    
    /**
     * id for page object relating to generic complete solution feedback
     */
    const FEEDBACK_SOLUTION_COMPLETE_PAGE_OBJECT_ID = 2;

    const TABLE_NAME_GENERIC_FEEDBACK = 'qpl_fb_generic';
    
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
    public function getGenericFeedbackTestPresentation(int $questionId, bool $solutionCompleted) : string
    {
        if ($this->page_obj_output_mode == "edit") {
            return "";
        }
        if ($this->questionOBJ->isAdditionalContentEditingModePageObject()) {
            $genericFeedbackTestPresentationHTML = $this->getPageObjectContent(
                $this->getGenericFeedbackPageObjectType(),
                $this->getGenericFeedbackPageObjectId($questionId, $solutionCompleted)
            );
        } else {
            $genericFeedbackTestPresentationHTML = $this->getGenericFeedbackContent($questionId, $solutionCompleted);
        }
        return $genericFeedbackTestPresentationHTML;
    }
    
    /**
     * returns the html of SPECIFIC feedback for the given question id
     * and answer index for test presentation
     *
     * @abstract
     * @access public
     * @param integer $questionId
     * @param integer $questionIndex
     * @param integer $answerIndex
     * @return string $specificAnswerFeedbackTestPresentationHTML
     */
    abstract public function getSpecificAnswerFeedbackTestPresentation(int $questionId, int $questionIndex, int $answerIndex) : string;

    /**
     * completes a given form object with the GENERIC form properties
     * required by all question types
     */
    final public function completeGenericFormProperties(ilPropertyFormGUI $form) : void
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
    abstract public function completeSpecificFormProperties(ilPropertyFormGUI $form) : void;
    
    /**
     * initialises a given form object's GENERIC form properties
     * relating to all question types
     */
    final public function initGenericFormProperties(ilPropertyFormGUI $form) : void
    {
        if ($this->questionOBJ->isAdditionalContentEditingModePageObject()) {
            $pageObjectType = $this->getGenericFeedbackPageObjectType();
            
            $valueFeedbackSolutionComplete = $this->getPageObjectNonEditableValueHTML(
                $pageObjectType,
                $this->getGenericFeedbackPageObjectId($this->questionOBJ->getId(), true)
            );
            
            $valueFeedbackSolutionIncomplete = $this->getPageObjectNonEditableValueHTML(
                $pageObjectType,
                $this->getGenericFeedbackPageObjectId($this->questionOBJ->getId(), false)
            );
        } else {
            $valueFeedbackSolutionComplete = $this->getGenericFeedbackContent(
                $this->questionOBJ->getId(),
                true
            );
            
            $valueFeedbackSolutionIncomplete = $this->getGenericFeedbackContent(
                $this->questionOBJ->getId(),
                false
            );
        }
        
        $form->getItemByPostVar('feedback_complete')->setValue($valueFeedbackSolutionComplete);
        $form->getItemByPostVar('feedback_incomplete')->setValue($valueFeedbackSolutionIncomplete);
    }
    
    /**
     * initialises a given form object's SPECIFIC form properties
     * relating to this question type
     */
    abstract public function initSpecificFormProperties(ilPropertyFormGUI $form) : void;
    
    /**
     * saves a given form object's GENERIC form properties
     * relating to all question types
     */
    final public function saveGenericFormProperties(ilPropertyFormGUI $form) : void
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
    abstract public function saveSpecificFormProperties(ilPropertyFormGUI $form) : void;
    
    /**
     * returns the fact wether the feedback editing form is saveable in page object editing or not.
     * by default all properties are edited as page object unless there are additional settings
     * (this method can be overwritten per question type if required)
     */
    public function isSaveableInPageObjectEditingMode() : bool
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
    final protected function buildFeedbackContentFormProperty(string $label, string $postVar, bool $asNonEditable) : ilSubEnabledFormPropertyGUI
    {
        if ($asNonEditable) {
            require_once 'Services/Form/classes/class.ilNonEditableValueGUI.php';
            
            $property = new ilNonEditableValueGUI($label, $postVar, true);
        } else {
            require_once 'Services/Form/classes/class.ilTextAreaInputGUI.php';
            require_once 'Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php';
            
            $property = new ilTextAreaInputGUI($label, $postVar);
            $property->setRequired(false);
            $property->setRows(10);
            $property->setCols(80);
            
            if (!$this->questionOBJ->getPreventRteUsage()) {
                $property->setUseRte(true);
                $property->addPlugin("latex");
                $property->addButton("latex");
                $property->addButton("pastelatex");

                require_once 'Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php';
                $property->setRteTags(ilObjAdvancedEditing::_getUsedHTMLTags("assessment"));
                $property->setRTESupport($this->questionOBJ->getId(), "qpl", "assessment");
            } else {
                require_once 'Modules/TestQuestionPool/classes/questions/class.ilAssSelfAssessmentQuestionFormatter.php';
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
    final public function getGenericFeedbackContent(int $questionId, bool $solutionCompleted) : string
    {
        require_once 'Services/RTE/classes/class.ilRTE.php';

        $res = $this->db->queryF(
            "SELECT * FROM {$this->getGenericFeedbackTableName()} WHERE question_fi = %s AND correctness = %s",
            array('integer', 'text'),
            array($questionId, (int) $solutionCompleted)
        );

        $feedbackContent = '';

        if ($this->db->numRows($res) > 0) {
            $row = $this->db->fetchAssoc($res);
            $feedbackContent = ilRTE::_replaceMediaObjectImageSrc($row['feedback'], 1);
        }
        return $feedbackContent;
    }

    abstract public function getSpecificAnswerFeedbackContent(int $questionId, int $questionIndex, int $answerIndex) : string;

    abstract public function getAllSpecificAnswerFeedbackContents(int $questionId) : string;
    
    public function isSpecificAnswerFeedbackAvailable(int $questionId) : bool
    {
        return (bool) strlen($this->getAllSpecificAnswerFeedbackContents($questionId));
    }

    /**
     * saves GENERIC feedback content for the given question id to the database.
     * Generic feedback is either feedback for the completed solution (all answers correct)
     * of the question or at least onen incorrect answer.
     */
    final public function saveGenericFeedbackContent(int $questionId, bool $solutionCompleted, string $feedbackContent) : int
    {
        require_once 'Services/RTE/classes/class.ilRTE.php';

        $feedbackId = $this->getGenericFeedbackId($questionId, $solutionCompleted);
        
        if (strlen($feedbackContent)) {
            $feedbackContent = ilRTE::_replaceMediaObjectImageSrc($feedbackContent, 0);
        }
        
        if ($feedbackId != -1) {
            $this->db->update(
                $this->getGenericFeedbackTableName(),
                array(
                    'feedback' => array('clob', $feedbackContent),
                    'tstamp' => array('integer', time())
                ),
                array(
                    'feedback_id' => array('integer', $feedbackId)
                )
            );
        } else {
            $feedbackId = $this->db->nextId($this->getGenericFeedbackTableName());
            
            $this->db->insert($this->getGenericFeedbackTableName(), array(
                'feedback_id' => array('integer', $feedbackId),
                'question_fi' => array('integer', $questionId),
                'correctness' => array('text', (int) $solutionCompleted), // text ?
                'feedback' => array('clob', $feedbackContent),
                'tstamp' => array('integer', time())
            ));
        }
        
        return $feedbackId;
    }
    
    abstract public function saveSpecificAnswerFeedbackContent(int $questionId, int $questionIndex, int $answerIndex, string $feedbackContent) : int;
    
    /**
     * deletes all GENERIC feedback contents (and page objects if required)
     * for the given question id
     */
    final public function deleteGenericFeedbacks(int $questionId, bool $isAdditionalContentEditingModePageObject) : void
    {
        if ($isAdditionalContentEditingModePageObject) {
            $this->ensurePageObjectDeleted(
                $this->getGenericFeedbackPageObjectType(),
                $this->getGenericFeedbackPageObjectId($questionId, true)
            );
            
            $this->ensurePageObjectDeleted(
                $this->getGenericFeedbackPageObjectType(),
                $this->getGenericFeedbackPageObjectId($questionId, false)
            );
        }
        
        $this->db->manipulateF(
            "DELETE FROM {$this->getGenericFeedbackTableName()} WHERE question_fi = %s",
            array('integer'),
            array($questionId)
        );
    }
    
    abstract public function deleteSpecificAnswerFeedbacks(int $questionId, bool $isAdditionalContentEditingModePageObject) : void;

    /**
     * duplicates the feedback relating to the given original question id
     * and saves it for the given duplicate question id
     */
    final public function duplicateFeedback(int $originalQuestionId, int $duplicateQuestionId) : void
    {
        $this->duplicateGenericFeedback($originalQuestionId, $duplicateQuestionId);
        $this->duplicateSpecificFeedback($originalQuestionId, $duplicateQuestionId);
    }
    
    /**
     * duplicates the GENERIC feedback relating to the given original question id
     * and saves it for the given duplicate question id
     */
    private function duplicateGenericFeedback(int $originalQuestionId, int $duplicateQuestionId) : void
    {
        $res = $this->db->queryF(
            "SELECT * FROM {$this->getGenericFeedbackTableName()} WHERE question_fi = %s",
            array('integer'),
            array($originalQuestionId)
        );
        
        while ($row = $this->db->fetchAssoc($res)) {
            $feedbackId = $this->db->nextId($this->getGenericFeedbackTableName());
            
            $this->db->insert($this->getGenericFeedbackTableName(), array(
                'feedback_id' => array('integer', $feedbackId),
                'question_fi' => array('integer', $duplicateQuestionId),
                'correctness' => array('text', $row['correctness']),
                'feedback' => array('clob', $row['feedback']),
                'tstamp' => array('integer', time())
            ));
            
            if ($this->questionOBJ->isAdditionalContentEditingModePageObject()) {
                $pageObjectType = $this->getGenericFeedbackPageObjectType();
                $this->duplicatePageObject($pageObjectType, $row['feedback_id'], $feedbackId, $duplicateQuestionId);
            }
        }
    }
    
    /**
     * duplicates the SPECIFIC feedback relating to the given original question id
     * and saves it for the given duplicate question id
     */
    abstract protected function duplicateSpecificFeedback(int $originalQuestionId, int $duplicateQuestionId) : void;
    
    /**
     * syncs the feedback from a duplicated question back to the original question
     */
    final public function syncFeedback(int $originalQuestionId, int $duplicateQuestionId) : void
    {
        $this->syncGenericFeedback($originalQuestionId, $duplicateQuestionId);
        $this->syncSpecificFeedback($originalQuestionId, $duplicateQuestionId);
    }
    
    /**
     * syncs the GENERIC feedback from a duplicated question back to the original question
     */
    private function syncGenericFeedback(int $originalQuestionId, int $duplicateQuestionId) : void
    {
        // delete generic feedback of the original question
        $this->db->manipulateF(
            "DELETE FROM {$this->getGenericFeedbackTableName()} WHERE question_fi = %s",
            array('integer'),
            array($originalQuestionId)
        );
            
        // get generic feedback of the actual (duplicated) question
        $result = $this->db->queryF(
            "SELECT * FROM {$this->getGenericFeedbackTableName()} WHERE question_fi = %s",
            array('integer'),
            array($duplicateQuestionId)
        );

        // save generic feedback to the original question
        while ($row = $this->db->fetchAssoc($result)) {
            $nextId = $this->db->nextId($this->getGenericFeedbackTableName());
            
            $this->db->insert($this->getGenericFeedbackTableName(), array(
                'feedback_id' => array('integer', $nextId),
                'question_fi' => array('integer', $originalQuestionId),
                'correctness' => array('text', $row['correctness']),
                'feedback' => array('clob', $row['feedback']),
                'tstamp' => array('integer', time())
            ));
        }
    }
    
    /**
     * returns the SPECIFIC answer feedback ID for a given question id and answer index.
     */
    final protected function getGenericFeedbackId(int $questionId, bool $solutionCompleted) : int
    {
        $res = $this->db->queryF(
            "SELECT feedback_id FROM {$this->getGenericFeedbackTableName()} WHERE question_fi = %s AND correctness = %s",
            array('integer','text'),
            array($questionId, (int) $solutionCompleted)
        );
        
        $feedbackId = -1;
        if ($this->db->numRows($res)) {
            $row = $this->db->fetchAssoc($res);
            $feedbackId = (int) $row['feedback_id'];
        }

        return $feedbackId;
    }
    
    protected function isGenericFeedbackId(int $feedbackId) : bool
    {
        $row = $this->db->fetchAssoc($this->db->queryF(
            "SELECT COUNT(feedback_id) cnt FROM {$this->getGenericFeedbackTableName()}
					WHERE question_fi = %s AND feedback_id = %s",
            array('integer','integer'),
            array($this->questionOBJ->getId(), $feedbackId)
        ));
        
        
        return (bool) $row['cnt'];
    }
    
    abstract protected function isSpecificAnswerFeedbackId(int $feedbackId) : bool;
    
    final public function checkFeedbackParent(int $feedbackId) : bool
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
    abstract protected function syncSpecificFeedback(int $originalQuestionId, int $duplicateQuestionId) : void;

    final protected function getGenericFeedbackTableName() : string
    {
        return self::TABLE_NAME_GENERIC_FEEDBACK;
    }
    
    /**
     * returns html content to be used as value for non editable value form properties
     * in feedback editing form
     */
    final protected function getPageObjectNonEditableValueHTML(string $pageObjectType, int $pageObjectId) : string
    {
        $link = $this->getPageObjectEditingLink($pageObjectType, $pageObjectId);
        $content = $this->getPageObjectContent($pageObjectType, $pageObjectId);

        return "$link<br /><br />$content";
    }
    
    public function getClassNameByType(string $a_type, bool $a_gui = false) : string
    {
        $gui = ($a_gui) ? "GUI" : "";
        include_once("./Modules/TestQuestionPool/classes/feedback/class.ilAssQuestionFeedback.php");

        if ($a_type == ilAssQuestionFeedback::PAGE_OBJECT_TYPE_GENERIC_FEEDBACK) {
            return "ilAssGenFeedbackPage" . $gui;
        }

        //if ($a_type == ilAssQuestionFeedback::PAGE_OBJECT_TYPE_SPECIFIC_FEEDBACK) {
        return "ilAssSpecFeedbackPage" . $gui;
    }
    
    private function getPageObjectEditingLink(string $pageObjectType, int $pageObjectId) : string
    {
        $cl = $this->getClassNameByType($pageObjectType, true);
        $this->ctrl->setParameterByClass($cl, 'feedback_type', $pageObjectType);
        $this->ctrl->setParameterByClass($cl, 'feedback_id', $pageObjectId);
        
        $linkHREF = $this->ctrl->getLinkTargetByClass($cl, 'edit');
        $linkTEXT = $this->lng->txt('tst_question_feedback_edit_page');
        
        return "<a href='$linkHREF'>$linkTEXT</a>";
    }

    final public function setPageObjectOutputMode(string $page_obj_output_mode) : void
    {
        $this->page_obj_output_mode = $page_obj_output_mode;
    }

    final public function getPageObjectOutputMode() : string
    {
        return $this->page_obj_output_mode;
    }

    final protected function getPageObjectContent(string $pageObjectType, int $pageObjectId) : string
    {
        $cl = $this->getClassNameByType($pageObjectType, true);
        require_once 'Modules/TestQuestionPool/classes/feedback/class.' . $cl . '.php';

        $this->ensurePageObjectExists($pageObjectType, $pageObjectId);

        $mode = ($this->ctrl->isAsynch()) ? "presentation" : $this->getPageObjectOutputMode();

        $pageObjectGUI = new $cl($pageObjectId);
        $pageObjectGUI->setOutputMode($mode);

        return $pageObjectGUI->presentation($mode);
    }
    
    final protected function getPageObjectXML(string $pageObjectType, int $pageObjectId) : string
    {
        $cl = $this->getClassNameByType($pageObjectType);
        require_once 'Modules/TestQuestionPool/classes/feedback/class.' . $cl . '.php';

        $this->ensurePageObjectExists($pageObjectType, $pageObjectId);
        
        $pageObject = new $cl($pageObjectId);
        return $pageObject->getXMLContent();
    }
    
    private function ensurePageObjectExists(string $pageObjectType, int $pageObjectId) : void
    {
        if ($pageObjectType == ilAssQuestionFeedback::PAGE_OBJECT_TYPE_GENERIC_FEEDBACK) {
            include_once("./Modules/TestQuestionPool/classes/feedback/class.ilAssGenFeedbackPage.php");
            if (!ilAssGenFeedbackPage::_exists($pageObjectType, $pageObjectId)) {
                $pageObject = new ilAssGenFeedbackPage();
                $pageObject->setParentId($this->questionOBJ->getId());
                $pageObject->setId($pageObjectId);
                $pageObject->createFromXML();
            }
        }
        if ($pageObjectType == ilAssQuestionFeedback::PAGE_OBJECT_TYPE_SPECIFIC_FEEDBACK) {
            include_once("./Modules/TestQuestionPool/classes/feedback/class.ilAssSpecFeedbackPage.php");
            if (!ilAssSpecFeedbackPage::_exists($pageObjectType, $pageObjectId)) {
                $pageObject = new ilAssSpecFeedbackPage();
                $pageObject->setParentId($this->questionOBJ->getId());
                $pageObject->setId($pageObjectId);
                $pageObject->createFromXML();
            }
        }
    }
    
    final protected function createPageObject(string $pageObjectType, int $pageObjectId, string $pageObjectContent) : void
    {
        $cl = $this->getClassNameByType($pageObjectType);
        require_once 'Modules/TestQuestionPool/classes/feedback/class.' . $cl . '.php';
        
        $pageObject = new $cl();
        $pageObject->setParentId($this->questionOBJ->getId());
        $pageObject->setId($pageObjectId);
        $pageObject->setXMLContent($pageObjectContent);
        $pageObject->createFromXML();
    }
    
    final protected function duplicatePageObject(string $pageObjectType, int $originalPageObjectId, int $duplicatePageObjectId, int $duplicatePageObjectParentId) : void
    {
        $cl = $this->getClassNameByType($pageObjectType);
        require_once 'Modules/TestQuestionPool/classes/feedback/class.' . $cl . '.php';

        $pageObject = new $cl($originalPageObjectId);
        $pageObject->setParentId($duplicatePageObjectParentId);
        $pageObject->setId($duplicatePageObjectId);
        $pageObject->createFromXML();
    }
    
    final protected function ensurePageObjectDeleted(string $pageObjectType, int $pageObjectId) : void
    {
        if ($pageObjectType == ilAssQuestionFeedback::PAGE_OBJECT_TYPE_GENERIC_FEEDBACK) {
            include_once("./Modules/TestQuestionPool/classes/feedback/class.ilAssGenFeedbackPage.php");
            if (ilAssGenFeedbackPage::_exists($pageObjectType, $pageObjectId)) {
                $pageObject = new ilAssGenFeedbackPage($pageObjectId);
                $pageObject->delete();
            }
        }
        if ($pageObjectType == ilAssQuestionFeedback::PAGE_OBJECT_TYPE_SPECIFIC_FEEDBACK) {
            include_once("./Modules/TestQuestionPool/classes/feedback/class.ilAssSpecFeedbackPage.php");
            if (ilAssSpecFeedbackPage::_exists($pageObjectType, $pageObjectId)) {
                $pageObject = new ilAssSpecFeedbackPage($pageObjectId);
                $pageObject->delete();
            }
        }
    }
    
    final protected function getGenericFeedbackPageObjectType() : string
    {
        return self::PAGE_OBJECT_TYPE_GENERIC_FEEDBACK;
    }
    
    final protected function getSpecificAnswerFeedbackPageObjectType() : string
    {
        return self::PAGE_OBJECT_TYPE_SPECIFIC_FEEDBACK;
    }
    
    /**
     * returns the fact whether the given page object type
     * relates to generic or specific feedback page objects
     */
    final public static function isValidFeedbackPageObjectType(string $feedbackPageObjectType) : bool
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
    final protected function getGenericFeedbackPageObjectId(int $questionId, bool $solutionCompleted) : int
    {
        $pageObjectId = $this->getGenericFeedbackId($questionId, $solutionCompleted);
        
        if ($pageObjectId != -1) {
            $pageObjectId = $this->saveGenericFeedbackContent($questionId, $solutionCompleted, '');
        }
        
        return $pageObjectId;
    }

    /**
     * returns the generic feedback export presentation for given question id
     * either for solution completed or incompleted
     */
    public function getGenericFeedbackExportPresentation(int $questionId, bool $solutionCompleted) : string
    {
        if ($this->questionOBJ->isAdditionalContentEditingModePageObject()) {
            $genericFeedbackExportPresentation = $this->getPageObjectXML(
                $this->getGenericFeedbackPageObjectType(),
                $this->getGenericFeedbackPageObjectId($questionId, $solutionCompleted)
            );
        } else {
            $genericFeedbackExportPresentation = $this->getGenericFeedbackContent($questionId, $solutionCompleted);
        }
                
        return $genericFeedbackExportPresentation;
    }
    
    /**
     * returns the generic feedback export presentation for given question id
     * either for solution completed or incompleted
     */
    abstract public function getSpecificAnswerFeedbackExportPresentation(int $questionId, int $questionIndex, int $answerIndex) : string;
    
    /**
     * imports the given feedback content as generic feedback for the given question id
     * for either the complete or incomplete solution
     */
    public function importGenericFeedback(int $questionId, bool $solutionCompleted, string $feedbackContent) : void
    {
        if ($this->questionOBJ->isAdditionalContentEditingModePageObject()) {
            $pageObjectId = $this->getGenericFeedbackPageObjectId($questionId, $solutionCompleted);
            $pageObjectType = $this->getGenericFeedbackPageObjectType();
            
            $this->createPageObject($pageObjectType, $pageObjectId, $feedbackContent);
        } else {
            $this->saveGenericFeedbackContent($questionId, $solutionCompleted, $feedbackContent);
        }
    }
    
    abstract public function importSpecificAnswerFeedback(int $questionId, int $questionIndex, int $answerIndex, string $feedbackContent) : void;
    
    public function migrateContentForLearningModule(ilAssSelfAssessmentMigrator $migrator, int $questionId) : void
    {
        $this->saveGenericFeedbackContent($questionId, true, $migrator->migrateToLmContent(
            $this->getGenericFeedbackContent($questionId, true)
        ));
        
        $this->saveGenericFeedbackContent($questionId, false, $migrator->migrateToLmContent(
            $this->getGenericFeedbackContent($questionId, false)
        ));
    }
}
