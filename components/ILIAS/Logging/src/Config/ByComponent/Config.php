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

namespace ILIAS\Logging\Config\ByComponent;

use ILIAS\Logging\ILIASLogLevel;
use ILIAS\Logging\Config\Basic\ConfigInterface as BasicConfigInterface;

class Config implements ConfigInterface
{
    /**
     * @var array<string, ?ILIASLogLevel>
     */
    protected array $levels_by_component_id;

    public function __construct(
        protected RepositoryInterface $repo,
        protected BasicConfigInterface $basic_config
    ) {
    }

    public function level(string $component_id): ILIASLogLevel
    {
        return $this->getLevels()[$component_id] ?? $this->basic_config->defaultLevel();
    }

    /**
     * @return array<string, ?ILIASLogLevel>
     */
    protected function getLevels(): array
    {
        return $this->levels_by_component_id ??= $this->repo->getAllLevelsForComponents();
    }
}
