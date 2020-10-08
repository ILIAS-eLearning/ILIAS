<?php
/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Setup\CLI;

use ILIAS\Setup\Agent;
use ILIAS\Setup\ArrayEnvironment;
use ILIAS\Setup\Config;
use ILIAS\Setup\Environment;
use ILIAS\Setup\Objective;
use ILIAS\Setup\ObjectiveCollection;
use ILIAS\Setup\Objective\Tentatively;
use ILIAS\Setup\Metrics;
use ILIAS\Setup\NoConfirmationException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Command to output status information about the installation.
 */
class StatusCommand extends Command
{
    use HasAgent;
    use ObjectiveHelper;

    protected static $defaultName = "status";

    /**
     * @var callable $lazy_agent must return a Setup\Agent
     */
    public function __construct(callable $lazy_agent)
    {
        parent::__construct();
        $this->lazy_agent = $lazy_agent;
    }

    public function configure()
    {
        $this->setDescription("Collect and show status information about the installation.");
    }


    public function execute(InputInterface $input, OutputInterface $output)
    {
        // ATTENTION: Don't do this (in general), please have a look at the comment
        // in ilIniFilesLoadedObjective.
        \ilIniFilesLoadedObjective::$might_populate_ini_files_as_well = false;

        $environment = new ArrayEnvironment([]);
        $storage = new Metrics\ArrayStorage();
        $objective = new Tentatively(
            $this->getAgent()->getStatusObjective($storage)
        );

        $this->achieveObjective($objective, $environment);

        $metric = $storage->asMetric();

        $output->write($metric->toYAML() . "\n");
    }
}
