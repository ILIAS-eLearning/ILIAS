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

namespace ILIAS\Course\Certificate;

use PDO;
use ilLogger;
use ilSetting;
use ilLPStatus;
use ilDBInterface;
use ilDBStatement;
use ilObjectDataCache;
use ilCertificateTemplate;
use ilCertificateObjectHelper;
use PHPUnit\Framework\TestCase;
use ilCertificateLPStatusHelper;
use ilCertificateTemplateRepository;
use ilCertificateObjUserTrackingHelper;
use ilCachedCertificateTemplateRepository;
use ilCertificateTemplateDatabaseRepository;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateCourseLearningProgressEvaluationTest extends TestCase
{
    public function testOnlyOneCourseIsCompletedOnLPChange(): void
    {
        $this->markTestSkipped('Data Provider needs to be revisited.');

        $templateRepository = $this->getMockBuilder(ilCertificateTemplateRepository::class)->getMock();

        $templateRepository->method('fetchActiveCertificateTemplatesForCoursesWithDisabledLearningProgress')
            ->willReturn(
                [
                    new ilCertificateTemplate(
                        5,
                        'crs',
                        '<xml>Some Content</xml>',
                        md5('<xml>Some Content</xml>'),
                        '[]',
                        1,
                        'v5.4.0',
                        123_456_789,
                        true,
                        '/some/where/background.jpg',
                        '/some/where/thumbnail.svg',
                        '-',
                        '-',
                        1
                    ),
                    new ilCertificateTemplate(
                        6,
                        'crs',
                        '<xml>Some Content</xml>',
                        md5('<xml>Some Content</xml>'),
                        '[]',
                        1,
                        'v5.4.0',
                        123_456_789,
                        true,
                        '/some/where/background.jpg',
                        '/some/where/thumbnail.svg',
                        '-',
                        '-',
                        5
                    ),
                ]
            );

        $setting = $this->getMockBuilder(ilSetting::class)
            ->disableOriginalConstructor()
            ->getMock();

        $consecutive_get = [
            ['cert_subitems_5', '[10,20]'],
            ['cert_subitems_6', '[10,50]'],
        ];
        $setting
            ->method('get')
            ->willReturnCallback(
                function (string $k) use (&$consecutive_get): string {
                    list($expected, $ret) = array_shift($consecutive_get);
                    $this->assertEquals($expected, $k);
                    return $k;
                }
            );

        $objectHelper = $this->getMockBuilder(ilCertificateObjectHelper::class)
            ->getMock();

        $consecutive_lookup = [10, 20, 10, 50];
        $objectHelper
            ->method('lookupObjId')
            ->willReturnCallback(
                function (int $id) use (&$consecutive_lookup): int {
                    $expected = array_shift($consecutive_lookup);
                    $this->assertEquals($expected, $id);

                    return $id * 10;
                }
            );

        $statusHelper = $this->getMockBuilder(ilCertificateLPStatusHelper::class)
            ->getMock();

        $consecutive_status = [
            [100, ilLPStatus::LP_STATUS_COMPLETED_NUM],
            [200, ilLPStatus::LP_STATUS_COMPLETED_NUM],
            [100, ilLPStatus::LP_STATUS_COMPLETED_NUM],
            [500, ilLPStatus::LP_STATUS_IN_PROGRESS_NUM],
        ];
        $statusHelper
            ->method('lookUpStatus')
            ->willReturnCallback(
                function (int $id) use (&$consecutive_status): int {
                    list($expected, $ret) = array_shift($consecutive_lookup);
                    $this->assertEquals($expected, $id);

                    return $ret;
                }
            );

        $trackingHelper = $this->getMockBuilder(ilCertificateObjUserTrackingHelper::class)
            ->getMock();
        $trackingHelper->method('enabledLearningProgress')->willReturn(true);

        $evaluation = new CertificateCourseLearningProgressEvaluation(
            $templateRepository,
            $setting,
            $objectHelper,
            $statusHelper,
            $trackingHelper
        );

        $completedCourses = $evaluation->evaluate(10, 200);

        $this->assertSame(5, $completedCourses[0]->getObjId());
    }

    public function testAllCoursesAreCompletedOnLPChange(): void
    {
        $this->markTestSkipped('Data Provider needs to be revisited.');

        $templateRepository = $this->getMockBuilder(ilCertificateTemplateRepository::class)->getMock();

        $templateRepository->method('fetchActiveCertificateTemplatesForCoursesWithDisabledLearningProgress')
            ->willReturn(
                [
                    new ilCertificateTemplate(
                        5,
                        'crs',
                        '<xml>Some Content</xml>',
                        md5('<xml>Some Content</xml>'),
                        '[]',
                        1,
                        'v5.4.0',
                        123_456_789,
                        true,
                        '/some/where/background.jpg',
                        '/some/where/thumbnail.svg',
                        '-',
                        '-',
                        1
                    ),
                    new ilCertificateTemplate(
                        6,
                        'crs',
                        '<xml>Some Content</xml>',
                        md5('<xml>Some Content</xml>'),
                        '[]',
                        1,
                        'v5.4.0',
                        123_456_789,
                        true,
                        '/some/where/background.jpg',
                        '/some/where/thumbnail.svg',
                        '-',
                        '-',
                        5
                    ),
                ]
            );

        $setting = $this->getMockBuilder(ilSetting::class)
            ->disableOriginalConstructor()
            ->getMock();

        $setting = $this->getMockBuilder(ilSetting::class)
            ->disableOriginalConstructor()
            ->getMock();

        $consecutive_get = [
            ['cert_subitems_5', '[10,20]'],
            ['cert_subitems_6', '[10,500]'],
        ];
        $setting
            ->method('get')
            ->willReturnCallback(
                function (string $k) use (&$consecutive_get): string {
                    list($expected, $ret) = array_shift($consecutive_get);
                    $this->assertEquals($expected, $k);
                    return $k;
                }
            );

        $objectHelper = $this->getMockBuilder(ilCertificateObjectHelper::class)
            ->getMock();

        $consecutive_lookup = [
            [10, 100],
            [20, 200],
            [10, 100],
            [500, 500],
        ];
        $objectHelper
            ->method('lookupObjId')
            ->willReturnCallback(
                function (int $id) use (&$consecutive_lookup): int {
                    list($expected, $ret) = array_shift($consecutive_lookup);
                    $this->assertEquals($expected, $id);

                    return $ret;
                }
            );

        $statusHelper = $this->getMockBuilder(ilCertificateLPStatusHelper::class)
            ->getMock();

        $consecutive_status = [
            [100, ilLPStatus::LP_STATUS_COMPLETED_NUM],
            [200, ilLPStatus::LP_STATUS_COMPLETED_NUM],
            [100, ilLPStatus::LP_STATUS_COMPLETED_NUM],
            [500, ilLPStatus::LP_STATUS_COMPLETED_NUM],
        ];

        $statusHelper
            ->method('lookUpStatus')
            ->willReturnCallback(
                function (int $id) use (&$consecutive_status): int {
                    list($expected, $ret) = array_shift($consecutive_lookup);
                    $this->assertEquals($expected, $id);

                    return $ret;
                }
            );

        $trackingHelper = $this->getMockBuilder(ilCertificateObjUserTrackingHelper::class)
            ->getMock();
        $trackingHelper->method('enabledLearningProgress')->willReturn(false);

        $evaluation = new CertificateCourseLearningProgressEvaluation(
            $templateRepository,
            $setting,
            $objectHelper,
            $statusHelper,
            $trackingHelper
        );

        $completedCourses = $evaluation->evaluate(10, 200);

        $this->assertSame(5, $completedCourses[0]->getObjId());
        $this->assertSame(6, $completedCourses[1]->getObjId());
    }

    public function testNoSubitemDefinedForEvaluation(): void
    {
        $templateRepository = $this->getMockBuilder(ilCertificateTemplateRepository::class)->getMock();

        $templateRepository->method('fetchActiveCertificateTemplatesForCoursesWithDisabledLearningProgress')
            ->willReturn(
                [
                    new ilCertificateTemplate(
                        5,
                        'crs',
                        '<xml>Some Content</xml>',
                        md5('<xml>Some Content</xml>'),
                        '[]',
                        1,
                        'v5.4.0',
                        123_456_789,
                        true,
                        '/some/where/background.jpg',
                        '/some/where/thumbnail.svg',
                        '-',
                        '-',
                        1
                    ),
                    new ilCertificateTemplate(
                        6,
                        'crs',
                        '<xml>Some Content</xml>',
                        md5('<xml>Some Content</xml>'),
                        '[]',
                        1,
                        'v5.4.0',
                        123_456_789,
                        true,
                        '/some/where/background.jpg',
                        '/some/where/thumbnail.svg',
                        '-',
                        '-',
                        5
                    ),
                ]
            );

        $setting = $this->getMockBuilder(ilSetting::class)
            ->disableOriginalConstructor()
            ->getMock();

        $consecutive_get = [
            'cert_subitems_5',
            'cert_subitems_6',
        ];
        $setting
            ->method('get')
            ->willReturnCallback(
                function (string $k) use (&$consecutive_get) {
                    $expected = array_shift($consecutive_get);
                    $this->assertEquals($expected, $k);

                    return null;
                }
            );

        $objectHelper = $this->getMockBuilder(ilCertificateObjectHelper::class)
            ->getMock();

        $statusHelper = $this->getMockBuilder(ilCertificateLPStatusHelper::class)
            ->getMock();

        $trackingHelper = $this->getMockBuilder(ilCertificateObjUserTrackingHelper::class)
            ->getMock();
        $trackingHelper->method('enabledLearningProgress')->willReturn(false);

        $evaluation = new CertificateCourseLearningProgressEvaluation(
            $templateRepository,
            $setting,
            $objectHelper,
            $statusHelper,
            $trackingHelper
        );

        $completedCourses = $evaluation->evaluate(10, 200);

        $this->assertSame([], $completedCourses);
    }

    public static function globalLearningProgressStateProvder(): array
    {
        return [
            'LP globally enabled' => [true, []],
            'LP globally disabled' => [true, [
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
                    'background_image_path' => '/some/where/background.jpg',
                    'thumbnail_image_path' => 'some/path/test.svg',
                    'background_image_ident' => '-',
                    'thumbnail_image_ident' => '-'
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
                    'background_image_path' => '/some/where/else/background.jpg',
                    'thumbnail_image_path' => 'some/path/test.svg',
                    'background_image_ident' => '-',
                    'thumbnail_image_ident' => '-'
                ]
            ]],
        ];
    }

    /**
     * @dataProvider globalLearningProgressStateProvder
     * @param array[] $template_recods
     */
    public function testRetrievingCertificateTemplatesForCoursesWorksAsExpectedWhenUsingNonCachingRepository(
        bool $isGlobalLpEnabled,
        array $template_recods
    ): void {
        $statement = $database = $this->getMockBuilder(ilDBStatement::class)->getMock();
        $i = 0;
        $database->method('fetch')->willReturnCallback(static function () use (&$i, $template_recods): ?array {
            $result = $template_recods[$i] ?? null;
            ++$i;

            return $result;
        });

        $database = $this->createMock(ilDBInterface::class);
        $database->expects($this->once())->method('queryF')->with(
            $isGlobalLpEnabled
                ? $this->logicalAnd(
                    $this->stringContains('LEFT JOIN ut_lp_settings', false),
                    $this->stringContains('uls.u_mode', false),
                ) : $this->logicalAnd(
                    $this->logicalNot($this->stringContains('LEFT JOIN ut_lp_settings', false)),
                    $this->logicalNot($this->stringContains('uls.u_mode', false)),
                )
        )->willReturn($statement);
        $database->expects($this->exactly(count($template_recods) + 1))
            ->method('fetchAssoc')
            ->with($statement)
            ->willReturnCallback(static function (ilDBStatement $statement) {
                return $statement->fetch(PDO::FETCH_ASSOC);
            });

        $logger = $this->getMockBuilder(ilLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectDataCache = $this->getMockBuilder(ilObjectDataCache::class)
            ->disableOriginalConstructor()
            ->getMock();

        $repository = new ilCertificateTemplateDatabaseRepository(
            $database,
            $logger,
            $objectDataCache
        );

        $templates = $repository->fetchActiveCertificateTemplatesForCoursesWithDisabledLearningProgress(
            $isGlobalLpEnabled
        );
        $this->assertCount(count($template_recods), $templates);
    }

    public function testRetrievingCertificateTemplatesForCoursesWillBeCachedWhenCachingRepositoryIsUsed(): void
    {
        $wrappedTemplateRepository = $this->getMockBuilder(ilCertificateTemplateRepository::class)->getMock();
        $wrappedTemplateRepository
            ->expects($this->exactly(2))
            ->method('fetchActiveCertificateTemplatesForCoursesWithDisabledLearningProgress')
            ->willReturnCallback(static function (bool $isGlobalLpEnabled): array {
                if ($isGlobalLpEnabled) {
                    return [];
                }

                return [
                    new ilCertificateTemplate(
                        5,
                        'crs',
                        '<xml>Some Content</xml>',
                        md5('<xml>Some Content</xml>'),
                        '[]',
                        1,
                        'v5.4.0',
                        123_456_789,
                        true,
                        '/some/where/background.jpg',
                        '/some/where/thumbnail.svg',
                        '-',
                        '-',
                        1
                    ),
                    new ilCertificateTemplate(
                        6,
                        'crs',
                        '<xml>Some Content</xml>',
                        md5('<xml>Some Content</xml>'),
                        '[]',
                        1,
                        'v5.4.0',
                        123_456_789,
                        true,
                        '/some/where/background.jpg',
                        '/some/where/thumbnail.svg',
                        '-',
                        '-',
                        5
                    ),
                ];
            });
        $templateRepository = new ilCachedCertificateTemplateRepository($wrappedTemplateRepository);

        $result1 = $templateRepository->fetchActiveCertificateTemplatesForCoursesWithDisabledLearningProgress(true);
        // Do not delegate to wrapped repository again
        $result2 = $templateRepository->fetchActiveCertificateTemplatesForCoursesWithDisabledLearningProgress(true);
        // Should be delegated
        $result3 = $templateRepository->fetchActiveCertificateTemplatesForCoursesWithDisabledLearningProgress(false);
        // Do not delegate to wrapped repository again
        $result4 = $templateRepository->fetchActiveCertificateTemplatesForCoursesWithDisabledLearningProgress(false);
        // Do not delegate to wrapped repository again
        $result5 = $templateRepository->fetchActiveCertificateTemplatesForCoursesWithDisabledLearningProgress(true);

        $this->assertSame($result1, $result2);
        $this->assertSame($result1, $result5);
        $this->assertCount(0, $result1);

        $this->assertNotSame($result1, $result3);
        $this->assertSame($result3, $result4);
        $this->assertCount(2, $result3);
    }
}
