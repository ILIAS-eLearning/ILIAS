<?php declare(strict_types=1);

/* Copyright (c) 2021 - Daniel Weise <daniel.weise@concepts-and-training.de> - Extended GPL, see LICENSE */
/* Copyright (c) 2021 - Nils Haagen <nils.haagen@concepts-and-training.de> - Extended GPL, see LICENSE */

use PHPUnit\Framework\TestCase;
use ILIAS\KioskMode\ControlBuilder;
use ILIAS\KioskMode\TOCBuilder;
use ILIAS\KioskMode\LocatorBuilder;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\UI\Component\Button as Button;
use ILIAS\UI\Component\ViewControl;

require_once('IliasMocks.php');

class LSControlBuilderTest extends TestCase
{
    use IliasMocks;

    protected LSControlBuilder $control_builder;

    protected function setUp() : void
    {
        $ui_factory = $this->mockUIFactory();
        $lang = $this->mockIlLanguage();

        $data_factory = new DataFactory();
        $uri = $data_factory->uri('https://ilias.de/somepath');
        $url_builder = new LSUrlBuilder($uri);
        $settings = new LSGlobalSettings(12);
        $uri = $data_factory->uri('http://ilias.de/some/other/path');
        $lp_url_builder = new LSUrlBuilder($uri);

        $this->control_builder = new LSControlBuilder($ui_factory, $url_builder, $lang, $settings, $lp_url_builder);
    }

    public function testConstruction()
    {
        $this->assertInstanceOf(ControlBuilder::class, $this->control_builder);
    }

    public function testInitialValues()
    {
        $this->assertNull($this->control_builder->getExitControl());
        $this->assertNull($this->control_builder->getNextControl());
        $this->assertNull($this->control_builder->getPreviousControl());
        $this->assertNull($this->control_builder->getDoneControl());
        $this->assertEquals([], $this->control_builder->getControls());
        $this->assertNull($this->control_builder->getToc());
    }

    public function testExit()
    {
        $cb = $this->control_builder->exit('cmd');
        $this->assertInstanceOf(ControlBuilder::class, $cb);
        $this->assertInstanceOf(Button\Bulky::class, $cb->getExitControl());
    }

    public function testUniqueExit()
    {
        try {
            //must not be able to set a second exit-control
            $this->control_builder
                ->exit('cmd')
                ->exit('cmd');
            $this->assertFalse("This should not happen");
        } catch (LogicException $e) {
            $this->assertTrue(true);
        }
    }

    public function testNavigationControls()
    {
        $cb = $this->control_builder
            ->previous('cmd', -1)
            ->next('cmd', 1);
        $this->assertInstanceOf(ControlBuilder::class, $cb);
        $this->assertInstanceOf(Button\Standard::class, $cb->getPreviousControl());
        $this->assertInstanceOf(Button\Standard::class, $cb->getNextControl());
    }

    public function testUniquePrevious()
    {
        try {
            $this->control_builder
                ->previous('cmd', 1)
                ->previous('cmd', 1);
            $this->assertFalse("This should not happen");
        } catch (LogicException $e) {
            $this->assertTrue(true);
        }
    }

    public function testUniqueNext()
    {
        try {
            $this->control_builder
                ->next('cmd', 1)
                ->next('cmd', 1);
            $this->assertFalse("This should not happen");
        } catch (LogicException $e) {
            $this->assertTrue(true);
        }
    }

    public function testToC()
    {
        $toc = $this->control_builder->tableOfContent('cmd', 'rootnode');
        $this->assertInstanceOf(TOCBuilder::class, $toc);
        $this->assertEquals($toc, $this->control_builder->getToc());
    }

    public function testUniqueToC()
    {
        try {
            $this->control_builder->tableOfContent('cmd', 'rootnode')
                ->end();
            $this->control_builder->tableOfContent('cmd', 'rootnode');
            $this->assertFalse("This should not happen");
        } catch (LogicException $e) {
            $this->assertTrue(true);
        }
    }

    public function testGeneric()
    {
        $cb = $this->control_builder->generic('label', 'cmd', 1);
        $this->assertInstanceOf(ControlBuilder::class, $cb);
        $this->assertInstanceOf(Button\Standard::class, $cb->getControls()[0]);
    }

    public function testMultipleGeneric()
    {
        $cb = $this->control_builder
            ->generic('label', 'cmd', 1)
            ->generic('label', 'cmd', 2)
            ->generic('label', 'cmd', 3);
        $this->assertCount(3, $cb->getControls());
    }

    public function testDone()
    {
        $cb = $this->control_builder->done('cmd', 1);
        $this->assertInstanceOf(ControlBuilder::class, $cb);
        $this->assertInstanceOf(Button\Primary::class, $cb->getDoneControl());
    }

    public function testUniqueDone()
    {
        try {
            $this->control_builder
                ->done('cmd', 1)
                ->done('cmd', 1);
            $this->assertFalse("This should not happen");
        } catch (LogicException $e) {
            $this->assertTrue(true);
        }
    }

    public function testMode()
    {
        $cb = $this->control_builder->mode('cmd', ['m1', 'm2']);
        $this->assertInstanceOf(ControlBuilder::class, $cb);
        $this->assertInstanceOf(ViewControl\Mode::class, $cb->getModeControls()[0]);
    }

    public function testLocator()
    {
        $cb = $this->control_builder->locator('cmd');
        $this->assertInstanceOf(LocatorBuilder::class, $cb);
    }

    public function testUniqueLocator()
    {
        try {
            $this->control_builder->locator('cmd')
                ->end();
            $this->control_builder->locator('cmd');
            $this->assertFalse("This should not happen");
        } catch (LogicException $e) {
            $this->assertTrue(true);
        }
    }
}
