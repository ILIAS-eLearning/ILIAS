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

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class UserCertificateAPITest extends ilCertificateBaseTestCase
{
    public function testUserDataCall(): void
    {
        $repository = $this->getMockBuilder(UserDataRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger = new \ILIAS\Services\Logging\NullLogger();
        $database = $this->createMock(ilDBInterface::class);

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
            $this->createMock(ilCertificateTemplateRepository::class),
            new ilCertificateQueueRepository(
                $database,
                $logger
            ),
            new ilCertificateTypeClassMap(),
            $logger,
            $this->getMockBuilder(ilObjectDataCache::class)->disableOriginalConstructor()->getMock()
        );

        $result = $api->getUserCertificateData(new \ILIAS\Certificate\API\Filter\UserDataFilter(), []);

        $this->assertSame(['5' => $userData], $result);
    }
}
