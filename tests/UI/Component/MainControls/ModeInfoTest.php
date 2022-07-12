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
    private SignalGenerator $sig_gen;


    public function setUp() : void
    {
        parent::setUp();
        $this->sig_gen = new SignalGenerator();
    }

    public function testRendering() : void
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

    public function testData() : void
    {
        $mode_title = 'That\'s one small step for [a] man';
        $uri_string = 'http://one_giant_leap?for=mankind';

        $mode_info = $this->getUIFactory()->mainControls()->modeInfo($mode_title, new URI($uri_string));

        $this->assertInstanceOf(\ILIAS\UI\Component\MainControls\ModeInfo::class, $mode_info);
        $this->assertEquals($mode_title, $mode_info->getModeTitle());
        $this->assertEquals(
            $uri_string,
            $mode_info->getCloseAction()->getBaseURI() . '?' . $mode_info->getCloseAction()->getQuery()
        );
    }

    public function getUIFactory() : NoUIFactory
    {
        $factory = new class() extends NoUIFactory {
            public SignalGenerator $sig_gen;

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
