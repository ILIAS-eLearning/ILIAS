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

use ILIAS\UI\Implementation\Component\SignalGenerator;
use ILIAS\UI\Component\Input\Field;
use ILIAS\Data;
use ILIAS\UI\Implementation\Component\Input\Field\Factory;
use ILIAS\UI\Implementation\Component\Input\NameSource;
use ILIAS\UI\Component\Input\InputData;

class LinkInputTest extends ILIAS_UI_TestBase
{
    use CommonFieldRendering;

    public function testImplementsFactoryInterface(): void
    {
        $factory = $this->getFieldFactory();
        $url = $factory->link("Test Label", "Test Byline");

        $this->assertInstanceOf(Field\Link::class, $url);
    }

    public function testRendering(): void
    {
        $factory = $this->getFieldFactory();
        $label = "Test Label";
        $byline = "Test Byline";
        [$name_source, $name] = $this->getDefaultNameSourceStub();
        $link = $factory->link($label, $byline)->withNameFrom($name_source);

        $f1 = $this->getFormWrappedHtml(
            'text-field-input',
            'ui_link_label',
            '<input id="id_1" type="text" name="' . $name . '" class="c-field-text" />',
            null,
            'id_1',
            null,
        );
        $f2 = $this->getFormWrappedHtml(
            'url-field-input',
            'ui_link_url',
            '<input id="id_2" type="url" name="' . $name . '" class="c-field-url" />',
            null,
            'id_2',
            null,
        );

        $expected = $this->getFormWrappedHtml(
            'link-field-input',
            $label,
            $f1 . $f2,
            $byline
        );
        $this->assertEquals($expected, $this->render($link));
    }

    public function testCommonRendering(): void
    {
        $f = $this->getFieldFactory();
        $label = "label";
        [$name_source] = $this->getDefaultNameSourceStub();
        $link = $f->link($label, null)->withNameFrom($name_source);

        $this->testWithError($link);
        $this->testWithNoByline($link);
        $this->testWithRequired($link);
        $this->testWithDisabled($link);
        $this->testWithAdditionalOnloadCodeRendersId($link);
    }

    public function testProducesNullWhenNoDataExists(): void
    {
        $f = $this->getFieldFactory();
        $input = $f->link("", "")
            ->withNameFrom($this->createFixedNameSourceStub('name'));
        $input = $input->withInput(new class () implements InputData {
            public function getOr($_, $default): string
            {
                return "";
            }
            public function get($_): string
            {
                return "";
            }
            public function has($name): bool
            {
                return true;
            }

        });
        $result = $input->getContent();

        $this->assertNull($result->value());
    }
}
