<?php
use PHPUnit\Framework\TestCase;

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
 * Class ilPdfGeneratorConstantsTest
 * @package ilPdfGenerator
 */
class ilPdfGeneratorConstantsTest extends TestCase
{
    public function testInstanceCanBeCreated() : void
    {
        $this->assertInstanceOf('ilPDFGenerationConstants', new ilPDFGenerationConstants());
    }

    public function testGetOrientations() : void
    {
        $this->assertCount(2, ilPDFGenerationConstants::getOrientations());
        $orientations = ilPDFGenerationConstants::getOrientations();
        $this->assertSame('Portrait', $orientations['Portrait']);
        $this->assertSame('Landscape', $orientations['Landscape']);
    }

    public function testGetPageSizesNames() : void
    {
        $this->assertCount(15, ilPDFGenerationConstants::getPageSizesNames());
    }

    public function testHeaderConstants() : void
    {
        $this->assertSame(0, ilPDFGenerationConstants::HEADER_NONE);
        $this->assertSame(1, ilPDFGenerationConstants::HEADER_TEXT);
        $this->assertSame(2, ilPDFGenerationConstants::HEADER_HTML);
    }

    public function testFooterConstants() : void
    {
        $this->assertSame(0, ilPDFGenerationConstants::FOOTER_NONE);
        $this->assertSame(1, ilPDFGenerationConstants::FOOTER_TEXT);
        $this->assertSame(2, ilPDFGenerationConstants::FOOTER_HTML);
    }
}
