<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilFormFieldParserTest extends PHPUnit_Framework_TestCase
{
    public function testA4()
    {
        $xlstProcess = $this->getMockBuilder('ilCertificateXlstProcess')
            ->getMock();

        $content = '';

        $parser = new ilFormFieldParser($xlstProcess);
        $formFields = $parser->fetchDefaultFormFields($content);

        $this->assertEquals(
            array(
                'pageformat' => 'a4',
                'pagewidth' => '21cm',
                'pageheight' => '29.7cm',
                'margin_body_top' => '0cm',
                'margin_body_right' => '2cm',
                'margin_body_bottom' => '0cm',
                'margin_body_left' => '2cm',
                'certificate_text' => ''
            ),
            $formFields
        );
    }

    public function testCustomPageWidth()
    {
        $xlstProcess = $this->getMockBuilder('ilCertificateXlstProcess')
            ->getMock();

        $xlstProcess
            ->expects($this->once())
            ->method('process');

        $content = 'page-width="210mm" page-height="310mm" <fo:region-body margin="1cm 2cm 3cm 4cm"/>';

        $parser = new ilFormFieldParser($xlstProcess);
        $formFields = $parser->fetchDefaultFormFields($content);

        $this->assertEquals(
            array(
                'pageformat' => 'custom',
                'pagewidth' => '210mm',
                'pageheight' => '310mm',
                'margin_body_top' => '1cm',
                'margin_body_right' => '2cm',
                'margin_body_bottom' => '3cm',
                'margin_body_left' => '4cm',
                'certificate_text' => ''
            ),
            $formFields
        );
    }

    public function testA5()
    {
        $xlstProcess = $this->getMockBuilder('ilCertificateXlstProcess')
            ->getMock();

        $xlstProcess
            ->expects($this->once())
            ->method('process');

        $content = 'page-width="14.8cm" page-height="21cm" <fo:region-body margin="1cm 2cm 3cm 4cm"/>';

        $parser = new ilFormFieldParser($xlstProcess);
        $formFields = $parser->fetchDefaultFormFields($content);

        $this->assertEquals(
            array(
                'pageformat' => 'a5',
                'pagewidth' => '14.8cm',
                'pageheight' => '21cm',
                'margin_body_top' => '1cm',
                'margin_body_right' => '2cm',
                'margin_body_bottom' => '3cm',
                'margin_body_left' => '4cm',
                'certificate_text' => ''
            ),
            $formFields
        );
    }

    public function testA5Landscape()
    {
        $xlstProcess = $this->getMockBuilder('ilCertificateXlstProcess')
            ->getMock();

        $xlstProcess
            ->expects($this->once())
            ->method('process');

        $content = 'page-width="21cm" page-height="14.8cm" <fo:region-body margin="1cm 2cm 3cm 4cm"/>';

        $parser = new ilFormFieldParser($xlstProcess);
        $formFields = $parser->fetchDefaultFormFields($content);

        $this->assertEquals(
            array(
                'pageformat' => 'a5landscape',
                'pagewidth' => '21cm',
                'pageheight' => '14.8cm',
                'margin_body_top' => '1cm',
                'margin_body_right' => '2cm',
                'margin_body_bottom' => '3cm',
                'margin_body_left' => '4cm',
                'certificate_text' => ''
            ),
            $formFields
        );
    }

    public function testA4Landscape()
    {
        $xlstProcess = $this->getMockBuilder('ilCertificateXlstProcess')
            ->getMock();

        $xlstProcess
            ->expects($this->once())
            ->method('process');

        $content = 'page-width="29.7cm" page-height="21cm" <fo:region-body margin="1cm 2cm 3cm 4cm"/>';

        $parser = new ilFormFieldParser($xlstProcess);
        $formFields = $parser->fetchDefaultFormFields($content);

        $this->assertEquals(
            array(
                'pageformat' => 'a4landscape',
                'pagewidth' => '29.7cm',
                'pageheight' => '21cm',
                'margin_body_top' => '1cm',
                'margin_body_right' => '2cm',
                'margin_body_bottom' => '3cm',
                'margin_body_left' => '4cm',
                'certificate_text' => ''
            ),
            $formFields
        );
    }

    public function testLetterLandscape()
    {
        $xlstProcess = $this->getMockBuilder('ilCertificateXlstProcess')
            ->getMock();

        $xlstProcess
            ->expects($this->once())
            ->method('process');

        $content = 'page-width="11in" page-height="8.5in" <fo:region-body margin="1cm 2cm 3cm 4cm"/>';

        $parser = new ilFormFieldParser($xlstProcess);
        $formFields = $parser->fetchDefaultFormFields($content);

        $this->assertEquals(
            array(
                'pageformat' => 'letterlandscape',
                'pagewidth' => '11in',
                'pageheight' => '8.5in',
                'margin_body_top' => '1cm',
                'margin_body_right' => '2cm',
                'margin_body_bottom' => '3cm',
                'margin_body_left' => '4cm',
                'certificate_text' => ''
            ),
            $formFields
        );
    }

    public function testLetter()
    {
        $xlstProcess = $this->getMockBuilder('ilCertificateXlstProcess')
            ->getMock();

        $xlstProcess
            ->expects($this->once())
            ->method('process');

        $content = 'page-width="8.5in" page-height="11in" <fo:region-body margin="1cm 2cm 3cm 4cm"/>';

        $parser = new ilFormFieldParser($xlstProcess);
        $formFields = $parser->fetchDefaultFormFields($content);

        $this->assertEquals(
            array(
                'pageformat' => 'letter',
                'pagewidth' => '8.5in',
                'pageheight' => '11in',
                'margin_body_top' => '1cm',
                'margin_body_right' => '2cm',
                'margin_body_bottom' => '3cm',
                'margin_body_left' => '4cm',
                'certificate_text' => ''
            ),
            $formFields
        );
    }
}
