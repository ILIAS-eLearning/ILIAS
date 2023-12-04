<?php

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

declare(strict_types=1);

class ilXlsFoParserTest extends ilCertificateBaseTestCase
{
    /**
     * @param array{"certificate_text": string, "pageformat": string, "pagewidth"?: string, "pageheight"?: string, "margin_body": array{"top": string, "right": string, "bottom": string, "left": string}} $form_data
     */
    private function verifyFoGeneratedFromXhtml(array $form_data, string $fo): void
    {
        $settings = $this->getMockBuilder(ilSetting::class)
                         ->disableOriginalConstructor()
                         ->getMock();
        $settings->method('get')->willReturnArgument(0);

        $language = $this->getMockBuilder(ilLanguage::class)->disableOriginalConstructor()->onlyMethods(
            ['txt']
        )->getMock();
        $language->method('txt')->willReturnArgument(0);

        $page_formats = new ilPageFormats($language);

        $util_helper = $this->getMockBuilder(ilCertificateUtilHelper::class)
                            ->disableOriginalConstructor()
                            ->getMock();

        $util_helper->method('stripSlashes')
                    ->willReturnArgument(0);

        $xmlChecker = new ilXMLChecker(new ILIAS\Data\Factory());
        $xslt_process = new ilCertificateXlstProcess();

        $language = $this->getMockBuilder(ilLanguage::class)
                         ->disableOriginalConstructor()
                         ->getMock();

        $xsl_loader = new ilCertificateXlsFileLoader();

        $xlsFoParser = new ilXlsFoParser(
            $settings,
            $page_formats,
            $xmlChecker,
            $util_helper,
            $xslt_process,
            $language,
            $xsl_loader
        );

        $output = $xlsFoParser->parse($form_data);

        $this->assertSame($this->normalizeXml($fo), $this->normalizeXml($output));
    }

    private function normalizeXml(string $xml): string
    {
        $xml = str_replace(["\n", "\r", "\t"], '', $xml);
        $xml = preg_replace("/>(\s+)</", "><", $xml);
        $xml = preg_replace('# {2,}#', ' ', $xml);
        $xml = trim($xml);

        return $xml;
    }

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
                           ->disableOriginalConstructor()
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

    public function testParseButXmlCheckerFindsAnError(): never
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
                           ->disableOriginalConstructor()
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
                           ->disableOriginalConstructor()
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
                           ->disableOriginalConstructor()
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

    public function nonBreakingSpaceIsAddedDataProvider(): Generator
    {
        $expected_fo_with_centered_block = <<<EOT
<?xml version="1.0"?>
<fo:root xmlns:fo="http://www.w3.org/1999/XSL/Format" font-family="rpc_pdf_font">
    <fo:layout-master-set>
        <fo:simple-page-master master-name="ILIAS_certificate" page-height="297mm" page-width="210mm">
            <fo:region-body margin="1cm 2cm 3cm 4cm"/>
            <fo:region-before region-name="background-image" extent="0"/>
        </fo:simple-page-master>
    </fo:layout-master-set>
    <fo:page-sequence master-reference="ILIAS_certificate">
        <fo:static-content flow-name="background-image">
            <fo:block-container absolute-position="absolute" top="0cm" left="0cm" z-index="0">
                <fo:block>
                    <fo:external-graphic src="url([BACKGROUND_IMAGE])" content-height="297mm" content-width="210mm"/>
                </fo:block>
            </fo:block-container>
        </fo:static-content>
        <fo:flow flow-name="xsl-region-body">
            <fo:block>
                <fo:block text-align="center">&#160;</fo:block>
            </fo:block>
        </fo:flow>
    </fo:page-sequence>
</fo:root>
EOT;

        yield 'Centered Paragraph' => [
            [
                'certificate_text' => '<p style="text-align: center;"></p>',
                'margin_body' => [
                    'top' => '1cm',
                    'right' => '2cm',
                    'bottom' => '3cm',
                    'left' => '4cm'
                ],
                'pageformat' => 'custom',
                'pagewidth' => '210mm',
                'pageheight' => '297mm'
            ],
            $expected_fo_with_centered_block
        ];

        $expected_fo_with_centered_block = <<<EOT
<?xml version="1.0"?>
<fo:root xmlns:fo="http://www.w3.org/1999/XSL/Format" font-family="rpc_pdf_font">
    <fo:layout-master-set>
        <fo:simple-page-master master-name="ILIAS_certificate" page-height="297mm" page-width="210mm">
            <fo:region-body margin="1cm 2cm 3cm 4cm"/>
            <fo:region-before region-name="background-image" extent="0"/>
        </fo:simple-page-master>
    </fo:layout-master-set>
    <fo:page-sequence master-reference="ILIAS_certificate">
        <fo:static-content flow-name="background-image">
            <fo:block-container absolute-position="absolute" top="0cm" left="0cm" z-index="0">
                <fo:block>
                    <fo:external-graphic src="url([BACKGROUND_IMAGE])" content-height="297mm" content-width="210mm"/>
                </fo:block>
            </fo:block-container>
        </fo:static-content>
        <fo:flow flow-name="xsl-region-body">
            <fo:block>
                <fo:block>&#160;</fo:block>
            </fo:block>
        </fo:flow>
    </fo:page-sequence>
</fo:root>
EOT;

        yield 'Empty paragraph' => [
            [
                'certificate_text' => '<p></p>',
                'margin_body' => [
                    'top' => '1cm',
                    'right' => '2cm',
                    'bottom' => '3cm',
                    'left' => '4cm'
                ],
                'pageformat' => 'custom',
                'pagewidth' => '210mm',
                'pageheight' => '297mm'
            ],
            $expected_fo_with_centered_block
        ];
    }

