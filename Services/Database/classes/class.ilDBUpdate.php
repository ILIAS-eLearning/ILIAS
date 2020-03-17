<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Database Update class
 *
 * @author  Peter Gabriel <pgabriel@databay.de>
 * @author  Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesDatabase
 */
class ilDBUpdate
{

    /**
     * db update file
     */
    public $DB_UPDATE_FILE;
    /**
     * current version of db
     *
     * @var    integer    db version number
     */
    public $currentVersion;
    /**
     * current version of file
     *
     * @var    integer    fiel version number
     */
    public $fileVersion;
    /**
     * @var string
     */
    public $updateMsg;


    /**
     * ilDBUpdate constructor.
     *
     * @param ilDBInterface $a_db_handler
     * @param bool          $tmp_flag
     */
    public function __construct($a_db_handler = 0, $client_ini = null)
    {
        // workaround to allow setup migration
        $this->client_ini = $client_ini;
        if ($a_db_handler) {
            $this->db = &$a_db_handler;
            $this->PATH = "./";
        } else {
            global $DIC;
            if ($DIC->offsetExists('mySetup')) {
                $mySetup = $DIC['mySetup'];
            }
            $this->db = $mySetup->db;
            $this->PATH = "./";
        }

        $this->getCurrentVersion();

        // get update file for current version
        $updatefile = $this->getFileForStep($this->currentVersion + 1);

        $this->current_file = $updatefile;
        $this->DB_UPDATE_FILE = $this->PATH . "setup/sql/" . $updatefile;

        //
        // NOTE: IF YOU SET THIS TO THE NEWEST FILE, CHANGE ALSO getFileForStep()
        //
        $this->LAST_UPDATE_FILE = $this->PATH . "setup/sql/dbupdate_04.php";

        $this->readDBUpdateFile();
        $this->readLastUpdateFile();
        $this->readFileVersion();
    }


    /**
     * Get db update file name for db step
     *
     * @param int $a_version
     *
     * @return string
     */
    public function getFileForStep($a_version)
    {
        //
        // NOTE: IF YOU ADD A NEW FILE HERE, CHANGE ALSO THE CONSTRUCTOR
        //
        switch (true) {
            case ((int) $a_version > 4182): // last number in previous file
                return "dbupdate_04.php";
            case ((int) $a_version > 2948): // last number in previous file
                return "dbupdate_03.php";
            case ((int) $a_version > 864): // last number in previous file
                return "dbupdate_02.php";
            default:
                return "dbupdate.php";
        }
    }


    /**
     * @param int $i
     */
    public function initStep($i)
    {
        //
    }


    public function readDBUpdateFile()
    {
        if (!file_exists($this->DB_UPDATE_FILE)) {
            $this->error = "no_db_update_file";
            $this->filecontent = array();

            return false;
        }

        $this->filecontent = @file($this->DB_UPDATE_FILE);

        return true;
    }


    public function readLastUpdateFile()
    {
        if (!file_exists($this->LAST_UPDATE_FILE)) {
            $this->error = "no_last_update_file";
            $this->lastfilecontent = array();

            return false;
        }

        $this->lastfilecontent = @file($this->LAST_UPDATE_FILE);

        return true;
    }


    /**
     * @return int
     */
    public function getCurrentVersion()
    {
        include_once './Services/Administration/classes/class.ilSetting.php';
        $set = new ilSetting("common", true);
        $this->currentVersion = (integer) $set->get("db_version");

        return $this->currentVersion;
    }


    /**
     * @param int $a_version
     *
     * @return bool
     */
    public function setCurrentVersion($a_version)
    {
        include_once './Services/Administration/classes/class.ilSetting.php';
        $set = new ilSetting("common", true);
        $set->set("db_version", $a_version);
        $this->currentVersion = $a_version;

        return true;
    }


    /**
     * Set running status for a step
     *
     * @param    int        step number
     */
    public function setRunningStatus($a_nr)
    {
        include_once './Services/Administration/classes/class.ilSetting.php';
        $set = new ilSetting("common", true);
        $set->set("db_update_running", $a_nr);
        $this->db_update_running = $a_nr;
    }


