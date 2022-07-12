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
 
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Implementation\Component as CImpl;
use ILIAS\UI\Implementation\Component\SignalGenerator;
use PHPUnit\Framework\MockObject\MockObject;

trait IliasMocks
{
    /**
     * Mock the UIFactory w/o all the Components.
     * You can easily return the desired component-factories
     * with setMethod/willReturn.
     *
     * @return UIFactory|MockObject
     */
    protected function mockUIFactory() : UIFactory
    {
        $ui_reflection = new ReflectionClass(UIFactory::class);
        $methods = array_map(
            fn ($m) => $m->getName(),
            $ui_reflection->getMethods()
        );

        $ui_factory = $this->getMockBuilder(UIFactory::class)
            ->onlyMethods($methods)
            ->getMock();

        $signal_generator = new SignalGenerator();
        $ui_factory->method('button')
            ->willReturn(new CImpl\Button\Factory());
        $ui_factory->method('viewControl')
            ->willReturn(new CImpl\ViewControl\Factory($signal_generator));
        $ui_factory->method('breadcrumbs')
            ->will(
                $this->returnCallback(function ($crumbs) {
                    return new CImpl\Breadcrumbs\Breadcrumbs($crumbs);
                })
            );
        $ui_factory->method('link')
            ->willReturn(new CImpl\Link\Factory());
        $ui_factory->method('symbol')
            ->willReturn(new CImpl\Symbol\Factory(
                new CImpl\Symbol\Icon\Factory(),
                new CImpl\Symbol\Glyph\Factory(),
                new CImpl\Symbol\Avatar\Factory()
            ));

        return $ui_factory;
    }


    public function uiFactoryBreadcrumbs(...$args) : CImpl\Breadcrumbs\Breadcrumbs
    {
        return new CImpl\Breadcrumbs\Breadcrumbs($args[0]);
    }
    
    /**
     * @return ilLanguage|MockObject
     */
    protected function mockIlLanguage() : ilLanguage
    {
        $lng = $this->getMockBuilder(ilLanguage::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['txt'])
            ->getMock();
        $lng->method('txt')
            ->willReturn('');

        return $lng;
    }
}
