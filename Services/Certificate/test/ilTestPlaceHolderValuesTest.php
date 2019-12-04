<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilTestPlaceholderValuesTest extends PHPUnit_Framework_TestCase
{
	public function testA()
	{
		$defaultPlaceholderValues = $this->getMockBuilder('ilDefaultPlaceholderValues')
			->disableOriginalConstructor()
			->getMock();

		$language = $this->getMockBuilder('ilLanguage')
			->disableOriginalConstructor()
			->getMock();

		$language->method('txt')
			->willReturn('Some Translation');

		$objectHelper = $this->getMockBuilder('ilCertificateObjectHelper')
			->getMock();

		$testObject = $this->getMockBuilder('ilObjTest')
			->disableOriginalConstructor()
			->getMock();

		$testObject->method('getActiveIdOfUser')
			->willReturn(999);

		$testObject->method('getTestResult')
			->willReturn(
				array(
					'test' => array(
						'passed' => true,
						'total_max_points' => 70,
						'total_reached_points' => 50
					)
				)
			);

		$testObject->method('getTestResult')
			->willReturn(array());


		$testObject->method('getTitle')
			->willReturn(' Some Title');

		$markSchema = $this->getMockBuilder('ASS_MarkSchema')
			->disableOriginalConstructor()
			->getMock();

		$matchingMark = $this->getMockBuilder('ASS_Mark')
			->getMock();

		$matchingMark->method('getShortName')
			->willReturn('aaa');

		$matchingMark->method('getOfficialName')
			->willReturn('bbb');

		$markSchema->method('getMatchingMark')
			->willReturn($matchingMark);

		$testObject->method('getMarkSchema')
			->willReturn($markSchema);

		$objectHelper->method('getInstanceByObjId')
			->willReturn($testObject);

		$testObjectHelper = $this->getMockBuilder('ilCertificateTestObjectHelper')
			->getMock();

		$userObjectHelper = $this->getMockBuilder('ilCertificateUserObjectHelper')
			->getMock();

		$userObjectHelper->method('lookupFields')
			->willReturn(array('usr_id' => 10));

		$lpStatusHelper = $this->getMockBuilder('ilCertificateLPStatusHelper')
			->getMock();

		$lpStatusHelper->method('lookupStatusChanged')
			->willReturn('2018-01-12');

		$utilHelper = $this->getMockBuilder('ilCertificateUtilHelper')
			->getMock();

		$utilHelper->method('prepareFormOutput')
			->willReturn('Formatted Output');

		$dateHelper = $this->getMockBuilder('ilCertificateDateHelper')
			->getMock();

		$dateHelper->method('formatDate')
			->willReturn('2018-01-12');

		$dateHelper->method('formatDateTime')
			->willReturn('2018-01-12 10:32:01');

		$placeholdervalues = new ilTestPlaceholderValues(
			$defaultPlaceholderValues,
			$language,
			$objectHelper,
			$testObjectHelper,
			$userObjectHelper,
			$lpStatusHelper,
			$utilHelper,
			$dateHelper
		);

		$result = $placeholdervalues->getPlaceholderValues(10, 200);

		$this->assertEquals(array(
			'RESULT_PASSED'      => 'Formatted Output',
			'RESULT_POINTS'      => 'Formatted Output',
			'RESULT_PERCENT'     => '71.43%',
			'MAX_POINTS'         => 'Formatted Output',
			'RESULT_MARK_SHORT'  => 'Formatted Output',
			'RESULT_MARK_LONG'   => 'Formatted Output',
			'TEST_TITLE'         => 'Formatted Output',
			'DATE_COMPLETED'     => '2018-01-12',
			'DATETIME_COMPLETED' => '2018-01-12 10:32:01'

		), $result);
	}

	public function testGetPlaceholderValuesForPreview()
	{
		$defaultPlaceholderValues = $this->getMockBuilder('ilDefaultPlaceholderValues')
			->disableOriginalConstructor()
			->getMock();

		$defaultPlaceholderValues->method('getPlaceholderValuesForPreview')
			->willReturn(
				array(
					'SOME_PLACEHOLDER' => 'something',
					'SOME_OTHER_PLACEHOLDER' => 'something else',
				)
			);

		$language = $this->getMockBuilder('ilLanguage')
			->disableOriginalConstructor()
			->getMock();

		$language->method('txt')
			->willReturn('Something');

		$objectMock = $this->getMockBuilder('ilObject')
			->disableOriginalConstructor()
			->getMock();

		$objectMock->method('getTitle')
			->willReturn('SomeTitle');

		$objectHelper = $this->getMockBuilder('ilCertificateObjectHelper')
			->getMock();

		$objectHelper->method('getInstanceByObjId')
			->willReturn($objectMock);

		$testObjectHelper = $this->getMockBuilder('ilCertificateTestObjectHelper')
			->getMock();

		$userObjectHelper = $this->getMockBuilder('ilCertificateUserObjectHelper')
			->getMock();

		$lpStatusHelper = $this->getMockBuilder('ilCertificateLPStatusHelper')
			->getMock();

		$utilHelper = $this->getMockBuilder('ilCertificateUtilHelper')
			->getMock();

		$utilHelper->method('prepareFormOutput')
			->willReturnCallback(function ($input) {
				return $input;
			});

		$dateHelper = $this->getMockBuilder('ilCertificateDateHelper')
			->getMock();

		$placeholdervalues = new ilTestPlaceholderValues(
			$defaultPlaceholderValues,
			$language,
			$objectHelper,
			$testObjectHelper,
			$userObjectHelper,
			$lpStatusHelper,
			$utilHelper,
			$dateHelper
		);

		$result = $placeholdervalues->getPlaceholderValuesForPreview(100, 10);

		$this->assertEquals(
			array(
				'SOME_PLACEHOLDER' => 'something',
				'SOME_OTHER_PLACEHOLDER' => 'something else',
				'RESULT_PASSED' => 'Something',
				'RESULT_POINTS' => 'Something',
				'RESULT_PERCENT' => 'Something',
				'MAX_POINTS' => 'Something',
				'RESULT_MARK_SHORT' => 'Something',
				'RESULT_MARK_LONG' => 'Something',
				'TEST_TITLE' => 'SomeTitle'
			),
			$result
		);
	}
}
