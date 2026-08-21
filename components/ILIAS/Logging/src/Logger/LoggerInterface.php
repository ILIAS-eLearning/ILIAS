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

use Psr\Log\LoggerInterface as PsrLoggerInterface;
use Psr\Log\LogLevel;
use Stringable;
use ILIAS\Logging\ILIASLogLevel;

interface LoggerInterface extends PsrLoggerInterface
{
    /**
     * It is recommended to use {@see ILIASLogLevel} to set the level.
     */
    public function isHandlingLogLevel(mixed $level): bool;

    /**
     * It is recommended to use {@see ILIASLogLevel} to set the level.
     */
    public function dump(mixed $value, mixed $level = ILIASLogLevel::INFO): void;

    /**
     * It is recommended to use {@see ILIASLogLevel} to set the level.
     */
    public function logStack(mixed $level = ILIASLogLevel::INFO, string|Stringable $message = '', array $context = []): void;
}
