<?php declare(strict_types=1);

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
 * Class ilTermsOfServiceUserHasGlobalRoleCriterionTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceUserHasGlobalRoleCriterionTest extends ilTermsOfServiceCriterionBaseTest
{
    /** @var MockObject&ilRbacReview */
    protected ilRbacReview $rbacReview;
    /** @var MockObject&ilLanguage */
    protected ilLanguage $lng;
    protected int $expectedInitialValue = 2;
    protected int $expectedAfterFormSubmitValue = 4;
    protected string $userRoleTitle = 'User';
    protected string $adminRoleTitle = 'Administrator';

    protected function setUp() : void
    {
        parent::setUp();

        $this->lng = $this->getLanguageMock();

        $this->lng
            ->method('txt')
            ->willReturn('dummy');
    }

    /**
     * @return ilTermsOfServiceUserHasGlobalRoleCriterion
     */
    protected function getInstance() : ilTermsOfServiceUserHasGlobalRoleCriterion
    {
        $this->rbacReview = $this->getRbacReviewMock();

        $criterion = new ilTermsOfServiceUserHasGlobalRoleCriterion(
            $this->rbacReview,
            $this->getObjectDataCacheMock()
        );

        return $criterion;
    }

    /**
     * @return ilTermsOfServiceUserHasGlobalRoleCriterion
     */
    public function testInstanceCanBeCreated() : ilTermsOfServiceUserHasGlobalRoleCriterion
    {
        $criterion = $this->getInstance();

        $this->assertSame('usr_global_role', $criterion->getTypeIdent());
        $this->assertFalse($criterion->hasUniqueNature());

        return $criterion;
    }

    /**
     * @param ilTermsOfServiceCriterionTypeGUI $gui
     * @param string                           $httpCriterionSelectionBodyParameter
     * @return MockObject&ilPropertyFormGUI
     */
    protected function buildForm(
        ilTermsOfServiceCriterionTypeGUI $gui,
        string $httpCriterionSelectionBodyParameter
    ) : ilPropertyFormGUI {
        $form = $this->getFormMock();

        $radioGroup = $this->getRadioGroupMock();

        $radioGroup
            ->method('getPostVar')
            ->willReturn($httpCriterionSelectionBodyParameter);

        $form->addItem($radioGroup);

        $gui->appendOption(
            $radioGroup,
            new ilTermsOfServiceCriterionConfig(['role_id' => $this->expectedInitialValue])
        );

        return $form;
    }

    /**
     * @param ilTermsOfServiceUserHasGlobalRoleCriterion $criterion
     * @depends testInstanceCanBeCreated
     * @return ilTermsOfServiceUserHasGlobalRoleCriterion
     */
    public function testFormUserInterfaceElementsAreProperlyBuilt(
        ilTermsOfServiceUserHasGlobalRoleCriterion $criterion
    ) : ilTermsOfServiceUserHasGlobalRoleCriterion {
        $httpCriterionSelectionBodyParameter = 'criterion';
        $httpCriterionConfigBodyParameter = $criterion->getTypeIdent() . '_role_id';

        $gui = $criterion->ui($this->lng);

        $this->assertInstanceOf(ilTermsOfServiceUserHasGlobalRoleCriterionGUI::class, $gui);

        $form = $this->buildForm($gui, $httpCriterionSelectionBodyParameter);

        $roleSelection = $form->getItemByPostVar($httpCriterionConfigBodyParameter);
        $this->assertInstanceOf(ilSelectInputGUI::class, $roleSelection);
        $this->assertEquals($roleSelection->getValue(), (string) $this->expectedInitialValue);

        return $criterion;
    }

    /**
     * @depends testFormUserInterfaceElementsAreProperlyBuilt
     * @param ilTermsOfServiceUserHasGlobalRoleCriterion $criterion
     */
    public function testValuesFromFormUserInterfaceElementsCanBeRetrieved(
        ilTermsOfServiceUserHasGlobalRoleCriterion $criterion
    ) : void {
        $httpCriterionSelectionBodyParameter = 'criterion';
        $httpCriterionConfigBodyParameter = $criterion->getTypeIdent() . '_role_id';

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
        $this->assertSame($this->expectedAfterFormSubmitValue, $value['role_id']);
        $this->assertEquals($this->getCriterionConfig(['role_id' => $this->expectedAfterFormSubmitValue]), $value);
    }

    /**
     * @depends testFormUserInterfaceElementsAreProperlyBuilt
     * @param ilTermsOfServiceUserHasGlobalRoleCriterion $criterion
     */
    public function testTypeIdentPresentationIsANonEmptyString(
        ilTermsOfServiceUserHasGlobalRoleCriterion $criterion
    ) : void {
        $gui = $criterion->ui($this->lng);

        $actual = $gui->getIdentPresentation();

        $this->assertIsString($actual);
        $this->assertNotEmpty($actual);
    }

    public function objectCacheProvider() : array
    {
        return [
            'Administrator Role Id' => [$this->expectedInitialValue, $this->adminRoleTitle],
            'User Role Id' => [$this->expectedAfterFormSubmitValue, $this->userRoleTitle],
            'Invalid Role Id' => [-1, ''],
        ];
    }

    /**
     * @param int    $roleId
     * @param string $roleTitle
     * @dataProvider objectCacheProvider
     */
    public function testValuePresentationMatchesExpectation(int $roleId, string $roleTitle) : void
    {
        $rbacReview = $this->getRbacReviewMock();
        $objectDataCache = $this->getObjectDataCacheMock();

        $objectDataCache
            ->method('lookupTitle')
            ->with($roleId)
            ->willReturn($roleTitle);

        $criterion = new ilTermsOfServiceUserHasGlobalRoleCriterion($rbacReview, $objectDataCache);
        $gui = $criterion->ui($this->lng);

        /** @var Legacy $actual */
        $actual = $gui->getValuePresentation(
            $this->getCriterionConfig(['role_id' => $roleId]),
            $this->getUiFactoryMock()
        );

        $this->assertInstanceOf(Component::class, $actual);
        $this->assertInstanceOf(Legacy::class, $actual);
        $this->assertSame($roleTitle, $actual->getContent());
    }

    public function failingConfigProvider() : array
    {
        $criterion = $this->getInstance();

        return [
            'Array' => [$criterion, $this->getCriterionConfig(['role_id' => []])],
            'Object' => [$criterion, $this->getCriterionConfig(['role_id' => new stdClass()])],
            'Double' => [$criterion, $this->getCriterionConfig(['role_id' => 1.424])],
            'String' => [$criterion, $this->getCriterionConfig(['role_id' => 'phpunit'])],
            'Wrong Key Provided for Extracting Role' => [
                $criterion,
                $this->getCriterionConfig(['another_config_key' => true])
            ],
            'Empty Configuration' => [$criterion, $this->getCriterionConfig()],
        ];
    }

    /**
     * @param ilTermsOfServiceUserHasGlobalRoleCriterion $criterion
     * @param ilTermsOfServiceCriterionConfig            $config
     * @dataProvider failingConfigProvider
     */
    public function testEvaluationFailsIfConfiguredRoleDoesNotMatchTheExpectedFormat(
        ilTermsOfServiceUserHasGlobalRoleCriterion $criterion,
        ilTermsOfServiceCriterionConfig $config
    ) : void {
        $user = $this->getUserMock();

        $this->assertFalse($criterion->evaluate($user, $config));
    }

    public function testEvaluationFailsIfConfiguredRoleIsNotAGlobalRole() : void
    {
        $user = $this->getUserMock();
        $criterion = $this->getInstance();

        $this->rbacReview
            ->expects($this->once())
            ->method('isGlobalRole')
            ->willReturn(false);

        $this->assertFalse(
            $criterion->evaluate($user, $this->getCriterionConfig(['role_id' => $this->expectedAfterFormSubmitValue]))
        );
    }

    public function testEvaluationFailsIfUserIsNotAssignedToConfiguredGlobalRole() : void
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

        $this->assertFalse(
            $criterion->evaluate($user, $this->getCriterionConfig(['role_id' => $this->expectedAfterFormSubmitValue]))
        );
    }

    public function testEvaluationSucceedsIfUserIsAssignedToDefinedGlobalRole() : void
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

        $this->assertTrue(
            $criterion->evaluate($user, $this->getCriterionConfig(['role_id' => $this->expectedAfterFormSubmitValue]))
        );
    }
}
