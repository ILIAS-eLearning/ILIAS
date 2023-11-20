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

require_once(__DIR__ . "/../../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../Base.php");
require_once(__DIR__ . "/InputTest.php");

use ILIAS\UI\Implementation\Component\SignalGenerator;
use ILIAS\UI\Component\Input\Field;
use ILIAS\Data;
use ILIAS\UI\Implementation\Component\Input\Field\Factory;
use ILIAS\UI\Implementation\Component\Input\NameSource;
use ILIAS\UI\Implementation\Component\Input\InputData;

class LinkInputTest extends ILIAS_UI_TestBase
{
    private DefNamesource $name_source;

    public function setUp(): void
    {
        $this->name_source = new DefNamesource();
    }

    protected function buildFactory(): Factory
    {
        $data_factory = new Data\Factory();
        $language = $this->createMock(ilLanguage::class);
        $language->method("txt")
            ->will($this->returnArgument(0));

        return new Factory(
            $this->createMock(\ILIAS\UI\Implementation\Component\Input\UploadLimitResolver::class),
            new SignalGenerator(),
            $data_factory,
            new ILIAS\Refinery\Factory($data_factory, $language),
            $language
        );
    }

    public function testImplementsFactoryInterface(): void
    {
        $factory = $this->buildFactory();
        $url = $factory->link("Test Label", "Test Byline");

        $this->assertInstanceOf(Field\Link::class, $url);
    }

    public function testRendering(): void
    {
        $factory = $this->buildFactory();
        $renderer = $this->getDefaultRenderer();
        $label = "Test Label";
        $byline = "Test Byline";
        $url = $factory->link($label, $byline)->withNameFrom($this->name_source);
        $html = $this->normalizeHTML($renderer->render($url));

        $expected = '
            <div class="form-group row">
                <label for="id_1" class="control-label col-sm-4 col-md-3 col-lg-2">ui_link_label</label>
                <div class="col-sm-8 col-md-9 col-lg-10">
                    <input id="id_1" type="text" name="name_0/label_1" class="form-control form-control-sm" />
                </div>
            </div>
            <div class="form-group row">
                <label for="id_2" class="control-label col-sm-4 col-md-3 col-lg-2">ui_link_url</label>
                <div class="col-sm-8 col-md-9 col-lg-10">
                    <input id="id_2" type="url" name="name_0/url_2" class="form-control form-control-sm" />
                </div>
            </div>';

        $this->assertEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($html)
        );
    }

    public function testProducesNullWhenNoDataExists(): void
    {
        $f = $this->buildFactory();
        $input = $f->link("", "")
            ->withNameFrom(new class () implements NameSource {
                public function getNewName(): string
                {
                    return "name";
                }
                public function getNewDedicatedName(): string
                {
                    return "dedicated_name";
                }
            });
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
