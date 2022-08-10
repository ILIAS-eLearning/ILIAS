<?php declare(strict_types=1);

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

use ILIAS\Certificate\API\Repository\UserDataRepository;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class UserCertificateAPITest extends ilCertificateBaseTestCase
{
    public function testUserDataCall() : void
    {
        $repository = $this->getMockBuilder(UserDataRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $userData = new \ILIAS\Certificate\API\Data\UserCertificateDto(
            5,
            'Some Title',
            100,
            1234567890,
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

        $api = new \ILIAS\Certificate\API\UserCertificateAPI($repository);

        $result = $api->getUserCertificateData(new \ILIAS\Certificate\API\Filter\UserDataFilter(), []);

        $this->assertSame(['5' => $userData], $result);
    }
}
