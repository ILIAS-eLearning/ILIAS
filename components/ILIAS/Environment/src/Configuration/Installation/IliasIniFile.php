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

namespace ILIAS\Environment\Configuration\Installation;

use ILIAS\Environment\Configuration\Ini\IniFileReadRepository;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class IliasIniFile extends IniFileReadRepository implements IliasIni
{
    // [server]

    public function getHttpPath(): string
    {
        return $this->getOrEmpty('server', 'http_path');
    }

    public function getAbsolutePath(): string
    {
        return $this->getOrEmpty('server', 'absolute_path');
    }

    public function getTimezone(): string
    {
        return $this->getOrEmpty('server', 'timezone');
    }

    // [clients]

    public function getClientsPath(): string
    {
        return $this->getOrEmpty('clients', 'path');
    }

    public function getClientIniFile(): string
    {
        return $this->getOrEmpty('clients', 'inifile');
    }

    public function getDataDirectory(): string
    {
        return $this->getOrEmpty('clients', 'datadir');
    }

    public function getDefaultClientId(): string
    {
        return $this->getOrEmpty('clients', 'default');
    }

    // [log]

    public function getLogPath(): string
    {
        return $this->getOrEmpty('log', 'path');
    }

    public function getLogFile(): string
    {
        return $this->getOrEmpty('log', 'file');
    }

    public function isLogEnabled(): bool
    {
        return $this->getOrEmpty('log', 'enabled') === '1';
    }

    public function getDefaultLogLevel(): string
    {
        return $this->getOrEmpty('log', 'default_level');
    }

    public function getLogErrorPath(): string
    {
        return $this->getOrEmpty('log', 'error_path');
    }

    // [tools]

    public function getConvertPath(): string
    {
        return $this->getOrEmpty('tools', 'convert');
    }

    public function getZipPath(): string
    {
        return $this->getOrEmpty('tools', 'zip');
    }

    public function getUnzipPath(): string
    {
        return $this->getOrEmpty('tools', 'unzip');
    }

    public function getJavaPath(): string
    {
        return $this->getOrEmpty('tools', 'java');
    }

    public function getFfmpegPath(): string
    {
        return $this->getOrEmpty('tools', 'ffmpeg');
    }

    public function getGhostscriptPath(): string
    {
        return $this->getOrEmpty('tools', 'ghostscript');
    }

    public function getFopPath(): string
    {
        return $this->getOrEmpty('tools', 'fop');
    }

    public function getLatexPath(): string
    {
        return $this->getOrEmpty('tools', 'latex');
    }

    // [https]

    public function isAutoHttpsDetectEnabled(): bool
    {
        return $this->getOrEmpty('https', 'auto_https_detect_enabled') === '1';
    }

    public function getAutoHttpsDetectHeaderName(): string
    {
        return $this->getOrEmpty('https', 'auto_https_detect_header_name');
    }

    public function getAutoHttpsDetectHeaderValue(): string
    {
        return $this->getOrEmpty('https', 'auto_https_detect_header_value');
    }
}
