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
 * Database Update class
 * @author  Peter Gabriel <pgabriel@databay.de>
 * @author  Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesDatabase
 */
class ilDBUpdate
{
    public string $DB_UPDATE_FILE;
    public ?int $currentVersion = null;
    public ?int $fileVersion = null;
    public string $updateMsg;
    protected ?ilIniFile $client_ini = null;
    protected ?int $custom_updates_current_version = 0;
    protected ?int $custom_updates_file_version = null;
    protected ?bool $custom_updates_info_read = null;
    protected string $error;
    protected string $PATH = './';
    protected ilDBInterface $db;
    protected string $current_file;
    protected string $LAST_UPDATE_FILE;
    protected array $filecontent;
    protected array $lastfilecontent;
    protected int $db_update_running;
    protected int $hotfix_current_version;
    protected ilSetting $hotfix_setting;
    protected array $hotfix_version;
    protected array $hotfix_content;
    protected int $hotfix_file_version;
    protected ilSetting $custom_updates_setting;
    protected array $custom_updates_content;
    protected Iterator $ctrl_structure_iterator;

    public function __construct(ilDBInterface $a_db_handler, ilIniFile $client_ini = null)
    {
        // workaround to allow setup migration
        $this->client_ini = $client_ini;
        $this->db = $a_db_handler;
        $this->PATH = "./";

        $this->getCurrentVersion();

        // get update file for current version
        $updatefile = $this->getFileForStep($this->currentVersion + 1);

        $this->current_file = $updatefile;
        $this->DB_UPDATE_FILE = $this->PATH . "setup/sql/" . $updatefile;

        //
        // NOTE: IF YOU SET THIS TO THE NEWEST FILE, CHANGE ALSO getFileForStep()
        //
        $this->LAST_UPDATE_FILE = $this->PATH . "setup/sql/dbupdate_05.php";

        $this->readDBUpdateFile();
        $this->readLastUpdateFile();
        $this->readFileVersion();

        $class_map = require ILIAS_ABSOLUTE_PATH . '/libs/composer/vendor/composer/autoload_classmap.php';
        $this->ctrl_structure_iterator = new ilCtrlArrayIterator($class_map);
    }

    /**
     * Get db update file name for db step
     */
    public function getFileForStep(int $a_version): string
    {
        //
        // NOTE: IF YOU ADD A NEW FILE HERE, CHANGE ALSO THE CONSTRUCTOR
        //
        switch (true) {
            case ($a_version > 5431): // last number in previous file
                return "dbupdate_05.php";
            case ($a_version > 4182): // last number in previous file
                return "dbupdate_04.php";
            case ($a_version > 2948): // last number in previous file
                return "dbupdate_03.php";
            case ($a_version > 864): // last number in previous file
                return "dbupdate_02.php";
            default:
                return "dbupdate.php";
        }
    }

    public function initStep(int $i): void
    {
        //
    }

    public function readDBUpdateFile(): bool
    {
        if (!file_exists($this->DB_UPDATE_FILE)) {
            $this->error = "no_db_update_file";
            $this->filecontent = array();

            return false;
        }

        $this->filecontent = @file($this->DB_UPDATE_FILE);

        return true;
    }

    public function readLastUpdateFile(): bool
    {
        if (!file_exists($this->LAST_UPDATE_FILE)) {
            $this->error = "no_last_update_file";
            $this->lastfilecontent = array();

            return false;
        }

        $this->lastfilecontent = @file($this->LAST_UPDATE_FILE);

        return true;
    }

    public function getCurrentVersion(): int
    {
        $set = new ilSetting("common", true);
        $this->currentVersion = (int) $set->get("db_version");

        return $this->currentVersion;
    }

    public function setCurrentVersion(int $a_version): void
    {
        $set = new ilSetting("common", true);
        $set->set("db_version", (string) $a_version);
        $this->currentVersion = $a_version;
    }

    /**
     * Set running status for a step
     * @param int        step number
     */
    public function setRunningStatus(int $a_nr): void
    {
        $set = new ilSetting("common", true);
        $set->set("db_update_running", (string) $a_nr);
        $this->db_update_running = $a_nr;
    }

