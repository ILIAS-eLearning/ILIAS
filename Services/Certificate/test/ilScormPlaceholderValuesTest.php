<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilScormPlaceholderValuesTest extends PHPUnit_Framework_TestCase
{
	public function testGetPlaceholderValues()
	{
		$defaultPlaceholderValues = $this->getMockBuilder('ilDefaultPlaceholderValues')
			->disableOriginalConstructor()
			->getMock();

		$language = $this->getMockBuilder('ilLanguage')
			->disableOriginalConstructor()
			->getMock();

		$language->method('txt')
			->willReturnCallback(function ($variableValue) {
				if ($variableValue === 'lang_sep_decimal') {
					return ',';
				} elseif ($variableValue === 'lang_sep_thousand') {
					return '.';
				}

				return 'Some Translation: ' . $variableValue;
			});

		$language->expects($this->once())
			->method('loadLanguageModule');

		$dateHelper = $this->getMockBuilder('ilCertificateDateHelper')
			->getMock();

		$objectMock = $this->getMockBuilder('ilObject')
			->disableOriginalConstructor()
			->setMethods(array('getPointsInPercent', 'getMaxPoints', 'getTitle', 'getId'))
			->getMock();

		$objectMock->method('getPointsInPercent')
			->willReturn(100);

		$objectMock->method('getMaxPoints')
			->willReturn(100);

		$objectMock->method('getTitle')
			->willReturn('SomeTitle');

		$objectMock->method('getId')
			->willReturn(500);

		$objectHelper = $this->getMockBuilder('ilCertificateObjectHelper')
			->getMock();

		$objectHelper->method('getInstanceByObjId')
			->willReturn($objectMock);

		$utilHelper = $this->getMockBuilder('ilCertificateUtilHelper')
			->getMock();

		$utilHelper->method('prepareFormOutput')
			->willReturn('Formatted String');

		$objectLPHelper = $this->getMockBuilder('ilCertificateObjectLPHelper')
			->getMock();

		$lpCollection = $this->getMockBuilder('ilLPCollection')
			->disableOriginalConstructor()
			->setMethods(array('getPossibleItems', 'getScoresForUserAndCP_Node_Id', 'isAssignedEntry'))
			->getMock();

		$lpCollection->method('getPossibleItems')
			->willReturn(array(100 => array('title' => 'Some Title')));

		$lpCollection->method('getScoresForUserAndCP_Node_Id')
			->willReturn(
				array(
					'raw' => 100,
					'max' => 300,
					'scaled' => 2
				)
			);

		$lpCollection->method('isAssignedEntry')
			->willReturn(true);

		$olp = $this->getMockBuilder('ilObjectLP')
			->disableOriginalConstructor()
			->setMethods(array('getCollectionInstance'))
			->getMock();

		$olp->method('getCollectionInstance')
			->willReturn($lpCollection);

		$objectLPHelper->method('getInstance')
			->willReturn($olp);

		$lpStatusHelper = $this->getMockBuilder('ilCertificateLPStatusHelper')
			->getMock();

		$lpStatusHelper->method('lookupStatusChanged')
			->willReturn('2018-12-01 13:00:11');

		$scormPlaceholderValues = new ilScormPlaceholderValues(
			$defaultPlaceholderValues,
			$language,
			$dateHelper,
			$objectHelper,
			$utilHelper,
			$objectLPHelper,
			$lpStatusHelper
		);

		$result = $scormPlaceholderValues->getPlaceholderValues(10, 200);

		$this->assertEquals(
			array(
				'SCORM_TITLE' => 'Formatted String',
				'SCORM_POINTS' => '100,0 %',
				'SCORM_POINTS_MAX' => 100,
				'SCO_T_0' => 'Some Title',
				'SCO_P_0' => '100,0',
				'SCO_PM_0' => '300,0',
				'SCO_PP_0' => '200,0 %'
			),
			$result
		);
	}

	public function getPlaceholderValuesForPreview()
	{
		$defaultPlaceholderValues = $this->getMockBuilder('ilDefaultPlaceholderValues')
			->disableOriginalConstructor()
			->getMock();

		$defaultPlaceholderValues->method('getPlaceholderValuesForPreview')
			->willReturn(
				array(
					'SOME_PLACEHOLDER' => 'aaa',
					'SOME_OTHER_PLACEHOLDER' => 'bbb'
				)
			);

		$language = $this->getMockBuilder('ilLanguage')
			->disableOriginalConstructor()
			->getMock();

		$dateHelper = $this->getMockBuilder('ilCertificateDateHelper')
			->getMock();

		$objectHelper = $this->getMockBuilder('ilCertificateObjectHelper')
			->getMock();

		$utilHelper = $this->getMockBuilder('ilCertificateUtilHelper')
			->getMock();

		$objectLPHelper = $this->getMockBuilder('ilCertificateObjectLPHelper')
			->getMock();

		$lpStatusHelper = $this->getMockBuilder('ilCertificateLPStatusHelper')
			->getMock();

		$scormPlaceholderValues = new ilScormPlaceholderValues(
			$defaultPlaceholderValues,
			$language,
			$dateHelper,
			$objectHelper,
			$utilHelper,
			$objectLPHelper,
			$lpStatusHelper
		);

		$result = $scormPlaceholderValues->getPlaceholderValuesForPreview();

		$this->assertEquals(
			array(
				'SOME_PLACEHOLDER' => 'aaa',
				'SOME_OTHER_PLACEHOLDER' => 'bbb'
			),
			$result
		);
	}
}
