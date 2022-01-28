<?php
/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
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
