<?php

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

declare(strict_types=1);

require_once(__DIR__ . "/../../../../../../vendor/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../Base.php");

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component as IC;

/**
 * Test on button implementation.
 */
class LegacyTest extends ILIAS_UI_TestBase
{
    public function getUIFactory(): NoUIFactory
    {
        return new class () extends NoUIFactory {
            public function legacy(string $content): C\Legacy\Legacy
            {
                return new IC\Legacy\Legacy($content, new IC\SignalGenerator());
            }
        };
    }

    public function testImplementsFactoryInterface(): void
    {
        $f = $this->getUIFactory();

        $this->assertInstanceOf("ILIAS\\UI\\Factory", $f);
        $this->assertInstanceOf(
            "ILIAS\\UI\\Component\\Legacy\\Legacy",
            $f->legacy("Legacy Content")
        );
    }

    public function testGetContent(): void
    {
        $f = $this->getUIFactory();
        $g = $f->legacy("Legacy Content");

        $this->assertEquals("Legacy Content", $g->getContent());
    }


    public function testRenderContent(): void
    {
        $f = $this->getUIFactory();
        $r = $this->getDefaultRenderer();

        $g = $f->legacy("Legacy Content");

        $this->assertEquals("Legacy Content", $r->render($g));
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testCreateWithCustomSignal(): void
    {
        $f = $this->getUIFactory();
        $signal_name = 'Custom Signal';

        $f->legacy('')->withCustomSignal($signal_name, '');
    }

    public function testGetExistingCustomSignal(): void
    {
        $f = $this->getUIFactory();
        $signal_name = 'Custom Signal';
        $g = $f->legacy('')->withCustomSignal($signal_name, '');

        $this->assertNotNull($g->getCustomSignal($signal_name));
    }

    public function testGetNonExistingCustomSignal(): void
    {
        $f = $this->getUIFactory();
        $signal_name = 'Custom Signal';
        $g = $f->legacy('');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Signal with name $signal_name is not registered");
        $g->getCustomSignal($signal_name);
    }

    public function testGetListOfSignals(): void
    {
        $f = $this->getUIFactory();
        $signal_name_1 = 'Custom Signal 1';
        $signal_name_2 = 'Custom Signal 2';

        $g = $f->legacy('')->withCustomSignal($signal_name_1, '')->withCustomSignal($signal_name_2, '');
        $l = $g->getAllCustomSignals();

        $this->assertIsArray($l);
    }

    public function testGetListWithCustomSignalsAndCode(): void
    {
        $f = $this->getUIFactory();
        $signal_name_1 = 'Custom Signal 1';
        $custom_code_1 = 'custom_js1();';
        $signal_name_2 = 'Custom Signal 2';
        $custom_code_2 = 'custom_js2();';

        $g = $f->legacy('')
            ->withCustomSignal($signal_name_1, $custom_code_1)
            ->withCustomSignal($signal_name_2, $custom_code_2);
        $signal_list = $g->getAllCustomSignals();

        $this->assertEquals(2, count($signal_list));
        $this->assertEquals($signal_list[$signal_name_1]['js_code'], $custom_code_1);
        $this->assertEquals($signal_list[$signal_name_2]['js_code'], $custom_code_2);
    }
}
