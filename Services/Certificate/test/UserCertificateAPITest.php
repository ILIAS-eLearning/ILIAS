<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

use Certificate\API\Repository\UserDataRepository;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class UserCertificateAPITest extends ilCertificateBaseTestCase
{
    public function testUserDataCall()
    {
        $repository = $this->getMockBuilder(UserDataRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $userData = new \Certificate\API\Data\UserCertificateDto(
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
            array(3000)
        );

        $repository->method('getUserData')
                   ->willReturn(array(5 => $userData));

        $api = new \Certificate\API\UserCertificateAPI($repository);

        $result = $api->getUserCertificateData(new \Certificate\API\Filter\UserDataFilter(array(20, 10 , 11)), array());

        $this->assertEquals(array('5' => $userData), $result);
    }
}
