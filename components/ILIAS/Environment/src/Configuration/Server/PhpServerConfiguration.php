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

namespace ILIAS\Environment\Configuration\Server;

use ILIAS\Data\DataSize;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class PhpServerConfiguration implements ServerConfiguration
{
    // — PHP runtime —

    public function getPhpVersion(): string
    {
        return PHP_VERSION;
    }

    public function getPhpSapi(): string
    {
        return PHP_SAPI;
    }

    public function isFpm(): bool
    {
        return PHP_SAPI === 'fpm-fcgi';
    }

    public function getPhpBinary(): string
    {
        if (PHP_BINARY !== '') {
            return escapeshellarg(PHP_BINARY);
        }

        foreach ([PHP_BINDIR . '/php', PHP_BINDIR . '/php-cli.exe', PHP_BINDIR . '/php.exe'] as $candidate) {
            if (is_readable($candidate)) {
                return escapeshellarg($candidate);
            }
        }

        return 'php';
    }

    // — Error handling —

    public function getErrorReportingLevel(): int
    {
        return (int) ini_get('error_reporting');
    }

    public function isErrorLoggingEnabled(): bool
    {
        return filter_var(ini_get('log_errors'), FILTER_VALIDATE_BOOLEAN);
    }

    public function isErrorDisplayEnabled(): bool
    {
        return filter_var(ini_get('display_errors'), FILTER_VALIDATE_BOOLEAN);
    }

    // — Memory & upload limits —

    public function getMemoryLimit(): DataSize
    {
        return new DataSize($this->parseIniBytes(ini_get('memory_limit')), DataSize::Byte);
    }

    public function getUploadSizeLimit(): DataSize
    {
        $bytes = min(
            $this->parseIniBytes(ini_get('post_max_size')),
            $this->parseIniBytes(ini_get('upload_max_filesize')),
        );
        return new DataSize($bytes, DataSize::Byte);
    }

    public function getPostMaxSize(): DataSize
    {
        return new DataSize($this->parseIniBytes(ini_get('post_max_size')), DataSize::Byte);
    }

    public function getUploadMaxFilesize(): DataSize
    {
        return new DataSize($this->parseIniBytes(ini_get('upload_max_filesize')), DataSize::Byte);
    }

    // — Execution constraints —

    public function getMaxExecutionTime(): int
    {
        return (int) ini_get('max_execution_time');
    }

    public function getMaxInputVars(): int
    {
        return (int) ini_get('max_input_vars');
    }

    // — Operating system —

    public function getOperatingSystem(): string
    {
        return PHP_OS;
    }

    public function getOperatingSystemFamily(): string
    {
        return PHP_OS_FAMILY;
    }

    // — Internal —

    private function parseIniBytes(string $value): int
    {
        if (is_numeric($value)) {
            return (int) $value;
        }

        $suffix = strtoupper(substr($value, -1));
        $number = (int) substr($value, 0, -1);

        return match ($suffix) {
            'P' => $number * 1024 ** 5,
            'T' => $number * 1024 ** 4,
            'G' => $number * 1024 ** 3,
            'M' => $number * 1024 ** 2,
            'K' => $number * 1024,
            default => (int) $value,
        };
    }
}
