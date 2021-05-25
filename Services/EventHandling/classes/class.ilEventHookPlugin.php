<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Abstract parent class for all event hook plugin classes.
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
abstract class ilEventHookPlugin extends ilPlugin
{
    /**
    * Get Component Type
    *
    * @return	string	Component Type
    */
    final public function getComponentType()
    {
        return IL_COMP_SERVICE;
    }
    
    /**
    * Get Component Name.
    *
    * @return	string	Component Name
    */
    final public function getComponentName()
    {
        return "EventHandling";
    }

    /**
    * Get Slot Name.
    *
    * @return	string	Slot Name
    */
    final public function getSlot()
    {
        return "EventHook";
    }

    /**
    * Get Slot ID.
    *
    * @return	string	Slot Id
    */
    final public function getSlotId()
    {
        return "evhk";
    }

    /**
    * Object initialization done by slot.
    */
    final protected function slotInit()
    {
        // nothing to do here
    }

    /**
     * Handle the event
     *
     * @param	string		component, e.g. "Services/User"
     * @param	event		event, e.g. "afterUpdate"
     * @param	array		array of event specific parameters
     */
    abstract public function handleEvent($a_component, $a_event, $a_parameter);
}
