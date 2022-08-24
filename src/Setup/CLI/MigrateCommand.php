<?php

declare(strict_types=1);

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

namespace ILIAS\Setup\CLI;

use ILIAS\Setup\AgentFinder;
use ILIAS\Setup\ArrayEnvironment;
use ILIAS\Setup\Environment;
use ILIAS\Setup\Migration;
use ILIAS\Setup\NoConfirmationException;
use ILIAS\Setup\Objective;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Migration command.
 */
class MigrateCommand extends Command
{
    use HasAgent;
    use ObjectiveHelper;

    protected static $defaultName = "migrate";

    /**
     * var Objective[]
     */
    protected array $preconditions;

    /**
     * @var Objective[] $preconditions will be achieved before command invocation
     */
    public function __construct(AgentFinder $agent_finder, array $preconditions)
    {
        parent::__construct();
        $this->agent_finder = $agent_finder;
        $this->preconditions = $preconditions;
    }

    protected function configure(): void
    {
        $this->setDescription("Starts and manages migrations needed after an update of ILIAS");
        $this->addOption("yes", "y", InputOption::VALUE_NONE, "Confirm every message of the installation.");
        $this->addOption("run", "R", InputOption::VALUE_REQUIRED, "Run the migration with the name given.");
        $this->addOption(
            "steps",
            "S",
            InputOption::VALUE_REQUIRED,
            "Run the selected migration with X steps. Pass " . Migration::INFINITE . " for all remaining steps."
        );
        $this->configureCommandForPlugins();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new IOWrapper($input, $output);
        $io->printLicenseMessage();
        $io->title("Trigger migrations in ILIAS");

        // Dispatching further sub-commands
        if ($input->hasOption('run') && !empty($input->getOption('run'))) {
            $this->runMigration($input, $io);
        } else {
            $this->listMigrations($input, $io);
        }

        return 0;
    }

    protected function runMigration(InputInterface $input, IOWrapper $io): void
    {
        $agent = $this->getRelevantAgent($input);

        $migration_name = $input->getOption('run');
        $migrations = $agent->getMigrations();
        if (!isset($migrations[$migration_name]) || !($migrations[$migration_name] instanceof Migration)) {
            $io->error("Aborting Migration, did not find $migration_name.");
            return;
        }
        $migration = $migrations[$migration_name];

        $steps = (int) $input->getOption('steps');

        switch ($steps) {
            case Migration::INFINITE:
                $io->text("Determined infinite steps to run.");
                break;
            case 0:
                $steps = $migration->getDefaultAmountOfStepsPerRun();
                $io->text("no --steps option found, fallback to default amount of steps of migration. ($steps)");
                break;
            default:
                $io->text("Determined $steps step(s) to run.");
                break;
        }
        $objective = new Objective\MigrationObjective($migration, $steps);

        $env = new ArrayEnvironment([
            Environment::RESOURCE_ADMIN_INTERACTION => $io
        ]);

        $preconditions = $migration->getPreconditions($env);
        if ($preconditions !== []) {
            $objective = new Objective\ObjectiveWithPreconditions(
                $objective,
                ...$preconditions
            );
        }
        $steps_text = $steps === Migration::INFINITE ? 'all' : (string) $steps;
        $io->inform("Preparing Environment for {$steps_text} steps in {$migration_name}");
        try {
            $this->achieveObjective($objective, $env, $io);
        } catch (NoConfirmationException $e) {
            $io->error("Aborting Migration, a necessary confirmation is missing:\n\n" . $e->getRequestedConfirmation());
        }
    }

    protected function listMigrations(InputInterface $input, IOWrapper $io): void
    {
        $agent = $this->getRelevantAgent($input);
        $migrations = $agent->getMigrations();
        $count = count($migrations);
        if ($count === 0) {
            $io->inform("There are currently no migrations to run.");
            return;
        }

        $env = new ArrayEnvironment([
            Environment::RESOURCE_ADMIN_INTERACTION => $io
        ]);

        $io->inform("There are $count migrations:");
        foreach ($migrations as $migration_key => $migration) {
            $env = $this->prepareEnvironmentForMigration($env, $migration);
            $migration->prepare($env);
            $steps = $migration->getRemainingAmountOfSteps();
            $status = $steps === 0 ? "[done]" : "[remaining steps: $steps]";
            $io->text($migration_key . ": " . $migration->getLabel() . " " . $status);
        }
        $io->inform('Run them by passing --run <migration_id>, e.g. --run $migration_key');
    }

    protected function prepareEnvironmentForMigration(
        Environment $environment,
        Migration $migration
    ): Environment {
        $preconditions = $migration->getPreconditions($environment);
        if ($preconditions !== []) {
            $objective = new Objective\ObjectiveWithPreconditions(
                new Objective\NullObjective(),
                ...$preconditions
            );

            $environment = $this->achieveObjective($objective, $environment);
        }

        return $environment;
    }
}
