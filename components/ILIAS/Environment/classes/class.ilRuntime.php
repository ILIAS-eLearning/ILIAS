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

use ILIAS\Environment\Configuration\Server\PhpServerConfiguration;

/**
 * @deprecated Use \ILIAS\Environment\Configuration\Server\ServerConfiguration instead
 */
final class ilRuntime implements Stringable
{
    private static ?self $instance = null;
    private PhpServerConfiguration $server;

    /**
     * The runtime is a constant state during one request, so please use the public static getInstance() to instantiate the runtime
     */
    private function __construct()
    {
        $this->server = new PhpServerConfiguration();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function isHHVM(): bool
    {
        return defined('HHVM_VERSION');
    }

    public function isPHP(): bool
    {
        return !$this->isHHVM();
    }

    public function isFPM(): bool
    {
        return $this->server->isFpm();
    }

    public function getVersion(): string
    {
        if ($this->isHHVM()) {
            return HHVM_VERSION;
        }

        return $this->server->getPhpVersion();
    }

    public function getName(): string
    {
        if ($this->isHHVM()) {
            return 'HHVM';
        }

        return 'PHP';
    }

    public function __toString(): string
    {
        return $this->getName() . ' ' . $this->getVersion();
    }

    public function getReportedErrorLevels(): int
    {
        if ($this->isHHVM()) {
            return (int) ini_get('hhvm.log.runtime_error_reporting_level');
        }

        return $this->server->getErrorReportingLevel();
    }

    public function shouldLogErrors(): bool
    {
        if ($this->isHHVM()) {
            return (bool) ini_get('hhvm.log.use_log_file');
        }

        return $this->server->isErrorLoggingEnabled();
    }

    public function shouldDisplayErrors(): bool
    {
        if ($this->isHHVM()) {
            return (bool) ini_get('hhvm.debug.server_error_message');
        }

        return $this->server->isErrorDisplayEnabled();
    }

    public function getBinary(): string
    {
        return $this->server->getPhpBinary();
    }
}
