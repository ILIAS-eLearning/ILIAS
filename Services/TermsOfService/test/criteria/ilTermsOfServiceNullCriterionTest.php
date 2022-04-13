<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Legacy\Legacy;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class ilTermsOfServiceNullCriterionTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceNullCriterionTest extends ilTermsOfServiceCriterionBaseTest
{
    /** @var MockObject|ilLanguage */
    protected ilLanguage $lng;

    protected function setUp() : void
    {
        parent::setUp();

        $this->lng = $this->getLanguageMock();

        $this->lng
            ->method('txt')
            ->willReturn('dummy');
    }

    protected function getInstance() : ilTermsOfServiceNullCriterion
    {
        return new ilTermsOfServiceNullCriterion();
    }

    public function testInstanceCanBeCreated() : ilTermsOfServiceNullCriterion
    {
        $criterion = $this->getInstance();

        $this->assertSame('null', $criterion->getTypeIdent());
        $this->assertSame(false, $criterion->hasUniqueNature());

        return $criterion;
    }

    /**
     * @param ilTermsOfServiceCriterionTypeGUI $gui
     * @return MockObject|ilPropertyFormGUI
     */
    protected function buildForm(
        ilTermsOfServiceCriterionTypeGUI $gui
    ) : ilPropertyFormGUI {
        $form = $this->getFormMock();

        $radioGroup = $this
            ->getMockBuilder(ilRadioGroupInputGUI::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getPostVar', 'addOption'])
            ->getMock();

        $form->addItem($radioGroup);

        $radioGroup
            ->expects($this->never())
            ->method('addOption');

        $gui->appendOption($radioGroup, $this->getCriterionConfig());

        return $form;
    }

    /**
     * @param ilTermsOfServiceNullCriterion $criterion
     * @depends testInstanceCanBeCreated
     * @return ilTermsOfServiceNullCriterion
     */
    public function testNoFormUserInterfaceElementsAreBuilt(
        ilTermsOfServiceNullCriterion $criterion
    ) : ilTermsOfServiceNullCriterion {
        $gui = $criterion->ui($this->lng);

        $this->buildForm($gui);

        return $criterion;
    }

    /**
     * @depends testNoFormUserInterfaceElementsAreBuilt
     * @param ilTermsOfServiceNullCriterion $criterion
     */
    public function testCriterionAlwaysCreateEmptyConfigValue(ilTermsOfServiceNullCriterion $criterion) : void
    {
        $gui = $criterion->ui($this->lng);

        $form = $this->buildForm($gui);

        $form
            ->expects($this->never())
            ->method('getInput');

        $value = $gui->getConfigByForm($form);

        $this->assertInstanceOf(ilTermsOfServiceCriterionConfig::class, $value);
        $this->assertEquals($this->getCriterionConfig(), $value);
    }

    /**
     * @depends testNoFormUserInterfaceElementsAreBuilt
     * @param ilTermsOfServiceNullCriterion $criterion
     */
    public function testTypeIdentPresentationEqualsANonEmptyString(ilTermsOfServiceNullCriterion $criterion) : void
    {
        $gui = $criterion->ui($this->lng);

        $actual = $gui->getIdentPresentation();

        $this->assertIsString($actual);
        $this->assertNotEmpty($actual);
    }

    public function testValuePresentationMatchesExpectation() : void
    {
        $criterion = $this->getInstance();
        $gui = $criterion->ui($this->lng);

        /** @var Legacy $actual */
        $actual = $gui->getValuePresentation(
            $this->getCriterionConfig(),
            $this->getUiFactoryMock()
        );

        $this->assertInstanceOf(Component::class, $actual);
        $this->assertInstanceOf(Legacy::class, $actual);
        $this->assertSame('-', $actual->getContent());
    }

    public function testEvaluationAlwaysSucceeds() : void
    {
        $user = $this->getUserMock();
        $criterion = $this->getInstance();

        $this->assertTrue($criterion->evaluate($user, $this->getCriterionConfig()));
    }
}
