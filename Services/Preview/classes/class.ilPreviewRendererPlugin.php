<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Component/classes/class.ilPlugin.php");

/**
 * Abstract parent class for all preview renderer plugin classes.
 *
 * @author Stefan Born <stefan.born@phzh.ch>
 * @version $Id$
 *
 * @ingroup ServicesPreview
 */
abstract class ilPreviewRendererPlugin extends ilPlugin
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
        return "Preview";
    }

    /**
     * Get Slot Name.
     *
     * @return        string        Slot Name
     */
    final public function getSlot()
    {
        return "PreviewRenderer";
    }

    /**
     * Get Slot ID.
     *
     * @return        string        Slot Id
     */
    final public function getSlotId()
    {
        return "pvre";
    }

    /**
     * Object initialization done by slot.
     */
    final protected function slotInit()
    {
        // nothing to do here
    }
    
    public function getRendererClassInstance()
    {
        $class = "il" . $this->getPluginName();
        $this->includeClass("class." . $class . ".php");
        return new $class();
    }
}
