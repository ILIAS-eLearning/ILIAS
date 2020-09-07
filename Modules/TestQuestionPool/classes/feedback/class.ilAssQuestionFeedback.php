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

    /**
     * table name for specific feedback
     */
    const TABLE_NAME_GENERIC_FEEDBACK = 'qpl_fb_generic';
    
    /**
     * object instance of current question
     *
     * @access protected
     * @var assQuestion
     */
    protected $questionOBJ = null;

    /**
     * global $ilCtrl
     *
     * @access protected
     * @var ilCtrl
     */
    protected $ctrl = null;

    /**
     * global $ilDB
     *
     * @access protected
     * @var ilDBInterface
     */
    protected $db = null;

    /**
     * global $lng
     *
     * @access protected
     * @var ilLanguage
     */
    protected $lng = null;

    /**
     * page object output mode
     *
     * @access protected
     * @var string
     */
    protected $page_obj_output_mode = "presentation";
    
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
     *
     * @access public
     * @param integer $questionId
     * @param boolean $solutionCompleted
     * @return string $genericFeedbackTestPresentationHTML
     */
    public function getGenericFeedbackTestPresentation($questionId, $solutionCompleted)
    {
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
    abstract public function getSpecificAnswerFeedbackTestPresentation($questionId, $questionIndex, $answerIndex);

    /**
     * completes a given form object with the GENERIC form properties
     * required by all question types
     *
     * @final
     * @access public
     * @param ilPropertyFormGUI $form
     */
    final public function completeGenericFormProperties(ilPropertyFormGUI $form)
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
    abstract public function completeSpecificFormProperties(ilPropertyFormGUI $form);
    
    /**
     * initialises a given form object's GENERIC form properties
     * relating to all question types
     *
     * @final
     * @access public
     * @param ilPropertyFormGUI $form
     */
    final public function initGenericFormProperties(ilPropertyFormGUI $form)
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
     *
     * @abstract
     * @access public
     * @param ilPropertyFormGUI $form
     */
    abstract public function initSpecificFormProperties(ilPropertyFormGUI $form);
    
    /**
     * saves a given form object's GENERIC form properties
     * relating to all question types
     *
     * @final
     * @access public
     * @param ilPropertyFormGUI $form
     */
    final public function saveGenericFormProperties(ilPropertyFormGUI $form)
    {
        if (!$this->questionOBJ->isAdditionalContentEditingModePageObject()) {
            $this->saveGenericFeedbackContent($this->questionOBJ->getId(), false, $form->getInput('feedback_incomplete'));
            $this->saveGenericFeedbackContent($this->questionOBJ->getId(), true, $form->getInput('feedback_complete'));
        }
    }
    
    /**
     * saves a given form object's SPECIFIC form properties
     * relating to this question type
     *
     * @abstract
     * @access public
     * @param ilPropertyFormGUI $form
     */
    abstract public function saveSpecificFormProperties(ilPropertyFormGUI $form);
    
    /**
     * returns the fact wether the feedback editing form is saveable in page object editing or not.
     * by default all properties are edited as page object unless there are additional settings
     * (this method can be overwritten per question type if required)
     *
     * @access public
     * @return boolean $isSaveableInPageObjectEditingMode
     */
    public function isSaveableInPageObjectEditingMode()
    {
        return false;
    }

    /**
     * builds and returns a form property gui object with the given label and postvar
     * that is addable to property forms
     * depending on the given flag "asNonEditable" it returns a ...
     * - non editable gui
     * - textarea input gui
     *
     * @final
     * @access protected
     * @param string $label
     * @param string $postVar
     * @param boolean $asNonEditable
     * @return ilTextAreaInputGUI|ilNonEditableValueGUI $formProperty
     */
    final protected function buildFeedbackContentFormProperty($label, $postVar, $asNonEditable)
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
     *
     * @final
     * @access public
     * @param integer $questionId
     * @param boolean $solutionCompleted
     * @return string $feedbackContent
     */
    final public function getGenericFeedbackContent($questionId, $solutionCompleted)
    {
        require_once 'Services/RTE/classes/class.ilRTE.php';

        $correctness = $solutionCompleted ? 1 : 0;
        
        $res = $this->db->queryF(
            "SELECT * FROM {$this->getGenericFeedbackTableName()} WHERE question_fi = %s AND correctness = %s",
            array('integer', 'text'),
            array($questionId, $correctness)
        );

        $feedbackContent = null;
        
        while ($row = $this->db->fetchAssoc($res)) {
            $feedbackContent = ilRTE::_replaceMediaObjectImageSrc($row['feedback'], 1);
            break;
        }
        
        return $feedbackContent;
    }

    /**
     * returns the SPECIFIC feedback content for a given question id and answer index.
     *
     * @abstract
     * @access public
     * @param integer $questionId
     * @param integer $questionIndex
     * @param integer $answerIndex
     * @return string $feedbackContent
     */
    abstract public function getSpecificAnswerFeedbackContent($questionId, $questionIndex, $answerIndex);

    /**
     * returns the SPECIFIC feedback content for a given question id and answer index.
     *
     * @abstract
     * @access public
     * @param integer $questionId
     * @return string $feedbackContent
     */
    abstract public function getAllSpecificAnswerFeedbackContents($questionId);
    
    /**
     * returns the fact wether any specific feedback content is available or not
     *
     * @param integer $questionId
     * @return bool
     */
    public function isSpecificAnswerFeedbackAvailable($questionId)
    {
        return (bool) strlen($this->getAllSpecificAnswerFeedbackContents($questionId));
    }

    /**
     * saves GENERIC feedback content for the given question id to the database.
     * Generic feedback is either feedback for the completed solution (all answers correct)
     * of the question or at least onen incorrect answer.
     *
     * @final
     * @access public
     * @param integer $questionId
     * @param boolean $solutionCompleted
     * @param string $feedbackContent
     * @return integer $feedbackId
     */
    final public function saveGenericFeedbackContent($questionId, $solutionCompleted, $feedbackContent)
    {
        require_once 'Services/RTE/classes/class.ilRTE.php';
    
        $correctness = $solutionCompleted ? 1 : 0;
        
        $feedbackId = $this->getGenericFeedbackId($questionId, $solutionCompleted);
        
        if (strlen($feedbackContent)) {
            $feedbackContent = ilRTE::_replaceMediaObjectImageSrc($feedbackContent, 0);
        }
        
        if ($feedbackId) {
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
                'correctness' => array('text', $correctness), // text ?
                'feedback' => array('clob', $feedbackContent),
                'tstamp' => array('integer', time())
            ));
        }
        
        return $feedbackId;
    }
    
    /**
     * saves SPECIFIC feedback content for the given question id and answer index to the database.
     *
     * @abstract
     * @access public
     * @param integer $questionId
     * @param integer $questionIndex
     * @param integer $answerIndex
     * @param string $feedbackContent
     * @return integer $feedbackId
     */
    abstract public function saveSpecificAnswerFeedbackContent($questionId, $questionIndex, $answerIndex, $feedbackContent);
    
    /**
     * deletes all GENERIC feedback contents (and page objects if required)
     * for the given question id
     *
     * @final
     * @access public
     * @param integer $questionId
     * @param boolean $isAdditionalContentEditingModePageObject
     */
    final public function deleteGenericFeedbacks($questionId, $isAdditionalContentEditingModePageObject)
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
    
    /**
     * deletes all SPECIFIC feedback contents for the given question id
     *
     * @abstract
     * @access public
     * @param integer $questionId
     * @param boolean $isAdditionalContentEditingModePageObject
     */
    abstract public function deleteSpecificAnswerFeedbacks($questionId, $isAdditionalContentEditingModePageObject);

    /**
     * duplicates the feedback relating to the given original question id
     * and saves it for the given duplicate question id
     *
     * @final
     * @access public
     * @param integer $originalQuestionId
     * @param integer $duplicateQuestionId
     */
    final public function duplicateFeedback($originalQuestionId, $duplicateQuestionId)
    {
        $this->duplicateGenericFeedback($originalQuestionId, $duplicateQuestionId);
        $this->duplicateSpecificFeedback($originalQuestionId, $duplicateQuestionId);
    }
    
    /**
     * duplicates the GENERIC feedback relating to the given original question id
     * and saves it for the given duplicate question id
     *
     * @final
     * @access private
     * @param integer $originalQuestionId
     * @param integer $duplicateQuestionId
     */
    final private function duplicateGenericFeedback($originalQuestionId, $duplicateQuestionId)
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
     *
     * @abstract
     * @access protected
     * @param integer $originalQuestionId
     * @param integer $duplicateQuestionId
     */
    abstract protected function duplicateSpecificFeedback($originalQuestionId, $duplicateQuestionId);
    
    /**
     * syncs the feedback from a duplicated question back to the original question
     *
     * @final
     * @access public
     * @param integer $originalQuestionId
     * @param integer $duplicateQuestionId
     */
    final public function syncFeedback($originalQuestionId, $duplicateQuestionId)
    {
        $this->syncGenericFeedback($originalQuestionId, $duplicateQuestionId);
        $this->syncSpecificFeedback($originalQuestionId, $duplicateQuestionId);
    }
    
    /**
     * syncs the GENERIC feedback from a duplicated question back to the original question
     *
     * @final
     * @access private
     * @param integer $originalQuestionId
     * @param integer $duplicateQuestionId
     */
    final private function syncGenericFeedback($originalQuestionId, $duplicateQuestionId)
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
     *
     * @final
     * @access protected
     * @param integer $questionId
     * @param boolean $answerIndex
     * @return string $feedbackId
     */
    final protected function getGenericFeedbackId($questionId, $solutionCompleted)
    {
        $res = $this->db->queryF(
            "SELECT feedback_id FROM {$this->getGenericFeedbackTableName()} WHERE question_fi = %s AND correctness = %s",
            array('integer','text'),
            array($questionId, (int) $solutionCompleted)
        );
        
        $feedbackId = null;
        
        while ($row = $this->db->fetchAssoc($res)) {
            $feedbackId = $row['feedback_id'];
            break;
        }
        
        return $feedbackId;
    }
    
    /**
     * @param int $feedbackId
     * @return bool
     */
    protected function isGenericFeedbackId($feedbackId)
    {
        $row = $this->db->fetchAssoc($this->db->queryF(
            "SELECT COUNT(feedback_id) cnt FROM {$this->getGenericFeedbackTableName()}
					WHERE question_fi = %s AND feedback_id = %s",
            array('integer','integer'),
            array($this->questionOBJ->getId(), $feedbackId)
        ));
        
        
        return $row['cnt'];
    }
    
    /**
     * @param int $feedbackId
     * @return bool
     */
    abstract protected function isSpecificAnswerFeedbackId($feedbackId);
    
    /**
     * @param int $feedbackId
     * @return bool
     */
    final public function checkFeedbackParent($feedbackId)
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
     *
     * @abstract
     * @access protected
     * @param integer $originalQuestionId
     * @param integer $duplicateQuestionId
     */
    abstract protected function syncSpecificFeedback($originalQuestionId, $duplicateQuestionId);
    
    /**
     * returns the table name for specific feedback
     *
     * @final
     * @return string $specificFeedbackTableName
     */
    final protected function getGenericFeedbackTableName()
    {
        return self::TABLE_NAME_GENERIC_FEEDBACK;
    }
    
    /**
     * returns html content to be used as value for non editable value form properties
     * in feedback editing form
     *
     * @final
     * @access protected
     * @param string $pageObjectType
     * @param integer $pageObjectId
     * @return string $nonEditableValueHTML
     */
    final protected function getPageObjectNonEditableValueHTML($pageObjectType, $pageObjectId)
    {
        $link = $this->getPageObjectEditingLink($pageObjectType, $pageObjectId);
        $content = $this->getPageObjectContent($pageObjectType, $pageObjectId);

        return "$link<br /><br />$content";
    }
    
    /**
     * Get class name by type
     *
     * @param
     * @return
     */
    public function getClassNameByType($a_type, $a_gui = false)
    {
        $gui = ($a_gui)
            ? "GUI"
            : "";
        include_once("./Modules/TestQuestionPool/classes/feedback/class.ilAssQuestionFeedback.php");
        if ($a_type == ilAssQuestionFeedback::PAGE_OBJECT_TYPE_GENERIC_FEEDBACK) {
            return "ilAssGenFeedbackPage" . $gui;
        }
        if ($a_type == ilAssQuestionFeedback::PAGE_OBJECT_TYPE_SPECIFIC_FEEDBACK) {
            return "ilAssSpecFeedbackPage" . $gui;
        }
    }
    
    
    /**
     * returns a link to page object editor for page object
     * with given type and id
     *
     * @final
     * @access private
     * @param string $pageObjectType
     * @param integer $pageObjectId
     * @return string $pageObjectEditingLink
     */
    final private function getPageObjectEditingLink($pageObjectType, $pageObjectId)
    {
        $cl = $this->getClassNameByType($pageObjectType, true);
        $this->ctrl->setParameterByClass($cl, 'feedback_type', $pageObjectType);
        $this->ctrl->setParameterByClass($cl, 'feedback_id', $pageObjectId);
        
        $linkHREF = $this->ctrl->getLinkTargetByClass($cl, 'edit');
        $linkTEXT = $this->lng->txt('tst_question_feedback_edit_page');
        
        return "<a href='$linkHREF'>$linkTEXT</a>";
    }

    /**
     * Set page object output mode
     *
     * @param string $a_val page output mode
     */
    final public function setPageObjectOutputMode($a_val)
    {
        $this->page_obj_output_mode = $a_val;
    }

    /**
     * Get page object output mode
     *
     * @return string page output mode
     */
    final public function getPageObjectOutputMode()
    {
        return $this->page_obj_output_mode;
    }

    /**
     * returns the content of page object with given type and id
     *
     * @final
     * @access protected
     * @param string $pageObjectType
     * @param integer $pageObjectId
     * @return string $pageObjectContent
     */
    final protected function getPageObjectContent($pageObjectType, $pageObjectId)
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];

        $cl = $this->getClassNameByType($pageObjectType, true);
        require_once 'Modules/TestQuestionPool/classes/feedback/class.' . $cl . '.php';

        $this->ensurePageObjectExists($pageObjectType, $pageObjectId);

        $mode = ($ilCtrl->isAsynch())
            ? "presentation"
            : $this->getPageObjectOutputMode();

        $pageObjectGUI = new $cl($pageObjectId);
        $pageObjectGUI->setOutputMode($mode);

        return $pageObjectGUI->presentation($mode);
    }
    
    /**
     * returns the xml of page object with given type and id
     *
     * @final
     * @access protected
     * @param string $pageObjectType
     * @param integer $pageObjectId
     * @return string $pageObjectXML
     */
    final protected function getPageObjectXML($pageObjectType, $pageObjectId)
    {
        $cl = $this->getClassNameByType($pageObjectType);
        require_once 'Modules/TestQuestionPool/classes/feedback/class.' . $cl . '.php';

        $this->ensurePageObjectExists($pageObjectType, $pageObjectId);
        
        $pageObject = new $cl($pageObjectId);
        return $pageObject->getXMLContent();
    }
    
    /**
     * ensures an existing page object with given type and id
     *
     * @final
     * @access private
     * @param string $pageObjectType
     * @param integer $pageObjectId
     */
    final private function ensurePageObjectExists($pageObjectType, $pageObjectId)
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
    
    /**
     * creates a new page object with given page object id and page object type
     * and passed page object content
     *
     * @final
     * @access protected
     * @param string $pageObjectType
     * @param integer $pageObjectId
     * @param string $pageObjectContent
     */
    final protected function createPageObject($pageObjectType, $pageObjectId, $pageObjectContent)
    {
        $cl = $this->getClassNameByType($pageObjectType);
        require_once 'Modules/TestQuestionPool/classes/feedback/class.' . $cl . '.php';
        
        $pageObject = new $cl();
        $pageObject->setParentId($this->questionOBJ->getId());
        $pageObject->setId($pageObjectId);
        $pageObject->setXMLContent($pageObjectContent);
        $pageObject->createFromXML();
    }
    
    /**
     * duplicates the page object with given type and original id
     * to new page object with same type and given duplicate id and duplicate parent id
     *
     * @final
     * @access protected
     * @param string $pageObjectType
     * @param integer $originalPageObjectId
     * @param integer $duplicatePageObjectId
     * @param integer $duplicatePageObjectParentId
     */
    final protected function duplicatePageObject($pageObjectType, $originalPageObjectId, $duplicatePageObjectId, $duplicatePageObjectParentId)
    {
        $cl = $this->getClassNameByType($pageObjectType);
        require_once 'Modules/TestQuestionPool/classes/feedback/class.' . $cl . '.php';

        $pageObject = new $cl($originalPageObjectId);
        $pageObject->setParentId($duplicatePageObjectParentId);
        $pageObject->setId($duplicatePageObjectId);
        $pageObject->createFromXML();
    }
    
    /**
     * ensures a no more existing page object for given type and id
     *
     * @final
     * @access protected
     * @param string $pageObjectType
     * @param integer $pageObjectId
     */
    final protected function ensurePageObjectDeleted($pageObjectType, $pageObjectId)
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
    
    /**
     * returns the type for generic feedback page objects
     * defined in local constant
     *
     * @final
     * @access protected
     * @return string $genericFeedbackPageObjectType
     */
    final protected function getGenericFeedbackPageObjectType()
    {
        return self::PAGE_OBJECT_TYPE_GENERIC_FEEDBACK;
    }
    
    /**
     * returns the type for specific feedback page objects
     * defined in local constant
     *
     * @final
     * @access protected
     * @return string $specificFeedbackPageObjectType
     */
    final protected function getSpecificAnswerFeedbackPageObjectType()
    {
        return self::PAGE_OBJECT_TYPE_SPECIFIC_FEEDBACK;
    }
    
    /**
     * returns the fact wether the given page object type
     * relates to generic or specific feedback page objects
     *
     * @final
     * @static
     * @access public
     * @param string $feedbackPageObjectType
     * @return array $validFeedbackPageObjectTypes
     */
    final public static function isValidFeedbackPageObjectType($feedbackPageObjectType)
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
     *
     * @final
     * @access protected
     * @param integer $questionId
     * @param boolean $solutionCompleted
     * @return integer $pageObjectId
     */
    final protected function getGenericFeedbackPageObjectId($questionId, $solutionCompleted)
    {
        $pageObjectId = $this->getGenericFeedbackId($questionId, $solutionCompleted);
        
        if (!$pageObjectId) {
            $pageObjectId = $this->saveGenericFeedbackContent($questionId, $solutionCompleted, null);
        }
        
        return $pageObjectId;
    }

    /**
     * returns the generic feedback export presentation for given question id
     * either for solution completed or incompleted
     *
     * @access public
     * @param integer $questionId
     * @param boolean $solutionCompleted
     * @return string $genericFeedbackExportPresentation
     */
    public function getGenericFeedbackExportPresentation($questionId, $solutionCompleted)
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
     *
     * @abstract
     * @access public
     * @param integer $questionId
     * @param integer $questionIndex
     * @param integer $answerIndex
     * @return string $specificFeedbackExportPresentation
     */
    abstract public function getSpecificAnswerFeedbackExportPresentation($questionId, $questionIndex, $answerIndex);
    
    /**
     * imports the given feedback content as generic feedback for the given question id
     * for either the complete or incomplete solution
     *
     * @access public
     * @param integer $questionId
     * @param boolean $solutionCompleted
     * @param string $feedbackContent
     */
    public function importGenericFeedback($questionId, $solutionCompleted, $feedbackContent)
    {
        if ($this->questionOBJ->isAdditionalContentEditingModePageObject()) {
            $pageObjectId = $this->getGenericFeedbackPageObjectId($questionId, $solutionCompleted);
            $pageObjectType = $this->getGenericFeedbackPageObjectType();
            
            $this->createPageObject($pageObjectType, $pageObjectId, $feedbackContent);
        } else {
            $this->saveGenericFeedbackContent($questionId, $solutionCompleted, $feedbackContent);
        }
    }
    
    /**
     * imports the given feedback content as specific feedback
     * for the given question id and answer index
     *
     * @abstract
     * @access public
     * @param integer $questionId
     * @param integer $questionIndex
     * @param integer $answerIndex
     * @param string $feedbackContent
     */
    abstract public function importSpecificAnswerFeedback($questionId, $questionIndex, $answerIndex, $feedbackContent);
    
    /**
     * @param ilAssSelfAssessmentMigrator $migrator
     * @param integer $questionId
     */
    public function migrateContentForLearningModule(ilAssSelfAssessmentMigrator $migrator, $questionId)
    {
        $this->saveGenericFeedbackContent($questionId, true, $migrator->migrateToLmContent(
            $this->getGenericFeedbackContent($questionId, true)
        ));
        
        $this->saveGenericFeedbackContent($questionId, false, $migrator->migrateToLmContent(
            $this->getGenericFeedbackContent($questionId, false)
        ));
    }
}
