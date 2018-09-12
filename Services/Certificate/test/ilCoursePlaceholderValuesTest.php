<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCoursePlaceholderValuesTest extends PHPUnit_Framework_TestCase
{
	public function testGetPlaceholderValues()
	{
		 $defaultPlaceholderValues = $this->getMockBuilder('ilDefaultPlaceholderValues')
			 ->getMock();

		 $defaultPlaceholderValues->method('getPlaceholderValues')
			 ->willReturn(array());

		$language = $this->getMockBuilder('ilLanguage')
			->disableOriginalConstructor()
			->getMock();

		$objectMock = $this->getMockBuilder('ilObject')
			->disableOriginalConstructor()
			->getMock();

		$objectMock->method('getTitle')
			->willReturn('Some Title');

		$objectHelper = $this->getMockBuilder('ilCertificateObjectHelper')
			->getMock();

		$objectHelper->method('getInstanceByObjId')
			->willReturn($objectMock);

		$participantsHelper = $this->getMockBuilder('ilCertificateParticipantsHelper')
			->getMock();

		$participantsHelper->method('getDateTimeOfPassed')
			->willReturn('2018-09-10');

		$ilUtilHelper = $this->getMockBuilder('ilCertificateUtilHelper')
			->getMock();

		$ilUtilHelper->method('prepareFormOutput')
			->willReturn('Some Title');

		$valuesObject = new ilCoursePlaceholderValues(
			$defaultPlaceholderValues,
			$language,
			$objectHelper,
			$participantsHelper,
			$ilUtilHelper
		);

		$placeholderValues = $valuesObject->getPlaceholderValues(100, 200);

		$this->assertEquals(
			array(
				'COURSE_TITLE'       => 'Some Title'
			),
			$placeholderValues);
	}

	public function testGetPreviewPlaceholderValues()
	{
		$defaultPlaceholderValues = $this->getMockBuilder('ilDefaultPlaceholderValues')
			->getMock();

		$defaultPlaceholderValues->method('getPlaceholderValuesForPreview')
			->willReturn(
				array(
					'SOME_PLACEHOLDER'       => 'ANYTHING',
					'SOME_OTHER_PLACEHOLDER'  => '2018-09-10',
				)
			);

		$language = $this->getMockBuilder('ilLanguage')
			->disableOriginalConstructor()
			->getMock();

		$objectHelper = $this->getMockBuilder('ilCertificateObjectHelper')
			->getMock();

		$participantsHelper = $this->getMockBuilder('ilCertificateParticipantsHelper')
			->getMock();

		$ilUtilHelper = $this->getMockBuilder('ilCertificateUtilHelper')
			->getMock();

		$valuesObject = new ilCoursePlaceholderValues(
			$defaultPlaceholderValues,
			$language,
			$objectHelper,
			$participantsHelper,
			$ilUtilHelper
		);

		$placeholderValues = $valuesObject->getPlaceholderValuesForPreview();

		$this->assertEquals(
			array(
				'SOME_PLACEHOLDER'        => 'ANYTHING',
				'SOME_OTHER_PLACEHOLDER'  => '2018-09-10',
			),
			$placeholderValues
		);
	}
}
