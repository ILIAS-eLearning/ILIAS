<?php

use ILIAS\Data\URI;
use ILIAS\UI\Implementation\Component\MainControls\ModeInfo;
use ILIAS\UI\Implementation\Component\SignalGenerator;
use ILIAS\UI\Implementation\Component\Symbol\Factory;

require_once("libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../Base.php");

/**
 * Class ModeInfoTest
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ModeInfoTest extends ILIAS_UI_TestBase
{

    public function testRendering()
    {
        $mode_title = 'That\'s one small step for [a] man';
        $uri_string = 'http://one_giant_leap?for=mankind';
        $mode_info = new ModeInfo($mode_title, new URI($uri_string));

        $r = $this->getDefaultRenderer();
        $html = $r->render($mode_info);

        $expected = <<<EOT
		<div class="il-mode-info">
		    <span class="il-mode-info-content">$mode_title<a class="glyph" href="$uri_string" aria-label="close"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></a>
		    </span>
		    </div>
EOT;

        $this->assertEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($html)
        );
    }


    public function getUIFactory()
    {
        $factory = new class extends NoUIFactory
        {
            public function symbol() : ILIAS\UI\Component\Symbol\Factory
            {
                return new Factory(
                    new \ILIAS\UI\Implementation\Component\Symbol\Icon\Factory(),
                    new \ILIAS\UI\Implementation\Component\Symbol\Glyph\Factory()
                );
            }
        };
        $factory->sig_gen = new SignalGenerator();

        return $factory;
    }
}
