<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

require_once(__DIR__ . "/../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../Base.php");

use \ILIAS\UI\Component as C;
use \ILIAS\UI\Implementation\Component as IC;

/**
 * Test on button implementation.
 */
class LegacyTest extends ILIAS_UI_TestBase
{
    public function getUIFactory()
    {
        $factory = new class extends NoUIFactory {
            public function legacy($content)
            {
                return new IC\Legacy\Legacy($content, new IC\SignalGenerator());
            }
        };
        return $factory;
    }

    public function test_implements_factory_interface()
    {
        $f = $this->getUIFactory();

        $this->assertInstanceOf("ILIAS\\UI\\Factory", $f);
        $this->assertInstanceOf(
            "ILIAS\\UI\\Component\\Legacy\\Legacy",
            $f->legacy("Legacy Content")
        );
    }

    public function test_get_content()
    {
        $f = $this->getUIFactory();
        $g = $f->legacy("Legacy Content");

        $this->assertEquals($g->getContent(), "Legacy Content");
    }


    public function test_render_content()
    {
        $f = $this->getUIFactory();
        $r = $this->getDefaultRenderer();

        $g = $f->legacy("Legacy Content");

        $this->assertEquals($r->render($g), "Legacy Content");
    }

    /**
     * @doesNotPerformAssertions
     */
    public function test_create_with_custom_signal()
    {
        $f = $this->getUIFactory();
        $signal_name = 'Custom Signal';

        $g = $f->legacy('')->withCustomSignal($signal_name, '');
    }

    public function test_get_existing_custom_signal()
    {
        $f = $this->getUIFactory();
        $signal_name = 'Custom Signal';
        $g = $f->legacy('')->withCustomSignal($signal_name, '');

        $this->assertNotNull($g->getCustomSignal($signal_name));
    }

    public function test_get_non_existing_custom_signal()
    {
        $f = $this->getUIFactory();
        $signal_name = 'Custom Signal';
        $g = $f->legacy('');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Signal with name $signal_name is not registered");
        $g->getCustomSignal($signal_name);
    }

    public function test_get_list_of_signals()
    {
        $f = $this->getUIFactory();
        $signal_name_1 = 'Custom Signal 1';
        $signal_name_2 = 'Custom Signal 2';

        $g = $f->legacy('')->withCustomSignal($signal_name_1, '')->withCustomSignal($signal_name_2, '');
        $l = $g->getAllSignals();

        $this->assertIsArray($l);
    }

    public function test_get_list_with_custom_signals_and_code()
    {
        $f = $this->getUIFactory();
        $signal_name_1 = 'Custom Signal 1';
        $custom_code_1 = 'custom_js1();';
        $signal_name_2 = 'Custom Signal 2';
        $custom_code_2 = 'custom_js2();';

        $g = $f->legacy('')
            ->withCustomSignal($signal_name_1, $custom_code_1)
            ->withCustomSignal($signal_name_2, $custom_code_2);
        $signal_list = $g->getAllSignals();

        $this->assertEquals(count($signal_list), 2);
        $this->assertEquals($signal_list[$signal_name_1]['js_code'], $custom_code_1);
        $this->assertEquals($signal_list[$signal_name_2]['js_code'], $custom_code_2);
    }
}
