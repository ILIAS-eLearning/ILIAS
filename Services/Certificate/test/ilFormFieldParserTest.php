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
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilFormFieldParserTest extends ilCertificateBaseTestCase
{
    public function testA4() : void
    {
        $xlstProcess = $this->getMockBuilder(ilCertificateXlstProcess::class)
            ->getMock();

        $content = '';

        $parser = new ilFormFieldParser($xlstProcess);
        $formFields = $parser->fetchDefaultFormFields($content);

        $this->assertSame(
            [
                'pageformat' => 'a4',
                'pagewidth' => '21cm',
                'pageheight' => '29.7cm',
                'margin_body_top' => '0cm',
                'margin_body_right' => '2cm',
                'margin_body_bottom' => '0cm',
                'margin_body_left' => '2cm',
                'certificate_text' => ''
            ],
            $formFields
        );
    }

    public function testCustomPageWidth() : void
    {
        $xlstProcess = $this->getMockBuilder(ilCertificateXlstProcess::class)
            ->getMock();

        $xlstProcess
            ->expects($this->once())
            ->method('process');

        $content = 'page-width="210mm" page-height="310mm" <fo:region-body margin="1cm 2cm 3cm 4cm"/>';

        $parser = new ilFormFieldParser($xlstProcess);
        $formFields = $parser->fetchDefaultFormFields($content);

        $this->assertSame(
            [
                'pageformat' => 'custom',
                'pagewidth' => '210mm',
                'pageheight' => '310mm',
                'margin_body_top' => '1cm',
                'margin_body_right' => '2cm',
                'margin_body_bottom' => '3cm',
                'margin_body_left' => '4cm',
                'certificate_text' => ''
            ],
            $formFields
        );
    }

    public function testA5() : void
    {
        $xlstProcess = $this->getMockBuilder(ilCertificateXlstProcess::class)
            ->getMock();

        $xlstProcess
            ->expects($this->once())
            ->method('process');

        $content = 'page-width="14.8cm" page-height="21cm" <fo:region-body margin="1cm 2cm 3cm 4cm"/>';

        $parser = new ilFormFieldParser($xlstProcess);
        $formFields = $parser->fetchDefaultFormFields($content);

        $this->assertSame(
            [
                'pageformat' => 'a5',
                'pagewidth' => '14.8cm',
                'pageheight' => '21cm',
                'margin_body_top' => '1cm',
                'margin_body_right' => '2cm',
                'margin_body_bottom' => '3cm',
                'margin_body_left' => '4cm',
                'certificate_text' => ''
            ],
            $formFields
        );
    }

    public function testA5Landscape() : void
    {
        $xlstProcess = $this->getMockBuilder(ilCertificateXlstProcess::class)
            ->getMock();

        $xlstProcess
            ->expects($this->once())
            ->method('process');

        $content = 'page-width="21cm" page-height="14.8cm" <fo:region-body margin="1cm 2cm 3cm 4cm"/>';

        $parser = new ilFormFieldParser($xlstProcess);
        $formFields = $parser->fetchDefaultFormFields($content);

        $this->assertSame(
            [
                'pageformat' => 'a5landscape',
                'pagewidth' => '21cm',
                'pageheight' => '14.8cm',
                'margin_body_top' => '1cm',
                'margin_body_right' => '2cm',
                'margin_body_bottom' => '3cm',
                'margin_body_left' => '4cm',
                'certificate_text' => ''
            ],
            $formFields
        );
    }

    public function testA4Landscape() : void
    {
        $xlstProcess = $this->getMockBuilder(ilCertificateXlstProcess::class)
            ->getMock();

        $xlstProcess
            ->expects($this->once())
            ->method('process');

        $content = 'page-width="29.7cm" page-height="21cm" <fo:region-body margin="1cm 2cm 3cm 4cm"/>';

        $parser = new ilFormFieldParser($xlstProcess);
        $formFields = $parser->fetchDefaultFormFields($content);

        $this->assertSame(
            [
                'pageformat' => 'a4landscape',
                'pagewidth' => '29.7cm',
                'pageheight' => '21cm',
                'margin_body_top' => '1cm',
                'margin_body_right' => '2cm',
                'margin_body_bottom' => '3cm',
                'margin_body_left' => '4cm',
                'certificate_text' => ''
            ],
            $formFields
        );
    }

    public function testLetterLandscape() : void
    {
        $xlstProcess = $this->getMockBuilder(ilCertificateXlstProcess::class)
            ->getMock();

        $xlstProcess
            ->expects($this->once())
            ->method('process');

        $content = 'page-width="11in" page-height="8.5in" <fo:region-body margin="1cm 2cm 3cm 4cm"/>';

        $parser = new ilFormFieldParser($xlstProcess);
        $formFields = $parser->fetchDefaultFormFields($content);

        $this->assertSame(
            [
                'pageformat' => 'letterlandscape',
                'pagewidth' => '11in',
                'pageheight' => '8.5in',
                'margin_body_top' => '1cm',
                'margin_body_right' => '2cm',
                'margin_body_bottom' => '3cm',
                'margin_body_left' => '4cm',
                'certificate_text' => ''
            ],
            $formFields
        );
    }

    public function testLetter() : void
    {
        $xlstProcess = $this->getMockBuilder(ilCertificateXlstProcess::class)
            ->getMock();

        $xlstProcess
            ->expects($this->once())
            ->method('process');

        $content = 'page-width="8.5in" page-height="11in" <fo:region-body margin="1cm 2cm 3cm 4cm"/>';

        $parser = new ilFormFieldParser($xlstProcess);
        $formFields = $parser->fetchDefaultFormFields($content);

        $this->assertSame(
            [
                'pageformat' => 'letter',
                'pagewidth' => '8.5in',
                'pageheight' => '11in',
                'margin_body_top' => '1cm',
                'margin_body_right' => '2cm',
                'margin_body_bottom' => '3cm',
                'margin_body_left' => '4cm',
                'certificate_text' => ''
            ],
            $formFields
        );
    }
}
