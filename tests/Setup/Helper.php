<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Tests\Setup;

use ILIAS\Setup;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Component\Input\Field\Input as Input;
use ILIAS\Refinery\Transformation;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Data\Factory as DataFactory;

trait Helper {
	protected function newAgent() {
		static $no = 0;

		$consumer = $this
			->getMockBuilder(Setup\Agent::class)
			->setMethods(["hasConfig", "getDefaultConfig", "getConfigInput", "getArrayToConfigTransformation", "getInstallObjective", "getUpdateObjective", "getBuildArtifactObjective"])
			->setMockClassName("Mock_AgentNo".($no++))
			->getMock();

		return $consumer;
	}

	protected function newObjective() {
		static $no = 0;

		$goal = $this
			->getMockBuilder(Setup\Objective::class)
			->setMethods(["getHash", "getLabel", "isNotable", "withResourcesFrom", "getPreconditions", "achieve"])
			->setMockClassName("Mock_ObjectiveNo".($no++))
			->getMock();

		$goal
			->method("getHash")
			->willReturn("".$no);

		return $goal;
	}

	protected function newInput() {
		static $no = 0;

		$input = $this
			->getMockBuilder(Input::class)
			->setMethods([])
			->setMockClassName("Mock_InputNo".($no++))
			->getMock();

		return $input;
	}

	protected function newConfig() {
		static $no = 0;

		$config = $this
			->getMockBuilder(Setup\Config::class)
			->setMethods([])
			->setMockClassName("Mock_ConfigNo".($no++))
			->getMock();

		return $config;
	}
}
