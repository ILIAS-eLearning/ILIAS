<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateObjectsForUserPreloaderTest extends ilCertificateBaseTestCase
{
    public function testUsersWithCertifcatesWillBePreoloaded() : void
    {
        $userCertificateRepository = $this->getMockBuilder(ilUserCertificateRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $userCertificateRepository->method('fetchObjectIdsWithCertificateForUser')
            ->willReturn(array(1, 2, 3));

        $preloader = new ilCertificateObjectsForUserPreloader($userCertificateRepository);

        $preloader->preLoad(100, array(500, 200));

        $result = $preloader->isPreloaded(100, 1);

        $this->assertTrue($result);
    }

    public function testUserWithCertificateIsNotPreloaded() : void
    {
        $userCertificateRepository = $this->getMockBuilder(ilUserCertificateRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $userCertificateRepository->method('fetchObjectIdsWithCertificateForUser')
            ->willReturn(array(1, 2, 3));

        $preloader = new ilCertificateObjectsForUserPreloader($userCertificateRepository);

        $preloader->preLoad(100, array(500, 200));

        $result = $preloader->isPreloaded(100, 5);

        $this->assertFalse($result);
    }

    public function testUserIsNoProloaded() : void
    {
        $userCertificateRepository = $this->getMockBuilder(ilUserCertificateRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $userCertificateRepository->method('fetchObjectIdsWithCertificateForUser')
            ->willReturn(array(1, 2, 3));

        $preloader = new ilCertificateObjectsForUserPreloader($userCertificateRepository);

        $preloader->preLoad(100, array(500, 200));

        $result = $preloader->isPreloaded(200, 1);

        $this->assertFalse($result);
    }
}
