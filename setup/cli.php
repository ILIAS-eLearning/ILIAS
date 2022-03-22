<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

$executed_in_directory = getcwd();
chdir(__DIR__ . "/..");

require_once(__DIR__ . "/../libs/composer/vendor/autoload.php");

require_once(__DIR__ . "/../include/inc.ilias_version.php");

// according to ./Services/Feeds/classes/class.ilExternalFeed.php:
if (!defined("MAGPIE_DIR")) {
    define("MAGPIE_DIR", "./Services/Feeds/magpierss/");
}

require_once(__DIR__ . "/classes/class.ilSetupObjective.php");
require_once(__DIR__ . "/classes/class.ilSetupAgent.php");
require_once(__DIR__ . "/classes/class.ilSetupConfig.php");
require_once(__DIR__ . "/classes/class.ilMakeInstallationAccessibleObjective.php");
require_once(__DIR__ . "/classes/class.ilUseRootConfirmed.php");
require_once(__DIR__ . "/classes/class.ilOwnRiskConfirmedObjective.php");
require_once(__DIR__ . "/classes/class.ilOverwritesExistingInstallationConfirmed.php");
require_once(__DIR__ . "/classes/class.ilIniFilesPopulatedObjective.php");
require_once(__DIR__ . "/classes/class.ilIniFilesLoadedObjective.php");
require_once(__DIR__ . "/classes/class.ilNICKeyRegisteredObjective.php");
require_once(__DIR__ . "/classes/class.ilNICKeyStoredObjective.php");
require_once(__DIR__ . "/classes/class.ilSetupConfigStoredObjective.php");
require_once(__DIR__ . "/classes/class.ilSetupPasswordManager.php");
require_once(__DIR__ . "/classes/class.ilSetupPasswordEncoderFactory.php");

use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Component\Input\Field\File;
use ILIAS\UI\Component\Input\Field\Tag;
use ILIAS\UI\Component\Input\Field\UploadHandler;

$c = build_container_for_setup($executed_in_directory);
$app = $c["app"];
$app->run();

function get_agent_name_by_class(string $class_name) : string
{
    // We assume that the name of an agent in the class ilXYZSetupAgent really
    // is XYZ. If that does not fit we just use the class name.
    $match = [];
    if (preg_match("/il(\w+)SetupAgent/", $class_name, $match)) {
        return $match[1];
    }
    return $class_name;
}

// ATTENTION: This is a hack to get around the usage of the echo/exit pattern in
// the setup for the command line version of the setup. Do not use this.
function setup_exit($message)
{
    if (!defined("ILIAS_SETUP_IGNORE_DB_UPDATE_STEP_MESSAGES") || !ILIAS_SETUP_IGNORE_DB_UPDATE_STEP_MESSAGES) {
        throw new \ILIAS\Setup\UnachievableException($message);
    }
}

