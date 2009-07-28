<?php
include_once("./Services/Component/classes/class.ilPlugin.php");
 
/**
* Abstract parent class for all Personal Desktop plugin classes.
*
* @author Stefan Schneider <stefan.schneider@hrz.uni-giessen.de>
* @version $Id$
*
* @ingroup ServicesPersonalDesktop
*/
abstract class ilPersonalDesktopGUIHookPlugin extends ilPlugin
{
        /**
        * Get Component Type
        *
        * @return        string        Component Type
        */
        final function getComponentType()
        {
                return IL_COMP_SERVICE;
        }
	
        /**
        * Get Component Name.
        *
        * @return        string        Component Name
        */
        final function getComponentName()
        {
                return "PersonalDesktopGUI";
        }
 
        /**
        * Get Slot Name.
        *
        * @return        string        Slot Name
        */
        final function getSlot()
        {
                return "PersonalDesktopGUIHook";
        }
 
        /**
        * Get Slot ID.
        *
        * @return        string        Slot Id
        */
        final function getSlotId()
        {
                return "pdhk";
        }
 
        /**
        * Object initialization done by slot.
        */
        protected final function slotInit()
        {
                // nothing to do here
        }
}
?>