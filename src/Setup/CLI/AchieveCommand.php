<?php declare(strict_types=1);

/* Copyright (c) 2021 - Daniel Weise <daniel.weise@concepts-and-training.de> - Extended GPL, see LICENSE */

namespace ILIAS\Setup\CLI;

use ILIAS\Setup\ArrayEnvironment;
use ILIAS\Setup\Environment;
use ILIAS\Setup\Objective;
use ILIAS\Setup\Objective\ObjectiveWithPreconditions;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use ILIAS\Setup\AgentFinder;
use ILIAS\Setup\NoConfirmationException;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Setup\NullConfig;
use InvalidArgumentException;

/**
 * Achieves an objective
 */
class AchieveCommand extends Command
{
    use HasAgent;
    use HasConfigReader;
    use ObjectiveHelper;

    protected static $defaultName = "achieve";

    /**
     * var Objective[]
     */
    protected $preconditions;

    /**
     * @var Refinery|null
     */
    protected $refinery;

    /**
     * @var Objective[] $preconditions will be achieved before command invocation
     */
    public function __construct(
        AgentFinder $agent_finder,
        ConfigReader $config_reader,
        array $preconditions,
        Refinery $refinery
    ) {
        parent::__construct();
        $this->agent_finder = $agent_finder;
        $this->config_reader = $config_reader;
        $this->preconditions = $preconditions;
        $this->refinery = $refinery;
    }

    public function configure()
    {
        $this->setDescription("Achieve a named objective from an agent.");
        $this->addArgument(
            "objective",
            InputArgument::OPTIONAL,
            "Objective to be execute from an agent. Format: \$AGENT::\$OBJECTIVE"
        );
        $this->addArgument("config", InputArgument::OPTIONAL, "Configuration file for the installation");
        $this->addOption(
            "config",
            null,
            InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
            "Define fields in the configuration file that should be overwritten, e.g. \"a.b.c=foo\"",
            []
        );
        $this->addOption("yes", "y", InputOption::VALUE_NONE, "Confirm every message of the objective.");
        $this->addOption("list", null, InputOption::VALUE_NONE, "Lists all achievable objectives");
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new IOWrapper($input, $output);
        $io->printLicenseMessage();

        if ($this->shouldListNamedObjectives($input)) {
            $this->executeListNamedObjectives($io, $output);
            return;
        }

        $this->executeAchieveObjective($io, $input);
    }

    private function shouldListNamedObjectives(InputInterface $input) : bool
    {
        return
            (
                $input->getOption("list") !== null
                && is_bool($input->getOption("list"))
                && (bool) $input->getOption("list")
            )
            ||
            (
                $input->getArgument("objective") === ""
                || $input->getArgument("objective") === null
            );
    }

    private function executeListNamedObjectives(IOWrapper $io, OutputInterface $output) : void
    {
        $io->title("Listing available objectives");

        $agentCollection = $this->agent_finder->getAgents();
        foreach ($agentCollection->getNamedObjectives(null) as $cmd => $objectiveCollection) {
            $output->write(str_pad($cmd, IOWrapper::LABEL_WIDTH));
            $output->writeln($objectiveCollection->getDescription());
        }
        $output->writeln("");
    }

    private function executeAchieveObjective(IOWrapper $io, InputInterface $input) : void
    {
        $agent = $this->getRelevantAgent($input);
        $objective_name = $input->getArgument('objective');

        $io->title("Achieve objective: $objective_name");

        $config = null;

        if ($input->getArgument("config")) {
            $config = $this->readAgentConfig($agent, $input);
        }

        $namedObjectives = $agent->getNamedObjectives($config);

        if (isset($namedObjectives[$objective_name])) {
            $objective = $namedObjectives[$objective_name];
        } else {
            throw new InvalidArgumentException(
                "There is no named objective '$objective_name'"
            );
        }

        if (count($this->preconditions) > 0) {
            $objective = new ObjectiveWithPreconditions(
                $objective->create(),
                ...$this->preconditions
            );
        } else {
            $objective = $objective->create();
        }

        $environment = new ArrayEnvironment([
            Environment::RESOURCE_ADMIN_INTERACTION => $io
        ]);
        if ($config) {
            $environment = $this->addAgentConfigsToEnvironment($agent, $config, $environment);
        }

        try {
            $this->achieveObjective($objective, $environment, $io);
            $io->success("Achieved objective '$objective_name'. Thanks and have fun!");
        } catch (NoConfirmationException $e) {
            $io->error("Aborted the attempt to achieve '$objective_name', a necessary confirmation is missing:\n\n" . $e->getRequestedConfirmation());
        }
    }
}
