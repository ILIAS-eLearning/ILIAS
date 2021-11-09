<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once(__DIR__ . "/../../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../Base.php");
require_once(__DIR__ . "/InputTest.php");

use ILIAS\UI\Implementation\Component\SignalGenerator;
use \ILIAS\Data;
use ILIAS\Refinery;

/**
 * Class TagInputTest
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class TagInputTest extends ILIAS_UI_TestBase
{
    /**
     * @var DefNamesource
     */
    private $name_source;

    public function setUp() : void
    {
        $this->name_source = new DefNamesource();
    }

    protected function buildFactory() : ILIAS\UI\Implementation\Component\Input\Field\Factory
    {
        $df = new Data\Factory();
        $language = $this->createMock(\ilLanguage::class);
        return new ILIAS\UI\Implementation\Component\Input\Field\Factory(
            new SignalGenerator(),
            $df,
            new \ILIAS\Refinery\Factory($df, $language),
            $language
        );
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testImplementsFactoryInterface() : void
    {
        $f = $this->buildFactory();

        $tag = $f->tag(
            "label",
            ["lorem", "ipsum", "dolor",],
            "byline"
        );
    }

    public function testRender() : void
    {
        $f = $this->buildFactory();
        $label = "label";
        $byline = "byline";
        $tags = ["lorem", "ipsum", "dolor",];
        $name = "name_0";
        $text = $f->tag($label, $tags, $byline)->withNameFrom($this->name_source);

        $r = $this->getDefaultRenderer();
        $html = $this->brutallyTrimHTML($r->render($text));
        $expected = $this->brutallyTrimHTML('
        <div class="form-group row">
            <label for="id_1" class="control-label col-sm-3">label</label>
            <div class="col-sm-9">
                <div id="container-id_1" class="form-control form-control-sm il-input-tag-container">
                    <input id="id_1" name="name_0" class="form-control form-control-sm il-input-tag" value=""/> 
                </div>
                <div class="help-block">byline</div>
            </div>
        </div>
        ');
        $this->assertEquals($expected, $html);
    }

    public function testRenderError() : void
    {
        $f = $this->buildFactory();
        $label = "label";
        $byline = "byline";
        $name = "name_0";
        $tags = ["lorem", "ipsum", "dolor",];
        $error = "an_error";
        $text = $f->tag($label, $tags, $byline)->withNameFrom($this->name_source)->withError($error);

        $r = $this->getDefaultRenderer();
        $html = $this->brutallyTrimHTML($r->render($text));
        $expected = $this->brutallyTrimHTML('
           <div class="form-group row">
            <label for="id_1" class="control-label col-sm-3">label</label>
            <div class="col-sm-9">
                <div class="help-block alert alert-danger" role="alert">an_error</div>
                <div id="container-id_1" class="form-control form-control-sm il-input-tag-container">
                    <input id="id_1" name="name_0" class="form-control form-control-sm il-input-tag" value=""/> 
                </div>
                <div class="help-block">byline</div>
            </div>
        </div>     
        ');
        $this->assertEquals($expected, $html);
    }

    public function testRenderNoByline() : void
    {
        $f = $this->buildFactory();
        $label = "label";
        $name = "name_0";
        $tags = ["lorem", "ipsum", "dolor",];
        $text = $f->tag($label, $tags)->withNameFrom($this->name_source);

        $r = $this->getDefaultRenderer();
        $html = $this->brutallyTrimHTML($r->render($text));
        $expected = $this->brutallyTrimHTML('
        <div class="form-group row">
            <label for="id_1" class="control-label col-sm-3">label</label>
            <div class="col-sm-9">
                <div id="container-id_1" class="form-control form-control-sm il-input-tag-container">
                    <input id="id_1" name="name_0" class="form-control form-control-sm il-input-tag" value=""/> 
                </div>
            </div>
        </div>
        ');
        $this->assertEquals($expected, $html);
    }

    public function testRenderRequired() : void
    {
        $f = $this->buildFactory();
        $label = "label";
        $name = "name_0";
        $tags = ["lorem", "ipsum", "dolor",];
        $text = $f->tag($label, $tags)->withNameFrom($this->name_source)->withRequired(true);

        $r = $this->getDefaultRenderer();
        $html = $this->brutallyTrimHTML($r->render($text));

        $expected = $this->brutallyTrimHTML('
        <div class="form-group row">
            <label for="id_1" class="control-label col-sm-3">label<span class="asterisk">*</span></label>
            <div class="col-sm-9">
                <div id="container-id_1" class="form-control form-control-sm il-input-tag-container">
                    <input id="id_1" name="name_0" class="form-control form-control-sm il-input-tag" value=""/> 
                </div>
            </div>
        </div>
        ');

        $this->assertEquals($expected, $html);
    }

    public function testRenderDisabled() : void
    {
        $f = $this->buildFactory();
        $label = "label";
        $tags = ["lorem", "ipsum", "dolor",];
        $text = $f->tag($label, $tags)->withNameFrom($this->name_source)->withDisabled(true);

        $r = $this->getDefaultRenderer();
        $html = $this->brutallyTrimHTML($r->render($text));

        $expected = $this->brutallyTrimHTML('
        <div class="form-group row">
            <label for="id_1" class="control-label col-sm-3">label</label>
            <div class="col-sm-9">
                <div id="container-id_1" class="form-control form-control-sm il-input-tag-container disabled">
                    <input id="id_1" name="name_0" class="form-control form-control-sm il-input-tag" readonly value=""/> 
                </div>
            </div>
        </div>
        ');

        $this->assertEquals($expected, $html);
    }

    public function testValueRequired() : void
    {
        $f = $this->buildFactory();
        $label = "label";
        $name = "name_0";
        $tags = ["lorem", "ipsum", "dolor",];
        /** @var \ILIAS\UI\Implementation\Component\Input\Field\Tag $tag */
        $tag = $f->tag($label, $tags)->withNameFrom($this->name_source)->withRequired(true);

        $raw_value1 = "lorem,ipsum";
        $expected_result = ['lorem', 'ipsum'];
        $tag1 = $tag->withInput(new DefInputData([$name => $raw_value1]));
        $value1 = $tag1->getContent();
        $this->assertTrue($value1->isOk());
        $value = $value1->value();
        $this->assertEquals($expected_result, $value);
    }

    public function testEmptyStringAsInputLeadToException() : void
    {
        $f = $this->buildFactory();
        $label = "label";
        $name = "name_0";
        $tags = ["lorem", "ipsum", "dolor",];
        /** @var \ILIAS\UI\Implementation\Component\Input\Field\Tag $tag */
        $tag = $f->tag($label, $tags)->withNameFrom($this->name_source)->withRequired(true);

        $tag2 = $tag->withInput(new DefInputData([$name => '']));
        $result = $tag2->getContent();
        $this->assertTrue($result->isOk());
        $this->assertEquals([], $result->value());
    }

    public function testNullValueLeadsToException() : void
    {
        $f = $this->buildFactory();
        $label = "label";
        $name = "name_0";
        $tags = ["lorem", "ipsum", "dolor",];

        $tag = $f->tag($label, $tags)->withNameFrom($this->name_source)->withRequired(true);
        $tag2 = $tag->withInput(new DefInputData([$name => null]));
        $value2 = $tag2->getContent();
        $this->assertTrue($value2->isError());
    }

    public function testUserCreatedNotAllowed() : void
    {
        $this->markTestSkipped("This is supposed to work, but currently does not.");

        $f = $this->buildFactory();
        $tags = ["lorem", "ipsum", "dolor",];
        $tag = $f->tag("label", $tags)->withUserCreatedTagsAllowed(false)->withNameFrom($this->name_source);

        $tag1 = $tag->withInput(
            new DefInputData(
                ["name_0" => "lorem,ipsum"]
            )
        );
        $value1 = $tag1->getContent();
        $this->assertTrue($value1->isOk());
        $value = $value1->value();
        $this->assertEquals(
            ["lorem", "ipsum"],
            $value
        );

        $tag1 = $tag->withInput(
            new DefInputData(
                ["name_0" => "conseptetuer,ipsum"]
            )
        );
        $value1 = $tag1->getContent();
        $this->assertTrue($value1->isError());
    }


    public function testMaxTagsOk() : void
    {
        $f = $this->buildFactory();

        $tag = $f->tag("label", [])->withMaxTags(3)->withNameFrom($this->name_source)->withInput(
            new DefInputData(["name_0" => "lorem,ipsum"])
        );
        $value = $tag->getContent();
        $this->assertTrue($value->isOk());
    }


    public function test_max_tags_not_ok() : void
    {
        $f = $this->buildFactory();

        $this->expectException(\InvalidArgumentException::class);
        $f->tag("label", [])->withMaxTags(2)->withNameFrom($this->name_source)->withInput(
            new DefInputData(
                ["name_0" => "lorem,ipsum,dolor"]
            )
        );
    }


    public function testMaxTaglengthTagsOk() : void
    {
        $f = $this->buildFactory();

        $tag = $f->tag("label", [])->withTagMaxLength(10)->withNameFrom($this->name_source)->withInput(
            new DefInputData(["name_0" => "lorem,ipsum"])
        );
        $value = $tag->getContent();
        $this->assertTrue($value->isOk());
    }


    public function testMaxTaglengthTagsNotOk() : void
    {
        $f = $this->buildFactory();

        $this->expectException(\InvalidArgumentException::class);
        $f->tag("label", [])->withTagMaxLength(2)->withNameFrom($this->name_source)->withInput(
            new DefInputData(
                ["name_0" => "lorem,ipsum,dolor"]
            )
        );
    }
}