    /**
     * Get running status
     * @return    int        current runnning db step
     */
    public function getRunningStatus(): int
    {
        $set = new ilSetting("common", true);
        $this->db_update_running = (int) $set->get("db_update_running");

        return $this->db_update_running;
    }

    /**
     * Clear running status
     */
    public function clearRunningStatus(): void
    {
        $set = new ilSetting("common", true);
        $set->set("db_update_running", "0");
        $this->db_update_running = 0;
    }

    public function readFileVersion(): int
    {
        //go through filecontent and search for last occurence of <#x>
        reset($this->lastfilecontent);
        $regs = array();
        $version = 0;
        foreach ($this->lastfilecontent as $row) {
            if (preg_match('/^\<\#([0-9]+)>/', $row, $regs)) {
                $version = $regs[1];
            }
        }

        $this->fileVersion = (int) $version;

        return $this->fileVersion;
    }

    /**
     * Get Version of file
     */
    public function getFileVersion(): ?int
    {
        return $this->fileVersion;
    }

    /**
     * execute a query
     * @return bool
     */
    public function execQuery(ilDBInterface $db, string $str): bool
    {
        $q = "";
        $sql = explode("\n", trim($str));
        foreach ($sql as $i => $statement) {
            $sql[$i] = trim($statement);
            if ($statement !== "" && $statement[0] !== "#") {
                //take line per line, until last char is ";"
                if (substr($statement, -1) === ";") {
                    //query is complete
                    /** @noinspection PhpUndefinedVariableInspection */
                    $q .= " " . substr($statement, 0, -1);
                    $check = $this->checkQuery($q);
                    if ($check === true) {
                        $db->query($q);
                    } else {
                        $this->error = (string) $check;
                        return false;
                    }
                    unset($q);
                } //if
                else {
                    /** @noinspection PhpUndefinedVariableInspection */
                    $q .= " " . $statement;
                } //else
            } //if
        } //for
        if (isset($q) && $q !== "") {
            echo "incomplete_statement: " . $q . "<br>";

            return false;
        }

        return true;
    }

    /**
     * check query
     */
    public function checkQuery(string $q): bool
    {
        return true;
    }

    private function initGlobalsRequiredForUpdateSteps(
        ?ilCtrlStructureReader &$ilCtrlStructureReader,
        ?ilDBInterface &$ilDB
    ): void {
        global $DIC;

        // TODO: There is currently a huge mixup of globals, $DIC and dependencies, esprecially in setup and during DB-Updates. This leads to many problems. The following core tries to provide the needed dependencies for the dbupdate-script. The code hopefully will change in the future.

        if (isset($GLOBALS['ilCtrlStructureReader'])) {
            $ilCtrlStructureReader = $GLOBALS['ilCtrlStructureReader'];
        } elseif ($DIC->offsetExists('ilCtrlStructureReader')) {
            $ilCtrlStructureReader = $DIC['ilCtrlStructureReader'];
        } else {
            $ilCtrlStructureReader = new ilCtrlStructureReader(
                $this->ctrl_structure_iterator,
                new ilCtrlStructureCidGenerator()
            );
            $DIC->offsetSet('ilCtrlStructureReader', $ilCtrlStructureReader);
        }

        $GLOBALS['ilCtrlStructureReader'] = $ilCtrlStructureReader;

        if ($this->client_ini) {
            $ilCtrlStructureReader->setIniFile($this->client_ini);
        }
        $ilDB = $DIC->database();
    }

    /**
     * Apply update
     * @return bool|void
     */
    public function applyUpdate(int $a_break = 0)
    {
        $ilCtrlStructureReader = null;
        $ilDB = null;
        $this->initGlobalsRequiredForUpdateSteps($ilCtrlStructureReader, $ilDB);

        $f = $this->fileVersion;
        $c = $this->currentVersion;

        if ($a_break > $this->currentVersion
            && $a_break < $this->fileVersion
        ) {
            $f = $a_break;
        }

        if ($c < $f) {
            $msg = array();
            for ($i = ($c + 1); $i <= $f; $i++) {
                // check wether next update file must be loaded
                if ($this->current_file != $this->getFileForStep($i)) {
                    $this->DB_UPDATE_FILE = $this->PATH . "setup/sql/" . $this->getFileForStep($i);
                    $this->readDBUpdateFile();
                }

                $this->initStep($i);

                if ($this->applyUpdateNr($i) === false) {
                    $msg[] = "msg: update_error - " . $this->error . "; nr: " . $i . ";";
                    $this->updateMsg = implode("\n", $msg);

                    return false;
                }

                $msg[] = "msg: update_applied; nr: " . $i . ";";
            }

            $this->updateMsg = implode("\n", $msg);
        } else {
            $this->updateMsg = "no_changes";
        }

        if ($f < $this->fileVersion) {
            return true;
        }
    }

