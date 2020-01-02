<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Legacy\Legacy;

/**
 * Class ilTermsOfServiceUserHasGlobalRoleCriterionTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceUserHasGlobalRoleCriterionTest extends \ilTermsOfServiceCriterionBaseTest
{
    /** @var PHPUnit_Framework_MockObject_MockObject|\ilRbacReview */
    protected $rbacReview;

    /** @var PHPUnit_Framework_MockObject_MockObject|\ilLanguage */
    protected $lng;

    /** @var int */
    protected $expectedInitialValue = 2;

    /** @var int */
    protected $expectedAfterFormSubmitValue = 4;

    /** @var string */
    protected $userRoleTitle = 'User';
    
    /** @var string */
    protected $adminRoleTitle = 'Administrator';

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
     * @return \ilTermsOfServiceUserHasGlobalRoleCriterion
     */
    protected function getInstance() : \ilTermsOfServiceUserHasGlobalRoleCriterion
    {
        $this->rbacReview = $this->getRbacReviewMock();

        $criterion = new \ilTermsOfServiceUserHasGlobalRoleCriterion(
            $this->rbacReview,
            $this->getObjectDataCacheMock()
        );

        return $criterion;
    }

    /**
     * @return \ilTermsOfServiceUserHasGlobalRoleCriterion
     */
    public function testInstanceCanBeCreated() : \ilTermsOfServiceUserHasGlobalRoleCriterion
    {
        $criterion = $this->getInstance();

        $this->assertEquals('usr_global_role', $criterion->getTypeIdent());
        $this->assertEquals(false, $criterion->hasUniqueNature());

        return $criterion;
    }

    /**
     * @param \ilTermsOfServiceCriterionTypeGUI $gui
     * @param string $httpCriterionSelectionBodyParameter
     * @return PHPUnit_Framework_MockObject_MockObject|\ilPropertyFormGUI
     */
    protected function buildForm(
        \ilTermsOfServiceCriterionTypeGUI $gui,
        string $httpCriterionSelectionBodyParameter
    ) : \ilPropertyFormGUI {
        $form = $this->getFormMock();

        $radioGroup = $this->getRadioGroupMock();

        $radioGroup
            ->expects($this->any())
            ->method('getPostVar')
            ->willReturn($httpCriterionSelectionBodyParameter);

        $form->addItem($radioGroup);

        $gui->appendOption(
            $radioGroup,
            new \ilTermsOfServiceCriterionConfig(['role_id' => $this->expectedInitialValue])
        );

        return $form;
    }

    /**
     * @param \ilTermsOfServiceUserHasGlobalRoleCriterion $criterion
     * @depends testInstanceCanBeCreated
     * @return \ilTermsOfServiceUserHasGlobalRoleCriterion
     */
    public function testFormUserInterfaceElementsAreProperlyBuilt(
        \ilTermsOfServiceUserHasGlobalRoleCriterion $criterion
    ) {
        $httpCriterionSelectionBodyParameter = 'criterion';
        $httpCriterionConfigBodyParameter = $criterion->getTypeIdent() . '_role_id';

        $gui = $criterion->ui($this->lng);

        $this->assertInstanceOf(\ilTermsOfServiceUserHasGlobalRoleCriterionGUI::class, $gui);

        $form = $this->buildForm($gui, $httpCriterionSelectionBodyParameter);

        $roleSelection = $form->getItemByPostVar($httpCriterionConfigBodyParameter);
        $this->assertInstanceOf(\ilSelectInputGUI::class, $roleSelection);
        $this->assertEquals($roleSelection->getValue(), $this->expectedInitialValue);

        return $criterion;
    }

    /**
     * @depends testFormUserInterfaceElementsAreProperlyBuilt
     * @param \ilTermsOfServiceUserHasGlobalRoleCriterion $criterion
     */
    public function testValuesFromFormUserInterfaceElementsCanBeRetrieved(
        \ilTermsOfServiceUserHasGlobalRoleCriterion $criterion
    ) {
        $httpCriterionSelectionBodyParameter = 'criterion';
        $httpCriterionConfigBodyParameter = $criterion->getTypeIdent() . '_role_id';

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

        $this->assertInstanceOf(\ilTermsOfServiceCriterionConfig::class, $value);
        $this->assertEquals($this->expectedAfterFormSubmitValue, $value['role_id']);
        $this->assertEquals($this->getCriterionConfig(['role_id' => $this->expectedAfterFormSubmitValue]), $value);
    }

    /**
     * @depends testFormUserInterfaceElementsAreProperlyBuilt
     * @param \ilTermsOfServiceUserHasGlobalRoleCriterion $criterion
     */
    public function testTypeIdentPresentationIsANonEmptyString(\ilTermsOfServiceUserHasGlobalRoleCriterion $criterion)
    {
        $gui = $criterion->ui($this->lng);

        $actual = $gui->getIdentPresentation();

        $this->assertInternalType('string', $actual);
        $this->assertNotEmpty($actual);
    }

    /**
     * @return array
     */
    public function objectCacheProvider() : array
    {
        return [
            [$this->expectedInitialValue, $this->adminRoleTitle],
            [$this->expectedAfterFormSubmitValue, $this->userRoleTitle],
            [-1, ''],
        ];
    }

    /**
     * @param int $roleId
     * @param string $roleTitle
     * @dataProvider objectCacheProvider
     */
    public function testValuePresentationMatchesExpectation(int $roleId, string $roleTitle)
    {
        $rbacReview = $this->getRbacReviewMock();
        $objectDataCache = $this->getObjectDataCacheMock();

        $objectDataCache
            ->expects($this->any())
            ->method('lookupTitle')
            ->with($roleId)
            ->willReturn($roleTitle);

        $criterion = new \ilTermsOfServiceUserHasGlobalRoleCriterion($rbacReview, $objectDataCache);
        $gui = $criterion->ui($this->lng);

        /** @var Legacy $actual */
        $actual = $gui->getValuePresentation(
            $this->getCriterionConfig(['role_id' => $roleId]),
            $this->dic->ui()->factory()
        );

        $this->assertInstanceOf(Component::class, $actual);
        $this->assertInstanceOf(Legacy::class, $actual);
        $this->assertEquals($roleTitle, $actual->getContent());
    }

    /**
     * @return array
     */
    public function failingConfigProvider() : array
    {
        $criterion = $this->getInstance();

        return [
            [$criterion, $this->getCriterionConfig(['role_id' => []])],
            [$criterion, $this->getCriterionConfig(['role_id' => new stdClass()])],
            [$criterion, $this->getCriterionConfig(['role_id' => 1.424])],
            [$criterion, $this->getCriterionConfig(['role_id' => 'phpunit'])],
            [$criterion, $this->getCriterionConfig(['another_config_key' => true])],
            [$criterion, $this->getCriterionConfig()],
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

        $this->assertFalse(
            $criterion->evaluate($user, $this->getCriterionConfig(['role_id' => $this->expectedAfterFormSubmitValue]))
        );
    }

    /**
     *
     */
    public function testEvaluationFailsIfUserIsNotAssignedToConfiguredGlobalRole()
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

    /**
     *
     */
    public function testEvaluationSucceedsIfUserIsAssignedToDefinedGlobalRole()
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
