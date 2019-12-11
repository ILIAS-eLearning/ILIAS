<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'class.ilPDFGenerationJob.php';

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
    public static function doJob(ilPDFGenerationJob $job)
    {
        /*
         * This place currently supports online the TCPDF-Generator. In future versions/iterations, this place
         * may serve to initialize other mechanisms and route jobs to them.
         */
        require_once 'class.ilTCPDFGenerator.php';
        ilTCPDFGenerator::generatePDF($job);
    }

    /**
     * Prepare the PDF generation
     * This initializes the purpose for MathJax rendering
     * It has to be called before any content is processed
     */
    public static function prepareGeneration()
    {
        include_once './Services/MathJax/classes/class.ilMathJax.php';

        // TCPDF supports only embedded PNG images
        // use high dpi to get a good result when the PDF is zoomed
        // zoom factor is adjusted to get the same image size as with SVG in the browser
        ilMathJax::getInstance()
            ->init(ilMathJax::PURPOSE_PDF)
            ->setDpi(600)
            ->setZoomFactor(0.17);
    }
}
