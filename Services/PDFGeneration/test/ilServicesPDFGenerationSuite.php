<?php
use PHPUnit\Framework\TestSuite;

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
 * Class ilPDFGenerationSuite
 * @package ilPdfGenerator
 */
class ilServicesPDFGenerationSuite extends TestSuite
{
    public static function suite() : \ilServicesPDFGenerationSuite
    {
        $suite = new self();
        require_once 'Services/PDFGeneration/test/ilPdfGeneratorConstantsTest.php';
        $suite->addTestSuite('ilPdfGeneratorConstantsTest');
        require_once 'Services/PDFGeneration/test/ilWkhtmlToPdfConfigTest.php';
        $suite->addTestSuite('ilWkhtmlToPdfConfigTest');

        return $suite;
    }
}
