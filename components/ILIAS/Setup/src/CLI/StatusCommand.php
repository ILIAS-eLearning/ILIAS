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

use ILIAS\Setup\AgentCollection;
use ILIAS\Setup\AgentFinder;
use ILIAS\Setup\ArrayEnvironment;
use ILIAS\Setup\Objective\Tentatively;
use ILIAS\Setup\Metrics;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
        $this->addOption(
            "filter",
            "f",
            InputOption::VALUE_REQUIRED,
            "Only show the metrics on this path, e.g. \"database\" or \"config.common\"."
        );
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filter = (string) ($input->getOption("filter") ?? "");

        try {
            $metrics = $this->getMetrics($this->getAgentFor($input, $filter), $filter);
        } catch (MetricsFilterException $e) {
            $output->writeln("<error>" . $e->getMessage() . "</error>");
            return Command::FAILURE;
        }

        $output->write($metrics->toYAML() . "\n");

        return Command::SUCCESS;
    }

    /**
     * The first part of a filter names an agent. Only that agent is built and asked
     * for its status, which spares the others and, for an agent of the core, even
     * finding the agents of the plugins.
     */
    protected function getAgentFor(InputInterface $input, string $filter): Agent
    {
        $key = $this->getAgentKeyOf($filter);
        if ($key === null
            || ($input->hasOption("legacy-plugin") && $input->getOption("legacy-plugin"))) {
            return $this->getRelevantAgent($input);
        }

        $component_agents = $this->agent_finder->getComponentAgents();
        if ($component_agents->getAgent($key) !== null) {
            return $component_agents->withOnlyAgent($key);
        }

        $agents = $this->getRelevantAgent($input);
        if (!$agents instanceof AgentCollection) {
            return $agents;
        }
        if ($agents->getAgent($key) === null) {
            throw new MetricsFilterException(
                "There is no metric '$key'. Run the command without a filter to see which metrics are available."
            );
        }
        return $agents->withOnlyAgent($key);
    }

    protected function getAgentKeyOf(string $filter): ?string
    {
        $path = $this->getPathOf($filter);
        return $path === [] ? null : $path[0];
    }

    /**
     * @return string[]
     */
    protected function getPathOf(string $filter): array
    {
        $path = array_values(array_filter(explode(".", $filter), fn(string $p): bool => $p !== ""));
        if (($path[0] ?? null) === "config") {
            array_shift($path);
        }
        return $path;
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

        $show_config_section = explode(".", $filter)[0] === "config";
        $metric = $this->select($this->getPathOf($filter), $storage->asMetric());

        $type = Metrics\Metric::TYPE_COLLECTION;
        list($config, $other) = $metric->extractByStability(Metrics\Metric::STABILITY_CONFIG);
        $values = [];
        if ($other && !$show_config_section) {
            $values = $other->getValue();
            $type = $other->getType();
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
            $type === Metrics\Metric::TYPE_COLLECTION
                ? Metrics\Metric::STABILITY_MIXED
                : Metrics\Metric::STABILITY_VOLATILE,
            $type,
            $values
        );
    }

    /**
     * @param string[] $path
     */
    protected function select(array $path, Metrics\Metric $metric): Metrics\Metric
    {
        $done = [];
        foreach ($path as $key) {
            if ($metric->getType() !== Metrics\Metric::TYPE_COLLECTION) {
                throw new MetricsFilterException(
                    "Cannot filter by '$key': "
                    . ($done === [] ? "the status" : "'" . implode(".", $done) . "'")
                    . " is a single value, not a collection."
                );
            }
            $metrics = $metric->getValue();
            if (!array_key_exists($key, $metrics)) {
                throw new MetricsFilterException(
                    "There is no metric '" . implode(".", [...$done, $key]) . "'. Available"
                    . ($done === [] ? "" : " below '" . implode(".", $done) . "'")
                    . ": " . implode(", ", array_keys($metrics))
                );
            }
            $metric = $metrics[$key];
            $done[] = $key;
        }
        return $metric;
    }
}
