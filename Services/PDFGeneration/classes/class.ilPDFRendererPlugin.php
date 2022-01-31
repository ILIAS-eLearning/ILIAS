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
    /** --- ilPDFRendererPlugin -- */
    // Note: Most of the required methods come from interface ilRendererConfig
}
