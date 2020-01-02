<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilXlsFoParserTest extends PHPUnit_Framework_TestCase
{
    public function testParseWithNonCustomPageFormatting()
    {
        $formData = array(
            'certificate_text' => '<xml> Some Context </xml>',
            'margin_body' => array(
                'top'    => '1cm',
                'right'  => '2cm',
                'bottom' => '3cm',
                'left'   => '4cm'
            ),
            'pageformat' => 'a4'
        );

        $settings = $this->getMockBuilder('ilSetting')
            ->disableOriginalConstructor()
            ->getMock();

        $settings->method('get')
            ->willReturn('Something');

        $pageFormats = $this->getMockBuilder('ilPageFormats')
            ->disableOriginalConstructor()
            ->getMock();

        $pageFormats->method('fetchPageFormats')
            ->willReturn(array(
                'a4' => array(
                    'name' => 'A4',
                    'value' => 'a4',
                    'width' => '210mm',
                    'height' => '297mm'
                ),
            ));

        $xmlChecker = $this->getMockBuilder('ilXMLChecker')
            ->getMock();

        $utilHelper = $this->getMockBuilder('ilCertificateUtilHelper')
            ->getMock();

        $utilHelper->method('stripSlashes')
            ->willReturnOnConsecutiveCalls(
                '297mm',
                '210mm',
                '1cm',
                '2cm',
                '3cm',
                '4cm'
            );

        $xlstProcess = $this->getMockBuilder('ilCertificateXlstProcess')
            ->getMock();

        $xlstProcess->method('process')
            ->with(
                array(
                    '/_xml' => '<html><body><xml> Some Context </xml></body></html>',
                    '/_xsl' => '<xml>Some XLS Content</xml>'
                ),
                array(
                    'pageheight'      => '297mm',
                    'pagewidth'       => '210mm',
                    'backgroundimage' => '[BACKGROUND_IMAGE]',
                    'marginbody'      => '1cm 2cm 3cm 4cm'
                )
            )
            ->willReturn('Something Processed');

        $language = $this->getMockBuilder('ilLanguage')
            ->disableOriginalConstructor()
            ->getMock();

        $certificateXlsFileLoader = $this->getMockBuilder('ilCertificateXlsFileLoader')
            ->getMock();

        $certificateXlsFileLoader->method('getXlsCertificateContent')
            ->willReturn('<xml>Some XLS Content</xml>');

        $xlsFoParser = new ilXlsFoParser(
            $settings,
            $pageFormats,
            $xmlChecker,
            $utilHelper,
            $xlstProcess,
            $language,
            $certificateXlsFileLoader
        );

        $output = $xlsFoParser->parse($formData);

        $this->assertEquals('Something Processed', $output);
    }

    /**
     * @expectedException  Exception
     */
    public function testParseButXmlCheckerFindsAnError()
    {
        $formData = array(
            'certificate_text' => '<xml> Some Context </xml>',
            'margin_body' => array(
                'top'    => '1cm',
                'right'  => '2cm',
                'bottom' => '3cm',
                'left'   => '4cm'
            ),
            'pageformat' => 'custom'
        );

        $settings = $this->getMockBuilder('ilSetting')
            ->disableOriginalConstructor()
            ->getMock();

        $settings->method('get')
            ->willReturn('Something');

        $pageFormats = $this->getMockBuilder('ilPageFormats')
            ->disableOriginalConstructor()
            ->getMock();

        $pageFormats->method('fetchPageFormats')
            ->willReturn(array(
                'a4' => array(
                    'name' => 'A4',
                    'value' => 'a4',
                    'width' => '210mm',
                    'height' => '297mm'
                ),
            ));

        $xmlChecker = $this->getMockBuilder('ilXMLChecker')
            ->getMock();

        $xmlChecker->method('hasError')
            ->willReturn(true);

        $utilHelper = $this->getMockBuilder('ilCertificateUtilHelper')
            ->getMock();

        $xlstProcess = $this->getMockBuilder('ilCertificateXlstProcess')
            ->getMock();

        $language = $this->getMockBuilder('ilLanguage')
            ->disableOriginalConstructor()
            ->getMock();

        $certificateXlsFileLoader = $this->getMockBuilder('ilCertificateXlsFileLoader')
            ->getMock();

        $certificateXlsFileLoader->method('getXlsCertificateContent')
            ->willReturn('<xml>Some XLS Content</xml>');

        $xlsFoParser = new ilXlsFoParser(
            $settings,
            $pageFormats,
            $xmlChecker,
            $utilHelper,
            $xlstProcess,
            $language,
            $certificateXlsFileLoader
        );

        $xlsFoParser->parse($formData);

        $this->fail();
    }

    public function testParseWithCustomPageFormatting()
    {
        $formData = array(
            'certificate_text' => '<xml> Some Context </xml>',
            'margin_body' => array(
                'top'    => '1cm',
                'right'  => '2cm',
                'bottom' => '3cm',
                'left'   => '4cm'
            ),
            'pageformat' => 'custom',
            'pagewidth' => '210mm',
            'pageheight' => '297mm'
        );

        $settings = $this->getMockBuilder('ilSetting')
            ->disableOriginalConstructor()
            ->getMock();

        $settings->method('get')
            ->willReturn('Something');

        $pageFormats = $this->getMockBuilder('ilPageFormats')
            ->disableOriginalConstructor()
            ->getMock();

        $pageFormats->method('fetchPageFormats')
            ->willReturn(array(
                'a4' => array(
                    'name' => 'A4',
                    'value' => 'a4',
                    'width' => '210mm',
                    'height' => '297mm'
                ),
            ));

        $xmlChecker = $this->getMockBuilder('ilXMLChecker')
            ->getMock();

        $utilHelper = $this->getMockBuilder('ilCertificateUtilHelper')
            ->getMock();

        $utilHelper->method('stripSlashes')
            ->willReturnOnConsecutiveCalls(
                '297mm',
                '210mm',
                '1cm',
                '2cm',
                '3cm',
                '4cm'
            );

        $xlstProcess = $this->getMockBuilder('ilCertificateXlstProcess')
            ->getMock();

        $xlstProcess->method('process')
            ->with(
                array(
                    '/_xml' => '<html><body><xml> Some Context </xml></body></html>',
                    '/_xsl' => '<xml>Some XLS Content</xml>'
                ),
                array(
                    'pageheight'      => '297mm',
                    'pagewidth'       => '210mm',
                    'backgroundimage' => '[BACKGROUND_IMAGE]',
                    'marginbody' => '1cm 2cm 3cm 4cm'
                )
            )
            ->willReturn('Something Processed');

        $language = $this->getMockBuilder('ilLanguage')
            ->disableOriginalConstructor()
            ->getMock();

        $certificateXlsFileLoader = $this->getMockBuilder('ilCertificateXlsFileLoader')
            ->getMock();

        $certificateXlsFileLoader->method('getXlsCertificateContent')
            ->willReturn('<xml>Some XLS Content</xml>');

        $xlsFoParser = new ilXlsFoParser(
            $settings,
            $pageFormats,
            $xmlChecker,
            $utilHelper,
            $xlstProcess,
            $language,
            $certificateXlsFileLoader
        );

        $output = $xlsFoParser->parse($formData);

        $this->assertEquals('Something Processed', $output);
    }

    public function testCommasWillBeConvertedToPointInDecimalSepartor()
    {
        $formData = array(
            'certificate_text' => '<xml> Some Context </xml>',
            'margin_body' => array(
                'top'    => '1cm',
                'right'  => '2cm',
                'bottom' => '3cm',
                'left'   => '4cm'
            ),
            'pageformat' => 'custom',
            'pagewidth' => '210mm',
            'pageheight' => '297mm'
        );

        $settings = $this->getMockBuilder('ilSetting')
            ->disableOriginalConstructor()
            ->getMock();

        $settings->method('get')
            ->willReturn('Something');

        $pageFormats = $this->getMockBuilder('ilPageFormats')
            ->disableOriginalConstructor()
            ->getMock();

        $pageFormats->method('fetchPageFormats')
            ->willReturn(array(
                'a4' => array(
                    'name' => 'A4',
                    'value' => 'a4',
                    'width' => '21,0mm',
                    'height' => '29,7mm'
                ),
            ));

        $xmlChecker = $this->getMockBuilder('ilXMLChecker')
            ->getMock();

        $utilHelper = $this->getMockBuilder('ilCertificateUtilHelper')
            ->getMock();

        $utilHelper->method('stripSlashes')
            ->willReturnOnConsecutiveCalls(
                '29,7mm',
                '21,0mm',
                '1cm',
                '2cm',
                '3cm',
                '4cm'
            );

        $xlstProcess = $this->getMockBuilder('ilCertificateXlstProcess')
            ->getMock();

        $xlstProcess->method('process')
            ->with(
                array(
                    '/_xml' => '<html><body><xml> Some Context </xml></body></html>',
                    '/_xsl' => '<xml>Some XLS Content</xml>'
                ),
                array(
                    'pageheight'      => '29.7mm',
                    'pagewidth'       => '21.0mm',
                    'backgroundimage' => '[BACKGROUND_IMAGE]',
                    'marginbody' => '1cm 2cm 3cm 4cm'
                )
            )
            ->willReturn('Something Processed');

        $language = $this->getMockBuilder('ilLanguage')
            ->disableOriginalConstructor()
            ->getMock();

        $certificateXlsFileLoader = $this->getMockBuilder('ilCertificateXlsFileLoader')
            ->getMock();

        $certificateXlsFileLoader->method('getXlsCertificateContent')
            ->willReturn('<xml>Some XLS Content</xml>');

        $xlsFoParser = new ilXlsFoParser(
            $settings,
            $pageFormats,
            $xmlChecker,
            $utilHelper,
            $xlstProcess,
            $language,
            $certificateXlsFileLoader
        );

        $output = $xlsFoParser->parse($formData);

        $this->assertEquals('Something Processed', $output);
    }
}
