<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilUserCertificateRepositoryTest extends \PHPUnit_Framework_TestCase
{
	public function testSaveOfUserCertificateToDatabase()
	{
		$database = $this->getMockBuilder('ilDBInterface')
			->getMock();

		$database->method('nextId')
			->willReturn(141);

		$database->method('insert')->with(
			'il_cert_user_cert',
			array(
				'id'                     => array('integer', 141),
				'pattern_certificate_id' => array('integer', 1),
				'obj_id'                 => array('integer', 20),
				'obj_type'               => array('clob',  'crs'),
				'user_id'                => array('integer', 400),
				'user_name'              => array('string', 'Niels Theen'),
				'acquired_timestamp'     => array('integer', 123456789),
				'certificate_content'    => array('clob', '<xml>Some Content</xml>'),
				'template_values'        => array('clob', '[]'),
				'valid_until'            => array('integer', null),
				'version'                => array('text', '1'),
				'ilias_version'          => array('text', 'v5.4.0'),
				'currently_active'       => array('integer', true),
				'background_image_path'  => array('clob', '/some/where/background.jpg'),
			)
		);

		$logger = $this->getMockBuilder('ilLogger')
			->disableOriginalConstructor()
			->getMock();

		$logger->expects($this->atLeastOnce())
			->method('info');

		$repository = new ilUserCertificateRepository($database, $logger);

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
			'1',
			'v5.4.0',
			true,
			'/some/where/background.jpg'
		);

		$repository->save($userCertificate);
	}

	public function testFetchAllActiveCertificateForUser()
	{
		$database = $this->getMockBuilder('ilDBInterface')
			->getMock();

		$database->method('nextId')
			->willReturn(141);

		$database->method('fetchAssoc')->willReturnOnConsecutiveCalls(
			array(
				'id'                     => 141,
				'pattern_certificate_id' => 1,
				'obj_id'                 => 20,
				'obj_type'               => 'crs',
				'user_id'                => 400,
				'user_name'              =>'Niels Theen',
				'acquired_timestamp'     => 123456789,
				'certificate_content'    => '<xml>Some Content</xml>',
				'template_values'        => '[]',
				'valid_until'            => null,
				'version'                => '1',
				'ilias_version'          => 'v5.4.0',
				'currently_active'       => true,
				'background_image_path'  => '/some/where/background.jpg',
			),
			array(
				'id'                     => 142,
				'pattern_certificate_id' => 5,
				'obj_id'                 => 3123,
				'obj_type'               => 'tst',
				'user_id'                => 400,
				'user_name'              => 'Niels Theen',
				'acquired_timestamp'     => 987654321,
				'certificate_content'    => '<xml>Some Other Content</xml>',
				'template_values'        => '[]',
				'valid_until'            => null,
				'version'                => '1',
				'ilias_version'          => 'v5.3.0',
				'currently_active'       => true,
				'background_image_path'  => '/some/where/else/background.jpg',
			)
		);

		$logger = $this->getMockBuilder('ilLogger')
			->disableOriginalConstructor()
			->getMock();

		$logger->expects($this->atLeastOnce())
			->method('info');

		$repository = new ilUserCertificateRepository($database, $logger);

		$results = $repository->fetchActiveCertificates(400);

		$this->assertEquals(141, $results[0]->getId());
		$this->assertEquals(142, $results[1]->getId());
	}

	public function testFetchActiveCertificateForUserObjectCombination()
	{
		$database = $this->getMockBuilder('ilDBInterface')
			->getMock();

		$database->method('nextId')
			->willReturn(141);

		$database->method('fetchAssoc')->willReturnOnConsecutiveCalls(
			array(
				'id'                     => 141,
				'pattern_certificate_id' => 1,
				'obj_id'                 => 20,
				'obj_type'               => 'crs',
				'user_id'                => 400,
				'user_name'              =>'Niels Theen',
				'acquired_timestamp'     => 123456789,
				'certificate_content'    => '<xml>Some Content</xml>',
				'template_values'        => '[]',
				'valid_until'            => null,
				'version'                => '1',
				'ilias_version'          => 'v5.4.0',
				'currently_active'       => true,
				'background_image_path'  => '/some/where/background.jpg',
			),
			array(
				'id'                     => 142,
				'pattern_certificate_id' => 5,
				'obj_id'                 => 20,
				'obj_type'               => 'tst',
				'user_id'                => 400,
				'user_name'              => 'Niels Theen',
				'acquired_timestamp'     => 987654321,
				'certificate_content'    => '<xml>Some Other Content</xml>',
				'template_values'        => '[]',
				'valid_until'            => null,
				'version'                => '1',
				'ilias_version'          => 'v5.3.0',
				'currently_active'       => true,
				'background_image_path'  => '/some/where/else/background.jpg',
			)
		);

		$logger = $this->getMockBuilder('ilLogger')
			->disableOriginalConstructor()
			->getMock();

		$logger->expects($this->atLeastOnce())
			->method('info');

		$repository = new ilUserCertificateRepository($database, $logger);

		$result = $repository->fetchActiveCertificate(400, 20);

		$this->assertEquals(141, $result->getId());
	}

	/**
	 * @expectedException ilException
	 */
	public function testFetchNoActiveCertificateLeadsToException()
	{
		$database = $this->getMockBuilder('ilDBInterface')
			->getMock();

		$database->method('nextId')
			->willReturn(141);

		$database->method('fetchAssoc')->willReturn(array());

		$logger = $this->getMockBuilder('ilLogger')
			->disableOriginalConstructor()
			->getMock();

		$logger->expects($this->atLeastOnce())
			->method('info');

		$repository = new ilUserCertificateRepository($database, $logger);

		$repository->fetchActiveCertificate(400, 20);

		$this->fail('Should never happen. Certificate Found?');
	}

	public function testFetchActiveCertificatesByType()
	{
		$database = $this->getMockBuilder('ilDBInterface')
			->getMock();

		$database->method('nextId')
			->willReturn(141);

		$database->method('fetchAssoc')->willReturnOnConsecutiveCalls(
			array(
				'id'                     => 141,
				'pattern_certificate_id' => 1,
				'obj_id'                 => 20,
				'obj_type'               => 'crs',
				'user_id'                => 400,
				'user_name'              =>'Niels Theen',
				'acquired_timestamp'     => 123456789,
				'certificate_content'    => '<xml>Some Content</xml>',
				'template_values'        => '[]',
				'valid_until'            => null,
				'version'                => '1',
				'ilias_version'          => 'v5.4.0',
				'currently_active'       => true,
				'background_image_path'  => '/some/where/background.jpg',
			),
			array(
				'id'                     => 142,
				'pattern_certificate_id' => 5,
				'obj_id'                 => 20,
				'obj_type'               => 'crs',
				'user_id'                => 400,
				'user_name'              => 'Niels Theen',
				'acquired_timestamp'     => 987654321,
				'certificate_content'    => '<xml>Some Other Content</xml>',
				'template_values'        => '[]',
				'valid_until'            => null,
				'version'                => '1',
				'ilias_version'          => 'v5.3.0',
				'currently_active'       => true,
				'background_image_path'  => '/some/where/else/background.jpg',
			)
		);

		$logger = $this->getMockBuilder('ilLogger')
			->disableOriginalConstructor()
			->getMock();

		$logger->expects($this->atLeastOnce())
			->method('info');

		$repository = new ilUserCertificateRepository($database, $logger);

		$results = $repository->fetchActiveCertificatesByType(400, 'crs');

		$this->assertEquals(141, $results[0]->getId());
		$this->assertEquals(142, $results[1]->getId());
	}

	public function testFetchCertificate()
	{
		$database = $this->getMockBuilder('ilDBInterface')
			->getMock();

		$database->method('nextId')
			->willReturn(141);

		$database->method('fetchAssoc')->willReturn(
			array(
				'id'                     => 141,
				'pattern_certificate_id' => 1,
				'obj_id'                 => 20,
				'obj_type'               => 'crs',
				'user_id'                => 400,
				'user_name'              =>'Niels Theen',
				'acquired_timestamp'     => 123456789,
				'certificate_content'    => '<xml>Some Content</xml>',
				'template_values'        => '[]',
				'valid_until'            => null,
				'version'                => '1',
				'ilias_version'          => 'v5.4.0',
				'currently_active'       => true,
				'background_image_path'  => '/some/where/background.jpg',
			)
		);

		$logger = $this->getMockBuilder('ilLogger')
			->disableOriginalConstructor()
			->getMock();

		$logger->expects($this->atLeastOnce())
			->method('info');

		$repository = new ilUserCertificateRepository($database, $logger);

		$result = $repository->fetchCertificate(141);

		$this->assertEquals(141, $result->getId());
	}

	/**
	 * @expectedException ilException
	 */
	public function testNoCertificateInFetchtCertificateLeadsToException()
	{
		$database = $this->getMockBuilder('ilDBInterface')
			->getMock();

		$database->method('nextId')
			->willReturn(141);

		$database->method('fetchAssoc')
			->willReturn(array());

		$logger = $this->getMockBuilder('ilLogger')
			->disableOriginalConstructor()
			->getMock();

		$logger->expects($this->atLeastOnce())
			->method('info');

		$repository = new ilUserCertificateRepository($database, $logger);

		$repository->fetchCertificate(141);

		$this->fail('Should never happen. Certificate Found?');
	}
}
