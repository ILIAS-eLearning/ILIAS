<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTermsOfServiceAcceptanceEntityTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceDocumentEvaluationTest extends \ilTermsOfServiceBaseTest
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
	 * @return PHPUnit_Framework_MockObject_MockObject|\ilTermsOfServiceDocumentCriteriaEvaluation
	 */
	protected function getEvaluatorMock()
	{
		$evaluator = $this
			->getMockBuilder(\ilTermsOfServiceDocumentCriteriaEvaluation::class)
			->disableOriginalConstructor()
			->getMock();

		return $evaluator;
	}

	/**
	 * 
	 */
	public function testNoDocumentIsFoundIfNoDocumentIsProvidedAtAll()
	{
		$evaluator = $this->getEvaluatorMock();
		$user = $this->getUserMock();

		$evaluation = new \ilTermsOfServiceSequentialDocumentEvaluation(
			$evaluator,
			$user,
			[]
		);

		$this->assertFalse($evaluation->hasDocument());
	}

	/**
	 * @expectedException \ilTermsOfServiceNoSignableDocumentFoundException
	 */
	public function testExceptionIsRaisedIfADocumentIsRequestedAndNotDocumentExistsAtAll()
	{
		$evaluator = $this->getEvaluatorMock();
		$user = $this->getUserMock();

		$evaluation = new \ilTermsOfServiceSequentialDocumentEvaluation(
			$evaluator,
			$user,
			[]
		);

		$this->assertException(\ilTermsOfServiceNoSignableDocumentFoundException::class);

		$evaluation->getDocument();
	}

	/**
	 *
	 */
	public function testFirstDocumentIsReturnedIfFirstDocumentMatchesCriteria()
	{
		$evaluator = $this->getEvaluatorMock();
		$user = $this->getUserMock();

		$doc = $this
			->getMockBuilder(\ilTermsOfServiceSignableDocument::class)
			->getMock();

		$evaluator
			->expects($this->exactly(1))
			->method('evaluate')
			->with($doc)
			->willReturn(true);

		$evaluation = new \ilTermsOfServiceSequentialDocumentEvaluation(
			$evaluator,
			$user,
			[$doc]
		);

		$this->assertTrue($evaluation->hasDocument());
		$this->assertEquals($doc, $evaluation->getDocument());
	}

	/**
	 *
	 */
	public function testFirstMatchingDocumentIsReturnedIfOnlyOneDocumentMatches()
	{
		$evaluator = $this->getEvaluatorMock();
		$user = $this->getUserMock();

		$doc1 = $this
			->getMockBuilder(\ilTermsOfServiceSignableDocument::class)
			->getMock();

		$doc2 = $this
			->getMockBuilder(\ilTermsOfServiceSignableDocument::class)
			->getMock();

		$doc3 = $this
			->getMockBuilder(\ilTermsOfServiceSignableDocument::class)
			->getMock();

		$evaluator
			->expects($this->exactly(3))
			->method('evaluate')
			->withConsecutive(
				[$doc1],
				[$doc2],
				[$doc3]
			)
			->will($this->returnValueMap([
				[$doc1, false],
				[$doc2, true],
				[$doc3, false]
			]));

		$evaluation = new \ilTermsOfServiceSequentialDocumentEvaluation(
			$evaluator,
			$user,
			[$doc1, $doc2, $doc3]
		);

		$this->assertTrue($evaluation->hasDocument());
		$this->assertEquals($doc2, $evaluation->getDocument());
	}

	/**
	 *
	 */
	public function testFirstMatchingDocumentIsReturnedIfMultipleDocumentsMatch()
	{
		$evaluator = $this->getEvaluatorMock();
		$user = $this->getUserMock();

		$doc1 = $this
			->getMockBuilder(\ilTermsOfServiceSignableDocument::class)
			->getMock();

		$doc2 = $this
			->getMockBuilder(\ilTermsOfServiceSignableDocument::class)
			->getMock();

		$doc3 = $this
			->getMockBuilder(\ilTermsOfServiceSignableDocument::class)
			->getMock();

		$evaluator
			->expects($this->exactly(3))
			->method('evaluate')
			->withConsecutive(
				[$doc1],
				[$doc2],
				[$doc3]
			)
			->will($this->returnValueMap([
				[$doc1, false],
				[$doc2, true],
				[$doc3, true]
			]));

		$evaluation = new \ilTermsOfServiceSequentialDocumentEvaluation(
			$evaluator,
			$user,
			[$doc1, $doc2, $doc3]
		);

		$this->assertTrue($evaluation->hasDocument());
		$this->assertEquals($doc2, $evaluation->getDocument());
	}
}