<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Legacy\Legacy;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class ilTermsOfServiceUserHasCountryCriterionTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceUserHasCountryCriterionTest extends ilTermsOfServiceCriterionBaseTest
{
    /** @var MockObject&ilLanguage */
    protected ilLanguage $lng;
    protected string $expectedInitialValue = 'EN';
    protected string $expectedAfterFormSubmitValue = 'DE';
    protected string $englishLanguageTranslation = 'English';
    protected string $germanLanguageTranslation = 'German';
    /** @var string[] */
    protected array $countries = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->lng = $this->getLanguageMock();

        $this->lng
            ->method('txt')
            ->willReturn('dummy');

        $this->countries = ['EN', 'DE'];
    }

    protected function getInstance(): ilTermsOfServiceUserHasCountryCriterion
    {
        return new ilTermsOfServiceUserHasCountryCriterion($this->countries);
    }

    public function testInstanceCanBeCreated(): ilTermsOfServiceUserHasCountryCriterion
    {
        $criterion = $this->getInstance();

        $this->assertSame('usr_country', $criterion->getTypeIdent());
        $this->assertTrue($criterion->hasUniqueNature());

        return $criterion;
    }

    /**
     * @param ilTermsOfServiceCriterionTypeGUI $gui
     * @param string $httpCriterionSelectionBodyParameter
     * @return MockObject&ilPropertyFormGUI
     */
    protected function buildForm(
        ilTermsOfServiceCriterionTypeGUI $gui,
        string $httpCriterionSelectionBodyParameter
    ): ilPropertyFormGUI {
        $form = $this->getFormMock();

        $radioGroup = $this->getRadioGroupMock();

        $radioGroup
            ->method('getPostVar')
            ->willReturn($httpCriterionSelectionBodyParameter);

        $form->addItem($radioGroup);

        $gui->appendOption($radioGroup, $this->getCriterionConfig(['country' => $this->expectedInitialValue]));

        return $form;
    }

    /**
     * @param ilTermsOfServiceUserHasCountryCriterion $criterion
     * @depends testInstanceCanBeCreated
     * @return ilTermsOfServiceUserHasCountryCriterion
     */
    public function testFormUserInterfaceElementsAreProperlyBuilt(
        ilTermsOfServiceUserHasCountryCriterion $criterion
    ): ilTermsOfServiceUserHasCountryCriterion {
        $httpCriterionSelectionBodyParameter = 'criterion';
        $httpCriterionConfigBodyParameter = $criterion->getTypeIdent() . '_country';

        $gui = $criterion->ui($this->lng);

        $this->assertInstanceOf(ilTermsOfServiceUserHasCountryCriterionGUI::class, $gui);

        $form = $this->buildForm($gui, $httpCriterionSelectionBodyParameter);

        $countrySelection = $form->getItemByPostVar($httpCriterionConfigBodyParameter);
        $this->assertInstanceOf(ilSelectInputGUI::class, $countrySelection);
        $this->assertSame($countrySelection->getValue(), $this->expectedInitialValue);

        return $criterion;
    }

    /**
     * @depends testFormUserInterfaceElementsAreProperlyBuilt
     * @param ilTermsOfServiceUserHasCountryCriterion $criterion
     */
    public function testValuesFromFormUserInterfaceElementsCanBeRetrieved(
        ilTermsOfServiceUserHasCountryCriterion $criterion
    ): void {
        $httpCriterionSelectionBodyParameter = 'criterion';
        $httpCriterionConfigBodyParameter = $criterion->getTypeIdent() . '_country';

        $gui = $criterion->ui($this->lng);

        $form = $this->buildForm($gui, $httpCriterionSelectionBodyParameter);

        $form
            ->expects($this->once())
            ->method('getInput')
            ->with($httpCriterionConfigBodyParameter)
            ->willReturnCallback(function () {
                return $this->expectedAfterFormSubmitValue;
            });

        $value = $gui->getConfigByForm($form);

        $this->assertInstanceOf(ilTermsOfServiceCriterionConfig::class, $value);
        $this->assertSame($this->expectedAfterFormSubmitValue, $value['country']);
        $this->assertEquals($this->getCriterionConfig(['country' => $this->expectedAfterFormSubmitValue]), $value);
    }

    /**
     * @depends testFormUserInterfaceElementsAreProperlyBuilt
     * @param ilTermsOfServiceUserHasCountryCriterion $criterion
     */
    public function testTypeIdentPresentationIsANonEmptyString(
        ilTermsOfServiceUserHasCountryCriterion $criterion
    ): void {
        $gui = $criterion->ui($this->lng);

        $actual = $gui->getIdentPresentation();

        $this->assertIsString($actual);
        $this->assertNotEmpty($actual);
    }

    /**
     * @return array<string, string[]>
     */
    public function countryProvider(): array
    {
        return [
            'English Language' => [$this->expectedInitialValue, $this->englishLanguageTranslation],
            'German Language' => [$this->expectedAfterFormSubmitValue, $this->germanLanguageTranslation],
            'Invalid Languages' => ['invalid_country', ''],
        ];
    }

    /**
     * @param string $country
     * @param string $translation
     * @dataProvider countryProvider
     */
    public function testValuePresentationMatchesExpectation(string $country, string $translation): void
    {
        $language = $this->getLanguageMock();

        $language
            ->method('txt')
            ->with('meta_c_' . $country, '')
            ->willReturn($translation);

        $criterion = new ilTermsOfServiceUserHasCountryCriterion($this->countries);
        $gui = $criterion->ui($language);

        /** @var Legacy $actual */
        $actual = $gui->getValuePresentation(
            $this->getCriterionConfig(['country' => $country]),
            $this->getUiFactoryMock()
        );

        $this->assertInstanceOf(Component::class, $actual);
        $this->assertInstanceOf(Legacy::class, $actual);
        $this->assertSame($translation, $actual->getContent());
    }

    public function failingConfigProvider(): array
    {
        $criterion = $this->getInstance();

        return [
            'English Language where German is Expected' => [$criterion, $this->getCriterionConfig(['country' => 'en'])],
            'Array' => [$criterion, $this->getCriterionConfig(['country' => []])],
            'Object' => [$criterion, $this->getCriterionConfig(['country' => new stdClass()])],
            'Double' => [$criterion, $this->getCriterionConfig(['country' => 1.0])],
            'Integer' => [$criterion, $this->getCriterionConfig(['country' => 1])],
            'Wrong Key Provided for Extracting Language' => [
                $criterion,
                $this->getCriterionConfig(['another_config_key' => true])
            ],
            'Empty Configuration' => [$criterion, $this->getCriterionConfig()],
        ];
    }

    public function succeedingConfigProvider(): array
    {
        $criterion = $this->getInstance();

        return [
            'German Language' => [$criterion, $this->getCriterionConfig(['country' => 'de'])],
            'German Language Uppercase' => [$criterion, $this->getCriterionConfig(['country' => 'DE'])],
        ];
    }

    /**
     * @param ilTermsOfServiceUserHasCountryCriterion $criterion
     * @param ilTermsOfServiceCriterionConfig $config
     * @dataProvider failingConfigProvider
     */
    public function testEvaluationFailsIfUserCountryDoesNotMatchDefinedLanguage(
        ilTermsOfServiceUserHasCountryCriterion $criterion,
        ilTermsOfServiceCriterionConfig $config
    ): void {
        $user = $this->getUserMock();

        $user
            ->method('getSelectedCountry')
            ->willReturn('de');

        $this->assertFalse($criterion->evaluate($user, $config));
    }

    /**
     * @param ilTermsOfServiceUserHasCountryCriterion $criterion
     * @param ilTermsOfServiceCriterionConfig $config
     * @dataProvider succeedingConfigProvider
     */
    public function testEvaluationSucceedsIfUserCountryDoesMatchDefinedLanguage(
        ilTermsOfServiceUserHasCountryCriterion $criterion,
        ilTermsOfServiceCriterionConfig $config
    ): void {
        $user = $this->getUserMock();

        $user
            ->method('getSelectedCountry')
            ->willReturn('de');

        $this->assertTrue($criterion->evaluate($user, $config));
    }
}
