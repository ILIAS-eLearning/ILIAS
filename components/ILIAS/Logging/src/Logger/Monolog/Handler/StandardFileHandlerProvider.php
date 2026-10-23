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

namespace ILIAS\Logging\Logger\Monolog\Handler;

use ILIAS\Logging\ILIASLogLevel;
use Monolog\Handler\HandlerInterface as MonologHandlerInterface;
use Monolog\Handler\StreamHandler as MonologStreamHandler;
use ILIAS\Logging\Config\Basic\ConfigInterface as BasicConfigInterface;

class StandardFileHandlerProvider implements ProviderInterface
{
    public function __construct(
        protected BasicConfigInterface $basic_config
    ) {
    }

    public function appliesTo(string $component_id): bool
    {
        return true;
    }

    public function getHandler(string $component_id, ILIASLogLevel $level): MonologHandlerInterface
    {
        $stream_handler = new MonologStreamHandler(
            $this->basic_config->pathToLogFile(),
            $level->value,
            true
        );

        $line_formatter = new ILIASLineFormatter();
        $stream_handler->setFormatter($line_formatter);

        return $stream_handler;
    }
}
