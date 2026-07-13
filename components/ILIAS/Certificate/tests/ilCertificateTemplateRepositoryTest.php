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

class ilCertificateTemplateRepositoryTest extends ilCertificateBaseTestCase
{
    public function testCertificateWillBeSavedToTheDatabase(): void
    {
        $database = $this->createMock(ilDBInterface::class);

        $logger = $this->getMockBuilder(ilLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectDataCache = $this->getMockBuilder(ilObjectDataCache::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectDataCache->method('lookUpType')->willReturn('crs');

        $database->method('nextId')
            ->willReturn(10);

        $database
            ->expects($this->once())
            ->method('insert')
            ->with(
                'il_cert_template',
                [
                    'id' => ['integer', 10],
                    'obj_id' => ['integer', 100],
                    'obj_type' => ['text', 'crs'],
                    'certificate_content' => ['clob', '<xml>Some Content</xml>'],
                    'certificate_hash' => ['text', md5('<xml>Some Content</xml>')],
                    'template_values' => ['clob', '[]'],
                    'version' => ['integer', 1],
                    'ilias_version' => ['text', 'v5.4.0'],
                    'created_timestamp' => ['integer', 123_456_789],
                    'currently_active' => ['integer', true],
                    'deleted' => ['integer', 0],
                    'background_image_ident' => ['text', '-'],
                    'tile_image_ident' => ['text', '-']
                ]
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
            123_456_789,
            true,
            '-',
            '-'
        );

        $repository = new ilCertificateTemplateDatabaseRepository($database, $logger, $objectDataCache);

        $repository->save($template);
    }

    public function testFetchCertificateTemplatesByObjId(): void
    {
        $logger = $this->getMockBuilder(ilLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $database = $this->createMock(ilDBInterface::class);
        $consecutive = [
            [
                'id' => 1,
                'obj_id' => 10,
                'obj_type' => 'crs',
                'certificate_content' => '<xml>Some Content</xml>',
                'certificate_hash' => md5('<xml>Some Content</xml>'),
                'template_values' => '[]',
                'version' => 1,
                'ilias_version' => 'v5.4.0',
                'created_timestamp' => 123_456_789,
                'currently_active' => true,
                'background_image_ident' => '-',
                'tile_image_ident' => '-'
            ],
            [
                'id' => 30,
                'obj_id' => 10,
                'obj_type' => 'tst',
                'certificate_content' => '<xml>Some Other Content</xml>',
                'certificate_hash' => md5('<xml>Some Content</xml>'),
                'template_values' => '[]',
                'version' => 55,
                'ilias_version' => 'v5.3.0',
                'created_timestamp' => 123_456_789,
                'currently_active' => false,
                'background_image_ident' => '-',
                'tile_image_ident' => '-'
            ]
        ];
        $database->method('fetchAssoc')->willReturnCallback(
            function () use (&$consecutive) {
                return array_shift($consecutive);
            }
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

    public function testFetchCurrentlyActiveCertificate(): void
    {
        $logger = $this->getMockBuilder(ilLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $database = $this->createMock(ilDBInterface::class);
        $consecutive = [
            [
                'id' => 1,
                'obj_id' => 10,
                'obj_type' => 'crs',
                'certificate_content' => '<xml>Some Content</xml>',
                'certificate_hash' => md5('<xml>Some Content</xml>'),
                'template_values' => '[]',
                'version' => 1,
                'ilias_version' => 'v5.4.0',
                'created_timestamp' => 123_456_789,
                'currently_active' => true,
                'background_image_ident' => '-',
                'tile_image_ident' => '-'
            ],
            [
                'id' => 30,
                'obj_id' => 10,
                'obj_type' => 'tst',
                'certificate_content' => '<xml>Some Other Content</xml>',
                'certificate_hash' => md5('<xml>Some Content</xml>'),
                'template_values' => '[]',
                'version' => 55,
                'ilias_version' => 'v5.3.0',
                'created_timestamp' => 123_456_789,
                'currently_active' => false,
                'background_image_ident' => '-',
                'tile_image_ident' => '-'
            ]
        ];
        $database->method('fetchAssoc')->willReturnCallback(
            function () use (&$consecutive) {
                return array_shift($consecutive);
            }
        );

        $objectDataCache = $this->getMockBuilder(ilObjectDataCache::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectDataCache->method('lookUpType')->willReturn('crs');

        $repository = new ilCertificateTemplateDatabaseRepository($database, $logger, $objectDataCache);

        $template = $repository->fetchCurrentlyActiveCertificate(10);

        $this->assertSame(1, $template->getId());
    }

    public function testFetchPreviousCertificate(): void
    {
        $logger = $this->getMockBuilder(ilLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $database = $this->createMock(ilDBInterface::class);
        $consecutive = [
                [
                    'id' => 1,
                    'obj_id' => 10,
                    'obj_type' => 'crs',
                    'certificate_content' => '<xml>Some Content</xml>',
                    'certificate_hash' => md5('<xml>Some Content</xml>'),
                    'template_values' => '[]',
                    'version' => 1,
                    'ilias_version' => 'v5.4.0',
                    'created_timestamp' => 123_456_789,
                    'currently_active' => true,
                    'background_image_ident' => '-',
                    'tile_image_ident' => '-'
                ],
                [
                    'id' => 30,
                    'obj_id' => 10,
                    'obj_type' => 'tst',
                    'certificate_content' => '<xml>Some Other Content</xml>',
                    'certificate_hash' => md5('<xml>Some Content</xml>'),
                    'template_values' => '[]',
                    'version' => 55,
                    'ilias_version' => 'v5.3.0',
                    'created_timestamp' => 123_456_789,
                    'currently_active' => false,
                    'background_image_ident' => '-',
                    'tile_image_ident' => '-'
                ]
        ];
        $database->method('fetchAssoc')->willReturnCallback(
            function () use (&$consecutive) {
                return array_shift($consecutive);
            }
        );

        $objectDataCache = $this->getMockBuilder(ilObjectDataCache::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectDataCache->method('lookUpType')->willReturn('crs');

        $repository = new ilCertificateTemplateDatabaseRepository($database, $logger, $objectDataCache);

        $template = $repository->fetchPreviousCertificate(10);

        $this->assertSame(30, $template->getId());
    }

    public function testDeleteTemplateFromDatabase(): void
    {
        $database = $this->createMock(ilDBInterface::class);
        $logger = $this->createStub(ilLogger::class);
        $object_data_cache = $this->createStub(ilObjectDataCache::class);

        $database->method('quote')->willReturnCallback(
            static fn(int $value, string $type): string => (string) $value
        );

        $captured_sql_string = null;
        $database
            ->expects($this->once())
            ->method('manipulate')
            ->willReturnCallback(static function (string $sql) use (&$captured_sql_string): int {
                $captured_sql_string = $sql;
                return 1;
            });

        $repository = new ilCertificateTemplateDatabaseRepository($database, $logger, $object_data_cache);

        $repository->deleteTemplate(10, 200);

        $this->assertSame(
            'UPDATE il_cert_template SET deleted = 1, currently_active = 0 WHERE id = 10 AND obj_id = 200',
            trim(preg_replace('/\s+/', ' ', $captured_sql_string))
        );
    }

    public function testActivatePreviousCertificate(): void
    {
        $logger = $this->getMockBuilder(ilLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $database = $this->createMock(ilDBInterface::class);

        $quote_consecutive = [
            [10, 'integer'],
            [30, 'integer']
        ];
        $database->method('quote')->willReturnCallback(
            function (int $v, string $type) use (&$quote_consecutive) {
                [$expected, $type] = array_shift($quote_consecutive);
                $this->assertEquals('integer', $type);
                $this->assertEquals($expected, $v);
                return (string) ($v);
            }
        );

        $consecutive = [
            [
                'id' => 1,
                'obj_id' => 10,
                'obj_type' => 'crs',
                'certificate_content' => '<xml>Some Content</xml>',
                'certificate_hash' => md5('<xml>Some Content</xml>'),
                'template_values' => '[]',
                'version' => 1,
                'ilias_version' => 'v5.4.0',
                'created_timestamp' => 123_456_789,
                'currently_active' => true,
                'background_image_ident' => '-',
                'tile_image_ident' => '-'
            ],
            [
                'id' => 30,
                'obj_id' => 10,
                'obj_type' => 'tst',
                'certificate_content' => '<xml>Some Other Content</xml>',
                'certificate_hash' => md5('<xml>Some Content</xml>'),
                'template_values' => '[]',
                'version' => 55,
                'ilias_version' => 'v5.3.0',
                'created_timestamp' => 123_456_789,
                'currently_active' => false,
                'background_image_ident' => '-',
                'tile_image_ident' => '-'
            ]
        ];
        $database->method('fetchAssoc')->willReturnCallback(
            function () use (&$consecutive) {
                return array_shift($consecutive);
            }
        );

        $query_consecutive = [
            "SELECT * FROM il_cert_template WHERE obj_id = 10 AND deleted = 0 ORDER BY version ASC",
            'UPDATE il_cert_template SET currently_active = 1 WHERE id = 30'
        ];
        $database->method('query')->willReturnCallback(
            function (string $v) use (&$query_consecutive) {
                $expected = array_shift($query_consecutive);
                $v = trim(str_replace(array("\n", "\r"), ' ', $v))  ;
                $this->assertEquals($expected, $v);
                return $this->createMock(ilDBStatement::class);
            },
        );

        $objectDataCache = $this->getMockBuilder(ilObjectDataCache::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectDataCache->method('lookUpType')->willReturn('crs');

        $repository = new ilCertificateTemplateDatabaseRepository($database, $logger, $objectDataCache);

        $template = $repository->activatePreviousCertificate(10);

        $this->assertSame(30, $template->getId());
    }

    public function testFetchAllObjectIdsByType(): void
    {
        $logger = $this->getMockBuilder(ilLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectDataCache = $this->getMockBuilder(ilObjectDataCache::class)
            ->disableOriginalConstructor()
            ->getMock();


        $database = $this->createMock(ilDBInterface::class);
        $consecutive = [
            [
                'id' => 1,
                'obj_id' => 10,
                'obj_type' => 'crs',
                'certificate_content' => '<xml>Some Content</xml>',
                'certificate_hash' => md5('<xml>Some Content</xml>'),
                'template_values' => '[]',
                'version' => 1,
                'ilias_version' => 'v5.4.0',
                'created_timestamp' => 123_456_789,
                'currently_active' => true,
                'background_image_ident' => '-',
                'tile_image_ident' => '-'
            ],
            [
                'id' => 30,
                'obj_id' => 30,
                'obj_type' => 'crs',
                'certificate_content' => '<xml>Some Other Content</xml>',
                'certificate_hash' => md5('<xml>Some Content</xml>'),
                'template_values' => '[]',
                'version' => 55,
                'ilias_version' => 'v5.3.0',
                'created_timestamp' => 123_456_789,
                'currently_active' => false,
                'background_image_ident' => '-',
                'tile_image_ident' => '-'
            ]
        ];
        $database->method('fetchAssoc')->willReturnCallback(
            function () use (&$consecutive) {
                return array_shift($consecutive);
            }
        );

        $repository = new ilCertificateTemplateDatabaseRepository($database, $logger, $objectDataCache);

        $templates = $repository->fetchActiveCertificateTemplatesForCoursesWithDisabledLearningProgress(true);

        $this->assertSame(10, $templates[0]->getObjId());
        $this->assertSame(30, $templates[1]->getObjId());
    }

    public function testFetchFirstCreatedTemplateFailsBecauseNothingWasSaved(): never
    {
        $this->expectException(ilException::class);

        $database = $this->createMock(ilDBInterface::class);

        $logger = $this->getMockBuilder(ilLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $database
            ->expects($this->once())
            ->method('quote')
            ->with(10, 'integer')
            ->willReturn('10');

        $objectDataCache = $this->getMockBuilder(ilObjectDataCache::class)
            ->disableOriginalConstructor()
            ->getMock();

        $database->method('fetchAssoc')
            ->willReturn([]);

        $database->method('fetchAssoc')
            ->willReturn([]);

        $repository = new ilCertificateTemplateDatabaseRepository($database, $logger, $objectDataCache);

        $repository->fetchFirstCreatedTemplate(10);

        $this->fail();
    }

    public function fetchFirstCreateTemplate(): void
    {
        $database = $this->createMock(ilDBInterface::class);

        $logger = $this->getMockBuilder(ilLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $database
            ->expects($this->once())
            ->method('quote')
            ->with(10, 'integer')
            ->willReturn(10);

        $objectDataCache = $this->getMockBuilder(ilObjectDataCache::class)
            ->disableOriginalConstructor()
            ->getMock();

        $database->method('fetchAssoc')
            ->willReturn([]);

        $database->method('fetchAssoc')->willReturn(
            [
                'id' => 1,
                'obj_id' => 10,
                'obj_type' => 'crs',
                'certificate_content' => '<xml>Some Content</xml>',
                'certificate_hash' => md5('<xml>Some Content</xml>'),
                'template_values' => '[]',
                'version' => 1,
                'ilias_version' => 'v5.4.0',
                'created_timestamp' => 123_456_789,
                'currently_active' => true,
                'background_image_ident' => '/some/where/background.jpg'
            ]
        );

        $repository = new ilCertificateTemplateDatabaseRepository($database, $logger, $objectDataCache);

        $firstTemplate = $repository->fetchFirstCreatedTemplate(10);

        $this->assertSame(1, $firstTemplate->getId());
    }
}
