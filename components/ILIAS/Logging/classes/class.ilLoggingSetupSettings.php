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
* Logger settings for setup
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @ingroup ServicesLogging
*/
class ilLoggingSetupSettings implements ilLoggingSettings
{
    private bool $enabled = false;
    private string $log_dir = '';
    private string $log_file = '';


    public function init(): void
    {
        $ilIliasIniFile = new ilIniFile("./ilias.ini.php");
        $ilIliasIniFile->read();

        $enabled = $ilIliasIniFile->readVariable('log', 'enabled');
        $this->enabled = $enabled == '1';
        $this->log_dir = (string) $ilIliasIniFile->readVariable('log', 'path');
        $this->log_file = (string) $ilIliasIniFile->readVariable('log', 'file');
    }

    /**
     * Logging enabled
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function getLogDir(): string
    {
        return $this->log_dir;
    }

    public function getLogFile(): string
    {
        return $this->log_file;
    }

    /**
     * Get log Level
     * @return int
     */
    public function getLevel(): int
    {
        return ilLogLevel::INFO;
    }

    public function getLevelByComponent(string $a_component_id): int
    {
        return $this->getLevel();
    }

    /**
     * Get log Level
     * @return int
     */
    public function getCacheLevel(): int
    {
        return ilLogLevel::INFO;
    }

    public function isCacheEnabled(): bool
    {
        return false;
    }

    public function isMemoryUsageEnabled(): bool
    {
        return false;
    }

    public function isBrowserLogEnabled(): bool
    {
        return false;
    }

    public function isBrowserLogEnabledForUser(string $a_login): bool
    {
        return false;
    }

    public function getBrowserLogUsers(): array
    {
        return array();
    }
}