    /**
     * apply an update
     * @param int $nr number what patch to apply (Reference: Patch for https://mantis.ilias.de/view.php?id=28550)
     * @access private
     */
    public function applyUpdateNr(int $nr, $hotfix = false, $custom_update = false): bool
    {
        $ilCtrlStructureReader = null;
        $ilMySQLAbstraction = null;
        $ilDB = null;
        $this->initGlobalsRequiredForUpdateSteps($ilCtrlStructureReader, $ilDB);

        //search for desired $nr
        reset($this->filecontent);

        if (!$hotfix && !$custom_update) {
            $this->setRunningStatus($nr);
        }

        //init
        $i = 0;

        //go through filecontent
        while (!preg_match("/^\<\#" . $nr . ">/", $this->filecontent[$i]) && $i < count($this->filecontent)) {
            $i++;
        }

        //update not found
        if ($i === count($this->filecontent)) {
            $this->error = "update_not_found";

            return false;
        }

        $i++;

        //update found, now extract this update to a new array
        $update = array();
        while ($i < count($this->filecontent) && !preg_match("/^<#" . ($nr + 1) . ">/", $this->filecontent[$i])) {
            $update[] = trim($this->filecontent[$i]);
            $i++;
        }

        //now you have the update, now process it
        $sql = array();
        $php = array();
        $mode = "sql";

        foreach ($update as $row) {
            if (preg_match("/<\?php/", $row)) {
                if (count($sql) > 0) {
                    if ($this->execQuery($this->db, implode("\n", $sql)) === false) {
                        return false;
                    }
                    $sql = array();
                }
                $mode = "php";
            } elseif (preg_match("/\?>/", $row)) {
                if (count($php) > 0) {
                    $code = implode("\n", $php);
                    if (eval($code) === false) {
                        $this->error = "Parse error: " . $code;

                        return false;
                    }
                    $php = array();
                }
                $mode = "sql";
            } else {
                if ($mode === "sql") {
                    $sql[] = $row;
                }

                if ($mode === "php") {
                    $php[] = $row;
                }
            } //else
        } //foreach

        if ($mode === "sql" && count($sql) > 0) {
            if ($this->execQuery($this->db, implode("\n", $sql)) === false) {
                $this->error = "dump_error: " . $this->error;

                return false;
            }
        }

        //increase db_Version number
        if (!$hotfix && !$custom_update) {
            $this->setCurrentVersion($nr);
        } elseif ($hotfix) {
            $this->setHotfixCurrentVersion($nr);
        } elseif ($custom_update) {
            $this->setCustomUpdatesCurrentVersion($nr);
        }

        if (!$hotfix && !$custom_update) {
            $this->clearRunningStatus();
        }

        //$this->currentVersion = $ilias->getSetting("db_version");

        return true;
    }

