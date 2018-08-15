<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTermsOfServiceDocumentCriteriaEvaluationTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceDocumentCriteriaEvaluationTest extends \ilTermsOfServiceBaseTest
{
	/**
	 * @var bool
	 */
	protected $backupGlobals = false;

	/**
	 * @inheritdoc
	 */
	public function setUp()
	{
		parent::setUp();
	}

	/**
	 * @return PHPUnit_Framework_MockObject_MockObject|\ilObjUser
	 */
	protected function getUserMock()
	{
		$user = $this
			->getMockBuilder(\ilObjUser::class)
			->disableOriginalConstructor()
			->setMethods(['getLanguage', 'getId', 'getLogin'])
			->getMock();

		$user
			->expects($this->any())
			->method('getId')
			->willReturn(-1);

		$user
			->expects($this->any())
			->method('getLogin')
			->willReturn('phpunit');

		return $user;
	}

	/**
	 * @return PHPUnit_Framework_MockObject_MockObject|\ilTermsOfServiceCriterionTypeFactoryInterface
	 */
	protected function getCriterionTypeFactoryMock()
	{
		$critTypeFactory = $this
			->getMockBuilder(\ilTermsOfServiceCriterionTypeFactoryInterface::class)
			->getMock();

		return $critTypeFactory;
	}

	/**
	 * @param string $typeIdent
	 * @return PHPUnit_Framework_MockObject_MockObject|\ilTermsOfServiceCriterionType
	 */
	protected function getCriterionTypeMock(string $typeIdent)
	{
		$criterionType = $this
			->getMockBuilder(\ilTermsOfServiceCriterionType::class)
			->getMock();

		$criterionType
			->expects($this->any())
			->method('getTypeIdent')
			->willReturn($typeIdent);

		return $criterionType;
	}

	/**
	 * @param ilTermsOfServiceCriterionType $criterionType
	 * @return PHPUnit_Framework_MockObject_MockObject|\ilTermsOfServiceEvaluableCriterion
	 */
	protected function getCriterionAssignmentMock(\ilTermsOfServiceCriterionType $criterionType): \ilTermsOfServiceEvaluableCriterion
	{
		$criterionAssignment = $this
			->getMockBuilder(\ilTermsOfServiceEvaluableCriterion::class)
			->getMock();

		$criterionAssignment
			->expects($this->any())
			->method('getCriterionId')
			->willReturn($criterionType->getTypeIdent());

		return $criterionAssignment;
	}

	/**
	 * 
	 */
	public function testLogicalAndEvaluatorReturnsTrueIfNoCriterionIsAssignedAtAll()
	{
		$user = $this->getUserMock();
		$critTypeFactory = $this->getCriterionTypeFactoryMock();

		$doc = $this
			->getMockBuilder(\ilTermsOfServiceSignableDocument::class)
			->getMock();

		$doc
			->expects($this->once())
			->method('getCriteria')
			->willReturn([]);

		$evaluator = new \ilTermsOfServiceLogicalAndDocumentCriteriaEvaluation($critTypeFactory, $user);

		$this->assertTrue($evaluator->evaluate($doc));
	}

	/**
	 *
	 */
	public function testLogicalAndEvaluatorReturnsTrueIfAllCriteriaMatch()
	{
		$user = $this->getUserMock();
		$criterionTypeFactory = $this->getCriterionTypeFactoryMock();

		$criterionType1 = $this->getCriterionTypeMock('dummy1');
		$criterionAssignment1 = $this->getCriterionAssignmentMock($criterionType1);

		$criterionType2 = $this->getCriterionTypeMock('dummy2');
		$criterionAssignment2 = $this->getCriterionAssignmentMock($criterionType2);

		$criterionType3 = $this->getCriterionTypeMock('dummy3');
		$criterionAssignment3 = $this->getCriterionAssignmentMock($criterionType3);

		$criterionType1
			->expects($this->once())
			->method('evaluate')
			->with($user)
			->willReturn(true);

		$criterionType2
			->expects($this->once())
			->method('evaluate')
			->with($user)
			->willReturn(true);

		$criterionType3
			->expects($this->once())
			->method('evaluate')
			->with($user)
			->willReturn(true);

		$doc = $this
			->getMockBuilder(\ilTermsOfServiceSignableDocument::class)
			->getMock();

		$doc
			->expects($this->once())
			->method('getCriteria')
			->willReturn([
				$criterionAssignment1,
				$criterionAssignment2,
				$criterionAssignment3
			]);


		$criterionTypeFactory
			->expects($this->exactly(3))
			->method('findByTypeIdent')
			->willReturnOnConsecutiveCalls(
				$criterionType1,
				$criterionType2,
				$criterionType3
			);

		$evaluator = new \ilTermsOfServiceLogicalAndDocumentCriteriaEvaluation($criterionTypeFactory, $user);

		$this->assertTrue($evaluator->evaluate($doc));
	}

	/**
	 *
	 */
	public function testLogicalAndEvaluatorReturnsFalseIfAnyCriteriaDoesNotMatch()
	{
		$user = $this->getUserMock();
		$criterionTypeFactory = $this->getCriterionTypeFactoryMock();

		$criterionType1 = $this->getCriterionTypeMock('dummy1');
		$criterionAssignment1 = $this->getCriterionAssignmentMock($criterionType1);

		$criterionType2 = $this->getCriterionTypeMock('dummy2');
		$criterionAssignment2 = $this->getCriterionAssignmentMock($criterionType2);

		$criterionType3 = $this->getCriterionTypeMock('dummy3');
		$criterionAssignment3 = $this->getCriterionAssignmentMock($criterionType3);

		$criterionType1
			->expects($this->once())
			->method('evaluate')
			->with($user)
			->willReturn(true);

		$criterionType2
			->expects($this->once())
			->method('evaluate')
			->with($user)
			->willReturn(false);

		$criterionType3
			->expects($this->never())
			->method('evaluate')
			->with($user)
			->willReturn(true);

		$doc = $this
			->getMockBuilder(\ilTermsOfServiceSignableDocument::class)
			->getMock();

		$doc
			->expects($this->once())
			->method('getCriteria')
			->willReturn([
				$criterionAssignment1,
				$criterionAssignment2,
				$criterionAssignment3
			]);


		$criterionTypeFactory
			->expects($this->exactly(2))
			->method('findByTypeIdent')
			->willReturnOnConsecutiveCalls(
				$criterionType1,
				$criterionType2,
				$criterionType3
			);

		$evaluator = new \ilTermsOfServiceLogicalAndDocumentCriteriaEvaluation($criterionTypeFactory, $user);

		$this->assertFalse($evaluator->evaluate($doc));
	}
}