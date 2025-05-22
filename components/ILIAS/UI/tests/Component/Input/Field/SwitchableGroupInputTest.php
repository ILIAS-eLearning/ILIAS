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
require_once(__DIR__ . "/CommonFieldRendering.php");

use ILIAS\UI\Implementation\Component as I;
use ILIAS\UI\Implementation\Component\Input\Field\SwitchableGroup;
use ILIAS\UI\Implementation\Component\Input\Field\Group;
use ILIAS\UI\Implementation\Component\Input\NameSource;
use ILIAS\UI\Component\Input\InputData;
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
    use CommonFieldRendering;

    /**
     * @var Group1|mixed|MockObject
     */
    protected $child1;

    /**
     * @var Group2|mixed|MockObject
     */
    protected $child2;

    /**
     * @var I\Input\Field\FormInputInternal|mixed|MockObject
     */
    protected $nested_child;

    /**
     * @var ILIAS\Language\Language|mixed|MockObject
     */
    protected $lng;

    protected Data\Factory $data_factory;
    protected Refinery $refinery;
    protected \ILIAS\UI\Component\Input\Field\Group $switchable_group;
    protected SwitchableGroup $group;

    public function setUp(): void
    {
        $this->nested_child = $this->createMock(I\Input\Field\FormInputInternal::class);
        $this->child1 = $this->createMock(Group1::class);
        $this->child2 = $this->createMock(Group2::class);
        $this->data_factory = new Data\Factory();
        $this->refinery = new Refinery($this->data_factory, $this->createMock(ILIAS\Language\Language::class));
        $this->lng = $this->createMock(ILIAS\Language\Language::class);

        $this->nested_child
            ->method("withNameFrom")
            ->willReturn($this->nested_child);

        $this->child1
            ->method("withNameFrom")
            ->willReturn($this->child1);
        $this->child1
            ->method("getInputs")
            ->willReturn([$this->nested_child]);

        $this->child2
            ->method("withNameFrom")
            ->willReturn($this->child2);
        $this->child2
            ->method("getInputs")
            ->willReturn([$this->nested_child]);

        $this->switchable_group = (new SwitchableGroup(
            $this->data_factory,
            $this->refinery,
            $this->lng,
            ["child1" => $this->child1, "child2" => $this->child2],
            "LABEL",
            "BYLINE"
        ))->withNameFrom(new class () implements NameSource {
            public function getNewName(): string
            {
                return "name0";
            }
        });
    }

    protected function buildFactory(): I\Input\Field\Factory
    {
        return new I\Input\Field\Factory(
            $this->createMock(\ILIAS\UI\Implementation\Component\Input\UploadLimitResolver::class),
            new SignalGenerator(),
            $this->data_factory,
            $this->refinery,
            $this->lng
        );
    }

    public function testWithDisabledDisablesChildren(): void
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

    public function testWithRequiredDoesNotRequire(): void
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

    public function testSwitchableGroupMayOnlyHaveGroupChildren(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->group = new SwitchableGroup(
            $this->data_factory,
            $this->refinery,
            $this->lng,
            [$this->createMock(I\Input\Field\FormInput::class)],
            "LABEL",
            "BYLINE"
        );
    }

    public function testSwitchableGroupForwardsValuesOnWithValue(): void
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

    public function testGroupOnlyDoesNotAcceptNonArrayValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->switchable_group->withValue(null);
    }

    public function testGroupOnlyDoesNoAcceptArrayValuesWithWrongLength(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->switchable_group->withValue([1, 2, 3]);
    }

    public function testGroupOnlyDoesAcceptKeyOnly(): void
    {
        $new_group = $this->switchable_group->withValue("child1");
        $this->assertEquals(["child1" => $this->child1, "child2" => $this->child2], $new_group->getInputs());
        $this->assertInstanceOf(SwitchableGroup::class, $new_group);
        $this->assertNotSame($this->switchable_group, $new_group);
    }

    public function testGroupOnlyDoesNotAcceptInvalidKey(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->switchable_group->withValue("child3");
    }

    public function testGroupForwardsValuesOnGetValue(): void
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

    public function testWithInputCallsChildrenAndAppliesOperations(): void
    {
        $this->assertNotSame($this->child1, $this->child2);

        $input_data = $this->createMock(InputData::class);

        $input_data
            ->expects($this->once())
            ->method("getOr")
            ->with("name0")
            ->willReturn("child1");

        $expected_result = $this->data_factory->ok("one");

        $this->child1
            ->expects($this->once())
            ->method("withInput")
            ->with($input_data)
            ->willReturn($this->child1);
        $this->child1
            ->expects($this->once())
            ->method("getContent")
            ->with()
            ->willReturn($expected_result);
        $this->nested_child
            ->expects($this->once())
            ->method("getContent")
            ->with()
            ->willReturn($expected_result);
        $this->child2
            ->expects($this->never())
            ->method("withInput");
        $this->child2
            ->expects($this->never())
            ->method("getContent");

        $called = false;
        $new_group = $this->switchable_group
            ->withAdditionalTransformation($this->refinery->custom()->transformation(function ($v) use (&$called): string {
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

    public function testWithInputDoesNotApplyOperationsOnError(): void
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
            ->withAdditionalTransformation($this->refinery->custom()->transformation(function (): void {
                $this->fail("This should not happen.");
            }))
            ->withInput($input_data);

        $this->assertEquals(["child1" => $this->child1, "child2" => $this->child2], $new_group->getInputs());
        $this->assertInstanceOf(SwitchableGroup::class, $new_group);
        $this->assertNotSame($this->switchable_group, $new_group);
        $this->assertTrue($new_group->getContent()->isError());
    }

    public function testErrorIsI18NOnError(): void
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

    public function testWithInputDoesNotAcceptUnknownKeys(): void
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
            ->withAdditionalTransformation($this->refinery->custom()->transformation(function () use (&$called): void {
                $this->fail("This should not happen.");
            }))
            ->withInput($input_data);
    }

    public function testRender(): SG
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

        $expected = <<<EOT
<fieldset class="c-input" data-il-ui-component="switchable-group-field-input" data-il-ui-input-name="" tabindex="0">
    <label>label</label>
    <div class="c-input__field">
        <fieldset class="c-input" data-il-ui-component="group-field-input" data-il-ui-input-name="">
            <label for="id_1">
                <input type="radio" id="id_1" value="g1" />
                <span></span>
            </label>
            <div class="c-input__field">
                <fieldset class="c-input" data-il-ui-component="text-field-input" data-il-ui-input-name=""><label
                        for="id_2">f</label>
                    <div class="c-input__field"><input id="id_2" type="text" class="c-field-text" /></div>
                    <div class="c-input__help-byline">some field</div>
                </fieldset>
            </div>
        </fieldset>
        <fieldset class="c-input" data-il-ui-component="group-field-input" data-il-ui-input-name="">
            <label for="id_3">
                <input type="radio" id="id_3" value="g2" />
                <span></span>
            </label>
            <div class="c-input__field">
                <fieldset class="c-input" data-il-ui-component="text-field-input" data-il-ui-input-name=""><label
                        for="id_4">f2</label>
                    <div class="c-input__field"><input id="id_4" type="text" class="c-field-text" /></div>
                    <div class="c-input__help-byline">some other field</div>
                </fieldset>
            </div>
        </fieldset>
    </div>
    <div class="c-input__help-byline">byline</div>
</fieldset>
EOT;
        $this->assertEquals(
            $this->brutallyTrimHTML($expected),
            $this->render($sg)
        );
        return $sg;
    }

    /**
     * @depends testRender
     */
    public function testRenderWithValue(SG $sg): void
    {
        $r = $this->getDefaultRenderer();
        $html = $this->render($sg->withValue('g2'));
        $expected = '<label for="id_3"><input type="radio" id="id_3" value="g2" checked="checked" />';
        $this->assertStringContainsString($expected, $this->render($sg->withValue('g2')));
    }

    public function testRenderWithValueByIndex(): void
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
        $empty_group_title = 'empty group, the title';
        $empty_group_byline = 'empty group, the byline';
        $group3 = $f->group([], $empty_group_title, $empty_group_byline);

        $sg = $f->switchableGroup([$group1, $group2, $group3], $label, $byline);

        $expected = '<label for="id_3"><input type="radio" id="id_3" value="1" checked="checked" />';
        $this->assertStringContainsString($expected, $this->render($sg->withValue('1')));
    }

    public function testCommonRendering(): void
    {
        $f = $this->getFieldFactory();
        $label = "label";

        $group1 = $f->group([
            "field_1" => $f->text("f")
        ]);
        $group2 = $f->group([
            "field_2" => $f->text("f2")
        ]);

        $sg = $f->switchableGroup(
            [
                "g1" => $group1,
                "g2" => $group2
            ],
            $label
        )->withNameFrom((new DefNamesource()));

        $this->testWithError($sg);
        $this->testWithNoByline($sg);
        $this->testWithRequired($sg);
        $this->testWithDisabled($sg);
        $this->testWithAdditionalOnloadCodeRendersId($sg);
    }
}
