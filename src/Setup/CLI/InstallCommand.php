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

namespace ILIAS\Setup\CLI;

use MysqlIfsnopDumper;
use ILIAS\Setup\AgentFinder;
use ILIAS\Setup\ArrayEnvironment;
use ILIAS\Setup\Environment;
use ILIAS\Setup\Objective;
use ILIAS\Setup\ObjectiveCollection;
use ILIAS\Setup\Objective\ObjectiveWithPreconditions;
use ILIAS\Setup\NoConfirmationException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Installation command.
 */
class InstallCommand extends Command
{
    use HasAgent;
    use HasConfigReader;
    use ObjectiveHelper;

    public const IMPORT = "import";
    protected const TMP_DIR = "tmp_dir";

    protected static $defaultName = "install";

    /**
     * var Objective[]
     */
    protected array $preconditions = [];

    /**
     * @var Objective[] $preconditions will be achieved before command invocation
     */
    public function __construct(AgentFinder $agent_finder, ConfigReader $config_reader, array $preconditions)
    {
        parent::__construct();
        $this->agent_finder = $agent_finder;
        $this->config_reader = $config_reader;
        $this->preconditions = $preconditions;
    }

    protected function configure(): void
    {
        $this->setDescription("Creates a fresh ILIAS installation based on the config");
        $this->addArgument("config", InputArgument::OPTIONAL, "Configuration file for the installation");
        $this->addOption("config", null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, "Define fields in the configuration file that should be overwritten, e.g. \"a.b.c=foo\"", []);
        $this->addOption("yes", "y", InputOption::VALUE_NONE, "Confirm every message of the installation.");
        $this->addOption("import-file", "i", InputOption::VALUE_REQUIRED, "Path to zip file to import from.");
        $this->configureCommandForPlugins();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // ATTENTION: This is a hack to get around the usage of the echo/exit pattern in
        // the setup for the command line version of the setup. Do not use this.
        if (!defined("ILIAS_SETUP_IGNORE_DB_UPDATE_STEP_MESSAGES")) {
            define("ILIAS_SETUP_IGNORE_DB_UPDATE_STEP_MESSAGES", true);
        }

        if ($input->hasOption('plugin') && $input->getOption('plugin') != "") {
            list($objective, $environment, $io) = $this->preparePluginInstallation($input, $output);
        } else {
            list($objective, $environment, $io) = $this->prepareILIASInstallation($input, $output);
        }
        try {
            $this->achieveObjective($objective, $environment, $io);
            if ($input->hasOption("import-file") && $input->getOption("import-file") != "") {
                $io->inform("Please ensure that all ILIAS directories (webdir/datadir/Customizing) have the right owner after import process.");
            }
            $io->success("Installation complete. Thanks and have fun!");
        } catch (NoConfirmationException $e) {
            $io->error("Aborting Installation, a necessary confirmation is missing:\n\n" . $e->getRequestedConfirmation());
        }

        return 0;
    }

    protected function prepareILIASInstallation(InputInterface $input, OutputInterface $output): array
    {
        $io = new IOWrapper($input, $output);
        $io->printLicenseMessage();
        $io->title("Install ILIAS");

        $environment = new ArrayEnvironment([
            Environment::RESOURCE_ADMIN_INTERACTION => $io
        ]);

        if ($input->hasOption("import-file") && $input->getOption("import-file") != "") {
            if ($input->getArgument("config") == "") {
                throw new \InvalidArgumentException("Missing configuration file.");
            }
            $import_file = $input->getOption("import-file");
            if (!is_file($import_file)) {
                throw new \InvalidArgumentException("Can't find import file '$import_file'.");
            }

            if (pathinfo($import_file)["extension"] != "zip") {
                throw new \InvalidArgumentException("Wrong file format for import file. 'zip' is expected.");
            }

            $tmp_dir = $this->createTempDir();
            if (is_null($tmp_dir)) {
                throw new \RuntimeException("Can't create temporary directory!");
            }
            $environment = $environment
                ->withConfigFor(self::IMPORT, $input->getOption("import-file"))
                ->withConfigFor(self::TMP_DIR, $tmp_dir)
            ;
            $dump_path = $tmp_dir . DIRECTORY_SEPARATOR . MysqlIfsnopDumper::FILE_NAME;
            $input->setOption(
                "config",
                array_merge($input->getOption("config"), ["database.path_to_db_dump=$dump_path"])
            );
        }

        $agent = $this->getRelevantAgent($input);

        $config = $this->readAgentConfig($agent, $input);

        $objective = new ObjectiveCollection(
            "Install and Update ILIAS",
            false,
            $agent->getInstallObjective($config),
            $agent->getUpdateObjective($config)
        );
        if ($this->preconditions !== []) {
            $objective = new ObjectiveWithPreconditions(
                $objective,
                ...$this->preconditions
            );
        }

        $environment = $this->addAgentConfigsToEnvironment($agent, $config, $environment);
        // ATTENTION: This is bad because we strongly couple this generic command
        // to something very specific here. This can go away once we have got rid of
        // everything related to clients, since we do not need that client-id then.
        // This will require some more work, though.
        $common_config = $config->getConfig("common");
        $environment = $environment->withResource(
            Environment::RESOURCE_CLIENT_ID,
            $common_config->getClientId()
        );

        return [$objective, $environment, $io];
    }

    protected function preparePluginInstallation(InputInterface $input, OutputInterface $output): array
    {
        $io = new IOWrapper($input, $output);
        $io->printLicenseMessage();
        $io->title("Install ILIAS Plugin");

        $agent = $this->getRelevantAgent($input);

        $config = $this->readAgentConfig($agent, $input, $input->getOption("plugin"));

        $objective = new ObjectiveCollection(
            "Install and Update ILIAS Plugin",
            false,
            $agent->getInstallObjective($config),
            $agent->getUpdateObjective($config)
        );
        if ($this->preconditions !== []) {
            $objective = new ObjectiveWithPreconditions(
                $objective,
                ...$this->preconditions
            );
        }

        $environment = new ArrayEnvironment([
            Environment::RESOURCE_ADMIN_INTERACTION => $io
        ]);

        if (!is_null($config)) {
            $environment = $this->addAgentConfigsToEnvironment($agent, $config, $environment);
        }

        return [$objective, $environment, $io];
    }

    protected function createTempDir(): ?string
    {
        $path = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . mt_rand() . microtime(true);
        if (mkdir($path)) {
            return $path;
        }
        return null;
    }
}
