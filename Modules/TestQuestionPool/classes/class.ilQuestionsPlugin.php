<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Component/classes/class.ilPlugin.php");

/**
 * Abstract parent class for all question plugin classes.
 *
 * @author Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @version $Id$
 *
 * @ingroup ServicesEventHandling
 */
abstract class ilQuestionsPlugin extends ilPlugin
{
    const COMP_NAME = 'TestQuestionPool';
    const SLOT_NAME = 'Questions';
    const SLOT_ID = 'qst';
    
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
        return self::COMP_NAME;
    }
    
    /**
     * Get Slot Name.
     *
     * @return string Slot Name
     */
    final public function getSlot()
    {
        return self::SLOT_NAME;
    }
    
    /**
     * Get Slot ID.
     *
     * @return string Slot Id
     */
    final public function getSlotId()
    {
        return self::SLOT_ID;
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
