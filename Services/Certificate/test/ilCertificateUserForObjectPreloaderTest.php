<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateUserForObjectPreloaderTest extends ilCertificateBaseTestCase
{
    public function testUsersWithCertifcatesWillBePreoloaded() : void
    {
        $userCertificateRepository = $this->getMockBuilder(ilUserCertificateRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $userCertificateRepository->method('fetchUserIdsWithCertificateForObject')
            ->willReturn(array(1, 2, 3));

        $activeValidator = $this->getMockBuilder(ilCertificateActiveValidator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $activeValidator->method('validate')
            ->willReturn(true);

        $preloader = new ilCertificateUserForObjectPreloader($userCertificateRepository, $activeValidator);

        $preloader->preLoadDownloadableCertificates(100);

        $result = $preloader->isPreloaded(100, 1);

        $this->assertTrue($result);
    }

    public function testUserWithCertificateIsNotPreloaded() : void
    {
        $userCertificateRepository = $this->getMockBuilder(ilUserCertificateRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $userCertificateRepository->method('fetchUserIdsWithCertificateForObject')
            ->willReturn(array(1, 2, 3));

        $activeValidator = $this->getMockBuilder(ilCertificateActiveValidator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $activeValidator->method('validate')
            ->willReturn(true);

        $preloader = new ilCertificateUserForObjectPreloader($userCertificateRepository, $activeValidator);

        $preloader->preLoadDownloadableCertificates(100);

        $result = $preloader->isPreloaded(100, 5);

        $this->assertFalse($result);
    }

    public function testUserIsNoProloaded() : void
    {
        $userCertificateRepository = $this->getMockBuilder(ilUserCertificateRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $userCertificateRepository->method('fetchUserIdsWithCertificateForObject')
            ->willReturn(array(1, 2, 3));

        $activeValidator = $this->getMockBuilder(ilCertificateActiveValidator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $activeValidator->method('validate')
            ->willReturn(true);

        $preloader = new ilCertificateUserForObjectPreloader($userCertificateRepository, $activeValidator);

        $preloader->preLoadDownloadableCertificates(100);

        $result = $preloader->isPreloaded(200, 1);

        $this->assertFalse($result);
    }

    public function testWontPreloadBecauseCertificatesAreCurrentlyInActive() : void
    {
        $userCertificateRepository = $this->getMockBuilder(ilUserCertificateRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $userCertificateRepository
            ->expects($this->never())
            ->method('fetchUserIdsWithCertificateForObject');

        $activeValidator = $this->getMockBuilder(ilCertificateActiveValidator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $activeValidator->method('validate')
            ->willReturn(false);

        $preloader = new ilCertificateUserForObjectPreloader($userCertificateRepository, $activeValidator);

        $preloader->preLoadDownloadableCertificates(100);

        $result = $preloader->isPreloaded(100, 5);

        $this->assertFalse($result);
    }
}
