<?php

declare(strict_types=1);

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
class ilXlsFoParserTest extends ilCertificateBaseTestCase
{
    public function testParseWithNonCustomPageFormatting(): void
    {
        $formData = [
            'certificate_text' => '<xml> Some Context </xml>',
            'margin_body' => [
                'top' => '1cm',
                'right' => '2cm',
                'bottom' => '3cm',
                'left' => '4cm'
            ],
            'pageformat' => 'a4'
        ];

        $settings = $this->getMockBuilder(ilSetting::class)
            ->disableOriginalConstructor()
            ->getMock();

        $settings->method('get')
            ->willReturn('Something');

        $pageFormats = $this->getMockBuilder(ilPageFormats::class)
            ->disableOriginalConstructor()
            ->getMock();

        $pageFormats->method('fetchPageFormats')
            ->willReturn([
                'a4' => [
                    'name' => 'A4',
                    'value' => 'a4',
                    'width' => '210mm',
                    'height' => '297mm'
                ],
            ]);

        $xmlChecker = new ilXMLChecker(new ILIAS\Data\Factory());

        $utilHelper = $this->getMockBuilder(ilCertificateUtilHelper::class)
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

        $xlstProcess = $this->getMockBuilder(ilCertificateXlstProcess::class)
            ->getMock();

        $xlstProcess->method('process')
            ->with(
                [
                    '/_xml' => '<html><body><xml> Some Context </xml></body></html>',
                    '/_xsl' => '<xml>Some XLS Content</xml>'
                ],
                [
                    'pageheight' => '297mm',
                    'pagewidth' => '210mm',
                    'backgroundimage' => '[BACKGROUND_IMAGE]',
                    'marginbody' => '1cm 2cm 3cm 4cm'
                ]
            )
            ->willReturn('Something Processed');

        $language = $this->getMockBuilder(ilLanguage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $certificateXlsFileLoader = $this->getMockBuilder(ilCertificateXlsFileLoader::class)
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

        $this->assertSame('Something Processed', $output);
    }

    public function testParseButXmlCheckerFindsAnError(): void
    {
        $this->expectException(Exception::class);

        $formData = [
            'certificate_text' => '<xml> Some Context <xml>',
            'margin_body' => [
                'top' => '1cm',
                'right' => '2cm',
                'bottom' => '3cm',
                'left' => '4cm'
            ],
            'pageformat' => 'custom'
        ];

        $settings = $this->getMockBuilder(ilSetting::class)
            ->disableOriginalConstructor()
            ->getMock();

        $settings->method('get')
            ->willReturn('Something');

        $pageFormats = $this->getMockBuilder(ilPageFormats::class)
            ->disableOriginalConstructor()
            ->getMock();

        $pageFormats->method('fetchPageFormats')
            ->willReturn([
                'a4' => [
                    'name' => 'A4',
                    'value' => 'a4',
                    'width' => '210mm',
                    'height' => '297mm'
                ],
            ]);

        $xmlChecker = new ilXMLChecker(new ILIAS\Data\Factory());

        $utilHelper = $this->getMockBuilder(ilCertificateUtilHelper::class)
            ->getMock();

        $xlstProcess = $this->getMockBuilder(ilCertificateXlstProcess::class)
            ->getMock();

        $language = $this->getMockBuilder(ilLanguage::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['txt'])
            ->getMock();
        $language->expects($this->once())->method('txt')->willReturn('certificate_not_well_formed');

        $certificateXlsFileLoader = $this->getMockBuilder(ilCertificateXlsFileLoader::class)
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

    public function testParseWithCustomPageFormatting(): void
    {
        $formData = [
            'certificate_text' => '<xml> Some Context </xml>',
            'margin_body' => [
                'top' => '1cm',
                'right' => '2cm',
                'bottom' => '3cm',
                'left' => '4cm'
            ],
            'pageformat' => 'custom',
            'pagewidth' => '210mm',
            'pageheight' => '297mm'
        ];

        $settings = $this->getMockBuilder(ilSetting::class)
            ->disableOriginalConstructor()
            ->getMock();

        $settings->method('get')
            ->willReturn('Something');

        $pageFormats = $this->getMockBuilder(ilPageFormats::class)
            ->disableOriginalConstructor()
            ->getMock();

        $pageFormats->method('fetchPageFormats')
            ->willReturn([
                'a4' => [
                    'name' => 'A4',
                    'value' => 'a4',
                    'width' => '210mm',
                    'height' => '297mm'
                ],
            ]);

        $xmlChecker = new ilXMLChecker(new ILIAS\Data\Factory());

        $utilHelper = $this->getMockBuilder(ilCertificateUtilHelper::class)
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

        $xlstProcess = $this->getMockBuilder(ilCertificateXlstProcess::class)
            ->getMock();

        $xlstProcess->method('process')
            ->with(
                [
                    '/_xml' => '<html><body><xml> Some Context </xml></body></html>',
                    '/_xsl' => '<xml>Some XLS Content</xml>'
                ],
                [
                    'pageheight' => '297mm',
                    'pagewidth' => '210mm',
                    'backgroundimage' => '[BACKGROUND_IMAGE]',
                    'marginbody' => '1cm 2cm 3cm 4cm'
                ]
            )
            ->willReturn('Something Processed');

        $language = $this->getMockBuilder(ilLanguage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $certificateXlsFileLoader = $this->getMockBuilder(ilCertificateXlsFileLoader::class)
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

        $this->assertSame('Something Processed', $output);
    }

    public function testCommasWillBeConvertedToPointInDecimalSepartor(): void
    {
        $formData = [
            'certificate_text' => '<xml> Some Context </xml>',
            'margin_body' => [
                'top' => '1cm',
                'right' => '2cm',
                'bottom' => '3cm',
                'left' => '4cm'
            ],
            'pageformat' => 'custom',
            'pagewidth' => '210mm',
            'pageheight' => '297mm'
        ];

        $settings = $this->getMockBuilder(ilSetting::class)
            ->disableOriginalConstructor()
            ->getMock();

        $settings->method('get')
            ->willReturn('Something');

        $pageFormats = $this->getMockBuilder(ilPageFormats::class)
            ->disableOriginalConstructor()
            ->getMock();

        $pageFormats->method('fetchPageFormats')
            ->willReturn([
                'a4' => [
                    'name' => 'A4',
                    'value' => 'a4',
                    'width' => '21,0mm',
                    'height' => '29,7mm'
                ],
            ]);

        $xmlChecker = new ilXMLChecker(new ILIAS\Data\Factory());

        $utilHelper = $this->getMockBuilder(ilCertificateUtilHelper::class)
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

        $xlstProcess = $this->getMockBuilder(ilCertificateXlstProcess::class)
            ->getMock();

        $xlstProcess->method('process')
            ->with(
                [
                    '/_xml' => '<html><body><xml> Some Context </xml></body></html>',
                    '/_xsl' => '<xml>Some XLS Content</xml>'
                ],
                [
                    'pageheight' => '29.7mm',
                    'pagewidth' => '21.0mm',
                    'backgroundimage' => '[BACKGROUND_IMAGE]',
                    'marginbody' => '1cm 2cm 3cm 4cm'
                ]
            )
            ->willReturn('Something Processed');

        $language = $this->getMockBuilder(ilLanguage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $certificateXlsFileLoader = $this->getMockBuilder(ilCertificateXlsFileLoader::class)
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

        $this->assertSame('Something Processed', $output);
    }
}
