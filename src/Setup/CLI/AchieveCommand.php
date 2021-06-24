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
use ILIAS\Setup\AgentCollection;

/**
 * Achieves an objective
 */
class AchieveCommand extends Command
{
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
        $this->setDescription("Execute a method for an agent to achieve a specific objective.");
        $this->addArgument("agent_method", InputArgument::REQUIRED, "Method to be execute from agent. Format: Agent::Method");
        $this->addArgument("config", InputArgument::OPTIONAL, "Configuration file for the installation");
        $this->addOption("config", null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, "Define fields in the configuration file that should be overwritten, e.g. \"a.b.c=foo\"", []);
        $this->addOption("yes", "y", InputOption::VALUE_NONE, "Confirm every message of the update.");
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $result = $this->parseAgentMethod($input->getArgument('agent_method'));
        if (is_null($result)) {
            throw new \InvalidArgumentException("Wrong input format for command.");
        }
        list($class_name, $method) = $result;

        $io = new IOWrapper($input, $output);
        $io->printLicenseMessage();
        $io->title("Execute " . $method . " on " . $class_name);

        $agent = $this->agent_finder->getAgentByClassName($class_name);

        if (!method_exists($agent, $method)) {
            throw new \InvalidArgumentException("Method '" . $method . "' not found for '" . $class_name ."'.");
        }

        $config = null;
        if ($agent->hasConfig()) {
            if (is_null($input->getArgument("config"))) {
                throw new \InvalidArgumentException("Agent '" . $class_name . "' needs a config file.");
            }
            $agent_name = $this->agent_finder->getAgentNameByClassName($class_name);
            $config = $this->readAgentConfig(
                new AgentCollection($this->refinery, [$agent_name => $agent]),
                $input
            );
            $config = $config->getConfig($agent_name);
        }

        $objective = $agent->$method($config);

        if (count($this->preconditions) > 0) {
            $objective = new ObjectiveWithPreconditions(
                $objective,
                ...$this->preconditions
            );
        }

        $environment = new ArrayEnvironment([
            Environment::RESOURCE_ADMIN_INTERACTION => $io
        ]);
        if ($config) {
            $environment = $this->addAgentConfigsToEnvironment($agent, $config, $environment);
        }

        try {
            $this->achieveObjective($objective, $environment, $io);
            $io->success("Execute '" . $method . "' on '" . $class_name . "'. Thanks and have fun!");
        } catch (NoConfirmationException $e) {
            $io->error("Aborting execute of '" . $method . "' on '" . $class_name . "', a necessary confirmation is missing:\n\n" . $e->getRequestedConfirmation());
        }
    }

    protected function parseAgentMethod(string $agent_method) : ?array
    {
        $result = [];
        if (!preg_match('/^\s*(\w+)::(\w+)\s*$/', $agent_method, $result)) {
            return null;
        }

        return [$result[1], $result[2]];
    }
}
