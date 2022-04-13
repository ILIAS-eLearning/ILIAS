<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Class ilPDFGeneration
 * Dispatcher to route PDF-Generation jobs to the appropriate handling mechanism.
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 * @deprecated
 */
class ilPDFGeneration
{
    public static function doJob(ilPDFGenerationJob $job) : void
    {
        ilTCPDFGenerator::generatePDF($job);
    }

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
