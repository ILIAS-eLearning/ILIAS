<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateTemplateRepositoryTest extends ilCertificateBaseTestCase
{
    public function testCertificateWillBeSavedToTheDatabase() : void
    {
        $database = $this->getMockBuilder(ilDBInterface::class)->getMock();

        $logger = $this->getMockBuilder(ilLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectDataCache = $this->getMockBuilder(ilObjectDataCache::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectDataCache->method('lookUpType')->willReturn('crs');

        $database->method('nextId')
            ->willReturn(10);

        $database->method('insert')
            ->with(
                'il_cert_template',
                array(
                    'id' => array('integer', 10),
                    'obj_id' => array('integer', 100),
                    'obj_type' => array('text', 'crs'),
                    'certificate_content' => array('clob', '<xml>Some Content</xml>'),
                    'certificate_hash' => array('text', md5('<xml>Some Content</xml>')),
                    'template_values' => array('clob', '[]'),
                    'version' => array('integer', 1),
                    'ilias_version' => array('text', 'v5.4.0'),
                    'created_timestamp' => array('integer', 123456789),
                    'currently_active' => array('integer', true),
                    'background_image_path' => array('text', '/some/where/background.jpg'),
                    'deleted' => array('integer', 0),
                    'thumbnail_image_path' => array('text', 'some/path/test.svg')
                )
            );

        $logger->expects($this->atLeastOnce())
            ->method('debug');

        $template = new ilCertificateTemplate(
            100,
            'crs',
            '<xml>Some Content</xml>',
            md5('<xml>Some Content</xml>'),
            '[]',
            1,
            'v5.4.0',
            123456789,
            true,
            $backgroundImagePath = '/some/where/background.jpg',
            'some/path/test.svg'
        );

        $repository = new ilCertificateTemplateDatabaseRepository($database, $logger, $objectDataCache);

        $repository->save($template);
    }

    public function testFetchCertificateTemplatesByObjId() : void
    {
        $database = $this->getMockBuilder(ilDBInterface::class)->getMock();

        $logger = $this->getMockBuilder(ilLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $database->method('fetchAssoc')
            ->willReturnOnConsecutiveCalls(
                array(
                    'id' => 1,
                    'obj_id' => 10,
                    'obj_type' => 'crs',
                    'certificate_content' => '<xml>Some Content</xml>',
                    'certificate_hash' => md5('<xml>Some Content</xml>'),
                    'template_values' => '[]',
                    'version' => 1,
                    'ilias_version' => 'v5.4.0',
                    'created_timestamp' => 123456789,
                    'currently_active' => true,
                    'background_image_path' => '/some/where/background.jpg',
                    'thumbnail_image_path' => 'some/path/test.svg'
                ),
                array(
                    'id' => 30,
                    'obj_id' => 10,
                    'obj_type' => 'tst',
                    'certificate_content' => '<xml>Some Other Content</xml>',
                    'certificate_hash' => md5('<xml>Some Content</xml>'),
                    'template_values' => '[]',
                    'version' => 55,
                    'ilias_version' => 'v5.3.0',
                    'created_timestamp' => 123456789,
                    'currently_active' => false,
                    'background_image_path' => '/some/where/else/background.jpg',
                    'thumbnail_image_path' => 'some/path/test.svg'
                )
            );

        $objectDataCache = $this->getMockBuilder(ilObjectDataCache::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectDataCache->method('lookUpType')->willReturn('crs');

        $repository = new ilCertificateTemplateDatabaseRepository($database, $logger, $objectDataCache);

        $templates = $repository->fetchCertificateTemplatesByObjId(10);

        $this->assertSame(1, $templates[0]->getId());
        $this->assertSame(30, $templates[1]->getId());
    }

    public function testFetchCurrentlyActiveCertificate() : void
    {
        $database = $this->getMockBuilder(ilDBInterface::class)->getMock();

        $logger = $this->getMockBuilder(ilLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $database->method('fetchAssoc')
            ->willReturnOnConsecutiveCalls(
                array(
                    'id' => 1,
                    'obj_id' => 10,
                    'obj_type' => 'crs',
                    'certificate_content' => '<xml>Some Content</xml>',
                    'certificate_hash' => md5('<xml>Some Content</xml>'),
                    'template_values' => '[]',
                    'version' => 1,
                    'ilias_version' => 'v5.4.0',
                    'created_timestamp' => 123456789,
                    'currently_active' => true,
                    'background_image_path' => '/some/where/background.jpg',
                    'thumbnail_image_path' => 'some/path/test.svg'
                ),
                array(
                    'id' => 30,
                    'obj_id' => 10,
                    'obj_type' => 'tst',
                    'certificate_content' => '<xml>Some Other Content</xml>',
                    'certificate_hash' => md5('<xml>Some Content</xml>'),
                    'template_values' => '[]',
                    'version' => 55,
                    'ilias_version' => 'v5.3.0',
                    'created_timestamp' => 123456789,
                    'currently_active' => false,
                    'background_image_path' => '/some/where/else/background.jpg',
                    'thumbnail_image_path' => 'some/path/test.svg'
                )
            );

        $objectDataCache = $this->getMockBuilder(ilObjectDataCache::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectDataCache->method('lookUpType')->willReturn('crs');

        $repository = new ilCertificateTemplateDatabaseRepository($database, $logger, $objectDataCache);

        $template = $repository->fetchCurrentlyActiveCertificate(10);

        $this->assertSame(1, $template->getId());
    }

    public function testFetchPreviousCertificate() : void
    {
        $database = $this->getMockBuilder(ilDBInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $logger = $this->getMockBuilder(ilLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $database->method('fetchAssoc')
            ->willReturnOnConsecutiveCalls(
                array(
                    'id' => 1,
                    'obj_id' => 10,
                    'obj_type' => 'crs',
                    'certificate_content' => '<xml>Some Content</xml>',
                    'certificate_hash' => md5('<xml>Some Content</xml>'),
                    'template_values' => '[]',
                    'version' => 1,
                    'ilias_version' => 'v5.4.0',
                    'created_timestamp' => 123456789,
                    'currently_active' => true,
                    'background_image_path' => '/some/where/background.jpg',
                    'thumbnail_image_path' => 'some/path/test.svg'
                ),
                array(
                    'id' => 30,
                    'obj_id' => 10,
                    'obj_type' => 'tst',
                    'certificate_content' => '<xml>Some Other Content</xml>',
                    'certificate_hash' => md5('<xml>Some Content</xml>'),
                    'template_values' => '[]',
                    'version' => 55,
                    'ilias_version' => 'v5.3.0',
                    'created_timestamp' => 123456789,
                    'currently_active' => false,
                    'background_image_path' => '/some/where/else/background.jpg',
                    'thumbnail_image_path' => 'some/path/test.svg'
                )
            );

        $objectDataCache = $this->getMockBuilder(ilObjectDataCache::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectDataCache->method('lookUpType')->willReturn('crs');

        $repository = new ilCertificateTemplateDatabaseRepository($database, $logger, $objectDataCache);

        $template = $repository->fetchPreviousCertificate(10);

        $this->assertSame(30, $template->getId());
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testDeleteTemplateFromDatabase() : void
    {
        $database = $this->getMockBuilder(ilDBInterface::class)->getMock();

        $logger = $this->getMockBuilder(ilLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $database->method('quote')
            ->withConsecutive(array(10, 'integer'), array(200, 'integer'))
            ->willReturnOnConsecutiveCalls('10', '200');

        $database->method('query')
            ->with('
DELETE FROM il_cert_template
WHERE id = 10
AND obj_id = 200');

        $objectDataCache = $this->getMockBuilder(ilObjectDataCache::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectDataCache->method('lookUpType')->willReturn('crs');

        $repository = new ilCertificateTemplateDatabaseRepository($database, $logger, $objectDataCache);

        $repository->deleteTemplate(10, 200);
    }

    public function testActivatePreviousCertificate() : void
    {
        $database = $this->getMockBuilder(ilDBInterface::class)->getMock();

        $logger = $this->getMockBuilder(ilLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $database->method('quote')
            ->withConsecutive(array(10, 'integer'), array(30, 'integer'))
            ->willReturnOnConsecutiveCalls('10', '30');

        $database->method('fetchAssoc')->willReturnOnConsecutiveCalls(
            array(
                'id' => 1,
                'obj_id' => 10,
                'obj_type' => 'crs',
                'certificate_content' => '<xml>Some Content</xml>',
                'certificate_hash' => md5('<xml>Some Content</xml>'),
                'template_values' => '[]',
                'version' => 1,
                'ilias_version' => 'v5.4.0',
                'created_timestamp' => 123456789,
                'currently_active' => true,
                'background_image_path' => '/some/where/background.jpg',
                'thumbnail_image_path' => 'some/path/test.svg'
            ),
            array(
                'id' => 30,
                'obj_id' => 10,
                'obj_type' => 'tst',
                'certificate_content' => '<xml>Some Other Content</xml>',
                'certificate_hash' => md5('<xml>Some Content</xml>'),
                'template_values' => '[]',
                'version' => 55,
                'ilias_version' => 'v5.3.0',
                'created_timestamp' => 123456789,
                'currently_active' => false,
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

        $objectDataCache = $this->getMockBuilder(ilObjectDataCache::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectDataCache->method('lookUpType')->willReturn('crs');

        $repository = new ilCertificateTemplateDatabaseRepository($database, $logger, $objectDataCache);

        $template = $repository->activatePreviousCertificate(10);

        $this->assertSame(30, $template->getId());
    }

    public function testFetchAllObjectIdsByType() : void
    {
        $database = $this->getMockBuilder(ilDBInterface::class)->getMock();

        $logger = $this->getMockBuilder(ilLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectDataCache = $this->getMockBuilder(ilObjectDataCache::class)
            ->disableOriginalConstructor()
            ->getMock();

        $database->method('fetchAssoc')->willReturnOnConsecutiveCalls(
            array(
                'id' => 1,
                'obj_id' => 10,
                'obj_type' => 'crs',
                'certificate_content' => '<xml>Some Content</xml>',
                'certificate_hash' => md5('<xml>Some Content</xml>'),
                'template_values' => '[]',
                'version' => 1,
                'ilias_version' => 'v5.4.0',
                'created_timestamp' => 123456789,
                'currently_active' => true,
                'background_image_path' => '/some/where/background.jpg',
                'thumbnail_image_path' => '/some/where/thumbnail.svg'
            ),
            array(
                'id' => 30,
                'obj_id' => 30,
                'obj_type' => 'crs',
                'certificate_content' => '<xml>Some Other Content</xml>',
                'certificate_hash' => md5('<xml>Some Content</xml>'),
                'template_values' => '[]',
                'version' => 55,
                'ilias_version' => 'v5.3.0',
                'created_timestamp' => 123456789,
                'currently_active' => false,
                'background_image_path' => '/some/where/else/background.jpg',
                'thumbnail_image_path' => '/some/where/thumbnail.svg'
            )
        );

        $repository = new ilCertificateTemplateDatabaseRepository($database, $logger, $objectDataCache);

        $templates = $repository->fetchActiveCertificateTemplatesForCoursesWithDisabledLearningProgress(true);

        $this->assertSame(10, $templates[0]->getObjId());
        $this->assertSame(30, $templates[1]->getObjId());
    }

    /**
     *
     */
    public function testFetchFirstCreatedTemplateFailsBecauseNothingWasSaved() : void
    {
        $this->expectException(\ilException::class);

        $database = $this->getMockBuilder(ilDBInterface::class)->getMock();

        $logger = $this->getMockBuilder(ilLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $database->method('quote')
            ->with(10, 'integer')
            ->willReturn('10');

        $objectDataCache = $this->getMockBuilder(ilObjectDataCache::class)
            ->disableOriginalConstructor()
            ->getMock();

        $database->method('fetchAssoc')
            ->willReturn(array());

        $database->method('fetchAssoc')
            ->willReturn(array());

        $repository = new ilCertificateTemplateDatabaseRepository($database, $logger, $objectDataCache);

        $repository->fetchFirstCreatedTemplate(10);

        $this->fail();
    }

    public function fetchFirstCreateTemplate() : void
    {
        $database = $this->getMockBuilder(ilDBInterface::class)->getMock();

        $logger = $this->getMockBuilder(ilLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $database->method('quote')
            ->with(10, 'integer')
            ->willReturn(10);

        $objectDataCache = $this->getMockBuilder(ilObjectDataCache::class)
            ->disableOriginalConstructor()
            ->getMock();

        $database->method('fetchAssoc')
            ->willReturn(array());

        $database->method('fetchAssoc')->willReturn(
            array(
                'id' => 1,
                'obj_id' => 10,
                'obj_type' => 'crs',
                'certificate_content' => '<xml>Some Content</xml>',
                'certificate_hash' => md5('<xml>Some Content</xml>'),
                'template_values' => '[]',
                'version' => 1,
                'ilias_version' => 'v5.4.0',
                'created_timestamp' => 123456789,
                'currently_active' => true,
                'background_image_path' => '/some/where/background.jpg'
            )
        );

        $repository = new ilCertificateTemplateDatabaseRepository($database, $logger, $objectDataCache);

        $firstTemplate = $repository->fetchFirstCreatedTemplate(10);

        $this->assertSame(1, $firstTemplate->getId());
    }
}
