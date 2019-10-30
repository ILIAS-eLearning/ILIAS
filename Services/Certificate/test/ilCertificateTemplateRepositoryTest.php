<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateTemplateRepositoryTest extends \PHPUnit_Framework_TestCase
{
	public function testCertificateWillBeSavedToTheDatabase()
	{
		$database = $this->getMockBuilder('ilDBInterface')
			->disableOriginalConstructor()
			->getMock();

		$logger = $this->getMockBuilder('ilLogger')
			->disableOriginalConstructor()
			->getMock();

		$objectDataCache = $this->getMockBuilder('ilObjectDataCache')
			->disableOriginalConstructor()
			->getMock();

		$objectDataCache->method('lookUpType')->willReturn('crs');

		$database->method('nextId')
			->willReturn(10);

		$database->method('insert')
			->with(
				'il_cert_template',
				array(
					'id'                    => array('integer', 10),
					'obj_id'                => array('integer', 100),
					'obj_type'              => array('text', 'crs'),
					'certificate_content'   => array('clob', '<xml>Some Content</xml>'),
					'certificate_hash'      => array('text', md5('<xml>Some Content</xml>')),
					'template_values'       => array('clob', '[]'),
					'version'               => array('integer', 1),
					'ilias_version'         => array('text', 'v5.4.0'),
					'created_timestamp'     => array('integer', 123456789),
					'currently_active'      => array('integer', true),
					'background_image_path' => array('text', '/some/where/background.jpg'),
					'deleted'               => array('integer', 0),
					'thumbnail_image_path'  => array('text', 'some/path/test.svg')
				)
			);

		$logger->expects($this->atLeastOnce())
			->method('info');

		$template = new ilCertificateTemplate(
			100,
			'crs',
			'<xml>Some Content</xml>',
			md5('<xml>Some Content</xml>'),
			'[]',
			'1',
			'v5.4.0',
			123456789,
			true,
			$backgroundImagePath = '/some/where/background.jpg',
			'some/path/test.svg'

		);

		$repository = new ilCertificateTemplateRepository($database, $logger, $objectDataCache);

		$repository->save($template);
	}

	public function testFetchCertificateTemplatesByObjId()
	{
		$database = $this->getMockBuilder('ilDBInterface')
			->disableOriginalConstructor()
			->getMock();

		$logger = $this->getMockBuilder('ilLogger')
			->disableOriginalConstructor()
			->getMock();

		$database->method('fetchAssoc')
			->willReturnOnConsecutiveCalls(
				array(
					'id'                    => 1,
					'obj_id'                => 10,
					'obj_type'              => 'crs',
					'certificate_content'   => '<xml>Some Content</xml>',
					'certificate_hash'      => md5('<xml>Some Content</xml>'),
					'template_values'       => '[]',
					'version'               => 1,
					'ilias_version'         => 'v5.4.0',
					'created_timestamp'     => 123456789,
					'currently_active'      => true,
					'background_image_path' => '/some/where/background.jpg',
					'thumbnail_image_path' => 'some/path/test.svg'
				),
				array(
					'id'                    => 30,
					'obj_id'                => 10,
					'obj_type'              => 'tst',
					'certificate_content'   => '<xml>Some Other Content</xml>',
					'certificate_hash'      => md5('<xml>Some Content</xml>'),
					'template_values'       => '[]',
					'version'               => 55,
					'ilias_version'         => 'v5.3.0',
					'created_timestamp'     => 123456789,
					'currently_active'      => false,
					'background_image_path' => '/some/where/else/background.jpg',
					'thumbnail_image_path'  => 'some/path/test.svg'
				)
			);

		$objectDataCache = $this->getMockBuilder('ilObjectDataCache')
			->disableOriginalConstructor()
			->getMock();

		$objectDataCache->method('lookUpType')->willReturn('crs');

		$repository = new ilCertificateTemplateRepository($database, $logger, $objectDataCache);

		$templates = $repository->fetchCertificateTemplatesByObjId(10);

		$this->assertEquals(1, $templates[0]->getId());
		$this->assertEquals(30, $templates[1]->getId());
	}

	public function testFetchCurrentlyActiveCertificate()
	{
		$database = $this->getMockBuilder('ilDBInterface')
			->disableOriginalConstructor()
			->getMock();

		$logger = $this->getMockBuilder('ilLogger')
			->disableOriginalConstructor()
			->getMock();

		$database->method('fetchAssoc')
			->willReturnOnConsecutiveCalls(
				array(
					'id'                    => 1,
					'obj_id'                => 10,
					'obj_type'              => 'crs',
					'certificate_content'   => '<xml>Some Content</xml>',
					'certificate_hash'      => md5('<xml>Some Content</xml>'),
					'template_values'       => '[]',
					'version'               => 1,
					'ilias_version'         => 'v5.4.0',
					'created_timestamp'     => 123456789,
					'currently_active'      => true,
					'background_image_path' => '/some/where/background.jpg',
					'thumbnail_image_path'  => 'some/path/test.svg'
				),
				array(
					'id'                    => 30,
					'obj_id'                => 10,
					'obj_type'              => 'tst',
					'certificate_content'   => '<xml>Some Other Content</xml>',
					'certificate_hash'      => md5('<xml>Some Content</xml>'),
					'template_values'       => '[]',
					'version'               => 55,
					'ilias_version'         => 'v5.3.0',
					'created_timestamp'     => 123456789,
					'currently_active'      => false,
					'background_image_path' => '/some/where/else/background.jpg',
					'thumbnail_image_path'  => 'some/path/test.svg'
				)
			);

		$objectDataCache = $this->getMockBuilder('ilObjectDataCache')
			->disableOriginalConstructor()
			->getMock();

		$objectDataCache->method('lookUpType')->willReturn('crs');

		$repository = new ilCertificateTemplateRepository($database, $logger, $objectDataCache);

		$template = $repository->fetchCurrentlyActiveCertificate(10);

		$this->assertEquals(1, $template->getId());
	}

	public function testFetchPreviousCertificate()
	{
		$database = $this->getMockBuilder('ilDBInterface')
			->disableOriginalConstructor()
			->getMock();

		$logger = $this->getMockBuilder('ilLogger')
			->disableOriginalConstructor()
			->getMock();

		$database->method('fetchAssoc')
			->willReturnOnConsecutiveCalls(
				array(
					'id'                    => 1,
					'obj_id'                => 10,
					'obj_type'              => 'crs',
					'certificate_content'   => '<xml>Some Content</xml>',
					'certificate_hash'      => md5('<xml>Some Content</xml>'),
					'template_values'       => '[]',
					'version'               => 1,
					'ilias_version'         => 'v5.4.0',
					'created_timestamp'     => 123456789,
					'currently_active'      => true,
					'background_image_path' => '/some/where/background.jpg',
					'thumbnail_image_path' => 'some/path/test.svg'
				),
				array(
					'id'                    => 30,
					'obj_id'                => 10,
					'obj_type'              => 'tst',
					'certificate_content'   => '<xml>Some Other Content</xml>',
					'certificate_hash'      => md5('<xml>Some Content</xml>'),
					'template_values'       => '[]',
					'version'               => 55,
					'ilias_version'         => 'v5.3.0',
					'created_timestamp'     => 123456789,
					'currently_active'      => false,
					'background_image_path' => '/some/where/else/background.jpg',
					'thumbnail_image_path' => 'some/path/test.svg'
				)
			);

		$objectDataCache = $this->getMockBuilder('ilObjectDataCache')
			->disableOriginalConstructor()
			->getMock();

		$objectDataCache->method('lookUpType')->willReturn('crs');

		$repository = new ilCertificateTemplateRepository($database, $logger, $objectDataCache);

		$template = $repository->fetchPreviousCertificate(10);

		$this->assertEquals(30, $template->getId());
	}

	public function testDeleteTemplateFromDatabase()
	{
		$database = $this->getMockBuilder('ilDBInterface')
			->disableOriginalConstructor()
			->getMock();

		$logger = $this->getMockBuilder('ilLogger')
			->disableOriginalConstructor()
			->getMock();

		$database->method('quote')
			->withConsecutive(array(10, 'integer'), array(200, 'integer'))
			->willReturnOnConsecutiveCalls(10, 200);

		$database->method('query')
			->with('
DELETE FROM il_cert_template
WHERE id = 10
AND obj_id = 200');

		$objectDataCache = $this->getMockBuilder('ilObjectDataCache')
			->disableOriginalConstructor()
			->getMock();

		$objectDataCache->method('lookUpType')->willReturn('crs');

		$repository = new ilCertificateTemplateRepository($database, $logger, $objectDataCache);

		$repository->deleteTemplate(10, 200);
	}

	public function testActivatePreviousCertificate()
	{
		$database = $this->getMockBuilder('ilDBInterface')
			->disableOriginalConstructor()
			->getMock();

		$logger = $this->getMockBuilder('ilLogger')
			->disableOriginalConstructor()
			->getMock();

		$database->method('quote')
			->withConsecutive(array(10, 'integer'), array(30, 'integer'))
			->willReturnOnConsecutiveCalls(10, 30);

		$database->method('fetchAssoc')->willReturnOnConsecutiveCalls(
			array(
				'id'                    => 1,
				'obj_id'                => 10,
				'obj_type'              => 'crs',
				'certificate_content'   => '<xml>Some Content</xml>',
				'certificate_hash'      => md5('<xml>Some Content</xml>'),
				'template_values'       => '[]',
				'version'               => 1,
				'ilias_version'         => 'v5.4.0',
				'created_timestamp'     => 123456789,
				'currently_active'      => true,
				'background_image_path' => '/some/where/background.jpg',
				'thumbnail_image_path' => 'some/path/test.svg'
			),
			array(
				'id'                    => 30,
				'obj_id'                => 10,
				'obj_type'              => 'tst',
				'certificate_content'   => '<xml>Some Other Content</xml>',
				'certificate_hash'      => md5('<xml>Some Content</xml>'),
				'template_values'       => '[]',
				'version'               => 55,
				'ilias_version'         => 'v5.3.0',
				'created_timestamp'     => 123456789,
				'currently_active'      => false,
				'background_image_path' => '/some/where/else/background.jpg',
				'thumbnail_image_path' => 'some/path/test.svg'
			)
		);

		$database->method('query')
			->withConsecutive(
				array($this->anything()),
				array('UPDATE il_cert_template
SET currently_active = 1
WHERE id = 30')
			);

		$objectDataCache = $this->getMockBuilder('ilObjectDataCache')
			->disableOriginalConstructor()
			->getMock();

		$objectDataCache->method('lookUpType')->willReturn('crs');

		$repository = new ilCertificateTemplateRepository($database, $logger, $objectDataCache);

		$template = $repository->activatePreviousCertificate(10, 200);

		$this->assertEquals(30, $template->getId());
	}

	public function testFetchAllObjectIdsByType()
	{
		$database = $this->getMockBuilder('ilDBInterface')
			->disableOriginalConstructor()
			->getMock();

		$logger = $this->getMockBuilder('ilLogger')
			->disableOriginalConstructor()
			->getMock();

		$database->method('quote')
			->with('crs')
			->willReturn('crs');

		$objectDataCache = $this->getMockBuilder('ilObjectDataCache')
			->disableOriginalConstructor()
			->getMock();

		$database->method('fetchAssoc')->willReturnOnConsecutiveCalls(
			array(
				'id'                    => 1,
				'obj_id'                => 10,
				'obj_type'              => 'crs',
				'certificate_content'   => '<xml>Some Content</xml>',
				'certificate_hash'      => md5('<xml>Some Content</xml>'),
				'template_values'       => '[]',
				'version'               => 1,
				'ilias_version'         => 'v5.4.0',
				'created_timestamp'     => 123456789,
				'currently_active'      => true,
				'background_image_path' => '/some/where/background.jpg',
				'thumbnail_image_path' => '/some/where/thumbnail.svg'
			),
			array(
				'id'                    => 30,
				'obj_id'                => 30,
				'obj_type'              => 'crs',
				'certificate_content'   => '<xml>Some Other Content</xml>',
				'certificate_hash'      => md5('<xml>Some Content</xml>'),
				'template_values'       => '[]',
				'version'               => 55,
				'ilias_version'         => 'v5.3.0',
				'created_timestamp'     => 123456789,
				'currently_active'      => false,
				'background_image_path' => '/some/where/else/background.jpg',
				'thumbnail_image_path' => '/some/where/thumbnail.svg'
			)
		);

		$repository = new ilCertificateTemplateRepository($database, $logger, $objectDataCache);

		$templates = $repository->fetchActiveTemplatesByType('crs');

		$this->assertEquals(10, $templates[0]->getObjId());
		$this->assertEquals(30, $templates[1]->getObjId());
	}

	/**
	 * @expectedException ilException
	 */
	public function testFetchFirstCreatedTemplateFailsBecauseNothingWasSaved()
	{
		$database = $this->getMockBuilder('ilDBInterface')
			->disableOriginalConstructor()
			->getMock();

		$logger = $this->getMockBuilder('ilLogger')
			->disableOriginalConstructor()
			->getMock();

		$database->method('quote')
			->with(10, 'integer')
			->willReturn(10);

		$objectDataCache = $this->getMockBuilder('ilObjectDataCache')
			->disableOriginalConstructor()
			->getMock();

		$database->method('fetchAssoc')
			->willReturn(array());

		$database->method('fetchAssoc')
			->willReturn(array());

		$repository = new ilCertificateTemplateRepository($database, $logger, $objectDataCache);

		$repository->fetchFirstCreatedTemplate(10);

		$this->fail();
	}

	public function fetchFirstCreateTemplate()
	{
		$database = $this->getMockBuilder('ilDBInterface')
			->disableOriginalConstructor()
			->getMock();

		$logger = $this->getMockBuilder('ilLogger')
			->disableOriginalConstructor()
			->getMock();

		$database->method('quote')
			->with(10, 'integer')
			->willReturn(10);

		$objectDataCache = $this->getMockBuilder('ilObjectDataCache')
			->disableOriginalConstructor()
			->getMock();

		$database->method('fetchAssoc')
			->willReturn(array());

		$database->method('fetchAssoc')->willReturn(
			array(
				'id'                    => 1,
				'obj_id'                => 10,
				'obj_type'              => 'crs',
				'certificate_content'   => '<xml>Some Content</xml>',
				'certificate_hash'      => md5('<xml>Some Content</xml>'),
				'template_values'       => '[]',
				'version'               => 1,
				'ilias_version'         => 'v5.4.0',
				'created_timestamp'     => 123456789,
				'currently_active'      => true,
				'background_image_path' => '/some/where/background.jpg'
			)
		);

		$repository = new ilCertificateTemplateRepository($database, $logger, $objectDataCache);

		$firstTemplate = $repository->fetchFirstCreatedTemplate(10);

		$this->assertEquals(1, $firstTemplate->getId());
	}
}
