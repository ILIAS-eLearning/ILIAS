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

use ILIAS\Setup\AgentFinder;
use ILIAS\Setup\ArrayEnvironment;
use ILIAS\Setup\Objective\Tentatively;
use ILIAS\Setup\Metrics;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use ILIAS\Setup\Agent;

/**
 * Command to output status information about the installation.
 */
class StatusCommand extends Command
{
    use HasAgent;
    use ObjectiveHelper;

    protected static $defaultName = "status";

    public function __construct(AgentFinder $agent_finder)
    {
        parent::__construct();
        $this->agent_finder = $agent_finder;
    }

    protected function configure(): void
    {
        $this->setDescription("Collect and show status information about the installation.");
        $this->configureCommandForPlugins();
        $this->addOption("filter", "f", InputOption::VALUE_REQUIRED, "filter for specific information");
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $agent = $this->getRelevantAgent($input);
        $filter = "";
        if ($input->hasOption("filter") && $input->getOption("filter") != "") {
            $filter = $input->getOption("filter");
        }
        $output->write($this->getMetrics($agent, $filter)->toYAML() . "\n");

        return 0;
    }

    public function getMetrics(Agent $agent, string $filter = ""): Metrics\Metric
    {
        // ATTENTION: Don't do this (in general), please have a look at the comment
        // in ilIniFilesLoadedObjective.
        \ilIniFilesLoadedObjective::$might_populate_ini_files_as_well = false;

        $environment = new ArrayEnvironment([]);
        $storage = new Metrics\ArrayStorage();
        $objective = new Tentatively(
            $agent->getStatusObjective($storage)
        );

        $this->achieveObjective($objective, $environment);

        $metric = $storage->asMetric();

        list($show_config_section, $metric) = $this->filter($filter, $metric);

        $type = Metrics\MetricType::COLLECTION;
        list($config, $other) = $metric->extractByStability(Metrics\MetricStability::CONFIG);
        $values = [];
        if ($other && !$show_config_section) {
            $values = $other->getValue();
            $type = $metric->getType();
        }

        if ($config) {
            if ($show_config_section) {
                $values = $config->getValue();
                $type = $config->getType();
            } else {
                $values["config"] = $config;
            }
        }

        return new Metrics\Metric(
            Metrics\MetricStability::VOLATILE,
            $type,
            fn() => $values
        );
    }

    protected function filter(string $filter, Metrics\Metric $metric): array
    {
        $show_config_section = false;
        if ($filter != "") {
            $filter = explode(".", $filter);

            if ($filter[0] === "config") {
                array_shift($filter);
                $show_config_section = true;
            } else {
                $show_config_section = false;
            }

            while (count($filter) > 0) {
                if ($metric->getType() !== Metrics\MetricType::COLLECTION) {
                    throw new \RuntimeException("Cannot find key...");
                }

                $current = array_shift($filter);
                $metrics = $metric->getValue();
                if (!array_key_exists($current, $metrics)) {
                    throw new \RuntimeException("Cannot find key...");
                }
                $metric = $metrics[$current];
            }
        }
        return array($show_config_section, $metric);
    }
}
