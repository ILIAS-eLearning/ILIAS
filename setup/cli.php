<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

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
			$c["command.install"]
		);
	};
	$c["command.install"] = function($c) {
		return new \ILIAS\Setup\CLI\InstallCommand(
			$c["consumer"]
		);
	};

	$c["consumer"] = function($c) {
		return new ILIAS\Setup\ConsumerCollection(
			$c["ui.field_factory"],
			$c["transformation_factory"],
			[
				"database" => $c["consumer.database"]
			]
		);
	};

	$c["consumer.database"] = function ($c) {
		return new \ilDatabaseSetupConsumer(
			$c["data_factory"]
		);
	};

	$c["ui.field_factory"] = function($c) {
		return new class implements FieldFactory {
			public function text($label, $byline = null) {
				throw new \LogicException("The CLI-setup does not support the UI-Framework.");
			}
			public function numeric($label, $byline = null) {
				throw new \LogicException("The CLI-setup does not support the UI-Framework.");
			}
			public function group(array $inputs) {
				throw new \LogicException("The CLI-setup does not support the UI-Framework.");
			}
			public function section(array $inputs, $label, $byline = null) {
				throw new \LogicException("The CLI-setup does not support the UI-Framework.");
			}
			public function dependantGroup(array $inputs) {
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
		};
	};

	$c["transformation_factory"] = function($c) {
		return new ILIAS\Transformation\Factory();
	};

	$c["data_factory"] = function($c) {
		return new ILIAS\Data\Factory();
	};

	return $c;
}

