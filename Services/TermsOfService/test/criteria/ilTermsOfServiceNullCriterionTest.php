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
 * Class ilTermsOfServiceNullCriterionTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceNullCriterionTest extends ilTermsOfServiceCriterionBaseTest
{
    /** @var MockObject&ilLanguage */
    protected ilLanguage $lng;

    protected function setUp(): void
    {
        parent::setUp();

        $this->lng = $this->getLanguageMock();

        $this->lng
            ->method('txt')
            ->willReturn('dummy');
    }

    protected function getInstance(): ilTermsOfServiceNullCriterion
    {
        return new ilTermsOfServiceNullCriterion();
    }

    public function testInstanceCanBeCreated(): ilTermsOfServiceNullCriterion
    {
        $criterion = $this->getInstance();

        $this->assertSame('null', $criterion->getTypeIdent());
        $this->assertFalse($criterion->hasUniqueNature());

        return $criterion;
    }

    /**
     * @return MockObject&ilPropertyFormGUI
     */
    protected function buildForm(
        ilTermsOfServiceCriterionTypeGUI $gui
    ): ilPropertyFormGUI {
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
     * @depends testInstanceCanBeCreated
     */
    public function testNoFormUserInterfaceElementsAreBuilt(
        ilTermsOfServiceNullCriterion $criterion
    ): ilTermsOfServiceNullCriterion {
        $gui = $criterion->ui($this->lng);

        $this->buildForm($gui);

        return $criterion;
    }

    /**
     * @depends testNoFormUserInterfaceElementsAreBuilt
     */
    public function testCriterionAlwaysCreateEmptyConfigValue(ilTermsOfServiceNullCriterion $criterion): void
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
     */
    public function testTypeIdentPresentationEqualsANonEmptyString(ilTermsOfServiceNullCriterion $criterion): void
    {
        $gui = $criterion->ui($this->lng);

        $actual = $gui->getIdentPresentation();

        $this->assertIsString($actual);
        $this->assertNotEmpty($actual);
    }

    public function testValuePresentationMatchesExpectation(): void
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

    public function testEvaluationAlwaysSucceeds(): void
    {
        $user = $this->getUserMock();
        $criterion = $this->getInstance();

        $this->assertTrue($criterion->evaluate($user, $this->getCriterionConfig()));
    }
}
