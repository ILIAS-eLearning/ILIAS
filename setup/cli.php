<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */


// according to ./Services/Feeds/classes/class.ilExternalFeed.php:
define("MAGPIE_DIR", "./Services/Feeds/magpierss/");

require_once(__DIR__."/classes/class.ilSetupLanguage.php");

require_once(__DIR__."/../libs/composer/vendor/autoload.php");

use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Component\Input\Field\Tag;

$c = build_container_for_setup();
$app = $c["app"];
$app->run();

function build_container_for_setup() {
	$c = new \Pimple\Container;

	$c["app"] =  function($c) {
		return new \ILIAS\Setup\CLI\App(
			$c["command.install"],
			$c["command.build-artifacts"]
		);
	};
	$c["command.install"] = function($c) {
		return new \ILIAS\Setup\CLI\InstallCommand(
			$c["agent"],
			$c["config_reader"]
		);
	};
	$c["command.build-artifacts"] = function($c) {
		return new \ILIAS\Setup\CLI\BuildArtifactsCommand(
			$c["agent"],
			$c["config_reader"]
		);
	};

	$c["agent"] = function($c) {
		return new ILIAS\Setup\AgentCollection(
			$c["ui.field_factory"],
			$c["refinery"],
			// TODO: use ImplementationOfInterfaceFinder here instead of fixed list
			[
				"database" => $c["agent.database"],
				"global_screen" => $c["agent.global_screen"],
				"ui_structure" => $c["agent.ui_structure"]
			]
		);
	};

	$c["agent.database"] = function ($c) {
		return new \ilDatabaseSetupAgent(
			$c["refinery"]
		);
	};

	$c["agent.global_screen"] = function($c) {
		return new \ilGlobalScreenSetupAgent(
			$c["refinery"]
		);
	};

	$c["agent.ui_structure"] = function($c) {
		return new \ilUIStructureSetupAgent();
	};


	$c["ui.field_factory"] = function($c) {
		return new class implements FieldFactory {
			public function text($label, $byline = null) {
				throw new \LogicException("The CLI-setup does not support the UI-Framework.");
			}
			public function numeric($label, $byline = null) {
				throw new \LogicException("The CLI-setup does not support the UI-Framework.");
			}
			public function group(array $inputs, string $label='') {
				throw new \LogicException("The CLI-setup does not support the UI-Framework.");
			}
			public function section(array $inputs, $label, $byline = null) {
				throw new \LogicException("The CLI-setup does not support the UI-Framework.");
			}
			public function dependantGroup(array $inputs) {
				throw new \LogicException("The CLI-setup does not support the UI-Framework.");
			}
			public function optionalGroup(array $inputs, string $label, string $byline = null) : \ILIAS\UI\Component\Input\Field\OptionalGroup {
				throw new \LogicException("The CLI-setup does not support the UI-Framework.");
			}
			public function switchableGroup(array $inputs, string $label, string $byline = null) : \ILIAS\UI\Component\Input\Field\SwitchableGroup{
				throw new \LogicException("The CLI-setup does not support the UI-Framework.");
			}
			public function checkbox($label, $byline = null) {
				throw new \LogicException("The CLI-setup does not support the UI-Framework.");
			}
			public function tag(string $label, array $tags, $byline = null): Tag {
				throw new \LogicException("The CLI-setup does not support the UI-Framework.");
			}
			public function password($label, $byline = null) {
				throw new \LogicException("The CLI-setup does not support the UI-Framework.");
			}
			public function select($label, array $options, $byline = null) {
				throw new \LogicException("The CLI-setup does not support the UI-Framework.");
			}
			public function textarea($label, $byline = null) {
				throw new \LogicException("The CLI-setup does not support the UI-Framework.");
			}
			public function radio($label, $byline = null) {
				throw new \LogicException("The CLI-setup does not support the UI-Framework.");
			}
			public function multiSelect($label, array $options, $byline = null) {
				throw new \LogicException("The CLI-setup does not support the UI-Framework.");
			}
			public function dateTime($label, $byline = null) {
				throw new \LogicException("The CLI-setup does not support the UI-Framework.");
			}
			public function duration($label, $byline = null) {
				throw new \LogicException("The CLI-setup does not support the UI-Framework.");
			}
		};
	};

	$c["refinery"] = function($c) {
		return new ILIAS\Refinery\Factory(
			$c["data_factory"],
			$c["lng"]
		);
	};

	$c["data_factory"] = function($c) {
		return new ILIAS\Data\Factory();
	};

	$c["lng"] = function ($c) {
		return new \ilSetupLanguage("en");
	};

	$c["config_reader"] = function($c) {
		return new \ILIAS\Setup\CLI\ConfigReader();
	};

	return $c;
}

