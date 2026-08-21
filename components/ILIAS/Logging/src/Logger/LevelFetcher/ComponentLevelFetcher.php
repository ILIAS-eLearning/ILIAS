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

namespace ILIAS\Logging\Logger\LevelFetcher;

use ILIAS\Logging\ILIASLogLevel;
use ILIAS\Logging\Config\ByComponent\ConfigInterface as ConfigByComponentInterface;

class ComponentLevelFetcher implements LevelFetcherInterface
{
    public function __construct(
        protected ConfigByComponentInterface $config_by_component,
        protected string $component_id
    ) {
    }

    public function fetchLevel(): ILIASLogLevel
    {
        return $this->config_by_component->level($this->component_id);
    }
}
