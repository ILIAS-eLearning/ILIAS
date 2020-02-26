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

    /**
     * @var SignalGenerator
     */
    private $sig_gen;


    public function setUp() : void
    {
        parent::setUp();
        $this->sig_gen = new SignalGenerator();
    }


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


    public function testData()
    {
        $mode_title = 'That\'s one small step for [a] man';
        $uri_string = 'http://one_giant_leap?for=mankind';

        $mode_info = $this->getUIFactory()->mainControls()->modeInfo($mode_title, new URI($uri_string));

        $this->assertInstanceOf(\ILIAS\UI\Component\MainControls\ModeInfo::class, $mode_info);
        $this->assertEquals($mode_title, $mode_info->getModeTitle());
        $this->assertEquals($uri_string, $mode_info->getCloseAction()->getBaseURI() . '?' . $mode_info->getCloseAction()->getQuery());
    }


    public function getUIFactory()
    {
        $factory = new class() extends NoUIFactory {

            /**
             * @inheritDoc
             */
            public function __construct()
            {
                $this->sig_gen = new SignalGenerator();
            }


            public function symbol() : ILIAS\UI\Component\Symbol\Factory
            {
                return new Factory(
                    new \ILIAS\UI\Implementation\Component\Symbol\Icon\Factory(),
                    new \ILIAS\UI\Implementation\Component\Symbol\Glyph\Factory(),
                    new \ILIAS\UI\Implementation\Component\Symbol\Avatar\Factory()
                );
            }


            public function mainControls() : \ILIAS\UI\Component\MainControls\Factory
            {
                return new \ILIAS\UI\Implementation\Component\MainControls\Factory(
                    $this->sig_gen,
                    new \ILIAS\UI\Implementation\Component\MainControls\Slate\Factory(
                        $this->sig_gen,
                        new \ILIAS\UI\Implementation\Component\Counter\Factory(),
                        $this->symbol()
                    )
                );
            }
        };
        $factory->sig_gen = $this->sig_gen;

        return $factory;
    }
}
