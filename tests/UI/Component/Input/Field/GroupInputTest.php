<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once(__DIR__ . "/../../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../Base.php");

use ILIAS\UI\Implementation\Component\Input\Field;
use ILIAS\UI\Implementation\Component\Input\InputData;
use \ILIAS\Data;

abstract class Input1 extends Field\Input
{
};
abstract class Input2 extends Field\Input
{
};

class GroupInputTest extends ILIAS_UI_TestBase
{
    /**
     * @var \ILIAS\Refinery\Factory
     */
    private $refinery;

    public function setUp() : void
    {
        $this->child1 = $this->createMock(Input1::class);
        $this->child2 = $this->createMock(Input2::class);
        $this->data_factory = new Data\Factory;
        $this->language = $this->createMock(\ilLanguage::class);
        $this->refinery = new \ILIAS\Refinery\Factory($this->data_factory, $this->language);

        $this->group = new Field\Group(
            $this->data_factory,
            $this->refinery,
            $this->language,
            [$this->child1, $this->child2],
            "LABEL",
            "BYLINE"
        );
    }

    public function testWithDisabledDisablesChildren()
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

        $new_group = $this->group->withDisabled(true);

        $this->assertEquals([$this->child2, $this->child1], $new_group->getInputs());
        $this->assertInstanceOf(Field\Group::class, $new_group);
        $this->assertNotSame($this->group, $new_group);
    }

    public function testWithRequiredRequiresChildren()
    {
        $this->assertNotSame($this->child1, $this->child2);

        $this->child1
            ->expects($this->once())
            ->method("withRequired")
            ->with(true)
            ->willReturn($this->child2);
        $this->child2
            ->expects($this->once())
            ->method("withRequired")
            ->with(true)
            ->willReturn($this->child1);

        $new_group = $this->group->withRequired(true);

        $this->assertEquals([$this->child2, $this->child1], $new_group->getInputs());
        $this->assertInstanceOf(Field\Group::class, $new_group);
        $this->assertNotSame($this->group, $new_group);
    }

    public function testGroupMayOnlyHaveInputChildren()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->group = new Field\Group(
            $this->data_factory,
            $this->refinery,
            $this->language,
            ["foo", "bar"],
            "LABEL",
            "BYLINE"
        );
    }

    public function testGroupForwardsValuesOnWithValue()
    {
        $this->assertNotSame($this->child1, $this->child2);

        $this->child1
            ->expects($this->once())
            ->method("withValue")
            ->with(1)
            ->willReturn($this->child2);
        $this->child1
            ->expects($this->once())
            ->method("isClientSideValueOk")
            ->with(1)
            ->willReturn(true);
        $this->child2
            ->expects($this->once())
            ->method("withValue")
            ->with(2)
            ->willReturn($this->child1);
        $this->child2
            ->expects($this->once())
            ->method("isClientSideValueOk")
            ->with(2)
            ->willReturn(true);

        $new_group = $this->group->withValue([1,2]);

        $this->assertEquals([$this->child2, $this->child1], $new_group->getInputs());
        $this->assertInstanceOf(Field\Group::class, $new_group);
        $this->assertNotSame($this->group, $new_group);
    }

    public function testWithValuePreservesKeys()
    {
        $this->assertNotSame($this->child1, $this->child2);

        $this->group = new Field\Group(
            $this->data_factory,
            $this->refinery,
            $this->language,
            ["child1" => $this->child1, "child2" => $this->child2],
            "LABEL",
            "BYLINE"
        );

        $this->child1
            ->method("withValue")
            ->willReturn($this->child2);
        $this->child1
            ->method("isClientSideValueOk")
            ->willReturn(true);
        $this->child2
            ->method("withValue")
            ->willReturn($this->child1);
        $this->child2
            ->method("isClientSideValueOk")
            ->willReturn(true);

        $new_group = $this->group->withValue(["child1" => 1,"child2" => 2]);

        $this->assertEquals(["child1" => $this->child2, "child2" => $this->child1], $new_group->getInputs());
    }


    public function testGroupOnlyDoesNoAcceptNonArrayValue()
    {
        $this->expectException(\InvalidArgumentException::class);

        $new_group = $this->group->withValue(1);
    }

    public function testGroupOnlyDoesNoAcceptArrayValuesWithWrongLength()
    {
        $this->expectException(\InvalidArgumentException::class);

        $new_group = $this->group->withValue([1]);
    }

    public function testGroupForwardsValuesOnGetValue()
    {
        $this->assertNotSame($this->child1, $this->child2);

        $this->child1
            ->expects($this->once())
            ->method("getValue")
            ->with()
            ->willReturn("one");
        $this->child2
            ->expects($this->once())
            ->method("getValue")
            ->with()
            ->willReturn("two");

        $vals = $this->group->getValue();

        $this->assertEquals(["one", "two"], $vals);
    }

    public function testWithInputCallsChildrenAndAppliesOperations()
    {
        $this->assertNotSame($this->child1, $this->child2);

        $input_data = $this->createMock(InputData::class);

        $this->child1
            ->expects($this->once())
            ->method("withInput")
            ->with($input_data)
            ->willReturn($this->child2);
        $this->child1
            ->expects($this->once())
            ->method("getContent")
            ->willReturn($this->data_factory->ok("one"));
        $this->child2
            ->expects($this->once())
            ->method("withInput")
            ->with($input_data)
            ->willReturn($this->child1);
        $this->child2
            ->expects($this->once())
            ->method("getContent")
            ->willReturn($this->data_factory->ok("two"));

        $called = false;
        $new_group = $this->group
            ->withAdditionalTransformation($this->refinery->custom()->transformation(function ($v) use (&$called) {
                $called = true;
                $this->assertEquals(["two", "one"], $v);
                return "result";
            }))
            ->withInput($input_data);

        $this->assertTrue($called);
        $this->assertEquals([$this->child2, $this->child1], $new_group->getInputs());
        $this->assertInstanceOf(Field\Group::class, $new_group);
        $this->assertNotSame($this->group, $new_group);
        $this->assertEquals($this->data_factory->ok("result"), $new_group->getContent());
    }

    public function testWithInputDoesNotApplyOperationsOnError()
    {
        $this->assertNotSame($this->child1, $this->child2);

        $input_data = $this->createMock(InputData::class);

        $this->child1
            ->expects($this->once())
            ->method("withInput")
            ->with($input_data)
            ->willReturn($this->child2);
        $this->child1
            ->expects($this->once())
            ->method("getContent")
            ->willReturn($this->data_factory->error(""));
        $this->child2
            ->expects($this->once())
            ->method("withInput")
            ->with($input_data)
            ->willReturn($this->child1);
        $this->child2
            ->expects($this->once())
            ->method("getContent")
            ->willReturn($this->data_factory->ok("two"));

        $i18n = "THERE IS SOME ERROR IN THIS GROUP";
        $this->language
            ->expects($this->once())
            ->method("txt")
            ->with("ui_error_in_group")
            ->willReturn($i18n);

        $new_group = $this->group
            ->withAdditionalTransformation($this->refinery->custom()->transformation(function ($v) {
                $this->assertFalse(true, "This should not happen.");
            }))
            ->withInput($input_data);

        $this->assertEquals([$this->child2, $this->child1], $new_group->getInputs());
        $this->assertInstanceOf(Field\Group::class, $new_group);
        $this->assertNotSame($this->group, $new_group);
        $this->assertTrue($new_group->getContent()->isError());
    }

    public function testErrorIsI18NOnError()
    {
        $this->assertNotSame($this->child1, $this->child2);

        $input_data = $this->createMock(InputData::class);

        $this->child1
            ->method("withInput")
            ->willReturn($this->child2);
        $this->child1
            ->method("getContent")
            ->willReturn($this->data_factory->error(""));
        $this->child2
            ->method("withInput")
            ->willReturn($this->child1);
        $this->child2
            ->method("getContent")
            ->willReturn($this->data_factory->ok("two"));

        $i18n = "THERE IS SOME ERROR IN THIS GROUP";
        $this->language
            ->expects($this->once())
            ->method("txt")
            ->with("ui_error_in_group")
            ->willReturn($i18n);

        $new_group = $this->group
            ->withInput($input_data);

        $this->assertTrue($new_group->getContent()->isError());
        $this->assertEquals($i18n, $new_group->getContent()->error());
    }
    public function testWithoutChildren()
    {
        $group = new Field\Group(
            $this->data_factory,
            $this->refinery,
            $this->language,
            [],
            "LABEL",
            "BYLINE"
        );
        $content = $group->getContent();
        $this->assertInstanceOf(\ILIAS\Data\Result\Ok::class, $content);
        $this->assertCount(0, $content->value());
    }

    public function getFieldFactory()
    {
        $factory = new Field\Factory(
            new IncrementalSignalGenerator(),
            new Data\Factory(),
            $this->getRefinery(),
            $this->getLanguage()
        );
        return $factory;
    }

    public function testGroupRendering()
    {
        $f = $this->getFieldFactory();
        $inputs = [
            $f->text("input1", "in 1"),
            $f->text("input2", "in 2")
        ];
        $label = 'group label';
        $group = $f->group($inputs, $label);

        $expected = <<<EOT
        <div class="form-group row">
            <label for="id_1" class="control-label col-sm-3">input1</label>
            <div class="col-sm-9">
                <input id="id_1" type="text" name="" class="form-control form-control-sm" />
                <div class="help-block">in 1</div>
            </div>
        </div>
        <div class="form-group row">
            <label for="id_2" class="control-label col-sm-3">input2</label>
            <div class="col-sm-9">
                <input id="id_2" type="text" name="" class="form-control form-control-sm" />
                <div class="help-block">in 2</div>
            </div>
        </div>
EOT;
        $actual = $this->brutallyTrimHTML($this->getDefaultRenderer()->render($group));
        $expected = $this->brutallyTrimHTML($expected);
        $this->assertEquals($expected, $actual);
    }
}
