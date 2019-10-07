<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once(__DIR__ . "/../../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../Base.php");

use ILIAS\UI\Implementation\Component\Input\Field\SwitchableGroup;
use ILIAS\UI\Implementation\Component\Input\Field\Group;
use ILIAS\UI\Implementation\Component\Input\Field\Input;
use ILIAS\UI\Implementation\Component\Input\NameSource;
use ILIAS\UI\Implementation\Component\Input\InputData;
use \ILIAS\Data;

class Group1 extends Group {};
class Group2 extends Group {};

class SwitchableGroupInputTest extends ILIAS_UI_TestBase {
	/**
	 * @var \ILIAS\Refinery\Factory
	 */
	private $refinery;

	public function setUp(): void{
		$this->child1 = $this->createMock(Group1::class);
		$this->child2 = $this->createMock(Group2::class);
		$this->data_factory = new Data\Factory();
		$this->refinery = new ILIAS\Refinery\Factory($this->data_factory, $this->createMock(\ilLanguage::class));

		$this->child1
			->method("withNameFrom")
			->willReturn($this->child1);
		$this->child2
			->method("withNameFrom")
			->willReturn($this->child2);

		$this->switchable_group = (new SwitchableGroup(
			$this->data_factory,
			$this->refinery,
			["child1" => $this->child1, "child2" => $this->child2],
			"LABEL",
			"BYLINE"
		))->withNameFrom(new class implements NameSource {
			public function getNewName() {
				return "name0";
			}
		});
	}

	public function testWithDisabledDisablesChildren() {
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

	public function testWithRequiredDoesNotRequire() {
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

	public function testSwitchableGroupMayOnlyHaveGroupChildren() {
		$this->expectException(\InvalidArgumentException::class);

		$this->group = new SwitchableGroup(
			$this->data_factory,
			$this->refinery,
			[$this->createMock(Input::class)],
			"LABEL",
			"BYLINE"
		);
	}

	public function testSwitchableGroupForwardsValuesOnWithValue() {
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

	public function testGroupOnlyDoesNotAcceptNonArrayValue() {
		$this->expectException(\InvalidArgumentException::class);

		$new_group = $this->switchable_group->withValue(null);
	}

	public function testGroupOnlyDoesNoAcceptArrayValuesWithWrongLength() {
		$this->expectException(\InvalidArgumentException::class);

		$new_group = $this->switchable_group->withValue([1, 2, 3]);
	}

	public function testGroupOnlyDoesAcceptKeyOnly() {
		$new_group = $this->switchable_group->withValue("child1");
		$this->assertEquals(["child1" => $this->child1, "child2" => $this->child2], $new_group->getInputs());
		$this->assertInstanceOf(SwitchableGroup::class, $new_group);
		$this->assertNotSame($this->switchable_group, $new_group);
	}

	public function testGroupOnlyDoesNotAcceptInvalidKey() {
		$this->expectException(\InvalidArgumentException::class);

		$new_group = $this->switchable_group->withValue("child3");
	}

	public function testGroupForwardsValuesOnGetValue() {
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

	public function testWithInputCallsChildrenAndAppliesOperations() {
		$this->assertNotSame($this->child1, $this->child2);

		$input_data = $this->createMock(InputData::class);

		$input_data
			->expects($this->once())
			->method("get")
			->with("name0")
			->willReturn("child1");

		$this->child1
			->expects($this->once())
			->method("withInput")
			->with($input_data)
			->willReturn($this->child1);
		$this->child1
			->expects($this->once())
			->method("getValue")
			->with()
			->willReturn("one");
		$this->child1
			->expects($this->once())
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
			->withAdditionalTransformation($this->refinery->custom()->transformation(function($v) use (&$called) {
				$called = true;
				$this->assertEquals(["child1", "one"], $v);
				return "result";
			}))
			->withInput($input_data);

		$this->assertTrue($called);
		$this->assertEquals(["child1" => $this->child1, "child2" => $this->child2], $new_group->getInputs());
		$this->assertInstanceOf(SwitchableGroup::class, $new_group);
		$this->assertNotSame($this->switchable_group, $new_group);
		$this->assertEquals($this->data_factory->ok("result"), $new_group->getContent());
	}

	public function testWithInputDoesNotApplyOperationsOnError() {
		$this->assertNotSame($this->child1, $this->child2);

		$input_data = $this->createMock(InputData::class);

		$input_data
			->expects($this->once())
			->method("get")
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

		$new_group = $this->switchable_group
			->withAdditionalTransformation($this->refinery->custom()->transformation(function($v){
				$this->assertFalse(true, "This should not happen.");
			}))
			->withInput($input_data);

		$this->assertEquals(["child1" => $this->child1, "child2" => $this->child2], $new_group->getInputs());
		$this->assertInstanceOf(SwitchableGroup::class, $new_group);
		$this->assertNotSame($this->switchable_group, $new_group);
		$this->assertTrue($new_group->getContent()->isError());
	}

	public function testWithInputDoesNotAcceptUnknownKeys() {
		$this->expectException(\InvalidArgumentException::class);

		$input_data = $this->createMock(InputData::class);

		$input_data
			->expects($this->once())
			->method("get")
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

		$new_group = $this->switchable_group
			->withAdditionalTransformation($this->refinery->custom()->transformation(function($v) use (&$called) {
				$this->assertFalse(true, "This should not happen.");
			}))
			->withInput($input_data);
	}
}
