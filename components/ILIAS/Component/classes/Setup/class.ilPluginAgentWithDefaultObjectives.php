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

use ILIAS\Setup;
use ILIAS\Refinery\Transformation;

/**
 * Adds the default objectives of a legacy plugin (installation, update and,
 * on installation, activation) to an agent contributed by that plugin which
 * does not extend ilPluginDefaultAgent.
 *
 * Everything else is delegated to the contributed agent, so its named
 * objectives, migrations, metrics and config keep their paths.
 */
class ilPluginAgentWithDefaultObjectives extends ilPluginDefaultAgent
{
    public function __construct(
        string $plugin_name,
        protected Setup\Agent $agent
    ) {
        parent::__construct($plugin_name);
    }

    public function hasConfig(): bool
    {
        return $this->agent->hasConfig();
    }

    public function getArrayToConfigTransformation(): Transformation
    {
        return $this->agent->getArrayToConfigTransformation();
    }

    public function getInstallObjective(Setup\Config $config = null): Setup\Objective
    {
        return new Setup\ObjectiveCollection(
            'Install legacy plugin ' . $this->plugin_name,
            false,
            parent::getInstallObjective($config),
            $this->agent->getInstallObjective($config)
        );
    }

    public function getUpdateObjective(Setup\Config $config = null): Setup\Objective
    {
        return new Setup\ObjectiveCollection(
            'Update legacy plugin ' . $this->plugin_name,
            false,
            parent::getUpdateObjective($config),
            $this->agent->getUpdateObjective($config)
        );
    }

    public function getBuildObjective(): Setup\Objective
    {
        return $this->agent->getBuildObjective();
    }

    public function getStatusObjective(Setup\Metrics\Storage $storage): Setup\Objective
    {
        return $this->agent->getStatusObjective($storage);
    }

    public function getMigrations(): array
    {
        return $this->agent->getMigrations();
    }

    public function getNamedObjectives(?Setup\Config $config = null): array
    {
        return $this->agent->getNamedObjectives($config);
    }
}
