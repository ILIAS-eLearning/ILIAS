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

class ilCertificateLearningHistoryProviderTest extends ilCertificateBaseTestCase
{
    public function testIsActive(): void
    {
        $learningHistoryFactory = $this->getMockBuilder(ilLearningHistoryFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $language = $this->getMockBuilder(ilLanguage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $dic = $this->getMockBuilder(\ILIAS\DI\Container::class)
            ->disableOriginalConstructor()
            ->getMock();

        $template = $this->getMockBuilder(ilTemplate::class)
            ->disableOriginalConstructor()
            ->getMock();

        $userCertificateRepository = $this->getMockBuilder(ilUserCertificateRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $controller = $this->getMockBuilder(ilCtrlInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $certificateSettings = $this->getMockBuilder(ilSetting::class)
            ->disableOriginalConstructor()
            ->getMock();

        $certificateSettings->method('get')
            ->willReturn('1');

        $uiFactory = $this->getMockBuilder(ILIAS\UI\Factory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $uiRenderer = $this->getMockBuilder(ILIAS\UI\Renderer::class)
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

    public function testGetEntries(): void
    {
        $learningHistoryFactory = $this->getMockBuilder(ilLearningHistoryFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $language = $this->getMockBuilder(ilLanguage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $language->method('txt')
            ->willReturnOnConsecutiveCalls(
                'Certificate for %1$s',
                '%1$s achieved.',
                'Certificate for %1$s',
                '%1$s achieved.'
            );

        $dic = $this->getMockBuilder(\ILIAS\DI\Container::class)
            ->disableOriginalConstructor()
            ->getMock();

        $template = $this->getMockBuilder(ilTemplate::class)
            ->disableOriginalConstructor()
            ->getMock();

        $template->method('get')
            ->willReturnOnConsecutiveCalls('Course Title', 'Test Title');

        $userCertificateRepository = $this->getMockBuilder(ilUserCertificateRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $userCertificateRepository->method('fetchActiveCertificatesInIntervalForPresentation')
            ->willReturn(
                [
                    new ilUserCertificatePresentation(
                        200,
                        'crs',
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
                        500,
                        'tst',
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
                ]
            );

        $controller = $this->getMockBuilder(ilCtrlInterface::class)
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

        $certificateSettings = $this->getMockBuilder(ilSetting::class)
            ->disableOriginalConstructor()
            ->getMock();

        $certificateSettings->method('get')
            ->willReturn('1');

        $uiFactory = $this->getMockBuilder(ILIAS\UI\Factory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $link = $this->getMockBuilder(\ILIAS\UI\Component\Link\Factory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $std_link = $this->getMockBuilder(\ILIAS\UI\Component\Link\Standard::class)
            ->disableOriginalConstructor()
            ->getMock();

        $link->method('standard')
            ->withConsecutive(
                ['Course Title', '<a href> </a>'],
                ['Test Title', '<a href> </a>']
            )
            ->willReturn($std_link);

        $uiFactory->method('link')
            ->willReturn($link);

        $uiRenderer = $this->getMockBuilder(ILIAS\UI\Renderer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $uiRenderer->method('render')
            ->with($std_link)
            ->willReturn('link');

        $utilHelper = $this->getMockBuilder(ilCertificateUtilHelper::class)
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

        $expectedEntries = [
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
            ),
        ];

        $actualEntries = $provider->getEntries(123456789, 987654321);
        $this->assertEquals($expectedEntries, $actualEntries);
    }

    public function testGetName(): void
    {
        $learningHistoryFactory = $this->getMockBuilder(ilLearningHistoryFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $language = $this->getMockBuilder(ilLanguage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $language
            ->expects($this->once())
            ->method('txt')
            ->willReturn('Certificates');

        $dic = $this->getMockBuilder(\ILIAS\DI\Container::class)
            ->disableOriginalConstructor()
            ->getMock();

        $template = $this->getMockBuilder(ilTemplate::class)
            ->disableOriginalConstructor()
            ->getMock();

        $userCertificateRepository = $this->getMockBuilder(ilUserCertificateRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $controller = $this->getMockBuilder(ilCtrlInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $certificateSettings = $this->getMockBuilder(ilSetting::class)
            ->disableOriginalConstructor()
            ->getMock();

        $certificateSettings->method('get')
            ->willReturn('1');

        $uiFactory = $this->getMockBuilder(ILIAS\UI\Factory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $uiRenderer = $this->getMockBuilder(ILIAS\UI\Renderer::class)
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

        $this->assertSame('Certificates', $provider->getName());
    }
}
