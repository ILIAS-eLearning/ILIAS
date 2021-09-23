<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateCronTest extends ilCertificateBaseTestCase
{
    public function testGetTitle()
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

        $configValues = array('lng');

        $dic->expects($this->any())
            ->method('offsetGet')
            ->will($this->returnCallback(
                function ($key) use ($configValues) {
                    return $configValues[$key];
                }
            ));

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

        $this->assertEquals('SomeTitle', $title);
    }

    public function testGetDescription()
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

        $dic->expects($this->any())
            ->method('offsetGet')
            ->will($this->returnCallback(
                function ($key) use ($configValues) {
                    return $configValues[$key];
                }
            ));

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

        $this->assertEquals('SomeDescription', $title);
    }

    public function testGetId()
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

        $configValues = array('lng');

        $dic->expects($this->any())
            ->method('offsetGet')
            ->will($this->returnCallback(
                function ($key) use ($configValues) {
                    return $configValues[$key];
                }
            ));

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

        $this->assertEquals('certificate', $id);
    }

    public function testActivation()
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

        $configValues = array('lng');

        $dic->expects($this->any())
            ->method('offsetGet')
            ->will($this->returnCallback(
                function ($key) use ($configValues) {
                    return $configValues[$key];
                }
            ));

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

    public function testFlexibleActivation()
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

        $configValues = array('lng');

        $dic->expects($this->any())
            ->method('offsetGet')
            ->will($this->returnCallback(
                function ($key) use ($configValues) {
                    return $configValues[$key];
                }
            ));

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

    public function testGetDefaultScheduleType()
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

        $configValues = array('lng');

        $dic->expects($this->any())
            ->method('offsetGet')
            ->will($this->returnCallback(
                function ($key) use ($configValues) {
                    return $configValues[$key];
                }
            ));

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

        $this->assertEquals(2, $flexibleSchedule);
    }

    public function testGetDefaultScheduleValue()
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

        $configValues = array('lng');

        $dic->expects($this->any())
            ->method('offsetGet')
            ->will($this->returnCallback(
                function ($key) use ($configValues) {
                    return $configValues[$key];
                }
            ));

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

        $this->assertEquals(1, $scheduleValue);
    }
}
