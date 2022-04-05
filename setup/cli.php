<?php declare(strict_types=1);

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
require_once(__DIR__ . "/classes/class.ilSetupMetricsCollectedObjective.php");

use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Component\Input\Field\File;
use ILIAS\UI\Component\Input\Field\Tag;
use ILIAS\UI\Component\Input\Field\UploadHandler;

$c = build_container_for_setup($executed_in_directory);
$app = $c["app"];
$app->run();

// ATTENTION: This is a hack to get around the usage of the echo/exit pattern in
// the setup for the command line version of the setup. Do not use this.
function setup_exit($message)
{
    if (!defined("ILIAS_SETUP_IGNORE_DB_UPDATE_STEP_MESSAGES") || !ILIAS_SETUP_IGNORE_DB_UPDATE_STEP_MESSAGES) {
        throw new \ILIAS\Setup\UnachievableException($message);
    }
}

function build_container_for_setup(string $executed_in_directory) : \Pimple\Container
{
    $c = new \Pimple\Container;

    $c["app"] = function ($c) {
        return new \ILIAS\Setup\CLI\App(
            $c["command.install"],
            $c["command.update"],
            $c["command.build-artifacts"],
            $c["command.achieve"],
            $c["command.status"],
            $c["command.migrate"]
        );
    };
    $c["command.install"] = function ($c) {
        return new \ILIAS\Setup\CLI\InstallCommand(
            $c["agent_finder"],
            $c["config_reader"],
            $c["common_preconditions"]
        );
    };
    $c["command.update"] = function ($c) {
        return new \ILIAS\Setup\CLI\UpdateCommand(
            $c["agent_finder"],
            $c["config_reader"],
            $c["common_preconditions"]
        );
    };
    $c["command.build-artifacts"] = function ($c) {
        return new \ILIAS\Setup\CLI\BuildArtifactsCommand(
            $c["agent_finder"]
        );
    };
    $c["command.achieve"] = function ($c) {
        return new \ILIAS\Setup\CLI\AchieveCommand(
            $c["agent_finder"],
            $c["config_reader"],
            $c["common_preconditions"],
            $c["refinery"]
        );
    };
    $c["command.status"] = function ($c) {
        return new \ILIAS\Setup\CLI\StatusCommand(
            $c["agent_finder"]
        );
    };

    $c["command.migrate"] = function ($c) {
        return new \ILIAS\Setup\CLI\MigrateCommand(
            $c["agent_finder"],
            $c["common_preconditions"]
        );
    };

    $c["common_preconditions"] = function ($c) {
        return [
            new \ilOwnRiskConfirmedObjective(),
            new \ilUseRootConfirmed()
        ];
    };

    $c["common_agent"] = function ($c) {
        return new \ilSetupAgent(
            $c["refinery"],
            $c["data_factory"]
        );
    };

    $c["agent_finder"] = function ($c) {
        return new ILIAS\Setup\ImplementationOfAgentFinder(
            $c["refinery"],
            $c["data_factory"],
            $c["lng"],
            $c["interface_finder"],
            [
                "common" => $c["common_agent"]
            ]
        );
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
            $c["json.parser"],
            $executed_in_directory
        );
    };

    $c["interface_finder"] = function ($c) {
        return new \ILIAS\Setup\ImplementationOfInterfaceFinder();
    };

    $c["json.parser"] = function ($c) {
        return new \Seld\JsonLint\JsonParser();
    };

    return $c;
}
