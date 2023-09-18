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
use ILIAS\Setup\Objective\Tentatively;
use ILIAS\Setup\Metrics;
use Symfony\Component\Console\Command\Command;
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
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $agent = $this->getRelevantAgent($input);

        $output->write($this->getMetrics($agent)->toYAML() . "\n");

        return 0;
    }

    public function getMetrics(Agent $agent): Metrics\Metric
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
        list($config, $other) = $metric->extractByStability(Metrics\Metric::STABILITY_CONFIG);
        if ($other) {
            $values = $other->getValue();
        } else {
            $values = [];
        }
        if ($config) {
            $values["config"] = $config;
        }

        return new Metrics\Metric(
            Metrics\Metric::STABILITY_MIXED,
            Metrics\Metric::TYPE_COLLECTION,
            $values
        );
    }
}
