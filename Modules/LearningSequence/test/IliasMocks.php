<?php

use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Implementation\Component as CImpl;
use ILIAS\UI\Implementation\Component\SignalGenerator;

trait IliasMocks
{
    /**
     * Mock the UIFactory w/o all the Components.
     * You can easily return the desired component-factories
     * with setMethod/willReturn.
     */
    protected function mockUIFactory()
    {
        $ui_reflection = new ReflectionClass(UIFactory::class);
        $methods = array_map(
            function ($m) {
                return $m->getName();
            },
            $ui_reflection->getMethods()
        );

        $ui_factory = $this->getMockBuilder(UIFactory::class)
            ->setMethods($methods)
            ->getMock();

        $signal_generator = new SignalGenerator();
        $ui_factory->method('button')
            ->willReturn(new CImpl\Button\Factory());
        $ui_factory->method('viewControl')
            ->willReturn(new CImpl\ViewControl\Factory($signal_generator));

        $ui_factory->method('breadcrumbs')
            ->will($this->returnCallback([static::class, 'uiFactoryBreadcrumbs']));

        $ui_factory->method('link')
            ->willReturn(new CImpl\Link\Factory());

        return $ui_factory;
    }


    public function uiFactoryBreadcrumbs()
    {
        $args = func_get_args();
        return new CImpl\Breadcrumbs\Breadcrumbs($args[0]);
    }


    protected function mockIlLanguage()
    {
        $lng = $this->getMockBuilder(ilLanguage::class)
            ->disableOriginalConstructor()
            ->setMethods(['txt'])
            ->getMock();
        $lng->method('txt')
            ->willReturn('');

        return $lng;
    }
}
