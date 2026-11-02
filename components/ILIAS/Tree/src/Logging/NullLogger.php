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

namespace ILIAS\Tree\Logging;

use ILIAS\Logging\Logger\LoggerInterface;
use ILIAS\Logging\ILIASLogLevel;
use Stringable;
use Psr\Log\LoggerTrait;

/**
 * Null logger implementation that discards all log messages.
 * Used in environments where logging is not available or not needed (e.g., setup).
 */
class NullLogger implements LoggerInterface
{
    use LoggerTrait;

    public function log($level, \Stringable|string $message, array $context = []): void
    {
    }

    public function isHandlingLogLevel(mixed $level): bool
    {
        return true;
    }

    public function dump(mixed $value, mixed $level = ILIASLogLevel::INFO): void
    {
    }

    public function logStack(
        mixed $level = ILIASLogLevel::INFO,
        Stringable|string $message = '',
        array $context = []
    ): void {
    }
}
