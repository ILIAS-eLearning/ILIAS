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

use ILIAS\UI\Implementation\Component\Input\Field;
use ILIAS\UI\Implementation\Component\Input\InputData;
use ILIAS\Data;
use ILIAS\Refinery\Factory as Refinery;
use PHPUnit\Framework\MockObject\MockObject;
use ILIAS\Data\Result\Ok;

abstract class Input1 extends Field\Input
{
}

abstract class Input2 extends Field\Input
{
}

class GroupInputTest extends ILIAS_UI_TestBase
{
    /**
     * @var Input1|mixed|MockObject
     */
    protected $child1;

    /**
     * @var Input2|mixed|MockObject
     */
    protected $child2;

    protected Data\Factory $data_factory;

    /**
     * @var ilLanguage|mixed|MockObject
     */
    protected $language;
    protected Refinery $refinery;
    protected Field\Group $group;

    public function setUp() : void
    {
        $this->child1 = $this->createMock(Input1::class);
        $this->child2 = $this->createMock(Input2::class);
        $this->data_factory = new Data\Factory;
        $this->language = $this->createMock(ilLanguage::class);
        $this->refinery = new Refinery($this->data_factory, $this->language);

        $this->group = new Field\Group(
            $this->data_factory,
            $this->refinery,
            $this->language,
            [$this->child1, $this->child2],
            "LABEL",
            "BYLINE"
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

        $new_group = $this->group->withDisabled(true);

        $this->assertEquals([$this->child2, $this->child1], $new_group->getInputs());
        $this->assertInstanceOf(Field\Group::class, $new_group);
        $this->assertNotSame($this->group, $new_group);
    }

    public function testWithRequiredRequiresChildren() : void
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

    public function testGroupMayOnlyHaveInputChildren() : void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->group = new Field\Group(
            $this->data_factory,
            $this->refinery,
            $this->language,
            ["foo", "bar"],
            "LABEL",
            "BYLINE"
        );
    }

    public function testGroupForwardsValuesOnWithValue() : void
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

    public function testWithValuePreservesKeys() : void
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

    public function testGroupOnlyDoesNoAcceptNonArrayValue() : void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->group->withValue(1);
    }

    public function testGroupOnlyDoesNoAcceptArrayValuesWithWrongLength() : void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->group->withValue([1]);
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
            ->expects($this->once())
            ->method("getValue")
            ->with()
            ->willReturn("two");

        $vals = $this->group->getValue();

        $this->assertEquals(["one", "two"], $vals);
    }

    public function testWithInputCallsChildrenAndAppliesOperations() : void
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
            ->withAdditionalTransformation($this->refinery->custom()->transformation(function ($v) use (&$called) : string {
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

    public function testWithInputDoesNotApplyOperationsOnError() : void
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
            ->withAdditionalTransformation($this->refinery->custom()->transformation(function () : void {
                $this->fail("This should not happen.");
            }))
            ->withInput($input_data);

        $this->assertEquals([$this->child2, $this->child1], $new_group->getInputs());
        $this->assertInstanceOf(Field\Group::class, $new_group);
        $this->assertNotSame($this->group, $new_group);
        $this->assertTrue($new_group->getContent()->isError());
    }

    public function testErrorIsI18NOnError() : void
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

    public function testWithoutChildren() : void
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
        $this->assertInstanceOf(Ok::class, $content);
        $this->assertCount(0, $content->value());
    }

    public function getFieldFactory() : Field\Factory
    {
        return new Field\Factory(
            $this->createMock(\ILIAS\UI\Implementation\Component\Input\UploadLimitResolver::class),
            new IncrementalSignalGenerator(),
            new Data\Factory(),
            $this->getRefinery(),
            $this->getLanguage()
        );
    }

    public function testGroupRendering() : void
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
            <label for="id_1" class="control-label col-sm-4 col-md-3 col-lg-2">input1</label>
            <div class="col-sm-8 col-md-9 col-lg-10">
                <input id="id_1" type="text" name="" class="form-control form-control-sm" />
                <div class="help-block">in 1</div>
            </div>
        </div>
        <div class="form-group row">
            <label for="id_2" class="control-label col-sm-4 col-md-3 col-lg-2">input2</label>
            <div class="col-sm-8 col-md-9 col-lg-10">
                <input id="id_2" type="text" name="" class="form-control form-control-sm" />
                <div class="help-block">in 2</div>
            </div>
        </div>
EOT;
        $actual = $this->brutallyTrimHTML($this->getDefaultRenderer()->render($group));
        $expected = $this->brutallyTrimHTML($expected);
        $this->assertEquals($expected, $actual);
    }

    public function testBylineProperty() : void
    {
        $bl = 'some byline';
        $f = $this->getFieldFactory();
        $group = $f->group([],"LABEL",$bl);
        $this->assertEquals($bl, $group->getByline());
    }
}
