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

namespace ILIAS\Logging\Logger\Monolog;

use ILIAS\Logging\ILIASLogLevel;
use Monolog\Logger as MonologLogger;
use Monolog\Handler\NullHandler;
use Monolog\Processor\PsrLogMessageProcessor;
use Monolog\Handler\Handler;
use Monolog\Handler\StreamHandler;

class Factory implements FactoryInterface
{
    public function logger(string $name, ILIASLogLevel $level, string $file_path): MonologLogger
    {
        $logger = new MonologLogger($name);

        $handler = $this->buildStandardHandler($level, $file_path);
        $logger->pushHandler($handler);

        $logger->pushProcessor(function ($record) {
            $record['extra']['suid'] = substr(session_id(), 0, 5);
            return $record;
        }); // suid log
        $logger->pushProcessor(new ILIASTraceProcessor(ILIASLogLevel::DEBUG)); // append trace
        $logger->pushProcessor(new PsrLogMessageProcessor()); // Interpolate context variables.

        return $logger;
    }

    protected function buildStandardHandler(ILIASLogLevel $level, string $file_path): Handler
    {
        $stream_handler = new StreamHandler(
            $file_path,
            $level->value,
            true
        );

        $line_formatter = new ILIASLineFormatter();
        $stream_handler->setFormatter($line_formatter);

        return $stream_handler;
    }

    public function nullLogger(string $name): MonologLogger
    {
        $logger = new MonologLogger($name);
        $logger->pushHandler(new NullHandler());
        return $logger;
    }
}
