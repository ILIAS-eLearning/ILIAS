<?php
require_once './Services/Component/classes/class.ilPlugin.php';
require_once './Services/PDFGeneration/interfaces/interface.ilRendererConfig.php';
require_once './Services/PDFGeneration/interfaces/interface.ilPDFRenderer.php';

/**
 * Abstract parent class for all pdf renderer plugin classes.
 *
 * @author Alex Killing      <alex.killing@gmx.de>
 * @author Maximilian Becker <mbecker@databay.de>
 *
 * @version $Id$
 *
 * @ingroup ServicesPDFGeneration
 */
abstract class ilPDFRendererPlugin extends ilPlugin implements ilRendererConfig, ilPDFRenderer
{
    /** --- ilPlugin -- */

    /**
     * Get Component Type
     *
     * @return string Component Type
     */
    final public function getComponentType()
    {
        return IL_COMP_SERVICE;
    }

    /**
     * Get Component Name.
     *
     * @return string Component Name
     */
    final public function getComponentName()
    {
        return "PDFGeneration";
    }

    /**
     * Get Slot Name.
     *
     * @return string Slot Name
     */
    final public function getSlot()
    {
        return "Renderer";
    }

    /**
     * Get Slot ID.
     *
     * @return string Slot Id
     */
    final public function getSlotId()
    {
        return "renderer";
    }

    /**
     * Object initialization done by slot.
     */
    final protected function slotInit()
    {
        // nothing to do here
    }

    /** --- ilPDFRendererPlugin -- */
    // Note: Most of the required methods come from interface ilRendererConfig
}
