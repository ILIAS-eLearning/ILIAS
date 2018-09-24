<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilUserCertificateTableProviderTest extends PHPUnit_Framework_TestCase
{
	public function testFetchingDataSetForTableWithoutParamtersAndWithoutFilters()
	{
		$database = $this->getMockBuilder('ilDBInterface')
			->disableOriginalConstructor()
			->getMock();

		$database
			->expects($this->once())
			->method('quote');

		$database->method('fetchAssoc')
			->willReturnOnConsecutiveCalls(
				array(
					'obj_id' => 100,
					'acquired_timestamp' => 1234567890
				),
				null
			);

		$logger = $this->getMockBuilder('ilLogger')
			->disableOriginalConstructor()
			->getMock();

		$logger
			->expects($this->once())
			->method('info');

		$controller = $this->getMockBuilder('ilCtrl')
			->getMock();

		$controller->method('getLinkTargetByClass')
			->willReturn('something');

		$objectMock = $this->getMockBuilder('ilObject')
			->disableOriginalConstructor()
			->getMock();

		$objectMock->method('getTitle')
			->willReturn('CourseTest');

		$objectHelper = $this->getMockBuilder('ilCertificateObjectHelper')
			->disableOriginalConstructor()
			->getMock();

		$objectHelper->method('getInstanceByObjId')
			->willReturn($objectMock);

		$dateHelper = $this->getMockBuilder('ilCertificateDateHelper')
			->disableOriginalConstructor()
			->getMock();

		$dateHelper->method('formatDate')
			->willReturn('2018-09-21');

		$provider = new ilUserCertificateTableProvider(
			$database,
			$logger,
			$controller,
			$objectHelper,
			$dateHelper,
			1
		);

		$dataSet = $provider->fetchDataSet(100, array(), array());

		$expected = array();

		$expected['items'][] = array(
			'id' => 100,
			'title' => 'CourseTest',
			'date' => '2018-09-21',
			'action' => 'something'
		);

		$this->assertEquals($expected, $dataSet);
	}

	public function testFetchingDataSetForTableWithLimitParamterAndWithoutFilters()
	{
		$database = $this->getMockBuilder('ilDBInterface')
			->disableOriginalConstructor()
			->getMock();

		$database
			->expects($this->atLeastOnce())
			->method('quote');

		$database->method('fetchAssoc')
			->willReturnOnConsecutiveCalls(
				array(
					'obj_id' => 100,
					'acquired_timestamp' => 1234567890
				),
				null,
				array(
					'cnt' => 5,
				),
				null
			);

		$logger = $this->getMockBuilder('ilLogger')
			->disableOriginalConstructor()
			->getMock();

		$logger
			->expects($this->atLeastOnce())
			->method('info');

		$controller = $this->getMockBuilder('ilCtrl')
			->getMock();

		$controller->method('getLinkTargetByClass')
			->willReturn('something');

		$objectMock = $this->getMockBuilder('ilObject')
			->disableOriginalConstructor()
			->getMock();

		$objectMock->method('getTitle')
			->willReturn('CourseTest');

		$objectHelper = $this->getMockBuilder('ilCertificateObjectHelper')
			->disableOriginalConstructor()
			->getMock();

		$objectHelper->method('getInstanceByObjId')
			->willReturn($objectMock);

		$dateHelper = $this->getMockBuilder('ilCertificateDateHelper')
			->disableOriginalConstructor()
			->getMock();

		$dateHelper->method('formatDate')
			->willReturn('2018-09-21');

		$provider = new ilUserCertificateTableProvider(
			$database,
			$logger,
			$controller,
			$objectHelper,
			$dateHelper,
			1
		);

		$dataSet = $provider->fetchDataSet(100, array('limit' => 2), array());

		$expected = array();

		$expected['items'][] = array(
			'id' => 100,
			'title' => 'CourseTest',
			'date' => '2018-09-21',
			'action' => 'something'
		);

		$expected['cnt'] = 5;

		$this->assertEquals($expected, $dataSet);
	}

	public function testFetchingDataSetForTableWithOrderFieldDate()
	{
		$database = $this->getMockBuilder('ilDBInterface')
			->disableOriginalConstructor()
			->getMock();

		$database
			->expects($this->atLeastOnce())
			->method('quote');

		$database->method('fetchAssoc')
			->willReturnOnConsecutiveCalls(
				array(
					'obj_id' => 100,
					'acquired_timestamp' => 1234567890
				),
				null,
				array(
					'cnt' => 5,
				),
				null
			);

		$logger = $this->getMockBuilder('ilLogger')
			->disableOriginalConstructor()
			->getMock();

		$logger
			->expects($this->atLeastOnce())
			->method('info');

		$controller = $this->getMockBuilder('ilCtrl')
			->getMock();

		$controller->method('getLinkTargetByClass')
			->willReturn('something');

		$objectMock = $this->getMockBuilder('ilObject')
			->disableOriginalConstructor()
			->getMock();

		$objectMock->method('getTitle')
			->willReturn('CourseTest');

		$objectHelper = $this->getMockBuilder('ilCertificateObjectHelper')
			->disableOriginalConstructor()
			->getMock();

		$objectHelper->method('getInstanceByObjId')
			->willReturn($objectMock);

		$dateHelper = $this->getMockBuilder('ilCertificateDateHelper')
			->disableOriginalConstructor()
			->getMock();

		$dateHelper->method('formatDate')
			->willReturn('2018-09-21');

		$provider = new ilUserCertificateTableProvider(
			$database,
			$logger,
			$controller,
			$objectHelper,
			$dateHelper,
			1
		);

		$dataSet = $provider->fetchDataSet(100, array('limit' => 2, 'order_field' => 'date'), array());

		$expected = array();

		$expected['items'][] = array(
			'id' => 100,
			'title' => 'CourseTest',
			'date' => '2018-09-21',
			'action' => 'something'
		);

		$expected['cnt'] = 5;

		$this->assertEquals($expected, $dataSet);
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testFetchingDataWithInvalidOrderFieldWillResultInException()
	{
		$database = $this->getMockBuilder('ilDBInterface')
			->disableOriginalConstructor()
			->getMock();

		$database
			->expects($this->atLeastOnce())
			->method('quote');

		$database->method('fetchAssoc')
			->willReturnOnConsecutiveCalls(
				array(
					'obj_id' => 100,
					'acquired_timestamp' => 1234567890
				),
				null,
				array(
					'cnt' => 5,
				),
				null
			);

		$logger = $this->getMockBuilder('ilLogger')
			->disableOriginalConstructor()
			->getMock();

		$logger
			->expects($this->atLeastOnce())
			->method('info');

		$controller = $this->getMockBuilder('ilCtrl')
			->getMock();

		$controller->method('getLinkTargetByClass')
			->willReturn('something');

		$objectMock = $this->getMockBuilder('ilObject')
			->disableOriginalConstructor()
			->getMock();

		$objectMock->method('getTitle')
			->willReturn('CourseTest');

		$objectHelper = $this->getMockBuilder('ilCertificateObjectHelper')
			->disableOriginalConstructor()
			->getMock();

		$objectHelper->method('getInstanceByObjId')
			->willReturn($objectMock);

		$dateHelper = $this->getMockBuilder('ilCertificateDateHelper')
			->disableOriginalConstructor()
			->getMock();

		$dateHelper->method('formatDate')
			->willReturn('2018-09-21');

		$provider = new ilUserCertificateTableProvider(
			$database,
			$logger,
			$controller,
			$objectHelper,
			$dateHelper,
			1
		);

		$dataSet = $provider->fetchDataSet(100, array('limit' => 2, 'order_field' => 'something'), array());

		$this->fail('Should never happen');
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testFetchingDataWithEmptyOrderFieldWillResultInException()
	{
		$database = $this->getMockBuilder('ilDBInterface')
			->disableOriginalConstructor()
			->getMock();

		$database
			->expects($this->atLeastOnce())
			->method('quote');

		$database->method('fetchAssoc')
			->willReturnOnConsecutiveCalls(
				array(
					'obj_id' => 100,
					'acquired_timestamp' => 1234567890
				),
				null,
				array(
					'cnt' => 5,
				),
				null
			);

		$logger = $this->getMockBuilder('ilLogger')
			->disableOriginalConstructor()
			->getMock();

		$logger
			->expects($this->atLeastOnce())
			->method('info');

		$controller = $this->getMockBuilder('ilCtrl')
			->getMock();

		$controller->method('getLinkTargetByClass')
			->willReturn('something');

		$objectMock = $this->getMockBuilder('ilObject')
			->disableOriginalConstructor()
			->getMock();

		$objectMock->method('getTitle')
			->willReturn('CourseTest');

		$objectHelper = $this->getMockBuilder('ilCertificateObjectHelper')
			->disableOriginalConstructor()
			->getMock();

		$objectHelper->method('getInstanceByObjId')
			->willReturn($objectMock);

		$dateHelper = $this->getMockBuilder('ilCertificateDateHelper')
			->disableOriginalConstructor()
			->getMock();

		$dateHelper->method('formatDate')
			->willReturn('2018-09-21');

		$provider = new ilUserCertificateTableProvider(
			$database,
			$logger,
			$controller,
			$objectHelper,
			$dateHelper,
			1
		);

		$dataSet = $provider->fetchDataSet(100, array('limit' => 2, 'order_field' => false), array());

		$this->fail('Should never happen');
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testFetchingDataWithWrongOrderDirectionWillResultInException()
	{
		$database = $this->getMockBuilder('ilDBInterface')
			->disableOriginalConstructor()
			->getMock();

		$database
			->expects($this->atLeastOnce())
			->method('quote');

		$database->method('fetchAssoc')
			->willReturnOnConsecutiveCalls(
				array(
					'obj_id' => 100,
					'acquired_timestamp' => 1234567890
				),
				null,
				array(
					'cnt' => 5,
				),
				null
			);

		$logger = $this->getMockBuilder('ilLogger')
			->disableOriginalConstructor()
			->getMock();

		$logger
			->expects($this->atLeastOnce())
			->method('info');

		$controller = $this->getMockBuilder('ilCtrl')
			->getMock();

		$controller->method('getLinkTargetByClass')
			->willReturn('something');

		$objectMock = $this->getMockBuilder('ilObject')
			->disableOriginalConstructor()
			->getMock();

		$objectMock->method('getTitle')
			->willReturn('CourseTest');

		$objectHelper = $this->getMockBuilder('ilCertificateObjectHelper')
			->disableOriginalConstructor()
			->getMock();

		$objectHelper->method('getInstanceByObjId')
			->willReturn($objectMock);

		$dateHelper = $this->getMockBuilder('ilCertificateDateHelper')
			->disableOriginalConstructor()
			->getMock();

		$dateHelper->method('formatDate')
			->willReturn('2018-09-21');

		$provider = new ilUserCertificateTableProvider(
			$database,
			$logger,
			$controller,
			$objectHelper,
			$dateHelper,
			1
		);

		$dataSet = $provider->fetchDataSet(
			100,
			array(
				'limit' => 2,
				'order_field' => 'date',
				'order_direction' => 'mac'
			),
			array()
		);

		$this->fail('Should never happen');
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testFetchingDataWithInvalidLimitParameterWillResultInException()
	{
		$database = $this->getMockBuilder('ilDBInterface')
			->disableOriginalConstructor()
			->getMock();

		$database
			->expects($this->atLeastOnce())
			->method('quote');

		$database->method('fetchAssoc')
			->willReturnOnConsecutiveCalls(
				array(
					'obj_id' => 100,
					'acquired_timestamp' => 1234567890
				),
				null,
				array(
					'cnt' => 5,
				),
				null
			);

		$logger = $this->getMockBuilder('ilLogger')
			->disableOriginalConstructor()
			->getMock();

		$logger
			->expects($this->atLeastOnce())
			->method('info');

		$controller = $this->getMockBuilder('ilCtrl')
			->getMock();

		$controller->method('getLinkTargetByClass')
			->willReturn('something');

		$objectMock = $this->getMockBuilder('ilObject')
			->disableOriginalConstructor()
			->getMock();

		$objectMock->method('getTitle')
			->willReturn('CourseTest');

		$objectHelper = $this->getMockBuilder('ilCertificateObjectHelper')
			->disableOriginalConstructor()
			->getMock();

		$objectHelper->method('getInstanceByObjId')
			->willReturn($objectMock);

		$dateHelper = $this->getMockBuilder('ilCertificateDateHelper')
			->disableOriginalConstructor()
			->getMock();

		$dateHelper->method('formatDate')
			->willReturn('2018-09-21');

		$provider = new ilUserCertificateTableProvider(
			$database,
			$logger,
			$controller,
			$objectHelper,
			$dateHelper,
			1
		);

		$dataSet = $provider->fetchDataSet(
			100,
			array(
				'limit' => 'something',
				'order_field' => 'date',
				'order_direction' => 'mac'
			),
			array()
		);

		$this->fail('Should never happen');
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testFetchingDataWithInvalidOffsetParameterWillResultInException()
	{
		$database = $this->getMockBuilder('ilDBInterface')
			->disableOriginalConstructor()
			->getMock();

		$database
			->expects($this->atLeastOnce())
			->method('quote');

		$database->method('fetchAssoc')
			->willReturnOnConsecutiveCalls(
				array(
					'obj_id' => 100,
					'acquired_timestamp' => 1234567890
				),
				null,
				array(
					'cnt' => 5,
				),
				null
			);

		$logger = $this->getMockBuilder('ilLogger')
			->disableOriginalConstructor()
			->getMock();

		$logger
			->expects($this->atLeastOnce())
			->method('info');

		$controller = $this->getMockBuilder('ilCtrl')
			->getMock();

		$controller->method('getLinkTargetByClass')
			->willReturn('something');

		$objectMock = $this->getMockBuilder('ilObject')
			->disableOriginalConstructor()
			->getMock();

		$objectMock->method('getTitle')
			->willReturn('CourseTest');

		$objectHelper = $this->getMockBuilder('ilCertificateObjectHelper')
			->disableOriginalConstructor()
			->getMock();

		$objectHelper->method('getInstanceByObjId')
			->willReturn($objectMock);

		$dateHelper = $this->getMockBuilder('ilCertificateDateHelper')
			->disableOriginalConstructor()
			->getMock();

		$dateHelper->method('formatDate')
			->willReturn('2018-09-21');

		$provider = new ilUserCertificateTableProvider(
			$database,
			$logger,
			$controller,
			$objectHelper,
			$dateHelper,
			1
		);

		$dataSet = $provider->fetchDataSet(
			100,
			array(
				'limit' => 3,
				'order_field' => 'date',
				'order_direction' => 'mac',
				'offset' => 'something'
			),
			array()
		);

		$this->fail('Should never happen');
	}
}
