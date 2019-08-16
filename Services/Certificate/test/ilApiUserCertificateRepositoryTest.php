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

    public function setUp() : void
    {
        $this->database = $this->getMockBuilder('ilDB')
            ->disableOriginalConstructor()
            ->addMethods(array('fetchAssoc'))
            ->getMock();

        $this->logger = $this->getMockBuilder('ilLogger')
                         ->disableOriginalConstructor()
                         ->getMock();
    }

    public function testGetUserData()
    {
        $filter = new \Certificate\API\Filter\UserCertificateFilter(
            'test',
            100,
            1234567890,
            2000,
            300
        );

        $this->database
            ->method('fetchAssoc')
            ->willReturn(
                array(
                    'title' => 'test',
                    'obj_id' => 100,
                    'acquired_timestamp' => 1234567890,
                    'user_id' => 2000,
                    'ref_ids' => 300
                )
            );

        $repository = new \Certificate\API\Repository\ilUserDataRepository(
            $this->database,
            $this->logger,
            'no title given'
        );



        /** @var array<int, \Certificate\API\Data\ilUserCertificateData> $userData */
        $userData = $repository->getUserData(array(1, 2, 3), $filter);

        $this->assertEquals('test', $userData[2000]->getObjectTitle());
    }
}
