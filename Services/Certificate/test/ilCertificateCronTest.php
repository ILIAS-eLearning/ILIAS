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
class ilCertificateCronTest extends ilCertificateBaseTestCase
{
    public function testGetTitle(): void
    {
        $queueRepository = $this->getMockBuilder(ilCertificateQueueRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $templateRepository = $this->getMockBuilder(ilCertificateTemplateRepository::class)->getMock();

        $userRepository = $this->getMockBuilder(ilUserCertificateRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $valueReplacement = $this->getMockBuilder(ilCertificateValueReplacement::class)
            ->disableOriginalConstructor()
            ->getMock();

        $logger = $this->getMockBuilder(ilLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $languageMock = $this->getMockBuilder(ilLanguage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $languageMock
            ->expects($this->atLeastOnce())
            ->method('txt')
            ->willReturn('SomeTitle');

        $dic = $this->getMockBuilder(\ILIAS\DI\Container::class)
            ->disableOriginalConstructor()
            ->getMock();

        $dic->method('language')
            ->willReturn($languageMock);

        $configValues = ['lng'];

        $dic
            ->method('offsetGet')
            ->willReturnCallback(static function ($key) use ($configValues) {
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
        $queueRepository = $this->getMockBuilder(ilCertificateQueueRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $templateRepository = $this->getMockBuilder(ilCertificateTemplateRepository::class)->getMock();

        $userRepository = $this->getMockBuilder(ilUserCertificateRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $valueReplacement = $this->getMockBuilder(ilCertificateValueReplacement::class)
            ->disableOriginalConstructor()
            ->getMock();

        $logger = $this->getMockBuilder(ilLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $languageMock = $this->getMockBuilder(ilLanguage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $languageMock
            ->expects($this->atLeastOnce())
            ->method('txt')
            ->willReturn('SomeDescription');

        $dic = $this->getMockBuilder(\ILIAS\DI\Container::class)
            ->disableOriginalConstructor()
            ->getMock();

        $dic->method('language')
            ->willReturn($languageMock);

        $configValues = ['lng'];

        $dic
            ->method('offsetGet')
            ->willReturnCallback(static function ($key) use ($configValues) {
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
        $database = $this->createMock(ilDBInterface::class);

        $queueRepository = $this->getMockBuilder(ilCertificateQueueRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $templateRepository = $this->getMockBuilder(ilCertificateTemplateRepository::class)->getMock();

        $userRepository = $this->getMockBuilder(ilUserCertificateRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $valueReplacement = $this->getMockBuilder(ilCertificateValueReplacement::class)
            ->disableOriginalConstructor()
            ->getMock();

        $logger = $this->getMockBuilder(ilLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $languageMock = $this->getMockBuilder(ilLanguage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $dic = $this->getMockBuilder(\ILIAS\DI\Container::class)
            ->disableOriginalConstructor()
            ->getMock();

        $dic->method('language')
            ->willReturn($languageMock);

        $dic->method('database')
            ->willReturn($database);

        $configValues = ['lng'];

        $dic
            ->method('offsetGet')
            ->willReturnCallback(static function ($key) use ($configValues) {
                return $configValues[$key];
            });

        $dic['lng'] = $languageMock;

        $objectMock = $this->getMockBuilder(ilObject::class)
            ->disableOriginalConstructor()
            ->getMock();

        $userMock = $this->getMockBuilder(ilObjUser::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectHelper = $this->getMockBuilder(ilCertificateObjectHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

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
        $database = $this->getMockBuilder(ilDBInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $queueRepository = $this->getMockBuilder(ilCertificateQueueRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $templateRepository = $this->getMockBuilder(ilCertificateTemplateRepository::class)->getMock();

        $userRepository = $this->getMockBuilder(ilUserCertificateRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $valueReplacement = $this->getMockBuilder(ilCertificateValueReplacement::class)
            ->disableOriginalConstructor()
            ->getMock();

        $logger = $this->getMockBuilder(ilLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $languageMock = $this->getMockBuilder(ilLanguage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $dic = $this->getMockBuilder(\ILIAS\DI\Container::class)
            ->disableOriginalConstructor()
            ->getMock();

        $dic->method('language')
            ->willReturn($languageMock);

        $dic->method('database')
            ->willReturn($database);

        $configValues = ['lng'];

        $dic
            ->method('offsetGet')
            ->willReturnCallback(static function ($key) use ($configValues) {
                return $configValues[$key];
            });

        $dic['lng'] = $languageMock;

        $objectMock = $this->getMockBuilder(ilObject::class)
            ->disableOriginalConstructor()
            ->getMock();

        $userMock = $this->getMockBuilder(ilObjUser::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectHelper = $this->getMockBuilder(ilCertificateObjectHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

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
        $database = $this->getMockBuilder(ilDBInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $queueRepository = $this->getMockBuilder(ilCertificateQueueRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $templateRepository = $this->getMockBuilder(ilCertificateTemplateRepository::class)->getMock();

        $userRepository = $this->getMockBuilder(ilUserCertificateRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $valueReplacement = $this->getMockBuilder(ilCertificateValueReplacement::class)
            ->disableOriginalConstructor()
            ->getMock();

        $logger = $this->getMockBuilder(ilLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $languageMock = $this->getMockBuilder(ilLanguage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $dic = $this->getMockBuilder(\ILIAS\DI\Container::class)
            ->disableOriginalConstructor()
            ->getMock();

        $dic->method('language')
            ->willReturn($languageMock);

        $dic->method('database')
            ->willReturn($database);

        $configValues = ['lng'];

        $dic
            ->method('offsetGet')
            ->willReturnCallback(static function ($key) use ($configValues) {
                return $configValues[$key];
            });

        $dic['lng'] = $languageMock;

        $objectMock = $this->getMockBuilder(ilObject::class)
            ->disableOriginalConstructor()
            ->getMock();

        $userMock = $this->getMockBuilder(ilObjUser::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectHelper = $this->getMockBuilder(ilCertificateObjectHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

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
        $database = $this->getMockBuilder(ilDBInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $queueRepository = $this->getMockBuilder(ilCertificateQueueRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $templateRepository = $this->getMockBuilder(ilCertificateTemplateRepository::class)->getMock();

        $userRepository = $this->getMockBuilder(ilUserCertificateRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $valueReplacement = $this->getMockBuilder(ilCertificateValueReplacement::class)
            ->disableOriginalConstructor()
            ->getMock();

        $logger = $this->getMockBuilder(ilLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $languageMock = $this->getMockBuilder(ilLanguage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $dic = $this->getMockBuilder(\ILIAS\DI\Container::class)
            ->disableOriginalConstructor()
            ->getMock();

        $dic->method('language')
            ->willReturn($languageMock);

        $dic->method('database')
            ->willReturn($database);

        $configValues = ['lng'];

        $dic
            ->method('offsetGet')
            ->willReturnCallback(static function ($key) use ($configValues) {
                return $configValues[$key];
            });

        $dic['lng'] = $languageMock;

        $objectMock = $this->getMockBuilder(ilObject::class)
            ->disableOriginalConstructor()
            ->getMock();

        $userMock = $this->getMockBuilder(ilObjUser::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectHelper = $this->getMockBuilder(ilCertificateObjectHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

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

        $this->assertSame(2, $flexibleSchedule);
    }

    public function testGetDefaultScheduleValue(): void
    {
        $database = $this->getMockBuilder(ilDBInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $queueRepository = $this->getMockBuilder(ilCertificateQueueRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $templateRepository = $this->getMockBuilder(ilCertificateTemplateRepository::class)->getMock();

        $userRepository = $this->getMockBuilder(ilUserCertificateRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $valueReplacement = $this->getMockBuilder(ilCertificateValueReplacement::class)
            ->disableOriginalConstructor()
            ->getMock();

        $logger = $this->getMockBuilder(ilLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $languageMock = $this->getMockBuilder(ilLanguage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $dic = $this->getMockBuilder(\ILIAS\DI\Container::class)
            ->disableOriginalConstructor()
            ->getMock();

        $dic->method('language')
            ->willReturn($languageMock);

        $dic->method('database')
            ->willReturn($database);

        $configValues = ['lng'];

        $dic
            ->method('offsetGet')
            ->willReturnCallback(static function ($key) use ($configValues) {
                return $configValues[$key];
            });

        $dic['lng'] = $languageMock;

        $objectMock = $this->getMockBuilder(ilObject::class)
            ->disableOriginalConstructor()
            ->getMock();

        $userMock = $this->getMockBuilder(ilObjUser::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectHelper = $this->getMockBuilder(ilCertificateObjectHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

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
