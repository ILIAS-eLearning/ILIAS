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

namespace ILIAS\Logging\Logger;

use ILIAS\Logging\Config\Basic\ConfigInterface;
use ILIAS\Logging\Logger\LevelFetcher\DefaultLevelFetcher;
use ILIAS\Logging\Logger\LevelFetcher\LevelFetcherFactoryInterface;

class DefaultConfigLoggerFactory implements DefaultConfigLoggerFactoryInterface
{
    public function __construct(
        protected LazyInternalFactoryInterface $internal_factory,
        protected ConfigInterface $basic_config,
        protected LevelFetcherFactoryInterface $level_fetcher_factory
    ) {
    }

    public function getLazy(string $component_id): LoggerInterface
    {
        return $this->internal_factory->getLazyGhost(
            $component_id,
            $this->level_fetcher_factory->defaultLevelFetcher($this->basic_config)
        );
    }
}