function build_container_for_setup(string $executed_in_directory)
{
    $c = new \Pimple\Container;

    $c["app"] = function ($c) {
        return new \ILIAS\Setup\CLI\App(
            $c["command.install"],
            $c["command.update"],
            $c["command.build-artifacts"],
            $c["command.reload-control-structure"]
        );
    };
    $c["command.install"] = function ($c) {
        return new \ILIAS\Setup\CLI\InstallCommand(
            $c["agent"],
            $c["config_reader"],
            $c["common_preconditions"]
        );
    };
    $c["command.update"] = function ($c) {
        return new \ILIAS\Setup\CLI\UpdateCommand(
            $c["agent"],
            $c["config_reader"],
            $c["common_preconditions"]
        );
    };
    $c["command.build-artifacts"] = function ($c) {
        return new \ILIAS\Setup\CLI\BuildArtifactsCommand(
            $c["agent"],
            $c["config_reader"],
            []// TODO: $c["common_preconditions"]
        );
    };
    $c["command.reload-control-structure"] = function ($c) {
        return new \ILIAS\Setup\CLI\ReloadControlStructureCommand(
            $c["agent"],
            $c["config_reader"],
            $c["common_preconditions"]
        );
    };

    $c["common_preconditions"] = function ($c) {
        return [
            new \ilOwnRiskConfirmedObjective(),
            new \ilUseRootConfirmed()
        ];
    };

    $c["agent"] = function ($c) {
        return function () use ($c) {
            return new ILIAS\Setup\AgentCollection(
                $c["ui.field_factory"],
                $c["refinery"],
                $c["agents"]
            );
        };
    };

    $c["agent_finder"] = function ($c) {
        return new ILIAS\Setup\ImplementationOfInterfaceFinder(
            ILIAS\Setup\Agent::class
        );
    };

    $c["common_agent"] = function ($c) {
        return new \ilSetupAgent(
            $c["refinery"],
            $c["data_factory"],
            $c["password_manager"]
        );
    };

    $c["agents"] = function ($c) {
        $agents["common"] = $c["common_agent"];
        foreach ($c["agent_finder"]->getMatchingClassNames() as $cls) {
            if (preg_match("/ILIAS\\\\Setup\\\\.*/", $cls)) {
                continue;
            }
            $name = get_agent_name_by_class($cls);
            if (isset($agents[$name])) {
                throw new \RuntimeException(
                    "Encountered duplicate agent $name in $cls"
                );
            }
            $agents[strtolower($name)] = new $cls(
                $c["refinery"],
                $c["data_factory"],
                $c["lng"]
            );
        };
        return $agents;
    };

    $c["ui.field_factory"] = function ($c) {
        return new class implements FieldFactory {
            public function text($label, $byline = null)
            {
                throw new \LogicException("The CLI-setup does not support the UI-Framework.");
            }
            public function numeric($label, $byline = null)
            {
                throw new \LogicException("The CLI-setup does not support the UI-Framework.");
            }
            public function group(array $inputs, string $label = '')
            {
                throw new \LogicException("The CLI-setup does not support the UI-Framework.");
            }
            public function section(array $inputs, $label, $byline = null)
            {
                throw new \LogicException("The CLI-setup does not support the UI-Framework.");
            }
            public function dependantGroup(array $inputs)
            {
                throw new \LogicException("The CLI-setup does not support the UI-Framework.");
            }
            public function optionalGroup(array $inputs, string $label, string $byline = null) : \ILIAS\UI\Component\Input\Field\OptionalGroup
            {
                throw new \LogicException("The CLI-setup does not support the UI-Framework.");
            }
            public function switchableGroup(array $inputs, string $label, string $byline = null) : \ILIAS\UI\Component\Input\Field\SwitchableGroup
            {
                throw new \LogicException("The CLI-setup does not support the UI-Framework.");
            }
            public function checkbox($label, $byline = null)
            {
                throw new \LogicException("The CLI-setup does not support the UI-Framework.");
            }
            public function tag(string $label, array $tags, $byline = null) : Tag
            {
                throw new \LogicException("The CLI-setup does not support the UI-Framework.");
            }
            public function password($label, $byline = null)
            {
                throw new \LogicException("The CLI-setup does not support the UI-Framework.");
            }
            public function select($label, array $options, $byline = null)
            {
                throw new \LogicException("The CLI-setup does not support the UI-Framework.");
            }
            public function textarea($label, $byline = null)
            {
                throw new \LogicException("The CLI-setup does not support the UI-Framework.");
            }
            public function radio($label, $byline = null)
            {
                throw new \LogicException("The CLI-setup does not support the UI-Framework.");
            }
            public function multiSelect($label, array $options, $byline = null)
            {
                throw new \LogicException("The CLI-setup does not support the UI-Framework.");
            }
            public function dateTime($label, $byline = null)
            {
                throw new \LogicException("The CLI-setup does not support the UI-Framework.");
            }
            public function duration($label, $byline = null)
            {
                throw new \LogicException("The CLI-setup does not support the UI-Framework.");
            }
            public function file(UploadHandler $handler, string $label, string $byline = null) : File
            {
                throw new \LogicException("The CLI-setup does not support the UI-Framework.");
            }
        };
    };

    $c["refinery"] = function ($c) {
        return new ILIAS\Refinery\Factory(
            $c["data_factory"],
            $c["lng"]
        );
    };

    $c["data_factory"] = function ($c) {
        return new ILIAS\Data\Factory();
    };

    $c["lng"] = function ($c) {
        return new \ilSetupLanguage("en");
    };

    $c["config_reader"] = function ($c) use ($executed_in_directory) {
        return new \ILIAS\Setup\CLI\ConfigReader(
            $executed_in_directory
        );
    };

    $c["password_manager"] = function ($c) {
        return new \ilSetupPasswordManager([
            'password_encoder' => 'bcryptphp',
            'encoder_factory' => new \ilSetupPasswordEncoderFactory([
                'default_password_encoder' => 'bcryptphp'
            ])
        ]);
    };

    return $c;
}
