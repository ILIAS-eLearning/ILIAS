<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
include_once("./Services/Component/classes/class.ilPlugin.php");
 
/**
* Abstract parent class for all personal desktop plugin classes.
*
* @author Stefan Schneider <stefan.schneider@hrz.uni-giessen.de>
* @version $Id$
*
* @ingroup ServicesPersonalDesktop
*/
abstract class ilPersonalDesktopHookPlugin extends ilPlugin
{
    /**
    * Get Component Type
    *
    * @return        string        Component Type
    */
    final public function getComponentType()
    {
        return IL_COMP_SERVICE;
    }

    /**
    * Get Component Name.
    *
    * @return        string        Component Name
    */
    final public function getComponentName()
    {
        return "PersonalDesktop";
    }

    /**
    * Get Slot Name.
    *
    * @return        string        Slot Name
    */
    final public function getSlot()
    {
        return "PersonalDesktopHook";
    }

    /**
    * Get Slot ID.
    *
    * @return        string        Slot Id
    */
    final public function getSlotId()
    {
        return "pdhk";
    }

    /**
    * Object initialization done by slot.
    */
    final protected function slotInit()
    {
        // nothing to do here
    }
}
