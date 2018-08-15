<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTermsOfServiceUserHasGlobalRoleCriterionTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceUserHasGlobalRoleCriterionTest extends \ilTermsOfServiceCriterionBaseTest
{
	/**
	 * @var PHPUnit_Framework_MockObject_MockObject|\ilRbacReview
	 */
	protected $rbacReview;

	/**
	 * @return \ilTermsOfServiceUserHasGlobalRoleCriterion
	 */
	protected function getInstance(): \ilTermsOfServiceUserHasGlobalRoleCriterion
	{
		$this->rbacReview = $this->getRbacReviewMock();

		$criterion = new \ilTermsOfServiceUserHasGlobalRoleCriterion(
			$this->rbacReview, $this->getObjectDataCacheMock()
		);

		return $criterion;
	}

	/**
	 * @return \ilTermsOfServiceUserHasGlobalRoleCriterion
	 */
	public function testInstanceCanBeCreated(): \ilTermsOfServiceUserHasGlobalRoleCriterion
	{
		$criterion = $this->getInstance();

		$this->assertEquals('usr_global_role', $criterion->getTypeIdent());

		return $criterion;
	}

	/**
	 * @param \ilTermsOfServiceUserHasGlobalRoleCriterion $criterion
	 * @depends testInstanceCanBeCreated
	 */
	public function testGraphicalUserInterface(\ilTermsOfServiceUserHasGlobalRoleCriterion $criterion)
	{
		$expectedInitialValue = 2;
		$expectedAfterFormSubmitValue = 4;
		$httpCriterionSelectionBodyParameter = 'criterion';
		$httpCriterionConfigBodyParameter = $criterion->getTypeIdent() . '_role_id';

		$lng = $this->getLanguageMock();

		$lng
			->expects($this->any())
			->method('txt')
			->willReturn('dummy');

		$gui = $criterion->getGUI($lng);

		$this->assertInstanceOf(\ilTermsOfServiceUserHasGlobalRoleCriterionGUI::class, $gui);

		$form = $this->getFormMock();

		$radioGroup = $this->getRadioGroupMock();

		$radioGroup
			->expects($this->any())
			->method('getPostVar')
			->willReturn($httpCriterionSelectionBodyParameter);

		$form->addItem($radioGroup);

		$gui->appendOption($radioGroup, new \ilTermsOfServiceCriterionConfig(['role_id' => $expectedInitialValue]));

		$roleSelection = $form->getItemByPostVar($httpCriterionConfigBodyParameter);
		$this->assertInstanceOf(\ilSelectInputGUI::class, $roleSelection);
		$this->assertEquals($roleSelection->getValue(), $expectedInitialValue);

		$form
			->expects($this->once())
			->method('getInput')
			->with($httpCriterionConfigBodyParameter)
			->will($this->returnCallback(function () use ($expectedAfterFormSubmitValue) {
				return $expectedAfterFormSubmitValue;
			}));

		$value = $gui->getConfigByForm($form);

		$this->assertInstanceOf(\ilTermsOfServiceCriterionConfig::class, $value);
		$this->assertEquals($expectedAfterFormSubmitValue, $value['role_id']);
		$this->assertEquals(new \ilTermsOfServiceCriterionConfig(['role_id' => $expectedAfterFormSubmitValue]), $value);
	}

	/**
	 * @return array
	 */
	public function failingConfigProvider(): array
	{
		$criterion = $this->getInstance();

		return [
			[$criterion, new \ilTermsOfServiceCriterionConfig(['role_id' => []])],
			[$criterion, new \ilTermsOfServiceCriterionConfig(['role_id' => new stdClass()])],
			[$criterion, new \ilTermsOfServiceCriterionConfig(['role_id' => 1.424])],
			[$criterion, new \ilTermsOfServiceCriterionConfig(['role_id' => 'phpunit'])],
			[$criterion, new \ilTermsOfServiceCriterionConfig(['another_config_key' => true])],
			[$criterion, new \ilTermsOfServiceCriterionConfig()],
		];
	}

	/**
	 * @param \ilTermsOfServiceUserHasGlobalRoleCriterion $criterion
	 * @param \ilTermsOfServiceCriterionConfig $config
	 * @dataProvider failingConfigProvider
	 */
	public function testEvaluationFailsIfConfiguredRoleDoesNotMatchTheExpectedFormat(
		\ilTermsOfServiceUserHasGlobalRoleCriterion $criterion,
		\ilTermsOfServiceCriterionConfig $config
	) {
		$user = $this->getUserMock();

		$this->assertFalse($criterion->evaluate($user, $config));
	}

	/**
	 *
	 */
	public function testEvaluationFailsIfConfiguredRoleIsNotAGlobalRole()
	{
		$user = $this->getUserMock();
		$criterion = $this->getInstance();

		$this->rbacReview
			->expects($this->once())
			->method('isGlobalRole')
			->willReturn(false);

		$this->assertFalse($criterion->evaluate($user, new \ilTermsOfServiceCriterionConfig(['role_id' => 5])));
	}

	/**
	 *
	 */
	public function testEvaluationFailsIfUserIsNotAssignedToConfiguredRole()
	{
		$user = $this->getUserMock();
		$criterion = $this->getInstance();

		$this->rbacReview
			->expects($this->once())
			->method('isGlobalRole')
			->willReturn(true);

		$this->rbacReview
			->expects($this->once())
			->method('isAssigned')
			->willReturn(false);

		$this->assertFalse($criterion->evaluate($user, new \ilTermsOfServiceCriterionConfig(['role_id' => 5])));
	}

	/**
	 *
	 */
	public function testEvaluationSucceedsIfUserLanguageDoesMatchDefinedLanguage()
	{
		$user = $this->getUserMock();
		$criterion = $this->getInstance();

		$this->rbacReview
			->expects($this->once())
			->method('isGlobalRole')
			->willReturn(true);

		$this->rbacReview
			->expects($this->once())
			->method('isAssigned')
			->willReturn(true);

		$this->assertTrue($criterion->evaluate($user, new \ilTermsOfServiceCriterionConfig(['role_id' => 2])));
	}
}