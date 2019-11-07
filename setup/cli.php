<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

chdir(__DIR__."/..");

require_once(__DIR__."/../libs/composer/vendor/autoload.php");

// according to ./Services/Feeds/classes/class.ilExternalFeed.php:
if (!defined("MAGPIE_DIR")) {
	define("MAGPIE_DIR", "./Services/Feeds/magpierss/");
}

require_once(__DIR__."/classes/class.ilCtrlStructureReader.php");

require_once(__DIR__."/classes/class.ilSetupObjective.php");
require_once(__DIR__."/classes/class.ilSetupAgent.php");
require_once(__DIR__."/classes/class.ilSetupConfig.php");
require_once(__DIR__."/classes/class.ilIniFilePopulatedObjective.php");
require_once(__DIR__."/classes/class.ilSetupConfigStoredObjective.php");
require_once(__DIR__."/classes/class.ilSetupPasswordManager.php");
require_once(__DIR__."/classes/class.ilSetupPasswordEncoderFactory.php");

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
				"common" => $c["agent.common"],
				"filesystem" => $c["agent.filesystem"],
				"globalcache" => $c["agent.globalcache"],
				"http" => $c["agent.http"],
				"language" => $c["agent.language"],
				"logging" => $c["agent.logging"],
				"style" => $c["agent.style"],
				"virusscanner" => $c["agent.virusscanner"],
				"database" => $c["agent.database"],
				"systemfolder" => $c["agent.systemfolder"],
				"preview" => $c["agent.preview"],
				"backgroundtasks" => $c["agent.backgroundtasks"]/*,
				"global_screen" => $c["agent.global_screen"],
				"ui_structure" => $c["agent.ui_structure"],
				"ctrl_structure" => $c["agent.ctrl_structure"]*/
			]
		);
	};

	$c["agent.common"] = function ($c) {
		return new \ilSetupAgent(
			$c["refinery"],
			$c["data_factory"],
			$c["password_manager"],
		);
	};

	$c["agent.backgroundtasks"] = function ($c) {
		return new \ilBackgroundTasksSetupAgent(
			$c["refinery"]
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

	$c["agent.http"] = function ($c) {
		return new \ilHttpSetupAgent(
			$c["refinery"]
		);
	};

	$c["agent.filesystem"] = function ($c) {
		return new \ilFileSystemSetupAgent(
			$c["refinery"]
		);
	};

	$c["agent.globalcache"] = function ($c) {
		return new \ilGlobalCacheSetupAgent(
			$c["refinery"]
		);
	};

	$c["agent.language"] = function ($c) {
		return new \ilLanguageSetupAgent(
			$c["refinery"],
			$c["lng"]
		);
	};

	$c["agent.logging"] = function ($c) {
		return new \ilLoggingSetupAgent(
			$c["refinery"]
		);
	};

	$c["agent.style"] = function ($c) {
		return new \ilStyleSetupAgent(
			$c["refinery"]
		);
	};

	$c["agent.systemfolder"] = function ($c) {
		return new \ilSystemFolderSetupAgent(
			$c["refinery"]
		);
	};

	$c["agent.virusscanner"] = function ($c) {
		return new \ilVirusScannerSetupAgent(
			$c["refinery"]
		);
	};

	$c["agent.preview"] = function ($c) {
		return new \ilPreviewSetupAgent(
			$c["refinery"]
		);
	};

	$c["agent.ui_structure"] = function($c) {
		return new \ilUIStructureSetupAgent();
	};
	$c["agent.ctrl_structure"] = function($c) {
		return new \ilUICoreSetupAgent(
			$c["ctrlstructure_reader"]
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

	$c["ctrlstructure_reader"] = function($c) {
		return new \ilCtrlStructureReader();
	};

	$c["password_manager"] = function($c) {
		return new \ilSetupPasswordManager([
			'password_encoder' => 'bcryptphp',
			'encoder_factory'  => new \ilSetupPasswordEncoderFactory([
				'default_password_encoder' => 'bcryptphp'
			])
		]);
	};

	return $c;
}

