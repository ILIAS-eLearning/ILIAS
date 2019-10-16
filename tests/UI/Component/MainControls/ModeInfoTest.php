<?php

/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../Base.php");

use ILIAS\UI\Implementation\Component as I;

/**
 * Tests for the ModeInfo.
 */
class ModeInfoTest extends ILIAS_UI_TestBase
{

    public function setUp() : void
    {
        $f = new I\Button\Factory();
        $this->button = $f->close();
        $this->text = 'mode info text';
    }


    protected function getFactory()
    {
        $sig_gen = new I\SignalGenerator();
        $sig_gen = new I\SignalGenerator();
        $counter_factory = new I\Counter\Factory();
        $slate_factory = new I\MainControls\Slate\Factory($sig_gen, $counter_factory);
        $factory = new I\MainControls\Factory($sig_gen, $slate_factory);

        return $factory;
    }


    public function testConstruction()
    {
        $mode_info = $this->getFactory()->modeInfo($this->text, $this->button);
        $this->assertInstanceOf(
            "ILIAS\\UI\\Component\\MainControls\\ModeInfo",
            $mode_info
        );

        return $mode_info;
    }


    public function getUIFactory()
    {
        $factory = new class extends NoUIFactory {

            public function listing()
            {
                return new I\Listing\Factory();
            }
        };

        return $factory;
    }


    /**
     * @depends testConstruction
     */
    public function testRendering($footer)
    {
        $r = $this->getDefaultRenderer();
        $html = $r->render($footer);

        $expected = <<<EOT
		<div class="container-fluid il-head_info "><div class=row" role="note"><div class="col-8"><h1>mode info text</h1><p></p></div><div class="col-4 pull-right il-head_info-close"><span><button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button></span></div></div></div>
EOT;

        $this->assertEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($html)
        );
    }


    protected function brutallyTrimHTML($html)
    {
        $html = str_replace(["\n", "\r", "\t"], "", $html);
        $html = preg_replace('# {2,}#', " ", $html);

        return trim($html);
    }
}
