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

use PHPUnit\Framework\MockObject\Stub;

class ilCertificateUserForObjectPreloaderTest extends ilCertificateBaseTestCase
{
    public function testUsersWithCertifcatesWillBePreoloaded(): void
    {
        /** @var ilUserCertificateRepository&Stub $userCertificateRepository */
        $userCertificateRepository = $this->createStub(ilUserCertificateRepository::class);

        $userCertificateRepository->method('fetchUserIdsWithCertificateForObject')
            ->willReturn([1, 2, 3]);

        /** @var ilCertificateActiveValidator&Stub $activeValidator */
        $activeValidator = $this->createStub(ilCertificateActiveValidator::class);

        $activeValidator->method('validate')
            ->willReturn(true);

        $preloader = new ilCertificateUserForObjectPreloader($userCertificateRepository, $activeValidator);

        $preloader->preLoadDownloadableCertificates(100);

        $result = $preloader->isPreloaded(100, 1);

        $this->assertTrue($result);
    }

    public function testUserWithCertificateIsNotPreloaded(): void
    {
        /** @var ilUserCertificateRepository&Stub $userCertificateRepository */
        $userCertificateRepository = $this->createStub(ilUserCertificateRepository::class);

        $userCertificateRepository->method('fetchUserIdsWithCertificateForObject')
            ->willReturn([1, 2, 3]);

        /** @var ilCertificateActiveValidator&Stub $activeValidator */
        $activeValidator = $this->createStub(ilCertificateActiveValidator::class);

        $activeValidator->method('validate')
            ->willReturn(true);

        $preloader = new ilCertificateUserForObjectPreloader($userCertificateRepository, $activeValidator);

        $preloader->preLoadDownloadableCertificates(100);

        $result = $preloader->isPreloaded(100, 5);

        $this->assertFalse($result);
    }

    public function testUserIsNoProloaded(): void
    {
        /** @var ilUserCertificateRepository&Stub $userCertificateRepository */
        $userCertificateRepository = $this->createStub(ilUserCertificateRepository::class);

        $userCertificateRepository->method('fetchUserIdsWithCertificateForObject')
            ->willReturn([1, 2, 3]);

        /** @var ilCertificateActiveValidator&Stub $activeValidator */
        $activeValidator = $this->createStub(ilCertificateActiveValidator::class);

        $activeValidator->method('validate')
            ->willReturn(true);

        $preloader = new ilCertificateUserForObjectPreloader($userCertificateRepository, $activeValidator);

        $preloader->preLoadDownloadableCertificates(100);

        $result = $preloader->isPreloaded(200, 1);

        $this->assertFalse($result);
    }

    public function testWontPreloadBecauseCertificatesAreCurrentlyInActive(): void
    {
        $userCertificateRepository = $this->getMockBuilder(ilUserCertificateRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $userCertificateRepository
            ->expects($this->never())
            ->method('fetchUserIdsWithCertificateForObject');

        /** @var ilCertificateActiveValidator&Stub $activeValidator */
        $activeValidator = $this->createStub(ilCertificateActiveValidator::class);

        $activeValidator->method('validate')
            ->willReturn(false);

        $preloader = new ilCertificateUserForObjectPreloader($userCertificateRepository, $activeValidator);

        $preloader->preLoadDownloadableCertificates(100);

        $result = $preloader->isPreloaded(100, 5);

        $this->assertFalse($result);
    }
}
