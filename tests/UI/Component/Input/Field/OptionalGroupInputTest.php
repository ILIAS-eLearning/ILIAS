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

use ILIAS\UI\Implementation\Component\Input\Field\OptionalGroup;
use ILIAS\UI\Implementation\Component\Input\Field\Input;
use ILIAS\UI\Implementation\Component\Input\NameSource;
use ILIAS\UI\Implementation\Component\Input\InputData;
use ILIAS\Data;
use ILIAS\Refinery\Factory as Refinery;
use PHPUnit\Framework\MockObject\MockObject;

abstract class Input11 extends Input
{
};
abstract class Input12 extends Input
{
};

class OptionalGroupInputTest extends ILIAS_UI_TestBase
{
    /**
     * @var Input11|mixed|MockObject
     */
    protected $child1;

    /**
     * @var Input12|mixed|MockObject
     */
    protected $child2;

    protected Data\Factory $data_factory;

    /**
     * @var ilLanguage|mixed|MockObject
     */
    protected $language;

    protected Refinery $refinery;
    protected \ILIAS\UI\Component\Input\Field\Input $optional_group;
    protected OptionalGroup $group;

    public function setUp() : void
    {
        $this->child1 = $this->createMock(Input11::class);
        $this->child2 = $this->createMock(Input12::class);
        $this->data_factory = new Data\Factory();
        $this->language = $this->createMock(ilLanguage::class);
        $this->refinery = new ILIAS\Refinery\Factory($this->data_factory, $this->language);

        $this->child1
            ->method("withNameFrom")
            ->willReturn($this->child1);
        $this->child2
            ->method("withNameFrom")
            ->willReturn($this->child2);

        $this->optional_group = (new OptionalGroup(
            $this->data_factory,
            $this->refinery,
            $this->language,
            [$this->child1, $this->child2],
            "LABEL",
            "BYLINE"
        ))->withNameFrom(new class implements NameSource {
            public function getNewName() : string
            {
                return "name0";
            }
        });
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

        $new_group = $this->optional_group->withDisabled(true);

        $this->assertEquals([$this->child2, $this->child1], $new_group->getInputs());
        $this->assertInstanceOf(OptionalGroup::class, $new_group);
        $this->assertNotSame($this->optional_group, $new_group);
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

        $new_group = $this->optional_group->withRequired(true);

        $this->assertEquals([$this->child1, $this->child2], $new_group->getInputs());
        $this->assertInstanceOf(OptionalGroup::class, $new_group);
        $this->assertNotSame($this->optional_group, $new_group);
    }

    public function testOptionalGroupMayOnlyHaveInputChildren() : void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->group = new OptionalGroup(
            $this->data_factory,
            $this->refinery,
            $this->language,
            ["foo", "bar"],
            "LABEL",
            "BYLINE"
        );
    }

    public function testOptionalGroupForwardsValuesOnWithValue() : void
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

        $new_group = $this->optional_group->withValue([1,2]);

        $this->assertEquals([$this->child2, $this->child1], $new_group->getInputs());
        $this->assertInstanceOf(OptionalGroup::class, $new_group);
        $this->assertNotSame($this->optional_group, $new_group);
    }

    public function testGroupOnlyDoesNoAcceptNonArrayValue() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->optional_group->withValue(1);
    }

    public function testGroupOnlyDoesNoAcceptArrayValuesWithWrongLength() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->optional_group->withValue([1]);
    }

    public function testGroupAcceptsNullButDoesNotForward() : void
    {
        $this->assertNotSame($this->child1, $this->child2);

        $this->child1
            ->expects($this->never())
            ->method("withValue");
        $this->child1
            ->expects($this->never())
            ->method("isClientSideValueOk");
        $this->child2
            ->expects($this->never())
            ->method("withValue");
        $this->child2
            ->expects($this->never())
            ->method("isClientSideValueOk");

        $new_group = $this->optional_group->withValue(null);

        $this->assertEquals([$this->child1, $this->child2], $new_group->getInputs());
        $this->assertInstanceOf(OptionalGroup::class, $new_group);
        $this->assertNotSame($this->optional_group, $new_group);
        $this->assertEquals(null, $new_group->getValue());
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

        $vals = $this->optional_group->getValue();

        $this->assertEquals(["one", "two"], $vals);
    }

    public function testWithInputCallsChildrenAndAppliesOperations() : void
    {
        $this->assertNotSame($this->child1, $this->child2);

        $input_data = $this->createMock(InputData::class);

        $input_data
            ->expects($this->once())
            ->method("getOr")
            ->with("name0", null)
            ->willReturn("checked");

        $this->child1
            ->expects($this->once())
            ->method("withInput")
            ->with($input_data)
            ->willReturn($this->child2);
        $this->child1
            ->expects($this->once())
            ->method("getContent")
            ->with()
            ->willReturn($this->data_factory->ok("one"));
        $this->child2
            ->expects($this->once())
            ->method("withInput")
            ->with($input_data)
            ->willReturn($this->child1);
        $this->child2
            ->expects($this->once())
            ->method("getContent")
            ->with()
            ->willReturn($this->data_factory->ok("two"));

        $called = false;
        $new_group = $this->optional_group
            ->withAdditionalTransformation($this->refinery->custom()->transformation(function ($v) use (&$called) : string {
                $called = true;
                $this->assertEquals(["two", "one"], $v);
                return "result";
            }))
            ->withInput($input_data);

        $this->assertTrue($called);
        $this->assertEquals([$this->child2, $this->child1], $new_group->getInputs());
        $this->assertInstanceOf(OptionalGroup::class, $new_group);
        $this->assertNotSame($this->optional_group, $new_group);
        $this->assertEquals($this->data_factory->ok("result"), $new_group->getContent());
    }

    public function testWithInputDoesNotApplyOperationsOnError() : void
    {
        $this->assertNotSame($this->child1, $this->child2);

        $input_data = $this->createMock(InputData::class);

        $input_data
            ->expects($this->once())
            ->method("getOr")
            ->with("name0", null)
            ->willReturn("checked");

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

        $new_group = $this->optional_group
            ->withAdditionalTransformation($this->refinery->custom()->transformation(function () : void {
                $this->fail("This should not happen.");
            }))
            ->withInput($input_data);

        $this->assertEquals([$this->child2, $this->child1], $new_group->getInputs());
        $this->assertInstanceOf(OptionalGroup::class, $new_group);
        $this->assertNotSame($this->optional_group, $new_group);
        $this->assertTrue($new_group->getContent()->isError());
    }

    public function testWithInputDoesNotCallChildrenWhenUnchecked() : void
    {
        $this->assertNotSame($this->child1, $this->child2);

        $input_data = $this->createMock(InputData::class);

        $input_data
            ->expects($this->once())
            ->method("getOr")
            ->with("name0", null)
            ->willReturn(null);

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

        $called = false;
        $new_group = $this->optional_group
            ->withAdditionalTransformation($this->refinery->custom()->transformation(function ($v) use (&$called) : string {
                $called = true;
                $this->assertEquals(null, $v);
                return "result";
            }))
            ->withInput($input_data);

        $this->assertTrue($called);
        $this->assertEquals([$this->child1, $this->child2], $new_group->getInputs());
        $this->assertInstanceOf(OptionalGroup::class, $new_group);
        $this->assertNotSame($this->optional_group, $new_group);
        $this->assertEquals($this->data_factory->ok("result"), $new_group->getContent());
    }
}
