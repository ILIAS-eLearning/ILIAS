<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTermsOfServiceNullCriterionTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceNullCriterionTest extends \ilTermsOfServiceCriterionBaseTest
{
	/**
	 * @var PHPUnit_Framework_MockObject_MockObject|\ilLanguage
	 */
	protected $lng;

	/**
	 * @inheritDoc
	 */
	public function setUp()
	{
		parent::setUp();

		$this->lng = $this->getLanguageMock();

		$this->lng
			->expects($this->any())
			->method('txt')
			->willReturn('dummy');
	}

	/**
	 * @return \ilTermsOfServiceNullCriterion
	 */
	protected function getInstance(): \ilTermsOfServiceNullCriterion
	{
		$criterion = new \ilTermsOfServiceNullCriterion();

		return $criterion;
	}

	/**
	 * @return \ilTermsOfServiceNullCriterion
	 */
	public function testInstanceCanBeCreated(): \ilTermsOfServiceNullCriterion
	{
		$criterion = $this->getInstance();

		$this->assertEquals('null', $criterion->getTypeIdent());

		return $criterion;
	}

	/**
	 * @param \ilTermsOfServiceCriterionTypeGUI $gui
	 * @return PHPUnit_Framework_MockObject_MockObject|\ilPropertyFormGUI
	 */
	protected function buildForm(
		\ilTermsOfServiceCriterionTypeGUI $gui
	): \ilPropertyFormGUI {
		$form = $this->getFormMock();

		$radioGroup = $this
			->getMockBuilder(\ilRadioGroupInputGUI::class)
			->disableOriginalConstructor()
			->setMethods(['getPostVar', 'addOption'])
			->getMock();

		$form->addItem($radioGroup);

		$radioGroup
			->expects($this->never())
			->method('addOption');

		$gui->appendOption($radioGroup, new \ilTermsOfServiceCriterionConfig([]));

		return $form;
	}

	/**
	 * @param \ilTermsOfServiceNullCriterion $criterion
	 * @depends testInstanceCanBeCreated
	 * @return \ilTermsOfServiceNullCriterion
	 */
	public function testNoFormUserInterfaceElementsAreBuilt(\ilTermsOfServiceNullCriterion $criterion)
	{
		$gui = $criterion->getGUI($this->lng);

		$this->buildForm($gui);

		return $criterion;
	}

	/**
	 * @depends testNoFormUserInterfaceElementsAreBuilt
	 * @param \ilTermsOfServiceNullCriterion $criterion
	 */
	public function testCriterionAlwaysCreateEmptyConfigValue(\ilTermsOfServiceNullCriterion $criterion)
	{
		$gui = $criterion->getGUI($this->lng);

		$form = $this->buildForm($gui);

		$form
			->expects($this->never())
			->method('getInput');

		$value = $gui->getConfigByForm($form);

		$this->assertInstanceOf(\ilTermsOfServiceCriterionConfig::class, $value);
		$this->assertEquals(new \ilTermsOfServiceCriterionConfig([]), $value);
	}

	/**
	 *
	 */
	public function testEvaluationAlwaysSucceeds()
	{
		$user = $this->getUserMock();
		$criterion = $this->getInstance();

		$this->assertTrue($criterion->evaluate($user, new \ilTermsOfServiceCriterionConfig([])));
	}
}