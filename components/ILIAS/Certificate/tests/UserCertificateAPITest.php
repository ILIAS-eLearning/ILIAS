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

use ILIAS\Certificate\API\Repository\UserDataRepository;
use PHPUnit\Framework\MockObject\Stub;

class UserCertificateAPITest extends ilCertificateBaseTestCase
{
    public function testUserDataCall(): void
    {
        /** @var UserDataRepository&Stub $repository */
        $repository = $this->createStub(UserDataRepository::class);
        $logger = new \ILIAS\components\Logging\NullLogger();
        /** @var ilDBInterface&Stub $database */
        $database = $this->createStub(ilDBInterface::class);

        $userData = new \ILIAS\Certificate\API\Data\UserCertificateDto(
            5,
            'Some Title',
            100,
            1_234_567_890,
            20,
            'Ilyas',
            'Homer',
            'breakdanceMcFunkyPants',
            'iliyas@ilias.de',
            'breakdance@funky.de',
            [3000]
        );

        $repository->method('getUserData')
                   ->willReturn([5 => $userData]);

        $api = new \ILIAS\Certificate\API\UserCertificateAPI(
            $repository,
            $this->createStub(ilCertificateTemplateRepository::class),
            new ilCertificateQueueRepository(
                $database,
                $logger
            ),
            new ilCertificateTypeClassMap(),
            $logger,
            $this->createStub(ilObjectDataCache::class)
        );

        $result = $api->getUserCertificateData(new \ILIAS\Certificate\API\Filter\UserDataFilter(), []);

        $this->assertSame(['5' => $userData], $result);
    }
}
