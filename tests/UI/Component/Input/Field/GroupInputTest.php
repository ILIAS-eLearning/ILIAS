<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once(__DIR__ . "/../../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../Base.php");

use ILIAS\UI\Implementation\Component\Input\Field\Group;
use ILIAS\UI\Implementation\Component\Input\Field\InputInternal;
use ILIAS\UI\Component\Input\Field\Input;
use \ILIAS\Data;
use \ILIAS\Refinery\Validation;
use \ILIAS\Refinery\Transformation;

interface Input1 extends InputInternal {};
interface Input2 extends InputInternal {};

class GroupInputTest extends ILIAS_UI_TestBase {
	public function setUp(): void{
		$this->child1 = $this->createMock(Input1::class);
		$this->child2 = $this->createMock(Input2::class);
		$this->data_factory = $this->createMock(Data\Factory::class);
		$this->validation_factory = $this->createMock(Validation\Factory::class);
		$this->transformation_factory = $this->createMock(Transformation\Factory::class);
		$this->group = new Group(
			$this->data_factory,
			$this->validation_factory,
			$this->transformation_factory,
			[$this->child1, $this->child2],
			"LABEL",
			"BYLINE"
		);
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

		$new_group = $this->group->withDisabled(true);

		$this->assertEquals([$this->child2, $this->child1], $new_group->getInputs());
	}

	public function testWithRequiredRequiresChildren() {
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
	}

	public function testGroupMayOnlyHaveInputChildren() {
		$this->expectException(\InvalidArgumentException::class);

		$this->group = new Group(
			$this->data_factory,
			$this->validation_factory,
			$this->transformation_factory,
			["foo", "bar"],
			"LABEL",
			"BYLINE"
		);
	}
}
