<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once(__DIR__ . "/../../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../Base.php");
require_once(__DIR__ . "/InputTest.php");

use ILIAS\UI\Implementation\Component\SignalGenerator;
use \ILIAS\Data;
use \ILIAS\Validation;
use \ILIAS\Transformation;

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


    public function setUp()
    {
        $this->name_source = new DefNamesource();
    }


    protected function buildFactory()
    {
        $df = new Data\Factory();
        return new ILIAS\UI\Implementation\Component\Input\Field\Factory(
            new SignalGenerator(),
            $df,
            new Validation\Factory($df, $this->createMock(\ilLanguage::class)),
            new Transformation\Factory()
        );
    }


    public function test_implements_factory_interface()
    {
        $f = $this->buildFactory();

        $tag = $f->tag(
            "label",
            ["lorem", "ipsum", "dolor",],
            "byline"
        );
    }


    public function test_render()
    {
        $f = $this->buildFactory();
        $label = "label";
        $byline = "byline";
        $tags = ["lorem", "ipsum", "dolor",];
        $name = "name_0";
        $text = $f->tag($label, $tags, $byline)->withNameFrom($this->name_source);

        $r = $this->getDefaultRenderer();
        $html = $this->normalizeHTML($r->render($text));

        $expected = "<div class=\"form-group row\">	<label for=\"name_0\" class=\"control-label col-sm-3\">label</label>	<div class=\"col-sm-9\">		<div id=\"container-id_1\" class=\"form-control form-control-sm il-input-tag\">	<input type=\"text\" id=\"id_1\" value=\"\" class=\"form-control form-control-sm\"/> <input type=\"hidden\" id=\"template-id_1\" value='name_0[]'>	</div>		<div class=\"help-block\">byline</div>			</div></div>";
        $this->assertEquals($expected, $html);
    }


    public function test_render_error()
    {
        $f = $this->buildFactory();
        $label = "label";
        $byline = "byline";
        $name = "name_0";
        $tags = ["lorem", "ipsum", "dolor",];
        $error = "an_error";
        $text = $f->tag($label, $tags, $byline)->withNameFrom($this->name_source)->withError($error);

        $r = $this->getDefaultRenderer();
        $html = $this->normalizeHTML($r->render($text));

        $expected
            = "<div class=\"form-group row\">	<label for=\"name_0\" class=\"control-label col-sm-3\">label</label>	<div class=\"col-sm-9\">		<div id=\"container-id_1\" class=\"form-control form-control-sm il-input-tag\">	<input type=\"text\" id=\"id_1\" value=\"\" class=\"form-control form-control-sm\"/> <input type=\"hidden\" id=\"template-id_1\" value='name_0[]'>	</div>		<div class=\"help-block\">byline</div>		<div class=\"help-block alert alert-danger\" role=\"alert\">			<img border=\"0\" src=\"./templates/default/images/icon_alert.svg\" alt=\"alert\" />			an_error		</div>	</div></div>";
        $this->assertEquals($expected, $html);
    }


    public function test_render_no_byline()
    {
        $f = $this->buildFactory();
        $label = "label";
        $name = "name_0";
        $tags = ["lorem", "ipsum", "dolor",];
        $text = $f->tag($label, $tags)->withNameFrom($this->name_source);

        $r = $this->getDefaultRenderer();
        $html = $this->normalizeHTML($r->render($text));

        $expected = "<div class=\"form-group row\">	<label for=\"name_0\" class=\"control-label col-sm-3\">label</label>	<div class=\"col-sm-9\">		<div id=\"container-id_1\" class=\"form-control form-control-sm il-input-tag\">	<input type=\"text\" id=\"id_1\" value=\"\" class=\"form-control form-control-sm\"/> <input type=\"hidden\" id=\"template-id_1\" value='name_0[]'>	</div>					</div></div>";
        $this->assertEquals($expected, $html);
    }


    public function test_render_value()
    {
        $f = $this->buildFactory();
        $label = "label";
        $value = ["lorem", "ipsum",];
        $name = "name_0";
        $tags = ["lorem", "ipsum", "dolor",];
        $text = $f->tag($label, $tags)->withValue($value)->withNameFrom($this->name_source);

        $r = $this->getDefaultRenderer();
        $html = $this->normalizeHTML($r->render($text));

        $expected
            = "<div class=\"form-group row\">	<label for=\"name_0\" class=\"control-label col-sm-3\">label</label>	<div class=\"col-sm-9\">		<div id=\"container-id_1\" class=\"form-control form-control-sm il-input-tag\">	<input type=\"text\" id=\"id_1\" value=\"lorem,ipsum\" class=\"form-control form-control-sm\"/> <input type=\"hidden\" id=\"template-id_1\" value='name_0[]'>		<input type=\"hidden\" id=\"tag-id_1-lorem\" name=\"name_0[]\" value='lorem'>		<input type=\"hidden\" id=\"tag-id_1-ipsum\" name=\"name_0[]\" value='ipsum'>	</div>					</div></div>";
        $this->assertEquals($expected, $html);
    }


    public function test_render_required()
    {
        $f = $this->buildFactory();
        $label = "label";
        $name = "name_0";
        $tags = ["lorem", "ipsum", "dolor",];
        $text = $f->tag($label, $tags)->withNameFrom($this->name_source)->withRequired(true);

        $r = $this->getDefaultRenderer();
        $html = $this->normalizeHTML($r->render($text));

        $expected = "<div class=\"form-group row\">	<label for=\"name_0\" class=\"control-label col-sm-3\">label<span class=\"asterisk\">*</span></label>	<div class=\"col-sm-9\">		<div id=\"container-id_1\" class=\"form-control form-control-sm il-input-tag\">	<input type=\"text\" id=\"id_1\" value=\"\" class=\"form-control form-control-sm\"/> <input type=\"hidden\" id=\"template-id_1\" value='name_0[]'>	</div>					</div></div>";

        $this->assertEquals($expected, $html);
    }


    public function test_value_required()
    {
        $f = $this->buildFactory();
        $label = "label";
        $name = "name_0";
        $tags = ["lorem", "ipsum", "dolor",];
        $tag = $f->tag($label, $tags)->withNameFrom($this->name_source)->withRequired(true);

        $raw_value1 = ["lorem", "ipsum",];
        $tag1 = $tag->withInput(new DefPostData([$name => $raw_value1]));
        $value1 = $tag1->getContent();
        $this->assertTrue($value1->isOk());
        $value = $value1->value();
        $this->assertEquals($raw_value1, $value);

        $tag2 = $tag->withInput(new DefPostData([$name => []]));
        $value2 = $tag2->getContent();
        $this->assertTrue($value2->isError());

        $tag2 = $tag->withInput(new DefPostData([$name => null]));
        $value2 = $tag2->getContent();
        $this->assertTrue($value2->isError());
    }


    public function test_user_created_not_allowed()
    {
        $f = $this->buildFactory();
        $tags = ["lorem", "ipsum", "dolor",];
        $tag = $f->tag("label", $tags)->withUserCreatedTagsAllowed(false)->withNameFrom($this->name_source);

        $tag1 = $tag->withInput(
            new DefPostData(
                ["name_0" => ["lorem", "ipsum",],]
            )
        );
        $value1 = $tag1->getContent();
        $this->assertTrue($value1->isOk());
        $value = $value1->value();
        $this->assertEquals(
            ["lorem", "ipsum",],
            $value
        );

        $tag1 = $tag->withInput(
            new DefPostData(
                ["name_0" => ["conseptetuer", "ipsum",],]
            )
        );
        $value1 = $tag1->getContent();
        $this->assertTrue($value1->isError());
    }


    public function test_max_tags_ok()
    {
        $f = $this->buildFactory();

        $tag = $f->tag("label", [])->withMaxTags(3)->withNameFrom($this->name_source)->withInput(
            new DefPostData(["name_0" => ["lorem", "ipsum",],])
        );
        $value = $tag->getContent();
        $this->assertTrue($value->isOk());
    }


    public function test_max_tags_not_ok()
    {
        $f = $this->buildFactory();

        $this->expectException(\InvalidArgumentException::class);
        $f->tag("label", [])->withMaxTags(2)->withNameFrom($this->name_source)->withInput(
            new DefPostData(
                ["name_0" => ["lorem", "ipsum", "dolor",],]
            )
        );
    }


    public function test_max_taglength_tags_ok()
    {
        $f = $this->buildFactory();

        $tag = $f->tag("label", [])->withTagMaxLength(10)->withNameFrom($this->name_source)->withInput(
            new DefPostData(["name_0" => ["lorem", "ipsum",],])
        );
        $value = $tag->getContent();
        $this->assertTrue($value->isOk());
    }


    public function test_max_taglength_tags_not_ok()
    {
        $f = $this->buildFactory();

        $this->expectException(\InvalidArgumentException::class);
        $f->tag("label", [])->withTagMaxLength(2)->withNameFrom($this->name_source)->withInput(
            new DefPostData(
                ["name_0" => ["lorem", "ipsum", "dolor",],]
            )
        );
    }
}
