<?php

/**
 * Class ilTermsOfServiceUserHasLanguageCriterionTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceUserHasLanguageCriterionTest extends \ilTermsOfServiceCriterionBaseTest
{
	/**
	 * @return \ilTermsOfServiceUserHasLanguageCriterion
	 */
	protected function getInstance(): \ilTermsOfServiceUserHasLanguageCriterion
	{
		return new \ilTermsOfServiceUserHasLanguageCriterion();
	}

	/**
	 * @return \ilTermsOfServiceUserHasLanguageCriterion
	 */
	public function testInstanceCanBeCreated(): \ilTermsOfServiceUserHasLanguageCriterion
	{
		$criterion = $this->getInstance();

		$this->assertEquals('usr_language', $criterion->getTypeIdent());

		return $criterion;
	}

	/**
	 * @param \ilTermsOfServiceUserHasLanguageCriterion $criterion
	 * @depends testInstanceCanBeCreated
	 */
	public function testGraphicalUserInterface(\ilTermsOfServiceUserHasLanguageCriterion $criterion)
	{
		$expectedInitialValue = 'en';
		$expectedAfterFormSubmitValue = 'de';
		$httpCriterionSelectionBodyParameter = 'criterion';
		$httpCriterionConfigBodyParameter = $criterion->getTypeIdent() . '_lng';

		$lng = $this->getLanguageMock();

		$lng
			->expects($this->any())
			->method('txt')
			->willReturn('dummy');

		$lng
			->expects($this->any())
			->method('getInstalledLanguages')
			->willReturn(['de', 'en']);

		$gui = $criterion->getGUI($lng);

		$this->assertInstanceOf(\ilTermsOfServiceUserHasLanguageCriterionGUI::class, $gui);

		$form = $this->getFormMock();

		$radioGroup = $this->getRadioGroupMock();

		$radioGroup
			->expects($this->any())
			->method('getPostVar')
			->willReturn($httpCriterionSelectionBodyParameter);

		$form->addItem($radioGroup);

		$gui->appendOption($radioGroup, new \ilTermsOfServiceCriterionConfig(['lng' => $expectedInitialValue]));

		$languageSelection = $form->getItemByPostVar($httpCriterionConfigBodyParameter);
		$this->assertInstanceOf(\ilSelectInputGUI::class, $languageSelection);
		$this->assertEquals($languageSelection->getValue(), $expectedInitialValue);

		$form
			->expects($this->once())
			->method('getInput')
			->with($httpCriterionConfigBodyParameter)
			->will($this->returnCallback(function () use ($expectedAfterFormSubmitValue) {
				return $expectedAfterFormSubmitValue;
			}));

		$value = $gui->getConfigByForm($form);

		$this->assertInstanceOf(\ilTermsOfServiceCriterionConfig::class, $value);
		$this->assertEquals($expectedAfterFormSubmitValue, $value['lng']);
		$this->assertEquals(new \ilTermsOfServiceCriterionConfig(['lng' => $expectedAfterFormSubmitValue]), $value);
	}

	/**
	 * @return array
	 */
	public function failingConfigProvider(): array
	{
		$criterion = $this->getInstance();

		return [
			[$criterion, new \ilTermsOfServiceCriterionConfig(['lng' => 'en'])],
			[$criterion, new \ilTermsOfServiceCriterionConfig(['lng' => []])],
			[$criterion, new \ilTermsOfServiceCriterionConfig(['lng' => new stdClass()])],
			[$criterion, new \ilTermsOfServiceCriterionConfig(['lng' => 1.0])],
			[$criterion, new \ilTermsOfServiceCriterionConfig(['lng' => 1])],
			[$criterion, new \ilTermsOfServiceCriterionConfig(['another_config_key' => true])],
			[$criterion, new \ilTermsOfServiceCriterionConfig()],
		];
	}

	/**
	 * @return array
	 */
	public function succeedingConfigProvider(): array
	{
		$criterion = $this->getInstance();

		return [
			[$criterion, new \ilTermsOfServiceCriterionConfig(['lng' => 'de'])],
			[$criterion, new \ilTermsOfServiceCriterionConfig(['lng' => 'DE'])],
		];
	}

	/**
	 * @param \ilTermsOfServiceUserHasLanguageCriterion $criterion
	 * @param \ilTermsOfServiceCriterionConfig $config
	 * @dataProvider failingConfigProvider
	 */
	public function testEvaluationFailsIfUserLanguageDoesNotMatchDefinedLanguage(
		\ilTermsOfServiceUserHasLanguageCriterion $criterion,
		\ilTermsOfServiceCriterionConfig $config
	) {
		$user = $this->getUserMock();

		$user
			->expects($this->any())
			->method('getLanguage')
			->willReturn('de');

		$this->assertFalse($criterion->evaluate($user, $config));
	}

	/**
	 * @param \ilTermsOfServiceUserHasLanguageCriterion $criterion
	 * @param \ilTermsOfServiceCriterionConfig $config
	 * @dataProvider succeedingConfigProvider
	 */
	public function testEvaluationSucceedsIfUserLanguageDoesMatchDefinedLanguage(
		\ilTermsOfServiceUserHasLanguageCriterion $criterion,
		\ilTermsOfServiceCriterionConfig $config
	) {
		$user = $this->getUserMock();

		$user
			->expects($this->any())
			->method('getLanguage')
			->willReturn('de');

		$this->assertTrue($criterion->evaluate($user, $config));
	}
}