<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\UI\Component\Component;
use ILIAS\UI\Implementation\Component\Legacy\Legacy;

/**
 * Class ilTermsOfServiceNullCriterionTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceNullCriterionTest extends \ilTermsOfServiceCriterionBaseTest
{
    /** @var PHPUnit_Framework_MockObject_MockObject|\ilLanguage */
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
    protected function getInstance() : \ilTermsOfServiceNullCriterion
    {
        $criterion = new \ilTermsOfServiceNullCriterion();

        return $criterion;
    }

    /**
     * @return \ilTermsOfServiceNullCriterion
     */
    public function testInstanceCanBeCreated() : \ilTermsOfServiceNullCriterion
    {
        $criterion = $this->getInstance();

        $this->assertEquals('null', $criterion->getTypeIdent());
        $this->assertEquals(false, $criterion->hasUniqueNature());

        return $criterion;
    }

    /**
     * @param \ilTermsOfServiceCriterionTypeGUI $gui
     * @return PHPUnit_Framework_MockObject_MockObject|\ilPropertyFormGUI
     */
    protected function buildForm(
        \ilTermsOfServiceCriterionTypeGUI $gui
    ) : \ilPropertyFormGUI {
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

        $gui->appendOption($radioGroup, $this->getCriterionConfig());

        return $form;
    }

    /**
     * @param \ilTermsOfServiceNullCriterion $criterion
     * @depends testInstanceCanBeCreated
     * @return \ilTermsOfServiceNullCriterion
     */
    public function testNoFormUserInterfaceElementsAreBuilt(\ilTermsOfServiceNullCriterion $criterion)
    {
        $gui = $criterion->ui($this->lng);

        $this->buildForm($gui);

        return $criterion;
    }

    /**
     * @depends testNoFormUserInterfaceElementsAreBuilt
     * @param \ilTermsOfServiceNullCriterion $criterion
     */
    public function testCriterionAlwaysCreateEmptyConfigValue(\ilTermsOfServiceNullCriterion $criterion)
    {
        $gui = $criterion->ui($this->lng);

        $form = $this->buildForm($gui);

        $form
            ->expects($this->never())
            ->method('getInput');

        $value = $gui->getConfigByForm($form);

        $this->assertInstanceOf(\ilTermsOfServiceCriterionConfig::class, $value);
        $this->assertEquals($this->getCriterionConfig(), $value);
    }

    /**
     * @depends testNoFormUserInterfaceElementsAreBuilt
     * @param \ilTermsOfServiceNullCriterion $criterion
     */
    public function testTypeIdentPresentatioEqualsANonEmptyString(\ilTermsOfServiceNullCriterion $criterion)
    {
        $gui = $criterion->ui($this->lng);

        $actual = $gui->getIdentPresentation();

        $this->assertInternalType('string', $actual);
        $this->assertNotEmpty($actual);
    }

    /**
     *
     */
    public function testValuePresentationMatchesExpectation()
    {
        $criterion = $this->getInstance();
        $gui = $criterion->ui($this->lng);

        /** @var Legacy $actual */
        $actual = $gui->getValuePresentation(
            $this->getCriterionConfig(),
            $this->dic->ui()->factory()
        );

        $this->assertInstanceOf(Component::class, $actual);
        $this->assertInstanceOf(Legacy::class, $actual);
        $this->assertEquals('-', $actual->getContent());
    }

    /**
     *
     */
    public function testEvaluationAlwaysSucceeds()
    {
        $user = $this->getUserMock();
        $criterion = $this->getInstance();

        $this->assertTrue($criterion->evaluate($user, $this->getCriterionConfig()));
    }
}
