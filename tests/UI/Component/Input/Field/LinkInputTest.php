<?php declare(strict_types=1);

/* Copyright (c) 2021 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once(__DIR__ . "/../../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../Base.php");
require_once(__DIR__ . "/InputTest.php");

use ILIAS\UI\Implementation\Component\SignalGenerator;
use \ILIAS\UI\Component\Input\Field;
use \ILIAS\Data;

class LinkInputTest extends ILIAS_UI_TestBase
{
    /**
     * @var DefNamesource
     */
    private $name_source;

    public function setUp() : void
    {
        $this->name_source = new DefNamesource();
    }

    protected function buildFactory()
    {
        $data_factory = new Data\Factory();
        $language = $this->createMock(\ilLanguage::class);
        $language->method("txt")
            ->willReturn($this->returnArgument(0));

        return new ILIAS\UI\Implementation\Component\Input\Field\Factory(
            new SignalGenerator(),
            $data_factory,
            new ILIAS\Refinery\Factory($data_factory, $language),
            $language
        );
    }

    public function test_implements_factory_interface()
    {
        $factory = $this->buildFactory();
        $url = $factory->link("Test Label", "Test Byline");

        $this->assertInstanceOf(Field\Link::class, $url);
    }

    public function test_rendering()
    {
        $factory = $this->buildFactory();
        $renderer = $this->getDefaultRenderer();
        $label = "Test Label";
        $byline = "Test Byline";
        $url = $factory->link($label, $byline)->withNameFrom($this->name_source);
        $html = $this->normalizeHTML($renderer->render($url));

        $expected = '
            <div class="form-group row">
                <label for="id_1" class="control-label col-sm-3">ui_link_label</label>
                <div class="col-sm-9">
                    <input id="id_1" type="text" name="name_1" class="form-control form-control-sm" />
                </div>
            </div>
            <div class="form-group row">
                <label for="id_2" class="control-label col-sm-3">ui_link_url</label>
                <div class="col-sm-9">
                    <input id="id_2" type="url" name="name_2" class="form-control form-control-sm" />
                </div>
            </div>';

        $this->assertEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($html)
        );
    }
}
