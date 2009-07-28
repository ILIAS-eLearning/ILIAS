include_once("./Services/Component/classes/class.ilPlugin.php");
 
/**
* Abstract parent class for all event hook plugin classes.
*
* @author Alex Killing <stefan.schneider@hrz.uni-giessen.de>
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
                return "PersonalDesktop";
        }
 
        /**
        * Get Slot Name.
        *
        * @return        string        Slot Name
        */
        final function getSlot()
        {
                return "PersonalDesktopHook";
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