    public function getDBVersionStatus(): bool
    {
        return !($this->fileVersion > $this->currentVersion);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getTables(): array
    {
        $a = array();

        $query = "SHOW TABLES";
        $res = $this->db->query($query);
        while ($row = $res->fetchRow()) {
            $status = $this->getTableStatus($row[0]);
            $a[] = array("name" => $status["Table"],
                         "table" => $row[0],
                         "status" => $status["Msg_text"],
            );
        }

        return $a;
    }

    /**
     * @return mixed
     */
    public function getTableStatus(string $table)
    {
        $query = "ANALYZE TABLE " . $table;
        return $this->db->query($query)->fetchRow(ilDBConstants::FETCHMODE_ASSOC);
    }


    ////
    //// Hotfix handling
    ////
    /**
     * Get current hotfix version
     */
    public function getHotfixCurrentVersion(): ?int
    {
        $this->readHotfixInfo();

        return $this->hotfix_current_version ?? null;
    }

    /**
     * Set current hotfix version
     */
    public function setHotfixCurrentVersion(int $a_version): bool
    {
        $this->readHotfixInfo();
        $this->hotfix_setting->set(
            "db_hotfixes_" . $this->hotfix_version[0],
            (string) $a_version
        );
        $this->hotfix_current_version = $a_version;

        return true;
    }

    /**
     * Get current hotfix version
     */
    public function getHotfixFileVersion(): ?int
    {
        $this->readHotfixInfo();

        return $this->hotfix_file_version ?? null;
    }

    /**
     * Set current hotfix version
     */
    public function readHotfixFileVersion(array $a_file_content): int
    {
        //go through filecontent and search for last occurence of <#x>
        reset($a_file_content);
        $regs = [];
        $version = '';
        foreach ($a_file_content as $row) {
            if (preg_match("/^<#([0-9]+)>/", $row, $regs)) {
                $version = $regs[1];
            }
        }

        return (int) $version;
    }

    /**
     * Get status of hotfix file
     */
    public function readHotfixInfo(bool $a_force = false): void
    {
        if (isset($this->hotfix_info_read) && $this->hotfix_info_read && !$a_force) {
            return;
        }
        $this->hotfix_setting = new ilSetting("common", true);
        $ilias_version = ILIAS_VERSION_NUMERIC;
        $version_array = explode(".", $ilias_version);
        $this->hotfix_version[0] = $version_array[0];
        $this->hotfix_version[1] = $version_array[1];
        $hotfix_file = $this->PATH . "setup/sql/" . $this->hotfix_version[0] . "_hotfixes.php";
        if (is_file($hotfix_file)) {
            $this->hotfix_content = @file($hotfix_file);
            $this->hotfix_current_version = (int) $this->hotfix_setting->get(
                "db_hotfixes_" . $this->hotfix_version[0]
            );
            $this->hotfix_file_version = $this->readHotfixFileVersion($this->hotfix_content);
        }
        $this->hotfix_info_read = true;
    }

    /**
     * Get status of hotfix file
     */
    public function hotfixAvailable(): bool
    {
        $this->readHotfixInfo();
        return isset($this->hotfix_file_version) && $this->hotfix_file_version > $this->hotfix_current_version;
    }

    /**
     * Apply hotfix
     */
    public function applyHotfix(): bool
    {
        $ilCtrlStructureReader = null;
        $ilDB = null;
        $this->initGlobalsRequiredForUpdateSteps($ilCtrlStructureReader, $ilDB);
        $this->readHotfixInfo(true);

        $f = $this->getHotfixFileVersion();
        $c = $this->getHotfixCurrentVersion();

        if ($c < $f) {
            $msg = array();
            for ($i = ($c + 1); $i <= $f; $i++) {
                $this->filecontent = $this->hotfix_content;

                if ($this->applyUpdateNr($i, true) === false) {
                    $msg[] = array("msg" => "update_error: " . $this->error,
                                   "nr" => $i,
                    );
                    $this->updateMsg = implode("\n", $msg);

                    return false;
                }

                $msg[] = array("msg" => "hotfix_applied",
                               "nr" => $i,
                );
            }

            $this->updateMsg = implode("\n", $msg);
        } else {
            $this->updateMsg = "no_changes";
        }

        return true;
    }

    public function getCustomUpdatesCurrentVersion(): ?int
    {
        $this->readCustomUpdatesInfo();

        return $this->custom_updates_current_version;
    }

    public function setCustomUpdatesCurrentVersion(?int $a_version): bool
    {
        $this->readCustomUpdatesInfo();
        $this->custom_updates_setting->set('db_version_custom', (string) $a_version);
        $this->custom_updates_current_version = $a_version;

        return true;
    }

    public function getCustomUpdatesFileVersion(): ?int
    {
        $this->readCustomUpdatesInfo();

        return $this->custom_updates_file_version;
    }

    public function readCustomUpdatesFileVersion(array $a_file_content): int
    {
        //go through filecontent and search for last occurence of <#x>
        reset($a_file_content);
        $regs = [];
        $version = '';
        foreach ($a_file_content as $row) {
            if (preg_match("/^<#([0-9]+)>/", $row, $regs)) {
                $version = $regs[1];
            }
        }

        return (int) $version;
    }

    public function readCustomUpdatesInfo(bool $a_force = false): void
    {
        if ($this->custom_updates_info_read && !$a_force) {
            return;
        }

        $this->custom_updates_setting = new ilSetting();
        $custom_updates_file = $this->PATH . "setup/sql/dbupdate_custom.php";
        if (is_file($custom_updates_file)) {
            $this->custom_updates_content = @file($custom_updates_file);
            $this->custom_updates_current_version = (int) $this->custom_updates_setting->get('db_version_custom', "0");
            $this->custom_updates_file_version = $this->readCustomUpdatesFileVersion($this->custom_updates_content);
        }
        $this->custom_updates_info_read = true;
    }

    public function customUpdatesAvailable(): bool
    {
        $this->readCustomUpdatesInfo();
        return $this->custom_updates_file_version > $this->custom_updates_current_version;
    }

    public function applyCustomUpdates(): bool
    {
        $ilCtrlStructureReader = null;
        $ilDB = null;
        $this->initGlobalsRequiredForUpdateSteps($ilCtrlStructureReader, $ilDB);
        $this->readCustomUpdatesInfo(true);

        $f = $this->getCustomUpdatesFileVersion();
        $c = $this->getCustomUpdatesCurrentVersion();

        if ($c < $f) {
            $msg = array();
            for ($i = ($c + 1); $i <= $f; $i++) {
                $this->filecontent = $this->custom_updates_content;

                if ($this->applyUpdateNr($i, false, true) === false) {
                    $msg[] = array("msg" => "update_error: " . $this->error,
                                   "nr" => $i,
                    );
                    $this->updateMsg = implode("\n", $msg);

                    return false;
                }

                $msg[] = array("msg" => "custom_update_applied",
                               "nr" => $i,
                );
            }

            $this->updateMsg = implode("\n", $msg);
        } else {
            $this->updateMsg = "no_changes";
        }

        return true;
    }

    /**
     * Get update steps as string (for presentation)
     * @return string steps from the update file
     */
    public function getUpdateSteps(int $a_break = 0): string
    {
        $ilCtrlStructureReader = null;
        $ilMySQLAbstraction = null;
        $ilDB = null;
        $this->initGlobalsRequiredForUpdateSteps($ilCtrlStructureReader, $ilDB);

        $str = "";

        $f = $this->fileVersion;
        $c = $this->currentVersion;

        if ($a_break > $this->currentVersion
            && $a_break < $this->fileVersion
        ) {
            $f = $a_break;
        }

        if ($c < $f) {
            for ($i = ($c + 1); $i <= $f; $i++) {
                // check wether next update file must be loaded
                if ($this->current_file != $this->getFileForStep($i)) {
                    $this->DB_UPDATE_FILE = $this->PATH . "setup/sql/" . $this->getFileForStep($i);
                    $this->readDBUpdateFile();
                }

                $str .= $this->getUpdateStepNr($i);
            }
        }

        return $str;
    }

    /**
     * Get hotfix steps
     * @return string steps from the update file
     */
    public function getHotfixSteps(): string
    {
        $this->readHotfixInfo(true);

        $str = "";

        $f = $this->getHotfixFileVersion();
        $c = $this->getHotfixCurrentVersion();

        if ($c < $f) {
            for ($i = ($c + 1); $i <= $f; $i++) {
                $this->filecontent = $this->hotfix_content;

                $str .= $this->getUpdateStepNr($i, true);
            }
        }

        return $str;
    }

    /**
     * Get single update step for presentation
     */
    public function getUpdateStepNr(int $nr, bool $hotfix = false, bool $custom_update = false): string
    {
        $str = "";

        //search for desired $nr
        reset($this->filecontent);

        //init
        $i = 0;

        //go through filecontent
        while (!preg_match("/^<#" . $nr . ">/", $this->filecontent[$i]) && $i < count($this->filecontent)) {
            $i++;
        }

        //update not found
        if ($i === count($this->filecontent)) {
            return '';
        }

        $i++;
        while ($i < count($this->filecontent) && !preg_match("/^<#" . ($nr + 1) . ">/", $this->filecontent[$i])) {
            $str .= $this->filecontent[$i];
            $i++;
        }

        return "<pre><b><#" . $nr . "></b>\n" . htmlentities($str) . "</pre>";
    }
}
