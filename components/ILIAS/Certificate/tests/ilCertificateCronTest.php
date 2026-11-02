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

use ILIAS\Cron\Job\Schedule\JobScheduleType;
use PHPUnit\Framework\MockObject\Stub;

class ilCertificateCronTest extends ilCertificateBaseTestCase
{
    public function testGetTitle(): void
    {
        /** @var ilCertificateQueueRepository&Stub $queueRepository */
        $queueRepository = $this->createStub(ilCertificateQueueRepository::class);

        /** @var ilCertificateTemplateRepository&Stub $templateRepository */
        $templateRepository = $this->createStub(ilCertificateTemplateRepository::class);

        /** @var ilUserCertificateRepository&Stub $userRepository */
        $userRepository = $this->createStub(ilUserCertificateRepository::class);

        /** @var ilCertificateValueReplacement&Stub $valueReplacement */
        $valueReplacement = $this->createStub(ilCertificateValueReplacement::class);

        /** @var ilLogger&Stub $logger */
        $logger = $this->createStub(ilLogger::class);

        $languageMock = $this->getMockBuilder(ilLanguage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $languageMock
            ->expects($this->atLeastOnce())
            ->method('txt')
            ->willReturn('SomeTitle');

        /** @var \ILIAS\DI\Container&Stub $dic */
        $dic = $this->createStub(\ILIAS\DI\Container::class);

        $dic->method('language')
            ->willReturn($languageMock);

        $configValues = ['lng'];

        $dic
            ->method('offsetGet')
            ->willReturnCallback(static function ($key) use ($configValues): string {
                return $configValues[$key];
            });

        $dic['lng'] = $languageMock;

        $cron = new ilCertificateCron(
            $queueRepository,
            $templateRepository,
            $userRepository,
            $valueReplacement,
            $logger,
            $dic,
            $languageMock
        );

        $title = $cron->getTitle();

        $this->assertSame('SomeTitle', $title);
    }

    public function testGetDescription(): void
    {
        /** @var ilCertificateQueueRepository&Stub $queueRepository */
        $queueRepository = $this->createStub(ilCertificateQueueRepository::class);

        /** @var ilCertificateTemplateRepository&Stub $templateRepository */
        $templateRepository = $this->createStub(ilCertificateTemplateRepository::class);

        /** @var ilUserCertificateRepository&Stub $userRepository */
        $userRepository = $this->createStub(ilUserCertificateRepository::class);

        /** @var ilCertificateValueReplacement&Stub $valueReplacement */
        $valueReplacement = $this->createStub(ilCertificateValueReplacement::class);

        /** @var ilLogger&Stub $logger */
        $logger = $this->createStub(ilLogger::class);

        $languageMock = $this->getMockBuilder(ilLanguage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $languageMock
            ->expects($this->atLeastOnce())
            ->method('txt')
            ->willReturn('SomeDescription');

        /** @var \ILIAS\DI\Container&Stub $dic */
        $dic = $this->createStub(\ILIAS\DI\Container::class);

        $dic->method('language')
            ->willReturn($languageMock);

        $configValues = ['lng'];

        $dic
            ->method('offsetGet')
            ->willReturnCallback(static function ($key) use ($configValues): string {
                return $configValues[$key];
            });

        $dic['lng'] = $languageMock;

        $cron = new ilCertificateCron(
            $queueRepository,
            $templateRepository,
            $userRepository,
            $valueReplacement,
            $logger,
            $dic,
            $languageMock
        );

        $title = $cron->getDescription();

        $this->assertSame('SomeDescription', $title);
    }

    public function testGetId(): void
    {
        /** @var ilDBInterface&Stub $database */
        $database = $this->createStub(ilDBInterface::class);

        /** @var ilCertificateQueueRepository&Stub $queueRepository */
        $queueRepository = $this->createStub(ilCertificateQueueRepository::class);

        /** @var ilCertificateTemplateRepository&Stub $templateRepository */
        $templateRepository = $this->createStub(ilCertificateTemplateRepository::class);

        /** @var ilUserCertificateRepository&Stub $userRepository */
        $userRepository = $this->createStub(ilUserCertificateRepository::class);

        /** @var ilCertificateValueReplacement&Stub $valueReplacement */
        $valueReplacement = $this->createStub(ilCertificateValueReplacement::class);

        /** @var ilLogger&Stub $logger */
        $logger = $this->createStub(ilLogger::class);

        /** @var ilLanguage&Stub $languageMock */
        $languageMock = $this->createStub(ilLanguage::class);

        /** @var \ILIAS\DI\Container&Stub $dic */
        $dic = $this->createStub(\ILIAS\DI\Container::class);

        $dic->method('language')
            ->willReturn($languageMock);

        $dic->method('database')
            ->willReturn($database);

        $configValues = ['lng'];

        $dic
            ->method('offsetGet')
            ->willReturnCallback(static function ($key) use ($configValues): string {
                return $configValues[$key];
            });

        $dic['lng'] = $languageMock;

        /** @var ilObject&Stub $objectMock */
        $objectMock = $this->createStub(ilObject::class);

        /** @var ilObjUser&Stub $userMock */
        $userMock = $this->createStub(ilObjUser::class);

        /** @var ilCertificateObjectHelper&Stub $objectHelper */
        $objectHelper = $this->createStub(ilCertificateObjectHelper::class);

        $objectHelper->method('getInstanceByObjId')
            ->willReturnOnConsecutiveCalls(
                $objectMock,
                $userMock
            );

        $cron = new ilCertificateCron(
            $queueRepository,
            $templateRepository,
            $userRepository,
            $valueReplacement,
            $logger,
            $dic,
            $languageMock,
            $objectHelper
        );

        $id = $cron->getId();

        $this->assertSame('certificate', $id);
    }

    public function testActivation(): void
    {
        /** @var ilDBInterface&Stub $database */
        $database = $this->createStub(ilDBInterface::class);

        /** @var ilCertificateQueueRepository&Stub $queueRepository */
        $queueRepository = $this->createStub(ilCertificateQueueRepository::class);

        /** @var ilCertificateTemplateRepository&Stub $templateRepository */
        $templateRepository = $this->createStub(ilCertificateTemplateRepository::class);

        /** @var ilUserCertificateRepository&Stub $userRepository */
        $userRepository = $this->createStub(ilUserCertificateRepository::class);

        /** @var ilCertificateValueReplacement&Stub $valueReplacement */
        $valueReplacement = $this->createStub(ilCertificateValueReplacement::class);

        /** @var ilLogger&Stub $logger */
        $logger = $this->createStub(ilLogger::class);

        /** @var ilLanguage&Stub $languageMock */
        $languageMock = $this->createStub(ilLanguage::class);

        /** @var \ILIAS\DI\Container&Stub $dic */
        $dic = $this->createStub(\ILIAS\DI\Container::class);

        $dic->method('language')
            ->willReturn($languageMock);

        $dic->method('database')
            ->willReturn($database);

        $configValues = ['lng'];

        $dic
            ->method('offsetGet')
            ->willReturnCallback(static function ($key) use ($configValues): string {
                return $configValues[$key];
            });

        $dic['lng'] = $languageMock;

        /** @var ilObject&Stub $objectMock */
        $objectMock = $this->createStub(ilObject::class);

        /** @var ilObjUser&Stub $userMock */
        $userMock = $this->createStub(ilObjUser::class);

        /** @var ilCertificateObjectHelper&Stub $objectHelper */
        $objectHelper = $this->createStub(ilCertificateObjectHelper::class);

        $objectHelper->method('getInstanceByObjId')
            ->willReturnOnConsecutiveCalls(
                $objectMock,
                $userMock
            );

        $cron = new ilCertificateCron(
            $queueRepository,
            $templateRepository,
            $userRepository,
            $valueReplacement,
            $logger,
            $dic,
            $languageMock,
            $objectHelper
        );

        $activation = $cron->hasAutoActivation();

        $this->assertTrue($activation);
    }

    public function testFlexibleActivation(): void
    {
        /** @var ilDBInterface&Stub $database */
        $database = $this->createStub(ilDBInterface::class);

        /** @var ilCertificateQueueRepository&Stub $queueRepository */
        $queueRepository = $this->createStub(ilCertificateQueueRepository::class);

        /** @var ilCertificateTemplateRepository&Stub $templateRepository */
        $templateRepository = $this->createStub(ilCertificateTemplateRepository::class);

        /** @var ilUserCertificateRepository&Stub $userRepository */
        $userRepository = $this->createStub(ilUserCertificateRepository::class);

        /** @var ilCertificateValueReplacement&Stub $valueReplacement */
        $valueReplacement = $this->createStub(ilCertificateValueReplacement::class);

        /** @var ilLogger&Stub $logger */
        $logger = $this->createStub(ilLogger::class);

        /** @var ilLanguage&Stub $languageMock */
        $languageMock = $this->createStub(ilLanguage::class);

        /** @var \ILIAS\DI\Container&Stub $dic */
        $dic = $this->createStub(\ILIAS\DI\Container::class);

        $dic->method('language')
            ->willReturn($languageMock);

        $dic->method('database')
            ->willReturn($database);

        $configValues = ['lng'];

        $dic
            ->method('offsetGet')
            ->willReturnCallback(static function ($key) use ($configValues): string {
                return $configValues[$key];
            });

        $dic['lng'] = $languageMock;

        /** @var ilObject&Stub $objectMock */
        $objectMock = $this->createStub(ilObject::class);

        /** @var ilObjUser&Stub $userMock */
        $userMock = $this->createStub(ilObjUser::class);

        /** @var ilCertificateObjectHelper&Stub $objectHelper */
        $objectHelper = $this->createStub(ilCertificateObjectHelper::class);

        $objectHelper->method('getInstanceByObjId')
            ->willReturnOnConsecutiveCalls(
                $objectMock,
                $userMock
            );

        $cron = new ilCertificateCron(
            $queueRepository,
            $templateRepository,
            $userRepository,
            $valueReplacement,
            $logger,
            $dic,
            $languageMock,
            $objectHelper
        );

        $flexibleSchedule = $cron->hasFlexibleSchedule();

        $this->assertTrue($flexibleSchedule);
    }

    public function testGetDefaultScheduleType(): void
    {
        /** @var ilDBInterface&Stub $database */
        $database = $this->createStub(ilDBInterface::class);

        /** @var ilCertificateQueueRepository&Stub $queueRepository */
        $queueRepository = $this->createStub(ilCertificateQueueRepository::class);

        /** @var ilCertificateTemplateRepository&Stub $templateRepository */
        $templateRepository = $this->createStub(ilCertificateTemplateRepository::class);

        /** @var ilUserCertificateRepository&Stub $userRepository */
        $userRepository = $this->createStub(ilUserCertificateRepository::class);

        /** @var ilCertificateValueReplacement&Stub $valueReplacement */
        $valueReplacement = $this->createStub(ilCertificateValueReplacement::class);

        /** @var ilLogger&Stub $logger */
        $logger = $this->createStub(ilLogger::class);

        /** @var ilLanguage&Stub $languageMock */
        $languageMock = $this->createStub(ilLanguage::class);

        /** @var \ILIAS\DI\Container&Stub $dic */
        $dic = $this->createStub(\ILIAS\DI\Container::class);

        $dic->method('language')
            ->willReturn($languageMock);

        $dic->method('database')
            ->willReturn($database);

        $configValues = ['lng'];

        $dic
            ->method('offsetGet')
            ->willReturnCallback(static function ($key) use ($configValues): string {
                return $configValues[$key];
            });

        $dic['lng'] = $languageMock;

        /** @var ilObject&Stub $objectMock */
        $objectMock = $this->createStub(ilObject::class);

        /** @var ilObjUser&Stub $userMock */
        $userMock = $this->createStub(ilObjUser::class);

        /** @var ilCertificateObjectHelper&Stub $objectHelper */
        $objectHelper = $this->createStub(ilCertificateObjectHelper::class);

        $objectHelper->method('getInstanceByObjId')
            ->willReturnOnConsecutiveCalls(
                $objectMock,
                $userMock
            );

        $cron = new ilCertificateCron(
            $queueRepository,
            $templateRepository,
            $userRepository,
            $valueReplacement,
            $logger,
            $dic,
            $languageMock,
            $objectHelper
        );

        $flexibleSchedule = $cron->getDefaultScheduleType();

        $this->assertSame(JobScheduleType::IN_MINUTES, $flexibleSchedule);
    }

    public function testGetDefaultScheduleValue(): void
    {
        /** @var ilDBInterface&Stub $database */
        $database = $this->createStub(ilDBInterface::class);

        /** @var ilCertificateQueueRepository&Stub $queueRepository */
        $queueRepository = $this->createStub(ilCertificateQueueRepository::class);

        /** @var ilCertificateTemplateRepository&Stub $templateRepository */
        $templateRepository = $this->createStub(ilCertificateTemplateRepository::class);

        /** @var ilUserCertificateRepository&Stub $userRepository */
        $userRepository = $this->createStub(ilUserCertificateRepository::class);

        /** @var ilCertificateValueReplacement&Stub $valueReplacement */
        $valueReplacement = $this->createStub(ilCertificateValueReplacement::class);

        /** @var ilLogger&Stub $logger */
        $logger = $this->createStub(ilLogger::class);

        /** @var ilLanguage&Stub $languageMock */
        $languageMock = $this->createStub(ilLanguage::class);

        /** @var \ILIAS\DI\Container&Stub $dic */
        $dic = $this->createStub(\ILIAS\DI\Container::class);

        $dic->method('language')
            ->willReturn($languageMock);

        $dic->method('database')
            ->willReturn($database);

        $configValues = ['lng'];

        $dic
            ->method('offsetGet')
            ->willReturnCallback(static function ($key) use ($configValues): string {
                return $configValues[$key];
            });

        $dic['lng'] = $languageMock;

        /** @var ilObject&Stub $objectMock */
        $objectMock = $this->createStub(ilObject::class);

        /** @var ilObjUser&Stub $userMock */
        $userMock = $this->createStub(ilObjUser::class);

        /** @var ilCertificateObjectHelper&Stub $objectHelper */
        $objectHelper = $this->createStub(ilCertificateObjectHelper::class);

        $objectHelper->method('getInstanceByObjId')
            ->willReturnOnConsecutiveCalls(
                $objectMock,
                $userMock
            );

        $cron = new ilCertificateCron(
            $queueRepository,
            $templateRepository,
            $userRepository,
            $valueReplacement,
            $logger,
            $dic,
            $languageMock,
            $objectHelper
        );

        $scheduleValue = $cron->getDefaultScheduleValue();

        $this->assertSame(1, $scheduleValue);
    }
}
