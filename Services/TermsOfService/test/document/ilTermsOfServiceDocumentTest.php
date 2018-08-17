<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTermsOfServiceDocumentTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceDocumentTest extends \ilTermsOfServiceCriterionBaseTest
{
	/**
	 * @return array
	 */
	public function criteriaAssignmentProvider(): array
	{
		$criterionAssignment1 = $this
			->getMockBuilder(\ilTermsOfServiceDocumentCriterionAssignment::class)
			->disableOriginalConstructor()
			->setMethods(['getId', 'getCriterionValue', 'getCriterionId'])
			->getMock();

		$criterionAssignment1
			->expects($this->any())
			->method('getId')
			->willReturn(1);

		$criterionAssignment1
			->expects($this->any())
			->method('getCriterionId')
			->willReturn('usr_global_role');

		$criterionAssignment1
			->expects($this->any())
			->method('getCriterionValue')
			->willReturn($this->getCriterionConfig(['role_id' => 4]));

		$criterionAssignment2 = $this
			->getMockBuilder(\ilTermsOfServiceDocumentCriterionAssignment::class)
			->disableOriginalConstructor()
			->setMethods(['getId', 'getCriterionValue', 'getCriterionId'])
			->getMock();

		$criterionAssignment2
			->expects($this->any())
			->method('getId')
			->willReturn(1);

		$criterionAssignment2
			->expects($this->any())
			->method('getCriterionId')
			->willReturn('usr_language');

		$criterionAssignment2
			->expects($this->any())
			->method('getCriterionValue')
			->willReturn($this->getCriterionConfig(['lng' => 'de']));

		$criterionAssignment3 = $this
			->getMockBuilder(\ilTermsOfServiceDocumentCriterionAssignment::class)
			->disableOriginalConstructor()
			->setMethods(['getId', 'getCriterionValue', 'getCriterionId'])
			->getMock();

		$criterionAssignment3
			->expects($this->any())
			->method('getId')
			->willReturn(0);

		$criterionAssignment3
			->expects($this->any())
			->method('getCriterionId')
			->willReturn('usr_global_role');

		$criterionAssignment3
			->expects($this->any())
			->method('getCriterionValue')
			->willReturn($this->getCriterionConfig(['role_id' => 6]));

		return [
			[$criterionAssignment1, $criterionAssignment2, $criterionAssignment3]
		];
	}

	/**
	 * @dataProvider criteriaAssignmentProvider
	 * @param ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment1
	 * @param ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment2
	 * @param ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment3
	 */
	public function testCriteriaCanBeAttached(
		\ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment1,
		\ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment2,
		\ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment3
	)
	{
		$document = new \ilTermsOfServiceDocument();
		$document->attachCriterion($criterionAssignment1);
		$document->attachCriterion($criterionAssignment2);
		$document->attachCriterion($criterionAssignment3);

		$this->assertCount(3, $document->getCriteria());
	}

	/**
	 * @dataProvider criteriaAssignmentProvider
	 * @param ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment1
	 * @param ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment2
	 * @param ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment3
	 */
	public function testCriteriaCanBeDetached(
		\ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment1,
		\ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment2,
		\ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment3
	)
	{
		$document = new \ilTermsOfServiceDocument();
		$document->attachCriterion($criterionAssignment1);
		$document->attachCriterion($criterionAssignment2);
		$document->attachCriterion($criterionAssignment3);

		$document->detachCriterion($criterionAssignment2);

		$this->assertCount(2, $document->getCriteria());
	}
}