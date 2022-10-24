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
class ilCertificateObjectsForUserPreloaderTest extends ilCertificateBaseTestCase
{
    public function testUsersWithCertifcatesWillBePreoloaded(): void
    {
        $userCertificateRepository = $this->getMockBuilder(ilUserCertificateRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $userCertificateRepository->method('fetchObjectIdsWithCertificateForUser')
            ->willReturn([1, 2, 3]);

        $preloader = new ilCertificateObjectsForUserPreloader($userCertificateRepository);

        $preloader->preLoad(100, [500, 200]);

        $result = $preloader->isPreloaded(100, 1);

        $this->assertTrue($result);
    }

    public function testUserWithCertificateIsNotPreloaded(): void
    {
        $userCertificateRepository = $this->getMockBuilder(ilUserCertificateRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $userCertificateRepository->method('fetchObjectIdsWithCertificateForUser')
            ->willReturn([1, 2, 3]);

        $preloader = new ilCertificateObjectsForUserPreloader($userCertificateRepository);

        $preloader->preLoad(100, [500, 200]);

        $result = $preloader->isPreloaded(100, 5);

        $this->assertFalse($result);
    }

    public function testUserIsNoProloaded(): void
    {
        $userCertificateRepository = $this->getMockBuilder(ilUserCertificateRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $userCertificateRepository->method('fetchObjectIdsWithCertificateForUser')
            ->willReturn([1, 2, 3]);

        $preloader = new ilCertificateObjectsForUserPreloader($userCertificateRepository);

        $preloader->preLoad(100, [500, 200]);

        $result = $preloader->isPreloaded(200, 1);

        $this->assertFalse($result);
    }
}
