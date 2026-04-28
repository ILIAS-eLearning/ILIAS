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

namespace ILIAS\Logging\Configuration;

use ILIAS\Environment\Configuration\Instance\IliasIni;
use ILIAS\Environment\Configuration\Instance\ClientIni;

/**
 * Default LoggingConfig backed by the ini files (`IliasIni`, `ClientIni`)
 * and direct queries against the `settings` (module = `logging`) and
 * `log_components` tables.
 *
 * The constructor must remain side-effect free so it can be evaluated at
 * bootstrap build time. All DB-backed values are loaded lazily on the
 * first read access.
 *
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class LoggingConfigImpl implements LoggingConfig
{
    private bool $db_loaded = false;

    private int $level = \ilLogLevel::INFO;
    private bool $cache = false;
    private int $cache_level = \ilLogLevel::DEBUG;
    private bool $memory_usage = false;
    private bool $browser = false;

    /**
     * @var string[]
     */
    private array $browser_users = [];

    /**
     * @var array<string, int>
     */
    private array $component_levels = [];

    public function __construct(
        private readonly IliasIni $ilias_ini,
        private readonly ClientIni $client_ini,
        private readonly \ilDBInterface $db,
    ) {
    }

    public function isLoggingEnabled(): bool
    {
        return $this->ilias_ini->isLogEnabled();
    }

    public function getLogDirectory(): string
    {
        return $this->ilias_ini->getLogPath();
    }

    public function getLogFile(): string
    {
        return $this->ilias_ini->getLogFile();
    }

    public function getLevel(): int
    {
        $this->loadDbValues();
        return $this->level;
    }

    public function getLevelByComponent(string $component_id): int
    {
        $this->loadDbValues();
        $level = $this->component_levels[$component_id] ?? 0;
        return $level !== 0 ? $level : $this->level;
    }

    public function getCacheLevel(): int
    {
        $this->loadDbValues();
        return $this->cache_level;
    }

    public function isCacheEnabled(): bool
    {
        $this->loadDbValues();
        return $this->cache;
    }

    public function isMemoryUsageEnabled(): bool
    {
        $this->loadDbValues();
        return $this->memory_usage;
    }

    public function isBrowserLogEnabled(): bool
    {
        $this->loadDbValues();
        return $this->browser;
    }

    public function isBrowserLogEnabledForUser(string $login): bool
    {
        if (!$this->isBrowserLogEnabled()) {
            return false;
        }
        return in_array($login, $this->browser_users, true);
    }

    public function getBrowserLogUsers(): array
    {
        $this->loadDbValues();
        return $this->browser_users;
    }

    public function getErrorDirectory(): string
    {
        return $this->ilias_ini->getLogErrorPath();
    }

    public function getErrorRecipient(): string
    {
        return $this->client_ini->getLogErrorRecipient();
    }

    private function loadDbValues(): void
    {
        if ($this->db_loaded) {
            return;
        }

        // The injected DB may still be a DBLegacyProxy at this point, which
        // forwards to `$DIC['ilDB']` — that offset is only populated by
        // `ilInitialisation::initDatabase()`. Bail out (and retry on the
        // next call) until the legacy global is in place.
        if (!isset($GLOBALS['DIC']['ilDB'])) {
            return;
        }

        if (!$this->db->tableExists('settings')) {
            return;
        }

        $this->db_loaded = true;

        $res = $this->db->queryF(
            'SELECT keyword, value FROM settings WHERE module = %s',
            ['text'],
            ['logging']
        );
        $rows = [];
        while ($row = $this->db->fetchAssoc($res)) {
            $rows[(string) $row['keyword']] = (string) $row['value'];
        }

        if (isset($rows['level'])) {
            $this->level = (int) $rows['level'];
        }
        if (isset($rows['cache'])) {
            $this->cache = (bool) $rows['cache'];
        }
        if (isset($rows['cache_level'])) {
            $this->cache_level = (int) $rows['cache_level'];
        }
        if (isset($rows['memory_usage'])) {
            $this->memory_usage = (bool) $rows['memory_usage'];
        }
        if (isset($rows['browser'])) {
            $this->browser = (bool) $rows['browser'];
        }
        if (isset($rows['browser_users'])) {
            $decoded = unserialize($rows['browser_users'], ['allowed_classes' => false]);
            if (is_array($decoded)) {
                $this->browser_users = array_values(array_map('strval', $decoded));
            }
        }

        if ($this->db->tableExists('log_components')) {
            $res = $this->db->query('SELECT component_id, log_level FROM log_components');
            while ($row = $this->db->fetchAssoc($res)) {
                $this->component_levels[(string) $row['component_id']] = (int) $row['log_level'];
            }
        }
    }
}
