<?php

class ilCertificateLearningHistoryProviderTest extends PHPUnit_Framework_TestCase
{
    public function testIsActive()
    {
        $learningHistoryFactory = $this->getMockBuilder('ilLearningHistoryFactory')
            ->disableOriginalConstructor()
            ->getMock();

        $language = $this->getMockBuilder('ilLanguage')
            ->disableOriginalConstructor()
            ->getMock();

        $dic = $this->getMockBuilder('\ILIAS\DI\Container')
            ->disableOriginalConstructor()
            ->getMock();

        $template = $this->getMockBuilder('ilTemplate')
            ->disableOriginalConstructor()
            ->getMock();

        $userCertificateRepository = $this->getMockBuilder('ilUserCertificateRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $controller = $this->getMockBuilder('ilCtrl')
            ->disableOriginalConstructor()
            ->getMock();

        $certificateSettings = $this->getMockBuilder('ilSetting')
            ->disableOriginalConstructor()
            ->getMock();

        $certificateSettings->method('get')
            ->willReturn(true);

        $uiFactory = $this->getMockBuilder('ILIAS\UI\Factory')
            ->disableOriginalConstructor()
            ->getMock();

        $uiRenderer = $this->getMockBuilder('ILIAS\UI\Renderer')
            ->disableOriginalConstructor()
            ->getMock();

        $provider = new ilCertificateLearningHistoryProvider(
            10,
            $learningHistoryFactory,
            $language,
            $template,
            $dic,
            $userCertificateRepository,
            $controller,
            $certificateSettings,
            $uiFactory,
            $uiRenderer
        );

        $this->assertTrue($provider->isActive());
    }

    public function testGetEntries()
    {
        $learningHistoryFactory = $this->getMockBuilder('ilLearningHistoryFactory')
            ->disableOriginalConstructor()
            ->getMock();

        $language = $this->getMockBuilder('ilLanguage')
            ->disableOriginalConstructor()
            ->getMock();

        $language->method('txt')
            ->willReturnOnConsecutiveCalls(
                'Certificate for %1$s',
                '%1$s achieved.',
                'Certificate for %1$s',
                '%1$s achieved.'
            );

        $dic = $this->getMockBuilder('\ILIAS\DI\Container')
            ->disableOriginalConstructor()
            ->getMock();

        $template = $this->getMockBuilder('ilTemplate')
            ->disableOriginalConstructor()
            ->getMock();

        $template->method('get')
            ->willReturnOnConsecutiveCalls('Course Title', 'Test Title');

        $userCertificateRepository = $this->getMockBuilder('ilUserCertificateRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $userCertificateRepository->method('fetchActiveCertificatesInIntervalForPresentation')
            ->willReturn(
                array(
                    new ilUserCertificatePresentation(
                        new ilUserCertificate(
                            1,
                            200,
                            'crs',
                            300,
                            'Ilyas Odys',
                            123456789,
                            '<xml>Some Content</xml>',
                            '["SOME_PLACEHOLDER"]',
                            null,
                            1,
                            'v5.4.0',
                            true,
                            '/some/where/background_1.jpg',
                            '/some/where/else/thumbnail_1.jpg',
                            40
                        ),
                        'Course Title',
                        'Course Description'
                    ),
                    new ilUserCertificatePresentation(
                        new ilUserCertificate(
                            5,
                            500,
                            'tst',
                            5000,
                            'Ilyas Odys',
                            987654321,
                            '<xml>Some Content</xml>',
                            '["SOME_PLACEHOLDER"]',
                            null,
                            1,
                            'v5.4.0',
                            true,
                            '/some/where/background_1.jpg',
                            '/some/where/else/thumbnail_1.jpg',
                            50
                        ),
                        'Test Title',
                        'Test Description'
                    )
                )
            );

        $controller = $this->getMockBuilder('ilCtrl')
            ->disableOriginalConstructor()
            ->getMock();

        $controller
            ->expects($this->exactly(2))
            ->method('getLinkTargetByClass')
            ->willReturn('<a href> </a>');

        $controller
            ->expects($this->exactly(2))
            ->method('clearParametersByClass');

        $controller
            ->expects($this->exactly(2))
            ->method('setParameterByClass');

        $certificateSettings = $this->getMockBuilder('ilSetting')
            ->disableOriginalConstructor()
            ->getMock();

        $certificateSettings->method('get')
            ->willReturn(true);

        $uiFactory = $this->getMockBuilder('ILIAS\UI\Factory')
            ->disableOriginalConstructor()
            ->getMock();

        $link = $this->getMockBuilder('\ILIAS\UI\Component\Link\Factory')
            ->disableOriginalConstructor()
            ->getMock();

        $link->method('standard')
            ->withConsecutive(
                array('Course Title', '<a href> </a>'),
                array('Test Title', '<a href> </a>')
            )
            ->willReturn('<link rel="stylesheet" href="">');

        $uiFactory->method('link')
            ->willReturn($link);

        $uiRenderer = $this->getMockBuilder('ILIAS\UI\Renderer')
            ->disableOriginalConstructor()
            ->getMock();

        $uiRenderer->method('render')
            ->with('<link rel="stylesheet" href="">')
            ->willReturn('link');

        $utilHelper = $this->getMockBuilder('ilCertificateUtilHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $utilHelper->method('getImagePath')
            ->willReturn('/some/acutal/image/path/background.png');

        $provider = new ilCertificateLearningHistoryProvider(
            10,
            $learningHistoryFactory,
            $language,
            $template,
            $dic,
            $userCertificateRepository,
            $controller,
            $certificateSettings,
            $uiFactory,
            $uiRenderer,
            $utilHelper
        );

        $expectedEntries = array(
            new ilLearningHistoryEntry(
                'Certificate for link achieved.',
                'Certificate for link achieved.',
                '/some/acutal/image/path/background.png',
                123456789,
                200
            ),
            new ilLearningHistoryEntry(
                'Certificate for link achieved.',
                'Certificate for link achieved.',
                '/some/acutal/image/path/background.png',
                987654321,
                500
            ),);

        $actualEntries = $provider->getEntries(123456789, 987654321);
        $this->assertEquals($expectedEntries, $actualEntries);
    }

    public function testGetName()
    {
        $learningHistoryFactory = $this->getMockBuilder('ilLearningHistoryFactory')
            ->disableOriginalConstructor()
            ->getMock();

        $language = $this->getMockBuilder('ilLanguage')
            ->disableOriginalConstructor()
            ->getMock();

        $language
            ->expects($this->once())
            ->method('txt')
            ->willReturn('Certificates');

        $dic = $this->getMockBuilder('\ILIAS\DI\Container')
            ->disableOriginalConstructor()
            ->getMock();

        $template = $this->getMockBuilder('ilTemplate')
            ->disableOriginalConstructor()
            ->getMock();

        $userCertificateRepository = $this->getMockBuilder('ilUserCertificateRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $controller = $this->getMockBuilder('ilCtrl')
            ->disableOriginalConstructor()
            ->getMock();

        $certificateSettings = $this->getMockBuilder('ilSetting')
            ->disableOriginalConstructor()
            ->getMock();

        $certificateSettings->method('get')
            ->willReturn(true);

        $uiFactory = $this->getMockBuilder('ILIAS\UI\Factory')
            ->disableOriginalConstructor()
            ->getMock();

        $uiRenderer = $this->getMockBuilder('ILIAS\UI\Renderer')
            ->disableOriginalConstructor()
            ->getMock();

        $provider = new ilCertificateLearningHistoryProvider(
            10,
            $learningHistoryFactory,
            $language,
            $template,
            $dic,
            $userCertificateRepository,
            $controller,
            $certificateSettings,
            $uiFactory,
            $uiRenderer
        );

        $this->assertEquals('Certificates', $provider->getName());
    }
}
