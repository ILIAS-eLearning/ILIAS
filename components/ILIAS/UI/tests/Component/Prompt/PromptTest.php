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
 */

declare(strict_types=1);

namespace Component\Prompt;

require_once(__DIR__ . "/../../Base.php");

use ILIAS\UI\Implementation\Component as I;
use ILIAS\UI\Component as C;
use ILIAS\UI\URLBuilder;
use ILIAS\Data\URI;

class PromptTest extends \ILIAS_UI_TestBase
{
    public function getUIFactory(): \NoUIFactory
    {
        $prompt_factory = new I\Prompt\Factory(
            new \IncrementalSignalGenerator()
        );

        return new class ($prompt_factory) extends \NoUIFactory {
            public function __construct(
                protected I\Prompt\Factory $prompt_factory,
            ) {
            }
            public function prompt(): I\Prompt\Factory
            {
                return $this->prompt_factory;
            }
        };
    }

    public function testPromptRender(): void
    {
        $url_builder = new URLBuilder(new URI('https://www.ilias.de'));
        $prompt = $this->getUIFactory()->prompt()->standard($url_builder);
        $renderer = $this->getDefaultRenderer();

        $expected_html = '
            <div class="il-prompt" id="id_1">
                <dialog aria-labelledby="id_1_title">
                    <div class="il-prompt__header">
                        <form><button formmethod="dialog" class="close" aria-label="close"><span aria-hidden="true">&times;</span></button></form>
                        <span class="il-prompt__title" id="id_1_title"></span>
                    </div>
                    <hr>
                    <div class="il-prompt__contents"></div>
                    <hr>
                    <div class="il-prompt__buttons"></div>
                </dialog>
                <section class="il-prompt__scripts"></section>
            </div>
        ';
        $actual_html = $renderer->render($prompt);

        $this->assertEquals(
            $this->brutallyTrimHTML($expected_html),
            $this->brutallyTrimHTML($actual_html),
        );
    }

    public function testPromptSignals(): void
    {
        $url_builder = new URLBuilder(new URI('https://www.ilias.de'));
        $url = $url_builder->buildURI()->__toString();
        $prompt = $this->getUIFactory()->prompt()->standard($url_builder);
        $this->assertEquals($url, $prompt->getAsyncUrl());
        $this->assertEquals($url, $prompt->getShowSignal()->getOptions()['url']);
        $this->assertInstanceOf(I\Signal::class, $prompt->getShowSignal());
        $this->assertInstanceOf(I\Signal::class, $prompt->getCloseSignal());

        $uri = new URI('https://test11.ilias.de');
        $this->assertEquals($uri->__toString(), $prompt->getShowSignal($uri)->getOptions()['url']);
    }

    public function testPromptURIParamter(): void
    {
        $url_builder = new URLBuilder(new URI('https://www.ilias.de'));
        list($url_builder, $parameter_token) = $url_builder->acquireParameters(
            ['prompt', 'test'],
            "param"
        );

        $prompt = $this->getUIFactory()->prompt()->standard($url_builder)
            ->withParameter($parameter_token, 'somevalue');

        $url = $url_builder->withParameter($parameter_token, 'somevalue')->buildURI()->__toString();
        $this->assertEquals($url, $prompt->getAsyncUrl());
        $this->assertEquals($url, $prompt->getShowSignal()->getOptions()['url']);
    }

}
