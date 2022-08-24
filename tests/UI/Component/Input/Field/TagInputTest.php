<?php

declare(strict_types=1);

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

require_once(__DIR__ . "/../../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../Base.php");
require_once(__DIR__ . "/InputTest.php");

use ILIAS\UI\Implementation\Component as I;
use ILIAS\UI\Implementation\Component\SignalGenerator;
use ILIAS\Data;
use ILIAS\Refinery\Factory as Refinery;

/**
 * Class TagInputTest
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class TagInputTest extends ILIAS_UI_TestBase
{
    protected DefNamesource $name_source;

    public function setUp(): void
    {
        $this->name_source = new DefNamesource();
    }

    protected function buildFactory(): I\Input\Field\Factory
    {
        $df = new Data\Factory();
        $language = $this->createMock(ilLanguage::class);
        return new I\Input\Field\Factory(
            $this->createMock(\ILIAS\UI\Implementation\Component\Input\UploadLimitResolver::class),
            new SignalGenerator(),
            $df,
            new Refinery($df, $language),
            $language
        );
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testImplementsFactoryInterface(): void
    {
        $f = $this->buildFactory();

        $f->tag(
            "label",
            ["lorem", "ipsum", "dolor"],
            "byline"
        );
    }

    public function testRender(): void
    {
        $f = $this->buildFactory();
        $label = "label";
        $byline = "byline";
        $tags = ["lorem", "ipsum", "dolor",];
        $text = $f->tag($label, $tags, $byline)->withNameFrom($this->name_source);

        $r = $this->getDefaultRenderer();
        $html = $this->brutallyTrimHTML($r->render($text));
        $expected = $this->brutallyTrimHTML('
        <div class="form-group row">
            <label for="id_1" class="control-label col-sm-4 col-md-3 col-lg-2">label</label>
            <div class="col-sm-8 col-md-9 col-lg-10">
                <div id="container-id_1" class="form-control form-control-sm il-input-tag-container">
                    <input id="id_1" name="name_0" class="form-control form-control-sm il-input-tag" value=""/> 
                </div>
                <div class="help-block">byline</div>
            </div>
        </div>
        ');
        $this->assertEquals($expected, $html);
    }

    public function testRenderError(): void
    {
        $f = $this->buildFactory();
        $label = "label";
        $byline = "byline";
        $tags = ["lorem", "ipsum", "dolor",];
        $error = "an_error";
        $text = $f->tag($label, $tags, $byline)->withNameFrom($this->name_source)->withError($error);

        $r = $this->getDefaultRenderer();
        $html = $this->brutallyTrimHTML($r->render($text));
        $expected = $this->brutallyTrimHTML('
           <div class="form-group row">
            <label for="id_1" class="control-label col-sm-4 col-md-3 col-lg-2">label</label>
            <div class="col-sm-8 col-md-9 col-lg-10">
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

    public function testRenderNoByline(): void
    {
        $f = $this->buildFactory();
        $label = "label";
        $tags = ["lorem", "ipsum", "dolor",];
        $text = $f->tag($label, $tags)->withNameFrom($this->name_source);

        $r = $this->getDefaultRenderer();
        $html = $this->brutallyTrimHTML($r->render($text));
        $expected = $this->brutallyTrimHTML('
        <div class="form-group row">
            <label for="id_1" class="control-label col-sm-4 col-md-3 col-lg-2">label</label>
            <div class="col-sm-8 col-md-9 col-lg-10">
                <div id="container-id_1" class="form-control form-control-sm il-input-tag-container">
                    <input id="id_1" name="name_0" class="form-control form-control-sm il-input-tag" value=""/> 
                </div>
            </div>
        </div>
        ');
        $this->assertEquals($expected, $html);
    }

    public function testRenderRequired(): void
    {
        $f = $this->buildFactory();
        $label = "label";
        $tags = ["lorem", "ipsum", "dolor",];
        $text = $f->tag($label, $tags)->withNameFrom($this->name_source)->withRequired(true);

        $r = $this->getDefaultRenderer();
        $html = $this->brutallyTrimHTML($r->render($text));

        $expected = $this->brutallyTrimHTML('
        <div class="form-group row">
            <label for="id_1" class="control-label col-sm-4 col-md-3 col-lg-2">label<span class="asterisk">*</span></label>
            <div class="col-sm-8 col-md-9 col-lg-10">
                <div id="container-id_1" class="form-control form-control-sm il-input-tag-container">
                    <input id="id_1" name="name_0" class="form-control form-control-sm il-input-tag" value=""/> 
                </div>
            </div>
        </div>
        ');

        $this->assertEquals($expected, $html);
    }

    public function testRenderDisabled(): void
    {
        $f = $this->buildFactory();
        $label = "label";
        $tags = ["lorem", "ipsum", "dolor",];
        $text = $f->tag($label, $tags)->withNameFrom($this->name_source)->withDisabled(true);

        $r = $this->getDefaultRenderer();
        $html = $this->brutallyTrimHTML($r->render($text));

        $expected = $this->brutallyTrimHTML('
        <div class="form-group row">
            <label for="id_1" class="control-label col-sm-4 col-md-3 col-lg-2">label</label>
            <div class="col-sm-8 col-md-9 col-lg-10">
                <div id="container-id_1" class="form-control form-control-sm il-input-tag-container disabled">
                    <input id="id_1" name="name_0" class="form-control form-control-sm il-input-tag" readonly value=""/> 
                </div>
            </div>
        </div>
        ');

        $this->assertEquals($expected, $html);
    }

    public function testValueRequired(): void
    {
        $f = $this->buildFactory();
        $label = "label";
        $name = "name_0";
        $tags = ["lorem", "ipsum", "dolor",];
        /** @var I\Input\Field\Tag $tag */
        $tag = $f->tag($label, $tags)->withNameFrom($this->name_source)->withRequired(true);

        $raw_value1 = "lorem,ipsum";
        $expected_result = ['lorem', 'ipsum'];
        $tag1 = $tag->withInput(new DefInputData([$name => $raw_value1]));
        $value1 = $tag1->getContent();
        $this->assertTrue($value1->isOk());
        $value = $value1->value();
        $this->assertEquals($expected_result, $value);
    }

    public function testEmptyStringAsInputLeadToException(): void
    {
        $f = $this->buildFactory();
        $label = "label";
        $name = "name_0";
        $tags = ["lorem", "ipsum", "dolor",];
        /** @var I\Input\Field\Tag $tag */
        $tag = $f->tag($label, $tags)->withNameFrom($this->name_source)->withRequired(true);

        $tag2 = $tag->withInput(new DefInputData([$name => '']));
        $result = $tag2->getContent();
        $this->assertFalse($result->isOk());
        try {
            $result->value();
            $this->fail();
        } catch (Exception $e) {
            $this->assertInstanceOf('ILIAS\Data\NotOKException', $e);
        }
    }

    public function testStringAsInputAsRequired(): void
    {
        $f = $this->buildFactory();
        $label = "label";
        $name = "name_0";
        $tags = ["lorem", "ipsum", "dolor",];
        /** @var I\Input\Field\Tag $tag */
        $tag = $f->tag($label, $tags)->withNameFrom($this->name_source)->withRequired(true);

        $tag2 = $tag->withInput(new DefInputData([$name => 'test']));
        $result = $tag2->getContent();
        $this->assertTrue($result->isOk());
        $this->assertEquals(['test'], $result->value());
    }

    public function testNullValueLeadsToException(): void
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

    public function testUserCreatedNotAllowed(): void
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

    public function testMaxTagsOk(): void
    {
        $f = $this->buildFactory();

        $tag = $f->tag("label", [])->withMaxTags(3)->withNameFrom($this->name_source)->withInput(
            new DefInputData(["name_0" => "lorem,ipsum"])
        );
        $value = $tag->getContent();
        $this->assertTrue($value->isOk());
    }

    public function test_max_tags_not_ok(): void
    {
        $f = $this->buildFactory();

        $this->expectException(InvalidArgumentException::class);
        $f->tag("label", [])->withMaxTags(2)->withNameFrom($this->name_source)->withInput(
            new DefInputData(
                ["name_0" => "lorem,ipsum,dolor"]
            )
        );
    }

    public function testMaxTaglengthTagsOk(): void
    {
        $f = $this->buildFactory();

        $tag = $f->tag("label", [])->withTagMaxLength(10)->withNameFrom($this->name_source)->withInput(
            new DefInputData(["name_0" => "lorem,ipsum"])
        );
        $value = $tag->getContent();
        $this->assertTrue($value->isOk());
    }

    public function testMaxTaglengthTagsNotOk(): void
    {
        $f = $this->buildFactory();

        $this->expectException(InvalidArgumentException::class);
        $f->tag("label", [])->withTagMaxLength(2)->withNameFrom($this->name_source)->withInput(
            new DefInputData(
                ["name_0" => "lorem,ipsum,dolor"]
            )
        );
    }
}
