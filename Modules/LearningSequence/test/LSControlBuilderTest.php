<?php

declare(strict_types=1);

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

class LSControlBuilderTest extends TestCase
{
    use IliasMocks;

    protected LSControlBuilder $control_builder;

    protected function setUp(): void
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

    public function testConstruction(): void
    {
        $this->assertInstanceOf(ControlBuilder::class, $this->control_builder);
    }

    public function testInitialValues(): void
    {
        $this->assertNull($this->control_builder->getExitControl());
        $this->assertNull($this->control_builder->getNextControl());
        $this->assertNull($this->control_builder->getPreviousControl());
        $this->assertNull($this->control_builder->getDoneControl());
        $this->assertEquals([], $this->control_builder->getControls());
        $this->assertNull($this->control_builder->getToc());
    }

    public function testExit(): void
    {
        $cb = $this->control_builder->exit('cmd');
        $this->assertInstanceOf(ControlBuilder::class, $cb);
        $this->assertInstanceOf(Button\Bulky::class, $cb->getExitControl());
    }

    public function testUniqueExit(): void
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

    public function testNavigationControls(): void
    {
        $cb = $this->control_builder
            ->previous('cmd', -1)
            ->next('cmd', 1);
        $this->assertInstanceOf(ControlBuilder::class, $cb);
        $this->assertInstanceOf(Button\Standard::class, $cb->getPreviousControl());
        $this->assertInstanceOf(Button\Standard::class, $cb->getNextControl());
    }

    public function testUniquePrevious(): void
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

    public function testUniqueNext(): void
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

    public function testToC(): void
    {
        $toc = $this->control_builder->tableOfContent('cmd', 'rootnode');
        $this->assertInstanceOf(TOCBuilder::class, $toc);
        $this->assertEquals($toc, $this->control_builder->getToc());
    }

    public function testUniqueToC(): void
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

    public function testGeneric(): void
    {
        $cb = $this->control_builder->generic('label', 'cmd', 1);
        $this->assertInstanceOf(ControlBuilder::class, $cb);
        $this->assertInstanceOf(Button\Standard::class, $cb->getControls()[0]);
    }

    public function testMultipleGeneric(): void
    {
        $cb = $this->control_builder
            ->generic('label', 'cmd', 1)
            ->generic('label', 'cmd', 2)
            ->generic('label', 'cmd', 3);
        $this->assertCount(3, $cb->getControls());
    }

    public function testDone(): void
    {
        $cb = $this->control_builder->done('cmd', 1);
        $this->assertInstanceOf(ControlBuilder::class, $cb);
        $this->assertInstanceOf(Button\Primary::class, $cb->getDoneControl());
    }

    public function testUniqueDone(): void
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

    public function testMode(): void
    {
        $cb = $this->control_builder->mode('cmd', ['m1', 'm2']);
        $this->assertInstanceOf(ControlBuilder::class, $cb);
        $this->assertInstanceOf(ViewControl\Mode::class, $cb->getModeControls()[0]);
    }

    public function testLocator(): void
    {
        $cb = $this->control_builder->locator('cmd');
        $this->assertInstanceOf(LocatorBuilder::class, $cb);
    }

    public function testUniqueLocator(): void
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
