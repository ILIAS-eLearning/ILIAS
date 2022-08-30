<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *********************************************************************/

use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use ILIAS\Refinery\Factory as Refinery;
use PHPUnit\Framework\MockObject\MockObject;
use ILIAS\HTTP\GlobalHttpState;
use ILIAS\DI\Container;

class ilObjTermsIfServiceGUITest extends ilTermsOfServiceBaseTest
{
    /** @var MockObject&ilObjTermsOfService */
    protected ilObjTermsOfService $tos;
    /** @var MockObject&ilGlobalTemplateInterface */
    protected ilGlobalTemplateInterface $tpl;
    /** @var MockObject&ilCtrlInterface */
    protected ilCtrlInterface $ctrl;
    /** @var MockObject&ilLanguage */
    protected ilLanguage $lng;
    /** @var MockObject&ilRbacSystem */
    protected ilRbacSystem $rbacsystem;
    /** @var MockObject&ilErrorHandling */
    protected ilErrorHandling $error;
    /** @var MockObject&Factory */
    protected Factory $uiFactory;
    /** @var MockObject&Renderer */
    protected Renderer $uiRenderer;
    /** @var MockObject&GlobalHttpState */
    protected GlobalHttpState $http;
    /** @var MockObject&Refinery */
    protected Refinery $refinery;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tos = $this->getMockBuilder(ilObjTermsOfService::class)->disableOriginalConstructor()->getMock();
        $this->tpl = $this->getMockBuilder(ilGlobalTemplateInterface::class)->getMock();
        $this->ctrl = $this->getMockBuilder(ilCtrl::class)->disableOriginalConstructor()->getMock();
        $this->lng = $this->getMockBuilder(ilLanguage::class)->disableOriginalConstructor()->getMock();
        $this->rbacsystem = $this->getMockBuilder(ilRbacSystem::class)->disableOriginalConstructor()->getMock();
        $this->error = $this->getMockBuilder(ilErrorHandling::class)->disableOriginalConstructor()->getMock();
        $this->http = $this->getMockBuilder(GlobalHttpState::class)->disableOriginalConstructor()->getMock();
        $this->refinery = $this->getMockBuilder(Refinery::class)->disableOriginalConstructor()->getMock();
        $this->uiFactory = $this->getMockBuilder(Factory::class)->disableOriginalConstructor()->getMock();
        $this->uiRenderer = $this->getMockBuilder(Renderer::class)->disableOriginalConstructor()->getMock();
    }

    /**
     * @dataProvider getSettingsFormDataProvider
     */
    public function testgetSettingsForm(ilObjTermsOfService $object): void
    {
        $gui = $this->getMockBuilder(ilObjTermsOfServiceGUI::class)
                    ->setMethodsExcept(['getSettingsForm'])
                    ->disableOriginalConstructor()
                    ->getMock();
        $dic = $this->getMockBuilder(Container::class)
                    ->disableOriginalConstructor()
                    ->getMock();
        $ui = $this->getMockBuilder(\ILIAS\DI\UIServices::class)
                   ->disableOriginalConstructor()
                   ->getMock();
        $input = $this->getMockBuilder(ILIAS\UI\Component\Input\Factory::class)
                      ->disableOriginalConstructor()
                      ->getMock();
        $field = $this->getMockBuilder(ILIAS\UI\Component\Input\Field\Factory::class)
                      ->disableOriginalConstructor()
                      ->getMock();
        $container = $this->getMockBuilder(ILIAS\UI\Component\Input\Container\Factory::class)
                          ->disableOriginalConstructor()
                          ->getMock();
        $form = $this->getMockBuilder(ILIAS\UI\Component\Input\Container\Form\Factory::class)
                     ->disableOriginalConstructor()
                     ->getMock();
        $standard = $this->getMockBuilder(\ILIAS\UI\Component\Input\Container\Form\Standard::class)
                         ->disableOriginalConstructor()
                         ->getMock();
        $optional_group = $this->getMockBuilder(ILIAS\UI\Component\Input\Field\OptionalGroup::class)
                               ->disableOriginalConstructor()
                               ->getMock();
        $optional_group->expects($this->once())
                       ->method('withValue')
                       ->with($this->equalTo($object->getStatus() ? [ilObjTermsOfServiceGUI::F_TOS_REEVALUATE_ON_LOGIN => $object->shouldReevaluateOnLogin()] : null))
                       ->willReturn($optional_group);
        $optional_group->expects($this->once())
                       ->method('withDisabled')
                       ->with($this->equalTo(false))
                       ->willReturn($optional_group);
        $checkbox = $this->getMockBuilder(ILIAS\UI\Component\Input\Field\Checkbox::class)
                         ->disableOriginalConstructor()
                         ->getMock();
        $checkbox->expects($this->once())
                 ->method('withValue')
                 ->with($this->equalTo($object->shouldReevaluateOnLogin()))
                 ->willReturn($checkbox);
        $checkbox->expects($this->once())
                 ->method('withDisabled')
                 ->with($this->equalTo(false))
                 ->willReturn($checkbox);

        $dic->expects($this->exactly(3))->method('ui')->willReturn($ui);
        $ui->expects($this->exactly(3))->method('factory')->willReturn($this->uiFactory);
        $this->uiFactory->expects($this->exactly(3))->method('input')->willReturn($input);
        $input->expects($this->exactly(2))->method('field')->willReturn($field);
        $field->expects($this->once())->method('optionalGroup')->willReturn($optional_group);
        $field->expects($this->once())->method('checkbox')->willReturn($checkbox);
        $input->expects($this->once())->method('container')->willReturn($container);
        $container->expects($this->once())->method('form')->willReturn($form);
        $form->expects($this->once())->method('standard')->willReturnCallback(
            function ($action, $inputs) use ($standard) {
                $this->assertIsArray($inputs);
                $this->assertCount(1, $inputs);
                $this->assertEquals('tos_status', array_key_first($inputs));
                $this->assertInstanceOf(ILIAS\UI\Component\Input\Field\OptionalGroup::class, $inputs['tos_status']);
                return $standard;
            }
        );

        $reflection_property = new ReflectionProperty(ilObjTermsOfServiceGUI::class, 'object');
        $reflection_property->setAccessible(true);
        $reflection_property->setValue($gui, $object);

        $reflection_property = new ReflectionProperty(ilObjTermsOfServiceGUI::class, 'lng');
        $reflection_property->setAccessible(true);
        $reflection_property->setValue($gui, $this->lng);

        $this->rbacsystem->expects($this->exactly(2))->method('checkAccess')->with(
            $this->equalTo('write'),
            $this->equalTo($object->getRefId())
        )->willReturn(true);

        $reflection_property = new ReflectionProperty(ilObjTermsOfServiceGUI::class, 'rbac_system');
        $reflection_property->setAccessible(true);
        $reflection_property->setValue($gui, $this->rbacsystem);

        $this->ctrl->expects($this->once())->method('getFormAction')->with(
            $this->isInstanceOf(ilObjTermsOfServiceGUI::class),
            'saveSettings'
        )
                   ->willReturnCallback(
                       function ($gui, $cmd) {
                           $this->assertEquals('saveSettings', $cmd);
                           return 'https://www.ilias.de/gui/saveSettings';
                       }
                   );

        $reflection_property = new ReflectionProperty(ilObjTermsOfServiceGUI::class, 'ctrl');
        $reflection_property->setAccessible(true);
        $reflection_property->setValue($gui, $this->ctrl);

        $reflection_property = new ReflectionProperty(ilObjTermsOfServiceGUI::class, 'dic');
        $reflection_property->setAccessible(true);
        $reflection_property->setValue($gui, $dic);

        $form = $gui->getSettingsForm();
        $inputs = $form->getInputs();
//        $this->assertEquals();
    }

    public function getSettingsFormDataProvider(): Generator
    {
        yield 'test1' => [$this->mockTermsOfServiceObject(1, true, true)];
    }

    protected function mockTermsOfServiceObject(
        int $id,
        bool $status,
        bool $reevaluateOnLogin
    ): ilObjTermsOfService {
        $obj = $this->getMockBuilder(ilObjTermsOfService::class)
                    ->onlyMethods(['getId',
                                   'getStatus',
                                   'shouldReevaluateOnLogin',
                                   'setId',
                                   'setStatus',
                                   'setReevaluateOnLogin'
                    ])
                    ->disableOriginalConstructor()
                    ->getMock();
        $obj->setId(1);
        $obj->setStatus($status);
        $obj->setReevaluateOnLogin($reevaluateOnLogin);
        return $obj;
    }
}