    /**
     * Get running status
     *
     * @return    int        current runnning db step
     */
    public function getRunningStatus()
    {
        include_once './Services/Administration/classes/class.ilSetting.php';
        $set = new ilSetting("common", true);
        $this->db_update_running = (integer) $set->get("db_update_running");

        return $this->db_update_running;
    }


    /**
     * Clear running status
     */
    public function clearRunningStatus()
    {
        include_once './Services/Administration/classes/class.ilSetting.php';
        $set = new ilSetting("common", true);
        $set->set("db_update_running", 0);
        $this->db_update_running = 0;
    }


    public function readFileVersion()
    {
        //go through filecontent and search for last occurence of <#x>
        reset($this->lastfilecontent);
        $regs = array();
        foreach ($this->lastfilecontent as $row) {
            if (preg_match('/^\<\#([0-9]+)>/', $row, $regs)) {
                $version = $regs[1];
            }
        }

        $this->fileVersion = (integer) $version;

        return $this->fileVersion;
    }


    /**
     * Get Version of file
     */
    public function getFileVersion()
    {
        return $this->fileVersion;
    }


    /**
     * execute a query
     *
     * @param    object    DB
     * @param    string    query
     *
     * @return    boolean
     */
    public function execQuery($db, $str)
    {
        $sql = explode("\n", trim($str));
        for ($i = 0; $i < count($sql); $i++) {
            $sql[$i] = trim($sql[$i]);
            if ($sql[$i] != "" && substr($sql[$i], 0, 1) != "#") {
                //take line per line, until last char is ";"
                if (substr($sql[$i], -1) == ";") {
                    //query is complete
                    $q .= " " . substr($sql[$i], 0, -1);
                    $check = $this->checkQuery($q);
                    if ($check === true) {
                        try {
                            $r = $db->query($q);
                        } catch (ilDatabaseException $e) {
                            var_dump($e); // FSX
                            exit;
                            $this->error = $e->getMessage();

                            return false;
                        }
                    } else {
                        $this->error = $check;

                        return false;
                    }
                    unset($q);
                } //if
                else {
                    $q .= " " . $sql[$i];
                } //else
            } //if
        } //for
        if ($q != "") {
            echo "incomplete_statement: " . $q . "<br>";

            return false;
        }

        return true;
    }


    /**
     * check query
     */
    public function checkQuery($q)
    {
        return true;
    }


    /**
     * @param $ilCtrlStructureReader
     * @param $ilMySQLAbstraction
     * @param $ilDB
     */
    private function initGlobalsRequiredForUpdateSteps(&$ilCtrlStructureReader, &$ilMySQLAbstraction, &$ilDB)
    {
        global $DIC;

        // TODO: There is currently a huge mixup of globals, $DIC and dependencies, esprecially in setup and during DB-Updates. This leads to many problems. The following core tries to provide the needed dependencies for the dbupdate-script. The code hopefully will change in the future.

        if (isset($GLOBALS['ilCtrlStructureReader'])) {
            $ilCtrlStructureReader = $GLOBALS['ilCtrlStructureReader'];
        } elseif ($DIC->offsetExists('ilCtrlStructureReader')) {
            $ilCtrlStructureReader = $DIC['ilCtrlStructureReader'];
        } else {
            require_once 'setup/classes/class.ilCtrlStructureReader.php';
            $ilCtrlStructureReader = new ilCtrlStructureReader();
            $DIC->offsetSet('ilCtrlStructureReader', $ilCtrlStructureReader);
        }

        $GLOBALS['ilCtrlStructureReader'] = $ilCtrlStructureReader;

        if ($DIC->offsetExists('ilMySQLAbstraction')) {
            $ilMySQLAbstraction = $DIC['ilMySQLAbstraction'];
        } else {
            $ilMySQLAbstraction = new ilMySQLAbstraction();
            $DIC->offsetSet('ilMySQLAbstraction', $ilMySQLAbstraction);
        }

        $GLOBALS['ilMySQLAbstraction'] = $ilMySQLAbstraction;

        if ($this->client_ini) {
            $ilCtrlStructureReader->setIniFile($this->client_ini);
        }
        $ilDB = $DIC->database();
    }


