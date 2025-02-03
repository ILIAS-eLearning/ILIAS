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
/**
 * @classDescription Creates a java server ini file for the current client
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilRpcIniFileWriter
{
    protected string $ini = '';

    protected string $host = '';
    protected int $port = 0;
    protected string $indexPath = '';
    protected string $logPath = '';
    protected string $logLevel = '';
    protected int $numThreads = 0;
    protected string $max_file_size = '';

    protected ilSetting $settings;
    protected ilIniFile $inifile;

    public function __construct()
    {
        global $DIC;
        $this->settings = $DIC->settings();
        $this->inifile = $DIC['ilIliasIniFile'];
    }

    public function write(): bool
    {
        // Main section
        $this->ini = "[Server]\n";
        $this->ini .= "IpAddress = " . $this->getHost() . "\n";
        $this->ini .= "Port = " . $this->getPort() . "\n";
        $this->ini .= "IndexPath = " . $this->getIndexPath() . "\n";
        $this->ini .= "LogFile = " . $this->getLogPath() . "\n";
        $this->ini .= "LogLevel = " . $this->getLogLevel() . "\n";
        $this->ini .= "NumThreads = " . $this->getNumThreads() . "\n";
        $this->ini .= "RamBufferSize = 256\n";
        $this->ini .= "IndexMaxFileSizeMB = " . $this->getMaxFileSize() . "\n";

        $this->ini .= "\n";

        $this->ini .= "[Client1]\n";
        $this->ini .= "ClientId = " . CLIENT_ID . "\n";
        $this->ini .= "NicId = " . $this->settings->get('inst_id', '0') . "\n";
        $this->ini .= "IliasIniPath = " . $this->inifile->readVariable(
            'server',
            'absolute_path'
        ) . DIRECTORY_SEPARATOR . "ilias.ini.php\n";

        return true;
    }

    public function getIniString(): string
    {
        return $this->ini;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function setHost(string $host): void
    {
        $this->host = $host;
    }

    public function getIndexPath(): string
    {
        return $this->indexPath;
    }

    public function setIndexPath(string $indexPath): void
    {
        $this->indexPath = $indexPath;
    }

    public function getLogLevel(): string
    {
        return $this->logLevel;
    }

    public function setLogLevel(string $logLevel): void
    {
        $this->logLevel = $logLevel;
    }

    public function getLogPath(): string
    {
        return $this->logPath;
    }

    public function setLogPath(string $logPath): void
    {
        $this->logPath = $logPath;
    }

    public function getNumThreads(): int
    {
        return $this->numThreads;
    }

    public function setNumThreads(int $numThreads): void
    {
        $this->numThreads = $numThreads;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function setPort(int $port): void
    {
        $this->port = $port;
    }

    public function setMaxFileSize(string $a_fs): void
    {
        $this->max_file_size = $a_fs;
    }

    public function getMaxFileSize(): string
    {
        return $this->max_file_size;
    }
}
