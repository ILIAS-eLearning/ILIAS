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

use ILIAS\DI\Container;

/**
 * Class ilBenchmark
 */
class ilBenchmark
{
    public const DB_BENCH_USER = "db_bench_user";
    public const ENABLE_DB_BENCH = "enable_db_bench";
    private ?ilDBInterface $db = null;
    private ?ilSetting $settings = null;
    private ?ilObjUser $user = null;

    private Container $dic;

    private string $start = '';
    private ?string $temporary_sql_storage = '';
    private array $collected_db_benchmarks = [];

    private bool $stop_db_recording = false;

    private int $bench_max_records;

    private ?bool $db_bechmark_enabled = null;
    private ?int $db_bechmark_user_id = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;
        $this->dic = $DIC;
        $this->initSettins();
        $this->bench_max_records = 2000;//(int) ($this->retrieveSetting("bench_max_records") ?? 0);
    }

    private function initSettins(): void
    {
        if (!$this->settings instanceof ilSetting) {
            $global_settings_available = $this->dic->isDependencyAvailable('settings');
            if ($global_settings_available) {
                $this->settings = $this->dic->settings();

                $this->db_bechmark_enabled = (bool) ($this->retrieveSetting(self::ENABLE_DB_BENCH) ?? false);
                $user_id = $this->retrieveSetting(self::DB_BENCH_USER);
                $this->db_bechmark_user_id = $user_id !== null ? (int) $user_id : null;
            }
        }
    }

    private function retrieveSetting(string $keyword): ?string
    {
        return $this->settings !== null
            ? $this->settings->get($keyword)
            : null;
    }

    private function retrieveDB(): ?ilDBInterface
    {
        if (!$this->db instanceof ilDBInterface && $this->dic->isDependencyAvailable('database')) {
            $this->db = $this->dic->database();
        }
        return $this->db;
    }

    private function isDBavailable(): bool
    {
        return !is_null($this->retrieveDB());
    }

    private function retrieveUser(): ?ilObjUser
    {
        if (!$this->user instanceof ilObjUser && $this->dic->isDependencyAvailable('user')) {
            $this->user = $this->dic->user();
        }
        return $this->user;
    }

    private function isUserAvailable(): bool
    {
        return !is_null($this->retrieveUser());
    }

    private function microtimeDiff(string $t1, string $t2): string
    {
        $partials1 = explode(" ", $t1);
        $partials2 = explode(" ", $t2);

        return (string) ((float) $partials2[0] - (float) $partials1[0] + (float) $partials2[1] - (float) $partials1[1]);
    }

    /**
     * delete all measurement data
     */
    public function clearData(): void
    {
        if ($this->isDBavailable()) {
            $db = $this->retrieveDB();
            if ($db !== null) {
                $db->manipulate("DELETE FROM benchmark");
            }
        }
    }

    /**
     * start measurement
     *
     * @deprecated
     */
    public function start(string $a_module, string $a_bench): void
    {
    }

    /**
     * stop measurement
     *
     * @deprecated
     */
    public function stop(string $a_module, string $a_bench): void
    {
    }

    /**
     * save all measurements
     */
    public function save(): void
    {
        if (!$this->isDBavailable() || !$this->isUserAvailable()) {
            return;
        }
        if ($this->isDbBenchEnabled()
            && $this->db_bechmark_user_id === $this->user->getId()) {
            if (is_array($this->collected_db_benchmarks)) {
                $this->stop_db_recording = true;

                $db = $this->retrieveDB();
                if ($db !== null) {
                    $db->manipulate("DELETE FROM benchmark");
                    foreach ($this->collected_db_benchmarks as $b) {
                        $id = $db->nextId('benchmark');
                        $db->insert("benchmark", [
                            "id" => ["integer", $id],
                            "duration" => ["float", $this->microtimeDiff($b["start"], $b["stop"])],
                            "sql_stmt" => ["clob", $b["sql"]]
                        ]);
                    }
                }
            }
            $this->disableDbBenchmark();
        }
    }

    /**
     * get current number of benchmark records
     */
    private function getCurrentRecordNumber(): int
    {
        if ($this->isDBavailable()) {
            $db = $this->retrieveDB();
            if ($db !== null) {
                $cnt_set = $db->query("SELECT COUNT(*) AS cnt FROM benchmark");
                $cnt_rec = $db->fetchAssoc($cnt_set);
                return (int) $cnt_rec["cnt"];
            }
        }
        return 0;
    }

    //
    //
    // NEW DB BENCHMARK IMPLEMENTATION
    //
    //

    /**
     * Check wether benchmarking is enabled or not
     */
    public function isDbBenchEnabled(): bool
    {
        return $this->db_bechmark_enabled === true && $this->isDBavailable();
    }

    public function enableDbBenchmarkForUserName(?string $a_user): void
    {
        if ($a_user === null) {
            $this->disableDbBenchmark();
            return;
        }
        $this->initSettins();
        $this->db_bechmark_enabled = true;
        $this->settings->set(self::ENABLE_DB_BENCH, '1');

        $user_id = ilObjUser::_lookupId($a_user);

        $this->db_bechmark_user_id = $user_id;
        $this->settings->set(self::DB_BENCH_USER, (string) $user_id);
    }

    public function disableDbBenchmark(): void
    {
        $this->db_bechmark_enabled = false;
        $this->settings->set(self::ENABLE_DB_BENCH, '0');
        $this->db_bechmark_user_id = null;
        $this->settings->set(self::DB_BENCH_USER, '0');
    }

    /**
     * start measurement
     */
    public function startDbBench(string $a_sql): void
    {
        $this->initSettins();
        if (
            !$this->stop_db_recording
            && $this->isDbBenchEnabled()
            && $this->isUserAvailable()
            && $this->db_bechmark_user_id === $this->user->getId()
        ) {
            $this->start = (string) microtime();
            $this->temporary_sql_storage = $a_sql;
        }
    }

    public function stopDbBench(): bool
    {
        if (
            !$this->stop_db_recording
            && $this->isDbBenchEnabled()
            && $this->isUserAvailable()
            && $this->db_bechmark_user_id === $this->user->getId()
        ) {
            $this->collected_db_benchmarks[] = ["start" => $this->start, "stop" => (string) microtime(), "sql" => $this->temporary_sql_storage];

            return true;
        }

        return false;
    }

    public function getDbBenchRecords(): array
    {
        if ($this->isDBavailable()) {
            $db = $this->retrieveDB();
            if ($db !== null) {
                $set = $db->query("SELECT * FROM benchmark");
                $b = [];
                while ($rec = $db->fetchAssoc($set)) {
                    $b[] = [
                        "sql" => $rec["sql_stmt"],
                        "time" => $rec["duration"]
                    ];
                }
                return $b;
            }
        }
        return [];
    }
}
