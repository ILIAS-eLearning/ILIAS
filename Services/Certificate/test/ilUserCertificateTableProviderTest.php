<?php

declare(strict_types=1);

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
class ilUserCertificateTableProviderTest extends ilCertificateBaseTestCase
{
    public function testFetchingDataSetForTableWithoutParamtersAndWithoutFilters(): void
    {
        $database = $this->getMockBuilder(ilDBInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $database
            ->expects($this->atLeastOnce())
            ->method('quote');

        $database->method('fetchAssoc')
            ->willReturnOnConsecutiveCalls(
                [
                    'id' => 600,
                    'obj_id' => 100,
                    'title' => 'CourseTest',
                    'obj_type' => 'crs',
                    'acquired_timestamp' => 1539867618,
                    'thumbnail_image_path' => 'some/path/test.svg',
                    'description' => 'some description',
                    'firstname' => 'ilyas',
                    'lastname' => 'homer',
                ],
                null
            );

        $logger = $this->getMockBuilder(ilLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $provider = new ilUserCertificateTableProvider(
            $database,
            $logger,
            'default_title'
        );

        $dataSet = $provider->fetchDataSet(100, ['language' => 'de'], []);

        $expected = [];

        $expected['items'][] = [
            'id' => 600,
            'title' => 'CourseTest',
            'obj_id' => 100,
            'obj_type' => 'crs',
            'date' => 1539867618,
            'thumbnail_image_path' => 'some/path/test.svg',
            'description' => 'some description',
            'firstname' => 'ilyas',
            'lastname' => 'homer',
        ];

        $expected['cnt'] = 1;

        $this->assertSame($expected, $dataSet);
    }

    public function testFetchingDataSetForTableWithLimitParamterAndWithoutFilters(): void
    {
        $database = $this->createMock(ilDBInterface::class);

        $database
            ->expects($this->atLeastOnce())
            ->method('quote');

        $database->method('fetchAssoc')
            ->willReturnOnConsecutiveCalls(
                [
                    'id' => 600,
                    'obj_id' => 100,
                    'title' => 'CourseTest',
                    'obj_type' => 'crs',
                    'acquired_timestamp' => 1539867618,
                    'thumbnail_image_path' => 'some/path/test.svg',
                    'description' => 'some description',
                    'firstname' => 'ilyas',
                    'lastname' => 'homer',
                ],
                null,
                [
                    'cnt' => 5,
                ],
                null
            );

        $logger = $this->getMockBuilder(ilLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $provider = new ilUserCertificateTableProvider(
            $database,
            $logger,
            'default_title'
        );

        $dataSet = $provider->fetchDataSet(100, ['language' => 'de', 'limit' => 2], []);

        $expected = [];

        $expected['items'][] = [
            'id' => 600,
            'title' => 'CourseTest',
            'obj_id' => 100,
            'obj_type' => 'crs',
            'date' => 1539867618,
            'thumbnail_image_path' => 'some/path/test.svg',
            'description' => 'some description',
            'firstname' => 'ilyas',
            'lastname' => 'homer',
        ];

        $expected['cnt'] = 5;

        $this->assertSame($expected, $dataSet);
    }

    public function testFetchingDataSetForTableWithOrderFieldDate(): void
    {
        $database = $this->createMock(ilDBInterface::class);

        $database
            ->expects($this->atLeastOnce())
            ->method('quote');

        $database->method('fetchAssoc')
            ->willReturnOnConsecutiveCalls(
                [
                    'id' => 600,
                    'obj_id' => 100,
                    'title' => 'CourseTest',
                    'obj_type' => 'crs',
                    'acquired_timestamp' => 1539867618,
                    'thumbnail_image_path' => 'some/path/test.svg',
                    'description' => 'some description',
                    'firstname' => 'ilyas',
                    'lastname' => 'homer',
                ],
                null,
                [
                    'cnt' => 5,
                ],
                null
            );

        $logger = $this->getMockBuilder(ilLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $provider = new ilUserCertificateTableProvider(
            $database,
            $logger,
            'default_title'
        );

        $dataSet = $provider->fetchDataSet(
            100,
            ['language' => 'de', 'limit' => 2, 'order_field' => 'date'],
            []
        );

        $expected = [];

        $expected['items'][] = [
            'id' => 600,
            'title' => 'CourseTest',
            'obj_id' => 100,
            'obj_type' => 'crs',
            'date' => 1539867618,
            'thumbnail_image_path' => 'some/path/test.svg',
            'description' => 'some description',
            'firstname' => 'ilyas',
            'lastname' => 'homer',
        ];

        $expected['cnt'] = 5;

        $this->assertSame($expected, $dataSet);
    }

    public function testFetchingDataWithInvalidOrderFieldWillResultInException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $database = $this->createMock(ilDBInterface::class);

        $database
            ->expects($this->atLeastOnce())
            ->method('quote');

        $database->method('fetchAssoc')
            ->willReturnOnConsecutiveCalls(
                [
                    'id' => 600,
                    'obj_id' => 100,
                    'title' => 'CourseTest',
                    'obj_type' => 'crs',
                    'acquired_timestamp' => 1539867618
                ],
                null,
                [
                    'cnt' => 5,
                ],
                null
            );

        $logger = $this->getMockBuilder(ilLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $provider = new ilUserCertificateTableProvider(
            $database,
            $logger,
            'default_title'
        );

        $provider->fetchDataSet(
            100,
            ['language' => 'de', 'limit' => 2, 'order_field' => 'something'],
            []
        );

        $this->fail('Should never happen');
    }

    public function testFetchingDataWithEmptyOrderFieldWillResultInException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $database = $this->createMock(ilDBInterface::class);

        $database
            ->expects($this->atLeastOnce())
            ->method('quote');

        $database->method('fetchAssoc')
            ->willReturnOnConsecutiveCalls(
                [
                    'id' => 600,
                    'obj_id' => 100,
                    'title' => 'CourseTest',
                    'obj_type' => 'crs',
                    'acquired_timestamp' => 1539867618
                ],
                null,
                [
                    'cnt' => 5,
                ],
                null
            );

        $logger = $this->getMockBuilder(ilLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $provider = new ilUserCertificateTableProvider(
            $database,
            $logger,
            'default_title'
        );

        $provider->fetchDataSet(
            100,
            ['language' => 'de', 'limit' => 2, 'order_field' => false],
            []
        );

        $this->fail('Should never happen');
    }

    public function testFetchingDataWithWrongOrderDirectionWillResultInException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $database = $this->createMock(ilDBInterface::class);

        $database
            ->expects($this->atLeastOnce())
            ->method('quote');

        $database->method('fetchAssoc')
            ->willReturnOnConsecutiveCalls(
                [
                    'id' => 600,
                    'obj_id' => 100,
                    'title' => 'CourseTest',
                    'obj_type' => 'crs',
                    'acquired_timestamp' => 1539867618
                ],
                null,
                [
                    'cnt' => 5,
                ],
                null
            );

        $logger = $this->getMockBuilder(ilLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $provider = new ilUserCertificateTableProvider(
            $database,
            $logger,
            'default_title'
        );

        $provider->fetchDataSet(
            600,
            [
                'language' => 'de',
                'limit' => 2,
                'order_field' => 'date',
                'order_direction' => 'mac'
            ],
            []
        );

        $this->fail('Should never happen');
    }

    public function testFetchingDataWithInvalidLimitParameterWillResultInException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $database = $this->createMock(ilDBInterface::class);

        $database
            ->expects($this->atLeastOnce())
            ->method('quote');

        $database->method('fetchAssoc')
            ->willReturnOnConsecutiveCalls(
                [
                    'id' => 600,
                    'obj_id' => 100,
                    'title' => 'CourseTest',
                    'obj_type' => 'crs',
                    'acquired_timestamp' => 1539867618
                ],
                null,
                [
                    'cnt' => 5,
                ],
                null
            );

        $logger = $this->getMockBuilder(ilLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $provider = new ilUserCertificateTableProvider(
            $database,
            $logger,
            'default_title'
        );

        $provider->fetchDataSet(
            600,
            [
                'language' => 'de',
                'limit' => 'something',
                'order_field' => 'date',
                'order_direction' => 'mac'
            ],
            []
        );

        $this->fail('Should never happen');
    }

    public function testFetchingDataWithInvalidOffsetParameterWillResultInException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $database = $this->createMock(ilDBInterface::class);

        $database
            ->expects($this->atLeastOnce())
            ->method('quote');

        $database->method('fetchAssoc')
            ->willReturnOnConsecutiveCalls(
                [
                    'id' => 600,
                    'obj_id' => 100,
                    'title' => 'CourseTest',
                    'obj_type' => 'crs',
                    'acquired_timestamp' => 1539867618
                ],
                null,
                [
                    'cnt' => 5,
                ],
                null
            );

        $logger = $this->getMockBuilder(ilLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $provider = new ilUserCertificateTableProvider(
            $database,
            $logger,
            'default_title'
        );

        $provider->fetchDataSet(
            600,
            [
                'limit' => 3,
                'language' => 'de',
                'order_field' => 'date',
                'order_direction' => 'mac',
                'offset' => 'something'
            ],
            []
        );

        $this->fail('Should never happen');
    }
}
