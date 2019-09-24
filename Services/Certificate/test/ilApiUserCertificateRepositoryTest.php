<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilApiUserCertificateRepositoryTest extends ilCertificateBaseTestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $database;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $logger;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $controller;

    public function setUp() : void
    {
        $this->database = $this->getMockBuilder('ilDBInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->controller = $this->getMockBuilder('ilCtrl')
                               ->disableOriginalConstructor()
                               ->getMock();

        $this->logger = $this->getMockBuilder('ilLogger')
                         ->disableOriginalConstructor()
                         ->getMock();
    }

    public function testGetUserData()
    {
        $filter = new \Certificate\API\Filter\UserDataFilter(
            array(1, 2, 3),
            'test',
            100,
            1234567890,
            2000,
            300
        );

        $this->database
            ->method('fetchAssoc')
            ->willReturnOnConsecutiveCalls(
                array(
                    'id' => 5,
                    'title' => 'test',
                    'obj_id' => 100,
                    'ref_id' => 5000,
                    'acquired_timestamp' => 1234567890,
                    'user_id' => 2000,
                    'firstname' => 'ilyas',
                    'lastname' => 'homer',
                    'login' => 'breakdanceMcFunkyPants',
                    'email' => 'ilyas@ilias.de',
                    'second_email' => 'breakdance@funky.de'
                ),
                array(
                    'id' => 5,
                    'title' => 'test',
                    'obj_id' => 100,
                    'ref_id' => 6000,
                    'acquired_timestamp' => 1234567890,
                    'user_id' => 2000,
                    'firstname' => 'ilyas',
                    'lastname' => 'homer',
                    'login' => 'breakdanceMcFunkyPants',
                    'email' => 'ilyas@ilias.de',
                    'second_email' => 'breakdance@funky.de'
                )
            );

        $this->controller->method('getLinkTargetByClass')->willReturn('somewhere.php?goto=4');

        $repository = new \Certificate\API\Repository\UserDataRepository(
            $this->database,
            $this->logger,
            $this->controller,
            'no title given'
        );

        /** @var array<int, \Certificate\API\Data\UserCertificateDto> $userData */
        $userData = $repository->getUserData($filter, array('something'));

        /** @var \Certificate\API\Data\UserCertificateDto $object */
        $object = $userData[5];
        $this->assertEquals('test', $object->getObjectTitle());
        $this->assertEquals(5, $object->getCertificateId());
        $this->assertEquals(100, $object->getObjectId());
        $this->assertEquals(array(5000, 6000), $object->getObjectRefIds());
        $this->assertEquals(1234567890, $object->getIssuedOnTimestamp());
        $this->assertEquals(2000, $object->getUserId());
        $this->assertEquals('ilyas', $object->getUserFirstName());
        $this->assertEquals('homer', $object->getUserLastName());
        $this->assertEquals('breakdanceMcFunkyPants', $object->getUserLogin());
        $this->assertEquals('ilyas@ilias.de', $object->getUserEmail());
        $this->assertEquals('breakdance@funky.de', $object->getUserSecondEmail());
        $this->assertEquals('somewhere.php?goto=4', $object->getDownloadLink());
    }
}
