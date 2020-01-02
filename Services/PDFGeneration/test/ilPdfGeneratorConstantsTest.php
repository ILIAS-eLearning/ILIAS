<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once __DIR__ . '/../classes/class.ilPDFGenerationConstants.php';

/**
 * Class ilPdfGeneratorConstantsTest
 * @package ilPdfGenerator
 */
class ilPdfGeneratorConstantsTest extends PHPUnit_Framework_TestCase
{
    public function testInstanceCanBeCreated()
    {
        $this->assertInstanceOf('ilPDFGenerationConstants', new ilPDFGenerationConstants());
    }

    public function testGetOrientations()
    {
        $this->assertCount(2, ilPDFGenerationConstants::getOrientations());
        $orientations =  ilPDFGenerationConstants::getOrientations();
        $this->assertSame('Portrait', $orientations['Portrait']);
        $this->assertSame('Landscape', $orientations['Landscape']);
    }

    public function testGetPageSizesNames()
    {
        $this->assertCount(15, ilPDFGenerationConstants::getPageSizesNames());
    }

    public function testHeaderConstants()
    {
        $this->assertSame(0, ilPDFGenerationConstants::HEADER_NONE);
        $this->assertSame(1, ilPDFGenerationConstants::HEADER_TEXT);
        $this->assertSame(2, ilPDFGenerationConstants::HEADER_HTML);
    }

    public function testFooterConstants()
    {
        $this->assertSame(0, ilPDFGenerationConstants::FOOTER_NONE);
        $this->assertSame(1, ilPDFGenerationConstants::FOOTER_TEXT);
        $this->assertSame(2, ilPDFGenerationConstants::FOOTER_HTML);
    }
}
