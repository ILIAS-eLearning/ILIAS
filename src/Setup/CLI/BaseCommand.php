<?php
/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Setup\CLI;

use ILIAS\Setup\UnachievableException;
use ILIAS\Setup\NoConfirmationException;
use ILIAS\Setup\Agent;
use ILIAS\Setup\Objective;
use ILIAS\Setup\Environment;
use ILIAS\Setup\Config;
use ILIAS\Setup\ObjectiveIterator;
use ILIAS\Setup\Objective\ObjectiveWithPreconditions;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command base class.
 */
abstract class BaseCommand extends Command
{
    protected static $defaultName = "install";

    /**
     * @var callable
     */
    protected $lazy_agent;

    /**
     * @var Agent|null
     */
    protected $agent;

    /**
     * @var ConfigReader
     */
    protected $config_reader;

    /**
     * var Objective[]
     */
    protected $preconditions;

    /**
     * @var callable $lazy_agent must return a Setup\Agent
     * @var Objective[] $preconditions will be achieved before command invocation
     */
    public function __construct(callable $lazy_agent, ConfigReader $config_reader, array $preconditions)
    {
        parent::__construct();
        $this->lazy_agent = $lazy_agent;
        $this->agent = null;
        $this->config_reader = $config_reader;
        $this->preconditions = $preconditions;
    }

    protected function getAgent() : Agent
    {
        if ($this->agent !== null) {
            return $this->agent;
        }
        $this->agent = ($this->lazy_agent)();
        return $this->agent;
    }

    public function configure()
    {
        $this
            ->addArgument("config", InputArgument::REQUIRED, "Configuration file for the Setup")
            ->addOption("config", null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, "Define fields in the configuration file that should be overwritten, e.g. \"a.b.c=foo\"", [])
            ->addOption("yes", "y", InputOption::VALUE_NONE, "Confirm every message of the setup.");
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new IOWrapper($input, $output, $this->shouldSayYes($input));

        $this->printLicenseMessage($io, $input);

        $this->printIntroMessage($io);

        $config = $this->readAgentConfig($this->getAgent(), $input);
        $environment = $this->buildEnvironment($this->getAgent(), $config, $io);
        $goal = $this->getObjective($this->getAgent(), $config);
        if (count($this->preconditions) > 0) {
            $goal = new ObjectiveWithPreconditions(
                $goal,
                ...$this->preconditions
            );
        }
        $goals = new ObjectiveIterator($environment, $goal);

        try {
            while ($goals->valid()) {
                $current = $goals->current();
                if (!$current->isApplicable($environment)) {
                    $goals->next();
                    continue;
                }
                $io->startObjective($current->getLabel(), $current->isNotable());
                try {
                    $environment = $current->achieve($environment);
                    $io->finishedLastObjective($current->getLabel(), $current->isNotable());
                    $goals->setEnvironment($environment);
                } catch (UnachievableException $e) {
                    $goals->markAsFailed($current);
                    $io->error($e->getMessage());
                    $io->failedLastObjective($current->getLabel());
                }
                $goals->next();
            }
            $this->printOutroMessage($io);
        } catch (NoConfirmationException $e) {
            $io->error("Aborting Setup, a necessary confirmation is missing:\n\n" . $e->getRequestedConfirmation());
        }
    }

    protected function shouldSayYes(InputInterface $input) : bool
    {
        return $input->getOption("yes") ?? false;
    }

    protected function printLicenseMessage(IOWrapper $io, InputInterface $input) : void
    {
        if ($this->shouldSayYes($input) || ($input->hasOption("no-interaction") && $input->getOption("no-interaction"))) {
            return;
        }
        $io->text(
            "   ILIAS Copyright (C) 1998-2019 ILIAS Open Source e.V. - GPLv3\n\n" .
            "This program comes with ABSOLUTELY NO WARRANTY. This is free software,\n" .
            "and you are welcome to redistribute it under certain conditions. Look\n" .
            "into the LICENSE file for details."
        );
    }

    abstract protected function printIntroMessage(IOWrapper $io);

    abstract protected function printOutroMessage(IOWrapper $io);

    protected function readAgentConfig(Agent $agent, InputInterface $input) : ?Config
    {
        if (!$agent->hasConfig()) {
            return null;
        }

        $config_file = $input->getArgument("config");
        $config_overwrites_raw = $input->getOption("config");
        $config_overwrites = [];
        foreach ($config_overwrites_raw as $o) {
            list($k, $v) = explode("=", $o);
            $config_overwrites[$k] = $v;
        }
        $config_content = $this->config_reader->readConfigFile($config_file, $config_overwrites);
        $trafo = $this->agent->getArrayToConfigTransformation();
        return $trafo->transform($config_content);
    }

    abstract protected function buildEnvironment(Agent $agent, ?Config $config, IOWrapper $io);

    abstract protected function getObjective(Agent $agent, ?Config $config) : Objective;
}
