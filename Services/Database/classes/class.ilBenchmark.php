<?php

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

/**
 * Class ilBenchmark
 */
class ilBenchmark
{

    private ?ilDBInterface $db = null;
    private ?ilSetting $settings = null;
    private ?ilObjUser $user = null;
    public array $collected_general_benchmarks = [];
    protected array $collected_db_benchmarks = [];
    protected string $start = '';
    protected ?string $temporary_sql_storage = '';
    protected bool $stop_db_recording = false;
    private int $bench_max_records;
    private bool $general_bechmark_enabled = false;
    private bool $db_bechmark_enabled = false;
    protected ?int $db_bechmark_user_id = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;
        $this->bench_max_records = (int) $this->retrieveSetting("bench_max_records") ?? 0;
        $this->general_bechmark_enabled = (bool) $this->retrieveSetting("enable_bench") ?? false;
    }

    private function retrieveSetting(string $identifier) : ?string
    {
        global $DIC;
        if (!$this->settings instanceof ilSetting && $DIC->isDependencyAvailable('settings')) {
            $this->settings = $DIC->settings();
        } elseif ($this->settings instanceof ilSetting) {
            return $this->settings->get("enable_bench");
        }
        return null;
    }

    private function retrieveDB() : ?ilDBInterface
    {
        global $DIC;
        if (!$this->db instanceof ilDBInterface && $DIC->isDependencyAvailable('database')) {
            $this->db = $DIC->database();
        }
        return $this->db;
    }

    private function isDBavailable() : bool
    {
        return !is_null($this->retrieveDB());
    }

    private function retrieveUser() : ?ilObjUser
    {
        global $DIC;
        if (!$this->user instanceof ilObjUser && $DIC->isDependencyAvailable('user')) {
            $this->user = $DIC->user();
        }
        return $this->user;
    }

    private function isUserAvailable() : bool
    {
        return !is_null($this->retrieveUser());
    }

    private function microtimeDiff(string $t1, string $t2) : string
    {
        $t1 = explode(" ", $t1);
        $t2 = explode(" ", $t2);

        return $t2[0] - $t1[0] + $t2[1] - $t1[1];
    }

    /**
     * delete all measurement data
     */
    public function clearData() : void
    {
        if ($this->isDBavailable()) {
            $this->retrieveDB()->manipulate("DELETE FROM benchmark");
        }
    }

    /**
     * start measurement
     *
     * @deprecated
     */
    public function start($a_module, $a_bench) : void
    {
    }

    /**
     * stop measurement
     *
     * @deprecated
     */
    public function stop($a_module, $a_bench) : void
    {
    }

    /**
     * save all measurements
     */
    public function save() : void
    {
        if (!$this->isDBavailable() || !$this->isUserAvailable()) {
            return;
        }
        if ($this->isDbBenchEnabled()
            && $this->db_bechmark_user_id === $this->user->getId()) {
            if (is_array($this->collected_db_benchmarks)) {
                $this->stop_db_recording = true;

                $this->retrieveDB()->manipulate("DELETE FROM benchmark");
                foreach ($this->collected_db_benchmarks as $b) {
                    $id = $this->retrieveDB()->nextId('benchmark');
                    $this->retrieveDB()->insert("benchmark", array(
                        "id" => array("integer", $id),
                        "duration" => array("float", $this->microtimeDiff($b["start"], $b["stop"])),
                        "sql_stmt" => array("clob", $b["sql"])
                    ));
                }
            }
            $this->disableDbBenchmark();
        }
    }

    /**
     * get current number of benchmark records
     */
    private function getCurrentRecordNumber() : int
    {
        if (!$this->isDBavailable()) {
            return 0;
        }
        $cnt_set = $this->retrieveDB()->query("SELECT COUNT(*) AS cnt FROM benchmark");
        $cnt_rec = $this->retrieveDB()->fetchAssoc($cnt_set);

        return (int ) $cnt_rec["cnt"];
    }

    /**
     * get maximum number of benchmark records
     */
    private function getMaximumRecords() : int
    {
        return $this->bench_max_records;
    }

    /**
     * set maximum number of benchmark records
     */
    private function setMaximumRecords(int $a_max) : void
    {
        $this->settings->set("bench_max_records", (string) $a_max);
        $this->bench_max_records = $a_max;
    }

    /**
     * check wether benchmarking is enabled or not
     */
    public function isGeneralbechmarkEnabled() : bool
    {
        return $this->general_bechmark_enabled;
    }

    /**
     * enable benchmarking
     */
    public function enable(bool $a_enable) : void
    {
        $this->general_bechmark_enabled = $a_enable;
        $this->settings->set('enable_bench', $a_enable ? '1' : '0');
    }


    //
    //
    // NEW DB BENCHMARK IMPLEMENTATION
    //
    //

    /**
     * Check wether benchmarking is enabled or not
     */
    public function isDbBenchEnabled()
    {
        return $this->db_bechmark_enabled && $this->isDBavailable();
    }

    public function enableDbBenchmarkForUser(int $a_user) : void
    {
        $this->db_bechmark_enabled = true;
        $this->settings->set("enable_db_bench", '1');
        $this->db_bechmark_user_id = $a_user;
        $this->settings->set("db_bench_user", (string) $a_user);
    }

    public function disableDbBenchmark()
    {
        $this->db_bechmark_enabled = false;
        $this->settings->set("enable_db_bench", '0');
        $this->db_bechmark_user_id = 0;
        $this->settings->set("db_bench_user", '0');
    }

    /**
     * start measurement
     *
     *
     * @return bool|int
     */
    public function startDbBench(string $a_sql)
    {
        if (
            $this->isDbBenchEnabled()
            && $this->isUserAvailable()
            && $this->db_bechmark_user_id === $this->user->getId()
            && !$this->stop_db_recording
        ) {
            $this->start = microtime();
            $this->temporary_sql_storage = $a_sql;
        }
    }

    public function stopDbBench() : bool
    {
        if ($this->isDbBenchEnabled()
                && $this->isUserAvailable()
            && $this->db_bechmark_user_id === $this->user->getId()
            && !$this->stop_db_recording) {
            $this->collected_db_benchmarks[] = array(
                "start" => $this->start,
                "stop" => microtime(),
                "sql" => $this->temporary_sql_storage,
            );

            return true;
        }

        return false;
    }

    public function getDbBenchRecords() : array
    {
        if (!$this->isDBavailable()) {
            return [];
        }
        $set = $this->retrieveDB()->query("SELECT * FROM benchmark");
        $b = [];
        while ($rec = $this->retrieveDB()->fetchAssoc($set)) {
            $b[] = [
                "sql" => $rec["sql_stmt"],
                "time" => $rec["duration"]
            ];
        }
        return $b;
    }
}
