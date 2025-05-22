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

require_once(__DIR__ . "/../../../../../../../vendor/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../Base.php");
require_once(__DIR__ . "/InputTest.php");
require_once(__DIR__ . "/CommonFieldRendering.php");

use ILIAS\UI\Implementation\Component as I;
use ILIAS\UI\Implementation\Component\SignalGenerator;
use ILIAS\UI\Component\Input\Field;
use ILIAS\Data;
use ILIAS\Refinery\Factory as Refinery;

class UrlInputTest extends ILIAS_UI_TestBase
{
    use CommonFieldRendering;

    private DefNamesource $name_source;

    public function setUp(): void
    {
        $this->name_source = new DefNamesource();
    }

    public function testImplementsFactoryInterface(): void
    {
        $factory = $this->getFieldFactory();
        $url = $factory->url("Test Label", "Test Byline");

        $this->assertInstanceOf(\ILIAS\UI\Component\Input\Container\Form\FormInput::class, $url);
        $this->assertInstanceOf(Field\Url::class, $url);
    }

    public function testRendering(): void
    {
        $factory = $this->getFieldFactory();
        $renderer = $this->getDefaultRenderer();
        $label = "Test Label";
        $byline = "Test Byline";
        $url = $factory->url($label, $byline)->withNameFrom($this->name_source);
        $expected = $this->getFormWrappedHtml(
            'url-field-input',
            $label,
            '<input id="id_1" type="url" name="name_0" class="c-field-url" />',
            $byline,
            'id_1'
        );
        $this->assertEquals($expected, $this->render($url));
    }

    public function testRenderValue(): void
    {
        $factory = $this->getFieldFactory();
        $label = "Test Label";
        $value = "https://www.ilias.de/";
        $url = $factory->url($label)->withValue($value)
            ->withNameFrom($this->name_source);
        $expected = $this->getFormWrappedHtml(
            'url-field-input',
            $label,
            '<input id="id_1" type="url" value="https://www.ilias.de/" name="name_0" class="c-field-url" />',
            null,
            'id_1'
        );
        $this->assertEquals($expected, $this->render($url));
    }

    public function testCommonRendering(): void
    {
        $f = $this->getFieldFactory();
        $url = $f->url('label', null)->withNameFrom($this->name_source);

        $this->testWithError($url);
        $this->testWithNoByline($url);
        $this->testWithRequired($url);
        $this->testWithDisabled($url);
        $this->testWithAdditionalOnloadCodeRendersId($url);
    }

}
