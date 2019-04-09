<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateCourseLearningProgressEvaluationTest extends ilCertificateBaseTestCase
{
	public function testOnlyOneCourseIsCompletedOnLPChange()
	{

		$templateRepository = $this->getMockBuilder('ilCertificateTemplateRepository')
			->disableOriginalConstructor()
			->getMock();

		$templateRepository->method('fetchAllObjectIdsByType')
			->willReturn(
				array(
					5,
					6
				)
			);

		$setting = $this->getMockBuilder('ilSetting')
			->disableOriginalConstructor()
			->getMock();

		$setting
			->method('get')
			->withConsecutive(
				array('cert_subitems_5'),
				array('cert_subitems_6')
			)
			->willReturnOnConsecutiveCalls(
				'[10,20]',
				'[10,500]'
			);

		$objectHelper = $this->getMockBuilder('ilCertificateObjectHelper')
			->getMock();

		$objectHelper->method('lookupObjId')
			->withConsecutive(
				array(10),
				array(20),
				array(10),
				array(500)
			)
			->willReturnOnConsecutiveCalls(100, 200, 100, 500);

		$statusHelper = $this->getMockBuilder('ilCertificateLPStatusHelper')
			->getMock();

		$statusHelper->method('lookUpStatus')
			->withConsecutive(
				array(100),
				array(200),
				array(100),
				array(500)
			)
			->willReturnOnConsecutiveCalls(
				ilLPStatus::LP_STATUS_COMPLETED_NUM,
				ilLPStatus::LP_STATUS_COMPLETED_NUM,
				ilLPStatus::LP_STATUS_COMPLETED_NUM,
				ilLPStatus::LP_STATUS_IN_PROGRESS
			);

		$evaluation = new ilCertificateCourseLearningProgressEvaluation(
			$templateRepository,
			$setting,
			$objectHelper,
			$statusHelper
		);

		$completedCourses = $evaluation->evaluate(10, 200);

		$this->assertEquals(array(5), $completedCourses);
	}

	public function testAllCoursesAreCompletedOnLPChange()
	{

		$templateRepository = $this->getMockBuilder('ilCertificateTemplateRepository')
			->disableOriginalConstructor()
			->getMock();

		$templateRepository->method('fetchAllObjectIdsByType')
			->willReturn(
				array(
					5,
					6
				)
			);

		$setting = $this->getMockBuilder('ilSetting')
			->disableOriginalConstructor()
			->getMock();

		$setting
			->method('get')
			->withConsecutive(
				array('cert_subitems_5'),
				array('cert_subitems_6')
			)
			->willReturnOnConsecutiveCalls(
				'[10,20]',
				'[10,500]'
			);

		$objectHelper = $this->getMockBuilder('ilCertificateObjectHelper')
			->getMock();

		$objectHelper->method('lookupObjId')
			->withConsecutive(
				array(10),
				array(20),
				array(10),
				array(500)
			)
			->willReturnOnConsecutiveCalls(100, 200, 100, 500);

		$statusHelper = $this->getMockBuilder('ilCertificateLPStatusHelper')
			->getMock();

		$statusHelper->method('lookUpStatus')
			->withConsecutive(
				array(100),
				array(200),
				array(100),
				array(500)
			)
			->willReturnOnConsecutiveCalls(
				ilLPStatus::LP_STATUS_COMPLETED_NUM,
				ilLPStatus::LP_STATUS_COMPLETED_NUM,
				ilLPStatus::LP_STATUS_COMPLETED_NUM,
				ilLPStatus::LP_STATUS_COMPLETED_NUM
			);

		$evaluation = new ilCertificateCourseLearningProgressEvaluation(
			$templateRepository,
			$setting,
			$objectHelper,
			$statusHelper
		);

		$completedCourses = $evaluation->evaluate(10, 200);

		$this->assertEquals(array(5, 6), $completedCourses);
	}

	public function testNoSubitemDefinedForEvaluation()
	{

		$templateRepository = $this->getMockBuilder('ilCertificateTemplateRepository')
			->disableOriginalConstructor()
			->getMock();

		$templateRepository->method('fetchAllObjectIdsByType')
			->willReturn(
				array(
					5,
					6
				)
			);

		$setting = $this->getMockBuilder('ilSetting')
			->disableOriginalConstructor()
			->getMock();

		$setting
			->method('get')
			->withConsecutive(
				array('cert_subitems_5'),
				array('cert_subitems_6')
			)
			->willReturnOnConsecutiveCalls(
				false,
				false
			);

		$objectHelper = $this->getMockBuilder('ilCertificateObjectHelper')
			->getMock();

		$statusHelper = $this->getMockBuilder('ilCertificateLPStatusHelper')
			->getMock();

		$evaluation = new ilCertificateCourseLearningProgressEvaluation(
			$templateRepository,
			$setting,
			$objectHelper,
			$statusHelper
		);

		$completedCourses = $evaluation->evaluate(10, 200);

		$this->assertEquals(array(), $completedCourses);
	}
}
