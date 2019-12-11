<?php
include_once("./Services/Component/classes/class.ilPlugin.php");

/**
 * Abstract parent class for all question plugin classes.
 *
 * @author Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @version $Id$
 *
 * @ingroup ServicesEventHandling
 */
abstract class ilSurveyQuestionsPlugin extends ilPlugin
{
    /**
     * Get Component Type
     *
     * @return string Component Type
     */
    final public function getComponentType()
    {
        return IL_COMP_MODULE;
    }
    
    /**
     * Get Component Name.
     *
     * @return string Component Name
     */
    final public function getComponentName()
    {
        return "SurveyQuestionPool";
    }
    
    /**
     * Get Slot Name.
     *
     * @return string Slot Name
     */
    final public function getSlot()
    {
        return "SurveyQuestions";
    }
    
    /**
     * Get Slot ID.
     *
     * @return string Slot Id
     */
    final public function getSlotId()
    {
        return "svyq";
    }
    
    /**
     * Object initialization done by slot.
     */
    final protected function slotInit()
    {
        // nothing to do here
    }
    
    abstract public function getQuestionType();
}
