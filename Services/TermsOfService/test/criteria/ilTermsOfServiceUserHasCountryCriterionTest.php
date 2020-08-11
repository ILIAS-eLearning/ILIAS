<?php declare(strict_types=1);

use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Legacy\Legacy;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class ilTermsOfServiceUserHasCountryCriterionTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceUserHasCountryCriterionTest extends ilTermsOfServiceCriterionBaseTest
{
    /** @var MockObject|ilLanguage */
    protected $lng;

    /** @var string */
    protected $expectedInitialValue = 'EN';

    /** @var string */
    protected $expectedAfterFormSubmitValue = 'DE';

    /** @var string */
    protected $englishLanguageTranslation = 'English';

    /** @var string */
    protected $germanLanguageTranslation = 'German';

    /** @var string[] */
    protected $countries = [];

    /**
     * @inheritDoc
     */
    public function setUp() : void
    {
        parent::setUp();

        $this->lng = $this->getLanguageMock();

        $this->lng
            ->expects($this->any())
            ->method('txt')
            ->willReturn('dummy');

        $this->countries = ['EN', 'DE'];
    }

    /**
     * @return ilTermsOfServiceUserHasCountryCriterion
     */
    protected function getInstance() : ilTermsOfServiceUserHasCountryCriterion
    {
        return new ilTermsOfServiceUserHasCountryCriterion($this->countries);
    }

    /**
     * @return ilTermsOfServiceUserHasCountryCriterion
     */
    public function testInstanceCanBeCreated() : ilTermsOfServiceUserHasCountryCriterion
    {
        $criterion = $this->getInstance();

        $this->assertEquals('usr_country', $criterion->getTypeIdent());
        $this->assertEquals(true, $criterion->hasUniqueNature());

        return $criterion;
    }

    /**
     * @param ilTermsOfServiceCriterionTypeGUI $gui
     * @param string $httpCriterionSelectionBodyParameter
     * @return MockObject|ilPropertyFormGUI
     * @throws ReflectionException
     */
    protected function buildForm(
        ilTermsOfServiceCriterionTypeGUI $gui,
        string $httpCriterionSelectionBodyParameter
    ) : ilPropertyFormGUI {
        $form = $this->getFormMock();

        $radioGroup = $this->getRadioGroupMock();

        $radioGroup
            ->expects($this->any())
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
     * @throws ReflectionException
     */
    public function testFormUserInterfaceElementsAreProperlyBuilt(
        ilTermsOfServiceUserHasCountryCriterion $criterion
    ) : ilTermsOfServiceUserHasCountryCriterion {
        $httpCriterionSelectionBodyParameter = 'criterion';
        $httpCriterionConfigBodyParameter = $criterion->getTypeIdent() . '_country';

        $gui = $criterion->ui($this->lng);

        $this->assertInstanceOf(ilTermsOfServiceUserHasCountryCriterionGUI::class, $gui);

        $form = $this->buildForm($gui, $httpCriterionSelectionBodyParameter);

        $countrySelection = $form->getItemByPostVar($httpCriterionConfigBodyParameter);
        $this->assertInstanceOf(ilSelectInputGUI::class, $countrySelection);
        $this->assertEquals($countrySelection->getValue(), $this->expectedInitialValue);

        return $criterion;
    }

    /**
     * @depends testFormUserInterfaceElementsAreProperlyBuilt
     * @param ilTermsOfServiceUserHasCountryCriterion $criterion
     * @throws ReflectionException
     */
    public function testValuesFromFormUserInterfaceElementsCanBeRetrieved(
        ilTermsOfServiceUserHasCountryCriterion $criterion
    ) : void {
        $httpCriterionSelectionBodyParameter = 'criterion';
        $httpCriterionConfigBodyParameter = $criterion->getTypeIdent() . '_country';

        $gui = $criterion->ui($this->lng);

        $form = $this->buildForm($gui, $httpCriterionSelectionBodyParameter);

        $form
            ->expects($this->once())
            ->method('getInput')
            ->with($httpCriterionConfigBodyParameter)
            ->will($this->returnCallback(function () {
                return $this->expectedAfterFormSubmitValue;
            }));

        $value = $gui->getConfigByForm($form);

        $this->assertInstanceOf(ilTermsOfServiceCriterionConfig::class, $value);
        $this->assertEquals($this->expectedAfterFormSubmitValue, $value['country']);
        $this->assertEquals($this->getCriterionConfig(['country' => $this->expectedAfterFormSubmitValue]), $value);
    }

    /**
     * @depends testFormUserInterfaceElementsAreProperlyBuilt
     * @param ilTermsOfServiceUserHasCountryCriterion $criterion
     */
    public function testTypeIdentPresentationIsANonEmptyString(
        ilTermsOfServiceUserHasCountryCriterion $criterion
    ) : void {
        $gui = $criterion->ui($this->lng);

        $actual = $gui->getIdentPresentation();

        $this->assertIsString($actual);
        $this->assertNotEmpty($actual);
    }

    /**
     * @return array
     */
    public function countryProvider() : array
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
     * @throws ReflectionException
     */
    public function testValuePresentationMatchesExpectation(string $country, string $translation) : void
    {
        $language = $this->getLanguageMock();

        $language
            ->expects($this->any())
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
        $this->assertEquals($translation, $actual->getContent());
    }

    /**
     * @return array
     */
    public function failingConfigProvider() : array
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

    /**
     * @return array
     */
    public function succeedingConfigProvider() : array
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
     * @throws ReflectionException
     */
    public function testEvaluationFailsIfUserCountryDoesNotMatchDefinedLanguage(
        ilTermsOfServiceUserHasCountryCriterion $criterion,
        ilTermsOfServiceCriterionConfig $config
    ) : void {
        $user = $this->getUserMock();

        $user
            ->expects($this->any())
            ->method('getSelectedCountry')
            ->willReturn('de');

        $this->assertFalse($criterion->evaluate($user, $config));
    }

    /**
     * @param ilTermsOfServiceUserHasCountryCriterion $criterion
     * @param ilTermsOfServiceCriterionConfig $config
     * @dataProvider succeedingConfigProvider
     * @throws ReflectionException
     */
    public function testEvaluationSucceedsIfUserCountryDoesMatchDefinedLanguage(
        ilTermsOfServiceUserHasCountryCriterion $criterion,
        ilTermsOfServiceCriterionConfig $config
    ) : void {
        $user = $this->getUserMock();

        $user
            ->expects($this->any())
            ->method('getSelectedCountry')
            ->willReturn('de');

        $this->assertTrue($criterion->evaluate($user, $config));
    }
}
