<?php declare(strict_types=1);

use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Legacy\Legacy;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class ilTermsOfServiceUserHasLanguageCriterionTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceUserHasLanguageCriterionTest extends ilTermsOfServiceCriterionBaseTest
{
    /** @var MockObject|ilLanguage */
    protected $lng;

    /** @var string */
    protected $expectedInitialValue = 'en';

    /** @var string */
    protected $expectedAfterFormSubmitValue = 'de';

    /** @var string */
    protected $englishLanguageTranslation = 'English';

    /** @var string */
    protected $germanLanguageTranslation = 'German';

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

        $this->lng
            ->expects($this->any())
            ->method('getInstalledLanguages')
            ->willReturn([$this->expectedAfterFormSubmitValue, $this->expectedInitialValue]);
    }

    /**
     * @return ilTermsOfServiceUserHasLanguageCriterion
     */
    protected function getInstance() : ilTermsOfServiceUserHasLanguageCriterion
    {
        return new ilTermsOfServiceUserHasLanguageCriterion();
    }

    /**
     * @return ilTermsOfServiceUserHasLanguageCriterion
     */
    public function testInstanceCanBeCreated() : ilTermsOfServiceUserHasLanguageCriterion
    {
        $criterion = $this->getInstance();

        $this->assertEquals('usr_language', $criterion->getTypeIdent());
        $this->assertEquals(true, $criterion->hasUniqueNature());

        return $criterion;
    }

    /**
     * @param ilTermsOfServiceCriterionTypeGUI $gui
     * @param string                           $httpCriterionSelectionBodyParameter
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

        $gui->appendOption($radioGroup, $this->getCriterionConfig(['lng' => $this->expectedInitialValue]));

        return $form;
    }

    /**
     * @param ilTermsOfServiceUserHasLanguageCriterion $criterion
     * @depends testInstanceCanBeCreated
     * @return ilTermsOfServiceUserHasLanguageCriterion
     * @throws ReflectionException
     */
    public function testFormUserInterfaceElementsAreProperlyBuilt(
        ilTermsOfServiceUserHasLanguageCriterion $criterion
    ) : ilTermsOfServiceUserHasLanguageCriterion {
        $httpCriterionSelectionBodyParameter = 'criterion';
        $httpCriterionConfigBodyParameter    = $criterion->getTypeIdent() . '_lng';

        $gui = $criterion->ui($this->lng);

        $this->assertInstanceOf(ilTermsOfServiceUserHasLanguageCriterionGUI::class, $gui);

        $form = $this->buildForm($gui, $httpCriterionSelectionBodyParameter);

        $languageSelection = $form->getItemByPostVar($httpCriterionConfigBodyParameter);
        $this->assertInstanceOf(ilSelectInputGUI::class, $languageSelection);
        $this->assertEquals($languageSelection->getValue(), $this->expectedInitialValue);

        return $criterion;
    }

    /**
     * @depends testFormUserInterfaceElementsAreProperlyBuilt
     * @param ilTermsOfServiceUserHasLanguageCriterion $criterion
     * @throws ReflectionException
     */
    public function testValuesFromFormUserInterfaceElementsCanBeRetrieved(
        ilTermsOfServiceUserHasLanguageCriterion $criterion
    ) : void {
        $httpCriterionSelectionBodyParameter = 'criterion';
        $httpCriterionConfigBodyParameter    = $criterion->getTypeIdent() . '_lng';

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
        $this->assertEquals($this->expectedAfterFormSubmitValue, $value['lng']);
        $this->assertEquals($this->getCriterionConfig(['lng' => $this->expectedAfterFormSubmitValue]), $value);
    }

    /**
     * @depends testFormUserInterfaceElementsAreProperlyBuilt
     * @param ilTermsOfServiceUserHasLanguageCriterion $criterion
     */
    public function testTypeIdentPresentationIsANonEmptyString(
        ilTermsOfServiceUserHasLanguageCriterion $criterion
    ) : void {
        $gui = $criterion->ui($this->lng);

        $actual = $gui->getIdentPresentation();

        $this->assertIsString($actual);
        $this->assertNotEmpty($actual);
    }

    /**
     * @return array
     */
    public function languageProvider() : array
    {
        return [
            'English Language' => [$this->expectedInitialValue, $this->englishLanguageTranslation],
            'German Language' => [$this->expectedAfterFormSubmitValue, $this->germanLanguageTranslation],
            'Invalid Languages' => ['invalid_lng', ''],
        ];
    }

    /**
     * @param string $lng
     * @param string $translation
     * @dataProvider languageProvider
     * @throws ReflectionException
     */
    public function testValuePresentationMatchesExpectation(string $lng, string $translation) : void
    {
        $language = $this->getLanguageMock();

        $language
            ->expects($this->any())
            ->method('txt')
            ->with('meta_l_' . $lng, '')
            ->willReturn($translation);

        $criterion = new ilTermsOfServiceUserHasLanguageCriterion();
        $gui       = $criterion->ui($language);

        /** @var Legacy $actual */
        $actual = $gui->getValuePresentation(
            $this->getCriterionConfig(['lng' => $lng]),
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
            'English Language where German is Expected' => [$criterion, $this->getCriterionConfig(['lng' => 'en'])],
            'Array' => [$criterion, $this->getCriterionConfig(['lng' => []])],
            'Object' => [$criterion, $this->getCriterionConfig(['lng' => new stdClass()])],
            'Double' => [$criterion, $this->getCriterionConfig(['lng' => 1.0])],
            'Integer' => [$criterion, $this->getCriterionConfig(['lng' => 1])],
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
            'German Language' => [$criterion, $this->getCriterionConfig(['lng' => 'de'])],
            'German Language Uppercase' => [$criterion, $this->getCriterionConfig(['lng' => 'DE'])],
        ];
    }

    /**
     * @param ilTermsOfServiceUserHasLanguageCriterion $criterion
     * @param ilTermsOfServiceCriterionConfig          $config
     * @dataProvider failingConfigProvider
     * @throws ReflectionException
     */
    public function testEvaluationFailsIfUserLanguageDoesNotMatchDefinedLanguage(
        ilTermsOfServiceUserHasLanguageCriterion $criterion,
        ilTermsOfServiceCriterionConfig $config
    ) : void {
        $user = $this->getUserMock();

        $user
            ->expects($this->any())
            ->method('getLanguage')
            ->willReturn('de');

        $this->assertFalse($criterion->evaluate($user, $config));
    }

    /**
     * @param ilTermsOfServiceUserHasLanguageCriterion $criterion
     * @param ilTermsOfServiceCriterionConfig          $config
     * @dataProvider succeedingConfigProvider
     * @throws ReflectionException
     */
    public function testEvaluationSucceedsIfUserLanguageDoesMatchDefinedLanguage(
        ilTermsOfServiceUserHasLanguageCriterion $criterion,
        ilTermsOfServiceCriterionConfig $config
    ) : void {
        $user = $this->getUserMock();

        $user
            ->expects($this->any())
            ->method('getLanguage')
            ->willReturn('de');

        $this->assertTrue($criterion->evaluate($user, $config));
    }
}