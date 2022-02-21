<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilUserCertificateTableProviderTest extends ilCertificateBaseTestCase
{
    public function testFetchingDataSetForTableWithoutParamtersAndWithoutFilters() : void
    {
        $database = $this->getMockBuilder(ilDBInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $database
            ->expects($this->atLeastOnce())
            ->method('quote');

        $database->method('fetchAssoc')
            ->willReturnOnConsecutiveCalls(
                array(
                    'id' => 600,
                    'obj_id' => 100,
                    'title' => 'CourseTest',
                    'obj_type' => 'crs',
                    'acquired_timestamp' => 1539867618,
                    'thumbnail_image_path' => 'some/path/test.svg',
                    'description' => 'some description',
                    'firstname' => 'ilyas',
                    'lastname' => 'homer',
                ),
                null
            );

        $logger = $this->getMockBuilder(ilLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $controller = $this->getMockBuilder(ilCtrlInterface::class)
            ->getMock();

        $controller->method('getLinkTargetByClass')
            ->willReturn('something');

        $objectMock = $this->getMockBuilder(ilObject::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectMock->method('getTitle')
            ->willReturn('CourseTest');

        $objectHelper = $this->getMockBuilder(ilCertificateObjectHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectHelper->method('getInstanceByObjId')
            ->willReturn($objectMock);

        $provider = new ilUserCertificateTableProvider(
            $database,
            $logger,
            $controller,
            'default_title',
            $objectHelper
        );

        $dataSet = $provider->fetchDataSet(100, array('language' => 'de'), array());

        $expected = array();

        $expected['items'][] = array(
            'id' => 600,
            'title' => 'CourseTest',
            'obj_id' => 100,
            'obj_type' => 'crs',
            'date' => 1539867618,
            'thumbnail_image_path' => 'some/path/test.svg',
            'description' => 'some description',
            'firstname' => 'ilyas',
            'lastname' => 'homer',
        );

        $expected['cnt'] = 1;

        $this->assertEquals($expected, $dataSet);
    }

    public function testFetchingDataSetForTableWithLimitParamterAndWithoutFilters() : void
    {
        $database = $this->getMockBuilder(ilDBInterface::class)->getMock();

        $database
            ->expects($this->atLeastOnce())
            ->method('quote');

        $database->method('fetchAssoc')
            ->willReturnOnConsecutiveCalls(
                array(
                    'id' => 600,
                    'obj_id' => 100,
                    'title' => 'CourseTest',
                    'obj_type' => 'crs',
                    'acquired_timestamp' => 1539867618,
                    'thumbnail_image_path' => 'some/path/test.svg',
                    'description' => 'some description',
                    'firstname' => 'ilyas',
                    'lastname' => 'homer',
                ),
                null,
                array(
                    'cnt' => 5,
                ),
                null
            );

        $logger = $this->getMockBuilder(ilLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $controller = $this->getMockBuilder(ilCtrlInterface::class)
            ->getMock();

        $controller->method('getLinkTargetByClass')
            ->willReturn('something');

        $objectMock = $this->getMockBuilder(ilObject::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectMock->method('getTitle')
            ->willReturn('CourseTest');

        $objectHelper = $this->getMockBuilder(ilCertificateObjectHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectHelper->method('getInstanceByObjId')
            ->willReturn($objectMock);

        $provider = new ilUserCertificateTableProvider(
            $database,
            $logger,
            $controller,
            'default_title',
            $objectHelper
        );

        $dataSet = $provider->fetchDataSet(100, array('language' => 'de', 'limit' => 2), array());

        $expected = array();

        $expected['items'][] = array(
            'id' => 600,
            'title' => 'CourseTest',
            'obj_id' => 100,
            'obj_type' => 'crs',
            'date' => 1539867618,
            'thumbnail_image_path' => 'some/path/test.svg',
            'description' => 'some description',
            'firstname' => 'ilyas',
            'lastname' => 'homer',
        );

        $expected['cnt'] = 5;

        $this->assertEquals($expected, $dataSet);
    }

    public function testFetchingDataSetForTableWithOrderFieldDate() : void
    {
        $database = $this->getMockBuilder(ilDBInterface::class)->getMock();

        $database
            ->expects($this->atLeastOnce())
            ->method('quote');

        $database->method('fetchAssoc')
            ->willReturnOnConsecutiveCalls(
                array(
                    'id' => 600,
                    'obj_id' => 100,
                    'title' => 'CourseTest',
                    'obj_type' => 'crs',
                    'acquired_timestamp' => 1539867618,
                    'thumbnail_image_path' => 'some/path/test.svg',
                    'description' => 'some description',
                    'firstname' => 'ilyas',
                    'lastname' => 'homer',
                ),
                null,
                array(
                    'cnt' => 5,
                ),
                null
            );

        $logger = $this->getMockBuilder(ilLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $controller = $this->getMockBuilder(ilCtrlInterface::class)
            ->getMock();

        $controller->method('getLinkTargetByClass')
            ->willReturn('something');

        $objectMock = $this->getMockBuilder(ilObject::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectMock->method('getTitle')
            ->willReturn('CourseTest');

        $objectHelper = $this->getMockBuilder(ilCertificateObjectHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectHelper->method('getInstanceByObjId')
            ->willReturn($objectMock);

        $provider = new ilUserCertificateTableProvider(
            $database,
            $logger,
            $controller,
            'default_title',
            $objectHelper
        );

        $dataSet = $provider->fetchDataSet(
            100,
            array('language' => 'de', 'limit' => 2, 'order_field' => 'date'),
            array()
        );

        $expected = array();

        $expected['items'][] = array(
            'id' => 600,
            'title' => 'CourseTest',
            'obj_id' => 100,
            'obj_type' => 'crs',
            'date' => 1539867618,
            'thumbnail_image_path' => 'some/path/test.svg',
            'description' => 'some description',
            'firstname' => 'ilyas',
            'lastname' => 'homer',
        );

        $expected['cnt'] = 5;

        $this->assertEquals($expected, $dataSet);
    }

    public function testFetchingDataWithInvalidOrderFieldWillResultInException() : void
    {
        $this->expectException(\InvalidArgumentException::class);

        $database = $this->getMockBuilder(ilDBInterface::class)->getMock();

        $database
            ->expects($this->atLeastOnce())
            ->method('quote');

        $database->method('fetchAssoc')
            ->willReturnOnConsecutiveCalls(
                array(
                    'id' => 600,
                    'obj_id' => 100,
                    'title' => 'CourseTest',
                    'obj_type' => 'crs',
                    'acquired_timestamp' => 1539867618
                ),
                null,
                array(
                    'cnt' => 5,
                ),
                null
            );

        $logger = $this->getMockBuilder(ilLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $controller = $this->getMockBuilder(ilCtrlInterface::class)
            ->getMock();

        $controller->method('getLinkTargetByClass')
            ->willReturn('something');

        $objectMock = $this->getMockBuilder(ilObject::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectMock->method('getTitle')
            ->willReturn('CourseTest');

        $objectHelper = $this->getMockBuilder(ilCertificateObjectHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectHelper->method('getInstanceByObjId')
            ->willReturn($objectMock);

        $provider = new ilUserCertificateTableProvider(
            $database,
            $logger,
            $controller,
            'default_title',
            $objectHelper
        );

        $dataSet = $provider->fetchDataSet(
            100,
            array('language' => 'de', 'limit' => 2, 'order_field' => 'something'),
            array()
        );

        $this->fail('Should never happen');
    }

    public function testFetchingDataWithEmptyOrderFieldWillResultInException() : void
    {
        $this->expectException(\InvalidArgumentException::class);

        $database = $this->getMockBuilder(ilDBInterface::class)->getMock();

        $database
            ->expects($this->atLeastOnce())
            ->method('quote');

        $database->method('fetchAssoc')
            ->willReturnOnConsecutiveCalls(
                array(
                    'id' => 600,
                    'obj_id' => 100,
                    'title' => 'CourseTest',
                    'obj_type' => 'crs',
                    'acquired_timestamp' => 1539867618
                ),
                null,
                array(
                    'cnt' => 5,
                ),
                null
            );

        $logger = $this->getMockBuilder(ilLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $controller = $this->getMockBuilder(ilCtrlInterface::class)
            ->getMock();

        $controller->method('getLinkTargetByClass')
            ->willReturn('something');

        $objectMock = $this->getMockBuilder(ilObject::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectMock->method('getTitle')
            ->willReturn('CourseTest');

        $objectHelper = $this->getMockBuilder(ilCertificateObjectHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectHelper->method('getInstanceByObjId')
            ->willReturn($objectMock);

        $provider = new ilUserCertificateTableProvider(
            $database,
            $logger,
            $controller,
            'default_title',
            $objectHelper
        );

        $dataSet = $provider->fetchDataSet(
            100,
            array('language' => 'de', 'limit' => 2, 'order_field' => false),
            array()
        );

        $this->fail('Should never happen');
    }

    public function testFetchingDataWithWrongOrderDirectionWillResultInException() : void
    {
        $this->expectException(\InvalidArgumentException::class);

        $database = $this->getMockBuilder(ilDBInterface::class)->getMock();

        $database
            ->expects($this->atLeastOnce())
            ->method('quote');

        $database->method('fetchAssoc')
            ->willReturnOnConsecutiveCalls(
                array(
                    'id' => 600,
                    'obj_id' => 100,
                    'title' => 'CourseTest',
                    'obj_type' => 'crs',
                    'acquired_timestamp' => 1539867618
                ),
                null,
                array(
                    'cnt' => 5,
                ),
                null
            );

        $logger = $this->getMockBuilder(ilLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $controller = $this->getMockBuilder(ilCtrlInterface::class)
            ->getMock();

        $controller->method('getLinkTargetByClass')
            ->willReturn('something');

        $objectMock = $this->getMockBuilder(ilObject::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectMock->method('getTitle')
            ->willReturn('CourseTest');

        $objectHelper = $this->getMockBuilder(ilCertificateObjectHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectHelper->method('getInstanceByObjId')
            ->willReturn($objectMock);

        $provider = new ilUserCertificateTableProvider(
            $database,
            $logger,
            $controller,
            'default_title',
            $objectHelper
        );

        $dataSet = $provider->fetchDataSet(
            600,
            array(
                'language' => 'de',
                'limit' => 2,
                'order_field' => 'date',
                'order_direction' => 'mac'
            ),
            array()
        );

        $this->fail('Should never happen');
    }

    public function testFetchingDataWithInvalidLimitParameterWillResultInException() : void
    {
        $this->expectException(\InvalidArgumentException::class);

        $database = $this->getMockBuilder(ilDBInterface::class)->getMock();

        $database
            ->expects($this->atLeastOnce())
            ->method('quote');

        $database->method('fetchAssoc')
            ->willReturnOnConsecutiveCalls(
                array(
                    'id' => 600,
                    'obj_id' => 100,
                    'title' => 'CourseTest',
                    'obj_type' => 'crs',
                    'acquired_timestamp' => 1539867618
                ),
                null,
                array(
                    'cnt' => 5,
                ),
                null
            );

        $logger = $this->getMockBuilder(ilLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $controller = $this->getMockBuilder(ilCtrlInterface::class)
            ->getMock();

        $controller->method('getLinkTargetByClass')
            ->willReturn('something');

        $objectMock = $this->getMockBuilder(ilObject::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectMock->method('getTitle')
            ->willReturn('CourseTest');

        $objectHelper = $this->getMockBuilder(ilCertificateObjectHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectHelper->method('getInstanceByObjId')
            ->willReturn($objectMock);

        $provider = new ilUserCertificateTableProvider(
            $database,
            $logger,
            $controller,
            'default_title',
            $objectHelper
        );

        $dataSet = $provider->fetchDataSet(
            600,
            array(
                'language' => 'de',
                'limit' => 'something',
                'order_field' => 'date',
                'order_direction' => 'mac'
            ),
            array()
        );

        $this->fail('Should never happen');
    }

    public function testFetchingDataWithInvalidOffsetParameterWillResultInException() : void
    {
        $this->expectException(\InvalidArgumentException::class);

        $database = $this->getMockBuilder(ilDBInterface::class)->getMock();

        $database
            ->expects($this->atLeastOnce())
            ->method('quote');

        $database->method('fetchAssoc')
            ->willReturnOnConsecutiveCalls(
                array(
                    'id' => 600,
                    'obj_id' => 100,
                    'title' => 'CourseTest',
                    'obj_type' => 'crs',
                    'acquired_timestamp' => 1539867618
                ),
                null,
                array(
                    'cnt' => 5,
                ),
                null
            );

        $logger = $this->getMockBuilder(ilLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $controller = $this->getMockBuilder(ilCtrlInterface::class)
            ->getMock();

        $controller->method('getLinkTargetByClass')
            ->willReturn('something');

        $objectMock = $this->getMockBuilder(ilObject::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectMock->method('getTitle')
            ->willReturn('CourseTest');

        $objectHelper = $this->getMockBuilder(ilCertificateObjectHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectHelper->method('getInstanceByObjId')
            ->willReturn($objectMock);

        $provider = new ilUserCertificateTableProvider(
            $database,
            $logger,
            $controller,
            'default_title',
            $objectHelper
        );

        $dataSet = $provider->fetchDataSet(
            600,
            array(
                'limit' => 3,
                'language' => 'de',
                'order_field' => 'date',
                'order_direction' => 'mac',
                'offset' => 'something'
            ),
            array()
        );

        $this->fail('Should never happen');
    }
}
