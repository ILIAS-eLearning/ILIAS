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

use ILIAS\Certificate\ValueObject\CertificateId;
use PHPUnit\Framework\MockObject\Stub;

class ilCertificateLearningHistoryProviderTest extends ilCertificateBaseTestCase
{
    public function testIsActive(): void
    {
        /** @var ilLearningHistoryFactory&Stub $learningHistoryFactory */
        $learningHistoryFactory = $this->createStub(ilLearningHistoryFactory::class);

        /** @var ilLanguage&Stub $language */
        $language = $this->createStub(ilLanguage::class);

        /** @var \ILIAS\DI\Container&Stub $dic */
        $dic = $this->createStub(\ILIAS\DI\Container::class);

        /** @var ilTemplate&Stub $template */
        $template = $this->createStub(ilTemplate::class);

        /** @var ilUserCertificateRepository&Stub $userCertificateRepository */
        $userCertificateRepository = $this->createStub(ilUserCertificateRepository::class);

        /** @var ilCtrlInterface&Stub $controller */
        $controller = $this->createStub(ilCtrlInterface::class);

        /** @var ilSetting&Stub $certificateSettings */
        $certificateSettings = $this->createStub(ilSetting::class);

        $certificateSettings->method('get')
            ->willReturn('1');

        /** @var ILIAS\UI\Factory&Stub $uiFactory */
        $uiFactory = $this->createStub(ILIAS\UI\Factory::class);

        /** @var ILIAS\UI\Renderer&Stub $uiRenderer */
        $uiRenderer = $this->createStub(ILIAS\UI\Renderer::class);

        /** @var ilCertificateUtilHelper&Stub $utilHelper */
        $utilHelper = $this->createStub(ilCertificateUtilHelper::class);

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

        $this->assertTrue($provider->isActive());
    }

    public function testGetEntries(): void
    {
        /** @var ilLearningHistoryFactory&Stub $learningHistoryFactory */
        $learningHistoryFactory = $this->createStub(ilLearningHistoryFactory::class);

        /** @var ilLanguage&Stub $language */
        $language = $this->createStub(ilLanguage::class);

        $language->method('txt')
            ->willReturnOnConsecutiveCalls(
                'Certificate for %1$s',
                '%1$s achieved.',
                'Certificate for %1$s',
                '%1$s achieved.'
            );

        /** @var \ILIAS\DI\Container&Stub $dic */
        $dic = $this->createStub(\ILIAS\DI\Container::class);

        /** @var ilTemplate&Stub $template */
        $template = $this->createStub(ilTemplate::class);

        $template->method('get')
            ->willReturnOnConsecutiveCalls('Course Title', 'Test Title');

        /** @var ilUserCertificateRepository&Stub $userCertificateRepository */
        $userCertificateRepository = $this->createStub(ilUserCertificateRepository::class);

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
                            123_456_789,
                            '<xml>Some Content</xml>',
                            '["SOME_PLACEHOLDER"]',
                            null,
                            1,
                            'v5.4.0',
                            true,
                            new CertificateId('11111111-2222-3333-4444-555555555555'),
                            '',
                            '',
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
                            987_654_321,
                            '<xml>Some Content</xml>',
                            '["SOME_PLACEHOLDER"]',
                            null,
                            1,
                            'v5.4.0',
                            true,
                            new CertificateId('11111111-2222-3333-4444-555555555555'),
                            '',
                            '',
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

        /** @var ilSetting&Stub $certificateSettings */
        $certificateSettings = $this->createStub(ilSetting::class);

        $certificateSettings->method('get')
            ->willReturn('1');

        /** @var ILIAS\UI\Factory&Stub $uiFactory */
        $uiFactory = $this->createStub(ILIAS\UI\Factory::class);

        $link = $this->getMockBuilder(\ILIAS\UI\Component\Link\Factory::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var \ILIAS\UI\Component\Link\Standard&Stub $std_link */
        $std_link = $this->createStub(\ILIAS\UI\Component\Link\Standard::class);

        $consecutive = ['Course Title', 'Test Title'];
        $link
            ->expects($this->exactly(2))
            ->method('standard')
            ->with(
                $this->callback(function ($value) use (&$consecutive) {
                    $this->assertSame(array_shift($consecutive), $value);
                    return true;
                }),
                $this->identicalTo('<a href> </a>')
            )
            ->willReturn($std_link);

        $uiFactory->method('link')
            ->willReturn($link);

        $uiRenderer = $this->getMockBuilder(ILIAS\UI\Renderer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $uiRenderer->expects($this->exactly(2))
            ->method('render')
            ->with($std_link)
            ->willReturn('link');

        /** @var ilCertificateUtilHelper&Stub $utilHelper */
        $utilHelper = $this->createStub(ilCertificateUtilHelper::class);

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
                123_456_789,
                200
            ),
            new ilLearningHistoryEntry(
                'Certificate for link achieved.',
                'Certificate for link achieved.',
                '/some/acutal/image/path/background.png',
                987_654_321,
                500
            ),
        ];

        $actualEntries = $provider->getEntries(123_456_789, 987_654_321);
        $this->assertEquals($expectedEntries, $actualEntries);
    }

    public function testGetName(): void
    {
        /** @var ilLearningHistoryFactory&Stub $learningHistoryFactory */
        $learningHistoryFactory = $this->createStub(ilLearningHistoryFactory::class);

        $language = $this->getMockBuilder(ilLanguage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $language
            ->expects($this->once())
            ->method('txt')
            ->willReturn('Certificates');

        /** @var \ILIAS\DI\Container&Stub $dic */
        $dic = $this->createStub(\ILIAS\DI\Container::class);

        /** @var ilTemplate&Stub $template */
        $template = $this->createStub(ilTemplate::class);

        /** @var ilUserCertificateRepository&Stub $userCertificateRepository */
        $userCertificateRepository = $this->createStub(ilUserCertificateRepository::class);

        /** @var ilCtrlInterface&Stub $controller */
        $controller = $this->createStub(ilCtrlInterface::class);

        /** @var ilSetting&Stub $certificateSettings */
        $certificateSettings = $this->createStub(ilSetting::class);

        $certificateSettings->method('get')
            ->willReturn('1');

        /** @var ILIAS\UI\Factory&Stub $uiFactory */
        $uiFactory = $this->createStub(ILIAS\UI\Factory::class);

        /** @var ILIAS\UI\Renderer&Stub $uiRenderer */
        $uiRenderer = $this->createStub(ILIAS\UI\Renderer::class);

        /** @var ilCertificateUtilHelper&Stub $utilHelper */
        $utilHelper = $this->createStub(ilCertificateUtilHelper::class);

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

        $this->assertSame('Certificates', $provider->getName());
    }
}
