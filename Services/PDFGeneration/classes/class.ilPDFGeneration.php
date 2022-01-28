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
 * Class ilPDFGeneration
 *
 * Dispatcher to route PDF-Generation jobs to the appropriate handling mechanism.
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 * @deprecated
 *
 */
class ilPDFGeneration
{
    public static function doJob(ilPDFGenerationJob $job) : void
    {
        ilTCPDFGenerator::generatePDF($job);
    }

    /**
     * Prepare the PDF generation
     * This initializes the purpose for MathJax rendering
     * It has to be called before any content is processed
     */
    public static function prepareGeneration() : void
    {
        // TCPDF supports only embedded PNG images
        // use high dpi to get a good result when the PDF is zoomed
        // zoom factor is adjusted to get the same image size as with SVG in the browser
        ilMathJax::getInstance()
            ->init(ilMathJax::PURPOSE_PDF)
            ->setDpi(600)
            ->setZoomFactor(0.17);
    }
}