    /**
     * Apply update
     */
    public function applyUpdate($a_break = 0)
    {
        $ilCtrlStructureReader = null;
        $ilMySQLAbstraction = null;
        $ilDB = null;
        $this->initGlobalsRequiredForUpdateSteps($ilCtrlStructureReader, $ilMySQLAbstraction, $ilDB);

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

                if ($this->applyUpdateNr($i, $inifile) == false) {
                    $msg[] = array("msg" => "update_error: " . $this->error,
                                   "nr" => $i,);
                    $this->updateMsg = $msg;

                    return false;
                } else {
                    $msg[] = array("msg" => "update_applied",
                                   "nr" => $i,);
                }
            }

            $this->updateMsg = $msg;
        } else {
            $this->updateMsg = "no_changes";
        }

        if ($f < $this->fileVersion) {
            return true;
        } else {
            return $this->loadXMLInfo();
        }
    }


    public function loadXMLInfo()
    {
        $ilCtrlStructureReader = null;
        $ilMySQLAbstraction = null;
        $ilDB = null;
        $this->initGlobalsRequiredForUpdateSteps($ilCtrlStructureReader, $ilMySQLAbstraction, $ilDB);

        // read module and service information into db
        require_once "./setup/classes/class.ilModuleReader.php";
        require_once "./setup/classes/class.ilServiceReader.php";
        require_once "./setup/classes/class.ilCtrlStructureReader.php";

        require_once "./Services/Component/classes/class.ilModule.php";
        require_once "./Services/Component/classes/class.ilService.php";
        $modules = ilModule::getAvailableCoreModules();
        $services = ilService::getAvailableCoreServices();

        $ilCtrlStructureReader->readStructure();

        $mr = new ilModuleReader("", "", "");
        $mr->clearTables();
        foreach ($modules as $module) {
            $mr = new ilModuleReader(
                ILIAS_ABSOLUTE_PATH . "/Modules/" . $module["subdir"] . "/module.xml",
                $module["subdir"],
                "Modules"
            );
            $mr->getModules();
            unset($mr);
        }

        $sr = new ilServiceReader("", "", "");
        $sr->clearTables();
        foreach ($services as $service) {
            $sr = new ilServiceReader(
                ILIAS_ABSOLUTE_PATH . "/Services/" . $service["subdir"] . "/service.xml",
                $service["subdir"],
                "Services"
            );
            $sr->getServices();
            unset($sr);
        }



        return true;
    }


    /**
     * apply an update
     *
     * @param int nr number what patch to apply
     *
     * @return bool
     * @access private
     */
    public function applyUpdateNr($nr, $hotfix = false, $custom_update = false)
    {
        $ilCtrlStructureReader = null;
        $ilMySQLAbstraction = null;
        $ilDB = null;
        $this->initGlobalsRequiredForUpdateSteps($ilCtrlStructureReader, $ilMySQLAbstraction, $ilDB);

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
        if ($i == count($this->filecontent)) {
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
                    if ($this->execQuery($this->db, implode("\n", $sql)) == false) {
                        $this->error = $this->error;

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
                if ($mode == "sql") {
                    $sql[] = $row;
                }

                if ($mode == "php") {
                    $php[] = $row;
                }
            } //else
        } //foreach

        if ($mode == "sql" && count($sql) > 0) {
            if ($this->execQuery($this->db, implode("\n", $sql)) == false) {
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


    public function getDBVersionStatus()
    {
        if ($this->fileVersion > $this->currentVersion) {
            return false;
        } else {
            return true;
        }
    }


    public function getTables()
    {
        $a = array();

        $query = "SHOW TABLES";
        $res = $this->db->query($query);
        while ($row = $res->fetchRow()) {
            $status = $this->getTableStatus($row[0]);
            $a[] = array("name" => $status["Table"],
                         "table" => $row[0],
                         "status" => $status["Msg_text"],);
        }

        return $a;
    }


    public function getTableStatus($table)
    {
        $a = array();

        $query = "ANALYZE TABLE " . $table;
        $res = $this->db->query($query);
        $row = $res->fetchRow(ilDBConstants::FETCHMODE_ASSOC);

        return $row;
    }


    ////
    //// Hotfix handling
    ////

    /**
     * Get current hotfix version
     */
    public function getHotfixCurrentVersion()
    {
        $this->readHotfixInfo();

        return $this->hotfix_current_version;
    }


    /**
     * Set current hotfix version
     */
    public function setHotfixCurrentVersion($a_version)
    {
        $this->readHotfixInfo();
        $this->hotfix_setting->set(
            "db_hotfixes_" . $this->hotfix_version[0] . "_" . $this->hotfix_version[1],
            $a_version
        );
        $this->hotfix_current_version = $a_version;

        return true;
    }


    /**
     * Get current hotfix version
     */
    public function getHotfixFileVersion()
    {
        $this->readHotfixInfo();

        return $this->hotfix_file_version;
    }


    /**
     * Set current hotfix version
     */
    public function readHotfixFileVersion($a_file_content)
    {
        //go through filecontent and search for last occurence of <#x>
        reset($a_file_content);
        $regs = array();
        foreach ($a_file_content as $row) {
            if (preg_match("/^<#([0-9]+)>/", $row, $regs)) {
                $version = $regs[1];
            }
        }

        return (integer) $version;
    }


    /**
     * Get status of hotfix file
     */
    public function readHotfixInfo($a_force = false)
    {
        if ($this->hotfix_info_read && !$a_force) {
            return;
        }
        include_once './Services/Administration/classes/class.ilSetting.php';
        $this->hotfix_setting = new ilSetting("common", true);
        $ilias_version = ILIAS_VERSION_NUMERIC;
        $version_array = explode(".", $ilias_version);
        $this->hotfix_version[0] = $version_array[0];
        $this->hotfix_version[1] = $version_array[1];
        $hotfix_file = $this->PATH . "setup/sql/" . $this->hotfix_version[0] . "_" . $this->hotfix_version[1] . "_hotfixes.php";
        if (is_file($hotfix_file)) {
            $this->hotfix_content = @file($hotfix_file);
            $this->hotfix_current_version = (int) $this->hotfix_setting->get(
                "db_hotfixes_" . $this->hotfix_version[0] . "_" . $this->hotfix_version[1]
            );
            $this->hotfix_file_version = $this->readHotfixFileVersion($this->hotfix_content);
        }
        $this->hotfix_info_read = true;
    }


    /**
     * Get status of hotfix file
     */
    public function hotfixAvailable()
    {
        $this->readHotfixInfo();
        if ($this->hotfix_file_version > $this->hotfix_current_version) {
            return true;
        }

        return false;
    }


    /**
     * Apply hotfix
     */
    public function applyHotfix()
    {
        $ilCtrlStructureReader = null;
        $ilMySQLAbstraction = null;
        $ilDB = null;
        $this->initGlobalsRequiredForUpdateSteps($ilCtrlStructureReader, $ilMySQLAbstraction, $ilDB);

        include_once './Services/Database/classes/class.ilMySQLAbstraction.php';

        $ilMySQLAbstraction = new ilMySQLAbstraction();
        $GLOBALS['DIC']['ilMySQLAbstraction'] = $ilMySQLAbstraction;

        $this->readHotfixInfo(true);

        $f = $this->getHotfixFileVersion();
        $c = $this->getHotfixCurrentVersion();

        if ($c < $f) {
            $msg = array();
            for ($i = ($c + 1); $i <= $f; $i++) {
                //				$this->initStep($i);	// nothings happens here

                $this->filecontent = $this->hotfix_content;

                if ($this->applyUpdateNr($i, true) == false) {
                    $msg[] = array("msg" => "update_error: " . $this->error,
                                   "nr" => $i,);
                    $this->updateMsg = $msg;

                    return false;
                } else {
                    $msg[] = array("msg" => "hotfix_applied",
                                   "nr" => $i,);
                }
            }

            $this->updateMsg = $msg;
        } else {
            $this->updateMsg = "no_changes";
        }

        return $this->loadXMLInfo();
    }


    public function getCustomUpdatesCurrentVersion()
    {
        $this->readCustomUpdatesInfo();

        return $this->custom_updates_current_version;
    }


    public function setCustomUpdatesCurrentVersion($a_version)
    {
        $this->readCustomUpdatesInfo();
        $this->custom_updates_setting->set('db_version_custom', $a_version);
        $this->custom_updates_current_version = $a_version;

        return true;
    }


    public function getCustomUpdatesFileVersion()
    {
        $this->readCustomUpdatesInfo();

        return $this->custom_updates_file_version;
    }


    public function readCustomUpdatesFileVersion($a_file_content)
    {
        //go through filecontent and search for last occurence of <#x>
        reset($a_file_content);
        $regs = array();
        foreach ($a_file_content as $row) {
            if (preg_match("/^<#([0-9]+)>/", $row, $regs)) {
                $version = $regs[1];
            }
        }

        return (integer) $version;
    }


    public function readCustomUpdatesInfo($a_force = false)
    {
        if ($this->custom_updates_info_read && !$a_force) {
            return;
        }
        include_once './Services/Administration/classes/class.ilSetting.php';

        $this->custom_updates_setting = new ilSetting();
        $custom_updates_file = $this->PATH . "setup/sql/dbupdate_custom.php";
        if (is_file($custom_updates_file)) {
            $this->custom_updates_content = @file($custom_updates_file);
            $this->custom_updates_current_version = (int) $this->custom_updates_setting->get('db_version_custom', 0);
            $this->custom_updates_file_version = $this->readCustomUpdatesFileVersion($this->custom_updates_content);
        }
        $this->custom_updates_info_read = true;
    }


    public function customUpdatesAvailable()
    {
        // trunk does not support custom updates
        //		return false;

        $this->readCustomUpdatesInfo();
        if ($this->custom_updates_file_version > $this->custom_updates_current_version) {
            return true;
        }

        return false;
    }


    public function applyCustomUpdates()
    {
        $ilCtrlStructureReader = null;
        $ilMySQLAbstraction = null;
        $ilDB = null;
        $this->initGlobalsRequiredForUpdateSteps($ilCtrlStructureReader, $ilMySQLAbstraction, $ilDB);

        include_once './Services/Database/classes/class.ilMySQLAbstraction.php';

        $ilMySQLAbstraction = new ilMySQLAbstraction();
        $GLOBALS['DIC']['ilMySQLAbstraction'] = $ilMySQLAbstraction;

        $this->readCustomUpdatesInfo(true);

        $f = $this->getCustomUpdatesFileVersion();
        $c = $this->getCustomUpdatesCurrentVersion();

        if ($c < $f) {
            $msg = array();
            for ($i = ($c + 1); $i <= $f; $i++) {
                $this->filecontent = $this->custom_updates_content;

                if ($this->applyUpdateNr($i, false, true) == false) {
                    $msg[] = array("msg" => "update_error: " . $this->error,
                                   "nr" => $i,);
                    $this->updateMsg = $msg;

                    return false;
                } else {
                    $msg[] = array("msg" => "custom_update_applied",
                                   "nr" => $i,);
                }
            }

            $this->updateMsg = $msg;
        } else {
            $this->updateMsg = "no_changes";
        }

        return $this->loadXMLInfo();
    }


    /**
     * Get update steps as string (for presentation)
     *
     * @return string steps from the update file
     */
    public function getUpdateSteps($a_break = 0)
    {
        $ilCtrlStructureReader = null;
        $ilMySQLAbstraction = null;
        $ilDB = null;
        $this->initGlobalsRequiredForUpdateSteps($ilCtrlStructureReader, $ilMySQLAbstraction, $ilDB);

        $str = "";

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

                $str .= $this->getUpdateStepNr($i);
            }
        }

        return $str;
    }


    /**
     * Get hotfix steps
     *
     * @return string steps from the update file
     */
    public function getHotfixSteps()
    {
        $this->readHotfixInfo(true);

        $str = "";

        $f = $this->getHotfixFileVersion();
        $c = $this->getHotfixCurrentVersion();

        if ($c < $f) {
            $msg = array();
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
    public function getUpdateStepNr($nr, $hotfix = false, $custom_update = false)
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
        if ($i == count($this->filecontent)) {
            return false;
        }

        $i++;

        //update found, now extract this update to a new array
        $update = array();
        while ($i < count($this->filecontent) && !preg_match("/^<#" . ($nr + 1) . ">/", $this->filecontent[$i])) {
            $str .= $this->filecontent[$i];
            $i++;
        }

        return "<pre><b><#" . $nr . "></b>\n" . htmlentities($str) . "</pre>";
    }
}
