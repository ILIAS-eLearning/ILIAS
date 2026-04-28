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

namespace ILIAS\Logging;

use ILIAS\Logging\Configuration\LoggingConfig;

/**
 * Fluent shortcut for component loggers is exposed via `__call` on the
 * concrete implementation: `$services->myComponent()` is equivalent to
 * `$services->getComponentLogger('myComponent')`.
 */
interface Services
{
    public function getComponentLogger(string $component_id): \ilLogger;

    public function getRootLogger(): \ilLogger;

    public function getLogger(string $component_id): \ilLogger;

    /**
     * @deprecated Use {@see Services::getRootLogger()} instead.
     */
    public function root(): \ilLogger;

    public function getConfig(): LoggingConfig;

    /**
     * @deprecated Use {@see Services::getConfig()} instead.
     */
    public function getSettings(): \ilLoggingSettings;

    public function initUser(string $login): void;
}
