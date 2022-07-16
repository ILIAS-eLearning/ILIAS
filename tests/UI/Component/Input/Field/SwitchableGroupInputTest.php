<?php declare(strict_types=1);

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

use ILIAS\UI\Implementation\Component as I;
use ILIAS\UI\Implementation\Component\Input\Field\SwitchableGroup;
use ILIAS\UI\Implementation\Component\Input\Field\Group;
use ILIAS\UI\Implementation\Component\Input\Field\Input;
use ILIAS\UI\Implementation\Component\Input\NameSource;
use ILIAS\UI\Implementation\Component\Input\InputData;
use ILIAS\Data;
use ILIAS\UI\Implementation\Component\SignalGenerator;
use ILIAS\Refinery\Factory as Refinery;
use PHPUnit\Framework\MockObject\MockObject;
use ILIAS\UI\Component\Input\Field\SwitchableGroup as SG;

class Group1 extends Group
{
}

class Group2 extends Group
{
}

class SwitchableGroupInputTest extends ILIAS_UI_TestBase
{
    /**
     * @var Group1|mixed|MockObject
     */
    protected $child1;

    /**
     * @var Group2|mixed|MockObject
     */
    protected $child2;

    /**
     * @var ilLanguage|mixed|MockObject
     */
    protected $lng;

    protected Data\Factory $data_factory;
    protected Refinery $refinery;
    protected \ILIAS\UI\Component\Input\Field\Input $switchable_group;
    protected SwitchableGroup $group;

    public function setUp() : void
    {
        $this->child1 = $this->createMock(Group1::class);
        $this->child2 = $this->createMock(Group2::class);
        $this->data_factory = new Data\Factory();
        $this->refinery = new Refinery($this->data_factory, $this->createMock(ilLanguage::class));
        $this->lng = $this->createMock(ilLanguage::class);

        $this->child1
            ->method("withNameFrom")
            ->willReturn($this->child1);
        $this->child1
            ->method("getInputs")
            ->willReturn([$this->child1]);

        $this->child2
            ->method("withNameFrom")
            ->willReturn($this->child2);
        $this->child2
            ->method("getInputs")
            ->willReturn([$this->child2]);


        $this->switchable_group = (new SwitchableGroup(
            $this->data_factory,
            $this->refinery,
            $this->lng,
            ["child1" => $this->child1, "child2" => $this->child2],
            "LABEL",
            "BYLINE"
        ))->withNameFrom(new class implements NameSource {
            public function getNewName() : string
            {
                return "name0";
            }
        });
    }

    protected function buildFactory() : I\Input\Field\Factory
    {
        return new I\Input\Field\Factory(
            $this->createMock(\ILIAS\UI\Implementation\Component\Input\UploadLimitResolver::class),
            new SignalGenerator(),
            $this->data_factory,
            $this->refinery,
            $this->lng
        );
    }

    public function testWithDisabledDisablesChildren() : void
    {
        $this->assertNotSame($this->child1, $this->child2);

        $this->child1
            ->expects($this->once())
            ->method("withDisabled")
            ->with(true)
            ->willReturn($this->child2);
        $this->child2
            ->expects($this->once())
            ->method("withDisabled")
            ->with(true)
            ->willReturn($this->child1);

        $new_group = $this->switchable_group->withDisabled(true);

        $this->assertEquals(["child1" => $this->child2, "child2" => $this->child1], $new_group->getInputs());
        $this->assertInstanceOf(SwitchableGroup::class, $new_group);
        $this->assertNotSame($this->switchable_group, $new_group);
    }

    public function testWithRequiredDoesNotRequire() : void
    {
        $this->assertNotSame($this->child1, $this->child2);

        $this->child1
            ->expects($this->never())
            ->method("withRequired");
        $this->child2
            ->expects($this->never())
            ->method("withRequired");

        $new_group = $this->switchable_group->withRequired(true);

        $this->assertEquals(["child1" => $this->child1, "child2" => $this->child2], $new_group->getInputs());
        $this->assertInstanceOf(SwitchableGroup::class, $new_group);
        $this->assertNotSame($this->switchable_group, $new_group);
    }

