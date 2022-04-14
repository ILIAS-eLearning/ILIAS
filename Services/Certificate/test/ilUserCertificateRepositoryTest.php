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

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilUserCertificateRepositoryTest extends ilCertificateBaseTestCase
{
    public function testSaveOfUserCertificateToDatabase() : void
    {
        $database = $this->getMockBuilder(ilDBInterface::class)
            ->getMock();

        $database->method('nextId')
            ->willReturn(141);

        $database->method('insert')->with(
            'il_cert_user_cert',
            array(
                'id' => array('integer', 141),
                'pattern_certificate_id' => array('integer', 1),
                'obj_id' => array('integer', 20),
                'obj_type' => array('text',  'crs'),
                'user_id' => array('integer', 400),
                'user_name' => array('text', 'Niels Theen'),
                'acquired_timestamp' => array('integer', 123456789),
                'certificate_content' => array('clob', '<xml>Some Content</xml>'),
                'template_values' => array('clob', '[]'),
                'valid_until' => array('integer', null),
                'version' => array('integer', 1),
                'ilias_version' => array('text', 'v5.4.0'),
                'currently_active' => array('integer', true),
                'background_image_path' => array('text', '/some/where/background.jpg'),
                'thumbnail_image_path' => array('text', '/some/where/thumbnail.svg'),
            )
        );

        $logger = $this->getMockBuilder(ilLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $logger->expects($this->atLeastOnce())
            ->method('info');

        $repository = new ilUserCertificateRepository(
            $database,
            $logger,
            'someDefaultTitle'
        );

        $userCertificate = new ilUserCertificate(
            1,
            20,
            'crs',
            400,
            'Niels Theen',
            123456789,
            '<xml>Some Content</xml>',
            '[]',
            null,
            1,
            'v5.4.0',
            true,
            '/some/where/background.jpg',
            '/some/where/thumbnail.svg'
        );

        $repository->save($userCertificate);
    }

    public function testFetchAllActiveCertificateForUser() : void
    {
        $database = $this->getMockBuilder(ilDBInterface::class)
            ->getMock();

        $database->method('nextId')
            ->willReturn(141);

        $database->method('fetchAssoc')->willReturnOnConsecutiveCalls(
            array(
                'id' => 141,
                'pattern_certificate_id' => 1,
                'obj_id' => 20,
                'obj_type' => 'crs',
                'user_id' => 400,
                'user_name' => 'Niels Theen',
                'acquired_timestamp' => 123456789,
                'certificate_content' => '<xml>Some Content</xml>',
                'template_values' => '[]',
                'valid_until' => null,
                'version' => 1,
                'ilias_version' => 'v5.4.0',
                'currently_active' => true,
                'background_image_path' => '/some/where/background.jpg',
                'thumbnail_image_path' => '/some/where/thumbnail.svg',
                'title' => 'Some Title'
            ),
            array(
                'id' => 142,
                'pattern_certificate_id' => 5,
                'obj_id' => 3123,
                'obj_type' => 'tst',
                'user_id' => 400,
                'user_name' => 'Niels Theen',
                'acquired_timestamp' => 987654321,
                'certificate_content' => '<xml>Some Other Content</xml>',
                'template_values' => '[]',
                'valid_until' => null,
                'version' => 1,
                'ilias_version' => 'v5.3.0',
                'currently_active' => true,
                'background_image_path' => '/some/where/else/background.jpg',
                'thumbnail_image_path' => '/some/where/thumbnail.svg',
                'title' => 'Someother Title'
            )
        );

        $logger = $this->getMockBuilder(ilLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $logger->expects($this->atLeastOnce())
            ->method('info');

        $repository = new ilUserCertificateRepository(
            $database,
            $logger,
            'someDefaultTitle'
        );

        $results = $repository->fetchActiveCertificates(400);

        $this->assertSame(141, $results[0]->getUserCertificate()->getId());
        $this->assertSame(142, $results[1]->getUserCertificate()->getId());
    }

    public function testFetchActiveCertificateForUserObjectCombination() : void
    {
        $database = $this->getMockBuilder(ilDBInterface::class)
            ->getMock();

        $database->method('nextId')
            ->willReturn(141);

        $database->method('fetchAssoc')->willReturnOnConsecutiveCalls(
            array(
                'id' => 141,
                'pattern_certificate_id' => 1,
                'obj_id' => 20,
                'obj_type' => 'crs',
                'user_id' => 400,
                'user_name' => 'Niels Theen',
                'acquired_timestamp' => 123456789,
                'certificate_content' => '<xml>Some Content</xml>',
                'template_values' => '[]',
                'valid_until' => null,
                'version' => 1,
                'ilias_version' => 'v5.4.0',
                'currently_active' => true,
                'background_image_path' => '/some/where/background.jpg',
                'thumbnail_image_path' => '/some/where/thumbnail.svg',
            ),
            array(
                'id' => 142,
                'pattern_certificate_id' => 5,
                'obj_id' => 20,
                'obj_type' => 'tst',
                'user_id' => 400,
                'user_name' => 'Niels Theen',
                'acquired_timestamp' => 987654321,
                'certificate_content' => '<xml>Some Other Content</xml>',
                'template_values' => '[]',
                'valid_until' => null,
                'version' => 1,
                'ilias_version' => 'v5.3.0',
                'currently_active' => true,
                'background_image_path' => '/some/where/else/background.jpg',
                'thumbnail_image_path' => '/some/where/thumbnail.svg',
            )
        );

        $logger = $this->getMockBuilder(ilLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $logger->expects($this->atLeastOnce())
            ->method('info');

        $repository = new ilUserCertificateRepository(
            $database,
            $logger,
            'someDefaultTitle'
        );

        $result = $repository->fetchActiveCertificate(400, 20);

        $this->assertSame(141, $result->getId());
    }

    /**
     *
     */
    public function testFetchNoActiveCertificateLeadsToException() : void
    {
        $this->expectException(\ilException::class);

        $database = $this->getMockBuilder(ilDBInterface::class)
            ->getMock();

        $database->method('nextId')
            ->willReturn(141);

        $database->method('fetchAssoc')->willReturn(array());

        $logger = $this->getMockBuilder(ilLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $logger->expects($this->atLeastOnce())
            ->method('info');

        $repository = new ilUserCertificateRepository($database, $logger, 'someDefaultTitle');

        $repository->fetchActiveCertificate(400, 20);

        $this->fail('Should never happen. Certificate Found?');
    }

    public function testFetchActiveCertificatesByType() : void
    {
        $database = $this->getMockBuilder(ilDBInterface::class)
            ->getMock();

        $database->method('nextId')
            ->willReturn(141);

        $database->method('fetchAssoc')->willReturnOnConsecutiveCalls(
            array(
                'id' => 141,
                'pattern_certificate_id' => 1,
                'obj_id' => 20,
                'obj_type' => 'crs',
                'user_id' => 400,
                'user_name' => 'Niels Theen',
                'acquired_timestamp' => 123456789,
                'certificate_content' => '<xml>Some Content</xml>',
                'template_values' => '[]',
                'valid_until' => null,
                'version' => 1,
                'ilias_version' => 'v5.4.0',
                'currently_active' => true,
                'background_image_path' => '/some/where/background.jpg',
                'thumbnail_image_path' => '/some/where/else/thumbnail.svg',
                'title' => 'SomeTitle',
                'someDescription' => 'SomeDescription'
            ),
            array(
                'id' => 142,
                'pattern_certificate_id' => 5,
                'obj_id' => 20,
                'obj_type' => 'crs',
                'user_id' => 400,
                'user_name' => 'Niels Theen',
                'acquired_timestamp' => 987654321,
                'certificate_content' => '<xml>Some Other Content</xml>',
                'template_values' => '[]',
                'valid_until' => null,
                'version' => 1,
                'ilias_version' => 'v5.3.0',
                'currently_active' => true,
                'background_image_path' => '/some/where/else/background.jpg',
                'thumbnail_image_path' => '/some/where/else/thumbnail.svg',
                'title' => 'SomeTitle',
                'someDescription' => 'SomeDescription'
            )
        );

        $logger = $this->getMockBuilder(ilLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $logger->expects($this->atLeastOnce())
            ->method('info');

        $repository = new ilUserCertificateRepository($database, $logger, 'someDefaultTitle');

        $results = $repository->fetchActiveCertificatesByTypeForPresentation(400, 'crs');

        $this->assertSame(141, $results[0]->getUserCertificate()->getId());
        $this->assertSame(142, $results[1]->getUserCertificate()->getId());
    }

    public function testFetchCertificate() : void
    {
        $database = $this->getMockBuilder(ilDBInterface::class)
            ->getMock();

        $database->method('nextId')
            ->willReturn(141);

        $database->method('fetchAssoc')->willReturn(
            array(
                'id' => 141,
                'pattern_certificate_id' => 1,
                'obj_id' => 20,
                'obj_type' => 'crs',
                'user_id' => 400,
                'user_name' => 'Niels Theen',
                'acquired_timestamp' => 123456789,
                'certificate_content' => '<xml>Some Content</xml>',
                'template_values' => '[]',
                'valid_until' => null,
                'version' => 1,
                'ilias_version' => 'v5.4.0',
                'currently_active' => true,
                'background_image_path' => '/some/where/background.jpg',
                'thumbnail_image_path' => '/some/where/else/thumbnail.svg',
                'title' => 'SomeTitle',
                'someDescription' => 'SomeDescription'
            )
        );

        $logger = $this->getMockBuilder(ilLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $logger->expects($this->atLeastOnce())
            ->method('info');

        $repository = new ilUserCertificateRepository($database, $logger, 'someTitle');

        $result = $repository->fetchCertificate(141);

        $this->assertSame(141, $result->getId());
    }

    public function testNoCertificateInFetchtCertificateLeadsToException() : void
    {
        $this->expectException(\ilException::class);

        $database = $this->getMockBuilder(ilDBInterface::class)
            ->getMock();

        $database->method('nextId')
            ->willReturn(141);

        $database->method('fetchAssoc')
            ->willReturn(array());

        $logger = $this->getMockBuilder(ilLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $logger->expects($this->atLeastOnce())
            ->method('info');

        $repository = new ilUserCertificateRepository($database, $logger, 'someTitle');

        $repository->fetchCertificate(141);

        $this->fail('Should never happen. Certificate Found?');
    }

    public function testFetchObjectWithCertificateForUser() : void
    {
        $database = $this->getMockBuilder(ilDBInterface::class)
            ->getMock();

        $database
            ->expects($this->once())
            ->method('query');

        $database
            ->expects($this->once())
            ->method('in');

        $database
            ->expects($this->exactly(3))
            ->method('fetchAssoc')
            ->willReturnOnConsecutiveCalls(
                array('obj_id' => 100),
                array('obj_id' => 300),
                array()
            );

        $database->method('fetchAssoc')
            ->willReturn(array());

        $logger = $this->getMockBuilder(ilLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $logger->expects($this->atLeastOnce())
            ->method('info');

        $repository = new ilUserCertificateRepository($database, $logger, 'someTitle');

        $userId = 10;
        $objectIds = array(200, 300, 400);

        $results = $repository->fetchObjectIdsWithCertificateForUser($userId, $objectIds);

        $this->assertSame(array(100, 300), $results);
    }

    public function testFetchUserIdsWithCertificateForObject() : void
    {
        $database = $this->getMockBuilder(ilDBInterface::class)
            ->getMock();

        $database
            ->expects($this->once())
            ->method('query');

        $database
            ->expects($this->exactly(3))
            ->method('fetchAssoc')
            ->willReturnOnConsecutiveCalls(
                array('user_id' => 100),
                array('user_id' => 300),
                array()
            );

        $database->method('fetchAssoc')
            ->willReturn(array());

        $logger = $this->getMockBuilder(ilLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $logger->expects($this->atLeastOnce())
            ->method('info');

        $repository = new ilUserCertificateRepository($database, $logger, 'someTitle');

        $objectId = 10;

        $results = $repository->fetchUserIdsWithCertificateForObject($objectId);

        $this->assertSame(array(100, 300), $results);
    }
}
