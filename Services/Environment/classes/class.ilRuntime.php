<?php

declare(strict_types=1);

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

/**
 * Class ilRuntime
 * @author  Michael Jansen <mjansen@databay.de>
 * @package Services/Environment
 */
final class ilRuntime
{
    private static ?self $instance = null;

    /**
     * The runtime is a constant state during one request, so please use the public static getInstance() to instantiate the runtime
     */
    private function __construct()
    {
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
        return PHP_SAPI === 'fpm-fcgi';
    }

    public function getVersion(): string
    {
        if ($this->isHHVM()) {
            return HHVM_VERSION;
        }

        return PHP_VERSION;
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

        return (int) ini_get('error_reporting');
    }

    public function shouldLogErrors(): bool
    {
        if ($this->isHHVM()) {
            return (bool) ini_get('hhvm.log.use_log_file');
        }

        return (bool) ini_get('log_errors');
    }

    public function shouldDisplayErrors(): bool
    {
        if ($this->isHHVM()) {
            return (bool) ini_get('hhvm.debug.server_error_message');
        }

        return (bool) ini_get('display_errors');
    }

    public function getBinary(): string
    {
        if (defined('PHP_BINARY') && PHP_BINARY !== '') {
            return escapeshellarg(PHP_BINARY);
        }

        $possibleBinaryLocations = [
            PHP_BINDIR . '/php',
            PHP_BINDIR . '/php-cli.exe',
            PHP_BINDIR . '/php.exe',
        ];

        foreach ($possibleBinaryLocations as $binary) {
            if (is_readable($binary)) {
                return escapeshellarg($binary);
            }
        }

        return 'php';
    }
}