    public function testSwitchableGroupMayOnlyHaveGroupChildren() : void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->group = new SwitchableGroup(
            $this->data_factory,
            $this->refinery,
            $this->lng,
            [$this->createMock(Input::class)],
            "LABEL",
            "BYLINE"
        );
    }

    public function testSwitchableGroupForwardsValuesOnWithValue() : void
    {
        $this->assertNotSame($this->child1, $this->child2);

        $this->child1
            ->expects($this->never())
            ->method("withValue");
        $this->child2
            ->expects($this->once())
            ->method("withValue")
            ->with(2)
            ->willReturn($this->child2);

        $new_group = $this->switchable_group->withValue(["child2", 2]);

        $this->assertEquals(["child1" => $this->child1, "child2" => $this->child2], $new_group->getInputs());
        $this->assertInstanceOf(SwitchableGroup::class, $new_group);
        $this->assertNotSame($this->switchable_group, $new_group);
    }

    public function testGroupOnlyDoesNotAcceptNonArrayValue() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->switchable_group->withValue(null);
    }

    public function testGroupOnlyDoesNoAcceptArrayValuesWithWrongLength() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->switchable_group->withValue([1, 2, 3]);
    }

    public function testGroupOnlyDoesAcceptKeyOnly() : void
    {
        $new_group = $this->switchable_group->withValue("child1");
        $this->assertEquals(["child1" => $this->child1, "child2" => $this->child2], $new_group->getInputs());
        $this->assertInstanceOf(SwitchableGroup::class, $new_group);
        $this->assertNotSame($this->switchable_group, $new_group);
    }

    public function testGroupOnlyDoesNotAcceptInvalidKey() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->switchable_group->withValue("child3");
    }

    public function testGroupForwardsValuesOnGetValue() : void
    {
        $this->assertNotSame($this->child1, $this->child2);

        $this->child1
            ->expects($this->once())
            ->method("getValue")
            ->with()
            ->willReturn("one");
        $this->child2
            ->expects($this->never())
            ->method("getValue");

        $vals = $this->switchable_group->withValue("child1")->getValue();

        $this->assertEquals(["child1", "one"], $vals);
    }

    public function testWithInputCallsChildrenAndAppliesOperations() : void
    {
        $this->assertNotSame($this->child1, $this->child2);

        $input_data = $this->createMock(InputData::class);

        $input_data
            ->expects($this->once())
            ->method("getOr")
            ->with("name0")
            ->willReturn("child1");

        $this->child1
            ->expects($this->once())
            ->method("withInput")
            ->with($input_data)
            ->willReturn($this->child1);
        $this->child1
            ->expects($this->exactly(2))
            ->method("getContent")
            ->with()
            ->willReturn($this->data_factory->ok("one"));
        $this->child2
            ->expects($this->never())
            ->method("withInput");
        $this->child2
            ->expects($this->never())
            ->method("getContent");

        $called = false;
        $new_group = $this->switchable_group
            ->withAdditionalTransformation($this->refinery->custom()->transformation(function ($v) use (&$called) : string {
                $called = true;
                $this->assertEquals(["child1", ["one"]], $v);
                return "result";
            }))
            ->withInput($input_data);

        $this->assertTrue($called);
        $this->assertEquals(["child1" => $this->child1, "child2" => $this->child2], $new_group->getInputs());
        $this->assertInstanceOf(SwitchableGroup::class, $new_group);
        $this->assertNotSame($this->switchable_group, $new_group);
        $this->assertEquals($this->data_factory->ok("result"), $new_group->getContent());
    }

    public function testWithInputDoesNotApplyOperationsOnError() : void
    {
        $this->assertNotSame($this->child1, $this->child2);

        $input_data = $this->createMock(InputData::class);

        $input_data
            ->expects($this->once())
            ->method("getOr")
            ->with("name0")
            ->willReturn("child2");

        $this->child1
            ->expects($this->never())
            ->method("withInput");
        $this->child1
            ->expects($this->never())
            ->method("getContent");
        $this->child2
            ->expects($this->once())
            ->method("withInput")
            ->with($input_data)
            ->willReturn($this->child2);
        $this->child2
            ->expects($this->once())
            ->method("getContent")
            ->willReturn($this->data_factory->error(""));

        $i18n = "THERE IS SOME ERROR IN THIS GROUP";
        $this->lng
            ->expects($this->once())
            ->method("txt")
            ->with("ui_error_in_group")
            ->willReturn($i18n);

        $new_group = $this->switchable_group
            ->withAdditionalTransformation($this->refinery->custom()->transformation(function () : void {
                $this->fail("This should not happen.");
            }))
            ->withInput($input_data);

        $this->assertEquals(["child1" => $this->child1, "child2" => $this->child2], $new_group->getInputs());
        $this->assertInstanceOf(SwitchableGroup::class, $new_group);
        $this->assertNotSame($this->switchable_group, $new_group);
        $this->assertTrue($new_group->getContent()->isError());
    }

    public function testErrorIsI18NOnError() : void
    {
        $this->assertNotSame($this->child1, $this->child2);

        $input_data = $this->createMock(InputData::class);

        $input_data
            ->expects($this->once())
            ->method("getOr")
            ->with("name0")
            ->willReturn("child2");

        $this->child2
            ->method("withInput")
            ->willReturn($this->child2);
        $this->child2
            ->method("getContent")
            ->willReturn($this->data_factory->error(""));

        $i18n = "THERE IS SOME ERROR IN THIS GROUP";
        $this->lng
            ->expects($this->once())
            ->method("txt")
            ->with("ui_error_in_group")
            ->willReturn($i18n);

        $switchable_group = $this->switchable_group
            ->withInput($input_data);

        $this->assertTrue($switchable_group->getContent()->isError());
        $this->assertEquals($i18n, $switchable_group->getContent()->error());
    }

    public function testWithInputDoesNotAcceptUnknownKeys() : void
    {
        $this->expectException(InvalidArgumentException::class);

        $input_data = $this->createMock(InputData::class);

        $input_data
            ->expects($this->once())
            ->method("getOr")
            ->with("name0")
            ->willReturn(123);

        $this->child1
            ->expects($this->never())
            ->method("withInput");
        $this->child1
            ->expects($this->never())
            ->method("getContent");
        $this->child2
            ->expects($this->never())
            ->method("withInput");
        $this->child2
            ->expects($this->never())
            ->method("getContent");

        $this->switchable_group
            ->withAdditionalTransformation($this->refinery->custom()->transformation(function () use (&$called) : void {
                $this->fail("This should not happen.");
            }))
            ->withInput($input_data);
    }

    public function testRender() : SG
    {
        $f = $this->buildFactory();
        $label = "label";
        $byline = "byline";

        $group1 = $f->group([
            "field_1" => $f->text("f", "some field")
        ]);
        $group2 = $f->group([
            "field_2" => $f->text("f2", "some other field")
        ]);

        $sg = $f->switchableGroup(
            [
                "g1" => $group1,
                "g2" => $group2
            ],
            $label,
            $byline
        );

        $r = $this->getDefaultRenderer();
        $html = $r->render($sg);
        $expected = <<<EOT
<div class="form-group row">
    <label class="control-label col-sm-4 col-md-3 col-lg-2">label</label>
    <div class="col-sm-8 col-md-9 col-lg-10">
        <div id="id_1" class="il-input-radio">
            <div class="form-control form-control-sm il-input-radiooption">
                <input type="radio" id="id_1_g1_opt" name="" value="g1" /><label for="id_1_g1_opt"></label>
                <div class="form-group row">
                    <label for="id_2" class="control-label col-sm-4 col-md-3 col-lg-2">f</label>
                    <div class="col-sm-8 col-md-9 col-lg-10">
                        <input id="id_2" type="text" name="" class="form-control form-control-sm" />
                        <div class="help-block">some field</div>
                    </div>
                </div>
            </div>
            <div class="form-control form-control-sm il-input-radiooption">
                <input type="radio" id="id_1_g2_opt" name="" value="g2" /><label for="id_1_g2_opt"></label>
                <div class="form-group row">
                    <label for="id_3" class="control-label col-sm-4 col-md-3 col-lg-2">f2</label>
                    <div class="col-sm-8 col-md-9 col-lg-10">
                        <input id="id_3" type="text" name="" class="form-control form-control-sm" />
                        <div class="help-block">some other field</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="help-block">byline</div>
    </div>
</div>

EOT;
        $this->assertEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($html)
        );
        return $sg;
    }

    /**
     * @depends testRender
     */
    public function testRenderWithValue(SG $sg) : void
    {
        $r = $this->getDefaultRenderer();
        $html = $r->render($sg->withValue('g2'));
        $expected = <<<EOT
<div class="form-group row">
    <label class="control-label col-sm-4 col-md-3 col-lg-2">label</label>
    <div class="col-sm-8 col-md-9 col-lg-10">
        <div id="id_1" class="il-input-radio">
            <div class="form-control form-control-sm il-input-radiooption">
                <input type="radio" id="id_1_g1_opt" name="" value="g1" /><label for="id_1_g1_opt"></label>
                <div class="form-group row">
                    <label for="id_2" class="control-label col-sm-4 col-md-3 col-lg-2">f</label>
                    <div class="col-sm-8 col-md-9 col-lg-10">
                        <input id="id_2" type="text" name="" class="form-control form-control-sm" />
                        <div class="help-block">some field</div>
                    </div>
                </div>
            </div>
            <div class="form-control form-control-sm il-input-radiooption">
                <input type="radio" id="id_1_g2_opt" name="" value="g2" checked="checked" /><label for="id_1_g2_opt"></label>
                <div class="form-group row">
                    <label for="id_3" class="control-label col-sm-4 col-md-3 col-lg-2">f2</label>
                    <div class="col-sm-8 col-md-9 col-lg-10">
                        <input id="id_3" type="text" name="" class="form-control form-control-sm" />
                        <div class="help-block">some other field</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="help-block">byline</div>
    </div>
</div>

EOT;
        $this->assertEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($html)
        );
    }

    public function testRenderWithValueByIndex() : void
    {
        $f = $this->buildFactory();
        $label = "label";
        $byline = "byline";

        $group1 = $f->group([
            "field_1" => $f->text("f", "some field")
        ]);
        $group2 = $f->group([
            "field_2" => $f->text("f2", "some other field")
        ]);

        //construct without string-key:
        $sg = $f->switchableGroup([$group1,$group2], $label, $byline);

        $r = $this->getDefaultRenderer();
        $html = $r->render($sg->withValue('1'));

        $expected = <<<EOT
<div class="form-group row">
    <label class="control-label col-sm-4 col-md-3 col-lg-2">label</label>
    <div class="col-sm-8 col-md-9 col-lg-10">
        <div id="id_1" class="il-input-radio">
            <div class="form-control form-control-sm il-input-radiooption">
                <input type="radio" id="id_1_0_opt" name="" value="0" /><label for="id_1_0_opt"></label>
                <div class="form-group row">
                    <label for="id_2" class="control-label col-sm-4 col-md-3 col-lg-2">f</label>
                    <div class="col-sm-8 col-md-9 col-lg-10">
                        <input id="id_2" type="text" name="" class="form-control form-control-sm" />
                        <div class="help-block">some field</div>
                    </div>
                </div>
            </div>
            <div class="form-control form-control-sm il-input-radiooption">
                <input type="radio" id="id_1_1_opt" name="" value="1" checked="checked" /><label for="id_1_1_opt"></label>
                <div class="form-group row">
                    <label for="id_3" class="control-label col-sm-4 col-md-3 col-lg-2">f2</label>
                    <div class="col-sm-8 col-md-9 col-lg-10">
                        <input id="id_3" type="text" name="" class="form-control form-control-sm" />
                        <div class="help-block">some other field</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="help-block">byline</div>
    </div>
</div>
EOT;
        $this->assertEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($html)
        );
    }
}