    /**
     * @dataProvider nonBreakingSpaceIsAddedDataProvider
     * @param array{"certificate_text": string, "pageformat": string, "pagewidth"?: string, "pageheight"?: string, "margin_body": array{"top": string, "right": string, "bottom": string, "left": string}} $form_data
     */
    public function testTransformingParagraphsWithNoTextAndNoChildrenResultsInNonBreakingSpaceXslFoBlock(
        array $form_data,
        string $fo
    ): void {
        $this->verifyFoGeneratedFromXhtml($form_data, $fo);
    }

    public function noNonBreakingSpaceIsAddedDataProvider(): Generator
    {
        $expected_fo_with_centered_block = <<<EOT
<?xml version="1.0"?>
<fo:root xmlns:fo="http://www.w3.org/1999/XSL/Format" font-family="rpc_pdf_font">
    <fo:layout-master-set>
        <fo:simple-page-master master-name="ILIAS_certificate" page-height="297mm" page-width="210mm">
            <fo:region-body margin="1cm 2cm 3cm 4cm"/>
            <fo:region-before region-name="background-image" extent="0"/>
        </fo:simple-page-master>
    </fo:layout-master-set>
    <fo:page-sequence master-reference="ILIAS_certificate">
        <fo:static-content flow-name="background-image">
            <fo:block-container absolute-position="absolute" top="0cm" left="0cm" z-index="0">
                <fo:block>
                    <fo:external-graphic src="url([BACKGROUND_IMAGE])" content-height="297mm" content-width="210mm"/>
                </fo:block>
            </fo:block-container>
        </fo:static-content>
        <fo:flow flow-name="xsl-region-body">
            <fo:block>
                <fo:block>[USER_FULLNAME]</fo:block>
            </fo:block>
        </fo:flow>
    </fo:page-sequence>
</fo:root>
EOT;

        yield 'Paragraph with Text' => [
            [
                'certificate_text' => '<p>[USER_FULLNAME]</p>',
                'margin_body' => [
                    'top' => '1cm',
                    'right' => '2cm',
                    'bottom' => '3cm',
                    'left' => '4cm'
                ],
                'pageformat' => 'custom',
                'pagewidth' => '210mm',
                'pageheight' => '297mm'
            ],
            $expected_fo_with_centered_block
        ];

        $expected_fo_with_centered_block = <<<EOT
<?xml version="1.0"?>
<fo:root xmlns:fo="http://www.w3.org/1999/XSL/Format" font-family="rpc_pdf_font">
    <fo:layout-master-set>
        <fo:simple-page-master master-name="ILIAS_certificate" page-height="297mm" page-width="210mm">
            <fo:region-body margin="1cm 2cm 3cm 4cm"/>
            <fo:region-before region-name="background-image" extent="0"/>
        </fo:simple-page-master>
    </fo:layout-master-set>
    <fo:page-sequence master-reference="ILIAS_certificate">
        <fo:static-content flow-name="background-image">
            <fo:block-container absolute-position="absolute" top="0cm" left="0cm" z-index="0">
                <fo:block>
                    <fo:external-graphic src="url([BACKGROUND_IMAGE])" content-height="297mm" content-width="210mm"/>
                </fo:block>
            </fo:block-container>
        </fo:static-content>
        <fo:flow flow-name="xsl-region-body">
            <fo:block>
                <fo:block><fo:inline font-size="24pt">[USER_FULLNAME]</fo:inline></fo:block>
            </fo:block>
        </fo:flow>
    </fo:page-sequence>
</fo:root>
EOT;

        yield 'Paragraph with Nodes' => [
            [
                'certificate_text' => '<p><span style="font-size: 24pt;">[USER_FULLNAME]</span></p>',
                'margin_body' => [
                    'top' => '1cm',
                    'right' => '2cm',
                    'bottom' => '3cm',
                    'left' => '4cm'
                ],
                'pageformat' => 'custom',
                'pagewidth' => '210mm',
                'pageheight' => '297mm'
            ],
            $expected_fo_with_centered_block
        ];
    }

    /**
     * @dataProvider noNonBreakingSpaceIsAddedDataProvider
     * @param array{"certificate_text": string, "pageformat": string, "pagewidth"?: string, "pageheight"?: string, "margin_body": array{"top": string, "right": string, "bottom": string, "left": string}} $form_data
     */
    public function testTransformingParagraphsWithTextOrChildrenResultsNotInNonBreakingSpaceXslFoBlock(
        array $form_data,
        string $fo
    ): void {
        $this->verifyFoGeneratedFromXhtml($form_data, $fo);
    }
}
